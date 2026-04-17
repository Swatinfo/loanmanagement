# PDF Generation

Bank-comparison quotation PDFs with EMI tables, bilingual documents, and company branding. Implemented via `PdfGenerationService` with a three-tier fallback.

## Strategy

The service tries, in order:

1. If `config('app.pdf_use_microservice') === true` → **microservice only**
2. Else if Chrome is available on the host → **Chrome headless** (falls back to microservice on failure)
3. Else → **microservice only**

Chrome produces the best output (full CSS3 + web fonts + Gujarati shaping). Microservice is a Node.js Puppeteer endpoint maintained separately — meant to handle servers without Chrome.

## Config keys

| Key | Env var | Default | Purpose |
|---|---|---|---|
| `app.pdf_use_microservice` | `PDF_USE_MICROSERVICE` | `false` | Force microservice path |
| `app.chrome_path` | `CHROME_PATH` | auto-detect | Explicit Chrome binary path |
| `app.pdf_service_url` | `PDF_SERVICE_URL` | `http://127.0.0.1:3000/pdf` | POST endpoint |
| `app.pdf_service_key` | `PDF_SERVICE_KEY` | (none) | Sent as `X-API-Key` header if set |

### Chrome auto-detection

`getChromePath()` checks common paths per OS:

- Windows: `C:\Program Files\Google\Chrome\Application\chrome.exe`, x86 variant
- Linux / macOS: `/usr/bin/google-chrome`, `/usr/bin/chromium`, `/usr/bin/chromium-browser`, `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome`, etc.
- Fallback: bare `chrome` or `chromium` (relies on PATH)

`isChromeAvailable()` additionally verifies `exec()` isn't disabled and the binary exists (or is in PATH).

## Method entry point

`PdfGenerationService::generate(array $data): array`

### Returns

- Success: `['success' => true, 'filename' => 'Loan_Proposal_...pdf', 'path' => '/abs/path']`
- Failure: `['error' => 'human-readable']`

### Steps

1. `renderHtml($data)` — produces the full HTML page
2. Ensure `storage/app/temp/` and `storage/app/pdfs/` exist
3. Build filename: `Loan_Proposal_{sanitizedName}_{YYYY-MM-DD}_{His}.pdf`
4. Write HTML to temp file
5. Run selected strategy (Chrome or microservice)
6. Cleanup temp HTML (except when microservice was used directly and still needs it)
7. Return result

## Chrome headless invocation

Platform-aware shell command:

**Windows:**
```
"C:\...\chrome.exe" --headless --disable-gpu --no-sandbox ^
  --run-all-compositor-stages-before-draw ^
  --user-data-dir="{tempProfile}" ^
  --print-to-pdf="{output.pdf}" --no-pdf-header-footer "{input.html}"
```

**Linux / macOS:**
```
/path/to/chrome --headless --disable-gpu --no-sandbox \
  --run-all-compositor-stages-before-draw \
  --user-data-dir={tempProfile} \
  --print-to-pdf={output.pdf} --no-pdf-header-footer {input.html}
```

Flags explained:
- `--headless` — no GUI
- `--disable-gpu`, `--no-sandbox` — server stability
- `--run-all-compositor-stages-before-draw` — forces full render
- `--user-data-dir=...` — fresh profile per run (cleaned up after)
- `--no-pdf-header-footer` — no Chrome-injected date/URL header
- `--print-to-pdf=...` — output target

After execution:
- On Windows: 500 ms sleep if the output file isn't immediately present (file flush quirk)
- Delete the temp profile directory
- Return error if the output file is still missing

## Microservice invocation

cURL POST to `config('app.pdf_service_url')`:

- Content-Type: `application/json`
- Optional header `X-API-Key: {pdf_service_key}`
- Body: `{ "html": "..." }`
- 5s connect timeout, 60s total timeout

Response bytes are written to the target PDF file. HTTP ≠ 200 or empty body → error.

## Template

`renderHtml()` builds a single HTML document with inline CSS. Structure:

- `<head>`: `@font-face` for Jost, Archivo, and NotoSansGujarati (loaded from `public/fonts/`); page setup via `@page { size: A4; margin: 0 }`
- Cover: logo, company header, customer info, loan amount in Indian format + bilingual words
- Per-bank page(s): bank logo + name, ROI range, charges table, EMI comparison table per tenure
- Documents page: bilingual document list (EN + GU columns)
- Footer: prepared-by info + disclaimer

Bilingual labels provided by the `$labels` constructor array — English + Gujarati pairs for section titles, table headers, etc.

## Fonts

Local woff2 / ttf files in `public/fonts/`:

- Jost — display/headers (400, 500, 600, 700)
- Archivo — body (400, 500, 600)
- NotoSansGujarati — Gujarati text (regular, bold)

Referenced via absolute `file://` URIs inside the template so Chrome headless can load them without a running web server.

## Security considerations

- Filename is sanitized from `customerName` — no path separators, no control chars
- HTML is the project's own template — input strings (customer name, notes) are escaped via Blade `{{ }}` when in view; inside the service they're escaped via `htmlspecialchars()` before injection
- Chrome's `--user-data-dir` isolates profile state; directory deleted after run
- Microservice endpoint should be reachable only from the app host (localhost by default) or protected by `pdf_service_key`

## Caching

Branded PDFs:
- Filename + path stored on `quotations.pdf_filename` / `quotations.pdf_path` on first generation
- Re-downloads serve the cached file when present
- Regenerated only when file missing or on explicit re-generation path

Plain PDFs:
- Always regenerated; not cached

## Troubleshooting

- **"Chrome not found"** — set `CHROME_PATH` env or install Chrome; or set `PDF_USE_MICROSERVICE=true` and configure the microservice.
- **Empty / truncated PDF** — check `storage/logs/laravel.log`. The service logs both the Chrome command and exit code. For microservice failures, it logs HTTP code + cURL error.
- **Gujarati rendering broken** — verify `NotoSansGujarati` font files exist and are readable. Chrome headless will silently fall back to default font (which has no Gujarati glyphs).
- **Chrome hangs / PDF never created** — pages with unresolvable external resources. Prefer `file://` URIs or base64-embedded images in the template.

## See also

- `.claude/services-reference.md` — `PdfGenerationService` method list
- `quotations.md` — quotation flow that calls this service
- `offline-pwa.md` — client-side PDF render via `window.print()` for offline use
