# Soft Khata — Laravel API

REST API backend for the **Soft Khata** Flutter business ledger app.

**Stack:** Laravel 13 · Sanctum · MySQL (`business_ledger`)

## Setup (local / Laragon)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* in .env (MySQL)
php artisan migrate --seed
php artisan serve
```

API base: `http://127.0.0.1:8000/api/v1/`

**Demo login (after seed):** `demo@example.com` / `password`

## Flutter

Run the app against this API:

```bash
flutter run -t lib/main_laravel.dart
```

See repo docs: `API_DOCUMENTATION.md`, `FLUTTER_LARAVEL_INTEGRATION.md` (in parent project folder).

## Note

This repository previously contained the legacy **Node.js** API. It has been replaced by Laravel. Node source is no longer maintained here.
