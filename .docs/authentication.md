# Authentication

Standard Laravel 12 **Breeze** session authentication. Session-based (`web` guard), Eloquent provider (`App\Models\User`).

## Guards & providers

- Guard: `web` (session driver)
- Provider: `users` table via Eloquent
- Password broker: 60-minute expiry

## Routes

All under `routes/auth.php`. See `.claude/routes-reference.md` for the full table. Highlights:

- `GET /login`, `POST /login` — login form + submission
- `POST /logout` — logout
- `GET /forgot-password`, `POST /forgot-password` — reset link request
- `GET /reset-password/{token}`, `POST /reset-password` — reset form + submit
- `GET /verify-email`, `GET /verify-email/{id}/{hash}` — verification (rate-limited 6/min)
- `GET /confirm-password`, `POST /confirm-password` — password confirmation gate
- `PUT /password` — change password (profile)

**Registration is disabled** — no `/register` route is wired. Users are created by admins via `/users/create`.

## Active-user enforcement

`EnsureUserIsActive` middleware is **appended to the `web` group** globally. On every authenticated request:

- If `Auth::check()` and `user->is_active === false`:
  - `Auth::logout()`
  - `$request->session()->invalidate()`
  - `$request->session()->regenerateToken()`
  - Redirect to `login` with error message

Deactivation is effectively immediate — the user cannot make another request after `is_active=false`.

## Permission gate

Routes use `permission:{slug}` middleware (alias for `CheckPermission`). If unauthenticated or user lacks the permission, responds 403.

```php
Route::middleware(['auth', 'permission:create_loan'])->group(function () { ... });
```

Resolution in `PermissionService` — see `permissions.md`.

## Password hashing

`User::$casts['password'] = 'hashed'` — assignment hashes automatically. Don't call `Hash::make()` yourself when setting on the model; assign plaintext and let the cast hash it.

## Profile

`ProfileController` (Breeze default):

- `GET /profile` — edit form (shows info + password + delete)
- `PATCH /profile` — updates name/email/phone (resets `email_verified_at` if email changed)
- `DELETE /profile` — soft-deletes current user, logs them out, invalidates session

## Impersonation

Custom routes (`ImpersonateController`), not Lab404 package UI. See `users.md`.

- Only `super_admin` can impersonate by default
- Set `ALLOW_IMPERSONATE_ALL=true` (env → `app.allow_impersonate_all`) to open up
- `super_admin` users can never be impersonated (`canBeImpersonated()` returns false)

## Session / CSRF

- `<meta name="csrf-token">` embedded in `layouts/app.blade.php`
- All POST/PUT/PATCH/DELETE requests require `@csrf` or `X-CSRF-TOKEN` header
- Offline / PWA AJAX reads the meta and sends header

## Known test quirks

Some Breeze-default auth and profile feature tests fail because:
- `EnsureUserIsActive` middleware interferes with the fake user setup
- Registration routes are disabled (some tests still invoke them)

These are pre-existing — do **not** fix while doing unrelated work.
