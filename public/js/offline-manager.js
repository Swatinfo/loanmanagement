// ============================================================
//  OFFLINE MANAGER — online-only gate
//
//  SHF is an online-only app. This module no longer queues writes or caches
//  data in IndexedDB. Its only job is to detect offline state, block all
//  in-page interaction, and surface a clear offline banner + disabled menu.
//
//  The object keeps its legacy method names as no-op shims so existing
//  call sites (config-loader.js, quotations/create.blade.php) don't break.
// ============================================================
var OfflineManager = (function () {
    var noopResolved = function () { return Promise.resolve(null); };

    function showOfflineGate(on) {
        var banner = document.getElementById('offlineBanner');
        if (banner) {
            if (on) {
                banner.className = 'offline-banner show offline';
                banner.innerHTML = 'You are offline / તમે ઑફલાઇન છો';
            } else {
                banner.className = 'offline-banner';
            }
        }

        // Disable every nav link and every form control while offline so
        // users can't half-submit forms that will 503 on the server.
        var nav = document.querySelectorAll('a.shf-nav-link, .navbar a, .shf-nav-link');
        nav.forEach(function (el) {
            if (on) {
                el.setAttribute('data-offline-href', el.getAttribute('href') || '');
                el.setAttribute('href', '#');
                el.setAttribute('aria-disabled', 'true');
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.5';
            } else {
                var h = el.getAttribute('data-offline-href');
                if (h !== null) { el.setAttribute('href', h); el.removeAttribute('data-offline-href'); }
                el.removeAttribute('aria-disabled');
                el.style.pointerEvents = '';
                el.style.opacity = '';
            }
        });

        document.querySelectorAll('form button[type=submit], form input[type=submit]').forEach(function (el) {
            el.disabled = on || el.dataset.alwaysEnabled === '1';
        });
    }

    return {
        // --- Network listeners (the only thing that still does work) ---
        setupNetworkListeners: function () {
            var update = function () { showOfflineGate(!navigator.onLine); };
            window.addEventListener('online', update);
            window.addEventListener('offline', update);
            update();
        },

        updateStatusUI: function (status) {
            showOfflineGate(status === 'offline');
        },

        // --- No-op shims (kept for API compatibility during transition) ---
        openDB: noopResolved,
        getCsrfToken: function () {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        },
        cacheConfig: noopResolved,
        getCachedConfig: noopResolved,
        cacheCharges: noopResolved,
        getCachedCharges: noopResolved,
        cacheNotes: noopResolved,
        getCachedNotes: function () { return Promise.resolve(''); },
        queueQuotation: function () { return Promise.reject(new Error('Offline queueing disabled — SHF is now online-only.')); },
        getPendingQuotations: function () { return Promise.resolve([]); },
        getPendingQuotationsWithKeys: function () { return Promise.resolve([]); },
        deleteQuotationByKey: noopResolved,
        clearPendingQuotations: noopResolved,
        queueConfigChange: function () { return Promise.reject(new Error('Offline queueing disabled — SHF is now online-only.')); },
        getPendingConfigChanges: function () { return Promise.resolve([]); },
        clearPendingConfigChanges: noopResolved,
        syncAll: function () { return Promise.resolve({ synced: 0, failed: 0 }); },
    };
})();
