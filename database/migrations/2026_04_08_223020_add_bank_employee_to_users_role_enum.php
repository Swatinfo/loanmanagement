<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','staff','bank_employee') NOT NULL DEFAULT 'staff'");

        // Update existing bank_employee task_role users to the new system role
        DB::table('users')
            ->where('task_role', 'bank_employee')
            ->where('role', 'staff')
            ->update(['role' => 'bank_employee']);

        // Seed role_permissions for bank_employee (view_loans=24, add_remarks=32, change_own_password=20)
        foreach ([20, 24, 32] as $permId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => 'bank_employee',
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Revert bank_employee users back to staff
        DB::table('users')
            ->where('role', 'bank_employee')
            ->update(['role' => 'staff']);

        DB::table('role_permissions')
            ->where('role', 'bank_employee')
            ->delete();

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','staff') NOT NULL DEFAULT 'staff'");
    }
};
