@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
                <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Loan #{{ $loan->loan_number }}
            </h2>
            <span class="shf-badge shf-badge-{{ $loan->status_color === 'primary' ? 'blue' : ($loan->status_color === 'success' ? 'green' : 'gray') }} ms-2">
                {{ $loan->status_label }}
            </span>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5">

        {{-- Temporary loan detail view — replaced by full view in Phase 3 --}}
        <div class="shf-section mb-4">
            <div class="shf-section-header">
                <div class="shf-section-number">1</div>
                <span class="shf-section-title">Customer & Loan Details</span>
            </div>
            <div class="shf-section-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Customer</small>
                        <strong>{{ $loan->customer_name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Type</small>
                        {{ $loan->customer_type_label }}
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Loan Amount</small>
                        <strong>{{ $loan->formatted_amount }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Bank</small>
                        {{ $loan->bank_name ?? '—' }}
                        @if($loan->roi_min && $loan->roi_max)
                            <small class="text-muted">(ROI: {{ $loan->roi_min }}% - {{ $loan->roi_max }}%)</small>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Current Stage</small>
                        {{ $loan->current_stage_name }}
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Loan Number</small>
                        {{ $loan->loan_number }}
                    </div>
                    @if($loan->due_date)
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Due Date</small>
                            {{ $loan->due_date->format('d M Y') }}
                        </div>
                    @endif
                    @if($loan->quotation_id)
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Source Quotation</small>
                            <a href="{{ route('quotations.show', $loan->quotation_id) }}">
                                Quotation #{{ $loan->quotation_id }}
                            </a>
                        </div>
                    @endif
                    @if($loan->notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notes</small>
                            {{ $loan->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <strong>Loan task created.</strong> Full loan management (stages, documents, workflow) will be available after Phase 3-5 implementation.
        </div>

    </div>
</div>
@endsection
