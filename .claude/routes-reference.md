# Routes Reference

Complete route table for SHF. Session auth (web guard), Eloquent provider. All web routes pass through `web` group + `EnsureUserIsActive` middleware appended globally.

## Middleware aliases (bootstrap/app.php)

| Alias | Class | Behavior |
|---|---|---|
| `permission` | `App\Http\Middleware\CheckPermission` | `handle($req, $next, $slug)` — aborts 403 if `$user->hasPermission($slug)` is false |
| `active` | `App\Http\Middleware\EnsureUserIsActive` | Logs out + redirects to login if `is_active=false` |

`EnsureUserIsActive` is appended to the `web` middleware group, so every authenticated web request checks it.

---

## Public / Redirect

| Method | URI | Name | Controller | Middleware |
|---|---|---|---|---|
| GET | `/` | — | redirect to `dashboard` | — |

## Auth (Breeze, `routes/auth.php`)

| Method | URI | Name | Controller | Middleware |
|---|---|---|---|---|
| GET | `/login` | `login` | Auth\AuthenticatedSessionController@create | guest |
| POST | `/login` | — | Auth\AuthenticatedSessionController@store | guest |
| GET | `/forgot-password` | `password.request` | Auth\PasswordResetLinkController@create | guest |
| POST | `/forgot-password` | `password.email` | Auth\PasswordResetLinkController@store | guest |
| GET | `/reset-password/{token}` | `password.reset` | Auth\NewPasswordController@create | guest |
| POST | `/reset-password` | `password.store` | Auth\NewPasswordController@store | guest |
| GET | `/verify-email` | `verification.notice` | Auth\EmailVerificationPromptController | auth |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | Auth\VerifyEmailController | auth, signed, throttle:6,1 |
| POST | `/email/verification-notification` | `verification.send` | Auth\EmailVerificationNotificationController@store | auth, throttle:6,1 |
| GET | `/confirm-password` | `password.confirm` | Auth\ConfirmablePasswordController@show | auth |
| POST | `/confirm-password` | — | Auth\ConfirmablePasswordController@store | auth |
| PUT | `/password` | `password.update` | Auth\PasswordController@update | auth |
| POST | `/logout` | `logout` | Auth\AuthenticatedSessionController@destroy | auth |

**Registration routes are intentionally disabled.**

## Dashboard & Activity Log

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/dashboard` | `dashboard` | DashboardController@index | auth |
| GET | `/dashboard/quotation-data` | `dashboard.quotation-data` | DashboardController@quotationData | auth |
| GET | `/dashboard/task-data` | `dashboard.task-data` | DashboardController@taskData | auth |
| GET | `/dashboard/loan-data` | `dashboard.loan-data` | DashboardController@dashboardLoanData | auth |
| GET | `/dashboard/dvr-data` | `dashboard.dvr-data` | DashboardController@dvrData | auth |
| GET | `/activity-log` | `activity-log` | DashboardController@activityLog | view_activity_log |
| GET | `/activity-log/data` | `activity-log.data` | DashboardController@activityLogData | view_activity_log |

## Profile

| Method | URI | Name | Controller | Middleware |
|---|---|---|---|---|
| GET | `/profile` | `profile.edit` | ProfileController@edit | auth |
| PATCH | `/profile` | `profile.update` | ProfileController@update | auth |
| DELETE | `/profile` | `profile.destroy` | ProfileController@destroy | auth |

## Users

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/users` | `users.index` | UserController@index | view_users |
| GET | `/users/data` | `users.data` | UserController@userData | view_users |
| GET | `/users/create` | `users.create` | UserController@create | create_users |
| POST | `/users` | `users.store` | UserController@store | create_users |
| GET | `/users/check-email` | `users.check-email` | UserController@checkEmail | view_users |
| GET | `/users/product-stage-holders` | `users.product-stage-holders` | UserController@productStageHolders | view_users |
| GET | `/users/{user}/edit` | `users.edit` | UserController@edit | edit_users |
| PUT | `/users/{user}` | `users.update` | UserController@update | edit_users |
| POST | `/users/{user}/toggle-active` | `users.toggle-active` | UserController@toggleActive | edit_users |
| DELETE | `/users/{user}` | `users.destroy` | UserController@destroy | delete_users |

