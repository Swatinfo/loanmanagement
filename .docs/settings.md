# Settings & Config

Two settings surfaces:

1. **`/settings`** — quotation-side config (`SettingsController`). Company info, banks list, bank charges, tenures, documents by customer type, IOM charges, GST, services, DVR vocab.
2. **`/loan-settings`** — workflow / org config (`LoanSettingsController` + `WorkflowConfigController`). Locations, branches, banks/products, master stages, product stages + user assignments, role permissions.

Both are stored differently:

- `/settings` data goes to `app_config` table (single row, key `main`) and `bank_charges` table.
- `/loan-settings` data goes to structured tables: `locations`, `branches`, `banks`, `products`, `stages`, `product_stages`, `product_stage_users`, `bank_stage_configs`, `role_permission`.

## ConfigService — the glue

`app/Services/ConfigService.php` is the only code that should read/write `app_config.main`. Controllers call it; nothing else.

### Methods

| Method | Purpose |
|---|---|
| `load(): array` | Merged config (defaults + DB). Seeds from defaults on first call if table empty. |
| `save(array)` | Upsert `app_config.main` |
| `reset(): array` | Overwrite DB with `config('app-defaults')` |
| `get(string $key, $default)` | Dot-notation read (`iomCharges.fixedCharge`) |
| `updateSection(string $key, $value)` | Dot-notation write + save |
| `updateMany(array $updates)` | Batch dot-notation writes + single save |

### Merge behavior (important)

`mergeWithDefaults()` uses `array_replace_recursive($defaults, $loaded)` then `replaceSequentialArrays()` walks the merged tree and **replaces any sequential (indexed) array entirely** with the loaded DB value.

Why: so a user deleting an entry in a list (e.g., a bank, a tenure, a document) stays deleted — otherwise the default would re-appear after next merge.

- Assoc arrays (`iomCharges: {thresholdAmount, fixedCharge, percentageAbove}`) merge per key.
- Sequential arrays (`banks: ["HDFC Bank", ...]`, `tenures: [5, 10, 15, 20]`, `documents_en.proprietor: [...]`) replace entirely.

### Double-encode pitfall

`AppConfig.config_json` is cast to `array`. **Never `json_encode()` before saving** — the cast handles serialization. Double-encoding produces a JSON-escaped string stored as JSON.

## `/settings` — tabs

Controller: `SettingsController`. View: `resources/views/settings/index.blade.php`.

All endpoints require `auth`. Specific permission per tab — see `.claude/routes-reference.md`.

| Tab | POST route | Permission | Notes |
|---|---|---|---|
| Company | `/settings/company` | `edit_company_info` | companyName, Address, Phone, Email |
| Banks | `/settings/banks` | `edit_banks` | `banks[]` list; dedup + sort; replaces entire list |
| Tenures | `/settings/tenures` | `edit_tenures` | `tenures[]` ints 1–50; dedup + numeric sort |
| Documents | `/settings/documents` | `edit_documents` | `documents_en[]`, `documents_gu[]` by customer type (4 tabs) |
| IOM Charges | `/settings/charges` | `edit_charges` | `iomCharges.thresholdAmount`, `fixedCharge`, `percentageAbove` |
| Bank Charges | `/settings/bank-charges` | `edit_charges` | Truncates `bank_charges`, bulk inserts rows from form |
| Services | `/settings/services` | `edit_services` | `ourServices` multiline string |
| GST | `/settings/gst` | `edit_gst` | `gstPercent` numeric 0–100 |
| DVR Contact Types | `/settings/dvr-contact-types` | `view_settings` | `dvrContactTypes[]` of `{key, label_en, label_gu}` |
| DVR Purposes | `/settings/dvr-purposes` | `view_settings` | `dvrPurposes[]` of `{key, label_en, label_gu}` |
| Reset | `/settings/reset` | `view_settings` | Reset config, truncate bank_charges |

### Documents tab (important UX detail)

Customer types: `proprietor`, `partnership_llp`, `pvt_ltd`, `salaried`. Each has a separate document list.

