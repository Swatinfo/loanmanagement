<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\GeneralTask;
use App\Models\GeneralTaskComment;
use App\Models\LoanDetail;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralTaskController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $canViewAll = $user->hasPermission('view_all_tasks');
        $isBdh = $user->hasRole('bdh');
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('general-tasks.index', compact('canViewAll', 'isBdh', 'users'));
    }

    /**
     * AJAX DataTables endpoint for task listing.
     */
    public function taskData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = GeneralTask::visibleTo($user)
            ->with(['creator', 'assignee', 'loan']);

        $recordsTotal = (clone $query)->count();

        // View filter: my_tasks_and_assigned, my_tasks, assigned_to_me, my_branch, all
        $view = $request->input('view', 'my_tasks_and_assigned');
        if ($view === 'my_tasks_and_assigned') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        } elseif ($view === 'my_tasks') {
            $query->where('created_by', $user->id);
        } elseif ($view === 'assigned_to_me') {
            $query->where('assigned_to', $user->id);
        } elseif ($view === 'my_branch' && $user->hasRole('bdh')) {
            $branchUserIds = \Illuminate\Support\Facades\DB::table('user_branches')
                ->whereIn('branch_id', $user->branches()->pluck('branches.id'))
                ->pluck('user_id')
                ->unique()
                ->toArray();
            $query->where(function ($q) use ($branchUserIds) {
                $q->whereIn('created_by', $branchUserIds)
                    ->orWhereIn('assigned_to', $branchUserIds);
            });
        }
        // 'all' = no extra filter (already scoped by visibleTo)

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereIn('status', [GeneralTask::STATUS_PENDING, GeneralTask::STATUS_IN_PROGRESS]);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('assignee', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('loan', fn ($l) => $l->where('loan_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Order
        $columns = ['title', 'assigned_to', 'priority', 'due_date', 'status', 'completed_at', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $tasks = $query->skip($start)->take($length)->get();

        $data = $tasks->map(function (GeneralTask $task) use ($user) {
            $loanInfo = '';
            if ($task->loan) {
                $loanInfo = '<a href="'.route('loans.show', $task->loan_detail_id).'" class="text-decoration-none">'
                    .'<span class="shf-badge shf-badge-blue shf-text-2xs">#'
                    .e($task->loan->loan_number).'</span></a>'
                    .'<br><small class="text-muted">'.e($task->loan->customer_name).'</small>';
            }

            $dueDateHtml = '—';
            $dueUrgency = null;
            if ($task->due_date && ! in_array($task->status, [GeneralTask::STATUS_COMPLETED, GeneralTask::STATUS_CANCELLED])) {
                $daysUntil = (int) today()->diffInDays($task->due_date, false);
                $dateStr = $task->due_date->format('d M Y');
                if ($daysUntil < 0) {
                    $overdueDays = abs($daysUntil);
                    $dueUrgency = 'overdue';
                    $dueDateHtml = $dateStr.'<br><span class="shf-badge shf-badge-red shf-text-2xs">Overdue by '.$overdueDays.' '.($overdueDays === 1 ? 'day' : 'days').'</span>';
                } elseif ($daysUntil === 0) {
                    $dueUrgency = 'due_today';
                    $dueDateHtml = $dateStr.'<br><span class="shf-badge shf-badge-orange shf-text-2xs">Due Today</span>';
                } elseif ($daysUntil === 1) {
                    $dueUrgency = 'due_tomorrow';
                    $dueDateHtml = $dateStr.'<br><span class="shf-badge shf-badge-orange shf-text-2xs">Due Tomorrow</span>';
                } elseif ($daysUntil === 2) {
                    $dueUrgency = 'due_soon';
                    $dueDateHtml = $dateStr.'<br><span class="shf-badge shf-badge-blue shf-text-2xs">Due in 2 days</span>';
                } else {
                    $dueDateHtml = $dateStr;
                }
            } elseif ($task->due_date) {
                $dueDateHtml = $task->due_date->format('d M Y');
            }

            return [
                'id' => $task->id,
                'title' => e($task->title),
                'description' => $task->description ? e(\Illuminate\Support\Str::limit($task->description, 80)) : '',
                'creator_name' => e($task->creator?->name ?? '—'),
                'assignee_name' => e($task->assignee?->name ?? '—'),
                'is_self_task' => ! $task->assigned_to || $task->assigned_to === $task->created_by,
                'loan_info' => $loanInfo,
                'loan_number' => $task->loan?->loan_number ?? '',
                'priority' => $task->priority,
                'priority_html' => $task->priority_badge_html,
                'due_date_html' => $dueDateHtml,
                'due_date_raw' => $task->due_date?->toDateString(),
                'due_urgency' => $dueUrgency,
                'status' => $task->status,
                'status_html' => $task->status_badge_html,
                'completed_at' => $task->completed_at?->format('d M Y'),
                'created_at' => $task->created_at?->format('d M Y'),
                'show_url' => route('general-tasks.show', $task),
                'can_edit' => $task->isEditableBy($user),
                'can_delete' => $task->isDeletableBy($user),
                'edit_data' => $task->isEditableBy($user) ? [
                    'update_url' => route('general-tasks.update', $task),
                    'title' => $task->title,
                    'description' => $task->description,
                    'assigned_to' => $task->assigned_to,
                    'priority' => $task->priority,
                    'due_date_formatted' => $task->due_date?->format('d/m/Y'),
                    'loan_detail_id' => $task->loan_detail_id,
                    'loan_label' => $task->loan ? '#'.$task->loan->loan_number.' - '.$task->loan->customer_name : null,
                ] : null,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
            'loan_detail_id' => 'nullable|exists:loan_details,id',
            'priority' => 'required|in:'.implode(',', GeneralTask::PRIORITIES),
            'due_date' => 'required|date_format:d/m/Y',
        ]);

        $user = Auth::user();
        $validated['created_by'] = $user->id;
        $validated['due_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['due_date'])->toDateString();

        // If no assignee specified, it's a self-task
        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = null;
        }

        $task = GeneralTask::create($validated);

        // Notify assignee if different from creator
        if ($task->assigned_to && $task->assigned_to !== $user->id) {
            $loanSuffix = $task->loan ? " for Loan of ({$task->loan->customer_name}) #{$task->loan->loan_number}" : '';

            $this->notificationService->notify(
                $task->assigned_to,
                'Task Assigned',
                "You have been assigned a task: \"{$task->title}\"{$loanSuffix}",
                'task',
                null,
                null,
                route('general-tasks.show', $task),
            );
        }

        ActivityLog::log('create_task', $task, [
            'assigned_to' => $task->assignee?->name,
        ]);

        return redirect()->route('general-tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(GeneralTask $task)
    {
        $user = Auth::user();
        if (! $task->isVisibleTo($user)) {
            abort(403);
        }

        $task->load(['creator', 'assignee', 'loan', 'comments.user']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Data for edit modal
        $loanLabel = '';
        if ($task->loan) {
            $loanLabel = '#'.$task->loan->loan_number;
            if ($task->loan->application_number) {
                $loanLabel .= ' / App: '.$task->loan->application_number;
            }
            $loanLabel .= ' — '.$task->loan->customer_name;
            if ($task->loan->bank_name) {
                $loanLabel .= ' ('.$task->loan->bank_name.')';
            }
        }

        $taskEditData = [
            'title' => $task->title,
            'description' => $task->description,
            'assigned_to' => $task->assigned_to,
            'priority' => $task->priority,
            'due_date_formatted' => $task->due_date?->format('d/m/Y'),
            'loan_detail_id' => $task->loan_detail_id,
            'loan_label' => $loanLabel,
        ];

        return view('general-tasks.show', compact('task', 'users', 'taskEditData'));
    }

    public function update(Request $request, GeneralTask $task)
    {
        $user = Auth::user();
        if (! $task->isEditableBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
            'loan_detail_id' => 'nullable|exists:loan_details,id',
            'priority' => 'required|in:'.implode(',', GeneralTask::PRIORITIES),
            'due_date' => 'required|date_format:d/m/Y',
        ]);

        $validated['due_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['due_date'])->toDateString();

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = null;
        }

        $oldAssignee = $task->assigned_to;
        $task->update($validated);

        // Notify new assignee if changed
        if ($task->assigned_to && $task->assigned_to !== $oldAssignee && $task->assigned_to !== $user->id) {
            $loanSuffix = $task->loan ? " for Loan of ({$task->loan->customer_name}) #{$task->loan->loan_number}" : '';

            $this->notificationService->notify(
                $task->assigned_to,
                'Task Assigned',
                "You have been assigned a task: \"{$task->title}\"{$loanSuffix}",
                'task',
                null,
                null,
                route('general-tasks.show', $task),
            );
        }

        ActivityLog::log('update_task', $task);

        return redirect()->back()
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(GeneralTask $task)
    {
        $user = Auth::user();
        if (! $task->isDeletableBy($user)) {
            abort(403);
        }

        $title = $task->title;
        $task->delete();

        ActivityLog::log('delete_task', null, [
            'title' => $title,
        ]);

        return redirect()->route('general-tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * AJAX status update.
     */
    public function updateStatus(Request $request, GeneralTask $task): JsonResponse
    {
        $user = Auth::user();
        if (! $task->isEditableBy($user) && $task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', GeneralTask::STATUSES),
        ]);

        // Only creator can cancel a task
        if ($validated['status'] === GeneralTask::STATUS_CANCELLED && ! $task->isEditableBy($user)) {
            return response()->json(['error' => 'Only the task creator can cancel this task'], 403);
        }

        $oldStatus = $task->status;
        $task->status = $validated['status'];

        if ($validated['status'] === GeneralTask::STATUS_COMPLETED) {
            $task->completed_at = now();
        } elseif ($oldStatus === GeneralTask::STATUS_COMPLETED) {
            $task->completed_at = null;
        }

        $task->save();

        // Notify creator when assignee completes
        if ($validated['status'] === GeneralTask::STATUS_COMPLETED
            && $task->assigned_to === $user->id
            && $task->created_by !== $user->id
        ) {
            $loanSuffix = $task->loan ? " for Loan of ({$task->loan->customer_name}) #{$task->loan->loan_number}" : '';

            $this->notificationService->notify(
                $task->created_by,
                'Task Completed',
                "Task \"{$task->title}\"{$loanSuffix} has been completed by {$user->name}",
                'task',
                null,
                null,
                route('general-tasks.show', $task),
            );
        }

        ActivityLog::log('update_task_status', $task, [
            'from' => $oldStatus,
            'to' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'status' => $task->status,
            'status_html' => $task->status_badge_html,
        ]);
    }

    /**
     * Add a comment to a task.
     */
    public function storeComment(Request $request, GeneralTask $task)
    {
        $user = Auth::user();
        if (! $task->isVisibleTo($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Notify the other party
        $notifyUserId = null;
        if ($user->id === $task->created_by && $task->assigned_to) {
            $notifyUserId = $task->assigned_to;
        } elseif ($user->id === $task->assigned_to && $task->created_by !== $user->id) {
            $notifyUserId = $task->created_by;
        }

        if ($notifyUserId) {
            $loanSuffix = $task->loan ? " for Loan of ({$task->loan->customer_name}) #{$task->loan->loan_number}" : '';

            $this->notificationService->notify(
                $notifyUserId,
                'Task Comment',
                "{$user->name} commented on task: \"{$task->title}\"{$loanSuffix}",
                'task',
                null,
                null,
                route('general-tasks.show', $task),
            );
        }

        ActivityLog::log('comment_task', $task, [
            'comment' => \Illuminate\Support\Str::limit($validated['body'], 100),
        ]);

        return redirect()->route('general-tasks.show', $task)
            ->with('success', 'Comment added.');
    }

    /**
     * Delete own comment.
     */
    public function destroyComment(GeneralTask $task, GeneralTaskComment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return redirect()->route('general-tasks.show', $task)
            ->with('success', 'Comment deleted.');
    }

    /**
     * AJAX loan search for task creation/editing.
     */
    public function searchLoans(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $loans = LoanDetail::visibleTo($user)
            ->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('application_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->select('id', 'loan_number', 'application_number', 'customer_name', 'bank_name', 'status')
            ->limit(10)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($loans);
    }
}
