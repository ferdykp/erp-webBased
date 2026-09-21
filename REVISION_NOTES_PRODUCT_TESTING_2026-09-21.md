# Beam Custom — Product Testing & UI/UX Revision

Date: 2026-09-21

## Product Testing flow

Product Testing is now a dedicated technical module and is no longer treated as a customer sterilization booking.

New flow:

1. **Test Data**
   - Requester / researcher: manual text, optional
   - Institution / company: manual text, optional
   - Contact: optional
   - Test / sample name: required
   - Quantity + unit: optional
   - Reference minimum/maximum dose: optional
   - Expected temperature: optional
   - Dimension: separate P × L × T fields, optional
   - Net/gross weight: optional
   - Notes: optional
2. **Process Parameter**
   - E-Beam Unit / Production Line: required
   - Beam Speed: required
   - Loading Mode: required (single side, double side, or custom)
   - Frequency: required
   - Scan Gear: required
   - Target Dose: optional (supports dose-finding tests)
   - Process notes: optional
3. **Report**
   - Test identity and requester data
   - Process parameter summary
   - Optional dosimeter rows
   - Dosimeter ID / position / absorbance
   - Automatic dose calculation using the existing dosimeter calibration equation
   - Minimum / average / maximum measured dose summary
   - Print / Save PDF through the browser print dialog

There is **no warehouse check-in, pallet planning, porter, or warehouse placement** in Product Testing.

## New database objects

- `product_tests`
- `product_test_dosimeters`

Run:

```bash
php artisan migrate
```

## Booking Slots removed

The Booking Slots module has been removed from:

- routes
- sidebar/navigation
- controller/model
- Blade views

A migration safely drops the obsolete `booking_slots` table if it exists.

## UI/UX redesign

### Admin

- New compact enterprise-style navigation
- Cleaner top bar
- Unified spacing, typography, forms, buttons, cards, status pills, tables, empty states, and metrics
- Product Testing now uses a clear 3-step workflow
- Responsive drawer navigation on smaller screens
- Desktop layout optimized for long operational pages

### Customer

- Customer portal shell redesigned to match the same visual system
- Cleaner navigation, account menu, responsive sidebar, and content spacing

### Responsive behavior

Layouts are designed from small mobile widths upward, including phone, tablet, laptop, desktop, and large desktop screens.

## Build / verification

Performed in the packaging environment:

- PHP syntax lint on application/routes/migrations: passed
- JavaScript syntax checks: passed
- Product Testing route definitions: checked
- Booking Slot references: removed
- Tailwind CSS compiled into `public/build` for the included production asset

Full Laravel runtime route boot cannot be executed in the packaging environment because its PHP build does not include `mbstring`. Run normal smoke tests in the target environment after dependencies are installed.

## Install / update

Preserve the existing production `.env` and `APP_KEY`.

```bash
composer install
npm install
php artisan migrate
php artisan optimize:clear
npm run build
```

If using local development:

```bash
npm run dev
php artisan serve
```
