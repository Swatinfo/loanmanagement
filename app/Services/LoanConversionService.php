<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\LoanDetail;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class LoanConversionService
{
    public function __construct(
        private LoanStageService $stageService,
        private LoanDocumentService $documentService,
    ) {}

    /**
     * Convert a quotation to a loan task.
     */
    public function convertFromQuotation(Quotation $quotation, int $bankIndex, array $extra = []): LoanDetail
    {
        if ($quotation->loan_id !== null) {
            throw new \RuntimeException('Quotation already converted to loan #'.$quotation->loan->loan_number);
        }

        $quotationBank = $quotation->banks[$bankIndex]
            ?? throw new \RuntimeException('Invalid bank index');

        return DB::transaction(function () use ($quotation, $quotationBank, $extra) {
            $bank = Bank::where('name', $quotationBank->bank_name)->first();

            // Create or find customer record
            $customer = Customer::create([
                'customer_name' => $quotation->customer_name,
                'mobile' => $extra['customer_phone'] ?? null,
                'email' => $extra['customer_email'] ?? null,
                'date_of_birth' => $extra['date_of_birth'] ?? null,
                'pan_number' => $extra['pan_number'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $loan = LoanDetail::create([
                'loan_number' => LoanDetail::generateLoanNumber(),
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'branch_id' => $extra['branch_id'] ?? null,
                'bank_id' => $bank?->id,
                'product_id' => $extra['product_id'] ?? null,
                'location_id' => $quotation->location_id,
                'customer_name' => $quotation->customer_name,
                'customer_type' => $quotation->customer_type,
                'customer_phone' => $extra['customer_phone'] ?? null,
                'customer_email' => $extra['customer_email'] ?? null,
                'date_of_birth' => $extra['date_of_birth'] ?? null,
                'pan_number' => $extra['pan_number'] ?? null,
                'loan_amount' => $quotation->loan_amount,
                'status' => LoanDetail::STATUS_ACTIVE,
                'current_stage' => 'document_collection',
                'bank_name' => $quotationBank->bank_name,
                'roi_min' => $quotationBank->roi_min,
                'roi_max' => $quotationBank->roi_max,
                'total_charges' => $quotationBank->total_charges,
                'due_date' => now()->addDays(7)->toDateString(),
                'created_by' => auth()->id(),
                'assigned_advisor' => $extra['assigned_advisor'] ?? auth()->id(),
                'notes' => $extra['notes'] ?? $quotation->additional_notes,
            ]);

            $quotation->update(['loan_id' => $loan->id]);

            // Populate documents from quotation
            $this->documentService->populateFromQuotation($loan, $quotation);

            // Snapshot workflow config (frozen at loan creation)
            $workflowConfig = $this->stageService->buildWorkflowSnapshot(
                $loan->bank_id, $loan->product_id, $loan->branch_id, $loan->location_id
            );
            $loan->update(['workflow_config' => $workflowConfig]);

            // Initialize stages and auto-complete inquiry + document_selection
            $this->stageService->initializeStages($loan);
            $this->stageService->autoCompleteStages($loan, ['inquiry', 'document_selection']);
            $this->stageService->autoAssignStage($loan, 'document_collection');

            ActivityLog::log('convert_quotation_to_loan', $loan, [
                'quotation_id' => $quotation->id,
                'loan_number' => $loan->loan_number,
                'customer_name' => $loan->customer_name,
                'loan_amount' => $loan->loan_amount,
                'bank_name' => $loan->bank_name,
            ]);

            return $loan;
        });
    }

    /**
     * Create a loan directly without a quotation.
     */
    public function createDirectLoan(array $data): LoanDetail
    {
        return DB::transaction(function () use ($data) {
            $bankName = isset($data['bank_id'])
                ? Bank::find($data['bank_id'])?->name
                : null;

            $loan = LoanDetail::create([
                'loan_number' => LoanDetail::generateLoanNumber(),
                'bank_id' => $data['bank_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_type' => $data['customer_type'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'loan_amount' => $data['loan_amount'],
                'status' => LoanDetail::STATUS_ACTIVE,
                'current_stage' => 'inquiry',
                'bank_name' => $bankName,
                'due_date' => now()->addDays(7)->toDateString(),
                'created_by' => auth()->id(),
                'assigned_advisor' => $data['assigned_advisor'] ?? auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Snapshot workflow config (frozen at loan creation)
            $workflowConfig = $this->stageService->buildWorkflowSnapshot(
                $loan->bank_id, $loan->product_id, $loan->branch_id
            );
            $loan->update(['workflow_config' => $workflowConfig]);

            // Populate documents from defaults
            $this->documentService->populateFromDefaults($loan);

            // Initialize stages
            $this->stageService->initializeStages($loan);

            ActivityLog::log('create_loan', $loan, [
                'customer_name' => $loan->customer_name,
                'loan_amount' => $loan->loan_amount,
                'bank_name' => $loan->bank_name,
            ]);

            return $loan;
        });
    }
}
