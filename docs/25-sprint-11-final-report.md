# Sprint 11 final report

Status: complete through production deployment and hosted verification on 15 September 2026.

## Change

Sprint 11 adds the deterministic vocabulary spaced-review queue. The only new production table is `vocabulary_review_schedules`; the only new migration record is `2026_09_13_002000_create_vocabulary_review_schedules_table` in batch 6. The table has a unique one-to-one foreign key to `vocabulary_progress`, `ON DELETE CASCADE`, and an index on `due_at`.

## Production database

The production baseline was audited immediately before the write: 20 tables and 19 migrations. The reviewed SQL was imported exactly once. Final phpMyAdmin checks show:

- 21 tables, 20 migration rows, 191 total rows.
- `vocabulary_review_schedules`: 0 rows.
- Existing counts preserved: topics 13, vocabularies 35, vocabulary progress 1, grammar lessons 5, passages 5, listening contents 3, questions 14, question options 45, exercises 5, exercise questions 9, writing prompts 6, speaking prompts 9, attempts 8, attempt answers 8, writing submissions 1, and speaking submissions 1.
- No existing table was altered, dropped, truncated, recreated, reset, or destructively deleted.

The SQL console reported an error only on its final `information_schema` verification query. The import statements had completed, and independent schema, row-count, relation, and migration checks confirmed the final state; the SQL was not rerun.

## Production release

The reviewed release was uploaded and extracted once through File Manager. The release manifest identifies Sprint 11 and excludes `.env` and runtime `storage`. Production retained `.env` at 642 B and the existing `storage` directory. The temporary archive was not left in `/htdocs`.

## Hosted verification

The spaced-review page rendered 1 due card and 34 new cards. Reveal displayed the existing `deadline` meaning and example. The dashboard rendered 35 active vocabulary entries, 5 active grammar lessons, 1 learning item, and 1 due item. GET-only regression checks passed for the learner routes, Manage Topics, and the Sprint 0–10 result/history/content routes. No rating POST was made, preserving vocabulary progress and avoiding test history writes. Direct browser navigation to `/health` was blocked by the client edge; this report makes no new claim about that specific browser navigation.

No Sprint 12 work was started.
