# Incident Response Procedures

Emergency procedures for https://69players.io

**Server:** 165.73.253.210
**Web Root:** `/var/www/betriver`
**SSH:** `ssh administrator@165.73.253.210`

---

## Quick Reference: Emergency Commands

```bash
# Maintenance mode ON (blocks all user access)
php artisan down --secret="emergency-bypass-token"

# Maintenance mode OFF
php artisan up

# Check application status
php artisan about

# Check queue workers
ps aux | grep queue:work

# Check recent errors
tail -100 storage/logs/laravel-$(date +%Y-%m-%d).log

# Check scheduler
crontab -l

# Restart queue workers
php artisan queue:restart

# Clear all caches
php artisan optimize:clear
```

---

## 1. Site Is Down

### Symptoms
- HTTP 500 or 502 errors
- Blank page
- Connection timeouts

### Diagnosis

```bash
# Check if PHP-FPM is running
systemctl status php-fpm 2>/dev/null || service php-fpm status

# Check web server
systemctl status httpd 2>/dev/null || systemctl status apache2 2>/dev/null

# Check recent errors
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# Check disk space
df -h

# Check PHP syntax errors
php -l public/index.php

# Check .env exists and is readable
ls -la .env
```

### Resolution

1. **PHP-FPM crashed:**
   ```bash
   systemctl restart php-fpm
   ```

2. **Web server crashed:**
   ```bash
   systemctl restart httpd    # Apache
   systemctl restart apache2  # Ubuntu
   ```

3. **Bad deployment:**
   ```bash
   # Rollback to previous commit
   git checkout $(cat /home/administrator/backups/last_deploy_commit.txt)
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan optimize
   chown -R nobody:nobody storage/ bootstrap/cache/
   ```

4. **Disk full:**
   ```bash
   # Clear old logs
   find storage/logs -name "*.log" -mtime +7 -delete
   # Clear compiled views
   php artisan view:clear
   ```

5. **Permission issue:**
   ```bash
   chown -R nobody:nobody storage/ bootstrap/cache/
   chmod -R 775 storage/ bootstrap/cache/
   ```

---

## 2. Deposits Failing

### Symptoms
- Users report deposits not credited
- Pending deposits stuck
- Payment gateway errors in logs

### Diagnosis

```bash
# Check recent deposit records
php artisan tinker --execute="
\$deposits = \App\Models\Deposit::latest()->take(10)->get(['id','status','amount','created_at']);
echo \$deposits->toJson(JSON_PRETTY_PRINT);
"

# Check for gateway errors
grep -i 'deposit\|payment\|gateway' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Check if queue workers are processing
php artisan queue:failed
```

### Resolution

1. **Stuck pending deposits (manual approval):**
   - Go to `/admin/deposits` (Admin panel)
   - Review pending deposits
   - Approve legitimate deposits (idempotent — safe to click multiple times)

2. **Queue workers stopped:**
   ```bash
   php artisan queue:restart
   # Or start new worker
   nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &
   ```

3. **Gateway webhook failure:**
   - Check gateway dashboard for pending webhooks
   - Manually trigger webhook retry from gateway admin
   - If webhook URL changed, update in gateway settings

---

## 3. Withdrawals Failing

### Symptoms
- Withdrawal requests stuck in pending
- Users report not receiving funds
- Errors in admin withdrawal page

### Diagnosis

```bash
# Check recent withdrawals
php artisan tinker --execute="
\$withdraws = \App\Models\Withdraw::latest()->take(10)->get(['id','status','amount','created_at']);
echo \$withdraws->toJson(JSON_PRETTY_PRINT);
"

# Check if automatic processing is running
php artisan schedule:list | grep withdraws
```

### Resolution

1. **Scheduler stopped:**
   ```bash
   crontab -l  # Verify cron exists
   # If missing, re-add:
   (crontab -l 2>/dev/null; echo "* * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1") | crontab -
   ```

2. **Manual processing:**
   ```bash
   php artisan withdraws:update
   ```

3. **Gateway issue:**
   - Check payment gateway dashboard
   - Verify API keys are valid
   - Check gateway rate limits

---

## 4. Bets Not Settling

### Symptoms
- Completed/closed games but bets still pending
- Users report winnings not credited
- `settle:stakes` command errors

### Diagnosis

```bash
# Check settlement schedule
php artisan schedule:list | grep settle

# Run settlement manually
php artisan settle:stakes

# Check for errors
grep -i 'settle\|stake' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Check unsettled stakes on closed games
php artisan tinker --execute="
\$count = \App\Models\Stake::where('status', 'pending')
    ->whereHas('game', fn(\$q) => \$q->where('closed', true))
    ->count();
echo \"Unsettled stakes on closed games: \$count\";
"
```

### Resolution

1. **Scheduler stopped:** Re-add cron (see Section 3).

2. **Manual settlement:**
   ```bash
   php artisan settle:stakes
   ```

3. **Game not marked closed:**
   - Check game status in admin at `/admin/games`
   - If game should be closed but isn't, update via admin interface
   - Then run settlement: `php artisan settle:stakes`

