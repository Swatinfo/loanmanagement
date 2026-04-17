# API

A small set of HTTP endpoints. Most are **AJAX endpoints inside the web app** (session auth), not a general-purpose external API. Public endpoints exist only for the PWA offline shell.

Complete route list: `.claude/routes-reference.md`.

## Authentication model

- **Web AJAX**: session-based. `<meta name="csrf-token">` in `layouts/app.blade.php`. Send `X-CSRF-TOKEN` header or include `_token` in form data.
- **Public JSON**: `GET /api/config/public` and `GET /api/notes` — no auth. Meant for the PWA to bootstrap offline.
- **No Sanctum / no token-based auth** — there is no external API consumer yet.

## Public endpoints (no auth)

### `GET /api/config/public`

Controller: `Api\ConfigApiController@public`.

Returns the full `ConfigService::load()` result plus all `BankCharge` rows. Used by the PWA to cache configuration for offline quotation generation.

Response shape (abridged):

```json
{
  "companyName": "Shreenathji Home Finance",
  "companyAddress": "...",
  "companyPhone": "+91 99747 89089",
  "companyEmail": "info@shf.com",
  "banks": ["HDFC Bank", "ICICI Bank", ...],
  "tenures": [5, 10, 15, 20],
  "iomCharges": { "thresholdAmount": 10000000, "fixedCharge": 5500, "percentageAbove": 0.35 },
  "gstPercent": 18,
  "ourServices": "...",
  "documents_en": { "proprietor": [...], "partnership_llp": [...], ... },
  "documents_gu": { ... },
  "dvrContactTypes": [...],
  "dvrPurposes": [...],
  "bankCharges": [
    { "bank_name": "HDFC Bank", "pf": 0.50, "admin": 5000, "stamp_notary": 0, ... }
  ]
}
```

### `GET /api/notes`

Controller: `Api\NotesApiController@get`. Reads free-form notes from `app_settings` (key `additional_notes`). Returns `{ success, notes }`. Gracefully returns empty if DB is unavailable.

## Session-auth API (in `routes/web.php`)

### Quotations

### `POST /api/sync`

Route name `api.sync`. Controller: `Api\SyncApiController@sync`. PWA offline sync: batch-creates quotations that were queued client-side while offline.

Body: `{ quotations: [ {customerName, customerType, loanAmount, banks: [...], ...}, ... ] }`.

Per-item flow:
1. Auto-fill `preparedByName`, `preparedByMobile` from auth user if blank.
2. Call `QuotationService::generate()`.
3. Log `ActivityLog` with source `offline_sync` if saved.

Response: `{ success: bool, results: [{ index, success, filename, error }, ...] }`.

### `POST /api/notes`

Save free-form app-level notes. Validation: `notes` trimmed. Upserts into `app_settings`. Returns `{ success }`.

## Quotation PDF endpoints

See `pdf-generation.md` for details.

- `POST /quotations/generate` — create + render + save PDF (returns JSON)
- `GET /quotations/{id}/download?branded=0|1` — download PDF (plain = always regenerated, branded = cached)
- `GET /quotations/{id}/preview-html?branded=0|1` — HTML preview (super_admin only)
- `GET /download-pdf?file=...` — legacy download by filename

## Loan stage AJAX endpoints

Return JSON. Called from the loan stages page (`/loans/{id}/stages`). See `workflow-developer.md`.

- `POST /loans/{loan}/stages/{stageKey}/status` — transition stage
- `POST /loans/{loan}/stages/{stageKey}/assign` — assign user
- `POST /loans/{loan}/stages/{stageKey}/transfer` — transfer to another user
- `POST /loans/{loan}/stages/{stageKey}/reject` — reject loan at stage
- `POST /loans/{loan}/stages/{stageKey}/skip` — skip stage
- `POST /loans/{loan}/stages/{stageKey}/notes` — save stage form data
- `POST /loans/{loan}/stages/{stageKey}/query` — raise a query
- `POST /loans/queries/{query}/respond` — reply
- `POST /loans/queries/{query}/resolve` — close
- `GET  /loans/{loan}/stages/{stageKey}/eligible-users` — picker for assignment
- Multi-phase actions: `sanction-action`, `legal-action`, `docket-action`, `technical-valuation-action`, `rate-pf-action`, `esign-action`, `sanction-decision-action`

All require `auth` + stage-management permission. Responses generally: `{ success, current_stage, progress, ... }` or `{ error }`.

## Loan documents AJAX

- `POST /loans/{loan}/documents` — add custom doc
- `POST /loans/{loan}/documents/{document}/status` — update status (pending/received/rejected/waived)
- `POST /loans/{loan}/documents/{document}/upload` — multipart file upload (max 10 MB, mimes pdf/jpg/jpeg/png/webp/doc/docx/xls/xlsx)
- `DELETE /loans/{loan}/documents/{document}` — remove doc entirely
- `DELETE /loans/{loan}/documents/{document}/file` — remove uploaded file, keep doc record
- `GET  /loans/{loan}/documents/{document}/download` — streamed file

## Loan valuation AJAX

- `POST /loans/{loan}/valuation` — upsert valuation
- `GET /api/reverse-geocode?lat=&lng=` — OSM Nominatim reverse geocode
- `GET /api/search-location?q=...` — OSM Nominatim forward geocode (auto-adds "India" on first-try miss)

## DataTables AJAX

All list pages use DataTables server-side mode. Endpoints return the standard DataTables response shape `{ draw, recordsTotal, recordsFiltered, data }`:

- `GET /loans/data`
- `GET /users/data`
- `GET /dvr/data`
- `GET /general-tasks/data`
- `GET /activity-log/data`
- `GET /reports/turnaround/data`
- `GET /dashboard/quotation-data`, `/dashboard/task-data`, `/dashboard/loan-data`, `/dashboard/dvr-data`

Controllers apply scope filters, permissions gates, and build row HTML on the server.

## Search autocomplete endpoints

Min query length 2 chars. Return up to ~20 results.

- `GET /dvr/search-loans?q=` — by loan_number, application_number, customer_name
- `GET /dvr/search-quotations?q=` — by customer_name (respects view_all_quotations)
- `GET /dvr/search-contacts?q=` — from DVRs + customers + loans; deduplicated by (name, phone)
- `GET /general-tasks/search-loans?q=` — loan picker for task link

## Notifications

- `GET /api/notifications/count` — polled every 60s; returns `{ count }`
- `POST /notifications/{id}/read`
- `POST /notifications/read-all`

## Impersonation

- `GET /api/impersonate/users?search=` — JSON of impersonate-eligible users

## CSRF

All non-GET web routes (including `/api/sync`, `/api/notes`) require a CSRF token. Endpoints in `routes/api.php` (the two public ones) are outside the CSRF middleware group.

## Response conventions

- Success: HTTP 200 with JSON `{ success: true, ... }`
- Validation failure: HTTP 422 with Laravel's default `{ message, errors: {field: [msgs]} }`
- Permission failure: HTTP 403
- Server error: HTTP 500; error message in JSON when debug or explicit try/catch, otherwise generic

## See also

- `.claude/routes-reference.md` — complete route table
- `offline-pwa.md` — PWA offline behavior
- `quotations.md`, `pdf-generation.md` — quotation PDF flow
