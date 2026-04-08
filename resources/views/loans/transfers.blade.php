@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg> Transfer History — {{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">
        @forelse($transfers as $transfer)
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $transfer->stageAssignment?->stage?->stage_name_en ?? $transfer->stage_key }}</strong>
                            <span class="shf-badge shf-badge-{{ $transfer->transfer_type === 'auto' ? 'blue' : 'gray' }} ms-1" class="shf-text-xs">
                                {{ $transfer->transfer_type === 'auto' ? 'Auto' : 'Manual' }}
                            </span>
                        </div>
                        <small class="text-muted">{{ $transfer->created_at?->diffForHumans() }}</small>
                    </div>
                    <div class="mt-1">
                        {{ $transfer->fromUser?->name ?? '—' }} &rarr; <strong>{{ $transfer->toUser?->name ?? '—' }}</strong>
                    </div>
                    @if($transfer->reason)
                        <small class="text-muted fst-italic">"{{ $transfer->reason }}"</small>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-4">No transfers yet.</p>
        @endforelse
    </div>
</div>
@endsection
