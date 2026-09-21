# Authentication isolation fix — 2026-09-21

This revision fixes a critical cross-guard login issue where customer and admin authentication state could share the same browser session context and a stale intended URL could redirect a customer toward the admin area.

## Changes

- Customer routes now use `auth:customer` instead of the default `auth` / `web` guard.
- Added `customer.only` middleware to enforce `role=customer` on every authenticated customer route.
- Customer login authenticates only through `Auth::guard('customer')` and includes `role=customer` in the credentials query.
- Customer login always redirects to a fixed customer destination; `redirect()->intended()` is no longer used.
- `url.intended` is cleared during persona switching to prevent a stale `/admin/...` destination from being reused.
- Successful customer login logs out the `admin` and legacy `web` guards.
- Successful admin login logs out the `customer` and legacy `web` guards.
- Both login flows regenerate the session ID after authentication.
- Both logout flows invalidate the session and regenerate the CSRF token.
- Logout endpoints are now behind their matching authenticated guard, so one area cannot accidentally invoke the other area's logout handler.
- Customer and admin login POST routes are rate-limited to 5 attempts per minute.
- Customer controllers and Blade views now explicitly read `auth('customer')`.
- Admin Blade views now explicitly read `auth('admin')`.
- Password confirmation rules now explicitly use `current_password:customer` or `current_password:admin`.
- Root `/` redirect checks the customer and admin guards explicitly instead of inspecting the default guard.

## Expected isolation

- `/customer/*` can only be satisfied by the `customer` guard.
- `/admin/*` can only be satisfied by the `admin` guard.
- Logging into one persona signs out the other persona in that browser session.
- A customer login cannot inherit an admin `url.intended` destination.
