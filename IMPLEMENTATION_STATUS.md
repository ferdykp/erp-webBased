# BeamCustom — Implementation status (15 October 2026)

## Completed in source code

- Orders list and stage-based order detail workspace (summary, receiving, preparation, irradiation, dosimetry/QA, documents).
- Read-only cross-order Production Board for waiting, processing, and done batches; machine and booking filters, role-protected route.
- Role-aware My Tasks derived from existing booking statuses, with direct links to stage-specific workspaces.
- Customer and staff login using separate Laravel guards, logout, session regeneration, and remember-me cookie (persistent login, not MFA device trust).
- Separate password-recovery forms and brokers for staff/admin and customer, including reset link routes, expiration, throttling, and token-table migration.
- More restrictive access to receiving/pallet writes and Product Testing viewing; staff account edits remain superadmin-only.
- Professional English wording on the added workspace/recovery pages.

## Not yet complete — do not treat this as a final production release

- **Receiving and Manager Approval:** Existing `storePlacement()` can set `bookings.status=approved`. A redesign must separate these states in additive database migrations and carefully backfill old orders.
- **Processing and QA:** Existing `finishBatch()` saves QA and may complete a booking. It has NOT been separated into finish-physical-processing, verified QA release, and dispatch closure.
- **Workflow screens:** The stage workspace currently summarizes existing data and links to original write forms. Receiving, batch editing, QA entry, and report generation have NOT been rewritten as inline workflow forms.
- **Document lifecycle:** Versioned, immutable released certificates, signature/authorization and document gating are NOT implemented.
- **Audit events, QA verifier role, dispatch workflow, equipment interlocks and automated business-state tests** are NOT implemented.
- **Authentication:** Remember-me is not trusted-device enrollment, session management or MFA. Email reset needs a working SMTP driver, configured securely in `.env`. Password-reset links use default Laravel notifications.
- **Tests:** Syntax linted in the packaging environment. Database migrations, full Laravel route compilation, permission matrix and end-to-end tests require app dependencies, `.env` and a staging database; not verified here.

## Required migrations

- `2026_10_15_000001_add_admin_remember_token.php`
- `2026_10_15_000002_create_admin_password_reset_tokens.php`

Migration files are not executed against your database by the ZIP. Back up the database and inspect migration status before applying.

## Installation (staging)

Use PHP 8.4 with the current `composer.lock` (PhpSpreadsheet 1.30.3 does not accept PHP 8.5). Run Composer, npm install/build, check `.env`, and **back up your database** before migrating. Configure MAIL_MAILER=smtp and verified mail credentials to use email-based recovery. Always enable HTTPS and secure cookies in production.

Review `npm audit` results. No automatic `npm audit fix --force` was applied: major dependency changes could break the frontend and require separate validation.

## Changes not made

Product Testing business logic and existing production/database records remain unchanged. The zip excludes `.env`, `.git`, dependencies, secrets and runtime logs.
