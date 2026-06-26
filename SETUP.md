# QR Attendance — Setup & Run

A CodeIgniter 4 application for QR-based personnel check-in / check-out.
Runs in Docker: web on **:8090**, phpMyAdmin on **:8091**, MariaDB on **:3307**.

## First run

```bash
cd C:\Users\precocious\Desktop\qr-sistemi

# Build & start everything (web + db + phpMyAdmin)
docker compose up -d --build

# Install PHP dependencies (only if vendor/ is missing)
docker compose exec web composer install

# Create the database tables
docker compose exec web php spark migrate

# Load demo data (company, admin, employees, a location)
docker compose exec web php spark db:seed DemoSeeder
```

Open <http://localhost:8090> and sign in.

## Demo accounts (password is `password`)

| Username | Role     |
|----------|----------|
| admin    | admin    |
| ayse     | employee |
| mehmet   | employee |

## Try the flow

1. Sign in as **admin** → **Admin → Locations → Main Gate → Show QR** (this is the kiosk/door screen).
2. On a phone, scan it — or open <http://localhost:8090/q/main-gate> directly.
3. Sign in (employee) → tap **Check in** / **Check out**.
4. **Dashboard** and **My history** show your times; **Admin → Reports** shows daily / weekly / monthly / yearly summaries with late & overtime.

## How the QR modes work

- **Fixed**: one static URL `/q/{code}`. Simple; can be scanned from anywhere (accepted trade-off). Double check-in is blocked per person.
- **Dynamic**: the QR screen calls `/admin/locations/{id}/token` and rebuilds the code every ~25s; each token is single-use and expires in 45s (`/q/{code}?t=...`). Create a location with mode **dynamic** to use it.

## Key paths

```
app/Controllers      Auth, Scan, Attendance, Dashboard, History, Admin/*
app/Models           Setting, User, Shift, Location, AttendanceLog, QrToken
app/Services         AttendanceCalculator, ShiftResolver, DynamicQr
app/Database         Migrations/ + Seeds/DemoSeeder
app/Views            layout/app + pages (mobile-first)
public/assets/css    app.css
```

## Notes & next steps

- **QR rendering** currently uses a CDN JS library (`qrcode-generator`). For a fully offline on-prem install, bundle it locally or switch to server-side generation (`chillerlan/php-qrcode`).
- **CSRF** is disabled by default; all forms already include `csrf_field()`, so enable it in `app/Config/Filters.php` (`globals.before → csrf`) when ready.
- **Auth** is lightweight session-based; **CodeIgniter Shield** is the planned production auth.
- Work hours / shift settings live in **Admin → Settings** (per-company, fixed or shift mode).
