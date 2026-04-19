// ============================================================
//  SERVICE WORKER — SHF Loan Management PWA (online-only gate)
//
//  Strategy: cache only the app shell (static assets + the offline
//  fallback page). App pages and API calls are always network-first.
//  If offline, any page/API request returns the offline shell or 503,
//  so stale data is never shown.
// ============================================================
var SHF_SW_VERSION = '20260418190650';
var STATIC_CACHE = 'shf-static-' + SHF_SW_VERSION;
var OFFLINE_URL = '/offline.html';

var STATIC_ASSETS = [
    OFFLINE_URL,
    '/css/shf.css',
    '/images/logo3.png',
    '/images/background.png',
    '/images/icon-192x192.png',
    '/images/icon-512x512.png',
    '/images/Shree-4.png',
    '/fonts/NotoSansGujarati-Regular.ttf',
    '/fonts/NotoSansGujarati-Bold.ttf',
    '/manifest.json',
];

// Install: pre-cache the app shell.
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function (cache) {
            return Promise.all(
                STATIC_ASSETS.map(function (url) {
                    return cache.add(url).catch(function (err) {
                        console.warn('SW: failed to pre-cache', url, err);
                    });
                })
            );
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// Activate: drop any non-current caches (including old dynamic caches from
// previous offline-first versions — we explicitly do NOT keep dynamic HTML).
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) { return key !== STATIC_CACHE; })
                    .map(function (key) { return caches.delete(key); })
            );
        }).then(function () { return self.clients.claim(); })
    );
});

// Fetch routing.
self.addEventListener('fetch', function (event) {
    var req = event.request;
    var url = new URL(req.url);

    // Only intercept same-origin requests. Let everything else (CDN, external
    // analytics, etc.) fall through.
    if (url.origin !== location.origin) { return; }

    // Non-GET (POST, PUT, DELETE) never cached. If offline, let the network
    // error bubble so the UI can surface it; don't pretend success.
    if (req.method !== 'GET') { return; }

    // Static assets: cache-first.
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(req).then(function (cached) {
                return cached || fetch(req).then(function (resp) {
                    if (resp && resp.status === 200) {
                        var clone = resp.clone();
                        caches.open(STATIC_CACHE).then(function (cache) { cache.put(req, clone); });
                    }
                    return resp;
                }).catch(function () {
                    return new Response('', { status: 503 });
                });
            })
        );
        return;
    }

    // Page navigations: network-first, offline-shell on failure.
    // Accept header is "text/html" for real navigations and HX-requests both.
    if (req.mode === 'navigate' || (req.headers.get('accept') || '').indexOf('text/html') !== -1) {
        event.respondWith(
            fetch(req).catch(function () {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // Everything else (XHR, fetch for JSON/API, etc.): network-only. If the
    // server isn't reachable, return a 503 JSON so callers see the error
    // explicitly instead of cached data.
    event.respondWith(
        fetch(req).catch(function () {
            return new Response(
                JSON.stringify({ offline: true, error: 'You are offline. Please reconnect.' }),
                { status: 503, headers: { 'Content-Type': 'application/json' } }
            );
        })
    );
});

function isStaticAsset(pathname) {
    if (pathname === OFFLINE_URL || pathname === '/manifest.json') { return true; }
    var exts = ['.css', '.js', '.ttf', '.woff', '.woff2', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.webp'];
    for (var i = 0; i < exts.length; i++) {
        if (pathname.endsWith(exts[i])) { return true; }
    }
    return false;
}

// --- Web Push: show OS notification when the server pushes ---
self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (err) { data = { title: 'SHF', body: event.data ? event.data.text() : '' }; }

    var title = data.title || 'SHF';
    var options = {
        body: data.body || '',
        icon: data.icon || '/images/icon-192x192.png',
        badge: data.badge || '/images/icon-192x192.png',
        tag: data.tag || 'shf-notification',
        renotify: true,
        data: data.data || {},
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// Click: focus an open window or open the deep link in a new tab.
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var target = (event.notification.data && event.notification.data.url) || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var c = clientList[i];
                if (c.url.indexOf(self.location.origin) === 0 && 'focus' in c) {
                    if (c.navigate) { c.navigate(target); }
                    return c.focus();
                }
            }
            return self.clients.openWindow(target);
        })
    );
});
