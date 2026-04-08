<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the database with all production data as of 2026-04-08.
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
        // Order matters: parent tables first, then children with FKs

        $this->seedPermissions();
        $this->seedLocations();
        $this->seedBranches();
        $this->seedBanks();
        $this->seedStages();
        $this->seedUsers();
        $this->seedRolePermissions();
        $this->seedTaskRolePermissions();
        $this->seedUserBranches();
        $this->seedBankEmployees();
        $this->seedBankLocations();
        $this->seedLocationUsers();
        $this->seedBankCharges();
        $this->seedProducts();
        $this->seedAppConfig();
        $this->seedAppSettings();
        $this->seedQuotations();
        $this->seedQuotationBanks();
        $this->seedQuotationEmi();
        $this->seedQuotationDocuments();

        // Clear any loan references from quotations
        DB::table('quotations')->whereNotNull('loan_id')->update(['loan_id' => null]);
    }

    private function seedPermissions(): void
    {
        $permissions = [
            ['id' => 1, 'name' => 'View Settings', 'slug' => 'view_settings', 'group' => 'Settings', 'description' => 'View the settings page'],
            ['id' => 2, 'name' => 'Edit Company Info', 'slug' => 'edit_company_info', 'group' => 'Settings', 'description' => 'Edit company information'],
            ['id' => 3, 'name' => 'Edit Banks', 'slug' => 'edit_banks', 'group' => 'Settings', 'description' => 'Add/edit/remove banks'],
            ['id' => 4, 'name' => 'Edit Documents', 'slug' => 'edit_documents', 'group' => 'Settings', 'description' => 'Add/edit/remove required documents'],
            ['id' => 5, 'name' => 'Edit Tenures', 'slug' => 'edit_tenures', 'group' => 'Settings', 'description' => 'Add/edit/remove loan tenures'],
            ['id' => 6, 'name' => 'Edit Charges', 'slug' => 'edit_charges', 'group' => 'Settings', 'description' => 'Edit bank charges'],
            ['id' => 7, 'name' => 'Edit Services', 'slug' => 'edit_services', 'group' => 'Settings', 'description' => 'Edit service charges'],
            ['id' => 8, 'name' => 'Edit GST', 'slug' => 'edit_gst', 'group' => 'Settings', 'description' => 'Edit GST percentage'],
            ['id' => 9, 'name' => 'Create Quotation', 'slug' => 'create_quotation', 'group' => 'Quotations', 'description' => 'Create new loan quotations'],
            ['id' => 10, 'name' => 'Generate PDF', 'slug' => 'generate_pdf', 'group' => 'Quotations', 'description' => 'Generate PDF for quotations'],
            ['id' => 11, 'name' => 'View Own Quotations', 'slug' => 'view_own_quotations', 'group' => 'Quotations', 'description' => 'View quotations created by self'],
            ['id' => 12, 'name' => 'View All Quotations', 'slug' => 'view_all_quotations', 'group' => 'Quotations', 'description' => 'View all quotations across users'],
            ['id' => 13, 'name' => 'Delete Quotations', 'slug' => 'delete_quotations', 'group' => 'Quotations', 'description' => 'Delete quotations'],
            ['id' => 14, 'name' => 'Download PDF', 'slug' => 'download_pdf', 'group' => 'Quotations', 'description' => 'Download generated PDFs'],
            ['id' => 15, 'name' => 'View Users', 'slug' => 'view_users', 'group' => 'Users', 'description' => 'View the users list'],
            ['id' => 16, 'name' => 'Create Users', 'slug' => 'create_users', 'group' => 'Users', 'description' => 'Create new user accounts'],
            ['id' => 17, 'name' => 'Edit Users', 'slug' => 'edit_users', 'group' => 'Users', 'description' => 'Edit existing user accounts'],
            ['id' => 18, 'name' => 'Delete Users', 'slug' => 'delete_users', 'group' => 'Users', 'description' => 'Delete user accounts'],
            ['id' => 19, 'name' => 'Assign Roles', 'slug' => 'assign_roles', 'group' => 'Users', 'description' => 'Assign roles to users'],
            ['id' => 20, 'name' => 'Change Own Password', 'slug' => 'change_own_password', 'group' => 'System', 'description' => 'Change own password'],
            ['id' => 21, 'name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'group' => 'System', 'description' => 'Manage role and user permissions'],
            ['id' => 22, 'name' => 'View Activity Log', 'slug' => 'view_activity_log', 'group' => 'System', 'description' => 'View system activity log'],
            ['id' => 23, 'name' => 'Convert to Loan', 'slug' => 'convert_to_loan', 'group' => 'Loans', 'description' => 'Convert quotation to loan task'],
            ['id' => 24, 'name' => 'View Loans', 'slug' => 'view_loans', 'group' => 'Loans', 'description' => 'View loan task list'],
            ['id' => 25, 'name' => 'View All Loans', 'slug' => 'view_all_loans', 'group' => 'Loans', 'description' => 'View all loans across users/branches'],
            ['id' => 26, 'name' => 'Create Loan', 'slug' => 'create_loan', 'group' => 'Loans', 'description' => 'Create loan tasks directly'],
            ['id' => 27, 'name' => 'Edit Loan', 'slug' => 'edit_loan', 'group' => 'Loans', 'description' => 'Edit loan details'],
            ['id' => 28, 'name' => 'Delete Loan', 'slug' => 'delete_loan', 'group' => 'Loans', 'description' => 'Delete loan tasks'],
            ['id' => 29, 'name' => 'Manage Loan Documents', 'slug' => 'manage_loan_documents', 'group' => 'Loans', 'description' => 'Mark documents as received/pending, add/remove documents'],
            ['id' => 30, 'name' => 'Manage Loan Stages', 'slug' => 'manage_loan_stages', 'group' => 'Loans', 'description' => 'Update stage status and assignments'],
            ['id' => 31, 'name' => 'Skip Loan Stages', 'slug' => 'skip_loan_stages', 'group' => 'Loans', 'description' => 'Skip stages in loan workflow'],
            ['id' => 32, 'name' => 'Add Remarks', 'slug' => 'add_remarks', 'group' => 'Loans', 'description' => 'Add remarks to loan stages'],
            ['id' => 33, 'name' => 'Manage Workflow Config', 'slug' => 'manage_workflow_config', 'group' => 'Loans', 'description' => 'Configure banks, products, branches, stage workflows'],
            ['id' => 34, 'name' => 'Upload Loan Documents', 'slug' => 'upload_loan_documents', 'group' => 'Loans', 'description' => 'Upload document files to loan documents'],
            ['id' => 35, 'name' => 'Download Loan Documents', 'slug' => 'download_loan_documents', 'group' => 'Loans', 'description' => 'Download/preview uploaded document files'],
            ['id' => 36, 'name' => 'Delete Loan Files', 'slug' => 'delete_loan_files', 'group' => 'Loans', 'description' => 'Remove uploaded document files'],
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
            ['id' => 3, 'parent_id' => 1, 'name' => 'Jamnagar', 'type' => 'city', 'code' => 'JAM', 'is_active' => true],
            ['id' => 4, 'parent_id' => 1, 'name' => 'Ahmedabad', 'type' => 'city', 'code' => 'AMD', 'is_active' => true],
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
            ['id' => 1, 'name' => 'HDFC Bank', 'code' => 'HDFC', 'is_active' => true, 'default_employee_id' => null],
            ['id' => 2, 'name' => 'ICICI Bank', 'code' => 'ICICI', 'is_active' => true, 'default_employee_id' => null],
            ['id' => 3, 'name' => 'Axis Bank', 'code' => 'AXIS', 'is_active' => true, 'default_employee_id' => null],
            ['id' => 4, 'name' => 'Kotak Mahindra Bank', 'code' => 'KOTAK', 'is_active' => true, 'default_employee_id' => null],
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
            ['id' => 1, 'stage_key' => 'inquiry', 'is_enabled' => true, 'stage_name_en' => 'Loan Inquiry', 'stage_name_gu' => 'Loan Inquiry', 'sequence_order' => 1, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Initial customer and loan details entry', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 2, 'stage_key' => 'document_selection', 'is_enabled' => true, 'stage_name_en' => 'Document Selection', 'stage_name_gu' => 'Document Selection', 'sequence_order' => 2, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Select required documents for the loan', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 3, 'stage_key' => 'document_collection', 'is_enabled' => true, 'stage_name_en' => 'Document Collection', 'stage_name_gu' => 'Document Collection', 'sequence_order' => 3, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Collect and verify all required documents', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 4, 'stage_key' => 'parallel_processing', 'is_enabled' => true, 'stage_name_en' => 'Parallel Processing', 'stage_name_gu' => 'Parallel Processing', 'sequence_order' => 4, 'is_parallel' => true, 'parent_stage_key' => null, 'stage_type' => 'parallel', 'description_en' => 'Four parallel tracks processed simultaneously', 'description_gu' => null, 'default_role' => null, 'sub_actions' => null],
            ['id' => 5, 'stage_key' => 'app_number', 'is_enabled' => true, 'stage_name_en' => 'Application Number', 'stage_name_gu' => 'Application Number', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Enter bank application number', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 6, 'stage_key' => 'bsm_osv', 'is_enabled' => true, 'stage_name_en' => 'BSM/OSV Approval', 'stage_name_gu' => 'BSM/OSV Approval', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Bank site and office verification', 'description_gu' => null, 'default_role' => '["bank_employee"]', 'sub_actions' => null],
            ['id' => 7, 'stage_key' => 'legal_verification', 'is_enabled' => true, 'stage_name_en' => 'Legal Verification', 'stage_name_gu' => 'Legal Verification', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Legal document verification', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 8, 'stage_key' => 'technical_valuation', 'is_enabled' => true, 'stage_name_en' => 'Technical Valuation', 'stage_name_gu' => 'Technical Valuation', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Property/asset technical valuation', 'description_gu' => null, 'default_role' => '["branch_manager","office_employee"]', 'sub_actions' => null],
            ['id' => 9, 'stage_key' => 'rate_pf', 'is_enabled' => true, 'stage_name_en' => 'Rate & PF Request', 'stage_name_gu' => 'Rate & PF Request', 'sequence_order' => 5, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Request interest rate and processing fee from bank', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => '[{"key":"bank_rate_details","name":"Bank Rate Details","sequence":1,"roles":["bank_employee"],"type":"form","is_enabled":true},{"key":"processing_charges","name":"Processing & Charges","sequence":2,"roles":["branch_manager","loan_advisor","office_employee"],"type":"form","is_enabled":true}]'],
            ['id' => 10, 'stage_key' => 'sanction', 'is_enabled' => true, 'stage_name_en' => 'Sanction Letter', 'stage_name_gu' => 'Sanction Letter', 'sequence_order' => 6, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Bank issues sanction letter', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => '[{"key":"send_for_sanction","name":"Send for Sanction Letter","sequence":1,"roles":["branch_manager","loan_advisor"],"type":"action_button","action":"send_for_sanction","transfer_to_role":"bank_employee","is_enabled":true},{"key":"sanction_generated","name":"Sanction Letter Generated","sequence":2,"roles":["bank_employee"],"type":"action_button","action":"sanction_generated","transfer_to_role":"loan_advisor","is_enabled":true},{"key":"sanction_details","name":"Sanction Details","sequence":3,"roles":["branch_manager","loan_advisor"],"type":"form","is_enabled":true}]'],
            ['id' => 11, 'stage_key' => 'docket', 'is_enabled' => true, 'stage_name_en' => 'Docket Login', 'stage_name_gu' => 'Docket Login', 'sequence_order' => 7, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Physical document processing and docket creation', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'sub_actions' => null],
            ['id' => 12, 'stage_key' => 'kfs', 'is_enabled' => true, 'stage_name_en' => 'KFS Generation', 'stage_name_gu' => 'KFS Generation', 'sequence_order' => 8, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Key Fact Statement generation', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'sub_actions' => null],
            ['id' => 13, 'stage_key' => 'esign', 'is_enabled' => true, 'stage_name_en' => 'E-Sign & eNACH', 'stage_name_gu' => 'E-Sign & eNACH', 'sequence_order' => 9, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Digital signature and eNACH mandate', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","bank_employee"]', 'sub_actions' => null],
            ['id' => 14, 'stage_key' => 'disbursement', 'is_enabled' => true, 'stage_name_en' => 'Disbursement', 'stage_name_gu' => 'Disbursement', 'sequence_order' => 10, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'decision', 'description_en' => 'Fund disbursement - transfer or cheque with OTC handling', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor"]', 'sub_actions' => null],
            ['id' => 16, 'stage_key' => 'property_valuation', 'is_enabled' => true, 'stage_name_en' => 'Property Valuation', 'stage_name_gu' => 'Property Valuation', 'sequence_order' => 4, 'is_parallel' => false, 'parent_stage_key' => 'parallel_processing', 'stage_type' => 'sequential', 'description_en' => 'Dedicated property valuation for LAP', 'description_gu' => null, 'default_role' => '["branch_manager","office_employee"]', 'sub_actions' => null],
            ['id' => 26, 'stage_key' => 'otc_clearance', 'is_enabled' => true, 'stage_name_en' => 'OTC Clearance', 'stage_name_gu' => 'OTC Clearance', 'sequence_order' => 11, 'is_parallel' => false, 'parent_stage_key' => null, 'stage_type' => 'sequential', 'description_en' => 'Cheque handover and OTC clearance', 'description_gu' => null, 'default_role' => '["branch_manager","loan_advisor","office_employee"]', 'sub_actions' => null],
        ];

        foreach ($stages as $stage) {
            unset($stage['id']);
            DB::table('stages')->updateOrInsert(
                ['stage_key' => $stage['stage_key']],
                array_merge($stage, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedUsers(): void
    {
        $defaultPassword = Hash::make('password');
        $adminPassword = Hash::make('Admin@123');

        $users = [
            ['id' => 1, 'name' => 'Super Admin', 'email' => 'admin@shf.com', 'role' => 'super_admin', 'is_active' => true, 'created_by' => null, 'phone' => '+91 99747 89089', 'task_role' => null, 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 2, 'name' => 'Denish Malviya', 'email' => 'denish@shfworld.com', 'role' => 'admin', 'is_active' => true, 'created_by' => null, 'phone' => '+91 99747 89089', 'task_role' => 'branch_manager', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 3, 'name' => 'JAYDEEP THESHIYA', 'email' => 'jaydeep@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '9725248300', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 4, 'name' => 'KULDEEP VAISHNAV', 'email' => 'kuldeep@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '8866236688', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 5, 'name' => 'HARDIK NASIT', 'email' => 'hardik@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '9726179351', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 6, 'name' => 'RAHUL MARAKANA', 'email' => 'rahul@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '9913744162', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 7, 'name' => 'DIPAK VIRANI', 'email' => 'dipak@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '7600143537', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 8, 'name' => 'JAYESH MORI', 'email' => 'jayesh@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '8000232586', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 9, 'name' => 'CHIRAG DHOLAKIYA', 'email' => 'chirag@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '9016348138', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 10, 'name' => 'DAXIT MALAVIYA', 'email' => 'daxit@shfworld.som', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '81600000286', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 11, 'name' => 'MILAN DHOLAKIYA', 'email' => 'milan@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '8401277654', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 12, 'name' => 'NITIN FALDU', 'email' => 'nitin@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '968701525', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 13, 'name' => 'KRUPALI SHILU', 'email' => 'krupali@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 2, 'phone' => '9099089072', 'task_role' => 'loan_advisor', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => null],
            ['id' => 14, 'name' => 'HDFC Employee 1', 'email' => 'hdfc@manager.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => null],
            ['id' => 15, 'name' => 'HDFC Employee 2', 'email' => 'hdfc@manager2.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => null],
            ['id' => 16, 'name' => 'Kotak Employee 1', 'email' => 'kotak@manager.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => 'hdfc@manager2.cop', 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 4],
            ['id' => 17, 'name' => 'Kotak Employee 2', 'email' => 'kotak@manager2.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 4],
            ['id' => 18, 'name' => 'Axix Employee 1', 'email' => 'axis@manager.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 3],
            ['id' => 19, 'name' => 'Axix Employee 2', 'email' => 'axis@manager2.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 3],
            ['id' => 20, 'name' => 'ICICI Employee 1', 'email' => 'icici@manager.cop', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 2],
            ['id' => 21, 'name' => 'ICICI Employee 2', 'email' => 'icici@manager2.com', 'role' => 'bank_employee', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'bank_employee', 'employee_id' => null, 'default_branch_id' => null, 'task_bank_id' => 2],
            ['id' => 22, 'name' => 'Office Employee1', 'email' => 'vipul@office.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 1, 'phone' => '+91 99747 89089', 'task_role' => 'office_employee', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 1],
            ['id' => 23, 'name' => 'Office Employee2', 'email' => 'officeemployee2@shfworld.com', 'role' => 'staff', 'is_active' => true, 'created_by' => 1, 'phone' => null, 'task_role' => 'office_employee', 'employee_id' => null, 'default_branch_id' => 1, 'task_bank_id' => 1],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                array_merge($user, [
                    'password' => (isset($user['id']) && $user['id'] == 1) ? $adminPassword : $defaultPassword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Update bank default_employee_id after users exist
        DB::table('banks')->where('id', 1)->update(['default_employee_id' => 15]);
        DB::table('banks')->where('id', 2)->update(['default_employee_id' => 21]);
        DB::table('banks')->where('id', 3)->update(['default_employee_id' => 18]);
        DB::table('banks')->where('id', 4)->update(['default_employee_id' => 17]);

        // Update branch manager after users exist
        DB::table('branches')->where('id', 1)->update(['manager_id' => 2, 'updated_by' => 1]);
    }

    private function seedRolePermissions(): void
    {
        // Clear existing to avoid duplicates
        DB::table('role_permissions')->truncate();

        // super_admin gets ALL permissions
        $allPermissionIds = DB::table('permissions')->pluck('id');
        foreach ($allPermissionIds as $permId) {
            DB::table('role_permissions')->insert([
                'role' => 'super_admin',
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // admin permissions (all except manage_permissions #21 and delete_users #18)
        $adminPermIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 19, 20, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36];
        foreach ($adminPermIds as $permId) {
            DB::table('role_permissions')->insert([
                'role' => 'admin',
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // staff permissions (limited set)
        $staffPermIds = [9, 10, 11, 14, 20, 23, 24, 26, 27, 29, 30, 31, 32, 34, 35, 36];
        foreach ($staffPermIds as $permId) {
            DB::table('role_permissions')->insert([
                'role' => 'staff',
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // bank_employee permissions (minimal: view_loans, add_remarks, change_own_password)
        $bankEmployeePermIds = [20, 24, 32];
        foreach ($bankEmployeePermIds as $permId) {
            DB::table('role_permissions')->insert([
                'role' => 'bank_employee',
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedTaskRolePermissions(): void
    {
        DB::table('task_role_permissions')->truncate();

        $taskRolePerms = [
            // branch_manager
            ['task_role' => 'branch_manager', 'permission_id' => 23],
            ['task_role' => 'branch_manager', 'permission_id' => 24],
            ['task_role' => 'branch_manager', 'permission_id' => 25],
            ['task_role' => 'branch_manager', 'permission_id' => 26],
            ['task_role' => 'branch_manager', 'permission_id' => 27],
            ['task_role' => 'branch_manager', 'permission_id' => 29],
            ['task_role' => 'branch_manager', 'permission_id' => 31],
            ['task_role' => 'branch_manager', 'permission_id' => 32],
            // loan_advisor
            ['task_role' => 'loan_advisor', 'permission_id' => 23],
            ['task_role' => 'loan_advisor', 'permission_id' => 24],
            ['task_role' => 'loan_advisor', 'permission_id' => 25],
            ['task_role' => 'loan_advisor', 'permission_id' => 26],
            ['task_role' => 'loan_advisor', 'permission_id' => 27],
            ['task_role' => 'loan_advisor', 'permission_id' => 29],
            ['task_role' => 'loan_advisor', 'permission_id' => 31],
            ['task_role' => 'loan_advisor', 'permission_id' => 32],
            // bank_employee
            ['task_role' => 'bank_employee', 'permission_id' => 24],
            ['task_role' => 'bank_employee', 'permission_id' => 32],
            // office_employee
            ['task_role' => 'office_employee', 'permission_id' => 23],
            ['task_role' => 'office_employee', 'permission_id' => 24],
            ['task_role' => 'office_employee', 'permission_id' => 26],
            ['task_role' => 'office_employee', 'permission_id' => 27],
            ['task_role' => 'office_employee', 'permission_id' => 29],
            ['task_role' => 'office_employee', 'permission_id' => 31],
            ['task_role' => 'office_employee', 'permission_id' => 32],
        ];

        foreach ($taskRolePerms as $trp) {
            DB::table('task_role_permissions')->insert(
                array_merge($trp, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedUserBranches(): void
    {
        $userIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 22, 23];
        foreach ($userIds as $userId) {
            DB::table('user_branches')->updateOrInsert(
                ['user_id' => $userId, 'branch_id' => 1],
                ['created_at' => null, 'updated_at' => null]
            );
        }
    }

    private function seedBankEmployees(): void
    {
        $employees = [
            ['bank_id' => 1, 'user_id' => 14, 'is_default' => false],
            ['bank_id' => 1, 'user_id' => 15, 'is_default' => true],
            ['bank_id' => 2, 'user_id' => 20, 'is_default' => false],
            ['bank_id' => 2, 'user_id' => 21, 'is_default' => true],
            ['bank_id' => 3, 'user_id' => 18, 'is_default' => true],
            ['bank_id' => 3, 'user_id' => 19, 'is_default' => false],
            ['bank_id' => 4, 'user_id' => 16, 'is_default' => false],
            ['bank_id' => 4, 'user_id' => 17, 'is_default' => true],
            ['bank_id' => 3, 'user_id' => 22, 'is_default' => false],
            ['bank_id' => 1, 'user_id' => 22, 'is_default' => false],
            ['bank_id' => 3, 'user_id' => 23, 'is_default' => false],
            ['bank_id' => 1, 'user_id' => 23, 'is_default' => false],
        ];

        foreach ($employees as $emp) {
            DB::table('bank_employees')->updateOrInsert(
                ['bank_id' => $emp['bank_id'], 'user_id' => $emp['user_id']],
                array_merge($emp, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBankLocations(): void
    {
        $bankLocations = [
            ['bank_id' => 1, 'location_id' => 2],
            ['bank_id' => 2, 'location_id' => 2],
            ['bank_id' => 2, 'location_id' => 3],
            ['bank_id' => 3, 'location_id' => 2],
            ['bank_id' => 3, 'location_id' => 3],
            ['bank_id' => 4, 'location_id' => 2],
            ['bank_id' => 4, 'location_id' => 3],
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
        $locationUsers = [
            ['location_id' => 1, 'user_id' => 17],
            ['location_id' => 2, 'user_id' => 17],
            ['location_id' => 2, 'user_id' => 22],
            ['location_id' => 2, 'user_id' => 23],
        ];

        foreach ($locationUsers as $lu) {
            DB::table('location_user')->updateOrInsert(
                ['location_id' => $lu['location_id'], 'user_id' => $lu['user_id']],
                array_merge($lu, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedBankCharges(): void
    {
        $charges = [
            ['bank_name' => 'Axis Bank', 'pf' => 0.50, 'admin' => 0, 'stamp_notary' => 2500, 'registration_fee' => 5900, 'advocate' => 1000, 'tc' => 4500, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
            ['bank_name' => 'HDFC Bank', 'pf' => 0.60, 'admin' => 0, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate' => 2500, 'tc' => 0, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
            ['bank_name' => 'ICICI Bank', 'pf' => 0.60, 'admin' => 5000, 'stamp_notary' => 1500, 'registration_fee' => 5900, 'advocate' => 2000, 'tc' => 2500, 'extra1_name' => null, 'extra1_amt' => 0, 'extra2_name' => null, 'extra2_amt' => 0],
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
            ['id' => 14, 'loan_id' => null, 'location_id' => 2, 'user_id' => 9, 'customer_name' => 'SAJANBEN MUKESHBHAI AAL', 'customer_type' => 'proprietor', 'loan_amount' => 4000000, 'pdf_filename' => null, 'pdf_path' => null, 'additional_notes' => "Loan amount may vary based on bank's visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nProperty file full copy required (with succession, copy will not be returned)\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 5000/- non-refundable", 'prepared_by_name' => 'CHIRAG DHOLAKIYA', 'prepared_by_mobile' => '9016348138', 'selected_tenures' => '[10,15]', 'created_at' => '2026-03-28 07:44:32', 'updated_at' => '2026-03-28 07:44:32'],
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
}
