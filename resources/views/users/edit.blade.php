@extends('layouts.app')

@section('header')
    <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.75rem; margin: 0;">
        <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit User: {{ $user->name }}
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="mx-auto px-3 px-sm-4 px-lg-5" style="max-width: 56rem;">
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
                                    <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
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
                                    <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
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
                                    <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                                        @foreach ($errors->get('phone') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Role</label>
                                <select id="role" name="role" class="shf-input">
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('role'))
                                    <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                                        @foreach ($errors->get('role') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Task Role (Loan Workflow)</label>
                                <select name="task_role" id="editTaskRoleSelect" class="shf-input mb-3">
                                    <option value="">— None (quotation only) —</option>
                                    @foreach(\App\Models\User::TASK_ROLE_LABELS as $value => $label)
                                        <option value="{{ $value }}" {{ old('task_role', $user->task_role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>

                                @php
                                    $taskRole = old('task_role', $user->task_role);
                                    $userBankIds = $user->employerBanks->pluck('id')->toArray();
                                    $userLocationIds = $user->locations->pluck('id')->toArray();
                                    $locStates = \App\Models\Location::with('children')->states()->active()->orderBy('name')->get();
                                    $allBanks = \App\Models\Bank::active()->orderBy('name')->get();
                                    // Roles that need location
                                    $singleCityRoles = ['bank_employee', 'office_employee'];
                                    $multiLocationRoles = ['branch_manager', 'loan_advisor'];
                                    $bankRoles = ['bank_employee', 'office_employee'];
                                @endphp

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
                                                    <input type="checkbox" name="assigned_locations[]" value="{{ $locState->id }}" class="shf-checkbox" style="width:14px;height:14px;" {{ in_array($locState->id, old('assigned_locations', $userLocationIds)) ? 'checked' : '' }}>
                                                    {{ $locState->name }}
                                                </label>
                                                @if($locState->children->where('is_active', true)->isNotEmpty())
                                                    <div class="ps-4 d-flex flex-wrap gap-2 mt-1">
                                                        @foreach($locState->children->where('is_active', true) as $locCity)
                                                            <label class="d-inline-flex align-items-center gap-1" style="font-size:0.8rem;cursor:pointer;">
                                                                <input type="checkbox" name="assigned_locations[]" value="{{ $locCity->id }}" class="shf-checkbox" style="width:13px;height:13px;" {{ in_array($locCity->id, old('assigned_locations', $userLocationIds)) ? 'checked' : '' }}>
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
                                        <select name="assigned_banks[]" class="shf-input mb-3">
                                            <option value="">— Select Bank —</option>
                                            @foreach($allBanks as $bank)
                                                <option value="{{ $bank->id }}" {{ in_array($bank->id, old('assigned_banks', $userBankIds)) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Multiple banks (office_employee) --}}
                                    <div id="editMultiBankField" style="{{ $taskRole === 'office_employee' ? '' : 'display:none;' }}">
                                        <label class="shf-form-label d-block mb-1">Assigned Banks</label>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach($allBanks as $bank)
                                                <label class="d-inline-flex align-items-center gap-1" style="font-size:0.85rem;cursor:pointer;">
                                                    <input type="checkbox" name="assigned_banks[]" value="{{ $bank->id }}" class="shf-checkbox" style="width:14px;height:14px;" {{ in_array($bank->id, old('assigned_banks', $userBankIds)) ? 'checked' : '' }}>
                                                    {{ $bank->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <label class="shf-form-label d-block mb-1">Default Branch</label>
                                <select name="default_branch_id" class="shf-input mb-3">
                                    <option value="">— None —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('default_branch_id', $user->default_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>

                                <label class="shf-form-label d-block mb-1">New Password (leave blank to keep current)</label>
                                <div class="position-relative">
                                    <input type="password" id="password" name="password" class="shf-input" style="padding-right: 2.5rem;">
                                    <button type="button" class="shf-password-toggle" data-target="password" tabindex="-1" style="position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:12px;background:none;border:none;color:#9ca3af;cursor:pointer;">
                                        <svg class="shf-eye-open" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed" style="width:20px;height:20px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @if ($errors->has('password'))
                                    <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                                        @foreach ($errors->get('password') as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="shf-form-label d-block mb-1">Confirm New Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="shf-input" style="padding-right: 2.5rem;">
                                    <button type="button" class="shf-password-toggle" data-target="password_confirmation" tabindex="-1" style="position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:12px;background:none;border:none;color:#9ca3af;cursor:pointer;">
                                        <svg class="shf-eye-open" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="shf-eye-closed" style="width:20px;height:20px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       class="shf-checkbox" style="width:16px;height:16px;">
                                <span class="ms-2 small" style="color: #6b7280;">Active</span>
                            </label>
                        </div>

                        <!-- User-Specific Permission Overrides -->
                        @if(auth()->user()->hasPermission('manage_permissions') && !$user->isSuperAdmin())
                            <div class="mt-4 pt-4" style="border-top: 1px solid var(--border);">
                                <h3 class="font-display fw-semibold" style="font-size: 1.125rem; color: #111827; margin-bottom: 0.5rem;">Permission Overrides</h3>
                                <p class="small mb-3" style="color: #6b7280;">Override role defaults for this specific user. "Default" uses the role's setting.</p>

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

                        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4" style="border-top: 1px solid var(--border);">
                            <a href="{{ route('users.index') }}" class="btn-accent-outline"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Cancel</a>
                            <button type="submit" class="btn-accent">
                                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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
    $('#editTaskRoleSelect').on('change', function() {
        var val = $(this).val();
        var singleCityRoles = ['bank_employee', 'office_employee'];
        var multiLocationRoles = ['branch_manager', 'loan_advisor'];
        var bankRoles = ['bank_employee', 'office_employee'];

        $('#editSingleCityField').toggle(singleCityRoles.indexOf(val) !== -1);
        $('#editMultiLocationField').toggle(multiLocationRoles.indexOf(val) !== -1);
        $('#editBankField').toggle(bankRoles.indexOf(val) !== -1);
        $('#editSingleBankField').toggle(val === 'bank_employee');
        $('#editMultiBankField').toggle(val === 'office_employee');
    });
});
</script>
@endpush
