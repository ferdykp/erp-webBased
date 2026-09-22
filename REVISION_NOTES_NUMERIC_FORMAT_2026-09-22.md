# Numeric Data Type & Display Revision — 22 Sep 2026

## Goal
Numeric values are now displayed according to their real value instead of showing unnecessary trailing zeroes.

Examples:

- `1.000` → `1`
- `12.5000` → `12.5`
- `0.3500` → `0.35`
- `20.0000 Hz` → `20 Hz`
- `1.2345` remains `1.2345`

## Quantity
Count-based quantity fields are integers:

- regular booking product quantity
- production batch quantity
- product testing quantity (optional)
- pallet / placement quantities
- damaged quantity validation

A migration changes `product_tests.quantity` and `booking_batches.quantity` to unsigned integer columns. Existing non-null fractional values are normalized to the nearest integer with a minimum value of 1 before the column type is changed.

## Technical decimals
Technical values remain decimal-capable but are cast to floats for presentation, so insignificant trailing zeroes are not shown:

- min/max dose
- target dose
- beam speed
- frequency
- scan gear
- dimensions
- weights
- volume
- density
- absorbance
- measured dose

The database precision is preserved; only presentation is cleaned up.

## Shared formatting
Added:

- `App\\Support\\NumberFormatter`
- browser helper `window.formatSmartNumber(...)`

These provide consistent formatting across Admin, Customer Portal, Production, Dosimeter, Product Testing and reports without forcing fixed decimal digits.

## Apply

```bash
php artisan migrate
php artisan optimize:clear
```

The frontend source did not require a new Tailwind build for this numeric-format revision, but it is safe to run your normal production build:

```bash
npm install
npm run build
```
