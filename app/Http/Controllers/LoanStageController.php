<?php

namespace App\Http\Controllers;

use App\Models\LoanDetail;
use App\Models\StageQuery;
use App\Models\User;
use App\Services\LoanStageService;
use App\Services\StageQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanStageController extends Controller
{
    public function __construct(
        private LoanStageService $stageService,
        private StageQueryService $queryService,
    ) {}

    public function index(LoanDetail $loan)
    {
        $this->authorizeView($loan);

        $loan->load(['valuationDetails', 'location.parent', 'branch', 'product']);
        $stageAssignments = $this->stageService->getLoanStageStatus($loan);
        $mainStages = $stageAssignments->filter(fn ($sa) => ! $sa->is_parallel_stage && $sa->parent_stage_key === null);
        $subStages = $stageAssignments->filter(fn ($sa) => $sa->is_parallel_stage || $sa->parent_stage_key !== null);
        $progress = $loan->progress;
        $allActiveUsers = User::whereHas('roles', fn ($q) => $q->whereNotIn('slug', ['super_admin', 'admin']))
            ->where('is_active', true)
            ->with(['employerBanks', 'locations', 'branches', 'roles'])
            ->orderBy('name')->get();
        $stageRoleEligibility = LoanStageService::getAllStageRoleEligibility();

        // Build skip-allowed map from product stage config
        $skipAllowed = [];
        if ($loan->product_id) {
            $productStages = \App\Models\ProductStage::where('product_id', $loan->product_id)
                ->with('stage')
                ->get();
            foreach ($productStages as $ps) {
                if ($ps->stage) {
                    $skipAllowed[$ps->stage->stage_key] = $ps->allow_skip;
                }
            }
        }

        return view('loans.stages', compact('loan', 'mainStages', 'subStages', 'progress', 'allActiveUsers', 'stageRoleEligibility', 'skipAllowed'));
    }

    public function updateStatus(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,rejected,skipped',
        ]);

        if ($validated['status'] === 'skipped' && ! auth()->user()->hasPermission('skip_loan_stages')) {
            return response()->json(['error' => 'You do not have permission to skip stages'], 403);
        }

        // Docket phase 2: office employee clicks "Generate KFS" → complete docket directly
        // (KFS stage will be assigned to task owner via handleStageCompletion)

        // Block completion if required stage data is missing
        if ($validated['status'] === 'completed') {
            $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->first();
            if ($assignment) {
                // Check for unresolved queries
                if ($assignment->hasPendingQueries()) {
                    $pendingCount = $assignment->queries()->whereIn('status', ['pending', 'responded'])->count();

                    return response()->json(['error' => "Cannot complete — {$pendingCount} unresolved query/queries. Resolve all queries first."], 422);
                }

                // Check document collection separately
                if ($stageKey === 'document_collection') {
                    $docService = app(\App\Services\LoanDocumentService::class);
                    if (! $docService->allRequiredResolved($loan)) {
                        return response()->json(['error' => 'Cannot complete — not all required documents have been collected.'], 422);
                    }
                } elseif (in_array($stageKey, ['technical_valuation', 'property_valuation'])) {
                    // Valuation stages need valuation_details record
                    if (! $loan->valuationDetails()->where('valuation_type', 'property')->whereNotNull('final_valuation')->exists()) {
                        return response()->json(['error' => 'Cannot complete — fill the valuation form first.'], 422);
                    }
                } elseif (! $this->isStageDataComplete($stageKey, $assignment)) {
                    $missingFields = $this->getFieldErrors($stageKey, $assignment->getNotesData());
                    if (! empty($missingFields)) {
                        return response()->json([
                            'error' => 'Please fill all required fields',
                            'field_errors' => $missingFields,
                        ], 422);
                    }

                    return response()->json(['error' => 'Cannot complete — required details not filled.'], 422);
                }
            }
        }

        try {
            $assignment = $this->stageService->updateStageStatus(
                $loan, $stageKey, $validated['status'], auth()->id()
            );

            $loan->refresh();
            $progress = $this->stageService->recalculateProgress($loan);

            return response()->json([
                'success' => true,
                'assignment' => [
                    'stage_key' => $assignment->stage_key,
                    'status' => $assignment->status,
                ],
                'current_stage' => $loan->current_stage,
                'progress' => [
                    'completed' => $progress->completed_stages,
                    'total' => $progress->total_stages,
                    'percentage' => $progress->overall_percentage,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function assign(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $assignment = $this->stageService->assignStage($loan, $stageKey, $validated['user_id']);

        return response()->json([
            'success' => true,
            'assigned_to' => $assignment->assignee?->name,
        ]);
    }

    public function skip(LoanDetail $loan, string $stageKey): JsonResponse
    {
        try {
            $this->stageService->skipStage($loan, $stageKey, auth()->id());
            $loan->refresh();

            return response()->json([
                'success' => true,
                'current_stage' => $loan->current_stage,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function transfer(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            // Clear handover_date on OTC transfer so the new assignee fills it
            if ($stageKey === 'otc_clearance') {
                $otcAssignment = $loan->stageAssignments()->where('stage_key', 'otc_clearance')->first();
                if ($otcAssignment) {
                    $notes = $otcAssignment->getNotesData();
                    unset($notes['handover_date']);
                    $otcAssignment->update(['notes' => ! empty($notes) ? json_encode($notes) : null]);
                }
            }

            $assignment = $this->stageService->transferStage(
                $loan, $stageKey, (int) $validated['user_id'], $validated['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'assigned_to' => $assignment->assignee?->name,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function transferHistory(LoanDetail $loan)
    {
        $transfers = $loan->stageTransfers()
            ->with(['fromUser', 'toUser', 'stageAssignment.stage'])
            ->latest('created_at')
            ->get();

        return view('loans.transfers', compact('loan', 'transfers'));
    }

    public function reject(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        if (! auth()->user()->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'bdh'])) {
            return response()->json(['error' => 'Only Branch Manager, BDH, or Admin can reject loans'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $this->stageService->rejectLoan($loan, $stageKey, $validated['reason']);

        return response()->json(['success' => true, 'message' => 'Loan rejected']);
    }

    public function raiseQuery(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $validated = $request->validate([
            'query_text' => 'required|string|max:5000',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->firstOrFail();
        $query = $this->queryService->raiseQuery($assignment, $validated['query_text'], auth()->id());

        return response()->json(['success' => true, 'query' => $query->load('raisedByUser')]);
    }

    public function respondToQuery(Request $request, StageQuery $query): JsonResponse
    {
        $validated = $request->validate([
            'response_text' => 'required|string|max:5000',
        ]);

        $response = $this->queryService->respondToQuery($query, $validated['response_text'], auth()->id());

        return response()->json(['success' => true, 'response' => $response->load('respondedByUser')]);
    }

    public function resolveQuery(StageQuery $query): JsonResponse
    {
        // Only the user who raised the query can resolve it
        if ($query->raised_by !== auth()->id()) {
            return response()->json(['error' => 'Only the user who raised this query can resolve it.'], 403);
        }

        $this->queryService->resolveQuery($query, auth()->id());

        return response()->json(['success' => true]);
    }

    public function sanctionAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_for_sanction,sanction_generated',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'sanction')->firstOrFail();

        if ($validated['action'] === 'send_for_sanction') {
            $assignment->mergeNotesData([
                'sanction_phase' => '2',
                'sanction_original_assignee' => $assignment->assigned_to,
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'sanction', 1);
                $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'sanction', 1);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'sanction', (int) $transferTo, 'Sent for sanction letter generation');
            }

            return response()->json(['success' => true, 'message' => 'Sent for sanction letter generation']);
        }

        if ($validated['action'] === 'sanction_generated') {
            $assignment->mergeNotesData(['sanction_phase' => '3']);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase3Role = $this->stageService->getLoanPhaseRole($loan, 'sanction', 2);
                $transferTo = $this->stageService->findUserForRole($phase3Role, $loan, 'sanction', 2);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'sanction', (int) $transferTo, 'Sanction letter generated');
            }

            return response()->json(['success' => true, 'message' => 'Sanction letter marked as generated']);
        }

        return response()->json(['error' => 'Invalid action'], 422);
    }

    public function legalAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_to_bank,initiate_legal',
            'suggested_legal_advisor' => 'nullable|string|max:255',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'legal_verification')->firstOrFail();

        if ($validated['action'] === 'send_to_bank') {
            $assignment->mergeNotesData([
                'legal_phase' => '2',
                'legal_original_assignee' => $assignment->assigned_to,
                'suggested_legal_advisor' => $validated['suggested_legal_advisor'] ?? '',
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'legal_verification', 1);
                $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'legal_verification', 1);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'legal_verification', (int) $transferTo, 'Sent for legal verification');
            }

            return response()->json(['success' => true, 'message' => 'Sent for legal verification']);
        }

        if ($validated['action'] === 'initiate_legal') {
            $assignment->mergeNotesData([
                'legal_phase' => '3',
                'confirmed_legal_advisor' => $validated['suggested_legal_advisor'] ?? $assignment->getNotesData()['suggested_legal_advisor'] ?? '',
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase3Role = $this->stageService->getLoanPhaseRole($loan, 'legal_verification', 2);
                $transferTo = $this->stageService->findUserForRole($phase3Role, $loan, 'legal_verification', 2);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'legal_verification', (int) $transferTo, 'Legal initiated, transferred back');
            }

            return response()->json(['success' => true, 'message' => 'Legal initiated, transferred to task owner']);
        }

        return response()->json(['error' => 'Invalid action'], 422);
    }

    public function technicalValuationAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_to_office',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'technical_valuation')->firstOrFail();

        $assignment->mergeNotesData([
            'tv_phase' => '2',
            'tv_original_assignee' => $assignment->assigned_to,
        ]);

        $transferTo = $validated['transfer_to'] ?? null;
        if (! $transferTo) {
            $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'technical_valuation', 1);
            $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'technical_valuation', 1);
        }

        if ($transferTo) {
            $this->stageService->transferStage($loan, 'technical_valuation', (int) $transferTo, 'Sent for technical valuation');
        }

        return response()->json(['success' => true, 'message' => 'Sent for technical valuation']);
    }

    public function esignAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_for_esign,esign_generated,esign_customer_done,esign_complete',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'esign')->firstOrFail();

        // Phase 1 → 2
        if ($validated['action'] === 'send_for_esign') {
            $assignment->mergeNotesData([
                'esign_phase' => '2',
                'esign_original_assignee' => $assignment->assigned_to,
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'esign', 1);
                $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'esign', 1);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'esign', (int) $transferTo, 'Sent for E-Sign & eNACH generation');
            }

            return response()->json(['success' => true, 'message' => 'Sent for E-Sign & eNACH generation']);
        }

        // Phase 2 → 3
        if ($validated['action'] === 'esign_generated') {
            $assignment->mergeNotesData([
                'esign_phase' => '3',
                'esign_bank_employee' => $assignment->assigned_to,
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase3Role = $this->stageService->getLoanPhaseRole($loan, 'esign', 2);
                $transferTo = $this->stageService->findUserForRole($phase3Role, $loan, 'esign', 2);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'esign', (int) $transferTo, 'E-Sign docs generated, sent for customer completion');
            }

            return response()->json(['success' => true, 'message' => 'Sent to task owner for customer completion']);
        }

        // Phase 3 → 4
        if ($validated['action'] === 'esign_customer_done') {
            $assignment->mergeNotesData(['esign_phase' => '4']);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase4Role = $this->stageService->getLoanPhaseRole($loan, 'esign', 3);
                $transferTo = $this->stageService->findUserForRole($phase4Role, $loan, 'esign', 3);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'esign', (int) $transferTo, 'E-Sign completed with customer, returned for confirmation');
            }

            return response()->json(['success' => true, 'message' => 'Returned for final confirmation']);
        }

        // Phase 4: Bank employee confirms → complete stage
        if ($validated['action'] === 'esign_complete') {
            $this->stageService->updateStageStatus($loan, 'esign', 'completed', auth()->id());
            $loan->refresh();
            $progress = $this->stageService->recalculateProgress($loan);

            return response()->json([
                'success' => true,
                'message' => 'E-Sign & eNACH completed',
                'assignment' => ['stage_key' => 'esign', 'status' => 'completed'],
                'current_stage' => $loan->current_stage,
                'progress' => ['completed' => $progress->completed_stages, 'total' => $progress->total_stages, 'percentage' => $progress->overall_percentage],
            ]);
        }

        return response()->json(['error' => 'Invalid action'], 422);
    }

    public function docketAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_to_office',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'docket')->firstOrFail();

        $assignment->mergeNotesData([
            'docket_phase' => '2',
            'docket_original_assignee' => $assignment->assigned_to,
        ]);

        $transferTo = $validated['transfer_to'] ?? null;
        if (! $transferTo) {
            $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'docket', 1);
            $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'docket', 1);
        }

        if ($transferTo) {
            $this->stageService->transferStage($loan, 'docket', (int) $transferTo, 'Sent for docket review');
        }

        return response()->json(['success' => true, 'message' => 'Sent for docket review']);
    }

    public function ratePfAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:send_to_bank,return_to_owner,complete',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'rate_pf')->firstOrFail();
        $notesData = $assignment->getNotesData();

        $requiredFields = [
            'interest_rate' => 'Interest Rate',
            'repo_rate' => 'Repo Rate',
            'bank_rate' => 'Bank Margin',
            'rate_offered_date' => 'Rate Offered Date',
            'rate_valid_until' => 'Valid Until',
            'processing_fee_type' => 'PF Type',
            'processing_fee' => 'Processing Fee',
            'gst_percent' => 'GST %',
            'admin_charges' => 'Admin Charges',
            'admin_charges_gst_percent' => 'Admin GST %',
        ];

        $fieldErrors = [];
        foreach ($requiredFields as $field => $label) {
            if (! isset($notesData[$field]) || $notesData[$field] === '' || $notesData[$field] === null) {
                $fieldErrors[$field] = $label.' is required';
            }
        }
        if (! empty($fieldErrors)) {
            return response()->json([
                'error' => 'Please fill all required fields',
                'field_errors' => $fieldErrors,
            ], 422);
        }

        if ($validated['action'] === 'send_to_bank') {
            $originalValues = [];
            foreach ($requiredFields as $field => $label) {
                $originalValues[$field] = $notesData[$field] ?? '';
            }
            // Include calculated/display fields in snapshot
            foreach (['processing_fee_amount', 'pf_gst_amount', 'total_pf', 'admin_charges_gst_amount', 'total_admin_charges', 'bank_reference'] as $extra) {
                $originalValues[$extra] = $notesData[$extra] ?? '';
            }
            $originalValues['special_conditions'] = $notesData['special_conditions'] ?? '';

            $assignment->mergeNotesData([
                'rate_pf_phase' => '2',
                'rate_pf_original_assignee' => $assignment->assigned_to,
                'original_values' => $originalValues,
            ]);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase2Role = $this->stageService->getLoanPhaseRole($loan, 'rate_pf', 0);
                $transferTo = $this->stageService->findUserForRole($phase2Role, $loan, 'rate_pf', 0);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'rate_pf', (int) $transferTo, 'Sent for bank rate review');
            }

            return response()->json(['success' => true, 'message' => 'Sent for rate review']);
        }

        if ($validated['action'] === 'return_to_owner') {
            $assignment->mergeNotesData(['rate_pf_phase' => '3']);

            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $phase3Role = $this->stageService->getLoanPhaseRole($loan, 'rate_pf', 1);
                $transferTo = $this->stageService->findUserForRole($phase3Role, $loan, 'rate_pf', 1);
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'rate_pf', (int) $transferTo, 'Bank reviewed rate details, returned');
            }

            return response()->json(['success' => true, 'message' => 'Returned to task owner']);
        }

        if ($validated['action'] === 'complete') {
            $this->stageService->updateStageStatus($loan, 'rate_pf', 'completed', auth()->id());

            return response()->json(['success' => true, 'message' => 'Rate & PF completed']);
        }

        return response()->json(['error' => 'Invalid action'], 422);
    }

    public function saveNotes(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $validated = $request->validate([
            'notes_data' => 'required|array',
        ]);

        $notesData = $validated['notes_data'];

        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->firstOrFail();

        // Validate required fields only for non-completed stages
        // Merge with stored notes so phase-dependent validation has full context
        if ($assignment->status !== 'completed') {
            $fieldErrors = $this->getFieldErrors($stageKey, array_merge($assignment->getNotesData(), $notesData));
            if (! empty($fieldErrors)) {
                $messages = array_values($fieldErrors);

                return response()->json([
                    'error' => implode(', ', $messages),
                    'field_errors' => $fieldErrors,
                ], 422);
            }
        }

        // Sanction: EMI amount must not exceed sanctioned amount
        if ($stageKey === 'sanction' && isset($notesData['emi_amount'], $notesData['sanctioned_amount'])) {
            $emiAmount = (float) $notesData['emi_amount'];
            $sanctionedAmount = (float) $notesData['sanctioned_amount'];
            if ($emiAmount > $sanctionedAmount) {
                return response()->json([
                    'error' => 'EMI amount cannot exceed sanctioned amount',
                    'field_errors' => ['emi_amount' => 'EMI amount (₹ '.number_format($emiAmount).') exceeds sanctioned amount (₹ '.number_format($sanctionedAmount).')'],
                ], 422);
            }
        }

        $assignment->mergeNotesData($notesData);

        // Sync specific fields to loan_details
        if ($stageKey === 'app_number' && ! empty($notesData['application_number'])) {
            $loan->update(['application_number' => $notesData['application_number']]);
        }

        // Calculate and store expected docket date when sanction details are saved
        if ($stageKey === 'sanction' && ! empty($notesData['sanction_date'])) {
            $appNumberAssignment = $loan->stageAssignments()->where('stage_key', 'app_number')->first();
            $appNotes = $appNumberAssignment ? $appNumberAssignment->getNotesData() : [];
            $docketOffset = $appNotes['docket_days_offset'] ?? null;

            if ($docketOffset && $docketOffset !== '0') {
                $sanctionDateObj = \Carbon\Carbon::createFromFormat('d/m/Y', $notesData['sanction_date']);
                $loan->update(['due_date' => $sanctionDateObj->copy()->addDays((int) $docketOffset)->toDateString()]);
            } elseif ($docketOffset === '0' && ! empty($appNotes['custom_docket_date'])) {
                $loan->update(['due_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $appNotes['custom_docket_date'])->toDateString()]);
            }
        }

        // Refresh assignment after notes merge
        $assignment->refresh();

        // Auto-complete if stage criteria met (for pending/in_progress stages)
        $stageAdvanced = false;
        $stageReverted = false;
        if (in_array($assignment->status, ['pending', 'in_progress']) && $this->isStageDataComplete($stageKey, $assignment)) {
            if ($assignment->status === 'pending') {
                $this->stageService->updateStageStatus($loan, $stageKey, 'in_progress', auth()->id());
            }
            $this->stageService->updateStageStatus($loan, $stageKey, 'completed', auth()->id());
            $stageAdvanced = true;
        }

        // Soft-revert: if a completed stage's edit makes it incomplete, revert status
        if ($assignment->status === 'completed' && ! $stageAdvanced) {
            $stageReverted = $this->stageService->revertStageIfIncomplete(
                $loan,
                $stageKey,
                $this->isStageDataComplete($stageKey, $assignment)
            );
        }

        return response()->json([
            'success' => true,
            'stage_advanced' => $stageAdvanced,
            'stage_reverted' => $stageReverted,
        ]);
    }

    /**
     * Required fields per stage — returns [field_name => "Label is required"] for missing fields.
     */
    private function getFieldErrors(string $stageKey, array $data): array
    {
        $errors = [];

        $rules = match ($stageKey) {
            'app_number' => array_merge(
                ['application_number' => 'Application Number', 'docket_days_offset' => 'Docket Timeline'],
                ($data['docket_days_offset'] ?? '') === '0' ? ['custom_docket_date' => 'Custom Docket Date'] : []
            ),
            'bsm_osv' => [],
            'legal_verification' => [],
            'technical_valuation', 'property_valuation' => [], // validated via valuation_details table, not notes
            'rate_pf' => [],
            'sanction' => $this->getSanctionRequiredFields($data),
            'docket' => ($data['docket_phase'] ?? '') === '2' ? ['login_date' => 'Login Date'] : [],
            'kfs' => [],
            'esign' => [],
            'otc_clearance' => ['handover_date' => 'Handover Date'],
            default => [],
        };

        foreach ($rules as $field => $label) {
            if (! isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[$field] = "{$label} is required";
            }
        }

        return $errors;
    }

    /**
     * Rate & PF has role-gated sections: bank employee fills rate, task owner fills PF/charges.
     * Only validate required fields that belong to the submitted section.
     */
    /**
     * Sanction stage phase 3: validate form fields only when task owner fills details.
     */
    private function getSanctionRequiredFields(array $data): array
    {
        if (! array_key_exists('sanction_date', $data)) {
            return [];
        }

        return [
            'sanction_date' => 'Sanction Date',
            'sanctioned_amount' => 'Sanctioned Amount',
            'tenure_months' => 'Tenure (Months)',
            'emi_amount' => 'EMI Amount',
        ];
    }

    /**
     * BSM/OSV: validate form fields only in phase 3 or 4.
     */
    /**
     * Check if all required data is present in the assignment's notes for auto-completion.
     */
    private function isStageDataComplete(string $stageKey, $assignment): bool
    {
        $notesData = $assignment->getNotesData();

        return match ($stageKey) {
            'app_number' => ! empty($notesData['application_number'])
                && isset($notesData['docket_days_offset']) && $notesData['docket_days_offset'] !== ''
                && ($notesData['docket_days_offset'] !== '0' || ! empty($notesData['custom_docket_date'])),
            'bsm_osv' => true,
            'legal_verification' => ($notesData['legal_phase'] ?? '') === '3',
            'technical_valuation', 'property_valuation' => false, // auto-completed by ValuationController, not saveNotes
            'rate_pf' => ($notesData['rate_pf_phase'] ?? '') === '3',
            'sanction' => ($notesData['sanction_phase'] ?? '') === '3'
                && ! empty($notesData['sanction_date'])
                && isset($notesData['sanctioned_amount']) && $notesData['sanctioned_amount'] !== '',
            'docket' => ($notesData['docket_phase'] ?? '') === '2' && ! empty($notesData['login_date']),
            'kfs' => true,
            'sanction_decision' => false, // completed via sanctionDecisionAction, not saveNotes
            'esign' => ($notesData['esign_phase'] ?? '') === '4',
            'otc_clearance' => ! empty($notesData['handover_date']),
            default => false,
        };
    }

    public function sanctionDecisionAction(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,escalate_to_bm,escalate_to_bdh,reject',
            'decision_remarks' => 'nullable|string|max:2000',
            'rejection_reason' => 'nullable|string|max:2000',
            'transfer_to' => 'nullable|exists:users,id',
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', 'sanction_decision')->firstOrFail();
        $notesData = $assignment->getNotesData();
        $user = auth()->user();
        $action = $validated['action'];

        // Approve — any assignee can approve
        if ($action === 'approve') {
            $loan->update(['is_sanctioned' => true]);
            $assignment->mergeNotesData(['decision_action' => 'approved', 'decided_by' => $user->id]);

            $this->stageService->updateStageStatus($loan, 'sanction_decision', 'completed', $user->id);
            $loan->refresh();
            $progress = $this->stageService->recalculateProgress($loan);

            return response()->json([
                'success' => true,
                'message' => 'Loan sanctioned successfully',
                'assignment' => ['stage_key' => 'sanction_decision', 'status' => 'completed'],
                'current_stage' => $loan->current_stage,
                'progress' => ['completed' => $progress->completed_stages, 'total' => $progress->total_stages, 'percentage' => $progress->overall_percentage],
            ]);
        }

        // Escalate — requires decision_remarks
        if (in_array($action, ['escalate_to_bm', 'escalate_to_bdh'])) {
            if (empty($validated['decision_remarks'])) {
                return response()->json(['error' => 'Remarks are required for escalation'], 422);
            }

            $targetRole = $action === 'escalate_to_bm' ? 'branch_manager' : 'bdh';
            $escalationHistory = $notesData['escalation_history'] ?? [];
            $escalationHistory[] = [
                'from_user_id' => $user->id,
                'from_user_name' => $user->name,
                'to_role' => $targetRole,
                'remarks' => $validated['decision_remarks'],
                'date' => now()->toDateTimeString(),
            ];

            $assignment->mergeNotesData([
                'escalation_history' => $escalationHistory,
                'decision_remarks' => $validated['decision_remarks'],
            ]);

            // Transfer to selected user or find best match
            $transferTo = $validated['transfer_to'] ?? null;
            if (! $transferTo) {
                $transferTo = User::where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('slug', $targetRole))
                    ->when($loan->branch_id, fn ($q) => $q->whereHas('branches', fn ($bq) => $bq->where('branches.id', $loan->branch_id)))
                    ->first()?->id;
            }

            if ($transferTo) {
                $this->stageService->transferStage($loan, 'sanction_decision', (int) $transferTo, "Escalated to {$targetRole}: {$validated['decision_remarks']}");
            }

            $roleLabel = $action === 'escalate_to_bm' ? 'Branch Manager' : 'BDH';

            return response()->json(['success' => true, 'message' => "Escalated to {$roleLabel}"]);
        }

        // Reject — requires rejection_reason (compulsory)
        if ($action === 'reject') {
            if (empty($validated['rejection_reason']) || strlen($validated['rejection_reason']) < 10) {
                return response()->json(['error' => 'Rejection reason is required (minimum 10 characters)'], 422);
            }

            // Only BM/BDH/admin/super_admin can reject
            if (! $user->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'bdh'])) {
                return response()->json(['error' => 'Only Branch Manager or BDH can reject loans'], 403);
            }

            $assignment->mergeNotesData([
                'decision_action' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'decided_by' => $user->id,
            ]);

            $this->stageService->rejectLoan($loan, 'sanction_decision', $validated['rejection_reason'], $user->id);

            // Reject all pending/in_progress stages (save previous_status for reactivation)
            $loan->stageAssignments()
                ->whereIn('status', ['pending', 'in_progress'])
                ->where('stage_key', '!=', 'sanction_decision')
                ->get()
                ->each(fn ($sa) => $sa->update([
                    'previous_status' => $sa->status,
                    'status' => 'rejected',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ]));

            return response()->json(['success' => true, 'message' => 'Loan rejected']);
        }

        return response()->json(['error' => 'Invalid action'], 422);
    }

    public function eligibleUsers(Request $request, LoanDetail $loan, string $stageKey): JsonResponse
    {
        $role = $request->query('role');

        $query = User::where('is_active', true)->where('id', '!=', auth()->id())->select('id', 'name', 'email');

        if ($role) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $role));

            // Filter bank employees by loan's bank
            if ($role === 'bank_employee' && $loan->bank_id) {
                $query->where(function ($q) use ($loan) {
                    $q->whereHas('employerBanks', fn ($bq) => $bq->where('banks.id', $loan->bank_id))
                        ->orWhere('task_bank_id', $loan->bank_id);
                });
            }

            // Filter office employees by loan's bank
            if ($role === 'office_employee' && $loan->bank_id) {
                $query->whereHas('employerBanks', fn ($q) => $q->where('banks.id', $loan->bank_id));
            }

            // Filter branch-based roles by loan's branch
            if (in_array($role, ['branch_manager', 'bdh', 'loan_advisor']) && $loan->branch_id) {
                $query->whereHas('branches', fn ($q) => $q->where('branches.id', $loan->branch_id));
            }
        } else {
            // No role filter — resolve from loan's workflow config
            $resolvedRole = $this->stageService->getLoanStageRole($loan, $stageKey);
            if ($resolvedRole === 'task_owner') {
                $query->whereHas('roles', fn ($q) => $q->whereIn('slug', ['loan_advisor', 'branch_manager', 'bdh']));
            } else {
                $query->whereHas('roles', fn ($q) => $q->where('slug', $resolvedRole));
            }
            if (in_array($resolvedRole, ['bank_employee', 'office_employee']) && $loan->bank_id) {
                $query->whereHas('employerBanks', fn ($bq) => $bq->where('banks.id', $loan->bank_id));
            }
        }

        $users = $query->orderBy('name')->limit(50)->get();

        // Determine default user from loan's workflow config snapshot
        $resolvedRole = $role ?: $this->stageService->getLoanStageRole($loan, $stageKey);
        $defaultUserId = $this->stageService->findUserForRole($resolvedRole, $loan, $stageKey);

        // Ensure default is in the returned list
        if ($defaultUserId && ! $users->contains('id', $defaultUserId)) {
            $defaultUserId = null;
        }

        return response()->json(['users' => $users, 'default_user_id' => $defaultUserId]);
    }

    /**
     * Find a bank employee for the loan's bank.
     */
    private function findBankEmployee(LoanDetail $loan): ?int
    {
        // Try city-level default first
        $bank = \App\Models\Bank::find($loan->bank_id);
        if ($bank) {
            $cityId = $loan->branch_id ? \App\Models\Branch::find($loan->branch_id)?->location_id : null;
            $defaultBE = $bank->getDefaultEmployeeForCity($cityId);
            if ($defaultBE && User::where('id', $defaultBE)->where('is_active', true)->exists()) {
                return $defaultBE;
            }
        }

        // Fallback: any active bank employee for this bank
        $bankEmployee = User::whereHas('roles', fn ($q) => $q->where('slug', 'bank_employee'))
            ->where(function ($q) use ($loan) {
                $q->where('task_bank_id', $loan->bank_id)
                    ->orWhereHas('employerBanks', fn ($bq) => $bq->where('banks.id', $loan->bank_id));
            })
            ->where('is_active', true)
            ->first();

        return $bankEmployee?->id;
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
        if (($user->hasRole('branch_manager') || $user->hasRole('bdh')) && $loan->branch_id) {
            if ($user->branches()->where('branches.id', $loan->branch_id)->exists()) {
                return;
            }
        }
        abort(403);
    }
}
