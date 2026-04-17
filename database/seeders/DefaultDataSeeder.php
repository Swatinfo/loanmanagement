<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the database with all production data as of 2026-04-14.
 * This seeder captures every record from the live database to allow
 * a fresh install to start with the exact same data.
 *
 * No loans or products are seeded — those are created through the UI.
 *
 * Run: php artisan db:seed --class=DefaultDataSeeder
 */
class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clean all loan data and references before seeding
        $this->purgeLoanData();

        // Order matters: parent tables first, then children with FKs

        $this->seedPermissions();
        $this->seedLocations();
        $this->seedBranches();
        $this->seedBanks();
        $this->seedStages();
        $this->seedBankStageConfigs();
        $this->seedUsers();
        $this->seedRoleAssignments();
        $this->seedRolePermissionMappings();
        $this->seedUserBranches();
        $this->seedBankLocations();
        $this->seedBankEmployees();
        $this->seedBankDefaults();
        $this->seedLocationUsers();
        $this->seedBankCharges();
        $this->seedProducts();
        $this->seedProductStages();
        $this->seedProductStageUsers();
        $this->seedAppConfig();
        $this->seedAppSettings();
        /*$this->seedQuotations();
        $this->seedQuotationBanks();
        $this->seedQuotationEmi();
        $this->seedQuotationDocuments();*/

        // Clear quotations from previous seeder runs (fresh ones created below)
        DB::table('quotation_documents')->delete();
        DB::table('quotation_emi')->delete();
        DB::table('quotation_banks')->delete();
        DB::table('quotations')->delete();

        $this->seedSampleQuotationAndLoan();
    }

    private function purgeLoanData(): void
    {
        $loanTables = [
            'query_responses',
            'stage_queries',
            'stage_transfers',
            'disbursement_details',
            'valuation_details',
            'remarks',
            'shf_notifications',
            'loan_documents',
            'loan_progress',
            'stage_assignments',
            'loan_details',
        ];

        // Disable FK checks for MySQL, use PRAGMA for SQLite
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        foreach ($loanTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // Clear loan_id references from quotations
        if (DB::getSchemaBuilder()->hasTable('quotations')) {
            DB::table('quotations')->whereNotNull('loan_id')->update(['loan_id' => null]);
        }

        // Clear loan-related activity logs
        if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
            DB::table('activity_logs')
                ->where('subject_type', 'App\\Models\\LoanDetail')
                ->delete();
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // ── Customers (id 1-2) ──
            ['name' => 'Manage Customers', 'slug' => 'manage_customers', 'group' => 'Customers', 'description' => 'Create and edit customer records'],
            ['name' => 'View Customers', 'slug' => 'view_customers', 'group' => 'Customers', 'description' => 'View customer list and details'],
            // ── System (id 3-5) ──
            ['name' => 'Impersonate Users', 'slug' => 'impersonate_users', 'group' => 'System', 'description' => 'Log in as another user'],
            ['name' => 'View Dashboard', 'slug' => 'view_dashboard', 'group' => 'System', 'description' => 'Access the dashboard'],
            ['name' => 'Manage Notifications', 'slug' => 'manage_notifications', 'group' => 'System', 'description' => 'View and manage notifications'],
            // ── Loans (id 6-13) ──
            ['name' => 'Transfer Loan Stages', 'slug' => 'transfer_loan_stages', 'group' => 'Loans', 'description' => 'Transfer stage assignment to another user'],
            ['name' => 'Reject Loan', 'slug' => 'reject_loan', 'group' => 'Loans', 'description' => 'Reject a loan application'],
            ['name' => 'Change Loan Status', 'slug' => 'change_loan_status', 'group' => 'Loans', 'description' => 'Put loan on hold or cancel'],
            ['name' => 'View Loan Timeline', 'slug' => 'view_loan_timeline', 'group' => 'Loans', 'description' => 'View loan stage timeline history'],
            ['name' => 'Manage Disbursement', 'slug' => 'manage_disbursement', 'group' => 'Loans', 'description' => 'Process loan disbursement'],
            ['name' => 'Manage Valuation', 'slug' => 'manage_valuation', 'group' => 'Loans', 'description' => 'Fill and edit valuation details'],
            ['name' => 'Raise Query', 'slug' => 'raise_query', 'group' => 'Loans', 'description' => 'Raise queries on loan stages'],
            ['name' => 'Resolve Query', 'slug' => 'resolve_query', 'group' => 'Loans', 'description' => 'Resolve raised queries'],
            // ── Quotations (id 14-15) ──
            ['name' => 'Download Branded PDF', 'slug' => 'download_pdf_branded', 'group' => 'Quotations', 'description' => 'Download PDF with SHF branding'],
            ['name' => 'Download Plain PDF', 'slug' => 'download_pdf_plain', 'group' => 'Quotations', 'description' => 'Download PDF without SHF branding'],
            // ── Settings (id 16-23) ──
            ['name' => 'View Settings', 'slug' => 'view_settings', 'group' => 'Settings', 'description' => 'View the settings page'],
            ['name' => 'Edit Company Info', 'slug' => 'edit_company_info', 'group' => 'Settings', 'description' => 'Edit company information'],
            ['name' => 'Edit Banks', 'slug' => 'edit_banks', 'group' => 'Settings', 'description' => 'Add/edit/remove banks'],
            ['name' => 'Edit Documents', 'slug' => 'edit_documents', 'group' => 'Settings', 'description' => 'Add/edit/remove required documents'],
            ['name' => 'Edit Tenures', 'slug' => 'edit_tenures', 'group' => 'Settings', 'description' => 'Add/edit/remove loan tenures'],
            ['name' => 'Edit Charges', 'slug' => 'edit_charges', 'group' => 'Settings', 'description' => 'Edit bank charges'],
            ['name' => 'Edit Services', 'slug' => 'edit_services', 'group' => 'Settings', 'description' => 'Edit service charges'],
            ['name' => 'Edit GST', 'slug' => 'edit_gst', 'group' => 'Settings', 'description' => 'Edit GST percentage'],
            // ── Quotations (id 24-29) ──
            ['name' => 'Create Quotation', 'slug' => 'create_quotation', 'group' => 'Quotations', 'description' => 'Create new loan quotations'],
            ['name' => 'Generate PDF', 'slug' => 'generate_pdf', 'group' => 'Quotations', 'description' => 'Generate PDF for quotations'],
            ['name' => 'View Own Quotations', 'slug' => 'view_own_quotations', 'group' => 'Quotations', 'description' => 'View quotations created by self'],
            ['name' => 'View All Quotations', 'slug' => 'view_all_quotations', 'group' => 'Quotations', 'description' => 'View all quotations across users'],
            ['name' => 'Delete Quotations', 'slug' => 'delete_quotations', 'group' => 'Quotations', 'description' => 'Delete quotations'],
            ['name' => 'Download PDF', 'slug' => 'download_pdf', 'group' => 'Quotations', 'description' => 'Download generated PDFs'],
            // ── Users (id 30-34) ──
            ['name' => 'View Users', 'slug' => 'view_users', 'group' => 'Users', 'description' => 'View the users list'],
            ['name' => 'Create Users', 'slug' => 'create_users', 'group' => 'Users', 'description' => 'Create new user accounts'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'group' => 'Users', 'description' => 'Edit existing user accounts'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'group' => 'Users', 'description' => 'Delete user accounts'],
            ['name' => 'Assign Roles', 'slug' => 'assign_roles', 'group' => 'Users', 'description' => 'Assign roles to users'],
            // ── System (id 35-37) ──
            ['name' => 'Change Own Password', 'slug' => 'change_own_password', 'group' => 'System', 'description' => 'Change own password'],
            ['name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'group' => 'System', 'description' => 'Manage role and user permissions'],
            ['name' => 'View Activity Log', 'slug' => 'view_activity_log', 'group' => 'System', 'description' => 'View system activity log'],
            // ── Loans (id 38-51) ──
            ['name' => 'Convert to Loan', 'slug' => 'convert_to_loan', 'group' => 'Loans', 'description' => 'Convert quotation to loan task'],
            ['name' => 'View Loans', 'slug' => 'view_loans', 'group' => 'Loans', 'description' => 'View loan task list'],
            ['name' => 'View All Loans', 'slug' => 'view_all_loans', 'group' => 'Loans', 'description' => 'View all loans across users/branches'],
            ['name' => 'Create Loan', 'slug' => 'create_loan', 'group' => 'Loans', 'description' => 'Create loan tasks directly'],
            ['name' => 'Edit Loan', 'slug' => 'edit_loan', 'group' => 'Loans', 'description' => 'Edit loan details'],
            ['name' => 'Delete Loan', 'slug' => 'delete_loan', 'group' => 'Loans', 'description' => 'Delete loan tasks'],
            ['name' => 'Manage Loan Documents', 'slug' => 'manage_loan_documents', 'group' => 'Loans', 'description' => 'Mark documents as received/pending, add/remove documents'],
            ['name' => 'Manage Loan Stages', 'slug' => 'manage_loan_stages', 'group' => 'Loans', 'description' => 'Update stage status and assignments'],
            ['name' => 'Skip Loan Stages', 'slug' => 'skip_loan_stages', 'group' => 'Loans', 'description' => 'Skip stages in loan workflow'],
            ['name' => 'Add Remarks', 'slug' => 'add_remarks', 'group' => 'Loans', 'description' => 'Add remarks to loan stages'],
            ['name' => 'Manage Workflow Config', 'slug' => 'manage_workflow_config', 'group' => 'Loans', 'description' => 'Configure banks, products, branches, stage workflows'],
            ['name' => 'Upload Loan Documents', 'slug' => 'upload_loan_documents', 'group' => 'Loans', 'description' => 'Upload document files to loan documents'],
            ['name' => 'Download Loan Documents', 'slug' => 'download_loan_documents', 'group' => 'Loans', 'description' => 'Download/preview uploaded document files'],
            ['name' => 'Delete Loan Files', 'slug' => 'delete_loan_files', 'group' => 'Loans', 'description' => 'Remove uploaded document files'],
            // ── Tasks (id 52) ──
            ['name' => 'View All Tasks', 'slug' => 'view_all_tasks', 'group' => 'Tasks', 'description' => 'View all general tasks across users (read-only)'],
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $p['slug']],
                array_merge($p, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedLocations(): void
    {
        $locations = [
            ['id' => 1, 'parent_id' => null, 'name' => 'Gujarat', 'type' => 'state', 'code' => 'GJ', 'is_active' => true],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Rajkot', 'type' => 'city', 'code' => 'RJT', 'is_active' => true],
        ];

        foreach ($locations as $loc) {
            DB::table('locations')->updateOrInsert(
                ['id' => $loc['id']],
                array_merge($loc, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBranches(): void
    {
        DB::table('branches')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Rajkot Main Office',
                'code' => 'RJK-MAIN',
                'address' => 'OFFICE NO 911, R K PRIME, CIRCLE, next to SILVER HEIGHT, Nehru Nagar Co operative Society, Nana Mava, Rajkot, Gujarat 360004',
                'city' => 'Rajkot',
                'phone' => '+91 99747 89089',
                'is_active' => true,
                'manager_id' => null,
                'location_id' => 2,
                'created_at' => '2026-04-06 15:24:26',
                'updated_at' => '2026-04-07 23:22:31',
            ]
        );
    }

    private function seedBanks(): void
    {
        $banks = [
            ['id' => 1, 'name' => 'HDFC Bank', 'code' => 'HDFC', 'is_active' => true],
            ['id' => 2, 'name' => 'ICICI Bank', 'code' => 'ICICI', 'is_active' => true],
            ['id' => 3, 'name' => 'Axis Bank', 'code' => 'AXIS', 'is_active' => true],
            ['id' => 4, 'name' => 'Kotak Mahindra Bank', 'code' => 'KOTAK', 'is_active' => true],
        ];

        foreach ($banks as $bank) {
            DB::table('banks')->updateOrInsert(
                ['id' => $bank['id']],
                array_merge($bank, ['created_at' => '2026-04-06 15:24:26', 'updated_at' => '2026-04-06 15:24:26'])
            );
        }
    }

    private function seedStages(): void
    {
        $stages = [
            ['id' => 1, 'stage_key' => 'inquiry', 'is_enabled' => true, 'stage_name_en' => 'Loan Inquiry', 'stage_name_gu' => 'Loan Inquiry', 'sequence_order' => 1, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Initial customer and loan details entry', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 2, 'stage_key' => 'document_selection', 'is_enabled' => true, 'stage_name_en' => 'Document Selection', 'stage_name_gu' => 'Document Selection', 'sequence_order' => 2, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Select required documents for the loan', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 3, 'stage_key' => 'document_collection', 'is_enabled' => true, 'stage_name_en' => 'Document Collection', 'stage_name_gu' => 'Document Collection', 'sequence_order' => 3, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Collect and verify all required documents', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 4, 'stage_key' => 'parallel_processing', 'is_enabled' => true, 'stage_name_en' => 'Parallel Processing', 'stage_name_gu' => 'Parallel Processing', 'sequence_order' => 4, 'is_parallel' => true, 'parent_stage_key' => null, 'stage_type' => 'parallel', 'description_en' => 'Four parallel tracks processed simultaneously', 'description_gu' => null, 'default_role' => null, 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 5, 'stage_key' => 'app_number', 'is_enabled' => true, 'stage_name_en' => 'Application Number', 'stage_name_gu' => 'Application Number', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Enter bank application number', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 6, 'stage_key' => 'bsm_osv', 'is_enabled' => true, 'stage_name_en' => 'BSM/OSV Approval', 'stage_name_gu' => 'BSM/OSV Approval', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Bank site and office verification', 'description_gu' => null, 'default_role' => '["bank_employee"]', 'assigned_role' => 'bank_employee', 'sub_actions' => null],
            ['id' => 7, 'stage_key' => 'sanction_decision', 'is_enabled' => true, 'stage_name_en' => 'Loan Sanction Decision', 'stage_name_gu' => "\u{0ab2}\u{0acb}\u{0aa8} \u{0aae}\u{0a82}\u{0a9c}\u{0ac2}\u{0ab0}\u{0ac0} \u{0aa8}\u{0abf}\u{0ab0}\u{0acd}\u{0aa3}\u{0aaf}", 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Loan sanction approval with escalation ladder', 'description_gu' => null, 'default_role' => '["office_employee","branch_manager","bdh"]', 'assigned_role' => 'office_employee', 'sub_actions' => null],
            ['id' => 8, 'stage_key' => 'legal_verification', 'is_enabled' => true, 'stage_name_en' => 'Legal Verification', 'stage_name_gu' => 'Legal Verification', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Legal document verification', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","bank_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'send_to_bank', 'name' => 'Send to Bank', 'sequence' => 1, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'send_to_bank', 'is_enabled' => true],
                ['key' => 'initiate_legal', 'name' => 'Initiate Legal', 'sequence' => 2, 'role' => 'bank_employee', 'roles' => ['bank_employee'], 'type' => 'action_button', 'action' => 'initiate_legal', 'is_enabled' => true],
                ['key' => 'review_complete', 'name' => 'Review & Complete', 'sequence' => 3, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'review_complete', 'is_enabled' => true],
            ])],
            ['id' => 9, 'stage_key' => 'property_valuation', 'is_enabled' => true, 'stage_name_en' => 'Property Valuation', 'stage_name_gu' => 'Property Valuation', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Dedicated property valuation for LAP', 'description_gu' => null, 'default_role' => '["branch_manager","office_employee"]', 'assigned_role' => 'office_employee', 'sub_actions' => null],
            ['id' => 10, 'stage_key' => 'technical_valuation', 'is_enabled' => true, 'stage_name_en' => 'Technical Valuation', 'stage_name_gu' => 'Technical Valuation', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Property/asset technical valuation', 'description_gu' => null, 'default_role' => '["branch_manager","office_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'send_to_office', 'name' => 'Send for Valuation', 'sequence' => 1, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'send_to_office', 'is_enabled' => true],
                ['key' => 'fill_valuation', 'name' => 'Fill Valuation Form', 'sequence' => 2, 'role' => 'office_employee', 'roles' => ['office_employee'], 'type' => 'form', 'is_enabled' => true],
            ])],
            ['id' => 11, 'stage_key' => 'rate_pf', 'is_enabled' => true, 'stage_name_en' => 'Rate & PF Request', 'stage_name_gu' => 'Rate & PF Request', 'sequence_order' => 5, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Request interest rate and processing fee from bank', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","bank_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'bank_rate_details', 'name' => 'Bank Rate Details', 'sequence' => 1, 'role' => 'bank_employee', 'roles' => ['bank_employee'], 'type' => 'form', 'is_enabled' => true],
                ['key' => 'processing_charges', 'name' => 'Processing & Charges', 'sequence' => 2, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor', 'office_employee'], 'type' => 'form', 'is_enabled' => true],
            ])],
            ['id' => 12, 'stage_key' => 'sanction', 'is_enabled' => true, 'stage_name_en' => 'Sanction Letter', 'stage_name_gu' => 'Sanction Letter', 'sequence_order' => 6, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Bank issues sanction letter', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","bank_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'send_for_sanction', 'name' => 'Send for Sanction Letter', 'sequence' => 1, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'send_for_sanction', 'is_enabled' => true],
                ['key' => 'sanction_generated', 'name' => 'Sanction Letter Generated', 'sequence' => 2, 'role' => 'bank_employee', 'roles' => ['bank_employee'], 'type' => 'action_button', 'action' => 'sanction_generated', 'is_enabled' => true],
                ['key' => 'sanction_details', 'name' => 'Sanction Details', 'sequence' => 3, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'form', 'is_enabled' => true],
            ])],
            ['id' => 13, 'stage_key' => 'docket', 'is_enabled' => true, 'stage_name_en' => 'Docket Login', 'stage_name_gu' => 'Docket Login', 'sequence_order' => 7, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Physical document processing and docket creation', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'submit_docket', 'name' => 'Submit Docket', 'sequence' => 1, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'form', 'is_enabled' => true],
                ['key' => 'review_generate_kfs', 'name' => 'Review & Generate KFS', 'sequence' => 2, 'role' => 'office_employee', 'roles' => ['office_employee'], 'type' => 'action_button', 'action' => 'generate_kfs', 'is_enabled' => true],
            ])],
            ['id' => 14, 'stage_key' => 'kfs', 'is_enabled' => true, 'stage_name_en' => 'KFS Generation', 'stage_name_gu' => 'KFS Generation', 'sequence_order' => 8, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Key Fact Statement generation', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
            ['id' => 15, 'stage_key' => 'esign', 'is_enabled' => true, 'stage_name_en' => 'E-Sign & eNACH', 'stage_name_gu' => 'E-Sign & eNACH', 'sequence_order' => 9, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Digital signature and eNACH mandate', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","bank_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => json_encode([
                ['key' => 'send_for_esign', 'name' => 'Send for E-Sign', 'sequence' => 1, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'send_for_esign', 'is_enabled' => true],
                ['key' => 'generate_esign', 'name' => 'Generate E-Sign Docs', 'sequence' => 2, 'role' => 'bank_employee', 'roles' => ['bank_employee'], 'type' => 'action_button', 'action' => 'esign_generated', 'is_enabled' => true],
                ['key' => 'customer_signing', 'name' => 'Customer Signing', 'sequence' => 3, 'role' => 'task_owner', 'roles' => ['branch_manager', 'loan_advisor'], 'type' => 'action_button', 'action' => 'esign_customer_done', 'is_enabled' => true],
                ['key' => 'confirm_complete', 'name' => 'Confirm & Complete', 'sequence' => 4, 'role' => 'bank_employee', 'roles' => ['bank_employee'], 'type' => 'action_button', 'action' => 'esign_complete', 'is_enabled' => true],
            ])],
            ['id' => 16, 'stage_key' => 'disbursement', 'is_enabled' => true, 'stage_name_en' => 'Disbursement', 'stage_name_gu' => 'Disbursement', 'sequence_order' => 10, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'decision', 'description_en' => 'Fund disbursement - transfer or cheque with OTC handling', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'assigned_role' => 'office_employee', 'sub_actions' => null],
            ['id' => 17, 'stage_key' => 'otc_clearance', 'is_enabled' => true, 'stage_name_en' => 'OTC Clearance', 'stage_name_gu' => 'OTC Clearance', 'sequence_order' => 11, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Cheque handover and OTC clearance', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'assigned_role' => 'task_owner', 'sub_actions' => null],
        ];

        foreach ($stages as $stage) {
            DB::table('stages')->updateOrInsert(
                ['stage_key' => $stage['stage_key']],
                array_merge($stage, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBankStageConfigs(): void
    {
        // Clear existing configs (re-seed from scratch)
        DB::table('bank_stage_configs')->delete();
        DB::table('product_stage_users')->delete();

        // For Axis, HDFC, Kotak: rate_pf phase 0 = office_employee (not bank_employee)
        // For Axis, HDFC, Kotak: sanction phase 1 = office_employee (not bank_employee)
        // ICICI keeps master defaults (bank_employee for both)

        $ratePfStageId = DB::table('stages')->where('stage_key', 'rate_pf')->value('id');
        $sanctionStageId = DB::table('stages')->where('stage_key', 'sanction')->value('id');

        // Bank IDs: 1=HDFC, 3=Axis, 4=Kotak
        $overrideBankIds = [1, 3, 4];

        foreach ($overrideBankIds as $bankId) {
            // Rate & PF: phase 0 (Bank Rate Details) → office_employee instead of bank_employee
            if ($ratePfStageId) {
                DB::table('bank_stage_configs')->updateOrInsert(
                    ['bank_id' => $bankId, 'stage_id' => $ratePfStageId],
                    ['assigned_role' => null, 'phase_roles' => json_encode(['0' => 'office_employee']), 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // Sanction: phase 1 (Sanction Letter Generated) → office_employee instead of bank_employee
            if ($sanctionStageId) {
                DB::table('bank_stage_configs')->updateOrInsert(
                    ['bank_id' => $bankId, 'stage_id' => $sanctionStageId],
                    ['assigned_role' => null, 'phase_roles' => json_encode(['1' => 'office_employee']), 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function seedUsers(): void
    {
        $adminPassword = Hash::make('Admin@123');
        $defaultPassword = Hash::make('password');

        $users = [
            // ── Management ──
            ['id' => 1, 'name' => 'Super Admin', 'email' => 'superadmin@shfworld.com', 'is_active' => true, 'created_by' => null, 'phone' => '+91 99747 89089', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null, 'password' => $adminPassword],
            ['id' => 2, 'name' => 'Admin', 'email' => 'admin@shfworld.com', 'is_active' => true, 'created_by' => null, 'phone' => '+91 99747 89089', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null, 'password' => $adminPassword],
            ['id' => 3, 'name' => 'HARDIK NASIT', 'email' => 'hardik@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9726179351', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 4, 'name' => 'KRUPALI SHILU', 'email' => 'krupali@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9099089072', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 5, 'name' => 'Denish BDH', 'email' => 'denish@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            // ── Loan Advisors ──
            ['id' => 6, 'name' => 'JAYDEEP THESHIYA', 'email' => 'jaydeep@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9725248300', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 7, 'name' => 'KULDEEP VAISHNAV', 'email' => 'kuldeep@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '8866236688', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 8, 'name' => 'RAHUL MARAKANA', 'email' => 'rahul@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9913744162', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 9, 'name' => 'DIPAK VIRANI', 'email' => 'dipak@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '7600143537', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 10, 'name' => 'JAYESH MORI', 'email' => 'jayesh@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '8000232586', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 11, 'name' => 'CHIRAG DHOLAKIYA', 'email' => 'chirag@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9016348138', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 12, 'name' => 'DAXIT MALAVIYA', 'email' => 'daxit@shfworld.som', 'is_active' => true, 'created_by' => 2, 'phone' => '81600000286', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 13, 'name' => 'MILAN DHOLAKIYA', 'email' => 'milan@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '8401277654', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 14, 'name' => 'NITIN FALDU', 'email' => 'nitin@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '968701525', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            // ── Axis Bank (bank_id=3) ──
            ['id' => 15, 'name' => 'PARTH VORA', 'email' => 'parth.axis@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 3],
            ['id' => 16, 'name' => 'KARTIK PARIKH', 'email' => 'kartik.axis@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 3],
            ['id' => 17, 'name' => 'MAYAN PANSURIYA', 'email' => 'mayan.axis@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 3],
            ['id' => 18, 'name' => 'BHARGAV VIRANI', 'email' => 'bhargav@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '6355717561', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            // ── HDFC Bank (bank_id=1) ──
            ['id' => 19, 'name' => 'PRATIK GADHIYA', 'email' => 'pratik.hdfc@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 1],
            ['id' => 20, 'name' => 'RAKSHIT GANDHI', 'email' => 'rakshit.hdfc@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 1],
            // ── ICICI Bank (bank_id=2) ──
            ['id' => 21, 'name' => 'RUSHIKA JADAV', 'email' => 'rushika.icici@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 2],
            ['id' => 22, 'name' => 'AVINASH PANDYA', 'email' => 'avinash.icici@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 2],
            ['id' => 23, 'name' => 'MANTHAN THUMMAR', 'email' => 'manthan@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '9904408239', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            // ── Kotak Mahindra Bank (bank_id=4) ──
            ['id' => 24, 'name' => 'VISHAL VYAS', 'email' => 'vishal.kotak@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 4],
            ['id' => 25, 'name' => 'JAYDIP KARMATA', 'email' => 'jaydeep.kotak@shfworld.com', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 4],
            // ── Multi-bank office employee (HDFC + Kotak) ──
            ['id' => 26, 'name' => 'HARSHIT NAKUM', 'email' => 'harshit@shfworld.com', 'is_active' => true, 'created_by' => 2, 'phone' => '8511381814', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
        ];

        foreach ($users as $user) {
            $userPassword = $user['password'] ?? $defaultPassword;
            unset($user['password']);
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                array_merge($user, [
                    'password' => $userPassword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Update branch manager after users exist
        DB::table('branches')->where('id', 1)->update(['manager_id' => 2, 'updated_by' => 1]);
    }

    // Legacy seedRolePermissions() and seedTaskRolePermissions() removed —
    // role-permission mappings are managed by the unified_roles_system migration
    // and editable at runtime via Permissions and Loan Settings pages.

    private function seedRoleAssignments(): void
    {
        $roleIds = DB::table('roles')->pluck('id', 'slug')->toArray();

        // Map users to roles: [email => [role_slugs]]
        $userRoles = [
            'superadmin@shfworld.com' => ['super_admin'],
            'admin@shfworld.com' => ['admin'],
            'hardik@shfworld.com' => ['branch_manager'],
            'denish@shfworld.com' => ['bdh'],
        ];

        // All loan advisors
        $advisorEmails = [
            'jaydeep@shfworld.com', 'kuldeep@shfworld.com',
            'rahul@shfworld.com', 'dipak@shfworld.com', 'jayesh@shfworld.com',
            'chirag@shfworld.com', 'daxit@shfworld.som', 'milan@shfworld.com',
            'nitin@shfworld.com',
        ];
        foreach ($advisorEmails as $email) {
            $userRoles[$email] = ['loan_advisor'];
        }

        // Bank employees
        $bankEmails = [
            'pratik.hdfc@shfworld.com', 'rakshit.hdfc@shfworld.com',
            'vishal.kotak@shfworld.com', 'jaydeep.kotak@shfworld.com',
            'parth.axis@shfworld.com', 'kartik.axis@shfworld.com', 'mayan.axis@shfworld.com',
            'rushika.icici@shfworld.com', 'avinash.icici@shfworld.com',
        ];
        foreach ($bankEmails as $email) {
            $userRoles[$email] = ['bank_employee'];
        }

        // Branch managers
        $userRoles['krupali@shfworld.com'] = ['branch_manager'];

        // Office employees
        $userRoles['bhargav@shfworld.com'] = ['office_employee'];
        $userRoles['harshit@shfworld.com'] = ['office_employee'];
        $userRoles['manthan@shfworld.com'] = ['office_employee'];

        foreach ($userRoles as $email => $roleSlugs) {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if (! $userId) {
                continue;
            }
            foreach ($roleSlugs as $slug) {
                if (! isset($roleIds[$slug])) {
                    continue;
                }
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $userId,
                    'role_id' => $roleIds[$slug],
                ]);
            }
        }
    }

    private function seedRolePermissionMappings(): void
    {
        $roleIds = DB::table('roles')->pluck('id', 'slug')->toArray();
        $permIds = DB::table('permissions')->pluck('id', 'slug')->toArray();

        // Define permissions per role (matches current production DB exactly)
        $rolePermissions = [
            'super_admin' => array_values(array_intersect_key($permIds, array_flip([
                'manage_customers', 'view_customers', 'impersonate_users', 'view_dashboard', 'manage_notifications',
                'transfer_loan_stages', 'reject_loan', 'change_loan_status', 'view_loan_timeline',
                'manage_disbursement', 'manage_valuation', 'raise_query', 'resolve_query',
                'download_pdf_branded', 'download_pdf_plain',
                'view_settings', 'edit_company_info', 'edit_banks', 'edit_documents', 'edit_tenures',
                'edit_charges', 'edit_services', 'edit_gst',
                'create_quotation', 'generate_pdf', 'view_own_quotations', 'view_all_quotations',
                'delete_quotations', 'download_pdf',
                'view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles',
                'change_own_password', 'manage_permissions', 'view_activity_log',
                'convert_to_loan', 'view_loans', 'view_all_loans', 'create_loan', 'edit_loan',
                'delete_loan', 'manage_loan_documents', 'manage_loan_stages',
                'add_remarks', 'manage_workflow_config',
                'upload_loan_documents', 'download_loan_documents', 'delete_loan_files',
                // Note: skip_loan_stages (id 46) is excluded for super_admin
            ]))),

            'admin' => array_values(array_intersect_key($permIds, array_flip([
                'manage_customers', 'view_customers', 'impersonate_users', 'view_dashboard', 'manage_notifications',
                'transfer_loan_stages', 'reject_loan', 'change_loan_status', 'view_loan_timeline',
                'manage_disbursement', 'manage_valuation', 'raise_query', 'resolve_query',
                'view_settings', 'edit_company_info', 'edit_banks', 'edit_documents', 'edit_tenures',
                'edit_charges', 'edit_services', 'edit_gst',
                'create_quotation', 'generate_pdf', 'view_own_quotations', 'view_all_quotations',
                'delete_quotations', 'download_pdf',
                'view_users', 'create_users', 'edit_users', 'assign_roles',
                'change_own_password', 'manage_permissions', 'view_activity_log',
                'convert_to_loan', 'view_loans', 'view_all_loans', 'create_loan', 'edit_loan',
                'delete_loan', 'manage_loan_documents', 'manage_loan_stages',
                'add_remarks', 'manage_workflow_config',
                'upload_loan_documents', 'download_loan_documents', 'delete_loan_files',
                'view_all_tasks',
                // Note: delete_users (id 33) excluded, download_pdf_branded/plain excluded
            ]))),

            'branch_manager' => array_values(array_intersect_key($permIds, array_flip([
                'manage_customers', 'view_customers', 'view_dashboard', 'manage_notifications',
                'transfer_loan_stages', 'reject_loan', 'change_loan_status', 'view_loan_timeline',
                'manage_disbursement', 'manage_valuation', 'raise_query', 'resolve_query',
                'create_quotation', 'generate_pdf', 'view_own_quotations', 'view_all_quotations', 'download_pdf',
                'view_users', 'change_own_password', 'view_activity_log',
                'convert_to_loan', 'view_loans', 'view_all_loans', 'create_loan', 'edit_loan',
                'manage_loan_documents', 'manage_loan_stages', 'add_remarks',
            ]))),

            'loan_advisor' => array_values(array_intersect_key($permIds, array_flip([
                'manage_customers', 'view_customers', 'view_dashboard', 'manage_notifications',
                'transfer_loan_stages', 'change_loan_status', 'view_loan_timeline',
                'manage_disbursement', 'raise_query', 'resolve_query',
                'create_quotation', 'generate_pdf', 'view_own_quotations', 'download_pdf',
                'change_own_password',
                'convert_to_loan', 'view_loans', 'create_loan', 'edit_loan',
                'manage_loan_documents', 'manage_loan_stages', 'add_remarks',
            ]))),

            'bank_employee' => array_values(array_intersect_key($permIds, array_flip([
                'view_customers', 'view_dashboard', 'manage_notifications',
                'view_loan_timeline', 'raise_query',
                'change_own_password',
                'view_loans', 'manage_loan_stages', 'add_remarks',
            ]))),

            'office_employee' => array_values(array_intersect_key($permIds, array_flip([
                'view_customers', 'view_dashboard', 'manage_notifications',
                'transfer_loan_stages', 'view_loan_timeline', 'manage_valuation', 'raise_query',
                'change_own_password',
                'view_loans', 'edit_loan',
                'manage_loan_documents', 'manage_loan_stages', 'add_remarks',
            ]))),
        ];

        // BDH gets same permissions as branch_manager
        $rolePermissions['bdh'] = $rolePermissions['branch_manager'];

        // Clear existing and re-insert
        DB::table('role_permission')->truncate();

        foreach ($rolePermissions as $roleSlug => $permissionIds) {
            $roleId = $roleIds[$roleSlug] ?? null;
            if (! $roleId) {
                continue;
            }
            foreach ($permissionIds as $permId) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    private function seedUserBranches(): void
    {
        // All users get Rajkot branch (branch_id=1) as default
        $userIds = range(1, 26);
        foreach ($userIds as $userId) {
            $isDefaultOE = false;
            DB::table('user_branches')->updateOrInsert(
                ['user_id' => $userId, 'branch_id' => 1],
                ['is_default_office_employee' => $isDefaultOE, 'created_at' => null, 'updated_at' => null]
            );
        }
    }

    private function seedBankEmployees(): void
    {
        $employees = [
            // Axis Bank (bank_id=3): PARTH=15, KARTIK=16, MAYAN=17, BHARGAV=18 (office)
            ['bank_id' => 3, 'user_id' => 15, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 3, 'user_id' => 16, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 3, 'user_id' => 17, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 3, 'user_id' => 18, 'is_default' => false, 'location_id' => null],
            // HDFC Bank (bank_id=1): PRATIK=19, RAKSHIT=20
            ['bank_id' => 1, 'user_id' => 19, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 1, 'user_id' => 20, 'is_default' => false, 'location_id' => null],
            // ICICI Bank (bank_id=2): RUSHIKA=21, AVINASH=22, MANTHAN=23 (office)
            ['bank_id' => 2, 'user_id' => 21, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 2, 'user_id' => 22, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 2, 'user_id' => 23, 'is_default' => false, 'location_id' => null],
            // Kotak Bank (bank_id=4): VISHAL=24, JAYDIP=25
            ['bank_id' => 4, 'user_id' => 24, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 4, 'user_id' => 25, 'is_default' => false, 'location_id' => null],
            // HARSHIT=26 (office, HDFC + Kotak)
            ['bank_id' => 1, 'user_id' => 26, 'is_default' => false, 'location_id' => null],
            ['bank_id' => 4, 'user_id' => 26, 'is_default' => false, 'location_id' => null],
        ];

        foreach ($employees as $emp) {
            DB::table('bank_employees')->updateOrInsert(
                ['bank_id' => $emp['bank_id'], 'user_id' => $emp['user_id'], 'location_id' => $emp['location_id']],
                array_merge($emp, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBankDefaults(): void
    {
        // Set Employee 1 as default per city for each bank
        // bank_id => user_id (Employee 1)
        $defaults = [1 => 19, 2 => 21, 3 => 15, 4 => 24];

        foreach ($defaults as $bankId => $userId) {
            $locationIds = DB::table('bank_location')
                ->where('bank_id', $bankId)
                ->pluck('location_id')
                ->toArray();

            foreach ($locationIds as $locId) {
                // Clear any existing default for this bank+city
                DB::table('bank_employees')
                    ->where('bank_id', $bankId)
                    ->where('location_id', $locId)
                    ->update(['is_default' => false]);

                // Upsert the default record
                DB::table('bank_employees')->updateOrInsert(
                    ['bank_id' => $bankId, 'user_id' => $userId, 'location_id' => $locId],
                    ['is_default' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function seedBankLocations(): void
    {
        $bankLocations = [
            ['bank_id' => 1, 'location_id' => 2],
            ['bank_id' => 2, 'location_id' => 2],
            ['bank_id' => 3, 'location_id' => 2],
            ['bank_id' => 4, 'location_id' => 2],
        ];

        foreach ($bankLocations as $bl) {
            DB::table('bank_location')->updateOrInsert(
                ['bank_id' => $bl['bank_id'], 'location_id' => $bl['location_id']],
                array_merge($bl, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedLocationUsers(): void
    {
        // Assign all users to Gujarat (state, id=1) and Rajkot (city, id=2)
        $allUserIds = DB::table('users')->pluck('id')->toArray();

        foreach ($allUserIds as $userId) {
            foreach ([1, 2] as $locationId) {
                DB::table('location_user')->updateOrInsert(
                    ['location_id' => $locationId, 'user_id' => $userId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function seedBankCharges(): void
    {
        $charges = [
            ['bank_name' => 'Axis Bank', 'pf' => 0.50, 'admin' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate' => 1000, 'tc' => 4500, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
            ['bank_name' => 'HDFC Bank', 'pf' => 0.60, 'admin' => 0, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate' => 2500, 'tc' => 0, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
            ['bank_name' => 'ICICI Bank', 'pf' => 0.00, 'admin' => 0, 'stamp_notary' => 0, 'registration_fee' => 0, 'advocate' => 2000, 'tc' => 2500, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
            ['bank_name' => 'Kotak Mahindra Bank', 'pf' => 0.50, 'admin' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate' => 2500, 'tc' => 0, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
        ];

        foreach ($charges as $charge) {
            DB::table('bank_charges')->updateOrInsert(
                ['bank_name' => $charge['bank_name']],
                array_merge($charge, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedProducts(): void
    {
        $products = [
            // ICICI (bank_id=2)
            ['bank_id' => 2, 'name' => 'Home Loan'],
            ['bank_id' => 2, 'name' => 'LAP'],
            ['bank_id' => 2, 'name' => 'OD'],
            ['bank_id' => 2, 'name' => 'PRATHAM'],
            // Axis (bank_id=3)
            ['bank_id' => 3, 'name' => 'Home Loan'],
            ['bank_id' => 3, 'name' => 'LAP'],
            ['bank_id' => 3, 'name' => 'ASHA'],
            // HDFC (bank_id=1)
            ['bank_id' => 1, 'name' => 'Home Loan'],
            ['bank_id' => 1, 'name' => 'LAP'],
            // Kotak (bank_id=4)
            ['bank_id' => 4, 'name' => 'Home Loan'],
            ['bank_id' => 4, 'name' => 'LAP'],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['bank_id' => $product['bank_id'], 'name' => $product['name']],
                array_merge($product, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedProductStages(): void
    {
        // Default bank employee per bank+product: [bank_id => [product_name => user_id]]
        $bankEmployeeMap = [
            1 => ['Home Loan' => 19, 'LAP' => 20],                    // HDFC: Pratik=Home Loan, Rakshit=LAP
            2 => ['_default' => 21],                                    // ICICI: Rushika for all
            3 => ['Home Loan' => 15, 'LAP' => 16, 'ASHA' => 17],      // Axis: Parth=Home Loan, Kartik=LAP, Mayan=ASHA
            4 => ['Home Loan' => 24, 'LAP' => 25],                    // Kotak: Vishal=Home Loan, Jaydip=LAP
        ];

        // Default office employee per bank
        $officeEmployeeMap = [
            1 => 26,  // HDFC → HARSHIT
            2 => 23,  // ICICI → MANTHAN
            3 => 18,  // Axis → BHARGAV
            4 => 26,  // Kotak → HARSHIT
        ];

        // Pre-load bank stage configs for resolving phase roles
        $bankStageConfigs = DB::table('bank_stage_configs')->get()->groupBy(function ($c) {
            return $c->bank_id.'_'.$c->stage_id;
        })->map->first();

        $products = DB::table('products')->where('is_active', true)->get();
        $stages = DB::table('stages')->where('is_enabled', true)->orderBy('sequence_order')->get();

        foreach ($products as $product) {
            $bankMap = $bankEmployeeMap[$product->bank_id] ?? [];
            $bankEmployeeId = $bankMap[$product->name] ?? $bankMap['_default'] ?? null;
            $officeEmployeeId = $officeEmployeeMap[$product->bank_id] ?? null;

            $bankLocationIds = DB::table('bank_location')
                ->where('bank_id', $product->bank_id)
                ->pluck('location_id')
                ->toArray();

            $order = 0;
            foreach ($stages as $stage) {
                $assignedRole = $stage->assigned_role ?? 'task_owner';

                // Resolve stage-level default user
                $defaultUserId = null;
                if ($assignedRole === 'bank_employee') {
                    $defaultUserId = $bankEmployeeId;
                } elseif ($assignedRole === 'office_employee') {
                    $defaultUserId = $officeEmployeeId;
                }

                DB::table('product_stages')->updateOrInsert(
                    ['product_id' => $product->id, 'stage_id' => $stage->id],
                    ['is_enabled' => true, 'allow_skip' => false, 'auto_skip' => false, 'sort_order' => $order++, 'default_user_id' => $defaultUserId, 'created_at' => now(), 'updated_at' => now()]
                );

                $ps = DB::table('product_stages')
                    ->where('product_id', $product->id)
                    ->where('stage_id', $stage->id)
                    ->first();

                if (! $ps) {
                    continue;
                }

                // Phase-level user assignments via product_stage_users (with phase_index)
                $subActions = json_decode($stage->sub_actions ?? '[]', true);

                if (! empty($subActions) && ! empty($bankLocationIds)) {
                    // Check bank override for this stage
                    $bsc = $bankStageConfigs[$product->bank_id.'_'.$stage->id] ?? null;
                    $bankPhaseRoles = $bsc ? json_decode($bsc->phase_roles ?? '{}', true) : [];

                    foreach ($subActions as $saIdx => $sa) {
                        // Resolve: bank override → master default
                        $phaseRole = $bankPhaseRoles[(string) $saIdx] ?? $sa['role'] ?? 'task_owner';

                        if ($phaseRole === 'task_owner') {
                            continue; // No user assignment for task_owner phases
                        }

                        $userId = ($phaseRole === 'bank_employee') ? $bankEmployeeId : $officeEmployeeId;
                        if (! $userId) {
                            continue;
                        }

                        foreach ($bankLocationIds as $locId) {
                            DB::table('product_stage_users')->updateOrInsert(
                                ['product_stage_id' => $ps->id, 'user_id' => $userId, 'location_id' => $locId, 'branch_id' => null, 'phase_index' => $saIdx],
                                ['is_default' => true, 'created_at' => now(), 'updated_at' => now()]
                            );
                        }
                    }
                }
            }
        }
    }

    private function seedProductStageUsers(): void
    {
        // Default bank employee per bank+product: [bank_id => [product_name => user_id]]
        $bankEmployeeMap = [
            1 => ['Home Loan' => 19, 'LAP' => 20],                    // HDFC
            2 => ['_default' => 21],                                    // ICICI
            3 => ['Home Loan' => 15, 'LAP' => 16, 'ASHA' => 17],      // Axis
            4 => ['Home Loan' => 24, 'LAP' => 25],                    // Kotak
        ];

        // Default office employee per bank
        $officeEmployeeMap = [
            1 => 26,  // HDFC → HARSHIT
            2 => 23,  // ICICI → MANTHAN
            3 => 18,  // Axis → BHARGAV
            4 => 26,  // Kotak → HARSHIT
        ];

        $products = DB::table('products')->where('is_active', true)->get();
        $stages = DB::table('stages')->where('is_enabled', true)->orderBy('sequence_order')->get();

        foreach ($products as $product) {
            $bankMap = $bankEmployeeMap[$product->bank_id] ?? [];
            $bankEmployeeId = $bankMap[$product->name] ?? $bankMap['_default'] ?? null;
            $officeEmployeeId = $officeEmployeeMap[$product->bank_id] ?? null;

            $bankLocationIds = DB::table('bank_location')
                ->where('bank_id', $product->bank_id)
                ->pluck('location_id')
                ->toArray();

            foreach ($stages as $stage) {
                $ps = DB::table('product_stages')
                    ->where('product_id', $product->id)
                    ->where('stage_id', $stage->id)
                    ->first();

                if (! $ps) {
                    continue;
                }

                $assignedRole = $stage->assigned_role ?? 'task_owner';

                // Stage-level user assignment (phase_index = null) for single-phase BE/OE stages
                if ($assignedRole !== 'task_owner') {
                    $userId = ($assignedRole === 'bank_employee') ? $bankEmployeeId : $officeEmployeeId;
                    if ($userId) {
                        foreach ($bankLocationIds as $locId) {
                            DB::table('product_stage_users')->updateOrInsert(
                                ['product_stage_id' => $ps->id, 'user_id' => $userId, 'location_id' => $locId, 'branch_id' => null, 'phase_index' => null],
                                ['is_default' => true, 'created_at' => now(), 'updated_at' => now()]
                            );
                        }
                    }
                }
            }
        }
    }

    private function seedAppConfig(): void
    {
        $config = [
            'companyName' => 'Shreenathji Home Finance',
            'companyAddress' => 'OFFICE NO 911, R K PRIME, CIRCLE, next to SILVER HEIGHT, Nehru Nagar Co operative Society, Nana Mava, Rajkot, Gujarat 360004',
            'companyPhone' => '+91 99747 89089',
            'companyEmail' => 'info@shf.com',
            'banks' => ['HDFC Bank', 'ICICI Bank', 'Axis Bank', 'Kotak Mahindra Bank'],
            'iomCharges' => [
                'thresholdAmount' => 10000000,
                'fixedCharge' => 7000,
                'percentageAbove' => 0.35,
            ],
            'tenures' => [5, 10, 15, 20],
            'documents_en' => [
                'proprietor' => ['Passport Size Photographs Both', 'PAN Card Both', 'Aadhaar Card Both', 'GST Registration Certificate', 'Udyam Registration Certificate', 'ITR (Last 3 years)', 'Bank Statement (Last 12 months)', 'Current Loan Statement ( if applicable )', 'Property File Xerox'],
                'partnership_llp' => ['PAN Card of Firm', 'Partnership Deed', 'GST Registration Certificate', 'ITR With Audit of Firm (Last 3 years)', 'Firm Current A/c Bank Statement (Last 12 months)', 'Current Loan Statement ( if applicable )', 'Passport Size Photographs of All Partners', 'PAN Card of All Partners', 'Aadhaar Card of All Partners', 'ITR of Partners (Last 3 years)', 'Bank Statement of Partners (Last 12 months)'],
                'pvt_ltd' => ['PAN Card of Company', 'Memorandum of Association (MOA)', 'Articles of Association (AOA)', 'GST Registration Certificate', 'ITR With Audit Report of Company (Last 3 years)', 'Current Loan Statement ( if applicable )', 'Company Current A/C Statement (Last 12 months)', 'Passport Size Photographs of All Director', 'PAN Card of All Directors', 'Aadhaar Card of All Directors', 'ITR of Directors (Last 3 years)', 'Bank Statement of Directors (Last 12 months)'],
                'salaried' => ['Passport Size Photographs Both', 'PAN Card Both', 'Aadhaar Card Both', 'Salary Slips (Last 6 months)', 'ITR (Last 2 years)', 'Form 16 (Last 2 years)', 'Bank Statement (Last 6 months)', 'Property Documents (if applicable)'],
            ],
            'documents_gu' => [
                'proprietor' => ['Passport Size Photographs Both', 'PAN Card Both', 'Aadhaar Card Both', 'GST Registration Certificate', 'Udyam Registration Certificate', 'ITR (Last 3 years)', 'Bank Statement (Last 12 months)', 'Current Loan Statement (if applicable)', 'Property File Xerox'],
                'partnership_llp' => ['PAN Card of Firm', 'Partnership Deed', 'GST Registration Certificate', 'ITR With Audit of Firm (Last 3 years)', 'Firm Current A/c Bank Statement (Last 12 months)', 'Current Loan Statement (if applicable)', 'Passport Size Photographs of All Partners', 'PAN Card of All Partners', 'Aadhaar Card of All Partners', 'ITR of Partners (Last 3 years)', 'Bank Statement of Partners (Last 12 months)'],
                'pvt_ltd' => ['PAN Card of Company', 'Memorandum of Association (MOA)', 'Articles of Association (AOA)', 'GST Registration Certificate', 'ITR With Audit Report of Company (Last 3 years)', 'Current Loan Statement (if applicable)', 'Company Current A/C Statement (Last 12 months)', 'Passport Size Photographs of All Director', 'PAN Card of All Directors', 'Aadhaar Card of All Directors', 'ITR of Directors (Last 3 years)', 'Bank Statement of Directors (Last 12 months)'],
                'salaried' => ['Passport Size Photographs Both', 'PAN Card Both', 'Aadhaar Card Both', 'Salary Slips (Last 6 months)', 'ITR (Last 2 years)', 'Form 16 (Last 2 years)', 'Bank Statement (Last 6 months)', 'Property Documents (if applicable)'],
            ],
            'gstPercent' => 18,
            'ourServices' => 'Home Loan, Mortgage Loan, Commercial Loan, Industrial Loan,Land Loan, Over Draft(OD)',
        ];

        DB::table('app_config')->updateOrInsert(
            ['config_key' => 'main'],
            [
                'config_json' => json_encode($config),
                'created_at' => '2026-02-27 15:59:58',
                'updated_at' => now(),
            ]
        );
    }

    private function seedAppSettings(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['setting_key' => 'additional_notes'],
            [
                'setting_value' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required",
                'updated_at' => '2026-04-06 06:47:53',
            ]
        );
    }

    private function seedQuotations(): void
    {
        $quotations = [
            ['id' => 1, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'ASHOKBHAI CHHANGOMALBHAI LALWANI', 'customer_type' => 'proprietor', 'loan_amount' => 4200000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed", 'prepared_by_name' => 'KULDEEP PATEL', 'prepared_by_mobile' => '8866236688', 'selected_tenures' => '[20]', 'created_at' => '2026-02-28 15:05:53', 'updated_at' => '2026-02-28 15:05:53'],
            ['id' => 2, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'AMIPARA MAHESHBHAI UKABHAI', 'customer_type' => 'proprietor', 'loan_amount' => 5820000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed", 'prepared_by_name' => 'HARDIK NASIT', 'prepared_by_mobile' => '+91 9726179351', 'selected_tenures' => '[15,20]', 'created_at' => '2026-03-03 12:51:14', 'updated_at' => '2026-03-03 12:51:14'],
            ['id' => 3, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'AMIPARA MAHESHBHAI UKABHAI', 'customer_type' => 'proprietor', 'loan_amount' => 5820000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed", 'prepared_by_name' => 'HARDIK NASIT', 'prepared_by_mobile' => '+91 9726179351', 'selected_tenures' => '[15,20]', 'created_at' => '2026-03-03 12:53:29', 'updated_at' => '2026-03-03 12:53:29'],
            ['id' => 4, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'AMIPARA MAHESHBHAI UKABHAI', 'customer_type' => 'proprietor', 'loan_amount' => 5820000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed", 'prepared_by_name' => 'HARDIK NASIT', 'prepared_by_mobile' => '+91 9726179351', 'selected_tenures' => '[15,20]', 'created_at' => '2026-03-03 12:54:43', 'updated_at' => '2026-03-03 12:54:43'],
            ['id' => 5, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'Brijesh Kumar unjiya', 'customer_type' => 'proprietor', 'loan_amount' => 2500000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nProperty insurance is mandatory", 'prepared_by_name' => 'Nitin faldu', 'prepared_by_mobile' => '+91 9687501525', 'selected_tenures' => '[15]', 'created_at' => '2026-03-05 10:51:54', 'updated_at' => '2026-03-05 10:51:54'],
            ['id' => 6, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'Brijesh Kumar unjiya', 'customer_type' => 'proprietor', 'loan_amount' => 2500000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nProperty insurance is mandatory", 'prepared_by_name' => 'Nitin faldu', 'prepared_by_mobile' => '+91 9687501525', 'selected_tenures' => '[15]', 'created_at' => '2026-03-05 10:56:14', 'updated_at' => '2026-03-05 10:56:14'],
            ['id' => 7, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'PRASHANT KISHORBHAI JADAV', 'customer_type' => 'proprietor', 'loan_amount' => 5000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nInsurance is mandatory\nAXIS BANK LTD account opening is mandatory", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '09016348138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-03-05 13:01:36', 'updated_at' => '2026-03-05 13:01:36'],
            ['id' => 8, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'SUBHASBHAI SORATHIYA', 'customer_type' => 'proprietor', 'loan_amount' => 2600000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nICICI account opening and insurance are not required\nWe will minimize charges as much as possible", 'prepared_by_name' => 'Admin', 'prepared_by_mobile' => '+91 9974277500', 'selected_tenures' => '[15,20]', 'created_at' => '2026-03-09 11:33:47', 'updated_at' => '2026-03-09 11:33:47'],
            ['id' => 9, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'MEGHANI CHANDUBHAI UKABHAI', 'customer_type' => 'proprietor', 'loan_amount' => 7000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nWe will minimize charges as much as possible", 'prepared_by_name' => 'RUSHI SOJITRA  &  KULDEEP PATEL', 'prepared_by_mobile' => '8460244864  &  8866236688', 'selected_tenures' => '[15]', 'created_at' => '2026-03-09 12:45:57', 'updated_at' => '2026-03-09 12:45:57'],
            ['id' => 10, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'HIRAPARA KEYUR', 'customer_type' => 'proprietor', 'loan_amount' => 1900000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nWe will minimize charges as much as possible", 'prepared_by_name' => 'Denish Malviya', 'prepared_by_mobile' => '+91 99747 89089', 'selected_tenures' => '[20]', 'created_at' => '2026-03-12 10:03:32', 'updated_at' => '2026-03-12 10:03:32'],
            ['id' => 11, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => '...', 'customer_type' => 'proprietor', 'loan_amount' => 2000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nLOGIN FEE 3000 /- NON REFUNDABLE", 'prepared_by_name' => 'KULDEEP PATEL', 'prepared_by_mobile' => '8866236688', 'selected_tenures' => '[20]', 'created_at' => '2026-03-15 06:06:32', 'updated_at' => '2026-03-15 06:06:32'],
            ['id' => 12, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'SHREE GANESH JEWELLERS', 'customer_type' => 'partnership_llp', 'loan_amount' => 5000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nLOGIN FEE 5000 /- NON REFUNDABLE", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '90163 48138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-03-19 13:51:11', 'updated_at' => '2026-03-19 13:51:11'],
            ['id' => 13, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'TANSUKH DHIRAJLAL VEKARIYA', 'customer_type' => 'proprietor', 'loan_amount' => 3500000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Rate of interest depends on customer's CIBIL score\nLoan approval at shown rate is not guaranteed\nLOGIN FEE 5000 /- NON REFUNDABLE", 'prepared_by_name' => 'Denish Malviya', 'prepared_by_mobile' => '+91 99747 89089', 'selected_tenures' => '[20]', 'created_at' => '2026-03-26 13:04:28', 'updated_at' => '2026-03-26 13:04:28'],
            ['id' => 14, 'loan_id' => null, 'location_id' => 2, 'user_id' => 9, 'customer_name' => 'SAJANBEN MUKESHBHAI AAL', 'customer_type' => 'proprietor', 'loan_amount' => 4000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nProperty file full comy required (with succession, comy will not be returned)\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 5000/- non-refundable", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '9016348138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-03-28 07:44:32', 'updated_at' => '2026-03-28 07:44:32'],
            ['id' => 15, 'loan_id' => null, 'location_id' => 2, 'user_id' => 2, 'customer_name' => 'HARDIK VEKARIYA', 'customer_type' => 'proprietor', 'loan_amount' => 3000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 5000/- non-refundable", 'prepared_by_name' => 'Denish Malviya', 'prepared_by_mobile' => '+91 99747 89089', 'selected_tenures' => '[10,15]', 'created_at' => '2026-03-30 14:10:33', 'updated_at' => '2026-03-30 14:10:33'],
            ['id' => 16, 'loan_id' => null, 'location_id' => 2, 'user_id' => 9, 'customer_name' => 'PRASHANTBHAI JADAV', 'customer_type' => 'proprietor', 'loan_amount' => 2000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 5000/- non-refundable", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '9016348138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-04-04 12:06:48', 'updated_at' => '2026-04-04 12:06:48'],
            ['id' => 17, 'loan_id' => null, 'location_id' => 2, 'user_id' => 9, 'customer_name' => 'PRASHANTBHAI JADAV', 'customer_type' => 'proprietor', 'loan_amount' => 2000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 5000/- non-refundable", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '9016348138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-04-04 12:08:33', 'updated_at' => '2026-04-07 05:57:48'],
            ['id' => 18, 'loan_id' => null, 'location_id' => 2, 'user_id' => 9, 'customer_name' => 'NARIGARA SURESHBHAI R', 'customer_type' => 'proprietor', 'loan_amount' => 1600000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '9016348138', 'selected_tenures' => '[15,20]', 'created_at' => '2026-04-06 06:47:53', 'updated_at' => '2026-04-07 05:29:57'],
        ];

        // ID mapping: old_id => new_id for quotation_banks/emi/documents
        // 21=>1, 22=>2, 23=>3, 24=>4, 25=>5, 26=>6, 27=>7, 29=>8, 30=>9, 33=>10, 35=>11, 36=>12, 37=>13, 38=>14, 40=>15, 41=>16, 42=>17, 43=>18

        foreach ($quotations as $q) {
            DB::table('quotations')->updateOrInsert(
                ['id' => $q['id']],
                $q
            );
        }
    }

    private function seedQuotationBanks(): void
    {
        $banks = [
            ['id' => 1, 'quotation_id' => 1, 'bank_name' => 'ICICI Bank', 'roi_min' => 7.55, 'roi_max' => 7.65, 'pf_charge' => 0.25, 'admin_charge' => 5000, 'stamp_notary' => 2000, 'registration_fee' => 6000, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 38290],
            ['id' => 2, 'quotation_id' => 2, 'bank_name' => 'ICICI Bank', 'roi_min' => 7.40, 'roi_max' => 7.75, 'pf_charge' => 0.25, 'admin_charge' => 5000, 'stamp_notary' => 2000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 42469],
            ['id' => 3, 'quotation_id' => 2, 'bank_name' => 'HDFC Bank', 'roi_min' => 7.40, 'roi_max' => 7.50, 'pf_charge' => 0.50, 'admin_charge' => 2360, 'stamp_notary' => 3000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 55023],
            ['id' => 4, 'quotation_id' => 3, 'bank_name' => 'ICICI Bank', 'roi_min' => 7.40, 'roi_max' => 7.75, 'pf_charge' => 0.25, 'admin_charge' => 5000, 'stamp_notary' => 2000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 42469],
            ['id' => 5, 'quotation_id' => 3, 'bank_name' => 'HDFC Bank', 'roi_min' => 7.35, 'roi_max' => 7.50, 'pf_charge' => 0.20, 'admin_charge' => 0, 'stamp_notary' => 3000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 31635],
            ['id' => 6, 'quotation_id' => 4, 'bank_name' => 'ICICI Bank', 'roi_min' => 7.40, 'roi_max' => 7.75, 'pf_charge' => 0.25, 'admin_charge' => 5000, 'stamp_notary' => 2000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 42469],
            ['id' => 7, 'quotation_id' => 4, 'bank_name' => 'HDFC Bank', 'roi_min' => 7.35, 'roi_max' => 7.50, 'pf_charge' => 0.20, 'admin_charge' => 0, 'stamp_notary' => 3000, 'registration_fee' => 6000, 'advocate_fees' => 3000, 'iom_charge' => 5900, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 31635],
            ['id' => 8, 'quotation_id' => 5, 'bank_name' => 'HDFC Bank', 'roi_min' => 9.00, 'roi_max' => 9.15, 'pf_charge' => 0.60, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 35600],
            ['id' => 9, 'quotation_id' => 5, 'bank_name' => 'Kotak Mahindra Bank', 'roi_min' => 9.00, 'roi_max' => 9.20, 'pf_charge' => 0.50, 'admin_charge' => 11000, 'stamp_notary' => 3000, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => 'Login fees', 'extra1_amount' => 5900, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 52030],
            ['id' => 10, 'quotation_id' => 5, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.05, 'roi_max' => 9.30, 'pf_charge' => 0.60, 'admin_charge' => 5000, 'stamp_notary' => 600, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2000, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 41600],
            ['id' => 11, 'quotation_id' => 5, 'bank_name' => 'Axis Bank', 'roi_min' => 9.00, 'roi_max' => 9.25, 'pf_charge' => 0.60, 'admin_charge' => 0, 'stamp_notary' => 2000, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 35100],
            ['id' => 12, 'quotation_id' => 6, 'bank_name' => 'HDFC Bank', 'roi_min' => 9.00, 'roi_max' => 9.15, 'pf_charge' => 0.60, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 35600],
            ['id' => 13, 'quotation_id' => 6, 'bank_name' => 'Kotak Mahindra Bank', 'roi_min' => 9.00, 'roi_max' => 9.20, 'pf_charge' => 0.50, 'admin_charge' => 0, 'stamp_notary' => 3000, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => 'Login fees', 'extra1_amount' => 5900, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 39050],
            ['id' => 14, 'quotation_id' => 6, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.05, 'roi_max' => 9.30, 'pf_charge' => 0.60, 'admin_charge' => 5000, 'stamp_notary' => 600, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2000, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 41600],
            ['id' => 15, 'quotation_id' => 6, 'bank_name' => 'Axis Bank', 'roi_min' => 9.00, 'roi_max' => 9.25, 'pf_charge' => 0.60, 'admin_charge' => 0, 'stamp_notary' => 2000, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 35100],
            ['id' => 16, 'quotation_id' => 7, 'bank_name' => 'Axis Bank', 'roi_min' => 9.00, 'roi_max' => 9.15, 'pf_charge' => 0.65, 'admin_charge' => 0, 'stamp_notary' => 4500, 'registration_fee' => 5900, 'advocate_fees' => 4600, 'iom_charge' => 5500, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 58850],
            ['id' => 17, 'quotation_id' => 8, 'bank_name' => 'ICICI Bank', 'roi_min' => 8.90, 'roi_max' => 9.40, 'pf_charge' => 0.60, 'admin_charge' => 5000, 'stamp_notary' => 600, 'registration_fee' => 7000, 'advocate_fees' => 2500, 'iom_charge' => 4000, 'tc_report' => 2000, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 40408],
            ['id' => 18, 'quotation_id' => 9, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.00, 'roi_max' => 9.15, 'pf_charge' => 0.75, 'admin_charge' => 5000, 'stamp_notary' => 1000, 'registration_fee' => 6000, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 84350],
            ['id' => 19, 'quotation_id' => 10, 'bank_name' => 'HDFC Bank', 'roi_min' => 7.20, 'roi_max' => 7.50, 'pf_charge' => 0.25, 'admin_charge' => 0, 'stamp_notary' => 3000, 'registration_fee' => 5000, 'advocate_fees' => 3000, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 23605],
            ['id' => 20, 'quotation_id' => 11, 'bank_name' => 'ICICI Bank', 'roi_min' => 7.55, 'roi_max' => 7.75, 'pf_charge' => 0.15, 'admin_charge' => 5000, 'stamp_notary' => 1500, 'registration_fee' => 6000, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 28940],
            ['id' => 21, 'quotation_id' => 12, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.05, 'roi_max' => 9.25, 'pf_charge' => 0.65, 'admin_charge' => 5900, 'stamp_notary' => 4500, 'registration_fee' => 6000, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 67812],
            ['id' => 22, 'quotation_id' => 12, 'bank_name' => 'Kotak Mahindra Bank', 'roi_min' => 8.50, 'roi_max' => 9.55, 'pf_charge' => 0.70, 'admin_charge' => 0, 'stamp_notary' => 4500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 61200],
            ['id' => 23, 'quotation_id' => 13, 'bank_name' => 'HDFC Bank', 'roi_min' => 7.20, 'roi_max' => 7.40, 'pf_charge' => 0.15, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5000, 'advocate_fees' => 3000, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 23695],
            ['id' => 24, 'quotation_id' => 14, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.40, 'roi_max' => 9.45, 'pf_charge' => 0.60, 'admin_charge' => 5900, 'stamp_notary' => 4500, 'registration_fee' => 6000, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 57782],
            ['id' => 25, 'quotation_id' => 15, 'bank_name' => 'HDFC Bank', 'roi_min' => 8.90, 'roi_max' => 9.00, 'pf_charge' => 0.60, 'admin_charge' => 0, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 38140],
            ['id' => 26, 'quotation_id' => 15, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.05, 'roi_max' => 9.15, 'pf_charge' => 0.60, 'admin_charge' => 5000, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 46540],
            ['id' => 27, 'quotation_id' => 15, 'bank_name' => 'Axis Bank', 'roi_min' => 9.15, 'roi_max' => 9.25, 'pf_charge' => 0.65, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 40910],
            ['id' => 28, 'quotation_id' => 15, 'bank_name' => 'Kotak Mahindra Bank', 'roi_min' => 8.90, 'roi_max' => 9.00, 'pf_charge' => 0.50, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate_fees' => 2500, 'iom_charge' => 7000, 'tc_report' => 0, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 35600],
            ['id' => 29, 'quotation_id' => 16, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.00, 'roi_max' => 9.05, 'pf_charge' => 0.60, 'admin_charge' => 5900, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate_fees' => 2000, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 40022],
            ['id' => 30, 'quotation_id' => 17, 'bank_name' => 'ICICI Bank', 'roi_min' => 9.00, 'roi_max' => 9.05, 'pf_charge' => 0.60, 'admin_charge' => 5000, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate_fees' => 2000, 'iom_charge' => 7000, 'tc_report' => 2500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 38960],
            ['id' => 31, 'quotation_id' => 18, 'bank_name' => 'Axis Bank', 'roi_min' => 7.90, 'roi_max' => 8.10, 'pf_charge' => 0.50, 'admin_charge' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate_fees' => 1000, 'iom_charge' => 7000, 'tc_report' => 4500, 'extra1_name' => null, 'extra1_amount' => 0, 'extra2_name' => null, 'extra2_amount' => 0, 'total_charges' => 30340],
        ];

        foreach ($banks as $bank) {
            DB::table('quotation_banks')->updateOrInsert(
                ['id' => $bank['id']],
                array_merge($bank, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedQuotationEmi(): void
    {
        $emis = [
            ['quotation_bank_id' => 1, 'tenure_years' => 20, 'monthly_emi' => 33963, 'total_interest' => 3951225, 'total_payment' => 8151225],
            ['quotation_bank_id' => 2, 'tenure_years' => 15, 'monthly_emi' => 53622, 'total_interest' => 3831946, 'total_payment' => 9651946],
            ['quotation_bank_id' => 2, 'tenure_years' => 20, 'monthly_emi' => 46530, 'total_interest' => 5347271, 'total_payment' => 11167271],
            ['quotation_bank_id' => 3, 'tenure_years' => 15, 'monthly_emi' => 53622, 'total_interest' => 3831946, 'total_payment' => 9651946],
            ['quotation_bank_id' => 3, 'tenure_years' => 20, 'monthly_emi' => 46530, 'total_interest' => 5347271, 'total_payment' => 11167271],
            ['quotation_bank_id' => 4, 'tenure_years' => 15, 'monthly_emi' => 53622, 'total_interest' => 3831946, 'total_payment' => 9651946],
            ['quotation_bank_id' => 4, 'tenure_years' => 20, 'monthly_emi' => 46530, 'total_interest' => 5347271, 'total_payment' => 11167271],
            ['quotation_bank_id' => 5, 'tenure_years' => 15, 'monthly_emi' => 53457, 'total_interest' => 3802300, 'total_payment' => 9622300],
            ['quotation_bank_id' => 5, 'tenure_years' => 20, 'monthly_emi' => 46353, 'total_interest' => 5304760, 'total_payment' => 11124760],
            ['quotation_bank_id' => 6, 'tenure_years' => 15, 'monthly_emi' => 53622, 'total_interest' => 3831946, 'total_payment' => 9651946],
            ['quotation_bank_id' => 6, 'tenure_years' => 20, 'monthly_emi' => 46530, 'total_interest' => 5347271, 'total_payment' => 11167271],
            ['quotation_bank_id' => 7, 'tenure_years' => 15, 'monthly_emi' => 53457, 'total_interest' => 3802300, 'total_payment' => 9622300],
            ['quotation_bank_id' => 7, 'tenure_years' => 20, 'monthly_emi' => 46353, 'total_interest' => 5304760, 'total_payment' => 11124760],
            ['quotation_bank_id' => 8, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 9, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 10, 'tenure_years' => 15, 'monthly_emi' => 25431, 'total_interest' => 2077594, 'total_payment' => 4577594],
            ['quotation_bank_id' => 11, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 12, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 13, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 14, 'tenure_years' => 15, 'monthly_emi' => 25431, 'total_interest' => 2077594, 'total_payment' => 4577594],
            ['quotation_bank_id' => 15, 'tenure_years' => 15, 'monthly_emi' => 25357, 'total_interest' => 2064200, 'total_payment' => 4564200],
            ['quotation_bank_id' => 16, 'tenure_years' => 10, 'monthly_emi' => 63338, 'total_interest' => 2600546, 'total_payment' => 7600546],
            ['quotation_bank_id' => 16, 'tenure_years' => 15, 'monthly_emi' => 50713, 'total_interest' => 4128399, 'total_payment' => 9128399],
            ['quotation_bank_id' => 17, 'tenure_years' => 15, 'monthly_emi' => 26216, 'total_interest' => 2118968, 'total_payment' => 4718968],
            ['quotation_bank_id' => 17, 'tenure_years' => 20, 'monthly_emi' => 23226, 'total_interest' => 2974221, 'total_payment' => 5574221],
            ['quotation_bank_id' => 18, 'tenure_years' => 15, 'monthly_emi' => 70999, 'total_interest' => 5779759, 'total_payment' => 12779759],
            ['quotation_bank_id' => 19, 'tenure_years' => 20, 'monthly_emi' => 14960, 'total_interest' => 1690313, 'total_payment' => 3590313],
            ['quotation_bank_id' => 20, 'tenure_years' => 20, 'monthly_emi' => 16173, 'total_interest' => 1881536, 'total_payment' => 3881536],
            ['quotation_bank_id' => 21, 'tenure_years' => 10, 'monthly_emi' => 63473, 'total_interest' => 2616792, 'total_payment' => 7616792],
            ['quotation_bank_id' => 21, 'tenure_years' => 15, 'monthly_emi' => 50862, 'total_interest' => 4155188, 'total_payment' => 9155188],
            ['quotation_bank_id' => 22, 'tenure_years' => 10, 'monthly_emi' => 61993, 'total_interest' => 2439141, 'total_payment' => 7439141],
            ['quotation_bank_id' => 22, 'tenure_years' => 15, 'monthly_emi' => 49237, 'total_interest' => 3862656, 'total_payment' => 8862656],
            ['quotation_bank_id' => 23, 'tenure_years' => 20, 'monthly_emi' => 27557, 'total_interest' => 3113734, 'total_payment' => 6613734],
            ['quotation_bank_id' => 24, 'tenure_years' => 10, 'monthly_emi' => 51540, 'total_interest' => 2184833, 'total_payment' => 6184833],
            ['quotation_bank_id' => 24, 'tenure_years' => 15, 'monthly_emi' => 41528, 'total_interest' => 3475033, 'total_payment' => 7475033],
            ['quotation_bank_id' => 25, 'tenure_years' => 10, 'monthly_emi' => 37841, 'total_interest' => 1540868, 'total_payment' => 4540868],
            ['quotation_bank_id' => 25, 'tenure_years' => 15, 'monthly_emi' => 30250, 'total_interest' => 2444963, 'total_payment' => 5444963],
            ['quotation_bank_id' => 26, 'tenure_years' => 10, 'monthly_emi' => 38084, 'total_interest' => 1570075, 'total_payment' => 4570075],
            ['quotation_bank_id' => 26, 'tenure_years' => 15, 'monthly_emi' => 30517, 'total_interest' => 2493113, 'total_payment' => 5493113],
            ['quotation_bank_id' => 27, 'tenure_years' => 10, 'monthly_emi' => 38247, 'total_interest' => 1589604, 'total_payment' => 4589604],
            ['quotation_bank_id' => 27, 'tenure_years' => 15, 'monthly_emi' => 30696, 'total_interest' => 2525329, 'total_payment' => 5525329],
            ['quotation_bank_id' => 28, 'tenure_years' => 10, 'monthly_emi' => 37841, 'total_interest' => 1540868, 'total_payment' => 4540868],
            ['quotation_bank_id' => 28, 'tenure_years' => 15, 'monthly_emi' => 30250, 'total_interest' => 2444963, 'total_payment' => 5444963],
            ['quotation_bank_id' => 29, 'tenure_years' => 10, 'monthly_emi' => 25335, 'total_interest' => 1040219, 'total_payment' => 3040219],
            ['quotation_bank_id' => 29, 'tenure_years' => 15, 'monthly_emi' => 20285, 'total_interest' => 1651360, 'total_payment' => 3651360],
            ['quotation_bank_id' => 30, 'tenure_years' => 10, 'monthly_emi' => 25335, 'total_interest' => 1040219, 'total_payment' => 3040219],
            ['quotation_bank_id' => 30, 'tenure_years' => 15, 'monthly_emi' => 20285, 'total_interest' => 1651360, 'total_payment' => 3651360],
            ['quotation_bank_id' => 31, 'tenure_years' => 15, 'monthly_emi' => 15198, 'total_interest' => 1135678, 'total_payment' => 2735678],
            ['quotation_bank_id' => 31, 'tenure_years' => 20, 'monthly_emi' => 13284, 'total_interest' => 1588073, 'total_payment' => 3188073],
        ];

        foreach ($emis as $emi) {
            DB::table('quotation_emi')->updateOrInsert(
                ['quotation_bank_id' => $emi['quotation_bank_id'], 'tenure_years' => $emi['tenure_years']],
                array_merge($emi, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedQuotationDocuments(): void
    {
        $docs = [
            // Quotation 1
            [1, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [1, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [1, 'Business Address Proof', 'Business Address Proof'],
            [1, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [1, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [1, 'GST Registration Certificate', 'GST Registration Certificate'],
            [1, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [1, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [1, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [1, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 2
            [2, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [2, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [2, 'Business Address Proof', 'Business Address Proof'],
            [2, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [2, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [2, 'GST Registration Certificate', 'GST Registration Certificate'],
            [2, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [2, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [2, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [2, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 3
            [3, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [3, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [3, 'Business Address Proof', 'Business Address Proof'],
            [3, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [3, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [3, 'GST Registration Certificate', 'GST Registration Certificate'],
            [3, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [3, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [3, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [3, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 4
            [4, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [4, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [4, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [4, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [4, 'GST Registration Certificate', 'GST Registration Certificate'],
            [4, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [4, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [4, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 5
            [5, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [5, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [5, 'Business Address Proof', 'Business Address Proof'],
            [5, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [5, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [5, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [5, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [5, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [5, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 6
            [6, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [6, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [6, 'Business Address Proof', 'Business Address Proof'],
            [6, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [6, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [6, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [6, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [6, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [6, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 7
            [7, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [7, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [7, 'Business Address Proof', 'Business Address Proof'],
            [7, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [7, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [7, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [7, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 8
            [8, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [8, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [8, 'Business Address Proof', 'Business Address Proof'],
            [8, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [8, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [8, 'GST Registration Certificate', 'GST Registration Certificate'],
            [8, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [8, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [8, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [8, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 9
            [9, 'PAN Card of Proprietor', 'PAN Card of Proprietor'],
            [9, 'Aadhaar Card of Proprietor', 'Aadhaar Card of Proprietor'],
            [9, 'Business Address Proof', 'Business Address Proof'],
            [9, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [9, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [9, 'GST Registration Certificate', 'GST Registration Certificate'],
            [9, 'Shop & Establishment Certificate', 'Shop & Establishment Certificate'],
            [9, 'Property Documents (if applicable)', 'Property Documents (if applicable)'],
            [9, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [9, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 10
            [10, 'PAN Card Both', 'PAN Card Both'],
            [10, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [10, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [10, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [10, 'GST Registration Certificate', 'GST Registration Certificate'],
            [10, 'Property File Xerox', 'Property File Xerox'],
            [10, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [10, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            [10, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 11
            [11, 'PAN Card Both', 'PAN Card Both'],
            [11, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [11, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [11, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [11, 'Property File Xerox', 'Property File Xerox'],
            [11, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [11, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            [11, 'Passport Size Photographs', 'Passport Size Photographs'],
            // Quotation 12 - Partnership
            [12, 'Passport Size Photographs of All Partners', 'Passport Size Photographs of All Partners'],
            [12, 'PAN Card of Firm', 'PAN Card of Firm'],
            [12, 'PAN Card of All Partners', 'PAN Card of All Partners'],
            [12, 'Aadhaar Card of All Partners', 'Aadhaar Card of All Partners'],
            [12, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [12, 'ITR of Firm (Last 3 years)', 'ITR of Firm (Last 3 years)'],
            [12, 'ITR of Partners (Last 3 years)', 'ITR of Partners (Last 3 years)'],
            [12, 'GST Registration Certificate', 'GST Registration Certificate'],
            [12, 'Board Resolution / Authority Letter', 'Board Resolution / Authority Letter'],
            [12, 'Partnership Deed', 'Partnership Deed'],
            [12, 'Firm Current A/c Bank Statement  (12 months)', 'Firm Current A/c Bank Statement  (12 months)'],
            [12, 'Passport Size Photographs of All Partners', 'Passport Size Photographs of All Partners'],
            // Quotation 13
            [13, 'Passport Size Photographs', 'Passport Size Photographs'],
            [13, 'PAN Card Both', 'PAN Card Both'],
            [13, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [13, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [13, 'GST Registration Certificate', 'GST Registration Certificate'],
            [13, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [13, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            [13, 'Bank Statement (12 months)', 'Bank Statement (12 months)'],
            [13, 'Property File Xerox', 'Property File Xerox'],
            // Quotation 14
            [14, 'Passport Size Photographs Both', 'Passport Size Photographs Both'],
            [14, 'PAN Card Both', 'PAN Card Both'],
            [14, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [14, 'GST Registration Certificate', 'GST Registration Certificate'],
            [14, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [14, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)'],
            [14, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            // Quotation 15
            [15, 'Passport Size Photographs Both', 'Passport Size Photographs Both'],
            [15, 'PAN Card Both', 'PAN Card Both'],
            [15, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [15, 'GST Registration Certificate', 'GST Registration Certificate'],
            [15, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [15, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [15, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)'],
            [15, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            // Quotation 18
            [18, 'Passport Size Photographs Both', 'Passport Size Photographs Both'],
            [18, 'PAN Card Both', 'PAN Card Both'],
            [18, 'Aadhaar Card Both', 'Aadhaar Card Both'],
            [18, 'GST Registration Certificate', 'GST Registration Certificate'],
            [18, 'Udyam Registration Certificate', 'Udyam Registration Certificate'],
            [18, 'ITR (Last 3 years)', 'ITR (Last 3 years)'],
            [18, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)'],
            [18, 'Current Loan Statement ( if applicable )', 'Current Loan Statement ( if applicable )'],
            [18, 'Property File Xerox', 'Property File Xerox'],
        ];

        // Quotations 16 and 17 had no documents in the original DB

        foreach ($docs as [$quotationId, $nameEn, $nameGu]) {
            DB::table('quotation_documents')->insert([
                'quotation_id' => $quotationId,
                'document_name_en' => $nameEn,
                'document_name_gu' => $nameGu,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedSampleQuotationAndLoan(): void
    {
        $admin = \App\Models\User::find(1);
        if (! $admin) {
            return;
        }
        auth()->login($admin);

        $config = app(\App\Services\ConfigService::class)->load();
        $quotationService = app(\App\Services\QuotationService::class);
        $conversionService = app(\App\Services\LoanConversionService::class);

        // Advisors who create quotations — BDH first, then BM, then loan advisors
        // This ensures BDH and BM get early products so we can test all roles
        $bdhIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('roles.slug', 'bdh')->where('users.is_active', true)
            ->pluck('users.id')->toArray();
        $bmIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('roles.slug', 'branch_manager')->where('users.is_active', true)
            ->pluck('users.id')->toArray();
        $laIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('roles.slug', 'loan_advisor')->where('users.is_active', true)
            ->pluck('users.id')->toArray();

        // Order: BDH → BM → Loan Advisors (so first products get BDH/BM creators)
        $advisorIds = array_values(array_unique(array_merge($bdhIds, $bmIds, $laIds)));
        if (empty($advisorIds)) {
            $advisorIds = [$admin->id];
        }

        // Sample customers — each quotation gets a unique customer
        $customers = [
            ['name' => 'Vipul Parsana', 'type' => 'proprietor', 'phone' => '9510717999', 'pan' => 'AODPP1247F', 'dob' => '1990-01-15'],
            ['name' => 'Rajesh Patel', 'type' => 'proprietor', 'phone' => '9876543210', 'pan' => 'ABCDE1234F', 'dob' => '1985-06-20'],
            ['name' => 'Amit Shah', 'type' => 'partnership_llp', 'phone' => '9898989898', 'pan' => 'FGHIJ5678K', 'dob' => '1988-03-10'],
            ['name' => 'Priya Mehta', 'type' => 'salaried', 'phone' => '9123456789', 'pan' => 'KLMNO9012P', 'dob' => '1992-11-05'],
            ['name' => 'Suresh Kumar', 'type' => 'pvt_ltd', 'phone' => '9234567890', 'pan' => 'QRSTU3456V', 'dob' => '1982-09-15'],
            ['name' => 'Meera Joshi', 'type' => 'proprietor', 'phone' => '9345678901', 'pan' => 'VWXYZ7890A', 'dob' => '1995-04-22'],
            ['name' => 'Kiran Desai', 'type' => 'salaried', 'phone' => '9456789012', 'pan' => 'BCDEF1234G', 'dob' => '1991-07-30'],
            ['name' => 'Nilesh Bhatt', 'type' => 'partnership_llp', 'phone' => '9567890123', 'pan' => 'HIJKL5678M', 'dob' => '1987-12-18'],
            ['name' => 'Hetal Trivedi', 'type' => 'proprietor', 'phone' => '9678901234', 'pan' => 'NOPQR9012S', 'dob' => '1993-02-14'],
            ['name' => 'Darshan Raval', 'type' => 'pvt_ltd', 'phone' => '9789012345', 'pan' => 'TUVWX3456Y', 'dob' => '1986-08-08'],
            ['name' => 'Pooja Vaghela', 'type' => 'salaried', 'phone' => '9890123456', 'pan' => 'ZABCD7890E', 'dob' => '1994-05-25'],
        ];

        // Loan amounts vary by product type
        $productAmounts = [
            'Home Loan' => [5000000, 7500000, 6000000, 3500000],
            'LAP' => [10000000, 8000000, 12000000],
            'ASHA' => [2500000, 3000000],
            'OD' => [5000000],
            'PRATHAM' => [4000000],
        ];

        // ROI ranges by bank
        $bankRoi = [
            'HDFC Bank' => ['min' => 8.75, 'max' => 9.00],
            'ICICI Bank' => ['min' => 9.00, 'max' => 9.15],
            'Axis Bank' => ['min' => 9.25, 'max' => 9.50],
            'Kotak Mahindra Bank' => ['min' => 8.50, 'max' => 8.75],
        ];

        // Iterate all active products across all banks
        $products = DB::table('products')
            ->join('banks', 'banks.id', '=', 'products.bank_id')
            ->where('products.is_active', true)
            ->where('banks.is_active', true)
            ->select('products.id as product_id', 'products.name as product_name', 'banks.id as bank_id', 'banks.name as bank_name')
            ->orderBy('banks.name')
            ->orderBy('products.name')
            ->get();

        $custIdx = 0;

        foreach ($products as $product) {
            $cust = $customers[$custIdx % count($customers)];
            $amounts = $productAmounts[$product->product_name] ?? [5000000];
            $amount = $amounts[$custIdx % count($amounts)];
            $roi = $bankRoi[$product->bank_name] ?? ['min' => 9.00, 'max' => 9.25];

            // Cycle through advisors — each quotation created by a different user
            $creatorId = $advisorIds[$custIdx % count($advisorIds)];
            $creator = \App\Models\User::find($creatorId);
            if ($creator) {
                auth()->login($creator);
            }

            $custIdx++;

            $charges = \App\Models\BankCharge::where('bank_name', $product->bank_name)->first();
            $pf = $charges ? round($amount * $charges->pf / 100) : 0;
            $totalCharges = $pf + ($charges->admin ?? 0) + ($charges->stamp_notary ?? 0)
                + ($charges->registration_fee ?? 0) + ($charges->advocate ?? 0) + ($charges->tc ?? 0);

            $avgRoi = ($roi['min'] + $roi['max']) / 2;

            $typeDocs = collect($config['documents_en'][$cust['type']] ?? $config['documents_en']['proprietor'] ?? [])
                ->map(fn ($d, $i) => [
                    'en' => $d,
                    'gu' => ($config['documents_gu'][$cust['type']] ?? $config['documents_gu']['proprietor'] ?? [])[$i] ?? $d,
                ])->toArray();

            $result = $quotationService->generate([
                'customerName' => $cust['name'],
                'customerType' => $cust['type'],
                'loanAmount' => $amount,
                'location_id' => 2,
                'banks' => [
                    [
                        'name' => $product->bank_name,
                        'roiMin' => $roi['min'],
                        'roiMax' => $roi['max'],
                        'charges' => [
                            'pf' => $charges->pf ?? 0,
                            'admin' => $charges->admin ?? 0,
                            'stampNotary' => $charges->stamp_notary ?? 0,
                            'registrationFee' => $charges->registration_fee ?? 0,
                            'advocate' => $charges->advocate ?? 0,
                            'iom' => 0,
                            'tc' => $charges->tc ?? 0,
                            'extra1Name' => null, 'extra1Amount' => 0,
                            'extra2Name' => null, 'extra2Amount' => 0,
                            'total' => $totalCharges,
                        ],
                        'emiByTenure' => collect($config['tenures'] ?? [5, 10, 15, 20])->mapWithKeys(function ($t) use ($amount, $avgRoi) {
                            $r = $avgRoi / 12 / 100;
                            $n = $t * 12;
                            $emi = ($r > 0) ? (int) ceil($amount * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1)) : (int) ceil($amount / $n);

                            return [$t => ['emi' => $emi, 'totalInterest' => ($emi * $n) - $amount, 'totalPayment' => $emi * $n]];
                        })->toArray(),
                    ],
                ],
                'documents' => $typeDocs,
                'selectedTenures' => $config['tenures'] ?? [5, 10, 15, 20],
                'preparedByName' => $creator->name ?? $admin->name,
                'preparedByMobile' => $creator->phone ?? $admin->phone ?? '',
            ], $creatorId);

            if (! empty($result['error']) || empty($result['quotation'])) {
                $this->command?->warn("  ⚠ Quotation failed for {$product->bank_name}/{$product->product_name}: ".($result['error'] ?? 'unknown'));

                continue;
            }

            $quotation = $result['quotation'];
            $amountLabel = '₹'.number_format($amount / 100000).'L';
            $creatorName = $creator->name ?? 'Admin';
            $this->command?->line("  + Quotation #{$quotation->id}: {$cust['name']} / {$product->bank_name} {$product->product_name} / {$amountLabel} (by {$creatorName})");

            // Convert to loan — advisor is the same user who created the quotation
            $loan = $conversionService->convertFromQuotation($quotation, 0, [
                'branch_id' => 1,
                'product_id' => $product->product_id,
                'customer_phone' => $cust['phone'],
                'customer_email' => null,
                'date_of_birth' => $cust['dob'],
                'pan_number' => $cust['pan'],
                'assigned_advisor' => $creatorId,
                'notes' => "Sample loan for {$product->bank_name} {$product->product_name}",
            ]);

            $this->command?->line("  + Loan #{$loan->id} ({$loan->loan_number}): {$product->bank_name} {$product->product_name}");
        }

        // Advance some loans through stages to demonstrate auto-assignment
        $this->advanceSampleLoans();

        auth()->logout();
    }

    /**
     * Advance some sample loans through workflow stages to verify auto-assignment.
     * Uses the workflow config snapshot for role resolution.
     */
    private function advanceSampleLoans(): void
    {
        $stageService = app(\App\Services\LoanStageService::class);
        $docService = app(\App\Services\LoanDocumentService::class);

        // Get loans by bank for varied advancement
        $loans = \App\Models\LoanDetail::with(['stageAssignments', 'bank', 'product'])
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        if ($loans->isEmpty()) {
            return;
        }

        $this->command?->line('');
        $this->command?->line('  Advancing sample loans through stages...');

        foreach ($loans as $idx => $loan) {
            $bankName = $loan->bank?->name ?? 'Unknown';
            $productName = $loan->product?->name ?? '';

            // Login as the loan's advisor for proper auth context
            $advisor = \App\Models\User::find($loan->assigned_advisor ?? $loan->created_by);
            if ($advisor) {
                auth()->login($advisor);
            }

            // All loans: complete document_collection → advance to parallel_processing
            // Mark all docs as received first
            $loan->documents()->update(['status' => 'received', 'received_date' => now(), 'received_by' => auth()->id()]);
            $stageService->updateStageStatus($loan, 'document_collection', 'completed', auth()->id());
            $loan->refresh();

            // After doc_collection completes, parallel_processing should auto-start
            // and app_number should be auto-assigned to task_owner
            $appAssignment = $loan->stageAssignments()->where('stage_key', 'app_number')->first();
            $appAssignee = $appAssignment?->assigned_to ? \App\Models\User::find($appAssignment->assigned_to)?->name : 'none';

            // Every 2nd loan: advance further through app_number
            if ($idx % 2 === 0) {
                // Complete app_number with application number data
                $appAssignment?->mergeNotesData([
                    'application_number' => 'APP-'.strtoupper(substr($bankName, 0, 3)).'-'.str_pad($loan->id, 4, '0', STR_PAD_LEFT),
                    'docket_days_offset' => 21,
                ]);
                $loan->update(['application_number' => 'APP-'.strtoupper(substr($bankName, 0, 3)).'-'.str_pad($loan->id, 4, '0', STR_PAD_LEFT)]);
                $stageService->updateStageStatus($loan, 'app_number', 'completed', auth()->id());
                $loan->refresh();

                // After app_number completes, bsm_osv should auto-start and auto-assign
                $bsmAssignment = $loan->stageAssignments()->where('stage_key', 'bsm_osv')->first();
                $bsmAssignee = $bsmAssignment?->assigned_to ? \App\Models\User::find($bsmAssignment->assigned_to)?->name : 'none';

                $this->command?->line("  → {$loan->loan_number} ({$bankName} {$productName}): doc_collection ✓ → app_number ✓ → bsm_osv assigned to {$bsmAssignee}");
            } else {
                $this->command?->line("  → {$loan->loan_number} ({$bankName} {$productName}): doc_collection ✓ → app_number assigned to {$appAssignee}");
            }
        }
    }
}
