<?php

namespace App\Services;

use App\Models\BankCharge;
use App\Models\Quotation;
use App\Models\QuotationBank;
use App\Models\QuotationDocument;
use App\Models\QuotationEmi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Quotation business logic — validation, PDF generation, DB persistence.
 * Ported from legacy includes/generate-quotation.php
 */
class QuotationService
{
    public function __construct(
        private ConfigService $configService,
        private PdfGenerationService $pdfService,
    ) {}

    /**
     * Generate a quotation PDF and save to database.
     *
     * @param  array  $input  Raw payload from client
     * @param  int  $userId  Authenticated user ID
     * @return array ['success' => true, 'quotation' => Quotation] or ['error' => '...']
     */
    public function generate(array $input, int $userId): array
    {
        $customerName = trim($input['customerName'] ?? '');
        $customerType = trim($input['customerType'] ?? '');
        $loanAmount = (int) ($input['loanAmount'] ?? 0);
        $banks = $input['banks'] ?? [];
        $documents = $input['documents'] ?? [];
        $additionalNotes = trim($input['additionalNotes'] ?? '');

        if (! $customerName || ! $customerType || $loanAmount <= 0 || empty($banks)) {
            return ['error' => 'Missing required fields (customerName, customerType, loanAmount, banks).'];
        }

        if ($loanAmount > 1000000000000) {
            return ['error' => 'Loan amount cannot exceed 1 lakh crore.'];
        }

        foreach ($banks as $bank) {
            $roiMin = (float) ($bank['roiMin'] ?? 0);
            $roiMax = (float) ($bank['roiMax'] ?? 0);
            $bankName = $bank['name'] ?? 'Unknown';
            if ($roiMin <= 0 || $roiMax <= 0) {
                return ['error' => "Min and Max ROI are required for {$bankName}."];
            }
            if ($roiMin > 30 || $roiMax > 30) {
                return ['error' => "ROI cannot exceed 30% for {$bankName}."];
            }
            if ($roiMin > $roiMax) {
                return ['error' => "Min ROI cannot be greater than Max ROI for {$bankName}."];
            }
        }

        // Load config
        $config = $this->configService->load();
        $companyPhone = $config['companyPhone'] ?? '+91 XXXXX XXXXX';
        $companyEmail = $config['companyEmail'] ?? 'info@shf.com';
        $tenures = $config['tenures'] ?? [5, 10, 15, 20];

        // Use selected tenures from client if provided
        if (! empty($input['selectedTenures']) && is_array($input['selectedTenures'])) {
            $selectedTenures = array_map('intval', $input['selectedTenures']);
            $filtered = array_values(array_intersect($selectedTenures, $tenures));
            if (! empty($filtered)) {
                $tenures = $filtered;
            }
        }

        // Build template data
        $today = now();
        $dateStr = $today->format('d F Y');

        $templateData = [
            'customerName' => $customerName,
            'customerType' => $customerType,
            'loanAmount' => $loanAmount,
            'date' => $dateStr,
            'companyPhone' => $companyPhone,
            'companyEmail' => $companyEmail,
            'tenures' => $tenures,
            'banks' => [],
            'documents' => $documents,
            'additionalNotes' => $additionalNotes,
            'ourServices' => trim($input['ourServices'] ?? ($config['ourServices'] ?? '')),
            'preparedByName' => trim($input['preparedByName'] ?? ''),
            'preparedByMobile' => trim($input['preparedByMobile'] ?? ''),
        ];

        // Process banks
        foreach ($banks as $bank) {
            $charges = $bank['charges'] ?? [];
            $totalCharges = (int) ($charges['total'] ?? 0);

            $emiByTenure = [];
            foreach ($bank['emiByTenure'] ?? [] as $tenure => $emiData) {
                $emiByTenure[(int) $tenure] = [
                    'emi' => (int) ($emiData['emi'] ?? 0),
                    'totalInterest' => (int) ($emiData['totalInterest'] ?? 0),
                    'totalPayment' => (int) ($emiData['totalPayment'] ?? 0),
                ];
            }

            $templateData['banks'][] = [
                'name' => $bank['name'] ?? '',
                'roiMin' => (float) ($bank['roiMin'] ?? 0),
                'roiMax' => (float) ($bank['roiMax'] ?? 0),
                'charges' => [
                    'pf' => (int) ($charges['pf'] ?? 0),
                    'pfPercent' => (float) ($charges['pfPercent'] ?? 0),
                    'admin' => (int) ($charges['admin'] ?? 0),
                    'adminBase' => (int) ($charges['adminBase'] ?? 0),
                    'stamp_notary' => (int) ($charges['stamp_notary'] ?? 0),
                    'registration_fee' => (int) ($charges['registration_fee'] ?? 0),
                    'advocate' => (int) ($charges['advocate'] ?? 0),
                    'iom' => (int) ($charges['iom'] ?? 0),
                    'tc' => (int) ($charges['tc'] ?? 0),
                    'extra1Name' => trim($charges['extra1Name'] ?? ''),
                    'extra1Amt' => (int) ($charges['extra1Amt'] ?? 0),
                    'extra2Name' => trim($charges['extra2Name'] ?? ''),
                    'extra2Amt' => (int) ($charges['extra2Amt'] ?? 0),
                    'total' => $totalCharges,
                ],
                'emiByTenure' => $emiByTenure,
            ];
        }

        // Generate PDF
        $pdfResult = $this->pdfService->generate($templateData);

        if (isset($pdfResult['error'])) {
            return $pdfResult;
        }

        // Save to database
        try {
            $quotation = DB::transaction(function () use (
                $userId, $customerName, $customerType, $loanAmount,
                $additionalNotes, $pdfResult, $templateData, $tenures
            ) {
                $branchId = $input['branch_id'] ?? User::find($userId)?->default_branch_id;

                $quotation = Quotation::create([
                    'user_id' => $userId,
                    'customer_name' => $customerName,
                    'customer_type' => $customerType,
                    'loan_amount' => $loanAmount,
                    'pdf_filename' => $pdfResult['filename'],
                    'pdf_path' => $pdfResult['path'],
                    'additional_notes' => $additionalNotes ?: null,
                    'prepared_by_name' => $templateData['preparedByName'] ?: null,
                    'prepared_by_mobile' => $templateData['preparedByMobile'] ?: null,
                    'selected_tenures' => $tenures,
                    'location_id' => $input['location_id'] ?? null,
                    'branch_id' => $branchId,
                ]);

                foreach ($templateData['banks'] as $bankData) {
                    $c = $bankData['charges'];
                    $qBank = QuotationBank::create([
                        'quotation_id' => $quotation->id,
                        'bank_name' => $bankData['name'],
                        'roi_min' => $bankData['roiMin'],
                        'roi_max' => $bankData['roiMax'],
                        'pf_charge' => $c['pfPercent'],
                        'admin_charge' => $c['adminBase'],
                        'stamp_notary' => $c['stamp_notary'],
                        'registration_fee' => $c['registration_fee'],
                        'advocate_fees' => $c['advocate'],
                        'iom_charge' => $c['iom'],
                        'tc_report' => $c['tc'],
                        'extra1_name' => $c['extra1Name'] ?: null,
                        'extra1_amount' => $c['extra1Amt'],
                        'extra2_name' => $c['extra2Name'] ?: null,
                        'extra2_amount' => $c['extra2Amt'],
                        'total_charges' => $c['total'],
                    ]);

                    foreach ($bankData['emiByTenure'] as $tenure => $emi) {
                        QuotationEmi::create([
                            'quotation_bank_id' => $qBank->id,
                            'tenure_years' => $tenure,
                            'monthly_emi' => $emi['emi'],
                            'total_interest' => $emi['totalInterest'],
                            'total_payment' => $emi['totalPayment'],
                        ]);
                    }
                }

                if (! empty($templateData['documents'])) {
                    foreach ($templateData['documents'] as $doc) {
                        QuotationDocument::create([
                            'quotation_id' => $quotation->id,
                            'document_name_en' => $doc['en'] ?? '',
                            'document_name_gu' => $doc['gu'] ?? '',
                        ]);
                    }
                }

                return $quotation;
            });

            // Update bank charges in DB for future reference
            $this->updateBankCharges($templateData['banks']);

            return ['success' => true, 'quotation' => $quotation];

        } catch (\Exception $e) {
            Log::error('Quotation DB save failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Database save failed: '.$e->getMessage(),
                'filename' => $pdfResult['filename'],
            ];
        }
    }

    /**
     * Update bank_charges table with latest charges from quotation.
     */
    private function updateBankCharges(array $banks): void
    {
        foreach ($banks as $bank) {
            $c = $bank['charges'];
            BankCharge::updateOrCreate(
                ['bank_name' => $bank['name']],
                [
                    'pf' => $c['pfPercent'],
                    'admin' => $c['adminBase'],
                    'stamp_notary' => $c['stamp_notary'],
                    'registration_fee' => $c['registration_fee'],
                    'advocate' => $c['advocate'],
                    'tc' => $c['tc'],
                    'extra1_name' => $c['extra1Name'] ?: null,
                    'extra1_amt' => $c['extra1Amt'],
                    'extra2_name' => $c['extra2Name'] ?: null,
                    'extra2_amt' => $c['extra2Amt'],
                ]
            );
        }
    }
}
