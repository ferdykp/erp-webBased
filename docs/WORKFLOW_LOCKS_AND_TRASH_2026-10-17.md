# Workflow locks and recoverable Trash — 17 October 2026

This change supersedes the workflow-bypass behavior described in `RBAC_SUPERADMIN_2026-10-17.md`. The latest requirement is that **every role, including Superadmin, follows the process sequence**. Role access still controls which modules/actions a staff member may use; it does not reopen a completed stage.

## Stage rules

| State | Available operations | Locked operations |
| --- | --- | --- |
| Pending, goods not received | Edit order, receive goods, cancel, move unused order to Trash | Production before receiving, handover |
| Goods received | Allocate remaining production batches; prepare pending/waiting batch settings | Receive again, edit the original order, arbitrary status changes |
| Batch waiting | Start production; prepare settings before the first run | Finish without production and actual runs |
| Batch processing | Record actual, nonoverlapping runs; correct runs before completion; record QA | Restart receiving or reorder completed stages |
| Batch done / order completed | Complete the next dosimetry/document/handover steps; view history and documents | Change batch parameters, restart/finish again, add or void completed production runs, edit order |
| Measurement set complete | View recorded results | Overwrite readings, resize the set, remove measured rows |
| Goods handed over / stock released | Read/download history and documents; move reconciled order to Trash | Repeat handover, receive again, production, dosimetry changes, new quality reviews/document snapshots |
| In Trash | Superadmin may restore, subject to parent and stage validation | Normal lists/actions, permanent deletion |

Payment remains a separate permitted step before handover; viewing invoices/reports does not modify a closed stage. Existing dispatch behavior is preserved: no manager approval is required, and dose discrepancies remain warnings. Physical handover, quantity and inventory reconciliation checks remain mandatory.

`WorkflowState` is shared by the main order UI and the backend transaction middleware. Controllers retain their original stage checks, and receiving also enforces the shared rule within its inventory transaction. Legacy `superadmin_override` submissions are rejected. The global override panel and script were removed. Sensitive staff role/password changes use a separate confirmation and reason; they cannot grant workflow bypass.

## Recoverable deletion

Laravel SoftDeletes retain rows for orders, customers and linked users, staff, product tests, warehouse slots, machines, porters, warehouse PICs and removed blank dosimeter rows. The `trash_entries` table records type/ID, label, actor, timestamp, a redacted snapshot and restoration metadata. Superadmin can access **Trash** from navigation. There is no purge or permanent-delete endpoint; model force deletion is rejected.

- Orders containing active stock or received goods without handover cannot be archived. The main UI hides that action until eligible. Direct requests are also rejected.
- Archiving a completed, handed-over order retains batches, QA, measurements, dispatch history, documents and other orders' stock. Child work queues exclude archived parent orders.
- Restore preserves the original stage. Restoring a completed order does not enable receiving, production or a second stock release.
- Customer contact/address rows remain intact. Linked customer login and profile restore together. Existing restrictions on referenced master/staff accounts and self/last-account deletion remain.
- Removed blank measurement rows reserve a separate archival sequence so active numbering can be reused safely. Restore appends a valid active index and reconciles the count, only while that measurement stage remains open. Measured evidence is locked.
- Historical codes/emails remain reserved by their original records, preventing identifier collisions. Code generators include archived records. Archived machines cannot be selected through forged request payloads.
- Existing evidence files remain available, including replaced product-test evidence. Deletion is transactional, and failure to create the required snapshot rolls back the archive operation.

Authentication, CSRF, role checks and operational audit logging remain enabled. Account password/token values are excluded from snapshots. Customer modal password, confirmation and reason fields reset when opening/closing the modal.

## Concurrency and query behavior

The main order list eagerly loads workflow relationships to avoid a new query for every eligibility check. Work queues use live-parent scopes. Backend state checks use current locking reads within transactions for dispatch, finalized dosimetry and quality review. Machine overlap detection now uses a locking current read after acquiring the machine lock; this prevents an older MySQL snapshot from missing a run committed while the request was waiting.

## Verification and database changes

- Full Laravel suite: 70 tests / 912 assertions passed on SQLite and isolated MySQL regression schema. The obsolete Superadmin-bypass expectations were replaced by strict-stage and Trash/restore tests; existing operational tests were updated where the new requirement intentionally changes deletion/finalization behavior.
- 14 JavaScript regression tests, PHP lint, validation of 97 compiled Blade views and the production build passed.
- Six MySQL concurrency checks passed: duplicate receiving, shared-slot receiving for different orders, duplicate dispatch, overlapping machine runs, simultaneous staff deletion and simultaneous final dosimetry writes.
- Browser fixture: completed order does not show Receive Goods, Process or Edit; only legitimate next steps and read actions remain. Trash Restore required a confirmation dialog and successfully restored a synthetic porter. Browser work used the separate SQLite fixture, not real customer records.
- Screenshots: `docs/ux-evidence/completed-workflow-locked-20261017.png` and `docs/ux-evidence/trash-restored-20261017.png`.

The additive migration `2026_10_17_000001_add_recoverable_trash.php` was applied to the verified local development MySQL database `db_beamcustom`, plus isolated test schemas/SQLite fixture. Only that migration was selected for local development; no reset, wipe, seed, operational deletion or other pending migration was run. Counts across all 11 existing affected tables were unchanged. No production server deployment or Git commit was performed.

Deployment to another environment requires applying the additive migration before serving this version, since active-record queries use `deleted_at`. Archive data has no automatic expiry; storage retention and backups should be planned with operational needs. These tests verify covered cases, not industrial certification, penetration-test coverage or every possible production failure.
