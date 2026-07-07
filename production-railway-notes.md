# Production deployment notes (Railway) — Laravel + Vite

## Required environment variables
Railway should provide at least:
- `APP_KEY` (set by Railway; if not, run `php artisan key:generate` during build/deploy)
- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_CHANNEL=stderr` (or `stack`)
- Database variables used by `config/database.php`:
  - typically `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Migrations
This repo is configured to run migrations on deploy via `railway.toml`:
- `php artisan migrate --force`

Ensure Railway has DB access during deploy.

## Storage
This repo expects `php artisan storage:link --force`:
- Links `storage/app/public` -> `public/storage`

If you upload files, ensure Railway storage is persistent/writable.

## Frontend build (Vite)
Railway build command runs production build:
- `npm ci`
- `npm run build` (Vite)

Vite will output into `public/build`, and Laravel layouts should reference the built assets.

## Cache commands
Deploy command runs:
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

If you change routes/views frequently, clear caches as part of the deployment process.

