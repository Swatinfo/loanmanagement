# Dashboard

Single-page dashboard for the authenticated user at `GET /dashboard`. Controller: `DashboardController`.

## Structure

Top stat cards + a tabbed panel with several DataTables fed by AJAX endpoints. No fixed sidebar layout — the navbar is the only chrome.

## Stat cards

Up to 4 cards rendered at the top of the page based on user permissions:

- **Quotations** — total, today, this month, converted
- **Loans** — total, active, completed, this month (shown if user has `view_loans`)
- **Tasks** — personal task counts
- **DVR** — visit counts

Each uses the `shf-stat-card` component with color-coded variants (`shf-stat-card-blue`, `-green`, `-accent`, `-warning`).

## Tabs

All tabs are permission-gated for visibility. Default selection is **data-driven** — pick the tab that has the most actionable items, not merely the first visible one.

### Default tab priority (in order)

1. Overdue personal tasks (due date past, not completed)
2. Loan tasks currently assigned to user (pending stages)
3. Pending personal tasks (not overdue)
4. Active loans the user owns or is working on
5. Unconverted quotations
6. Personal tasks (fallback)

### Tab list

- **Personal Tasks** — general tasks created by / assigned to the user (see `general-tasks.md`)
- **Loan Tasks** — current stage assignments for loans the user is working on
- **Active Loans** — loans the user created / advises / is currently assigned to a stage of
- **Quotations** — user's recent quotations, with conversion status
- **DVR** — user's recent visits + pending follow-ups
- **Activity Log** — (admin / `view_activity_log` only) recent audit events

## AJAX endpoints

DataTable rows come from:

- `GET /dashboard/quotation-data`
- `GET /dashboard/task-data`
- `GET /dashboard/loan-data`
- `GET /dashboard/dvr-data`

All are session-auth, apply user-scoped filters (visibility rules from the relevant model scopes), return standard DataTables JSON.

## Quick action buttons (header)

Right-aligned in the page header, all gated by permissions:

- **New Quotation** → `/quotations/create` (permission: `create_quotation`)
- **New Task** → opens `#dashCreateTaskModal` inline modal
- **New Visit** → opens `#dashCreateDvrModal` inline modal

Modals are embedded in the dashboard view so creation is a one-click path — **never redirect to another page just to open a modal**.

## DVR create modal

Pre-fills:
- Visit date = today
- User = current user
- Branch = user's default branch

Uses the same validation logic as `DailyVisitReportController@store`. On success, the DVR page's data reloads.

## Task create modal

Pre-fills due date (today + 7 days), normal priority. Optional loan link via autocomplete (`/general-tasks/search-loans`).

## Activity Log page

Separate from the dashboard: `GET /activity-log` (permission: `view_activity_log`). DataTable with filters on user, action, subject type, date range. Data endpoint: `GET /activity-log/data`.

## Responsive patterns

- Stat cards: 4-up on desktop, 2-up on tablet, 1-up on mobile
- Tabs scroll horizontally on narrow screens (`overflow-x: auto` on `.shf-tabs`)
- DataTables use the **mobile card pattern** (`.shf-table-mobile`) on narrow screens — `thead` hides, `tbody` rows become flex-card blocks with `data-label` pseudo-elements

## Implementation notes

- **Permissions decide visibility** — don't hide data behind `hasRole()` checks; use `hasPermission('slug')`
- **Data-driven defaults** — read actual counts before deciding which tab is active; do not assume the first tab is always correct
- DataTable initialization follows the project's standard `{ dom: 'rt<"shf-dt-bottom"ip>' }` layout — no separate search box; inline filters above the table

## See also

- `general-tasks.md` — personal/delegated tasks
- `dvr.md` — daily visit reports
- `loans.md` — loan visibility rules
- `quotations.md` — quotation creation path
- `frontend.md` — stat card / DataTable styling
