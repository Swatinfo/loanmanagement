# Plan: Fix Inline Font-Size Declarations Below 0.7rem

## Pre-read files completed
- `tasks/lessons.md`
- `.docs/frontend.md`
- `.docs/views.md`
- All 10 target blade files

## CSS Utility Classes Reference
- `shf-text-2xs` = 0.65rem (badges only)
- `shf-text-xs` = 0.75rem (labels, secondary text)
- `shf-text-sm` = 0.8rem (form labels, body small)

## Changes by File

### 1. `resources/views/loans/documents.blade.php`
- [ ] Line 71: `style="font-size:0.6rem;"` on Rejected badge -> add `class="shf-text-2xs"`, remove font-size from style
- [ ] Line 73: `style="font-size:0.6rem;"` on Waived badge -> same
- [ ] Line 75: `style="font-size:0.6rem;"` on Collected badge -> same
- [ ] Line 95: `style="font-size:0.65rem;"` on Download link -> add `class="shf-text-xs"`, remove font-size from style
- [ ] Line 98: `style="font-size:0.65rem;..."` on Delete File button -> add `shf-text-xs` class, remove font-size from style (keep other styles)
- [ ] Line 108: `style="font-size:0.65rem;..."` on Upload label -> add `shf-text-xs` class, remove font-size from style (keep other styles)
- [ ] Line 116: `style="background:...;font-size:0.65rem;"` on Waive button -> add `shf-text-xs`, remove font-size (keep background)
- [ ] Line 120: `style="background:...;font-size:0.65rem;"` on Remove button -> same

### 2. `resources/views/loans/show.blade.php`
- [ ] Line 59: `style="font-size: 0.65rem;"` on task_role_label badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 183: `style="font-size:0.6rem;"` on query count badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 403: `style="font-size:0.6rem;display:none;"` on remarks count badge -> add `shf-text-2xs`, keep display:none

### 3. `resources/views/dashboard.blade.php`
- [ ] Line 128: `style="font-size:0.6rem;"` on my_tasks badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 970 (JS): `font-size:0.65rem;` in JS string for bank tags -> use `shf-text-xs` class
- [ ] Line 973 (JS): `font-size:0.65rem;` in JS string for +N badge -> use `shf-text-xs` class

### 4. `resources/views/notifications/index.blade.php`
- [ ] Line 18: `style="font-size: 0.65rem;"` on type badge -> add `shf-text-2xs`, remove font-size

### 5. `resources/views/loans/transfers.blade.php`
- [ ] Line 21: `style="font-size: 0.65rem;"` on transfer type badge -> add `shf-text-2xs`, remove font-size

### 6. `resources/views/layouts/navigation.blade.php`
- [ ] Line 128: `style="font-size: 0.6rem; padding: 2px 5px;"` on notif badge -> add `shf-text-2xs`, keep padding

### 7. `resources/views/permissions/index.blade.php`
- [ ] Already has `<div class="table-responsive">` wrapping (line 29) - NO CHANGE NEEDED

### 8. `resources/views/quotations/show.blade.php`
- [ ] EMI table already has `<div class="table-responsive">` (line 217) - NO CHANGE NEEDED
- [ ] Charges table section doesn't have a table element, just card-style divs - NO CHANGE NEEDED

### 9. `resources/views/settings/workflow-product-stages.blade.php` (CRITICAL)
- [ ] Line 79: `style="font-size: 0.55rem;"` on Parallel badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 83: `style="font-size: 0.55rem;"` on sub-stages badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 87: `style="font-size:0.65rem;"` on roles text -> add `shf-text-xs` class, remove font-size
- [ ] Line 100: `style="font-size:0.55rem;"` on dash text -> add `shf-text-2xs`, remove font-size
- [ ] Line 186: `style="font-size:0.45rem;"` on location type badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 252: `style="font-size:0.45rem;"` on sub-action type badge -> add `shf-text-2xs`, remove font-size
- [ ] Line 255: `style="font-size:0.6rem;"` on roles text -> add `shf-text-xs` class, remove font-size
- [ ] Line 326: `style="font-size:0.45rem;"` on sub-action location type badge -> add `shf-text-2xs`, remove font-size

### 10. `resources/views/loans/disbursement.blade.php`
- [ ] No font-sizes below 0.7rem found - NO CHANGES NEEDED

## Execution Order
Process files 1-10 in sequence, making edits for each.
