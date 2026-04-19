# Contributing to SHF Loan Quotation

## Branches

- `main` is the protected production branch. Do not commit or push directly.
- All work happens on feature branches cut from `main`:
  - `feat/<short-description>` — new features
  - `fix/<short-description>` — bug fixes
  - `refactor/<short-description>` — internal changes, no behavior delta
  - `docs/<short-description>` — documentation-only
  - `chore/<short-description>` — tooling, deps, build config

## Commits

- One logical change per commit. Do not bundle unrelated changes.
- **Do not** use "Replace entire codebase" or any bulk-replace commit message.
- Use conventional commits:

```
<type>(<scope>): <short description>

<optional body — why, not what>
```

- `type`: `feat`, `fix`, `refactor`, `docs`, `chore`, `test`, `perf`
- `scope`: optional, e.g., `loans`, `quotations`, `pdf`, `permissions`, `dvr`
- Keep subject line ≤ 72 chars, imperative mood ("add", not "added")

Examples:

```
feat(loans): add OTC clearance stage for cheque disbursements
fix(pdf): escape Gujarati content in bank comparison template
refactor(permissions): extract tier resolution to dedicated service
```

## Pull requests

- Every change reaches `main` through a PR — no exceptions, including hotfixes.
- PR title uses the same conventional-commit format as the commit.
- PR description must include:
  1. **Why** the change is needed (link ticket/issue if applicable)
  2. **What** changed at a high level
  3. **Test plan** — how to verify the change works
  4. **Doc updates** — list reference docs updated (`.docs/*.md`, `.claude/*.md`)
- Link to screenshots/screencasts for any UI change.
- Keep PRs small enough to review in one sitting (target < 400 lines changed).

## Before opening a PR

- [ ] Branch is rebased on latest `main`
- [ ] `php artisan test --compact` passes locally
- [ ] `vendor/bin/pint --dirty --format agent` run on all changed PHP files
- [ ] Relevant reference docs updated (see CLAUDE.md doc-sync checklist)
- [ ] `tasks/todo.md` progress updated if part of a tracked initiative

## Forbidden

- Force-pushing to `main`
- Skipping hooks (`--no-verify`)
- Amending published commits on shared branches
- Committing `.env`, credentials, or secrets
- Bulk "replace everything" commits that obscure history
- Merging your own PR without review for changes that touch permissions, workflow stages, or money math

## Reviews

- At least one approval from another engineer before merge.
- Reviewer checks: correctness, doc sync, test coverage, and adherence to the `.claude/rules/` guidelines auto-loaded into Claude Code sessions.
- Squash-merge is the default; use merge commits only when preserving history inside a long-running feature branch is worth it.

## Hotfix flow

1. Cut `fix/<slug>` from `main`.
2. Minimal diff targeting only the bug.
3. Test plan in PR must cover the regression case explicitly.
4. Same review rules apply — no self-merge on production-affecting fixes.
