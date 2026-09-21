# Report Module Revision — 2026-09-21

## Restored report navigation
The admin sidebar again exposes report types as a dropdown instead of a single Reports link.

### Technical / Nuctech
1. Workshop Daily Work
2. Daily Processing Schedule
3. Processing & Delivery
4. Irradiation Process Log
5. Machine Operation Log

### Logistics / JTS
1. Unirradiated Material Card
2. Outbound Delivery Slip
3. Inbound Delivery Slip
4. Irradiated Material Card

## Role access
- superadmin: Nuctech + JTS
- manager: Nuctech + JTS
- production: Nuctech
- cargo_admin: JTS

## Report Center
`/admin/report` is now a real report catalog. It no longer exposes an invalid generic `all` Excel export.
Choose a report type first, then select an order to generate the Excel document.

## Files changed
- `resources/views/admin/layout/aside.blade.php`
- `resources/views/admin/report/index.blade.php`
- `app/Http/Controllers/ReportController.php`
- `routes/web.php`
