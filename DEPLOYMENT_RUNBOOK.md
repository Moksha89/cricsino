# Deployment Runbook

Step-by-step deployment process for https://69players.io

**Server:** 165.73.253.210 (cPanel / PHP-FPM 8.2)
**Web Root:** `/var/www/betriver`
**PHP Binary:** `/opt/cpanel/ea-php82/root/usr/bin/php`
**PHP-FPM User:** `nobody`

---

## Pre-Deployment

1. **Notify the team** that a deployment is starting.
2. **Back up the database** before any deployment:
   ```bash
   mysqldump -u betriver -p betriver > /home/administrator/backups/betriver_$(date +%Y%m%d_%H%M%S).sql
   ```
3. **Record the current commit** for rollback:
   ```bash
   cd /var/www/betriver
   git rev-parse HEAD > /home/administrator/backups/last_deploy_commit.txt
   ```

## Deployment Steps

### Step 1: Pull Latest Code

```bash
cd /var/www/betriver
git pull origin main
```

If deploying a specific branch or tag:
```bash
git fetch origin
git checkout <branch-or-tag>
```

### Step 2: Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Step 3: Run Database Migrations

```bash
php artisan migrate --force
```

Review pending migrations first if unsure:
```bash
php artisan migrate:status
```

### Step 4: Build Frontend Assets

```bash
npm ci
npm run build
```

### Step 5: Optimize Laravel

```bash
php artisan optimize:clear
php artisan optimize
```

This caches config, events, routes, and views.

### Step 6: Restart Queue Workers

```bash
php artisan queue:restart
```

Queue workers will gracefully restart after finishing their current job.

### Step 7: Fix File Permissions

```bash
chown -R nobody:nobody storage/ bootstrap/cache/
```

This ensures PHP-FPM (`nobody` user) can write to logs, cache, and sessions.

### Step 8: Verify Cron

```bash
crontab -l
```

Expected output:
```
* * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

If missing, add it:
```bash
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

## Post-Deployment Verification

### Quick Commands

```bash
php artisan about               # App environment summary
php artisan route:list | wc -l  # Route count
php artisan schedule:list       # Scheduled commands
php artisan queue:failed        # Failed jobs
```

### Smoke Test

```bash
for url in / /login /sports /sports/cricket /sport/in-play /casino /casino/crash; do
  status=$(curl -s -o /dev/null -w '%{http_code}' "https://69players.io${url}")
  echo "$status $url"
done
```

All public routes should return `200`.

### Verify Session Security

```bash
curl -sI https://69players.io/login | grep -i 'set-cookie'
```

Cookies must include `secure` and `httponly` flags.

### Verify Logs

```bash
ls -la storage/logs/laravel-$(date +%Y-%m-%d).log
tail -20 storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Verify Queue Workers

```bash
ps aux | grep queue:work
```

If no workers are running:
```bash
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &
```

---

## Rollback Process

If the deployment causes issues:

### Step 1: Revert Code

```bash
cd /var/www/betriver
PREV_COMMIT=$(cat /home/administrator/backups/last_deploy_commit.txt)
git checkout $PREV_COMMIT
```

### Step 2: Reinstall Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### Step 3: Rollback Migrations (if needed)

```bash
php artisan migrate:rollback --step=1
```

Only roll back migrations that were added in the failed deployment.

### Step 4: Re-optimize and Restart

```bash
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
chown -R nobody:nobody storage/ bootstrap/cache/
```

### Step 5: Restore Database (if needed)

```bash
mysql -u betriver -p betriver < /home/administrator/backups/betriver_YYYYMMDD_HHMMSS.sql
```

### Step 6: Verify Rollback

Run the same smoke test from post-deployment verification.

---

## Full Deploy Script (Copy-Paste)

```bash
cd /var/www/betriver
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
chown -R nobody:nobody storage/ bootstrap/cache/
php artisan schedule:list
php artisan queue:failed
echo "Deployment complete: $(date)"
```

---

## Environment-Specific Notes

| Setting | Value |
|---------|-------|
| PHP Version | 8.2.31 |
| PHP Binary | `/opt/cpanel/ea-php82/root/usr/bin/php` |
| Web Root | `/var/www/betriver` |
| PHP-FPM User | `nobody` |
| Database | MySQL `betriver` @ 127.0.0.1:3306 |
| Queue Driver | `database` |
| Log Channel | `daily` (14-day retention) |
| Session Cookie | `secure`, `httponly`, `samesite=lax` |
