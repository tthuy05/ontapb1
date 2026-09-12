# Sprint 8 report — hardening, content QA, and accessibility

Date: 12 September 2026
Status: implementation, production upload, and hosted verification complete with health-endpoint verification limited by the hosting/client edge

## Delivered

- Added `content:validate` for content, provenance, relationship, and active-state QA.
- Added private allow-listed `study:export` JSON export with traversal/public-path protection and no runtime/secrets export.
- Added a repeatable PowerShell performance measurement script.
- Added a dedicated 120/minute per-IP/session limiter to state-changing study and Manage routes.
- Added safe generic 403/404/419/429/500/503 pages.
- Added a keyboard skip link, main landmark, visible focus treatment, and live status/error regions.
- Added focused Sprint 8 tests and updated the security, testing, development, README, and deployment documentation.
- No database migration, SQL import, schema change, or speculative index was added.

## Verification

- Focused Sprint 8 suite: 7 tests, 43 assertions — passed.
- Full PHPUnit regression suite: 98 tests, 635 assertions — passed.
- PHP lint for changed PHP files — passed.
- `git diff --check` — passed.
- Disposable SQLite `migrate:fresh --seed` through all 19 migrations and Sprint 1–7 seeders — passed.
- Content QA on the disposable database: 20 records, 0 issues — passed.
- Performance sample, three iterations: `content:validate --json` 381.06 ms average; `route:list --except-vendor` 374.52 ms average.
- Export tests confirm only allow-listed study tables are written to private storage and unsafe paths are rejected.
- Error-page test confirms a generic 404 without stack-trace or vendor-path disclosure.

## Production deployment and hosted verification

- Fresh pre-write audit matched the Sprint 7 baseline: 20 tables, 19 migration rows, 66 total rows, and the recorded content/progress counts.
- Sprint 8 is app-only; no SQL was imported and no production migration row changed.
- The approved release `sprint8-release.zip` was extracted into `/htdocs` after one earlier File Manager attempt returned `Invalid response from server` without changing the root listing. The successful extraction added the two content-QA commands, `ContentQualityReport.php`, and the seven safe error-view files.
- File Manager confirmed `/htdocs/.env` remained present at 642 B and `/htdocs/storage` remained present. The production manifest changed to the Sprint 8 app-only manifest; no `.env`, storage, database, export, log, cache, or development artifact was uploaded.
- Post-deployment phpMyAdmin verification still shows exactly 20 tables, 66 total rows, and the exact 19 Sprint 0–7 migration records. Existing vocabulary progress, topics, vocabulary, grammar, Reading, Listening, questions/options, attempts, writing submissions, and speaking submissions were not changed.
- Hosted smoke checks passed for the dashboard and Vocabulary, Grammar, Reading, Listening, Writing, Speaking, Practice, Mock exams, History, Review, and Manage pages. The hosted 404 path returned the generic `Page not found` view without stack-trace or environment disclosure, and the dashboard retained the recorded content/progress summary.
- Direct `/health` verification was attempted but the in-app browser reported `ERR_BLOCKED_BY_CLIENT`; a direct HTTP request received the hosting edge JavaScript challenge rather than the application health payload. This is recorded as an environment limitation, not a pass.

Sprint 9 was not started.

Sprint 9 was not started.
