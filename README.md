# ElectroHub Shop

ElectroHub waa e-commerce app ku dhisan Laravel, leh:

- Shop frontend (products, cart, checkout)
- Payments: Stripe iyo COD
- User auth/profile
- Admin dashboard (products, orders, users, reports)
- Email invoices, notifications, audit logs
- Product variants, delivery tracking, inventory history, payment events
- SEO metadata, role permissions, security checklist

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Default local users:

- Admin: `admin@example.com` / `password`
- Customer: `test@example.com` / `password`

## Payment Configuration

Ku buuxi `.env`:

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_WEBHOOK_LIVE_MODE=false
```

Webhook endpoints:

- Stripe: `/payments/stripe/webhook`

Marka production la galayo:

- `STRIPE_WEBHOOK_LIVE_MODE=true` haddii aad isticmaaleyso live Stripe endpoint.
- Hubi in webhook secret sax yahay.

## Mail + Queue (Production)

Ku beddel mailer-ka `log` una beddel SMTP provider:

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="ElectroHub"
```

Queue worker (background jobs):

```bash
php artisan queue:work --queue=default --tries=3 --timeout=120
```

Haddii job fashilmo:

```bash
php artisan queue:failed
php artisan queue:retry all
```

Windows (NSSM/Task Scheduler) ama Linux (Supervisor/systemd) ku socodsii worker-ka si joogto ah.

## Backup + Restore (SQLite)

Env vars:

```env
BACKUP_DISK=local
BACKUP_PATH=backups/database
BACKUP_KEEP_DAYS=14
```

Manual backup:

```bash
php artisan app:backup-db
```

Restore backup:

```bash
php artisan app:restore-db backups/database/sqlite_YYYYMMDD_HHMMSS.sqlite
```

Scheduled backup:

- `routes/console.php` wuxuu jadwaleeyaa backup maalin kasta `02:00`.
- Production scheduler command:

```bash
php artisan schedule:work
```

Ama cron (Linux):

```bash
* * * * * cd /path/to/shop && php artisan schedule:run >> /dev/null 2>&1
```

## Production Hardening

- Isticmaal `.env.production.example` sida template production.
- `APP_ENV=production` iyo `APP_DEBUG=false`.
- HTTPS kaliya: app-ku production wuxuu ku qasbaa `https` scheme.
- Orod `php artisan storage:link` si product uploads uga soo muuqdaan `public/storage`.
- U samee admin user gaar ah production, kadib beddel ama tirtir password-ka seed-ka default ah.
- Rate limiting waa la saaray:
	- Checkout POST: `throttle:checkout`
	- Stripe webhook: `throttle:stripe-webhook`

Deploy kadib orod:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Roles + Permissions

- `super_admin`: full admin access, user roles, products, orders, reports.
- `sales_admin`: orders, returns, reports, customer/order operations.
- `inventory_admin`: products, stock, product images.
- `customer`: storefront, cart, checkout, own orders, wishlist, profile.

Role changes are managed from Admin > Users.

## Pro Commerce Modules

- Product variants: Admin > Products, fill variants as `name | sku | price delta | stock`.
- Shipping: checkout supports metro, regional, and store pickup delivery options.
- Tracking: admins can add courier, tracking number, and estimated delivery date from an order page.
- Inventory history: Admin > Inventory History shows stock changes from product edits, admin orders, and checkout.
- Payment logs: order pages show COD/Stripe payment activity and failure notes.
- Reports: Admin > Reports includes revenue stats, top products, low stock, CSV, and PDF exports.
- Security: Admin > Security shows environment checks and hardening reminders.
- SEO: product edit pages support meta title and meta description.

## Deployment Checklist

1. Upload code and run `composer install --no-dev --optimize-autoloader`.
2. Run `npm ci` and `npm run build`, or upload the generated `public/build` assets.
3. Copy `.env.production.example` to `.env` and fill `APP_KEY`, `APP_URL`, database, mail, Stripe, and backup values.
4. Run `php artisan migrate --force`.
5. Run `php artisan storage:link`.
6. Run `php artisan optimize:clear`, then cache config, routes, and views.
7. Start queue worker: `php artisan queue:work --queue=default --tries=3 --timeout=120`.
8. Start scheduler: `php artisan schedule:run` every minute via cron, or `php artisan schedule:work`.
9. Confirm `/`, `/login`, `/admin/dashboard`, checkout, Stripe webhook, email, and backups.

## Quality Checks

```bash
php artisan test
npm run build
php artisan route:list
```
