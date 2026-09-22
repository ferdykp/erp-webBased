# Responsive UI Revision — 2026-09-22

This revision is based on `beamCustom_auth-security-fixed_2026-09-21` and keeps the admin/customer guard separation from that build.

## Responsive strategy

The UI now uses a fluid, mobile-first Tailwind layout rather than a fixed 1440/1540px application canvas.

Breakpoints:
- base: small phones / ~320px
- `min-[420px]`: larger phones
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px
- `3xl`: 1920px (custom Tailwind breakpoint)
- `4xl`: 2560px (custom Tailwind breakpoint)

## Main changes

- Admin and Customer application shells are fluid at large resolutions.
- Removed fixed `max-w-[1540px]` and `max-w-[1440px]` content ceilings.
- Main page padding scales progressively from small phones to 4K displays.
- Sidebar width scales on desktop / ultrawide screens and remains an off-canvas drawer on mobile.
- Navbar height and horizontal spacing scale across breakpoints.
- Product Testing cards expand from 1 column to as many as 5 columns on very wide screens.
- Product Testing create form uses 4 columns on 2XL screens where appropriate.
- Product Testing Process Parameter uses a wider summary/form split and 3-column machine parameter grid on 2XL screens.
- Product Testing report and Report Center are no longer unnecessarily constrained to a narrow central width.
- Pallet grid can scale to 5/6/7 columns on 2XL/3XL/4XL screens.
- Dosimeter pages use the available content area and tablet cards scale up on large displays.
- Customer history is fluid instead of capped at 6XL.
- Customer profile/edit areas are wider while retaining readable limits.
- Small-screen two-column blocks in check-in, booking detail, calculation panels, production parameter, dosimeter, and customer forms now collapse to one column below ~420px.
- Landing-page containers scale further on 2XL/3XL displays.

## Tailwind

No handwritten application CSS was added. `resources/css/app.css` remains only:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

`tailwind.config.js` now defines `3xl` and `4xl` breakpoints. The included compiled CSS in `public/build` has also been regenerated with Tailwind CLI so the new responsive utilities are present.

## Validation

- PHP / Blade syntax scan: 180 files checked, 0 failures.
- JavaScript syntax scan: 6 files checked, 0 failures.
- Tailwind CSS regenerated successfully using Tailwind CLI.
- Full Vite build could not run in the Linux workspace because the original project `node_modules` was created on another platform and did not contain Rollup's Linux optional native package. Run `npm install` / `npm run build` on the target environment.