## Roles

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/roles` | `roles.index` | RoleManagementController@index | manage_permissions |
| GET | `/roles/create` | `roles.create` | RoleManagementController@create | manage_permissions |
| POST | `/roles` | `roles.store` | RoleManagementController@store | manage_permissions |
| GET | `/roles/{role}/edit` | `roles.edit` | RoleManagementController@edit | manage_permissions |
| PUT | `/roles/{role}` | `roles.update` | RoleManagementController@update | manage_permissions |
| GET | `/roles/check-name` | `roles.check-name` | RoleManagementController@checkName | manage_permissions |

## Permissions

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/permissions` | `permissions.index` | PermissionController@index | manage_permissions |
| PUT | `/permissions` | `permissions.update` | PermissionController@update | manage_permissions |

## Settings (quotation settings)

All require `auth`. Specific permissions per action.

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/settings` | `settings.index` | SettingsController@index | view_settings |
| POST | `/settings/company` | `settings.company` | SettingsController@updateCompany | edit_company_info |
| POST | `/settings/banks` | `settings.banks` | SettingsController@updateBanks | edit_banks |
| POST | `/settings/tenures` | `settings.tenures` | SettingsController@updateTenures | edit_tenures |
| POST | `/settings/documents` | `settings.documents` | SettingsController@updateDocuments | edit_documents |
| POST | `/settings/charges` | `settings.charges` | SettingsController@updateCharges | edit_charges |
| POST | `/settings/bank-charges` | `settings.bank-charges` | SettingsController@updateBankCharges | edit_charges |
| POST | `/settings/services` | `settings.services` | SettingsController@updateServices | edit_services |
| POST | `/settings/gst` | `settings.gst` | SettingsController@updateGst | edit_gst |
| POST | `/settings/dvr-contact-types` | `settings.dvr-contact-types` | SettingsController@updateDvrContactTypes | view_settings |
| POST | `/settings/dvr-purposes` | `settings.dvr-purposes` | SettingsController@updateDvrPurposes | view_settings |
| POST | `/settings/reset` | `settings.reset` | SettingsController@reset | view_settings |

## Loan Settings (workflow config)

Prefix `/loan-settings`. Require `auth` + (for writes) `manage_workflow_config`.

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/loan-settings` | `loan-settings.index` | LoanSettingsController@index (perm: view_loans) |
| POST | `/loan-settings/banks` | `loan-settings.banks.store` | WorkflowConfigController@storeBank |
| DELETE | `/loan-settings/banks/{bank}` | `loan-settings.banks.destroy` | WorkflowConfigController@destroyBank |
| POST | `/loan-settings/products` | `loan-settings.products.store` | WorkflowConfigController@storeProduct |
| GET | `/loan-settings/products/{product}/stages` | `loan-settings.product-stages` | WorkflowConfigController@productStages |
| POST | `/loan-settings/products/{product}/stages` | `loan-settings.product-stages.save` | WorkflowConfigController@saveProductStages |
| POST | `/loan-settings/products/{product}/locations` | `loan-settings.product-locations.save` | WorkflowConfigController@saveProductLocations |
| POST | `/loan-settings/branches` | `loan-settings.branches.store` | WorkflowConfigController@storeBranch |
| DELETE | `/loan-settings/branches/{branch}` | `loan-settings.branches.destroy` | WorkflowConfigController@destroyBranch |
| DELETE | `/loan-settings/products/{product}` | `loan-settings.products.destroy` | WorkflowConfigController@destroyProduct |
| POST | `/loan-settings/master-stages` | `loan-settings.master-stages.save` | LoanSettingsController@saveMasterStages |
| POST | `/loan-settings/locations` | `loan-settings.locations.store` | LoanSettingsController@storeLocation |
| DELETE | `/loan-settings/locations/{location}` | `loan-settings.locations.destroy` | LoanSettingsController@destroyLocation |
| POST | `/loan-settings/task-role-permissions` | `loan-settings.task-role-permissions.save` | LoanSettingsController@saveTaskRolePermissions |

