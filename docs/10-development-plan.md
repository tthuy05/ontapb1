# Development Plan

## Delivery model

Implementation is divided into vertical, reviewable sprints. A sprint is complete only when its migrations, server behavior, Blade UI, security checks, automated tests, and deployment implication are addressed. Exact calendar duration depends on owner availability and content volume; the order is more reliable than an early date estimate.

No implementation sprint starts until the Phase 1 Approval Checkpoint is approved.

## Migration order

Create schema in this dependency order:

1. `topics`
2. `vocabularies`
3. `vocabulary_progress`
4. `grammar_lessons`
5. `passages`
6. `listening_contents`
7. `questions`
8. `question_options`
9. `exercises`
10. `exercise_questions`
11. `writing_prompts`
12. `speaking_prompts`
13. `exams`
14. `exam_sections`
15. `exam_section_items`
16. `attempts`
17. `attempt_answers`
18. `writing_submissions`
19. `speaking_submissions`

Rollback occurs in reverse. Foreign keys and indexes are created with their tables; cross-table checks that MySQL cannot express cleanly remain transactional application invariants with tests.

## Sprint 0 — foundation and deployment proof

**Goal:** prove the approved stack on development and primary free hosting before domain work.

- Scope/tasks: initialize Laravel 13 on PHP 8.3, configure MySQL/MariaDB, Blade/Bootstrap assets, code quality/test commands, owner-session middleware, generic login/logout, health endpoint, production error/log settings, and a minimal InfinityFree deployment proof.
- Dependencies: approval, hosting account, chosen owner secret, PHP/Composer availability.
- Schema/models: none beyond Laravel infrastructure actually required; do not add a `users` table.
- Controllers/requests/routes/views: session controller, health action, `owner` middleware, login and a protected placeholder dashboard.
- Tests: successful/failed login, throttle, session regeneration, CSRF logout, protected-route redirect, non-disclosing health response.
- Deployment: prepare a local production release, upload into InfinityFree's fixed `htdocs` with the protected rewrite layout, import the Sprint 0 SQL through phpMyAdmin, and verify HTTPS, DB connectivity and persistent storage.
- Risks: host resource ceilings or runtime mismatch discovered too late.
- Definition of done: local and hosted protected placeholder pass smoke tests; no app modules or seeded copyrighted content.

## Sprint 1 — topics, vocabulary, and grammar

**Goal:** deliver the first complete study/reference vertical.

- Scope/tasks: topic CRUD, vocabulary CRUD/progress, grammar CRUD, source/licence fields, filters, activation, learner lists/details.
- Dependencies: Sprint 0 and approved initial syllabus batch.
- Tables/models: `topics`, `vocabularies`, `vocabulary_progress`, `grammar_lessons` and relationships/casts.
- Controllers/requests/routes/views: learner vocabulary/grammar controllers; `Manage` topic/vocabulary/grammar controllers and Form Requests; routes and pages in Documents 08–09.
- Tests: constraints, duplicate rules, status visibility, filtering, restricted delete, ownership gate, XSS escaping, source validation.
- Deployment: migration plus a small original-content import; record DB/storage growth.
- Risks: content entry scope swamps product work.
- Definition of done: owner can curate, activate, study and track the first vocabulary/grammar batch on production.

## Sprint 2 — reading/listening content and question bank

**Goal:** create reusable source content and valid objective questions.

- Scope/tasks: passage/listening CRUD, safe audio-path handling, transcript policy, question/option CRUD, type registry for single-choice and true/false, preview.
- Dependencies: topic schema and original/open-licensed pilot content.
- Tables/models: `passages`, `listening_contents`, `questions`, `question_options`.
- Controllers/requests/routes/views: learner Reading/Listening; Manage content/question controllers; type-aware Form Requests and editors.
- Tests: one correct option invariant, parent-source rules, activation validation, inactive filtering, path traversal rejection, unsupported type rejection.
- Deployment: upload/commit only the small approved audio pilot; measure quota and audio delivery.
- Risks: licensing ambiguity and free-host disk/bandwidth.
- Definition of done: valid questions can be curated and previewed without exposing answer keys in learner study pages.

