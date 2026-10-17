> Superseded: the latest user instruction requires strict stage locks for every role and recoverable Trash. See [Workflow locks and Trash](WORKFLOW_LOCKS_AND_TRASH_2026-10-17.md). The bypass behavior below describes the earlier implementation and is no longer active.

# Superadmin RBAC audit — 17 October 2026

## Architecture and scope

The existing `admin` authentication guard, four staff roles, `CheckAdminRole` middleware and Laravel Gate remain the permission architecture. Superadmin passes role middleware and Gate checks. Other roles retain their existing controller authorization and workflow guards. No permission tables, migrations, calculations, report templates or database relationships were introduced or changed for this RBAC work.

Review and fixes cover order management and status/payment updates, receiving and warehouse stock, batch planning and production parameters, production runs and QA completion, dosimetry, testing, templates and template review, reports/documents, dispatch, warehouse and production master data, customer accounts and staff accounts. Existing route methods and CSRF protection remain active. Customer portal ownership checks remain separate from the staff guard.

## Explicit override

Superadmin sees a shared override panel. Select override, enter a reason of at least eight characters, and confirm the next action. Forms and supported same-origin admin AJAX requests carry `superadmin_override`, `confirmed` and `override_reason`. The backend verifies the authenticated role and sets a server-side request attribute; supplying a request flag alone cannot grant access. Ordinary actions do not require enabling the override.

Confirmed override permits corrections despite completed/received/dispatched states, including changing staff roles, resetting staff/customer passwords, revising template review, replacing this order's receiving allocation, correcting production timestamps/runs/QA, editing dosimetry after dispatch and removing referenced records. Superadmin can remove its own or the final staff account with explicit confirmation, ending its session. Inputs must still be structurally valid: numeric ranges, product ownership, foreign keys, units, stock reconciliation and machine time overlap validation continue to protect data integrity.

Non-Superadmin cannot forge override flags. Normal role and workflow restrictions remain. Dispatch still does not require manager approval; dose discrepancies remain informational as requested previously.

## Audit and transaction guarantees

Authenticated admin writes run inside a database transaction with retry for deadlocks. Audit records capture actor, route, method, override reason and available root/related record snapshots before and after the action. Root resolution covers bound models, legacy numeric IDs, templates, staff, customers and linked customer users, master data, batch IDs and relocation slots. Newly created Eloquent records are also captured. If a required snapshot fails, the database mutation and audit insert roll back together.

Password values, hashes, tokens and secrets are excluded from snapshots. Password resets are represented by a sensitive-field-change marker. Optional customer reset inputs clear whenever the modal opens or closes so values cannot carry into the next customer edit.

Destructive requests retain existing delete confirmation. Referenced-record removal additionally requires confirmed override and creates a snapshot before deleting. Existing nonnullable dependent records are removed recursively, nullable references are detached, and evidence files are retained. This is an audited permanent deletion, not a trash/restore feature; the panel and delete dialog explain the effect. Shared warehouse slot caches are recomputed and other orders' stock is retained. Repeated dispatch corrections edit dispatch metadata without releasing stock again. Re-receiving a dispatched order explicitly reopens its existing dispatch record.

Audit UI presents actor, time, route and reason prominently, with expandable formatted snapshots to avoid long JSON flooding the page. Existing historical actions cannot be reconstructed retroactively.

## Verification

- Full source verification: 72 Laravel tests / 934 assertions on SQLite; 15 JavaScript tests; PHP syntax, 97 compiled Blade views and production build passed.
- Full Laravel regression: 72 tests / 934 assertions also passed on isolated MySQL schema `beamcustom_codex_ux_20261016_regression`.
- Five concurrent MySQL scenarios passed in `beamcustom_codex_ux_20261016`: duplicate receiving, receiving different orders in a shared slot, duplicate dispatch, overlapping machine runs and simultaneous staff deletion.
- Dedicated RBAC tests cover authenticated role/Gate access, forged overrides, confirmation/reason validation, completed parameter corrections preserving status, finalized testing and dosimetry, final dosimeter row removal, QA override without fabricated run timestamps, template re-review snapshots, staff role/password and customer password changes, history-preserving deletion, stock isolation, CSRF enforcement and rollback when audit capture fails.
- Browser: signed in with synthetic Superadmin, changed completed batch speed 10 → 11, verified completed status retained, restored 11 → 10 with a second confirmed action and verified both audit snapshots/reasons. No browser console errors or warnings were recorded. Password and permanent deletion flows were tested through isolated automated fixtures, not through the browser.
- Screenshot: `docs/ux-evidence/superadmin-audit-20261017.png`.

All verification used isolated fixtures. The operational database was not migrated or reset. No deployment or commit was performed. Privileged overrides deliberately allow exceptional states; the operator must review the consequences and reconcile operational records when needed. Passing these scenarios is evidence for covered behavior, not a guarantee against every possible production failure or future regression.
