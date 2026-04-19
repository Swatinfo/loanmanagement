#!/usr/bin/env bash
# =============================================================================
#  setup-reverb-nginx.sh
#
#  Wires VestaCP's Nginx (reverse proxy → Apache) to forward WebSocket upgrade
#  requests on /app/ and server-to-server triggers on /apps/ to a local Reverb
#  listener. Idempotent — safe to re-run.
#
#  Usage (as root):
#    sudo ./setup-reverb-nginx.sh \
#         --domain loans.shfworld.com \
#         --user admin \
#         --reverb-port 6001
#
#  Defaults: user=admin, reverb-port=6001, domain=(required).
#
#  What it does:
#    1. Backs up the existing nginx.ssl.conf (timestamped).
#    2. Ensures /etc/nginx/nginx.conf has the `map $http_upgrade` block.
#    3. Writes /home/<user>/conf/web/<domain>/nginx.ssl.conf_shfreverb with
#       the WS upgrade + /apps proxy rules.
#    4. Inserts `include ...conf_shfreverb;` into nginx.ssl.conf ahead of the
#       catch-all `location /` (only if not already present).
#    5. `nginx -t` to validate, then `systemctl reload nginx`.
#
#  Notes:
#    - VestaCP's "Rebuild" in the panel will wipe the include line added in
#      step 4. To survive rebuilds permanently, follow §13A.D in
#      notification_setup.md (custom Nginx template).
#    - The port MUST match .env's REVERB_SERVER_PORT.
# =============================================================================

set -euo pipefail

# ---- Defaults ----------------------------------------------------------------
DOMAIN=""
VESTA_USER="admin"
REVERB_PORT="6001"
SSL_CONF_OVERRIDE=""

# ---- Parse args --------------------------------------------------------------
while [[ $# -gt 0 ]]; do
    case "$1" in
        --domain)       DOMAIN="$2"; shift 2 ;;
        --user)         VESTA_USER="$2"; shift 2 ;;
        --reverb-port)  REVERB_PORT="$2"; shift 2 ;;
        --ssl-conf)     SSL_CONF_OVERRIDE="$2"; shift 2 ;;
        -h|--help)
            sed -n '2,/^# =/p' "$0" | sed 's/^# \{0,1\}//'
            exit 0
            ;;
        *)
            echo "Unknown argument: $1" >&2
            exit 1
            ;;
    esac
done

if [[ $EUID -ne 0 ]]; then
    echo "Error: this script must be run as root (sudo)." >&2
    exit 1
fi

# ---- Interactive prompts for anything not supplied on CLI --------------------
# When running non-interactively (no TTY), the prompts auto-skip and we require
# the args to be passed explicitly.
if [[ -t 0 ]]; then
    if [[ -z "$DOMAIN" ]]; then
        read -rp "Domain (e.g. loans.shfworld.com): " DOMAIN
    fi
    if [[ -z "$VESTA_USER" || "$VESTA_USER" == "admin" ]]; then
        read -rp "VestaCP system user [${VESTA_USER:-admin}]: " input_user
        VESTA_USER="${input_user:-${VESTA_USER:-admin}}"
    fi
    if [[ -z "$REVERB_PORT" || "$REVERB_PORT" == "6001" ]]; then
        read -rp "Reverb internal port [${REVERB_PORT:-6001}]: " input_port
        REVERB_PORT="${input_port:-${REVERB_PORT:-6001}}"
    fi
fi

# Final validation — required even if we were non-interactive.
if [[ -z "$DOMAIN" ]]; then
    echo "Error: domain is required. Pass --domain loans.shfworld.com or run interactively." >&2
    exit 1
fi

if ! [[ "$REVERB_PORT" =~ ^[0-9]+$ ]] || (( REVERB_PORT < 1 || REVERB_PORT > 65535 )); then
    echo "Error: Reverb port '${REVERB_PORT}' is not a valid TCP port (1-65535)." >&2
    exit 1
fi

