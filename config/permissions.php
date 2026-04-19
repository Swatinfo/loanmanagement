<?php

return [
    'groups' => [
        'Settings' => [
            ['slug' => 'view_settings', 'name' => 'View Settings', 'description' => 'View the settings page'],
            ['slug' => 'edit_company_info', 'name' => 'Edit Company Info', 'description' => 'Edit company information'],
            ['slug' => 'edit_banks', 'name' => 'Edit Banks', 'description' => 'Add/edit/remove banks'],
            ['slug' => 'edit_documents', 'name' => 'Edit Documents', 'description' => 'Add/edit/remove required documents'],
            ['slug' => 'edit_tenures', 'name' => 'Edit Tenures', 'description' => 'Add/edit/remove loan tenures'],
            ['slug' => 'edit_charges', 'name' => 'Edit Charges', 'description' => 'Edit bank charges'],
            ['slug' => 'edit_services', 'name' => 'Edit Services', 'description' => 'Edit service charges'],
            ['slug' => 'edit_gst', 'name' => 'Edit GST', 'description' => 'Edit GST percentage'],
        ],
        'Quotations' => [
            ['slug' => 'create_quotation', 'name' => 'Create Quotation', 'description' => 'Create new loan quotations'],
            ['slug' => 'generate_pdf', 'name' => 'Generate PDF', 'description' => 'Generate PDF for quotations'],
            ['slug' => 'view_own_quotations', 'name' => 'View Own Quotations', 'description' => 'View quotations created by self'],
            ['slug' => 'view_all_quotations', 'name' => 'View All Quotations', 'description' => 'View all quotations across users'],
            ['slug' => 'delete_quotations', 'name' => 'Delete Quotations', 'description' => 'Delete quotations'],
            ['slug' => 'download_pdf', 'name' => 'Download PDF', 'description' => 'Download generated PDFs'],
            ['slug' => 'download_pdf_branded', 'name' => 'Download Branded PDF', 'description' => 'Download PDF with SHF branding'],
            ['slug' => 'download_pdf_plain', 'name' => 'Download Plain PDF', 'description' => 'Download PDF without SHF branding'],
            ['slug' => 'hold_quotation', 'name' => 'Hold Quotation', 'description' => 'Put a quotation on hold with a reason and follow-up date'],
            ['slug' => 'cancel_quotation', 'name' => 'Cancel Quotation', 'description' => 'Cancel a quotation with a reason (terminal state)'],
            ['slug' => 'resume_quotation', 'name' => 'Resume Quotation', 'description' => 'Resume an on-hold quotation back to active'],
        ],
        'Users' => [
            ['slug' => 'view_users', 'name' => 'View Users', 'description' => 'View the users list'],
            ['slug' => 'create_users', 'name' => 'Create Users', 'description' => 'Create new user accounts'],
            ['slug' => 'edit_users', 'name' => 'Edit Users', 'description' => 'Edit existing user accounts'],
            ['slug' => 'delete_users', 'name' => 'Delete Users', 'description' => 'Delete user accounts'],
            ['slug' => 'assign_roles', 'name' => 'Assign Roles', 'description' => 'Assign roles to users'],
        ],
        'Loans' => [
            ['slug' => 'convert_to_loan', 'name' => 'Convert to Loan', 'description' => 'Convert quotation to loan task'],
            ['slug' => 'view_loans', 'name' => 'View Loans', 'description' => 'View loan task list'],
            ['slug' => 'view_all_loans', 'name' => 'View All Loans', 'description' => 'View all loans across users/branches'],
            ['slug' => 'create_loan', 'name' => 'Create Loan', 'description' => 'Create loan tasks directly'],
            ['slug' => 'edit_loan', 'name' => 'Edit Loan', 'description' => 'Edit loan details'],
            ['slug' => 'delete_loan', 'name' => 'Delete Loan', 'description' => 'Delete loan tasks'],
            ['slug' => 'manage_loan_documents', 'name' => 'Manage Loan Documents', 'description' => 'Mark documents as received/pending, add/remove documents'],
            ['slug' => 'upload_loan_documents', 'name' => 'Upload Loan Documents', 'description' => 'Upload document files to loan documents'],
            ['slug' => 'download_loan_documents', 'name' => 'Download Loan Documents', 'description' => 'Download/preview uploaded document files'],
            ['slug' => 'delete_loan_files', 'name' => 'Delete Loan Files', 'description' => 'Remove uploaded document files'],
            ['slug' => 'manage_loan_stages', 'name' => 'Manage Loan Stages', 'description' => 'Update stage status and assignments'],
            ['slug' => 'skip_loan_stages', 'name' => 'Skip Loan Stages', 'description' => 'Skip stages in loan workflow'],
            ['slug' => 'add_remarks', 'name' => 'Add Remarks', 'description' => 'Add remarks to loan stages'],
            ['slug' => 'manage_workflow_config', 'name' => 'Manage Workflow Config', 'description' => 'Configure banks, products, branches, stage workflows'],
        ],
        'Tasks' => [
            ['slug' => 'view_all_tasks', 'name' => 'View All Tasks', 'description' => 'View all general tasks across users (read-only)'],
        ],
        'DVR' => [
            ['slug' => 'view_dvr', 'name' => 'View DVR', 'description' => 'View daily visit reports'],
            ['slug' => 'create_dvr', 'name' => 'Create DVR', 'description' => 'Create daily visit reports'],
            ['slug' => 'edit_dvr', 'name' => 'Edit DVR', 'description' => 'Edit daily visit reports'],
            ['slug' => 'delete_dvr', 'name' => 'Delete DVR', 'description' => 'Delete daily visit reports'],
            ['slug' => 'view_all_dvr', 'name' => 'View All DVR', 'description' => 'View all daily visit reports across users'],
        ],
        'System' => [
            ['slug' => 'change_own_password', 'name' => 'Change Own Password', 'description' => 'Change own password'],
            ['slug' => 'manage_permissions', 'name' => 'Manage Permissions', 'description' => 'Manage role and user permissions'],
            ['slug' => 'view_activity_log', 'name' => 'View Activity Log', 'description' => 'View system activity log'],
            ['slug' => 'view_reports', 'name' => 'View Reports', 'description' => 'View turnaround time and performance reports'],
        ],
    ],

    // Role-permission mappings are managed in the role_permission pivot table
    // (seeded via 2026_04_09_211216_create_unified_roles_system migration)
    // and editable at runtime via Permissions and Loan Settings pages.
];
