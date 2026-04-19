<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DailyVisitReport;
use App\Models\DisbursementDetail;
use App\Models\GeneralTask;
use App\Models\GeneralTaskComment;
use App\Models\LoanDetail;
use App\Models\Product;
use App\Models\ProductStage;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Stage;
use App\Models\User;
use App\Services\LoanConversionService;
use App\Services\LoanStageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedScreenshotLoans extends Command
{
    protected $signature = 'app:seed-screenshot-loans
        {--mode=complete : Coverage mode — "main" (minimal dataset for one-shot route screenshots) or "complete" (every stage/phase/variation — default)}';

    protected $description = 'Seed test fixtures (loans, quotations, DVRs, tasks) for the screenshot tool. Two modes: "main" (1-of-each, for per-route captures) or "complete" (every stage/phase/role handoff).';

    private string $mode = 'complete';

    private LoanConversionService $conversionService;

    private LoanStageService $stageService;

    private int $bankId;

    private int $productId;

    private int $branchId;

    private int $locationId;

    private array $roleUsers = [];

    public function handle(LoanConversionService $conversionService, LoanStageService $stageService): int
    {
        $this->conversionService = $conversionService;
        $this->stageService = $stageService;

        $mode = strtolower((string) $this->option('mode'));
        if (! in_array($mode, ['main', 'complete'], true)) {
            $this->error("Invalid --mode={$mode}. Use 'main' or 'complete'.");

            return 1;
        }
        $this->mode = $mode;
        $this->info("Mode: {$this->mode}");

        if (! $this->verifyPrerequisites()) {
            return 1;
        }
        $this->ensureUsers();
        $this->ensurePivots();
        $this->ensureProductStages();
        $this->printVerification();
        $this->cleanExistingData();

        if ($this->mode === 'main') {
            $this->createMinimalLoans();
        } else {
            $this->createAllLoans();
        }

        $this->seedQuotations();
        $this->seedDvrs();
        $this->seedTasks();
        $this->writeFixtures();

        return 0;
    }

    private function verifyPrerequisites(): bool
    {
        $bank = Bank::where('is_active', true)->first();
        $branch = Branch::where('is_active', true)->first();
        $product = $bank ? Product::where('bank_id', $bank->id)->where('is_active', true)->first() : null;

        if (! $bank || ! $branch || ! $product) {
            $this->error('Missing bank, branch, or product. Run DefaultDataSeeder first.');

            return false;
        }

        if (Stage::count() < 15) {
            $this->error('Stages not seeded. Run DefaultDataSeeder first.');

            return false;
        }

        $this->bankId = $bank->id;
        $this->productId = $product->id;
        $this->branchId = $branch->id;
        $this->locationId = $branch->location_id ?? 2;

        $this->info("Using: Bank={$bank->name}, Product={$product->name}, Branch={$branch->name}");

        return true;
    }

    private function ensureUsers(): void
    {
        $this->info("\n── Ensuring users for each role ──");

        $roles = [
            'branch_manager' => ['name' => 'Screenshot Branch Manager', 'email' => 'screenshot-bm@shf.com'],
            'bdh' => ['name' => 'Screenshot BDH', 'email' => 'screenshot-bdh@shf.com'],
            'loan_advisor' => ['name' => 'Screenshot Loan Advisor', 'email' => 'screenshot-la@shf.com'],
            'bank_employee' => ['name' => 'Screenshot Bank Employee', 'email' => 'screenshot-be@shf.com'],
            'office_employee' => ['name' => 'Screenshot Office Employee', 'email' => 'screenshot-oe@shf.com'],
        ];

        foreach ($roles as $roleSlug => $defaults) {
            $query = User::whereHas('roles', fn ($q) => $q->where('slug', $roleSlug))
                ->where('is_active', true);

            // For bank_employee, prefer one already assigned to the target bank
            if ($roleSlug === 'bank_employee') {
                $query->whereHas('employerBanks', fn ($q) => $q->where('banks.id', $this->bankId));
            }

            $user = $query->first();
            if ($user) {
                $this->roleUsers[$roleSlug] = $user;
                $this->line("  ✓ {$roleSlug}: {$user->name} (#{$user->id})");
            } else {
                $user = User::create([
                    'name' => $defaults['name'],
                    'email' => $defaults['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'default_branch_id' => $this->branchId,
                ]);
                $roleId = Role::where('slug', $roleSlug)->value('id');
                if ($roleId) {
                    $user->roles()->attach($roleId);
                }
                $this->roleUsers[$roleSlug] = $user;
                $this->line("  + Created {$roleSlug}: {$user->name} (#{$user->id})");
            }
        }

        // Super admin for advancing stages
        $this->roleUsers['super_admin'] = User::whereHas('roles', fn ($q) => $q->where('slug', 'super_admin'))
            ->first() ?? $this->roleUsers['branch_manager'];
    }

    private function ensurePivots(): void
    {
        $this->info("\n── Ensuring pivot table assignments ──");

        // user_branches for all non-bank roles
        foreach (['branch_manager', 'bdh', 'loan_advisor', 'office_employee'] as $role) {
            $user = $this->roleUsers[$role];
            DB::table('user_branches')->insertOrIgnore([
                'user_id' => $user->id, 'branch_id' => $this->branchId,
            ]);
        }

        // bank_employees for bank_employee (only if not already assigned to the target bank)
        $beUser = $this->roleUsers['bank_employee'];
        $hasBank = DB::table('bank_employees')
            ->where('user_id', $beUser->id)
            ->where('bank_id', $this->bankId)
            ->exists();
        if (! $hasBank) {
            DB::table('bank_employees')->insertOrIgnore([
                'user_id' => $beUser->id, 'bank_id' => $this->bankId, 'is_default' => false,
            ]);
        }

        // location_user for office_employee
        DB::table('location_user')->insertOrIgnore([
            'user_id' => $this->roleUsers['office_employee']->id, 'location_id' => $this->locationId,
        ]);

        $this->line('  ✓ Pivot tables verified');
    }

    private function ensureProductStages(): void
    {
        $existingCount = ProductStage::where('product_id', $this->productId)->count();
        if ($existingCount > 0) {
            return;
        }

        $stages = Stage::where('is_enabled', true)->orderBy('sequence_order')->get();
        $order = 0;
        foreach ($stages as $stage) {
            ProductStage::create([
                'product_id' => $this->productId,
                'stage_id' => $stage->id,
                'is_enabled' => true,
                'allow_skip' => false,
                'sort_order' => $order++,
            ]);
        }
        $this->info("  + Created {$stages->count()} product_stage entries");
    }

    private function cleanExistingData(): void
    {
        $this->info("\n── Cleaning existing data ──");
        $this->call('loans:purge', ['--force' => true]);

        // Purge fixture rows created by this seeder so re-runs stay idempotent.
        GeneralTaskComment::query()->delete();
        GeneralTask::query()->delete();
        DailyVisitReport::query()->delete();
        Quotation::query()->delete();
    }

    private function createAllLoans(): void
    {
        $this->info("\n── Creating loans for every stage/phase/action ──\n");

        $adminId = $this->roleUsers['super_admin']->id;
        auth()->loginUsingId($adminId);

        $loans = [
            // ── Stage 3: Document Collection (stages 1-2 auto-complete on conversion) ──
            ['name' => 'Priya Mehta', 'amount' => 5000000, 'target' => 'document_collection', 'type' => 'salaried', 'days' => 3],

            // ── Stage 4: Parallel Processing ──
            // 4a: App Number (only this starts first)
            ['name' => 'Suresh Kumar', 'amount' => 7500000, 'target' => 'parallel_app_number', 'type' => 'proprietor', 'days' => 5],
            // 4b: BSM/OSV (starts after app_number completes)
            ['name' => 'Vikram Singh', 'amount' => 6000000, 'target' => 'parallel_bsm_osv', 'type' => 'partnership_llp', 'days' => 7],
            // 4c: Legal Verification — Phase 1 (advisor, suggest legal advisor)
            ['name' => 'Divya Solanki', 'amount' => 5600000, 'target' => 'legal_phase1', 'type' => 'salaried', 'days' => 8],
            // 4c: Legal Verification — Phase 2 (bank employee, initiate legal)
            ['name' => 'Prakash Rana', 'amount' => 6200000, 'target' => 'legal_phase2', 'type' => 'pvt_ltd', 'days' => 9],
            // 4c: Legal Verification — Phase 3 (advisor, complete)
            ['name' => 'Neeta Joshi', 'amount' => 4800000, 'target' => 'legal_phase3', 'type' => 'salaried', 'days' => 9],
            // 4d: Technical Valuation (office employee, valuation form)
            ['name' => 'Ravi Chauhan', 'amount' => 5800000, 'target' => 'parallel_valuation', 'type' => 'salaried', 'days' => 8],
            // 4e: Sanction Decision — Office Employee (initial, can approve or escalate)
            ['name' => 'Neha Sharma', 'amount' => 4000000, 'target' => 'sanction_decision_oe', 'type' => 'salaried', 'days' => 9],
            // 4e: Sanction Decision — Escalated to BM
            ['name' => 'Deepak Joshi', 'amount' => 8000000, 'target' => 'sanction_decision_bm', 'type' => 'pvt_ltd', 'days' => 10],
            // 4e: Sanction Decision — Escalated to BDH
            ['name' => 'Manav Patel', 'amount' => 9200000, 'target' => 'sanction_decision_bdh', 'type' => 'proprietor', 'days' => 11],
            // 4e: Sanction Decision — only stage remaining (legal + valuation completed)
            ['name' => 'Kishan Mehta', 'amount' => 7100000, 'target' => 'sanction_decision_only', 'type' => 'salaried', 'days' => 10],

            // ── Stage 5: Rate & PF — 3 phases ──
            ['name' => 'Anita Desai', 'amount' => 4500000, 'target' => 'rate_pf_phase1', 'type' => 'salaried', 'days' => 12],
            ['name' => 'Rohit Verma', 'amount' => 5500000, 'target' => 'rate_pf_phase2', 'type' => 'proprietor', 'days' => 13],
            ['name' => 'Nilesh Prajapati', 'amount' => 4100000, 'target' => 'rate_pf_phase3', 'type' => 'salaried', 'days' => 14],

            // ── Stage 6: Sanction Letter — 3 phases ──
            ['name' => 'Meera Gupta', 'amount' => 3000000, 'target' => 'sanction_phase1', 'type' => 'salaried', 'days' => 15],
            ['name' => 'Kiran Trivedi', 'amount' => 7000000, 'target' => 'sanction_phase2', 'type' => 'proprietor', 'days' => 16],
            ['name' => 'Tushar Bhatt', 'amount' => 3600000, 'target' => 'sanction_phase3', 'type' => 'proprietor', 'days' => 17],

            // ── Stage 7: Docket — 3 phases ──
            ['name' => 'Bhavesh Modi', 'amount' => 4200000, 'target' => 'docket_phase1', 'type' => 'salaried', 'days' => 18],
            ['name' => 'Gaurav Pandya', 'amount' => 3900000, 'target' => 'docket_phase2', 'type' => 'salaried', 'days' => 19],
            ['name' => 'Hemal Trivedi', 'amount' => 4100000, 'target' => 'docket_phase3', 'type' => 'proprietor', 'days' => 20],

            // ── Stage 8: KFS ──
            ['name' => 'Sonal Parikh', 'amount' => 3800000, 'target' => 'kfs', 'type' => 'proprietor', 'days' => 21],

            // ── Stage 9: E-Sign — 4 phases ──
            ['name' => 'Jayesh Raval', 'amount' => 6500000, 'target' => 'esign_phase1', 'type' => 'salaried', 'days' => 22],
            ['name' => 'Nisha Thakor', 'amount' => 5100000, 'target' => 'esign_phase2', 'type' => 'proprietor', 'days' => 23],
            ['name' => 'Darshan Kotak', 'amount' => 4700000, 'target' => 'esign_phase3', 'type' => 'salaried', 'days' => 24],
            ['name' => 'Minal Shah', 'amount' => 5300000, 'target' => 'esign_phase4', 'type' => 'proprietor', 'days' => 25],

            // ── Stage 10: Disbursement ──
            ['name' => 'Pooja Nair', 'amount' => 9000000, 'target' => 'disbursement', 'type' => 'pvt_ltd', 'days' => 26],

            // ── Stage 11: OTC Clearance ──
            ['name' => 'Hemant Desai', 'amount' => 5200000, 'target' => 'otc_clearance', 'type' => 'salaried', 'days' => 28],

            // ── Closed loans ──
            ['name' => 'Ramesh Jain', 'amount' => 4800000, 'target' => 'completed_fund', 'type' => 'salaried', 'days' => 30],
            ['name' => 'Kavita Soni', 'amount' => 3200000, 'target' => 'completed_cheque', 'type' => 'proprietor', 'days' => 35],
            ['name' => 'Manish Vyas', 'amount' => 6100000, 'target' => 'rejected_at_sanction_decision', 'type' => 'pvt_ltd', 'days' => 15],
            ['name' => 'Sunil Doshi', 'amount' => 2800000, 'target' => 'on_hold', 'type' => 'salaried', 'days' => 10],
            ['name' => 'Pallavi Shah', 'amount' => 3300000, 'target' => 'cancelled', 'type' => 'proprietor', 'days' => 4],
        ];

        foreach ($loans as $i => $def) {
            try {
                $loan = $this->conversionService->createDirectLoan([
                    'customer_name' => $def['name'],
                    'customer_type' => $def['type'],
                    'loan_amount' => $def['amount'],
                    'bank_id' => $this->bankId,
                    'product_id' => $this->productId,
                    'branch_id' => $this->branchId,
                    'assigned_advisor' => $this->roleUsers['loan_advisor']->id,
                    'notes' => "Screenshot loan — target: {$def['target']}",
                ]);

                $this->advanceToTarget($loan, $def['target'], $adminId, $i);
                $this->backdateLoan($loan, $def['days']);

                $loan->refresh();
                $this->line(sprintf('  %2d. %-22s → %-30s [%s]', $i + 1, $def['name'], $def['target'], $loan->loan_number));
            } catch (\Throwable $e) {
                $this->error("  ✗ #{$i}: {$def['name']} — {$e->getMessage()}");
            }
        }

        // Write loan-stage-map.json for the screenshot script
        $activeLoans = LoanDetail::where('status', LoanDetail::STATUS_ACTIVE)->orderBy('id')->get();
        $map = [];
        foreach ($activeLoans as $loan) {
            preg_match('/target: (.+)/', $loan->notes, $m);
            $map[$loan->id] = $m[1] ?? $loan->current_stage;
        }
        $mapDir = base_path('screenshots');
        if (! is_dir($mapDir)) {
            mkdir($mapDir, 0755, true);
        }
        file_put_contents("{$mapDir}/loan-stage-map.json", json_encode($map, JSON_PRETTY_PRINT));
        $this->line("\n✓ Wrote screenshots/loan-stage-map.json (".count($map).' active loans)');

        $this->info("\n✅ Done! Created ".LoanDetail::count().' loans.');
    }

    // ── Advance Logic ──

    private function advanceToTarget(LoanDetail $loan, string $target, int $userId, int $index): void
    {
        // ── Sequential early stages ──
        if (in_array($target, ['inquiry', 'document_selection', 'document_collection'])) {
            $this->advanceThrough($loan, $target, $userId);
            $this->reassignStageToRole($loan, $target);

            return;
        }

        // ── Stage 4a: App Number in progress ──
        if ($target === 'parallel_app_number') {
            $this->advanceThrough($loan, 'parallel_processing', $userId);
            $loan->getStageAssignment('app_number')?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);

            return;
        }

        // ── Stage 4b: BSM/OSV in progress ──
        if ($target === 'parallel_bsm_osv') {
            $this->advanceToParallelWithAppDone($loan, $userId, $index);
            // Only bsm_osv should be in_progress now
            $loan->getStageAssignment('bsm_osv')?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);

            return;
        }

        // ── Stage 4c: Legal Verification phases ──
        if (str_starts_with($target, 'legal_phase')) {
            $this->advanceToParallelWithBsmDone($loan, $userId, $index);
            $phase = substr($target, -1); // 1, 2, or 3
            $assignment = $loan->getStageAssignment('legal_verification');
            if ($phase === '1') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['legal_phase' => '1']);
            } elseif ($phase === '2') {
                $assignment?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);
                $assignment?->mergeNotesData(['legal_phase' => '2', 'suggested_legal_advisor' => 'Advocate Sharma', 'legal_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            } elseif ($phase === '3') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['legal_phase' => '3', 'suggested_legal_advisor' => 'Advocate Sharma', 'confirmed_legal_advisor' => 'Advocate Sharma']);
            }

            return;
        }

        // ── Stage 4d: Technical Valuation ──
        if ($target === 'parallel_valuation') {
            $this->advanceToParallelWithBsmDone($loan, $userId, $index);
            $loan->getStageAssignment('technical_valuation')?->update(['assigned_to' => $this->roleUsers['office_employee']->id]);

            return;
        }

        // ── Stage 4e: Sanction Decision ──
        if (str_starts_with($target, 'sanction_decision_')) {
            $this->advanceToParallelWithBsmDone($loan, $userId, $index);
            $assignment = $loan->getStageAssignment('sanction_decision');
            $variant = str_replace('sanction_decision_', '', $target);

            if ($variant === 'only') {
                // Complete legal_verification + technical_valuation, leave only sanction_decision
                $this->forceComplete($loan, 'legal_verification', $userId);
                $this->forceComplete($loan, 'technical_valuation', $userId);
                $loan->refresh();
                $assignment?->update(['assigned_to' => $this->roleUsers['office_employee']->id]);

                return;
            }

            if ($variant === 'oe') {
                // Office employee — initial assignee
                $assignment?->update(['assigned_to' => $this->roleUsers['office_employee']->id]);
            } elseif ($variant === 'bm') {
                // Escalated to Branch Manager
                $assignment?->update(['assigned_to' => $this->roleUsers['branch_manager']->id]);
                $assignment?->mergeNotesData([
                    'escalation_history' => [[
                        'from_user_id' => $this->roleUsers['office_employee']->id,
                        'from_user_name' => $this->roleUsers['office_employee']->name,
                        'to_role' => 'branch_manager',
                        'remarks' => 'High value loan — needs BM approval',
                        'date' => now()->subDays(1)->toDateTimeString(),
                    ]],
                    'decision_remarks' => 'High value loan — needs BM approval',
                ]);
            } elseif ($variant === 'bdh') {
                // Escalated to BDH (through BM)
                $assignment?->update(['assigned_to' => $this->roleUsers['bdh']->id]);
                $assignment?->mergeNotesData([
                    'escalation_history' => [
                        [
                            'from_user_id' => $this->roleUsers['office_employee']->id,
                            'from_user_name' => $this->roleUsers['office_employee']->name,
                            'to_role' => 'branch_manager',
                            'remarks' => 'Exceeds branch limit',
                            'date' => now()->subDays(2)->toDateTimeString(),
                        ],
                        [
                            'from_user_id' => $this->roleUsers['branch_manager']->id,
                            'from_user_name' => $this->roleUsers['branch_manager']->name,
                            'to_role' => 'bdh',
                            'remarks' => 'Needs BDH final approval — special case',
                            'date' => now()->subDays(1)->toDateTimeString(),
                        ],
                    ],
                    'decision_remarks' => 'Needs BDH final approval — special case',
                ]);
            }

            return;
        }

        // ── Stage 5: Rate & PF phases ──
        if (str_starts_with($target, 'rate_pf_phase')) {
            $this->advanceThroughParallel($loan, $userId, $index);
            $this->advanceThrough($loan, 'rate_pf', $userId);
            $phase = substr($target, -1);
            $assignment = $loan->getStageAssignment('rate_pf');

            $ratePfNotes = [
                'interest_rate' => '8.50', 'repo_rate' => '6.50', 'bank_rate' => '2.00',
                'rate_offered_date' => now()->format('d/m/Y'), 'rate_valid_until' => now()->addDays(15)->format('d/m/Y'),
                'bank_reference' => 'REF-2026-'.str_pad($index, 4, '0', STR_PAD_LEFT),
                'processing_fee' => '0.50', 'admin_charges' => '5000', 'processing_fee_gst' => '900', 'total_pf' => '5900',
            ];
            $assignment?->mergeNotesData($ratePfNotes);

            if ($phase === '1') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['rate_pf_phase' => '1']);
            } elseif ($phase === '2') {
                $assignment?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);
                $assignment?->mergeNotesData(['rate_pf_phase' => '2', 'rate_pf_original_assignee' => $this->roleUsers['loan_advisor']->id, 'original_values' => $ratePfNotes]);
            } elseif ($phase === '3') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['rate_pf_phase' => '3', 'rate_pf_original_assignee' => $this->roleUsers['loan_advisor']->id, 'original_values' => $ratePfNotes]);
            }

            return;
        }

        // ── Stage 6: Sanction Letter phases ──
        if (str_starts_with($target, 'sanction_phase')) {
            $this->advanceThroughParallel($loan, $userId, $index);
            $this->completeRatePf($loan, $userId, $index);
            $this->advanceThrough($loan, 'sanction', $userId);
            $phase = substr($target, -1);
            $assignment = $loan->getStageAssignment('sanction');

            if ($phase === '1') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['sanction_phase' => '1']);
            } elseif ($phase === '2') {
                $assignment?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);
                $assignment?->mergeNotesData(['sanction_phase' => '2', 'sanction_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            } elseif ($phase === '3') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['sanction_phase' => '3', 'sanction_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            }

            return;
        }

        // ── Stage 7: Docket phases ──
        if (str_starts_with($target, 'docket_phase')) {
            $this->advanceThroughParallel($loan, $userId, $index);
            $this->completeRatePf($loan, $userId, $index);
            $this->completeSanction($loan, $userId, $index);
            $this->advanceThrough($loan, 'docket', $userId);
            $phase = substr($target, -1);
            $assignment = $loan->getStageAssignment('docket');

            if ($phase === '1') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['docket_phase' => '1']);
            } elseif ($phase === '2') {
                $assignment?->update(['assigned_to' => $this->roleUsers['office_employee']->id]);
                $assignment?->mergeNotesData(['docket_phase' => '2', 'login_date' => now()->format('d/m/Y'), 'docket_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            } elseif ($phase === '3') {
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['docket_phase' => '3', 'login_date' => now()->format('d/m/Y'), 'docket_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            }

            return;
        }

        // ── Stage 8: KFS ──
        if ($target === 'kfs') {
            $this->advanceToStage($loan, 'kfs', $userId, $index);
            $loan->getStageAssignment('kfs')?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);

            return;
        }

        // ── Stage 9: E-Sign phases (4 phases) ──
        if (str_starts_with($target, 'esign_phase')) {
            $this->advanceToStage($loan, 'esign', $userId, $index);
            $phase = substr($target, -1);
            $assignment = $loan->getStageAssignment('esign');

            if ($phase === '1') {
                // Phase 1: Advisor sends to bank
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['esign_phase' => '1']);
            } elseif ($phase === '2') {
                // Phase 2: Bank generates docs
                $assignment?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);
                $assignment?->mergeNotesData(['esign_phase' => '2', 'esign_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            } elseif ($phase === '3') {
                // Phase 3: Advisor completes with customer
                $assignment?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);
                $assignment?->mergeNotesData(['esign_phase' => '3', 'esign_bank_employee' => $this->roleUsers['bank_employee']->id, 'esign_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            } elseif ($phase === '4') {
                // Phase 4: Bank confirms
                $assignment?->update(['assigned_to' => $this->roleUsers['bank_employee']->id]);
                $assignment?->mergeNotesData(['esign_phase' => '4', 'esign_bank_employee' => $this->roleUsers['bank_employee']->id, 'esign_original_assignee' => $this->roleUsers['loan_advisor']->id]);
            }

            return;
        }

        // ── Stage 10: Disbursement ──
        if ($target === 'disbursement') {
            $this->advanceToStage($loan, 'disbursement', $userId, $index);
            $loan->getStageAssignment('disbursement')?->update(['assigned_to' => $this->roleUsers['loan_advisor']->id]);

            return;
        }

        // ── Stage 11: OTC Clearance ──
        if ($target === 'otc_clearance') {
            $this->advanceToStage($loan, 'disbursement', $userId, $index);
            DisbursementDetail::create([
                'loan_id' => $loan->id, 'disbursement_type' => 'cheque',
                'disbursement_date' => now(), 'amount_disbursed' => $loan->loan_amount,
                'cheques' => [['cheque_number' => 'CHQ-001', 'cheque_date' => now()->format('d/m/Y'), 'cheque_amount' => $loan->loan_amount]],
            ]);
            $this->stageService->updateStageStatus($loan, 'disbursement', 'completed', $userId);
            $loan->refresh();
            $loan->getStageAssignment('otc_clearance')?->update(['assigned_to' => $this->roleUsers['office_employee']->id]);

            return;
        }

        // ── Completed (fund transfer) ──
        if ($target === 'completed_fund') {
            $this->advanceToStage($loan, 'disbursement', $userId, $index);
            DisbursementDetail::create([
                'loan_id' => $loan->id, 'disbursement_type' => 'fund_transfer',
                'disbursement_date' => now(), 'amount_disbursed' => $loan->loan_amount,
                'bank_account_number' => '1234567890',
            ]);
            $this->stageService->updateStageStatus($loan, 'disbursement', 'completed', $userId);

            return;
        }

        // ── Completed (cheque + OTC) ──
        if ($target === 'completed_cheque') {
            $this->advanceToStage($loan, 'disbursement', $userId, $index);
            DisbursementDetail::create([
                'loan_id' => $loan->id, 'disbursement_type' => 'cheque',
                'disbursement_date' => now(), 'amount_disbursed' => $loan->loan_amount,
                'cheques' => [['cheque_number' => 'CHQ-002', 'cheque_date' => now()->format('d/m/Y'), 'cheque_amount' => $loan->loan_amount]],
            ]);
            $this->stageService->updateStageStatus($loan, 'disbursement', 'completed', $userId);
            $loan->refresh();
            $loan->getStageAssignment('otc_clearance')?->mergeNotesData(['handover_date' => now()->format('d/m/Y')]);
            $this->forceComplete($loan, 'otc_clearance', $userId);

            return;
        }

        // ── Rejected at sanction decision ──
        if ($target === 'rejected_at_sanction_decision') {
            $this->advanceToParallelWithBsmDone($loan, $userId, $index);
            $loan->refresh();
            $loan->forceFill([
                'status' => LoanDetail::STATUS_REJECTED,
                'rejected_at' => now(), 'rejected_by' => $this->roleUsers['bdh']->id,
                'rejected_stage' => 'sanction_decision',
                'rejection_reason' => 'Property valuation below threshold — loan amount exceeds 80% LTV ratio. Customer unable to provide additional collateral.',
            ])->save();
            // Reject all stages
            $loan->stageAssignments()->whereIn('status', ['pending', 'in_progress'])
                ->update(['status' => 'rejected', 'completed_at' => now(), 'completed_by' => $userId]);

            return;
        }

        // ── On Hold ──
        if ($target === 'on_hold') {
            $this->advanceThrough($loan, 'document_collection', $userId);
            $loan->update([
                'status' => LoanDetail::STATUS_ON_HOLD,
                'status_reason' => 'Customer requested hold — awaiting property documents from municipal office',
                'status_changed_at' => now(), 'status_changed_by' => $userId,
            ]);

            return;
        }

        // ── Cancelled ──
        if ($target === 'cancelled') {
            $this->advanceThrough($loan, 'inquiry', $userId);
            $loan->update([
                'status' => LoanDetail::STATUS_CANCELLED,
                'status_reason' => 'Customer withdrew — opted for another institution',
                'status_changed_at' => now(), 'status_changed_by' => $userId,
            ]);

            return;
        }
    }

    // ── Helper Methods ──

    /**
     * Advance through all parallel sub-stages (complete everything including sanction_decision with is_sanctioned).
     */
    private function advanceThroughParallel(LoanDetail $loan, int $userId, int $index): void
    {
        $this->advanceThrough($loan, 'parallel_processing', $userId);
        $this->setAppNumberNotes($loan, $index);

        // 4a: app_number
        $this->forceComplete($loan, 'app_number', $userId);
        // 4b: bsm_osv (starts after app_number)
        $this->forceComplete($loan, 'bsm_osv', $userId);
        // 4c, 4d, 4e: start after bsm_osv
        $this->forceComplete($loan, 'legal_verification', $userId);
        $this->forceComplete($loan, 'technical_valuation', $userId);

        // Sanction decision: approve → sets is_sanctioned
        $sdAssignment = $loan->getStageAssignment('sanction_decision');
        if ($sdAssignment && ! in_array($sdAssignment->status, ['completed',
            'skipped'])) {
            $loan->update(['is_sanctioned' => true]);
            $sdAssignment->mergeNotesData(['decision_action' => 'approved', 'decided_by' => $userId]);
            $this->forceComplete($loan, 'sanction_decision', $userId);
        }

        $loan->refresh();
    }

    /**
     * Advance through parallel with only app_number done (bsm_osv in progress).
     */
    private function advanceToParallelWithAppDone(LoanDetail $loan, int $userId, int $index): void
    {
        $this->advanceThrough($loan, 'parallel_processing', $userId);
        $this->setAppNumberNotes($loan, $index);
        $this->forceComplete($loan, 'app_number', $userId);
        $loan->refresh();
    }

    /**
     * Advance through parallel with app_number + bsm_osv done (4c/4d/4e in progress).
     */
    private function advanceToParallelWithBsmDone(LoanDetail $loan, int $userId, int $index): void
    {
        $this->advanceToParallelWithAppDone($loan, $userId, $index);
        $this->forceComplete($loan, 'bsm_osv', $userId);
        $loan->refresh();
    }

    /**
     * Advance to a specific post-parallel stage (completes all parallel + rate_pf + sanction etc as needed).
     */
    private function advanceToStage(LoanDetail $loan, string $targetStage, int $userId, int $index): void
    {
        $postParallelStages = ['rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance'];

        if (in_array($targetStage, $postParallelStages)) {
            $this->advanceThroughParallel($loan, $userId, $index);
        }

        $stageOrder = ['rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance'];
        foreach ($stageOrder as $stage) {
            if ($stage === $targetStage) {
                break;
            }
            if ($stage === 'rate_pf') {
                $this->completeRatePf($loan, $userId, $index);
            } elseif ($stage === 'sanction') {
                $this->completeSanction($loan, $userId, $index);
            } elseif ($stage === 'docket') {
                $this->completeDocket($loan, $userId);
            } else {
                $this->forceComplete($loan, $stage, $userId);
            }
        }

        // Ensure target is in_progress
        $loan->refresh();
        $assignment = $loan->getStageAssignment($targetStage);
        if ($assignment && $assignment->status === 'pending') {
            $this->stageService->updateStageStatus($loan, $targetStage, 'in_progress', $userId);
            $loan->refresh();
        }
    }

    private function completeRatePf(LoanDetail $loan, int $userId, int $index): void
    {
        $assignment = $loan->getStageAssignment('rate_pf');
        if (! $assignment || in_array($assignment->status, ['completed',
            'skipped'])) {
            return;
        }

        // Ensure in_progress
        if ($assignment->status === 'pending') {
            $this->stageService->updateStageStatus($loan, 'rate_pf', 'in_progress', $userId);
            $loan->refresh();
            $assignment = $loan->getStageAssignment('rate_pf');
        }

        $assignment->mergeNotesData([
            'rate_pf_phase' => '3',
            'interest_rate' => '8.50', 'repo_rate' => '6.50', 'bank_rate' => '2.00',
            'rate_offered_date' => now()->format('d/m/Y'), 'rate_valid_until' => now()->addDays(15)->format('d/m/Y'),
            'bank_reference' => 'REF-'.str_pad($index, 4, '0', STR_PAD_LEFT),
            'processing_fee' => '0.50', 'admin_charges' => '5000', 'processing_fee_gst' => '900', 'total_pf' => '5900',
        ]);

        $this->stageService->updateStageStatus($loan, 'rate_pf', 'completed', $userId);
        $loan->refresh();
    }

    private function completeSanction(LoanDetail $loan, int $userId, int $index): void
    {
        $assignment = $loan->getStageAssignment('sanction');
        if (! $assignment || in_array($assignment->status, ['completed',
            'skipped'])) {
            return;
        }

        if ($assignment->status === 'pending') {
            $this->stageService->updateStageStatus($loan, 'sanction', 'in_progress', $userId);
            $loan->refresh();
            $assignment = $loan->getStageAssignment('sanction');
        }

        $assignment->mergeNotesData([
            'sanction_phase' => '3',
            'sanction_date' => now()->format('d/m/Y'),
            'sanctioned_amount' => (string) $loan->loan_amount,
            'sanctioned_rate' => '8.50',
            'emi_amount' => (string) round($loan->loan_amount * 0.01),
        ]);

        $this->stageService->updateStageStatus($loan, 'sanction', 'completed', $userId);
        $loan->refresh();
    }

    private function completeDocket(LoanDetail $loan, int $userId): void
    {
        $assignment = $loan->getStageAssignment('docket');
        if (! $assignment || in_array($assignment->status, ['completed',
            'skipped'])) {
            return;
        }

        if ($assignment->status === 'pending') {
            $this->stageService->updateStageStatus($loan, 'docket', 'in_progress', $userId);
            $loan->refresh();
            $assignment = $loan->getStageAssignment('docket');
        }

        $assignment->mergeNotesData([
            'docket_phase' => '3',
            'login_date' => now()->format('d/m/Y'),
        ]);

        $this->stageService->updateStageStatus($loan, 'docket', 'completed', $userId);
        $loan->refresh();
    }

    private function advanceThrough(LoanDetail $loan, string $targetStage, int $userId): void
    {
        $sequentialStages = [
            'inquiry', 'document_selection', 'document_collection', 'parallel_processing',
        ];

        foreach ($sequentialStages as $stage) {
            if ($stage === $targetStage) {
                break;
            }
            $this->forceComplete($loan, $stage, $userId);
        }
    }

    private function forceComplete(LoanDetail $loan, string $stageKey, int $userId): void
    {
        $assignment = $loan->getStageAssignment($stageKey);
        if (! $assignment || in_array($assignment->status, ['completed',
            'skipped'])) {
            return;
        }

        if ($assignment->status === 'pending') {
            $this->stageService->updateStageStatus($loan, $stageKey, 'in_progress', $userId);
            $loan->refresh();
        }

        // Populate realistic stage data before completing
        $this->populateStageData($loan, $stageKey, $userId);

        $assignment = $loan->getStageAssignment($stageKey);
        if ($assignment && $assignment->status === 'in_progress') {
            $this->stageService->updateStageStatus($loan, $stageKey, 'completed', $userId);
            $loan->refresh();
        }
    }

    /**
     * Populate realistic data for a stage before it gets force-completed.
     */
    private function populateStageData(LoanDetail $loan, string $stageKey, int $userId): void
    {
        switch ($stageKey) {
            case 'document_collection':
                // Mark all documents as received
                $loan->documents()->where('status', 'pending')->each(function ($doc) use ($userId) {
                    $doc->update([
                        'status' => 'received',
                        'received_date' => now()->subDays(rand(1, 5)),
                        'received_by' => $userId,
                    ]);
                });
                break;

            case 'app_number':
                $assignment = $loan->getStageAssignment('app_number');
                if ($assignment) {
                    $notes = $assignment->getNotesData();
                    if (empty($notes['application_number'])) {
                        $assignment->mergeNotesData([
                            'application_number' => 'HL'.date('Y').str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                            'docket_days_offset' => '2',
                            'stageRemarks' => 'Application submitted',
                        ]);
                        $loan->update(['application_number' => $assignment->getNotesData()['application_number']]);
                    }
                }
                break;

            case 'legal_verification':
                $assignment = $loan->getStageAssignment('legal_verification');
                if ($assignment) {
                    $assignment->mergeNotesData([
                        'legal_phase' => '3',
                        'suggested_legal_advisor' => 'Advocate R. Sharma',
                        'confirmed_legal_advisor' => 'Advocate R. Sharma',
                        'legal_original_assignee' => $this->roleUsers['loan_advisor']->id,
                    ]);
                }
                break;

            case 'technical_valuation':
                // Create valuation details if not exists
                if (! $loan->valuationDetails()->exists()) {
                    \App\Models\ValuationDetail::create([
                        'loan_id' => $loan->id,
                        'valuation_type' => 'property',
                        'property_type' => 'residential_bunglow',
                        'property_address' => 'Plot No. '.rand(1, 500).', Rajkot, Gujarat',
                        'latitude' => '22.3039',
                        'longitude' => '70.8022',
                        'land_area' => (string) rand(100, 500),
                        'land_rate' => rand(5000, 15000),
                        'land_valuation' => rand(500000, 5000000),
                        'construction_area' => (string) rand(80, 300),
                        'construction_rate' => rand(1000, 3000),
                        'construction_valuation' => rand(200000, 900000),
                        'final_valuation' => rand(700000, 6000000),
                        'market_value' => rand(700000, 6000000),
                        'valuator_name' => 'Mr. Patel',
                    ]);
                }
                break;

            case 'sanction_decision':
                $assignment = $loan->getStageAssignment('sanction_decision');
                if ($assignment) {
                    $assignment->mergeNotesData(['decision_action' => 'approved', 'decided_by' => $userId]);
                    $loan->update(['is_sanctioned' => true]);
                }
                break;

            case 'docket':
                $assignment = $loan->getStageAssignment('docket');
                if ($assignment) {
                    $assignment->mergeNotesData([
                        'docket_phase' => '3',
                        'login_date' => now()->format('d/m/Y'),
                        'docket_original_assignee' => $this->roleUsers['loan_advisor']->id,
                    ]);
                }
                break;

            case 'esign':
                $assignment = $loan->getStageAssignment('esign');
                if ($assignment) {
                    $assignment->mergeNotesData([
                        'esign_phase' => '4',
                        'esign_original_assignee' => $this->roleUsers['loan_advisor']->id,
                        'esign_bank_employee' => $this->roleUsers['bank_employee']->id,
                    ]);
                }
                break;

            case 'otc_clearance':
                $assignment = $loan->getStageAssignment('otc_clearance');
                if ($assignment) {
                    $assignment->mergeNotesData(['handover_date' => now()->format('d/m/Y')]);
                }
                break;
        }
    }

    private function reassignStageToRole(LoanDetail $loan, string $stageKey): void
    {
        $roleMap = [
            'inquiry' => 'loan_advisor', 'document_selection' => 'loan_advisor',
            'document_collection' => 'loan_advisor', 'disbursement' => 'loan_advisor',
        ];

        $role = $roleMap[$stageKey] ?? null;
        if ($role && isset($this->roleUsers[$role])) {
            $loan->getStageAssignment($stageKey)?->update(['assigned_to' => $this->roleUsers[$role]->id]);
        }
    }

    private function setAppNumberNotes(LoanDetail $loan, int $index): void
    {
        $assignment = $loan->getStageAssignment('app_number');
        if (! $assignment) {
            return;
        }

        $appNum = 'HL2026'.str_pad($index + 1, 5, '0', STR_PAD_LEFT);
        $offsets = [1, 2, 3, 2, 1, 3, 2, 1, 3, 2];
        $offset = (string) ($offsets[$index % count($offsets)] ?? 2);

        $assignment->mergeNotesData([
            'application_number' => $appNum,
            'docket_days_offset' => $offset,
            'stageRemarks' => 'Application submitted',
        ]);

        $loan->update(['application_number' => $appNum]);
    }

    private function backdateLoan(LoanDetail $loan, int $daysAgo): void
    {
        $createdAt = now()->subDays($daysAgo);
        LoanDetail::withoutTimestamps(fn () => $loan->forceFill(['created_at' => $createdAt])->save());

        $assignments = $loan->stageAssignments()->orderBy('started_at')->orderBy('id')->get();
        $completedStages = $assignments->whereNotNull('started_at');

        if ($completedStages->isEmpty()) {
            return;
        }

        $totalHours = $createdAt->diffInHours(now());
        $stageCount = $completedStages->count();
        $hoursPerStage = $stageCount > 1 ? $totalHours / $stageCount : $totalHours;

        $idx = 0;
        foreach ($completedStages as $assignment) {
            $start = $createdAt->copy()->addHours((int) ($idx * $hoursPerStage));
            $end = $createdAt->copy()->addHours((int) (($idx + 1) * $hoursPerStage));

            $update = ['started_at' => $start];
            if ($assignment->completed_at) {
                $update['completed_at'] = $end;
            }
            $assignment->timestamps = false;
            $assignment->update($update);
            $idx++;
        }
    }

    private function printVerification(): void
    {
        $this->info("\n── Role Users ──");
        $this->table(
            ['Role', 'User', 'ID'],
            collect($this->roleUsers)->map(fn ($u, $role) => [$role, $u->name, $u->id])->values()->toArray()
        );
    }

    /**
     * Minimal seed for "main" mode — one loan mid-workflow, enough to populate every `/loans/{id}/...` route.
     */
    private function createMinimalLoans(): void
    {
        $this->info("\n── Creating minimal fixture loan (main mode) ──");

        $adminId = $this->roleUsers['super_admin']->id;
        auth()->loginUsingId($adminId);

        $loans = [
            ['name' => 'Screenshot Ravi Chauhan', 'amount' => 5800000, 'target' => 'rate_pf_phase2', 'type' => 'salaried', 'days' => 12],
            ['name' => 'Screenshot Ramesh Jain', 'amount' => 4800000, 'target' => 'completed_fund', 'type' => 'salaried', 'days' => 30],
        ];

        foreach ($loans as $i => $def) {
            try {
                $loan = $this->conversionService->createDirectLoan([
                    'customer_name' => $def['name'],
                    'customer_type' => $def['type'],
                    'loan_amount' => $def['amount'],
                    'bank_id' => $this->bankId,
                    'product_id' => $this->productId,
                    'branch_id' => $this->branchId,
                    'assigned_advisor' => $this->roleUsers['loan_advisor']->id,
                    'notes' => "Screenshot loan — target: {$def['target']}",
                ]);
                $this->advanceToTarget($loan, $def['target'], $adminId, $i);
                $this->backdateLoan($loan, $def['days']);
                $loan->refresh();
                $this->line(sprintf('  %2d. %-28s → %-24s [%s]', $i + 1, $def['name'], $def['target'], $loan->loan_number));
            } catch (\Throwable $e) {
                $this->error("  ✗ #{$i}: {$def['name']} — {$e->getMessage()}");
            }
        }

        $activeLoans = LoanDetail::where('status', LoanDetail::STATUS_ACTIVE)->orderBy('id')->get();
        $map = [];
        foreach ($activeLoans as $loan) {
            preg_match('/target: (.+)/', $loan->notes, $m);
            $map[$loan->id] = $m[1] ?? $loan->current_stage;
        }
        $mapDir = base_path('screenshots');
        if (! is_dir($mapDir)) {
            mkdir($mapDir, 0755, true);
        }
        file_put_contents("{$mapDir}/loan-stage-map.json", json_encode($map, JSON_PRETTY_PRINT));
    }

    /**
     * Seed representative quotations. Main mode = 1 active. Complete = active + on_hold + cancelled.
     */
    private function seedQuotations(): void
    {
        $this->info("\n── Seeding quotations ──");

        $advisor = $this->roleUsers['loan_advisor'];
        $admin = $this->roleUsers['super_admin'];
        $branchId = $this->branchId;

        $rows = [
            [
                'user_id' => $advisor->id,
                'customer_name' => 'Screenshot Asha Patel',
                'customer_type' => 'salaried',
                'loan_amount' => 3500000,
                'prepared_by_name' => $advisor->name,
                'prepared_by_mobile' => '9876543210',
                'selected_tenures' => [10, 15, 20],
                'branch_id' => $branchId,
                'status' => Quotation::STATUS_ACTIVE,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        if ($this->mode === 'complete') {
            $rows[] = [
                'user_id' => $advisor->id,
                'customer_name' => 'Screenshot Vijay Mehra',
                'customer_type' => 'proprietor',
                'loan_amount' => 6000000,
                'prepared_by_name' => $advisor->name,
                'prepared_by_mobile' => '9876543211',
                'selected_tenures' => [5, 10, 15],
                'branch_id' => $branchId,
                'status' => Quotation::STATUS_ON_HOLD,
                'hold_reason_key' => 'rate_too_high',
                'hold_note' => 'Customer negotiating better rate at another bank.',
                'hold_follow_up_date' => now()->addDays(7)->toDateString(),
                'held_at' => now()->subDays(2),
                'held_by' => $admin->id,
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(2),
            ];
            $rows[] = [
                'user_id' => $advisor->id,
                'customer_name' => 'Screenshot Nisha Oza',
                'customer_type' => 'salaried',
                'loan_amount' => 2800000,
                'prepared_by_name' => $advisor->name,
                'prepared_by_mobile' => '9876543212',
                'selected_tenures' => [15, 20],
                'branch_id' => $branchId,
                'status' => Quotation::STATUS_CANCELLED,
                'cancel_reason_key' => 'customer_withdrew',
                'cancel_note' => 'Customer opted for another institution.',
                'cancelled_at' => now()->subDays(4),
                'cancelled_by' => $admin->id,
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(4),
            ];
        }

        foreach ($rows as $row) {
            $q = Quotation::create($row);
            $this->line("  + Quotation #{$q->id}: {$q->customer_name} [{$q->status}]");
        }
    }

    /**
     * Seed representative DVRs. Main = 1. Complete = regular, pending follow-up, chained follow-up.
     */
    private function seedDvrs(): void
    {
        $this->info("\n── Seeding DVRs ──");

        $advisor = $this->roleUsers['loan_advisor'];
        $branchId = $this->branchId;

        $first = DailyVisitReport::create([
            'user_id' => $advisor->id,
            'visit_date' => now()->subDays(5)->toDateString(),
            'contact_name' => 'Screenshot CA Paresh Shah',
            'contact_phone' => '9876500001',
            'contact_type' => 'CA',
            'purpose' => 'relationship',
            'notes' => 'Introduced new housing-loan product; CA to refer 2-3 salaried clients next week.',
            'outcome' => 'Positive — will refer leads.',
            'follow_up_needed' => false,
            'is_follow_up_done' => false,
            'branch_id' => $branchId,
        ]);
        $this->line("  + DVR #{$first->id} (no follow-up)");

        if ($this->mode === 'complete') {
            $followUpPending = DailyVisitReport::create([
                'user_id' => $advisor->id,
                'visit_date' => now()->subDays(3)->toDateString(),
                'contact_name' => 'Screenshot Rakesh Builder',
                'contact_phone' => '9876500002',
                'contact_type' => 'builder/developer',
                'purpose' => 'new_lead',
                'notes' => 'Met at site; shared brochure. Follow-up for site visit.',
                'outcome' => 'Interested, site visit needed.',
                'follow_up_needed' => true,
                'follow_up_date' => now()->addDays(2)->toDateString(),
                'follow_up_notes' => 'Site visit + valuation estimate',
                'is_follow_up_done' => false,
                'branch_id' => $branchId,
            ]);
            $this->line("  + DVR #{$followUpPending->id} (follow-up pending)");

            $chainParent = DailyVisitReport::create([
                'user_id' => $advisor->id,
                'visit_date' => now()->subDays(10)->toDateString(),
                'contact_name' => 'Screenshot Hemant Desai',
                'contact_phone' => '9876500003',
                'contact_type' => 'new_customer',
                'purpose' => 'new_lead',
                'notes' => 'Interested in HL up to 45L.',
                'outcome' => 'Docs pending.',
                'follow_up_needed' => true,
                'follow_up_date' => now()->subDays(7)->toDateString(),
                'follow_up_notes' => 'Collect salary slips + bank statements.',
                'is_follow_up_done' => true,
                'branch_id' => $branchId,
            ]);
            $chainChild = DailyVisitReport::create([
                'user_id' => $advisor->id,
                'visit_date' => now()->subDays(7)->toDateString(),
                'contact_name' => 'Screenshot Hemant Desai',
                'contact_phone' => '9876500003',
                'contact_type' => 'new_customer',
                'purpose' => 'document_collection',
                'notes' => 'Collected docs; quotation generated next day.',
                'outcome' => 'Quotation generated.',
                'follow_up_needed' => false,
                'is_follow_up_done' => false,
                'parent_visit_id' => $chainParent->id,
                'branch_id' => $branchId,
            ]);
            $chainParent->update(['follow_up_visit_id' => $chainChild->id]);
            $this->line("  + DVR #{$chainParent->id} → #{$chainChild->id} (visit chain)");
        }
    }

    /**
     * Seed representative general tasks. Main = 1 pending. Complete = pending + in_progress (with comments) + completed.
     */
    private function seedTasks(): void
    {
        $this->info("\n── Seeding general tasks ──");

        $admin = $this->roleUsers['super_admin'];
        $advisor = $this->roleUsers['loan_advisor'];

        $pending = GeneralTask::create([
            'title' => 'Screenshot — Prepare weekly branch report',
            'description' => 'Compile quotations + loans summary for the week.',
            'created_by' => $admin->id,
            'assigned_to' => $advisor->id,
            'status' => GeneralTask::STATUS_PENDING,
            'priority' => GeneralTask::PRIORITY_NORMAL,
            'due_date' => now()->addDays(3)->toDateString(),
        ]);
        $this->line("  + Task #{$pending->id} (pending)");

        if ($this->mode === 'complete') {
            $inProgress = GeneralTask::create([
                'title' => 'Screenshot — Follow up on pending legal verifications',
                'description' => 'Three loans stuck at legal phase 2 — chase bank employee.',
                'created_by' => $admin->id,
                'assigned_to' => $advisor->id,
                'status' => GeneralTask::STATUS_IN_PROGRESS,
                'priority' => GeneralTask::PRIORITY_HIGH,
                'due_date' => now()->addDays(1)->toDateString(),
            ]);
            GeneralTaskComment::create([
                'general_task_id' => $inProgress->id,
                'user_id' => $advisor->id,
                'body' => 'Called advocate; awaiting legal report by EOD.',
            ]);
            GeneralTaskComment::create([
                'general_task_id' => $inProgress->id,
                'user_id' => $admin->id,
                'body' => 'Please escalate to BM if report not received by tomorrow.',
            ]);
            $this->line("  + Task #{$inProgress->id} (in_progress, 2 comments)");

            $completed = GeneralTask::create([
                'title' => 'Screenshot — Month-end valuation audit',
                'description' => 'Reconcile valuation reports for March disbursements.',
                'created_by' => $admin->id,
                'assigned_to' => $advisor->id,
                'status' => GeneralTask::STATUS_COMPLETED,
                'priority' => GeneralTask::PRIORITY_LOW,
                'due_date' => now()->subDays(2)->toDateString(),
                'completed_at' => now()->subDays(1),
            ]);
            $this->line("  + Task #{$completed->id} (completed)");
        }
    }

    /**
     * Write a fixtures JSON the screenshot script reads to know which record IDs to visit.
     */
    private function writeFixtures(): void
    {
        $firstLoan = LoanDetail::orderBy('id')->first();
        $firstQuotation = Quotation::orderBy('id')->first();
        $firstDvr = DailyVisitReport::orderBy('id')->first();
        $firstTask = GeneralTask::orderBy('id')->first();
        $firstCustomer = Customer::orderBy('id')->first();
        $firstRole = Role::where('slug', '!=', 'super_admin')->orderBy('id')->first();

        // A user that isn't the super admin (so the /users/{id}/edit page is editable).
        $firstEditableUser = $this->roleUsers['loan_advisor'] ?? User::orderBy('id')->first();

        $fixtures = [
            'mode' => $this->mode,
            'loan_id' => $firstLoan?->id,
            'quotation_id' => $firstQuotation?->id,
            'dvr_id' => $firstDvr?->id,
            'task_id' => $firstTask?->id,
            'user_id' => $firstEditableUser?->id,
            'customer_id' => $firstCustomer?->id,
            'product_id' => $this->productId,
            'role_id' => $firstRole?->id,
            'bank_id' => $this->bankId,
            'branch_id' => $this->branchId,
            'generated_at' => now()->toIso8601String(),
        ];

        $mapDir = base_path('screenshots');
        if (! is_dir($mapDir)) {
            mkdir($mapDir, 0755, true);
        }
        file_put_contents("{$mapDir}/screenshot-fixtures.json", json_encode($fixtures, JSON_PRETTY_PRINT));

        $this->info("\n✓ Wrote screenshots/screenshot-fixtures.json");
        $this->line('  mode='.$fixtures['mode'].' loan='.$fixtures['loan_id'].' quotation='.$fixtures['quotation_id']
            .' dvr='.$fixtures['dvr_id'].' task='.$fixtures['task_id'].' user='.$fixtures['user_id']
            .' customer='.$fixtures['customer_id'].' role='.$fixtures['role_id']);
    }
}