if ! [[ "$VESTA_USER" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
    echo "Error: user '${VESTA_USER}' doesn't look like a valid unix username." >&2
    exit 1
fi

# ---- Derived paths -----------------------------------------------------------
# Different VestaCP/HestiaCP versions store the SSL vhost config in different
# places. Probe the known layouts until one resolves to a real file.
POSSIBLE_SSL_CONFS=(
    "/home/${VESTA_USER}/conf/web/${DOMAIN}/nginx.ssl.conf"         # VestaCP (default)
    "/home/${VESTA_USER}/conf/web/${DOMAIN}.nginx.ssl.conf"         # VestaCP older
    "/home/${VESTA_USER}/conf/web/${DOMAIN}/nginx.ssl.conf_main"    # HestiaCP variant
    "/etc/nginx/conf.d/domains/${DOMAIN}.ssl.conf"                  # some HestiaCP builds
)

SSL_CONF=""
if [[ -n "$SSL_CONF_OVERRIDE" ]]; then
    if [[ -f "$SSL_CONF_OVERRIDE" ]]; then
        SSL_CONF="$SSL_CONF_OVERRIDE"
    else
        echo "Error: --ssl-conf points to a non-existent file: $SSL_CONF_OVERRIDE" >&2
        exit 1
    fi
else
    for candidate in "${POSSIBLE_SSL_CONFS[@]}"; do
        if [[ -f "$candidate" ]]; then
            SSL_CONF="$candidate"
            break
        fi
    done
fi

if [[ -z "$SSL_CONF" ]]; then
    echo "Error: could not find an SSL vhost config for ${DOMAIN}." >&2
    echo "Tried:" >&2
    for p in "${POSSIBLE_SSL_CONFS[@]}"; do echo "  - $p" >&2; done
    echo "" >&2
    echo "Run this to find it manually:" >&2
    echo "  sudo find /home/${VESTA_USER}/conf /etc/nginx -name '*${DOMAIN}*' 2>/dev/null" >&2
    echo "Then re-run this script with --ssl-conf /absolute/path/to/nginx.ssl.conf" >&2
    exit 1
fi

VHOST_DIR=$(dirname "$SSL_CONF")
# Derive the custom include path from the SSL conf so it works for BOTH the
# nested layout (<dir>/nginx.ssl.conf → <dir>/nginx.ssl.conf_shfreverb)
# AND the flat layout (<domain>.nginx.ssl.conf → <domain>.nginx.ssl.conf_shfreverb).
CUSTOM_CONF="${SSL_CONF}_shfreverb"
BACKUP="${SSL_CONF}.bak.$(date +%Y%m%d-%H%M%S)"
NGINX_MAIN="/etc/nginx/nginx.conf"

# ---- Colors ------------------------------------------------------------------
GREEN="\033[1;32m"; YELLOW="\033[1;33m"; RED="\033[1;31m"; RESET="\033[0m"
info()  { printf "${GREEN}→${RESET} %s\n" "$*"; }
warn()  { printf "${YELLOW}!${RESET} %s\n" "$*"; }
abort() { printf "${RED}✗${RESET} %s\n" "$*" >&2; exit 1; }

# ---- Sanity checks -----------------------------------------------------------
info "Target: ${DOMAIN} (user=${VESTA_USER}, reverb-port=${REVERB_PORT})"

[[ -d "$VHOST_DIR" ]] || abort "VestaCP vhost dir not found: ${VHOST_DIR}"
[[ -f "$SSL_CONF" ]]  || abort "Missing ${SSL_CONF} — is SSL enabled for ${DOMAIN}?"
command -v nginx >/dev/null || abort "nginx binary not found in PATH."

# Verify Reverb listener is either running or about to be. Warn only — the
# proxy config is safe to put in place even if Reverb comes up later.
if ! ss -tlnp 2>/dev/null | grep -q "127.0.0.1:${REVERB_PORT}"; then
    warn "Nothing is listening on 127.0.0.1:${REVERB_PORT} yet."
    warn "That's fine if you haven't started the shf-reverb service."
fi

# ---- Step 1: backup ----------------------------------------------------------
info "Backing up ${SSL_CONF} → ${BACKUP}"
cp -a "$SSL_CONF" "$BACKUP"

# ---- Step 2: ensure http-level map in /etc/nginx/nginx.conf ------------------
if grep -q '$http_upgrade' "$NGINX_MAIN"; then
    info "map \$http_upgrade already present in ${NGINX_MAIN}"
else
    info "Adding map \$http_upgrade to ${NGINX_MAIN}"
    cp -a "$NGINX_MAIN" "${NGINX_MAIN}.bak.$(date +%Y%m%d-%H%M%S)"
    # Insert the map block right after the first `http {` line.
    awk '
        /^[[:space:]]*http[[:space:]]*\{/ && !inserted {
            print
            print "    map $http_upgrade $connection_upgrade { default upgrade; \"\" close; }"
            inserted=1
            next
        }
        { print }
    ' "$NGINX_MAIN" > "${NGINX_MAIN}.tmp" && mv "${NGINX_MAIN}.tmp" "$NGINX_MAIN"
fi

# ---- Step 3: write the custom include file -----------------------------------
info "Writing ${CUSTOM_CONF}"
cat > "$CUSTOM_CONF" <<EOF
# Auto-generated by setup-reverb-nginx.sh — edit with care.
# Routes /app/ (WebSocket client) and /apps/ (server-to-server trigger) to
# the local Reverb listener. Must appear BEFORE the catch-all location /.

location /app/ {
    proxy_pass         http://127.0.0.1:${REVERB_PORT};
    proxy_http_version 1.1;
    proxy_set_header   Upgrade \$http_upgrade;
    proxy_set_header   Connection \$connection_upgrade;
    proxy_set_header   Host \$host;
    proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header   X-Forwarded-Proto \$scheme;
    proxy_read_timeout 86400;
    proxy_send_timeout 86400;
    proxy_buffering    off;
}

location /apps/ {
    proxy_pass         http://127.0.0.1:${REVERB_PORT};
    proxy_http_version 1.1;
    proxy_set_header   Host \$host;
    proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header   X-Forwarded-Proto \$scheme;
}
EOF
chown "${VESTA_USER}:${VESTA_USER}" "$CUSTOM_CONF"
chmod 644 "$CUSTOM_CONF"

# ---- Step 4: inject include into the main vhost config -----------------------
INCLUDE_LINE="    include ${CUSTOM_CONF};"

if grep -qF "$CUSTOM_CONF" "$SSL_CONF"; then
    info "Include line already present in ${SSL_CONF}"
else
    info "Injecting include line into ${SSL_CONF}"

    # Insert before the first "location /" block. This is an approximation;
    # if your vhost has unusual structure, verify manually after the script.
    awk -v incl="$INCLUDE_LINE" '
        BEGIN { done=0 }
        /location[[:space:]]*\/[[:space:]]*\{/ && !done {
            print incl
            done=1
        }
        { print }
    ' "$SSL_CONF" > "${SSL_CONF}.tmp" && mv "${SSL_CONF}.tmp" "$SSL_CONF"

    if ! grep -qF "$CUSTOM_CONF" "$SSL_CONF"; then
        warn "Could not auto-insert include; appending near end of server block."
        # Fallback — add before the closing brace of the server block.
        awk -v incl="$INCLUDE_LINE" '
            /^}/ && !done {
                print incl
                done=1
            }
            { print }
        ' "$SSL_CONF" > "${SSL_CONF}.tmp" && mv "${SSL_CONF}.tmp" "$SSL_CONF"
    fi
fi

# ---- Step 5: validate + reload -----------------------------------------------
info "Testing nginx configuration"
if ! nginx -t 2>&1; then
    warn "nginx -t failed. Restoring original vhost config from ${BACKUP}."
    cp -a "$BACKUP" "$SSL_CONF"
    abort "Nginx config test failed; original restored. Fix the error then re-run."
fi

info "Reloading nginx"
systemctl reload nginx

# ---- Done --------------------------------------------------------------------
printf "\n${GREEN}✓${RESET} Nginx is now proxying /app/ and /apps/ on ${DOMAIN} → 127.0.0.1:${REVERB_PORT}\n\n"

echo "Verify with:"
echo "  curl -sI -o /dev/null -w 'http_code=%{http_code}\\n' https://${DOMAIN}/app/"
echo "    → expect 400 (Reverb rejects non-WS GET — proves the proxy forwarded)"
echo ""
echo "Backup kept at: ${BACKUP}"
echo ""
echo "Note: if you re-save ${DOMAIN} in VestaCP's panel, the include line in"
echo "      nginx.ssl.conf gets wiped. Re-run this script, or use a custom"
echo "      Nginx template (see notification_setup.md §13A.D)."
