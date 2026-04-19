<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <title>@yield('title', config('app.name', 'SHF Loan Management'))</title>

    <!-- Bootstrap 5.3 CSS (local) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- SHF Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/shf.css') }}?v={{ config('app.shf_version') }}">
</head>

<body class="font-body" style="background: #3a3536; min-height: 100vh;">
    <div class="d-flex flex-column align-items-center justify-content-center"
        style="min-height: 100vh; padding-top: 1.5rem;">
        <div class="mb-4 text-center">
            <a href="/" class="d-flex flex-column align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/logo3.png') }}" alt="SHF Logo" style="height: 48px; width: auto;">
                <span class="font-display fw-bold"
                    style="font-size: 1.5rem; color: #f15a29; letter-spacing: 0.15em;">SHF</span>
                <span class="font-display"
                    style="color: rgba(255,255,255,0.6); font-size: 0.875rem; font-weight: 500;">Loan Management</span>
            </a>
        </div>

        <div class="bg-white shadow-lg overflow-hidden"
            style="width: 100%; max-width: 28rem; padding: 2rem; border-radius: 10px; border-top: 3px solid #f15a29;">
            @yield('content')
        </div>
    </div>

    <!-- jQuery + Bootstrap JS + SHF App -->
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/shf-app.js') }}?v={{ config('app.shf_version') }}"></script>
    @stack('scripts')
</body>

</html>
