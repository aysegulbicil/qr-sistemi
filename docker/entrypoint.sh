#!/bin/sh
set -e

cd /var/www/html

# CI4 cekirdegi (vendor) kurulu degilse composer install calistir.
# Kosul autoload.php uzerinden: bos/yarim bir vendor klasoru de kurulumu tetikler.
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ eksik -> composer install calistiriliyor..."
    composer install --no-interaction --prefer-dist --no-progress \
      || echo "[entrypoint] composer install BASARISIZ. Elle calistir: docker compose exec web composer install"
fi

# CI4 'writable' klasoru: alt klasorleri olustur + yazma izinleri ver
mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar
chown -R www-data:www-data writable 2>/dev/null || true
chmod -R 0777 writable 2>/dev/null || true

exec "$@"
