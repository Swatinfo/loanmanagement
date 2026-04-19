# Real-time + Web Push Setup

Step-by-step to turn on real-time bell updates (Reverb) and mobile-style push notifications (Web Push).

Phase 4.1 has already wired the broadcast infrastructure. When a `ShfNotification` is created, `App\Events\NotificationBroadcast` fires on a private channel `users.{userId}`. With `BROADCAST_CONNECTION=log`, this is a no-op. Completing this doc turns it live.

---

## Phase 4.2 — Reverb (real-time bell, replaces 60s polling)

### 1. Install the server

```bash
composer require laravel/reverb
php artisan reverb:install
```

`reverb:install` publishes `config/reverb.php`, adds `REVERB_*` keys to `.env`, and prints an app key/secret to paste in.

### 2. Update `.env`

```
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=shf-app
REVERB_APP_KEY=<printed by installer>
REVERB_APP_SECRET=<printed by installer>
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Production uses TLS: set `REVERB_SCHEME=https` and terminate TLS in Nginx/Apache.

### 3. Run the server

Dev (one-off):

```bash
php artisan reverb:start
```

Production — add to the supervisor file alongside the queue worker (`.docs/ops.md`):

```ini
[program:shf-reverb]
process_name=%(program_name)s
command=php /var/www/shf/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/shf-reverb.log
stopwaitsecs=10
```

Open port `8080` on the firewall (or whatever `REVERB_PORT` is set to).

Nginx proxy (if you want WebSockets on the same domain as the web app):

```nginx
location /reverb/ {
    proxy_pass http://127.0.0.1:8080/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

### 4. Frontend (Laravel Echo + Pusher-protocol client)

Since the project is no-build (local vendor), use the UMD builds of `laravel-echo` and `pusher-js`:

1. Download `laravel-echo/dist/echo.iife.js` and `pusher-js/dist/web/pusher.min.js` into `public/vendor/laravel-echo/` and `public/vendor/pusher-js/`.
2. Add to `layouts/app.blade.php`:

```blade
<script src="{{ asset('vendor/pusher-js/pusher.min.js') }}"></script>
<script src="{{ asset('vendor/laravel-echo/echo.iife.js') }}"></script>
<script>
  window.Echo = new Echo.default({
    broadcaster: 'reverb',
    key: '{{ env('REVERB_APP_KEY') }}',
    wsHost: '{{ env('REVERB_HOST', '127.0.0.1') }}',
    wsPort: {{ env('REVERB_PORT', 8080) }},
    wssPort: {{ env('REVERB_PORT', 8080) }},
    forceTLS: '{{ env('REVERB_SCHEME', 'http') }}' === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
  });

  @auth
  window.Echo.private(`users.{{ auth()->id() }}`)
    .listen('.notification.created', (data) => {
      if (typeof updateNotifBadge === 'function') updateNotifBadge();
      if (window.SHFLoans?.showToast) SHFLoans.showToast(data.title, 'info');
    });
  @endauth
</script>
```

3. Delete or gate the existing `setInterval(updateNotifBadge, 60000)` — keep it only as a fallback for users with blocked WebSockets.

### 5. Broadcast auth route

Laravel registers `/broadcasting/auth` automatically when the `channels.php` route file is loaded via `bootstrap/app.php` (already wired in Phase 4.1). No extra setup.

---

## Phase 4.3 — Web Push (OneSignal-style native, no vendor)

### 1. Install the channel + generate VAPID keys

```bash
composer require laravel-notification-channels/webpush
php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider" --tag="migrations"
php artisan migrate
php artisan webpush:vapid
```

`webpush:vapid` writes `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` to `.env`. **Keep the private key out of version control.** Each environment needs its own pair.

### 2. Subscription UI (profile page)

Add to the profile view a toggle that calls the browser's Push API:

```javascript
async function subscribeToPush() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    alert('Your browser does not support push notifications.');
    return;
  }
  const permission = await Notification.requestPermission();
  if (permission !== 'granted') return;

  const registration = await navigator.serviceWorker.ready;
  const sub = await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array('{{ env('VAPID_PUBLIC_KEY') }}'),
  });

  await fetch('/push/subscribe', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body: JSON.stringify(sub),
  });
}

function urlBase64ToUint8Array(base64) {
  const pad = '='.repeat((4 - base64.length % 4) % 4);
  const b64 = (base64 + pad).replace(/-/g, '+').replace(/_/g, '/');
  const raw = atob(b64);
  return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}
```

### 3. Backend subscription endpoint

`POST /push/subscribe` → controller calls `$user->updatePushSubscription($endpoint, $key, $token)`.

### 4. Service worker push handler

In `public/sw.js` (or wherever the current service worker lives), add:

```javascript
self.addEventListener('push', (event) => {
  const data = event.data?.json() ?? {};
  event.waitUntil(
    self.registration.showNotification(data.title || 'SHF', {
      body: data.body,
      icon: '/icons/icon-192.png',
      badge: '/icons/badge-72.png',
      data: { url: data.url },
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data.url || '/dashboard'));
});
```

### 5. Hook into Laravel Notifications

Once Web Push is live, convert `ShfNotification` writes to proper Laravel `Notification` classes that use both `database` and `webpush` channels:

```php
// app/Notifications/LoanStageAssigned.php
public function via($notifiable): array
{
    return ['database', 'webpush']; // + 'broadcast' if Reverb is also on
}
```

At that point, `ShfNotification` becomes a read-only query layer over the `notifications` table (database channel writes there); `NotificationService::notify()` becomes a thin shim that calls `$user->notify(new SomeNotification(...))`.

---

## Rollout order

1. **4.2 Reverb first** — real-time bell, big UX win, no VAPID keys needed.
2. **4.3 Web Push second** — requires VAPID generation per environment + user opt-in UI.
3. **Full Laravel Notifications swap third** — only after both channels have a user-visible effect.

## Fallbacks

- **No WebSocket** (corporate firewall, hostile hosting): keep 60s polling as a graceful fallback — detect via Echo connection-state events.
- **User denies push permission**: the bell + broadcast path still works; no push, no problem.
- **`BROADCAST_CONNECTION=log`** (current default): broadcasts log silently. No user impact, good for staging dry-runs.

## What's already done (Phase 4.1)

- `App\Events\NotificationBroadcast` event — private channel `users.{userId}`, payload `notification.created`
- `ShfNotification::created` model event auto-dispatches the broadcast
- `routes/channels.php` with per-user authorization
- `bootstrap/app.php` wired to load `channels.php`
