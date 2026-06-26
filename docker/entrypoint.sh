#!/bin/sh
set -e

cd /var/www/html

# CI4 uygulamasi mevcutsa ve bagimliliklar kurulu degilse composer install calistir
if [ -f composer.json ] && [ ! -d vendor ]; then
    echo "[entrypoint] composer.json bulundu -> composer install calistiriliyor..."
    composer install --no-interaction --prefer-dist --no-progress \
      || echo "[entrypoint] composer install basarisiz. Elle: docker compose exec web composer install"
fi

# CI4 'writable' klasoru: alt klasorleri olustur + yazma izinleri ver
mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar
chown -R www-data:www-data writable 2>/dev/null || true
chmod -R 0777 writable 2>/dev/null || true

exec "$@"
