<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        // Exclude Loans group — managed separately via Loan Settings → Role Permissions
        $permissions = Permission::where('group', '!=', 'Loans')->get()->groupBy('group');
        $roles = Role::whereNotIn('slug', ['super_admin'])->orderBy('id')->get();

        // Get current role-permission mappings
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->slug] = $role->permissions()->pluck('permissions.id')->toArray();
        }

        return view('permissions.index', compact('permissions', 'roles', 'rolePermissions'));
    }

    public function update(Request $request)
    {
        $editableRoles = Role::whereNotIn('slug', ['super_admin'])->get();
        // Only manage non-Loans permissions here (Loans managed in Loan Settings)
        $allPermissionIds = Permission::where('group', '!=', 'Loans')->pluck('id')->toArray();

        foreach ($editableRoles as $role) {
            // Get current Loans-group permissions for this role (preserve them)
            $loansPermIds = \DB::table('role_permission')
                ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                ->where('role_permission.role_id', $role->id)
                ->where('permissions.group', 'Loans')
                ->pluck('permissions.id')
                ->toArray();

            // Get selected non-Loans permissions from form
            $selectedPermissions = collect($request->input("role.{$role->slug}", []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => in_array($id, $allPermissionIds))
                ->toArray();

            // Sync: selected non-Loans + preserved Loans
            $role->permissions()->sync(array_merge($selectedPermissions, $loansPermIds));
        }

        // Super admin always gets all permissions
        $superAdmin = Role::where('slug', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id')->toArray());
        }

        ActivityLog::log('permissions_updated', null, [
            'roles' => $editableRoles->pluck('slug')->toArray(),
        ]);

        app(PermissionService::class)->clearAllCaches();

        return redirect()->route('permissions.index')->with('success', 'Permissions updated successfully.');
    }
}
