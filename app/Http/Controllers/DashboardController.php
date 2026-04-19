<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GeneralTask;
use App\Models\LoanDetail;
use App\Models\Quotation;
use App\Models\StageAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has any quotation-related permission
        $canViewQuotations = $user->hasPermission('create_quotation')
            || $user->hasPermission('view_own_quotations')
            || $user->hasPermission('view_all_quotations');

        // Quotation stats (only if user has quotation access)
        $stats = null;
        $users = [];
        $permissions = [];

        if ($canViewQuotations) {
            $statsQuery = Quotation::visibleTo($user);

            $stats = [
                'total' => (clone $statsQuery)->count(),
                'not_converted' => (clone $statsQuery)->whereNull('loan_id')->count(),
                'today' => (clone $statsQuery)->whereDate('created_at', today())->count(),
                'this_month' => (clone $statsQuery)->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
            ];

            if ($user->hasPermission('view_all_quotations')) {
                $users = User::select('id', 'name')->orderBy('name')->get();
            }

            $permissions = [
                'view_all' => $user->hasPermission('view_all_quotations'),
                'download_pdf' => $user->hasPermission('download_pdf'),
                'download_pdf_branded' => $user->hasPermission('download_pdf_branded'),
                'download_pdf_plain' => $user->hasPermission('download_pdf_plain'),
                'delete_quotations' => $user->hasPermission('delete_quotations'),
            ];
        }

        // Loan stats + my tasks (if user has loan access)
        $loanStats = null;
        $myTasks = 0;
        $recentLoans = 0;

        if ($user->hasPermission('view_loans') || $user->hasWorkflowRole()) {
            $loanBase = LoanDetail::visibleTo($user);
            $loanStats = [
                'active' => (clone $loanBase)->where('status', 'active')->count(),
                'my_tasks' => StageAssignment::where('assigned_to', $user->id)
                    ->whereIn('status', ['pending', 'in_progress'])->count(),
                'completed_month' => (clone $loanBase)->where('status', 'completed')
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)->count(),
            ];

            $myTasks = StageAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            $recentLoans = LoanDetail::visibleTo($user)
                ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
                ->count();
        }

        // Active users for task creation modal
        $activeUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Personal tasks stats (general tasks, not loan stage tasks)
        $personalTaskStats = [
            'pending' => GeneralTask::visibleTo($user)->pending()->count(),
            'overdue' => GeneralTask::visibleTo($user)->pending()
                ->whereNotNull('due_date')->where('due_date', '<', today())->count(),
        ];

        // DVR stats
        $canViewDvr = $user->hasPermission('view_dvr');
        $dvrStats = null;
        $dvrConfig = [];
        if ($canViewDvr) {
            $dvrBase = \App\Models\DailyVisitReport::visibleTo($user);
            $dvrStats = [
                'today' => (clone $dvrBase)->where('visit_date', today())->count(),
                'pending_follow_ups' => (clone $dvrBase)->pendingFollowUps()->count(),
                'overdue_follow_ups' => (clone $dvrBase)->overdueFollowUps()->count(),
            ];
        }
        if ($user->hasPermission('create_dvr')) {
            $config = app(\App\Services\ConfigService::class)->load();
            $dvrConfig = [
                'contactTypes' => $config['dvrContactTypes'] ?? [],
                'purposes' => $config['dvrPurposes'] ?? [],
            ];
        }

        // Default tab: show what needs attention right now
        // dvr overdue → overdue personal → loan stage tasks → pending personal → dvr pending → loans → quotations → fallback
        if ($dvrStats && $dvrStats['overdue_follow_ups'] > 0) {
            $defaultTab = 'dvr';
        } elseif ($personalTaskStats['overdue'] > 0) {
            $defaultTab = 'personal-tasks';
        } elseif ($myTasks > 0) {
            $defaultTab = 'tasks';
        } elseif ($personalTaskStats['pending'] > 0) {
            $defaultTab = 'personal-tasks';
        } elseif ($recentLoans > 0) {
            $defaultTab = 'loans';
        } elseif ($canViewQuotations && $stats && $stats['not_converted'] > 0) {
            $defaultTab = 'quotations';
        } else {
            $defaultTab = 'personal-tasks';
        }

        return view('dashboard', compact(
            'stats', 'users', 'permissions',
            'loanStats', 'defaultTab', 'canViewQuotations',
            'personalTaskStats', 'activeUsers', 'dvrConfig',
            'canViewDvr', 'dvrStats'
        ));
    }

    /**
     * Server-side DataTables AJAX endpoint for quotations.
     */
    public function quotationData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $canViewAll = $user->hasPermission('view_all_quotations');
        $canDownload = $user->hasPermission('download_pdf');
        $canDownloadBranded = $user->hasPermission('download_pdf_branded');
        $canDownloadPlain = $user->hasPermission('download_pdf_plain');
        $canDelete = $user->hasPermission('delete_quotations');
        $canHold = $user->hasPermission('hold_quotation');
        $canCancel = $user->hasPermission('cancel_quotation');
        $canResume = $user->hasPermission('resume_quotation');

        // Base query — scoped by permission/role (view_all, own, or branch)
        $query = Quotation::visibleTo($user)
            ->with(['user', 'banks', 'loan', 'location.parent']);

        // Total records (before any filtering)
        $recordsTotal = (clone $query)->count();

        // Quotation status filter (active / on_hold / cancelled / not_cancelled / all)
        $status = $request->input('status', 'not_cancelled');
        if ($status === 'active') {
            $query->where('status', Quotation::STATUS_ACTIVE);
        } elseif ($status === 'on_hold') {
            $query->where('status', Quotation::STATUS_ON_HOLD);
        } elseif ($status === 'cancelled') {
            $query->where('status', Quotation::STATUS_CANCELLED);
        } elseif ($status === 'not_cancelled') {
            $query->where('status', '!=', Quotation::STATUS_CANCELLED);
        }
        // 'all' → no status filter

        // Loan status filter
        $loanStatus = $request->input('loan_status', 'not_converted');
        if ($loanStatus === 'not_converted') {
            // Default: only quotations not yet converted to a loan
            $query->whereNull('loan_id');
        } elseif ($loanStatus === 'active') {
            // Converted with active loan
            $query->whereHas('loan', fn ($q) => $q->where('status', 'active'));
        } elseif ($loanStatus === 'completed') {
            $query->whereHas('loan', fn ($q) => $q->where('status', 'completed'));
        } elseif ($loanStatus === 'rejected') {
            $query->whereHas('loan', fn ($q) => $q->where('status', 'rejected'));
        } elseif ($loanStatus === 'converted') {
            // All converted (any loan status)
            $query->whereNotNull('loan_id');
        }
        // 'all' → no filter

        // Custom filters
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('created_by') && $canViewAll) {
            $query->where('user_id', $request->created_by);
        }

        // DataTables search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('pdf_filename', 'like', "%{$search}%");
            });
        }

        // Filtered count (after search + custom filters, before pagination)
        $recordsFiltered = (clone $query)->count();

        // Column ordering
        $columns = ['id', 'customer_name', 'customer_type', 'loan_amount', 'banks'];
        if ($canViewAll) {
            $columns[] = 'created_by';
        }
        $columns[] = 'created_at';

        $orderColumnIndex = (int) $request->input('order.0.column', count($columns) - 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'updated_at';

        // Map virtual columns to actual DB columns
        $orderableMap = [
            'id' => 'id',
            'customer_name' => 'customer_name',
            'customer_type' => 'customer_type',
            'loan_amount' => 'loan_amount',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'created_by' => 'user_id',
        ];

        if (isset($orderableMap[$orderColumn])) {
            $query->orderBy($orderableMap[$orderColumn], $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $quotations = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $canConvert = $user->hasPermission('convert_to_loan');

        $data = $quotations->map(function ($q) use ($canViewAll, $canDownload, $canDownloadBranded, $canDownloadPlain, $canDelete, $canConvert, $canHold, $canCancel, $canResume) {
            $bankNames = $q->banks ? $q->banks->pluck('bank_name')->toArray() : [];

            $typeLabels = [
                'proprietor' => 'Proprietor',
                'partnership_llp' => 'Partnership/LLP',
                'pvt_ltd' => 'PVT LTD',
                'salaried' => 'Salaried',
                'all' => 'All Types',
            ];

            $typeBadgeClass = match ($q->customer_type) {
                'proprietor' => 'shf-badge-green',
                'partnership_llp' => 'shf-badge-blue',
                'pvt_ltd' => 'shf-badge-orange',
                'salaried' => 'shf-badge-purple',
                default => 'shf-badge-gray',
            };

            return [
                'id' => $q->id,
                'customer_name' => $q->customer_name,
                'customer_type' => $q->customer_type,
                'type_label' => $typeLabels[$q->customer_type] ?? ucfirst($q->customer_type),
                'type_badge_class' => $typeBadgeClass,
                'loan_amount' => $q->loan_amount,
                'formatted_amount' => $q->formatted_amount,
                'banks' => $bankNames,
                'created_by' => $canViewAll ? ($q->user?->name ?? '—') : null,
                'date' => $q->created_at ? $q->created_at->format('d M Y, h:i A') : '—',
                'date_raw' => $q->created_at?->toISOString(),
                'show_url' => route('quotations.show', $q->id),
                'download_url' => $canDownload
                    ? route('quotations.download', $q->id)
                    : null,
                'download_branded_url' => $canDownloadBranded
                    ? route('quotations.download', [$q->id, 'branded' => 1])
                    : null,
                'download_plain_url' => $canDownloadPlain
                    ? route('quotations.download', [$q->id, 'branded' => 0])
                    : null,
                'delete_url' => $canDelete
                    ? route('quotations.destroy', $q->id)
                    : null,
                'convert_url' => ($canConvert && ! $q->loan_id)
                    ? route('quotations.convert', $q->id)
                    : null,
                'loan_url' => $q->loan_id
                    ? route('loans.show', $q->loan_id)
                    : null,
                'is_converted' => (bool) $q->loan_id,
                'loan_status' => $q->loan?->status,
                'loan_status_label' => $q->loan?->status_label,
                'status' => $q->status,
                'status_html' => $q->status_badge_html,
                'is_on_hold' => $q->is_on_hold,
                'is_cancelled' => $q->is_cancelled,
                'hold_follow_up_date' => $q->hold_follow_up_date?->format('d M Y'),
                'hold_reason_label' => $q->hold_reason_label,
                'cancel_reason_label' => $q->cancel_reason_label,
                'hold_url' => ($canHold && $q->status === Quotation::STATUS_ACTIVE && ! $q->loan_id)
                    ? route('quotations.hold', $q->id)
                    : null,
                'cancel_url' => ($canCancel && $q->status !== Quotation::STATUS_CANCELLED && ! $q->loan_id)
                    ? route('quotations.cancel', $q->id)
                    : null,
                'resume_url' => ($canResume && $q->status === Quotation::STATUS_ON_HOLD)
                    ? route('quotations.resume', $q->id)
                    : null,
                'location_name' => $q->location ? ($q->location->parent?->name ? $q->location->parent->name.'/' : '').$q->location->name : '',
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    /**
     * AJAX endpoint for My Tasks on dashboard.
     */
    public function taskData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = StageAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['loan.bank', 'loan.product', 'loan.branch', 'loan.location.parent', 'loan.stageAssignments.assignee', 'stage']);

        $recordsTotal = (clone $query)->count();

        // Filters
        if ($request->filled('stage')) {
            $query->where('stage_key', $request->stage);
        }
        if ($request->filled('task_status')) {
            $query->where('status', $request->task_status);
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->whereHas('loan', function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Order
        $query->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc');

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $tasks = $query->skip($start)->take($length)->get();

        $stagesIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>';

        $data = $tasks->map(function ($task) use ($stagesIcon) {
            $loan = $task->loan;
            $locationName = $loan->location ? $loan->location->name : '';
            $stageName = $task->stage?->stage_name_en ?? ucwords(str_replace('_', ' ', $task->stage_key));
            $cssClass = LoanDetail::stageBadgeClass($task->stage_key);

            $typeLabel = LoanDetail::CUSTOMER_TYPE_LABELS[$loan->customer_type] ?? ucfirst($loan->customer_type ?? '');

            return [
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer_name
                    .($typeLabel ? '<br><small class="text-muted">'.$typeLabel.'</small>' : ''),
                'customer_name_plain' => $loan->customer_name,
                'bank_name' => ($loan->bank?->name ?? $loan->bank_name)
                    .($loan->product ? '<br><small class="text-muted">'.$loan->product->name.'</small>' : '')
                    .($locationName ? '<br><small class="location-info" style="font-size:0.65rem;">'.$locationName.'</small>' : ''),
                'bank_name_plain' => $loan->bank?->name ?? $loan->bank_name ?? '—',
                'formatted_amount' => $loan->formatted_amount,
                'stage_name' => '<span class="shf-badge '.$cssClass.' shf-text-2xs">'.$stageName.'</span>',
                'status_label' => '<span class="shf-badge '.($task->status === 'in_progress' ? 'shf-badge-blue' : 'shf-badge-gray').'">'
                    .($task->status === 'in_progress' ? 'In Progress' : 'Pending').'</span>',
                'assigned_at' => $task->updated_at->diffForHumans(),
                'actions_html' => '<a href="'.route('loans.stages', $loan).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">'.$stagesIcon.' Stages</a>',
                'location_name' => $locationName,
                'owner_info' => $loan->current_owner?->name ?? '—',
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    /**
     * AJAX endpoint for Recent Loans on dashboard.
     */
    public function dashboardLoanData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = LoanDetail::visibleTo($user)
            ->with(['bank', 'product', 'creator', 'advisor', 'location.parent', 'stageAssignments.assignee.roles']);

        $recordsTotal = (clone $query)->count();

        // Filters (same as loan listing)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: exclude closed loans
            $query->whereNotIn('status', ['completed', 'rejected', 'cancelled']);
        }
        if ($request->filled('stage')) {
            $query->where('current_stage', $request->stage);
        }
        if ($request->filled('bank')) {
            $query->where('bank_id', $request->bank);
        }
        if ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        }
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }
        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('stageAssignments', function ($q) use ($role) {
                $q->whereColumn('stage_key', 'loan_details.current_stage')
                    ->whereHas('assignee', fn ($uq) => $uq->whereHas('roles', fn ($rq) => $rq->where('slug', $role)));
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Order
        $columns = ['loan_number', 'customer_name', 'bank_name', 'loan_amount', 'current_stage', 'status', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 6);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $loans = $query->skip($start)->take($length)->get();

        $statusBadgeClass = fn ($s) => match ($s) {
            'active' => 'blue', 'completed' => 'green', 'rejected' => 'red', 'on_hold' => 'orange', default => 'gray'
        };
        $viewIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        $stagesIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>';

        $data = $loans->map(function ($loan) use ($statusBadgeClass, $viewIcon, $stagesIcon) {
            $locationName = $loan->location ? $loan->location->name : '';
            $sl = LoanDetail::STATUS_LABELS[$loan->status] ?? null;

            $actions = '<div class="d-flex gap-1">';
            $actions .= '<a href="'.route('loans.show', $loan).'" class="btn-accent-sm">'.$viewIcon.' View</a>';
            if (in_array($loan->status, ['active', 'on_hold'])) {
                $actions .= '<a href="'.route('loans.stages', $loan).'" class="btn-accent-sm" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">'.$stagesIcon.' Stages</a>';
            }
            $actions .= '</div>';

            return [
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer_name,
                'bank_name' => ($loan->bank?->name ?? $loan->bank_name)
                    .($loan->product ? '<br><small class="text-muted">'.$loan->product->name.'</small>' : '')
                    .($locationName ? '<br><small class="location-info" style="font-size:0.65rem;">'.$locationName.'</small>' : ''),
                'bank_name_plain' => $loan->bank?->name ?? $loan->bank_name ?? '—',
                'formatted_amount' => $loan->formatted_amount,
                'stage_name' => $loan->stage_badge_html,
                'status_label' => '<span class="shf-badge shf-badge-'.$statusBadgeClass($loan->status).'">'.($sl['label'] ?? ucfirst($loan->status)).'</span>',
                'created_at' => $loan->created_at?->format('d M Y'),
                'actions_html' => $actions,
                'location_name' => $locationName,
                'owner_info' => $loan->current_owner?->name ?? '—',
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    /**
     * Server-side DataTables AJAX endpoint for DVR on dashboard.
     */
    public function dvrData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = \App\Models\DailyVisitReport::visibleTo($user)
            ->with(['user', 'loan', 'branch']);

        $recordsTotal = (clone $query)->count();

        // Default: show pending follow-ups + today's visits (exclude completed)
        $filter = $request->input('dvr_filter', 'pending');
        if ($filter === 'pending') {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('follow_up_needed', true)->where('is_follow_up_done', false);
                })->orWhere('visit_date', today());
            });
        } elseif ($filter === 'overdue') {
            $query->overdueFollowUps();
        } elseif ($filter === 'today') {
            $query->where('visit_date', today());
        } elseif ($filter === 'active') {
            // All except completed follow-ups
            $query->where(function ($q) {
                $q->where('follow_up_needed', false)
                    ->orWhere(function ($q2) {
                        $q2->where('follow_up_needed', true)->where('is_follow_up_done', false);
                    });
            });
        }
        // 'all' = no filter (includes completed)

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Primary sort: pending first (is_follow_up_done=0 before 1), then by user column
        $query->orderBy('is_follow_up_done', 'asc');

        $columns = ['visit_date', 'contact_name', 'contact_type', 'follow_up_date', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'visit_date';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $visits = $query->skip($start)->take($length)->get();

        $config = app(\App\Services\ConfigService::class)->load();
        $contactTypeLabels = collect($config['dvrContactTypes'] ?? [])->pluck('label_en', 'key')->toArray();
        $purposeLabels = collect($config['dvrPurposes'] ?? [])->pluck('label_en', 'key')->toArray();

        $data = $visits->map(function (\App\Models\DailyVisitReport $visit) use ($contactTypeLabels, $purposeLabels) {
            // Follow-up urgency (same as DVR index)
            $followUpHtml = '—';
            $followUpUrgency = null;
            if ($visit->follow_up_needed) {
                if ($visit->is_follow_up_done) {
                    $followUpHtml = '<span class="shf-badge shf-badge-green shf-text-2xs">Done</span>';
                } elseif ($visit->follow_up_date) {
                    $daysUntil = (int) today()->diffInDays($visit->follow_up_date, false);
                    $dateStr = $visit->follow_up_date->format('d M Y');
                    if ($daysUntil < 0) {
                        $overdueDays = abs($daysUntil);
                        $followUpUrgency = 'overdue';
                        $followUpHtml = $dateStr.'<br><span class="shf-badge shf-badge-red shf-text-2xs">Overdue by '.$overdueDays.' '.($overdueDays === 1 ? 'day' : 'days').'</span>';
                    } elseif ($daysUntil === 0) {
                        $followUpUrgency = 'due_today';
                        $followUpHtml = $dateStr.'<br><span class="shf-badge shf-badge-orange shf-text-2xs">Due Today</span>';
                    } elseif ($daysUntil === 1) {
                        $followUpUrgency = 'due_tomorrow';
                        $followUpHtml = $dateStr.'<br><span class="shf-badge shf-badge-orange shf-text-2xs">Due Tomorrow</span>';
                    } elseif ($daysUntil <= 3) {
                        $followUpUrgency = 'due_soon';
                        $followUpHtml = $dateStr.'<br><span class="shf-badge shf-badge-blue shf-text-2xs">Due in '.$daysUntil.' days</span>';
                    } else {
                        $followUpHtml = $dateStr.'<br><span class="shf-badge shf-badge-gray shf-text-2xs">Pending</span>';
                    }
                } else {
                    $followUpHtml = '<span class="shf-badge shf-badge-orange shf-text-2xs">Pending</span>';
                }
            }

            return [
                'id' => $visit->id,
                'visit_date' => $visit->visit_date->format('d M Y'),
                'contact_name' => e($visit->contact_name),
                'contact_phone' => e($visit->contact_phone ?? ''),
                'contact_type' => $contactTypeLabels[$visit->contact_type] ?? $visit->contact_type,
                'purpose' => $purposeLabels[$visit->purpose] ?? $visit->purpose,
                'follow_up_html' => $followUpHtml,
                'follow_up_urgency' => $followUpUrgency,
                'follow_up_needed' => $visit->follow_up_needed,
                'is_follow_up_done' => $visit->is_follow_up_done,
                'user_name' => e($visit->user?->name ?? '—'),
                'show_url' => route('dvr.show', $visit),
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function activityLog()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $actionTypes = ActivityLog::select('description')->distinct()->orderBy('description')->pluck('description');

        return view('activity-log', compact('users', 'actionTypes'));
    }

    public function activityLogData(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user');

        $recordsTotal = (clone $query)->count();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }
        if ($request->filled('action_type')) {
            $query->where('description', $request->action_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $query)->count();

        $columns = ['created_at', 'causer_id', 'description', 'subject_type', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $logs = $query->skip($start)->take($length)->get();

        $actionBadges = [
            'login' => 'shf-badge-green', 'logout' => 'shf-badge-gray',
            'create_quotation' => 'shf-badge-blue', 'delete_quotation' => 'shf-badge-red',
            'update_settings' => 'shf-badge-orange', 'create_user' => 'shf-badge-blue',
            'update_user' => 'shf-badge-blue', 'delete_user' => 'shf-badge-red',
            'update_permissions' => 'shf-badge-orange', 'create_loan' => 'shf-badge-blue',
            'save_product_stages' => 'shf-badge-orange', 'impersonate_start' => 'shf-badge-orange',
            'impersonate_end' => 'shf-badge-gray',
        ];

        $data = $logs->map(function ($log) use ($actionBadges) {
            $badge = $actionBadges[$log->action] ?? 'shf-badge-gray';
            $actionLabel = ucwords(str_replace('_', ' ', $log->action));

            $subject = $log->subject_type
                ? class_basename($log->subject_type).' #'.$log->subject_id
                : '—';

            $details = '—';
            if ($log->properties) {
                $props = $log->properties;
                if (isset($props['customer_name'])) {
                    $details = e($props['customer_name']);
                    if (isset($props['loan_amount'])) {
                        $details .= ' — ₹ '.number_format($props['loan_amount']);
                    }
                } elseif (isset($props['name'])) {
                    $details = e($props['name']);
                } elseif (isset($props['section'])) {
                    $details = 'Section: '.e($props['section']);
                }
            }

            return [
                'date_html' => '<span style="white-space:nowrap;color:#6b7280;">'.$log->created_at->format('d M Y')
                    .'<span class="d-block small" style="color:#9ca3af;">'.$log->created_at->format('h:i A').'</span></span>',
                'user_name' => $log->user?->name ?? 'System',
                'action_html' => '<span class="shf-badge '.$badge.'">'.$actionLabel.'</span>',
                'subject' => $subject,
                'details' => $details,
                'ip_address' => $log->ip_address ?? '—',
                // For mobile
                'action' => $log->action,
                'action_label' => $actionLabel,
                'action_badge' => $badge,
                'date_short' => $log->created_at->format('d M Y, h:i A'),
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }
}
