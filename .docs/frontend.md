# Frontend (CSS + JS)

Bootstrap 5.3 + jQuery 3.7, local vendor files, no build step. Custom `shf-*` design system in `public/css/shf.css`.

## Vendor stack

Loaded globally in `resources/views/layouts/app.blade.php`:

- **CSS**: Bootstrap 5.3, Bootstrap Datepicker, SHF custom (`css/shf.css`), SweetAlert2
- **JS**: jQuery 3.7.1, Bootstrap bundle 5.3, Bootstrap Datepicker, SortableJS, SweetAlert2, SHF App, offline-manager, pdf-renderer

Vendor directories under `public/vendor/`: `bootstrap`, `datatables`, `datepicker`, `jquery`, `leaflet`, `sortablejs`, `sweetalert2`.

Fonts under `public/fonts/`:
- Jost (Regular, Medium, SemiBold, Bold) — display/headers, via `font-display` class
- Archivo (Regular, Medium, SemiBold) — body/UI, default `body` font
- NotoSansGujarati (Regular, Bold) — bilingual support

## Design tokens (`:root` in `shf.css`)

Colors:
- `--primary-dark` `#3a3536bf`, `--primary-dark-solid` `#3a3536`, `--primary-dark-light` `#3a353696`
- `--accent` `#f15a29`, `--accent-warm` `#f47929`, `--accent-light` `#f99d3e`, `--accent-dim` `rgba(241,90,41,0.10)`
- `--bg` `#f8f8f8`, `--bg-alt` `#e6e7e8`, `--text` `#1a1a1a`, `--text-muted` `#6b7280`, `--border` `#bcbec0`, `--white`, `--red` `#c0392b`, `--green` `#27ae60`

Sizing / shape:
- `--radius` `10px`, `--shadow`, `--shadow-lg`
- `--bs-border-width` `2px` (Bootstrap override)

Font scale (use utility classes of the same name):
- `--shf-text-2xs` `0.65rem` — badges, tiny labels
- `--shf-text-xs` `0.75rem` — secondary labels
- `--shf-text-sm` `0.8rem` — form labels
- `--shf-text-base` `0.875rem` — body default
- `--shf-text-md` `0.95rem` — slightly emphasized
- `--shf-text-lg` `1.1rem` — section headings
- `--shf-text-xl` `1.25rem` — page titles

## Component classes

### Sections
- `.shf-section` — white card container
- `.shf-section-header` — dark gradient header with number + title
- `.shf-section-number` — orange 24px circle with a step number
- `.shf-section-title`
- `.shf-section-body` — 20px padding
- `.shf-section-no-top-radius` — for tab-connected sections

### Buttons (orange pill by default)
- `.btn-accent`, `.btn-accent-sm`
- `.btn-accent-outline`, `.btn-accent-outline-white` (for dark backgrounds)
- Status variants: `.shf-btn-success` (green gradient), `.shf-btn-warning`, `.shf-btn-danger`, `.shf-btn-gray`, `.shf-btn-danger-alt`
- `.shf-btn-minimal` — unstyled link-like button
- `.shf-btn-icon` — icon-in-button positioning

### Cards & stat cards
- `.shf-card`
- `.shf-stat-card` — white card with left border + icon + value + label
- Color variants: `-blue`, `-accent`, `-green`, `-warning` (border + icon pair)

### Forms
- `.shf-form-label` — uppercase small label
- `.shf-input`, `.shf-input-sm`, `.shf-input-readonly`
- `.shf-amount-wrap` / `.shf-amount-input` / `.shf-amount-raw` — Indian currency trio
- `.shf-select-compact`
- `.shf-validation-error` — error message under invalid field

### Badges
- `.shf-badge` base
- Colors: `-orange`, `-blue`, `-gray`, `-green`, `-purple`, `-red`, `-username`
- **Stage-specific**: `shf-badge-stage-inquiry`, `-doc-selection`, `-doc-collection`, `-app-number`, `-bsm-osv`, `-legal`, `-valuation`, `-sanction-decision`, `-rate-pf`, `-sanction`, `-docket`, `-kfs`, `-esign`, `-disbursement`, `-otc`

### Stage status (left border 4px)
- `.shf-stage-pending`, `-in-progress`, `-completed`, `-rejected`, `-skipped`

### Stage cards
- `.shf-stage-card`, `.shf-stage-card--child` (left indent + accent border)
- `.shf-stage-header`, `.shf-stage-header-title`, `.shf-stage-body`, `.shf-stage-roles`

### Phase pills
- `.shf-phase-pill` base
- `--done`, `--active`, `--pending`
- `.shf-phase-chevron` — arrow between pills
- `.shf-transfer-hint` — muted caption

