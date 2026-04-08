@extends('layouts.app')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Quotation #{{ $quotation->id }}
        </h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
            @if(!$quotation->is_converted && auth()->user()->hasPermission('convert_to_loan'))
                <a href="{{ route('quotations.convert', $quotation) }}" class="btn-accent btn-accent-sm">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Convert to Loan
                </a>
            @elseif($quotation->is_converted)
                <a href="{{ route('loans.show', $quotation->loan_id) }}" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Loan #{{ $quotation->loan->loan_number }}
                </a>
            @endif
            @if(auth()->user()->hasPermission('download_pdf'))
                <a href="{{ route('quotations.download', $quotation) }}" class="btn-accent btn-accent-sm">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            @endif
            @if(auth()->user()->hasPermission('delete_quotations'))
                <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                      class="shf-confirm-delete" data-confirm-title="Delete this quotation?" data-confirm-text="This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #c0392b, #e74c3c);">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            <!-- Customer & Loan Info -->
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">Customer & Loan Details</span>
                </div>
                <div class="shf-section-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <div class="mb-3">
                                    <dt class="shf-form-label">Customer Name</dt>
                                    <dd class="mt-1 small fw-medium">{{ $quotation->customer_name }}</dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Customer Type</dt>
                                    <dd class="mt-1">
                                        <span class="shf-badge
                                            {{ $quotation->customer_type === 'proprietor' ? 'shf-badge-green' :
                                               ($quotation->customer_type === 'partnership_llp' ? 'shf-badge-blue' :
                                               ($quotation->customer_type === 'pvt_ltd' ? 'shf-badge-orange' :
                                               ($quotation->customer_type === 'salaried' ? 'shf-badge-purple' : 'shf-badge-gray'))) }}">
                                            {{ $quotation->getTypeLabel() }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Loan Amount</dt>
                                    <dd class="mt-1 font-display fw-bold" style="font-size: 1.125rem; color: #f15a29;">{{ $quotation->formatted_amount }}</dd>
                                </div>
                                @if($quotation->location)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Location</dt>
                                        <dd class="mt-1 small">{{ $quotation->location->parent?->name ? $quotation->location->parent->name . ' / ' : '' }}{{ $quotation->location->name }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <div class="mb-3">
                                    <dt class="shf-form-label">Created</dt>
                                    <dd class="mt-1 small">{{ $quotation->created_at ? $quotation->created_at->format('d M Y, h:i A') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Created By</dt>
                                    <dd class="mt-1 small">{{ $quotation->user?->name ?? 'System / Legacy' }}</dd>
                                </div>
                                @if($quotation->prepared_by_name)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Prepared By</dt>
                                        <dd class="mt-1 small">
                                            {{ $quotation->prepared_by_name }}
                                            @if($quotation->prepared_by_mobile)
                                                <span style="color: #6b7280; margin-left: 4px;">({{ $quotation->prepared_by_mobile }})</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                                @if($quotation->pdf_filename)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">PDF File</dt>
                                        <dd class="mt-1 small text-truncate" style="color: #6b7280;" title="{{ $quotation->pdf_filename }}">{{ $quotation->pdf_filename }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            @if($quotation->documents->count() > 0)
                <div class="shf-section mb-4">
                    <div class="shf-section-header">
                        <div class="shf-section-number">2</div>
                        <span class="shf-section-title">Required Documents</span>
                    </div>
                    <div class="shf-section-body">
                        <div class="shf-doc-grid">
                            @foreach($quotation->documents as $doc)
                                <div class="shf-doc-item checked">
                                    <svg style="width:16px;height:16px;color:#f15a29;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="small">
                                        <span class="fw-medium">{{ $doc->document_name_en }}</span>
                                        @if($doc->document_name_gu)
                                            <span style="color: #6b7280; margin-left: 4px;">/ {{ $doc->document_name_gu }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Banks & EMI Details -->
            @if($quotation->banks->count() > 0)
                @foreach($quotation->banks as $bankIdx => $bank)
                    <div class="shf-section mb-4">
                        <div class="shf-section-header">
                            <div class="shf-section-number">{{ ($quotation->documents->count() > 0 ? 3 : 2) + $bankIdx }}</div>
                            <span class="shf-section-title">{{ $bank->bank_name }}</span>
                            <span class="ms-auto small font-display" style="color: rgba(255,255,255,0.6);">
                                ROI: {{ number_format($bank->roi_min, 2) }}%
                                @if($bank->roi_max != $bank->roi_min)
                                    - {{ number_format($bank->roi_max, 2) }}%
                                @endif
                            </span>
                        </div>
                        <div class="shf-section-body">
                            <!-- Charges -->
                            <div class="mb-4">
                                <h4 class="shf-form-label mb-3">Charges Breakdown</h4>
                                <div class="row g-3">
                                    @php
                                        $chargeItems = [
                                            ['label' => 'PF Charge', 'value' => $bank->pf_charge],
                                            ['label' => 'Admin Charges', 'value' => $bank->admin_charge],
                                            ['label' => 'Stamp Paper and Notary Charges', 'value' => $bank->stamp_notary],
                                            ['label' => 'IOM Stamp Paper Charges', 'value' => $bank->iom_charge],
                                            ['label' => 'Registration Fee', 'value' => $bank->registration_fee],
                                            ['label' => 'Advocate Fees', 'value' => $bank->advocate_fees],
                                            ['label' => 'TC Report Charges', 'value' => $bank->tc_report],
                                        ];
                                        if ($bank->extra1_name) {
                                            $chargeItems[] = ['label' => $bank->extra1_name, 'value' => $bank->extra1_amount];
                                        }
                                        if ($bank->extra2_name) {
                                            $chargeItems[] = ['label' => $bank->extra2_name, 'value' => $bank->extra2_amount];
                                        }
                                    @endphp
                                    @foreach($chargeItems as $item)
                                        @if($item['value'] > 0)
                                            <div class="col-sm-6 col-md-3">
                                                <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px;">
                                                    <div class="small" style="color: #6b7280;">{{ $item['label'] }}</div>
                                                    <div class="small fw-semibold font-display mt-1">₹ {{ number_format($item['value']) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <div style="background: var(--accent-dim); border: 1px solid var(--accent); border-radius: 8px; padding: 8px 16px;">
                                        <span class="small fw-semibold" style="color: #f15a29;">Total Charges:</span>
                                        <span class="small fw-bold font-display ms-2" style="color: #f15a29;">₹ {{ number_format($bank->total_charges) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- EMI Table -->
                            @if($bank->emiEntries->count() > 0)
                                <div>
                                    <h4 class="shf-form-label mb-3">EMI Schedule</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tenure</th>
                                                    <th class="text-end">Monthly EMI</th>
                                                    <th class="text-end">Total Interest</th>
                                                    <th class="text-end">Total Payment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bank->emiEntries->sortBy('tenure_years') as $emi)
                                                    <tr>
                                                        <td class="fw-medium">{{ $emi->tenure_years }} years</td>
                                                        <td class="text-end fw-semibold font-display">₹ {{ number_format($emi->monthly_emi) }}</td>
                                                        <td class="text-end" style="color: #6b7280;">₹ {{ number_format($emi->total_interest) }}</td>
                                                        <td class="text-end fw-semibold font-display">₹ {{ number_format($emi->total_payment) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Additional Notes -->
            @if($quotation->additional_notes)
                <div class="shf-section mt-4">
                    <div class="shf-section-header">
                        <div class="shf-section-number">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <span class="shf-section-title">Additional Notes</span>
                    </div>
                    <div class="shf-section-body">
                        <p class="small mb-0" style="color: #374151; white-space: pre-line;">{{ $quotation->additional_notes }}</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
