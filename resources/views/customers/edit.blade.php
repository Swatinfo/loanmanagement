@extends('layouts.app')
@section('title', 'Edit ' . $customer->customer_name . ' — Customer')

@section('header')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('customers.show', $customer) }}" class="shf-header-back">
            <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="font-display fw-semibold text-white shf-page-title mb-0">Edit Customer</h2>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5 shf-max-w-lg">

            <form method="POST" action="{{ route('customers.update', $customer) }}" class="shf-section">
                @csrf
                @method('PUT')

                <div class="shf-section-header">
                    <span class="shf-section-title">Customer Details</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="shf-form-label">Name *</label>
                            <input type="text" name="customer_name" class="shf-input"
                                value="{{ old('customer_name', $customer->customer_name) }}" required maxlength="255">
                            @error('customer_name')
                                <div class="shf-validation-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="shf-form-label">Mobile</label>
                            <input type="text" name="mobile" class="shf-input"
                                value="{{ old('mobile', $customer->mobile) }}" maxlength="20">
                            @error('mobile')
                                <div class="shf-validation-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="shf-form-label">Email</label>
                            <input type="email" name="email" class="shf-input"
                                value="{{ old('email', $customer->email) }}" maxlength="255">
                            @error('email')
                                <div class="shf-validation-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="shf-form-label">PAN</label>
                            <input type="text" name="pan_number" class="shf-input shf-font-mono text-uppercase"
                                value="{{ old('pan_number', $customer->pan_number) }}" maxlength="10"
                                pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]" placeholder="AAAAA9999A">
                            @error('pan_number')
                                <div class="shf-validation-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="shf-form-label">Date of Birth</label>
                            <input type="text" name="date_of_birth" class="shf-input shf-datepicker-past"
                                autocomplete="off" placeholder="dd/mm/yyyy"
                                value="{{ old('date_of_birth', $customer->date_of_birth?->format('d/m/Y')) }}">
                            @error('date_of_birth')
                                <div class="shf-validation-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="shf-section-body shf-border-top-light d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.show', $customer) }}" class="shf-btn-gray">Cancel</a>
                    <button type="submit" class="btn-accent">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
@endsection
