<?php

namespace App\Http\Controllers;

use App\Models\LoanDetail;
use App\Models\LoanDocument;
use App\Services\FileUploadService;
use App\Services\LoanDocumentService;
use App\Services\LoanStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanDocumentController extends Controller
{
    public function __construct(
        private LoanDocumentService $documentService,
        private LoanStageService $stageService,
    ) {}

    public function index(LoanDetail $loan)
    {
        $documents = $loan->documents()->orderBy('sort_order')->get();
        $progress = $this->documentService->getProgress($loan);

        return view('loans.documents', compact('loan', 'documents', 'progress'));
    }

    public function updateStatus(Request $request, LoanDetail $loan, LoanDocument $document): JsonResponse
    {
        abort_unless($document->loan_id === $loan->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:pending,received,rejected,waived',
            'rejected_reason' => 'nullable|string|max:500',
        ]);

        $this->documentService->updateStatus(
            $document,
            $validated['status'],
            auth()->id(),
            $validated['rejected_reason'] ?? null,
        );

        $document->refresh()->load('receivedByUser');
        $progress = $this->documentService->getProgress($loan);

        // Auto-advance: if all required docs resolved and current stage is document_collection
        $stageAdvanced = false;
        $stageReverted = false;
        $allResolved = $this->documentService->allRequiredResolved($loan);

        if ($loan->current_stage === 'document_collection' && $allResolved) {
            $assignment = $loan->stageAssignments()->where('stage_key', 'document_collection')->first();
            if ($assignment && in_array($assignment->status, ['pending', 'in_progress'])) {
                if ($assignment->status === 'pending') {
                    $this->stageService->updateStageStatus($loan, 'document_collection', 'in_progress', auth()->id());
                }
                $this->stageService->updateStageStatus($loan, 'document_collection', 'completed', auth()->id());
                $stageAdvanced = true;
            }
        }

        // Soft-revert: if document_collection was completed but docs are no longer all resolved
        if (! $stageAdvanced) {
            $docAssignment = $loan->stageAssignments()->where('stage_key', 'document_collection')->first();
            if ($docAssignment && $docAssignment->status === 'completed') {
                $stageReverted = $this->stageService->revertStageIfIncomplete(
                    $loan,
                    'document_collection',
                    $allResolved
                );
            }
        }

        return response()->json([
            'success' => true,
            'stage_advanced' => $stageAdvanced,
            'stage_reverted' => $stageReverted,
            'document' => [
                'id' => $document->id,
                'status' => $document->status,
                'received_date' => $document->received_date?->format('d M Y'),
                'received_by' => $document->receivedByUser?->name,
            ],
            'progress' => $progress,
        ]);
    }

    public function store(Request $request, LoanDetail $loan): JsonResponse
    {
        $validated = $request->validate([
            'document_name_en' => 'required|string|max:255',
            'document_name_gu' => 'nullable|string|max:255',
            'is_required' => 'boolean',
        ]);

        $document = $this->documentService->addDocument(
            $loan,
            $validated['document_name_en'],
            $validated['document_name_gu'] ?? null,
            $validated['is_required'] ?? true,
        );

        $progress = $this->documentService->getProgress($loan);

        return response()->json([
            'success' => true,
            'document' => $document,
            'progress' => $progress,
        ]);
    }

    public function destroy(LoanDetail $loan, LoanDocument $document): JsonResponse
    {
        abort_unless($document->loan_id === $loan->id, 404);

        $this->documentService->removeDocument($document);
        $progress = $this->documentService->getProgress($loan);

        return response()->json(['success' => true, 'progress' => $progress]);
    }

    public function upload(Request $request, LoanDetail $loan, LoanDocument $document): JsonResponse
    {
        abort_unless($document->loan_id === $loan->id, 404);

        $request->validate(
            ['file' => FileUploadService::rules()],
            FileUploadService::messages(),
        );

        $document = $this->documentService->uploadFile(
            $document,
            $request->file('file'),
            auth()->id(),
        );

        $progress = $this->documentService->getProgress($loan);

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'status' => $document->status,
                'file_name' => $document->file_name,
                'file_size' => $document->formattedFileSize(),
                'has_file' => true,
            ],
            'progress' => $progress,
        ]);
    }

    public function download(LoanDetail $loan, LoanDocument $document): StreamedResponse
    {
        abort_unless($document->loan_id === $loan->id, 404);
        abort_unless($document->hasFile(), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name,
            ['Content-Type' => $document->file_mime]
        );
    }

    public function deleteFile(LoanDetail $loan, LoanDocument $document): JsonResponse
    {
        abort_unless($document->loan_id === $loan->id, 404);
        abort_unless($document->hasFile(), 404);

        $this->documentService->deleteFile($document);
        $progress = $this->documentService->getProgress($loan);

        return response()->json(['success' => true, 'progress' => $progress]);
    }
}