## Loans (CRUD)

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/loans` | `loans.index` | LoanController@index | view_loans |
| GET | `/loans/data` | `loans.data` | LoanController@loanData | view_loans |
| GET | `/loans/create` | `loans.create` | LoanController@create | create_loan |
| POST | `/loans` | `loans.store` | LoanController@store | create_loan |
| GET | `/loans/{loan}` | `loans.show` | LoanController@show | view_loans |
| GET | `/loans/{loan}/timeline` | `loans.timeline` | LoanController@timeline | view_loans |
| GET | `/loans/{loan}/edit` | `loans.edit` | LoanController@edit | edit_loan |
| PUT | `/loans/{loan}` | `loans.update` | LoanController@update | edit_loan |
| POST | `/loans/{loan}/status` | `loans.update-status` | LoanController@updateStatus | edit_loan |
| DELETE | `/loans/{loan}` | `loans.destroy` | LoanController@destroy | delete_loan |

## Loan Stages

All require `auth` + `manage_loan_stages` (some additional perms noted).

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/loans/{loan}/stages` | `loans.stages` | LoanStageController@index (perm: view_loans) |
| GET | `/loans/{loan}/transfers` | `loans.transfers` | LoanStageController@transferHistory (perm: view_loans) |
| POST | `/loans/{loan}/stages/{stageKey}/status` | `loans.stages.status` | LoanStageController@updateStatus |
| POST | `/loans/{loan}/stages/{stageKey}/assign` | `loans.stages.assign` | LoanStageController@assign |
| POST | `/loans/{loan}/stages/{stageKey}/transfer` | `loans.stages.transfer` | LoanStageController@transfer (+`transfer_loan_stages`) |
| POST | `/loans/{loan}/stages/{stageKey}/reject` | `loans.stages.reject` | LoanStageController@reject |
| POST | `/loans/{loan}/stages/{stageKey}/query` | `loans.stages.query` | LoanStageController@raiseQuery |
| POST | `/loans/{loan}/stages/{stageKey}/notes` | `loans.stages.notes` | LoanStageController@saveNotes |
| POST | `/loans/{loan}/stages/{stageKey}/skip` | `loans.stages.skip` | LoanStageController@skip (perm: skip_loan_stages) |
| GET | `/loans/{loan}/stages/{stageKey}/eligible-users` | `loans.stages.eligible-users` | LoanStageController@eligibleUsers |
| POST | `/loans/{loan}/stages/technical_valuation/action` | `loans.stages.technical-valuation-action` | LoanStageController@technicalValuationAction |
| POST | `/loans/{loan}/stages/esign/action` | `loans.stages.esign-action` | LoanStageController@esignAction |
| POST | `/loans/{loan}/stages/docket/action` | `loans.stages.docket-action` | LoanStageController@docketAction |
| POST | `/loans/{loan}/stages/rate_pf/action` | `loans.stages.rate-pf-action` | LoanStageController@ratePfAction |
| POST | `/loans/{loan}/stages/sanction/action` | `loans.stages.sanction-action` | LoanStageController@sanctionAction |
| POST | `/loans/{loan}/stages/legal_verification/action` | `loans.stages.legal-action` | LoanStageController@legalAction |
| POST | `/loans/{loan}/stages/sanction_decision/action` | `loans.stages.sanction-decision-action` | LoanStageController@sanctionDecisionAction |
| POST | `/loans/queries/{query}/respond` | `loans.queries.respond` | LoanStageController@respondToQuery |
| POST | `/loans/queries/{query}/resolve` | `loans.queries.resolve` | LoanStageController@resolveQuery |

