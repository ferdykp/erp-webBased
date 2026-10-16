#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
echo "Checking JavaScript syntax and receiving regression tests..."
node --check resources/js/receiving-assistant.js
node --test tests/js/*.test.mjs

echo "Checking application PHP syntax..."
find app routes database/migrations -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

echo "Checking project prerequisites..."
if [ -f vendor/autoload.php ]; then
    php scripts/check-blade.php
    php artisan route:list --json >/dev/null
    php artisan test
else
    echo 'SKIP route and Laravel tests: vendor/autoload.php missing'
fi
if [ -d node_modules ]; then
    npm run build
else
    echo 'SKIP Vite build: node_modules missing'
fi
