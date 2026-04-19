<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\LoanDetail;
use App\Models\LoanDocument;
use App\Models\Quotation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LoanDocumentService
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    /**
     * Copy documents from quotation to loan.
     */
    public function populateFromQuotation(LoanDetail $loan, Quotation $quotation): void
    {
        $quotation->loadMissing('documents');
        $order = 0;

        foreach ($quotation->documents as $doc) {
            LoanDocument::create([
                'loan_id' => $loan->id,
                'document_name_en' => $doc->document_name_en,
                'document_name_gu' => $doc->document_name_gu,
                'is_required' => true,
                'status' => 'pending',
                'sort_order' => $order++,
            ]);
        }
    }

    /**
     * Populate documents from config defaults based on customer type.
     */
    public function populateFromDefaults(LoanDetail $loan): void
    {
        $config = $this->configService->load();
        $type = $loan->customer_type;

        $docsEn = $config['documents_en'][$type] ?? [];
        $docsGu = $config['documents_gu'][$type] ?? [];

        foreach ($docsEn as $i => $nameEn) {
            LoanDocument::create([
                'loan_id' => $loan->id,
                'document_name_en' => $nameEn,
                'document_name_gu' => $docsGu[$i] ?? null,
                'is_required' => true,
                'status' => 'pending',
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * Update document status.
     */
    public function updateStatus(LoanDocument $document, string $status, int $userId, ?string $rejectedReason = null): void
    {
        $updateData = ['status' => $status];

        if ($status === 'received') {
            $updateData['received_date'] = now()->toDateString();
            $updateData['received_by'] = $userId;
            $updateData['rejected_reason'] = null;
        } elseif ($status === 'rejected') {
            $updateData['rejected_reason'] = $rejectedReason;
            $updateData['received_date'] = null;
            $updateData['received_by'] = null;
        } else {
            $updateData['received_date'] = null;
            $updateData['received_by'] = null;
            $updateData['rejected_reason'] = null;
        }

        $document->update($updateData);

        ActivityLog::log('update_document_status', $document, [
            'document_name' => $document->document_name_en,
            'loan_number' => $document->loan->loan_number,
            'new_status' => $status,
        ]);
    }

    /**
     * Get document collection progress.
     */
    public function getProgress(LoanDetail $loan): array
    {
        $total = $loan->documents()->required()->count();
        $resolved = $loan->documents()->required()->resolved()->count();
        $received = $loan->documents()->required()->received()->count();
        $rejected = $loan->documents()->required()->rejected()->count();
        $pending = $loan->documents()->required()->pending()->count();
        $percentage = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;

        return compact('total', 'resolved', 'received', 'rejected', 'pending', 'percentage');
    }

    /**
     * Check if all required documents are resolved.
     */
    public function allRequiredResolved(LoanDetail $loan): bool
    {
        return $loan->documents()->required()->unresolved()->count() === 0;
    }

    /**
     * Add a custom document.
     */
    public function addDocument(LoanDetail $loan, string $nameEn, ?string $nameGu, bool $required = true): LoanDocument
    {
        $maxOrder = $loan->documents()->max('sort_order') ?? -1;

        return LoanDocument::create([
            'loan_id' => $loan->id,
            'document_name_en' => trim($nameEn),
            'document_name_gu' => $nameGu ? trim($nameGu) : null,
            'is_required' => $required,
            'status' => 'pending',
            'sort_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Remove a document.
     */
    public function removeDocument(LoanDocument $document): void
    {
        // Delete uploaded file if exists
        if ($document->hasFile()) {
            Storage::disk('local')->delete($document->file_path);
        }

        ActivityLog::log('remove_loan_document', $document, [
            'document_name' => $document->document_name_en,
            'loan_number' => $document->loan->loan_number,
        ]);

        $document->delete();
    }

    /**
     * Upload a file for a document.
     */
    public function uploadFile(LoanDocument $document, UploadedFile $file, int $userId): LoanDocument
    {
        // Delete old file if replacing
        if ($document->hasFile()) {
            Storage::disk('local')->delete($document->file_path);
        }

        $loan = $document->loan;
        $directory = 'loan-documents/'.$loan->id;
        $fileName = FileUploadService::hashedFilename($file);
        $path = $file->storeAs($directory, $fileName, 'local');

        $document->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime' => $file->getMimeType(),
            'uploaded_by' => $userId,
            'uploaded_at' => now(),
        ]);

        // Auto-mark as received on upload if still pending
        if ($document->status === 'pending') {
            $this->updateStatus($document, 'received', $userId);
        }

        ActivityLog::log('upload_loan_document', $document, [
            'document_name' => $document->document_name_en,
            'loan_number' => $loan->loan_number,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return $document->fresh();
    }

    /**
     * Delete the uploaded file from a document (keeps the document record).
     */
    public function deleteFile(LoanDocument $document): void
    {
        if (! $document->hasFile()) {
            return;
        }

        Storage::disk('local')->delete($document->file_path);

        ActivityLog::log('delete_loan_document_file', $document, [
            'document_name' => $document->document_name_en,
            'loan_number' => $document->loan->loan_number,
            'file_name' => $document->file_name,
        ]);

        $document->update([
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'file_mime' => null,
            'uploaded_by' => null,
            'uploaded_at' => null,
        ]);
    }
}
