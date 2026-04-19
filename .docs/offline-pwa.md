# Offline / PWA

The app is a Progressive Web App: installable on mobile + desktop, but **online-only for any data**. When offline, users see an offline shell with a disabled menu.

## Capabilities

- **Installable** — `manifest.json` exposes icons + start URL; browser offers install prompt on return visits.
- **Online gate** — all page and API requests require network. Offline = shell page only.
- **App-shell cache** — CSS, fonts, icons, and the offline page are cached so the shell boots instantly.

**Not supported (by design):**
- Offline data reads
- Offline writes / queued mutations
- Stale-while-revalidate for HTML pages (we never serve old data)

## Rationale

The older PWA cached pages and queued quotations in IndexedDB for later sync. This was removed because:

- Loan workflow is multi-user + stage-gated — stale data or out-of-order writes corrupt the timeline.
- Sync-conflict edge cases (who wins when a stage was transferred while the user was offline?) have no clean answer for a financial workflow.
- The product decision is explicit: **offline = no access**, surfaced unambiguously.

## Service worker — `public/sw.js`

Strategy per request class:

| Request | Strategy | Offline behavior |
|---|---|---|
| Static asset (`*.css`, `*.js`, fonts, images) | Cache-first | Serves from cache; static assets rarely change mid-session |
| Page navigation (`text/html`) | Network-first | Serves `offline.html` shell on failure |
| XHR/fetch (JSON/API) | Network-only | Returns `{ offline: true, error: ... }` with HTTP 503 |
| Non-GET (POST/PUT/DELETE) | Passthrough | Network error propagates so the UI shows it explicitly |

Cache version is `SHF_SW_VERSION` at the top of `sw.js`. Bump it to invalidate all caches.

## Offline shell — `public/offline.html`

Self-contained static HTML with:
- SHF branding
- Bilingual "You are offline / તમે ઑફલાઇન છો" message
- Retry button (checks `navigator.onLine`, reloads on true)
- Auto-reload when the browser reports `online` again

No external fonts, no external CSS — it works even if the full shell cache was purged.

## Client-side gate — `public/js/offline-manager.js`

`OfflineManager.setupNetworkListeners()`:
- Listens for `online` / `offline` window events.
- When offline: disables every `.shf-nav-link`, shows the `#offlineBanner`, disables every submit button.
- When online: restores everything.

The module also exports no-op shims (`cacheConfig`, `queueQuotation`, `syncAll`, etc.) so legacy call sites don't break while being migrated. Do not write new code against these shims — they all either resolve to `null` or reject with an explicit "offline queueing disabled" error.

## Manifest — `public/manifest.json`

```json
{
  "name": "SHF World",
  "short_name": "SHF World",
  "description": "Shreenathji Home Finance — Loan Management",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#f8f8f8",
  "theme_color": "#3a3536",
  "icons": [
    { "src": "/images/icon-192x192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },
    { "src": "/images/icon-512x512.png", "sizes": "512x512", "type": "image/png", "purpose": "any" },
    { "src": "/images/icon-192x192.png", "sizes": "192x192", "type": "image/png", "purpose": "maskable" },
    { "src": "/images/icon-512x512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
  ]
}
```

## Migration notes for call sites

Any view that previously did:

```js
if (!navigator.onLine) { OfflineManager.queueQuotation(payload); }
```

should now do:

```js
if (!navigator.onLine) { SHFLoans.showToast('You are offline. Please reconnect.', 'error'); return; }
```

The `queueQuotation` shim will reject with an error anyway — surface it to the user instead of silently queueing.

## Testing offline behavior

1. Chrome DevTools → Application → Service Workers → Offline checkbox.
2. Navigate to any route. Expect the offline shell.
3. Uncheck Offline. Click Retry. Full app loads.
4. Try submitting a form while offline: button is disabled; nothing happens.

## What was removed in this change

- IndexedDB stores: `cached_config`, `cached_charges`, `cached_notes`, `pending_quotations`, `pending_config_changes`.
- Sync flow through `POST /api/sync`.
- Dynamic HTML cache in the service worker (`shf-dynamic-*`).
- `OfflineSync` / `offlineSyncError` custom events.

Call sites still referencing these have been kept functional via the no-op shim in `offline-manager.js`. Clean-up of those call sites is deferred — they no longer do anything harmful.
