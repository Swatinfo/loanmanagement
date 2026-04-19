# Production Notification Setup

End-to-end recipe to get **real-time bell updates (Reverb)** and **native OS push notifications (Web Push)** working on your live server.

Flow once set up:

```
Browser ──► Web server :443 (TLS)
             ├─ /*        → Laravel (PHP-FPM / Apache)
             ├─ /app/*    → Reverb  (WebSocket client connections)
             └─ /apps/*   → Reverb  (server-to-server event API)

Laravel ──► Reverb  (via BROADCAST_* env — talks to 127.0.0.1:PORT directly)
Laravel ──► FCM / APNs via VAPID-signed Web Push payloads
```

---

## Table of contents

**Part 1 — common setup (do these regardless of your stack):**

- [§1 Prerequisites](#1-prerequisites)
- [§2 Environment variables](#2-environment-variables)
- [§3 Generate VAPID keys](#3-generate-vapid-keys)
- [§4 Migrations](#4-migrations)
- [§5 Queue worker (via scheduler)](#5-queue-worker--driven-by-the-scheduler)
- [§6 Scheduler cron](#6-scheduler-cron)
- [§7 Service worker + HTTPS requirements](#7-service-worker--https-requirements)

**Part 2 — pick ONE web-server stack:**

- [§A VestaCP / HestiaCP (Apache + Nginx managed by the panel)](#a-vestacp--hestiacp)
- [§B Apache only](#b-apache-only)
- [§C Nginx only](#c-nginx-only)
- [§D Apache + Nginx (hand-configured reverse proxy)](#d-apache--nginx-hand-configured-reverse-proxy)
- [§E Windows (Apache or IIS)](#e-windows-apache-or-iis)

**Part 3 — verification + ops:**

- [§8 End-to-end test](#8-end-to-end-test)
- [§9 Scaling to multiple app servers](#9-scaling-to-multiple-app-servers)
- [§10 Troubleshooting](#10-troubleshooting)
- [§11 Deploy checklist](#11-deploy-checklist)
- [§12 What the user sees](#12-what-the-user-sees)

---

# Part 1 — Common setup

## 1. Prerequisites

On Linux (Debian/Ubuntu):

```bash
sudo apt update
sudo apt install -y php-cli php-pcntl php-posix php-curl php-gmp openssl
```

- `php-pcntl` + `php-posix` — Reverb's long-running server needs these
- `php-gmp` — required by the web-push crypto
- `openssl` CLI — VAPID key generation fallback when PHP's built-in EC keygen breaks

Verify:

```bash
php -m | grep -iE 'pcntl|posix|curl|gmp|openssl|sockets'
# Expect: curl, gmp, openssl, pcntl, posix, sockets
```

On Windows, the PHP build that ships with FlyEnv or Laragon already has these extensions enabled.

---

## 2. Environment variables

Append this block to your production `.env` (adjust for your domain):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
ASSET_URL=https://your-domain.com

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

# ── Reverb (real-time bell) ─────────────────────────────────
REVERB_APP_ID=<random>
REVERB_APP_KEY=<random>
REVERB_APP_SECRET=<random>

# What the browser connects to — your public HTTPS URL
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# What Reverb itself binds to locally. Web server proxies /app/ here.
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=6001

# What Laravel uses to talk to Reverb server-to-server. Stays internal.
BROADCAST_REVERB_HOST=127.0.0.1
BROADCAST_REVERB_PORT=6001
BROADCAST_REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# ── Web Push (OS notifications) ─────────────────────────────
VAPID_SUBJECT="mailto:admin@your-domain.com"
VAPID_PUBLIC_KEY=        # fill in from §3
VAPID_PRIVATE_KEY=       # fill in from §3
```

Generate strong IDs:

```bash
php -r 'echo bin2hex(random_bytes(8))."\n";'   # REVERB_APP_ID
php -r 'echo bin2hex(random_bytes(10))."\n";'  # REVERB_APP_KEY
php -r 'echo bin2hex(random_bytes(10))."\n";'  # REVERB_APP_SECRET
```

Then:

```bash
cd /path/to/app
php artisan config:clear
php artisan config:cache
```

Pick your Reverb internal port based on what's free (many VestaCP servers use 8080 for Apache, so 6001 is the safe default used throughout this guide). Check:

```bash
ss -tlnp | grep -E ':(6001|8080)\b'
```

---

## 3. Generate VAPID keys

Try the packaged command first:

```bash
php artisan webpush:vapid
```

If it errors with *"Unable to create the key"* (PHP build with missing `openssl.cnf`), use the openssl CLI fallback:

```bash
cd /tmp
openssl ecparam -name prime256v1 -genkey -noout -out vapid.pem
openssl ec -in vapid.pem -text -noout > vapid.txt

PRIV_HEX=$(awk '/priv:/{flag=1;next} /pub:/{flag=0} flag' vapid.txt | tr -d ' :\n' | sed 's/^00//')
PUB_HEX=$(awk '/pub:/{flag=1;next} /ASN1/{flag=0} flag' vapid.txt | tr -d ' :\n')

PRIV_B64=$(echo -n "$PRIV_HEX" | xxd -r -p | base64 -w 0 | tr '/+' '_-' | tr -d '=')
PUB_B64=$(echo -n "$PUB_HEX" | xxd -r -p | base64 -w 0 | tr '/+' '_-' | tr -d '=')

echo "VAPID_PUBLIC_KEY=\"$PUB_B64\""
echo "VAPID_PRIVATE_KEY=\"$PRIV_B64\""
rm vapid.pem vapid.txt
```

Paste the printed two lines into `.env`. **One fresh keypair per environment** — never reuse dev keys in production; existing subscriptions would all silently fail.

Tighten `.env` perms:

```bash
chown admin:admin /path/to/app/.env   # or www-data on non-panel setups
chmod 640 /path/to/app/.env
```

---

## 4. Migrations

```bash
cd /path/to/app
php artisan migrate --force
```

Creates `push_subscriptions`, `activity_log`, and (if not already present) the queue/session tables.

Verify:

```bash
php artisan tinker --execute="echo DB::table('push_subscriptions')->count();"
# 0 → table exists
```

---

## 5. Queue worker — driven by the scheduler

No systemd or supervisor unit is needed for the queue. The worker is fired by the Laravel scheduler every minute via `routes/console.php`:

```php
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=120')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

- `--stop-when-empty` — drains pending jobs then exits
- `everyMinute()` — worst-case 1-minute latency between enqueue and dispatch
- `withoutOverlapping()` — only one worker at a time
- `runInBackground()` — other scheduled commands aren't blocked while this drains

This is already committed to the repo — you only need to wire the cron in §6.

After every deploy:

```bash
cd /path/to/app
php artisan queue:restart
```

**When to switch to a persistent worker:** if you later add latency-sensitive jobs (SMS with hard SLAs), swap to a supervisor/systemd unit that runs `queue:work` continuously. Notifications don't need it.

---

## 6. Scheduler cron

One crontab line drives both the queue worker **and** the 48h auto-mark-read:

```bash
sudo -u admin crontab -e          # VestaCP: user owning the site
#  OR
sudo -u www-data crontab -e       # non-panel setups
```

Add:

```
* * * * * cd /path/to/app && /usr/bin/php artisan schedule:run >> /var/log/shf/schedule.log 2>&1
```

Log dir:

```bash
sudo mkdir -p /var/log/shf
sudo chown admin:admin /var/log/shf   # or www-data:www-data
```

Verify:

```bash
sudo -u admin php /path/to/app/artisan schedule:list
```

Should show both `queue:work` (next due in under a minute) and `notifications:mark-stale-read` (next due at the top of the hour).

---

## 7. Service worker + HTTPS requirements

Web Push requires:

- **HTTPS** on the public origin (use Let's Encrypt / VestaCP's built-in cert automation).
- `/sw.js` served with `Content-Type: application/javascript` from the same origin.
- The service worker scope covers the whole site (we serve it from `/` root).

Verify after deploy:

```bash
curl -I https://your-domain.com/sw.js
# HTTP/2 200
# content-type: application/javascript
```

---

# Part 2 — Pick ONE web-server stack

## §A VestaCP / HestiaCP

VestaCP wraps **Nginx → Apache** with its own config manager. HestiaCP is a maintained fork with the same layout — everything here applies to both.

### A.1 Port conflict

VestaCP's Apache typically listens on `:8080` per user. Pick a different port for Reverb — **6001** is the default for this guide.

```bash
cat /home/admin/conf/web/loans.shfworld.com/apache2.conf | grep -i Listen
```

If Apache is on 8080, make sure `.env` has:

```env
REVERB_SERVER_PORT=6001
BROADCAST_REVERB_PORT=6001
```

### A.2 Reverb as a systemd service (runs under the VestaCP user)

`/etc/systemd/system/shf-reverb.service`:

```ini
[Unit]
Description=SHF Reverb WebSocket server
After=network.target

[Service]
Type=simple
User=admin
Group=admin
Restart=always
RestartSec=5
WorkingDirectory=/home/admin/web/loans.shfworld.com/public_html
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=6001
TimeoutStopSec=10
LimitNOFILE=10000
StandardOutput=append:/var/log/shf/reverb.log
StandardError=append:/var/log/shf/reverb.log

[Install]
WantedBy=multi-user.target
```

Enable:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now shf-reverb
sudo systemctl status shf-reverb
```

**PHP path tip:** VestaCP can pin a different PHP version per site. Check `sudo -u admin php -v`; if not the version you want, use the absolute path (e.g. `/usr/bin/php8.2`) in `ExecStart`.

### A.3 Nginx config — use the provided script

Run the deploy script shipped at `deploy/setup-reverb-nginx.sh`:

```bash
chmod +x deploy/setup-reverb-nginx.sh
sudo ./deploy/setup-reverb-nginx.sh \
     --domain loans.shfworld.com \
     --user admin \
     --reverb-port 6001
```

The script:

1. Backs up `nginx.ssl.conf` (timestamped).
2. Ensures the `map $http_upgrade $connection_upgrade` block is in `/etc/nginx/nginx.conf`.
3. Writes `/home/admin/conf/web/loans.shfworld.com/nginx.ssl.conf_shfreverb` with the `/app/` + `/apps/` proxy rules.
4. Injects an `include ...conf_shfreverb;` line into the main vhost config **before** the catch-all `location /`.
5. Runs `nginx -t` — restores the backup if validation fails.
6. Reloads Nginx.

Re-run it whenever a VestaCP "Rebuild" wipes the include line.

### A.4 Making it survive "Rebuild" permanently (optional)

VestaCP regenerates `nginx.ssl.conf` on panel save. Two options:

**Custom Nginx template (recommended)**

```bash
sudo cp /usr/local/vesta/data/templates/web/nginx/php-fpm/default.stpl \
         /usr/local/vesta/data/templates/web/nginx/php-fpm/shf-reverb.stpl
sudo cp /usr/local/vesta/data/templates/web/nginx/php-fpm/default.tpl \
         /usr/local/vesta/data/templates/web/nginx/php-fpm/shf-reverb.tpl
```

Inside each file's `server {}` block, paste the same `location /app/` + `location /apps/` blocks from the script. Then in the VestaCP panel: the site → **Proxy Template** → `shf-reverb` → Save. Rebuilds now preserve it.

### A.5 VestaCP firewall

Reverb on `127.0.0.1:6001` isn't publicly exposed. No firewall rule required. To explicitly deny:

```bash
sudo v-add-firewall-rule DROP 0.0.0.0/0 6001 TCP "Block external Reverb"
```

### A.6 Redis (only if scaling — §9)

```bash
sudo apt install -y redis-server php-redis
sudo systemctl enable --now redis-server
```

### A.7 Sanity check

```bash
sudo systemctl status shf-reverb | head -5
sudo nginx -T | grep -A 10 'loans.shfworld.com' | grep -E 'location|proxy_pass'
sudo -u admin php /home/admin/web/loans.shfworld.com/public_html/artisan schedule:list | head -5
```

---

## §B Apache only

When Apache is the only front-end server (no Nginx reverse proxy). You'll use Apache's own `mod_proxy_wstunnel` to upgrade WebSocket requests.

### B.1 Enable the required modules

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

### B.2 Reverb as a systemd service

Same as §A.2 but adjust `User=` and `WorkingDirectory=` for your Apache user (usually `www-data`):

```ini
[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/shf
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=6001
```

### B.3 Apache virtual host — add WebSocket proxy

Edit your site's `:443` VirtualHost (e.g. `/etc/apache2/sites-available/your-domain-ssl.conf`):

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/shf/public

    SSLEngine On
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

    # ── Reverb WebSocket proxy ──
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ "ws://127.0.0.1:6001/app/$1" [P,L]

    ProxyPass        /apps/ http://127.0.0.1:6001/apps/
    ProxyPassReverse /apps/ http://127.0.0.1:6001/apps/

    # ── PHP / FastCGI (your existing config) ──
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>

    <Directory /var/www/shf/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

The `RewriteCond` / `RewriteRule` for `/app/` catches WebSocket upgrades and proxies them as `ws://`. Non-WS requests to `/app/...` fall through to normal handling (which 404s — that's fine).

### B.4 Test + reload

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### B.5 Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 6001/tcp      # Reverb is localhost-only; explicit deny
sudo ufw enable
```

---

## §C Nginx only

When Nginx serves Laravel directly via PHP-FPM (no Apache). This is the simplest layout.

### C.1 Reverb service

Same systemd unit as §A.2 but with `User=www-data` and the correct `WorkingDirectory`.

### C.2 `http {}` level map (one-time)

In `/etc/nginx/nginx.conf`, inside `http { }`:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
```

### C.3 Site vhost — `/etc/nginx/sites-available/your-domain.com`

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/shf/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # ── Reverb WebSocket proxy (MUST appear before location /) ──
    location /app/ {
        proxy_pass         http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection $connection_upgrade;
        proxy_set_header   Host $host;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
        proxy_buffering    off;
    }

    location /apps/ {
        proxy_pass         http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header   Host $host;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # ── Laravel ──
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$host$request_uri;
}
```

**Ordering matters:** `location /app/` must come **before** `location /`. Nginx picks the most specific prefix — without the explicit `/app/` block, WS requests hit `try_files` and 404.

### C.4 Test + reload

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### C.5 Firewall

Same as §B.5.

---

## §D Apache + Nginx (hand-configured reverse proxy)

When Nginx sits in front of Apache on a vanilla (non-panel) server — Nginx terminates TLS and forwards to Apache on an internal port.

### D.1 Apache — bind to localhost only

`/etc/apache2/ports.conf`:

```apache
Listen 127.0.0.1:80
```

```bash
sudo systemctl reload apache2
```

No mod_proxy_wstunnel needed — Nginx does the WebSocket upgrade, not Apache.

### D.2 Reverb service

Same as §B.2 (usually `User=www-data`).

### D.3 Nginx config

`/etc/nginx/nginx.conf` inside `http { }`:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
```

`/etc/nginx/sites-available/your-domain.com`:

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate     /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # ── Reverb: WebSocket client connections ──
    location /app/ {
        proxy_pass         http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection $connection_upgrade;
        proxy_set_header   Host $host;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
        proxy_buffering    off;
    }

    # ── Reverb: server-to-server event API ──
    location /apps/ {
        proxy_pass         http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header   Host $host;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # ── Everything else → Apache ──
    location / {
        proxy_pass         http://127.0.0.1:80;
        proxy_http_version 1.1;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$host$request_uri;
}
```

### D.4 Test + reload

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### D.5 Firewall

Same as §B.5, plus **do not** expose Apache's `:80` publicly (already bound to localhost in §D.1).

---

## §E Windows (Apache or IIS)

Dev setups (FlyEnv, Laragon, XAMPP) and Windows VPS.

### E.1 Install prerequisites

- PHP 8.2+ with `pcntl`, `posix`, `curl`, `gmp`, `openssl`, `sockets` enabled — check via `php -m`
- [NSSM](https://nssm.cc) — to run Reverb + scheduler as Windows services
- OpenSSL CLI (bundled with Git for Windows)

### E.2 Reverb as a Windows service (NSSM)

```powershell
nssm install SHFReverb "C:\php\php.exe" `
      "F:\sites\shf\artisan" reverb:start --host=127.0.0.1 --port=6001
nssm set SHFReverb AppDirectory "F:\sites\shf"
nssm set SHFReverb Start SERVICE_AUTO_START
nssm set SHFReverb AppStdout "C:\logs\shf-reverb.log"
nssm set SHFReverb AppStderr "C:\logs\shf-reverb.log"
nssm start SHFReverb
```

### E.3 Scheduler as a scheduled task

Windows doesn't have cron. Use Task Scheduler to run:

```
C:\php\php.exe F:\sites\shf\artisan schedule:run
```

Trigger: **Daily, repeat every 1 minute for a duration of 24 hours.**

This fires the scheduler once per minute, driving both the queue worker and the 48h auto-mark-read.

### E.4 Apache on Windows

If you're using Apache (FlyEnv, XAMPP):

Follow §B but the Apache config paths are different (FlyEnv: `C:\Program Files\FlyEnv-Data\server\vhost\apache\<domain>.conf`). Load the same modules (`proxy`, `proxy_http`, `proxy_wstunnel`, `rewrite`, `ssl`, `headers`) — uncomment them in Apache's `httpd.conf`.

### E.5 IIS on Windows

IIS needs the **Application Request Routing (ARR)** + **URL Rewrite** modules. In your site's `web.config`:

```xml
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
        <rule name="Reverb WebSocket" stopProcessing="true">
          <match url="^app/(.*)" />
          <action type="Rewrite" url="http://127.0.0.1:6001/app/{R:1}" />
        </rule>
        <rule name="Reverb Trigger" stopProcessing="true">
          <match url="^apps/(.*)" />
          <action type="Rewrite" url="http://127.0.0.1:6001/apps/{R:1}" />
        </rule>
      </rules>
    </rewrite>
    <webSocket enabled="true" />
  </system.webServer>
</configuration>
```

Enable the WebSocket Protocol feature in Windows Server Manager under IIS.

### E.6 Windows Firewall

Open 80/443 public; keep 6001 local-only. Windows Firewall rule:

```powershell
New-NetFirewallRule -DisplayName "Reverb local only" -Direction Inbound `
  -Protocol TCP -LocalPort 6001 -LocalAddress 127.0.0.1 -Action Allow
```

---

# Part 3 — Verification + ops

## 8. End-to-end test

From the server shell:

```bash
cd /path/to/app
sudo -u admin php artisan tinker --execute='
  $u = App\Models\User::find(1);
  App\Models\ShfNotification::create([
    "user_id" => $u->id,
    "title"   => "Prod test / પ્રોડ ટેસ્ટ",
    "message" => "End-to-end notification check",
    "type"    => "info",
    "link"    => "/dashboard",
  ]);
  echo "Dispatched to {$u->id} / {$u->name}\n";
'
```

In the browser logged in as user 1 on `https://your-domain.com`:

1. DevTools → **Network → WS** — expect `wss://your-domain.com/app/<APP_KEY>?protocol=7&...` with status **101 Switching Protocols**.
2. The bell badge ticks immediately after the tinker command.
3. Close the tab, rerun tinker — expect a **native OS notification** (if the user has opted into Web Push).

Quick proxy reachability test without opening a browser:

```bash
curl -sI -o /dev/null -w 'http_code=%{http_code}\n' https://your-domain.com/app/
# Expected: 400 — Reverb rejects non-WebSocket GETs, proves the proxy forwarded
```

---

## 9. Scaling to multiple app servers

Reverb's default mode keeps WebSocket clients in memory per-process. If you add a second app server, broadcasts from server A won't reach subscribers on server B. Enable Redis-backed scaling:

```env
REVERB_SCALING_ENABLED=true
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=...
```

All servers must point at the **same** Redis. All servers must run `reverb:start`. Your load balancer should use **sticky sessions** for the WebSocket upstream (or hash by source IP).

---

## 10. Troubleshooting

| Symptom | Check / fix |
|---|---|
| `Echo init failed` in browser console | `VITE_REVERB_*` matches `REVERB_*`; hard-reload to bust the Service Worker cache |
| `wss://` connection returns 404 | Nginx `location /app/` isn't before `location /`, or `server_name` mismatch |
| `wss://` returns 502 | Reverb isn't running — `systemctl status shf-reverb` |
| `wss://` stays "Pending" / times out | `proxy_read_timeout` too low; must be ≥ 86400 for long-lived WS |
| Broadcast never fires (DB has notification, no bell update) | `BROADCAST_CONNECTION=reverb`, ran `config:cache`, scheduler/cron is running |
| Broadcast fails with `SSL certificate problem: unable to get local issuer` | Laravel is calling HTTPS for the server-to-server trigger — set `BROADCAST_REVERB_SCHEME=http` and `BROADCAST_REVERB_HOST=127.0.0.1` |
| OS push notification never arrives | User didn't opt in, VAPID mismatch between env and subscriptions, or subscription expired (user cleared browser data) |
| `getaddrinfo failed` in `shf-reverb.log` | `REVERB_SERVER_HOST=127.0.0.1`, not the public domain |
| Queue jobs pile up in `jobs` table | Scheduler cron isn't firing — verify `crontab -l` and `php artisan schedule:list` next-due times |
| `php-pcntl ... not found` when Reverb starts | `sudo apt install php-pcntl php-posix`, then `systemctl restart shf-reverb` |
| VestaCP rebuild wiped the Nginx include | Re-run `deploy/setup-reverb-nginx.sh`, or switch to a custom Nginx template (§A.4) |

### Useful one-liners

```bash
# Watch Reverb in real time
sudo journalctl -u shf-reverb -f

# Watch the scheduler (queue + stale-read sweeps log here)
sudo tail -f /var/log/shf/schedule.log

# How many subscriptions belong to a user
php artisan tinker --execute='echo App\Models\User::find(1)->pushSubscriptions()->count();'

# Flush & retry failed queued jobs
php artisan queue:flush
php artisan queue:retry all
```

---

## 11. Deploy checklist

Run through this once per release:

- [ ] `.env` has live `REVERB_*`, `BROADCAST_REVERB_*`, `VAPID_*` values (**different from dev**)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:clear && php artisan config:cache`
- [ ] `php artisan route:cache && php artisan view:clear`
- [ ] `php artisan queue:restart` (signals any running scheduled worker to pick up new code)
- [ ] `sudo systemctl restart shf-reverb` *(only if the Reverb package version changed — code reload isn't automatic for Reverb)*
- [ ] Crontab intact: `sudo -u admin crontab -l | grep schedule:run`
- [ ] `sudo -u admin php artisan schedule:list` shows `queue:work` + `notifications:mark-stale-read`
- [ ] Test: load site, confirm `wss://` connects (Network → WS), fire a test notification
- [ ] `curl -I https://your-domain.com/sw.js` returns 200
- [ ] Log rotation for `/var/log/shf/*.log` — add to `/etc/logrotate.d/shf`

### `/etc/logrotate.d/shf` starter:

```
/var/log/shf/*.log {
    daily
    rotate 14
    compress
    delaycompress
    missingok
    notifempty
    create 0640 admin admin
    sharedscripts
    postrotate
        systemctl reload shf-reverb >/dev/null 2>&1 || true
    endscript
}
```

---

## 12. What the user sees

1. Logs in → opt-in banner slides up (or silent resync if they've already granted).
2. Clicks **Enable** → browser permission prompt → Allow.
3. `/api/push/subscribe` fires, endpoint stored under their `user_id` in `push_subscriptions`.
4. On every loan stage change / query / task assignment routed to them:
   - **Tab open**: bell badge ticks, chime plays, toast appears (Reverb path).
   - **Tab closed / browser closed / PWA backgrounded**: native OS notification (Web Push path).
5. Click the notification → focuses an existing SHF tab (or opens a new one) and deep-links to `/loans/{id}/stages` or `/general-tasks/{id}`.
6. After **48 hours**, unread notifications are auto-marked read.
7. `/notifications` shows all unread first, then only the last 5 read — list stays focused.
