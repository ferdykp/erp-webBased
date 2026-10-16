# BeamCustom — Security & Workflow Patch (15 October 2026)

## Implemented
- Separate warehouse check-in from manager business approval. Check-in saves physical receipt and placement without setting `approved`.
- Manager approval requires a checked-in, placed booking in `pending` state.
- Refuse modifications of pallet placement for bookings processing/completed/cancelled.
- Irradiation start rechecks booking approval, warehouse receipt, machine parameters, and waiting state under database row locks.
- Batch completion records QA; failed visual, indicator, or damage checks cannot auto-complete a batch.
- Booking completion requires all existing batches `done` **and** sum of batch quantity equals order product quantity.
- Restrict sensitive staff profile edits and destructive booking/customer actions to superadmin.
- Limit Product Testing writes to technical roles.
- Validate staff roles and unique email; prevent deletion of own account / last superadmin.

## Important remaining work before production
- This is a targeted security/workflow patch, not an entire UI redesign or a complete system rewrite.
- Review legacy data: bookings marked `approved` by the old check-in route might need manager reconciliation. No destructive migration is included.
- QA currently uses visual/indicator/damage as conservative release gates. Business must define acceptable **dose range**, dosimetry verification, release authorization, and exceptions before production use.
- Run integration tests with realistic MySQL dataset; test cross-role authorization, concurrent warehouse placement, all product types, order cancellation, invoice/report exports, and idempotency.
- Review all status mutation paths (particularly manual updateStatus), pallet moves, report access, and the wider code for security issues.
- `vendor/`, `node_modules/`, `.env`, `.git`, runtime/log files and macOS metadata are excluded from the distributed ZIP. Install with `composer install` and `npm ci` on a trusted environment; never share real credentials. Rotate secrets if original `.env` was exposed.

## Deploy safely
1. Back up application and database.
2. Compare files changed: `routes/web.php`, `AdminBookingController.php`, `AdminProductionController.php`, `UserAdminController.php`.
3. Set correct local `.env` privately; use staging DB. Run `composer install`, `npm ci`, `npm run build`.
4. Run `php artisan optimize:clear`, `php artisan route:list`, and `php artisan test` after installing dependencies.
5. Manually test: customer booking -> check-in still pending -> manager approve -> batch parameter -> irradiation -> QA fail remains processing -> QA pass -> quantity full -> completed.
6. Audit and deploy only after stakeholder QA signoff.
