# Launch Checklist

Pre-launch verification for https://69players.io

---

## 1. Environment Configuration

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://69players.io`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `LOG_STACK=daily`
- [ ] `LOG_DAYS=14`
- [ ] `QUEUE_CONNECTION=database` (or redis)
- [ ] `CACHE_DRIVER` set appropriately
- [ ] `SESSION_DRIVER` set appropriately
- [ ] No `.env` committed to git
- [ ] Debugbar disabled (`APP_DEBUG=false`)

## 2. SSL / Session

- [ ] HTTPS active on all pages
- [ ] HSTS header present (`Strict-Transport-Security`)
- [ ] Session cookie has `Secure` flag
- [ ] Session cookie has `HttpOnly` flag
- [ ] Session cookie has `SameSite=Lax`
- [ ] CSRF protection active (419 on invalid token)

Verify with:
```bash
curl -sI https://69players.io/login | grep -iE 'set-cookie|strict-transport'
```

## 3. Scheduler

- [ ] Crontab configured:
  ```
  * * * * * cd /var/www/betriver && /opt/cpanel/ea-php82/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] `php artisan schedule:list` shows:
  - `settle:stakes` every 5 minutes
  - `update:games` every 5 minutes
  - `withdraws:update` every 15 minutes
  - `update:rates` daily at midnight
- [ ] `odds:import` is NOT scheduled
- [ ] `scores:refresh` is NOT scheduled

## 4. Queue Workers

- [ ] Queue workers are running: `ps aux | grep queue:work`
- [ ] `php artisan queue:failed` shows no failed jobs
- [ ] `jobs` table exists in database
- [ ] `failed_jobs` table exists in database
- [ ] Notification dispatch works (test with a deposit or support ticket)

## 5. Smoke Test Routes

### Public (expect 200)
- [ ] `/`
- [ ] `/login`
- [ ] `/register`
- [ ] `/sports`
- [ ] `/sports/cricket`
- [ ] `/sport/in-play`
- [ ] `/casino`
- [ ] `/casino/crash`

### Authenticated User (expect 200 when logged in, 302 when not)
- [ ] `/deposits/create`
- [ ] `/withdraws/create`
- [ ] `/account/statement`
- [ ] `/account/promotions`
- [ ] `/support`
- [ ] `/notifications`

### Admin (expect 200 when admin, 302/403 otherwise)
- [ ] `/admin`
- [ ] `/admin/agents`
- [ ] `/admin/casino-games`
- [ ] `/admin/odds-import`
- [ ] `/admin/live-scores`
- [ ] `/admin/promotions`
- [ ] `/admin/support`
- [ ] `/admin/notifications`

Quick smoke test command:
```bash
for url in / /login /sports /sports/cricket /sport/in-play /casino /casino/crash; do
  status=$(curl -s -o /dev/null -w '%{http_code}' "https://69players.io${url}")
  echo "$status $url"
done
```

## 6. Payment Test Checklist

- [ ] Deposit page loads (`/deposits/create`)
- [ ] Available payment gateways display correctly
- [ ] Deposit submission creates a pending deposit record
- [ ] Admin can view pending deposits at `/admin/deposits`
- [ ] Admin deposit approval credits balance exactly once (idempotent)
- [ ] Double-click on approve does not double-credit
- [ ] Failed/rejected deposits cannot be re-approved
- [ ] Withdrawal page loads (`/withdraws/create`)
- [ ] Withdrawal request creates pending record
- [ ] Admin can approve/reject withdrawals
- [ ] Transaction audit trail is created for all wallet changes

## 7. Betting Test Checklist

- [ ] Sports page shows available games
- [ ] Bet slip opens when selecting an odds value
- [ ] Bet placement works on open/active games
- [ ] Bet placement is BLOCKED on closed games ("Betting is closed for this match.")
- [ ] Bet placement is BLOCKED on inactive games
- [ ] Bet placement validates sufficient balance
- [ ] Bet delay works for live/in-play games
- [ ] Exchange mode shows Back/Lay options
- [ ] Bookie mode shows bookmaker odds
- [ ] `settle:stakes` runs on schedule (every 5 minutes)
- [ ] Settlement only processes closed games
- [ ] No settlement logic was modified in hardening

## 8. Admin Test Checklist

- [ ] Admin dashboard loads with stats
- [ ] User management works (view, credit, debit)
- [ ] Admin credit creates transaction audit record
- [ ] Admin debit blocks overdraw ("Insufficient balance for debit")
- [ ] Agent management pages load
- [ ] Casino games management loads
- [ ] Odds import page loads (key masked)
- [ ] Live scores page loads
- [ ] Support tickets visible
- [ ] Promotions management works
- [ ] Notification management works

## 9. Rollback Readiness

- [ ] Previous release tag/commit hash documented
- [ ] Rollback command prepared:
  ```bash
  cd /var/www/betriver
  git stash
  git checkout <previous-commit>
  composer install --no-dev --optimize-autoloader
  php artisan migrate --force
  php artisan optimize
  php artisan queue:restart
  chown -R nobody:nobody storage/ bootstrap/cache/
  ```
- [ ] Database backup exists before deployment
- [ ] Team notified of launch window

---

## Known Remaining Risks

| Risk | Severity | Status |
|------|----------|--------|
| Debugbar installed (disabled via APP_DEBUG=false) | Low | Monitor |
| No automated DB backup | Medium | Configure post-launch |
| No Sentry/Bugsnag error monitoring | Medium | Configure post-launch |
| No uptime monitoring | Medium | Configure post-launch |
| Casino bet placement lacks DB transaction/lock | Medium | Fix in next hardening pass |
| PWA icon-192x192.png returns 404 | Low | Fix post-launch |