## Sprint 3 — exercises and attempt persistence

**Goal:** make practice reliably resumable and server-scored.

- Scope/tasks: exercise composer, start transaction, immutable snapshots, pre-created answer rows, answer saves with versions, manual and expiry submission, result page.
- Dependencies: question bank and final snapshot JSON schemas.
- Tables/models: create the approved assessment dependency block in migration order: `exercises`, `exercise_questions`, `writing_prompts`, `speaking_prompts`, `exams`, `exam_sections`, `exam_section_items`, `attempts`, and `attempt_answers`. Sprint 3 exposes only the exercise/attempt subset; the prompt/exam tables remain empty until their later verticals.
- Controllers/requests/routes/views: Practice, Attempt and Result controllers; answer/save/submit Requests; catalog, attempt and result pages.
- Tests: snapshot immutability, start idempotency policy, save conflict, no-save-after-submit, deadline authority, transaction rollback, answer-key non-disclosure.
- Deployment: validate session duration, DB timestamps/timezone, save latency and log redaction.
- Risks: race conditions at submit/expiry and accidental live-content joins in history.
- Definition of done: single-choice/true-false exercise can start, resume, save, expire, submit and render entirely from snapshots.

### Sprint 3 completion record — 6 September 2026

- [x] Nine approved assessment tables and nine migration records were imported once through phpMyAdmin without changing Sprint 0–2 tables or data.
- [x] Exercise/Attempt/Result controllers, Requests, scoring, Blade pages, autosave, timer, and owner Manage Exercise workflow were released to InfinityFree.
- [x] The original Reading pilot exercise references the existing `topics.id=4` (`reading-daily-plans`) and `questions.id=1`; prompt/exam dependency tables remain empty.
- [x] Local full tests, focused migration tests, syntax/style/build gates, and hosted Sprint 0–2 regression plus Sprint 3 smoke checks passed.

## Sprint 4 — scoring, history, and wrong-answer review

**Goal:** complete trustworthy objective practice feedback.

- Scope/tasks: `ScoringService`, raw score/percentage, breakdowns, history filters, snapshot-derived wrong-answer review.
- Dependencies: Sprint 3 and approved normalization/scoring rules.
- Tables/models: no new tables; indexed queries on attempts/answers.
- Controllers/requests/routes/views: History and Review controllers/pages; result refinements.
- Tests: per-type scoring matrices, totals/rounding, repeated submit idempotency, deleted/deactivated live content, query-count guardrails.
- Deployment: migrate indexes if measurements justify them; smoke-test historical pages.
- Risks: UI could misrepresent practice percentage as official VSTEP score.
- Definition of done: every objective result is reproducible from its snapshots and carries an explicit non-official label.

### Sprint 4 completion record — 7 September 2026

- [x] Result breakdowns, snapshot-stable history filters, wrong-answer review, and dashboard practice summaries are implemented without new schema.
- [x] Local focused/full tests, PHP lint, Pint, Composer checks, Vite build, pnpm audit, Blade cache, route inventory, and disposable migration/seed checks passed.
- [x] Hosted Sprint 4 smoke checks and Sprint 0–3 regression checks passed; no Sprint 5 work was started.

## Sprint 5 — mock-exam composition and flow

**Goal:** support configurable VSTEP-style simulations without hard-coded exam structure.

- Scope/tasks: exam/section/item composer, structural validation, snapshot/start flow, section navigation, overall timing, final result.
- Dependencies: stable attempt engine and an approved original exam content set.
- Tables/models: activate the existing `exams`, `exam_sections`, and `exam_section_items` models/relationships; no new table is expected because their foreign-key dependency block was created before attempts in Sprint 3.
- Controllers/requests/routes/views: Exam learner/Manage controllers, composer Requests, exam catalog/detail/section/take pages.
- Tests: ordered heterogeneous items, invalid structure rejection, inactive child rejection, timer/section access, snapshot survival after edits.
- Deployment: run a production-like full exam under free-tier memory/request limits.
- Risks: official format assumptions, long sessions, incomplete bank.
- Definition of done: one full configurable mock can be completed, while its raw result is not presented as official band conversion.

