#!/bin/bash
set -euo pipefail

echo "=== DEPLOY START ==="

cd /var/www/erp-webBased || exit

echo "Pull latest code..."
git pull origin main

echo "Install composer..."
composer install --no-dev --optimize-autoloader

echo "Clear cache..."
php artisan optimize:clear

echo "Run migration..."
php artisan migrate --force

echo "Move sensitive uploads to private storage..."
php artisan uploads:secure

echo "Build assets..."
npm install
npm run build

echo "Optimize Laravel..."
php artisan optimize

echo "Restart queue..."
php artisan queue:restart

echo "=== DEPLOY DONE ==="
