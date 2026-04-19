@extends('layouts.app')
@section('title', 'Notifications — SHF')

@section('header')
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="font-display fw-semibold text-white shf-page-title"><svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg> Notifications</h2>
        <button class="btn-accent-outline-white btn-accent-sm" id="markAllReadBtn"><svg class="shf-btn-icon shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Mark All Read</button>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5 shf-max-w-lg">

        {{-- Web Push toggle --}}
        <div class="card border-0 shadow-sm mb-3" id="pushPrompt" style="display:none;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <strong id="pushPromptTitle">Enable desktop + mobile notifications</strong>
                        <div class="small text-muted" id="pushPromptSubtitle">
                            Get alerted on your phone/desktop even when SHF is closed.<br>
                            <span class="text-muted">ફોન/ડેસ્કટોપ પર SHF બંધ હોય ત્યારે પણ નોટિફિકેશન મળશે.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn-accent btn-accent-sm" id="enablePushBtn">Enable</button>
                        <button class="btn-accent-outline btn-accent-sm" id="disablePushBtn" style="display:none;">Disable</button>
                    </div>
                </div>

                {{-- Blocked-state instructions (shown only when permission === 'denied') --}}
                <div id="pushPromptBlockedHelp" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid rgba(0,0,0,0.08);">
                    <p class="mb-1 small"><strong>How to re-enable / ફરીથી ચાલુ કરવા:</strong></p>
                    <ol class="small mb-0 ps-3">
                        <li>
                            Tap the <strong>lock</strong> icon in the address bar<br>
                            <span class="text-muted">સરનામા પટ્ટીમાં <strong>લોક</strong> આઇકન પર ટેપ કરો</span>
                        </li>
                        <li>
                            Open <strong>Permissions</strong> → <strong>Notifications</strong><br>
                            <span class="text-muted"><strong>પરવાનગી</strong> → <strong>સૂચનાઓ</strong> ખોલો</span>
                        </li>
                        <li>
                            Select <strong>Allow</strong><br>
                            <span class="text-muted"><strong>મંજૂરી આપો</strong> પસંદ કરો</span>
                        </li>
                        <li>
                            Reload this page<br>
                            <span class="text-muted">આ પાનું રીલોડ કરો</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Sound settings --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <strong>Notification sound</strong>
                        <div class="small text-muted">Chime when a live notification arrives. / લાઇવ નોટિફિકેશન આવે ત્યારે અવાજ.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select id="chimePresetSelect" class="form-select form-select-sm shf-max-w-20"></select>
                        <button class="btn-accent btn-accent-sm" id="testSoundBtn">Test</button>
                        <button class="btn-accent btn-accent-sm" id="unmuteBtn" style="display:none;">Unmute</button>
                        <button class="btn-accent-outline btn-accent-sm" id="muteBtn" style="display:none;">Mute</button>
                    </div>
                </div>
            </div>
        </div>

        @forelse($notifications as $notification)
            <div class="card border-0 shadow-sm mb-2 {{ $notification->is_read ? 'opacity-75' : '' }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="shf-badge shf-badge-{{ match($notification->type) { 'success' => 'green', 'warning' => 'orange', 'error' => 'orange', 'assignment' => 'blue', default => 'gray' } }} shf-text-xs">
                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                            </span>
                            <strong>{{ $notification->title }}</strong>
                        </div>
                        <small class="text-muted text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 mt-1 small">{{ $notification->message }}</p>
                    <div class="d-flex gap-2">
                        @if($notification->link)
                            <a href="{{ $notification->link }}" class="btn-accent-sm"><svg class="shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> View</a>
                        @endif
                        @if(!$notification->is_read)
                            <button class="btn-accent-outline btn-accent-sm shf-mark-read" data-id="{{ $notification->id }}"><svg class="shf-btn-icon shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Mark Read</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-4">No notifications.</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('.shf-mark-read').on('click', function() {
        var $btn = $(this), id = $btn.data('id');
        $.post('/notifications/' + id + '/read', { _token: csrfToken }).done(function() {
            $btn.closest('.card').addClass('opacity-75');
            $btn.remove();
        });
    });

    $('#markAllReadBtn').on('click', function() {
        $.post('/notifications/read-all', { _token: csrfToken }).done(function() { location.reload(); });
    });

    // Push notification toggle
    if (window.SHFPush && window.SHFPush.supported()) {
        var $prompt = $('#pushPrompt'), $enable = $('#enablePushBtn'), $disable = $('#disablePushBtn');
        var $blockedHelp = $('#pushPromptBlockedHelp'), $subtitle = $('#pushPromptSubtitle');
        var defaultTitle = 'Enable desktop + mobile notifications';
        var refresh = function () {
            window.SHFPush.status().then(function (s) {
                $prompt.show();
                if (s.permission === 'denied') {
                    $('#pushPromptTitle').text('Notifications blocked — enable them in browser settings.');
                    $enable.hide(); $disable.hide();
                    $subtitle.hide();
                    $blockedHelp.show();
                    return;
                }
                $('#pushPromptTitle').text(defaultTitle);
                $subtitle.show();
                $blockedHelp.hide();
                $enable.toggle(!s.subscribed);
                $disable.toggle(s.subscribed);
            }).catch(function () {
                $prompt.show();
                $('#pushPromptTitle').text(defaultTitle);
                $subtitle.show();
                $blockedHelp.hide();
                $enable.show();
                $disable.hide();
            });
        };
        $enable.on('click', function () {
            $enable.prop('disabled', true).text('Enabling…');
            window.SHFPush.enable().then(refresh).catch(function (err) {
                alert(err.message || 'Failed to enable notifications.');
                refresh();
            }).finally(function () { $enable.prop('disabled', false).text('Enable'); });
        });
        $disable.on('click', function () {
            $disable.prop('disabled', true).text('Disabling…');
            window.SHFPush.disable().then(refresh, refresh)
                .finally(function () { $disable.prop('disabled', false).text('Disable'); });
        });
        refresh();
    }

    // Sound mute/unmute + test + preset selection
    if (window.SHFPush && typeof window.SHFPush.playChime === 'function') {
        var $mute = $('#muteBtn'), $unmute = $('#unmuteBtn'), $test = $('#testSoundBtn');
        var $preset = $('#chimePresetSelect');
        var refreshSound = function () {
            var muted = window.SHFPush.isMuted();
            $mute.toggle(!muted);
            $unmute.toggle(muted);
        };
        $test.on('click', function () { window.SHFPush.playChime(); });
        $mute.on('click', function () { window.SHFPush.mute(); refreshSound(); });
        $unmute.on('click', function () { window.SHFPush.unmute(); refreshSound(); window.SHFPush.playChime(); });
        refreshSound();

        if ($preset.length && typeof window.SHFPush.getChimePresets === 'function') {
            var current = window.SHFPush.getChimePreset();
            var presets = window.SHFPush.getChimePresets();
            presets.forEach(function (p) {
                var $opt = $('<option></option>').val(p.key).text(p.label);
                if (p.key === current) { $opt.prop('selected', true); }
                $preset.append($opt);
            });
            $preset.on('change', function () {
                window.SHFPush.setChimePreset(this.value);
                if (!window.SHFPush.isMuted()) { window.SHFPush.playChime(); }
            });
        }
    }
});
</script>
@endpush
