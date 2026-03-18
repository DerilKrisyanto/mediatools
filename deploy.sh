#!/bin/bash
set -e

echo "🚀 Deploying MediaTools..."
cd /var/www/mediatools

git config --global --add safe.directory /var/www/mediatools 2>/dev/null || true
git config pull.rebase false

echo "📦 Sync dengan GitHub..."
git fetch origin
git reset --hard origin/main

echo "📚 Update PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔨 Build assets..."
npm install --silent
npm run build

echo "📁 Buat folder yang dibutuhkan..."
mkdir -p storage/app/file_converter
mkdir -p storage/app/pdf_utilities
mkdir -p storage/app/image_converter
mkdir -p storage/app/public/uploads
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

echo "🗄️ Migrate database..."
php artisan config:clear
php artisan migrate --force

echo "⚡ Optimize Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔗 Storage link..."
php artisan storage:link --force 2>/dev/null || true

echo "🔄 Restart queue workers..."
php artisan queue:restart
supervisorctl restart mediatools-worker:* 2>/dev/null || true

echo "🔒 Fix permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chmod -R 777 storage/app/file_converter
chmod -R 777 storage/app/pdf_utilities
chmod -R 777 storage/app/image_converter

mkdir -p /var/www/.cache/dconf
mkdir -p /var/www/.cache/fontconfig
mkdir -p /var/www/.config/libreoffice
chown -R www-data:www-data /var/www/.cache
chown -R www-data:www-data /var/www/.config 2>/dev/null || true
chown -R www-data:www-data /var/www/.local 2>/dev/null || true

echo ""
echo "✅ Deploy selesai! https://mediatools.cloud"
