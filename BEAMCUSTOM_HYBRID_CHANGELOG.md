# BeamCustom — Hybrid Workspace update

## Implemented

- New internal `Orders` index with search and pagination: `/admin/orders`.
- New read-only, stage-based `Order Workspace`: `/admin/orders/{booking}?stage=summary|receiving|preparation|irradiation|quality|documents`.
- New role-aware `My Tasks` list based on current booking statuses: `/admin/tasks`.
- Sidebar entry points to the new screens; legacy actions remain available for compatibility.
- Admin and customer login forms offer **Remember this device**. This uses Laravel's standard persistent remember-me cookie, **not** multifactor-authentication trusted-device enrollment.
- Added `remember_token` migration for the `admins` table. The `users` migration already has a remember token.
- Removed account-enumeration behavior from staff login failure responses.
- Limited receiving/placement/pallet mutation routes to `superadmin|cargo_admin` rather than all operational staff.
- Restricted Product Testing create/edit form routes to `superadmin|production` (existing technical writes already required those roles).
- Made selected authentication and access-denied copy more professional.

## Important limitations — not yet implemented

This release is a **safe transitional release, not full completion** of the proposed end-to-end Hybrid Workspace.

- Legacy receiving, approval, irradiation, dosimeter, and reporting **write forms still run on their existing pages** and are linked from the workspace. This is intentional to avoid changing production business logic without data migration and SOP validation.
- `storePlacement()` currently sets `bookings.status` to `approved`; its existing behavior has **not** been changed. A future business-state migration must decouple receiving and managerial approval. Do not rely on the current `approved` status as independent proof of managerial approval.
- `finishBatch()` currently also persists QA and may mark an order completed; this is still legacy behavior. Proper separation of process finish vs QA release has not been implemented.
- The initial task list is an approximation derived from existing booking status; it does not represent a true task assignment system.
- This build does not implement password reset by email, multi-factor authentication, device-session management, a new QA verification gate, document versioning, or full audit history.
- Production behavior and end-to-end authorization have **not** been verified against a populated database. A runtime vendor dependency directory was intentionally excluded from this source package.

## Installation

1. Create a backup of the live database and files before deployment.
2. Review the diff against your original source and apply to staging first.
3. Configure a private `.env` on the server (`.env.example` is included); set application/session configuration and secure HTTPS.
4. Run `composer install`, `npm install`, `npm run build` as appropriate for the deployment environment.
5. Run `php artisan migrate --force` for the admin remember-token migration.
6. Run `php artisan optimize:clear` and inspect `php artisan route:list`.
7. Verify login/logout/remember-me with both guards and all four internal roles, including authorization for old routes.
8. Walk through receiving, approval, QA, and reports in staging before enabling for real customers.

## Security and source packaging

The archive excludes `.env`, `.git`, `vendor`, `node_modules`, app logs, and local runtime state. Original secrets are intentionally not distributed. Never commit real session credentials or API keys into source control.

### Remember this device

The checkbox means "stay signed in" using a persistent server-backed Laravel recaller token. It is **not** a security exemption for devices and should not be treated as device verification. For shared computers, leave it unchecked. Logging out explicitly terminates the persistent login for that guard in the current browser. Restrict session cookies to HTTPS in production.
