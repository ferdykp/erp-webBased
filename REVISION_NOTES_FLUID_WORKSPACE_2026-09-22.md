# Fluid workspace responsive update — 2026-09-22

## Goal
Use large/ultrawide monitors more effectively while preserving tablet/mobile responsiveness.

## Changes
- Admin and customer workspace gutters are now fluid with Tailwind `clamp(...)` utilities and capped at 32px.
- Dashboard fills the available viewport height instead of leaving a large blank area below the content.
- Stats and workflow cards scale in height, spacing, typography and icon size on 2XL/3XL/4XL displays.
- QR Check-in and Recent Arrivals stretch to consume remaining dashboard height.
- Recent Arrivals now fetches up to 10 items and becomes vertically scrollable when necessary.
- QR scanner area grows with the viewport instead of staying at a small fixed content height.
- Customer portal uses the same fluid workspace gutter approach.
- CRUD forms for Porter/Warehouse PIC use wider readable widths on large screens.
- No manual stylesheet was added; changes use Tailwind utility/arbitrary classes only.
