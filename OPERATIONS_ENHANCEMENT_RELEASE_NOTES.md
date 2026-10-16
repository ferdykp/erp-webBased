# BeamCustom — Operations Enhancement (2026-10-15)

This update is based on `BeamCustom_Continuation_Dosimetry_Safe_2026-10-15.zip`.

## Five feature areas

1. **Quality review workspace**: `/admin/orders/{booking}/quality-review`. Shows existing batch QA and dosimeter details; Manager/Superadmin can record an explicit review, hold, or correction request. A `reviewed` decision requires recorded dosimeter IDs and absorbance, completed batches, and QA records. It is **not** product release, dose acceptance, or validation of the calibration formula.
2. **Versioned document drafts**: `/admin/orders/{booking}/document-drafts`. Snapshot copies of order/receiving/batches/QA/dosimetry into JSON drafts with revision number and creator. This is not an issued PDF or certificate. Existing Excel exports are unchanged.
3. **Production parameter presets**: `/admin/parameter-templates`. Production drafts presets, Manager approves/rejects, production can apply approved preset to unstarted batches after confirming suitability. Backend validates stored product dmin/dmax and writes an audit record. Template approval is administrative and does **not** mean the irradiation process is independently validated. Always check equipment SOP and process validation.
4. **Warehouse relocation journal**: `/admin/warehouse-movements`. Moves all recorded contents from one occupied pallet location to an empty one, in a transaction and with row locking; verifies pallet and placement quantities and synchronizes placement records. Requires Cargo/Superadmin confirmation of physical movement. It does not create equipment capacity limits or handle split-pallet transfers.
5. **Audit and approval records**: `/admin/operational-audit`. Tracks actions from the four new workflows with acting staff and metadata. **Legacy controller actions are not covered yet**; this is not a comprehensive compliance audit system.

## Deployment

* Backup database, keep local `.env` (not included in ZIP), and use PHP 8.4 for the current Composer lock.
* Review migration `2026_10_15_000010_create_operational_assistance_tables.php`, then run `php artisan migrate` in a non-production environment first.
* Run `composer install`, `npm install`, `npm run build` or launch `npm run dev`, and `php artisan optimize:clear`.
* Verify permissions for staff roles and run transactions against test data before introducing actual records.

## Important limitations

* No live end-to-end database / browser testing performed in this environment. PHP lint and ZIP integrity checks are performed.
* No validated calibration curves or equipment capacity are present in the source; no dose computation, QA product release, or automatic pallet capacity decision is invented.
* Existing booking status and legacy approvals are retained for backwards compatibility; independent QA-gated dispatch is **not implemented**.
* Document drafts are operational snapshots, not formally approved reports and not digitally signed.
* Audit records are application database records and are not tamper-evident / externally retained.
* Existing pallet relocation controller remains as legacy workflow; future work should unify its event logging and concurrency model before production rollout.
