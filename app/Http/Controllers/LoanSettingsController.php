<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\BankStageConfig;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\ProductStage;
use App\Models\Stage;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanSettingsController extends Controller
{
    public function index()
    {
        $banks = Bank::with(['products.productStages.branchUsers', 'products.locations', 'employees', 'locations.parent'])->orderBy('name')->get();
        $branches = Branch::with('location.parent')->orderBy('name')->get();
        $stages = Stage::orderBy('sequence_order')->get(); // All stages for Stage Master tab
        $enabledStages = Stage::where('is_enabled', true)->orderBy('sequence_order')->get(); // For product config
        $activeBranches = Branch::active()->orderBy('name')->get();
        $allActiveUsers = User::where('is_active', true)
            ->whereHas('roles')
            ->with(['employerBanks', 'roles'])
            ->orderBy('name')->get();

        // Role permissions data (using new roles system)
        $loanPermissions = Permission::where('group', 'Loans')->orderBy('id')->get();
        $workflowRoles = \App\Models\Role::whereNotIn('slug', ['super_admin'])->orderBy('id')->get();
        $rolePermissions = [];
        foreach ($workflowRoles as $wfRole) {
            $rolePermissions[$wfRole->slug] = $wfRole->permissions()->pluck('permissions.id')->toArray();
        }

        // Bank stage configs for Stage Master tab (bank-wise role overrides)
        $bankStageConfigs = BankStageConfig::all()->groupBy(function ($c) {
            return $c->bank_id.'_'.$c->stage_id;
        })->map->first();

        return view('loan-settings.index', compact('banks', 'branches', 'stages', 'enabledStages', 'activeBranches', 'allActiveUsers', 'loanPermissions', 'rolePermissions', 'workflowRoles', 'bankStageConfigs'));
    }

    /**
     * Save master stage defaults + bank-wise role overrides.
     */
    public function saveMasterStages(Request $request)
    {
        $stages = $request->input('stages', []);
        $bankConfigs = $request->input('bank_configs', []);
        $validRoles = ['task_owner', 'bank_employee', 'office_employee'];
        $changedBankStages = []; // Track which bank+stage combos changed for propagation

        foreach ($stages as $stageData) {
            if (empty($stageData['id'])) {
                continue;
            }

            $stage = Stage::find($stageData['id']);
            if (! $stage) {
                continue;
            }

            $assignedRole = in_array($stageData['assigned_role'] ?? '', $validRoles)
                ? $stageData['assigned_role']
                : $stage->assigned_role;

            $updateData = [
                'is_enabled' => ($stageData['is_enabled'] ?? 0) ? true : false,
                'assigned_role' => $assignedRole,
            ];

            // Update sub-action phase roles
            if (! empty($stageData['phase_roles']) && is_array($stage->sub_actions)) {
                $subActions = $stage->sub_actions;
                foreach ($stageData['phase_roles'] as $phaseIdx => $phaseRole) {
                    if (isset($subActions[$phaseIdx]) && in_array($phaseRole, $validRoles)) {
                        $subActions[$phaseIdx]['role'] = $phaseRole;
                    }
                }
                $updateData['sub_actions'] = $subActions;
            }

            $stage->update($updateData);

            // When a stage is disabled, also disable it in all product stage configs
            if (! $updateData['is_enabled']) {
                ProductStage::where('stage_id', $stage->id)->update(['is_enabled' => false]);
            }
        }

        // Save bank-wise role overrides
        foreach ($bankConfigs as $bankId => $bankStages) {
            foreach ($bankStages as $stageId => $config) {
                $stage = Stage::find($stageId);
                if (! $stage) {
                    continue;
                }

                $bankRole = in_array($config['assigned_role'] ?? '', $validRoles) ? $config['assigned_role'] : null;
                $phaseRoles = [];
                $hasPhaseOverride = false;

                if (! empty($config['phase_roles']) && is_array($config['phase_roles'])) {
                    $subActions = $stage->sub_actions ?? [];
                    foreach ($config['phase_roles'] as $phaseIdx => $phaseRole) {
                        if (in_array($phaseRole, $validRoles)) {
                            $phaseRoles[(string) $phaseIdx] = $phaseRole;
                            // Check if different from master default
                            $masterRole = $subActions[$phaseIdx]['role'] ?? 'task_owner';
                            if ($phaseRole !== $masterRole) {
                                $hasPhaseOverride = true;
                            }
                        }
                    }
                }

                // Only save bank config if it differs from master default
                $isDifferent = ($bankRole && $bankRole !== $stage->assigned_role) || $hasPhaseOverride;

                if ($isDifferent) {
                    $existing = BankStageConfig::where('bank_id', $bankId)->where('stage_id', $stageId)->first();
                    $newData = [
                        'assigned_role' => ($bankRole && $bankRole !== $stage->assigned_role) ? $bankRole : null,
                        'phase_roles' => $hasPhaseOverride ? $phaseRoles : null,
                    ];

                    if ($existing) {
                        $oldRole = $existing->assigned_role;
                        $oldPhases = $existing->phase_roles;
                        $existing->update($newData);
                        if ($oldRole !== $newData['assigned_role'] || $oldPhases !== $newData['phase_roles']) {
                            $changedBankStages[] = ['bank_id' => (int) $bankId, 'stage_id' => (int) $stageId];
                        }
                    } else {
                        BankStageConfig::create(array_merge($newData, [
                            'bank_id' => (int) $bankId,
                            'stage_id' => (int) $stageId,
                        ]));
                        $changedBankStages[] = ['bank_id' => (int) $bankId, 'stage_id' => (int) $stageId];
                    }
                } else {
                    // Remove override if it matches master default
                    $deleted = BankStageConfig::where('bank_id', $bankId)->where('stage_id', $stageId)->delete();
                    if ($deleted) {
                        $changedBankStages[] = ['bank_id' => (int) $bankId, 'stage_id' => (int) $stageId];
                    }
                }
            }
        }

        // Propagate: clear product stage overrides for changed bank+stage combos
        foreach ($changedBankStages as $change) {
            $productIds = \App\Models\Product::where('bank_id', $change['bank_id'])->pluck('id');
            if ($productIds->isNotEmpty()) {
                ProductStage::whereIn('product_id', $productIds)
                    ->where('stage_id', $change['stage_id'])
                    ->update(['sub_actions_override' => null]);
            }
        }

        ActivityLog::log('master_stages_updated', null, [
            'updated_by' => auth()->user()->name,
            'bank_overrides_changed' => count($changedBankStages),
        ]);

        return redirect()->route('loan-settings.index', ['tab' => 'master-stages'])
            ->with('success', 'Stage defaults saved');
    }

    public function storeLocation(Request $request)
    {
        $locationId = $request->input('id');

        $validated = $request->validate([
            'id' => 'nullable|exists:locations,id',
            'type' => 'required|in:state,city',
            'parent_id' => 'nullable|required_if:type,city|exists:locations,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
        ]);

        if ($validated['type'] === 'state') {
            $validated['parent_id'] = null;
        }

        if ($locationId) {
            \App\Models\Location::findOrFail($locationId)->update($validated);
            $message = 'Location updated';
        } else {
            \App\Models\Location::create($validated);
            $message = 'Location added';
        }

        return redirect(route('loan-settings.index').'#locations')->with('success', $message);
    }

    public function destroyLocation(\App\Models\Location $location): JsonResponse
    {
        if ($location->children()->exists()) {
            return response()->json(['error' => 'Cannot delete — has cities under it. Delete cities first.'], 422);
        }

        if (\App\Models\Branch::where('location_id', $location->id)->exists()) {
            return response()->json(['error' => 'Cannot delete — branches are assigned to this location.'], 422);
        }

        $location->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Save role permissions (Role Permissions tab).
     */
    public function saveTaskRolePermissions(Request $request)
    {
        $loanPermissionIds = Permission::where('group', 'Loans')->pluck('id')->toArray();
        $workflowRoles = \App\Models\Role::whereNotIn('slug', ['super_admin'])->get();

        foreach ($workflowRoles as $wfRole) {
            // Clear existing Loans-group role permissions
            \DB::table('role_permission')
                ->where('role_id', $wfRole->id)
                ->whereIn('permission_id', $loanPermissionIds)
                ->delete();

            $selected = $request->input("task_role.{$wfRole->slug}", []);
            foreach ($selected as $permissionId) {
                if (in_array((int) $permissionId, $loanPermissionIds)) {
                    \DB::table('role_permission')->insertOrIgnore([
                        'role_id' => $wfRole->id,
                        'permission_id' => (int) $permissionId,
                    ]);
                }
            }
        }

        app(PermissionService::class)->clearAllCaches();
        ActivityLog::log('role_permissions_updated', null, [
            'updated_by' => auth()->user()->name,
        ]);

        return redirect()->route('loan-settings.index', ['tab' => 'role-permissions'])
            ->with('success', 'Task role permissions updated.');
    }
}