## Loan Documents

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/loans/{loan}/documents` | `loans.documents` | LoanDocumentController@index | view_loans |
| POST | `/loans/{loan}/documents` | `loans.documents.store` | LoanDocumentController@store | manage_loan_documents |
| POST | `/loans/{loan}/documents/{document}/status` | `loans.documents.status` | LoanDocumentController@updateStatus | manage_loan_documents |
| POST | `/loans/{loan}/documents/{document}/upload` | `loans.documents.upload` | LoanDocumentController@upload | upload_loan_documents |
| DELETE | `/loans/{loan}/documents/{document}` | `loans.documents.destroy` | LoanDocumentController@destroy | manage_loan_documents |
| GET | `/loans/{loan}/documents/{document}/download` | `loans.documents.download` | LoanDocumentController@download | download_loan_documents |
| DELETE | `/loans/{loan}/documents/{document}/file` | `loans.documents.deleteFile` | LoanDocumentController@deleteFile | delete_loan_files |

## Loan Valuation

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/loans/{loan}/valuation` | `loans.valuation` | LoanValuationController@show | manage_loan_stages |
| GET | `/loans/{loan}/valuation-map` | `loans.valuation.map` | LoanValuationController@showMap | manage_loan_stages |
| POST | `/loans/{loan}/valuation` | `loans.valuation.store` | LoanValuationController@store | manage_loan_stages |
| GET | `/api/reverse-geocode` | `api.reverse-geocode` | LoanValuationController@reverseGeocode | auth |
| GET | `/api/search-location` | `api.search-location` | LoanValuationController@searchLocation | auth |

## Loan Disbursement

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/loans/{loan}/disbursement` | `loans.disbursement` | LoanDisbursementController@show | manage_loan_stages |
| POST | `/loans/{loan}/disbursement` | `loans.disbursement.store` | LoanDisbursementController@store | manage_loan_stages |

## Loan Remarks

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/loans/{loan}/remarks` | `loans.remarks.index` | LoanRemarkController@index | view_loans |
| POST | `/loans/{loan}/remarks` | `loans.remarks.store` | LoanRemarkController@store | add_remarks |

## Quotations

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/quotations/create` | `quotations.create` | QuotationController@create | create_quotation |
| POST | `/quotations/generate` | `quotations.generate` | QuotationController@generate | generate_pdf |
| GET | `/quotations/{quotation}` | `quotations.show` | QuotationController@show | auth |
| GET | `/quotations/{quotation}/preview-html` | `quotations.preview-html` | QuotationController@previewHtml | auth |
| GET | `/quotations/{quotation}/download` | `quotations.download` | QuotationController@download | download_pdf |
| GET | `/download-pdf` | `quotations.download-file` | QuotationController@downloadByFilename | download_pdf |
| DELETE | `/quotations/{quotation}` | `quotations.destroy` | QuotationController@destroy | delete_quotations |
| GET | `/quotations/{quotation}/convert` | `quotations.convert` | LoanConversionController@showConvertForm | convert_to_loan |
| POST | `/quotations/{quotation}/convert` | `quotations.convert.store` | LoanConversionController@convert | convert_to_loan |

## Daily Visit Reports (DVR)

Prefix `dvr.`.

| Method | URI | Name | Controller | Permission |
|---|---|---|---|---|
| GET | `/dvr` | `dvr.index` | DailyVisitReportController@index | view_dvr |
| GET | `/dvr/data` | `dvr.data` | DailyVisitReportController@dvrData | view_dvr |
| GET | `/dvr/search-loans` | `dvr.search-loans` | DailyVisitReportController@searchLoans | view_dvr |
| GET | `/dvr/search-quotations` | `dvr.search-quotations` | DailyVisitReportController@searchQuotations | view_dvr |
| GET | `/dvr/search-contacts` | `dvr.search-contacts` | DailyVisitReportController@searchContacts | view_dvr |
| POST | `/dvr` | `dvr.store` | DailyVisitReportController@store | create_dvr |
| GET | `/dvr/{dvr}` | `dvr.show` | DailyVisitReportController@show | view_dvr |
| PUT | `/dvr/{dvr}` | `dvr.update` | DailyVisitReportController@update | edit_dvr |
| PATCH | `/dvr/{dvr}/follow-up-done` | `dvr.follow-up-done` | DailyVisitReportController@markFollowUpDone | edit_dvr |
| DELETE | `/dvr/{dvr}` | `dvr.destroy` | DailyVisitReportController@destroy | delete_dvr |

## General Tasks

All `auth` only — no permission gate (permissions handled in controller via model `isVisibleTo` / `isEditableBy`).

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/general-tasks` | `general-tasks.index` | GeneralTaskController@index |
| GET | `/general-tasks/data` | `general-tasks.data` | GeneralTaskController@taskData |
| GET | `/general-tasks/search-loans` | `general-tasks.search-loans` | GeneralTaskController@searchLoans |
| POST | `/general-tasks` | `general-tasks.store` | GeneralTaskController@store |
| GET | `/general-tasks/{task}` | `general-tasks.show` | GeneralTaskController@show |
| PUT | `/general-tasks/{task}` | `general-tasks.update` | GeneralTaskController@update |
| DELETE | `/general-tasks/{task}` | `general-tasks.destroy` | GeneralTaskController@destroy |
| PATCH | `/general-tasks/{task}/status` | `general-tasks.update-status` | GeneralTaskController@updateStatus |
| POST | `/general-tasks/{task}/comments` | `general-tasks.comments.store` | GeneralTaskController@storeComment |
| DELETE | `/general-tasks/{task}/comments/{comment}` | `general-tasks.comments.destroy` | GeneralTaskController@destroyComment |

