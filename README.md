# B1 English Self-Study

Private, single-owner VSTEP Level 3 / B1 study application built with Laravel 13, PHP 8.3+, Blade, Bootstrap, and MariaDB/MySQL.

Sprint 6 extends the Sprint 0–5 foundation with Writing practice: owner-managed Task 1/2 prompts, durable draft/submission history, server word counts, prompt snapshots, and self-check guidance. There is still no registration, `users` table, role system, or Speaking upload workflow.

## Local setup

Requirements:

- PHP 8.3+ with `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, and `zip`
- Composer 2
- MariaDB/MySQL
- Node.js and pnpm

Create an empty MariaDB/MySQL database and a least-privilege application user, then run:

```bash
cp .env.example .env
composer install
php artisan key:generate
pnpm install --frozen-lockfile
pnpm run build
```

Set the `DB_*` values in `.env`. Generate an owner hash without storing the plain password in source control:

```bash
php -r "echo password_hash('replace-with-a-long-passphrase', PASSWORD_ARGON2ID), PHP_EOL;"
```

Put the output in `OWNER_PASSWORD_HASH`, set `OWNER_LOGIN`, then initialize and run the application:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan serve
```

The Sprint 1 seed is deliberately small and original: three topics, three vocabulary entries, and one grammar lesson. Sprint 2 adds the small Reading/Listening/question pilot; Sprint 3 adds one original active Reading exercise mapped to the existing Reading question. Sprint 4 adds application-only history/review; Sprint 5 adds the mock-exam flow; Sprint 6 adds Writing practice and its forward-only submission schema.

## Checks

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
composer validate --strict --no-check-publish
composer audit
pnpm run build
pnpm audit --prod
php artisan route:list --except-vendor
```

## Production configuration

Production secrets belong only in the host's untracked `.env`. At minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-direct-subdomain.example
APP_FORCE_HTTPS=true
APP_DISPLAY_TIMEZONE=Asia/Ho_Chi_Minh
LOG_CHANNEL=daily
LOG_LEVEL=warning
DB_CONNECTION=mysql
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
QUEUE_FAILED_DRIVER=null
OWNER_LOGIN=owner
OWNER_PASSWORD_HASH=replace-with-a-generated-hash
```

Keep Laravel timestamps in UTC; `APP_DISPLAY_TIMEZONE` records the approved display timezone for later UI work. Mail, Redis, paid APIs, object storage, and speaking uploads are intentionally absent.

## First InfinityFree deployment proof

InfinityFree Free has a fixed `htdocs` web root and no server-side SSH, Composer, Node/npm, or Artisan. The owner must create/sign in to the free account, complete any verification/CAPTCHA, select a direct free subdomain, create one MySQL database/user, and use FTP/FTPS or File Manager. Do not add a payment method.

1. Run `scripts/build-infinityfree-release.ps1` locally. It installs production-only Composer dependencies and builds `public/build`; it never includes `.env`, `node_modules`, local SQLite, logs, caches, tests, source-control metadata, or development packages.
2. Upload the release contents into the site's `htdocs` directory. The outer `deploy/infinityfree/htdocs.htaccess` file protects Laravel internals and rewrites requests into `public`; keep Laravel's standard `public/.htaccess`.
3. Create the production `.env` manually in `htdocs` with the direct HTTPS subdomain and panel-provided MySQL values. Use file sessions/cache and secure cookies; never put secrets in Git or logs.
4. For a new database only, import `database/infinityfree/sprint-0-schema.sql`. For an existing installation, review each forward-only SQL batch against the live schema before importing it once: Sprint 2 uses `database/infinityfree/sprint-2-update.sql`; Sprint 3 uses `database/infinityfree/sprint-3-update.sql`; Sprint 4 is comment-only; Sprint 6 uses `database/infinityfree/sprint-6-update.sql` and adds only `writing_submissions` plus original pilot prompts.
5. Verify the panel's PHP 8.3 and `pdo_mysql`, HTTPS, redirect/security headers, owner login, dashboard, Topics Manage flow, Vocabulary learner/Manage/progress flow, Grammar learner/Manage flow, Reading/Listening, Practice start/save/submit/result, History, wrong-answer Review, mock exams, Writing learner/Manage flow, logout, `GET /health`, and denial of `.env`/source/vendor/storage paths.
6. Upload each reviewed release over application files while preserving the existing `.env`, `storage`, and database. Never upload the local `.env` or replace runtime storage.

The full workflow, SQL import method, future forward-only schema strategy, persistence behavior, and rollback procedure are in [docs/15-deployment-plan.md](docs/15-deployment-plan.md). Render + Neon remains an inactive fallback requiring separate approval.

## Rollback

Keep the previous release until smoke checks pass. If a release fails, restore the prior uploaded files and untracked environment file, then restore the reviewed SQL export only when data recovery is needed. Never run `migrate:fresh`, delete/recreate the database, or replace `htdocs/storage` during deployment.
