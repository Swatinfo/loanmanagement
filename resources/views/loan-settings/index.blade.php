@extends('layouts.app')

@section('header')
    <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
        <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        Loan Settings
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- SHF-style tabs (matching quotation settings) --}}
            <div class="shf-tabs">
                @php $activeTab = request('tab', 'locations'); @endphp
                <button class="shf-tab{{ $activeTab === 'locations' ? ' active' : '' }}"
                    data-tab="locations">Locations</button>
                <button class="shf-tab{{ $activeTab === 'banks' ? ' active' : '' }}" data-tab="banks">Banks</button>
                <button class="shf-tab{{ $activeTab === 'branches' ? ' active' : '' }}"
                    data-tab="branches">Branches</button>
                <button class="shf-tab{{ $activeTab === 'master-stages' ? ' active' : '' }}" data-tab="master-stages">Stage
                    Master</button>
                <button class="shf-tab{{ $activeTab === 'products' ? ' active' : '' }}" data-tab="products">Products &
                    Stages</button>
                <button class="shf-tab{{ $activeTab === 'role-permissions' ? ' active' : '' }}"
                    data-tab="role-permissions">Role Permissions</button>
            </div>

            <div class="shf-card" style="border-top-left-radius: 0; border-top-right-radius: 0;">

                {{-- Locations Tab --}}
                <div class="settings-tab-pane p-4" id="tab-locations"{!! $activeTab !== 'locations' ? ' style="display:none;"' : '' !!}>
                    <p class="small mb-3" style="color: #6b7280;">Manage states and cities. Branches, users, and products
                        can be assigned to locations.</p>

                    @php $locations = \App\Models\Location::with('children')->states()->orderBy('name')->get(); @endphp

                    @if (auth()->user()->hasPermission('manage_workflow_config'))
                        <div class="shf-add-form-wrapper mb-3">
                            <button class="shf-add-form-toggle collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#locationFormCollapse" aria-expanded="false" id="locationFormToggle">
                                <span id="locationFormTitle">+ Add Location</span>
                                <svg class="shf-chevron" style="width:16px;height:16px;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="collapse" id="locationFormCollapse">
                                <div class="shf-add-form-body">
                                    <form method="POST" action="{{ route('loan-settings.locations.store') }}"
                                        id="locationForm">
                                        @csrf
                                        <input type="hidden" name="id" id="locationEditId" value="">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="shf-form-label d-block mb-1">Type <span
                                                        class="text-danger">*</span></label>
                                                <select name="type" id="locationTypeInput" class="shf-input">
                                                    <option value="state">State</option>
                                                    <option value="city">City</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3" id="locationParentWrapper" style="display:none;">
                                                <label class="shf-form-label d-block mb-1">State <span
                                                        class="text-danger">*</span></label>
                                                <select name="parent_id" id="locationParentInput" class="shf-input">
                                                    <option value="">— Select State —</option>
                                                    @foreach ($locations as $state)
                                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="shf-form-label d-block mb-1">Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" id="locationNameInput"
                                                    class="shf-input" placeholder="e.g. Gujarat or Rajkot">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="shf-form-label d-block mb-1">Code</label>
                                                <input type="text" name="code" id="locationCodeInput"
                                                    class="shf-input" placeholder="e.g. GJ">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn-accent" id="locationSubmitBtn">
                                                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span id="locationSubmitText">Add</span>
                                                </button>
                                                <button type="button" class="btn-accent-outline shf-form-cancel"
                                                    data-collapse="#locationFormCollapse"
                                                    data-reset="locationForm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @foreach ($locations as $state)
                        <div class="mb-3 border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $state->name }}</strong>
                                    @if ($state->code)
                                        <small class="text-muted">({{ $state->code }})</small>
                                    @endif
                                    <span
                                        class="shf-badge shf-badge-blue ms-1 shf-text-2xs">{{ $state->children->count() }}
                                        cities</span>
                                    @if (!$state->is_active)
                                        <span class="shf-badge shf-badge-gray ms-1 shf-text-2xs">Inactive</span>
                                    @endif
                                </div>
                                @if (auth()->user()->hasPermission('manage_workflow_config'))
                                    <div class="d-flex gap-1">
                                        <button class="btn-accent-sm shf-edit-location" data-id="{{ $state->id }}"
                                            data-name="{{ $state->name }}" data-code="{{ $state->code }}"
                                            data-type="state" data-parent-id="">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                                        </button>
                                        <button class="btn-accent-sm shf-delete-item shf-text-xs"
                                            style="background:linear-gradient(135deg,#dc2626,#ef4444);"
                                            data-url="{{ route('loan-settings.locations.destroy', $state) }}">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if ($state->children->isNotEmpty())
                                <div class="mt-2 ps-3">
                                    @foreach ($state->children as $city)
                                        <div
                                            class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <div>
                                                <span class="text-muted" style="font-size:0.8rem;">↳</span>
                                                <span style="font-size:0.85rem;">{{ $city->name }}</span>
                                                @if ($city->code)
                                                    <small class="text-muted">({{ $city->code }})</small>
                                                @endif
                                                @if (!$city->is_active)
                                                    <span
                                                        class="shf-badge shf-badge-gray ms-1 shf-text-2xs">Inactive</span>
                                                @endif
                                            </div>
                                            @if (auth()->user()->hasPermission('manage_workflow_config'))
                                                <div class="d-flex gap-1">
                                                    <button class="btn-accent-sm shf-edit-location shf-text-xs"
                                                        data-id="{{ $city->id }}" data-name="{{ $city->name }}"
                                                        data-code="{{ $city->code }}" data-type="city"
                                                        data-parent-id="{{ $city->parent_id }}">
                                                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                                                    </button>
                                                    <button class="btn-accent-sm shf-delete-item shf-text-xs"
                                                        style="background:linear-gradient(135deg,#dc2626,#ef4444);"
                                                        data-url="{{ route('loan-settings.locations.destroy', $city) }}">
                                                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Delete
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                </div>

                {{-- Banks Tab --}}
                <div class="settings-tab-pane p-4" id="tab-banks"{!! $activeTab !== 'banks' ? ' style="display:none;"' : '' !!}>
                    @if (auth()->user()->hasPermission('manage_workflow_config'))
                        <div class="shf-add-form-wrapper mb-3">
                            <button class="shf-add-form-toggle collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#bankFormCollapse" aria-expanded="false" id="bankFormToggle">
                                <span id="bankFormTitle">+ Add Bank</span>
                                <svg class="shf-chevron" style="width:16px;height:16px;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="collapse" id="bankFormCollapse">
                                <div class="shf-add-form-body">
                                    <form method="POST" action="{{ route('loan-settings.banks.store') }}"
                                        id="bankForm">
                                        @csrf
                                        <input type="hidden" name="id" id="bankEditId" value="">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="shf-form-label d-block mb-1">Bank Name</label>
                                                <input type="text" name="name" id="bankNameInput"
                                                    class="shf-input" placeholder="e.g. State Bank of India"
                                                    value="{{ old('name') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="shf-form-label d-block mb-1">Code</label>
                                                <input type="text" name="code" id="bankCodeInput"
                                                    class="shf-input" placeholder="e.g. SBI"
                                                    value="{{ old('code') }}">
                                            </div>
                                            <div class="col-md-4" id="bankLocationSection" style="display:none;">
                                                <label class="shf-form-label d-block mb-1">Available Locations</label>
                                                <div
                                                    style="max-height:220px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
                                                    @php $bankLocStates = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get(); @endphp
                                                    @foreach ($bankLocStates as $bls)
                                                        <div class="mb-2">
                                                            <label
                                                                class="d-flex align-items-center gap-1 fw-semibold shf-text-sm"
                                                                style="cursor:pointer;">
                                                                <input type="checkbox" name="bank_locations[]"
                                                                    value="{{ $bls->id }}"
                                                                    class="shf-checkbox bank-loc-check">
                                                                {{ $bls->name }}
                                                            </label>
                                                            @foreach ($bls->children->where('is_active', true) as $blc)
                                                                <label
                                                                    class="d-flex align-items-center gap-1 ps-3 shf-text-xs"
                                                                    style="cursor:pointer;">
                                                                    <input type="checkbox" name="bank_locations[]"
                                                                        value="{{ $blc->id }}"
                                                                        class="shf-checkbox bank-loc-check">
                                                                    {{ $blc->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn-accent" id="bankSubmitBtn">
                                                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span id="bankSubmitText">Add Bank</span>
                                                </button>
                                                <button type="button" class="btn-accent-outline shf-form-cancel"
                                                    data-collapse="#bankFormCollapse" data-reset="bankForm"
                                                    onclick="resetBankForm()"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach ($banks as $bank)
                        <div class="py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $bank->name }}</strong>
                                    @if ($bank->code)
                                        <small class="text-muted">({{ $bank->code }})</small>
                                    @endif
                                    <small class="text-muted ms-2">{{ $bank->products->count() }} products</small>

                                    {{-- Assigned locations --}}
                                    @if ($bank->locations->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach ($bank->locations as $bankLoc)
                                                <span class="shf-badge shf-badge-blue shf-text-xs">
                                                    📍
                                                    {{ $bankLoc->parent?->name ? $bankLoc->parent->name . '/' : '' }}{{ $bankLoc->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-1"><small class="text-muted fst-italic">All locations</small>
                                        </div>
                                    @endif
                                </div>
                                @if (auth()->user()->hasPermission('manage_workflow_config'))
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        <button class="btn-accent-sm shf-edit-bank" data-id="{{ $bank->id }}"
                                            data-name="{{ $bank->name }}" data-code="{{ $bank->code }}"
                                            data-location-ids="{{ $bank->locations->pluck('id')->toJson() }}">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button class="btn-accent-sm shf-delete-item"
                                            style="background:linear-gradient(135deg,#dc2626,#ef4444);"
                                            data-url="{{ route('loan-settings.banks.destroy', $bank) }}">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Branches Tab --}}
                <div class="settings-tab-pane p-4" id="tab-branches"{!! $activeTab !== 'branches' ? ' style="display:none;"' : '' !!}>
                    @if (auth()->user()->hasPermission('manage_workflow_config'))
                        <div class="shf-add-form-wrapper mb-3">
                            <button class="shf-add-form-toggle collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#branchFormCollapse" aria-expanded="false" id="branchFormToggle">
                                <span id="branchFormTitle">+ Add Branch</span>
                                <svg class="shf-chevron" style="width:16px;height:16px;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="collapse" id="branchFormCollapse">
                                <div class="shf-add-form-body">
                                    <form method="POST" action="{{ route('loan-settings.branches.store') }}"
                                        id="branchForm">
                                        @csrf
                                        <input type="hidden" name="id" id="branchEditId" value="">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="shf-form-label d-block mb-1">Branch Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" id="branchNameInput"
                                                    class="shf-input" placeholder="e.g. Ahmedabad Office"
                                                    value="{{ old('name') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="shf-form-label d-block mb-1">Code</label>
                                                <input type="text" name="code" id="branchCodeInput"
                                                    class="shf-input" placeholder="e.g. AHM"
                                                    value="{{ old('code') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="shf-form-label d-block mb-1">City</label>
                                                <input type="text" name="city" id="branchCityInput"
                                                    class="shf-input" placeholder="City" value="{{ old('city') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="shf-form-label d-block mb-1">Phone</label>
                                                <input type="text" name="phone" id="branchPhoneInput"
                                                    class="shf-input" placeholder="Phone" value="{{ old('phone') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="shf-form-label d-block mb-1">City / Location</label>
                                                <select name="location_id" id="branchLocationInput" class="shf-input">
                                                    <option value="">— Select City —</option>
                                                    @php $locStates = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get(); @endphp
                                                    @foreach ($locStates as $locState)
                                                        <optgroup label="{{ $locState->name }}">
                                                            @foreach ($locState->children->where('is_active', true) as $locCity)
                                                                <option value="{{ $locCity->id }}">{{ $locCity->name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="shf-form-label d-block mb-1">Branch Manager</label>
                                                <select name="manager_id" id="branchManagerInput" class="shf-input">
                                                    <option value="">— Select Manager —</option>
                                                    @foreach ($allActiveUsers->whereIn('task_role', ['branch_manager', 'loan_advisor']) as $mgr)
                                                        <option value="{{ $mgr->id }}">{{ $mgr->name }}
                                                            ({{ $mgr->task_role_label }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn-accent"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</button>
                                                <button type="button" class="btn-accent-outline shf-form-cancel"
                                                    data-collapse="#branchFormCollapse"
                                                    data-reset="branchForm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach ($branches as $branch)
                        <div class="py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <strong>{{ $branch->name }}</strong>
                                    @if ($branch->code)
                                        <small class="text-muted">({{ $branch->code }})</small>
                                    @endif
                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                        @if ($branch->location)
                                            <span
                                                class="shf-badge shf-badge-gray shf-text-2xs">{{ $branch->location->parent?->name ? $branch->location->parent->name . ' / ' : '' }}{{ $branch->location->name }}</span>
                                        @elseif($branch->city)
                                            <small class="text-muted">{{ $branch->city }}</small>
                                        @endif
                                        @if ($branch->phone)
                                            <small class="text-muted">{{ $branch->phone }}</small>
                                        @endif
                                    </div>
                                    @if ($branch->manager)
                                        <small class="text-muted d-block mt-1">Manager:
                                            <strong>{{ $branch->manager->name }}</strong></small>
                                    @endif
                                </div>
                                @if (auth()->user()->hasPermission('manage_workflow_config'))
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button class="btn-accent-sm shf-text-xs shf-edit-branch"
                                            data-id="{{ $branch->id }}" data-name="{{ $branch->name }}"
                                            data-code="{{ $branch->code }}" data-city="{{ $branch->city }}"
                                            data-phone="{{ $branch->phone }}"
                                            data-manager-id="{{ $branch->manager_id }}"
                                            data-location-id="{{ $branch->location_id }}"><svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit</button>
                                        <button class="btn-accent-sm shf-text-xs shf-delete-item"
                                            style="background:linear-gradient(135deg,#dc2626,#ef4444);"
                                            data-url="{{ route('loan-settings.branches.destroy', $branch) }}">
                                            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Stage Master Tab --}}
                <div class="settings-tab-pane p-4" id="tab-master-stages"{!! $activeTab !== 'master-stages' ? ' style="display:none;"' : '' !!}>
                    <p class="text-muted mb-3">Global stage configuration. Set the default responsible role for each stage.
                        Product-specific stages inherit from these defaults.</p>

                    <form method="POST" action="{{ route('loan-settings.master-stages.save') }}" id="masterStagesForm">
                        @csrf
                        @php
                            $mainStages = $stages->whereNull('parent_stage_key');
                            $childStages = $stages->whereNotNull('parent_stage_key');
                        @endphp

                        {{-- Column header --}}
                        @foreach ($stages as $stage)
                            @php
                                $isParallelHeader = $stage->is_parallel && !$stage->parent_stage_key;
                                $hasSubActions = !empty($stage->sub_actions) && is_array($stage->sub_actions);
                            @endphp
                            <div class="shf-stage-card {{ $stage->parent_stage_key ? 'shf-stage-card--child' : '' }}">
                                <input type="hidden" name="stages[{{ $loop->index }}][id]"
                                    value="{{ $stage->id }}">

                                <div class="shf-stage-header">
                                    <div class="shf-stage-header-title">
                                        @if ($stage->parent_stage_key)
                                            <span class="text-muted">↳</span>
                                        @endif
                                        <strong
                                            class="{{ $stage->parent_stage_key ? 'fw-medium' : '' }}">{{ $stage->stage_name_en }}</strong>
                                        @if ($stage->stage_name_gu)
                                            <small
                                                class="text-muted d-none d-sm-inline">({{ $stage->stage_name_gu }})</small>
                                        @endif
                                        @if ($stage->is_parallel)
                                            <span class="shf-badge shf-badge-blue shf-text-2xs">Parallel</span>
                                        @endif
                                        @if ($stage->stage_type === 'decision')
                                            <span class="shf-badge shf-badge-orange shf-text-2xs">Decision</span>
                                        @endif
                                        @if ($hasSubActions)
                                            <span
                                                class="shf-badge shf-badge-orange shf-text-2xs">{{ count($stage->sub_actions) }}
                                                sub-stages</span>
                                        @endif
                                    </div>
                                    @if (!$isParallelHeader && !$hasSubActions)
                                        <input type="hidden" name="stages[{{ $loop->index }}][is_enabled]"
                                            value="0">
                                        <input type="checkbox" name="stages[{{ $loop->index }}][is_enabled]"
                                            value="1" class="shf-toggle shf-master-stage-toggle"
                                            {{ $stage->is_enabled ? 'checked' : '' }}>
                                    @else
                                        <input type="hidden" name="stages[{{ $loop->index }}][is_enabled]"
                                            value="1">
                                    @endif
                                </div>

                                {{-- Description + notes + roles --}}
                                <div class="shf-stage-body">
                                    @if ($stage->description_en)
                                        <small class="text-muted d-block shf-text-xs">{{ $stage->description_en }}</small>
                                    @endif
                                    @php
                                        $stagePhases = match ($stage->stage_key) {
                                            'inquiry' => [
                                                [
                                                    'role' => 'Loan Advisor / Branch Manager',
                                                    'action' => 'Creates loan with customer details',
                                                ],
                                                ['note' => 'Auto-completed when converting from quotation'],
                                            ],
                                            'document_selection' => [
                                                [
                                                    'role' => 'Loan Advisor / Branch Manager',
                                                    'action' => 'Selects required documents for the loan type',
                                                ],
                                                ['note' => 'Auto-completed when converting from quotation'],
                                            ],
                                            'document_collection' => [
                                                [
                                                    'role' => 'Loan Advisor',
                                                    'action' =>
                                                        'Collects and verifies all required documents from customer',
                                                ],
                                                ['role' => 'Loan Advisor', 'action' => 'Uploads document files'],
                                                [
                                                    'note' =>
                                                        'Cannot complete until all required documents are collected',
                                                ],
                                            ],
                                            'parallel_processing' => [
                                                ['note' => 'Container — all sub-stages run simultaneously'],
                                                ['note' => 'Application Number must complete first to unlock others'],
                                            ],
                                            'app_number' => [
                                                [
                                                    'role' => 'Loan Advisor',
                                                    'action' => 'Enters bank application number',
                                                ],
                                                [
                                                    'role' => 'Loan Advisor',
                                                    'action' => 'Selects docket timeline (S+1 / S+2 / S+3 / Custom)',
                                                ],
                                                ['note' => 'Must complete before other parallel stages start'],
                                            ],
                                            'bsm_osv' => [
                                                [
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Performs BSM/OSV site verification',
                                                ],
                                                [
                                                    'note' =>
                                                        'Auto-assigned to default bank employee. Clicks Complete when done',
                                                ],
                                            ],
                                            'legal_verification' => [
                                                [
                                                    'phase' => '1',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Enters suggested legal advisor name → Send to Bank',
                                                ],
                                                [
                                                    'phase' => '2',
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Confirms legal advisor name → Initiate Legal',
                                                ],
                                                [
                                                    'phase' => '3',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Reviews, can reassign or Complete',
                                                ],
                                            ],
                                            'technical_valuation' => [
                                                [
                                                    'role' => 'Office Employee',
                                                    'action' =>
                                                        'Fills property valuation form (land area, rate, construction, lat/lng)',
                                                ],
                                                ['note' => 'Stage auto-completes when form is saved'],
                                            ],
                                            'property_valuation' => [
                                                [
                                                    'role' => 'Office Employee',
                                                    'action' =>
                                                        'Fills property valuation form (same as Technical Valuation)',
                                                ],
                                                ['note' => 'Dedicated property valuation for LAP products'],
                                            ],
                                            'rate_pf' => [
                                                [
                                                    'phase' => '1',
                                                    'role' => 'Loan Advisor',
                                                    'action' => 'Fills all rate & charges fields → Send to Bank',
                                                ],
                                                [
                                                    'phase' => '2',
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Reviews/edits all fields → Save & Return',
                                                ],
                                                [
                                                    'phase' => '3',
                                                    'role' => 'Loan Advisor',
                                                    'action' => 'Reviews changes (original values shown) → Complete',
                                                ],
                                            ],
                                            'sanction' => [
                                                [
                                                    'phase' => '1',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Sends for sanction letter generation → Bank',
                                                ],
                                                [
                                                    'phase' => '2',
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Marks sanction letter generated → Returns',
                                                ],
                                                [
                                                    'phase' => '3',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Enters sanction date, amount, EMI → Complete',
                                                ],
                                                ['note' => 'Docket date auto-calculated from sanction date + offset'],
                                            ],
                                            'docket' => [
                                                [
                                                    'phase' => '1',
                                                    'role' => 'Loan Advisor',
                                                    'action' => 'Enters login date → Send to Office Employee',
                                                ],
                                                [
                                                    'phase' => '2',
                                                    'role' => 'Office Employee',
                                                    'action' => 'Reviews and clicks Complete → Returns to owner',
                                                ],
                                                [
                                                    'phase' => '3',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Clicks Complete to finish stage',
                                                ],
                                                ['note' => 'Shows expected docket date with days remaining/overdue'],
                                            ],
                                            'kfs' => [
                                                [
                                                    'role' => 'Loan Advisor / Branch Manager / Office Employee',
                                                    'action' => 'Clicks Complete when KFS document has been generated',
                                                ],
                                            ],
                                            'esign' => [
                                                [
                                                    'phase' => '1',
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Generates E-Sign & eNACH → Sends to Task Owner',
                                                ],
                                                [
                                                    'phase' => '2',
                                                    'role' => 'Task Owner',
                                                    'action' => 'Completes signing with customer → Returns to Bank',
                                                ],
                                                [
                                                    'phase' => '3',
                                                    'role' => 'Bank Employee',
                                                    'action' => 'Confirms and clicks Complete',
                                                ],
                                            ],
                                            'disbursement' => [
                                                [
                                                    'role' => 'Loan Advisor / Branch Manager',
                                                    'action' => 'Chooses Fund Transfer or Cheque',
                                                ],
                                                ['note' => 'Fund Transfer → loan completes immediately'],
                                                ['note' => 'Cheque → enters multiple cheque details → advances to OTC'],
                                            ],
                                            'otc_clearance' => [
                                                [
                                                    'role' => 'Task Owner',
                                                    'action' =>
                                                        'Enters cheque handover date, or assigns to Office Employee',
                                                ],
                                                [
                                                    'role' => 'Office Employee',
                                                    'action' => 'Enters handover date if assigned',
                                                ],
                                                ['note' => 'When completed, loan is marked as Completed'],
                                            ],
                                            default => null,
                                        };
                                    @endphp
                                    @if ($stagePhases)
                                        @php
                                            $roleCssClass = function ($role) {
                                                if (str_contains($role, 'Bank Employee')) {
                                                    return 'shf-role-bank-employee';
                                                }
                                                if (str_contains($role, 'Office Employee')) {
                                                    return 'shf-role-office-employee';
                                                }
                                                if (str_contains($role, 'Branch Manager')) {
                                                    return 'shf-role-branch-manager';
                                                }
                                                if (str_contains($role, 'Loan Advisor')) {
                                                    return 'shf-role-loan-advisor';
                                                }
                                                return 'shf-role-task-owner';
                                            };
                                            $roleBgClass = function ($role) {
                                                if (str_contains($role, 'Bank Employee')) {
                                                    return 'shf-role-bg-bank-employee';
                                                }
                                                if (str_contains($role, 'Office Employee')) {
                                                    return 'shf-role-bg-office-employee';
                                                }
                                                if (str_contains($role, 'Branch Manager')) {
                                                    return 'shf-role-bg-branch-manager';
                                                }
                                                if (str_contains($role, 'Loan Advisor')) {
                                                    return 'shf-role-bg-loan-advisor';
                                                }
                                                return 'shf-role-bg-task-owner';
                                            };
                                            $masterTransfer = match ($stage->stage_key) {
                                                'inquiry',
                                                'document_selection',
                                                'document_collection',
                                                'app_number'
                                                    => 'Transfer to: Loan Advisor, Branch Manager',
                                                'bsm_osv' => 'Transfer to: Bank Employee (same bank)',
                                                'legal_verification' => 'Auto-transfers: Task Owner ↔ Bank Employee',
                                                'technical_valuation',
                                                'property_valuation'
                                                    => 'Transfer to: Office Employee, Branch Manager',
                                                'rate_pf'
                                                    => 'Auto-transfers: Loan Advisor → Bank Employee → Loan Advisor',
                                                'sanction' => 'Auto-transfers: Task Owner → Bank Employee → Task Owner',
                                                'docket'
                                                    => 'Auto-transfers: Loan Advisor → Office Employee → Task Owner',
                                                'kfs' => 'Transfer to: Loan Advisor, Branch Manager, Office Employee',
                                                'esign' => 'Auto-transfers: Bank Employee → Task Owner → Bank Employee',
                                                'disbursement' => 'Transfer to: Loan Advisor, Branch Manager',
                                                'otc_clearance'
                                                    => 'Transfer to: Office Employee, Loan Advisor, Branch Manager',
                                                default => null,
                                            };
                                        @endphp
                                        <div class="shf-stage-notes">
                                            @foreach ($stagePhases as $sp)
                                                @if (isset($sp['phase']))
                                                    <div class="shf-phase-step">
                                                        <span
                                                            class="shf-phase-num {{ $roleBgClass($sp['role']) }}">{{ $sp['phase'] }}</span>
                                                        <span><strong
                                                                class="{{ $roleCssClass($sp['role']) }}">{{ $sp['role'] }}</strong>
                                                            — {{ $sp['action'] }}</span>
                                                    </div>
                                                @elseif(isset($sp['role']))
                                                    <div>
                                                        <span class="shf-role-dot {{ $roleBgClass($sp['role']) }}"></span>
                                                        <strong
                                                            class="{{ $roleCssClass($sp['role']) }}">{{ $sp['role'] }}</strong>
                                                        — {{ $sp['action'] }}
                                                    </div>
                                                @elseif(isset($sp['note']))
                                                    <div class="shf-note-line">
                                                        <svg style="width:12px;height:12px;display:inline;vertical-align:middle;"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $sp['note'] }}
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if ($masterTransfer)
                                                <div class="shf-transfer-line">
                                                    <svg style="width:12px;height:12px;display:inline;vertical-align:middle;"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                    {{ $masterTransfer }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="shf-stage-roles">
                                        @if ($isParallelHeader)
                                            <small class="text-muted">— Group label —</small>
                                        @elseif($hasSubActions)
                                            <small class="text-muted">— See sub-stages —</small>
                                        @else
                                            @php $stageRoles = is_array($stage->default_role) ? $stage->default_role : ($stage->default_role ? [$stage->default_role] : []); @endphp
                                            <div class="shf-role-section-label d-block">Eligible Roles</div>
                                            <div class="d-flex flex-wrap gap-1 shf-role-checkboxes">
                                                @foreach (\App\Models\User::TASK_ROLE_LABELS as $role => $label)
                                                    <label
                                                        class="shf-role-checkbox-label d-inline-flex align-items-center gap-1 me-1">
                                                        <input type="checkbox"
                                                            name="stages[{{ $loop->parent->index }}][default_role][]"
                                                            value="{{ $role }}" class="shf-checkbox"
                                                            {{ in_array($role, $stageRoles) ? 'checked' : '' }}>
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>{{-- close shf-stage-body --}}
                            </div>{{-- close shf-stage-card --}}

                            {{-- Sub-actions with role assignment + enable/disable --}}
                            @if ($hasSubActions)
                                @foreach ($stage->sub_actions as $saIdx => $subAction)
                                    @php $saEnabled = $subAction['is_enabled'] ?? true; @endphp
                                    <div class="shf-stage-card shf-stage-card--child"
                                        style="margin-left:36px;border-left-color:#d97706;">
                                        <div class="shf-stage-header">
                                            <div class="shf-stage-header-title">
                                                <span class="text-muted">⤷</span>
                                                <strong
                                                    class="fw-medium">{{ $subAction['name'] ?? $subAction['key'] }}</strong>
                                                <span
                                                    class="shf-badge shf-badge-{{ ($subAction['type'] ?? '') === 'action_button' ? 'orange' : 'blue' }} shf-text-2xs">
                                                    {{ ($subAction['type'] ?? '') === 'action_button' ? 'Action' : 'Form' }}
                                                </span>
                                                @if (!empty($subAction['transfer_to_role']))
                                                    <span class="text-muted shf-text-xs">→
                                                        {{ $subAction['transfer_to_role'] }}</span>
                                                @endif
                                            </div>
                                            <input type="hidden"
                                                name="stages[{{ $loop->parent->index }}][sub_actions][{{ $saIdx }}][is_enabled]"
                                                value="0">
                                            <input type="checkbox"
                                                name="stages[{{ $loop->parent->index }}][sub_actions][{{ $saIdx }}][is_enabled]"
                                                value="1" class="shf-toggle shf-master-substage-toggle"
                                                {{ $saEnabled ? 'checked' : '' }}>
                                        </div>
                                        <div class="shf-stage-body">
                                            @php $saRoles = $subAction['roles'] ?? []; @endphp
                                            <div class="shf-substage-role-label d-block">Sub-action Roles</div>
                                            <div class="d-flex flex-wrap gap-1 shf-role-checkboxes">
                                                @foreach (\App\Models\User::TASK_ROLE_LABELS as $role => $label)
                                                    <label
                                                        class="shf-substage-checkbox-label d-inline-flex align-items-center gap-1 me-1">
                                                        <input type="checkbox"
                                                            name="stages[{{ $loop->parent->parent->index }}][sub_actions][{{ $saIdx }}][roles][]"
                                                            value="{{ $role }}" class="shf-checkbox"
                                                            {{ in_array($role, $saRoles) ? 'checked' : '' }}>
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach

                        @if (auth()->user()->hasPermission('manage_workflow_config'))
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn-accent">
                                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Stage Defaults
                                </button>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Products & Stages Tab --}}
                <div class="settings-tab-pane p-4" id="tab-products"{!! $activeTab !== 'products' ? ' style="display:none;"' : '' !!}>
                    @if (auth()->user()->hasPermission('manage_workflow_config'))
                        <div class="shf-add-form-wrapper mb-3">
                            <button class="shf-add-form-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#productFormCollapse" aria-expanded="false">
                                <span>+ Add Product</span>
                                <svg class="shf-chevron" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="collapse" id="productFormCollapse">
                                <div class="shf-add-form-body">
                                    <form method="POST" action="{{ route('loan-settings.products.store') }}" id="productForm">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="shf-form-label d-block mb-1">Bank</label>
                                                <select name="bank_id" class="shf-input" required>
                                                    <option value="">Select bank...</option>
                                                    @foreach ($banks as $b)
                                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="shf-form-label d-block mb-1">Product Name</label>
                                                <input type="text" name="name" class="shf-input" placeholder="e.g. Home Loan" required>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn-accent"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Product</button>
                                                <button type="button" class="btn-accent-outline shf-form-cancel" data-collapse="#productFormCollapse" data-reset="productForm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach ($banks as $bank)
                        <div class="shf-stage-card">
                            <div class="shf-stage-header">
                                <div class="shf-stage-header-title">
                                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <strong>{{ $bank->name }}</strong>
                                    <span class="shf-badge shf-badge-gray shf-text-2xs">{{ $bank->products->count() }}
                                        products</span>
                                </div>
                            </div>
                            <div class="shf-stage-body" style="padding:0;">
                                @foreach ($bank->products as $product)
                                    <div class="px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <strong>{{ $product->name }}</strong>
                                                @if ($product->code)
                                                    <small class="text-muted">({{ $product->code }})</small>
                                                @endif
                                                @if ($product->productStages->count() > 0)
                                                    <span
                                                        class="shf-badge shf-badge-green shf-text-2xs">{{ $product->productStages->where('is_enabled', true)->count() }}
                                                        stages</span>
                                                @endif
                                                @if ($product->locations->isNotEmpty())
                                                    <span
                                                        class="shf-badge shf-badge-gray shf-text-2xs">{{ $product->locations->pluck('name')->implode(', ') }}</span>
                                                @else
                                                    <span class="shf-badge shf-badge-gray shf-text-2xs">All
                                                        locations</span>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-1 flex-shrink-0">
                                                <a href="{{ route('loan-settings.product-stages', $product) }}"
                                                    class="btn-accent-sm shf-text-xs"><svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Stages</a>
                                                <button type="button"
                                                    class="btn-accent-sm shf-toggle-product-locations shf-text-xs"
                                                    style="background:linear-gradient(135deg,#6b7280,#9ca3af);"
                                                    data-target="#product-locs-{{ $product->id }}"><svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Locations</button>
                                                @if (auth()->user()->hasPermission('manage_workflow_config'))
                                                    <button class="btn-accent-sm shf-delete-item shf-text-xs"
                                                        style="background:linear-gradient(135deg,#dc2626,#ef4444);"
                                                        data-url="{{ route('loan-settings.products.destroy', $product) }}">
                                                        <svg style="width:12px;height:12px;" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Inline Location Config (collapsed) --}}
                                        <div id="product-locs-{{ $product->id }}" style="display:none;"
                                            class="mt-2 pb-2">
                                            <form method="POST"
                                                action="{{ route('loan-settings.product-locations.save', $product) }}">
                                                @csrf
                                                @php
                                                    $productLocIds = $product->locations->pluck('id')->toArray();
                                                    $bankLocIds = $bank->locations->pluck('id')->toArray();
                                                    $bankLocParentIds = $bank->locations
                                                        ->whereNotNull('parent_id')
                                                        ->pluck('parent_id')
                                                        ->unique()
                                                        ->toArray();
                                                @endphp
                                                <div class="p-2 border rounded" style="background:#f8fafc;">
                                                    @if ($bank->locations->isEmpty())
                                                        <small class="text-muted">Bank has no locations assigned. Configure
                                                            bank locations first.</small>
                                                    @else
                                                        <small class="text-muted d-block mb-2 shf-text-xs">Select cities
                                                            where this product is
                                                            available (from {{ $bank->name }}'s locations).</small>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach (\App\Models\Location::with('children')->states()->active()->orderBy('name')->get() as $ls)
                                                                @php
                                                                    // Only show states/cities that belong to this bank
                                                                    $stateInBank = in_array($ls->id, $bankLocIds);
                                                                    $bankCities = $ls->children
                                                                        ->where('is_active', true)
                                                                        ->whereIn('id', $bankLocIds);
                                                                @endphp
                                                                @if ($stateInBank || $bankCities->isNotEmpty())
                                                                    <div style="min-width:130px;">
                                                                        @if ($stateInBank)
                                                                            <label
                                                                                class="d-flex align-items-center gap-1 fw-semibold"
                                                                                style="font-size:0.75rem;cursor:pointer;">
                                                                                <input type="checkbox" name="locations[]"
                                                                                    value="{{ $ls->id }}"
                                                                                    class="shf-checkbox"
                                                                                    style="width:12px;height:12px;"
                                                                                    {{ in_array($ls->id, $productLocIds) ? 'checked' : '' }}>
                                                                                {{ $ls->name }}
                                                                            </label>
                                                                        @else
                                                                            <small class="fw-semibold d-block"
                                                                                style="font-size:0.75rem;">{{ $ls->name }}</small>
                                                                        @endif
                                                                        @foreach ($bankCities as $lc)
                                                                            <label
                                                                                class="d-flex align-items-center gap-1 ps-3"
                                                                                style="cursor:pointer;"
                                                                                class="shf-text-xs">
                                                                                <input type="checkbox" name="locations[]"
                                                                                    value="{{ $lc->id }}"
                                                                                    class="shf-checkbox"
                                                                                    style="width:12px;height:12px;"
                                                                                    {{ in_array($lc->id, $productLocIds) ? 'checked' : '' }}>
                                                                                {{ $lc->name }}
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div class="mt-2 d-flex justify-content-end gap-2">
                                                        <button type="submit" class="btn-accent-sm shf-text-xs">
                                                            <svg style="width:12px;height:12px;" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Save Locations
                                                        </button>
                                                        <button type="button" class="btn-accent-outline shf-close-product-locs" data-target="#product-locs-{{ $product->id }}"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                @if ($bank->products->isEmpty())
                                    <p class="text-muted small px-3 py-3">No products yet</p>
                                @endif
                            </div>{{-- close shf-stage-body --}}
                        </div>{{-- close shf-stage-card --}}
                    @endforeach


                </div>

                {{-- ═══ Role Permissions Tab ═══ --}}
                <div class="settings-tab-pane p-4" id="tab-role-permissions"{!! $activeTab !== 'role-permissions' ? ' style="display:none;"' : '' !!}>
                    <div class="shf-section">
                        <div class="shf-section-header">
                            <div class="shf-section-number">
                                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="shf-section-title">Task Role × Permission Matrix</span>
                        </div>
                        <div class="shf-section-body">
                            <p class="small mb-4" style="color: #6b7280;">
                                Configure which loan permissions each task role has. These are additive to system role
                                permissions.
                            </p>

                            @php $groupedLoanPerms = $loanPermissions->groupBy('group'); @endphp

                            @if (auth()->user()->hasPermission('manage_workflow_config'))
                                <form action="{{ route('loan-settings.task-role-permissions.save') }}" method="POST">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Permission</th>
                                                    @foreach (\App\Models\User::TASK_ROLE_LABELS as $role => $label)
                                                        <th class="text-center">{{ $label }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($groupedLoanPerms as $group => $perms)
                                                    <tr style="background: var(--accent-dim);">
                                                        <td colspan="{{ count(\App\Models\User::TASK_ROLES) + 1 }}"
                                                            class="font-display fw-semibold small"
                                                            style="color: #f15a29; text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 16px;">
                                                            {{ $group }}
                                                        </td>
                                                    </tr>
                                                    @foreach ($perms as $perm)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-medium">{{ $perm->name }}</span>
                                                                @if ($perm->description)
                                                                    <span class="d-block small"
                                                                        style="color: #9ca3af;">{{ $perm->description }}</span>
                                                                @endif
                                                            </td>
                                                            @foreach (\App\Models\User::TASK_ROLES as $role)
                                                                <td class="text-center">
                                                                    <input type="checkbox"
                                                                        name="task_role[{{ $role }}][]"
                                                                        value="{{ $perm->id }}"
                                                                        {{ in_array($perm->id, $taskRolePermissions[$role] ?? []) ? 'checked' : '' }}
                                                                        class="shf-checkbox"
                                                                        style="width:16px;height:16px;">
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn-accent">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Save Permissions
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Permission</th>
                                                @foreach (\App\Models\User::TASK_ROLE_LABELS as $role => $label)
                                                    <th class="text-center">{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedLoanPerms as $group => $perms)
                                                <tr style="background: var(--accent-dim);">
                                                    <td colspan="{{ count(\App\Models\User::TASK_ROLES) + 1 }}"
                                                        class="font-display fw-semibold small"
                                                        style="color: #f15a29; text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 16px;">
                                                        {{ $group }}
                                                    </td>
                                                </tr>
                                                @foreach ($perms as $perm)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-medium">{{ $perm->name }}</span>
                                                            @if ($perm->description)
                                                                <span class="d-block small"
                                                                    style="color: #9ca3af;">{{ $perm->description }}</span>
                                                            @endif
                                                        </td>
                                                        @foreach (\App\Models\User::TASK_ROLES as $role)
                                                            <td class="text-center">
                                                                <input type="checkbox"
                                                                    {{ in_array($perm->id, $taskRolePermissions[$role] ?? []) ? 'checked' : '' }}
                                                                    disabled class="shf-checkbox"
                                                                    style="width:16px;height:16px;opacity:1;cursor:not-allowed;">
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            $(function() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                // SHF tab switching (same pattern as quotation settings)
                $('.shf-tab').on('click', function() {
                    var tab = $(this).data('tab');
                    $('.shf-tab').removeClass('active');
                    $(this).addClass('active');
                    $('.settings-tab-pane').hide();
                    $('#tab-' + tab).show();
                    history.replaceState(null, '', '#' + tab);
                });
                // Restore tab from URL hash on page load, or show banks tab if there are validation errors
                var hash = window.location.hash.substring(1);
                @if ($errors->any() && old('manager_id') !== null)
                    hash = 'branches';
                @elseif ($errors->any())
                    hash = 'banks';
                @endif
                if (hash && $('#tab-' + hash).length) {
                    $('.shf-tab').removeClass('active');
                    $('.shf-tab[data-tab="' + hash + '"]').addClass('active');
                    $('.settings-tab-pane').hide();
                    $('#tab-' + hash).show();
                }

                // Cancel button — reset form + close collapse
                $(document).on('click', '.shf-form-cancel', function() {
                    var collapseId = $(this).data('collapse');
                    var formId = $(this).data('reset');

                    // Reset form fields
                    if (formId) {
                        var $form = $('#' + formId);
                        $form[0].reset();
                        $form.find('input[type="hidden"]').not('input[name="_token"]').val('');
                        $form.find('.is-invalid').removeClass('is-invalid');
                    }

                    // Reset titles back to Add
                    if (formId === 'locationForm') {
                        $('#locationFormTitle').text('+ Add Location');
                        $('#locationSubmitText').text('Add');
                    } else if (formId === 'bankForm') {
                        resetBankForm();
                    } else if (formId === 'branchForm') {
                        $('#branchFormTitle').text('+ Add Branch');
                    }

                    // Close collapse
                    if (collapseId) {
                        var $collapse = $(collapseId);
                        if ($collapse.hasClass('show')) {
                            bootstrap.Collapse.getOrCreateInstance($collapse[0]).hide();
                        }
                    }
                });

                // Location form — type toggle
                $('#locationTypeInput').on('change', function() {
                    $('#locationParentWrapper').toggle($(this).val() === 'city');
                });
                $(document).on('click', '.shf-edit-location', function() {
                    $('#locationEditId').val($(this).data('id'));
                    $('#locationNameInput').val($(this).data('name'));
                    $('#locationCodeInput').val($(this).data('code'));
                    $('#locationTypeInput').val($(this).data('type')).trigger('change');
                    $('#locationParentInput').val($(this).data('parent-id'));
                    $('#locationFormTitle').text('Edit Location');
                    $('#locationSubmitText').text('Update');
                    var $collapse = $('#locationFormCollapse');
                    if (!$collapse.hasClass('show')) {
                        new bootstrap.Collapse($collapse[0], {
                            toggle: true
                        });
                    }
                    $collapse[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });

                // Edit bank — populate form with locations
                $(document).on('click', '.shf-edit-bank', function() {
                    $('#bankEditId').val($(this).data('id'));
                    $('#bankNameInput').val($(this).data('name'));
                    $('#bankCodeInput').val($(this).data('code'));

                    // Reset location checkboxes
                    $('.bank-loc-check').prop('checked', false);

                    // Check assigned locations
                    var locationIds = $(this).data('location-ids') || [];
                    locationIds.forEach(function(id) {
                        $('.bank-loc-check[value="' + id + '"]').prop('checked', true);
                    });

                    $('#bankLocationSection').show();
                    $('#bankFormTitle').text('Edit Bank');
                    $('#bankSubmitText').text('Update Bank');
                    var $collapse = $('#bankFormCollapse');
                    if (!$collapse.hasClass('show')) {
                        new bootstrap.Collapse($collapse[0], {
                            toggle: true
                        });
                    }
                    $collapse[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    $('#bankNameInput').focus();
                });

                window.resetBankForm = function() {
                    $('#bankEditId').val('');
                    $('#bankNameInput').val('');
                    $('#bankCodeInput').val('');
                    $('.bank-loc-check').prop('checked', false);
                    $('#bankLocationSection').hide();
                    $('#bankFormTitle').text('+ Add Bank');
                    $('#bankSubmitText').text('Add Bank');
                };

                // Toggle inline product stage config
                $(document).on('click', '.shf-toggle-product-locations', function() {
                    var $panel = $($(this).data('target'));
                    $panel.is(':visible') ? $panel.slideUp(200) : $panel.slideDown(200);
                });

                // Close product location panel
                $(document).on('click', '.shf-close-product-locs', function() {
                    $($(this).data('target')).slideUp(200);
                });

                $(document).on('click', '.shf-toggle-stages', function() {
                    var target = $(this).data('target');
                    var $panel = $(target);
                    if ($panel.is(':visible')) {
                        $panel.slideUp(200);
                    } else {
                        // Close any other open panels first
                        $('.shf-product-stages-panel:visible').slideUp(200);
                        $panel.slideDown(200);
                    }
                });

                // Edit branch — populate form
                $(document).on('click', '.shf-edit-branch', function() {
                    $('#branchEditId').val($(this).data('id'));
                    $('#branchNameInput').val($(this).data('name'));
                    $('#branchCodeInput').val($(this).data('code'));
                    $('#branchCityInput').val($(this).data('city'));
                    $('#branchPhoneInput').val($(this).data('phone'));
                    $('#branchManagerInput').val($(this).data('manager-id') || '');
                    $('#branchLocationInput').val($(this).data('location-id') || '');
                    $('#branchFormTitle').text('Edit Branch');
                    var $collapse = $('#branchFormCollapse');
                    if (!$collapse.hasClass('show')) {
                        new bootstrap.Collapse($collapse[0], {
                            toggle: true
                        });
                    }
                    $collapse[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    $('#branchNameInput').focus();
                });

                // Delete bank/branch
                $(document).on('click', '.shf-delete-item', function() {
                    var url = $(this).data('url');
                    Swal.fire({
                        title: 'Delete?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Delete'
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            $.ajax({
                                    url: url,
                                    method: 'DELETE',
                                    data: {
                                        _token: csrfToken
                                    }
                                })
                                .done(function() {
                                    location.reload();
                                })
                                .fail(function(xhr) {
                                    Swal.fire('Error', xhr.responseJSON?.error || 'Cannot delete',
                                        'error');
                                });
                        }
                    });
                });

                // --- Stage Master form validation ---
                $('#masterStagesForm').on('submit', function(e) {
                    var hasError = false;
                    var firstErrorEl = null;

                    // Clear previous
                    $('.shf-master-stage, .shf-master-substage').css({
                        'outline': '',
                        'background': ''
                    });
                    $('.shf-inline-error').remove();

                    function addInlineError($row, msg) {
                        hasError = true;
                        if (!firstErrorEl) firstErrorEl = $row;
                        $row.css({
                            'outline': '2px solid #dc3545',
                            'background': '#fff5f5'
                        });
                        $row.append(
                            '<div class="shf-inline-error text-danger mt-1" style="font-size:0.75rem;">* ' +
                            msg + '</div>');
                    }

                    // Validate enabled stages
                    $('.shf-master-stage').each(function() {
                        var $row = $(this);
                        var $toggle = $row.find('.shf-master-stage-toggle');
                        if (!$toggle.length || !$toggle.is(':checked')) return;
                        var $roleBoxes = $row.find('.shf-role-checkboxes');
                        if (!$roleBoxes.length) return;
                        if ($roleBoxes.find('input[type="checkbox"]:checked').length === 0) {
                            addInlineError($row, 'Select at least one role');
                        }
                    });

                    // Validate enabled sub-stages
                    $('.shf-master-substage').each(function() {
                        var $row = $(this);
                        var $toggle = $row.find('.shf-master-substage-toggle');
                        if (!$toggle.length || !$toggle.is(':checked')) return;
                        var $roleBoxes = $row.find('.shf-role-checkboxes');
                        if (!$roleBoxes.length) return;
                        if ($roleBoxes.find('input[type="checkbox"]:checked').length === 0) {
                            addInlineError($row, 'Select at least one role');
                        }
                    });

                    if (hasError) {
                        e.preventDefault();
                        if (firstErrorEl) {
                            $('html, body').animate({
                                scrollTop: $(firstErrorEl).offset().top - 100
                            }, 300);
                        }
                        return false;
                    }
                });
            });
        </script>
    @endpush
