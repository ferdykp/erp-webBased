# BeamCustom continuation — Dosimetry data preservation

- `DosimeterController::storeQuantity` now resizes tablet rows without deleting/recreating recorded measurements.
- Increasing the count adds missing rows only; lowering it removes only empty trailing rows. A reduction touching recorded dosimeter IDs, absorbance, calculated dose, or image evidence is rejected.
- Tablet quantity updates and row mutations run inside one DB transaction with a record lock.
- The page reloads from server state after changing quantity so existing values/evidence remain visible; validation errors are surfaced to the operator.

## Still pending
This is a **focused safety continuation**, not a completed implementation of all Smart Operations features. Calibrated dosimetry, QA release and independent review, signed documents, product templates, pallet capacities and workflow event auditing still require controlled domain changes and site-specific SOP approval.

## Verification
No database migrations added. PHP syntax lint and archive checks can be done in the working environment; full browser/database tests must run against a staging database.
