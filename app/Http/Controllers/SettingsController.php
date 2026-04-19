<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankCharge;
use App\Services\ConfigService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected ConfigService $configService
    ) {}

    public function index()
    {
        $config = $this->configService->load();
        $bankCharges = BankCharge::orderBy('bank_name')->get();
        $loanBanks = \App\Models\Bank::active()->orderBy('name')->pluck('name')->toArray();

        return view('settings.index', compact('config', 'bankCharges', 'loanBanks'));
    }

    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'companyName' => 'required|string|max:255',
            'companyAddress' => 'required|string|max:5000',
            'companyPhone' => 'required|string|max:50',
            'companyEmail' => 'required|email|max:255',
        ]);

        $this->configService->updateMany($validated);
        ActivityLog::log('settings_updated', null, ['section' => 'company']);

        return back()->with('success', 'Company details updated.');
    }

    public function updateBanks(Request $request)
    {
        $validated = $request->validate([
            'banks' => 'required|array|min:1',
            'banks.*' => 'required|string|max:100',
        ]);

        // Remove duplicates and empty
        $banks = array_values(array_unique(array_filter($validated['banks'])));
        $this->configService->updateSection('banks', $banks);
        ActivityLog::log('settings_updated', null, ['section' => 'banks']);

        return back()->with('success', 'Banks updated.');
    }

    public function updateTenures(Request $request)
    {
        $validated = $request->validate([
            'tenures' => 'required|array|min:1',
            'tenures.*' => 'required|integer|min:1|max:50',
        ]);

        $tenures = array_values(array_unique(array_map('intval', $validated['tenures'])));
        sort($tenures);
        $this->configService->updateSection('tenures', $tenures);
        ActivityLog::log('settings_updated', null, ['section' => 'tenures']);

        return back()->with('success', 'Tenures updated.');
    }

    public function updateDocuments(Request $request)
    {
        $validated = $request->validate([
            'documents_en' => 'required|array',
            'documents_gu' => 'required|array',
        ]);

        $this->configService->updateMany([
            'documents_en' => $validated['documents_en'],
            'documents_gu' => $validated['documents_gu'],
        ]);
        ActivityLog::log('settings_updated', null, ['section' => 'documents']);

        return back()->with('success', 'Documents updated.');
    }

    public function updateCharges(Request $request)
    {
        $validated = $request->validate([
            'iomCharges.thresholdAmount' => 'required|integer|min:0',
            'iomCharges.fixedCharge' => 'required|integer|min:0',
            'iomCharges.percentageAbove' => 'required|numeric|min:0|max:100',
        ]);

        $this->configService->updateSection('iomCharges', [
            'thresholdAmount' => (int) $validated['iomCharges']['thresholdAmount'],
            'fixedCharge' => (int) $validated['iomCharges']['fixedCharge'],
            'percentageAbove' => (float) $validated['iomCharges']['percentageAbove'],
        ]);
        ActivityLog::log('settings_updated', null, ['section' => 'charges']);

        return back()->with('success', 'IOM Stamp Paper Charges updated.');
    }

    public function updateBankCharges(Request $request)
    {
        $validated = $request->validate([
            'charges' => 'required|array',
            'charges.*.bank_name' => 'required|string|max:100',
            'charges.*.pf' => 'required|numeric|min:0|max:99.99',
            'charges.*.admin' => 'required|integer|min:0',
            'charges.*.stamp_notary' => 'required|integer|min:0',
            'charges.*.registration_fee' => 'required|integer|min:0',
            'charges.*.advocate' => 'required|integer|min:0',
            'charges.*.tc' => 'required|integer|min:0',
            'charges.*.extra1_name' => 'nullable|string|max:100',
            'charges.*.extra1_amt' => 'nullable|integer|min:0',
            'charges.*.extra2_name' => 'nullable|string|max:100',
            'charges.*.extra2_amt' => 'nullable|integer|min:0',
        ]);

        // Clear and re-insert
        BankCharge::truncate();
        foreach ($validated['charges'] as $charge) {
            BankCharge::create($charge);
        }

        ActivityLog::log('settings_updated', null, ['section' => 'bank_charges']);

        return back()->with('success', 'Bank charges updated.');
    }

    public function updateServices(Request $request)
    {
        $validated = $request->validate([
            'ourServices' => 'required|string|max:10000',
        ]);

        $this->configService->updateSection('ourServices', $validated['ourServices']);
        ActivityLog::log('settings_updated', null, ['section' => 'services']);

        return back()->with('success', 'Services updated.');
    }

    public function updateGst(Request $request)
    {
        $validated = $request->validate([
            'gstPercent' => 'required|numeric|min:0|max:100',
        ]);

        $this->configService->updateSection('gstPercent', (float) $validated['gstPercent']);
        ActivityLog::log('settings_updated', null, ['section' => 'gst']);

        return back()->with('success', 'GST percentage updated.');
    }

    public function updateDvrContactTypes(Request $request)
    {
        $validated = $request->validate([
            'dvrContactTypes' => 'required|array|min:1',
            'dvrContactTypes.*.key' => 'required|string|max:500',
            'dvrContactTypes.*.label_en' => 'required|string|max:10000',
            'dvrContactTypes.*.label_gu' => 'required|string|max:10000',
        ]);

        $this->configService->updateSection('dvrContactTypes', array_values($validated['dvrContactTypes']));
        ActivityLog::log('settings_updated', null, ['section' => 'dvr_contact_types']);

        return back()->with('success', 'DVR contact types updated.');
    }

    public function updateDvrPurposes(Request $request)
    {
        $validated = $request->validate([
            'dvrPurposes' => 'required|array|min:1',
            'dvrPurposes.*.key' => 'required|string|max:500',
            'dvrPurposes.*.label_en' => 'required|string|max:1000',
            'dvrPurposes.*.label_gu' => 'required|string|max:1000',
        ]);

        $this->configService->updateSection('dvrPurposes', array_values($validated['dvrPurposes']));
        ActivityLog::log('settings_updated', null, ['section' => 'dvr_purposes']);

        return back()->with('success', 'DVR purposes updated.');
    }

    public function updateQuotationHoldReasons(Request $request)
    {
        $validated = $request->validate([
            'quotationHoldReasons' => 'required|array|min:1',
            'quotationHoldReasons.*.key' => 'required|string|max:500',
            'quotationHoldReasons.*.label_en' => 'required|string|max:1000',
            'quotationHoldReasons.*.label_gu' => 'required|string|max:1000',
            'quotationHoldReasons.*.group' => 'nullable|string|max:100',
        ]);

        $reasons = array_map(fn ($r) => [
            'key' => $r['key'],
            'label_en' => $r['label_en'],
            'label_gu' => $r['label_gu'],
            'group' => $r['group'] ?? 'Other',
        ], array_values($validated['quotationHoldReasons']));

        $this->configService->updateSection('quotationHoldReasons', $reasons);
        ActivityLog::log('settings_updated', null, ['section' => 'quotation_hold_reasons']);

        return back()->with('success', 'Hold reasons updated.');
    }

    public function updateQuotationCancelReasons(Request $request)
    {
        $validated = $request->validate([
            'quotationCancelReasons' => 'required|array|min:1',
            'quotationCancelReasons.*.key' => 'required|string|max:500',
            'quotationCancelReasons.*.label_en' => 'required|string|max:1000',
            'quotationCancelReasons.*.label_gu' => 'required|string|max:1000',
            'quotationCancelReasons.*.group' => 'nullable|string|max:100',
        ]);

        $reasons = array_map(fn ($r) => [
            'key' => $r['key'],
            'label_en' => $r['label_en'],
            'label_gu' => $r['label_gu'],
            'group' => $r['group'] ?? 'Other',
        ], array_values($validated['quotationCancelReasons']));

        $this->configService->updateSection('quotationCancelReasons', $reasons);
        ActivityLog::log('settings_updated', null, ['section' => 'quotation_cancel_reasons']);

        return back()->with('success', 'Cancel reasons updated.');
    }

    public function reset()
    {
        $this->configService->reset();
        BankCharge::truncate();
        ActivityLog::log('settings_reset');

        return back()->with('success', 'All settings reset to defaults.');
    }
}
