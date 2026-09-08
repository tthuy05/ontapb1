# Sprint 6 final report — writing practice

Status: complete. Sprint 7 was not started.

## Delivered

Sprint 6 adds original B1/VSTEP-aligned Writing practice with:

- owner-managed Task 1/Task 2 prompts and provenance metadata;
- learner prompt listing, editor, checklist, draft save, submit, and review;
- server-derived Unicode word counts and optimistic draft versions;
- immutable prompt snapshots and immutable submitted responses;
- explicit practice-only labeling with no fabricated official score.

## Local verification

- Full Laravel suite: 84 tests passed, 549 assertions.
- SQLite migration/seed rehearsal, route listing, view cache, production Composer install, and InfinityFree release build passed.
- The release archive contained no `.env`, local database, logs, cache, `vendor` development artifacts, or runtime storage.

## Production database

The reviewed Sprint 6 SQL was imported exactly once after the production baseline audit. No SQL was re-imported afterward.

- Tables: 19.
- Migration rows: 18.
- Total rows: 61, including one expected Writing smoke submission.
- New table: `writing_submissions`.
- New migration: `2026_09_08_001800_create_writing_submissions_table` (batch 4).
- Pilot prompts: id 1 `Write to a study-group organizer` (Task 1, minimum 120 words, 20 minutes) and id 2 `Balancing study and free time` (Task 2, minimum 250 words, 40 minutes). Both are active, original, and have no topic reference.
- Smoke submission: id 1 references `writing_prompt_id=1`, is `submitted`, contains 130 server-counted words, and preserves the prompt snapshot.

Existing production data was preserved: topics 5, vocabularies 3, vocabulary progress 1, grammar lessons 1, passages 1, listening contents 1, questions 2, question options 5, exercises 1, exercise questions 1, exams 1, exam sections 1, exam section items 1, attempts 8, attempt answers 8, and speaking prompts 0.

The Sprint 6 SQL did not alter existing tables and contained no executable destructive operation, database recreation, or reset.

## Production release

The full release `release-20260908-175957-sprint6-tar.zip` (SHA-256 `544485946360622B5E4850FB1F00A657ED36280BD16CED36760E06D541192DD9`) was uploaded and extracted while preserving `.env` and `storage`. After the first hosted route audit, only the two malformed Manage Writing Blade views were corrected and uploaded individually. The corrective archive used for validation was `release-20260908-183701-sprint6-fix-tar.zip` (SHA-256 `88712BBA314E1B1B096977210B05E579C4C52B54A3B7D79ABAB7C5A623B09CF3`); only `index.blade.php` and `_form.blade.php` were replaced.

File Manager confirmed `/htdocs/.env` remained 642 B and `/htdocs/storage` remained present. No secrets were printed, uploaded, or committed.

## Hosted verification

Passed:

- Writing listing and active pilot prompt visibility;
- Task 1 editor, checklist, draft save, 130-word submission, and snapshot review;
- Manage Writing index, detail, and preview after the targeted view fix;
- Sprint 0–5 regression routes for dashboard, Vocabulary, Grammar, Reading, Listening, Practice, History, Review, mock exams, result, and Manage;
- no horizontal overflow on Writing, Manage Writing, Vocabulary, and Mock Exams at the tested mobile viewport.

Direct `/health` navigation was blocked by the browser client with `ERR_BLOCKED_BY_CLIENT`; it is not claimed as a successful hosted check.

## Git checkpoint

The final Git audit and checkpoint are recorded in the completion response for this task. Sprint 7 remains unopened.
