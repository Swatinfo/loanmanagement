<?php

use App\Http\Controllers\Api\NotesApiController;
use App\Http\Controllers\Api\SyncApiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyVisitReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralTaskController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanConversionController;
use App\Http\Controllers\LoanDisbursementController;
use App\Http\Controllers\LoanDocumentController;
use App\Http\Controllers\LoanRemarkController;
use App\Http\Controllers\LoanSettingsController;
use App\Http\Controllers\LoanStageController;
use App\Http\Controllers\LoanValuationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowConfigController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/quotation-data', [DashboardController::class, 'quotationData'])->name('dashboard.quotation-data');
    Route::get('/dashboard/task-data', [DashboardController::class, 'taskData'])->name('dashboard.task-data');
    Route::get('/dashboard/loan-data', [DashboardController::class, 'dashboardLoanData'])->name('dashboard.loan-data');
    Route::get('/dashboard/dvr-data', [DashboardController::class, 'dvrData'])->name('dashboard.dvr-data');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::middleware('permission:view_users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'userData'])->name('users.data');
    });

    Route::middleware('permission:create_users')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:view_users')->group(function () {
        Route::get('/users/check-email', [UserController::class, 'checkEmail'])->name('users.check-email');
    });

    Route::middleware('permission:view_users')->group(function () {
        Route::get('/users/product-stage-holders', [UserController::class, 'productStageHolders'])->name('users.product-stage-holders');
    });

    Route::middleware('permission:edit_users')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    Route::middleware('permission:delete_users')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Permissions Management
    Route::middleware('permission:manage_permissions')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::put('/permissions', [PermissionController::class, 'update'])->name('permissions.update');
    });

    // Settings
    Route::middleware('permission:view_settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
    Route::middleware('permission:edit_company_info')->post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
    Route::middleware('permission:edit_banks')->post('/settings/banks', [SettingsController::class, 'updateBanks'])->name('settings.banks');
    Route::middleware('permission:edit_tenures')->post('/settings/tenures', [SettingsController::class, 'updateTenures'])->name('settings.tenures');
    Route::middleware('permission:edit_documents')->post('/settings/documents', [SettingsController::class, 'updateDocuments'])->name('settings.documents');
    Route::middleware('permission:edit_charges')->group(function () {
        Route::post('/settings/charges', [SettingsController::class, 'updateCharges'])->name('settings.charges');
        Route::post('/settings/bank-charges', [SettingsController::class, 'updateBankCharges'])->name('settings.bank-charges');
    });
    Route::middleware('permission:edit_services')->post('/settings/services', [SettingsController::class, 'updateServices'])->name('settings.services');
    Route::middleware('permission:edit_gst')->post('/settings/gst', [SettingsController::class, 'updateGst'])->name('settings.gst');
    Route::middleware('permission:view_settings')->group(function () {
        Route::post('/settings/dvr-contact-types', [SettingsController::class, 'updateDvrContactTypes'])->name('settings.dvr-contact-types');
        Route::post('/settings/dvr-purposes', [SettingsController::class, 'updateDvrPurposes'])->name('settings.dvr-purposes');
        Route::post('/settings/quotation-hold-reasons', [SettingsController::class, 'updateQuotationHoldReasons'])->name('settings.quotation-hold-reasons');
        Route::post('/settings/quotation-cancel-reasons', [SettingsController::class, 'updateQuotationCancelReasons'])->name('settings.quotation-cancel-reasons');
        Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
    });

    // Loan Management
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/data', [LoanController::class, 'loanData'])->name('loans.data');
    });
    Route::middleware('permission:create_loan')->group(function () {
        Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    });
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
        Route::get('/loans/{loan}/timeline', [LoanController::class, 'timeline'])->name('loans.timeline');
    });
    Route::middleware('permission:edit_loan')->group(function () {
        Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
        Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
        Route::post('/loans/{loan}/status', [LoanController::class, 'updateStatus'])->name('loans.update-status');
    });
    Route::middleware('permission:delete_loan')->group(function () {
        Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    });

    // Loan Settings
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loan-settings', [LoanSettingsController::class, 'index'])->name('loan-settings.index');
    });
    Route::middleware('permission:manage_workflow_config')->group(function () {
        Route::post('/loan-settings/banks', [WorkflowConfigController::class, 'storeBank'])->name('loan-settings.banks.store');
        Route::delete('/loan-settings/banks/{bank}', [WorkflowConfigController::class, 'destroyBank'])->name('loan-settings.banks.destroy');
        Route::post('/loan-settings/products', [WorkflowConfigController::class, 'storeProduct'])->name('loan-settings.products.store');
        Route::get('/loan-settings/products/{product}/stages', [WorkflowConfigController::class, 'productStages'])->name('loan-settings.product-stages');
        Route::post('/loan-settings/products/{product}/stages', [WorkflowConfigController::class, 'saveProductStages'])->name('loan-settings.product-stages.save');
        Route::post('/loan-settings/products/{product}/locations', [WorkflowConfigController::class, 'saveProductLocations'])->name('loan-settings.product-locations.save');
        Route::post('/loan-settings/branches', [WorkflowConfigController::class, 'storeBranch'])->name('loan-settings.branches.store');
        Route::delete('/loan-settings/branches/{branch}', [WorkflowConfigController::class, 'destroyBranch'])->name('loan-settings.branches.destroy');
        Route::delete('/loan-settings/products/{product}', [WorkflowConfigController::class, 'destroyProduct'])->name('loan-settings.products.destroy');
        Route::post('/loan-settings/master-stages', [LoanSettingsController::class, 'saveMasterStages'])->name('loan-settings.master-stages.save');
        Route::post('/loan-settings/locations', [LoanSettingsController::class, 'storeLocation'])->name('loan-settings.locations.store');
        Route::delete('/loan-settings/locations/{location}', [LoanSettingsController::class, 'destroyLocation'])->name('loan-settings.locations.destroy');
        Route::post('/loan-settings/task-role-permissions', [LoanSettingsController::class, 'saveTaskRolePermissions'])->name('loan-settings.task-role-permissions.save');
    });

    // Stage workflow
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loans/{loan}/stages', [LoanStageController::class, 'index'])->name('loans.stages');
        Route::get('/loans/{loan}/transfers', [LoanStageController::class, 'transferHistory'])->name('loans.transfers');
    });
    Route::middleware('permission:manage_loan_stages')->group(function () {
        Route::post('/loans/{loan}/stages/{stageKey}/status', [LoanStageController::class, 'updateStatus'])->name('loans.stages.status');
        Route::post('/loans/{loan}/stages/{stageKey}/assign', [LoanStageController::class, 'assign'])->name('loans.stages.assign');
        Route::post('/loans/{loan}/stages/{stageKey}/transfer', [LoanStageController::class, 'transfer'])->middleware('permission:transfer_loan_stages')->name('loans.stages.transfer');
        Route::post('/loans/{loan}/stages/{stageKey}/reject', [LoanStageController::class, 'reject'])->name('loans.stages.reject');
        Route::post('/loans/{loan}/stages/{stageKey}/query', [LoanStageController::class, 'raiseQuery'])->name('loans.stages.query');
        Route::post('/loans/{loan}/stages/{stageKey}/notes', [LoanStageController::class, 'saveNotes'])->name('loans.stages.notes');
        Route::post('/loans/{loan}/stages/technical_valuation/action', [LoanStageController::class, 'technicalValuationAction'])->name('loans.stages.technical-valuation-action');
        Route::post('/loans/{loan}/stages/esign/action', [LoanStageController::class, 'esignAction'])->name('loans.stages.esign-action');
        Route::post('/loans/{loan}/stages/docket/action', [LoanStageController::class, 'docketAction'])->name('loans.stages.docket-action');
        Route::post('/loans/{loan}/stages/rate_pf/action', [LoanStageController::class, 'ratePfAction'])->name('loans.stages.rate-pf-action');
        Route::post('/loans/{loan}/stages/sanction/action', [LoanStageController::class, 'sanctionAction'])->name('loans.stages.sanction-action');
        Route::post('/loans/{loan}/stages/legal_verification/action', [LoanStageController::class, 'legalAction'])->name('loans.stages.legal-action');
        Route::post('/loans/{loan}/stages/sanction_decision/action', [LoanStageController::class, 'sanctionDecisionAction'])->name('loans.stages.sanction-decision-action');
        Route::get('/loans/{loan}/stages/{stageKey}/eligible-users', [LoanStageController::class, 'eligibleUsers'])->name('loans.stages.eligible-users');
        Route::post('/loans/queries/{query}/respond', [LoanStageController::class, 'respondToQuery'])->name('loans.queries.respond');
        Route::post('/loans/queries/{query}/resolve', [LoanStageController::class, 'resolveQuery'])->name('loans.queries.resolve');
    });
    Route::middleware('permission:skip_loan_stages')->group(function () {
        Route::post('/loans/{loan}/stages/{stageKey}/skip', [LoanStageController::class, 'skip'])->name('loans.stages.skip');
    });

    // Disbursement
    Route::middleware('permission:manage_loan_stages')->group(function () {
        Route::get('/loans/{loan}/disbursement', [LoanDisbursementController::class, 'show'])->name('loans.disbursement');
        Route::post('/loans/{loan}/disbursement', [LoanDisbursementController::class, 'store'])->name('loans.disbursement.store');
    });

    // Valuation
    Route::middleware('permission:manage_loan_stages')->group(function () {
        Route::get('/loans/{loan}/valuation', [LoanValuationController::class, 'show'])->name('loans.valuation');
        Route::get('/loans/{loan}/valuation-map', [LoanValuationController::class, 'showMap'])->name('loans.valuation.map');
        Route::get('/api/reverse-geocode', [LoanValuationController::class, 'reverseGeocode'])->name('api.reverse-geocode');
        Route::get('/api/search-location', [LoanValuationController::class, 'searchLocation'])->name('api.search-location');
        Route::post('/loans/{loan}/valuation', [LoanValuationController::class, 'store'])->name('loans.valuation.store');
    });

    // Remarks
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loans/{loan}/remarks', [LoanRemarkController::class, 'index'])->name('loans.remarks.index');
    });
    Route::middleware('permission:add_remarks')->group(function () {
        Route::post('/loans/{loan}/remarks', [LoanRemarkController::class, 'store'])->name('loans.remarks.store');
    });

    // Role Management (super_admin only via manage_permissions)
    Route::middleware('permission:manage_permissions')->group(function () {
        Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update');
        Route::get('/roles/check-name', [RoleManagementController::class, 'checkName'])->name('roles.check-name');
    });

    // Reports (all logged-in users, data scoped by role in controller)
    Route::get('/reports/turnaround', [ReportController::class, 'turnaround'])->name('reports.turnaround');
    Route::get('/reports/turnaround/data', [ReportController::class, 'turnaroundData'])->name('reports.turnaround.data');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/count', [NotificationController::class, 'unreadCount'])->name('api.notifications.count');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Web Push subscriptions
    Route::get('/api/push/public-key', [\App\Http\Controllers\PushSubscriptionController::class, 'publicKey'])->name('push.public-key');
    Route::post('/api/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/api/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Document collection
    Route::middleware('permission:view_loans')->group(function () {
        Route::get('/loans/{loan}/documents', [LoanDocumentController::class, 'index'])->name('loans.documents');
    });
    Route::middleware('permission:manage_loan_documents')->group(function () {
        Route::post('/loans/{loan}/documents/{document}/status', [LoanDocumentController::class, 'updateStatus'])->name('loans.documents.status');
        Route::post('/loans/{loan}/documents', [LoanDocumentController::class, 'store'])->name('loans.documents.store');
        Route::delete('/loans/{loan}/documents/{document}', [LoanDocumentController::class, 'destroy'])->name('loans.documents.destroy');
    });
    Route::middleware('permission:upload_loan_documents')->group(function () {
        Route::post('/loans/{loan}/documents/{document}/upload', [LoanDocumentController::class, 'upload'])->name('loans.documents.upload');
    });
    Route::middleware('permission:download_loan_documents')->group(function () {
        Route::get('/loans/{loan}/documents/{document}/download', [LoanDocumentController::class, 'download'])->name('loans.documents.download');
    });
    Route::middleware('permission:delete_loan_files')->group(function () {
        Route::delete('/loans/{loan}/documents/{document}/file', [LoanDocumentController::class, 'deleteFile'])->name('loans.documents.deleteFile');
    });

    // Quotation to Loan conversion
    Route::middleware('permission:convert_to_loan')->group(function () {
        Route::get('/quotations/{quotation}/convert', [LoanConversionController::class, 'showConvertForm'])
            ->name('quotations.convert');
        Route::post('/quotations/{quotation}/convert', [LoanConversionController::class, 'convert'])
            ->name('quotations.convert.store');
    });

    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::middleware('permission:create_quotation')->group(function () {
        Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    });
    Route::middleware('permission:generate_pdf')->group(function () {
        Route::post('/quotations/generate', [QuotationController::class, 'generate'])->name('quotations.generate');
    });
    Route::middleware('permission:download_pdf')->group(function () {
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->name('quotations.download');
        Route::get('/download-pdf', [QuotationController::class, 'downloadByFilename'])->name('quotations.download-file');
    });
    Route::get('/quotations/{quotation}/preview-html', [QuotationController::class, 'previewHtml'])->name('quotations.preview-html');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::middleware('permission:delete_quotations')->group(function () {
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });
    Route::middleware('permission:hold_quotation')->group(function () {
        Route::post('/quotations/{quotation}/hold', [QuotationController::class, 'hold'])->name('quotations.hold');
    });
    Route::middleware('permission:cancel_quotation')->group(function () {
        Route::post('/quotations/{quotation}/cancel', [QuotationController::class, 'cancel'])->name('quotations.cancel');
    });
    Route::middleware('permission:resume_quotation')->group(function () {
        Route::post('/quotations/{quotation}/resume', [QuotationController::class, 'resume'])->name('quotations.resume');
    });

    // General Tasks (personal + delegated)
    Route::prefix('general-tasks')->name('general-tasks.')->group(function () {
        Route::get('/search-loans', [GeneralTaskController::class, 'searchLoans'])->name('search-loans');
        Route::get('/data', [GeneralTaskController::class, 'taskData'])->name('data');
        Route::get('/', [GeneralTaskController::class, 'index'])->name('index');
        Route::post('/', [GeneralTaskController::class, 'store'])->name('store');
        Route::get('/{task}', [GeneralTaskController::class, 'show'])->name('show');
        Route::put('/{task}', [GeneralTaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [GeneralTaskController::class, 'destroy'])->name('destroy');
        Route::patch('/{task}/status', [GeneralTaskController::class, 'updateStatus'])->name('update-status');
        Route::post('/{task}/comments', [GeneralTaskController::class, 'storeComment'])->name('comments.store');
        Route::delete('/{task}/comments/{comment}', [GeneralTaskController::class, 'destroyComment'])->name('comments.destroy');
    });

    // Daily Visit Reports (DVR)
    Route::prefix('dvr')->name('dvr.')->group(function () {
        Route::middleware('permission:view_dvr')->group(function () {
            Route::get('/', [DailyVisitReportController::class, 'index'])->name('index');
            Route::get('/data', [DailyVisitReportController::class, 'dvrData'])->name('data');
            Route::get('/search-loans', [DailyVisitReportController::class, 'searchLoans'])->name('search-loans');
            Route::get('/search-quotations', [DailyVisitReportController::class, 'searchQuotations'])->name('search-quotations');
            Route::get('/search-contacts', [DailyVisitReportController::class, 'searchContacts'])->name('search-contacts');
            Route::get('/{dvr}', [DailyVisitReportController::class, 'show'])->name('show');
        });
        Route::middleware('permission:create_dvr')->group(function () {
            Route::post('/', [DailyVisitReportController::class, 'store'])->name('store');
        });
        Route::middleware('permission:edit_dvr')->group(function () {
            Route::put('/{dvr}', [DailyVisitReportController::class, 'update'])->name('update');
            Route::patch('/{dvr}/follow-up-done', [DailyVisitReportController::class, 'markFollowUpDone'])->name('follow-up-done');
        });
        Route::middleware('permission:delete_dvr')->group(function () {
            Route::delete('/{dvr}', [DailyVisitReportController::class, 'destroy'])->name('destroy');
        });
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::middleware('permission:view_customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/data', [CustomerController::class, 'data'])->name('data');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        });
        Route::middleware('permission:manage_customers')->group(function () {
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        });
    });

    // Activity Log
    Route::middleware('permission:view_activity_log')->group(function () {
        Route::get('/activity-log', [DashboardController::class, 'activityLog'])->name('activity-log');
        Route::get('/activity-log/data', [DashboardController::class, 'activityLogData'])->name('activity-log.data');
    });

    // Impersonation — search endpoint (authorization in controller via canImpersonate)
    Route::get('/api/impersonate/users', [ImpersonateController::class, 'users'])->name('impersonate.users');

    // Custom impersonate take/leave with smart redirect
    Route::get('/impersonate/take/{id}', [ImpersonateController::class, 'take'])->name('impersonate.take');
    Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');

    // API endpoints that require auth (session-based, used by PWA)
    Route::post('/api/sync', [SyncApiController::class, 'sync'])->name('api.sync');
    Route::post('/api/notes', [NotesApiController::class, 'save'])->name('api.notes.save');
});

// Note: Package Route::impersonate() removed — custom take/leave routes in auth group handle impersonation with smart redirect

require __DIR__.'/auth.php';
