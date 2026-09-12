# Sprint 7 final report — speaking practice

Status: complete. Sprint 8 was not started.

## Scope delivered

Sprint 7 adds timed Speaking practice for the private owner application:

- three owner-managed, original B1/VSTEP-aligned prompts;
- prompt provenance, status filtering, validation, preview, and safe draft deletion;
- learner prompt list, part filter, preparation timer, speaking timer, local MediaRecorder controls, playback, and local download;
- self-review metadata, notes, optimistic save versions, prompt snapshots, immutable submitted reviews, and submission history;
- metadata-only persistence: no audio upload endpoint and no server-side audio file.

## Exact production database change

The reviewed SQL file is database/infinityfree/sprint-7-update.sql. It was imported exactly once after a read-only audit confirmed the Sprint 6 baseline. The only new production table is speaking_submissions. The only new migration record is:

2026_09_08_001900_create_speaking_submissions_table

It was recorded in batch 5. The final schema has:

- speaking_submissions.speaking_prompt_id -> speaking_prompts.id, ON DELETE RESTRICT;
- speaking_submissions.attempt_id -> attempts.id, ON DELETE RESTRICT;
- speaking_submissions.exam_section_item_id -> exam_section_items.id, ON DELETE RESTRICT;
- unique (attempt_id, exam_section_item_id);
- indexes on (speaking_prompt_id, status), (attempt_id, status), and (exam_section_item_id, status).

The SQL has no executable ALTER TABLE, DROP, TRUNCATE, destructive DELETE, database recreation, reset, or migrate:fresh equivalent. It does not alter existing Sprint 0–6 tables.

## Pilot data

The exact original active prompts are:

1. Describe your daily study routine — social_interaction, 5-second preparation, 45-second speaking.
2. Choose a useful study activity — solution_discussion, 60-second preparation, 120-second speaking.
3. Talk about a helpful learning habit — topic_development, 60-second preparation, 120-second speaking.

All three have topic_id NULL, source_type original, and the source note Original synthetic Sprint 7 pilot prompt. They reference no existing Sprint 2 content. The hosted smoke review references existing speaking prompt id 1.

## Data preservation

The audited Sprint 0–6 baseline was preserved: topics 5, vocabularies 3, vocabulary progress 1, grammar lessons 1, passages 1, listening contents 1, questions 2, question options 5, exercises 1, exercise questions 1, exams 1, exam sections 1, exam section items 1, attempts 8, attempt answers 8, writing prompts 2, and writing submissions 1.

No production table was dropped, truncated, recreated, or reset. The production .env and runtime storage were preserved. The release archive intentionally contained neither.

## Local verification

Passed:

- focused Sprint 7 tests: 7 tests, 43 assertions;
- complete Laravel suite: 91 tests, 592 assertions;
- disposable SQLite migration and seed rehearsal;
- PHP lint, targeted Pint, Composer validation and audit;
- package audit, JavaScript syntax check, Vite production build, view cache, and route inventory;
- release build and secret/artifact exclusion audit.

## Production release

The final release archive was release-20260908-224359-sprint7-final.zip with SHA-256:

67746ABF982C0F0E6F991AE2C7ECDAB30B80DA40D14CDC8C18DFB1F589EA315C

File Manager extraction preserved the existing .env and storage. No secret values were printed, uploaded, or committed.

## Hosted verification

Passed:

- Speaking list and part filter;
- all three learner prompt pages;
- Manage Speaking list, detail, preview, create, and edit;
- preparation and speaking timers;
- local recorder controls, playback, and download wiring;
- denied-microphone fallback;
- submitted review, prompt snapshot, self-review metadata, and history;
- one submission shown for prompt 1 and zero for prompts 2 and 3;
- Sprint 0–6 regression route matrix;
- protected paths, security headers, secure HTTPS navigation, and the tested mobile viewport;
- no console errors or horizontal overflow in the tested routes.

The hosted recorder verification used a synthetic browser audio stream solely to test MediaRecorder wiring; no user audio was captured or uploaded. The real permission-denied fallback was also verified. Direct /health navigation was blocked by the browser client with ERR_BLOCKED_BY_CLIENT, so it is recorded as an edge-path limitation rather than claimed as a new Sprint 7 pass.

## Final state and checkpoint

The post-import database state was 20 tables and 19 migration rows. The hosted smoke created exactly one metadata-only submitted Speaking review, shown by the owner-facing Manage Speaking and review pages; no other production write followed. The corresponding final row expectation is one speaking_submissions row and 66 total rows across the audited tables.

The final Git checkpoint is recorded after the local secret audit and push:

- branch: main;
- commit: to be recorded in the completion response;
- remote: origin;
- Sprint 8: not started.
