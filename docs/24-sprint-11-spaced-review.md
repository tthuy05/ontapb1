# Sprint 11 vocabulary spaced review

## Scope and status

Sprint 11 implements the first approved post-MVP learning improvement: a deterministic vocabulary review queue. Local implementation, production import, release upload, and hosted verification are complete.

The feature reuses active `vocabularies` and the existing one-to-one `vocabulary_progress` record. It adds no user account, notification service, queue worker, cache, audio, AI, or third-party dependency.

## Review behavior

- Scheduled cards due at or before the current time appear first.
- Existing `learning` or `review` progress without a schedule appears next, so old progress is adopted without rewriting it.
- Up to five active new/untracked cards appear last; the whole queue is capped at 20.
- Inactive vocabulary and vocabulary under an inactive topic never enter the queue.
- The answer uses a native `<details>` reveal and all rating actions are ordinary authenticated, CSRF-protected, throttled forms.

Rating rules are deterministic:

| Rating | Next interval | Progress effect |
|---|---|---|
| `Again` | 10 minutes, stored as interval 0 | reset streak, increment lapse and incorrect count, state `learning` |
| `Hard` | 1 day initially, then at least +1 day or 1.2× | increment streak and correct count |
| `Good` | 1 day initially, then at least +1 day or 2× | increment streak and correct count |
| `Easy` | 4 days initially, then at least +2 days or 2.5× | increment streak and correct count |

Intervals are capped at 3,650 days. Successful ratings map to `learning` below 7 days, `review` from 7–20 days, and `learned` from 21 days. These labels remain personal-study guidance, not a scientific memory score.

## Schema and SQL review

Migration: `2026_09_13_002000_create_vocabulary_review_schedules_table`.

New table: `vocabulary_review_schedules`. Its required `vocabulary_progress_id` foreign key uses `ON DELETE CASCADE`, is unique, and therefore enforces one schedule per progress row. `due_at` is indexed for the due queue. There is no pilot data or backfill.

The InfinityFree SQL candidate contains one `CREATE TABLE IF NOT EXISTS` and one guarded migration-record insert in batch 6. It has no executable `ALTER`, `DROP`, `TRUNCATE`, `DELETE`, database recreation, reset command, existing content insert, or existing-progress update. The one production import changed the baseline from 20 tables / 19 migrations to 21 tables / 20 migrations; all existing rows retained their values and the new schedule table started empty.

## Local verification

- Focused Sprint 11 suite: 11 tests, 71 assertions.
- Full SQLite suite: 112 tests, 728 assertions.
- Full PostgreSQL 18 suite: 112 tests, 728 assertions.
- Pint targeted style check: passed.
- Composer strict validation: passed.
- Disposable migration/seed rehearsal: 21 tables, 20 migrations, zero schedule rows.
- Restored content QA: 96 records, 0 issues.

## Production verification

- The pre-write audit confirmed 20 tables, 19 migrations, the existing content/progress counts, `.env`, and runtime `storage`.
- The reviewed SQL was imported exactly once. Independent phpMyAdmin checks confirmed 21 tables, 20 migrations, `vocabulary_review_schedules` with zero rows, and the Sprint 11 migration in batch 6. phpMyAdmin displayed an error only for its final `information_schema` verification query; no rerun was made, and the independent schema/migration checks passed.
- The reviewed release was uploaded and extracted once through File Manager. `.env` remained present at 642 B, `storage` remained present, the manifest identifies Sprint 11, and the archive was not left in `/htdocs`.
- Hosted `/vocabulary/review?i=1` showed 1 due card and 34 new cards, including the existing `deadline` progress and new cards; reveal displayed the meaning/example. The dashboard showed 35 active vocabulary entries, 5 active grammar lessons, 1 learning item, and 1 due item.
- GET-only regression checks passed for learner pages, `/manage/topics` (13 topics), `/vocabulary`, `/grammar`, `/reading`, `/listening`, `/practice`, `/history`, `/exams`, `/writing`, `/speaking`, and wrong-answer review. `/topics` is intentionally not a public route and returned the expected 404; its owner-facing route is `/manage/topics`.
- No rating or other study-write action was submitted during verification, so existing vocabulary progress and history were not changed. Sprint 12 was not started.
