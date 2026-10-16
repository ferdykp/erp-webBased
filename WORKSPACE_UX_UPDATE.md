# BeamCustom – Order UX Update (15 October 2026)

## Changes
- Displays company **and primary customer contact** on Order Management list and preview.
- Completed orders expose an **Irradiation Process Report (PDF)** via `GET /admin/orders/{booking}/process-report.pdf`. PDF includes recorded order details, products, batch processing, receiving locations, and available dosimetry data. It is explicitly **not** a QA release certificate.
- The **Receive Goods** action goes directly to the Assisted Receiving workspace instead of opening the previous three-step modal. The check-in modal and its JavaScript are no longer rendered on Order Management.
- The receiving interface calculates pallet count from verified boxes per pallet, suggests all empty warehouse locations in one action, prioritizes a chosen line, and allows per-location review or modification.
- Displays booked quantity, actual counted quantity, difference, number of required/available locations, and estimated quantity for each location.
- Keeps receiving confirmation manual and maintains existing server-side locks and reservation checks.
- For safety, orders containing multiple different product records cannot use the single-product allocation assistant until the per-product inventory mapping is designed.

## Limitations and compatibility
- The report intentionally does not state irradiation compliance, calibration validity or QA release.
- The PDF route is restricted to internal authenticated roles; only completed regular orders are eligible.
- Older technical/pre-radiation modals in other pages have not been fully redesigned in this update. This release focuses on the booking list and receiving workflow.
- Physical pallet capacity rules still require facility-approved values; the interface does **not** assume capacity.
- Existing migrations and database structures are preserved. No new migration is required for this update.

## Local install
Use PHP 8.4 for composer, then `composer install`, `npm install`, `npm run build`, `php artisan optimize:clear` and `php artisan serve`. Verify with a staging database before production use.
