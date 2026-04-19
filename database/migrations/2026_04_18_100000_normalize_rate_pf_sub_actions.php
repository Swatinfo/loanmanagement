<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalize rate_pf sub_actions to 3 entries (one per runtime phase), matching
 * the convention used by sanction, legal_verification, docket, esign.
 *
 * Shifts all related phase indices:
 *   - stages.sub_actions: prepend fill_rate_pf (phase 1), existing entries become 2 and 3
 *   - bank_stage_configs.phase_roles: "0" -> "1", "1" -> "2"
 *   - product_stage_users.phase_index: 0 -> 1, 1 -> 2 (for product_stages tied to rate_pf)
 *   - loan_details.workflow_config.rate_pf.phases: "0" -> "1", "1" -> "2"
 */
return new class extends Migration
{
    public function up(): void
    {
        $ratePfStageId = DB::table('stages')->where('stage_key', 'rate_pf')->value('id');

        if (! $ratePfStageId) {
            return;
        }

        $this->normalizeStageSubActions($ratePfStageId);
        $this->shiftBankStageConfigs($ratePfStageId, forward: true);
        $this->shiftProductStageUsers($ratePfStageId, forward: true);
        $this->shiftLoanWorkflowConfigs(forward: true);
    }

    public function down(): void
    {
        $ratePfStageId = DB::table('stages')->where('stage_key', 'rate_pf')->value('id');

        if (! $ratePfStageId) {
            return;
        }

        $this->revertStageSubActions($ratePfStageId);
        $this->shiftBankStageConfigs($ratePfStageId, forward: false);
        $this->shiftProductStageUsers($ratePfStageId, forward: false);
        $this->shiftLoanWorkflowConfigs(forward: false);
    }

    private function normalizeStageSubActions(int $stageId): void
    {
        $row = DB::table('stages')->where('id', $stageId)->first();
        if (! $row || ! $row->sub_actions) {
            return;
        }

        $existing = json_decode($row->sub_actions, true);
        if (! is_array($existing) || count($existing) !== 2) {
            return;
        }

        $phase1 = [
            'key' => 'fill_rate_pf',
            'name' => 'Fill Rate & PF Details',
            'sequence' => 1,
            'role' => 'task_owner',
            'roles' => ['branch_manager', 'loan_advisor'],
            'type' => 'form',
            'is_enabled' => true,
        ];

        $existing[0]['sequence'] = 2;
        $existing[1]['sequence'] = 3;

        $normalized = array_values(array_merge([$phase1], $existing));

        DB::table('stages')->where('id', $stageId)->update([
            'sub_actions' => json_encode($normalized),
            'updated_at' => now(),
        ]);
    }

    private function revertStageSubActions(int $stageId): void
    {
        $row = DB::table('stages')->where('id', $stageId)->first();
        if (! $row || ! $row->sub_actions) {
            return;
        }

        $existing = json_decode($row->sub_actions, true);
        if (! is_array($existing) || count($existing) !== 3) {
            return;
        }

        $reverted = array_values(array_slice($existing, 1));
        if (isset($reverted[0])) {
            $reverted[0]['sequence'] = 1;
        }
        if (isset($reverted[1])) {
            $reverted[1]['sequence'] = 2;
        }

        DB::table('stages')->where('id', $stageId)->update([
            'sub_actions' => json_encode($reverted),
            'updated_at' => now(),
        ]);
    }

    private function shiftBankStageConfigs(int $stageId, bool $forward): void
    {
        $configs = DB::table('bank_stage_configs')->where('stage_id', $stageId)->get();

        foreach ($configs as $config) {
            if (! $config->phase_roles) {
                continue;
            }

            $roles = json_decode($config->phase_roles, true);
            if (! is_array($roles) || $roles === []) {
                continue;
            }

            $shifted = [];
            foreach ($roles as $idx => $role) {
                $newIdx = $forward ? ((int) $idx + 1) : ((int) $idx - 1);
                if ($newIdx < 0) {
                    continue;
                }
                $shifted[(string) $newIdx] = $role;
            }

            DB::table('bank_stage_configs')->where('id', $config->id)->update([
                'phase_roles' => $shifted === [] ? null : json_encode($shifted),
                'updated_at' => now(),
            ]);
        }
    }

    private function shiftProductStageUsers(int $stageId, bool $forward): void
    {
        $productStageIds = DB::table('product_stages')->where('stage_id', $stageId)->pluck('id');

        if ($productStageIds->isEmpty()) {
            return;
        }

        $delta = $forward ? 1 : -1;

        $rows = DB::table('product_stage_users')
            ->whereIn('product_stage_id', $productStageIds)
            ->whereNotNull('phase_index')
            ->get();

        if ($forward) {
            foreach ($rows->sortByDesc('phase_index') as $row) {
                DB::table('product_stage_users')->where('id', $row->id)->update([
                    'phase_index' => $row->phase_index + $delta,
                    'updated_at' => now(),
                ]);
            }
        } else {
            foreach ($rows->sortBy('phase_index') as $row) {
                if ($row->phase_index + $delta < 0) {
                    continue;
                }
                DB::table('product_stage_users')->where('id', $row->id)->update([
                    'phase_index' => $row->phase_index + $delta,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function shiftLoanWorkflowConfigs(bool $forward): void
    {
        $loans = DB::table('loan_details')
            ->whereNotNull('workflow_config')
            ->select('id', 'workflow_config')
            ->get();

        foreach ($loans as $loan) {
            $raw = is_string($loan->workflow_config)
                ? json_decode($loan->workflow_config, true)
                : (array) $loan->workflow_config;

            if (! is_array($raw) || ! isset($raw['rate_pf']['phases']) || ! is_array($raw['rate_pf']['phases'])) {
                continue;
            }

            $phases = $raw['rate_pf']['phases'];
            $shifted = [];

            foreach ($phases as $idx => $data) {
                $newIdx = $forward ? ((int) $idx + 1) : ((int) $idx - 1);
                if ($newIdx < 0) {
                    continue;
                }
                $shifted[(string) $newIdx] = $data;
            }

            if ($forward) {
                $shifted['0'] = [
                    'role' => 'task_owner',
                    'default_user_id' => $raw['rate_pf']['default_user_id'] ?? null,
                ];
                ksort($shifted);
            }

            $raw['rate_pf']['phases'] = $shifted;

            DB::table('loan_details')->where('id', $loan->id)->update([
                'workflow_config' => json_encode($raw),
                'updated_at' => now(),
            ]);
        }
    }
};
