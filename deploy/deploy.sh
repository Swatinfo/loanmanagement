#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# SHF Loan Management — Deployment Script
# ═══════════════════════════════════════════════════════════════
#
# Cache-busts all custom CSS/JS so browsers fetch the latest
# version instead of serving stale cached files.
#
# Usage:
#   bash deploy.sh              # auto-generates timestamp version
#   bash deploy.sh 2.0.1        # use a custom version string
#
# What it does:
#   1. Generates a version string (YYYYMMDDHHmmss or custom)
#   2. Updates SHF_VERSION in .env
#   3. Updates SHF_SW_VERSION in public/sw.js (triggers SW update)
#   4. Clears Laravel caches (config, view, route)
#
# Files affected:
#   - .env (SHF_VERSION)
#   - public/sw.js (SHF_SW_VERSION)
#   - config/app.php reads SHF_VERSION → appended as ?v= on all
#     custom CSS/JS in layouts/app.blade.php & layouts/guest.blade.php
#
# ═══════════════════════════════════════════════════════════════

set -e

# Use custom version if provided, otherwise auto-generate timestamp
V=${1:-$(date +%Y%m%d%H%M%S)}

echo "═══════════════════════════════════════"
echo "  SHF Deploy — Version: $V"
echo "═══════════════════════════════════════"

# 1. Update SHF_VERSION in .env
if grep -q "^SHF_VERSION=" .env 2>/dev/null; then
    sed -i "s/^SHF_VERSION=.*/SHF_VERSION=$V/" .env
    echo "  ✓ .env SHF_VERSION updated"
else
    echo "SHF_VERSION=$V" >> .env
    echo "  + .env SHF_VERSION added"
fi

# 2. Update SHF_SW_VERSION in both sw.js files (root + public)
for swfile in "sw.js" "public/sw.js"; do
    if [ -f "$swfile" ]; then
        sed -i "s/SHF_SW_VERSION = '[^']*'/SHF_SW_VERSION = '$V'/" "$swfile"
        echo "  ✓ $swfile SHF_SW_VERSION updated"
    fi
done

# 3. Clear Laravel caches
if [ -f "artisan" ]; then
    php artisan config:clear 2>/dev/null && echo "  ✓ Config cache cleared" || true
    php artisan view:clear 2>/dev/null && echo "  ✓ View cache cleared" || true
    php artisan route:clear 2>/dev/null && echo "  ✓ Route cache cleared" || true
    php artisan optimize:clear 2>/dev/null && echo "  ✓ all cache cleared" || true
fi

echo ""
echo "✅ Deployed with version: $V"
echo ""
echo "  Browsers will fetch fresh:"
echo "    → css/shf.css?v=$V"
echo "    → js/shf-app.js?v=$V"
echo "    → js/offline-manager.js?v=$V"
echo "    → js/pdf-renderer.js?v=$V"
echo "    → Service worker caches: shf-static-$V / shf-dynamic-$V"