### Stage notes block
- `.shf-stage-notes`, `.shf-phase-step`, `.shf-phase-num`, `.shf-role-dot`, `.shf-note-line`, `.shf-transfer-line`

### Role colors (CSS custom props in the file)
Named: `.shf-role-loan-advisor` `#2563eb`, `.shf-role-bank-employee` `#d97706`, `.shf-role-branch-manager` `#059669`, `.shf-role-office-employee` `#7c3aed`, `.shf-role-task-owner` `#4b5563`. Background variants: `.shf-role-bg-*`.

### Tabs
- `.shf-tabs` (dark container), `.shf-tab`, `.shf-tab.active`, `.shf-tab-pane-hidden`, `.shf-tab-close`
- `.shf-nav-link` / `.shf-nav-active` — navbar tab styling

### Tags & chips
- `.shf-tag`, `.shf-tag-remove` — removable inline tag
- `.shf-chip` — toggleable chip (uses `input:checked`)

### Documents
- `.shf-doc-grid` (2-col on desktop)
- `.shf-doc-item`, `.shf-doc-received`, `.shf-doc-pending`, `.shf-doc-rejected`, `.shf-doc-highlight`, `.shf-doc-actions`
- Drag: `.doc-sortable-ghost`, `.doc-drag-handle`

### Icons
- Size classes: `.shf-icon-xs` (10px), `-2xs` (12), `-sm` (14), `-md` (16), `-lg` (20), `-xl` (32)
- `.shf-icon-inline` — vertical-align middle
- `.shf-header-icon` — for section headers

### Navigation
- `.shf-navbar-bg` — semi-transparent dark with backdrop blur
- `.shf-header-back` — hover-white "← back" link

### Stage pipeline (progress visualization)
- `.shf-pipeline`, `.shf-pipeline-border`, `.shf-pipeline-step`, `.shf-connector`
- `.shf-stage-dot`, `.shf-stage-dot--completed`, `--current`, `--in-progress`, `--pending`, `--skipped`, `--rejected`
- `.shf-stage-label`, `.shf-stage-label--current`, `--completed`
- Animations: `.shf-anim-dot`, `.shf-anim-conn-appear`

### Empty state
- `.shf-empty-state-icon`, `.shf-empty-icon-blue`, `-accent`, `-green`

### Toasts
- `.shf-toast-wrapper` — fixed bottom-right container
- `.shf-toast` — + `.success`, `.error`, `.warning` variants (left-border color)
- `.shf-toast-close`

### Collapsible/filter helpers
- `.shf-collapsible`, `.shf-filter-open`, `.shf-filter-count`, `.shf-filter-body-collapse`
- `.shf-collapse-arrow` (rotates on open), `.shf-collapse-hidden`
- `.shf-add-form-wrapper`, `.shf-add-form-toggle`, `.shf-add-form-body` — collapsible add-item form pattern

### Other layout
- `.shf-page-header` (dark gradient with title)
- `.shf-page-title` (1.25rem)
- `.shf-parallel-grid` — 2-col on desktop, 1-col mobile
- `.shf-form-actions` — sticky-bottom button bar on mobile (<1200px), inline on desktop; respects safe-area-inset-bottom
- `.shf-impersonation-banner` — fixed bottom amber banner
- `.shf-guest-card` — login card with orange top border
- `.shf-timeline-line` — left-side vertical rule for timelines
- `.shf-remark-item`, `.shf-notification-badge`, `.shf-upload-label`, `.shf-password-toggle`

### Toggles & checkboxes
- `.shf-checkbox` — accent-colored
- `.shf-toggle` — switch (18x36px)
- `.shf-password-toggle` — absolute positioned eye button

### Progress
- `.shf-progress-sm` (6px), `.shf-progress-md` (8px)

### Utility classes
- Text colors: `.shf-text-accent`, `.shf-text-white-muted`, `.shf-text-white-70`, `.shf-text-nowrap`, `.shf-text-gray`, `.shf-text-gray-light`, `.shf-text-dark-alt`, `.shf-text-error`, `.shf-text-success-alt`, `.text-prewrap`, `.text-inherit`
- Font: `.font-display` (Jost), `.font-body` (Archivo), `.shf-font-mono`
- Max widths: `.shf-max-w-sm` (28rem), `-md` (42), `-lg` (48), `-xl` (56), `-20` (20), `-36` (36)
- Cursor/border: `.shf-clickable`, `.shf-border-accent`, `.shf-border-top-light`, `.bg-accent-dim`