## Notifications

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/notifications` | `notifications.index` | NotificationController@index |
| GET | `/api/notifications/count` | `api.notifications.count` | NotificationController@unreadCount |
| POST | `/notifications/{notification}/read` | `notifications.read` | NotificationController@markRead |
| POST | `/notifications/read-all` | `notifications.read-all` | NotificationController@markAllRead |

## Impersonation

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/api/impersonate/users` | `impersonate.users` | ImpersonateController@users |
| GET | `/impersonate/take/{id}` | `impersonate.take` | ImpersonateController@take |
| GET | `/impersonate/leave` | `impersonate.leave` | ImpersonateController@leave |

Authorization handled inside the controller via `User::canImpersonate()` + `User::canBeImpersonated()`. By default only `super_admin`; set `ALLOW_IMPERSONATE_ALL=true` to enable for all users.

## Reports

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/reports/turnaround` | `reports.turnaround` | ReportController@turnaround |
| GET | `/reports/turnaround/data` | `reports.turnaround.data` | ReportController@turnaroundData |

Data scope filtered inside controller by role (all / branch / self).

## API endpoints

### Public (`routes/api.php`, no auth)

| Method | URI | Controller | Purpose |
|---|---|---|---|
| GET | `/api/config/public` | Api\ConfigApiController@public | App config + bank charges for PWA/offline |
| GET | `/api/notes` | Api\NotesApiController@get | Free-form offline notes read |

### Web group (session auth)

| Method | URI | Name | Controller | Purpose |
|---|---|---|---|---|
| POST | `/api/sync` | `api.sync` | Api\SyncApiController@sync | PWA offline quotation batch sync |
| POST | `/api/notes` | `api.notes.save` | Api\NotesApiController@save | Save notes |

## Route model bindings

Implicit binding — Laravel resolves by class name convention:

| Param | Model |
|---|---|
| `{user}` | App\Models\User |
| `{loan}` | App\Models\LoanDetail |
| `{quotation}` | App\Models\Quotation |
| `{task}` | App\Models\GeneralTask |
| `{comment}` | App\Models\GeneralTaskComment |
| `{notification}` | App\Models\ShfNotification |
| `{dvr}` | App\Models\DailyVisitReport |
| `{product}` | App\Models\Product |
| `{bank}` | App\Models\Bank |
| `{branch}` | App\Models\Branch |
| `{location}` | App\Models\Location |
| `{role}` | App\Models\Role |
| `{document}` | App\Models\LoanDocument |
| `{query}` | App\Models\StageQuery |

## Rate limiting

- Email verification routes throttled at `6,1` (6 attempts / minute).

## CSRF

All non-GET web routes require CSRF token via `@csrf` Blade directive or `X-CSRF-TOKEN` header. PWA AJAX reads `<meta name="csrf-token">`.
