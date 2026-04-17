<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\BankStageConfig;
use App\Models\Branch;
use App\Models\LoanDetail;
use App\Models\Product;
use App\Models\ProductStage;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowConfigController extends Controller
{
    public function index()
    {
        $banks = Bank::with('products')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $stages = Stage::where('is_enabled', true)->orderBy('sequence_order')->get();

        return view('settings.workflow', compact('banks', 'branches', 'stages'));
    }

    public function storeBank(Request $request)
    {
        $bankId = $request->input('id');

        $validated = $request->validate([
            'id' => 'nullable|exists:banks,id',
            'name' => 'required|string|max:255|unique:banks,name,'.($bankId ?? 'NULL'),
            'code' => 'nullable|string|max:20|unique:banks,code,'.($bankId ?? 'NULL'),
            'bank_locations' => 'nullable|array',
            'bank_locations.*' => 'exists:locations,id',
        ]);

        if ($validated['id'] ?? null) {
            $bank = Bank::findOrFail($validated['id']);
            $bank->update([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
            ]);
            $message = 'Bank updated';
        } else {
            $bank = Bank::create([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
            ]);
            $message = 'Bank created';
        }

        // Sync bank locations
        $bank->locations()->sync($request->input('bank_locations', []));

        return redirect(route('loan-settings.index').'#banks')->with('success', $message);
    }

    public function destroyBank(Bank $bank): JsonResponse
    {
        if ($bank->products()->exists()) {
            return response()->json(['error' => 'Cannot delete bank with existing products. Remove products first.'], 422);
        }

        if (LoanDetail::where('bank_id', $bank->id)->whereNull('deleted_at')->exists()) {
            return response()->json(['error' => 'Cannot delete bank with active loans.'], 422);
        }

        $bank->delete();

        return response()->json(['success' => true]);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:products,id',
            'bank_id' => 'required|exists:banks,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
        ]);

        // Check unique (bank_id + name)
        $exists = Product::where('bank_id', $validated['bank_id'])
            ->where('name', $validated['name'])
            ->when($validated['id'] ?? null, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'This product already exists for the selected bank.');
        }

        if ($validated['id'] ?? null) {
            Product::findOrFail($validated['id'])->update($validated);
        } else {
            Product::create($validated);
        }

        return redirect()->route('loan-settings.index')->with('success', 'Product saved');
    }

    public function saveProductLocations(Request $request, Product $product)
    {
        $product->locations()->sync($request->input('locations', []));

        return redirect(route('loan-settings.index').'#products')
            ->with('success', 'Locations updated for '.$product->name);
    }

    public function productStages(Product $product)
    {
        $product->load(['bank.locations', 'locations']);
        $stages = Stage::where('is_enabled', true)->orderBy('sequence_order')->get();
        $productStages = $product->productStages()->with('branchUsers')->get()->keyBy('stage_id');
        $branches = \App\Models\Branch::active()->orderBy('name')->get();
        $allActiveUsers = \App\Models\User::where('is_active', true)
            ->whereHas('roles')
            ->with(['employerBanks', 'locations', 'roles'])
            ->orderBy('name')->get();

        // Load bank-level stage config for this product's bank
        $bankStageConfigs = BankStageConfig::where('bank_id', $product->bank_id)
            ->get()
            ->keyBy('stage_id');

        // Build resolved roles per stage and phase (bank config → master default)
        $resolvedRoles = [];
        foreach ($stages as $stage) {
            $bsc = $bankStageConfigs[$stage->id] ?? null;
            $resolvedRoles[$stage->id] = [
                'role' => $bsc?->assigned_role ?? $stage->assigned_role ?? 'task_owner',
                'phases' => [],
            ];
            if (is_array($stage->sub_actions)) {
                foreach ($stage->sub_actions as $idx => $sa) {
                    $resolvedRoles[$stage->id]['phases'][$idx] = $bsc?->phase_roles[(string) $idx]
                        ?? $sa['role'] ?? 'task_owner';
                }
            }
        }

        return view('settings.workflow-product-stages', compact('product', 'stages', 'productStages', 'branches', 'allActiveUsers', 'resolvedRoles'));
    }

    public function saveProductStages(Request $request, Product $product)
    {
        $stages = $request->input('stages', []);

        foreach ($stages as $stageData) {
            if (empty($stageData['stage_id'])) {
                continue;
            }

            // Build sub_actions_override from submitted role checkboxes + user assignments
            $subActionsOverride = null;
            if (! empty($stageData['sub_actions_override']) && is_array($stageData['sub_actions_override'])) {
                $subActionsOverride = [];
                foreach ($stageData['sub_actions_override'] as $saIdx => $saData) {
                    $saRoles = $saData['roles'] ?? [];
                    $saUsers = array_map('intval', array_filter($saData['users'] ?? []));
                    $saDefaultUser = ! empty($saData['default_user']) ? (int) $saData['default_user'] : null;
                    // Process location overrides for this sub-action
                    $saLocationOverrides = [];
                    if (! empty($saData['location_overrides']) && is_array($saData['location_overrides'])) {
                        foreach ($saData['location_overrides'] as $saLocOverride) {
                            $saLocId = $saLocOverride['location_id'] ?? null;
                            if (! $saLocId) {
                                continue;
                            }
                            $saLocationOverrides[] = [
                                'location_id' => (int) $saLocId,
                                'users' => array_map('intval', array_filter($saLocOverride['users'] ?? [])),
                                'default' => ! empty($saLocOverride['default']) ? (int) $saLocOverride['default'] : null,
                            ];
                        }
                    }

                    $subActionsOverride[(int) $saIdx] = [
                        'is_enabled' => ($saData['is_enabled'] ?? 0) ? true : false,
                        'roles' => array_values(array_intersect($saRoles, \App\Models\Role::pluck('slug')->toArray())),
                        'users' => $saUsers,
                        'default_user' => $saDefaultUser,
                        'location_overrides' => $saLocationOverrides,
                    ];
                }
            }

            $productStage = ProductStage::updateOrCreate(
                ['product_id' => $product->id, 'stage_id' => $stageData['stage_id']],
                [
                    'is_enabled' => ($stageData['is_enabled'] ?? 0) ? true : false,
                    'default_assignee_role' => $stageData['default_assignee_role'] ?? null,
                    'default_user_id' => $stageData['default_user_id'] ?? null,
                    'auto_skip' => ($stageData['auto_skip'] ?? 0) ? true : false,
                    'sub_actions_override' => $subActionsOverride,
                ],
            );

            // Save branch-wise multi-user assignments with default
            if (isset($stageData['branch_users']) && is_array($stageData['branch_users'])) {
                // Delete only branch-based assignments (keep location-based)
                $productStage->branchUsers()->whereNotNull('branch_id')->delete();

                foreach ($stageData['branch_users'] as $branchId => $branchData) {
                    // Support both old format (single user_id) and new format (users[] + default)
                    if (is_array($branchData) && isset($branchData['users'])) {
                        $userIds = array_filter($branchData['users'] ?? []);
                        $defaultUserId = $branchData['default'] ?? null;
                        foreach ($userIds as $userId) {
                            \App\Models\ProductStageUser::create([
                                'product_stage_id' => $productStage->id,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                                'is_default' => (int) $userId === (int) $defaultUserId,
                            ]);
                        }
                    } elseif ($branchData) {
                        // Backward compat: single user_id
                        \App\Models\ProductStageUser::create([
                            'product_stage_id' => $productStage->id,
                            'branch_id' => $branchId,
                            'user_id' => $branchData,
                            'is_default' => true,
                        ]);
                    }
                }
            }

            // Save location-based user assignments (stage-level, phase_index = null)
            if (isset($stageData['location_overrides']) && is_array($stageData['location_overrides'])) {
                // Delete only location-based, stage-level assignments
                $productStage->branchUsers()->whereNull('branch_id')->whereNotNull('location_id')->whereNull('phase_index')->delete();

                foreach ($stageData['location_overrides'] as $override) {
                    $locationId = $override['location_id'] ?? null;
                    if (! $locationId) {
                        continue;
                    }
                    $userIds = array_filter($override['users'] ?? []);
                    $defaultUserId = $override['default'] ?? null;
                    foreach ($userIds as $userId) {
                        \App\Models\ProductStageUser::create([
                            'product_stage_id' => $productStage->id,
                            'branch_id' => null,
                            'location_id' => $locationId,
                            'user_id' => $userId,
                            'is_default' => (int) $userId === (int) $defaultUserId,
                        ]);
                    }
                }
            }

            // Save phase-level location user assignments
            if (isset($stageData['phase_location_overrides']) && is_array($stageData['phase_location_overrides'])) {
                foreach ($stageData['phase_location_overrides'] as $phaseIdx => $phaseOverrides) {
                    // Delete existing phase-level location assignments for this phase
                    $productStage->branchUsers()->whereNull('branch_id')->whereNotNull('location_id')
                        ->where('phase_index', (int) $phaseIdx)->delete();

                    if (! is_array($phaseOverrides)) {
                        continue;
                    }
                    foreach ($phaseOverrides as $override) {
                        $locationId = $override['location_id'] ?? null;
                        if (! $locationId) {
                            continue;
                        }
                        $userIds = array_filter($override['users'] ?? []);
                        $defaultUserId = $override['default'] ?? null;
                        foreach ($userIds as $userId) {
                            \App\Models\ProductStageUser::create([
                                'product_stage_id' => $productStage->id,
                                'branch_id' => null,
                                'location_id' => $locationId,
                                'user_id' => $userId,
                                'is_default' => (int) $userId === (int) $defaultUserId,
                                'phase_index' => (int) $phaseIdx,
                            ]);
                        }
                    }
                }
            }
        }

        ActivityLog::log('save_product_stages', $product, [
            'product_name' => $product->name,
            'bank_name' => $product->bank->name,
        ]);

        return redirect(route('loan-settings.index').'#products')
            ->with('success', 'Stage configuration saved for '.$product->name);
    }

    public function storeBranch(Request $request)
    {
        $branchId = $request->input('id');

        $validated = $request->validate([
            'id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255|unique:branches,name,'.($branchId ?? 'NULL'),
            'code' => 'nullable|string|max:20|unique:branches,code,'.($branchId ?? 'NULL'),
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'manager_id' => 'required|exists:users,id',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        if ($validated['id'] ?? null) {
            Branch::findOrFail($validated['id'])->update($validated);
        } else {
            Branch::create($validated);
        }

        return redirect(route('loan-settings.index').'#branches')->with('success', 'Branch saved');
    }

    public function destroyProduct(Product $product): JsonResponse
    {
        if (LoanDetail::where('product_id', $product->id)->whereNull('deleted_at')->exists()) {
            return response()->json(['error' => 'Cannot delete product — it has active loans.'], 422);
        }

        $product->delete();

        return response()->json(['success' => true]);
    }

    public function destroyBranch(Branch $branch): JsonResponse
    {
        if ($branch->users()->exists()) {
            return response()->json(['error' => 'Cannot delete branch with assigned users.'], 422);
        }

        if (LoanDetail::where('branch_id', $branch->id)->whereNull('deleted_at')->exists()) {
            return response()->json(['error' => 'Cannot delete branch with active loans.'], 422);
        }

        $branch->delete();

        return response()->json(['success' => true]);
    }
}
