# Activity Log

Audit trail of user-facing actions. Powered by `ActivityLog` model + `ActivityLog::log()` helper.

## Model

Table: `activity_logs` — see `.claude/database-schema.md`.

| Column | Purpose |
|---|---|
| `user_id` | Who did it (nullable — null for system/seeder actions) |
| `action` | Slug-ish code (e.g., `loan_created`, `stage_completed`, `user_activated`) |
| `subject_type` | Fully-qualified model class, e.g., `App\Models\LoanDetail` |
| `subject_id` | PK of the subject |
| `properties` | JSON — free-form event details |
| `ip_address` | IPv4/IPv6, max 45 chars |
| `user_agent` | Browser UA string |
| `created_at`, `updated_at` | Timestamps |

Index on `user_id`, composite `(subject_type, subject_id)`, and `created_at`.

## Writing

Use the static helper:

```php
ActivityLog::log('loan_created', $loan, [
    'loan_number' => $loan->loan_number,
    'amount' => $loan->loan_amount,
    'bank' => $loan->bank_name,
]);
```

Automatically captures `user_id` (from `auth()->id()`), `ip_address`, `user_agent`. Pass null subject for non-entity events. Properties is arbitrary — keep it flat + JSON-safe.

## Conventions for the `action` slug

| Prefix | Usage |
|---|---|
| `{entity}_created` | new row: `loan_created`, `quotation_created`, `user_created` |
| `{entity}_updated` | edit: `loan_updated`, `user_updated` |
| `{entity}_deleted` | soft/hard delete |
| `stage_*` | workflow actions: `stage_assigned`, `stage_completed`, `stage_rejected`, `stage_skipped`, `stage_transferred`, `stage_reverted`, `stage_notes_saved` |
| `doc_*` | document operations: `doc_status_changed`, `doc_uploaded`, `doc_deleted`, `doc_file_deleted` |
| `query_*` | `query_raised`, `query_responded`, `query_resolved` |
| `loan_status_changed` | on_hold / cancelled / reactivated |
| `user_activated`, `user_deactivated` | toggle active |

There's no enum enforcement — controllers and services just pass strings. Stay consistent.

## Reading

### UI

- `GET /activity-log` (permission: `view_activity_log`) — list with filters + DataTable
- `GET /activity-log/data` — AJAX data endpoint

Filters include user, action, date range, subject type. Results show action, user, subject (with link if entity still exists), properties (formatted), timestamp.

### Per-entity log

The loan timeline (`LoanTimelineService::getTimeline()`) pulls `ActivityLog` rows indirectly — it reads from the same `stage_assignments`, `stage_queries`, etc. directly rather than re-querying activity_logs. Activity log is for ad-hoc admin review.

## When to log

**Log:**
- Creation, update, delete of first-class entities
- Permission / role / config changes
- Workflow actions (stage transitions, transfers, query flow)
- Security-relevant actions (impersonate, user toggle)
- Document operations (received, rejected, uploaded, deleted)

**Don't log:**
- Read-only page views (too noisy — use Laravel request log for that)
- AJAX polling (notifications count, DataTable loads)
- Every keystroke in a form — log once on submit

## Retention

No built-in rotation. If the table grows large (~hundreds of thousands of rows), add an admin archive/export script or a scheduled prune.

## See also

- `.claude/database-schema.md` — `activity_logs` table
- `.claude/services-reference.md` — services that log (virtually all write operations)
- `dashboard.md` — Activity Log page is a dashboard-linked admin tool
