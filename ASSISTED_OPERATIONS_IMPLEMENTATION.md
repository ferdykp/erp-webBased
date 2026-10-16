# BeamCustom — Assisted Operations Delivery (2026-10-15)

## Implemented in this package
- Dedicated **Receive Goods** workspace linked from Order Workspace > Receiving. Reuses existing transactional placement endpoint; checks exact quantity, allocates counts based on a **manually confirmed** units-per-location parameter, proposes unoccupied pallet locations and requires operator confirmation.
- Dedicated **Assisted Batch Planning** linked from Order Workspace > Preparation. Computes a batch distribution from the remaining bookable quantity and a **manually approved** maximum units per batch. Submits to existing production endpoint; process parameters remain manual and required.
- Improved staff My Tasks distinction: incoming physical goods vs received orders awaiting approval.
- Prevents unintended deletion of recorded dosimeter measurements when changing tablet quantity. Existing clients performing deliberate replacement must provide `replace_existing=1`.
- Existing Unified Workspaces (Production Board, Documents, Warehouse, Product Testing) are preserved.

## Scope limits: not implemented as working features
- Automatic pallet *capacity validation*: database has no certified per-slot/weight/dimension limits. UI suggestions are not physical capacity approval.
- Receiving a quantity discrepancy: it is blocked pending resolution (no approved discrepancy workflow exists).
- Approved process recipe/templates and machine capacity rules: no controlled, versioned process specification.
- Calibration-aware dosimetry and formal independent QA release: current controller uses a fixed polynomial that must be independently validated; this release does not change its calibration or automatically certify product.
- Versioned, signature-controlled final documents, independent dispatch gate, audit trail, customer product presets and barcode scanning: additional schemas/business approvals needed.
- Formal SOP certification: no site-specific machine validation, equipment calibration or documented customer acceptance criteria were supplied.

## Safety and acceptance
- Staging deployment only. Perform independent validation of radiation-process parameters and dosimetry calibration, quality roles, document traceability, receiving discrepancies and pallet capacities before production.
- All existing order endpoints remain available for compatibility. No destructive database migrations were added.
- Syntax checks and ZIP integrity checks are not end-to-end integration tests.

## Manual checks
1. PHP 8.4 composer install; migrate status; Laravel routes.
2. Cargo: open a `pending` or `approved` order > Receiving > Assisted Receiving; verify proposed locations, limit, and constraints.
3. Verify duplicate/occupied slot reservation fails on server even if selected concurrently.
4. Manager: order in `pending` after receiving should show in approval tasks; cargo incoming tasks should not show it.
5. Production: approved & received order > Preparation > Assisted Batch Planning; enter validated max batch size and process parameters. Verify sum validation and authorization.
6. Dosimetry: verify existing results cannot be erased without explicit replace_existing confirmation.
7. Confirm legacy Product Testing, exports, auth, and warehouse continue to work.
