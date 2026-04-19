# Lessons Learned

Patterns and corrections captured during development. Review at session start.

---

## Branch-scoped visibility (2026-04-18)
- **All branch scopes must read the full `user_branches` list, not `default_branch_id`**: `$user->branches()->pluck('branches.id')` is the canonical pattern across `LoanDetail::scopeVisibleTo`, `Quotation::scopeVisibleTo`, `Customer::scopeVisibleTo`, and `DailyVisitReport::scopeVisibleTo`. This matches the multi-branch UX on the user edit page (`assigned_branches[]`). Do not use `default_branch_id` for scope — it's the create-time fallback only.
- **`view_all_*` permissions must be removed from branch-scoped roles**: giving `branch_manager`/`bdh` a `view_all_loans` or `view_all_quotations` short-circuits the scope before the branch check runs, making them see everything across branches. Admin keeps both bypasses.
- **Ensure `branch_id` on create, or scope rows drop**: `QuotationService::generate()` falls back to `User::find($userId)?->default_branch_id` because a null `branch_id` means a branch_manager can never see the record via their branch list (they'd have to be the creator). Same rule for any future branch-scoped module.
- **Customer scope uses `whereHas('loans')`**: `customers` has no `branch_id` column. Scope joins via `loans.branch_id` (option chosen over backfilling a column). Trade-off: a customer with zero loans is invisible to branch roles — acceptable because `Customer` rows are only created during loan conversion.
- **Admin DVR is participant, not supervisor**: the seeder grants `admin` `view_dvr + create_dvr + edit_dvr` (no `view_all_dvr`/`delete_dvr`) so admins who create DVRs see only their own. If you need a read-all admin, grant `view_all_dvr` as a user-level override, not by changing role defaults.

---

## Quotation hold/cancel design (2026-04-18)
- **Follow-up on hold → always DVR, never task**: DVR already has `quotation_id` FK + first-class `follow_up_date`/`follow_up_needed`/`is_follow_up_done` fields and a visit-chain model; tasks are for internal to-dos. Do not add a "create as task" option on quotation hold even though `general_tasks.quotation_id` exists.
- **Hold vs cancel lifecycle**: `status=cancelled` is terminal (no resume, no convert). `on_hold` → `active` via `/quotations/{id}/resume`. Conversion is blocked on cancelled quotations in `LoanConversionController`.
- **Reason vocab = config, not enum**: `quotationHoldReasons` / `quotationCancelReasons` stored as `[{key, label_en, label_gu}, …]` in `app_config.main`, editable at `/settings` → Quotation Reasons. Same pattern as `dvrContactTypes` / `dvrPurposes`. Controller validates `reason_key` using `in:` rule against the config's key list at request time.
- **Dashboard shortcut vs modal UX**: Dashboard Hold/Cancel buttons navigate to `/quotations/{id}?action=hold|cancel`; the show page auto-opens the corresponding modal. Keeps modal UI in one place and avoids duplicating big reason-lists across views. Resume is a simple SweetAlert confirm + POST (no form), OK on dashboard directly.
- **`.dvr-remove-btn` jQuery handler must check `listId` explicitly**: original DVR code had an `else` fallback. Adding another list (quotation hold reasons) broke it — misrouted deletes to DVR purposes. Always narrow `if/else if` when handlers are reused for new lists.
- **Reason lists use a `group` field for `<optgroup>` rendering**: `quotationHoldReasons` / `quotationCancelReasons` items each carry `{key, label_en, label_gu, group}`. The settings UI shows a group input per row (with `<datalist>` suggestions from existing groups) and renders the list grouped by `group`. The show-page modal renders options inside `<optgroup>` blocks. Missing/blank `group` → falls back to `Other`. If you add another grouped vocab later, reuse `renderReasonList()` — don't copy `renderDvrList()` which is flat.
- **Never let notification fan-out fail the primary request**: `ShfNotification::booted()` creates a row, then fans out to Reverb broadcast + Web Push. Both fan-outs are wrapped in `try/catch` + `Log::warning` because: (a) `NotificationBroadcast` uses `ShouldBroadcastNow` (synchronous HTTP to Reverb), and (b) Reverb is a separate process that may be down in dev or during restarts. If the broadcast throws, the DB row is already saved — letting the exception bubble would show a 500 for an operation that actually succeeded. Same rule applies to any future side-effect added to `booted()`.
- **`ConfigService::load()` self-heals top-level key drift**: the `app_config.main` row is seeded with the full `config/app-defaults.php` tree on first call. When new top-level keys are later added to defaults (e.g. `dvrContactTypes`, `quotationHoldReasons`), `load()` merges them in memory but also persists the merge back to DB via `save($merged)` if `array_diff_key($merged, $loaded)` is non-empty. Prevents silent DB drift where the merged read-path works but the DB row is missing keys. **Caveat**: the check is top-level only — if you add a *nested* assoc key (e.g. `iomCharges.newField`), self-heal won't catch it. For nested additions, add a one-off migration that calls `ConfigService::load()` → `save()`, or extend `load()` to walk nested assoc arrays.
- **`ConfigService` MUST bypass Laravel's config cache for `app-defaults.php`**: the defaults file is admin-editable reference data, not boot-time config. `ConfigService::defaults()` uses `require base_path('config/app-defaults.php')` instead of `config('app-defaults')`, so `php artisan config:cache` never freezes it. Never replace `$this->defaults()` back with `config('app-defaults')` — that reintroduces the stale-cache bug where a file edit on production wouldn't take effect until `config:clear`.

---

## Layout & Views
- **2026-02-27**: Migrated from Blade component slots (`<x-app-layout>`, `{{ $slot }}`) to `@extends`/`@section` pattern. Always use `@extends('layouts.app')` or `@extends('layouts.guest')` — never Blade component wrappers.
- **2026-02-27**: When updating view architecture, always update CLAUDE.md + MEMORY.md in the same change. Don't forget documentation sync.

## Frontend Stack
- **2026-02-27**: Frontend is Bootstrap 5.3 + jQuery 3.7 (local vendor files), NOT Tailwind/Alpine. All CSS classes use `shf-` prefix. Custom CSS in `public/css/shf.css`, JS in `public/js/shf-app.js`.

## Theme & CSS Variables
- **2026-04-14**: CSS variable `--dark` does NOT exist. Use `--primary-dark-solid` (#3a3536) for solid dark backgrounds, `--primary-dark` (semi-transparent), `--primary-dark-light` (lighter). Using `var(--dark)` causes transparent backgrounds making white text invisible.
- **2026-04-14**: Font classes: `font-display` = Jost (headings, modal titles, buttons), `font-body` = Archivo (body, forms). Always add `font-display` to modal titles and section headers.
- **2026-04-14**: Full variable palette: `--accent` (#f15a29), `--accent-warm` (#f47929), `--accent-light` (#f99d3e), `--accent-dim` (10% opacity), `--bg` (#f8f8f8), `--bg-alt` (#e6e7e8), `--text` (#1a1a1a), `--text-muted` (#6b7280), `--border` (#bcbec0), `--red` (#c0392b), `--green` (#27ae60).

## Modals & Dialogs
- **2026-04-14**: Modal header pattern: `background: var(--primary-dark-solid); color: #fff; border-radius: 12px 12px 0 0;` with `btn-close btn-close-white`. Never use plain Bootstrap modal header.
- **2026-04-14**: Modal footer buttons: Cancel = `btn-accent-outline btn-accent-sm`, Save/Submit = `btn-accent btn-accent-sm`. Never use `btn btn-secondary` or any Bootstrap default button classes in modals.
- **2026-04-14**: Modal titles must be bilingual (English / Gujarati) with `font-display` class. E.g., "Create New Task / નવું ટાસ્ક બનાવો", "Edit Task / ટાસ્ક સુધારો".
- **2026-04-14**: Modal centering is handled globally in `shf.css` via `.modal-dialog` flexbox. Do NOT add `modal-dialog-centered` class to individual modals — it's redundant.
- **2026-04-14**: Modal & SweetAlert backdrop uses branded orange-tinted gradient (`--primary-dark-solid` to `--accent` at 25%) defined in `shf.css`. Don't use plain gray/black backdrops.
- **2026-04-14**: Danger/delete buttons in modals: use `shf-btn-danger-alt` class, never inline `style="background:linear-gradient(135deg,#dc3545,#e85d6a);"`.

## SweetAlert (Swal)
- **2026-04-14**: Delete confirmation forms: add `shf-confirm-delete` class to the `<form>` — `shf-app.js` auto-handles the Swal.fire popup with `data-confirm-title` and `data-confirm-text` attributes.
- **2026-04-14**: Swal button color convention: orange `#f15a29` for confirmations, red `#dc2626` for destructive actions, gray `#6c757d` for cancel. Many existing calls are inconsistent (known debt).

## Buttons
- **2026-04-14**: Always use custom button classes: `btn-accent` / `btn-accent-outline` for actions, `btn-accent-sm` for size. Never Bootstrap defaults (`btn-primary`, `btn-secondary`, `btn-outline-secondary`, `btn-outline-light`, `btn-dark`).
- **2026-04-14**: On dark backgrounds (e.g., `shf-section-header`, `shf-page-header`), use `btn-accent-outline-white` — not `btn btn-outline-light`.
- **2026-04-14**: Danger buttons: use `shf-btn-danger-alt` class. Other semantic colors: `shf-btn-success` (green), `shf-btn-warning` (yellow), `shf-btn-gray` (gray).

## UI Debt Resolved (2026-04-14)
- All `var(--dark)` replaced with `var(--primary-dark-solid)` across all blade files
- `raiseQueryModal` standardized: dark header + bilingual title + accent buttons
- Inline danger gradient replaced with `shf-btn-danger-alt` class
- All Bootstrap button classes (`btn-outline-secondary`, `btn-outline-light`, `btn-outline-primary`, `btn-outline-danger`, `btn-outline-warning`, `btn-success`, `btn-dark`) replaced with custom `shf-*` / `btn-accent-*` classes across all blade views
- Redundant `modal-dialog-centered` removed (global CSS handles centering)
- Swal `confirmButtonColor` standardized: `#dc2626` (red) for destructive, `#f15a29` (orange) for confirmations. `cancelButtonColor: '#6c757d'` added where missing

## Responsive Design
- **2026-02-27**: Use `navbar-expand-lg` (992px) not `navbar-expand-sm` (576px) — sm is too small for anything with 5+ nav items. All nav visibility classes must match: `d-lg-*` not `d-sm-*`.
- **2026-02-27**: Filter forms should use `col-6 col-md-auto` pattern — fields pair on mobile, auto-width on desktop. Never `col-sm-auto` for 4+ filter fields.
- **2026-02-27**: Tables with 5+ columns need dual layout: desktop table (`d-none d-md-block`) + mobile card layout (`d-md-none`). Card layout is far better than horizontal scroll on phones.

## Tables & Date Inputs
- **2026-02-27**: Use Bootstrap's built-in table classes (`table`, `table-hover`, etc.) for all tables — not custom `shf-table` with dark gradient headers. Keep it clean, no shadow backgrounds on tables.
- **2026-02-27**: Use Bootstrap Datepicker (local vendor files, path: `vendor/datepicker/`) for all date inputs — not native `<input type="date">`.

## Workflow
- **2026-02-27**: ALWAYS write the plan to `tasks/todo.md` BEFORE starting implementation — not just show it to the user. The plan in todo.md IS the plan of record.
- **2026-02-27**: Update `tasks/todo.md` progress (check items) as EACH step completes — not all at once after the entire task is done. The user should be able to see live progress.

## Settings / Config
- **2026-03-12**: When Eloquent model has `'array'` cast on a JSON column, pass the raw array — don't manually `json_encode`. Double-encoding causes data to be stored as a JSON string inside a JSON string.
- **2026-03-12**: Settings forms with tag-based UI (banks, tenures, documents) must auto-add pending input values on form submit. Users expect typing a value and clicking "Save" to work — they shouldn't need to click "+ Add" first.
- **2026-03-12**: Settings documents form: all doc type tabs must render their inputs on page load, not just the active tab. Otherwise, only the active tab's data is included in the form submission and other types get silently lost.

## Workflow Config
- **2026-04-16**: Stage roles simplified to 3 categories: `task_owner`, `bank_employee`, `office_employee`. BM/BDH/LA are all "task_owner" (resolved from loan's assigned_advisor/created_by at runtime).
- **2026-04-16**: Workflow config is frozen at loan creation time (`loan_details.workflow_config` JSON). All phase transitions read from snapshot. Config changes only affect new loans.
- **2026-04-16**: Bank-wise overrides stored in `bank_stage_configs` table. Only rows where bank differs from master default. UI shows all banks always.
- **2026-04-16**: All multi-phase stages now have `sub_actions` in DB with `role` field per phase: legal_verification (3), technical_valuation (2), rate_pf (3), sanction (3), docket (2), esign (4).
- **2026-04-16**: `product_stage_users.phase_index` allows per-phase user assignment. null = stage-level, integer = phase-specific.
- **2026-04-18**: Normalized `rate_pf.sub_actions` from 2 entries (implicit phase 1) to 3 entries — one per runtime phase. Phase indices shifted: controller calls `getLoanPhaseRole($loan, 'rate_pf', $idx)` now pass `1` for phase 2 (was `0`) and `2` for phase 3 (was `1`). Migration `2026_04_18_100000_normalize_rate_pf_sub_actions` shifts `stages.sub_actions`, `bank_stage_configs.phase_roles`, `product_stage_users.phase_index`, and `loan_details.workflow_config.rate_pf.phases`. All multi-phase stages now share the same convention: one sub_actions entry per runtime phase.

## Documentation Sync
- **2026-04-07**: ALWAYS update reference docs (database-schema.md, routes-reference.md, services-reference.md, models.md, permissions.md) AS PART of each phase implementation — not deferred. Mark "Update reference docs" complete only after actually updating them.

## Testing
- **2026-02-27**: Auth and Profile tests (Breeze defaults) have pre-existing failures due to `EnsureUserIsActive` middleware and disabled registration. These are NOT caused by view changes — don't waste time debugging them during unrelated work.

## Feature Flags
- **2026-04-18**: `app.open_rate_pf_parallel` (env `OPEN_RATE_PF_PARALLEL`) controls whether `rate_pf` runs in parallel with the `parallel_processing` sub-stages and whether `sanction` waits for both. Helper: `LoanStageService::usesParallelRatePf()`. Always read via `config('app.*')`, never `env()` directly inside services (Laravel 12 rule). Flag does not invalidate `loan_details.workflow_config` snapshot; flips are safe at runtime after `config:clear`. Helpers `openRatePfInParallel` and `advanceToSanctionIfReady` are public; backfill for in-flight loans can be done via a tinker call like `app(LoanStageService::class)->openRatePfInParallel($loan)` or via a seed command.
