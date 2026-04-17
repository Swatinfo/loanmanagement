@extends('layouts.app')
@section('title', 'Edit User — SHF')

@section('header')
    <h2 class="font-display fw-semibold text-white shf-page-title">
        <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit User: {{ $user->name }}
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="mx-auto px-3 px-sm-4 px-lg-5 shf-max-w-xl">
            <!-- Basic Info -->
            <div class="shf-section">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">User Information</span>
                </div>
                <div class="shf-section-body">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Name</label>
                                <input type="text" id="name" name="name" class="shf-input" value="{{ old('name', $user->name) }}" required>
                                @if ($errors->has('name'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('name') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Email</label>
                                <input type="email" id="email" name="email" class="shf-input" value="{{ old('email', $user->email) }}" required>
                                @if ($errors->has('email'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('email') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Phone (optional)</label>
                                <input type="text" id="phone" name="phone" class="shf-input" value="{{ old('phone', $user->phone) }}">
                                @if ($errors->has('phone'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('phone') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                @php
                                    $allRoles = \App\Models\Role::orderBy('id')->get();
                                    $userRoleSlug = old('roles.0', $user->roles->first()?->slug);
                                    $userBankIds = $user->employerBanks->pluck('id')->toArray();
                                    $userLocationIds = $user->locations->pluck('id')->toArray();
                                    $locStates = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get();
                                    $allBanks = \App\Models\Bank::active()->orderBy('name')->get();
                                    $singleCityRoles = ['bank_employee', 'office_employee'];
                                    $multiLocationRoles = ['branch_manager', 'bdh', 'loan_advisor'];
                                    $bankRoles = ['bank_employee', 'office_employee'];
                                    $taskRole = $userRoleSlug;
                                @endphp
                                <label class="shf-form-label d-block mb-1">Role <span class="text-danger">*</span></label>
                                <select name="roles[]" id="editRoleSelect" class="shf-input">
                                    @foreach($allRoles as $r)
                                        @if(!auth()->user()->isSuperAdmin() && $r->slug === 'super_admin') @continue @endif
                                        <option value="{{ $r->slug }}" {{ $userRoleSlug === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('roles'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('roles') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">

                                {{-- Location: Single city for bank_employee/office_employee --}}
                                <div id="editSingleCityField" style="{{ in_array($taskRole, $singleCityRoles) ? '' : 'display:none;' }}">
                                    <label class="shf-form-label d-block mb-1">City <span class="text-danger">*</span></label>
                                    <select name="assigned_locations[]" id="editSingleCitySelect" class="shf-input mb-3">
                                        <option value="">— Select City —</option>
                                        @foreach($locStates as $locState)
                                            <optgroup label="{{ $locState->name }}">
                                                @foreach($locState->children->where('is_active', true) as $locCity)
                                                    <option value="{{ $locCity->id }}" {{ in_array($locCity->id, old('assigned_locations', $userLocationIds)) ? 'selected' : '' }}>{{ $locCity->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Location: Multiple for branch_manager/loan_advisor --}}
                                <div id="editMultiLocationField" style="{{ in_array($taskRole, $multiLocationRoles) ? '' : 'display:none;' }}">
                                    <label class="shf-form-label d-block mb-1">Assigned Locations</label>
                                    <div class="mb-3" style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
                                        @foreach($locStates as $locState)
                                            <div class="mb-2">
                                                <label class="d-inline-flex align-items-center gap-1 fw-semibold" style="font-size:0.85rem;cursor:pointer;">
                                                    <input type="checkbox" name="assigned_locations[]" value="{{ $locState->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($locState->id, old('assigned_locations', $userLocationIds)) ? 'checked' : '' }}>
                                                    {{ $locState->name }}
                                                </label>
                                                @if($locState->children->where('is_active', true)->isNotEmpty())
                                                    <div class="ps-4 d-flex flex-wrap gap-2 mt-1">
                                                        @foreach($locState->children->where('is_active', true) as $locCity)
                                                            <label class="d-inline-flex align-items-center gap-1" style="font-size:0.8rem;cursor:pointer;">
                                                                <input type="checkbox" name="assigned_locations[]" value="{{ $locCity->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($locCity->id, old('assigned_locations', $userLocationIds)) ? 'checked' : '' }}>
                                                                {{ $locCity->name }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Bank: Single for bank_employee, Multiple for office_employee --}}
                                <div id="editBankField" style="{{ in_array($taskRole, $bankRoles) ? '' : 'display:none;' }}">
                                    {{-- Single bank (bank_employee) --}}
                                    <div id="editSingleBankField" style="{{ $taskRole === 'bank_employee' ? '' : 'display:none;' }}">
                                        <label class="shf-form-label d-block mb-1">Bank <span class="text-danger">*</span></label>
                                        <select name="assigned_banks[]" id="editBankSelect" class="shf-input mb-3">
                                            <option value="">— Select Bank —</option>
                                            @foreach($allBanks as $bank)
                                                <option value="{{ $bank->id }}" {{ in_array($bank->id, old('assigned_banks', $userBankIds)) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Default for cities --}}
                                        @php
                                            $userDefaultCityIds = \DB::table('bank_employees')
                                                ->where('user_id', $user->id)
                                                ->where('is_default', true)
                                                ->whereNotNull('location_id')
                                                ->pluck('location_id')
                                                ->toArray();
                                            $bankCities = [];
                                            foreach ($allBanks as $b) {
                                                $bankCities[$b->id] = $b->locations->where('type', 'city');
                                            }
                                        @endphp
                                        @foreach($allBanks as $bank)
                                            <div class="shf-bank-city-defaults mb-3" data-bank-id="{{ $bank->id }}" style="{{ in_array($bank->id, $userBankIds) && $taskRole === 'bank_employee' ? '' : 'display:none;' }}">
                                                <label class="shf-form-label d-block mb-1">Default for cities</label>
                                                @forelse($bankCities[$bank->id] ?? [] as $city)
                                                    @php
                                                        $isDefault = in_array($city->id, $userDefaultCityIds);
                                                        $currentDefaultUser = \DB::table('bank_employees')
                                                            ->join('users', 'users.id', '=', 'bank_employees.user_id')
                                                            ->where('bank_employees.bank_id', $bank->id)
                                                            ->where('bank_employees.location_id', $city->id)
                                                            ->where('bank_employees.is_default', true)
                                                            ->where('bank_employees.user_id', '!=', $user->id)
                                                            ->value('users.name');
                                                    @endphp
                                                    <label class="d-inline-flex align-items-center gap-2 mb-1" style="cursor:pointer;font-size:0.85rem;">
                                                        <input type="checkbox" name="default_bank_cities[]" value="{{ $city->id }}" class="shf-checkbox shf-icon-sm" {{ $isDefault ? 'checked' : '' }}>
                                                        {{ $city->name }}
                                                        @if($currentDefaultUser)
                                                            <small class="text-muted">(Currently: {{ $currentDefaultUser }})</small>
                                                        @elseif(!$isDefault)
                                                            <small class="text-muted">(No default)</small>
                                                        @endif
                                                    </label><br>
                                                @empty
                                                    <small class="text-muted">No cities configured for this bank.</small>
                                                @endforelse
                                            </div>
                                        @endforeach
                                    </div>
                                    {{-- Multiple banks (office_employee) --}}
                                    <div id="editMultiBankField" style="{{ $taskRole === 'office_employee' ? '' : 'display:none;' }}">
                                        <label class="shf-form-label d-block mb-1">Assigned Banks</label>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach($allBanks as $bank)
                                                <label class="d-inline-flex align-items-center gap-1" style="font-size:0.85rem;cursor:pointer;">
                                                    <input type="checkbox" name="assigned_banks[]" value="{{ $bank->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($bank->id, old('assigned_banks', $userBankIds)) ? 'checked' : '' }}>
                                                    {{ $bank->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Primary Branch --}}
                            @php $editBranchRequiredRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee']; @endphp
                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Primary Branch <span class="text-danger shf-branch-required-star" style="{{ in_array($taskRole, $editBranchRequiredRoles) ? '' : 'display:none;' }}">*</span></label>
                                <select name="default_branch_id" id="editDefaultBranch" class="shf-input" {{ in_array($taskRole, $editBranchRequiredRoles) ? 'required' : '' }}>
                                    <option value="">— None —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('default_branch_id', $user->default_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Assigned Branches (multi-checkbox) --}}
                            @php
                                $multiBranchRoles = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'];
                                $userBranchIds = $user->branches->pluck('id')->toArray();
                                $userOEBranchIds = \DB::table('user_branches')
                                    ->where('user_id', $user->id)
                                    ->where('is_default_office_employee', true)
                                    ->pluck('branch_id')
                                    ->toArray();
                                $editBranchDefaultOEInfo = [];
                                foreach ($branches as $br) {
                                    $currentOE = \DB::table('user_branches')
                                        ->join('users', 'users.id', '=', 'user_branches.user_id')
                                        ->where('user_branches.branch_id', $br->id)
                                        ->where('user_branches.is_default_office_employee', true)
                                        ->where('user_branches.user_id', '!=', $user->id)
                                        ->value('users.name');
                                    $editBranchDefaultOEInfo[$br->id] = $currentOE;
                                }
                            @endphp
                            <div class="col-md-6" id="editMultiBranchField" style="{{ in_array($taskRole, $multiBranchRoles) ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Assigned Branches</label>
                                <small class="text-muted d-block mb-1 shf-oe-default-hint {{ $taskRole !== 'office_employee' ? 'd-none' : '' }}">Default OE is a fallback — bank-specific assignment is in Settings → Workflow → Product Stages</small>
                                <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:8px;">
                                    @foreach($branches as $branch)
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="d-inline-flex align-items-center gap-1 flex-grow-1" style="font-size:0.85rem;cursor:pointer;">
                                                <input type="checkbox" name="assigned_branches[]" value="{{ $branch->id }}" class="shf-checkbox shf-icon-sm shf-assigned-branch-cb" data-branch-id="{{ $branch->id }}" {{ in_array($branch->id, old('assigned_branches', $userBranchIds)) ? 'checked' : '' }}>
                                                {{ $branch->name }}
                                            </label>
                                            {{-- Default OE toggle (office_employee only) --}}
                                            <label class="d-inline-flex align-items-center gap-1 shf-oe-default-toggle {{ $taskRole !== 'office_employee' ? 'd-none' : '' }}" data-branch-id="{{ $branch->id }}" style="font-size:0.8rem;cursor:pointer;">
                                                <input type="checkbox" name="default_oe_branches[]" value="{{ $branch->id }}" class="shf-checkbox shf-icon-sm" {{ in_array($branch->id, old('default_oe_branches', $userOEBranchIds)) ? 'checked' : '' }}>
                                                <span class="text-muted">Default OE</span>
                                                @if($editBranchDefaultOEInfo[$branch->id])
                                                    <small class="text-muted">({{ $editBranchDefaultOEInfo[$branch->id] }})</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Replace in Product Stages --}}
                            <div class="col-12" id="editReplacePSU" style="{{ in_array($taskRole, ['bank_employee', 'office_employee']) ? '' : 'display:none;' }}">
                                <label class="shf-form-label d-block mb-1">Replace in Product Stages</label>
                                <div id="editPSUHolders" style="max-height:250px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:12px;">
                                    <small class="text-muted">Select bank and city to load current holders.</small>
                                </div>
                            </div>

                            <div class="col-md-6">

                                <label class="shf-form-label d-block mb-1">New Password (leave blank to keep current)</label>
                                <div class="position-relative">
                                    <input type="password" id="password" name="password" class="shf-input" style="padding-right:2.5rem">
                                    <button type="button" class="shf-password-toggle shf-password-toggle" data-target="password" tabindex="-1">
                                        <svg class="shf-eye-open shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed shf-eye-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @if ($errors->has('password'))
                                    <ul class="list-unstyled mt-1 mb-0 small shf-text-error">
                                        @foreach ($errors->get('password') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Confirm New Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="shf-input" style="padding-right:2.5rem">
                                    <button type="button" class="shf-password-toggle shf-password-toggle" data-target="password_confirmation" tabindex="-1">
                                        <svg class="shf-eye-open shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed shf-eye-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="shf-form-label d-block mb-1">Status</label>
                            <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer;">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       class="shf-toggle">
                                <span class="small shf-text-gray">Active</span>
                            </label>
                        </div>

                        <!-- User-Specific Permission Overrides -->
                        @if(auth()->user()->hasPermission('manage_permissions') && !$user->isSuperAdmin())
                            <div class="mt-4 pt-4" style="border-top: 1px solid var(--border);">
                                <h3 class="font-display fw-semibold" style="font-size: 1.125rem; color: #111827; margin-bottom: 0.5rem;">Permission Overrides</h3>
                                <p class="small mb-3 shf-text-gray">Override role defaults for this specific user. "Default" uses the role's setting.</p>

                                @foreach($permissions as $group => $perms)
                                    <div class="mb-4">
                                        <h4 class="font-display fw-semibold small mb-2" style="color: #f15a29; text-transform: uppercase; letter-spacing: 0.05em;">{{ $group }}</h4>
                                        <div class="ms-3">
                                            @foreach($perms as $perm)
                                                @php
                                                    $override = $userOverrides[$perm->id] ?? null;
                                                @endphp
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <span class="small" style="color: #6b7280; width: 12rem;">{{ $perm->name }}</span>
                                                    <select name="permissions[{{ $perm->id }}]" class="shf-input small" style="width: auto; padding: 6px 12px;">
                                                        <option value="default" {{ !$override ? 'selected' : '' }}>Default (role)</option>
                                                        <option value="grant" {{ $override === 'grant' ? 'selected' : '' }}>Grant</option>
                                                        <option value="deny" {{ $override === 'deny' ? 'selected' : '' }}>Deny</option>
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
                                Update User
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
    var editUserId = {{ $user->id }};

    // ── Form validation ──
    $('form').on('submit', function(e) {
        var $form = $(this);
        var role = $('#editRoleSelect').val();

        var rules = {
            name: { required: true, maxlength: 255 },
            email: { required: true, email: true, maxlength: 255 }
        };

        // Password: only validate if filled
        var pw = $('#password').val();
        if (pw) {
            rules['password'] = { minlength: 8 };
            rules['password_confirmation'] = {
                custom: function() {
                    var confirm = $('#password_confirmation').val();
                    if (pw !== confirm) return 'Passwords do not match.';
                    return null;
                }
            };
        }

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
        $.get(@json(route('users.check-email')), { email: email, exclude_id: editUserId }, function(data) {
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

    function showBankCityDefaults() {
        var bankId = $('#editBankSelect').val();
        $('.shf-bank-city-defaults').hide();
        if (bankId && $('#editRoleSelect').val() === 'bank_employee') {
            $('.shf-bank-city-defaults[data-bank-id="' + bankId + '"]').show();
        }
    }

    function syncEditPrimaryBranch() {
        var primaryId = $('#editDefaultBranch').val();
        $('.shf-assigned-branch-cb').each(function() {
            var branchId = $(this).data('branch-id').toString();
            if (branchId === primaryId) {
                $(this).prop('checked', true).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }

    function toggleEditOEDefaults() {
        var role = $('#editRoleSelect').val();
        if (role === 'office_employee') {
            $('.shf-oe-default-toggle, .shf-oe-default-hint').removeClass('d-none');
        } else {
            $('.shf-oe-default-toggle, .shf-oe-default-hint').addClass('d-none');
        }
    }

    function toggleEditMultiBranch() {
        var role = $('#editRoleSelect').val();
        $('#editMultiBranchField').toggle(multiBranchRoles.indexOf(role) !== -1);
    }

    function toggleEditReplacePSU() {
        var role = $('#editRoleSelect').val();
        $('#editReplacePSU').toggle(psuRoles.indexOf(role) !== -1);
    }

    function loadEditPSUHolders() {
        var role = $('#editRoleSelect').val();
        if (psuRoles.indexOf(role) === -1) return;

        var cityId = $('#editSingleCitySelect').val();
        var bankIds = [];

        if (role === 'bank_employee') {
            var bankId = $('#editBankSelect').val();
            if (!bankId || !cityId) {
                var msg = !bankId && !cityId ? 'Select bank and city first' : !bankId ? 'Select bank first' : 'Select city first';
                $('#editPSUHolders').html('<small class="text-muted">' + msg + '</small>');
                return;
            }
            bankIds = [bankId];
        } else {
            $('#editMultiBankField input[name="assigned_banks[]"]:checked').each(function() {
                bankIds.push($(this).val());
            });
            if (!cityId) {
                $('#editPSUHolders').html('<small class="text-muted">Select city to load current holders.</small>');
                return;
            }
            if (!bankIds.length) {
                $('#editPSUHolders').html('<small class="text-muted">Select assigned banks to load current holders.</small>');
                return;
            }
        }

        $('#editPSUHolders').html('<small class="text-muted">Loading...</small>');
        $.get(@json(route('users.product-stage-holders')), {
            'bank_ids[]': bankIds,
            location_id: cityId,
            role: role
        }, function(data) {
            if (!data.length) {
                $('#editPSUHolders').html('<small class="text-muted">No current holders found for this role.</small>');
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
                    if (h.user_id === editUserId) {
                        html += '<div class="d-flex align-items-center gap-1 ms-2 mb-1" style="font-size:0.85rem;">';
                        html += '<span class="text-muted"><em>You</em> (' + h.stage_list + ')</span>';
                        html += '</div>';
                    } else {
                        html += '<label class="d-flex align-items-center gap-1 ms-2 mb-1" style="font-size:0.85rem;cursor:pointer;">';
                        html += '<input type="checkbox" name="replace_psu[]" value="' + h.user_id + '_' + h.product_id + '" class="shf-checkbox shf-icon-sm">';
                        html += '<span>' + h.user_name + ' (' + h.stage_list + ')</span>';
                        html += '</label>';
                    }
                });
                html += '</div>';
            });
            $('#editPSUHolders').html(html);
        }).fail(function() {
            $('#editPSUHolders').html('<small class="text-muted text-danger">Failed to load holders.</small>');
        });
    }

    $('#editRoleSelect').on('change', function() {
        var val = $(this).val();
        var singleCityRoles = ['bank_employee', 'office_employee'];
        var multiLocationRoles = ['branch_manager', 'bdh', 'loan_advisor'];
        var bankRoles = ['bank_employee', 'office_employee'];

        $('#editSingleCityField').toggle(singleCityRoles.indexOf(val) !== -1);
        $('#editMultiLocationField').toggle(multiLocationRoles.indexOf(val) !== -1);
        $('#editBankField').toggle(bankRoles.indexOf(val) !== -1);
        $('#editSingleBankField').toggle(val === 'bank_employee');
        $('#editMultiBankField').toggle(val === 'office_employee');
        showBankCityDefaults();
        toggleEditMultiBranch();
        toggleEditOEDefaults();
        toggleEditReplacePSU();
        toggleEditBranchRequired();
        loadEditPSUHolders();
    });

    function toggleEditBranchRequired() {
        var role = $('#editRoleSelect').val();
        var required = ['branch_manager', 'bdh', 'loan_advisor', 'office_employee'].indexOf(role) !== -1;
        $('#editDefaultBranch').prop('required', required);
        $('.shf-branch-required-star').toggle(required);
    }

    $('#editBankSelect').on('change', function() {
        showBankCityDefaults();
        loadEditPSUHolders();
    });

    $('#editSingleCitySelect').on('change', function() {
        loadEditPSUHolders();
    });

    // Office employee bank checkboxes trigger PSU reload
    $(document).on('change', '#editMultiBankField input[name="assigned_banks[]"]', function() {
        loadEditPSUHolders();
    });

    $('#editDefaultBranch').on('change', syncEditPrimaryBranch);

    // Init (order matters: branches first, then OE toggles after)
    syncEditPrimaryBranch();
    toggleEditMultiBranch();
    toggleEditReplacePSU();
    toggleEditOEDefaults();
    loadEditPSUHolders();
});
</script>
@endpush
