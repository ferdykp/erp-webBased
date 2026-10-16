# BeamCustom — Production hardening status

This is an incremental hardening update, **not** a certification of production readiness.

## Changes implemented
- Receiving: align frontend and backend Manual Pallet Count for non-even distribution (e.g. 121 units across 4 pallets), while retaining database row locking and server-side uniqueness checks.
- Block the legacy administrative order-status endpoint from bypassing receiving/approval/production/quality and physical dispatch. It now permits only cancellation of untouched pending bookings; other transitions must use dedicated workflows.
- Disable direct arbitrary modification of production timestamps (previous update-duration endpoint), pending a separately authorized correction service with audit history.
- Normalize/strictly compare role middleware values.
- Generic receiving error response and structured server log, instead of exposing exception details to the browser.
- Node regression tests for receiving calculations and constraints, plus a source verification script.

## Important remaining work / release blockers
- Build comprehensive Laravel feature tests with a representative MySQL staging dataset (SQLite alone will not validate row-level locking).
- Audit all historic controllers for authorization, upload security, QA/measurement changes and audit completeness.
- Introduce an approved, audited workflow for correcting production timestamps and cancelling bookings after inventory movements; legacy shortcuts are blocked.
- Formal QA release, process validation by facility experts, calibration/traceability and approved operating procedures.
- Restore and rollback drill, monitoring, real backup verification and concurrency/load tests.
- Profile slow SQL queries before introducing indexes, caching or global partial navigation.

## Verification
Run `bash scripts/verify-source.sh` on the target PHP 8.4/macOS environment. After installing dependencies, review failed tests and run staging end-to-end tests. Do not deploy to a live industrial process before formal release acceptance.
