# BeamCustom Unified Operations — Implementation notes

## Included in this update
- Consolidated sidebar for Orders, Production, Product Testing, Warehouse, Documents, Customers, Analytics & Approvals and Settings.
- Production Workspace combines batch list, status views, search, machine selection, parameter editing, start irradiation and finish/QA forms in one split-view screen. Existing guarded Laravel endpoints are reused.
- Order Workspace Documents includes role-controlled direct Excel downloads for the existing Nuctech/JTS report formats.
- Documents Center resolves document type and order selection under a single `/admin/report?family=...&type=...` URL. Existing report URLs remain compatible.
- Warehouse navigation provides same-page access to pallet layout, inventory summary, position matrix and layout settings.
- Settings groups account, warehouse personnel, production units and team access into one navigation destination.
- Dosimetry/QA is discoverable from the relevant Order Workspace stage; existing dosimetry forms remain intact.
- Business approvals continue using the existing, role-gated approval controller and can be reached from My Tasks / Analytics. Product Testing workflow remains unchanged.

## Important technical boundaries
- This is a consolidated UX and integration update, not a new QA release engine, document issuance system, or complete receiving/approval state-machine rewrite.
- Existing `finishBatch` stores QA and marks qualifying batches done according to its existing rules; it does NOT implement an independent QA-authorized release gate. No automatic dispatch/closure was added.
- No destructive database schema changes are included.
- The old production and report URLs remain valid for bookmarks and compatibility.
- PHP syntax was checked in the build environment. Full Laravel application tests against project database and frontend browser testing remain required before production use.

## Local setup
Use PHP 8.4 for the locked `phpoffice/phpspreadsheet` dependency. Back up existing database and environment configuration; keep `.env` outside this distribution. Run composer install, npm install, php artisan optimize:clear and then start Laravel and Vite. Examine migration status before applying changes.
