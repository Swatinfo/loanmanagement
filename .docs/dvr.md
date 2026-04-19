# Daily Visit Reports (DVR)

Field activity tracking for advisors and branch staff. Each visit captures contact, purpose, outcome, and optional follow-up. Visits can chain via parent/follow-up links.

## Surfaces

- `/dvr` — list with filters (DataTable)
- `/dvr/{id}` — detail with visit chain timeline
- `POST /dvr` — create (modal on dashboard or list page)
- `PUT /dvr/{id}` — edit (creator or with `edit_dvr`)
- `PATCH /dvr/{id}/follow-up-done` — mark follow-up complete without logging a new visit
- `DELETE /dvr/{id}` — delete

Autocomplete helpers: `/dvr/search-loans`, `/dvr/search-quotations`, `/dvr/search-contacts`.

## Permissions

| Permission | Routes gated |
|---|---|
| `view_dvr` | all list/show/search routes |
| `create_dvr` | `POST /dvr` |
| `edit_dvr` | `PUT`, `PATCH` (mark follow-up done) |
| `delete_dvr` | `DELETE` |
| `view_all_dvr` | expands visibility scope to all users |

## Model

`DailyVisitReport`. Fields:

- `user_id` — creator
- `visit_date` (date)
- `contact_name`, `contact_phone` (max 20)
- `contact_type` — key from config `dvrContactTypes`
- `purpose` — key from config `dvrPurposes`
- `notes`, `outcome`, `follow_up_notes` (text, nullable)
- `follow_up_needed` (bool), `follow_up_date` (date), `is_follow_up_done` (bool)
- `parent_visit_id`, `follow_up_visit_id` — self-FK for chains
- `quotation_id`, `loan_id` — optional link to a quotation or loan
- `branch_id` — set from user's default branch on create

## Configurable vocab

Contact types and visit purposes are both config-driven arrays. Defaults in `config/app-defaults.php`, editable via `/settings/dvr-contact-types` and `/settings/dvr-purposes`:

```php
'dvrContactTypes' => [
  ['key' => 'existing_customer', 'label_en' => 'Existing Customer', 'label_gu' => 'હાલનો ગ્રાહક'],
  ['key' => 'new_customer', 'label_en' => 'New Customer', ...],
  ['key' => 'CA', ...],
  ['key' => 'builder_developer', ...],
  ['key' => 'dsa_connector', ...],
  ['key' => 'other', ...],
];

'dvrPurposes' => [
  ['key' => 'new_lead', ...],
  ['key' => 'follow_up', ...],
  ['key' => 'document_collection', ...],
  ['key' => 'quotation_delivery', ...],
  ['key' => 'payment_disbursement', ...],
  ['key' => 'relationship', ...],
  ['key' => 'other', ...],
];
```

UI reads keys from the model, labels from config. Don't hardcode labels in Blade.

## Visibility scope

`scopeVisibleTo($user)`:

1. If user has `view_all_dvr` → all visits
2. If user is `branch_manager` or `bdh` → own visits + visits from users in their branches (via `user_branches` pivot)
3. Else → own visits only

## Create flow

Validation:
- `contact_name` max 255 (nullable)
- `contact_phone` nullable max 20
- `contact_type`, `purpose` max 50
- `visit_date` d/m/Y
- `notes`, `outcome`, `follow_up_notes` nullable, max 5000
- `follow_up_needed` boolean; `follow_up_date` required_if + `after:today`
- `quotation_id`, `loan_id`, `parent_visit_id` nullable with exists checks

Steps:

1. Convert dates d/m/Y → Y-m-d
2. Set `branch_id` from `user->default_branch_id`
3. If `parent_visit_id` set:
   - Mark parent: `is_follow_up_done = true`
   - Link parent.follow_up_visit_id = newly created id
4. Create row
5. Log activity (contact name + type)
6. Redirect: to `dvr.show` (or back to `dvr.index` if `_from_dashboard=1` query param)

## Edit / delete

- `isEditableBy($user)` — creator + `edit_dvr` permission, OR super_admin
- `isDeletableBy($user)` — `delete_dvr` permission
- Edit validation identical to create (except `parent_visit_id` excluded)
- Delete unlinks from parent visit if this was a follow-up (clears parent.follow_up_visit_id)

## Mark follow-up done

`PATCH /dvr/{id}/follow-up-done` — toggles `is_follow_up_done = true` without creating a new visit. Used when the user called the contact but doesn't need a full visit record.

## Visit chains

Each DVR can have:
- `parent_visit_id` → the visit this is a follow-up to
- `follow_up_visit_id` → the visit created as the follow-up (set when a child is created)

`getVisitChain()` method traverses the full chain (parents + children) and returns a collection sorted by date — used in the show page's timeline.

## List & filters

`GET /dvr/data` (DataTables server-side):

Filters:
- `view`: `my_visits` (default), `my_branch` (BDH/BM only), `all`
- `contact_type`, `purpose` — key filters
- `follow_up`: `active` (needed + not done), `pending` (same + not past), `overdue` (past, not done), `done`, `all`
- `date_from`, `date_to` (d/m/Y)
- `user_id`

Search across contact name, contact phone, notes, user name, loan number, customer name.

## Follow-up urgency badges

Derived in the data builder:

- **overdue** — follow_up_needed, follow_up_date past
- **due_today** — follow_up_date == today
- **due_tomorrow** — follow_up_date == tomorrow
- **due_soon** — within 3 days
- **pending** — follow_up_needed, no date OR future

## Search helpers (AJAX, min 2 chars)

- `/dvr/search-loans?q=` — loan_number, application_number, customer_name
- `/dvr/search-quotations?q=` — customer_name, respects view_all_quotations
- `/dvr/search-contacts?q=` — multi-source: DVR history → customers → loans; deduplicated by (name, phone)

Contacts endpoint is the slickest — it lets the user re-use existing contacts across visits without re-typing.

## Dashboard integration

- `dashboard/dvr-data` AJAX feed for the DVR tab
- **New Visit** button opens `#dashCreateDvrModal` — same validation as full create route
- Pending/overdue follow-ups surfaced as actionable items on dashboard

## Auto-created from quotation hold

When a quotation is put on hold (`POST /quotations/{id}/hold`), a DVR is auto-created with:
- `quotation_id` = the held quotation
- `purpose` = `follow_up`
- `contact_type` = `existing_customer`
- `follow_up_needed` = true, `follow_up_date` = user-supplied date
- `notes` = "Quotation #N put on hold. Reason: …"
- `user_id` / `branch_id` = actor + quotation branch (or user default)

## Daily reminder command

`php artisan reminders:send-daily --when=morning|evening` sends an in-app notification (via `NotificationService`) to each user with due DVR follow-ups + tasks for today (morning, 08:00) or tomorrow (evening, 20:00). Scheduled in `routes/console.php`.

## Activity actions logged

`dvr_created`, `dvr_updated`, `dvr_deleted`, `dvr_follow_up_marked_done`.

## See also

- `.claude/database-schema.md` — `daily_visit_reports` table
- `settings.md` — contact types and purposes configuration
- `dashboard.md` — dashboard DVR tab
