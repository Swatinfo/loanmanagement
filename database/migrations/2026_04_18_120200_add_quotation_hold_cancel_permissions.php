<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        [
            'name' => 'Hold Quotation',
            'slug' => 'hold_quotation',
            'group' => 'Quotations',
            'description' => 'Put a quotation on hold with a reason and follow-up date',
        ],
        [
            'name' => 'Cancel Quotation',
            'slug' => 'cancel_quotation',
            'group' => 'Quotations',
            'description' => 'Cancel a quotation with a reason (terminal state)',
        ],
        [
            'name' => 'Resume Quotation',
            'slug' => 'resume_quotation',
            'group' => 'Quotations',
            'description' => 'Resume an on-hold quotation back to active',
        ],
    ];

    private array $roleGrants = [
        'hold_quotation' => ['admin', 'branch_manager', 'bdh', 'loan_advisor'],
        'cancel_quotation' => ['admin', 'branch_manager', 'bdh', 'loan_advisor'],
        'resume_quotation' => ['admin', 'branch_manager', 'bdh'],
    ];

    public function up(): void
    {
        $now = now();

        $rows = array_map(fn ($p) => array_merge($p, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $this->permissions);

        DB::table('permissions')->insert($rows);

        foreach ($this->roleGrants as $slug => $roleSlugs) {
            $permissionId = DB::table('permissions')->where('slug', $slug)->value('id');
            if (! $permissionId) {
                continue;
            }

            $roleIds = DB::table('roles')->whereIn('slug', $roleSlugs)->pluck('id');

            $inserts = $roleIds->map(fn ($roleId) => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ])->all();

            if ($inserts) {
                DB::table('role_permission')->insert($inserts);
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->permissions, 'slug');
        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('user_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