**All tabs must render their inputs on page load**, not only the currently active tab — otherwise hidden tabs lose their data on save.

### Tag inputs

Auto-add pending typed values on form submit before posting. Users expect "Save" to capture text they typed but didn't hit Enter on.

## `/loan-settings` — workflow config

Controller: `LoanSettingsController` + delegation to `WorkflowConfigController`. View: `resources/views/loan-settings/index.blade.php`.

Permission to view: `view_loans`. Most write actions: `manage_workflow_config`.

Tabs (query param `?tab=`):

### Locations

`storeLocation` creates/edits `locations` (state or city with `parent_id`). `destroyLocation` blocks if the location has children or branches.

### Branches

`WorkflowConfigController::storeBranch` — name, code (unique), manager_id required, location_id (city) optional. `destroyBranch` blocks if users or active loans reference it.

### Banks / Products

`storeBank`, `destroyBank`, `storeProduct`, `destroyProduct`. Delete is blocked when dependent records exist. `saveProductLocations` syncs `location_product` pivot.

### Master Stages

`saveMasterStages` — one big form covering:
- Per-stage: `is_enabled`, `assigned_role`, `phase_roles[]` (for multi-phase stages)
- Per (bank, stage): `BankStageConfig` override

Only writes `bank_stage_configs` rows when overrides **differ** from master; otherwise removed. When a bank's role changes, downstream `product_stages` with stale overrides for that (bank, stage) are cleared so they re-resolve at runtime.

### Products → Stages

`productStages($product)` shows the detailed per-product config. `saveProductStages($product)` writes:

- `product_stages` row: `is_enabled`, `default_assignee_role`, `default_user_id`, `auto_skip`, `sub_actions_override` (per-phase roles/users)
- `product_stage_users`: stage-level default user, per-branch, per-location (city/state), per-phase

Multi-tier user resolution: branch → city → state → global default. See `user-assignment.md`.

### Role Permissions (Loans group only)

`saveTaskRolePermissions` — role × Loans-group permission matrix. Clears only Loans permissions per role, syncs selected. Does **not** touch non-Loans permissions (they're managed on `/permissions`).

Clears `PermissionService` caches after save.

## Defaults file: `config/app-defaults.php`

Source of truth for **what the config SHOULD be** when first seeded or reset. Top-level keys:

| Key | Shape | Notes |
|---|---|---|
| `companyName`, `companyAddress`, `companyPhone`, `companyEmail` | string | Simple scalars |
| `banks` | `string[]` | Sequential — replaced entirely |
| `iomCharges` | `{ thresholdAmount:int, fixedCharge:int, percentageAbove:float }` | Assoc — merged |
| `tenures` | `int[]` | Sequential |
| `documents_en.{type}` | `string[]` per customer type | Sequential, 4 customer types |
| `documents_gu.{type}` | same in Gujarati | |
| `dvrContactTypes` | `[{key, label_en, label_gu}]` | Sequential |
| `dvrPurposes` | same | |
| `gstPercent` | `int` | |
| `ourServices` | multiline string | |

## Seed vs runtime

- **Seeder** (`DefaultDataSeeder`) populates structural tables (locations, banks, products, stages, roles).
- **Config defaults** populate `app_config.main` on first `ConfigService::load()` call.
- Settings UIs only mutate `app_config.main` (and `bank_charges`), never structural tables.

## Adding a new config key

1. Add to `config/app-defaults.php` with default value.
2. Add a form field on the relevant `/settings` tab view.
3. Add a `SettingsController` action that validates + calls `ConfigService::updateSection()` or `updateMany()`.
4. Add the route in `routes/web.php` with the appropriate permission.
5. Add permission to `config/permissions.php` if it's a new one (seeded via migration).
6. Update this doc.
7. Controller validates as usual; don't re-validate in ConfigService.

## See also

- `.claude/services-reference.md` — `ConfigService` methods
- `.claude/database-schema.md` — `app_config`, `app_settings`, `bank_charges` tables
- `permissions.md` — `edit_*` permissions
- `workflow-developer.md` — master stages + product stages deeply
