@extends('layouts.app')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
                <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Loan #{{ $loan->loan_number }}
            </h2>
            <span
                class="shf-badge shf-badge-{{ match ($loan->status_color) {'primary' => 'blue','success' => 'green','danger' => 'orange','warning' => 'orange',default => 'gray'} }} ms-1">
                {{ $loan->status_label }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('loans.index') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
            <a href="{{ route('loans.timeline', $loan) }}" class="btn btn-sm btn-outline-light"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Timeline</a>
            @if (auth()->user()->hasPermission('edit_loan'))
                <a href="{{ route('loans.edit', $loan) }}" class="btn btn-sm btn-outline-light"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</a>
                @if ($loan->status === 'active')
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle"
                            data-bs-toggle="dropdown">Status</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item shf-status-change" href="#" data-status="on_hold">Put On
                                    Hold</a></li>
                            <li><a class="dropdown-item shf-status-change" href="#" data-status="cancelled">Cancel
                                    Loan</a></li>
                        </ul>
                    </div>
                @elseif(in_array($loan->status, ['on_hold', 'cancelled']))
                    <button class="btn btn-sm btn-outline-success shf-status-change"
                        data-status="active"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Reactivate</button>
                @endif
            @endif
            @if (auth()->user()->hasPermission('delete_loan'))
                <button class="btn btn-sm btn-outline-danger" id="deleteLoanBtn"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Ownership & Time Banner --}}
            <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #f15a29 !important;">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <small class="text-muted">Current Owner:</small>
                            @if ($loan->current_owner)
                                <strong class="ms-1">{{ $loan->current_owner->name }}</strong>
                                <span class="shf-badge shf-badge-gray ms-1"
                                    class="shf-text-xs">{{ $loan->current_owner->task_role_label }}</span>
                                <span class="ms-2"><small class="text-muted">with them for</small> <strong
                                        style="color: #f15a29;">{{ $loan->time_with_current_owner }}</strong></span>
                            @else
                                <span class="ms-1 text-muted">Unassigned</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Total Loan Time:</small>
                            <strong class="ms-1">{{ $loan->total_loan_time }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer & Loan Info (collapsible, closed) --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header shf-collapsible" data-target="#collapse-details" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <svg class="shf-collapse-arrow"
                            style="width:14px;height:14px;transition:transform 0.2s;transform:rotate(0deg);color:#fff;"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="shf-section-title">{{ $loan->customer_name }}</span>
                        <small
                            style="color:rgba(255,255,255,0.6);">{{ $loan->bank_name ?? '' }}{{ $loan->product ? ' / ' . $loan->product->name : '' }}</small>
                        <span class="shf-badge shf-badge-green ms-auto"
                            style="font-size:0.7rem;">{{ $loan->formatted_amount }}</span>
                    </div>
                </div>
                <div id="collapse-details" class="shf-section-body" style="display:none;">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <div class="mb-3">
                                    <dt class="shf-form-label">Customer Name</dt>
                                    <dd class="mt-1 small fw-medium">{{ $loan->customer_name }}</dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Customer Type</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="shf-badge {{ match ($loan->customer_type) {'proprietor' => 'shf-badge-green','partnership_llp' => 'shf-badge-blue','pvt_ltd' => 'shf-badge-orange','salaried' => 'shf-badge-purple',default => 'shf-badge-gray'} }}">
                                            {{ $loan->customer_type_label }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Loan Amount</dt>
                                    <dd class="mt-1 font-display fw-bold" style="font-size: 1.125rem; color: #f15a29;">
                                        {{ $loan->formatted_amount }}</dd>
                                </div>
                                @if ($loan->customer_phone)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Phone</dt>
                                        <dd class="mt-1 small">{{ $loan->customer_phone }}</dd>
                                    </div>
                                @endif
                                @if ($loan->customer_email)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Email</dt>
                                        <dd class="mt-1 small">{{ $loan->customer_email }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <div class="mb-3">
                                    <dt class="shf-form-label">Bank</dt>
                                    <dd class="mt-1 small fw-medium">{{ $loan->bank_name ?? '—' }}
                                        @if ($loan->roi_min && $loan->roi_max)
                                            <span class="text-muted">(ROI: {{ $loan->roi_min }}% -
                                                {{ $loan->roi_max }}%)</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-3">
                                    <dt class="shf-form-label">Product / Branch</dt>
                                    <dd class="mt-1 small">{{ $loan->product?->name ?? '—' }} ·
                                        {{ $loan->branch?->name ?? '—' }}</dd>
                                </div>
                                @if($loan->location)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Location</dt>
                                        <dd class="mt-1 small">{{ $loan->location->parent?->name ? $loan->location->parent->name . ' / ' : '' }}{{ $loan->location->name }}</dd>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <dt class="shf-form-label">Advisor</dt>
                                    <dd class="mt-1 small">{{ $loan->advisor?->name ?? ($loan->creator?->name ?? '—') }}
                                    </dd>
                                </div>
                                @if ($loan->due_date)
                                    <div class="mb-3">
                                        <dt class="shf-form-label">Due Date</dt>
                                        <dd class="mt-1 small">{{ $loan->due_date->format('d M Y') }}</dd>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <dt class="shf-form-label">Loan Number</dt>
                                    <dd class="mt-1 small fw-medium" style="font-family: monospace;">
                                        {{ $loan->loan_number }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current Stage (clickable → stages screen) --}}
            @php
                $activeQueryCount = \App\Models\StageQuery::where('loan_id', $loan->id)
                    ->whereIn('status', ['pending', 'responded'])
                    ->count();
            @endphp
            <a href="{{ route('loans.stages', $loan) }}" class="shf-section mb-4 d-block text-decoration-none"
                style="cursor:pointer;transition:box-shadow 0.15s;">
                <div class="shf-section-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="shf-section-number">2</div>
                        <span class="shf-section-title" style="color:inherit;">Current Stage</span>
                        @if ($activeQueryCount > 0)
                            <span class="shf-badge shf-badge-orange" class="shf-text-2xs">{{ $activeQueryCount }}
                                {{ Str::plural('query', $activeQueryCount) }}</span>
                        @endif
                    </div>
                    <svg style="width:16px;height:16px;color:#999;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <div class="shf-section-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0" style="color:#111;">{{ $loan->current_stage_name }}</h5>
                            @if ($loan->progress)
                                <small
                                    class="text-muted">{{ $loan->progress->completed_stages }}/{{ $loan->progress->total_stages }}
                                    stages ({{ number_format($loan->progress->overall_percentage, 0) }}%)</small>
                            @endif
                        </div>
                        <span class="btn-accent-sm">View Stages</span>
                    </div>
                    @if ($activeQueryCount > 0)
                        <div class="alert alert-warning py-1 mt-2 mb-0" style="font-size:0.75rem;">
                            <svg style="width:12px;height:12px;display:inline;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ $activeQueryCount }} active {{ Str::plural('query', $activeQueryCount) }} — stages blocked
                            until resolved
                        </div>
                    @endif
                    @if ($loan->progress)
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success"
                                style="width: {{ $loan->progress->overall_percentage }}%"></div>
                        </div>
                    @endif
                </div>
            </a>

            {{-- Stage Detail — show what's happening at current stage --}}
            @php
                $currentStage = $loan->current_stage;
                $subStages = $loan
                    ->stageAssignments()
                    ->where('parent_stage_key', 'parallel_processing')
                    ->orderBy('id')
                    ->get();
            @endphp

            @if ($currentStage === 'parallel_processing' && $subStages->isNotEmpty())
                {{-- Parallel sub-stages summary --}}
                <div class="shf-section mb-4">
                    <div class="shf-section-header">
                        <div class="shf-section-number">3</div>
                        <span class="shf-section-title">Parallel Processing — Sub-stages</span>
                    </div>
                    <div class="shf-section-body p-0">
                        @foreach ($subStages as $sub)
                            @php
                                $subStageName =
                                    \App\Models\Stage::where('stage_key', $sub->stage_key)->value('stage_name_en') ??
                                    ucwords(str_replace('_', ' ', $sub->stage_key));
                                $statusColors = [
                                    'completed' => 'green',
                                    'in_progress' => 'blue',
                                    'pending' => 'gray',
                                    'rejected' => 'red',
                                    'skipped' => 'orange',
                                ];
                                $statusLabels = [
                                    'completed' => 'Done',
                                    'in_progress' => 'In Progress',
                                    'pending' => 'Pending',
                                    'rejected' => 'Rejected',
                                    'skipped' => 'Skipped',
                                ];
                            @endphp
                            <div
                                class="d-flex align-items-center justify-content-between px-4 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <span class="fw-medium" style="font-size:0.9rem;">{{ $subStageName }}</span>
                                    @if ($sub->assignee)
                                        <small class="text-muted ms-2">{{ $sub->assignee->name }}</small>
                                    @endif
                                </div>
                                <span
                                    class="shf-badge shf-badge-{{ $statusColors[$sub->status] ?? 'gray' }}">{{ $statusLabels[$sub->status] ?? $sub->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($currentStage === 'document_collection')
                {{-- Document progress (prominent when current stage) --}}
                @php $docProgress = app(\App\Services\LoanDocumentService::class)->getProgress($loan); @endphp
                @if ($docProgress['total'] > 0)
                    <div class="shf-section mb-4">
                        <div class="shf-section-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="shf-section-number">3</div>
                                <span class="shf-section-title">Document Collection</span>
                            </div>
                            <a href="{{ route('loans.documents', $loan) }}" class="btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> Collect Documents</a>
                        </div>
                        <div class="shf-section-body">
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $docProgress['percentage'] }}%">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $docProgress['resolved'] }}/{{ $docProgress['total'] }} documents collected
                                ({{ $docProgress['percentage'] }}%)
                                @if ($docProgress['rejected'] > 0)
                                    — <span class="text-danger">{{ $docProgress['rejected'] }} rejected</span>
                                @endif
                            </small>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Documents (collapsible, shown when NOT current stage) --}}
            @php $docProgress = $docProgress ?? app(\App\Services\LoanDocumentService::class)->getProgress($loan); @endphp
            @if ($docProgress['total'] > 0 && $currentStage !== 'document_collection')
                <div class="shf-section mb-4">
                    <div class="shf-section-header shf-collapsible" data-target="#collapse-docs" style="cursor:pointer;">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <svg class="shf-collapse-arrow"
                                style="width:14px;height:14px;transition:transform 0.2s;transform:rotate(0deg);color:#fff;"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title">Documents</span>
                            <small
                                style="color:rgba(255,255,255,0.6);">{{ $docProgress['resolved'] }}/{{ $docProgress['total'] }}
                                ({{ $docProgress['percentage'] }}%)</small>
                        </div>
                    </div>
                    <div id="collapse-docs" class="shf-section-body" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="progress mb-2" style="height: 8px;width:200px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $docProgress['percentage'] }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $docProgress['resolved'] }}/{{ $docProgress['total'] }} collected
                                    @if ($docProgress['rejected'] > 0)
                                        — <span class="text-danger">{{ $docProgress['rejected'] }} rejected</span>
                                    @endif
                                </small>
                            </div>
                            <a href="{{ route('loans.documents', $loan) }}" class="btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> View All</a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Source Quotation (collapsible, closed) --}}
            @if ($loan->quotation_id)
                <div class="shf-section mb-4">
                    <div class="shf-section-header shf-collapsible" data-target="#collapse-quotation"
                        style="cursor:pointer;">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <svg class="shf-collapse-arrow"
                                style="width:14px;height:14px;transition:transform 0.2s;transform:rotate(0deg);color:#fff;"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title">Source Quotation</span>
                            <small class="ms-1" style="color:rgba(255,255,255,0.6);">#{{ $loan->quotation_id }}</small>
                        </div>
                    </div>
                    <div id="collapse-quotation" class="shf-section-body" style="display:none;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span>Quotation #{{ $loan->quotation_id }} — Created
                                {{ $loan->quotation?->created_at?->format('d M Y') }}</span>
                            <a href="{{ route('quotations.show', $loan->quotation_id) }}" class="btn-accent-sm">View
                                Quotation</a>
                            @if ($loan->quotation?->pdf_filename && auth()->user()->hasPermission('download_pdf'))
                                <a href="{{ route('quotations.download-file', ['file' => $loan->quotation->pdf_filename]) }}"
                                    class="btn btn-sm btn-outline-secondary">Download PDF</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notes (collapsible, closed) --}}
            @if ($loan->notes)
                <div class="shf-section mb-4">
                    <div class="shf-section-header shf-collapsible" data-target="#collapse-notes"
                        style="cursor:pointer;">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <svg class="shf-collapse-arrow"
                                style="width:14px;height:14px;transition:transform 0.2s;transform:rotate(0deg);color:#fff;"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title">Notes</span>
                        </div>
                    </div>
                    <div id="collapse-notes" class="shf-section-body" style="display:none;">
                        <p class="mb-0" style="white-space: pre-line;">{{ $loan->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Remarks (collapsible, closed) --}}
            <div class="shf-section mb-4">
                <div class="shf-section-header shf-collapsible" data-target="#collapse-remarks" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <svg class="shf-collapse-arrow"
                            style="width:14px;height:14px;transition:transform 0.2s;transform:rotate(0deg);color:#fff;"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="shf-section-title">Remarks</span>
                        <span id="remarksCount" class="shf-badge shf-badge-orange"
                            style="font-size:0.6rem;display:none;"></span>
                    </div>
                </div>
                <div id="collapse-remarks" class="shf-section-body" style="display:none;">
                    @if (auth()->user()->hasPermission('add_remarks'))
                        <form id="remarkForm" class="mb-3">
                            <div class="input-group">
                                <input type="text" id="remarkInput" class="shf-input-sm"
                                    placeholder="Add a remark..." maxlength="5000">
                                <button type="submit" class="btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Post</button>
                            </div>
                        </form>
                    @endif
                    <div id="remarksList"><small class="text-muted">Loading...</small></div>
                </div>
            </div>

            {{-- Rejection Info --}}
            @if ($loan->status === 'rejected')
                <div class="alert alert-danger">
                    <strong>Loan Rejected</strong> at stage "{{ $loan->rejected_stage }}"
                    on {{ $loan->rejected_at?->format('d M Y H:i') }}
                    @if ($loan->rejection_reason)
                        <br>Reason: {{ $loan->rejection_reason }}
                    @endif
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Collapsible sections
            $(document).on('click', '.shf-collapsible', function() {
                var $target = $($(this).data('target'));
                var $arrow = $(this).find('.shf-collapse-arrow');
                var isOpen = $target.is(':visible');
                $target.slideToggle(200);
                $arrow.css('transform', isOpen ? 'rotate(0deg)' : 'rotate(90deg)');
            });

            // Status change
            $('.shf-status-change').on('click', function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                Swal.fire({
                    title: 'Change loan status?',
                    text: 'Status will be changed to "' + status.replace('_', ' ') + '"',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f15a29',
                    confirmButtonText: 'Yes, change it'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.post('{{ route('loans.update-status', $loan) }}', {
                            _token: csrfToken,
                            status: status
                        }).done(function() {
                            location.reload();
                        });
                    }
                });
            });

            // Remarks
            function loadRemarks() {
                $.get('{{ route('loans.remarks.index', $loan) }}', function(r) {
                    if (!r.remarks.length) {
                        $('#remarksList').html('<small class="text-muted">No remarks yet.</small>');
                        $('#remarksCount').hide();
                        return;
                    }
                    $('#remarksCount').text(r.remarks.length).show();
                    var html = '';
                    r.remarks.forEach(function(rm) {
                        html +=
                            '<div class="py-2 border-bottom"><div class="d-flex justify-content-between"><strong style="font-size:0.85rem;">' +
                            rm.user_name + '</strong><small class="text-muted">' + rm.created_at +
                            '</small></div><p class="mb-0 small">' + rm.remark + '</p></div>';
                    });
                    $('#remarksList').html(html);
                });
            }
            loadRemarks();

            $('#remarkForm').on('submit', function(e) {
                e.preventDefault();
                var text = $('#remarkInput').val().trim();
                if (!text) return;
                $.post('{{ route('loans.remarks.store', $loan) }}', {
                        _token: csrfToken,
                        remark: text
                    })
                    .done(function(r) {
                        if (r.success) {
                            $('#remarkInput').val('');
                            loadRemarks();
                        }
                    });
            });

            // Delete
            $('#deleteLoanBtn').on('click', function() {
                Swal.fire({
                    title: 'Delete this loan?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('loans.destroy', $loan) }}',
                            method: 'DELETE',
                            data: {
                                _token: csrfToken
                            },
                            success: function(r) {
                                window.location.href = r.redirect;
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