### Sprint 5 implementation record — 7 September 2026

- [x] Added owner composition for ordered objective Reading/Listening sections and items using the existing Sprint 3 exam tables.
- [x] Added activation validation for contiguous positions, one objective question per item, matching skills, active dependencies, valid options, and time limits.
- [x] Added immutable exam configuration/question/context snapshots, section navigation, overall deadline handling, raw section/skill/topic/type result breakdowns, and non-official scoring labels.
- [x] Added the `B1 objective mock pilot` local seeder using the existing Sprint 2 Reading question; Listening remains available for future reviewed active content.
- [x] Added a reusable local-only encrypted-credential FTP listing/deployment workflow. It protects the production `.env` and `storage` paths and never deletes remote files.
- [x] No Sprint 5 migration or production SQL import is required: the `exams`, `exam_sections`, and `exam_section_items` tables already exist from Sprint 3.
- [x] Hosted deployment and final smoke/regression results are recorded in `docs/17-sprint-5-final-report.md`: production remains at 18 tables/17 migrations, the pilot completed successfully at 100%, the existing `.env`/`storage` were preserved, and Sprint 0–4 routes passed regression checks.

## Sprint 6 — Writing practice

**Goal:** save and review B1/VSTEP-aligned written practice.

- Scope/tasks: writing prompt CRUD, Task 1/2 metadata, editor, server word count, prompt snapshot, response save and self-check.
- Dependencies: approved writing plan and non-official rubric wording.
- Tables/models: `writing_prompts`, `writing_submissions`.
- Controllers/requests/routes/views: Writing and Manage Writing controllers/Requests/pages.
- Tests: word counting edge cases, immutable prompt snapshot, safe long text, state rules, no fabricated auto-score.
- Deployment: verify request limits and backups for text submissions.
- Risks: self-check mistaken for examiner assessment.
- Definition of done: original prompts and responses are durable, reviewable, and honestly labeled.

### Sprint 6 implementation record — 8 September 2026

- [x] Added Writing prompt management, Task 1/Task 2 metadata, learner editor, server-side Unicode word count, self-check fields, prompt snapshots, optimistic draft saves, and immutable submitted review.
- [x] Added `writing_submissions` with restricted foreign keys and the named `(attempt_id, exam_section_item_id)` uniqueness rule; no existing Sprint 0–5 table was altered.
- [x] Added two active original pilot prompts and imported the reviewed Sprint 6 SQL exactly once. Production finished at 19 tables and 18 migration rows; the existing content/progress rows were preserved.
- [x] Uploaded the full Sprint 6 release without `.env` or runtime storage, then uploaded only the two corrected Manage Writing views after the first hosted route audit found a namespace rendering defect.
- [x] Full local suite passed with 84 tests and 549 assertions. Hosted Writing smoke tests, Sprint 0–5 regression routes, and representative mobile overflow checks passed; direct `/health` was blocked by the browser client and is recorded as such.
- [x] Final evidence is recorded in `docs/18-sprint-6-final-report.md`. Sprint 7 was not started.

## Sprint 7 — Speaking practice with local recording

**Goal:** provide timed speaking rehearsal without consuming server audio storage.

- Scope/tasks: speaking prompt CRUD, browser feature detection, permission flow, record/stop/play/download, timer, self-review metadata; no upload.
- Dependencies: HTTPS, supported browser and approved speaking guidance.
- Tables/models: `speaking_prompts`, `speaking_submissions` (metadata/notes only).
- Controllers/requests/routes/views: Speaking and Manage Speaking controllers/Requests/pages; small progressive-enhancement JS module.
- Tests: server prompt/submission rules, secure headers/permissions behavior, manual browser matrix for allowed/denied/unsupported microphones.
- Deployment: HTTPS and microphone smoke test on production domain; confirm no audio request reaches server.
- Risks: codec/browser variation, permission denial, recordings lost on navigation.
- Definition of done: supported browsers can rehearse and download locally; unsupported paths retain timer/prompts/notes.

### Sprint 7 implementation record — 8–12 September 2026

