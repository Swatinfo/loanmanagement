@extends('layouts.app')
@section('title', 'Disbursement — SHF')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white shf-page-title"><svg class="shf-header-icon" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg> Disbursement — {{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg
                class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg> Back</a>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5 shf-max-w-xl">

            @if (session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            @if ($isLocked)
                <div
                    class="alert alert-{{ $loan->status === 'rejected' ? 'danger' : ($loan->status === 'cancelled' ? 'warning' : 'success') }} mb-4">
                    <strong>Loan {{ ucfirst($loan->status) }}.</strong> Details are read-only.
                </div>
            @endif

            <form method="POST" action="{{ route('loans.disbursement.store', $loan) }}">
                @csrf
                <fieldset {{ $isLocked ? 'disabled' : '' }}>
                    <div class="shf-section mb-4">
                        <div class="shf-section-header"><span class="shf-section-title">Disbursement Method</span></div>
                        <div class="shf-section-body">
                            <div class="row g-3">
                                @foreach (\App\Models\DisbursementDetail::TYPES as $key => $label)
                                    <div class="col-sm-6">
                                        <div
                                            class="form-check p-3 border rounded {{ old('disbursement_type', $disbursement?->disbursement_type) === $key ? 'border-primary' : '' }}">
                                            <input class="form-check-input" type="radio" name="disbursement_type"
                                                value="{{ $key }}" id="type_{{ $key }}"
                                                {{ old('disbursement_type', $disbursement?->disbursement_type ?? 'fund_transfer') === $key ? 'checked' : '' }}
                                                required>
                                            <label class="form-check-label"
                                                for="type_{{ $key }}"><strong>{{ $label }}</strong></label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="shf-section mb-4">
                        <div class="shf-section-header"><span class="shf-section-title">Details</span></div>
                        <div class="shf-section-body">
                            @if ($sanctionedAmount)
                                <div class="alert alert-info py-2 mb-3 shf-text-sm">
                                    <strong>Sanctioned Amount:</strong> ₹ {{ number_format((float) $sanctionedAmount) }}
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="shf-form-label">Amount <span class="text-danger">*</span></label>
                                    <div class="shf-amount-wrap">
                                        <div class="input-group"><span class="input-group-text">₹</span>
                                            <input type="text" id="disbAmount" class="shf-input shf-amount-input"
                                                value="{{ old('amount_disbursed', $disbursement?->amount_disbursed ?? ($sanctionedAmount ?? $loan->loan_amount)) }}"
                                                required data-max-amount="{{ $sanctionedAmount ?? '' }}">
                                            <input type="hidden" name="amount_disbursed" class="shf-amount-raw"
                                                value="{{ old('amount_disbursed', $disbursement?->amount_disbursed ?? ($sanctionedAmount ?? $loan->loan_amount)) }}">
                                        </div>
                                        <div class="shf-text-xs text-muted mt-1" data-amount-words></div>
                                        <div class="shf-text-xs text-warning mt-1 d-none" id="amountExceedError"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="shf-form-label">Disbursement Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="disbursement_date" class="shf-input shf-datepicker-custom"
                                        data-min-date="{{ $disbursement?->created_at ? $disbursement->created_at->format('d/m/Y') : now()->subDays(3)->format('d/m/Y') }}"
                                        data-max-date="{{ $disbursement?->created_at ? now()->format('d/m/Y') : now()->addDays(3)->format('d/m/Y') }}"
                                        value="{{ old('disbursement_date', $disbursement?->disbursement_date?->format('d/m/Y') ?? now()->format('d/m/Y')) }}"
                                        required>
                                </div>

                                <div class="col-sm-6">
                                    <label class="shf-form-label">Loan Account Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="shf-input w-100"
                                        value="{{ old('bank_account_number', $disbursement?->bank_account_number) }}">
                                </div>

                                {{-- Cheque fields --}}
                                <div class="col-12 shf-cheque-fields shf-collapse-hidden">
                                    <label class="shf-form-label">Cheques</label>
                                    <div class="row g-2 mb-1 shf-text-xs text-muted d-none d-sm-flex">
                                        <div class="col-sm-3">Name</div>
                                        <div class="col-sm-3">Cheque No.</div>
                                        <div class="col-sm-2">Date</div>
                                        <div class="col-sm-2">Amount</div>
                                    </div>
                                    <div id="chequeList">
                                        @php $existingCheques = old('cheques', $disbursement?->cheques ?? []); @endphp
                                        @if (!empty($existingCheques))
                                            @foreach ($existingCheques as $i => $chq)
                                                <div class="row g-2 mb-2 cheque-row">
                                                    <div class="col-sm-3">
                                                        <input type="text"
                                                            name="cheques[{{ $i }}][cheque_name]"
                                                            class="shf-input shf-input-sm" placeholder="Name"
                                                            value="{{ $chq['cheque_name'] ?? '' }}" required>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <input type="text"
                                                            name="cheques[{{ $i }}][cheque_number]"
                                                            class="shf-input shf-input-sm" placeholder="Cheque Number"
                                                            value="{{ $chq['cheque_number'] ?? '' }}" required>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <input type="text"
                                                            name="cheques[{{ $i }}][cheque_date]"
                                                            class="shf-input shf-input-sm shf-datepicker-custom"
                                                            data-min-date="{{ $disbursement?->created_at ? $disbursement->created_at->format('d/m/Y') : now()->subDays(30)->format('d/m/Y') }}"
                                                            data-max-date="{{ $disbursement?->created_at ? now()->format('d/m/Y') : now()->addDays(30)->format('d/m/Y') }}"
                                                            placeholder="dd/mm/yyyy"
                                                            value="{{ $chq['cheque_date'] ?? '' }}" required>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="shf-amount-wrap">
                                                            <div class="input-group input-group-sm"><span
                                                                    class="input-group-text">₹</span>
                                                                <input type="text"
                                                                    class="shf-input shf-input-sm shf-amount-input cheque-amount-display"
                                                                    value="{{ $chq['cheque_amount'] ?? '' }}" required>
                                                                <input type="hidden"
                                                                    name="cheques[{{ $i }}][cheque_amount]"
                                                                    class="shf-amount-raw cheque-amount"
                                                                    value="{{ $chq['cheque_amount'] ?? '' }}">
                                                            </div>
                                                            <div class="shf-text-2xs text-muted mt-1" data-amount-words>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <button type="button"
                                                            class="btn-accent-outline btn-accent-sm remove-cheque w-100"><svg
                                                                class="shf-btn-icon shf-icon-2xs" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" id="addCheque" class="btn-accent-sm mt-1 shf-text-xs"><svg
                                            class="shf-icon-2xs" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg> Add Cheque</button>
                                    <div class="mt-2">
                                        <small class="text-muted">Cheque Total: <strong id="chequeTotal">₹
                                                0</strong></small>
                                        <small class="text-danger d-none" id="chequeTotalError"></small>
                                    </div>
                                    <div class="text-danger shf-text-xs mt-1 d-none" id="chequeValidationError"></div>
                                </div>

                                <div class="col-12">
                                    <label class="shf-form-label">Notes</label>
                                    <textarea name="notes" class="shf-input w-100" rows="2">{{ old('notes', $disbursement?->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <div class="shf-form-actions d-flex justify-content-end gap-3 mb-4">
                    <a href="{{ route('loans.stages', $loan) }}" class="btn-accent-outline"><svg class="shf-icon-md"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg> {{ $isLocked ? 'Back' : 'Cancel' }}</a>
                    @if (!$isLocked)
                        <button type="submit" class="btn-accent" style="padding: 10px 24px;"><svg class="shf-icon-md"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg> Process Disbursement</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('.shf-datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
            $('.shf-datepicker-custom').each(function() {
                var opts = {
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true
                };
                if ($(this).data('min-date')) opts.startDate = $(this).data('min-date');
                if ($(this).data('max-date')) opts.endDate = $(this).data('max-date');
                $(this).datepicker(opts);
            });

            var chequeIndex = {{ count($existingCheques ?? []) }};

            function toggleFields() {
                var type = $('input[name="disbursement_type"]:checked').val();
                $('.shf-cheque-fields').toggle(type === 'cheque');
            }

            $('input[name="disbursement_type"]').on('change', function() {
                $('.form-check.border').removeClass('border-primary');
                $(this).closest('.form-check').addClass('border-primary');
                toggleFields();
            });
            toggleFields();

            // Add cheque row
            $('#addCheque').on('click', function() {
                var html = '<div class="row g-2 mb-2 cheque-row">' +
                    '<div class="col-sm-3"><input type="text" name="cheques[' + chequeIndex +
                    '][cheque_name]" class="shf-input shf-input-sm" placeholder="Name" required></div>' +
                    '<div class="col-sm-3"><input type="text" name="cheques[' + chequeIndex +
                    '][cheque_number]" class="shf-input shf-input-sm" placeholder="Cheque Number" required></div>' +
                    '<div class="col-sm-2"><input type="text" name="cheques[' + chequeIndex +
                    '][cheque_date]" class="shf-input shf-input-sm shf-datepicker-custom" data-min-date="{{ now()->subDays(30)->format('d/m/Y') }}" data-max-date="{{ now()->addDays(30)->format('d/m/Y') }}" placeholder="dd/mm/yyyy" required></div>' +
                    '<div class="col-sm-2"><div class="shf-amount-wrap"><div class="input-group input-group-sm"><span class="input-group-text">₹</span><input type="text" class="shf-input shf-input-sm shf-amount-input cheque-amount-display" required></div><input type="hidden" name="cheques[' +
                    chequeIndex +
                    '][cheque_amount]" class="shf-amount-raw cheque-amount"><div class="shf-text-2xs text-muted mt-1" data-amount-words></div></div></div>' +
                    '<div class="col-sm-2"><button type="button" class="btn-accent-outline btn-accent-sm remove-cheque w-100"><svg class="shf-btn-icon shf-icon-2xs" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>' +
                    '</div>';
                $('#chequeList').append(html);
                var $newRow = $('#chequeList .cheque-row:last');
                $newRow.find('.shf-datepicker-custom').each(function() {
                    var opts = {
                        format: 'dd/mm/yyyy',
                        autoclose: true,
                        todayHighlight: true
                    };
                    if ($(this).data('min-date')) opts.startDate = $(this).data('min-date');
                    if ($(this).data('max-date')) opts.endDate = $(this).data('max-date');
                    $(this).datepicker(opts);
                });
                SHF.initAmountFields();
                chequeIndex++;
                updateChequeTotal();
            });

            // Remove cheque row
            $(document).on('click', '.remove-cheque', function() {
                $(this).closest('.cheque-row').remove();
                updateChequeTotal();
            });

            // Update cheque total
            function updateChequeTotal() {
                var total = 0;
                $('.cheque-amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#chequeTotal').text('₹ ' + total.toLocaleString('en-IN'));
                var disbAmt = parseFloat($('[name="amount_disbursed"]').val()) || 0;
                var maxAmount = parseFloat($('#disbAmount').data('max-amount')) || 0;
                var $error = $('#chequeTotalError');
                if (total > disbAmt) {
                    $error.text(' (exceeds disbursement amount!)').removeClass('d-none').addClass('text-danger').removeClass('text-warning');
                } else if (maxAmount && total > maxAmount) {
                    $error.text(' (warning: exceeds sanctioned amount)').removeClass('d-none').addClass('text-warning').removeClass('text-danger');
                } else {
                    $error.addClass('d-none');
                }
            }

            $(document).on('input', '.cheque-amount-display', function() {
                updateChequeTotal();
            });
            $('#disbAmount').on('input', function() {
                updateChequeTotal();
                validateDisbursementAmount();
            });
            updateChequeTotal();

            // Show warning (not blocking) if disbursement exceeds sanctioned amount
            function validateDisbursementAmount() {
                var maxAmount = parseFloat($('#disbAmount').data('max-amount')) || 0;
                if (!maxAmount) return true;
                var amount = parseFloat($('[name="amount_disbursed"]').val()) || 0;
                var $error = $('#amountExceedError');
                if (amount > maxAmount) {
                    $error.text('Warning: Amount exceeds sanctioned amount (₹ ' + maxAmount.toLocaleString(
                        'en-IN') + ')').removeClass('d-none');
                    return true; // allow saving, just warn
                } else {
                    $error.addClass('d-none');
                    return true;
                }
            }
            validateDisbursementAmount();

            // Clear inline errors on input
            $(document).on('input change', '.is-invalid', function() {
                $(this).removeClass('is-invalid');
                $(this).closest('.col-sm-6, .col-sm-3, .col-sm-2').find('.shf-field-error').remove();
            });

            // Client-side validation
            $('form').on('submit', function(e) {
                var $form = $(this);
                var valid = true;
                var firstError = null;

                // Clear previous errors
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.shf-field-error').remove();
                $('#chequeValidationError').addClass('d-none');

                function addError($input, msg) {
                    $input.addClass('is-invalid');
                    var $col = $input.closest('.col-sm-6, .col-sm-3, .col-sm-2, .shf-amount-wrap');
                    if (!$col.find('.shf-field-error').length) {
                        $col.append('<div class="shf-field-error text-danger shf-text-xs mt-1">' + msg +
                            '</div>');
                    }
                    if (!firstError) firstError = $input;
                    valid = false;
                }

                // Amount
                var amount = parseFloat($('[name="amount_disbursed"]').val()) || 0;
                if (amount <= 0) {
                    addError($('#disbAmount'), 'Amount is required');
                }
                // Show warning if exceeds sanctioned (non-blocking)
                validateDisbursementAmount();

                // Disbursement date
                var $dateInput = $('[name="disbursement_date"]');
                if (!$dateInput.val() || !$dateInput.val().trim()) {
                    addError($dateInput, 'Disbursement Date is required');
                }

                // Bank account number (required)
                var type = $('input[name="disbursement_type"]:checked').val();
                var $accountInput = $('[name="bank_account_number"]');
                if (!$accountInput.val() || !$accountInput.val().trim()) {
                    addError($accountInput, 'Loan Account Number is required');
                }

                // Cheque validation
                if (type === 'cheque') {
                    var $rows = $('.cheque-row');
                    if ($rows.length === 0) {
                        $('#chequeValidationError').text('Please add at least one cheque entry.')
                            .removeClass('d-none');
                        if (!firstError) firstError = $('#addCheque');
                        valid = false;
                    } else {
                        var chequeTotal = 0;
                        $rows.each(function() {
                            var $row = $(this);
                            var $name = $row.find('[name$="[cheque_name]"]');
                            var $num = $row.find('[name$="[cheque_number]"]');
                            var $date = $row.find('[name$="[cheque_date]"]');
                            var $amt = $row.find('.cheque-amount');
                            var $amtDisplay = $row.find('.cheque-amount-display');
                            if (!$name.val() || !$name.val().trim()) {
                                $name.addClass('is-invalid');
                                if (!firstError) firstError = $name;
                                valid = false;
                            }
                            if (!$num.val() || !$num.val().trim()) {
                                $num.addClass('is-invalid');
                                if (!firstError) firstError = $num;
                                valid = false;
                            }
                            if (!$date.val() || !$date.val().trim()) {
                                $date.addClass('is-invalid');
                                if (!firstError) firstError = $date;
                                valid = false;
                            }
                            var amt = parseFloat($amt.val()) || 0;
                            if (amt <= 0) {
                                $amtDisplay.addClass('is-invalid');
                                if (!firstError) firstError = $amtDisplay;
                                valid = false;
                            }
                            chequeTotal += amt;
                        });
                        if (!valid && !$('#chequeValidationError').is(':visible')) {
                            $('#chequeValidationError').text('Please fill in all cheque details.')
                                .removeClass('d-none');
                        }
                        var disbAmt = parseFloat($('[name="amount_disbursed"]').val()) || 0;
                        var maxAmount = parseFloat($('#disbAmount').data('max-amount')) || 0;
                        if (valid && chequeTotal > disbAmt) {
                            $('#chequeValidationError').text('Cheque total (₹ ' + chequeTotal
                                .toLocaleString('en-IN') + ') exceeds disbursement amount (₹ ' + disbAmt
                                .toLocaleString('en-IN') + ').').removeClass('d-none');
                            if (!firstError) firstError = $('.cheque-amount').first();
                            valid = false;
                        }
                        // Warning only (non-blocking) if cheque total exceeds sanctioned
                        if (valid && maxAmount && chequeTotal > maxAmount) {
                            $('#chequeValidationError').text('Warning: Cheque total (₹ ' + chequeTotal
                                .toLocaleString('en-IN') + ') exceeds sanctioned amount (₹ ' + maxAmount
                                .toLocaleString('en-IN') + ').').removeClass('d-none').removeClass('text-danger').addClass('text-warning');
                        }
                    }
                }

                if (!valid) {
                    e.preventDefault();
                    if (firstError && firstError.length) {
                        firstError[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                }
            });
        });
    </script>
@endpush
