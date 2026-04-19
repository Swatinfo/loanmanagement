// ============================================================
//  SHF Web Push — browser-native notifications
//
//  Exposes window.SHFPush with enable()/disable()/status() so the UI can
//  offer a "Enable notifications" toggle. Subscription is sent to
//  /api/push/subscribe which persists the endpoint keyed to the logged-in
//  user via NotificationChannels\WebPush.
// ============================================================
(function () {
    var publicKey = null;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function urlBase64ToUint8Array(b64) {
        var padding = '='.repeat((4 - b64.length % 4) % 4);
        var base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = atob(base64);
        var arr = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) { arr[i] = raw.charCodeAt(i); }
        return arr;
    }

    function fetchPublicKey() {
        if (publicKey) { return Promise.resolve(publicKey); }
        return fetch('/api/push/public-key', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                publicKey = data.key;
                return publicKey;
            });
    }

    function supported() {
        return 'serviceWorker' in navigator
            && 'PushManager' in window
            && 'Notification' in window;
    }

    function permission() {
        return supported() ? Notification.permission : 'unsupported';
    }

    function currentSubscription() {
        return navigator.serviceWorker.ready
            .then(function (reg) { return reg.pushManager.getSubscription(); });
    }

    function postSubscription(sub) {
        return fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(sub.toJSON()),
        }).then(function (r) {
            if (!r.ok) { throw new Error('Server rejected subscription (' + r.status + ').'); }
            return sub;
        });
    }

    function enable() {
        if (!supported()) {
            return Promise.reject(new Error('Push notifications not supported in this browser.'));
        }

        return Notification.requestPermission().then(function (perm) {
            if (perm !== 'granted') {
                throw new Error('Notification permission denied.');
            }

            return fetchPublicKey().then(function (key) {
                if (!key) { throw new Error('No VAPID key configured on server.'); }

                return navigator.serviceWorker.ready.then(function (reg) {
                    return reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(key),
                    });
                });
            });
        }).then(postSubscription).then(function (sub) {
            try { localStorage.removeItem('shf-push-disabled'); } catch (e) {}
            return sub;
        });
    }

    // Re-register the current browser subscription with the server under the
    // currently-authenticated user. Safe to call on every page load — the
    // server-side updatePushSubscription() is an upsert keyed on endpoint,
    // so impersonated sessions get the endpoint reassigned to them.
    function resync() {
        if (!supported()) { return Promise.resolve(null); }
        if (Notification.permission !== 'granted') { return Promise.resolve(null); }
        return currentSubscription().then(function (sub) {
            if (!sub) { return null; }
            return postSubscription(sub);
        });
    }

    function disable() {
        return currentSubscription().then(function (sub) {
            try { localStorage.setItem('shf-push-disabled', '1'); } catch (e) {}
            if (!sub) { return true; }
            var endpoint = sub.endpoint;
            return sub.unsubscribe().then(function () {
                return fetch('/api/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ endpoint: endpoint }),
                });
            });
        });
    }

    function wasExplicitlyDisabled() {
        try { return localStorage.getItem('shf-push-disabled') === '1'; } catch (e) { return false; }
    }

    // Detach this endpoint from whoever currently owns it server-side, WITHOUT
    // killing the browser's pushManager subscription. Used when leaving
    // impersonation so the impersonated user's row is gone instantly. On the
    // next page load, the admin's auto-re-subscribe re-uses the same browser
    // sub and upserts it to admin's user_id — no new endpoint generated.
    function detachFromCurrentUser() {
        return currentSubscription().then(function (sub) {
            if (!sub) { return true; }
            return fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ endpoint: sub.endpoint }),
                keepalive: true,
            });
        }).catch(function () {
            // Don't block navigation if this fails.
        });
    }

    // Remove browser + server subscription on logout so the next user on this
    // device starts with a clean slate. Unlike disable(), this does NOT set the
    // explicit-disable flag — the next user should be able to auto-subscribe.
    function cleanupOnLogout() {
        return currentSubscription().then(function (sub) {
            if (!sub) { return true; }
            var endpoint = sub.endpoint;
            return sub.unsubscribe().then(function () {
                return fetch('/api/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ endpoint: endpoint }),
                    keepalive: true,
                });
            });
        }).catch(function () {
            // Never block logout if cleanup fails.
        });
    }

    function status() {
        if (!supported()) { return Promise.resolve({ supported: false, subscribed: false, permission: 'unsupported' }); }
        return currentSubscription().then(function (sub) {
            return { supported: true, subscribed: !!sub, permission: Notification.permission };
        });
    }

    // ============================================================
    //  In-tab chime for live Reverb pushes.
    //  Uses MP3 assets in /public/sounds/. Audio elements are cached and
    //  preloaded so the chime fires with minimal latency. Muting is persisted
    //  in localStorage under `shf-notif-sound`. Selected preset in
    //  `shf-chime-preset`.
    // ============================================================

    function isMuted() {
        return localStorage.getItem('shf-notif-sound') === 'off';
    }

    // Append the deployed asset version to each chime URL so browsers can't
    // serve a stale MP3 after we ship new audio (Service Worker ignores .mp3,
    // but the HTTP cache will happily reuse old bytes keyed on the bare path).
    function assetVersion() {
        var tag = document.querySelector('meta[name="shf-asset-version"]');
        return tag && tag.getAttribute('content') ? tag.getAttribute('content') : '';
    }

    function versionedSrc(path) {
        var v = assetVersion();
        return v ? (path + '?v=' + encodeURIComponent(v)) : path;
    }

    var CHIMES = {
        smooth: {
            label: 'Smooth Notification',
            src: versionedSrc('/sounds/smooth_notification.mp3'),
        },
        cyan: {
            label: 'Cyan Message',
            src: versionedSrc('/sounds/cyan_message.mp3'),
        },
        luster: {
            label: 'Luster',
            src: versionedSrc('/sounds/luster.mp3'),
        },
        mario: {
            label: 'Mario Coin',
            src: versionedSrc('/sounds/mario_coin.mp3'),
        },
        classic: {
            label: 'Classic Notification',
            src: versionedSrc('/sounds/notification9.mp3'),
        },
    };

    var audioCache = {};
    var warnedUnprimed = false;

    function getChimePreset() {
        var saved = localStorage.getItem('shf-chime-preset');
        return (saved && CHIMES[saved]) ? saved : 'smooth';
    }

    function setChimePreset(key) {
        if (!CHIMES[key]) { return false; }
        localStorage.setItem('shf-chime-preset', key);
        return true;
    }

    function getChimePresets() {
        return Object.keys(CHIMES).map(function (k) {
            return { key: k, label: CHIMES[k].label };
        });
    }

    function getAudioFor(key) {
        if (audioCache[key]) { return audioCache[key]; }
        var preset = CHIMES[key];
        if (!preset) { return null; }
        var audio = new Audio(preset.src);
        audio.preload = 'auto';
        audioCache[key] = audio;
        return audio;
    }

    function playChime() {
        if (isMuted()) { return; }
        var key = getChimePreset();
        var audio = getAudioFor(key);
        if (!audio) { return; }
        try {
            audio.currentTime = 0;
            audio.volume = 1.0;
            var promise = audio.play();
            if (promise && typeof promise.catch === 'function') {
                promise.catch(function () { warnOnce(); });
            }
        } catch (e) {
            warnOnce();
        }
    }

    function warnOnce() {
        if (warnedUnprimed) { return; }
        warnedUnprimed = true;
        console.info('SHFPush: sound blocked by browser autoplay policy. Click anywhere in the page once to enable notification sound.');
    }

    window.SHFPush = {
        supported: supported,
        permission: permission,
        enable: enable,
        disable: disable,
        resync: resync,
        status: status,
        wasExplicitlyDisabled: wasExplicitlyDisabled,
        cleanupOnLogout: cleanupOnLogout,
        detachFromCurrentUser: detachFromCurrentUser,
        playChime: playChime,
        isMuted: isMuted,
        mute: function () { localStorage.setItem('shf-notif-sound', 'off'); },
        unmute: function () { localStorage.removeItem('shf-notif-sound'); },
        getChimePreset: getChimePreset,
        setChimePreset: setChimePreset,
        getChimePresets: getChimePresets,
    };
})();
