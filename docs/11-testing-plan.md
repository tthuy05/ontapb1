# Testing Plan

## Objectives

Testing protects the properties most likely to harm a self-study tool: losing answers, changing history after content edits, revealing keys early, scoring incorrectly, expiring at the wrong time, allowing unauthenticated access, or making data impossible to restore. The suite should be fast enough to run on every change and realistic enough to exercise database transactions.

## Layers

| Layer | Scope | Examples |
|---|---|---|
| Static/style | PHP syntax, formatting, optional focused static analysis | invalid types, dead imports, unsafe patterns |
| Unit | Pure deterministic logic | answer normalization, type scoring, percentage calculation, word count |
| Feature | HTTP + middleware + validation + DB | login, CRUD, start/save/submit, status visibility, review |
| Browser/manual | Browser APIs and complex interaction | MediaRecorder, timers, keyboard navigation, responsive composer |
| Deployment smoke | Built production environment | HTTPS, DB, protected page, storage/audio, migrations, logs |
| Data/restore | Backup and history integrity | export, clean restore, snapshot result reproduction |

Use a separate test database. CI/local test configuration must never point at production; an environment guard should fail loudly if it does.

## Critical test matrix

### Authentication and security

- Unauthenticated access is rejected for every learner/manage route, including guessed object URLs and JSON endpoints.
- Correct login regenerates session; failure is generic and throttled; logout invalidates it.
- State-changing form and JSON requests reject missing/invalid CSRF.
- Owner credential comparison uses Laravel hashing; secrets never appear in response/log fixtures.
- Stored HTML/script-like content is escaped or passed through the approved sanitizer policy.
- Uploaded/referenced file paths reject traversal, executable extensions and paths outside the approved audio directory.

### Content management

- Required fields, status/type values, source metadata and length limits are enforced.
- A single-choice/true-false question has the exact valid option/correctness structure.
- Active exercises/exams cannot include missing, draft or structurally invalid items.
- Referential delete is restricted; deactivation leaves historical access intact.
- Ordering updates are complete, unique, authorized and transactional.
- Search/filter/pagination remain stable for empty and large-enough fixture sets.

### Attempt creation and snapshots

- Start creates one attempt and the expected ordered `attempt_answers` in one transaction.
- Every rendered/scored historical field comes from the documented snapshot, not a changed live row.
- Editing/deactivating/deleting permissible live content after start does not alter the attempt result.
- Snapshot JSON schema is validated on creation and versioned.
- Inactive exercises/exams cannot start new attempts.

### Saving, concurrency and timing

- Valid response normalization and save increments `save_version` and `last_saved_at`.
- An older version receives `409` and cannot overwrite a newer response.
- Submitted or deadline-passed attempts reject edits.
- The server timestamp is authoritative even if browser time is wrong or a countdown is paused.
- Saves at the deadline boundary follow a documented rule; submit is idempotent during double-click/retry.
- Transaction failure during start or submit leaves no half-created attempt/partial score.

### Scoring and review

For every MVP objective type, table-driven unit cases cover correct, incorrect, missing and malformed answers. Feature tests also verify:

- points earned never exceed available points;
- raw totals and percentages follow documented decimal/rounding rules;
- repeated submit returns the same result;
- wrong-answer review includes only submitted incorrect auto-scored answers;
- correct answer/explanation are not in take-page HTML, pre-submit payloads, or save responses;
- no UI/API field calls the result an official VSTEP score or level.

### Writing and speaking

- Prompt snapshots and submitted text survive later prompt edits.
- Word count has agreed handling of contractions, punctuation, numbers, Unicode and whitespace.
- Writing/speaking pages do not generate an official score.
- Sprint 6 feature coverage verifies draft/submitted state transitions, optimistic save versions, immutable submitted responses, restricted prompt references, long-text limits, and original-content provenance.
- The hosted Sprint 6 smoke flow verified prompt listing, editor, draft save, 130-word submit, prompt-snapshot review, and owner Manage Writing pages.
- Speaking server requests contain notes/metadata only; manual network inspection confirms recorded blobs are not uploaded.
- Browser checks cover supported codec, fallback codec, denied permission, missing device, insecure-context development warning and unsupported API.

## Sprint 7 verification record — local implementation checkpoint

The Sprint 7 feature suite passes 7 tests and 43 assertions. It covers owner protection, active/draft/topic visibility, draft-to-submitted metadata, duration bounds, immutable submitted practice, prompt snapshots, original-content management/provenance, and the metadata-only schema/indexes. The full regression suite passes 91 tests and 592 assertions. SQLite `migrate:fresh --seed` reaches the new migration and `SprintSevenContentSeeder`; the production SQL audit finds no executable `ALTER TABLE`, `DROP`, `TRUNCATE`, `DELETE`, database recreation, or reset statement.

The hosted manual matrix remains part of the deployment gate: Chromium/HTTPS with allowed microphone, denied permission, unsupported/insecure fallback, local playback/download, no audio request, and mobile-width overflow checks. A supported browser must prove recording stays local; fallback cases must still save notes and metadata.

## VSTEP mock structural tests

When an exam is labeled as a strict full-format simulation, a validator/test fixture checks the approved structure: Listening 3 parts/35 objective questions/about 40 minutes; Reading 4 passages/40 questions/60 minutes; Writing two tasks/60 minutes with task weighting metadata; Speaking three parts/about 12 minutes. Content-length conflict and provider-specific operational rules remain warnings unless resolved by an approved source.