4. **Settlement error:**
   - Check error logs for specific exception
   - Do NOT modify `SettleStakes.php` or `TradeManager.php` without developer review
   - If critical, enable maintenance mode and escalate

---

## 5. Queue Workers Stopped

### Symptoms
- Notifications not sending
- Background jobs not processing
- `php artisan queue:failed` shows growing list

### Diagnosis

```bash
ps aux | grep queue:work
php artisan queue:failed
```

### Resolution

```bash
# Restart existing workers
php artisan queue:restart

# If no workers exist, start new ones
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs (only if confirmed safe to discard)
php artisan queue:flush
```

---

## 6. Scheduler Stopped

### Symptoms
- `settle:stakes` not running
- `update:games` not running
- `withdraws:update` not running
- Crontab empty or missing

### Diagnosis

```bash
crontab -l
php artisan schedule:list
```

### Resolution

```bash
# Re-add the scheduler cron
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Verify
crontab -l
php artisan schedule:list

# Run missed commands manually
php artisan settle:stakes
php artisan update:games
php artisan withdraws:update
php artisan update:rates
```

---

## 7. API Keys Leaked

### Symptoms
- API key found in logs, frontend source, or public repository
- Unauthorized API usage detected

### Immediate Response

1. **Rotate the compromised key immediately** at the provider's dashboard:
   - TheOddsApi: https://the-odds-api.com/account/
   - Payment gateway: provider-specific dashboard

2. **Update `.env` on production:**
   ```bash
   nano /var/www/betriver/.env
   # Update the key value
   ```

3. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan optimize:clear
   php artisan optimize
   ```

4. **Check for unauthorized usage:**
   - Review API provider usage dashboard
   - Review application logs for suspicious activity
   - Check if any data was accessed

5. **Audit how the leak happened:**
   - Check git history for accidentally committed secrets
   - Check log files for logged secrets:
     ```bash
     grep -r 'theoddsapi\|api_key\|secret' storage/logs/
     ```
   - Check frontend source: view page source in browser, search for key values

---

## 8. Emergency: Disable Betting

If betting must be stopped immediately (e.g., odds error, settlement bug):

```bash
# Option A: Close all active games
php artisan tinker --execute="
\App\Models\Game::where('active', true)->update(['active' => false]);
echo 'All games deactivated. Betting is blocked.';
"

# Option B: Maintenance mode (blocks entire site)
php artisan down --secret="emergency-bypass-token"
# Access site via: https://69players.io/emergency-bypass-token
```

**To re-enable:**
```bash
# Option A: Reactivate games
php artisan tinker --execute="
\App\Models\Game::where('active', false)->update(['active' => true]);
echo 'Games reactivated.';
"

# Option B: Exit maintenance mode
php artisan up
```

---

## 9. Emergency: Maintenance Mode

```bash
# Enable with bypass secret
php artisan down --secret="emergency-bypass-token"

# Access the site while in maintenance mode:
# Visit: https://69players.io/emergency-bypass-token
# This sets a cookie that allows access

# Disable maintenance mode
php artisan up
```

---

## 10. Logs to Check

| Log | Location | Purpose |
|-----|----------|---------|
| Application log | `storage/logs/laravel-YYYY-MM-DD.log` | All application errors |
| PHP-FPM log | `/var/log/php-fpm/error.log` or cPanel error log | PHP crashes |
| Web server log | `/var/log/httpd/error_log` or cPanel error log | HTTP errors |
| Queue failed jobs | `php artisan queue:failed` | Failed background jobs |
| Cron log | `/var/log/cron` or `grep CRON /var/log/syslog` | Scheduler execution |

### Useful Log Commands

```bash
# Today's application errors
tail -100 storage/logs/laravel-$(date +%Y-%m-%d).log

# Search for specific errors
grep -i 'error\|exception\|fatal' storage/logs/laravel-$(date +%Y-%m-%d).log | tail -20

# Count errors today
grep -c 'ERROR\|CRITICAL' storage/logs/laravel-$(date +%Y-%m-%d).log

# Check for secret leaks in logs
grep -ri 'password\|secret\|api_key\|theoddsapi' storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## 11. Commands Quick Reference

| Situation | Command |
|-----------|---------|
| Check app status | `php artisan about` |
| View recent errors | `tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log` |
| Restart queue workers | `php artisan queue:restart` |
| View failed jobs | `php artisan queue:failed` |
| Retry failed jobs | `php artisan queue:retry all` |
| Clear all caches | `php artisan optimize:clear` |
| Rebuild caches | `php artisan optimize` |
| Enable maintenance | `php artisan down --secret="emergency-bypass-token"` |
| Disable maintenance | `php artisan up` |
| Run settlement | `php artisan settle:stakes` |
| Update games | `php artisan update:games` |
| Process withdrawals | `php artisan withdraws:update` |
| Check disk space | `df -h` |
| Check PHP-FPM | `systemctl status php-fpm` |
| Check crontab | `crontab -l` |
