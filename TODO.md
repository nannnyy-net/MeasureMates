# TODO - Deployment & env/vite/storage fixes

- [x] Fix `.env.example` for deployment safety (secure defaults, debug off, cookie alignment).

- [x] Update `package.json` with deployment helper scripts (preview/build aliases).

- [x] Update `vite.config.js` to support subdirectory deployments via `base`.
- [x] Update `composer.json` scripts to run deployment-friendly steps (storage:link, optimize:clear) via a `deploy`/helper script.

- [x] Add/adjust deployment configuration (GitHub Actions workflow) if none exists.

- [x] Verify build + runtime wiring: run `npm run build` and ensure `php artisan storage:link` works.


