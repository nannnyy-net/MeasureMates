# Railway Production Deployment Prep (Laravel + Vite)

- [x] Add `railway.toml` with production build command (composer --no-dev, npm ci, npm run build).
- [x] Add deploy command to cache config/route/view, ensure `storage` symlink, and run migrations.
- [ ] Confirm Railway environment variables (at least APP_KEY, APP_ENV, APP_DEBUG, DB settings).
- [ ] Confirm correct runtime start command for your Railway stack (php artisan serve vs PHP-FPM template).
- [ ] Validate on Railway: app boots, Vite assets load, storage uploads accessible.

