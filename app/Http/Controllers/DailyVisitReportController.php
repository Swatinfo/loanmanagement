<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DailyVisitReport;
use App\Models\LoanDetail;
use App\Models\User;
use App\Services\ConfigService;
use App\Validation\DvrValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyVisitReportController extends Controller
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $config = $this->configService->load();
        $canViewAll = $user->hasPermission('view_all_dvr');
        $isBdh = $user->hasRole('bdh');
        $isBranchManager = $user->hasRole('branch_manager');
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $contactTypes = $config['dvrContactTypes'] ?? [];
        $purposes = $config['dvrPurposes'] ?? [];

        return view('dvr.index', compact('canViewAll', 'isBdh', 'isBranchManager', 'users', 'contactTypes', 'purposes'));
    }

    /**
     * AJAX DataTables endpoint for DVR listing.
     */
    public function dvrData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = DailyVisitReport::visibleTo($user)
            ->with(['user', 'loan', 'quotation', 'branch']);

        $recordsTotal = (clone $query)->count();

        // View filter
        $view = $request->input('view', 'my_visits');
        if ($view === 'my_visits') {
            $query->where('user_id', $user->id);
        } elseif ($view === 'my_branch' && $user->hasAnyRole(['bdh', 'branch_manager'])) {
            $branchUserIds = \Illuminate\Support\Facades\DB::table('user_branches')
                ->whereIn('branch_id', $user->branches()->pluck('branches.id'))
                ->pluck('user_id')
                ->unique()
                ->toArray();
            $query->whereIn('user_id', $branchUserIds);
        }
        // 'all' = no extra filter (already scoped by visibleTo)

        // Contact type filter
        if ($request->filled('contact_type')) {
            $query->where('contact_type', $request->contact_type);
        }

        // Purpose filter
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        // Follow-up filter (default: exclude completed follow-ups)
        $followUpFilter = $request->input('follow_up', 'active');
        if ($followUpFilter === 'active') {
            // Show visits without follow-up + visits with pending follow-up (exclude done)
            $query->where(function ($q) {
                $q->where('follow_up_needed', false)
                    ->orWhere(function ($q2) {
                        $q2->where('follow_up_needed', true)->where('is_follow_up_done', false);
                    });
            });
        } elseif ($followUpFilter === 'pending') {
            $query->pendingFollowUps();
        } elseif ($followUpFilter === 'overdue') {
            $query->overdueFollowUps();
        } elseif ($followUpFilter === 'done') {
            $query->where('follow_up_needed', true)->where('is_follow_up_done', true);
        }
        // 'all' = no follow-up filter

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_from)->toDateString());
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_to)->toDateString());
        }

        // User filter (for admin/BDH)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        $search = $request->input('search.value', '');
        if ($search !== '' && $search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_phone', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('loan', fn ($l) => $l->where('loan_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Order
        $columns = ['visit_date', 'contact_name', 'contact_type', 'purpose', 'follow_up_date', 'created_at'];
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'visit_date';
        $query->orderBy($orderColumn, $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $visits = $query->skip($start)->take($length)->get();

        $config = $this->configService->load();
        $contactTypeLabels = collect($config['dvrContactTypes'] ?? [])->pluck('label_en', 'key')->toArray();
        $purposeLabels = collect($config['dvrPurposes'] ?? [])->pluck('label_en', 'key')->toArray();

        $data = $visits->map(function (DailyVisitReport $visit) use ($user, $contactTypeLabels, $purposeLabels) {
            $loanInfo = '';
            if ($visit->loan) {
                $loanInfo = '<a href="'.route('loans.show', $visit->loan_id).'" class="text-decoration-none">'
                    .'<span class="shf-badge shf-badge-blue shf-text-2xs">#'.e($visit->loan->loan_number).'</span></a>'
                    .'<br><small class="text-muted">'.e($visit->loan->customer_name).'</small>';
            } elseif ($visit->quotation) {
                $loanInfo = '<span class="shf-badge shf-badge-gray shf-text-2xs">Q#'.$visit->quotation_id.'</span>'
                    .'<br><small class="text-muted">'.e($visit->quotation->customer_name).'</small>';
            }

            // Follow-up status (same urgency logic as personal task due dates)
            $followUpHtml = '—';
            $followUpUrgency = null;
            if ($visit->follow_up_needed) {
                if ($visit->is_follow_up_done) {
                    $followUpHtml = '<span class="shf-badge shf-badge-green shf-text-2xs">Done</span>';
                    if ($visit->follow_up_visit_id) {
                        $followUpHtml .= ' <a href="'.route('dvr.show', $visit->follow_up_visit_id).'" class="shf-text-2xs">View</a>';
                    }
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

            $contactTypeLabel = $contactTypeLabels[$visit->contact_type] ?? ucfirst(str_replace('_', ' ', $visit->contact_type));
            $purposeLabel = $purposeLabels[$visit->purpose] ?? ucfirst(str_replace('_', ' ', $visit->purpose));

            return [
                'id' => $visit->id,
                'visit_date' => $visit->visit_date->format('d M Y'),
                'visit_date_raw' => $visit->visit_date->toDateString(),
                'contact_name' => e($visit->contact_name),
                'contact_phone' => e($visit->contact_phone ?? ''),
                'contact_type' => $contactTypeLabel,
                'contact_type_key' => $visit->contact_type,
                'purpose' => $purposeLabel,
                'purpose_key' => $visit->purpose,
                'notes' => $visit->notes ? e(\Illuminate\Support\Str::limit($visit->notes, 60)) : '',
                'outcome' => $visit->outcome ? e(\Illuminate\Support\Str::limit($visit->outcome, 60)) : '',
                'user_name' => e($visit->user?->name ?? '—'),
                'loan_info' => $loanInfo,
                'follow_up_html' => $followUpHtml,
                'follow_up_urgency' => $followUpUrgency,
                'follow_up_needed' => $visit->follow_up_needed,
                'is_follow_up_done' => $visit->is_follow_up_done,
                'show_url' => route('dvr.show', $visit),
                'can_edit' => $visit->isEditableBy($user),
                'can_delete' => $visit->isDeletableBy($user),
                'branch_name' => $visit->branch?->name ?? '',
                'created_at' => $visit->created_at?->format('d M Y'),
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
        $validated = $request->validate(DvrValidationRules::create());

        $user = Auth::user();
        $validated['user_id'] = $user->id;
        $validated['visit_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['visit_date'])->toDateString();
        $validated['follow_up_needed'] = ! empty($validated['follow_up_needed']);
        $validated['branch_id'] = $user->default_branch_id;

        if (! empty($validated['follow_up_date'])) {
            $validated['follow_up_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['follow_up_date'])->toDateString();
        }

        $visit = DailyVisitReport::create($validated);

        // If this is a follow-up to a parent visit, link them and mark parent done
        $successMsg = 'Visit report created successfully.';
        if ($visit->parent_visit_id) {
            DailyVisitReport::where('id', $visit->parent_visit_id)->update([
                'is_follow_up_done' => true,
                'follow_up_visit_id' => $visit->id,
            ]);
            $successMsg = 'Follow-up visit created. Previous follow-up marked as done.';
        }

        ActivityLog::log('create_dvr', $visit, [
            'contact' => $visit->contact_name,
            'type' => $visit->contact_type,
        ]);

        // If created from dashboard, redirect to DVR index; otherwise show the visit
        if ($request->has('_from_dashboard')) {
            return redirect()->route('dvr.index')
                ->with('success', $successMsg);
        }

        return redirect()->route('dvr.show', $visit)
            ->with('success', $successMsg);
    }

    public function show(DailyVisitReport $dvr)
    {
        $user = Auth::user();
        if (! $dvr->isVisibleTo($user)) {
            abort(403);
        }

        $dvr->load(['user', 'loan', 'quotation', 'branch', 'parentVisit', 'followUpVisit']);

        $config = $this->configService->load();
        $contactTypes = $config['dvrContactTypes'] ?? [];
        $purposes = $config['dvrPurposes'] ?? [];
        $contactTypeLabels = collect($contactTypes)->pluck('label_en', 'key')->toArray();
        $purposeLabels = collect($purposes)->pluck('label_en', 'key')->toArray();

        // Get visit chain for timeline
        $visitChain = $dvr->getVisitChain();

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('dvr.show', compact('dvr', 'contactTypes', 'purposes', 'contactTypeLabels', 'purposeLabels', 'visitChain', 'users'));
    }

    public function update(Request $request, DailyVisitReport $dvr)
    {
        $user = Auth::user();
        if (! $dvr->isEditableBy($user)) {
            abort(403);
        }

        $validated = $request->validate(DvrValidationRules::update());

        $validated['visit_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['visit_date'])->toDateString();
        $validated['follow_up_needed'] = ! empty($validated['follow_up_needed']);

        if (! empty($validated['follow_up_date'])) {
            $validated['follow_up_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['follow_up_date'])->toDateString();
        } else {
            $validated['follow_up_date'] = null;
        }

        $dvr->update($validated);

        ActivityLog::log('update_dvr', $dvr);

        return redirect()->back()
            ->with('success', 'Visit report updated successfully.');
    }

    public function destroy(DailyVisitReport $dvr)
    {
        $user = Auth::user();
        if (! $dvr->isDeletableBy($user)) {
            abort(403);
        }

        $contactName = $dvr->contact_name;

        // Unlink parent if this was a follow-up
        if ($dvr->parent_visit_id) {
            DailyVisitReport::where('id', $dvr->parent_visit_id)->update([
                'is_follow_up_done' => false,
                'follow_up_visit_id' => null,
            ]);
        }

        $dvr->delete();

        ActivityLog::log('delete_dvr', null, [
            'contact' => $contactName,
        ]);

        return redirect()->route('dvr.index')
            ->with('success', 'Visit report deleted.');
    }

    /**
     * Mark a follow-up as done without logging a new visit.
     */
    public function markFollowUpDone(DailyVisitReport $dvr): JsonResponse
    {
        $user = Auth::user();
        if (! $dvr->isEditableBy($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dvr->update(['is_follow_up_done' => true]);

        ActivityLog::log('dvr_followup_done', $dvr, [
            'contact' => $dvr->contact_name,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX loan search for DVR.
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

    /**
     * AJAX quotation search for DVR.
     */
    public function searchQuotations(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $query = \App\Models\Quotation::where('customer_name', 'like', "%{$search}%");

        if (! $user->hasPermission('view_all_quotations')) {
            $query->where('user_id', $user->id);
        }

        $quotations = $query->select('id', 'customer_name', 'loan_amount', 'customer_type')
            ->limit(10)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($quotations);
    }

    /**
     * AJAX contact search — searches DVR history, customers, and loan records by phone or name.
     */
    public function searchContacts(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $results = collect();

        // 1. Search previous DVR contacts (most relevant — user's own visits first)
        $dvrContacts = DailyVisitReport::visibleTo($user)
            ->where(function ($q) use ($search) {
                $q->where('contact_phone', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            })
            ->select('contact_name', 'contact_phone', 'contact_type')
            ->orderBy('visit_date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->contact_name,
                'phone' => $r->contact_phone,
                'type' => $r->contact_type,
                'source' => 'DVR',
            ]);
        $results = $results->merge($dvrContacts);

        // 2. Search customers table
        $customers = \App\Models\Customer::where(function ($q) use ($search) {
            $q->where('mobile', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%");
        })
            ->select('customer_name', 'mobile')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->customer_name,
                'phone' => $r->mobile,
                'type' => 'existing_customer',
                'source' => 'Customer',
            ]);
        $results = $results->merge($customers);

        // 3. Search loan records
        $loans = LoanDetail::visibleTo($user)
            ->where(function ($q) use ($search) {
                $q->where('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->select('customer_name', 'customer_phone', 'loan_number')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->customer_name,
                'phone' => $r->customer_phone,
                'type' => 'existing_customer',
                'source' => 'Loan #'.$r->loan_number,
            ]);
        $results = $results->merge($loans);

        // Deduplicate by phone+name combo
        $unique = $results->unique(fn ($r) => strtolower($r['name']).'|'.($r['phone'] ?? ''))
            ->take(15)
            ->values();

        return response()->json($unique);
    }
}
