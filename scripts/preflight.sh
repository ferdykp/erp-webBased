#!/usr/bin/env bash
# BeamCustom release preflight. Read-only checks; does not migrate or clear the database.
set -uo pipefail
cd "$(dirname "$0")/.."
FAIL=0
warn() { printf 'WARN  %s\n' "$1"; }
pass() { printf 'PASS  %s\n' "$1"; }
fail() { printf 'FAIL  %s\n' "$1"; FAIL=1; }
command -v php >/dev/null && pass 'PHP available' || fail 'PHP not found'
command -v composer >/dev/null && pass 'Composer available' || warn 'Composer unavailable'
command -v npm >/dev/null && pass 'npm available' || warn 'npm unavailable'
if command -v php >/dev/null; then
 php -r 'exit(version_compare(PHP_VERSION,"8.5", "<") && version_compare(PHP_VERSION,"8.2", ">=") ? 0 : 1);' && pass 'PHP 8.2-8.4 compatible with locked spreadsheet package' || fail 'Use PHP 8.2-8.4 for current composer.lock'
fi
[ -f .env ] && pass '.env exists' || fail '.env missing'
if [ -f .env ]; then
 grep -Eq '^APP_ENV=production\s*$' .env && pass 'APP_ENV production' || warn 'APP_ENV is not production'
 grep -Eq '^APP_DEBUG=(false|0)\s*$' .env && pass 'Debug disabled' || fail 'Set APP_DEBUG=false before launch'
 grep -Eq '^APP_KEY=base64:[^[:space:]]+' .env && pass 'Application key set' || fail 'Missing APP_KEY'
 grep -Eq '^APP_URL=https://' .env && pass 'HTTPS application URL' || warn 'Configure APP_URL with HTTPS in production'
fi
[ -f vendor/autoload.php ] && pass 'Composer dependencies installed' || fail 'Run composer install'
[ -f public/build/manifest.json ] && pass 'Vite manifest present' || fail 'Run npm ci && npm run build'
[ -L public/storage ] && pass 'Public storage link exists' || warn 'Check whether php artisan storage:link is required'
for d in storage/framework/cache storage/framework/views storage/framework/sessions storage/logs bootstrap/cache; do
 [ -d "$d" ] && pass "Directory $d exists" || warn "Create/check $d"
done
if [ -f vendor/autoload.php ]; then
 php artisan route:list --compact >/dev/null 2>&1 && pass 'Routes boot correctly' || { php artisan route:list >/dev/null 2>&1 && pass 'Routes boot correctly' || fail 'Laravel route registration failed'; }
 php artisan migrate:status >/dev/null 2>&1 && pass 'Migration status accessible' || warn 'Migration status unavailable; check DB configuration'
fi
[ "$FAIL" -eq 0 ] && echo 'Preflight finished: no blocking checks (warnings still need review).' || echo 'Preflight failed: resolve FAIL items before deployment.'
exit "$FAIL"
