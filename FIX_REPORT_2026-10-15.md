# BeamCustom interaction repair

## Corrections in this patch
- Register Smart Receiving Alpine component in the Vite entry point before `Alpine.start()` rather than relying on a view-local function.
- Fix admin profile dropdown: outside click belongs to the dropdown container, not the toggle button; Escape closes it; add ARIA expansion state.
- Add global `[x-cloak]` CSS to prevent contradictory conditional alerts or an initially open dropdown before Alpine initializes.
- Maintain existing receiving endpoint, field names and existing server-side validation.

## Important caveats
- These are targeted fixes to Alpine/UI initialization; not a verified all-modules end-to-end repair.
- Native macOS `node_modules` cannot be reused on Linux. The existing archive included platform-specific dependencies; this distribution omits them. Reinstall dependencies and rebuild assets on your Mac.
- Build and browser tests were not completed in this environment; PHP CLI lacks mbstring and Node native Rollup dependency is missing for Linux.
- Physical pallet capacity, concurrent allocation, quantity corrections and QA decisions must still be validated server-side under facility SOP.

## Local macOS installation
1. Preserve your existing `.env` and database; back up the application first.
2. Use PHP 8.4 (`export PATH="/opt/homebrew/opt/php@8.4/bin:/opt/homebrew/opt/php@8.4/sbin:$PATH"`).
3. Run `composer install` and `npm ci`.
4. Run `npm run build` then `php artisan optimize:clear`.
5. Test receiving (`Quantity Matches Booking`, discrepancy, allocation/submit) and the account dropdown (toggle, click outside, Escape).
