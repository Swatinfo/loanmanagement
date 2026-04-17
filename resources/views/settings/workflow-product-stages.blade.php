@extends('layouts.app')
@section('title', 'Product Stages — SHF')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $product->bank->name }} / {{ $product->name }} — Stage Configuration
        </h2>
        <a href="{{ route('loan-settings.index') }}#products" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            <p class="text-muted mb-3">Configure stages and availability for this product.</p>

            @php
                $locStates = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get();
                $bankLocIds = $product->bank->locations->pluck('id')->toArray();
                $locationConfigRoles = ['bank_employee', 'office_employee'];
                $roleLabels = ['task_owner' => 'Task Owner', 'bank_employee' => 'Bank Employee', 'office_employee' => 'Office Employee'];
                $roleBadgeClass = ['task_owner' => 'shf-badge-blue', 'bank_employee' => 'shf-badge-orange', 'office_employee' => 'shf-badge-purple'];
            @endphp

            <form method="POST" action="{{ route('loan-settings.product-stages.save', $product) }}" id="productStagesForm">
                @csrf

                <div class="shf-card">
                    <div class="p-4">
                        {{-- Column header row --}}
                        <div class="d-none d-md-flex align-items-center py-2 border-bottom mb-1"
                            style="font-size:0.7rem;font-weight:600;color:var(--primary-dark-light);text-transform:uppercase;letter-spacing:0.5px;">
                            <div style="flex:1 1 0;min-width:0;">Stage</div>
                            <div class="text-center" style="width:70px;flex-shrink:0;">Active</div>
                        </div>

                        @foreach ($stages as $stageIdx => $stage)
                            @php
                                $ps = $productStages[$stage->id] ?? null;
                                $si = $stageIdx;
                                $stageResolved = $resolvedRoles[$stage->id] ?? ['role' => 'task_owner', 'phases' => []];
                                $stageRole = $stageResolved['role'];
                                $hasSubActions = !empty($stage->sub_actions) && is_array($stage->sub_actions);
                                $isParallelHeader = $stage->is_parallel && !$stage->parent_stage_key;
                                $isHeaderOnly = $isParallelHeader;
                                $branchAssignments = $ps ? $ps->branchUsers : collect();
                            @endphp

                            {{-- Stage block --}}
                            <div class="shf-stage-block py-2 {{ !$loop->last ? 'border-bottom' : '' }} {{ $stage->parent_stage_key ? 'ps-4' : '' }}"
                                data-stage-idx="{{ $si }}"
                                style="{{ $stage->parent_stage_key ? 'background:#fafafa;' : '' }}">
                                <input type="hidden" name="stages[{{ $si }}][stage_id]"
                                    value="{{ $stage->id }}">

                                <div class="d-flex align-items-center">
                                    {{-- Stage name --}}
                                    <div style="flex:1 1 0;min-width:0;">
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($stage->parent_stage_key)
                                                <span class="text-muted shf-text-sm">↳</span>
                                            @endif
                                            <span
                                                class="{{ $stage->parent_stage_key ? 'small' : 'fw-medium' }}">{{ $stage->stage_name_en }}</span>
                                            @if ($isParallelHeader)
                                                <span class="shf-badge shf-badge-blue shf-text-2xs"
                                                   >Parallel</span>
                                            @endif
                                            @if ($hasSubActions)
                                                <span class="shf-badge shf-badge-orange shf-text-2xs"
                                                   >{{ count($stage->sub_actions) }}
                                                    sub-stages</span>
                                            @endif
                                            @if (!$isHeaderOnly && !$hasSubActions)
                                                <span class="shf-badge {{ $roleBadgeClass[$stageRole] ?? 'shf-badge-gray' }} shf-text-2xs">{{ $roleLabels[$stageRole] ?? $stageRole }}</span>
                                            @endif
                                        </div>
                                        @if (!$isHeaderOnly)
                                            @php
                                                $psPhases = match($stage->stage_key) {
                                                    'inquiry' => [
                                                        ['role' => 'Loan Advisor / Branch Manager', 'action' => 'Creates loan with customer details'],
                                                        ['note' => 'Auto-completed when converting from quotation'],
                                                    ],
                                                    'document_selection' => [
                                                        ['role' => 'Loan Advisor / Branch Manager', 'action' => 'Selects required documents for the loan type'],
                                                        ['note' => 'Auto-completed when converting from quotation'],
                                                    ],
                                                    'document_collection' => [
                                                        ['role' => 'Loan Advisor', 'action' => 'Collects and verifies all required documents from customer'],
                                                        ['role' => 'Loan Advisor', 'action' => 'Uploads document files'],
                                                        ['note' => 'Cannot complete until all required documents are collected'],
                                                    ],
                                                    'app_number' => [
                                                        ['role' => 'Loan Advisor', 'action' => 'Enters bank application number'],
                                                        ['role' => 'Loan Advisor', 'action' => 'Selects docket timeline (S+1 / S+2 / S+3 / Custom)'],
                                                        ['note' => 'Must complete before other parallel stages start'],
                                                    ],
                                                    'bsm_osv' => [
                                                        ['role' => 'Bank Employee', 'action' => 'Performs BSM/OSV site verification'],
                                                        ['note' => 'Auto-assigned to default bank employee. User configured here is used.'],
                                                    ],
                                                    'legal_verification' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Enters suggested legal advisor name → Send to Bank'],
                                                        ['phase' => '2', 'role' => 'Bank Employee', 'action' => 'Confirms legal advisor name → Initiate Legal'],
                                                        ['phase' => '3', 'role' => 'Task Owner', 'action' => 'Reviews, can reassign or Complete'],
                                                        ['note' => 'Bank employee configured here is used for phase 2 transfer'],
                                                    ],
                                                    'technical_valuation' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Clicks "Send for Technical Valuation" → transfers to Office Employee'],
                                                        ['phase' => '2', 'role' => 'Office Employee', 'action' => 'Fills valuation form (land area, rate, construction, lat/lng) → Complete'],
                                                        ['note' => 'OE configured here is used for phase 2 transfer. Stage auto-completes when form is saved.'],
                                                    ],
                                                    'property_valuation' => [
                                                        ['role' => 'Office Employee', 'action' => 'Fills property valuation form (same as Technical Valuation)'],
                                                        ['note' => 'User configured here is used for auto-assignment. Dedicated property valuation for LAP products.'],
                                                    ],
                                                    'sanction_decision' => [
                                                        ['role' => 'Office Employee', 'action' => 'Approve / Escalate to Branch Manager or BDH / Reject'],
                                                        ['note' => 'User configured here is used for auto-assignment'],
                                                    ],
                                                    'rate_pf' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Fills interest rate, repo rate, PF (% or amount), GST, admin charges → Send to Bank'],
                                                        ['phase' => '2', 'role' => 'Bank Employee', 'action' => 'Reviews/edits all fields → Save & Return to Task Owner'],
                                                        ['phase' => '3', 'role' => 'Task Owner', 'action' => 'Reviews bank changes (original values shown) → auto-completes'],
                                                        ['note' => 'Request interest rate and processing fee from bank. Total PF and Total Admin auto-calculated (readonly). All dates past-only except Valid Until.'],
                                                    ],
                                                    'sanction' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Sends for sanction letter generation → Bank'],
                                                        ['phase' => '2', 'role' => 'Bank Employee', 'action' => 'Marks sanction letter generated → Returns'],
                                                        ['phase' => '3', 'role' => 'Task Owner', 'action' => 'Enters sanction date, amount, EMI → Complete'],
                                                        ['note' => 'Docket date auto-calculated from sanction date + offset'],
                                                    ],
                                                    'docket' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Enters login date (past only) → Send to Office Employee'],
                                                        ['phase' => '2', 'role' => 'Office Employee', 'action' => 'Reviews → clicks "Generate KFS" → Docket completes, KFS assigned to Task Owner'],
                                                        ['note' => 'Shows expected docket date with days remaining/overdue. No future dates allowed.'],
                                                    ],
                                                    'kfs' => [
                                                        ['role' => 'Task Owner', 'action' => 'Reviews KFS → clicks "KFS Complete" → E-Sign assigned to Task Owner'],
                                                        ['note' => 'Auto-assigned to task owner from docket completion'],
                                                    ],
                                                    'esign' => [
                                                        ['phase' => '1', 'role' => 'Task Owner', 'action' => 'Sends for E-Sign & eNACH → Bank Employee'],
                                                        ['phase' => '2', 'role' => 'Bank Employee', 'action' => 'Generates E-Sign & eNACH docs → Returns to Task Owner'],
                                                        ['phase' => '3', 'role' => 'Task Owner', 'action' => 'Completes signing with customer → Returns to Bank'],
                                                        ['phase' => '4', 'role' => 'Bank Employee', 'action' => 'Confirms → Complete. Disbursement assigned to Office Employee'],
                                                        ['note' => 'Auto-assigned to task owner from KFS completion. Disbursement uses product stage OE config.'],
                                                    ],
                                                    'disbursement' => [
                                                        ['role' => 'Office Employee', 'action' => 'Processes disbursement — chooses Fund Transfer or Cheque'],
                                                        ['note' => 'OE configured here is used for auto-assignment'],
                                                        ['note' => 'Fund Transfer → loan completes immediately (OTC skipped)'],
                                                        ['note' => 'Cheque → enters cheque details → advances to OTC Clearance'],
                                                    ],
                                                    'otc_clearance' => [
                                                        ['role' => 'Task Owner', 'action' => 'Enters cheque handover date, or assigns to Office Employee'],
                                                        ['role' => 'Office Employee', 'action' => 'Enters handover date if assigned'],
                                                        ['note' => 'When completed, loan is marked as Completed'],
                                                    ],
                                                    default => null,
                                                };
                                                $psTransfer = match($stage->stage_key) {
                                                    'inquiry', 'document_selection', 'document_collection', 'app_number' => 'Transfer to: Loan Advisor, Branch Manager',
                                                    'bsm_osv' => 'Transfer to: Bank Employee (same bank)',
                                                    'legal_verification' => 'Auto-transfers: Task Owner ↔ Bank Employee',
                                                    'technical_valuation' => 'Auto-transfers: Task Owner → Office Employee',
                                                    'property_valuation' => 'Transfer to: Office Employee, Branch Manager',
                                                    'rate_pf' => 'Auto-transfers: Task Owner → Bank Employee → Task Owner',
                                                    'sanction' => 'Auto-transfers: Task Owner → Bank Employee → Task Owner',
                                                    'docket' => 'Auto-transfers: Task Owner → Office Employee',
                                                    'kfs' => 'Assigned to Task Owner from docket completion',
                                                    'sanction_decision' => 'Escalation: Office Employee → Branch Manager → BDH',
                                                    'esign' => 'Auto-transfers: Task Owner → Bank Employee → Task Owner → Bank Employee',
                                                    'disbursement' => 'Assigned to Office Employee (product stage config)',
                                                    'otc_clearance' => 'Transfer to: Office Employee, Loan Advisor, Branch Manager',
                                                    default => null,
                                                };
                                                $psRoleCss = function($role) {
                                                    if (str_contains($role, 'Bank Employee')) return 'shf-role-bank-employee';
                                                    if (str_contains($role, 'Office Employee')) return 'shf-role-office-employee';
                                                    if (str_contains($role, 'Branch Manager')) return 'shf-role-branch-manager';
                                                    if (str_contains($role, 'Loan Advisor')) return 'shf-role-loan-advisor';
                                                    return 'shf-role-task-owner';
                                                };
                                                $psRoleBg = function($role) {
                                                    if (str_contains($role, 'Bank Employee')) return 'shf-role-bg-bank-employee';
                                                    if (str_contains($role, 'Office Employee')) return 'shf-role-bg-office-employee';
                                                    if (str_contains($role, 'Branch Manager')) return 'shf-role-bg-branch-manager';
                                                    if (str_contains($role, 'Loan Advisor')) return 'shf-role-bg-loan-advisor';
                                                    return 'shf-role-bg-task-owner';
                                                };
                                            @endphp
                                            @if ($psPhases)
                                                <div class="shf-stage-notes">
                                                    @foreach ($psPhases as $sp)
                                                        @if (isset($sp['phase']))
                                                            <div class="shf-phase-step">
                                                                <span class="shf-phase-num {{ $psRoleBg($sp['role']) }}">{{ $sp['phase'] }}</span>
                                                                <span><strong class="{{ $psRoleCss($sp['role']) }}">{{ $sp['role'] }}</strong> — {{ $sp['action'] }}</span>
                                                            </div>
                                                        @elseif(isset($sp['role']))
                                                            <div>
                                                                <span class="shf-role-dot {{ $psRoleBg($sp['role']) }}"></span>
                                                                <strong class="{{ $psRoleCss($sp['role']) }}">{{ $sp['role'] }}</strong> — {{ $sp['action'] }}
                                                            </div>
                                                        @elseif(isset($sp['note']))
                                                            <div class="shf-note-line">
                                                                <svg class="shf-icon-2xs shf-icon-inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                {{ $sp['note'] }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                    @if ($psTransfer)
                                                        <div class="shf-transfer-line">
                                                            <svg class="shf-icon-2xs shf-icon-inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                            {{ $psTransfer }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Enable/disable toggle (right side) --}}
                                    <div class="text-center" style="width:70px;flex-shrink:0;">
                                        @if ($isHeaderOnly)
                                            {{-- Always enabled for parent stages — sub-stages control their own state --}}
                                            <input type="hidden" name="stages[{{ $si }}][is_enabled]"
                                                value="1">
                                            <small class="text-muted shf-text-2xs">—</small>
                                        @else
                                            <input type="hidden" name="stages[{{ $si }}][is_enabled]"
                                                value="0">
                                            <input type="checkbox" name="stages[{{ $si }}][is_enabled]"
                                                value="1" class="shf-toggle shf-stage-toggle"
                                                {{ $ps?->is_enabled ?? true ? 'checked' : '' }}>
                                        @endif
                                    </div>
                                </div>

                                {{-- Location user assignment (only for non-header stages with location-configurable roles) --}}
                                @if (!$isHeaderOnly && !$hasSubActions)
                                    @php
                                        $stageHasLocationRoles = in_array($stageRole, $locationConfigRoles);
                                    @endphp
                                    @if ($stageHasLocationRoles)
                                        @php
                                            $allStageUsers = $allActiveUsers->filter(
                                                fn($u) => $u->roles->where('slug', $stageRole)->isNotEmpty(),
                                            );
                                            if (in_array($stageRole, ['bank_employee', 'office_employee']) && $product->bank_id) {
                                                $allStageUsers = $allStageUsers->filter(
                                                    fn($u) => $u->employerBanks->contains('id', $product->bank_id),
                                                );
                                            }
                                            $savedOverrides = $branchAssignments
                                                ->whereNull('branch_id')
                                                ->whereNotNull('location_id')
                                                ->whereNull('phase_index')
                                                ->groupBy('location_id');
                                            $overrideIdx = 0;
                                        @endphp
                                        <div class="ps-4 pb-2 mt-1">
                                            @foreach ($locStates as $locState)
                                                @php
                                                    $stateInBank = in_array($locState->id, $bankLocIds);
                                                    $bankCitiesForState = $locState->children
                                                        ->where('is_active', true)
                                                        ->filter(fn($c) => in_array($c->id, $bankLocIds));
                                                    $allBankLocs = collect();
                                                    if ($stateInBank) {
                                                        $allBankLocs->push($locState);
                                                    }
                                                    $allBankLocs = $allBankLocs->merge($bankCitiesForState);
                                                @endphp
                                                @foreach ($allBankLocs as $bankLoc)
                                                    @php
                                                        $blId = $bankLoc->id;
                                                        $savedUsers = $savedOverrides[$blId] ?? collect();
                                                        $savedUserIds = $savedUsers->pluck('user_id')->toArray();
                                                        $savedDefaultId = $savedUsers
                                                            ->where('is_default', true)
                                                            ->first()?->user_id;
                                                        $locEligibleUsers = $allStageUsers->filter(function ($u) use (
                                                            $blId,
                                                            $bankLoc,
                                                        ) {
                                                            if ($u->locations->contains('id', $blId)) {
                                                                return true;
                                                            }
                                                            if (
                                                                $bankLoc->parent_id &&
                                                                $u->locations->contains('id', $bankLoc->parent_id)
                                                            ) {
                                                                return true;
                                                            }
                                                            return false;
                                                        });
                                                    @endphp
                                                    <div class="d-flex align-items-center gap-2 mb-1 p-2 border rounded shf-loc-row"
                                                        style="background:{{ $locEligibleUsers->isEmpty() ? '#fff5f5' : '#f0f9ff' }};font-size:0.78rem;">
                                                        <input type="hidden"
                                                            name="stages[{{ $si }}][location_overrides][{{ $overrideIdx }}][location_id]"
                                                            value="{{ $blId }}">
                                                        <div style="min-width:120px;flex-shrink:0;">
                                                            <small
                                                                class="fw-semibold">{{ $bankLoc->parent?->name ? $bankLoc->parent->name . '/' : '' }}{{ $bankLoc->name }}</small>
                                                            <span
                                                                class="shf-badge shf-badge-{{ $bankLoc->isState() ? 'blue' : 'green' }} shf-text-2xs"
                                                               >{{ $bankLoc->type }}</span>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1 flex-grow-1">
                                                            @if ($locEligibleUsers->isNotEmpty())
                                                                @foreach ($locEligibleUsers as $eu)
                                                                    @php $isChecked = in_array($eu->id, $savedUserIds); @endphp
                                                                    <label
                                                                        class="d-inline-flex align-items-center gap-1 border rounded px-1 py-1 {{ $isChecked ? 'border-primary' : '' }} shf-text-xs"
                                                                        style="cursor:pointer;background:{{ $isChecked ? '#eef2ff' : '#fff' }};">
                                                                        <input type="checkbox"
                                                                            name="stages[{{ $si }}][location_overrides][{{ $overrideIdx }}][users][]"
                                                                            value="{{ $eu->id }}"
                                                                            class="shf-checkbox"
                                                                            style="width:11px;height:11px;"
                                                                            {{ $isChecked ? 'checked' : '' }}>
                                                                        {{ $eu->name }} <small
                                                                            class="text-muted">({{ $roleLabels[$eu->workflow_role_label] ?? $eu->workflow_role_label }})</small>
                                                                        <input type="radio"
                                                                            name="stages[{{ $si }}][location_overrides][{{ $overrideIdx }}][default]"
                                                                            value="{{ $eu->id }}"
                                                                            style="width:11px;height:11px;accent-color:#f15a29;"
                                                                            {{ $savedDefaultId == $eu->id ? 'checked' : '' }}>
                                                                    </label>
                                                                @endforeach
                                                            @else
                                                                <small class="text-danger">No eligible employees for this
                                                                    location. Assign users in User Management.</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @php $overrideIdx++; @endphp
                                                @endforeach
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Phases (each with resolved role + user assignment for BE/OE) --}}
                            @if ($hasSubActions)
                                @foreach ($stage->sub_actions as $saIdx => $subAction)
                                    @php
                                        $phaseRole = $stageResolved['phases'][$saIdx] ?? $subAction['role'] ?? 'task_owner';
                                        $phaseNeedsUsers = in_array($phaseRole, $locationConfigRoles);
                                        $saAllUsers = collect();
                                        if ($phaseNeedsUsers) {
                                            $saAllUsers = $allActiveUsers->filter(fn($u) => $u->roles->where('slug', $phaseRole)->isNotEmpty());
                                            if (in_array($phaseRole, ['bank_employee', 'office_employee']) && $product->bank_id) {
                                                $saAllUsers = $saAllUsers->filter(fn($u) => $u->employerBanks->contains('id', $product->bank_id));
                                            }
                                        }
                                        // Saved phase-level user assignments
                                        $phaseSavedUsers = $branchAssignments->where('phase_index', $saIdx);
                                        // Also check stage-level assignments as fallback
                                        $stageLevelUsers = $branchAssignments->whereNull('phase_index');
                                    @endphp
                                    <div class="shf-substage-block py-1 ps-5 border-bottom"
                                        style="background:#f5f0eb;font-size:0.78rem;">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="shf-phase-num shf-role-bg-task-owner" style="width:20px;height:20px;font-size:0.6rem;">{{ $saIdx + 1 }}</span>
                                            <span>{{ $subAction['name'] ?? $subAction['key'] }}</span>
                                            <span class="shf-badge {{ $roleBadgeClass[$phaseRole] ?? 'shf-badge-gray' }} shf-text-2xs">{{ $roleLabels[$phaseRole] ?? $phaseRole }}</span>
                                        </div>

                                        @if ($phaseNeedsUsers)
                                            @php $saOverrideIdx = 0; @endphp
                                            <div class="ps-4 mt-1 pb-1">
                                                @foreach ($locStates as $saLocState)
                                                    @php
                                                        $saStateInBank = in_array($saLocState->id, $bankLocIds);
                                                        $saBankCities = $saLocState->children->where('is_active', true)->filter(fn($c) => in_array($c->id, $bankLocIds));
                                                        $saAllBankLocs = collect();
                                                        if ($saStateInBank) { $saAllBankLocs->push($saLocState); }
                                                        $saAllBankLocs = $saAllBankLocs->merge($saBankCities);
                                                    @endphp
                                                    @foreach ($saAllBankLocs as $saBankLoc)
                                                        @php
                                                            $sblId = $saBankLoc->id;
                                                            // Check phase-level saved users, then stage-level
                                                            $savedForLoc = $phaseSavedUsers->where('location_id', $sblId);
                                                            if ($savedForLoc->isEmpty()) {
                                                                $savedForLoc = $stageLevelUsers->where('location_id', $sblId);
                                                            }
                                                            $saSavedUserIds = $savedForLoc->pluck('user_id')->toArray();
                                                            $saSavedDefault = $savedForLoc->where('is_default', true)->first()?->user_id;
                                                            $saLocUsers = $saAllUsers->filter(function ($u) use ($sblId, $saBankLoc) {
                                                                return $u->locations->contains('id', $sblId) || ($saBankLoc->parent_id && $u->locations->contains('id', $saBankLoc->parent_id));
                                                            });
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2 mb-1 p-2 border rounded shf-loc-row"
                                                            style="background:{{ $saLocUsers->isEmpty() ? '#fff5f5' : '#f0f9ff' }};font-size:0.72rem;">
                                                            <input type="hidden"
                                                                name="stages[{{ $si }}][phase_location_overrides][{{ $saIdx }}][{{ $saOverrideIdx }}][location_id]"
                                                                value="{{ $sblId }}">
                                                            <div style="min-width:120px;flex-shrink:0;">
                                                                <small class="fw-semibold">{{ $saBankLoc->parent?->name ? $saBankLoc->parent->name . '/' : '' }}{{ $saBankLoc->name }}</small>
                                                                <span class="shf-badge shf-badge-{{ $saBankLoc->isState() ? 'blue' : 'green' }} shf-text-2xs">{{ $saBankLoc->type }}</span>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1 flex-grow-1">
                                                                @if ($saLocUsers->isNotEmpty())
                                                                    @foreach ($saLocUsers as $sau)
                                                                        @php $isChecked = in_array($sau->id, $saSavedUserIds); @endphp
                                                                        <label class="d-inline-flex align-items-center gap-1 border rounded px-1 py-1 {{ $isChecked ? 'border-primary' : '' }} shf-text-xs"
                                                                            style="cursor:pointer;background:{{ $isChecked ? '#eef2ff' : '#fff' }};">
                                                                            <input type="checkbox"
                                                                                name="stages[{{ $si }}][phase_location_overrides][{{ $saIdx }}][{{ $saOverrideIdx }}][users][]"
                                                                                value="{{ $sau->id }}" class="shf-checkbox shf-icon-xs"
                                                                                {{ $isChecked ? 'checked' : '' }}>
                                                                            {{ $sau->name }}
                                                                            <input type="radio"
                                                                                name="stages[{{ $si }}][phase_location_overrides][{{ $saIdx }}][{{ $saOverrideIdx }}][default]"
                                                                                value="{{ $sau->id }}" style="width:10px;height:10px;accent-color:#f15a29;"
                                                                                {{ $saSavedDefault == $sau->id ? 'checked' : '' }}>
                                                                        </label>
                                                                    @endforeach
                                                                @else
                                                                    <small class="text-danger">No eligible employees for this location.</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @php $saOverrideIdx++; @endphp
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="ps-4 pb-1">
                                                <small class="text-muted shf-text-xs">(Auto-assigned to task owner)</small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="shf-form-actions d-flex justify-content-end gap-3 mt-3 mb-4">
                    <a href="{{ route('loan-settings.index') }}#products" class="btn-accent-outline"><svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                    <button type="submit" class="btn-accent">
                        <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#productStagesForm').on('submit', function(e) {
                var hasError = false;
                var firstErrorEl = null;

                // Clear previous
                $('.shf-loc-row').css('outline', '');
                $('.shf-inline-error').remove();

                function addError($row, msg) {
                    hasError = true;
                    if (!firstErrorEl) firstErrorEl = $row;
                    $row.css('outline', '2px solid #dc3545');
                    $row.append('<div class="shf-inline-error text-danger mt-1 shf-text-2xs">* ' + msg + '</div>');
                }

                // Validate enabled stages
                $('.shf-stage-block').each(function() {
                    var $block = $(this);
                    var $toggle = $block.find('.shf-stage-toggle');
                    if (!$toggle.length || !$toggle.is(':checked')) return;
                    $block.find('.shf-loc-row').each(function() { validateLocRow($(this)); });
                });

                // Validate enabled sub-stages
                $('.shf-substage-block').each(function() {
                    var $block = $(this);
                    var $toggle = $block.find('.shf-substage-toggle');
                    if (!$toggle.length || !$toggle.is(':checked')) return;
                    $block.find('.shf-loc-row').each(function() { validateLocRow($(this)); });
                });

                function validateLocRow($row) {
                    var $checkboxes = $row.find('input[type="checkbox"]');
                    var $radios = $row.find('input[type="radio"]');

                    if (!$checkboxes.length) {
                        addError($row, 'No eligible employees');
                        return;
                    }
                    if ($checkboxes.filter(':checked').length === 0) {
                        addError($row, 'Select at least one employee');
                        return;
                    }
                    var checkedRadio = $radios.filter(':checked');
                    if (checkedRadio.length === 0) {
                        $row.css('outline', '2px solid #f15a29');
                        $row.append('<div class="shf-inline-error" style="font-size:0.7rem;color:#f15a29;">* Select a default employee</div>');
                        hasError = true;
                        if (!firstErrorEl) firstErrorEl = $row;
                        return;
                    }
                    var checkedVals = $checkboxes.filter(':checked').map(function() { return $(this).val(); }).get();
                    if (checkedVals.indexOf(checkedRadio.val()) === -1) {
                        $row.css('outline', '2px solid #f15a29');
                        $row.append('<div class="shf-inline-error" style="font-size:0.7rem;color:#f15a29;">* Default must be a selected employee</div>');
                        hasError = true;
                        if (!firstErrorEl) firstErrorEl = $row;
                    }
                }

                if (hasError) {
                    e.preventDefault();
                    if (firstErrorEl) {
                        $('html, body').animate({ scrollTop: $(firstErrorEl).offset().top - 100 }, 300);
                    }
                    return false;
                }
            });

            // Auto-select default when only one checkbox checked
            $(document).on('change', '.shf-loc-row input[type="checkbox"]', function() {
                var $row = $(this).closest('.shf-loc-row');
                var $checked = $row.find('input[type="checkbox"]:checked');
                if ($checked.length === 1) {
                    $row.find('input[type="radio"][value="' + $checked.val() + '"]').prop('checked', true);
                }
                if (!$(this).is(':checked')) {
                    var val = $(this).val();
                    var $radio = $row.find('input[type="radio"][value="' + val + '"]:checked');
                    if ($radio.length) {
                        $radio.prop('checked', false);
                        var $first = $row.find('input[type="checkbox"]:checked').first();
                        if ($first.length) {
                            $row.find('input[type="radio"][value="' + $first.val() + '"]').prop('checked',
                                true);
                        }
                    }
                }
            });
        });
    </script>
@endpush
