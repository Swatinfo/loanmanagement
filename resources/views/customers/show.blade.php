@extends('layouts.app')
@section('title', $customer->customer_name . ' — Customer')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('customers.index') }}" class="shf-header-back">
                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-display fw-semibold text-white shf-page-title mb-0">{{ $customer->customer_name }}</h2>
        </div>
        @if ($customer->isEditableBy(auth()->user()))
            <a href="{{ route('customers.edit', $customer) }}" class="btn-accent-outline-white btn-accent-sm">
                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Basic info --}}
            <div class="shf-section mb-3">
                <div class="shf-section-header">
                    <span class="shf-section-title">Details</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">Name</div>
                            <div class="fw-semibold">{{ $customer->customer_name }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">Mobile</div>
                            <div>{{ $customer->mobile ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">Email</div>
                            <div>{{ $customer->email ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">PAN</div>
                            <div class="shf-font-mono">{{ $customer->pan_number ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">Date of Birth</div>
                            <div>{{ $customer->date_of_birth?->format('d M Y') ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="shf-form-label">Created</div>
                            <div>{{ $customer->created_at?->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loans --}}
            <div class="shf-section">
                <div class="shf-section-header">
                    <span class="shf-section-title">Loans ({{ $customer->loans->count() }})</span>
                </div>
                <div class="shf-section-body">
                    @forelse ($customer->loans as $loan)
                        <div class="d-flex align-items-center justify-content-between py-2 shf-border-top-light">
                            <div>
                                <a href="{{ route('loans.show', $loan) }}" class="fw-semibold text-decoration-none">
                                    #{{ $loan->loan_number }}
                                </a>
                                <span class="shf-text-sm shf-text-gray ms-2">
                                    {{ $loan->bank?->name ?? $loan->bank_name }}
                                    @if ($loan->branch)
                                        • {{ $loan->branch->name }}
                                    @endif
                                </span>
                            </div>
                            <span class="shf-badge shf-badge-{{ $loan->status === 'active' ? 'orange' : ($loan->status === 'completed' ? 'green' : 'gray') }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="shf-text-gray mb-0">No loans linked to this customer yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
