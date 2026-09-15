# Sprint 11 vocabulary spaced review

## Scope and status

Sprint 11 implements the first approved post-MVP learning improvement: a deterministic vocabulary review queue. Local implementation and verification are complete. Production SQL import and application upload remain pending a separate production-write approval.

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

The InfinityFree SQL candidate contains one `CREATE TABLE IF NOT EXISTS` and one guarded migration-record insert in batch 6. It has no executable `ALTER`, `DROP`, `TRUNCATE`, `DELETE`, database recreation, reset command, existing content insert, or existing-progress update. A successful production import changes the baseline from 20 tables / 19 migrations to 21 tables / 20 migrations while all existing rows retain their values.

## Local verification

- Focused Sprint 11 suite: 11 tests, 71 assertions.
- Full SQLite suite: 112 tests, 728 assertions.
- Full PostgreSQL 18 suite: 112 tests, 728 assertions.
- Pint targeted style check: passed.
- Composer strict validation: passed.
- Disposable migration/seed rehearsal: 21 tables, 20 migrations, zero schedule rows.
- Restored content QA: 96 records, 0 issues.

## Production gate

No production write or release upload has occurred. Before deployment, verify the complete Sprint 9/10 baseline and stop if it differs. Preserve the existing `.env`, runtime `storage`, content, attempts, Writing/Speaking history, and vocabulary progress. Import the SQL once, upload only changed reviewed release files, run the new hosted review flow, and repeat Sprint 0–10 regressions. Do not begin another sprint during this gate.
