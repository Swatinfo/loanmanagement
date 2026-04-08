@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg> Notifications</h2>
        <button class="btn btn-sm btn-outline-light" id="markAllReadBtn"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Mark All Read</button>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">
        @forelse($notifications as $notification)
            <div class="card border-0 shadow-sm mb-2 {{ $notification->is_read ? 'opacity-75' : '' }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="shf-badge shf-badge-{{ match($notification->type) { 'success' => 'green', 'warning' => 'orange', 'error' => 'orange', 'assignment' => 'blue', default => 'gray' } }}" class="shf-text-xs">
                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                            </span>
                            <strong>{{ $notification->title }}</strong>
                        </div>
                        <small class="text-muted text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 mt-1 small">{{ $notification->message }}</p>
                    <div class="d-flex gap-2">
                        @if($notification->link)
                            <a href="{{ $notification->link }}" class="btn-accent-sm"><svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> View</a>
                        @endif
                        @if(!$notification->is_read)
                            <button class="btn btn-sm btn-outline-secondary shf-mark-read" data-id="{{ $notification->id }}"><svg class="shf-btn-icon" style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Mark Read</button>
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
});
</script>
@endpush
