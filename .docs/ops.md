# Operations

Runtime processes, deployment, and background workers.

## Queue worker

The app uses the `database` queue driver (`QUEUE_CONNECTION=database`). Tables: `jobs`, `job_batches`, `failed_jobs`.

### Development

```bash
php artisan queue:listen --tries=1 --timeout=0
# or use the composer dev script (runs server + queue + logs + vite)
composer dev
```

### Production — Linux (supervisor)

`/etc/supervisor/conf.d/shf-queue.conf`:

```ini
[program:shf-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/shf/artisan queue:work --tries=3 --timeout=120 --sleep=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/shf-queue.log
stopwaitsecs=130
```

Reload after change:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start shf-queue:*
```

### Production — Linux (systemd alternative)

`/etc/systemd/system/shf-queue.service`:

```ini
[Unit]
Description=SHF queue worker
After=network.target mariadb.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/shf
ExecStart=/usr/bin/php artisan queue:work --tries=3 --timeout=120 --sleep=3
TimeoutStopSec=130

[Install]
WantedBy=multi-user.target
```

Enable:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now shf-queue
sudo systemctl status shf-queue
```

### Production — Windows (NSSM)

The primary dev environment is Windows. To run a persistent queue worker:

```powershell
nssm install SHFQueue "C:\php\php.exe" "F:\G Drive\Projects\shf_loan_quotation\artisan" queue:work --tries=3 --timeout=120 --sleep=3
nssm set SHFQueue AppDirectory "F:\G Drive\Projects\shf_loan_quotation"
nssm set SHFQueue Start SERVICE_AUTO_START
nssm start SHFQueue
```

### After deploying code

Any time code changes, tell running workers to pick up new code:

```bash
php artisan queue:restart
```

Workers hold code in memory — without this, they keep running the old version.

### Monitoring failed jobs

```bash
php artisan queue:failed                      # list
php artisan queue:retry {id|all}              # retry
php artisan queue:forget {id}                 # drop
```

### When to queue work

Queue anything that takes more than a few hundred milliseconds or doesn't need a sync result:

- PDF generation (quotation, KFS, sanction letter)
- Notification dispatch (push, broadcast, email)
- Bulk operations (imports, batch exports)
- Third-party API calls (banks, SMS, email)

## Scheduler

If time-based tasks are added (daily reports, stale-query sweep, DVR follow-up reminders), schedule them in `routes/console.php` and run:

Linux cron (`crontab -e`):

```
* * * * * cd /var/www/shf && php artisan schedule:run >> /dev/null 2>&1
```

Windows Task Scheduler: run `php artisan schedule:run` every minute in the project directory.

### Registered schedule (as of 2026-04-18)

Verify with `php artisan schedule:list`:

| Cron | Command | Purpose |
|---|---|---|
| `0 * * * *` | `notifications:mark-stale-read --hours=48` | Auto-mark unread notifications older than 48h |
| `* * * * *` | `queue:work --stop-when-empty --tries=3 --timeout=120` | Drain queued jobs (withoutOverlapping) |
| `0 8 * * *` | `reminders:send-daily --when=morning` | Count today's DVR follow-ups + tasks, notify each user |
| `0 20 * * *` | `reminders:send-daily --when=evening` | Count tomorrow's DVR follow-ups + tasks (heads-up) |

## Environment configuration

- `APP_ENV=production` flips the DB config to use the `LIVE_DB_*` variables (see `config/database.php`).
- Confirm `APP_DEBUG=false` in production.
- `ALLOW_IMPERSONATE_ALL=0` in production (only super_admin can impersonate).
- `OPEN_RATE_PF_PARALLEL=0|1` — controls workflow ordering:
  - `0` (default): Rate & PF opens sequentially after `parallel_processing` completes and `is_sanctioned=true`.
  - `1`: Rate & PF opens alongside the parallel sub-stages after `bsm_osv` completes; Sanction Letter waits for BOTH `parallel_processing` and `rate_pf` to finish.
  - Safe to flip at runtime (`php artisan config:clear` in prod). Does not affect the frozen `workflow_config` snapshot on existing loans.
  - Flipping OFF→ON does NOT retro-open rate_pf for loans already past `bsm_osv`; only new completions of `bsm_osv` follow the parallel flow. Flipping ON→OFF is safe — `checkParallelCompletion` gates on status and will not double-start a rate_pf that is already in_progress/completed.
