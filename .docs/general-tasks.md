# General Tasks

Personal / delegated tasks — separate from loan workflow stages. Any user can create tasks for themselves or assign to others. Optional link to a loan.

## Surfaces

- `/general-tasks` — list (DataTable)
- `/general-tasks/create` — via modal (on dashboard or list page)
- `/general-tasks/{id}` — show with comments
- `/general-tasks/{id}/edit`, DELETE — creator only
- `/general-tasks/{id}/status` — PATCH (creator or assignee)
- `/general-tasks/{id}/comments` — POST to add, DELETE to remove own

**No permission gate on the routes** — all authenticated users can use tasks. Visibility & edit rights are enforced inside the model and controller.

## Model

`GeneralTask` (table `general_tasks`):

- `title`, `description`, `created_by`, `assigned_to`, `loan_detail_id`, `quotation_id`
- `status`: `pending` / `in_progress` / `completed` / `cancelled`
- `priority`: `low` / `normal` / `high` / `urgent`
- `due_date`, `completed_at`

Constants: `STATUSES`, `STATUS_LABELS` (with badge CSS classes), `PRIORITIES`, `PRIORITY_LABELS`.

Relationships: `creator`, `assignee`, `loan`, `comments` (HasMany `GeneralTaskComment`, latest first).

Scopes: `scopeVisibleTo($user)`, `scopePending()` (status in pending, in_progress).

Accessors: `statusBadgeHtml`, `priorityBadgeHtml`, `isOverdue`.

Methods: `isVisibleTo`, `isEditableBy` (creator only), `canChangeStatus` (creator OR assignee), `isDeletableBy` (creator only).

### Visibility scope

`scopeVisibleTo($user)`:

1. If user has `view_all_tasks` permission → all tasks (read-only in UI for admins)
2. Else OR-union:
   - `created_by = user` OR `assigned_to = user` (own tasks)
   - If user has a branch (BDH / branch manager): tasks where `created_by` or `assigned_to` is in that user's branch (via `user_branches` pivot)

## Create flow

`GeneralTaskController@store`:

Validation:
- `title` required, max 255
- `description` nullable, max 5000
- `assigned_to` nullable exists:users (null = self-task)
- `loan_detail_id` nullable exists:loan_details
- `priority` required, in PRIORITIES
- `due_date` required, d/m/Y format

Steps:

1. Convert date from d/m/Y to Y-m-d
2. Create `GeneralTask`
3. If `assigned_to` is set AND different from creator → `NotificationService::notify()` to the assignee
4. Log activity with assignee name

**After creation, redirect to the task show page** (`/general-tasks/{id}`) — not back to the list.

## Status changes

`PATCH /general-tasks/{id}/status`:

- Validation: `status` in STATUSES
- Auth: creator OR assigned_to can change status
- **Only the creator** can cancel
- When transitioning to `completed`: set `completed_at = now()`
- When reverting from `completed`: clear `completed_at`
- If assignee completed a task assigned by someone else → notify creator

Response: `{ success, status, status_html }`.

## Comments

`POST /general-tasks/{id}/comments` — body: `body` (max 5000). Auth: anyone who can view the task (`isVisibleTo`).

On comment post:
- Notify the other party (if creator comments → notify assignee; if assignee → notify creator)
- Log activity with truncated comment text

`DELETE /general-tasks/{id}/comments/{comment}` — only the comment author.

## Listing & filters

`GET /general-tasks/data` (DataTables server-side):

Filters:
- `view`: `my_tasks_and_assigned` (default), `my_tasks`, `assigned_to_me`, `my_branch` (BDH), `all` (admin)
- `status`: `active` (pending + in_progress), `pending`, `in_progress`, `completed`, `cancelled`
- `priority`
- Search across title, assignee name, creator name, loan number, customer name

Due-date urgency badges (computed per row, excludes completed/cancelled):
- overdue, due_today, due_tomorrow, due_soon

## Dashboard integration

- **Personal Tasks** tab on the dashboard uses `/dashboard/task-data`
- Default-tab priority: overdue tasks → pending tasks → other tabs
- **New Task** button on the dashboard opens an inline modal (`#dashCreateTaskModal`) — creates the task, closes modal, reloads the tab

## Loan linking

Optional `loan_detail_id` creates a back-reference between the task and a loan. UI includes autocomplete at `/general-tasks/search-loans?q=` (min 2 chars) that matches by loan number, app number, customer name.

Tasks don't create stage assignments — they're purely informational / tracking outside the workflow.

## Delete

`DELETE /general-tasks/{id}`:
- Creator only (`isDeletableBy`)
- Activity logged with title
- Soft delete not in use — rows are permanently removed

## Related activity types

- `task_created`, `task_updated`, `task_status_changed`, `task_comment_added`, `task_deleted`, `task_comment_deleted`

## See also

- `.claude/routes-reference.md` — full routes list
- `.claude/database-schema.md` — `general_tasks`, `general_task_comments` tables
- `dashboard.md` — dashboard tab integration
