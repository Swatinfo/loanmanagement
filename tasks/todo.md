# Task Tracker

Current and completed tasks. Updated as work progresses.

---

## Completed: Quotation hold/cancel + daily reminders (2026-04-18)

- [x] Migration: `quotations` table — add `status` (active/on_hold/cancelled), hold + cancel columns, indexes on `status` and `hold_follow_up_date`.
- [x] Migration: `general_tasks` table — add nullable `quotation_id` FK.
- [x] Migration: seed `hold_quotation`, `cancel_quotation`, `resume_quotation` perms + grants.
- [x] `config/app-defaults.php`: `quotationHoldReasons` + `quotationCancelReasons` vocab.
- [x] `config/permissions.php`: append new slugs to Quotations group.
- [x] `Quotation` model: constants, casts, scopes, `heldBy`/`cancelledBy` relations, label accessors.
- [x] `GeneralTask` model: add `quotation_id` fillable + relation.
- [x] `QuotationController`: `hold()`, `cancel()`, `resume()` actions. Hold auto-creates DVR. Notifies creator.
- [x] Routes: 3 quotation routes + 2 settings routes.
- [x] `LoanConversionController`: block conversion of cancelled quotations.
- [x] Settings UI: Quotation Reasons tab with two sub-sections.
- [x] Dashboard quotation tab: status filter + status column + action buttons (desktop + mobile).
- [x] Quotation show page: status banner + modals + auto-open via `?action=`.
- [x] `SendDailyReminders` command + 08:00 / 20:00 schedule.
- [x] Tests: `QuotationHoldCancelTest` (5 cases, all passing).
- [x] Pint + full test suite (47 passing).
- [x] Docs: quotations, permissions, settings, dvr, general-tasks, database-schema, routes-reference, services-reference, lessons.

---

## In Progress: Comprehensive Improvement Plan (2026-04-17)

Full plan approved across iterations. Scope confirmations:
- DB: MariaDB already in place. No engine migration.
- Permission tables (roles/permissions/role_permission/role_user/user_permissions) exist. No `spatie/laravel-permission` swap.
- PWA: online-only gate. No offline data, no offline writes.
- Out of scope: PDF path, DataTables server-side, frontend framework swap, error tracking.

### Phase 0 — Baseline ✓

- [x] **0.1** `CONTRIBUTING.md` with branch protection + conventional commits
- [x] **0.2** MariaDB sanity audit — all JSON columns use native `json` (except `app_config.config_json` `longText` by design); collation `utf8mb4_unicode_ci`
- [x] **0.3** Queue driver confirmed (`database`), supervisor/systemd/NSSM docs in `.docs/ops.md`

### Phase 1 — Quick wins ✓

- [x] **1.1** Permission cache invalidation + `Gate::before` + `@can` integration + tier-matrix tests (10 tests)
- [x] **1.2** `FileUploadService` with MIME + mimetype whitelist (jpeg/png/webp/pdf), hashed filename, private storage (`storage/app/private/`), 7 unit tests
- [x] **1.3** XSS audit — 25 `{!! !!}` uses classified: 18 dead ternaries, 7 static-controlled HTML, 0 risky. PDF uses `$e = htmlspecialchars(...)` for all dynamic fields
- [x] **1.4** Impersonation audit — `TakeImpersonation`/`LeaveImpersonation` logged with `original_user_id`; `ActivityLog::log()` captures `impersonator_id` in properties for every action during impersonation (4 tests)

### Phase 2 — Code quality ✓

- [x] **2.1** `app/Validation/LoanValidationRules` + `DvrValidationRules` extracted; `LoanController` and `DailyVisitReportController` updated. Quotation has no inline validation to extract. Stage-transition rules are small per-method — kept inline.
- [x] **2.2** Service audit written to `.docs/service-audit.md`. `LoanStageService` flagged as god service (27 methods, 6 responsibilities); split deferred until Phase 5.2 tests exist. All other services cohesive.
- [x] **2.3** Found `LoanDetail::getCurrentOwnerAttribute` doing `User::find()` per row — now reuses `advisor` relation. Added `advisor` to dashboard eager load. Enabled `Model::preventLazyLoading()` in non-prod.

### Phase 3 — Library replacements

- [x] **3.1** `spatie/laravel-activitylog` installed. `activity_log` table created with custom `ip_address`/`user_agent` cols. Backfill migration copies legacy `activity_logs` rows (kept in place for historical read). `App\Models\ActivityLog` extends Spatie's `Activity`; legacy `::log()` helper preserved; `action`/`user_id`/`user` accessors kept for backward compat. `DashboardController::activityLog*` updated to new cols. 5 compat tests added.
- [x] **3.2** Declined after audit. Config shape (4 customer types × bilingual doc lists, nested iom/DVR structs) fits `spatie/laravel-settings` poorly. Rationale in `.docs/settings-package-decision.md`. `ConfigService` stays.

### Phase 4 — Notifications + Real-time + Web Push

- [x] **4.1** `NotificationBroadcast` event + `ShfNotification::created` hook + `routes/channels.php` with private-user auth. Broadcast defaults to `log` driver (no-op) until Reverb is flipped on. 3 tests.
- [~] **4.2/4.3** Reverb + Web Push setup written to `.docs/realtime-setup.md` (packages, env, supervisor, Echo frontend, VAPID key generation, SW push handler). Implementation deferred — requires hosting + VAPID key decisions.

### Phase 5 — Testing

- [x] **5.1** No-op — no pre-existing Breeze test files on disk to delete.
- [~] **5.2** Starter workflow test suite shipped (13 tests: state machine, illegal-transition rejection, query-blocks-completion, initialization, next-stage, role resolution). Full ~60-test matrix across role handoffs/multi-phase stages deferred — this is the base the split in Phase 2.2 would build on.

### Phase 6 — PWA online-only gate ✓

- [x] **6.1** `public/offline.html` shell with bilingual messaging + retry. `sw.js` rewritten to cache static assets + offline shell only; pages go network-first with offline shell fallback; XHR/fetch returns 503 JSON; non-GET passthrough. `offline-manager.js` neutered — legacy methods no-op/shim, `setupNetworkListeners` disables nav links + submit buttons while offline. `.docs/offline-pwa.md` fully rewritten with rationale, strategy table, testing recipe, and migration notes for call sites.

---

## Recently Completed

- [x] Complete Documentation Regeneration from codebase scan (2026-04-15)
- [x] Turnaround Time Report + Loan Duration (2026-04-15)
- [x] SHF Operational Manual v3 → v4 Update (2026-04-15)
- [x] Bank-Wise Dynamic Stage Role Configuration (2026-04-16)
- [x] Docket Login + OTC Clearance + Stage Tooling (2026-04-14)
- [x] DVR (Daily Visit Report) Module (2026-04-14)
- [x] Workflow Stage Flow Changes (2026-04-14)

---

## Completed

(historical tasks archived to .ignore/tasks/todo.md)
