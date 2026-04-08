<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
            $statsQuery = Quotation::query();
            if (! $user->hasPermission('view_all_quotations')) {
                $statsQuery->where('user_id', $user->id);
            }

            $stats = [
                'total' => (clone $statsQuery)->count(),
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
                'delete_quotations' => $user->hasPermission('delete_quotations'),
            ];
        }

        // Loan stats + my tasks (if user has loan access)
        $loanStats = null;
        $myTasks = collect();
        $recentLoans = collect();

        if ($user->hasPermission('view_loans') || $user->task_role) {
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

        if ($myTasks > 0) {
            $defaultTab = 'tasks';
        } elseif ($recentLoans > 0) {
            $defaultTab = 'loans';
        } elseif ($canViewQuotations) {
            $defaultTab = 'quotations';
        } else {
            $defaultTab = 'tasks';
        }

        return view('dashboard', compact(
            'stats', 'users', 'permissions',
            'loanStats', 'defaultTab', 'canViewQuotations'
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
        $canDelete = $user->hasPermission('delete_quotations');

        // Base query
        $query = Quotation::with(['user', 'banks', 'loan', 'location.parent']);

        // Permission-based scoping: staff sees own only
        if (! $canViewAll) {
            $query->where('user_id', $user->id);
        }

        // Total records (before any filtering)
        $recordsTotal = (clone $query)->count();

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

        $data = $quotations->map(function ($q) use ($canViewAll, $canDownload, $canDelete, $canConvert) {
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
            ->with(['loan.bank', 'loan.branch', 'loan.location.parent']);

        $recordsTotal = (clone $query)->count();

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

        // Cache stage names to avoid N+1
        $stageKeys = $tasks->pluck('stage_key')->unique();
        $stageNames = \App\Models\Stage::whereIn('stage_key', $stageKeys)
            ->pluck('stage_name_en', 'stage_key');

        $data = $tasks->map(function ($task) use ($stageNames) {
            $loan = $task->loan;
            $locationName = $loan->location ? ($loan->location->parent?->name ? $loan->location->parent->name.'/' : '').$loan->location->name : '';

            return [
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer_name,
                'bank_name' => ($loan->bank?->name ?? $loan->bank_name)
                    .($locationName ? '<br><small class="text-info" style="font-size:0.7rem;">'.$locationName.'</small>' : ''),
                'stage_name' => $stageNames[$task->stage_key] ?? ucwords(str_replace('_', ' ', $task->stage_key)),
                'status_label' => '<span class="shf-badge '.($task->status === 'in_progress' ? 'shf-badge-blue' : 'shf-badge-gray').'">'
                    .($task->status === 'in_progress' ? 'In Progress' : 'Pending').'</span>',
                'assigned_at' => $task->updated_at->diffForHumans(),
                'actions_html' => '<a href="'.route('loans.stages', $loan).'" class="btn-accent-sm"><svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> View</a>',
                'location_name' => $locationName,
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
            ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
            ->with(['bank', 'creator', 'location.parent']);

        $recordsTotal = (clone $query)->count();

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

        $stageKeys = $loans->pluck('current_stage')->unique()->filter();
        $stageNames = \App\Models\Stage::whereIn('stage_key', $stageKeys)
            ->pluck('stage_name_en', 'stage_key');

        $statusColors = ['primary' => 'blue', 'success' => 'green', 'danger' => 'red', 'warning' => 'orange', 'secondary' => 'gray'];

        $data = $loans->map(function ($loan) use ($stageNames, $statusColors) {
            $locationName = $loan->location ? ($loan->location->parent?->name ? $loan->location->parent->name.'/' : '').$loan->location->name : '';
            $sl = LoanDetail::STATUS_LABELS[$loan->status] ?? null;
            $badgeColor = $statusColors[$sl['color'] ?? 'secondary'] ?? 'gray';

            return [
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer_name,
                'bank_name' => ($loan->bank?->name ?? $loan->bank_name)
                    .($locationName ? '<br><small class="text-info" style="font-size:0.7rem;">'.$locationName.'</small>' : ''),
                'formatted_amount' => $loan->formatted_amount,
                'stage_name' => '<small>'.($stageNames[$loan->current_stage] ?? ucwords(str_replace('_', ' ', $loan->current_stage ?? '—'))).'</small>',
                'status_label' => '<span class="shf-badge shf-badge-'.$badgeColor.'">'.($sl['label'] ?? ucfirst($loan->status)).'</span>',
                'created_at' => $loan->created_at?->format('d M Y'),
                'actions_html' => '<a href="'.route('loans.show', $loan).'" class="btn-accent-sm"><svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> View</a>',
                'location_name' => $locationName,
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
        $actionTypes = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity-log', compact('users', 'actionTypes'));
    }

    public function activityLogData(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user');

        $recordsTotal = (clone $query)->count();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action_type')) {
            $query->where('action', $request->action_type);
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
                $q->where('action', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $query)->count();

        $columns = ['created_at', 'user_id', 'action', 'subject_type', 'created_at'];
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
