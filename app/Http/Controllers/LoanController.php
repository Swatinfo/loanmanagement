<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\Branch;
use App\Models\LoanDetail;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Stage;
use App\Models\User;
use App\Services\LoanConversionService;
use App\Validation\LoanValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        private LoanConversionService $conversionService,
    ) {}

    public function index()
    {
        $baseQuery = LoanDetail::visibleTo(auth()->user());
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        $user = auth()->user();
        $banks = Bank::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();
        $isBankEmployee = $user->hasRole('bank_employee');
        // Bank employees participate in these stages (via default_role or phase actions)
        $bankEmployeeStages = ['bsm_osv', 'rate_pf', 'sanction', 'legal_verification', 'esign'];
        $stages = \App\Models\Stage::where('is_enabled', true)
            ->when($isBankEmployee, fn ($q) => $q->whereIn('stage_key', $bankEmployeeStages))
            ->when(! $isBankEmployee, fn ($q) => $q->whereNull('parent_stage_key'))
            ->orderBy('sequence_order')
            ->get();

        return view('loans.index', compact('stats', 'banks', 'branches', 'stages'));
    }

    public function loanData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $canEdit = $user->hasPermission('edit_loan');
        $canDelete = $user->hasPermission('delete_loan');

        $query = LoanDetail::visibleTo($user)->with(['creator', 'advisor', 'bank', 'branch', 'product', 'location.parent', 'stageAssignments.assignee.roles']);

        $recordsTotal = (clone $query)->count();

        // Custom filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('stage')) {
            $query->where('current_stage', $request->stage);
        }
        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('stageAssignments', function ($q) use ($role) {
                $q->whereColumn('stage_key', 'loan_details.current_stage')
                    ->whereHas('assignee', fn ($uq) => $uq->whereHas('roles', fn ($rq) => $rq->where('slug', $role)));
            });
        }
        if ($request->filled('docket')) {
            $query->where('status', 'active')
                ->whereNotNull('expected_docket_date')
                ->whereHas('stageAssignments', fn ($q) => $q->where('stage_key', 'docket')->where('status', '!=', 'completed'));

            match ($request->docket) {
                'overdue' => $query->where('expected_docket_date', '<', now()->toDateString()),
                'due_today' => $query->whereDate('expected_docket_date', now()->toDateString()),
                'due_soon' => $query->whereBetween('expected_docket_date', [now()->toDateString(), now()->addDays(7)->toDateString()]),
                'due_15' => $query->whereBetween('expected_docket_date', [now()->toDateString(), now()->addDays(15)->toDateString()]),
                'due_month' => $query->whereBetween('expected_docket_date', [now()->toDateString(), now()->addMonth()->toDateString()]),
                'custom' => $request->filled('docket_date')
                    ? $query->where('expected_docket_date', '<=', $request->docket_date)
                    : null,
                default => null,
            };
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Ordering
        $columns = ['loan_number', 'customer_name', 'bank_name', 'product_id', 'loan_amount', 'current_stage', 'status', 'updated_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 7);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'updated_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $loans = $query->skip($start)->take($length)->get();

        $data = $loans->map(function ($loan) use ($canEdit) {
            $viewIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
            $editIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';

            $stagesIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>';

            $actions = '<div class="d-flex gap-1">';
            $actions .= '<a href="'.route('loans.show', $loan).'" class="btn-accent-sm">'.$viewIcon.' View</a>';
            if (in_array($loan->status, ['active', 'on_hold'])) {
                $actions .= '<a href="'.route('loans.stages', $loan).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">'.$stagesIcon.' Stages</a>';
            }
            if ($canEdit && ! $loan->isBasicEditLocked()) {
                $actions .= '<a href="'.route('loans.edit', $loan).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#6b7280,#9ca3af);">'.$editIcon.' Edit</a>';
            }
            $actions .= '</div>';

            $ownerName = $loan->current_owner?->name ?? '—';
            $timeWithOwner = $loan->time_with_current_owner;

            // Get sanction/disbursement amounts from stage notes
            $amountInfo = $loan->formatted_amount;
            $sanctionAssignment = $loan->stageAssignments->where('stage_key', 'sanction')->first();
            $disbursementAssignment = $loan->stageAssignments->where('stage_key', 'disbursement')->first();
            if ($disbursementAssignment?->status === 'completed') {
                $disbNotes = $disbursementAssignment->getNotesData();
                if (! empty($disbNotes['disbursed_amount'])) {
                    $amountInfo .= '<br><small class="text-success fw-semibold">DIS-₹ '.number_format($disbNotes['disbursed_amount']).'</small>';
                }
            } elseif ($sanctionAssignment?->status === 'completed') {
                $sancNotes = $sanctionAssignment->getNotesData();
                if (! empty($sancNotes['sanctioned_amount'])) {
                    $amountInfo .= '<br><small class="text-primary fw-semibold">SC-₹ '.number_format($sancNotes['sanctioned_amount']).'</small>';
                }
            }

            $locationName = $loan->location ? ($loan->location->parent?->name ? $loan->location->parent->name.'/' : '').$loan->location->name : '';

            // Docket overdue check
            $isOverdue = false;
            $docketInfo = '';
            if ($loan->status === 'active') {
                $appNumberAssignment = $loan->stageAssignments->where('stage_key', 'app_number')->first();
                $docketAssignment = $loan->stageAssignments->where('stage_key', 'docket')->first();
                if ($appNumberAssignment?->status === 'completed' && $docketAssignment && $docketAssignment->status !== 'completed') {
                    $appNotes = $appNumberAssignment->getNotesData();
                    $sanctionAssignmentForDocket = $loan->stageAssignments->where('stage_key', 'sanction')->first();

                    $expectedDate = null;
                    if (! empty($appNotes['custom_docket_date'])) {
                        $expectedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $appNotes['custom_docket_date']);
                    } elseif (! empty($appNotes['docket_days_offset']) && $sanctionAssignmentForDocket?->completed_at) {
                        $expectedDate = $sanctionAssignmentForDocket->completed_at->copy()->addDays((int) $appNotes['docket_days_offset']);
                    }

                    if ($expectedDate) {
                        $isOverdue = now()->gt($expectedDate);
                        $docketInfo = $expectedDate->format('d M Y');
                    }
                }
            }

            return [
                'loan_number' => $loan->loan_number.($isOverdue ? '<br><small class="text-danger fw-bold" title="Docket expected by '.$docketInfo.'">⚠ Docket Overdue</small>' : ($docketInfo ? '<br><small class="text-muted" title="Expected docket date">📅 '.$docketInfo.'</small>' : '')),
                'customer_name' => $loan->customer_name,
                'bank_name' => $loan->bank_name ?? '—',
                'bank_product' => ($loan->bank_name ?? '—').'<br><small class="text-muted">'.($loan->product?->name ?? '—').'</small>'
                    .($locationName ? '<br><small class="location-info" style="font-size:0.65rem;">'.$locationName.'</small>' : ''),
                'location_name' => $locationName,
                'amount_info' => $amountInfo,
                'formatted_amount' => $loan->formatted_amount,
                'current_stage_name' => $loan->stage_badge_html,
                'owner_info' => $ownerName !== '—' ? $ownerName.'<br><small class="text-muted">'.$timeWithOwner.'</small>' : '—',
                'status_label' => '<span class="shf-badge shf-badge-'.$this->statusBadgeClass($loan->status).'">'.$loan->status_label.'</span>'
                    .(in_array($loan->status, ['on_hold', 'cancelled', 'rejected']) && ($loan->status_reason || $loan->rejection_reason)
                        ? '<br><small class="text-muted" title="'.e($loan->status_reason ?? $loan->rejection_reason).'">'.e(\Str::limit($loan->status_reason ?? $loan->rejection_reason, 40)).'</small>'
                        : ''),
                'created_at' => $loan->created_at?->format('d M Y').'<br><small class="text-muted">'.$loan->total_loan_time.'</small>',
                'actions_html' => $actions,
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
        if (! auth()->user()->canCreateLoans()) {
            abort(403, 'You do not have permission to create loans.');
        }

        $user = auth()->user();
        $banks = Bank::active()->orderBy('name')->get();

        // Filter branches: admin/super_admin see all, others see only their assigned branches
        $isAdminOrSuper = $user->hasAnyRole(['super_admin', 'admin']);
        $branches = $isAdminOrSuper
            ? Branch::active()->with('location.parent')->orderBy('name')->get()
            : $user->branches()->where('is_active', true)->with('location.parent')->orderBy('name')->get();

        $products = Product::active()->with(['bank', 'locations'])->orderBy('name')->get();
        $advisors = User::advisorEligible()->with(['branches', 'locations'])->orderBy('name')->get();

        // Build branch → location map and product → location map for JS filtering
        $branchLocationMap = $branches->mapWithKeys(fn ($b) => [
            $b->id => ['city_id' => $b->location_id, 'state_id' => $b->location?->parent_id],
        ]);
        $productLocationMap = $products->mapWithKeys(fn ($p) => [
            $p->id => $p->locations->pluck('id')->toArray(),
        ]);

        return view('loans.create', compact('banks', 'branches', 'products', 'advisors', 'branchLocationMap', 'productLocationMap'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->canCreateLoans()) {
            abort(403, 'You do not have permission to create loans.');
        }

        $validated = $request->validate(LoanValidationRules::upsert());

        // Convert date format for storage
        $validated['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['date_of_birth'])->toDateString();
        $validated['pan_number'] = strtoupper($validated['pan_number']);

        $loan = $this->conversionService->createDirectLoan($validated);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan #'.$loan->loan_number.' created successfully');
    }

    public function show(LoanDetail $loan)
    {
        $this->authorizeView($loan);

        $loan->load(['quotation', 'branch', 'bank', 'product', 'creator', 'advisor', 'location.parent']);
        $stages = app(\App\Services\LoanStageService::class)->getOrderedStages();

        return view('loans.show', compact('loan', 'stages'));
    }

    public function timeline(LoanDetail $loan)
    {
        $this->authorizeView($loan);

        $timeline = app(\App\Services\LoanTimelineService::class)->getTimeline($loan);

        return view('loans.timeline', compact('loan', 'timeline'));
    }

    public function edit(LoanDetail $loan)
    {
        $this->authorizeView($loan);
        $loan->loadMissing('stageAssignments');

        if ($loan->isBasicEditLocked()) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Loan details cannot be edited after Application Number has been completed.');
        }

        $banks = Bank::active()->orderBy('name')->get();
        $branches = Branch::active()->with('location.parent')->orderBy('name')->get();
        $products = Product::active()->with(['bank', 'locations'])->orderBy('name')->get();
        $advisors = User::whereHas('roles', fn ($q) => $q->whereNotIn('slug', ['super_admin', 'admin']))->where('is_active', true)->with(['branches', 'locations'])->orderBy('name')->get();

        return view('loans.edit', compact('loan', 'banks', 'branches', 'products', 'advisors'));
    }

    public function update(Request $request, LoanDetail $loan)
    {
        $this->authorizeView($loan);
        $loan->loadMissing('stageAssignments');

        if ($loan->isBasicEditLocked()) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Loan details cannot be edited after Application Number has been completed.');
        }

        $validated = $request->validate(LoanValidationRules::upsert());

        // Convert date format for storage
        $validated['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['date_of_birth'])->toDateString();
        $validated['pan_number'] = strtoupper($validated['pan_number']);

        // Track changed fields
        $changed = array_keys(array_diff_assoc($validated, $loan->only(array_keys($validated))));

        if ($validated['bank_id'] ?? null) {
            $validated['bank_name'] = Bank::find($validated['bank_id'])?->name;
        }

        $loan->update($validated);

        ActivityLog::log('edit_loan', $loan, [
            'loan_number' => $loan->loan_number,
            'changed_fields' => $changed,
        ]);

        return redirect()->route('loans.show', $loan)->with('success', 'Loan updated');
    }

    public function updateStatus(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate(LoanValidationRules::statusChange());

        $user = auth()->user();

        // Only BDH/Branch Manager/Admin/Super Admin can cancel or reactivate rejected loans
        if ($validated['status'] === 'cancelled' || ($validated['status'] === 'active' && $loan->status === 'rejected')) {
            if (! $user->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'bdh'])) {
                return response()->json(['error' => 'Only Branch Manager or BDH can perform this action'], 403);
            }
        }

        $oldStatus = $loan->status;
        $updateData = ['status' => $validated['status']];

        if (in_array($validated['status'], ['on_hold', 'cancelled'])) {
            $updateData['status_reason'] = $validated['reason'];
            $updateData['status_changed_at'] = now();
            $updateData['status_changed_by'] = auth()->id();
        } elseif ($validated['status'] === 'active') {
            $updateData['status_reason'] = null;
            $updateData['status_changed_at'] = now();
            $updateData['status_changed_by'] = auth()->id();

            // Reactivating from rejected — clear rejection fields and restore stages
            if ($oldStatus === 'rejected') {
                $updateData['rejected_at'] = null;
                $updateData['rejected_by'] = null;
                $updateData['rejected_stage'] = null;
                $updateData['rejection_reason'] = null;
                $updateData['is_sanctioned'] = false;

                // Restore rejected stages to their previous status
                $loan->stageAssignments()
                    ->where('status', 'rejected')
                    ->get()
                    ->each(function ($sa) {
                        $restoreTo = $sa->previous_status ?? 'pending';
                        $sa->update([
                            'status' => $restoreTo,
                            'previous_status' => null,
                            'completed_at' => null,
                            'completed_by' => null,
                        ]);
                    });

                // Recalculate progress
                app(\App\Services\LoanStageService::class)->recalculateProgress($loan);
            }
        }

        $loan->update($updateData);

        ActivityLog::log('change_loan_status', $loan, [
            'loan_number' => $loan->loan_number,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json(['success' => true, 'status' => $validated['status']]);
    }

    public function destroy(LoanDetail $loan): JsonResponse
    {
        // Clear loan reference on linked quotation so it can be re-converted
        Quotation::where('loan_id', $loan->id)->update(['loan_id' => null]);

        ActivityLog::log('delete_loan', null, [
            'loan_number' => $loan->loan_number,
            'customer_name' => $loan->customer_name,
        ]);

        $loan->delete();

        return response()->json(['success' => true, 'redirect' => route('loans.index')]);
    }

    private function authorizeView(LoanDetail $loan): void
    {
        $user = auth()->user();
        if ($user->hasPermission('view_all_loans')) {
            return;
        }
        if ($loan->created_by === $user->id || $loan->assigned_advisor === $user->id) {
            return;
        }
        if ($loan->stageAssignments()->where('assigned_to', $user->id)->exists()) {
            return;
        }
        // Branch managers and BDHs can view loans in their branches
        if (($user->hasRole('branch_manager') || $user->hasRole('bdh')) && $loan->branch_id) {
            if ($user->branches()->where('branches.id', $loan->branch_id)->exists()) {
                return;
            }
        }
        abort(403);
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'active' => 'blue',
            'completed' => 'green',
            'rejected' => 'orange',
            'cancelled' => 'gray',
            'on_hold' => 'orange',
            default => 'gray',
        };
    }
}
