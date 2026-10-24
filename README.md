# Soft Khata — Laravel API

REST API backend for the **Soft Khata** Flutter business ledger app.

**This is the only Laravel copy in the monorepo.** Git remote: https://github.com/asadBangash/softkhata

**Stack:** Laravel 13 · Sanctum · MySQL (`business_ledger`)

## Setup (local / Laragon)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* in .env (MySQL)
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

API base: `http://127.0.0.1:8000/api/v1/`

**Demo login (after seed):** `demo@example.com` / `password`

## Production (shared hosting)

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

Document root must point to `public/`.

## Flutter

Run the app against this API:

```bash
flutter run -t lib/main_laravel.dart
```

See parent project docs: `API_DOCUMENTATION.md`, `FLUTTER_LARAVEL_INTEGRATION.md`.

## History

This repository previously contained the legacy **Node.js** API. It was replaced by Laravel in 2026.
