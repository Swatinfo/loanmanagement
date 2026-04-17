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
            $query->whereHas('roles', fn ($q) => $q->where('slug', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
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
        $columns = ['name', 'email', 'name', 'name', 'name', 'is_active', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 6);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $users = $query->skip($start)->take($length)->get();

        $authId = auth()->id();

        // Pre-load product assignments per user (from product_stage_users) — grouped by bank
        $userProductMap = \DB::table('product_stage_users as psu')
            ->join('product_stages as ps', 'psu.product_stage_id', '=', 'ps.id')
            ->join('products as p', 'ps.product_id', '=', 'p.id')
            ->join('banks as b', 'p.bank_id', '=', 'b.id')
            ->whereIn('psu.user_id', $users->pluck('id'))
            ->select('psu.user_id', 'b.name as bank_name', 'p.name as product_name')
            ->distinct()
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->groupBy('bank_name')->map(fn ($products) => $products->pluck('product_name')->unique()->sort()->values()));

        $isSuperAdmin = auth()->user()->isSuperAdmin();

        $data = $users->map(function ($user) use ($canEdit, $canCreate, $canDelete, $authId, $userProductMap, $isSuperAdmin) {
            // Name + phone
            $nameHtml = '<div class="fw-medium">'.e($user->name).'</div>';
            if ($user->phone) {
                $nameHtml .= '<div class="small" style="color:#6b7280;">'.e($user->phone).'</div>';
            }

            // Role badges (unified)
            $roleHtml = '';
            foreach ($user->roles as $r) {
                $badge = match ($r->slug) {
                    'super_admin' => 'shf-badge-orange',
                    'admin' => 'shf-badge-blue',
                    'bank_employee' => 'shf-badge-purple',
                    default => 'shf-badge-gray',
                };
                $roleHtml .= '<span class="shf-badge '.$badge.' me-1" style="font-size:0.7rem;">'.e($r->name).'</span>';
            }
            if ($user->employerBanks->isNotEmpty()) {
                $bankNames = $user->employerBanks->pluck('name')->unique();
                $userProducts = $userProductMap[$user->id] ?? collect();
                if ($userProducts->isNotEmpty()) {
                    $bankParts = $bankNames->map(function ($bank) use ($userProducts) {
                        $products = $userProducts[$bank] ?? null;

                        return $products ? e($bank).' <span style="font-size:0.6rem;color:#000;">('.e($products->implode(', ')).')</span>' : e($bank);
                    });
                    $roleHtml .= '<br><small class="text-muted">'.$bankParts->implode(', ').'</small>';
                } else {
                    $roleHtml .= '<br><small class="text-muted">'.e($bankNames->implode(', ')).'</small>';
                }
            }
            if ($user->locations->isNotEmpty()) {
                $roleHtml .= '<br><small class="location-info" style="font-size:0.65rem;">'.e($user->locations->pluck('name')->implode(', ')).'</small>';
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

            // Actions — super_admin users can only be managed by other super_admins
            $targetIsSuperAdmin = $user->roles->contains('slug', 'super_admin');
            $canManageThisUser = ! $targetIsSuperAdmin || $isSuperAdmin;

            $actions = '<div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">';
            $iconEdit = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> ';
            $iconCopy = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> ';
            $iconToggle = $user->is_active
                ? '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg> '
                : '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> ';
            $iconDelete = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> ';
            if ($canEdit && $canManageThisUser) {
                $actions .= '<a href="'.route('users.edit', $user).'" class="btn-accent-sm">'.$iconEdit.'Edit</a>';
            }
            if ($canCreate && $canManageThisUser) {
                $actions .= '<a href="'.route('users.create', ['copy' => $user->id]).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">'.$iconCopy.'Copy</a>';
            }
            if ($canEdit && $canManageThisUser && $user->id !== $authId) {
                $toggleColor = $user->is_active ? '#d97706,#f59e0b' : '#16a34a,#22c55e';
                $toggleLabel = $user->is_active ? 'Deactivate' : 'Activate';
                $actions .= '<button type="button" class="btn-accent-sm btn-toggle-active" data-url="'.route('users.toggle-active', $user).'" style="background:linear-gradient(135deg,'.$toggleColor.');">'.$iconToggle.$toggleLabel.'</button>';
            }
            if ($canDelete && $canManageThisUser && $user->id !== $authId) {
                $actions .= '<button type="button" class="btn-accent-sm btn-delete-user" data-url="'.route('users.destroy', $user).'" style="background:linear-gradient(135deg,#dc2626,#ef4444);">'.$iconDelete.'Delete</button>';
            }
            $actions .= '</div>';

            return [
                'name_html' => $nameHtml,
                'email' => e($user->email),
                'role_html' => $roleHtml,
                'branch_html' => $branchHtml,
                'status_html' => $statusHtml,
                'created_html' => $createdHtml,
                'actions_html' => $actions,
                // For mobile cards
                'name' => $user->name,
                'phone' => $user->phone,
                'role_label' => $user->role_label,
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
            if ($copyFrom && $copyFrom->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
                $copyFrom = null;
            }
        }

        return view('users.create', compact('roles', 'branches', 'copyFrom'));
    }

    public function store(Request $request)
    {
        $roles = $this->getAllowedRoles();

        $allRoleSlugs = \App\Models\Role::pluck('slug')->toArray();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($allRoleSlugs)],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_banks' => ['nullable', 'array'],
            'assigned_banks.*' => ['exists:banks,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'default_branch_id' => $validated['default_branch_id'] ?? null,
        ]);

        // Sync roles
        $roleIds = \App\Models\Role::whereIn('slug', $validated['roles'])->pluck('id');
        $user->roles()->sync($roleIds);

        // Sync branches: multi-branch assignment
        $this->syncUserBranches($user, $request, $validated);

        // Sync bank assignments for bank_employee/office_employee
        $this->syncBankAssignments($user, $request, $validated);

        // Replace in product stages (bulk update)
        $this->replaceProductStageUsers($user, $request, $validated);

        // Sync location assignments
        $user->locations()->sync($request->input('assigned_locations', []));

        // Handle permission overrides
        if ($request->has('permissions')) {
            $this->syncUserPermissions($user, $request->input('permissions', []));
        }

        ActivityLog::log('user_created', $user, ['roles' => $validated['roles']]);
        app(PermissionService::class)->clearUserCache($user);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Cannot edit a Super Admin account.');
        }

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

        $allRoleSlugs = \App\Models\Role::pluck('slug')->toArray();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($allRoleSlugs)],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_banks' => ['nullable', 'array'],
            'assigned_banks.*' => ['exists:banks,id'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'default_branch_id' => $validated['default_branch_id'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync roles
        $roleIds = \App\Models\Role::whereIn('slug', $validated['roles'])->pluck('id');
        $user->roles()->sync($roleIds);

        // Sync branches: multi-branch assignment
        $this->syncUserBranches($user, $request, $validated);

        // Sync bank assignments for bank_employee/office_employee
        $this->syncBankAssignments($user, $request, $validated);

        // Replace in product stages (bulk update)
        $this->replaceProductStageUsers($user, $request, $validated);

        // Sync location assignments
        $user->locations()->sync($request->input('assigned_locations', []));

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

        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Cannot change status of a Super Admin account.');
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

        $roles = \App\Models\Role::orderBy('id')->pluck('name', 'slug')->toArray();

        if ($currentUser->isSuperAdmin()) {
            return $roles;
        }

        if ($currentUser->isAdmin()) {
            unset($roles['super_admin']);

            return $roles;
        }

        // Non-admin users cannot manage roles
        return [];
    }

    /**
     * Sync user branches: multi-branch assignment with per-branch OE defaults.
     */
    protected function syncUserBranches(User $user, Request $request, array $validated): void
    {
        $assignedBranches = array_map('intval', $request->input('assigned_branches', []));
        $defaultBranchId = $validated['default_branch_id'] ?? null;

        // Always include the primary branch
        if ($defaultBranchId && ! in_array((int) $defaultBranchId, $assignedBranches)) {
            $assignedBranches[] = (int) $defaultBranchId;
        }

        if (empty($assignedBranches)) {
            $user->branches()->sync([]);

            return;
        }

        // Build pivot data with is_default_office_employee flags
        $defaultOeBranches = array_map('intval', $request->input('default_oe_branches', []));
        $isOE = in_array('office_employee', $validated['roles']);
        $pivotData = [];

        foreach ($assignedBranches as $branchId) {
            $isDefaultOE = $isOE && in_array($branchId, $defaultOeBranches);

            // If setting as default OE, clear previous default from other users
            if ($isDefaultOE) {
                \DB::table('user_branches')
                    ->where('branch_id', $branchId)
                    ->where('is_default_office_employee', true)
                    ->where('user_id', '!=', $user->id)
                    ->update(['is_default_office_employee' => false]);
            }

            $pivotData[$branchId] = ['is_default_office_employee' => $isDefaultOE];
        }

        $user->branches()->sync($pivotData);
    }

    /**
     * Sync bank assignments for bank_employee/office_employee.
     */
    protected function syncBankAssignments(User $user, Request $request, array $validated): void
    {
        $bankRoles = ['bank_employee', 'office_employee'];
        if (! array_intersect($validated['roles'], $bankRoles)) {
            $user->employerBanks()->detach();

            return;
        }

        $assignedBanks = $request->input('assigned_banks', []);
        $user->employerBanks()->sync($assignedBanks);

        // Handle bank_employee city defaults
        if (in_array('bank_employee', $validated['roles']) && ! empty($assignedBanks)) {
            $bankId = (int) $assignedBanks[0];
            $defaultCities = array_map('intval', $request->input('default_bank_cities', []));

            // Clear this user's defaults for this bank
            \DB::table('bank_employees')
                ->where('bank_id', $bankId)
                ->where('user_id', $user->id)
                ->update(['is_default' => false, 'location_id' => null]);

            // Set new city defaults
            foreach ($defaultCities as $cityId) {
                \DB::table('bank_employees')
                    ->where('bank_id', $bankId)
                    ->where('location_id', $cityId)
                    ->where('is_default', true)
                    ->update(['is_default' => false, 'location_id' => null]);

                \DB::table('bank_employees')
                    ->where('bank_id', $bankId)
                    ->where('user_id', $user->id)
                    ->update(['is_default' => true, 'location_id' => $cityId]);
            }
        }
    }

    /**
     * Bulk replace users in product_stage_users (per-product scoped).
     * Input: replace_psu[] = ["15_5", "16_6"] (format: oldUserId_productId)
     */
    protected function replaceProductStageUsers(User $user, Request $request, array $validated): void
    {
        $replacePsu = $request->input('replace_psu', []);
        if (empty($replacePsu)) {
            return;
        }

        $newUserRole = in_array('bank_employee', $validated['roles']) ? 'bank_employee' : (in_array('office_employee', $validated['roles']) ? 'office_employee' : null);
        if (! $newUserRole) {
            return;
        }

        $locationIds = array_map('intval', $request->input('assigned_locations', []));

        // Get stage IDs where assigned_role or any phase role matches the new user's role
        $roleMatchingStageIds = \App\Models\Stage::where('is_enabled', true)->get()
            ->filter(function ($s) use ($newUserRole) {
                // Check stage-level assigned_role
                if ($s->assigned_role === $newUserRole) {
                    return true;
                }
                // Check phase-level roles in sub_actions
                if (is_array($s->sub_actions)) {
                    foreach ($s->sub_actions as $sa) {
                        if (($sa['role'] ?? null) === $newUserRole) {
                            return true;
                        }
                    }
                }

                return false;
            })
            ->pluck('id')
            ->toArray();

        foreach ($replacePsu as $key) {
            $parts = explode('_', $key);
            if (count($parts) !== 2) {
                continue;
            }
            $oldUserId = (int) $parts[0];
            $productId = (int) $parts[1];

            // Get product_stage IDs scoped to this product + role-matching stages
            $productStageIds = \DB::table('product_stages')
                ->where('product_id', $productId)
                ->whereIn('stage_id', $roleMatchingStageIds)
                ->pluck('id')
                ->toArray();

            if (empty($productStageIds)) {
                continue;
            }

            // Update only PSU records matching: old user + this product's stages + this location
            $count = \DB::table('product_stage_users')
                ->where('user_id', $oldUserId)
                ->whereIn('product_stage_id', $productStageIds)
                ->where(function ($q) use ($locationIds) {
                    $q->whereIn('location_id', $locationIds)
                        ->orWhereNull('location_id');
                })
                ->update(['user_id' => $user->id]);

            if ($count > 0) {
                $oldUser = User::find($oldUserId);
                $product = \App\Models\Product::find($productId);
                ActivityLog::log('replace_product_stages', $user, [
                    'replaced' => $oldUser?->name ?? "User #{$oldUserId}",
                    'product' => $product?->name ?? "Product #{$productId}",
                    'count' => $count,
                ]);
            }
        }
    }

    /**
     * AJAX: Get current product stage holders grouped by product, filtered by role.
     */
    public function productStageHolders(Request $request): \Illuminate\Http\JsonResponse
    {
        $locationId = $request->integer('location_id');
        $bankIds = $request->input('bank_ids', []);
        $role = $request->input('role', '');
        $excludeUserId = $request->integer('exclude_user_id');

        if (! $locationId || ! $role) {
            return response()->json([]);
        }

        // Get stage IDs where default_role contains the requested role
        $roleMatchingStageIds = \App\Models\Stage::where('is_enabled', true)->get()
            ->filter(fn ($s) => is_array($s->default_role) && in_array($role, $s->default_role))
            ->pluck('id')
            ->toArray();

        if (empty($roleMatchingStageIds)) {
            return response()->json([]);
        }

        $query = \DB::table('product_stage_users as psu')
            ->join('product_stages as ps', 'ps.id', '=', 'psu.product_stage_id')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->join('stages as s', 's.id', '=', 'ps.stage_id')
            ->join('users as u', 'u.id', '=', 'psu.user_id')
            ->join('banks as b', 'b.id', '=', 'p.bank_id')
            ->where('psu.location_id', $locationId)
            ->where('psu.is_default', true)
            ->whereIn('ps.stage_id', $roleMatchingStageIds);

        // Scope to selected banks (single for bank_employee, multiple for office_employee)
        if (! empty($bankIds)) {
            $query->whereIn('p.bank_id', $bankIds);
        }

        $rows = $query->select(
            'psu.user_id', 'u.name as user_name',
            'p.id as product_id', 'p.name as product_name',
            'b.name as bank_name',
            's.stage_name_en',
        )->orderBy('b.name')->orderBy('p.name')->orderBy('s.sequence_order')->get();

        // Group by product + user
        $grouped = [];
        foreach ($rows as $row) {
            $key = $row->user_id.'_'.$row->product_id;
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'user_id' => $row->user_id,
                    'user_name' => $row->user_name,
                    'product_id' => $row->product_id,
                    'product_name' => $row->product_name,
                    'bank_name' => $row->bank_name,
                    'stages' => [],
                ];
            }
            $grouped[$key]['stages'][] = $row->stage_name_en;
        }

        // Format for JSON
        $result = array_values(array_map(function ($item) {
            $item['stage_list'] = implode(', ', $item['stages']);
            $item['stage_count'] = count($item['stages']);
            unset($item['stages']);

            return $item;
        }, $grouped));

        return response()->json($result);
    }

    /**
     * AJAX: Check if an email is available (unique).
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->input('email', '');
        $excludeId = $request->integer('exclude_id');

        $query = User::where('email', $email);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['available' => ! $query->exists()]);
    }

    /**
     * Sync user-specific permission overrides.
     */
    protected function syncUserPermissions(User $user, array $permissions): void
    {
        UserPermission::where('user_id', $user->id)->delete();

        foreach ($permissions as $permissionId => $type) {
            if (in_array($type, ['grant', 'deny'])) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission_id' => $permissionId,
                    'type' => $type,
                ]);
            }
        }
    }
}
