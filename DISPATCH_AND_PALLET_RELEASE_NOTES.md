# BeamCustom — Pallet Lifecycle Update

## Features
- Receiving: quantity-based or manual physical pallet count. Manual mode distributes quantities across selected pallet count without creating unlimited physical capacity.
- Auto-assignment selects empty locations only. Insufficient locations are reported; no synthetic/overflow locations are created.
- Completed orders: Manager authorizes dispatch after reviewing the latest `quality_reviews` entry and required documents; Cargo confirms actual physical handover and releases all validated pallet contents in one transaction.
- `placement_details` retained and marked `dispatched` as history. Dispatch snapshot lists original locations, quantities, reference, staff and time.
- `order_dispatches` is a new table; run and review migration before use.

## Operational constraints
- `reviewed` is not equivalent to formal QA release; approving Manager must explicitly verify local SOP and required release documents.
- Full dispatch only. Partial shipments, mixed-booking pallets, and inconsistent inventory are blocked pending reconciliation.
- Current receiving still supports one product per order.
- No physical maximum can be assumed; manual pallet count is not an exemption from weight, size or storage rules.
- Existing historic deployments may contain inconsistent `placement_details`; reconcile before dispatch.

## Install / validate
- Backup DB and files; PHP 8.4; `composer install`; `npm ci`; `npm run build`; `php artisan migrate:status`; inspect and run `php artisan migrate` on staging; `php artisan route:list --name=admin.dispatch`; `php artisan optimize:clear`.
- Verify with real QA, Warehouse, Manager personnel in staging before rollout. No end-to-end DB/browser test performed here.
