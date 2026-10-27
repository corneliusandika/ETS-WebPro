#!/usr/bin/env bash
set -euo pipefail

cd /home/site/wwwroot

# Direktori yang dibutuhkan Laravel dan Dompdf.
mkdir -p \
    storage/framework/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/fonts \
    bootstrap/cache \
    /home/data

chmod -R ug+rwX storage bootstrap/cache || true
chmod 775 /home/data || true

# Arahkan nginx ke public/ Laravel.
cp azure/default /etc/nginx/sites-available/default
if [ -d /etc/nginx/sites-enabled ]; then
    cp azure/default /etc/nginx/sites-enabled/default
fi

nginx -t
service nginx reload || nginx -s reload

# SQLite disimpan di /home/data agar bertahan saat deploy/restart.
touch /home/data/database.sqlite
chmod 664 /home/data/database.sqlite || true

php artisan migrate --force || echo "WARNING: migration failed; cek log Laravel"

# Seed demo hanya sekali agar data tidak terduplikasi.
if [ ! -f /home/data/.seeded ]; then
    if php artisan db:seed --force; then
        touch /home/data/.seeded
    else
        echo "WARNING: seeding failed; cek log Laravel"
    fi
fi

php artisan config:cache || echo "WARNING: config cache failed"
