# Tailwind / Alpine cleanup — 21 Sep 2026

## Fixed

- Removed every `x-collapse` usage from the application.
- Removed the Alpine Collapse CDN/plugin dependency entirely.
- Dropdowns and expandable sections now use Alpine `x-show` + `x-transition`, so the previous console warning is gone.
- Removed stale `x-cloak` dependencies from the application views.

## Full Tailwind migration

- `resources/css/app.css` now contains Tailwind directives only:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- Removed the custom component layer and handwritten global CSS from `app.css`.
- Replaced custom classes such as `nav-link`, `panel-card`, `form-control`, `btn-primary`, `status-pill`, etc. with Tailwind utility classes directly in Blade.
- Removed inline `style="..."` attributes from the web views, including dynamic step indicators and progress displays.
- Admin shell, admin sidebar, navbar, dropdowns, report navigation, customer shell/sidebar/navbar, Product Testing pages, invoice, certificate, welcome screen, and error screen now use direct Tailwind utilities.
- Product Testing report print layout now uses Tailwind `print:` utilities instead of custom print CSS.
- The customer ticket PDF also uses Tailwind utility classes. Because it is rendered by DomPDF rather than a browser/Vite runtime, the already-generated Tailwind bundle is embedded into the PDF view dynamically from `public/build/manifest.json`; no handwritten CSS rules remain in that template.

## Responsive/UI improvements retained

- Mobile off-canvas navigation.
- Desktop sticky/full-height sidebar.
- Mobile-first Product Testing form and parameter flow.
- Responsive cards, tables, report layout, and action bars.
- Input text size remains mobile-safe to avoid Safari/iOS form zoom issues.

## Compiled assets

- Tailwind CSS was regenerated into `public/build/assets/app-Q9ni6MN-.css`.
- The existing application JS bundle remains valid because `resources/js/app.js` itself did not require changes.

## Validation

- 169 PHP / Blade files passed `php -l` syntax checking.
- `resources/js/app.js` passed `node --check`.
- No `x-collapse` remains in `resources/views` or `resources/js`.
- No legacy custom component class from the removed `app.css` component layer remains in application views.
- No inline `style="..."` attributes remain in `resources/views`.

## After extracting

```bash
npm install
npm run build
php artisan optimize:clear
```

The ZIP also keeps the Laravel runtime directories (`storage/framework/views`, `sessions`, cache directories, and `storage/logs`) through `.gitignore` placeholders so `php artisan optimize:clear` does not fail because of a missing compiled-view directory.
