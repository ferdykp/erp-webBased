# Order Management unified workspace update

- `/admin/bookings` is the canonical single-page order list with status tabs, summary counts, server-side search, pagination, and an inline preview panel.
- Legacy `/admin/bookings/status/{status}` URLs redirect to the corresponding tab.
- `/admin/orders` redirects to `/admin/bookings`; full order detail is still available at `/admin/orders/{booking}`.
- The sidebar no longer contains the redundant Order Workspace link or order status submenus.
- Existing check-in modals and underlying warehouse handler are retained. Receive Goods is restricted to Cargo Admin and Superadmin.
- Current production, QA, and report flows remain unchanged; preview does not infer QA approval.
- Run `php artisan optimize:clear`, `php artisan route:list`, and test against local data before deployment.