### Per-page, pagination, DataTables
- `.shf-per-page` — accent-bordered "X per page" selector
- `.shf-pagination` — native pagination styled with accent
- `.shf-dt-section`, `.shf-dt-bottom` — DataTables wrappers
- Dom layout convention: `{ dom: 'rt<"shf-dt-bottom"ip>' }`
- DataTables applies themed overrides automatically; native filter hidden (`.dataTables_filter { display: none }`)

### Dates
- Use the global Bootstrap Datepicker (loaded in layout). Don't add native `<input type="date">`.
- Class: `.shf-datepicker` (or variants: `-past`, `-custom`). Initialized by `shf-app.js`.

## JS API — `SHF.*` (public/js/shf-app.js)

### Validation

`SHF.validateForm($form, rules) → boolean` — client-side validation. Rule types:

- `required: true`
- `maxlength: N`, `minlength: N`, `min: N`, `max: N` (numeric; strips commas)
- `email: true`
- `numeric: true`
- `pattern: regex`, `patternMsg: string`
- `dateFormat: 'd/m/Y'`
- `label: string` — custom label for errors
- `custom: function(val, $field, $form) → errorMessage | null`

On failure: adds `.is-invalid`, inline error div `.shf-validation-error`, scrolls to first error (-120px), focuses. Clears automatically on next `input` / `change`.

`SHF.validateBeforeAjax($container, rules, url, data)` — validate first, then `$.post` if valid. Returns jqXHR or `false`.

### Indian number formatting

- `SHF.formatIndianNumber(num) → "12,34,567"`
- `SHF.numberToWordsEn(num)` — English words with Crore/Lakh/Thousand + "Rupees"
- `SHF.numberToWordsGu(num)` — Gujarati words + "રૂપિયા"
- `SHF.bilingualAmountWords(num)` — "EN / GU"
- `SHF.initAmountFields()` — auto-init `.shf-amount-input` fields: formats display, syncs hidden `.shf-amount-raw`, updates `[data-amount-words]`. Runs automatically on DOMContentLoaded.

### Auto behaviors (on `$(document).ready()`)

- All forms get `novalidate`
- `[data-auto-dismiss]` toasts fade after delay (default 5000ms)
- `.shf-toast-close` removes toast
- `.shf-password-toggle` toggles password visibility + eye icon
- `.shf-saved-msg` fades out after 2s
- `[data-bs-show-on-load="true"]` modals auto-open
- `.shf-confirm-delete` triggers SweetAlert2 confirm before submit
- `.shf-collapsible[data-target]` slideToggles target
- Mobile (<768px): filters auto-collapse; `window.shfCollapseFiltersOnMobile()` helper
- Desktop: filters auto-expand at load
- Radio in multi-select auto-checks the neighboring checkbox
- Textarea auto-expand fallback for browsers lacking `field-sizing: content`

## JS API — `SHFLoans.*` (public/js/shf-loans.js)

- `SHFLoans.initProductDropdown()` — filter `#productSelect` options by `data-bank-id` when `#bankSelect` changes
- `SHFLoans.showToast(message, type)` — `success` / `error` / `info` (default) bottom-right alert that auto-dismisses after 4s
- `SHFLoans.init()` — called on DOMContentLoaded

## Global inline scripts (layouts/app.blade.php)

- **Notification badge polling** — `updateNotifBadge()` every 60s, calls `/api/notifications/count`
- **Impersonate search modal** — SweetAlert2 user picker
- **Service Worker registration** — `navigator.serviceWorker.register('/sw.js')`
- **OfflineManager** — network listeners + auto-sync on reconnect if pending items exist
- **PWA install prompt** — captures `beforeinstallprompt`, shows `#installBanner` with 24h dismiss cooldown stored in localStorage

## Responsive pattern for tables

Wrap in `.shf-section .shf-dt-section`. Use `{ dom: 'rt<"shf-dt-bottom"ip>' }`. Build mobile cards in `drawCallback`:

- On `<768px`: `table.dataTable` is hidden via CSS; data rendered as `.shf-card` blocks
- Each cell becomes a key/value row with `data-label` pseudo-element

Alternative: use `.shf-table-mobile` on simpler tables — CSS transforms rows into cards automatically with `<td data-label="...">` markup.

## CSS rules of engagement

- **All custom classes start with `shf-`** to avoid Bootstrap collisions
- **No new files** — extend `public/css/shf.css`. Keep tokens in `:root`.
- **No build step** — don't add SCSS or PostCSS. Plain CSS with custom properties.
- **Responsive breakpoints**: 1200px (desktop chrome), 768px (tablet), 480px (small phones). Match existing usage.
- **Media queries** grouped at the end of the file (mobile first would require a rewrite — not planned)

## See also

- `views.md` — Blade view conventions
- `offline-pwa.md` — service worker + PWA install behavior
