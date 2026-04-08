@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $product->bank->name }} / {{ $product->name }} — Stage Configuration
        </h2>
        <a href="{{ route('loan-settings.index') }}#products" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
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
                $roleLabels = \App\Models\User::TASK_ROLE_LABELS;
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
                                $eligibleRoles = is_array($stage->default_role) ? $stage->default_role : [];
                                $hasSubActions = !empty($stage->sub_actions) && is_array($stage->sub_actions);
                                $isParallelHeader = $stage->is_parallel && !$stage->parent_stage_key;
                                // Parent stages with sub-actions or parallel header: no toggle, no user assignment
                                $isHeaderOnly = $hasSubActions || $isParallelHeader;
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
                                                <span class="text-muted" style="font-size:0.8rem;">↳</span>
                                            @endif
                                            <span
                                                class="{{ $stage->parent_stage_key ? 'small' : 'fw-medium' }}">{{ $stage->stage_name_en }}</span>
                                            @if ($isParallelHeader)
                                                <span class="shf-badge shf-badge-blue"
                                                    class="shf-text-2xs">Parallel</span>
                                            @endif
                                            @if ($hasSubActions)
                                                <span class="shf-badge shf-badge-orange"
                                                    class="shf-text-2xs">{{ count($stage->sub_actions) }}
                                                    sub-stages</span>
                                            @endif
                                            @if (!$isHeaderOnly && !empty($eligibleRoles))
                                                <small class="text-muted d-none d-sm-inline" class="shf-text-xs">
                                                    ({{ collect($eligibleRoles)->map(fn($r) => $roleLabels[$r] ?? $r)->implode(', ') }})
                                                </small>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Enable/disable toggle (right side) --}}
                                    <div class="text-center" style="width:70px;flex-shrink:0;">
                                        @if ($isHeaderOnly)
                                            {{-- Always enabled for parent stages — sub-stages control their own state --}}
                                            <input type="hidden" name="stages[{{ $si }}][is_enabled]"
                                                value="1">
                                            <small class="text-muted" class="shf-text-2xs">—</small>
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
                                @if (!$isHeaderOnly)
                                    @php
                                        $stageHasLocationRoles = !empty(
                                            array_intersect($eligibleRoles, $locationConfigRoles)
                                        );
                                    @endphp
                                    @if ($stageHasLocationRoles)
                                        @php
                                            $locConfigRolesForStage = array_intersect(
                                                $eligibleRoles,
                                                $locationConfigRoles,
                                            );
                                            $allStageUsers = $allActiveUsers->whereIn(
                                                'task_role',
                                                $locConfigRolesForStage,
                                            );
                                            if ($product->bank_id) {
                                                $allStageUsers = $allStageUsers->filter(
                                                    fn($u) => $u->employerBanks->contains('id', $product->bank_id),
                                                );
                                            }
                                            $savedOverrides = $branchAssignments
                                                ->whereNull('branch_id')
                                                ->whereNotNull('location_id')
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
                                                                class="shf-badge shf-badge-{{ $bankLoc->isState() ? 'blue' : 'green' }}"
                                                                class="shf-text-2xs">{{ $bankLoc->type }}</span>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1 flex-grow-1">
                                                            @if ($locEligibleUsers->isNotEmpty())
                                                                @foreach ($locEligibleUsers as $eu)
                                                                    @php $isChecked = in_array($eu->id, $savedUserIds); @endphp
                                                                    <label
                                                                        class="d-inline-flex align-items-center gap-1 border rounded px-1 py-1 {{ $isChecked ? 'border-primary' : '' }}"
                                                                        class="shf-text-xs" style="cursor:pointer;background:{{ $isChecked ? '#eef2ff' : '#fff' }};">
                                                                        <input type="checkbox"
                                                                            name="stages[{{ $si }}][location_overrides][{{ $overrideIdx }}][users][]"
                                                                            value="{{ $eu->id }}"
                                                                            class="shf-checkbox"
                                                                            style="width:11px;height:11px;"
                                                                            {{ $isChecked ? 'checked' : '' }}>
                                                                        {{ $eu->name }} <small
                                                                            class="text-muted">({{ $roleLabels[$eu->task_role] ?? $eu->task_role }})</small>
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

                            {{-- Sub-actions (each with its own enable/disable + user assignment) --}}
                            @if ($hasSubActions)
                                @php $psSubActions = ($ps?->sub_actions_override && is_array($ps->sub_actions_override)) ? $ps->sub_actions_override : []; @endphp
                                @foreach ($stage->sub_actions as $saIdx => $subAction)
                                    @php
                                        $saRoles = $subAction['roles'] ?? [];
                                        $saLocRoles = array_intersect($saRoles, $locationConfigRoles);
                                        $saAllUsers = $saLocRoles
                                            ? $allActiveUsers->whereIn('task_role', $saLocRoles)
                                            : collect();
                                        if ($product->bank_id && $saAllUsers->isNotEmpty()) {
                                            $saAllUsers = $saAllUsers->filter(
                                                fn($u) => $u->employerBanks->contains('id', $product->bank_id),
                                            );
                                        }
                                        $saIsEnabled = $psSubActions[$saIdx]['is_enabled'] ?? true;
                                    @endphp
                                    <div class="shf-substage-block py-1 ps-5 border-bottom"
                                        data-stage-idx="{{ $si }}" data-sa-idx="{{ $saIdx }}"
                                        style="background:#f5f0eb;font-size:0.78rem;">
                                        <div class="d-flex align-items-center">
                                            {{-- Sub-action name --}}
                                            <div style="flex:1 1 0;min-width:0;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted" style="font-size:0.7rem;">⤷</span>
                                                    <span>{{ $subAction['name'] ?? $subAction['key'] }}</span>
                                                    <span
                                                        class="shf-badge shf-badge-{{ ($subAction['type'] ?? '') === 'action_button' ? 'orange' : 'blue' }}"
                                                        class="shf-text-2xs">
                                                        {{ ($subAction['type'] ?? '') === 'action_button' ? 'Action' : 'Form' }}
                                                    </span>
                                                    <small class="text-muted ms-1" class="shf-text-xs">
                                                        ({{ collect($saRoles)->map(fn($r) => $roleLabels[$r] ?? $r)->implode(', ') }})
                                                    </small>
                                                </div>
                                            </div>

                                            {{-- Enable/disable toggle (right side, aligned with stage toggles) --}}
                                            <div class="text-center" style="width:70px;flex-shrink:0;">
                                                <input type="hidden"
                                                    name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][is_enabled]"
                                                    value="0">
                                                <input type="checkbox"
                                                    name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][is_enabled]"
                                                    value="1" class="shf-toggle shf-substage-toggle"
                                                    {{ $saIsEnabled ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        {{-- Location user assignment for sub-action --}}
                                        @if (!empty($saLocRoles))
                                            @php
                                                $saLocOverrides = $psSubActions[$saIdx]['location_overrides'] ?? [];
                                                $saOverrideIdx = 0;
                                            @endphp
                                            <div class="ps-4 mt-1 pb-1">
                                                @foreach ($locStates as $saLocState)
                                                    @php
                                                        $saStateInBank = in_array($saLocState->id, $bankLocIds);
                                                        $saBankCities = $saLocState->children
                                                            ->where('is_active', true)
                                                            ->filter(fn($c) => in_array($c->id, $bankLocIds));
                                                        $saAllBankLocs = collect();
                                                        if ($saStateInBank) {
                                                            $saAllBankLocs->push($saLocState);
                                                        }
                                                        $saAllBankLocs = $saAllBankLocs->merge($saBankCities);
                                                    @endphp
                                                    @foreach ($saAllBankLocs as $saBankLoc)
                                                        @php
                                                            $sblId = $saBankLoc->id;
                                                            $saSavedOverride = collect($saLocOverrides)->firstWhere(
                                                                'location_id',
                                                                $sblId,
                                                            );
                                                            $saSavedUserIds = $saSavedOverride['users'] ?? [];
                                                            $saSavedDefault = $saSavedOverride['default'] ?? null;
                                                            $saLocUsers = $saAllUsers->filter(function ($u) use (
                                                                $sblId,
                                                                $saBankLoc,
                                                            ) {
                                                                if ($u->locations->contains('id', $sblId)) {
                                                                    return true;
                                                                }
                                                                if (
                                                                    $saBankLoc->parent_id &&
                                                                    $u->locations->contains('id', $saBankLoc->parent_id)
                                                                ) {
                                                                    return true;
                                                                }
                                                                return false;
                                                            });
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2 mb-1 p-2 border rounded shf-loc-row"
                                                            style="background:{{ $saLocUsers->isEmpty() ? '#fff5f5' : '#f0f9ff' }};font-size:0.72rem;">
                                                            <input type="hidden"
                                                                name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][location_overrides][{{ $saOverrideIdx }}][location_id]"
                                                                value="{{ $sblId }}">
                                                            <div style="min-width:120px;flex-shrink:0;">
                                                                <small
                                                                    class="fw-semibold">{{ $saBankLoc->parent?->name ? $saBankLoc->parent->name . '/' : '' }}{{ $saBankLoc->name }}</small>
                                                                <span
                                                                    class="shf-badge shf-badge-{{ $saBankLoc->isState() ? 'blue' : 'green' }}"
                                                                    class="shf-text-2xs">{{ $saBankLoc->type }}</span>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1 flex-grow-1">
                                                                @if ($saLocUsers->isNotEmpty())
                                                                    @foreach ($saLocUsers as $sau)
                                                                        @php $isChecked = in_array($sau->id, $saSavedUserIds); @endphp
                                                                        <label
                                                                            class="d-inline-flex align-items-center gap-1 border rounded px-1 py-1 {{ $isChecked ? 'border-primary' : '' }}"
                                                                            class="shf-text-xs" style="cursor:pointer;background:{{ $isChecked ? '#eef2ff' : '#fff' }};">
                                                                            <input type="checkbox"
                                                                                name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][location_overrides][{{ $saOverrideIdx }}][users][]"
                                                                                value="{{ $sau->id }}"
                                                                                class="shf-checkbox"
                                                                                style="width:10px;height:10px;"
                                                                                {{ $isChecked ? 'checked' : '' }}>
                                                                            {{ $sau->name }} <small
                                                                                class="text-muted">({{ $roleLabels[$sau->task_role] ?? $sau->task_role }})</small>
                                                                            <input type="radio"
                                                                                name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][location_overrides][{{ $saOverrideIdx }}][default]"
                                                                                value="{{ $sau->id }}"
                                                                                style="width:10px;height:10px;accent-color:#f15a29;"
                                                                                {{ $saSavedDefault == $sau->id ? 'checked' : '' }}>
                                                                        </label>
                                                                    @endforeach
                                                                @else
                                                                    <small class="text-danger">No eligible employees for
                                                                        this location. Assign users in User
                                                                        Management.</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @php $saOverrideIdx++; @endphp
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-3 mb-4">
                    <a href="{{ route('loan-settings.index') }}#products" class="btn-accent-outline"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                    <button type="submit" class="btn-accent">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    $row.append('<div class="shf-inline-error text-danger mt-1" style="font-size:0.7rem;">* ' + msg + '</div>');
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
