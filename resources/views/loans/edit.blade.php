@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit Loan #{{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.show', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">

        <form method="POST" action="{{ route('loans.update', $loan) }}">
            @csrf
            @method('PUT')

            <div class="shf-section mb-4">
                <div class="shf-section-header"><div class="shf-section-number">1</div><span class="shf-section-title">Customer Information</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="shf-form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="shf-input w-100" value="{{ old('customer_name', $loan->customer_name) }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Customer Type <span class="text-danger">*</span></label>
                            <select name="customer_type" class="shf-input w-100" required>
                                @foreach(\App\Models\LoanDetail::CUSTOMER_TYPE_LABELS as $key => $label)
                                    <option value="{{ $key }}" {{ old('customer_type', $loan->customer_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" class="shf-input w-100" value="{{ old('customer_phone', $loan->customer_phone) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Email</label>
                            <input type="email" name="customer_email" class="shf-input w-100" value="{{ old('customer_email', $loan->customer_email) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header"><div class="shf-section-number">2</div><span class="shf-section-title">Loan Details</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="shf-form-label">Loan Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="loan_amount" class="shf-input w-100" value="{{ old('loan_amount', $loan->loan_amount) }}" min="1" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bankSelect" class="shf-input w-100" required>
                                <option value="">-- Select --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ old('bank_id', $loan->bank_id) == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="productSelect" class="shf-input w-100" required>
                                <option value="">-- Select --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-bank-id="{{ $product->bank_id }}" {{ old('product_id', $loan->product_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="shf-form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="shf-input w-100" required>
                                <option value="">-- Select --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $loan->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header"><div class="shf-section-number">3</div><span class="shf-section-title">Assignment</span></div>
                <div class="shf-section-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="shf-form-label">Assigned Advisor</label>
                            <select name="assigned_advisor" class="shf-input w-100">
                                <option value="">-- None --</option>
                                @foreach($advisors as $advisor)
                                    <option value="{{ $advisor->id }}" {{ old('assigned_advisor', $loan->assigned_advisor) == $advisor->id ? 'selected' : '' }}>
                                        {{ $advisor->name }} ({{ $advisor->task_role_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="shf-form-label">Notes</label>
                            <textarea name="notes" class="shf-input w-100" rows="3">{{ old('notes', $loan->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mb-4">
                <a href="{{ route('loans.show', $loan) }}" class="btn-accent-outline"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                <button type="submit" class="btn-accent" style="padding: 10px 24px; font-weight: 600;"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Update Loan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var $bank = $('#bankSelect'), $product = $('#productSelect');
    var allOptions = $product.find('option').clone();
    var currentProduct = '{{ old("product_id", $loan->product_id) }}';
    $bank.on('change', function() {
        var bankId = $(this).val();
        $product.empty().append('<option value="">-- Select --</option>');
        if (bankId) {
            allOptions.each(function() { if ($(this).data('bank-id') == bankId) $product.append($(this).clone()); });
        } else {
            $product.append(allOptions.clone());
        }
        if (currentProduct) { $product.val(currentProduct); currentProduct = null; }
    });
    if ($bank.val()) $bank.trigger('change');
});
</script>
@endpush
