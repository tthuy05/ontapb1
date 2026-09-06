# Sprint 5 final report — mock-exam composition and flow

Date: 7 September 2026

## Delivered

- Owner-managed configurable objective mock exams using the existing Sprint 3
  `exams`, `exam_sections`, and `exam_section_items` tables.
- Ordered Reading/Listening sections with activation validation, matching skills,
  active dependencies, objective question/option checks, contiguous positions,
  and time limits.
- Learner catalog, detail, section navigation, server deadline handling,
  immutable exam/answer snapshots, raw breakdowns, and an explicit non-official
  result label.
- Submit-time answer persistence fallback so a normal form submit remains
  authoritative when client-side autosave is delayed or unavailable.
- Idempotent local pilot seeder for `B1 objective mock pilot`, referencing the
  existing Sprint 2 Reading question `questions.id=1`.

## Local verification

- Focused Sprint 5: 6 tests, 39 assertions passed.
- Full feature suite: 77 tests, 511 assertions passed.
- PHP lint, Pint, route inventory, view cache, Composer validation/audit,
  Vite build, pnpm production audit, and disposable SQLite migration/seed
  checks passed.

## Production verification

- Database: 18 tables and 17 migration records; no Sprint 5 migration or SQL
  import was required.
- Existing Sprint 0–2 content and vocabulary progress remain present. The
  production counts are: topics 5, vocabularies 3, vocabulary_progress 1,
  grammar_lessons 1, passages 1, listening_contents 1, questions 2,
  question_options 5, exercises 1, exercise_questions 1.
- Sprint 5 pilot: one active exam, one Reading section, one section item, and
  one existing Reading question. No existing content rows were rewritten.
- Production attempt rows increased only from the authorized hosted smoke
  tests; the final pilot attempt was attempt #8 with one correct answer and a
  100% raw result.
- The safe release upload excluded `.env` and production `storage`; the
  production root still contains `.env` (642 B) and `storage`. No remote delete
  was performed. Focused post-upload corrections updated the compiled JS
  manifest/asset and `AttemptController.php` only.
- Health JSON could not be independently opened in the in-app browser because
  the browser client blocked the endpoint; application HTML routes and the
  local health tests passed.

## Hosted smoke and regression

- Sprint 5: dashboard, exam catalog, exam detail, section/take flow, timer,
  submit, result breakdown, answer explanation, and history all passed.
- Final pilot result: attempt #8, `1.00 / 1.00`, `100.00%`, correct 1,
  incorrect 0, unanswered 0.
- Sprint 0–4 routes passed: Vocabulary, Grammar, Reading, Listening, Practice,
  History, Wrong-answer review, Manage, and Dashboard.
- No tested route showed horizontal overflow in the browser check.
- The browser automation did not independently observe the asynchronous
  autosave event; the final normal-submit persistence path was verified through
  the hosted 100% result and is covered by the Sprint 5 feature tests.

## Scope boundary

Sprint 6 and later work was not started.
