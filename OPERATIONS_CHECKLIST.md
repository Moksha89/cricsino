# Operations Checklist

Routine operational procedures for https://69players.io

---

## Daily Checks

Perform these every day:

- [ ] **Site is up:** Visit https://69players.io and confirm it loads
- [ ] **Check failed jobs:**
  ```bash
  php artisan queue:failed
  ```
  If any exist, investigate and retry or clear:
  ```bash
  php artisan queue:retry all
  ```
- [ ] **Check today's error log:**
  ```bash
  tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
  ```
  Look for `ERROR`, `CRITICAL`, or `EMERGENCY` entries.
- [ ] **Check pending deposits:**
  Visit `/admin/deposits` — approve or reject any pending deposits.
- [ ] **Check pending withdrawals:**
  Visit `/admin/withdrawals` — process any pending withdrawals.
- [ ] **Check support tickets:**
  Visit `/admin/support` — respond to any open tickets.
- [ ] **Verify queue workers running:**
  ```bash
  ps aux | grep queue:work
  ```
  If no workers found:
  ```bash
  nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &
  ```

---

## Weekly Checks

Perform these once per week:

- [ ] **Disk space check:**
  ```bash
  df -h
  ```
  Alert if any partition exceeds 80%.
- [ ] **Log file size check:**
  ```bash
  du -sh storage/logs/
  ls -la storage/logs/ | tail -10
  ```
  Daily rotation should keep this manageable. If growing too large, verify `LOG_DAYS=14`.
- [ ] **Scheduler verification:**
  ```bash
  crontab -l
  php artisan schedule:list
  ```
  Confirm 4 scheduled commands are listed.
- [ ] **Database size check:**
  ```bash
  php artisan tinker --execute="
  \$tables = DB::select('SELECT table_name, ROUND(data_length/1024/1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = \"betriver\" ORDER BY data_length DESC LIMIT 10');
  foreach (\$tables as \$t) echo \$t->table_name . ': ' . \$t->size_mb . ' MB' . PHP_EOL;
  "
  ```
- [ ] **Review promotion claims:**
  Visit `/admin/promotions` — check for suspicious or duplicate claims.
- [ ] **Review user registrations:**
  Visit `/admin/users` — check for suspicious accounts.

---

## Monthly Checks

Perform these once per month:

- [ ] **Full database backup:**
  ```bash
  mysqldump -u betriver -p betriver > /home/administrator/backups/betriver_monthly_$(date +%Y%m%d).sql
  ```
- [ ] **Verify backup is restorable:**
  ```bash
  # Test on a separate database — never on production
  mysql -u betriver -p betriver_test < /home/administrator/backups/betriver_monthly_YYYYMMDD.sql
  ```
- [ ] **Review PHP and package updates:**
  ```bash
  php -v
  composer outdated
  npm outdated
  ```
- [ ] **Check SSL certificate expiry:**
  ```bash
  echo | openssl s_client -connect 69players.io:443 2>/dev/null | openssl x509 -noout -enddate
  ```
- [ ] **Review application settings:**
  Visit `/admin/settings` — verify all values are correct.
