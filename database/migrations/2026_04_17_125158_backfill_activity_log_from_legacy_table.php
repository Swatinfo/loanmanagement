<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copy rows from the legacy `activity_logs` table into Spatie's `activity_log`.
 *
 * Column remap:
 *   user_id        → causer_id (causer_type = App\Models\User)
 *   action         → description
 *   subject_type   → subject_type
 *   subject_id     → subject_id
 *   properties     → properties
 *   ip_address     → ip_address (custom column preserved)
 *   user_agent     → user_agent (custom column preserved)
 *   timestamps     → timestamps
 *
 * We do NOT drop `activity_logs` yet — leave it in place until the next release
 * as a read-only historical reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasTable('activity_log')) {
            return;
        }

        // Only backfill if `activity_log` is empty — otherwise this migration
        // has already run, or spatie is already being used.
        if (DB::table('activity_log')->exists()) {
            return;
        }

        DB::table('activity_logs')->orderBy('id')->chunkById(500, function ($rows): void {
            $insert = [];
            foreach ($rows as $row) {
                $insert[] = [
                    'log_name' => 'default',
                    'description' => (string) $row->action,
                    'subject_type' => $row->subject_type,
                    'subject_id' => $row->subject_id,
                    'event' => null,
                    'causer_type' => $row->user_id ? \App\Models\User::class : null,
                    'causer_id' => $row->user_id,
                    'attribute_changes' => null,
                    'properties' => $row->properties,
                    'batch_uuid' => null,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }
            if ($insert !== []) {
                DB::table('activity_log')->insert($insert);
            }
        });
    }

    public function down(): void
    {
        // Cleanest revert is to truncate `activity_log`; the legacy table is untouched.
        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')->truncate();
        }
    }
};
