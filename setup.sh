#!/usr/bin/env bash
# Setup Kopi POS untuk Linux / macOS. Pastikan MySQL menyala dan database "kopi_pos" sudah dibuat.
set -e
cd "$(dirname "$0")"
if [ ! -f vendor/autoload.php ]; then
  command -v composer >/dev/null || { echo "Composer belum terpasang: https://getcomposer.org"; exit 1; }
  composer install --no-dev --optimize-autoloader --no-interaction
fi
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
php artisan migrate:fresh --seed --force
php artisan storage:link || true
php artisan optimize:clear
chmod -R ug+rw storage bootstrap/cache
echo "Selesai. Jalankan: php artisan serve  lalu buka http://127.0.0.1:8000 (login: owner / admin123)"
