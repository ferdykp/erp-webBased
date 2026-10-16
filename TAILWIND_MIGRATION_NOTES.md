# BeamCustom — Tailwind CSS standardization

- Existing Laravel web pages already use Tailwind CSS v3 via `resources/css/app.css`, Vite and Blade utility classes. We preserved Tailwind v3 for compatibility.
- `resources/views/errors/minimal.blade.php` no longer has handwritten `<style>` declarations or inline CSS. It uses Tailwind utilities.
- Error pages load a **standalone compiled Tailwind stylesheet** at `public/css/error-tailwind.css` when available, so their appearance can survive a missing Vite manifest.
- `npm run build` now runs `build:error-css` before Vite. Run `npm ci && npm run build` to create both the standalone error CSS and the standard Vite manifest. Since dependencies could not be fetched here, compiled assets are not included in this ZIP.
- `resources/views/errors/layout.blade.php` is a compatibility wrapper for the standard error view.
- PDF templates continue to use Dompdf-supported print-specific CSS. **Tailwind cannot be used reliably for all Dompdf features**; forcing utilities into generated PDF HTML would risk breaking the production reports. Those styles are separate from browser-facing UI.
- External Font Awesome icon styles remain from the existing app, because replacing icon usage would be a broader redesign.
- No modifications to business workflow, DB schema, or auth.

## Verify on development machine

```bash
npm ci
npm run build
php artisan optimize:clear
php artisan view:cache
php artisan route:list
```

Visit `/admin/bookings` and simulate an error route in a staging environment. Confirm `/css/error-tailwind.css` and `/build/manifest.json` return HTTP 200.
