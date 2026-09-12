# Deployment Research and Plan

## Decision status

**Primary Sprint 0 target: InfinityFree Free Hosting.** The target changed on 30 August 2026 after alwaysdata's registration flow required card verification and rejected the owner's card. alwaysdata is not an active deployment target for this project.

The application remains a private, single-owner Laravel 13 / Blade / Bootstrap monolith with MySQL/MariaDB. No payment method, paid resource, email service, queue worker, Redis, scheduler, object storage, Docker service, or external analytics is required.

Render Free + Neon Free PostgreSQL remains a fallback only if a measured InfinityFree constraint blocks the application and the owner separately approves the database-engine change. Render's own free-tier documentation warns that Free instances are not for production applications.

Provider facts were rechecked from official InfinityFree sources on 30 August 2026. Free offers and fair-use rules remain operational dependencies and must be rechecked before each production change.

## Runtime compatibility

- Laravel framework: `v13.29.0` from `composer.lock` (Laravel 13).
- Project PHP requirement: `^8.3`. It was changed from `^8.4` only because InfinityFree Free currently runs PHP 8.3; Laravel 13 itself requires `^8.3`, so Laravel was not downgraded.
- The lockfile now resolves Symfony 7.4 packages that support PHP 8.3 instead of the previously locked Symfony 8.1 packages that required PHP 8.4.
- Local PHP 8.3.30 + Composer 2.9.4 passed `composer check-platform-reqs --no-dev` for the production dependency set.
- Production still needs the host's actual required extensions verified from the InfinityFree control panel or a temporary, immediately removed diagnostic file. The application requires the usual Laravel extensions including `ctype`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, and `xml`.

## Current official InfinityFree facts

| Area | Free-hosting fact and deployment consequence |
|---|---|
| Cost and duration | InfinityFree advertises free hosting with no credit card and no expiry. Use only the free hosting account; never upgrade or add billing details. |
| Capacity | The official site advertises 5 GB disk and unlimited bandwidth, while the Terms reserve fair-use limits and allow suspension for excessive resource use. Keep a conservative disk, inode, request, and database budget. |
| PHP | Free hosting is currently PHP 8.3. The project therefore targets PHP 8.3; no PHP 8.4-only dependency may be added without a new provider review. |
| Database | The official site advertises MySQL 8.0 / MariaDB 11.4 and many databases. Create only one MySQL database and one least-privilege application user. Free databases accept connections only from hosted PHP or InfinityFree phpMyAdmin, not an external desktop client. |
| Web root | `htdocs` is the fixed web root and cannot be changed. Files outside the site's `htdocs` are not a usable private application directory on Free Hosting. Keep Laravel inside `htdocs`, deny direct access to internals with the checked-in outer `.htaccess`, and rewrite requests into `public`. |
| Server tools | Free Hosting has no SSH/SFTP, Composer, Node.js, npm, or server-side Artisan. Install Composer dependencies and build Vite assets locally; upload `vendor` and `public/build`; never upload `node_modules`. |
| Upload | Use the provider's FTP/FTPS or File Manager. The provider's FTP endpoint and credentials are shown in the signed-in panel; do not put them in source control or command logs. |
| SSL | Direct free subdomains receive system-wide SSL automatically. HTTPS is not forced by the certificate, `www.` free-subdomains are not covered, and free-subdomain custom certificates are not supported. Use the direct non-`www` hostname and force HTTPS in Laravel plus `.htaccess`. |
| Runtime limits | No cron, long-running worker, WebSocket server, or server shell is available. Keep `QUEUE_CONNECTION=sync`, `CACHE_STORE=file`, `SESSION_DRIVER=file`, and do not add scheduled work. |
| Backups | InfinityFree Terms place backup responsibility on the owner and do not guarantee provider backups. Keep the repository, exported SQL, and original licensed assets off-host. |
| Content policy | This is a small personal website, not a file-hosting or backup service. Fixed audio may be streamed as reviewed web content; do not use the account for arbitrary file sharing or downloadable archives. |

