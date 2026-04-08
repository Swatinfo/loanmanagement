<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function userData(Request $request): JsonResponse
    {
        $query = User::with(['creator', 'branches', 'taskBank', 'employerBanks', 'locations']);
        $canEdit = auth()->user()->hasPermission('edit_users');
        $canCreate = auth()->user()->hasPermission('create_users');
        $canDelete = auth()->user()->hasPermission('delete_users');

        $recordsTotal = (clone $query)->count();

        // Custom filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('task_role')) {
            $query->where('task_role', $request->task_role);
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Order
        $columns = ['name', 'email', 'role', 'task_role', 'name', 'is_active', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 6);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $users = $query->skip($start)->take($length)->get();

        $authId = auth()->id();
        $roleLabels = User::TASK_ROLE_LABELS;

        $data = $users->map(function ($user) use ($canEdit, $canCreate, $canDelete, $authId) {
            // Name + phone
            $nameHtml = '<div class="fw-medium">'.e($user->name).'</div>';
            if ($user->phone) {
                $nameHtml .= '<div class="small" style="color:#6b7280;">'.e($user->phone).'</div>';
            }

            // Role badge
            $roleBadge = $user->isSuperAdmin() ? 'shf-badge-orange' : ($user->isAdmin() ? 'shf-badge-blue' : 'shf-badge-gray');
            $roleHtml = '<span class="shf-badge '.$roleBadge.'">'.e($user->role_label).'</span>';

            // Loan role
            $loanRoleHtml = '<small class="text-muted">—</small>';
            if ($user->task_role_label) {
                $loanRoleHtml = '<span class="shf-badge shf-badge-blue" style="font-size:0.7rem;">'.e($user->task_role_label).'</span>';
                if ($user->employerBanks->isNotEmpty()) {
                    $loanRoleHtml .= '<br><small class="text-muted">'.e($user->employerBanks->pluck('name')->implode(', ')).'</small>';
                } elseif ($user->taskBank) {
                    $loanRoleHtml .= '<br><small class="text-muted">'.e($user->taskBank->name).'</small>';
                }
                if ($user->locations->isNotEmpty()) {
                    $loanRoleHtml .= '<br><small class="text-info" style="font-size:0.65rem;">'.e($user->locations->pluck('name')->implode(', ')).'</small>';
                }
            }

            // Branch
            $branchHtml = $user->branches->isNotEmpty()
                ? '<small>'.e($user->branches->pluck('name')->implode(', ')).'</small>'
                : '<small class="text-muted">—</small>';

            // Status
            $statusHtml = '<span class="shf-badge '.($user->is_active ? 'shf-badge-green' : 'shf-badge-red').'">'
                .($user->is_active ? 'Active' : 'Inactive').'</span>';

            // Created
            $createdHtml = '<span style="color:#6b7280;white-space:nowrap;">'.$user->created_at->format('d M Y').'</span>';
            if ($user->creator) {
                $createdHtml .= '<div class="small" style="color:#9ca3af;">by '.e($user->creator->name).'</div>';
            }

            // Actions
            $actions = '<div class="d-flex align-items-center justify-content-end gap-2">';
            $iconEdit = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> ';
            $iconCopy = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> ';
            $iconToggle = $user->is_active
                ? '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg> '
                : '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ';
            $iconDelete = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> ';
            if ($canEdit) {
                $actions .= '<a href="'.route('users.edit', $user).'" class="btn-accent-sm">'.$iconEdit.'Edit</a>';
            }
            if ($canCreate) {
                $actions .= '<a href="'.route('users.create', ['copy' => $user->id]).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">'.$iconCopy.'Copy</a>';
            }
            if ($canEdit && $user->id !== $authId) {
                $toggleColor = $user->is_active ? '#d97706,#f59e0b' : '#16a34a,#22c55e';
                $toggleLabel = $user->is_active ? 'Deactivate' : 'Activate';
                $actions .= '<button type="button" class="btn-accent-sm btn-toggle-active" data-url="'.route('users.toggle-active', $user).'" style="background:linear-gradient(135deg,'.$toggleColor.');">'.$iconToggle.$toggleLabel.'</button>';
            }
            if ($canDelete && $user->id !== $authId) {
                $actions .= '<button type="button" class="btn-accent-sm btn-delete-user" data-url="'.route('users.destroy', $user).'" style="background:linear-gradient(135deg,#dc2626,#ef4444);">'.$iconDelete.'Delete</button>';
            }
            $actions .= '</div>';

            return [
                'name_html' => $nameHtml,
                'email' => e($user->email),
                'role_html' => $roleHtml,
                'loan_role_html' => $loanRoleHtml,
                'branch_html' => $branchHtml,
                'status_html' => $statusHtml,
                'created_html' => $createdHtml,
                'actions_html' => $actions,
                // For mobile cards
                'name' => $user->name,
                'phone' => $user->phone,
                'role_label' => $user->role_label,
                'task_role_label' => $user->task_role_label ?? '',
                'is_active' => $user->is_active,
                'branches' => $user->branches->pluck('name')->implode(', '),
                'created_at' => $user->created_at->format('d M Y'),
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function create()
    {
        $roles = $this->getAllowedRoles();
        $branches = \App\Models\Branch::active()->orderBy('name')->get();

        // Copy from existing user
        $copyFrom = null;
        if (request('copy')) {
            $copyFrom = User::find(request('copy'));
        }

        return view('users.create', compact('roles', 'branches', 'copyFrom'));
    }

    public function store(Request $request)
    {
        $roles = $this->getAllowedRoles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys($roles))],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'task_role' => ['nullable', Rule::in(User::TASK_ROLES)],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_banks' => ['nullable', 'array'],
            'assigned_banks.*' => ['exists:banks,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'task_role' => $validated['task_role'] ?? null,
            'default_branch_id' => $validated['default_branch_id'] ?? null,
        ]);

        // Assign to branch
        if ($validated['default_branch_id'] ?? null) {
            $user->branches()->sync([$validated['default_branch_id']]);
        }

        // Sync bank assignments
        $bankRoles = ['bank_employee', 'office_employee'];
        if (in_array($validated['task_role'] ?? null, $bankRoles)) {
            $assignedBanks = $request->input('assigned_banks', []);
            $user->employerBanks()->sync($assignedBanks);
            if (! empty($assignedBanks)) {
                $user->update(['task_bank_id' => $assignedBanks[0]]);
            }
        }

        // Sync location assignments for all task roles
        if ($validated['task_role'] ?? null) {
            $user->locations()->sync($request->input('assigned_locations', []));
        }

        ActivityLog::log('user_created', $user, ['role' => $user->role]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = $this->getAllowedRoles();
        $permissions = Permission::all()->groupBy('group');
        $userOverrides = UserPermission::where('user_id', $user->id)
            ->pluck('type', 'permission_id')
            ->toArray();
        $branches = \App\Models\Branch::active()->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'permissions', 'userOverrides', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing super_admin unless you are super_admin
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Cannot edit a Super Admin account.');
        }

        $roles = $this->getAllowedRoles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys($roles))],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'task_role' => ['nullable', Rule::in(User::TASK_ROLES)],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_banks' => ['nullable', 'array'],
            'assigned_banks.*' => ['exists:banks,id'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'task_role' => $validated['task_role'] ?? null,
            'default_branch_id' => $validated['default_branch_id'] ?? null,
        ]);

        // Sync branch
        if ($validated['default_branch_id'] ?? null) {
            $user->branches()->syncWithoutDetaching([$validated['default_branch_id']]);
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync bank assignments
        $bankRoles = ['bank_employee', 'office_employee'];
        if (in_array($validated['task_role'] ?? null, $bankRoles)) {
            $assignedBanks = $request->input('assigned_banks', []);
            $user->employerBanks()->sync($assignedBanks);
            // Set task_bank_id to first assigned bank
            $user->update(['task_bank_id' => ! empty($assignedBanks) ? $assignedBanks[0] : null]);
        } else {
            $user->employerBanks()->detach();
            $user->update(['task_bank_id' => null]);
        }

        // Sync location assignments for all task roles
        if ($validated['task_role'] ?? null) {
            $user->locations()->sync($request->input('assigned_locations', []));
        } else {
            $user->locations()->detach();
        }

        // Handle user-specific permission overrides
        if ($request->has('permissions')) {
            $this->syncUserPermissions($user, $request->input('permissions', []));
        }

        ActivityLog::log('user_updated', $user);
        app(PermissionService::class)->clearUserCache($user);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        // Prevent deleting super_admin unless you are super_admin
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Cannot delete a Super Admin account.'], 403);
        }

        // Prevent deleting users who have loans
        if (\App\Models\LoanDetail::where('created_by', $user->id)->exists()) {
            return response()->json(['message' => 'Cannot delete this user because they have associated loans. Deactivate the user instead.'], 422);
        }

        ActivityLog::log('user_deleted', $user, ['name' => $user->name, 'email' => $user->email]);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        ActivityLog::log($user->is_active ? 'user_activated' : 'user_deactivated', $user);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Get allowed roles based on current user's role hierarchy.
     */
    protected function getAllowedRoles(): array
    {
        $currentUser = auth()->user();

        if ($currentUser->isSuperAdmin()) {
            return [
                'super_admin' => 'Super Admin',
                'admin' => 'Admin',
                'staff' => 'Staff',
                'bank_employee' => 'Bank Employee',
            ];
        }

        if ($currentUser->isAdmin()) {
            return [
                'admin' => 'Admin',
                'staff' => 'Staff',
                'bank_employee' => 'Bank Employee',
            ];
        }

        return ['staff' => 'Staff'];
    }

    /**
     * Sync user-specific permission overrides.
     */
    protected function syncUserPermissions(User $user, array $permissions): void
    {
        // Delete existing overrides
        UserPermission::where('user_id', $user->id)->delete();

        foreach ($permissions as $permissionId => $type) {
            if (in_array($type, ['grant', 'deny'])) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission_id' => $permissionId,
                    'type' => $type,
                ]);
            }
            // 'default' means no override, so skip
        }
    }
}
