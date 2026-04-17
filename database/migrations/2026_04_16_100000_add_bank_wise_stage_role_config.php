<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add assigned_role to stages
        Schema::table('stages', function (Blueprint $table) {
            $table->string('assigned_role', 50)->default('task_owner')->after('default_role');
        });

        // 2. Set assigned_role for each stage based on current workflow
        $stageRoles = [
            'inquiry' => 'task_owner',
            'document_selection' => 'task_owner',
            'document_collection' => 'task_owner',
            'parallel_processing' => 'task_owner',
            'app_number' => 'task_owner',
            'bsm_osv' => 'bank_employee',
            'sanction_decision' => 'office_employee',
            'legal_verification' => 'task_owner',
            'property_valuation' => 'office_employee',
            'technical_valuation' => 'task_owner',
            'rate_pf' => 'task_owner',
            'sanction' => 'task_owner',
            'docket' => 'task_owner',
            'kfs' => 'task_owner',
            'esign' => 'task_owner',
            'disbursement' => 'office_employee',
            'otc_clearance' => 'task_owner',
        ];

        foreach ($stageRoles as $stageKey => $role) {
            DB::table('stages')->where('stage_key', $stageKey)->update(['assigned_role' => $role]);
        }

        // 3. Add role field to existing rate_pf sub_actions
        $ratePf = DB::table('stages')->where('stage_key', 'rate_pf')->first();
        if ($ratePf && $ratePf->sub_actions) {
            $subActions = json_decode($ratePf->sub_actions, true);
            if (is_array($subActions)) {
                // Phase 1: bank_rate_details -> bank_employee (bank reviews rates)
                if (isset($subActions[0])) {
                    $subActions[0]['role'] = 'bank_employee';
                }
                // Phase 2: processing_charges -> task_owner (advisor fills charges)
                if (isset($subActions[1])) {
                    $subActions[1]['role'] = 'task_owner';
                }
                DB::table('stages')->where('stage_key', 'rate_pf')->update([
                    'sub_actions' => json_encode($subActions),
                ]);
            }
        }

        // 4. Add role field to existing sanction sub_actions
        $sanction = DB::table('stages')->where('stage_key', 'sanction')->first();
        if ($sanction && $sanction->sub_actions) {
            $subActions = json_decode($sanction->sub_actions, true);
            if (is_array($subActions)) {
                // Phase 1: send_for_sanction -> task_owner
                if (isset($subActions[0])) {
                    $subActions[0]['role'] = 'task_owner';
                }
                // Phase 2: sanction_generated -> bank_employee
                if (isset($subActions[1])) {
                    $subActions[1]['role'] = 'bank_employee';
                }
                // Phase 3: sanction_details -> task_owner
                if (isset($subActions[2])) {
                    $subActions[2]['role'] = 'task_owner';
                }
                DB::table('stages')->where('stage_key', 'sanction')->update([
                    'sub_actions' => json_encode($subActions),
                ]);
            }
        }

        // 5. Populate sub_actions for multi-phase stages that currently lack them

        // legal_verification: 3 phases
        DB::table('stages')->where('stage_key', 'legal_verification')->update([
            'sub_actions' => json_encode([
                [
                    'key' => 'send_to_bank',
                    'name' => 'Send to Bank',
                    'sequence' => 1,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'action_button',
                    'action' => 'send_to_bank',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'initiate_legal',
                    'name' => 'Initiate Legal',
                    'sequence' => 2,
                    'role' => 'bank_employee',
                    'roles' => ['bank_employee'],
                    'type' => 'action_button',
                    'action' => 'initiate_legal',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'review_complete',
                    'name' => 'Review & Complete',
                    'sequence' => 3,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'action_button',
                    'action' => 'review_complete',
                    'is_enabled' => true,
                ],
            ]),
        ]);

        // technical_valuation: 2 phases
        DB::table('stages')->where('stage_key', 'technical_valuation')->update([
            'sub_actions' => json_encode([
                [
                    'key' => 'send_to_office',
                    'name' => 'Send for Valuation',
                    'sequence' => 1,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'action_button',
                    'action' => 'send_to_office',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'fill_valuation',
                    'name' => 'Fill Valuation Form',
                    'sequence' => 2,
                    'role' => 'office_employee',
                    'roles' => ['office_employee'],
                    'type' => 'form',
                    'is_enabled' => true,
                ],
            ]),
        ]);

        // docket: 2 phases
        DB::table('stages')->where('stage_key', 'docket')->update([
            'sub_actions' => json_encode([
                [
                    'key' => 'submit_docket',
                    'name' => 'Submit Docket',
                    'sequence' => 1,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'form',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'review_generate_kfs',
                    'name' => 'Review & Generate KFS',
                    'sequence' => 2,
                    'role' => 'office_employee',
                    'roles' => ['office_employee'],
                    'type' => 'action_button',
                    'action' => 'generate_kfs',
                    'is_enabled' => true,
                ],
            ]),
        ]);

        // esign: 4 phases
        DB::table('stages')->where('stage_key', 'esign')->update([
            'sub_actions' => json_encode([
                [
                    'key' => 'send_for_esign',
                    'name' => 'Send for E-Sign',
                    'sequence' => 1,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'action_button',
                    'action' => 'send_for_esign',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'generate_esign',
                    'name' => 'Generate E-Sign Docs',
                    'sequence' => 2,
                    'role' => 'bank_employee',
                    'roles' => ['bank_employee'],
                    'type' => 'action_button',
                    'action' => 'esign_generated',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'customer_signing',
                    'name' => 'Customer Signing',
                    'sequence' => 3,
                    'role' => 'task_owner',
                    'roles' => ['branch_manager', 'loan_advisor'],
                    'type' => 'action_button',
                    'action' => 'esign_customer_done',
                    'is_enabled' => true,
                ],
                [
                    'key' => 'confirm_complete',
                    'name' => 'Confirm & Complete',
                    'sequence' => 4,
                    'role' => 'bank_employee',
                    'roles' => ['bank_employee'],
                    'type' => 'action_button',
                    'action' => 'esign_complete',
                    'is_enabled' => true,
                ],
            ]),
        ]);

        // 6. Create bank_stage_configs table
        Schema::create('bank_stage_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->string('assigned_role', 50)->nullable();
            $table->json('phase_roles')->nullable();
            $table->timestamps();

            $table->unique(['bank_id', 'stage_id']);
        });

        // 7. Add workflow_config to loan_details
        Schema::table('loan_details', function (Blueprint $table) {
            $table->json('workflow_config')->nullable()->after('notes');
        });

        // 8. Add phase_index to product_stage_users
        Schema::table('product_stage_users', function (Blueprint $table) {
            $table->integer('phase_index')->nullable()->after('user_id');
        });

        // 9. Backfill workflow_config for active loans
        $this->backfillActiveLoans();
    }

    public function down(): void
    {
        Schema::table('product_stage_users', function (Blueprint $table) {
            $table->dropColumn('phase_index');
        });

        Schema::table('loan_details', function (Blueprint $table) {
            $table->dropColumn('workflow_config');
        });

        Schema::dropIfExists('bank_stage_configs');

        // Remove sub_actions from stages that didn't have them before
        foreach (['legal_verification', 'technical_valuation', 'docket', 'esign'] as $key) {
            DB::table('stages')->where('stage_key', $key)->update(['sub_actions' => null]);
        }

        // Remove role field from rate_pf and sanction sub_actions
        foreach (['rate_pf', 'sanction'] as $key) {
            $stage = DB::table('stages')->where('stage_key', $key)->first();
            if ($stage && $stage->sub_actions) {
                $subActions = json_decode($stage->sub_actions, true);
                if (is_array($subActions)) {
                    foreach ($subActions as &$sa) {
                        unset($sa['role']);
                    }
                    DB::table('stages')->where('stage_key', $key)->update([
                        'sub_actions' => json_encode($subActions),
                    ]);
                }
            }
        }

        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn('assigned_role');
        });
    }

    /**
     * Backfill workflow_config for existing active/on_hold loans.
     * Uses current master stage config (no bank overrides exist yet).
     */
    private function backfillActiveLoans(): void
    {
        $stages = DB::table('stages')->where('is_enabled', true)->get();

        $config = [];
        foreach ($stages as $stage) {
            $stageConfig = [
                'role' => $stage->assigned_role ?? 'task_owner',
                'default_user_id' => null,
            ];

            $subActions = $stage->sub_actions ? json_decode($stage->sub_actions, true) : null;
            if (is_array($subActions) && count($subActions) > 0) {
                $phases = [];
                foreach ($subActions as $idx => $sa) {
                    $phases[(string) $idx] = [
                        'role' => $sa['role'] ?? 'task_owner',
                        'default_user_id' => null,
                    ];
                }
                $stageConfig['phases'] = $phases;
            }

            $config[$stage->stage_key] = $stageConfig;
        }

        $configJson = json_encode($config);

        // Update all active/on_hold loans that have no workflow_config
        DB::table('loan_details')
            ->whereIn('status', ['active', 'on_hold'])
            ->whereNull('workflow_config')
            ->update(['workflow_config' => $configJson]);
    }
};
