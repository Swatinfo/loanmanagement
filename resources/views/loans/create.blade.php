@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create New Loan</h2>
        <a href="{{ route('loans.index') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">

        <form method="POST" action="{{ route('loans.store') }}">
            @csrf

            {{-- Customer Info --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">Customer Information</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="shf-form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="shf-input w-100 @error('customer_name') is-invalid @enderror"
                                   value="{{ old('customer_name') }}" required>
                            @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Customer Type <span class="text-danger">*</span></label>
                            <select name="customer_type" class="shf-input w-100 @error('customer_type') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                @foreach(\App\Models\LoanDetail::CUSTOMER_TYPE_LABELS as $key => $label)
                                    <option value="{{ $key }}" {{ old('customer_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('customer_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" class="shf-input w-100" value="{{ old('customer_phone') }}" maxlength="20">
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Email</label>
                            <input type="email" name="customer_email" class="shf-input w-100" value="{{ old('customer_email') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loan Details --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">2</div>
                    <span class="shf-section-title">Loan Details</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="shf-form-label">Loan Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="loan_amount" class="shf-input w-100 @error('loan_amount') is-invalid @enderror"
                                       value="{{ old('loan_amount') }}" min="1" required>
                            </div>
                            @error('loan_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bankSelect" class="shf-input w-100" required>
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="productSelect" class="shf-input w-100" required>
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-bank-id="{{ $product->bank_id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="shf-input w-100" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assignment --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">3</div>
                    <span class="shf-section-title">Assignment</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="shf-form-label">Assigned Advisor</label>
                            <select name="assigned_advisor" class="shf-input w-100">
                                <option value="">-- Current User ({{ auth()->user()->name }}) --</option>
                                @foreach($advisors as $advisor)
                                    <option value="{{ $advisor->id }}" {{ old('assigned_advisor') == $advisor->id ? 'selected' : '' }}>
                                        {{ $advisor->name }} ({{ $advisor->task_role_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="shf-form-label">Notes</label>
                            <textarea name="notes" class="shf-input w-100" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-4">
                <a href="{{ route('loans.index') }}" class="btn-accent-outline"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                <button type="submit" class="btn-accent" style="padding: 10px 24px; font-weight: 600;"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create Loan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // Product dependent dropdown
    var $bank = $('#bankSelect'), $product = $('#productSelect');
    var allOptions = $product.find('option').clone();
    $bank.on('change', function() {
        var bankId = $(this).val();
        $product.empty().append('<option value="">-- Select Product --</option>');
        if (bankId) {
            allOptions.each(function() {
                if ($(this).data('bank-id') == bankId) $product.append($(this).clone());
            });
        } else {
            $product.append(allOptions.clone());
        }
    });
    if ($bank.val()) $bank.trigger('change');
});
</script>
@endpush
