# Sprint 9 content expansion

## Scope

Sprint 9 batch 1 expands the original B1 pilot without changing the production schema. The batch uses the existing management/import paths and stays forward-only for an already populated database.

All new content is original and carries the source note `Original Sprint 9 batch 1`. Active records are editorially reviewed; questions also record answer-review status in metadata. Listening scripts remain draft until a permitted recording is supplied and checked.

## Batch inventory

| Content | Added | State | Notes |
|---|---:|---|---|
| vocabulary topics | 8 | active | Personal identity, work, travel, health, technology, environment, shopping, and community |
| vocabulary entries | 32 | active | Four original entries per new topic |
| grammar lessons | 4 | active | Past/present perfect, future forms, modals, and comparisons |
| Reading passages | 4 | active | Original B1 texts with exact word counts |
| Reading questions | 8 | active | Two single-choice questions per passage, four reviewed options each |
| Reading exercises | 4 | active | Each links its two active questions |
| Listening scripts | 2 | draft | Audio paths are pending; no audio file is uploaded |
| Listening questions | 4 | draft | Kept inactive until recordings pass the audio review |
| Writing prompts | 4 | active | Two Task 1 and two Task 2 prompts |
| Speaking prompts | 6 | active | Two prompts for each of the three practice parts |

The existing Sprint 0–8 records are not updated or deleted. Existing vocabulary progress is not referenced by the batch and remains unchanged.

## Quality and balance controls

- `php artisan content:validate` checks relationships, source metadata, active-state rules, options, and passage word counts.
- `php artisan content:report` reports topic coverage, active question balance, prompt distribution, source/status distribution, and declared audio bytes.
- Duplicate protection uses stable slugs, titles, prompts, and the vocabulary topic/term/part-of-speech key.
- The SQL file uses `NOT EXISTS` guards and contains no schema statement, `DROP`, `TRUNCATE`, destructive `DELETE`, or migration-row insert.
- Draft listening records deliberately show two pending recordings and only one byte of placeholder size; they cannot appear in the learner library while `draft`.

## Import and rollback evidence

`database/infinityfree/sprint-9-update.sql` is an idempotent MySQL/MariaDB batch wrapped in a transaction. It was generated from the same seeded records, syntax-checked against a disposable SQLite adaptation, then applied twice to a disposable Sprint 0–8 baseline. Both applications ended with the same counts:

- 13 topics;
- 35 vocabulary entries;
- 5 grammar lessons;
- 5 passages;
- 3 listening records;
- 14 questions and 45 options;
- 5 exercises and 9 exercise links;
- 6 writing prompts;
- 9 speaking prompts.

Because Sprint 9 adds content only, the production migration count remains 19 and the production table count remains 20. The pre-import production export must be retained before applying this batch. Rollback means restoring the reviewed database export only if recovery is necessary; the application release itself remains independent of this content import.

## Audio budget

No server audio is added in batch 1. The two scripts are editorial drafts with pending paths so the content bank can be reviewed without consuming hosting storage. A future audio activation requires an owner-recorded or compatible licensed asset, measured file size, MIME verification, provenance, and a fresh `content:validate`/`content:report` run before changing the record to active.