- [ ] **Clear old log files** (if LOG_DAYS isn't auto-purging):
  ```bash
  find storage/logs -name "laravel-*.log" -mtime +30 -delete
  ```
- [ ] **Review agent transactions:**
  Visit `/admin/agents` — check for unusual activity.
- [ ] **Update TheOddsApi usage:**
  Check remaining API quota at https://the-odds-api.com/account/

---

## Backup Verification

### Current Status: No automated backup configured

**Recommended setup:**

1. **Daily automated backup via cron:**
   ```bash
   # Add to crontab
   0 3 * * * mysqldump -u betriver -p'betriver123' betriver | gzip > /home/administrator/backups/betriver_$(date +\%Y\%m\%d).sql.gz
   ```

2. **Keep 30 days of backups:**
   ```bash
   # Add cleanup to crontab
   0 4 * * * find /home/administrator/backups -name "betriver_*.sql.gz" -mtime +30 -delete
   ```

3. **Verify backup directory exists:**
   ```bash
   mkdir -p /home/administrator/backups
   ```

---

## Failed Jobs Check

```bash
# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry a specific job
php artisan queue:retry <job-id>

# Clear all failed jobs (only when confirmed safe)
php artisan queue:flush
```

Common failure causes:
- Database connection timeout
- External API unavailable (payment gateway, odds API)
- Memory limit exceeded
- Job timeout exceeded

---

## Scheduler Check

```bash
# Verify crontab
crontab -l
# Expected: * * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Verify scheduled commands
php artisan schedule:list
# Expected:
#   settle:stakes      */5  * * * *
#   update:games       */5  * * * *
#   withdraws:update   */15 * * * *
#   update:rates       0    0 * * *
```

Commands that should NOT be scheduled:
- `odds:import` — manual only via admin UI
- `scores:refresh` — manual only via admin UI

---

## Queue Worker Check

```bash
# Check if workers are running
ps aux | grep queue:work

# If no workers found, start one
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# After deployments, always restart workers
php artisan queue:restart
```

---

## Disk Space Check

```bash
# Overall disk usage
df -h

# Application storage usage
du -sh storage/
du -sh storage/logs/
du -sh storage/framework/cache/
du -sh storage/framework/sessions/

# Database size
mysql -u betriver -p -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'betriver';"
```

Alert thresholds:
- Disk usage > 80%: Warning
- Disk usage > 90%: Critical — clear logs and caches immediately

---

## Log File Check

```bash
# List recent logs
ls -la storage/logs/laravel-*.log | tail -7

# Count errors in today's log
grep -c 'ERROR\|CRITICAL\|EMERGENCY' storage/logs/laravel-$(date +%Y-%m-%d).log

# Search for specific issues
grep -i 'deposit\|payment' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -10
grep -i 'settle\|stake' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -10
grep -i 'exception\|fatal' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -10

# Verify no secrets in logs
grep -ri 'api_key\|secret\|password' storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## Odds Import — Manual Process

Odds are imported manually via the admin panel. No automatic scheduling.

1. Log in as admin
2. Navigate to `/admin/odds-import`
3. Select the sport (e.g., "Cricket — IPL")
4. Click "Import Odds"
5. Verify the import log shows:
   - Status: `completed`
   - Games imported count
   - Odds imported count
   - Any errors or skipped items

**CLI alternative:**
```bash
php artisan odds:import cricket_ipl
```

**Important:**
- Free tier allows 500 requests/month — use sparingly
- Each import uses 1 API request
- Re-imports update existing odds (no duplicates)
- Imported odds do NOT affect exchange order book
- Imported odds do NOT trigger settlement

---

## Live Score Refresh — Manual Process

Score refresh is manual only. No automatic scheduling.

1. Log in as admin
2. Navigate to `/admin/live-scores`
3. Select the sport
4. Click "Refresh Scores"
5. Verify the refresh log shows:
   - Games updated count
   - Errors count

**CLI alternative:**
```bash
php artisan scores:refresh cricket_ipl
```

**Important:**
- Score refresh does NOT settle bets
- Score refresh does NOT change wallet balances
- Score refresh does NOT mark bets won/lost
- Only updates the `scores` table and `is_live`/`closed` game flags
- If a game shows no score data, it displays "Score unavailable" — this is normal

---

## Deposit / Withdrawal Monitoring

### Deposits
- Check `/admin/deposits` daily
- Approve legitimate pending deposits
- The approval is idempotent (safe against double-clicks)
- Failed or already-completed deposits cannot be re-approved

### Withdrawals
- Check `/admin/withdrawals` daily
- `withdraws:update` runs every 15 minutes automatically
- For manual processing: `php artisan withdraws:update`
- Review large withdrawals before approval

### Transaction Audit
- All wallet changes create transaction records
- Admin credit/debit creates `ADMIN_ACTION` type transactions
- Check `/admin/transactions` for audit trail

---

## Promotion Claim Monitoring

- Check `/admin/promotions` weekly
- Review active promotions and claim counts
- Bonus crediting has double-credit protection (DB transaction + lock)
- Each user can only claim a promotion once
- Check `/admin/bonus-transactions` for bonus audit trail

---

## Support Ticket Monitoring

- Check `/admin/support` daily
- Respond to open tickets promptly
- Support messages are escaped (XSS safe)
- Users can only see their own tickets
- Admin can see all tickets

---

## Known Remaining Risks

| Risk | Severity | Recommended Action |
|------|----------|-------------------|
| Debugbar installed (disabled via `APP_DEBUG=false`) | Low | Remove from `composer.json` or move to `require-dev` |
| No automated DB backup | Medium | Configure daily mysqldump cron (see Backup section) |
| No Sentry/Bugsnag error monitoring | Medium | Install Sentry Laravel SDK and configure DSN |
| No uptime monitoring | Medium | Set up UptimeRobot, Pingdom, or similar service |
| Casino bet placement lacks DB transaction/lock | Medium | Add `DB::transaction` + `lockForUpdate` in next hardening pass |
| PWA icon-192x192.png returns 404 | Low | Add icon file to `public/` directory |
