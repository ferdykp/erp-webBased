# BeamCustom Revision Notes — 2026-09-21

Revised source for the E-Beam booking, warehouse, production and dosimeter workflow.

## Main changes
- Added dedicated Product Testing with a shorter test/trial form.
- Product Testing remains connected to the shared check-in → warehouse → production → dosimeter/QA pipeline.
- Added `booking_type`, `porter_name`, test objective/notes, and batch duration support.
- Added missing booking-slot schema and validation improvements.
- Reworked warehouse check-in, pallet allocation, duplicate-location checks, relocation and inventory release.
- Single Porter Team handling replaces the broken multi-porter validation path.
- Safer booking-code generation.
- Hardened customer ownership/profile flow and server-side booking calculations.
- Improved production batch validation, start/finish checks, QA and dosimeter handling.
- Separated regular-order reporting from Product Testing.
- Consolidated shared check-in UI/JS and improved responsive layouts/mobile navigation.
- Fixed multiple stale route/view/asset and model/schema inconsistencies found during static audit.

## New migrations
- `database/migrations/2026_09_21_000001_add_testing_and_checkin_fields.php`
- `database/migrations/2026_09_21_000002_create_booking_slots_table.php`

Back up the database, then run:

```bash
composer install
npm install
php artisan migrate
php artisan storage:link
npm run build
```

For an existing installation, keep the existing `.env` and **do not regenerate the production APP_KEY**.

## Packaging
This ZIP intentionally excludes `.env`, `vendor`, `node_modules`, `.git`, the local SQLite database, runtime logs/caches, `public/storage`, and stale `public/build` output. Reinstall dependencies and rebuild Vite assets on the target environment.

## Validation performed
- PHP syntax lint: passed for 177 application/config/migration/route/Blade PHP files.
- JavaScript syntax check: passed.
- Full Artisan runtime could not be executed in the packaging container because PHP `mbstring` is unavailable there.
- The uploaded `node_modules` was created for a different platform, so the packaging container could not run Vite until dependencies are freshly installed. This is why `node_modules` and old build output are not shipped.
