# Fix All Font Sizes — CSS Variables + Inline Cleanup

## Context
Project has 175+ inline font-size declarations, many below readable threshold. Adding CSS custom properties for consistent sizing and replacing all inline font-sizes with CSS classes.

## CSS Variable System (added to :root in shf.css)
- `--shf-text-2xs: 0.65rem` (10.4px) — badges, tiny labels
- `--shf-text-xs: 0.75rem` (12px) — secondary labels, hints
- `--shf-text-sm: 0.8rem` (12.8px) — form labels, small text
- `--shf-text-base: 0.875rem` (14px) — body text
- `--shf-text-md: 0.95rem` (15.2px) — emphasized
- `--shf-text-lg: 1.1rem` (17.6px) — headings
- `--shf-text-xl: 1.25rem` (20px) — page titles

Utility classes: `shf-text-2xs` through `shf-text-xl`

## Files to Fix (by priority)

### Critical (0.45-0.55rem = unreadable)
1. `settings/workflow-product-stages.blade.php` — 8 fixes (0.45rem badges!)
2. `loan-settings/index.blade.php` — 12+ fixes (0.5-0.55rem labels)

### High (0.6-0.65rem = below threshold)
3. `loans/stages.blade.php` — 6+ fixes
4. `loans/documents.blade.php` — 8 fixes
5. `loans/show.blade.php` — 3 fixes
6. `dashboard.blade.php` — 3 fixes (incl JS)
7. `layouts/navigation.blade.php` — 1 fix

### Medium (0.65-0.7rem badges)
8. `notifications/index.blade.php` — 1 fix
9. `loans/transfers.blade.php` — 1 fix

### Table responsive wrappers
Already handled — permissions, quotation show tables already wrapped.

## Verification
- `php artisan view:clear`
- Visual check on all screen sizes
- `php artisan test --compact --filter="Loan|Disbursement|Document"`
