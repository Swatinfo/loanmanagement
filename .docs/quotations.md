# Quotations

Comparison quotations across multiple banks, with EMI calculations, per-bank charges, and bilingual required-documents list. Output is a PDF; records are persisted for retrieval and later conversion into a loan.

## Routes

See `.claude/routes-reference.md`. Key:

- `GET /quotations/create` — form (permission: `create_quotation`)
- `POST /quotations/generate` — create + render + save (permission: `generate_pdf`)
- `GET /quotations/{id}` — show (any authenticated user; scoped by ownership + `view_all_quotations`)
- `GET /quotations/{id}/download?branded=1|0` — PDF download (permissions: `download_pdf` + branded/plain variant if configured)
- `GET /quotations/{id}/preview-html?branded=1|0` — HTML preview (super_admin only, debugging)
- `DELETE /quotations/{id}` — delete (permission: `delete_quotations`)
- `GET /quotations/{id}/convert`, `POST /quotations/{id}/convert` — conversion flow (permission: `convert_to_loan`)

## Controller

`QuotationController`, constructor-injects `ConfigService` + `QuotationService`.

## Data model

- **`quotations`** — header row with customer + loan amount + prepared-by info + `selected_tenures` (JSON array) + `pdf_filename`/`pdf_path` + optional `loan_id` back-link (set when converted).
- **`quotation_banks`** — one row per selected bank per quotation: ROI range, per-bank charges (PF %, admin, stamp/notary, registration fee, advocate, IOM charge, TC, two configurable extras).
- **`quotation_emi`** — one row per (bank × tenure): monthly EMI, total interest, total payment.
- **`quotation_documents`** — bilingual document list (EN + GU) per quotation.
- **`customers`** — not created on quotation creation (only on conversion to loan). Quotation customer data is stored inline on the `quotations` row.

See `.claude/database-schema.md` for full column list.

## Create flow — `QuotationService::generate(array $input, int $userId)`

All heavy lifting is in `app/Services/QuotationService.php`. Controller calls once with the form payload.

### Input validation

Throws `['error' => ...]` on fail:
- `customerName`, `customerType`, `loanAmount` required
- `loanAmount` ≤ 10^12 (1 lakh crore hard cap)
- `banks[]` required, non-empty array
- Per bank: `roiMin`, `roiMax` in (0, 30], `roiMin ≤ roiMax`

### Steps

1. Load config via `ConfigService::load()` (company info, tenures, gst, etc.)
2. Filter `$input['selectedTenures']` to keep only values that exist in config tenures
3. Build the **template data** — customer info, loan info, date, company info, per-bank breakdown:
   - Calculate EMI by tenure (standard reducing-balance formula, rounded to rupees)
   - Extract + validate charges per bank
4. Call `PdfGenerationService::generate($templateData)` — see `pdf-generation.md`
5. DB transaction:
   - Create `Quotation`
   - For each bank: create `QuotationBank`, then `QuotationEmi` per tenure
   - For each document pair: create `QuotationDocument`
6. Call `updateBankCharges($banks)` — upserts `bank_charges` by bank_name with the latest values for future pre-fill
7. Return `['success' => true, 'quotation' => $quotation]`

### Partial-success case

If PDF generation succeeds but DB save fails, the service returns `['success' => false, 'error' => ..., 'filename' => 'Loan_Proposal_*.pdf']`. The controller surfaces this so the user still has the PDF path.

## EMI calculation

Standard reducing-balance monthly EMI: `P × r × (1+r)^n / ((1+r)^n − 1)` where:

- `P` = loan amount
- `r` = monthly rate = `(roiMin + roiMax)/2 / 12 / 100` (midpoint of the rate range, converted to monthly)
- `n` = tenure in months (years × 12)

Calculated client-side on `/quotations/create` for preview; re-calculated server-side in `QuotationService::generate` for the canonical values stored in `quotation_emi`.

## IOM charge (config-driven)

IOM stamp-paper charge depends on loan amount:

- If `loanAmount <= iomCharges.thresholdAmount` → `iomCharges.fixedCharge`
- Else → `loanAmount × iomCharges.percentageAbove / 100`

Thresholds stored in `config/app-defaults.php` and editable via `/settings` (Charges tab).

## PDF variants

Two variants (gated by distinct permissions `download_pdf_branded` / `download_pdf_plain`):

- **Branded** — full SHF branding, logo, company info header/footer. Cached on disk in `storage/app/pdfs/` and path stored on the quotation for re-download.
- **Plain** — stripped branding (for sharing with banks directly). Always regenerated, not cached.

`QuotationController::download`:
- Plain: `regeneratePdf($q, false)` every time
- Branded: use cached path/filename if present, else regenerate and persist

`QuotationController::downloadByFilename` exists for legacy `/download-pdf?file=...` URLs.

## Currency formatting

- Display: Indian format `₹ X,XX,XXX` via `SHF.formatIndianNumber()` (JS) and `LoanDetail::formattedAmount` / `NumberToWordsService::formatCurrency` (PHP)
- Words: `SHF.bilingualAmountWords(num)` → `"Twelve Lakh Rupees / બાર લાખ રૂપિયા"`
- Form inputs: `.shf-amount-wrap` with visible `.shf-amount-input` (formatted) + hidden `.shf-amount-raw` (integer)

## Conversion to loan

Controller: `LoanConversionController`.

- `GET /quotations/{id}/convert` — shows the convert form (blocks if already converted)
- `POST /quotations/{id}/convert` — validation:
  - `bank_index` required int ≥0 (index into the quotation's banks)
  - `product_id`, `customer_phone`, `date_of_birth` (d/m/Y), `pan_number` (regex `[A-Z]{5}[0-9]{4}[A-Z]` uppercased), `assigned_advisor` — required
  - `customer_email` nullable email, `notes` nullable
- Calls `LoanConversionService::convertFromQuotation(Quotation, int $bankIndex, array $extra)`

See `loans.md` and `workflow-developer.md` for the conversion side-effects.

## UI surfaces

- `/quotations/create` — tabbed form (location/branch → customer → banks → loan details → documents)
- `/quotations/{id}` — detail page with per-bank comparison + PDF download buttons + convert button
- `/quotations/{id}/convert` — conversion form (pre-filled from quotation + user profile)

Quotations listing is on the **dashboard** — there's no standalone `/quotations` index. The dashboard has "Pending Quotations" and similar tabs (see `dashboard.md`).

## Soft delete / conversion gate

- `Quotation` uses `SoftDeletes` + `HasAuditColumns`.
- `destroy()`: blocks if `isConverted()` (quotation has `loan_id`).
- Linked `loan_details.quotation_id` is set to null on loan delete, so the quotation becomes convertible again.

## Offline generation

PWA caches quotation form state in IndexedDB. `public/js/pdf-renderer.js` can generate a client-side PDF via `window.print()` when the user is offline. On reconnect, `/api/sync` flushes pending quotations to the server. See `offline-pwa.md`.

## Surface checklist (before touching the quotation flow)

1. Read `pdf-generation.md` for template + fallback logic
2. Read `.claude/services-reference.md` for `QuotationService` and `PdfGenerationService` method signatures
3. Don't bypass `ConfigService` for config values
4. Don't calculate charges inline — reuse `QuotationService`'s computations; if you need new charge logic, add it to the service
