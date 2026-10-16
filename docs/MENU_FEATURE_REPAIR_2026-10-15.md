> Catatan historis. Hasil integrasi/pengujian terbaru dan keputusan dispatch tanpa approval ada di [laporan 16 Oktober](WORKFLOW_INTEGRATION_2026-10-16.md). Daftar keterbatasan dan aturan approval lama di bawah tidak menggantikan keputusan terbaru pengguna.

# Menu and feature repair — 15 October 2026

## Fixed in this pass

- Parameter templates and password recovery views: separate adjacent Blade directives that caused PHP parse failures.
- Password reset: correct route argument order so token links open the intended portal; enable notifications on Admin so sending reset links works.
- Routes: exclude unsupported Porter/PIC `show` actions; route the legacy customer creation POST to the existing store handler.
- Customer booking: preserve integer trailing zeroes in product dimensions (`10x20x30`).
- Order business detail, invoice and legacy history: render missing contacts without a null dereference.
- Customer dashboard: move modal and JavaScript outside the replaced table partial, remove premature jQuery dependency, and use delegated pagination.
- Admin customer search: remove obsolete Alpine 2 initialization calls. Alpine 3 observes inserted markup.
- Shared asynchronous tables: abort superseded requests, ignore stale responses, preserve existing rows on failures, show a retry message and clear loading state.
- Booking detail modal: clear stale content, hide print until loaded, handle failed requests and abort obsolete requests.
- Mobile sidebar overlays: hide until Alpine initializes.
- Quantity migration: replace MySQL-only GREATEST with equivalent CASE expression to allow the configured SQLite test database to migrate.

## Verification

- Laravel: 8 tests, 289 assertions passed. Includes static admin pages, visible dashboard/settings navigation for four roles, password recovery notification and reset, customer pages, booking creation, ticket PDF response and selected order detail pages.
- JavaScript: 6 tests passed, covering receiving calculations and asynchronous table failures/stale responses.
- All 95 Blade templates compiled and passed PHP syntax checks.
- Application/controller/migration PHP syntax checks passed.
- Vite production build passed. Browserslist reports outdated compatibility metadata (non-fatal).
- Source whitespace checks passed.

Run `bash scripts/verify-source.sh` to repeat the automated checks.

## Scope limits

Tests use an isolated SQLite database, not live operational records. Notification delivery is mocked; SMTP delivery is not verified. PDF endpoint is tested for successful PDF output, not visual layout. No interactive browser/device or load/concurrency test was performed. Full production, dosimetry, QA release and physical dispatch workflows still need representative staging end-to-end validation. Existing workspace changes were preserved. This pass does not certify that every feature is bug-free.
