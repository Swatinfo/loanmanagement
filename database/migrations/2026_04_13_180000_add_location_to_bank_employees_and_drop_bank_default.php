<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add location_id to bank_employees (skip if already exists from partial run)
        if (! Schema::hasColumn('bank_employees', 'location_id')) {
            Schema::table('bank_employees', function (Blueprint $table) {
                $table->foreignId('location_id')->nullable()->after('is_default')->constrained('locations')->nullOnDelete();
            });
        }

        // Step 2: Migrate existing global defaults to per-city records
        if (Schema::hasColumn('banks', 'default_employee_id')) {
            $banks = DB::table('banks')->whereNotNull('default_employee_id')->get();
            foreach ($banks as $bank) {
                $bankLocationIds = DB::table('bank_location')
                    ->where('bank_id', $bank->id)
                    ->pluck('location_id')
                    ->toArray();

                foreach ($bankLocationIds as $locationId) {
                    DB::table('bank_employees')
                        ->where('bank_id', $bank->id)
                        ->where('user_id', $bank->default_employee_id)
                        ->update([
                            'is_default' => true,
                            'location_id' => $locationId,
                        ]);
                }
            }

            // Step 3: Drop the global default_employee_id from banks
            Schema::table('banks', function (Blueprint $table) {
                $table->dropForeign(['default_employee_id']);
                $table->dropColumn('default_employee_id');
            });
        }

        // Step 4: Update unique constraint to include location_id
        // Must drop FKs first, then unique index, then re-add FKs
        $hasOldUnique = Schema::hasIndex('bank_employees', 'bank_employees_bank_id_user_id_unique');
        if ($hasOldUnique) {
            Schema::table('bank_employees', function (Blueprint $table) {
                $table->dropForeign(['bank_id']);
                $table->dropForeign(['user_id']);
                $table->dropUnique(['bank_id', 'user_id']);
            });
            Schema::table('bank_employees', function (Blueprint $table) {
                $table->unique(['bank_id', 'user_id', 'location_id'], 'bank_employees_bank_user_location_unique');
                $table->foreign('bank_id')->references('id')->on('banks')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Restore unique constraint
        Schema::table('bank_employees', function (Blueprint $table) {
            $table->dropUnique('bank_employees_bank_user_location_unique');
            $table->unique(['bank_id', 'user_id']);
        });

        // Restore default_employee_id on banks
        Schema::table('banks', function (Blueprint $table) {
            $table->foreignId('default_employee_id')->nullable()->constrained('users')->nullOnDelete();
        });

        // Migrate back: set banks.default_employee_id from first city-level default
        $defaults = DB::table('bank_employees')
            ->where('is_default', true)
            ->whereNotNull('location_id')
            ->get()
            ->groupBy('bank_id');

        foreach ($defaults as $bankId => $records) {
            DB::table('banks')->where('id', $bankId)->update([
                'default_employee_id' => $records->first()->user_id,
            ]);
        }

        // Drop location_id
        Schema::table('bank_employees', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