- [x] Added Speaking prompt CRUD, provenance/status rules, active-prompt visibility, local recording controls, preparation/speaking timers, self-review metadata, prompt snapshots, and immutable submitted reviews.
- [x] Added `speaking_submissions` as a metadata-only table with restricted foreign keys, the named `(attempt_id, exam_section_item_id)` uniqueness rule, and status/query indexes; no existing Sprint 0–6 table was altered.
- [x] Added three original active pilot prompts and the reviewed forward-only InfinityFree SQL update; the production import was performed exactly once after the Sprint 6 baseline audit.
- [x] Built the production release with production-only dependencies/assets while excluding `.env`, `storage`, tests, logs, local databases, and development packages.
- [x] Local verification passed: focused Speaking tests, full suite (91 tests, 592 assertions), disposable migration/seed rehearsal, PHP lint, targeted Pint, Composer/package audits, Vite build, view cache, route inventory, and release audit.

## Sprint 8 — hardening, content QA, and accessibility

**Goal:** make the MVP dependable for sustained personal use.

- Scope/tasks: complete security checklist, backup/export commands, performance measurement, responsive/accessibility fixes, source audits, error/empty states and content validation report.
- Dependencies: all MVP verticals and representative content volume.
- Tables/models: only measured index/constraint changes; no speculative schema.
- Controllers/requests/routes/views: targeted refinements, no architecture rewrite.
- Tests: full critical-path feature suite, WCAG/manual checks, restore rehearsal, production smoke checklist.
- Deployment: verify cron/scheduler only if actually needed, logs, quota, inactivity reminder, export off-host.
- Risks: free-tier operational limitations and inconsistent content quality.
- Definition of done: MVP checklist, security tests, restore test, accessibility audit and deployment runbook all pass.

### Sprint 8 implementation record — 12 September 2026

Sprint 8 local hardening adds a private allow-listed `study:export` JSON command, a `content:validate` relationship/provenance QA command, repeatable performance measurement, write-route throttling, safe generic error pages, and keyboard/focus/live-region improvements. No schema change or speculative index was required. The implementation and verification record is in [Document 21](21-sprint-8-final-report.md).

## Sprint 9 — controlled content expansion

**Goal:** grow from pilot content to the approved B1 syllabus without breaking quality or quota.

- Scope/tasks: add original/open-licensed batches, peer/self QA checklist, balance reports by topic/skill/type/difficulty, audio-budget monitoring.
- Dependencies: stable import workflow and source ledger.
- Tables/models/controllers: normally none; use existing management/import paths.
- Tests: import validation, duplicates, orphan checks, balanced-bank reports.
- Deployment: batch import, database export before each batch, post-import smoke test.
- Risks: quantity over quality, duplicate items, unclear licensing.
- Definition of done: target inventory is met with source records and review status for every item.

## Sprint 10 — optional hosting migration

**Goal:** move only if InfinityFree limits materially block use.

- Scope/tasks: approved MySQL-to-PostgreSQL compatibility pass, Render Docker image, Neon connection, immutable release process and rollback/restore rehearsal.
- Dependencies: explicit owner approval; Render and Neon free terms rechecked at migration time.
- Tables/models: portable types/queries; migration and export transformation tests.
- Controllers/routes/views: no expected functional change.
- Tests: full suite on PostgreSQL, case/collation assumptions, connection limits, cold-start behavior, ephemeral-filesystem audit.
- Deployment: Git-connected Render service, Neon secrets, static audio shipped with release, database restore.
- Risks: two-provider outages/quotas, free-service suspension, DB semantic differences.
- Definition of done: parity and restore verified before DNS/bookmark cutover; InfinityFree SQL/application export retained for rollback.

## Cross-sprint controls

- Keep migrations forward-only after production data exists; fix mistakes with new migrations.
- Seeders contain only tiny synthetic/original samples, never scraped exam material.
- Each sprint updates documentation when a decision changes.
- Production deployment follows test, backup/export, migration, cache, smoke check, then rollback decision.
- New services/tables/packages require a concrete use case and owner approval when they alter the Phase 1 architecture.
