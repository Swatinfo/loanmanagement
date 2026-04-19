<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\LoanDetail;
use App\Models\Product;
use App\Models\Role;
use App\Models\Stage;
use App\Models\StageAssignment;
use App\Models\StageQuery;
use App\Models\User;
use App\Services\LoanStageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * State-machine and invariant tests for the 12-stage workflow.
 *
 * Scope: service-level behavior. Controller/HTTP coverage is in sibling files.
 * Focus: transitions respect the DAG, open queries block completion,
 *        next-stage lookup, can-start predicate.
 */
class StageTransitionTest extends TestCase
{
    use RefreshDatabase;

    private LoanStageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LoanStageService::class);
        $this->seedMainStages();
    }

    /**
     * Seed just the stages needed for these tests. Deliberately lighter than
     * `DefaultDataSeeder` — keep tests fast.
     */
    private function seedMainStages(): void
    {
        $sequence = [
            'inquiry', 'document_selection', 'document_collection',
            'parallel_processing', 'sanction_decision', 'rate_pf', 'sanction',
            'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance',
        ];

        foreach ($sequence as $i => $key) {
            Stage::firstOrCreate(
                ['stage_key' => $key],
                [
                    'stage_name_en' => ucwords(str_replace('_', ' ', $key)),
                    'stage_name_gu' => $key,
                    'sequence_order' => $i + 1,
                    'is_parallel' => false,
                    'parent_stage_key' => null,
                    'stage_type' => 'sequential',
                    'is_enabled' => true,
                ]
            );
        }

        // A couple of parallel sub-stages under parallel_processing.
        foreach (['app_number', 'bsm_osv', 'legal_verification', 'technical_valuation'] as $i => $key) {
            Stage::firstOrCreate(
                ['stage_key' => $key],
                [
                    'stage_name_en' => ucwords(str_replace('_', ' ', $key)),
                    'sequence_order' => 100 + $i,
                    'is_parallel' => true,
                    'parent_stage_key' => 'parallel_processing',
                    'stage_type' => 'parallel',
                    'is_enabled' => true,
                ]
            );
        }
    }

    private function makeUser(string $roleSlug = 'loan_advisor'): User
    {
        $user = User::create([
            'name' => 'U'.uniqid(),
            'email' => uniqid().'@test',
            'password' => bcrypt('x'),
            'is_active' => true,
        ]);
        $user->roles()->sync(Role::where('slug', $roleSlug)->pluck('id'));

        return $user->fresh('roles');
    }

    private function makeLoan(?User $creator = null): LoanDetail
    {
        $creator ??= $this->makeUser();

        $bank = Bank::create(['name' => 'TestBank', 'is_active' => true]);
        $branch = Branch::create(['name' => 'TestBranch', 'is_active' => true]);
        $product = Product::create(['name' => 'TestProduct', 'bank_id' => $bank->id, 'is_active' => true]);

        return LoanDetail::create([
            'loan_number' => 'L-'.uniqid(),
            'customer_name' => 'Customer',
            'customer_type' => 'salaried',
            'loan_amount' => 1000000,
            'status' => 'active',
            'current_stage' => 'inquiry',
            'bank_id' => $bank->id,
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'created_by' => $creator->id,
            'assigned_advisor' => $creator->id,
        ]);
    }

    /* ── StageAssignment state machine ── */

    public function test_pending_can_transition_to_in_progress_or_skipped(): void
    {
        $assignment = new StageAssignment(['status' => 'pending']);
        $this->assertTrue($assignment->canTransitionTo('in_progress'));
        $this->assertTrue($assignment->canTransitionTo('skipped'));
        $this->assertFalse($assignment->canTransitionTo('completed'));
        $this->assertFalse($assignment->canTransitionTo('rejected'));
    }

    public function test_in_progress_can_transition_to_completed_or_rejected(): void
    {
        $assignment = new StageAssignment(['status' => 'in_progress']);
        $this->assertTrue($assignment->canTransitionTo('completed'));
        $this->assertTrue($assignment->canTransitionTo('rejected'));
        $this->assertFalse($assignment->canTransitionTo('pending'));
        $this->assertFalse($assignment->canTransitionTo('skipped'));
    }

    public function test_rejected_can_reopen_to_in_progress(): void
    {
        $assignment = new StageAssignment(['status' => 'rejected']);
        $this->assertTrue($assignment->canTransitionTo('in_progress'));
        $this->assertFalse($assignment->canTransitionTo('completed'));
    }

    public function test_skipped_is_terminal(): void
    {
        $assignment = new StageAssignment(['status' => 'skipped']);
        foreach (['pending', 'in_progress', 'completed', 'rejected'] as $status) {
            $this->assertFalse($assignment->canTransitionTo($status), "skipped should not transition to {$status}");
        }
    }

    public function test_completed_can_reopen_for_corrections(): void
    {
        $assignment = new StageAssignment(['status' => 'completed']);
        $this->assertTrue($assignment->canTransitionTo('in_progress'));
    }

    /* ── Service-level enforcement ── */

    public function test_update_stage_status_rejects_illegal_transition(): void
    {
        $loan = $this->makeLoan();
        $this->service->initializeStages($loan);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Cannot transition stage 'inquiry' from 'pending' to 'completed'");
        $this->service->updateStageStatus($loan, 'inquiry', 'completed');
    }

    public function test_update_stage_status_allows_legal_transition(): void
    {
        $user = $this->makeUser();
        $loan = $this->makeLoan($user);
        $this->service->initializeStages($loan);

        $this->service->updateStageStatus($loan, 'inquiry', 'in_progress', $user->id);
        $assignment = $loan->stageAssignments()->where('stage_key', 'inquiry')->first();

        $this->assertSame('in_progress', $assignment->status);
        $this->assertNotNull($assignment->started_at);
    }

    public function test_open_query_blocks_stage_completion(): void
    {
        $user = $this->makeUser();
        $loan = $this->makeLoan($user);
        $this->service->initializeStages($loan);
        $this->service->updateStageStatus($loan, 'inquiry', 'in_progress', $user->id);

        $assignment = $loan->stageAssignments()->where('stage_key', 'inquiry')->first();
        StageQuery::create([
            'loan_id' => $loan->id,
            'stage_key' => 'inquiry',
            'stage_assignment_id' => $assignment->id,
            'query_text' => 'Please clarify',
            'raised_by' => $user->id,
            'status' => 'pending',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unresolved queries');
        $this->service->updateStageStatus($loan, 'inquiry', 'completed', $user->id);
    }

    public function test_resolved_query_allows_stage_completion(): void
    {
        $user = $this->makeUser();
        $loan = $this->makeLoan($user);
        $this->service->initializeStages($loan);
        $this->service->updateStageStatus($loan, 'inquiry', 'in_progress', $user->id);

        $assignment = $loan->stageAssignments()->where('stage_key', 'inquiry')->first();
        StageQuery::create([
            'loan_id' => $loan->id,
            'stage_key' => 'inquiry',
            'stage_assignment_id' => $assignment->id,
            'query_text' => 'Clarified',
            'raised_by' => $user->id,
            'status' => 'resolved',
        ]);

        $this->service->updateStageStatus($loan, 'inquiry', 'completed', $user->id);
        $this->assertSame('completed', $assignment->fresh()->status);
    }

    /* ── Initialization ── */

    public function test_initialize_stages_creates_all_enabled_stage_assignments(): void
    {
        $loan = $this->makeLoan();
        $this->service->initializeStages($loan);

        $count = $loan->stageAssignments()->count();
        $expected = Stage::whereIn('stage_key', [
            'inquiry', 'document_selection', 'document_collection',
            'parallel_processing', 'app_number', 'bsm_osv', 'legal_verification', 'technical_valuation', 'sanction_decision',
            'rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance',
        ])->where('is_enabled', true)->count();

        $this->assertSame($expected, $count);
        $this->assertGreaterThan(0, $expected, 'seeded stages should exist');
    }

    /* ── Stage sequencing ── */

    public function test_get_next_stage_returns_following_main_stage(): void
    {
        $next = $this->service->getNextStage('inquiry');
        $this->assertSame('document_selection', $next);
    }

    public function test_get_next_stage_returns_null_at_end(): void
    {
        $mainKeys = $this->service->getMainStageKeys();
        $last = end($mainKeys);
        $this->assertNull($this->service->getNextStage($last));
    }

    /* ── Role resolution ── */

    public function test_resolve_stage_role_falls_back_to_default_when_no_bank_config(): void
    {
        $role = $this->service->resolveStageRole('inquiry', bankId: null);
        $this->assertIsString($role);
        $this->assertNotSame('', $role);
    }
}
