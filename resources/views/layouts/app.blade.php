<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shf-asset-version" content="{{ config('app.shf_version') }}">

    <title>@yield('title', config('app.name', 'SHF Loan Management'))</title>



    {{-- ── Favicons ───────────────────────────────────────────── --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon/android-chrome-512x512.png') }}">

    {{-- ── Apple / iOS ────────────────────────────────────────── --}}
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SHF World">

    {{-- ── Android / PWA ─────────────────────────────────────── --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#3a3536">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- ── Windows Tiles ─────────────────────────────────────── --}}
    <meta name="msapplication-TileColor" content="#3a3536">
    <meta name="msapplication-TileImage" content="{{ asset('favicon/android-chrome-192x192.png') }}">
    <meta name="msapplication-config" content="none">

    <!-- Fonts: now loaded via @font-face in shf.css (local woff2 files) -->

    <!-- Bootstrap 5.3 CSS (local) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="{{ asset('vendor/datepicker/css/bootstrap-datepicker3.min.css') }}">

    <!-- SHF Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/shf.css') }}?v={{ config('app.shf_version') }}">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">

    @stack('styles')
</head>

@php
    // FAB ("+ New Quotation / Task / Visit") is only surfaced on list /
    // dashboard style pages where the user is browsing. It's suppressed on:
    //   - loan deep-workflow pages (stages/docs/valuation/etc) that have
    //     their own in-page sticky action bar
    //   - form / edit / create / config pages where the page already has
    //     its own primary CTA at form-end
    //   - any settings surface
    // Keeping the nav visible on all of these; only the FAB is hidden so the
    // page's own primary action isn't visually competed against.
    $shfHideFabRoutes = [
        // Loan deep-workflow
        'loans.stages', 'loans.stages.*',
        'loans.documents', 'loans.documents.*',
        'loans.valuation', 'loans.valuation.*',
        'loans.transfers',
        'loans.timeline',
        'loans.disbursement', 'loans.disbursement.*',
        'loans.queries.*',
        'loans.remarks.*',
        // Form / edit pages with their own primary CTA
        'loans.edit', 'loans.create',
        'users.edit', 'users.create',
        'customers.edit',
        'quotations.create', 'quotations.show', 'quotations.convert', 'quotations.convert.store',
        'general-tasks.show',
        'dvr.show',
        'profile.*',
        // Settings surfaces
        'settings.*',
        'loan-settings.*',
        'permissions.*',
        'roles.*',
    ];
    $shfHideFab = request()->routeIs(...$shfHideFabRoutes);
    // `has-bottom-nav`: reserves space for the mobile bottom nav.
    // `has-fab`: reserves space for the FAB on desktop (mobile already covered
    // by has-bottom-nav; FAB rides over the content→nav margin gap).
    $shfBodyClass = 'font-body bg-body-tertiary';
    if (auth()->check()) {
        $shfBodyClass .= ' has-bottom-nav';
        if (! $shfHideFab) {
            $shfBodyClass .= ' has-fab';
        }
    }
@endphp
<body class="{{ $shfBodyClass }}">
    <div class="min-vh-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @hasSection('header')
            <header class="shadow"
                style="background: linear-gradient(135deg, #3a3536cc 0%, #3a353680 100%); backdrop-filter: blur(10px); position: relative; z-index: 10;">
                <div class="py-3 px-3 px-sm-4 px-lg-5">
                    @yield('header')
                </div>
            </header>
        @endif

        <!-- Flash Messages (Toast Style) — jQuery-driven -->
        @if (session('success'))
            <div class="shf-toast-wrapper">
                <div class="shf-toast success" data-auto-dismiss="5000">
                    <svg style="width:16px;height:16px;color:#4ade80;flex-shrink:0;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="shf-toast-close shf-tab-close">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="shf-toast-wrapper">
                <div class="shf-toast error" data-auto-dismiss="8000">
                    <svg style="width:16px;height:16px;color:#f87171;flex-shrink:0;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="shf-toast-close shf-tab-close">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
        @if (session('warning'))
            <div class="shf-toast-wrapper">
                <div class="shf-toast warning" data-auto-dismiss="7000">
                    <svg style="width:16px;height:16px;color:#facc15;flex-shrink:0;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="shf-toast-close shf-tab-close">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    @auth
        @include('partials.bottom-nav')
        @unless ($shfHideFab)
            @include('partials.mobile-fab')
        @endunless
    @endauth

    <!-- PWA Install Banner -->
    <div id="installBanner"
        style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9998;width:calc(100% - 32px);max-width:400px;">
        <div
            style="background:linear-gradient(135deg,#3a3536 0%,#4a4546 100%);border-radius:16px;padding:16px 20px;box-shadow:0 8px 32px rgba(0,0,0,0.25);border:1px solid rgba(241,90,41,0.3);display:flex;align-items:center;gap:14px;">
            <img src="{{ asset('images/icon-192x192.png') }}" alt="SHF"
                style="width:48px;height:48px;border-radius:12px;flex-shrink:0;">
            <div style="flex:1;min-width:0;">
                <div style="color:#fff;font-weight:700;font-size:0.9rem;font-family:'Jost',sans-serif;">Install SHF
                    World</div>
                <div style="color:rgba(255,255,255,0.6);font-size:0.75rem;margin-top:2px;">Quick access from your home
                    screen</div>
            </div>
            <button id="installBtn"
                style="background:linear-gradient(135deg,#f15a29,#f47929);color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:700;font-size:0.8rem;cursor:pointer;white-space:nowrap;font-family:'Jost',sans-serif;">Install</button>
            <button id="installDismiss"
                style="background:none;border:none;color:rgba(255,255,255,0.4);cursor:pointer;padding:4px;font-size:1.2rem;line-height:1;"
                aria-label="Dismiss">&times;</button>
        </div>
    </div>

    <!-- Offline Status Banner -->
    <div id="offlineBanner" class="offline-banner"
        style="display:none;position:fixed;bottom:0;left:0;right:0;padding:8px 16px;text-align:center;font-size:14px;z-index:9999;transition:transform 0.3s ease;">
    </div>

    <style>
        .offline-banner {
            transform: translateY(100%);
        }

        .offline-banner.show {
            display: block !important;
            transform: translateY(0);
        }

        .offline-banner.offline {
            background: #c0392b;
            color: #fff;
            border-top: 2px solid #e74c3c;
        }

        .offline-banner.syncing {
            background: #f39c12;
            color: #fff;
            border-top: 2px solid #f1c40f;
        }

        .offline-banner.online {
            background: #27ae60;
            color: #fff;
            border-top: 2px solid #2ecc71;
        }

        /* Sticky navbar: keep expanded mobile menu within the viewport so it
           can scroll internally instead of growing past the sticky anchor. */
        @media (max-width: 1199.98px) {
            #shfNavbar {
                max-height: calc(100vh - 56px - env(safe-area-inset-top));
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Keep the mobile navbar's top row compact — cap the logo so the
           icons + hamburger all fit. We DO NOT disable flex-wrap on the nav
           container itself; the expanded #shfNavbar collapse still needs to
           drop BELOW the top row when the hamburger is tapped. */
        @media (max-width: 1199.98px) {
            .navbar-brand img {
                max-width: 110px !important;
                height: auto !important;
                max-height: 28px;
            }
        }

        /* Mobile phones (≤ 400px): keep the role badge visible but tighten
           container padding, margin and badge padding so everything still
           fits on a single line at 344 px wide. */
        @media (max-width: 400px) {
            .navbar .container-fluid {
                padding-left: 8px;
                padding-right: 8px;
            }
            .navbar-brand {
                margin-right: 0 !important;
            }
            .navbar > .container-fluid > .d-flex.d-xl-none.ms-auto {
                gap: 4px !important;
                margin-right: 4px !important;
            }
            .navbar > .container-fluid > .d-flex.d-xl-none.ms-auto .shf-badge-username {
                padding: 2px 6px !important;
                font-size: 0.6rem !important;
            }
        }
    </style>

    <!-- jQuery + Bootstrap JS + Datepicker + SweetAlert2 + SHF App -->
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/shf-app.js') }}?v={{ config('app.shf_version') }}"></script>

    @stack('scripts')

    <!-- Offline Manager (IndexedDB + sync) -->
    <script src="{{ asset('js/offline-manager.js') }}?v={{ config('app.shf_version') }}"></script>

    {{-- Real-time notifications via Laravel Echo + Reverb --}}
    <script src="{{ asset('vendor/pusher-js/pusher.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-echo/echo.iife.js') }}"></script>
    @auth
    @php
        $reverbApp = config('reverb.apps.apps.0.options', []);
        $reverbPort = (int) ($reverbApp['port'] ?? 443);
        $reverbScheme = $reverbApp['scheme'] ?? 'https';
        $reverbHost = $reverbApp['host'] ?? request()->getHost();
    @endphp
    <script>
        (function () {
            try {
                window.Echo = new Echo({
                    broadcaster: 'reverb',
                    key: @json(config('broadcasting.connections.reverb.key')),
                    wsHost: @json($reverbHost),
                    wsPort: {{ $reverbPort }},
                    wssPort: {{ $reverbPort }},
                    forceTLS: {{ $reverbScheme === 'https' ? 'true' : 'false' }},
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                });

                window.Echo.private('users.{{ auth()->id() }}')
                    .listen('.notification.created', function (data) {
                        if (typeof window.updateNotifBadge === 'function') {
                            window.updateNotifBadge();
                        }
                        if (window.SHFPush && typeof window.SHFPush.playChime === 'function') {
                            window.SHFPush.playChime();
                        }
                        if (window.SHFLoans && typeof window.SHFLoans.showToast === 'function') {
                            window.SHFLoans.showToast(data.title || 'New notification', 'info');
                        }
                    });
            } catch (err) {
                console.warn('Echo init failed (falling back to polling):', err);
            }
        })();
    </script>
    @endauth

    <!-- Client-side PDF renderer (offline PDF via print dialog) -->
    <script src="{{ asset('js/pdf-renderer.js') }}?v={{ config('app.shf_version') }}"></script>

    {{-- Web Push helper (window.SHFPush) --}}
    <script src="{{ asset('js/push-notifications.js') }}?v={{ config('app.shf_version') }}"></script>
    @auth
    @php
        // True if the *currently-authed* user (real or impersonated) has at least
        // one active push subscription in the DB. Drives the banner — a granted
        // browser that belongs to a different user should still prompt.
        $currentUserHasPushSub = auth()->user()->pushSubscriptions()->exists();
    @endphp
    {{-- Dismissible banner that offers push opt-in. Styled to match the PWA install banner above. --}}
    <div id="pushOptInBanner"
        style="display:none;position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:9997;width:calc(100% - 24px);max-width:400px;">
        <div
            style="background:linear-gradient(135deg,#3a3536 0%,#4a4546 100%);border-radius:16px;padding:14px 16px;box-shadow:0 12px 32px rgba(0,0,0,0.28);border:1px solid rgba(241,90,41,0.3);">
            {{-- Row 1: icon + text + dismiss --}}
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="width:40px;height:40px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#f15a29,#f47929);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(241,90,41,0.35);">
                    <svg fill="none" stroke="#fff" viewBox="0 0 24 24" style="width:22px;height:22px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div style="flex:1;min-width:0;padding-top:1px;">
                    <div style="color:#fff;font-weight:700;font-size:0.95rem;font-family:'Jost',sans-serif;line-height:1.25;">
                        Enable notifications
                    </div>
                    <div style="color:rgba(255,255,255,0.65);font-size:0.78rem;margin-top:4px;line-height:1.4;">
                        Loan updates on your phone + desktop, even when SHF is closed.
                    </div>
                </div>
                <button id="pushOptInDismissBtn"
                    style="background:none;border:none;color:rgba(255,255,255,0.5);cursor:pointer;padding:2px 4px;font-size:1.4rem;line-height:1;margin:-4px -4px 0 0;flex-shrink:0;"
                    aria-label="Dismiss">&times;</button>
            </div>
            {{-- Row 2: actions --}}
            <div style="display:flex;justify-content:flex-end;align-items:center;gap:16px;margin-top:12px;">
                <a href="{{ route('notifications.index') }}" id="pushOptInSettingsLink"
                    style="color:rgba(255,255,255,0.75);text-decoration:none;font-size:0.8rem;font-family:'Jost',sans-serif;font-weight:500;">Settings</a>
                <button id="pushOptInEnableBtn"
                    style="background:linear-gradient(135deg,#f15a29,#f47929);color:#fff;border:none;padding:9px 22px;border-radius:10px;font-weight:700;font-size:0.85rem;cursor:pointer;font-family:'Jost',sans-serif;box-shadow:0 4px 12px rgba(241,90,41,0.35);white-space:nowrap;">Enable</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            if (!window.SHFPush || !window.SHFPush.supported()) { return; }

            var currentUserHasPushSub = @json($currentUserHasPushSub);
            var isImpersonating = @json(app('impersonate')->isImpersonating());
            var banner = document.getElementById('pushOptInBanner');
            var enableBtn = document.getElementById('pushOptInEnableBtn');
            var dismissBtn = document.getElementById('pushOptInDismissBtn');

            // Permission already granted AND current user already has a server-side sub.
            // Silent resync — keeps the endpoint mapped to this session's user.
            // During impersonation this is the "ping-pong" that reassigns the endpoint
            // to the impersonated user so admin sees their notifications in context.
            if (window.SHFPush.permission() === 'granted' && currentUserHasPushSub) {
                window.SHFPush.resync().catch(function () {});
                return;
            }

            // Permission granted but server has NO sub for this user (e.g. after
            // impersonation start, or a user who has never opted in on this device).
            // Try a silent re-subscribe — no prompt needed since permission exists.
            // Respect an explicit user Disable (but not during impersonation — the
            // admin's flag on their own device shouldn't block pushes for the user
            // they're acting as).
            if (window.SHFPush.permission() === 'granted' && !currentUserHasPushSub
                && (isImpersonating
                    || !(typeof window.SHFPush.wasExplicitlyDisabled === 'function' && window.SHFPush.wasExplicitlyDisabled()))) {
                window.SHFPush.enable().catch(function () { if (!isImpersonating) { showBanner(); } });
                return;
            }

            // Don't nag during impersonation — admin knows the state of their own push.
            if (isImpersonating) { return; }

            // Permission blocked → we can't prompt; banner directs to /notifications
            // where the user gets a "blocked — change in browser settings" message.
            if (window.SHFPush.permission() === 'denied') {
                showBanner(true);
                return;
            }

            // Permission is 'default' (never asked). Respect the 24h dismiss cooldown.
            var lastDismiss = parseInt(localStorage.getItem('push-optin-dismissed') || '0', 10);
            if (lastDismiss && (Date.now() - lastDismiss) < 86400000) { return; }
            showBanner();

            function showBanner(blocked) {
                if (!banner) { return; }
                if (blocked) { enableBtn.style.display = 'none'; }
                banner.style.display = 'block';

                enableBtn.addEventListener('click', function () {
                    enableBtn.disabled = true;
                    enableBtn.textContent = 'Enabling…';
                    window.SHFPush.enable().then(function () {
                        banner.style.display = 'none';
                    }).catch(function (err) {
                        enableBtn.disabled = false;
                        enableBtn.textContent = 'Enable';
                        console.warn('Push enable failed:', err);
                        // Send them to the full settings page — it surfaces the
                        // "notifications blocked, change in browser settings" hint
                        // and the disable/test-sound controls.
                        window.location.href = @json(route('notifications.index'));
                    });
                });

                dismissBtn.addEventListener('click', function () {
                    localStorage.setItem('push-optin-dismissed', Date.now().toString());
                    banner.style.display = 'none';
                });
            }
        })();
    </script>

    {{-- Leave-impersonation interceptor: detach the endpoint from the impersonated user
         *before* the redirect, so no row briefly lingers under their user_id. --}}
    <script>
        (function () {
            var leaveUrl = @json(route('impersonate.leave'));
            var links = document.querySelectorAll('a[href="' + leaveUrl + '"]');
            if (!links.length) { return; }

            links.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    if (link.__shfLeaveCleaned) { return; }
                    e.preventDefault();
                    link.__shfLeaveCleaned = true;

                    var go = function () { window.location.href = leaveUrl; };

                    if (window.SHFPush && typeof window.SHFPush.detachFromCurrentUser === 'function') {
                        var done = false;
                        var guard = setTimeout(function () { if (!done) { done = true; go(); } }, 1500);
                        window.SHFPush.detachFromCurrentUser().finally(function () {
                            if (done) { return; }
                            done = true;
                            clearTimeout(guard);
                            go();
                        });
                    } else {
                        go();
                    }
                });
            });
        })();
    </script>

    {{-- Logout interceptor: unsubscribe push + clear server record before the form posts. --}}
    <script>
        (function () {
            var logoutForms = document.querySelectorAll('form[action="{{ route('logout') }}"]');
            if (!logoutForms.length) { return; }

            logoutForms.forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (form.__shfLogoutCleaned) { return; }
                    e.preventDefault();
                    form.__shfLogoutCleaned = true;

                    var finish = function () {
                        // Clear local toggles so next user on this device starts fresh.
                        try {
                            localStorage.removeItem('shf-push-disabled');
                            localStorage.removeItem('push-optin-dismissed');
                        } catch (err) {}
                        form.submit();
                    };

                    if (window.SHFPush && typeof window.SHFPush.cleanupOnLogout === 'function') {
                        // Race safety: don't hang the logout if cleanup stalls.
                        var done = false;
                        var guard = setTimeout(function () { if (!done) { done = true; finish(); } }, 1500);
                        window.SHFPush.cleanupOnLogout().finally(function () {
                            if (done) { return; }
                            done = true;
                            clearTimeout(guard);
                            finish();
                        });
                    } else {
                        finish();
                    }
                });
            });
        })();
    </script>
    @endauth

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(function(reg) {
                console.log('SW registered:', reg.scope);
            }).catch(function(err) {
                console.warn('SW registration failed:', err);
            });
        }
        // Wire up offline/online banner + auto-sync via OfflineManager
        if (typeof OfflineManager !== 'undefined') {
            OfflineManager.setupNetworkListeners();

            // Auto-sync on page load if online with pending items
            if (navigator.onLine) {
                OfflineManager.getPendingQuotations().then(function(items) {
                    if (items.length > 0) {
                        OfflineManager.syncAll();
                    }
                }).catch(function() {});
            }
        }

        // PWA Install Prompt
        (function() {
            var deferredPrompt = null;
            var banner = document.getElementById('installBanner');
            var installBtn = document.getElementById('installBtn');
            var dismissBtn = document.getElementById('installDismiss');

            // Don't show if already installed or previously dismissed this session
            if (window.matchMedia('(display-mode: standalone)').matches || navigator.standalone) {
                return;
            }

            // Check if user dismissed recently (24h cooldown)
            var dismissed = localStorage.getItem('pwa-install-dismissed');
            if (dismissed && (Date.now() - parseInt(dismissed)) < 86400000) {
                return;
            }

            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                banner.style.display = '';
            });

            installBtn.addEventListener('click', function() {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(result) {
                    if (result.outcome === 'accepted') {
                        banner.style.display = 'none';
                    }
                    deferredPrompt = null;
                });
            });

            dismissBtn.addEventListener('click', function() {
                banner.style.display = 'none';
                localStorage.setItem('pwa-install-dismissed', Date.now().toString());
                deferredPrompt = null;
            });

            // Hide banner if app gets installed
            window.addEventListener('appinstalled', function() {
                banner.style.display = 'none';
                deferredPrompt = null;
            });
        })();
    </script>
</body>

</html>
