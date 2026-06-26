# qr-sistemi — Docker Kurulumu

Bu klasör şu an **CodeIgniter 4** için hazır bir Docker iskelesi içerir.
Uygulama kodu henüz eklenmedi; yapı, kodu eklediğinde `docker compose up`
ile çalışacak şekilde hazırlandı. (Aynı stack: barlas-trailer = CI4 / PHP 8.2)

## Gereksinimler
- Docker Desktop (Windows'ta WSL2 ile)

## Klasör yapısı
```
qr-sistemi/
├─ Dockerfile              # PHP 8.2 + Apache imajı (CI4 için)
├─ docker-compose.yml      # web (+ profil ile db / phpmyadmin)
├─ .dockerignore
├─ .env.example            # '.env' olarak kopyalanacak örnek
└─ docker/
   ├─ apache/000-default.conf   # docroot = public/ , rewrite açık
   ├─ php/php.ini               # özel PHP ayarları
   └─ entrypoint.sh             # composer install + writable izinleri
```

## Hızlı başlangıç
```bash
# 0) Proje klasörüne gir (ÖNEMLİ - komutlar bu klasörde çalışır)
cd C:\Users\precocious\Desktop\qr-sistemi

# 1) Ortam dosyasını hazırla
cp .env.example .env

# 2) İmajı kur ve web servisini başlat
docker compose up -d --build
```
Site: http://localhost:8090

> **Portlar:** barlas-trailer zaten 8080'i (phpMyAdmin 8081) kullanıyor.
> qr-sistemi çakışmasın diye **8090** (phpMyAdmin 8091, DB 3307) kullanır.
> İkisi aynı anda, birbirinden bağımsız çalışır — barlas'a dokunulmaz.

> Not: qr-sistemi klasörü şu an **boş** olduğu için tarayıcıda 403/404
> görmen normaldir. CI4 uygulamasını ekledikten sonra çalışacaktır.

## CI4 uygulamasını eklemek (ileride)
**A) Sıfırdan yeni CI4 uygulaması** (Docker dosyalarını koruyarak):
```bash
docker compose run --rm web sh -lc \
  "composer create-project codeigniter4/appstarter /tmp/ci4 && cp -rn /tmp/ci4/. /var/www/html/"
docker compose exec web composer install
```

**B) barlas-trailer'ı temel almak:**
barlas-trailer içeriğini (Dockerfile / docker-compose.yml / docker/ hariç)
bu klasöre kopyala, sonra:
```bash
docker compose exec web composer install
```

## Veritabanını açmak (hazır ama şimdilik kapalı)
DB + phpMyAdmin yalnızca `db` profili ile başlar:
```bash
docker compose --profile db up -d
```
- MariaDB: `localhost:3307` — db `qr_sistemi`, kullanıcı `qr_user`, şifre `qr_pass`
- phpMyAdmin: http://localhost:8091

Sonra CI4 `.env` içinde veritabanı satırlarını aç:
```
database.default.hostname = db
database.default.database = qr_sistemi
database.default.username = qr_user
database.default.password = qr_pass
database.default.DBDriver = MySQLi
database.default.port = 3306
```
> Önemli: hostname **db** (servis adı), `localhost` değil.

## Sık kullanılan komutlar
```bash
docker compose up -d --build      # başlat / yeniden kur
docker compose down               # durdur
docker compose logs -f web        # web loglarını izle
docker compose exec web bash      # konteyner içi kabuk
docker compose --profile db down  # db dahil hepsini durdur
```

## Notlar
- `.env` dosyası hem CI4 hem Docker Compose tarafından okunur; bu normaldir,
  Compose kullanmadığı satırları yok sayar.
- PHP eklentileri (mysqli / pdo_mysql dahil) imajda kurulu; veritabanını
  açtığında imajı yeniden derlemene gerek yok.
- Port çakışırsa `.env` içinde `WEB_PORT`, `DB_PORT`, `PMA_PORT` değiştir.
