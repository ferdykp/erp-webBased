> Catatan historis. Hasil integrasi/pengujian terbaru dan keputusan dispatch tanpa approval ada di [laporan 16 Oktober](WORKFLOW_INTEGRATION_2026-10-16.md). Daftar keterbatasan dan aturan approval lama di bawah tidak menggantikan keputusan terbaru pengguna.

# BeamCustom — Release readiness and technical debt

## Scope of this package
This package contains a **limited reliability hardening pass**, not a production certification. Existing operational workflows and schema are retained. Changes: self-contained 403/404/419/429/500/503 error views without Vite dependency, QR scanner asset moved from global admin layout to its consumer dashboard, and a read-only deployment preflight script.

## Operational constraints
- No local `.env`, database, Composer vendor directory, node_modules, or production credentials are distributed.
- Current `composer.lock` pins PhpSpreadsheet 1.30.3, requiring PHP < 8.5. Use PHP 8.4 in deployment unless dependency compatibility is reviewed and updated.
- Production parameter recipes, product-release decisions, dosimetry calibration, and pallet capacity rules must be validated by the facility's authorized team before any automated release.
- `APP_DEBUG=false`, HTTPS, secure session cookies, protected logs, backups, scheduled jobs, and tested restore procedures are necessary before public launch.

## Deployment order (staging first)
1. Back up database, uploaded files, and existing APP_KEY. Keep `.env` server-side.
2. Select PHP 8.4. `composer install --no-dev --prefer-dist --optimize-autoloader`.
3. `npm ci && npm run build` (or deploy CI-built manifest/assets).
4. Review pending migrations with `php artisan migrate:status`; only after backup, run `php artisan migrate --force`.
5. `php artisan storage:link` if public uploads are served through that disk.
6. Run `bash scripts/preflight.sh`; review warnings.
7. Run `php artisan test`, functional browser tests, and `npm audit --omit=dev`/`npm audit` to assess vulnerable dependencies. Do not force-break major versions without regression tests.
8. Verify queue workers, scheduler, backups, monitoring, SMTP, file permissions, and reverse-proxy settings. Restart workers on deployment.
9. Run critical workflow tests before traffic cutover; define rollback procedure.

## Critical end-to-end checks not performed in this container
- Admin/customer login, password recovery, remember-me/logout across browsers and sessions.
- Authorization of every changing route for all roles and tenant isolation for customer data.
- Check-in quantity reconciliation, concurrent pallet assignment, and invalid movement scenarios.
- Batch planning, parameter approval, concurrent start/finish, QA review/hold, release conditions.
- Dosimetry data preservation, draft document versions, PDF rendering with actual records.
- Upload validation, mail delivery, CSRF/session expiry, error templates in maintenance mode.
- Performance profiling with real database volumes, N+1 query review and load testing.

## About partial page updates
Existing Blade pages are traditional server-rendered navigation. Replacing global navigation with PJAX/SPA behavior requires auditing scripts, form submissions, event listeners, browser history, and focus management. It was **not** enabled in this pass: doing so without that audit could break industrial workflows. Instead, continue implementing page-specific progressive enhancement and targeted AJAX updates behind tested feature boundaries.

## Known debt
- Existing controllers and Blade templates contain substantial domain logic and need incremental extraction to Form Requests, Policies, Services and reusable components.
- QR scanning currently uses an external CDN on Dashboard and needs an offline/privacy review before deployment to restricted factory networks.
- Existing Error views 401, 402 and 503 may use independent styling; 401/402 inherit the same fallback automatically if they extend `errors::minimal`.
- Complete QA-controlled dispatch gates, audit coverage of legacy actions, and verified process SOPs remain release blockers where applicable.
