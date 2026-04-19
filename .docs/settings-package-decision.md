# Decision: Keep `ConfigService`, skip `spatie/laravel-settings`

**Date:** 2026-04-17
**Context:** Phase 3.2 of the comprehensive improvement plan proposed migrating `ConfigService` (reads `app_config.config_json` with `config/app-defaults.php` fallback) to `spatie/laravel-settings` with typed classes (`CompanySettings`, `LoanSettings`, `DvrSettings`, `QuotationSettings`).

## Why I'm pushing back

Spatie's `laravel-settings` is designed for **a fixed set of typed scalar/simple properties per class**. That's great for things like `SiteSettings { string $title; int $max_uploads; bool $allow_registration; }`.

The SHF config is the opposite shape:

- `documents_en` / `documents_gu` — 4 customer types × 10–14 bilingual doc strings each (≈90 entries)
- `dvrContactTypes` / `dvrPurposes` — structured lists with `{key, label_en, label_gu}` entries
- `banks` — variable-length list
- `iomCharges` — small nested struct
- `tenures` — list of ints

Migrating this shape to Spatie gets you one of two bad outcomes:

**Option A:** Every property is typed as `array`. You keep the nested JSON, you lose the typing benefit, and you pay all the migration cost for nothing.

**Option B:** Flatten each entry into its own typed property — `$proprietor_docs_en`, `$partnership_docs_en`, etc. You multiply surface area, the views break catastrophically, and any future customer-type addition requires a code change.

Either way, you stop being able to add a new customer type or document from the UI without editing PHP.

## What works well today

`ConfigService` is 127 lines and already:

- Loads from `app_config.config_json` with `config/app-defaults.php` fallback
- Does `array_replace_recursive` with the **correct** sequential-list replacement semantics (deletions from defaults are respected — see `replaceSequentialArrays`)
- Supports `get('dot.key')`, `updateSection()`, `updateMany()`, `save()`, `reset()`
- Is used by 9 files with a clean API
- Has no observed bugs

The only critique in the original plan was "typed classes give IDE autocomplete." For a config this dynamic, that benefit is illusory — you'd autocomplete into `array` properties and get nothing.

## What to do instead

1. **Keep `ConfigService`.** Don't install `spatie/laravel-settings`.
2. **Add a typed facade for the scalar-shaped sections only**, if future sections emerge that fit spatie's model. For example, if a new `PdfSettings` section with `{title, watermark, page_size}` shows up, it can be a DTO that reads from `ConfigService::get('pdf')` and returns a typed object. Keep the storage in `app_config`.
3. **Move on to higher-value work** — Phase 4 (real-time notifications) and Phase 5 (workflow tests) both have concrete user-visible benefits that the settings swap does not.

## Reversal conditions

Revisit if any of these become true:

- Config shape becomes scalar-heavy (rare nested arrays)
- IDE autocomplete is repeatedly slowing someone down on the settings UI
- A need emerges for versioned settings migrations over time (Spatie's forte)

None of these apply today.