Official sources:

- [InfinityFree Free Website Hosting](https://www.infinityfree.com/)
- [InfinityFree Terms of Service](https://www.infinityfree.com/terms/)
- [Free hosting is now upgraded to PHP 8.3](https://forum.infinityfree.com/t/free-hosting-is-now-upgraded-to-php-8-3/109714)
- [How to install a Laravel site on InfinityFree](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578)
- [Can I SSH into the host and run commands like npm install?](https://forum.infinityfree.com/t/can-i-ssh-into-the-host-and-run-commands-like-npm-install/59811)
- [SSH is not available on free hosting](https://forum.infinityfree.com/t/ssh/51141)
- [Connecting to MySQL from an external application](https://forum.infinityfree.com/t/connecting-to-mysql-from-an-external-application/49339)
- [Changes to SSL for free subdomains](https://forum.infinityfree.com/t/changes-to-ssl-for-free-subdomains/110169)
- [Placing .env files outside the htdocs folder](https://forum.infinityfree.com/t/placing-env-files-ouside-the-htdocs-folder/91524)

## Production layout for fixed `htdocs`

The upload root is the site's `htdocs` directory:

```text
htdocs/
├── .htaccess                 # deploy/infinityfree/htdocs.htaccess
├── .env                      # created manually on the host; never from Git
├── app/
├── artisan                   # not executed on the host
├── bootstrap/
├── composer.json             # harmless metadata; denied by .htaccess
├── composer.lock             # harmless metadata; denied by .htaccess
├── config/
├── database/
├── public/
│   ├── .htaccess             # Laravel's standard public rules
│   ├── build/
│   └── index.php
├── resources/
├── routes/
├── storage/                  # writable runtime directories; not a public disk
└── vendor/                   # production-only Composer dependencies
```

InfinityFree does not provide a private sibling directory that PHP can use, so the application must be inside `htdocs`. The outer rewrite file denies direct requests for `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`, `vendor`, `.env`, `artisan`, dependency manifests, and build metadata. Other requests are internally routed to `public`; the normal Laravel `public/.htaccess` then routes them to `public/index.php`. Sprint 1 does not use `public/storage` or `storage:link`, so runtime storage remains denied.

The release builder at `scripts/build-infinityfree-release.ps1` copies only the needed application directories, installs production-only dependencies, builds Vite assets, removes local SQLite/cache/session/view/log state plus package test/source-control metadata, and fails if `.env`, `node_modules`, local database/log files, repository metadata, package tests, or development Composer packages enter the release.

## Production environment

Create `.env` manually in the uploaded `htdocs` directory. Do not upload the local `.env`, paste secrets into logs, or commit a production environment file. Set:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<direct-free-subdomain>
APP_FORCE_HTTPS=true
APP_DISPLAY_TIMEZONE=Asia/Ho_Chi_Minh
LOG_CHANNEL=daily
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=<InfinityFree database hostname, not localhost>
DB_PORT=3306
DB_DATABASE=<panel database name>
DB_USERNAME=<panel database user>
DB_PASSWORD=<panel database password>
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
OWNER_LOGIN=owner
OWNER_PASSWORD_HASH=<Argon2id hash generated locally or in the owner's password manager>
```

Generate an `APP_KEY` and owner password hash locally with the PHP 8.3 runtime, or generate them in a trusted owner-controlled environment. The plain owner password and database password must never be sent to this chat, source control, or a log. Because the provider has no Artisan, do not plan to run `key:generate`, `migrate`, `optimize`, `storage:link`, or any other command on the host.

## MySQL and Sprint 0 schema

Create one MySQL database and one application user in the InfinityFree panel. Use the exact database hostname, database name, username, and generated password shown by the panel; `localhost` is not assumed. Import [database/infinityfree/sprint-0-schema.sql](../database/infinityfree/sprint-0-schema.sql) once through InfinityFree phpMyAdmin after selecting the new database. The SQL creates only Laravel's empty `migrations` repository; Sprint 0 has no domain tables, users table, study content, or seed data.

Before the first import and before every later schema batch:

1. Export the current database from phpMyAdmin and keep it off-host.
2. Review the SQL and confirm it contains no secrets or Sprint 1 tables.
3. Import inside phpMyAdmin and verify the `migrations` table exists.
4. Keep application and database changes forward-only. For future Laravel migrations, generate and test SQL locally against MySQL, import the reviewed SQL in phpMyAdmin, and record the migration rows; never use `migrate:fresh`, `DROP DATABASE`, or a release upload that deletes `htdocs/storage` or the database.

This preserves study history when Sprint 1 is later approved: application releases are replaceable files, while the MySQL database is independently exported and updated with additive, reviewed SQL.

## First InfinityFree deployment proof

1. The owner creates/signs into the free InfinityFree account, completes any email verification/CAPTCHA, creates one hosting account, selects one direct free subdomain, and confirms the free plan without payment details.
2. Create one MySQL database/user, note the provider FTP/File Manager details and database hostname, and open phpMyAdmin.
3. Locally run the release builder. Upload its contents to the site's `htdocs`; do not upload `.env`, `node_modules`, tests, local SQLite, caches, sessions, views, logs, or development dependencies.
4. Create the production `.env` manually in `htdocs`; set the direct HTTPS subdomain and database credentials from the panel; keep `.env` denied by the outer `.htaccess`.
5. Import the Sprint 0 SQL through phpMyAdmin. Do not run Artisan on the host. Confirm the `migrations` table is present and empty.
6. Verify the panel's PHP version/extensions and the presence of `pdo_mysql`; record the observed values without exposing credentials.
7. Open the direct HTTPS subdomain. Verify the HTTP-to-HTTPS redirect, certificate, security headers, and that `/health` returns only `{"status":"ok"}` while using the real MySQL connection.
8. Verify the owner login, protected dashboard, logout, and fail-closed access after logout. Verify `/.env`, `/app/...`, `/vendor/...`, `/storage/...`, `/composer.json`, and `/database/...` do not disclose content.
9. Create a harmless marker under `storage/app/private` in the File Manager, record its presence, upload a second application release without deleting `htdocs/storage` or the database, and verify that the marker and `migrations` table remain. Remove the marker after the proof.
10. Record upload duration, disk/inode/quota display, PHP version, database engine, SSL result, observed fair-use warnings, and the manual rollback (restore prior files, environment, and SQL export).

## Hosted proof record (2026-08-31)

- The active free account is `if0_42788092`, serving `https://hoctienganh.site.je/`; no paid resource or payment method was used.
- One production database, `if0_42788092_b1`, was created on `sql213.infinityfree.com`. Credentials and the application key exist only in the manually created production `.env` and are not recorded here.
- The uploaded application ran on PHP 8.3.19 with `pdo_mysql`, `mbstring`, and `openssl` available. `/health` returned HTTP 200 with exactly `{"status":"ok"}` after a real database query.
- phpMyAdmin showed exactly one production table, `migrations`, with no Sprint 1 tables or content. It remained present after login, dashboard reload, logout, and repeated hosted requests.
- Guest access redirected to the owner login, owner authentication opened the protected dashboard, reload preserved the authenticated file session, logout returned to login, and protected access failed closed afterward.
- The direct subdomain served HTTPS, HTTP redirected to HTTPS, and HSTS, CSP, clickjacking, MIME-sniffing, referrer, permissions, and no-store/private cache controls were observed.
- Direct requests for `.env`, application source/configuration, logs, Composer metadata, Artisan, bootstrap cache, and vendor files did not disclose content. Production errors and missing routes used generic 500/404 responses without debug details.
- InfinityFree's ZIP extraction omitted empty `storage/framework/views` and `storage/framework/sessions` directories, initially causing a generic Laravel 500. Only those two required runtime directories were created on the host. The release builder now emits safe placeholder files so future archives preserve every required writable directory; the deployed application was not rebuilt or re-uploaded for this documentation-only correction.
- The temporary non-secret runtime diagnostic `public/sprint0-runtime-check.php` was deleted after verification. Its exact public URL now returns the generic 404 page.
- A second release upload with a persistent-file marker was not performed: the final verification instruction prohibited rebuilding or re-uploading unless verification showed it was necessary. Persistence was instead verified across normal hosted requests/reloads, including the database table, authenticated file session, and runtime directories.

## Storage and audio behavior

InfinityFree files in `htdocs` persist across an ordinary overwrite/upload, but there is no provider backup guarantee and an account can be suspended or content removed under fair-use/Terms rules. The release workflow therefore never deletes `storage` and the owner keeps off-host SQL/application exports.

Sprint 0 has no audio files. A later reviewed Listening pilot may ship small licensed/static audio in the public release or a validated persistent path and must measure disk, inodes, and streaming transfer. Speaking recordings remain in browser memory with optional owner-local download; the MVP must not upload them to InfinityFree. Do not create a public upload endpoint or use the host as file storage.

## Routine release and rollback

1. Run local tests and the release builder with a clean diff.
2. Export the production database from phpMyAdmin and retain the prior release directory/files.
3. Upload the new release over the application files, preserving `.env`, `storage`, and the database.
4. Apply only reviewed forward SQL in phpMyAdmin when a future migration exists.
5. Smoke-test HTTPS, login, dashboard, logout, `/health`, security denials, and one representative persistent file.
6. If checks fail, restore the prior uploaded files and `.env`; restore the SQL export only when a tested data recovery is required. Never delete or recreate the production database to roll back an application release.

## Sprint 1 production update

Sprint 1 uses [database/infinityfree/sprint-1-update.sql](../database/infinityfree/sprint-1-update.sql) as one reviewed, forward-only phpMyAdmin batch. It mirrors the four Laravel migrations for `topics`, `vocabularies`, `vocabulary_progress`, and `grammar_lessons`, records those migration names, and imports the same deliberately small original pilot as `SprintOneContentSeeder`. It contains no destructive schema or data statement and no secret.

For the existing hosted database:

1. Run the complete local test and release gates, then build the InfinityFree release locally.
2. Export the current production database from phpMyAdmin and retain the prior hosted application files.
3. Review the Sprint 1 SQL, select the existing production database in phpMyAdmin, and import it once. Do not run `migrate:fresh`, recreate the database, or re-import the Sprint 0 bootstrap.
4. Verify four Sprint 1 migration rows, four new domain tables, three pilot topics, three vocabulary entries, zero initial progress rows, and one grammar lesson.
5. Upload the Sprint 1 release over application files while preserving the host-created `.env`, `storage`, and database.
6. Smoke-test login, dashboard, Topics Manage actions, Vocabulary list/detail/progress persistence/Manage actions, Grammar list/detail/Manage actions, `/health`, logout, HTTPS/security headers, and source-path denials.
7. If any check fails, stop writes, restore the prior application files, diagnose against logs without exposing secrets, and use the pre-import SQL export only when a database restore is genuinely required.

## Sprint 2 production update

Sprint 2 was previously applied to the existing Sprint 0/Sprint 1 database as one reviewed forward-only batch. Its four content/question tables and small original Reading/Listening pilot were present before Sprint 3. The Sprint 0–2 production baseline immediately before Sprint 3 was 9 tables and 8 unique migration records, with vocabulary progress preserved.

## Sprint 3 production update

Sprint 3 was deployed on 6 September 2026 to `https://hoctienganh.site.je/` and database `if0_42788092_b1` on `sql213.infinityfree.com`.

- The reviewed [Sprint 3 SQL](../database/infinityfree/sprint-3-update.sql) was imported exactly once after a production export. It added the nine tables `exercises`, `exercise_questions`, `writing_prompts`, `speaking_prompts`, `exams`, `exam_sections`, `exam_section_items`, `attempts`, and `attempt_answers`, plus nine batch-3 migration rows. The post-import database has exactly 18 tables and 17 unique migration rows.
- The nine migration names are `2026_09_06_000900_create_exercises_table`, `2026_09_06_001000_create_exercise_questions_table`, `2026_09_06_001100_create_writing_prompts_table`, `2026_09_06_001200_create_speaking_prompts_table`, `2026_09_06_001300_create_exams_table`, `2026_09_06_001400_create_exam_sections_table`, `2026_09_06_001500_create_exam_section_items_table`, `2026_09_06_001600_create_attempts_table`, and `2026_09_06_001700_create_attempt_answers_table`.
- The only pilot content added was one active Reading exercise, `Weekly study details practice`, referencing existing `topics.id=4` (`reading-daily-plans`) and existing `questions.id=1` (`When does Mai read a short article?`) through one `exercise_questions` row. Writing, Speaking, and Exam tables remain empty.
- The SQL contains no `ALTER TABLE`, `DROP`, `TRUNCATE`, destructive `DELETE`, database recreation, or `migrate:fresh` equivalent. Existing Sprint 0–2 data remained unchanged: 5 topics, 3 vocabularies, 1 vocabulary-progress row, 1 grammar lesson, 1 passage, 1 listening record, 2 questions, and 5 question options. Three append-only attempts/answers rows are smoke-test history; one completed pilot attempt scored `1.00/1.00`.
- The uploaded release is `release-20260906-225150.zip`, SHA-256 `5A97E82CC2F43C2909DE238ADF1E0259176C3B844953E72DA9BB149572DD3606`. The archive had no `.env` and no runtime data; File Manager extraction preserved the existing production `.env` and `storage`.
- Local and hosted Sprint 0–3 checks passed as recorded in [Document 11](11-testing-plan.md). InfinityFree edge blocking of direct `/health` and protected source-path requests was documented; the same-origin health response passed without disclosure. No Sprint 4 work was started.

## Sprint 4 production update

Sprint 4 is an application-only release. It adds result breakdowns, snapshot-stable History, wrong-answer Review, and dashboard practice summaries while reusing the existing Sprint 3 `attempts` and `attempt_answers` tables. The reviewed [Sprint 4 SQL](../database/infinityfree/sprint-4-update.sql) contains comments only: there are no executable schema/data statements, so no production SQL import or migration-row change is required. Production remains at exactly 18 tables and 17 unique migration rows, and all Sprint 0–2 content/progress remains unchanged.

The Sprint 4 release archive is uploaded only after a fresh read-only production audit confirms the Sprint 3 baseline, and it preserves the existing untracked `.env` and runtime `storage`. Hosted verification covers History/Review/result breakdowns, owner protection, snapshot behavior, responsive layout, and Sprint 0–3 regression paths. Sprint 5 remains unopened.

## Sprint 5 production update

Sprint 5 reused the Sprint 3 exam dependency tables and added the mock-exam catalog, section/take flow, timer, submission, raw result, and history integration. No Sprint 5 migration or SQL import was required. The active pilot references the existing Sprint 2 Reading question through the existing Sprint 3 exercise/question mapping. Production remained at 18 tables and 17 migrations, and the existing `.env`, `storage`, content, and vocabulary progress were preserved.

## Sprint 6 production update

Sprint 6 was deployed on 8 September 2026 to `https://hoctienganh.site.je/` and database `if0_42788092_b1` on `sql213.infinityfree.com`.

- The reviewed [Sprint 6 SQL](../database/infinityfree/sprint-6-update.sql) was imported exactly once after a read-only baseline audit. It created only `writing_submissions`, inserted migration `2026_09_08_001800_create_writing_submissions_table` in batch 4, and upserted two original active prompts. It contains no executable `ALTER TABLE`, `DROP`, `TRUNCATE`, destructive `DELETE`, database recreation, or reset operation.
- The post-deployment database has exactly 19 tables, 18 migration rows, and 61 total rows. `writing_prompts` has the two approved pilot prompts; `writing_submissions` has one expected hosted smoke record with status `submitted`, `word_count=130`, and `writing_prompt_id=1`. Existing Sprint 0–5 counts remain unchanged: topics 5, vocabularies 3, vocabulary progress 1, grammar 1, passages 1, listening 1, questions 2, options 5, exercises 1, exercise questions 1, exams 1, exam sections 1, exam section items 1, attempts 8, attempt answers 8, and speaking prompts 0.
- The full release was `release-20260908-175957-sprint6-tar.zip`, SHA-256 `544485946360622B5E4850FB1F00A657ED36280BD16CED36760E06D541192DD9`. The first hosted route audit found only `/manage/writing` failing because two Blade view namespace references were malformed. A targeted upload then replaced only `resources/views/manage/writing/index.blade.php` and `_form.blade.php` from `release-20260908-183701-sprint6-fix-tar.zip`, SHA-256 `88712BBA314E1B1B096977210B05E579C4C52B54A3B7D79ABAB7C5A623B09CF3`. No other files were re-uploaded.
- Both releases excluded `.env` and runtime data. File Manager confirmed `/htdocs/.env` remained 642 B and `/htdocs/storage` remained present. No production secrets were printed or committed.
- Hosted smoke verified Writing listing, editor, draft save, submitted review, Manage Writing index/detail/preview, all Sprint 0–5 regression routes, and no horizontal overflow at the tested mobile viewport. Direct `/health` navigation was blocked by the browser client with `ERR_BLOCKED_BY_CLIENT`; this is recorded as an unverified provider/browser edge path, not claimed as a pass.
- The final implementation and hosted evidence are recorded in [Document 18](18-sprint-6-final-report.md). Sprint 7 was not started.

## Sprint 7 production update

Sprint 7 adds browser-local Speaking practice and one metadata-only review table. The reviewed [Sprint 7 SQL](../database/infinityfree/sprint-7-update.sql) was imported exactly once after a read-only Sprint 6 baseline audit. It creates only speaking_submissions, records migration 2026_09_08_001900_create_speaking_submissions_table in batch 5, and adds three original active prompts. It contains no executable ALTER TABLE, DROP, TRUNCATE, destructive DELETE, database recreation, or reset operation.

- The post-import production audit recorded exactly 20 tables, 19 migration rows, three speaking prompts, and zero speaking submissions. The pre-Sprint 7 data counts were preserved, including five topics, three vocabularies, one vocabulary-progress row, one grammar lesson, one passage, one listening record, two questions, five question options, one exercise, one exercise-question mapping, one exam, one exam section, one exam-section item, eight attempts, eight attempt answers, two writing prompts, and one writing submission.
- The release archive release-20260908-224359-sprint7-final.zip was uploaded and extracted over application files only. Its SHA-256 is 67746ABF982C0F0E6F991AE2C7ECDAB30B80DA40D14CDC8C18DFB1F589EA315C. The archive contains neither .env nor runtime storage; File Manager confirmed the existing .env remained 642 bytes and storage remained present.
- The new speaking_submissions table uses restricted foreign keys to speaking_prompts, attempts, and exam_section_items; it has the approved attempt/item uniqueness rule and status/query indexes. Submitted reviews retain prompt snapshots and self-review metadata; audio is never uploaded or stored on the server.
- Hosted smoke testing passed the learner prompt list, part filter, all three prompt pages, Manage Speaking list/detail/preview/create/edit routes, browser-local preparation and speaking timers, playback/download wiring, fallback messaging after denied microphone permission, and the immutable submitted review. The single smoke review references prompt 1, is submitted, records 8 seconds and version 2, and appears as one submission in Manage Speaking.
- Sprint 0–6 regression routes, protected-route behavior, security headers, source-path denials, and the tested mobile viewport remained clean. Direct /health navigation was blocked by the browser client with ERR_BLOCKED_BY_CLIENT and is not claimed as a fresh Sprint 7 health pass.

The complete implementation and verification record is in [Document 19](19-sprint-7-final-report.md). Sprint 8 follows as an application-only hardening release.

## Sprint 8 production update

Sprint 8 is an application-only hardening release. It adds content QA/export commands for operator use, write-route throttling, safe error pages, and accessibility refinements. The local measured review found no concrete schema/index change, so there is no Sprint 8 production SQL import and no migration-row change; the production database remains at exactly 20 tables and 19 migrations.

Before any application upload, perform a fresh read-only audit against the Sprint 7 baseline: the existing tables/migration rows, vocabulary progress, topics, vocabularies, grammar, Reading, Listening, questions/options, writing and speaking history, `.env` size/presence, and runtime `storage` must match the recorded baseline. Upload only the reviewed application release contents, excluding `.env`, `storage`, database exports, logs, caches, `vendor`, and development artifacts. Preserve the existing production `.env` and runtime storage in place.

After upload, run the hosted health/login/dashboard, learner and Manage route inventory, content visibility, progress/history/result, Practice and mock-exam attempt, Writing, Speaking browser-local recording/fallback, security-header, protected-path, generic-error, and mobile overflow checks. If provider-edge blocking prevents a direct `/health` navigation, record that limitation and verify the same-origin response without weakening source-path protections. Do not start Sprint 9.

The local export/restore rehearsal and performance evidence, plus the production deployment record, are in [Document 21](21-sprint-8-final-report.md). The approved app-only release was extracted successfully after a transient File Manager response error; the post-upload database remained at exactly 20 tables, 66 rows, and 19 migrations, while `.env` and runtime storage were preserved. Hosted route and safe-404 checks passed. The provider/client edge blocked direct `/health` verification and direct HTTP inspection returned the edge JavaScript challenge, so that limitation remains recorded rather than treated as a pass.

## Fallback: Render Free + Neon Free

Keep this path documented but inactive. Render Free requires a Docker PHP runtime, has an ephemeral filesystem and cold starts, and its own documentation says Free instances should not be used for production. Neon Free changes the production engine to PostgreSQL and has its own compute, storage, restore-history, and network quotas. Activation requires an explicit owner decision, a PostgreSQL compatibility pass, a restore rehearsal, and a separate deployment review. Do not create Render, Neon, Docker, or paid resources for Sprint 0 while InfinityFree remains viable.

## Sprint 0 acceptance checklist (historical checkpoint)

The checklist below records the original Sprint 0 acceptance state; the later Sprint 1–3 production updates above are the current deployment record.

- [x] InfinityFree is the primary target and alwaysdata is retired from the active target.
- [x] Laravel 13 and Blade/Bootstrap remain unchanged.
- [x] MySQL/MariaDB remains the production engine.
- [x] PHP 8.3 compatibility is recorded and the lockfile resolves PHP 8.3-compatible packages.
- [x] Local Composer production install and local frontend build workflow are defined.
- [x] Fixed `htdocs` layout and source/secrets denial rules are versioned.
- [x] phpMyAdmin-only Sprint 0 schema import is versioned; no Sprint 1 tables/content exist.
- [x] Owner creates/verifies the InfinityFree account, domain, database, and File Manager access.
- [x] Release is uploaded and production `.env` is configured without exposing secrets.
- [x] Hosted HTTPS, login/dashboard/logout, `/health`, denial paths, and real MySQL connectivity pass.
- [x] Normal hosted requests/reloads preserve the database and file-session/runtime state.

The Sprint 0 Definition of Done is achieved for the final approved InfinityFree scope. The stronger second-upload marker exercise from the original proof plan remains explicitly unperformed because the final verification scope prohibited an unnecessary rebuild/re-upload.
