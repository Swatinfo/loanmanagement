# SHF Docs Index

Developer documentation for **Shreenathji Home Finance (SHF)** — a bilingual (English/Gujarati) loan management platform on Laravel 12 + SQLite.

## Reading order

1. **Start here** if you're new: `authentication.md` → `permissions.md` → `roles.md` → `users.md`
2. **Before touching code**: re-read the pre-read gate in `CLAUDE.md` for the specific area
3. **Reference files** (under `.claude/`): always source of truth for schema, routes, services

## Guides by area

| Area | Guide |
|---|---|
| Auth & session | `authentication.md` |
| Access control | `permissions.md`, `roles.md`, `user-assignment.md` |
| User management | `users.md` |
| Customers | see `.claude/routes-reference.md` → Customers; scope in `Customer::scopeVisibleTo` |
| Quotations & PDF | `quotations.md`, `pdf-generation.md` |
| Loan CRUD | `loans.md` |
| Loan workflow (user-facing) | `workflow-guide.md` |
| Loan workflow (dev) | `workflow-developer.md` |
| Workflow code changes (step-by-step) | `workflow-code-changes.md` |
| Dashboard | `dashboard.md` |
| General tasks | `general-tasks.md` |
| DVR (Daily Visit Reports) | `dvr.md` |
| Settings & config | `settings.md` |
| Activity log | `activity-log.md` |
| Models | `models.md` |
| Database | `database.md` |
| API endpoints | `api.md` |
| Frontend (CSS/JS) | `frontend.md` |
| Blade views | `views.md` |
| Offline / PWA | `offline-pwa.md` |
| Operations (queue, scheduler, deploy) | `ops.md` |
| Real-time + Web Push setup | `realtime-setup.md` |
| Service layer audit & inventory | `service-audit.md` |
| Settings package decision record | `settings-package-decision.md` |

## Deep references

Under `.claude/`:

- `database-schema.md` — every table, column, FK
- `routes-reference.md` — every route with controller + permission
- `services-reference.md` — every service method signature

## Source-of-truth files (edit these, not docs)

- `public/css/shf.css` — every `shf-*` class
- `public/js/shf-app.js` — `SHF.*` helpers
- `public/js/shf-loans.js` — `SHFLoans.*` helpers
- `config/app-defaults.php` — default config values
- `config/permissions.php` — permission catalogue