Do not test an invented raw-to-10 conversion. If a future official conversion table is obtained and approved, it receives source/version fields and golden test vectors.

## Sprint 8 verification record — 12 September 2026

The focused Sprint 8 suite passes 7 tests and 43 assertions. It covers clean seeded content QA, machine-readable QA output, private allow-listed export paths, traversal/public-path rejection, the skip-link/main landmark, safe 404 rendering, and write-route throttling. The complete regression suite passes 98 tests and 635 assertions.

The disposable SQLite restore rehearsal rebuilt all 19 migrations and all approved Sprint 1–7 seeders, then `content:validate` reported 20 records and 0 issues. No production database was used for this rehearsal. Three local performance iterations measured `content:validate --json` at 347.18–417.66 ms (381.06 ms average) and `route:list --except-vendor` at 356.89–384.28 ms (374.52 ms average) on the development machine.

Manual accessibility checks for the release gate cover keyboard focus visibility, skip-link focus, live status/error announcements, 360px/768px/desktop layout, 200% zoom, reduced motion, and browser-local Speaking fallback behavior. Error responses use generic messages and do not render exception details.

## Sprint 11 local verification record — 15 September 2026

The focused Sprint 11 suite covers the one-to-one schedule schema/indexes, cascade behavior, owner protection, due/legacy/new queue ordering, five-new-card limit, active-content filtering, deterministic rating transitions, counters, dashboard due count, and the forward-only InfinityFree SQL contract. The full suite passes on both in-memory SQLite and a temporary PostgreSQL 18 cluster with 112 tests and 728 assertions.

A disposable SQLite `migrate:fresh --seed` rehearsal applies all 20 migrations, creates 21 tables, and leaves `vocabulary_review_schedules` empty. `content:validate --json` still reports 96 records and 0 issues with the approved Sprint 1–9 content counts. No production database or credential is used for local verification.

## Performance and resource tests

Representative data should include at least the expected MVP bank scale, not only five records. Measure:

- dashboard/history/result query count and response time;
- attempt start/submit transaction time at a full exam size;
- payload size and answer-save latency;
- memory during import/composer/full result rendering;
- audio asset bytes and monthly transfer estimate;
- DB and total disk quota after representative content/backups.

Targets from Requirements are evaluated on normal broadband and on production free hosting. Avoid fragile millisecond assertions in automated suites; use profiling and broad regression thresholds.

## Accessibility and compatibility

- Automated HTML/accessibility tooling is a useful baseline, followed by keyboard-only manual completion.
- Test labels, fieldsets/legends, focus order, focus visibility, validation association, live save/timer messages, contrast, 200% zoom and reduced motion.
- Responsive manual passes at approximately 360px, 768px and desktop.
- Primary browser matrix: current Chromium desktop/mobile, current Firefox desktop, and current Safari/WebKit when accessible. Media recording is progressively enhanced, so unsupported recording is not a total page failure.

## Backup/restore verification

At least once before MVP completion and after major schema changes:

1. create an application export and database dump;
2. provision an empty compatible database in a non-production environment;
3. restore schema/data and approved persistent audio;
4. load a historical result and reproduce its score from snapshots;
5. verify source/licence metadata and submission text;
6. record restore duration and any manual steps.

Host backups are a recovery layer, not the only backup. The owner's periodic off-host export is tested too.

## Test data policy

- Factories use synthetic/original English text and clearly fake credentials.
- Tests do not copy paid textbooks, real candidate responses, leaked exams, or unlicensed audio.
- Time tests freeze the framework clock; IDs and timestamps are not assumed to have production values.
- Fixtures represent Unicode Vietnamese notes and English punctuation to catch encoding problems.

## Release gates

A production release requires:

- formatting/static checks and automated unit/feature suite pass;
- no pending destructive/unreviewed migration;
- a current export before migrations affecting stored study history;
- security-critical and attempt-flow manual smoke checks;
- production config/cache and HTTPS health check;
- documented rollback decision and post-deploy verification.

## Sprint 3 verification record — 6 September 2026

Local gates passed: PHPUnit `65 tests, 423 assertions`; disposable SQLite `migrate:fresh --seed` through all 17 migrations and the three content seeders; 107 PHP files linted; targeted Pint; Composer validation, audit, and production platform requirements; pnpm audit; Vite production build; Blade view cache; and the route inventory.

Hosted checks passed on `https://hoctienganh.site.je/`: dashboard and all Sprint 0–2 learner/manage GET regressions, Practice catalog/detail, active exercise filtering and empty state, attempt start/save/reload/submit/result, answer-key withholding before submit, invalid-resource 404s, owner-protected routes, security headers, and the 360px no-horizontal-overflow check. The existing Draft Listening record correctly remains absent from the public Listening library. The hosted `/health` endpoint returned HTTP 200 and exactly `{"status":"ok"}` through a same-origin request; direct navigation was blocked by the provider edge. Protected source-path fetches were likewise blocked by the edge and disclosed no content. No new content-management writes were used for verification.

## Sprint 4 verification record — 7 September 2026

Local Sprint 4 tests cover owner protection, snapshot-stable history, status/search/skill filters, result skill/topic/type breakdowns, submitted-incorrect-only review, snapshot-stable review detail, and dashboard review summaries. The full suite passed with 71 tests and 472 assertions. No schema migration is required for Sprint 4.

## Out-of-scope testing in MVP

Large multi-user load, distributed concurrency, native mobile devices as an exhaustive lab, AI scoring accuracy, cloud audio upload/transcoding, and payment workflows are not MVP test areas because those features are not in scope.
