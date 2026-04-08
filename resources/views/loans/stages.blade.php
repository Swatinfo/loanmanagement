@extends('layouts.app')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
                <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Stages — {{ $loan->loan_number }}
            </h2>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($progress)
                <span class="text-white" style="font-size: 0.9rem;">
                    {{ $progress->completed_stages }}/{{ $progress->total_stages }} stages ({{ number_format($progress->overall_percentage, 0) }}%)
                </span>
            @endif
            <a href="{{ route('loans.show', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 56rem;">

        {{-- Customer & Loan Info --}}
        <div class="card mb-3 border-0" style="background: var(--bg-light, #f8f8f8);">
            <div class="card-body py-2 px-3">
                <div class="row g-2" style="font-size: 0.85rem;">
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted">Customer:</span><br>
                        <strong>{{ $loan->customer_name }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted">Bank:</span><br>
                        <strong>{{ $loan->bank_name ?? '—' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted">Product:</span><br>
                        <strong>{{ $loan->product?->name ?? '—' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted">Loan Amount:</span><br>
                        <strong>₹ {{ number_format($loan->loan_amount) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if($progress)
            <div class="d-flex align-items-center gap-1 mb-4 flex-wrap" style="overflow-x: auto;">
                @foreach($mainStages as $sa)
                    @php
                        $dotColor = match($sa->status) {
                            'completed' => 'bg-success text-white',
                            'in_progress' => 'bg-primary text-white',
                            'skipped' => 'bg-warning text-white',
                            'rejected' => 'bg-danger text-white',
                            default => 'bg-light text-muted border',
                        };
                        $isCurrent = $loan->current_stage === $sa->stage_key;
                    @endphp
                    <div class="d-flex align-items-center gap-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $dotColor }} {{ $isCurrent ? 'shadow' : '' }}"
                             class="shf-text-xs"
                             style="width: 28px; height: 28px; font-weight: 700; flex-shrink: 0;{{ $isCurrent ? 'box-shadow: 0 0 0 3px rgba(13,110,253,0.3);' : '' }}"
                             title="{{ $sa->stage?->stage_name_en }}">
                            {{ $loop->iteration }}
                        </div>
                        @if(!$loop->last)
                            <div style="width: 16px; height: 2px;" class="{{ $sa->status === 'completed' ? 'bg-success' : 'bg-light' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Precompute role labels, bank filter, and location context once --}}
        @php
            $roleLabels = \App\Models\User::TASK_ROLE_LABELS;
            $bankFilterRoles = ['bank_employee', 'office_employee'];
            $locationRoles = ['bank_employee'];
            $branchRoles = ['office_employee', 'loan_advisor', 'branch_manager'];
            $loanCityId = $loan->branch?->location_id;
            $loanStateId = $loan->branch?->location?->parent_id;
        @endphp

        {{-- Stage Cards --}}
        @foreach($mainStages as $assignment)
            @php
                // Precompute stage-specific data once per card
                $stageKey = $assignment->stage_key;
                $eligibleRoles = $stageRoleEligibility[$stageKey] ?? [];
                $stageUsers = $eligibleRoles ? $allActiveUsers->whereIn('task_role', $eligibleRoles) : collect();
                // Filter by bank, location, and branch based on role type
                $stageUsers = $stageUsers->filter(function($u) use ($loan, $bankFilterRoles, $locationRoles, $branchRoles, $loanCityId, $loanStateId) {
                    if (in_array($u->task_role, $bankFilterRoles) && $loan->bank_id && !$u->employerBanks->contains('id', $loan->bank_id)) return false;
                    if (in_array($u->task_role, $locationRoles) && $loanCityId && $u->locations->isNotEmpty() && !$u->locations->contains('id', $loanCityId) && !$u->locations->contains('id', $loanStateId)) return false;
                    if (in_array($u->task_role, $branchRoles) && $loan->branch_id && $u->branches->isNotEmpty() && !$u->branches->contains('id', $loan->branch_id)) return false;
                    return true;
                });
                $assignedUserRole = $assignment->assignee?->task_role;
            @endphp
            <div class="card mb-3 border-start border-3 {{ match($assignment->status) {
                'completed' => 'border-success',
                'in_progress' => 'border-primary',
                'rejected' => 'border-danger',
                'skipped' => 'border-warning',
                default => 'border-secondary',
            } }}" id="stage-{{ $assignment->stage_key }}">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="shf-badge shf-badge-{{ match(\App\Models\StageAssignment::STATUS_LABELS[$assignment->status]['color']) {
                            'success' => 'green', 'primary' => 'blue', 'danger' => 'orange', 'warning' => 'orange', default => 'gray'
                        } }} shf-text-xs">
                            {{ \App\Models\StageAssignment::STATUS_LABELS[$assignment->status]['label'] }}
                        </span>
                        <strong>{{ $assignment->stage?->stage_name_en }}</strong>
                        @if($assignment->stage?->stage_name_gu)
                            <small class="text-muted d-none d-sm-inline">({{ $assignment->stage->stage_name_gu }})</small>
                        @endif
                    </div>
                    @if($assignment->assignee)
                        <small class="text-muted">{{ $assignment->assignee->name }}</small>
                    @endif
                </div>
                <div class="card-body py-2">
                    @if($assignment->started_at)
                        <small class="text-muted">Started: {{ $assignment->started_at->format('d M Y H:i') }}</small>
                    @endif
                    @if($assignment->completed_at)
                        <small class="text-muted ms-3">{{ $assignment->status === 'completed' ? 'Completed' : ucfirst($assignment->status) }}: {{ $assignment->completed_at->format('d M Y H:i') }}</small>
                    @endif

                    {{-- Phase progress indicator for multi-phase stages --}}
                    @php
                        $phaseConfig = match($assignment->stage_key) {
                            'rate_pf' => [
                                'current' => $assignment->getNotesData()['rate_pf_phase'] ?? '1',
                                'phases' => [
                                    ['key' => '1', 'label' => 'Fill Details', 'role' => 'Loan Advisor'],
                                    ['key' => '2', 'label' => 'Bank Review', 'role' => 'Bank Employee'],
                                    ['key' => '3', 'label' => 'Final Review', 'role' => 'Loan Advisor'],
                                ],
                            ],
                            'sanction' => [
                                'current' => $assignment->getNotesData()['sanction_phase'] ?? '1',
                                'phases' => [
                                    ['key' => '1', 'label' => 'Send to Bank', 'role' => 'Task Owner'],
                                    ['key' => '2', 'label' => 'Generate Letter', 'role' => 'Bank Employee'],
                                    ['key' => '3', 'label' => 'Fill Details', 'role' => 'Task Owner'],
                                ],
                            ],
                            'legal_verification' => [
                                'current' => $assignment->getNotesData()['legal_phase'] ?? '1',
                                'phases' => [
                                    ['key' => '1', 'label' => 'Send to Bank', 'role' => 'Task Owner'],
                                    ['key' => '2', 'label' => 'Initiate Legal', 'role' => 'Bank Employee'],
                                    ['key' => '3', 'label' => 'Review', 'role' => 'Task Owner'],
                                ],
                            ],
                            'docket' => [
                                'current' => $assignment->getNotesData()['docket_phase'] ?? '1',
                                'phases' => [
                                    ['key' => '1', 'label' => 'Login Date', 'role' => 'Loan Advisor'],
                                    ['key' => '2', 'label' => 'Docket Login', 'role' => 'Office Employee'],
                                    ['key' => '3', 'label' => 'Confirm', 'role' => 'Task Owner'],
                                ],
                            ],
                            'esign' => [
                                'current' => $assignment->getNotesData()['esign_phase'] ?? '1',
                                'phases' => [
                                    ['key' => '1', 'label' => 'Generate', 'role' => 'Bank Employee'],
                                    ['key' => '2', 'label' => 'Customer Sign', 'role' => 'Task Owner'],
                                    ['key' => '3', 'label' => 'Confirm', 'role' => 'Bank Employee'],
                                ],
                            ],
                            default => null,
                        };
                    @endphp
                    @php
                        $roleBgCss = function($role) {
                            if (str_contains($role, 'Bank Employee')) return 'shf-role-bg-bank-employee';
                            if (str_contains($role, 'Office Employee')) return 'shf-role-bg-office-employee';
                            if (str_contains($role, 'Branch Manager')) return 'shf-role-bg-branch-manager';
                            if (str_contains($role, 'Loan Advisor')) return 'shf-role-bg-loan-advisor';
                            return 'shf-role-bg-task-owner';
                        };
                        $transferSuggestion = match($assignment->stage_key) {
                            'inquiry', 'document_selection', 'document_collection', 'app_number' => 'Can transfer to: Loan Advisor, Branch Manager',
                            'bsm_osv' => 'Can transfer to: Bank Employee (same bank)',
                            'legal_verification' => 'Phase auto-transfers between Task Owner ↔ Bank Employee',
                            'technical_valuation', 'property_valuation' => 'Can transfer to: Office Employee, Branch Manager',
                            'rate_pf' => 'Phase auto-transfers: Loan Advisor → Bank Employee → Loan Advisor',
                            'sanction' => 'Phase auto-transfers: Task Owner → Bank Employee → Task Owner',
                            'docket' => 'Phase auto-transfers: Loan Advisor → Office Employee → Task Owner',
                            'kfs' => 'Can transfer to: Loan Advisor, Branch Manager, Office Employee',
                            'esign' => 'Phase auto-transfers: Bank Employee → Task Owner → Bank Employee',
                            'disbursement' => 'Can transfer to: Loan Advisor, Branch Manager',
                            'otc_clearance' => 'Can transfer to: Office Employee, Loan Advisor, Branch Manager',
                            default => null,
                        };
                    @endphp
                    @if($phaseConfig && $assignment->status === 'in_progress')
                        <div class="d-flex align-items-center gap-2 mt-2 mb-1 flex-wrap">
                            @foreach($phaseConfig['phases'] as $pi => $phase)
                                @php
                                    $isCurrent = $phaseConfig['current'] === $phase['key'];
                                    $isDone = (int)$phaseConfig['current'] > (int)$phase['key'];
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    @if($isDone)
                                        <span class="shf-phase-pill shf-phase-pill--done">
                                            <svg style="width:14px;height:14px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            {{ $phase['label'] }}
                                            <span class="shf-pill-role">({{ $phase['role'] }})</span>
                                        </span>
                                    @elseif($isCurrent)
                                        <span class="shf-phase-pill shf-phase-pill--active {{ $roleBgCss($phase['role']) }}" style="border-color:transparent;">
                                            <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
                                            {{ $phase['label'] }}
                                            <span class="shf-pill-role">({{ $phase['role'] }})</span>
                                        </span>
                                    @else
                                        <span class="shf-phase-pill shf-phase-pill--pending">
                                            {{ $phase['label'] }}
                                            <span class="shf-pill-role">({{ $phase['role'] }})</span>
                                        </span>
                                    @endif
                                    @if(!$loop->last)
                                        <svg class="shf-phase-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if($transferSuggestion && $assignment->status === 'in_progress')
                        <div class="shf-transfer-hint">
                            <svg style="width:12px;height:12px;display:inline;vertical-align:middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            {{ $transferSuggestion }}
                        </div>
                    @endif

                    {{-- Parallel sub-stages --}}
                    @if($assignment->stage_key === 'parallel_processing' && $subStages->isNotEmpty())
                        <p class="text-muted small mt-2 mb-2">All parallel tracks must complete before advancing.</p>
                        <div class="row">
                            @foreach($subStages->where('parent_stage_key', 'parallel_processing') as $sub)
                                @php
                                    $subKey = $sub->stage_key;
                                    $subRoles = $stageRoleEligibility[$subKey] ?? [];
                                    $subUsers = $subRoles ? $allActiveUsers->whereIn('task_role', $subRoles) : collect();
                                    $subUsers = $subUsers->filter(function($u) use ($loan, $bankFilterRoles, $locationRoles, $branchRoles, $loanCityId, $loanStateId) {
                                        if (in_array($u->task_role, $bankFilterRoles) && $loan->bank_id && !$u->employerBanks->contains('id', $loan->bank_id)) return false;
                                        if (in_array($u->task_role, $locationRoles) && $loanCityId && $u->locations->isNotEmpty() && !$u->locations->contains('id', $loanCityId) && !$u->locations->contains('id', $loanStateId)) return false;
                                        if (in_array($u->task_role, $branchRoles) && $loan->branch_id && $u->branches->isNotEmpty() && !$u->branches->contains('id', $loan->branch_id)) return false;
                                        return true;
                                    });
                                    $subAssignedRole = $sub->assignee?->task_role;
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="card border-start border-2 {{ match($sub->status) { 'completed' => 'border-success', 'in_progress' => 'border-primary', default => 'border-secondary' } }}">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong style="font-size: 0.85rem;">{{ $sub->stage?->stage_name_en }}</strong>
                                                <span class="shf-badge shf-badge-{{ match(\App\Models\StageAssignment::STATUS_LABELS[$sub->status]['color']) { 'success' => 'green', 'primary' => 'blue', default => 'gray' } }} shf-text-2xs">
                                                    {{ \App\Models\StageAssignment::STATUS_LABELS[$sub->status]['label'] }}
                                                </span>
                                            </div>
                                            @if($sub->assignee) <small class="text-muted">{{ $sub->assignee->name }}</small> @endif

                                            {{-- Completed: show saved data with Edit toggle --}}
                                            @if($sub->status === 'completed')
                                                @php $notesData = $sub->getNotesData(); @endphp
                                                <div class="mt-2 border-top pt-2 shf-stage-saved-data" id="saved-{{ $sub->stage_key }}">
                                                    @switch($sub->stage_key)
                                                        @case('app_number')
                                                            <div class="small"><span class="text-muted">Application No:</span> <strong>{{ $notesData['application_number'] ?? '—' }}</strong></div>
                                                            @if(!empty($notesData['docket_days_offset']))
                                                                <div class="small"><span class="text-muted">Docket Timeline:</span> <strong>S+{{ $notesData['docket_days_offset'] }}</strong></div>
                                                            @elseif(!empty($notesData['custom_docket_date']))
                                                                <div class="small"><span class="text-muted">Expected Docket:</span> <strong>{{ $notesData['custom_docket_date'] }}</strong></div>
                                                            @endif
                                                            @if(!empty($notesData['stageRemarks']))<div class="small text-muted mt-1">{{ $notesData['stageRemarks'] }}</div>@endif
                                                            @break
                                                        @case('bsm_osv')
                                                            <div class="small text-success">BSM/OSV verification completed</div>
                                                            @break
                                                        @case('legal_verification')
                                                            @if(!empty($notesData['confirmed_legal_advisor'] ?? $notesData['suggested_legal_advisor'] ?? null))
                                                                <div class="small"><span class="text-muted">Legal Advisor:</span> <strong>{{ $notesData['confirmed_legal_advisor'] ?? $notesData['suggested_legal_advisor'] }}</strong></div>
                                                            @endif
                                                            <div class="small text-success">Legal verification completed</div>
                                                            @break
                                                        @case('technical_valuation')
                                                        @case('property_valuation')
                                                            @php $val = $loan->valuationDetails->where('valuation_type', 'property')->first(); @endphp
                                                            @if($val)
                                                                @if($val->final_valuation)<div class="small"><span class="text-muted">Valuation:</span> <strong>₹ {{ number_format($val->final_valuation) }}</strong></div>@endif
                                                                @if($val->property_type)<div class="small"><span class="text-muted">Type:</span> {{ \App\Models\ValuationDetail::PROPERTY_TYPES[$val->property_type] ?? $val->property_type }}</div>@endif
                                                                @if($val->valuator_name)<div class="small"><span class="text-muted">Valuator:</span> {{ $val->valuator_name }}</div>@endif
                                                            @else
                                                                <div class="small text-muted">No valuation details</div>
                                                            @endif
                                                            @break
                                                    @endswitch
                                                    <button type="button" class="btn-accent-sm mt-1 shf-edit-saved shf-text-2xs" data-target="#edit-{{ $sub->stage_key }}" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">
                                                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                                                    </button>
                                                </div>
                                                {{-- Hidden edit form for completed sub-stages --}}
                                                <div id="edit-{{ $sub->stage_key }}" style="display:none;">
                                                    @switch($sub->stage_key)
                                                        @case('app_number')
                                                            @include('loans.partials.stage-notes-form', ['assignment' => $sub, 'loan' => $loan, 'fields' => [
                                                                ['name' => 'application_number', 'label' => 'Application Number', 'required' => true, 'placeholder' => 'e.g. HL20250113001'],
                                                                ['name' => 'docket_days_offset', 'label' => 'Docket Timeline', 'type' => 'select', 'required' => true, 'options' => ['' => 'Select...', '1' => 'S+1 (1 day after sanction)', '2' => 'S+2 (2 days after sanction)', '3' => 'S+3 (3 days after sanction)', '0' => 'Custom Date']],
                                                                ['name' => 'custom_docket_date', 'label' => 'Custom Docket Date', 'type' => 'date'],
                                                                ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                                            ]])
                                                            @break
                                                        @case('bsm_osv')
                                                            @break
                                                        @case('legal_verification')
                                                            @break
                                                        @case('technical_valuation')
                                                        @case('property_valuation')
                                                            <div class="mt-2 border-top pt-2">
                                                                <a href="{{ route('loans.valuation', $loan) }}" class="btn-accent-sm">
                                                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                    Edit Valuation
                                                                </a>
                                                            </div>
                                                            @break
                                                    @endswitch
                                                </div>
                                            @elseif($sub->status === 'in_progress')
                                                {{-- In-progress: show editable form --}}
                                                @switch($sub->stage_key)
                                                    @case('app_number')
                                                        @include('loans.partials.stage-notes-form', ['assignment' => $sub, 'loan' => $loan, 'fields' => [
                                                            ['name' => 'application_number', 'label' => 'Application Number', 'required' => true, 'placeholder' => 'e.g. HL20250113001'],
                                                            ['name' => 'docket_days_offset', 'label' => 'Docket Timeline', 'type' => 'select', 'required' => true, 'options' => ['' => 'Select...', '1' => 'S+1 (1 day after sanction)', '2' => 'S+2 (2 days after sanction)', '3' => 'S+3 (3 days after sanction)', '0' => 'Custom Date']],
                                                            ['name' => 'custom_docket_date', 'label' => 'Custom Docket Date', 'type' => 'date'],
                                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                                        ]])
                                                        @break
                                                    @case('bsm_osv')
                                                        <div class="mt-2 border-top pt-2">
                                                            <small class="text-muted">Assigned to bank employee for BSM/OSV verification. Mark complete when done.</small>
                                                        </div>
                                                        @break
                                                    @case('legal_verification')
                                                        @php
                                                            $legalNotes = $sub->getNotesData();
                                                            $legalPhase = $legalNotes['legal_phase'] ?? '1';
                                                        @endphp

                                                        @if($legalPhase === '1')
                                                            {{-- Phase 1: Task owner sends to bank --}}
                                                            <div class="mt-2 border-top pt-2">
                                                                <small class="text-muted d-block mb-2">Enter suggested legal advisor name and send to bank employee.</small>
                                                                <input type="text" class="shf-input shf-input-sm mb-2" id="legalAdvisorName" placeholder="Suggested Legal Advisor name (required)" value="{{ $legalNotes['suggested_legal_advisor'] ?? '' }}">
                                                                <button class="btn-accent-sm shf-legal-action" data-loan-id="{{ $loan->id }}" data-action="send_to_bank">
                                                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                                    Send to Bank
                                                                </button>
                                                            </div>
                                                        @elseif($legalPhase === '2')
                                                            {{-- Phase 2: Bank employee confirms legal advisor and initiates --}}
                                                            <div class="mt-2 border-top pt-2">
                                                                <div class="alert alert-info py-2 mb-2 shf-text-sm">
                                                                    <strong>Legal verification requested.</strong> Confirm or change the legal advisor and click Initiate.
                                                                </div>
                                                                <label class="form-label small">Legal Advisor Name <span class="text-danger">*</span></label>
                                                                <input type="text" class="shf-input shf-input-sm mb-2" id="legalAdvisorName" placeholder="Legal Advisor name (required)" value="{{ $legalNotes['suggested_legal_advisor'] ?? '' }}">
                                                                <button class="btn-accent-sm shf-legal-action" data-loan-id="{{ $loan->id }}" data-action="initiate_legal" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    Initiate Legal Verification
                                                                </button>
                                                            </div>
                                                        @elseif($legalPhase === '3')
                                                            {{-- Phase 3: Back to task owner — can reassign or complete --}}
                                                            <div class="mt-2 border-top pt-2">
                                                                <small class="text-muted d-block mb-1">Legal verification initiated. Legal Advisor: <strong>{{ $legalNotes['confirmed_legal_advisor'] ?? $legalNotes['suggested_legal_advisor'] ?? '—' }}</strong></small>
                                                                <small class="text-muted">You can reassign to another eligible user or mark as complete.</small>
                                                            </div>
                                                        @endif
                                                        @break
                                                    @case('technical_valuation')
                                                    @case('property_valuation')
                                                        @php $val = $loan->valuationDetails->where('valuation_type', 'property')->first(); @endphp
                                                        @if($val)
                                                            <div class="mt-2 border-top pt-2">
                                                                @if($val->final_valuation)<div class="small"><span class="text-muted">Valuation:</span> <strong>₹ {{ number_format($val->final_valuation) }}</strong></div>@endif
                                                                @if($val->property_type)<div class="small"><span class="text-muted">Type:</span> {{ \App\Models\ValuationDetail::PROPERTY_TYPES[$val->property_type] ?? $val->property_type }}</div>@endif
                                                                @if($val->valuator_name)<div class="small"><span class="text-muted">Valuator:</span> {{ $val->valuator_name }}</div>@endif
                                                            </div>
                                                        @endif
                                                        <div class="mt-2">
                                                            <a href="{{ route('loans.valuation', $loan) }}" class="btn-accent-sm">
                                                                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                                                {{ $val ? 'Edit Valuation' : 'Fill Valuation Form' }}
                                                            </a>
                                                        </div>
                                                        @break
                                                @endswitch
                                            @endif

                                            {{-- Active Queries Banner --}}
                                            @php $subActiveQueries = \App\Models\StageQuery::where('loan_id', $loan->id)->where('stage_key', $sub->stage_key)->whereIn('status', ['pending', 'responded'])->with(['raisedByUser', 'responses.respondedByUser'])->get(); @endphp
                                            @if($subActiveQueries->isNotEmpty())
                                                <div class="alert alert-warning py-2 mt-2 mb-1 shf-text-sm">
                                                    <strong>Active Queries ({{ $subActiveQueries->count() }})</strong> — Stage cannot be completed until resolved.
                                                    @foreach($subActiveQueries as $q)
                                                        <div class="border-top mt-2 pt-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <strong>{{ $q->raisedByUser->name }}</strong>
                                                                <span class="shf-badge shf-badge-{{ $q->status === 'pending' ? 'orange' : 'blue' }} shf-text-2xs">{{ ucfirst($q->status) }}</span>
                                                            </div>
                                                            <p class="mb-1">{{ $q->query_text }}</p>
                                                            @foreach($q->responses as $resp)
                                                                <div class="ps-3 border-start border-2 border-info mb-1">
                                                                    <small><strong>{{ $resp->respondedByUser->name }}</strong> · {{ $resp->created_at->diffForHumans() }}</small>
                                                                    <p class="mb-0 small">{{ $resp->response_text }}</p>
                                                                </div>
                                                            @endforeach
                                                            {{-- Response form (any user can respond to a pending query) --}}
                                                            @if($q->status === 'pending')
                                                                <form class="shf-query-respond mt-1" data-url="{{ route('loans.queries.respond', $q) }}">
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text" name="response_text" class="shf-input shf-text-sm" placeholder="Type response..." required>
                                                                        <button type="submit" class="btn-accent-sm"><svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg> Respond</button>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                            {{-- Resolve button (only for user who raised the query, and query is responded) --}}
                                                            @if($q->status === 'responded' && $q->raised_by === auth()->id())
                                                                <button class="btn-accent-sm mt-1 shf-query-resolve shf-text-2xs" data-url="{{ route('loans.queries.resolve', $q) }}" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                                                    Resolve Query
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Resolved Queries Summary --}}
                                            @php $subResolvedQueries = \App\Models\StageQuery::where('loan_id', $loan->id)->where('stage_key', $sub->stage_key)->where('status', 'resolved')->get(); @endphp
                                            @if($subResolvedQueries->isNotEmpty())
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <svg style="width:12px;height:12px;display:inline;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                                        {{ $subResolvedQueries->count() }} query/queries resolved
                                                    </small>
                                                </div>
                                            @endif

                                            @if(auth()->user()->hasPermission('manage_loan_stages'))
                                                <div class="mt-1 d-flex gap-1 flex-wrap">
                                                    @if($sub->status === 'in_progress')
                                                        <button class="btn-accent-sm shf-raise-query-btn shf-text-2xs" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;" data-loan-id="{{ $loan->id }}" data-stage="{{ $subKey }}">
                                                            <span style="font-weight:bold;">?</span> Query
                                                        </button>
                                                    @endif
                                                    {{-- Assign dropdown --}}
                                                    @if($sub->isActionable() && $subUsers->isNotEmpty())
                                                        <select class="shf-input shf-text-xs" style="width:auto;padding:2px 6px;"
                                                                data-loan-id="{{ $loan->id }}" data-stage="{{ $subKey }}"
                                                                onchange="if(this.value){var s=this;$.post('/loans/'+s.dataset.loanId+'/stages/'+s.dataset.stage+'/assign',{_token:$('meta[name=csrf-token]').attr('content'),user_id:s.value},function(r){if(r.success)Swal.fire({icon:'success',title:'Assigned to '+r.assigned_to,timer:1500,showConfirmButton:false})})}">
                                                            <option value="">Assign...</option>
                                                            @foreach($subUsers as $su)
                                                                <option value="{{ $su->id }}" {{ $sub->assigned_to === $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                    {{-- Role-wise transfer --}}
                                                    @if($sub->status === 'in_progress' && $sub->assigned_to)
                                                        @foreach($subRoles as $trRole)
                                                            @php
                                                                if ($trRole === $subAssignedRole) continue;
                                                                $trUsers = $subUsers->where('task_role', $trRole)->where('id', '!=', $sub->assigned_to);
                                                            @endphp
                                                            @if($trUsers->isNotEmpty())
                                                                <div class="dropdown d-inline-block">
                                                                    <button class="btn-accent-sm dropdown-toggle shf-text-2xs" style="background:linear-gradient(135deg,#6b7280,#9ca3af);" data-bs-toggle="dropdown">
                                                                        → {{ $roleLabels[$trRole] ?? ucfirst($trRole) }}
                                                                    </button>
                                                                    <ul class="dropdown-menu shf-text-xs">
                                                                        @foreach($trUsers as $tru)
                                                                            <li><a class="dropdown-item shf-quick-transfer" href="#" data-loan-id="{{ $loan->id }}" data-stage="{{ $subKey }}" data-user-id="{{ $tru->id }}" data-user-name="{{ $tru->name }}">{{ $tru->name }}</a></li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stage-specific forms and links --}}
                    @php $stageEditable = in_array($assignment->status, ['in_progress', 'completed']); @endphp
                    @if($stageEditable)
                        @switch($assignment->stage_key)
                            @case('inquiry')
                                @if(in_array($assignment->status, ['in_progress', 'completed']))
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted">Customer and loan details were captured during loan creation.</small>
                                        <a href="{{ route('loans.edit', $loan) }}" class="btn-accent-sm ms-2">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit Loan Details
                                        </a>
                                    </div>
                                @endif
                                @break
                            @case('document_selection')
                                @if(in_array($assignment->status, ['in_progress', 'completed']))
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted">Select and manage required documents for this loan.</small>
                                        <a href="{{ route('loans.documents', $loan) }}" class="btn-accent-sm ms-2">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Manage Documents
                                        </a>
                                    </div>
                                @endif
                                @break
                            @case('document_collection')
                                @if(in_array($assignment->status, ['in_progress', 'completed']))
                                    @php $docProgress = app(\App\Services\LoanDocumentService::class)->getProgress($loan); @endphp
                                    <div class="mt-2 border-top pt-2">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="flex-grow-1">
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $docProgress['percentage'] }}%"></div>
                                                </div>
                                            </div>
                                            <small class="text-muted text-nowrap">{{ $docProgress['resolved'] }}/{{ $docProgress['total'] }} ({{ $docProgress['percentage'] }}%)</small>
                                        </div>
                                        <a href="{{ route('loans.documents', $loan) }}" class="btn-accent-sm">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            Collect Documents
                                        </a>
                                        @if($docProgress['percentage'] < 100)
                                            <small class="text-warning ms-2">All required documents must be collected before completing this stage.</small>
                                        @endif
                                    </div>
                                @endif
                                @break
                            @case('rate_pf')
                                @php
                                    $ratePfNotes = $assignment->getNotesData();
                                    $ratePfPhase = $ratePfNotes['rate_pf_phase'] ?? '1';
                                    $ratePfCompleted = $assignment->status === 'completed';
                                @endphp

                                {{-- Completed: show saved data with Edit toggle --}}
                                @if($ratePfCompleted)
                                    <div class="mt-2 border-top pt-2 shf-stage-saved-data" id="saved-rate_pf">
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Interest Rate:</span> <strong>{{ $ratePfNotes['interest_rate'] ?? '—' }}%</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Rate Offered Date:</span> <strong>{{ $ratePfNotes['rate_offered_date'] ?? '—' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Valid Until:</span> <strong>{{ $ratePfNotes['rate_valid_until'] ?? '—' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Bank Reference:</span> <strong>{{ $ratePfNotes['bank_reference'] ?? '—' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Repo Rate:</span> <strong>{{ $ratePfNotes['repo_rate'] ?? '—' }}%</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Bank Margin:</span> <strong>{{ $ratePfNotes['bank_rate'] ?? '—' }}%</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Processing Fee:</span> <strong>{{ $ratePfNotes['processing_fee'] ?? '0' }}%</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Admin Charges:</span> <strong>₹ {{ $ratePfNotes['admin_charges'] ?? '0' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">PF GST:</span> <strong>₹ {{ $ratePfNotes['processing_fee_gst'] ?? '0' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Total PF:</span> <strong>₹ {{ $ratePfNotes['total_pf'] ?? '0' }}</strong></div>
                                            </div>
                                            @if(!empty($ratePfNotes['special_conditions']))
                                                <div class="col-12">
                                                    <div class="small"><span class="text-muted">Special Conditions:</span> {{ $ratePfNotes['special_conditions'] }}</div>
                                                </div>
                                            @endif
                                            @if(!empty($ratePfNotes['stageRemarks']))
                                                <div class="col-12">
                                                    <div class="small text-muted">{{ $ratePfNotes['stageRemarks'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn-accent-sm mt-2 shf-edit-saved shf-text-2xs" data-target="#edit-rate_pf" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">
                                            <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                                        </button>
                                    </div>
                                    <div id="edit-rate_pf" style="display:none;">
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'interest_rate', 'label' => 'Interest Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'rate_offered_date', 'label' => 'Rate Offered Date', 'type' => 'date'],
                                            ['name' => 'rate_valid_until', 'label' => 'Valid Until', 'type' => 'date'],
                                            ['name' => 'bank_reference', 'label' => 'Bank Reference'],
                                            ['name' => 'repo_rate', 'label' => 'Repo Rate (%)', 'type' => 'number', 'step' => '0.01'],
                                            ['name' => 'bank_rate', 'label' => 'Bank Margin (%)', 'type' => 'number', 'step' => '0.01'],
                                            ['name' => 'processing_fee', 'label' => 'Processing Fee (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'admin_charges', 'label' => 'Admin Charges', 'type' => 'currency'],
                                            ['name' => 'processing_fee_gst', 'label' => 'PF GST', 'type' => 'currency'],
                                            ['name' => 'total_pf', 'label' => 'Total PF', 'type' => 'currency'],
                                            ['name' => 'special_conditions', 'label' => 'Special Conditions', 'type' => 'textarea', 'col' => 12],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                    </div>

                                {{-- Phase 1: Eligible user fills ALL fields, then sends to bank --}}
                                @elseif($ratePfPhase === '1')
                                    <div class="mt-2 border-top pt-2">
                                        <small class="fw-semibold text-muted d-block mb-1">Rate & Processing Details</small>
                                        <small class="text-muted d-block mb-2">Fill all details before sending to bank.</small>
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'interest_rate', 'label' => 'Interest Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'placeholder' => 'e.g. 8.5'],
                                            ['name' => 'repo_rate', 'label' => 'Repo Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'placeholder' => 'e.g. 6.5'],
                                            ['name' => 'bank_rate', 'label' => 'Bank Margin (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'placeholder' => 'e.g. 2.5'],
                                            ['name' => 'rate_offered_date', 'label' => 'Rate Offered Date', 'type' => 'date', 'required' => true, 'default' => now()->format('d/m/Y')],
                                            ['name' => 'rate_valid_until', 'label' => 'Valid Until', 'type' => 'date', 'required' => true, 'default' => now()->addDays(15)->format('d/m/Y')],
                                            ['name' => 'bank_reference', 'label' => 'Bank Reference', 'required' => true],
                                            ['name' => 'processing_fee', 'label' => 'Processing Fee (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'placeholder' => 'e.g. 1.0'],
                                            ['name' => 'admin_charges', 'label' => 'Admin Charges', 'type' => 'currency', 'required' => true, 'default' => '0'],
                                            ['name' => 'processing_fee_gst', 'label' => 'PF GST', 'type' => 'currency', 'required' => true, 'default' => '0'],
                                            ['name' => 'total_pf', 'label' => 'Total PF', 'type' => 'currency', 'required' => true, 'default' => '0'],
                                            ['name' => 'special_conditions', 'label' => 'Special Conditions', 'type' => 'textarea', 'col' => 12],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                        <button class="btn-accent-sm shf-rate-pf-action mt-1" data-loan-id="{{ $loan->id }}" data-action="send_to_bank">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            Send to Bank
                                        </button>
                                    </div>

                                {{-- Phase 2: Bank employee sees ALL fields (editable), no hints --}}
                                @elseif($ratePfPhase === '2')
                                    <div class="mt-2 border-top pt-2">
                                        <div class="alert alert-info py-2 mb-2 shf-text-sm">
                                            <strong>Rate request received.</strong> Review and update all details, then return to task owner.
                                        </div>
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'interest_rate', 'label' => 'Interest Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'repo_rate', 'label' => 'Repo Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'bank_rate', 'label' => 'Bank Margin (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'rate_offered_date', 'label' => 'Rate Offered Date', 'type' => 'date', 'required' => true],
                                            ['name' => 'rate_valid_until', 'label' => 'Valid Until', 'type' => 'date', 'required' => true],
                                            ['name' => 'bank_reference', 'label' => 'Bank Reference', 'required' => true],
                                            ['name' => 'processing_fee', 'label' => 'Processing Fee (%)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                                            ['name' => 'admin_charges', 'label' => 'Admin Charges', 'type' => 'currency', 'required' => true],
                                            ['name' => 'processing_fee_gst', 'label' => 'PF GST', 'type' => 'currency', 'required' => true],
                                            ['name' => 'total_pf', 'label' => 'Total PF', 'type' => 'currency', 'required' => true],
                                            ['name' => 'special_conditions', 'label' => 'Special Conditions', 'type' => 'textarea', 'col' => 12],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                        <button class="btn-accent-sm shf-rate-pf-action mt-1" data-loan-id="{{ $loan->id }}" data-action="return_to_owner" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Save & Return to Task Owner
                                        </button>
                                    </div>

                                {{-- Phase 3: Eligible user sees editable form with original values as hints --}}
                                @elseif($ratePfPhase === '3')
                                    @php $origValues = $ratePfNotes['original_values'] ?? []; @endphp
                                    <div class="mt-2 border-top pt-2">
                                        <small class="fw-semibold text-muted d-block mb-1">Bank has reviewed. Values you originally entered are shown below each field.</small>
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'interest_rate', 'label' => 'Interest Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'hint' => ($origValues['interest_rate'] ?? '') !== '' ? ($origValues['interest_rate'] . '%') : ''],
                                            ['name' => 'repo_rate', 'label' => 'Repo Rate (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'hint' => ($origValues['repo_rate'] ?? '') !== '' ? ($origValues['repo_rate'] . '%') : ''],
                                            ['name' => 'bank_rate', 'label' => 'Bank Margin (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'hint' => ($origValues['bank_rate'] ?? '') !== '' ? ($origValues['bank_rate'] . '%') : ''],
                                            ['name' => 'rate_offered_date', 'label' => 'Rate Offered Date', 'type' => 'date', 'required' => true, 'hint' => $origValues['rate_offered_date'] ?? ''],
                                            ['name' => 'rate_valid_until', 'label' => 'Valid Until', 'type' => 'date', 'required' => true, 'hint' => $origValues['rate_valid_until'] ?? ''],
                                            ['name' => 'bank_reference', 'label' => 'Bank Reference', 'required' => true, 'hint' => $origValues['bank_reference'] ?? ''],
                                            ['name' => 'processing_fee', 'label' => 'Processing Fee (%)', 'type' => 'number', 'step' => '0.01', 'required' => true, 'hint' => ($origValues['processing_fee'] ?? '') !== '' ? ($origValues['processing_fee'] . '%') : ''],
                                            ['name' => 'admin_charges', 'label' => 'Admin Charges', 'type' => 'currency', 'required' => true, 'hint' => ($origValues['admin_charges'] ?? '') !== '' ? ('₹ ' . $origValues['admin_charges']) : ''],
                                            ['name' => 'processing_fee_gst', 'label' => 'PF GST', 'type' => 'currency', 'required' => true, 'hint' => ($origValues['processing_fee_gst'] ?? '') !== '' ? ('₹ ' . $origValues['processing_fee_gst']) : ''],
                                            ['name' => 'total_pf', 'label' => 'Total PF', 'type' => 'currency', 'required' => true, 'hint' => ($origValues['total_pf'] ?? '') !== '' ? ('₹ ' . $origValues['total_pf']) : ''],
                                            ['name' => 'special_conditions', 'label' => 'Special Conditions', 'type' => 'textarea', 'col' => 12, 'hint' => $origValues['special_conditions'] ?? ''],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                        <small class="text-muted d-block mt-1">Save any changes, then click Complete to finish this stage.</small>
                                    </div>
                                @endif
                                @break
                            @case('sanction')
                                @php
                                    $sanctionNotes = $assignment->getNotesData();
                                    $sanctionPhase = $sanctionNotes['sanction_phase'] ?? '1';
                                    $sanctionCompleted = $assignment->status === 'completed';
                                    $ratePfAssignment = $loan->stageAssignments()->where('stage_key', 'rate_pf')->first();
                                    $interestRateDefault = $ratePfAssignment ? ($ratePfAssignment->getNotesData()['interest_rate'] ?? '') : '';
                                @endphp

                                {{-- Completed: show saved data with Edit toggle --}}
                                @if($sanctionCompleted)
                                    <div class="mt-2 border-top pt-2 shf-stage-saved-data" id="saved-sanction">
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Sanction Date:</span> <strong>{{ $sanctionNotes['sanction_date'] ?? '—' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Sanctioned Amount:</span> <strong>₹ {{ $sanctionNotes['sanctioned_amount'] ?? '—' }}</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">Sanctioned Rate:</span> <strong>{{ $sanctionNotes['sanctioned_rate'] ?? '—' }}%</strong></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="small"><span class="text-muted">EMI Amount:</span> <strong>₹ {{ $sanctionNotes['emi_amount'] ?? '—' }}</strong></div>
                                            </div>
                                            @if(!empty($sanctionNotes['conditions']))
                                                <div class="col-12">
                                                    <div class="small"><span class="text-muted">Conditions:</span> {{ $sanctionNotes['conditions'] }}</div>
                                                </div>
                                            @endif
                                            @if(!empty($sanctionNotes['stageRemarks']))
                                                <div class="col-12">
                                                    <div class="small text-muted">{{ $sanctionNotes['stageRemarks'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn-accent-sm mt-2 shf-edit-saved shf-text-2xs" data-target="#edit-sanction" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">
                                            <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                                        </button>
                                    </div>
                                    <div id="edit-sanction" style="display:none;">
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'sanction_date', 'label' => 'Sanction Date', 'type' => 'date', 'required' => true, 'readonly' => true],
                                            ['name' => 'sanctioned_amount', 'label' => 'Sanctioned Amount', 'type' => 'currency', 'required' => true, 'default' => $loan->loan_amount],
                                            ['name' => 'sanctioned_rate', 'label' => 'Sanctioned Rate (%)', 'type' => 'number', 'step' => '0.01', 'readonly' => true, 'default' => $interestRateDefault],
                                            ['name' => 'emi_amount', 'label' => 'EMI Amount', 'type' => 'currency', 'required' => true],
                                            ['name' => 'conditions', 'label' => 'Conditions', 'type' => 'textarea', 'col' => 12],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                    </div>

                                {{-- Phase 1: Task owner sends for sanction letter --}}
                                @elseif($sanctionPhase === '1')
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted d-block mb-2">Send this loan for sanction letter generation by the bank employee.</small>
                                        <button class="btn-accent-sm shf-sanction-action" data-loan-id="{{ $loan->id }}" data-action="send_for_sanction">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            Send for Sanction Letter Generation
                                        </button>
                                    </div>

                                {{-- Phase 2: Bank employee marks sanction letter as generated --}}
                                @elseif($sanctionPhase === '2')
                                    <div class="mt-2 border-top pt-2">
                                        <div class="alert alert-info py-2 mb-2 shf-text-sm">
                                            <strong>Waiting for sanction letter.</strong> Please generate the sanction letter for this loan and click the button below when done.
                                        </div>
                                        <button class="btn-accent-sm shf-sanction-action" data-loan-id="{{ $loan->id }}" data-action="sanction_generated" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Sanction Letter Generated
                                        </button>
                                    </div>

                                {{-- Phase 3: Task owner fills sanction details --}}
                                @elseif($sanctionPhase === '3')
                                    <div class="mt-2 border-top pt-2">
                                        <small class="fw-semibold text-muted d-block mb-1">Sanction letter has been generated. Enter the details below.</small>
                                        @include('loans.partials.stage-notes-form', ['fields' => [
                                            ['name' => 'sanction_date', 'label' => 'Sanction Date', 'type' => 'date', 'required' => true, 'default' => now()->format('d/m/Y')],
                                            ['name' => 'sanctioned_amount', 'label' => 'Sanctioned Amount', 'type' => 'currency', 'required' => true, 'default' => $loan->loan_amount],
                                            ['name' => 'sanctioned_rate', 'label' => 'Sanctioned Rate (%)', 'type' => 'number', 'step' => '0.01', 'readonly' => true, 'default' => $interestRateDefault],
                                            ['name' => 'emi_amount', 'label' => 'EMI Amount', 'type' => 'currency', 'required' => true],
                                            ['name' => 'conditions', 'label' => 'Conditions', 'type' => 'textarea', 'col' => 12],
                                            ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                        ]])
                                    </div>
                                @endif
                                @break
                            @case('docket')
                                @php
                                    $docketNotes = $assignment->getNotesData();
                                    $docketPhase = $docketNotes['docket_phase'] ?? '1';

                                    // Calculate expected docket date from sanction_date + app_number offset
                                    $appNumberAssignment = $loan->stageAssignments()->where('stage_key', 'app_number')->first();
                                    $appNotes = $appNumberAssignment ? $appNumberAssignment->getNotesData() : [];
                                    $sanctionAssignment = $loan->stageAssignments()->where('stage_key', 'sanction')->first();
                                    $sanctionNotesDocket = $sanctionAssignment ? $sanctionAssignment->getNotesData() : [];
                                    $expectedDocketDate = null;
                                    $expectedDocketCarbon = null;
                                    $docketOffset = $appNotes['docket_days_offset'] ?? null;

                                    if ($docketOffset && $docketOffset !== '0' && !empty($sanctionNotesDocket['sanction_date'])) {
                                        $expectedDocketCarbon = \Carbon\Carbon::createFromFormat('d/m/Y', $sanctionNotesDocket['sanction_date'])->addDays((int) $docketOffset);
                                        $expectedDocketDate = $expectedDocketCarbon->format('d/m/Y');
                                    } elseif ($docketOffset === '0' && !empty($appNotes['custom_docket_date'])) {
                                        $expectedDocketCarbon = \Carbon\Carbon::createFromFormat('d/m/Y', $appNotes['custom_docket_date']);
                                        $expectedDocketDate = $appNotes['custom_docket_date'];
                                    }

                                    $docketDaysInfo = '';
                                    if ($expectedDocketCarbon) {
                                        $diffDays = now()->startOfDay()->diffInDays($expectedDocketCarbon->startOfDay(), false);
                                        if ($diffDays > 0) {
                                            $docketDaysInfo = '<span class="text-success fw-semibold">' . $diffDays . ' day' . ($diffDays > 1 ? 's' : '') . ' remaining</span>';
                                        } elseif ($diffDays === 0) {
                                            $docketDaysInfo = '<span class="text-warning fw-semibold">Due today</span>';
                                        } else {
                                            $docketDaysInfo = '<span class="text-danger fw-semibold">' . abs($diffDays) . ' day' . (abs($diffDays) > 1 ? 's' : '') . ' overdue</span>';
                                        }
                                    }
                                @endphp

                                {{-- Expected docket date banner --}}
                                @if($expectedDocketDate)
                                    <div class="alert {{ $expectedDocketCarbon && $expectedDocketCarbon->isPast() ? 'alert-danger' : 'alert-info' }} py-2 mt-2 mb-2" style="font-size:0.85rem;">
                                        <strong>Expected Docket Date:</strong> {{ $expectedDocketDate }}
                                        @if($docketOffset && $docketOffset !== '0')
                                            <small class="text-muted">(Sanction + {{ $docketOffset }}d)</small>
                                        @endif
                                        — {!! $docketDaysInfo !!}
                                    </div>
                                @endif

                                {{-- Phase 1: User fills login date, sends to office employee --}}
                                @if($docketPhase === '1')
                                    @include('loans.partials.stage-notes-form', ['fields' => [
                                        ['name' => 'login_date', 'label' => 'Login Date', 'type' => 'date', 'required' => true, 'default' => now()->format('d/m/Y')],
                                        ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                    ]])
                                    <button class="btn-accent-sm shf-docket-action mt-1" data-loan-id="{{ $loan->id }}" data-action="send_to_office">
                                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Save & Send to Office Employee
                                    </button>

                                {{-- Phase 2: Office employee reviews and completes --}}
                                @elseif($docketPhase === '2')
                                    <div class="mt-2 border-top pt-2">
                                        <div class="small mb-1"><span class="text-muted">Login Date:</span> <strong>{{ $docketNotes['login_date'] ?? '—' }}</strong></div>
                                        @if(!empty($docketNotes['stageRemarks']))<div class="small text-muted mb-1">{{ $docketNotes['stageRemarks'] }}</div>@endif
                                        <small class="text-muted">Review and click Complete when docket login is done.</small>
                                    </div>

                                {{-- Phase 3: Back to loan creator --}}
                                @elseif($docketPhase === '3')
                                    <div class="mt-2 border-top pt-2">
                                        <div class="small mb-1"><span class="text-muted">Login Date:</span> <strong>{{ $docketNotes['login_date'] ?? '—' }}</strong></div>
                                        <small class="text-muted">Docket login completed by office employee. Click Complete to finish this stage.</small>
                                    </div>
                                @endif
                                @break
                            @case('kfs')
                                <div class="mt-2 border-top pt-2">
                                    <small class="text-muted d-block mb-2">Click below when KFS has been generated for this loan.</small>
                                </div>
                                @break
                            @case('esign')
                                @php $esignPhase = ($assignment->getNotesData()['esign_phase'] ?? '1'); @endphp

                                @if($esignPhase === '1')
                                    {{-- Phase 1: Bank employee generates E-Sign & eNACH --}}
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted d-block mb-2">Generate E-Sign & eNACH documents for this loan, then click below.</small>
                                        <button class="btn-accent-sm shf-esign-action" data-loan-id="{{ $loan->id }}" data-action="esign_generated">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            E-Sign & eNACH Generated
                                        </button>
                                    </div>
                                @elseif($esignPhase === '2')
                                    {{-- Phase 2: Eligible user completes with customer --}}
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted d-block mb-2">E-Sign & eNACH has been generated by bank. Complete the signing process with the customer.</small>
                                        <button class="btn-accent-sm shf-esign-action" data-loan-id="{{ $loan->id }}" data-action="esign_customer_done" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            E-Sign & eNACH Completed with Customer
                                        </button>
                                    </div>
                                @elseif($esignPhase === '3')
                                    {{-- Phase 3: Bank employee final confirmation --}}
                                    <div class="mt-2 border-top pt-2">
                                        <small class="text-muted d-block mb-2">Customer has completed E-Sign & eNACH. Click Complete to finish this stage.</small>
                                    </div>
                                @endif
                                @break
                            @case('disbursement')
                                <div class="mt-2">
                                    <a href="{{ route('loans.disbursement', $loan) }}" class="btn-accent-sm">
                                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Open Disbursement Form
                                    </a>
                                </div>
                                @break
                            @case('otc_clearance')
                                @php
                                    $otcNotes = $assignment->getNotesData();
                                    $disbursementData = $loan->disbursement;
                                    $chequeList = $disbursementData?->cheques ?? [];
                                @endphp
                                <div class="mt-2 border-top pt-2">
                                    @if(!empty($chequeList))
                                        <small class="fw-semibold text-muted d-block mb-2">Cheques to be handed over:</small>
                                        <div class="table-responsive mb-2">
                                            <table class="table table-sm table-hover mb-0 shf-text-sm">
                                                <thead><tr><th>Cheque No.</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                                                <tbody>
                                                    @foreach($chequeList as $chq)
                                                        <tr>
                                                            <td>{{ $chq['cheque_number'] ?? '—' }}</td>
                                                            <td>{{ $chq['cheque_date'] ?? '—' }}</td>
                                                            <td class="text-end">₹ {{ number_format($chq['cheque_amount'] ?? 0) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    @include('loans.partials.stage-notes-form', ['fields' => [
                                        ['name' => 'handover_date', 'label' => 'Handover Date', 'type' => 'date', 'required' => true, 'default' => now()->format('d/m/Y')],
                                        ['name' => 'stageRemarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12],
                                    ]])
                                </div>
                                @break
                        @endswitch
                    @endif

                    {{-- Active Queries Banner (main stages) --}}
                    @php
                        $queryStages = ['rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement'];
                        $mainActiveQueries = in_array($assignment->stage_key, $queryStages)
                            ? \App\Models\StageQuery::where('loan_id', $loan->id)->where('stage_key', $assignment->stage_key)->whereIn('status', ['pending', 'responded'])->with(['raisedByUser', 'responses.respondedByUser'])->get()
                            : collect();
                    @endphp
                    @if($mainActiveQueries->isNotEmpty())
                        <div class="alert alert-warning py-2 mt-2 shf-text-sm">
                            <strong>Active Queries ({{ $mainActiveQueries->count() }})</strong> — Stage cannot be completed until resolved.
                            @foreach($mainActiveQueries as $q)
                                <div class="border-top mt-2 pt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ $q->raisedByUser->name }}</strong>
                                        <span class="shf-badge shf-badge-{{ $q->status === 'pending' ? 'orange' : 'blue' }} shf-text-2xs">{{ ucfirst($q->status) }}</span>
                                    </div>
                                    <p class="mb-1">{{ $q->query_text }}</p>
                                    @foreach($q->responses as $resp)
                                        <div class="ps-3 border-start border-2 border-info mb-1">
                                            <small><strong>{{ $resp->respondedByUser->name }}</strong> · {{ $resp->created_at->diffForHumans() }}</small>
                                            <p class="mb-0 small">{{ $resp->response_text }}</p>
                                        </div>
                                    @endforeach
                                    {{-- Response form (any user can respond to a pending query) --}}
                                    @if($q->status === 'pending')
                                        <form class="shf-query-respond mt-1" data-url="{{ route('loans.queries.respond', $q) }}">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="response_text" class="shf-input shf-text-sm" placeholder="Type response..." required>
                                                <button type="submit" class="btn-accent-sm"><svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg> Respond</button>
                                            </div>
                                        </form>
                                    @endif
                                    {{-- Resolve button (only for user who raised the query, and query is responded) --}}
                                    @if($q->status === 'responded' && $q->raised_by === auth()->id())
                                        <button class="btn-accent-sm mt-1 shf-query-resolve shf-text-2xs" data-url="{{ route('loans.queries.resolve', $q) }}" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                                            Resolve Query
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Resolved Queries Summary (main stages) --}}
                    @if(in_array($assignment->stage_key, $queryStages ?? []))
                        @php $mainResolvedQueries = \App\Models\StageQuery::where('loan_id', $loan->id)->where('stage_key', $assignment->stage_key)->where('status', 'resolved')->get(); @endphp
                        @if($mainResolvedQueries->isNotEmpty())
                            <div class="mt-1">
                                <small class="text-muted">
                                    <svg style="width:12px;height:12px;display:inline;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    {{ $mainResolvedQueries->count() }} query/queries resolved
                                </small>
                            </div>
                        @endif
                    @endif

                    {{-- Actions --}}
                    @if(auth()->user()->hasPermission('manage_loan_stages') && $assignment->stage_key !== 'parallel_processing')
                        <div class="mt-2 d-flex gap-2 flex-wrap align-items-center">
                            @if($assignment->status === 'in_progress')
                                <button class="btn-accent-sm shf-reject-btn" style="background:linear-gradient(135deg,#dc2626,#ef4444);" data-loan-id="{{ $loan->id }}" data-stage="{{ $assignment->stage_key }}">
                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Reject
                                </button>
                                {{-- Raise Query button for main stages --}}
                                @if(in_array($assignment->stage_key, $queryStages))
                                    <button class="btn-accent-sm shf-raise-query-btn" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;" data-loan-id="{{ $loan->id }}" data-stage="{{ $assignment->stage_key }}">
                                        <span class="shf-text-sm" style="font-weight:bold;">?</span> Query
                                    </button>
                                @endif
                            @endif
                            {{-- Skip: only for in_progress stages, with skip permission, and allowed by product config --}}
                            @if($assignment->status === 'in_progress' && auth()->user()->hasPermission('skip_loan_stages') && ($skipAllowed[$assignment->stage_key] ?? true))
                                <button class="btn-accent-sm shf-stage-action" style="background:linear-gradient(135deg,#d97706,#f59e0b);" data-loan-id="{{ $loan->id }}" data-stage="{{ $assignment->stage_key }}" data-action="skipped">
                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                    Skip
                                </button>
                            @endif
                            @if($assignment->isActionable())
                                <select class="shf-input shf-text-sm" style="width: auto; padding: 4px 8px;"
                                        data-loan-id="{{ $loan->id }}" data-stage="{{ $stageKey }}"
                                        onchange="if(this.value){var s=this;$.post('/loans/'+s.dataset.loanId+'/stages/'+s.dataset.stage+'/assign',{_token:$('meta[name=csrf-token]').attr('content'),user_id:s.value},function(r){if(r.success)Swal.fire({icon:'success',title:'Assigned to '+r.assigned_to,timer:1500,showConfirmButton:false})})}">
                                    <option value="">Assign to...</option>
                                    @foreach($stageUsers as $u)
                                        <option value="{{ $u->id }}" {{ $assignment->assigned_to === $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->task_role_label }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @if($assignment->status === 'in_progress' && $assignment->assigned_to)
                                @foreach($eligibleRoles as $trRole)
                                    @php
                                        if ($trRole === $assignedUserRole) continue;
                                        $roleUsers = $stageUsers->where('task_role', $trRole)->where('id', '!=', $assignment->assigned_to);
                                    @endphp
                                    @if($roleUsers->isNotEmpty())
                                        <div class="dropdown d-inline-block">
                                            <button class="btn-accent-sm dropdown-toggle shf-text-xs" style="background:linear-gradient(135deg,#6b7280,#9ca3af);" data-bs-toggle="dropdown">
                                                → {{ $roleLabels[$trRole] ?? ucfirst($trRole) }}
                                            </button>
                                            <ul class="dropdown-menu shf-text-sm">
                                                @foreach($roleUsers as $ru)
                                                    <li>
                                                        <a class="dropdown-item shf-quick-transfer" href="#"
                                                           data-loan-id="{{ $loan->id }}" data-stage="{{ $assignment->stage_key }}"
                                                           data-user-id="{{ $ru->id }}" data-user-name="{{ $ru->name }}">
                                                            {{ $ru->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="text-center mt-3">
            <a href="{{ route('loans.transfers', $loan) }}" class="btn btn-sm btn-outline-secondary"><svg class="shf-btn-icon" style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Transfer History</a>
        </div>

    </div>
</div>

{{-- Raise Query Modal --}}
<div class="modal fade" id="raiseQueryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Raise Query</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="queryStageKey">
                <input type="hidden" id="queryLoanId">
                <div class="mb-3">
                    <label class="shf-form-label">Query</label>
                    <textarea id="queryText" class="shf-input" rows="3" placeholder="Describe your query..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal"><svg class="shf-btn-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Cancel</button>
                <button class="btn-accent" id="submitQueryBtn" style="padding: 8px 20px;"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Raise Query</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Show/hide custom docket date based on docket_days_offset selection
    function toggleCustomDocketDate($select) {
        var $form = $select.closest('form');
        var $customField = $form.find('[name="custom_docket_date"]').closest('.col-sm-6');
        if ($select.val() === '0') {
            $customField.show();
        } else {
            $customField.hide();
            $form.find('[name="custom_docket_date"]').val('');
        }
    }
    $(document).on('change', '[name="docket_days_offset"]', function() {
        toggleCustomDocketDate($(this));
    });
    // Init on page load for any existing forms
    $('[name="docket_days_offset"]').each(function() { toggleCustomDocketDate($(this)); });

    // Init Bootstrap Datepicker on all shf-datepicker fields
    $('.shf-datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true });

    // Stage status change
    $(document).on('click', '.shf-stage-action', function() {
        var loanId = $(this).data('loan-id'), stage = $(this).data('stage'), action = $(this).data('action');
        $.post('/loans/' + loanId + '/stages/' + stage + '/status', { _token: csrfToken, status: action })
            .done(function(r) { if (r.success) location.reload(); })
            .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error'); });
    });

    // Assign
    $(document).on('change', '.shf-stage-assign', function() {
        var userId = $(this).val(), loanId = $(this).data('loan-id'), stage = $(this).data('stage');
        if (!userId) return;
        $.post('/loans/' + loanId + '/stages/' + stage + '/assign', { _token: csrfToken, user_id: userId })
            .done(function(r) { if (r.success) Swal.fire({ icon: 'success', title: 'Assigned to ' + r.assigned_to, timer: 1500, showConfirmButton: false }); });
    });

    // Reject
    $(document).on('click', '.shf-reject-btn', function() {
        var loanId = $(this).data('loan-id'), stage = $(this).data('stage');
        Swal.fire({
            title: 'Reject Loan?',
            html: 'This will reject the <strong>entire loan</strong>. This cannot be undone.',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Rejection reason (required)',
            inputPlaceholder: 'Why is this loan being rejected?',
            inputValidator: function(v) { if (!v) return 'Reason is required'; },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Reject Loan'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('/loans/' + loanId + '/stages/' + stage + '/reject', { _token: csrfToken, reason: result.value })
                    .done(function() { location.reload(); });
            }
        });
    });

    // Stage notes form — inline errors + focus first error field
    $(document).on('submit', '.shf-stage-notes-form', function(e) {
        e.preventDefault();
        var $form = $(this), url = $form.data('notes-url');
        // Clear previous inline errors
        $form.find('.shf-field-error').remove();
        $form.find('.is-invalid').removeClass('is-invalid');
        var formData = {};
        $form.serializeArray().forEach(function(item) { formData[item.name] = item.value; });
        $.post(url, { _token: csrfToken, notes_data: formData })
            .done(function(r) {
                if (r.success && r.stage_advanced) {
                    Swal.fire({ icon: 'success', title: 'Stage completed!', text: 'Moving to next stage...', timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                } else if (r.success) {
                    Swal.fire({ icon: 'success', title: 'Details saved', timer: 1500, showConfirmButton: false });
                }
            })
            .fail(function(xhr) {
                var errorMsg = xhr.responseJSON?.error || 'Failed';
                // Parse field-level errors like "Interest Rate is required, Processing Fee is required"
                var fieldErrors = xhr.responseJSON?.field_errors || {};
                var firstErrorField = null;
                Object.keys(fieldErrors).forEach(function(fieldName) {
                    var $input = $form.find('[name="' + fieldName + '"]');
                    if ($input.length) {
                        $input.addClass('is-invalid');
                        var $parent = $input.closest('.col-sm-6, .col-sm-12, [class^="col-sm-"]');
                        if (!$parent.length) $parent = $input.parent();
                        $parent.append('<div class="shf-field-error text-danger shf-text-xs" style="margin-top:2px;">' + fieldErrors[fieldName] + '</div>');
                        if (!firstErrorField) firstErrorField = $input;
                    }
                });
                if (firstErrorField) {
                    firstErrorField[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                } else {
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
    });

    // Quick role-based transfer
    $(document).on('click', '.shf-quick-transfer', function(e) {
        e.preventDefault();
        var loanId = $(this).data('loan-id');
        var stage = $(this).data('stage');
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        Swal.fire({
            title: 'Transfer to ' + userName + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Transfer'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('/loans/' + loanId + '/stages/' + stage + '/transfer', { _token: csrfToken, user_id: userId, reason: 'Role-based transfer' })
                    .done(function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Transferred to ' + r.assigned_to, timer: 1500, showConfirmButton: false })
                                .then(function() { location.reload(); });
                        }
                    })
                    .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error'); });
            }
        });
    });

    // E-Sign phase actions
    $(document).on('click', '.shf-esign-action', function() {
        var $btn = $(this);
        var loanId = $btn.data('loan-id');
        var action = $btn.data('action');
        $btn.prop('disabled', true);
        $.post('/loans/' + loanId + '/stages/esign/action', { _token: csrfToken, action: action })
            .done(function(r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                }
            })
            .fail(function(xhr) {
                $btn.prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error');
            });
    });

    // Docket phase actions
    $(document).on('click', '.shf-docket-action', function() {
        var $btn = $(this);
        var loanId = $btn.data('loan-id');
        // Save form data first, then send action
        var $form = $btn.closest('.card-body').find('.shf-stage-notes-form');
        if ($form.length) {
            var formData = {};
            $form.serializeArray().forEach(function(item) { formData[item.name] = item.value; });
            $btn.prop('disabled', true);
            $.post($form.data('notes-url'), { _token: csrfToken, notes_data: formData })
                .done(function() {
                    $.post('/loans/' + loanId + '/stages/docket/action', { _token: csrfToken, action: 'send_to_office' })
                        .done(function(r) {
                            if (r.success) {
                                Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                                    .then(function() { location.reload(); });
                            }
                        })
                        .fail(function(xhr) { $btn.prop('disabled', false); Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error'); });
                })
                .fail(function(xhr) { $btn.prop('disabled', false); Swal.fire('Error', xhr.responseJSON?.error || 'Save failed', 'error'); });
        }
    });

    // Rate & PF phase actions
    $(document).on('click', '.shf-rate-pf-action', function() {
        var $btn = $(this);
        var loanId = $btn.data('loan-id');
        var action = $btn.data('action');

        // If sending to bank, first save the current form data
        if (action === 'send_to_bank') {
            var $form = $btn.closest('.card-body').find('.shf-stage-notes-form');
            if ($form.length) {
                var formData = {};
                $form.serializeArray().forEach(function(item) { formData[item.name] = item.value; });
                // Save notes first, then send action
                $.post($form.data('notes-url'), { _token: csrfToken, notes_data: formData })
                    .done(function() {
                        $.post('/loans/' + loanId + '/stages/rate_pf/action', { _token: csrfToken, action: action })
                            .done(function(r) {
                                if (r.success) {
                                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                                        .then(function() { location.reload(); });
                                }
                            })
                            .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error'); });
                    })
                    .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Save failed', 'error'); });
                return;
            }
        }

        // If returning to owner, first save bank rate form data
        if (action === 'return_to_owner') {
            var $form = $btn.closest('.card-body').find('.shf-stage-notes-form');
            if ($form.length) {
                var formData = {};
                $form.serializeArray().forEach(function(item) { formData[item.name] = item.value; });
                $.post($form.data('notes-url'), { _token: csrfToken, notes_data: formData })
                    .done(function() {
                        $.post('/loans/' + loanId + '/stages/rate_pf/action', { _token: csrfToken, action: action })
                            .done(function(r) {
                                if (r.success) {
                                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                                        .then(function() { location.reload(); });
                                }
                            })
                            .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error'); });
                    })
                    .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Save failed', 'error'); });
                return;
            }
        }

        $btn.prop('disabled', true);
        $.post('/loans/' + loanId + '/stages/rate_pf/action', { _token: csrfToken, action: action })
            .done(function(r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                }
            })
            .fail(function(xhr) {
                $btn.prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error');
            });
    });

    // Legal verification phase actions
    $(document).on('click', '.shf-legal-action', function() {
        var $btn = $(this);
        var loanId = $btn.data('loan-id');
        var action = $btn.data('action');
        var advisorName = $('#legalAdvisorName').val();

        if (!advisorName || !advisorName.trim()) {
            Swal.fire('Error', 'Legal Advisor name is required', 'error');
            return;
        }

        var postData = { _token: csrfToken, action: action, suggested_legal_advisor: advisorName.trim() };

        $btn.prop('disabled', true);
        $.post('/loans/' + loanId + '/stages/legal_verification/action', postData)
            .done(function(r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                }
            })
            .fail(function(xhr) {
                $btn.prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error');
            });
    });

    // Sanction stage phase actions (send for sanction / sanction generated)
    $(document).on('click', '.shf-sanction-action', function() {
        var $btn = $(this);
        var loanId = $btn.data('loan-id');
        var action = $btn.data('action');
        var confirmMsg = action === 'send_for_sanction'
            ? 'Send this loan for sanction letter generation?'
            : 'Confirm that the sanction letter has been generated?';
        Swal.fire({
            title: 'Confirm',
            text: confirmMsg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true);
                $.post('/loans/' + loanId + '/stages/sanction/action', { _token: csrfToken, action: action })
                    .done(function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                                .then(function() { location.reload(); });
                        }
                    })
                    .fail(function(xhr) {
                        $btn.prop('disabled', false);
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed', 'error');
                    });
            }
        });
    });

    // Bank rate auto-calculation: interest_rate - repo_rate = bank_rate
    $(document).on('input', 'input[name="repo_rate"]', function() {
        var $form = $(this).closest('.shf-stage-notes-form');
        var repoRate = parseFloat($(this).val()) || 0;
        // Interest rate is in a different form (bank section), find it by stage card
        var $card = $(this).closest('.card');
        var interestRate = parseFloat($card.find('input[name="interest_rate"]').val()) || 0;
        var bankRate = Math.max(0, (interestRate - repoRate)).toFixed(2);
        $form.find('input[name="bank_rate"]').val(bankRate);
    });

    // Edit saved sub-stage data
    $(document).on('click', '.shf-edit-saved', function() {
        var target = $(this).data('target');
        $(this).closest('.shf-stage-saved-data').hide();
        $(target).slideDown(200);
    });

    // Raise query modal
    $(document).on('click', '.shf-raise-query-btn', function() {
        $('#queryLoanId').val($(this).data('loan-id'));
        $('#queryStageKey').val($(this).data('stage'));
        $('#queryText').val('');
        $('#raiseQueryModal').modal('show');
    });

    $('#submitQueryBtn').on('click', function() {
        var queryText = $('#queryText').val();
        if (!queryText || !queryText.trim()) {
            Swal.fire('Error', 'Query text is required.', 'error');
            return;
        }
        var loanId = $('#queryLoanId').val();
        var stageKey = $('#queryStageKey').val();
        var $btn = $(this);
        $btn.prop('disabled', true).text('Submitting...');
        $.post('/loans/' + loanId + '/stages/' + stageKey + '/query', {
            _token: csrfToken,
            query_text: queryText
        }).done(function(r) {
            if (r.success) { $('#raiseQueryModal').modal('hide'); location.reload(); }
        }).fail(function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.error || 'Failed to raise query', 'error');
        }).always(function() {
            $btn.prop('disabled', false).text('Raise Query');
        });
    });

    // Respond to query
    $(document).on('submit', '.shf-query-respond', function(e) {
        e.preventDefault();
        var $form = $(this), url = $form.data('url');
        var responseText = $form.find('[name="response_text"]').val();
        if (!responseText || !responseText.trim()) return;
        var $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true);
        $.post(url, { _token: csrfToken, response_text: responseText })
            .done(function(r) { if (r.success) location.reload(); })
            .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed to respond', 'error'); })
            .always(function() { $btn.prop('disabled', false); });
    });

    // Resolve query
    $(document).on('click', '.shf-query-resolve', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.post($btn.data('url'), { _token: csrfToken })
            .done(function(r) { if (r.success) location.reload(); })
            .fail(function(xhr) { Swal.fire('Error', xhr.responseJSON?.error || 'Failed to resolve', 'error'); })
            .always(function() { $btn.prop('disabled', false); });
    });

});
</script>
@endpush
