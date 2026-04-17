@extends('layouts.app')
@section('title', 'New User — SHF')

@section('header')
    <h2 class="font-display fw-semibold text-white shf-page-title">
        <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        {{ $copyFrom ? 'Copy User' : 'Create User' }}
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="mx-auto px-3 px-sm-4 px-lg-5 shf-max-w-xl">
            @if($copyFrom)
                <div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2">
                    <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    Copying from <strong>{{ $copyFrom->name }}</strong> — change the name, email and password.
                </div>
            @endif
            <div class="shf-section">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">User Information</span>
                </div>
                <div class="shf-section-body">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Name</label>
                                <input type="text" name="name" class="shf-input" value="{{ old('name', $copyFrom?->name) }}" required autofocus>
                                @if ($errors->has('name'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('name') as $message) <li>{{ $message }}</li> @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Email</label>
                                <input type="email" name="email" class="shf-input" value="{{ old('email') }}" required>
                                @if ($errors->has('email'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('email') as $message) <li>{{ $message }}</li> @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Phone (optional)</label>
                                <input type="text" name="phone" class="shf-input" value="{{ old('phone', $copyFrom?->phone) }}">
                            </div>

                            <div class="col-md-6">
                                @php
                                    $allRolesCreate = \App\Models\Role::orderBy('id')->get();
                                    $copyRoleSlug = $copyFrom ? $copyFrom->roles->first()?->slug : 'loan_advisor';
                                    $selectedRole = old('roles.0', $copyRoleSlug);
                                @endphp
                                <label class="shf-form-label d-block mb-1">Role <span class="text-danger">*</span></label>
                                <select name="roles[]" id="createRoleSelect" class="shf-input">
                                    @foreach($allRolesCreate as $r)
                                        @if(!auth()->user()->isSuperAdmin() && $r->slug === 'super_admin') @continue @endif
                                        <option value="{{ $r->slug }}" {{ $selectedRole === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('roles'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('roles') as $message) <li>{{ $message }}</li> @endforeach
                                    </ul>
                                @endif
                            </div>

                            @php
                                $copyBankIds = $copyFrom ? $copyFrom->employerBanks->pluck('id')->toArray() : [];
                                $copyLocationIds = $copyFrom ? $copyFrom->locations->pluck('id')->toArray() : [];
                                $locStatesCreate = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get();
                                $allBanksCreate = \App\Models\Bank::active()->orderBy('name')->get();
                                $singleCityRoles = ['bank_employee', 'office_employee'];
                                $multiLocRoles = ['branch_manager', 'bdh', 'loan_advisor'];
                                $bankCitiesCreate = [];
                                foreach ($allBanksCreate as $b) {
                                    $bankCitiesCreate[$b->id] = $b->locations->where('type', 'city');
                                }
                            @endphp

                            {{-- City for bank_employee/office_employee --}}
                            <div class="col-md-6" id="createSingleCityField" style="{{ in_array($selectedRole, $singleCityRoles) ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">City <span class="text-danger">*</span></label>
                                <select name="assigned_locations[]" class="shf-input">
                                    <option value="">-- Select City --</option>
                                    @foreach($locStatesCreate as $locState)
                                        <optgroup label="{{ $locState->name }}">
                                            @foreach($locState->children->where('is_active', true) as $locCity)
                                                <option value="{{ $locCity->id }}" {{ in_array($locCity->id, old('assigned_locations', $copyLocationIds)) ? 'selected' : '' }}>{{ $locCity->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Locations for branch_manager/bdh/loan_advisor --}}
                            <div class="col-md-6" id="createMultiLocationField" style="{{ in_array($selectedRole, $multiLocRoles) ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Assigned Locations</label>
                                <div style="max-height:160px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
                                    @foreach($locStatesCreate as $locState)
                                        <div class="mb-2">
                                            <label class="d-inline-flex align-items-center gap-1 fw-semibold" style="font-size:0.85rem;cursor:pointer;">
                                                <input type="checkbox" name="assigned_locations[]" value="{{ $locState->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($locState->id, old('assigned_locations', $copyLocationIds)) ? 'checked' : '' }}>
                                                {{ $locState->name }}
                                            </label>
                                            @if($locState->children->where('is_active', true)->isNotEmpty())
                                                <div class="ps-4 d-flex flex-wrap gap-2 mt-1">
                                                    @foreach($locState->children->where('is_active', true) as $locCity)
                                                        <label class="d-inline-flex align-items-center gap-1" style="font-size:0.8rem;cursor:pointer;">
                                                            <input type="checkbox" name="assigned_locations[]" value="{{ $locCity->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($locCity->id, old('assigned_locations', $copyLocationIds)) ? 'checked' : '' }}>
                                                            {{ $locCity->name }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Bank for bank_employee --}}
                            <div class="col-md-6" id="createSingleBankField" style="{{ $selectedRole === 'bank_employee' ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Bank <span class="text-danger">*</span></label>
                                <select name="assigned_banks[]" id="createBankSelect" class="shf-input">
                                    <option value="">-- Select Bank --</option>
                                    @foreach($allBanksCreate as $bank)
                                        <option value="{{ $bank->id }}" {{ in_array($bank->id, old('assigned_banks', $copyBankIds)) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                {{-- Default for cities (same pattern as edit) --}}
                                @foreach($allBanksCreate as $bank)
                                    <div class="shf-bank-city-defaults mt-2" data-bank-id="{{ $bank->id }}" style="{{ in_array($bank->id, old('assigned_banks', $copyBankIds)) && $selectedRole === 'bank_employee' ? '' : 'display:none;' }}">
                                        <label class="shf-form-label d-block mb-1">Default for cities</label>
                                        @forelse($bankCitiesCreate[$bank->id] ?? [] as $city)
                                            @php
                                                $currentDefaultUser = \DB::table('bank_employees')
                                                    ->join('users', 'users.id', '=', 'bank_employees.user_id')
                                                    ->where('bank_employees.bank_id', $bank->id)
                                                    ->where('bank_employees.location_id', $city->id)
                                                    ->where('bank_employees.is_default', true)
                                                    ->value('users.name');
                                            @endphp
                                            <label class="d-inline-flex align-items-center gap-2 mb-1" style="cursor:pointer;font-size:0.85rem;">
                                                <input type="checkbox" name="default_bank_cities[]" value="{{ $city->id }}" class="shf-checkbox shf-icon-sm">
                                                {{ $city->name }}
                                                @if($currentDefaultUser)
                                                    <small class="text-muted">(Currently: {{ $currentDefaultUser }})</small>
                                                @else
                                                    <small class="text-muted">(No default)</small>
                                                @endif
                                            </label><br>
                                        @empty
                                            <small class="text-muted">No cities configured for this bank.</small>
                                        @endforelse
                                    </div>
                                @endforeach
                            </div>

                            {{-- Banks for office_employee --}}
                            <div class="col-md-6" id="createMultiBankField" style="{{ $selectedRole === 'office_employee' ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Assigned Banks</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($allBanksCreate as $bank)
                                        <label class="d-inline-flex align-items-center gap-1" style="font-size:0.85rem;cursor:pointer;">
                                            <input type="checkbox" name="assigned_banks[]" value="{{ $bank->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($bank->id, old('assigned_banks', $copyBankIds)) ? 'checked' : '' }}>
                                            {{ $bank->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Primary Branch --}}
                            @php $branchRequiredRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee']; @endphp
                            <div class="col-md-6" id="createPrimaryBranchField">
                                <label class="shf-form-label d-block mb-1">Primary Branch <span class="text-danger shf-branch-required-star" style="{{ in_array($selectedRole, $branchRequiredRoles) ? '' : 'display:none;' }}">*</span></label>
                                <select name="default_branch_id" id="createDefaultBranch" class="shf-input" {{ in_array($selectedRole, $branchRequiredRoles) ? 'required' : '' }}>
                                    <option value="">-- None --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('default_branch_id', $copyFrom?->default_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Assigned Branches (multi-checkbox) --}}
                            @php
                                $multiBranchRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'];
                                $branchDefaultOEInfo = [];
                                foreach ($branches as $br) {
                                    $currentOE = \DB::table('user_branches')
                                        ->join('users', 'users.id', '=', 'user_branches.user_id')
                                        ->where('user_branches.branch_id', $br->id)
                                        ->where('user_branches.is_default_office_employee', true)
                                        ->value('users.name');
                                    $branchDefaultOEInfo[$br->id] = $currentOE;
                                }
                            @endphp
                            <div class="col-md-6" id="createMultiBranchField" style="{{ in_array($selectedRole, $multiBranchRoles) ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Assigned Branches</label>
                                <small class="text-muted d-block mb-1 shf-oe-default-hint {{ $selectedRole !== 'office_employee' ? 'd-none' : '' }}">Default OE is a fallback — bank-specific assignment is in Settings → Workflow → Product Stages</small>
                                <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
                                    @foreach($branches as $branch)
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="d-inline-flex align-items-center gap-1 flex-grow-1" style="font-size:0.85rem;cursor:pointer;">
                                                <input type="checkbox" name="assigned_branches[]" value="{{ $branch->id }}" class="shf-checkbox shf-icon-sm shf-assigned-branch-cb" data-branch-id="{{ $branch->id }}" {{ in_array($branch->id, old('assigned_branches', [])) ? 'checked' : '' }}>
                                                {{ $branch->name }}
                                            </label>
                                            {{-- Default OE toggle (office_employee only) --}}
                                            <label class="d-inline-flex align-items-center gap-1 shf-oe-default-toggle {{ $selectedRole !== 'office_employee' ? 'd-none' : '' }}" data-branch-id="{{ $branch->id }}" style="font-size:0.8rem;cursor:pointer;">
                                                <input type="checkbox" name="default_oe_branches[]" value="{{ $branch->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($branch->id, old('default_oe_branches', [])) ? 'checked' : '' }}>
                                                <span class="text-muted">Default OE</span>
                                                @if($branchDefaultOEInfo[$branch->id])
                                                    <small class="text-muted">({{ $branchDefaultOEInfo[$branch->id] }})</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Replace in Product Stages (full width) --}}
                            <div class="col-12" id="createReplacePSU" style="display:none;">
                                <label class="shf-form-label d-block mb-1">Replace in Product Stages</label>
                                <div id="createPSUHolders" style="max-height:250px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:12px;">
                                    <small class="text-muted">Select bank and city to load current holders.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password" name="password" class="shf-input" style="padding-right:2.5rem" required>
                                    <button type="button" class="shf-password-toggle" data-target="password" tabindex="-1">
                                        <svg class="shf-eye-open shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed shf-eye-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @if ($errors->has('password'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('password') as $message) <li>{{ $message }}</li> @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Confirm Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="shf-input" style="padding-right:2.5rem" required>
                                    <button type="button" class="shf-password-toggle" data-target="password_confirmation" tabindex="-1">
                                        <svg class="shf-eye-open shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed shf-eye-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Status</label>
                                <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" checked class="shf-toggle">
                                    <span class="small shf-text-gray">Active</span>
                                </label>
                            </div>
                        </div>

                        {{-- Permission Overrides (same as edit) --}}
                        @if(auth()->user()->hasPermission('manage_permissions'))
                            <div class="mt-4 pt-4" style="border-top: 1px solid var(--border);">
                                <h3 class="font-display fw-semibold" style="font-size: 1.125rem; color: #111827; margin-bottom: 0.5rem;">Permission Overrides</h3>
                                <p class="small mb-3 shf-text-gray">Override role defaults for this user. "Default" uses the role's setting.</p>

                                @php $allPermsGrouped = \App\Models\Permission::all()->groupBy('group'); @endphp
                                @foreach($allPermsGrouped as $group => $perms)
                                    <div class="mb-4">
                                        <h4 class="font-display fw-semibold small mb-2" style="color: #f15a29; text-transform: uppercase; letter-spacing: 0.05em;">{{ $group }}</h4>
                                        <div class="ms-3">
                                            @foreach($perms as $perm)
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <span class="small" style="color: #6b7280; width: 12rem;">{{ $perm->name }}</span>
                                                    <select name="permissions[{{ $perm->id }}]" class="shf-input small" style="width: auto; padding: 6px 12px;">
                                                        <option value="default" selected>Default (role)</option>
                                                        <option value="grant">Grant</option>
                                                        <option value="deny">Deny</option>
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="shf-form-actions d-flex align-items-center justify-content-end gap-3 mt-4 pt-4" style="border-top: 1px solid var(--border);">
                            <a href="{{ route('users.index') }}" class="btn-accent-outline"><svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                            <button type="submit" class="btn-accent">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $copyFrom ? 'Create Copy' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    var multiBranchRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'];
    var psuRoles = ['bank_employee', 'office_employee'];
    var singleCityRoles = ['bank_employee', 'office_employee'];
    var branchRequiredRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'];

    // ── Form validation ──
    $('form').on('submit', function(e) {
        var $form = $(this);
        var role = $('#createRoleSelect').val();

        var rules = {
            name: { required: true, maxlength: 255 },
            email: { required: true, email: true, maxlength: 255 },
            password: { required: true, minlength: 8 },
            password_confirmation: {
                required: true,
                custom: function() {
                    var pw = $('#password').val();
                    var confirm = $('#password_confirmation').val();
                    if (confirm && pw !== confirm) return 'Passwords do not match.';
                    return null;
                }
            }
        };

        // Conditional: city required for bank/office employee
        if (singleCityRoles.indexOf(role) !== -1) {
            rules['assigned_locations[]'] = { required: true, label: 'City' };
        }
        // Conditional: bank required for bank_employee
        if (role === 'bank_employee') {
            rules['assigned_banks[]'] = { required: true, label: 'Bank' };
        }
        // Conditional: branch required for BM/BDH/LA/OE
        if (branchRequiredRoles.indexOf(role) !== -1) {
            rules['default_branch_id'] = { required: true, label: 'Primary Branch' };
        }

        if (!SHF.validateForm($form, rules)) {
            e.preventDefault();
            return false;
        }

        // Async email uniqueness check
        e.preventDefault();
        var email = $form.find('[name="email"]').val();
        $.get(@json(route('users.check-email')), { email: email }, function(data) {
            if (!data.available) {
                var $emailField = $form.find('[name="email"]');
                $emailField.addClass('is-invalid');
                $emailField.closest('.col-md-6').append('<div class="text-danger small mt-1 shf-client-error">This email is already taken.</div>');
                $emailField.focus();
            } else {
                $form.off('submit').submit();
            }
        }).fail(function() {
            $form.off('submit').submit();
        });
    });

    function showCreateBankCityDefaults() {
        var bankId = $('#createBankSelect').val();
        $('.shf-bank-city-defaults').hide();
        if (bankId && $('#createRoleSelect').val() === 'bank_employee') {
            $('.shf-bank-city-defaults[data-bank-id="' + bankId + '"]').show();
        }
    }

    function syncPrimaryBranch() {
        var primaryId = $('#createDefaultBranch').val();
        $('.shf-assigned-branch-cb').each(function() {
            var branchId = $(this).data('branch-id').toString();
            if (branchId === primaryId) {
                $(this).prop('checked', true).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }

    function toggleOEDefaults() {
        var role = $('#createRoleSelect').val();
        if (role === 'office_employee') {
            $('.shf-oe-default-toggle, .shf-oe-default-hint').removeClass('d-none');
        } else {
            $('.shf-oe-default-toggle, .shf-oe-default-hint').addClass('d-none');
        }
    }

    function toggleMultiBranch() {
        var role = $('#createRoleSelect').val();
        $('#createMultiBranchField').toggle(multiBranchRoles.indexOf(role) !== -1);
    }

    function toggleReplacePSU() {
        var role = $('#createRoleSelect').val();
        $('#createReplacePSU').toggle(psuRoles.indexOf(role) !== -1);
    }

    function loadPSUHolders() {
        var role = $('#createRoleSelect').val();
        if (psuRoles.indexOf(role) === -1) return;

        var cityId = $('#createSingleCityField select').val();
        var bankIds = [];

        if (role === 'bank_employee') {
            var bankId = $('#createBankSelect').val();
            if (!bankId || !cityId) {
                var msg = !bankId && !cityId ? 'Select bank and city first' : !bankId ? 'Select bank first' : 'Select city first';
                $('#createPSUHolders').html('<small class="text-muted">' + msg + '</small>');
                return;
            }
            bankIds = [bankId];
        } else {
            // Office employee: get checked banks
            $('#createMultiBankField input[name="assigned_banks[]"]:checked').each(function() {
                bankIds.push($(this).val());
            });
            if (!cityId) {
                $('#createPSUHolders').html('<small class="text-muted">Select city to load current holders.</small>');
                return;
            }
            if (!bankIds.length) {
                $('#createPSUHolders').html('<small class="text-muted">Select assigned banks to load current holders.</small>');
                return;
            }
        }

        $('#createPSUHolders').html('<small class="text-muted">Loading...</small>');
        $.get(@json(route('users.product-stage-holders')), {
            'bank_ids[]': bankIds,
            location_id: cityId,
            role: role
        }, function(data) {
            if (!data.length) {
                $('#createPSUHolders').html('<small class="text-muted">No current holders found for this role.</small>');
                return;
            }
            // Group by product
            var byProduct = {};
            $.each(data, function(i, h) {
                var key = h.bank_name + ' — ' + h.product_name;
                if (!byProduct[key]) byProduct[key] = [];
                byProduct[key].push(h);
            });
            var html = '';
            $.each(byProduct, function(productLabel, holders) {
                html += '<div class="mb-2"><strong class="shf-text-xs" style="color:var(--accent);">' + productLabel + '</strong>';
                $.each(holders, function(i, h) {
                    html += '<label class="d-flex align-items-center gap-1 ms-2 mb-1" style="font-size:0.85rem;cursor:pointer;">';
                    html += '<input type="checkbox" name="replace_psu[]" value="' + h.user_id + '_' + h.product_id + '" class="shf-checkbox shf-icon-sm">';
                    html += '<span>' + h.user_name + ' (' + h.stage_list + ')</span>';
                    html += '</label>';
                });
                html += '</div>';
            });
            $('#createPSUHolders').html(html);
        }).fail(function() {
            $('#createPSUHolders').html('<small class="text-muted text-danger">Failed to load holders.</small>');
        });
    }

    $('#createRoleSelect').on('change', function() {
        var val = $(this).val();
        var singleCityRoles = ['bank_employee', 'office_employee'];
        var multiLocRoles = ['branch_manager', 'bdh', 'loan_advisor'];

        $('#createSingleCityField').toggle(singleCityRoles.indexOf(val) !== -1);
        $('#createMultiLocationField').toggle(multiLocRoles.indexOf(val) !== -1);
        $('#createSingleBankField').toggle(val === 'bank_employee');
        $('#createMultiBankField').toggle(val === 'office_employee');
        showCreateBankCityDefaults();
        toggleMultiBranch();
        toggleOEDefaults();
        toggleReplacePSU();
        toggleBranchRequired();
        loadPSUHolders();
    });

    function toggleBranchRequired() {
        var role = $('#createRoleSelect').val();
        var required = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'].indexOf(role) !== -1;
        $('#createDefaultBranch').prop('required', required);
        $('.shf-branch-required-star').toggle(required);
    }

    $('#createBankSelect').on('change', function() {
        showCreateBankCityDefaults();
        loadPSUHolders();
    });

    $('#createSingleCityField select').on('change', function() {
        loadPSUHolders();
    });

    // Office employee bank checkboxes trigger PSU reload
    $(document).on('change', '#createMultiBankField input[name="assigned_banks[]"]', function() {
        loadPSUHolders();
    });

    $('#createDefaultBranch').on('change', syncPrimaryBranch);

    // Init (order matters: branches first, then OE toggles after)
    syncPrimaryBranch();
    toggleMultiBranch();
    toggleReplacePSU();
    toggleOEDefaults();
    loadPSUHolders();
});
</script>
@endpush
