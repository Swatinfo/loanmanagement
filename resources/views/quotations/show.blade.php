@extends('layouts.app')
@section('title', 'Quotation Details — SHF')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Quotation #{{ $quotation->id }}
        </h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
            @if(!$quotation->is_converted && !$quotation->is_cancelled && auth()->user()->hasPermission('convert_to_loan'))
                <a href="{{ route('quotations.convert', $quotation) }}" class="btn-accent btn-accent-sm">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Convert to Loan
                </a>
            @elseif($quotation->is_converted)
                <a href="{{ route('loans.show', $quotation->loan_id) }}" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Loan #{{ $quotation->loan->loan_number }}
                </a>
            @endif
            @if(auth()->user()->hasPermission('download_pdf_branded'))
                <a href="{{ route('quotations.download', [$quotation, 'branded' => 1]) }}" class="btn-accent btn-accent-sm">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Branded PDF
                </a>
            @endif
            @if(auth()->user()->hasPermission('download_pdf_plain'))
                <a href="{{ route('quotations.download', [$quotation, 'branded' => 0]) }}" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #6b7280, #9ca3af);">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Plain PDF
                </a>
            @endif
            @if(auth()->user()->isSuperAdmin())
                <div class="dropdown d-inline-block">
                    <button class="btn-accent btn-accent-sm dropdown-toggle" style="background: linear-gradient(135deg, #7c3aed, #a855f7);" data-bs-toggle="dropdown">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Preview HTML
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('quotations.preview-html', [$quotation, 'branded' => 1]) }}" target="_blank">Branded HTML</a></li>
                        <li><a class="dropdown-item" href="{{ route('quotations.preview-html', [$quotation, 'branded' => 0]) }}" target="_blank">Plain HTML</a></li>
                    </ul>
                </div>
            @endif
            @if($quotation->status === \App\Models\Quotation::STATUS_ACTIVE && !$quotation->is_converted && auth()->user()->hasPermission('hold_quotation'))
                <button type="button" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #d97706, #f59e0b);" data-bs-toggle="modal" data-bs-target="#holdModal">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Put on Hold
                </button>
            @endif
            @if($quotation->is_on_hold && auth()->user()->hasPermission('resume_quotation'))
                <form method="POST" action="{{ route('quotations.resume', $quotation) }}" class="shf-confirm-delete" data-confirm-title="Resume this quotation?" data-confirm-text="Move it back to active." data-confirm-button="Yes, resume" data-confirm-icon="question">
                    @csrf
                    <button type="submit" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #16a34a, #22c55e);">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Resume
                    </button>
                </form>
            @endif
            @if(!$quotation->is_cancelled && !$quotation->is_converted && auth()->user()->hasPermission('cancel_quotation'))
                <button type="button" class="btn-accent btn-accent-sm shf-btn-danger-alt" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Cancel
                </button>
            @endif
            @if(auth()->user()->hasPermission('delete_quotations'))
                <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                      class="shf-confirm-delete" data-confirm-title="Delete this quotation?" data-confirm-text="This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-accent btn-accent-sm shf-btn-danger-alt">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            @if($quotation->is_on_hold)
                <div class="shf-section mb-4" style="border-left: 4px solid #d97706;">
                    <div class="shf-section-body">
                        <div class="d-flex align-items-start gap-3">
                            <svg class="shf-icon-lg" fill="none" stroke="#d97706" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <div class="fw-semibold" style="color:#d97706;">On Hold — {{ $quotation->hold_reason_label }}</div>
                                <div class="small shf-text-gray mt-1">
                                    Follow-up: <strong>{{ $quotation->hold_follow_up_date?->format('d M Y') }}</strong>
                                    &middot; Held by {{ $quotation->heldBy?->name ?? '—' }}
                                    on {{ $quotation->held_at?->format('d M Y, h:i A') }}
                                </div>
                                @if($quotation->hold_note)
                                    <div class="small mt-2" style="white-space: pre-line;">{{ $quotation->hold_note }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($quotation->is_cancelled)
                <div class="shf-section mb-4" style="border-left: 4px solid #c0392b;">
                    <div class="shf-section-body">
                        <div class="d-flex align-items-start gap-3">
                            <svg class="shf-icon-lg" fill="none" stroke="#c0392b" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <div class="fw-semibold" style="color:#c0392b;">Cancelled — {{ $quotation->cancel_reason_label }}</div>
                                <div class="small shf-text-gray mt-1">
                                    Cancelled by {{ $quotation->cancelledBy?->name ?? '—' }}
                                    on {{ $quotation->cancelled_at?->format('d M Y, h:i A') }}
                                </div>
                                @if($quotation->cancel_note)
                                    <div class="small mt-2" style="white-space: pre-line;">{{ $quotation->cancel_note }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                                        <dd class="mt-1 small text-truncate shf-text-gray" title="{{ $quotation->pdf_filename }}">{{ $quotation->pdf_filename }}</dd>
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
                                                    <div class="small shf-text-gray">{{ $item['label'] }}</div>
                                                    <div class="small fw-semibold font-display mt-1">₹ {{ number_format($item['value']) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <div style="background: var(--accent-dim); border: 1px solid var(--accent); border-radius: 8px; padding: 8px 16px;">
                                        <span class="small fw-semibold shf-text-accent">Total Charges:</span>
                                        <span class="small fw-bold font-display ms-2 shf-text-accent">₹ {{ number_format($bank->total_charges) }}</span>
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
                                                        <td class="text-end shf-text-gray">₹ {{ number_format($emi->total_interest) }}</td>
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
                            <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
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

    {{-- Hold Modal --}}
    @if($quotation->status === \App\Models\Quotation::STATUS_ACTIVE && !$quotation->is_converted && auth()->user()->hasPermission('hold_quotation'))
        <div class="modal fade" id="holdModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: var(--radius); border: 1px solid var(--border);">
                    <form method="POST" action="{{ route('quotations.hold', $quotation) }}" id="holdForm">
                        @csrf
                        <div class="modal-header" style="background: var(--primary-dark-solid); color: #fff;">
                            <h5 class="modal-title font-display">Put Quotation on Hold</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small mb-3 shf-text-gray">A follow-up DVR will be automatically created with the date you provide below.</p>
                            <div class="mb-3">
                                <label class="shf-form-label">Reason <span class="text-danger">*</span></label>
                                <select name="reason_key" class="shf-input" required>
                                    <option value="">Select a reason…</option>
                                    @php
                                        $groupedHold = collect($holdReasons)->groupBy(fn ($r) => $r['group'] ?? 'Other')->sortKeys();
                                    @endphp
                                    @foreach($groupedHold as $groupName => $groupReasons)
                                        <optgroup label="{{ $groupName }}">
                                            @foreach($groupReasons as $reason)
                                                <option value="{{ $reason['key'] }}">{{ $reason['label_en'] }} / {{ $reason['label_gu'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="shf-form-label">Follow-up Date <span class="text-danger">*</span></label>
                                <input type="text" name="follow_up_date" class="shf-input shf-datepicker" placeholder="dd/mm/yyyy" autocomplete="off" required>
                                <small class="shf-text-gray-light">Must be in the future.</small>
                            </div>
                            <div class="mb-0">
                                <label class="shf-form-label">Note (optional)</label>
                                <textarea name="note" class="shf-input" rows="3" maxlength="5000" placeholder="Any additional context…"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-end gap-2 border-0 pt-0 pb-4 pe-4">
                            <button type="button" class="btn-accent-outline btn-accent-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-accent btn-accent-sm" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Confirm Hold
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Modal --}}
    @if(!$quotation->is_cancelled && !$quotation->is_converted && auth()->user()->hasPermission('cancel_quotation'))
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: var(--radius); border: 1px solid var(--border);">
                    <form method="POST" action="{{ route('quotations.cancel', $quotation) }}" id="cancelForm">
                        @csrf
                        <div class="modal-header" style="background: var(--primary-dark-solid); color: #fff;">
                            <h5 class="modal-title font-display">Cancel Quotation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small mb-3 shf-text-gray" style="color:#c0392b!important;">Cancelled quotations cannot be resumed or converted to a loan. This is a terminal state.</p>
                            <div class="mb-3">
                                <label class="shf-form-label">Reason <span class="text-danger">*</span></label>
                                <select name="reason_key" class="shf-input" required>
                                    <option value="">Select a reason…</option>
                                    @php
                                        $groupedCancel = collect($cancelReasons)->groupBy(fn ($r) => $r['group'] ?? 'Other')->sortKeys();
                                    @endphp
                                    @foreach($groupedCancel as $groupName => $groupReasons)
                                        <optgroup label="{{ $groupName }}">
                                            @foreach($groupReasons as $reason)
                                                <option value="{{ $reason['key'] }}">{{ $reason['label_en'] }} / {{ $reason['label_gu'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="shf-form-label">Note (optional)</label>
                                <textarea name="note" class="shf-input" rows="3" maxlength="5000" placeholder="Any additional context…"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-end gap-2 border-0 pt-0 pb-4 pe-4">
                            <button type="button" class="btn-accent-outline btn-accent-sm" data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn-accent btn-accent-sm shf-btn-danger-alt">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Confirm Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            $(function () {
                // Datepicker: future dates only for hold follow-up
                $('#holdModal .shf-datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    startDate: new Date(Date.now() + 86400000) // tomorrow
                });

                // Client-side validation
                $('#holdForm').on('submit', function (e) {
                    if (!SHF.validateForm($(this), {
                        reason_key: { required: true, label: 'Reason' },
                        follow_up_date: { required: true, dateFormat: 'dd/mm/yyyy', label: 'Follow-up Date' }
                    })) { e.preventDefault(); }
                });
                $('#cancelForm').on('submit', function (e) {
                    if (!SHF.validateForm($(this), {
                        reason_key: { required: true, label: 'Reason' }
                    })) { e.preventDefault(); }
                });

                // Auto-open modal when navigated from dashboard with ?action=hold or ?action=cancel
                var params = new URLSearchParams(window.location.search);
                var action = params.get('action');
                if (action === 'hold' && $('#holdModal').length) {
                    new bootstrap.Modal(document.getElementById('holdModal')).show();
                } else if (action === 'cancel' && $('#cancelModal').length) {
                    new bootstrap.Modal(document.getElementById('cancelModal')).show();
                }
            });
        </script>
    @endpush
@endsection
