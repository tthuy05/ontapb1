# Question Engine

## Scope decision

The question engine handles automatically scoreable, reusable objective items. Writing and Speaking use dedicated prompt/submission entities because forcing long responses, recording metadata, and manual evaluation into objective answer options would make the system harder to understand.

## Sprint 3 shipped behavior

The deployed Sprint 3 engine implements the MVP `single_choice`/`true_false` path for Exercises. Starting an exercise snapshots the prompt, option text/keys, correct key, explanation, source context, order, and points into pre-created `attempt_answers`; the take page omits correctness and explanations. Answer saves validate against that snapshot with optimistic `save_version`, and submission scores on the server from the stored snapshot. The hosted pilot successfully saved, survived reload, submitted, and rendered a 100% result with feedback revealed only after submission. Sprint 5 adds the mock-exam flow, and Sprint 6 adds unscored Writing practice with durable prompt/response snapshots. Multiple choice, fill-blank, Speaking, and Writing/Exam official scoring remain future scope.

## Sprint 4 shipped behavior

Result pages group raw points, counts, and percentages by snapshotted skill, topic, and question type. History filters by snapshotted title, skill, and status. Wrong-answer Review derives only submitted incorrect objective answers and renders their stored prompt, options, context, response, correct key, and explanation; live edits cannot change historical feedback. Practice percentages remain explicitly non-official.

## Type roadmap

| Type | Response | Scoring | Release |
|---|---|---|---|
| `single_choice` | one stable option key | full points for exact key | MVP |
| `true_false` | one of two stable option keys | same as single choice | MVP |
| `multiple_choice` | unique set of option keys | all-or-nothing initially | Post-MVP, approval required |
| `fill_blank` | text/list of blanks | normalized exact accepted answers | Post-MVP |
| `short_answer` | text | manual/self-review | Future if useful |
| Writing | dedicated submission | not automatic in MVP | MVP |
| Speaking | dedicated metadata/recording flow | not automatic in MVP | Later MVP |

## Sprint 6 Writing behavior

Writing is intentionally outside the objective scoring engine. The server counts Unicode non-whitespace tokens, validates draft/submitted state, stores a prompt snapshot, and keeps submitted responses immutable. Self-check fields are guidance only; the UI and review pages never present an official VSTEP score.

The database uses a string type and validated JSON response, so adding a type does not require a universal schema. A type is enabled only after its validation, UI, snapshot, scoring, and tests exist.

## Question model

- Queryable common fields live in `questions`: skill, topic, type, level, difficulty, prompt, explanation, status, and source category.
- Choice text and correct flags live in ordered `question_options`.
- Type-specific non-option configuration lives in `answer_config`; for example, future fill-blank accepted values and case/whitespace rules.
- Optional tags/subskills live in `metadata` rather than normalized tables.
- A question is standalone or linked to one Reading passage or one Listening content record, never both.

## Activation validation

A draft cannot become active unless:

1. Skill, type, B1 level, difficulty, source category, prompt, and explanation are valid.
2. Context is active and matches the skill.
3. `single_choice` has at least two options and exactly one correct option.
4. `true_false` has exactly two clearly labelled options and exactly one correct option.
5. Option keys and positions are unique.
6. Source/license details are adequate for the selected `source_type`.
7. The item has been previewed and answer/explanation checked.

## Reading and Listening contexts

- `passage_id` connects a group of Reading questions to one passage.
- `listening_content_id` connects Listening questions to transcript/audio metadata.
- The learner page loads context once and orders its questions through the exercise/exam mapping.
- Listening transcripts remain server-side until the exercise is submitted or an explicit learner reveal is allowed.
- Context is copied into the attempt snapshot. Audio itself is immutable/versioned by path and checksum; binary duplication per attempt is avoided.

## Exercise composition

`exercise_questions` supplies ordered reuse and point values. An exercise is intentionally short, may be untimed, may focus on one topic/type, and supports free review after submission.

## Exam composition

`exams` -> `exam_sections` -> `exam_section_items` supplies explicit section order, skill/time rules, and mixed objective/manual tasks. Objective items reuse the same question table. Writing/Speaking item links use their own prompt tables. Exam configuration is separate because section timing and navigation would make an exercise table ambiguous.

## Exercise vs exam reuse

Shared:

- question records and source metadata;
- attempt state vocabulary;
- attempt-time snapshots;
- answer-save endpoint behavior;
- objective scoring rules;
- result components and history.

Different:

- exercises have one ordered question list and simple timing;
- exams have sections, section navigation/time policies, format labels, and may include manual tasks;
- result UI for exams includes section breakdown and clearly ungraded skills.

This avoids duplicated scoring code without inventing a generic assessment framework.

## Snapshot contract

At attempt start, each objective occurrence creates an `attempt_answers` row with:

```json
{
  "question_snapshot": {
    "type": "single_choice",
    "prompt": "...",
    "options": [{"key": "A", "content": "..."}],
    "correct_keys": ["B"],
    "explanation": "...",
    "skill": "reading",
    "difficulty": 2
  },
  "context_snapshot": {
    "kind": "passage",
    "title": "...",
    "body_or_transcript": "...",
    "audio_path": null,
    "audio_checksum": null
  }
}
```

This is a schema illustration, not application code. Snapshot keys are versioned in `attempts.configuration_snapshot` using `snapshot_version` so future readers can interpret old attempts.

## Correct-answer confidentiality

Correct keys/configuration and explanations exist in the database snapshot but are not serialized to the attempt-taking HTML/JSON. The server selects only prompt, option keys/text, and saved response. Submission uses the stored snapshot, not client fields or the possibly edited live question.

## Scoring rules

- Single choice / true-false: exact valid snapshotted option key earns `max_points`; otherwise zero.
- Unanswered: zero, counted separately.
- Invalid/unknown response at submission: zero and logged as a data-integrity event.
- Multiple choice, if approved: set equality after deduplication; no partial points in its first version.
- Fill blank, if approved: documented normalization only (trim and optional case folding); no semantic/fuzzy matching.
- Writing/Speaking: `ungraded`, never silently treated as zero in skill reporting.
- Totals use decimal stored points. Percentage is `score_awarded / max_score * 100`, rounded consistently after totaling.

## VSTEP score warning

The application can report raw counts, points, and percentages. It must not convert objective raw counts to an official VSTEP 0-10 skill score because an official raw-conversion table has not been verified. It must not report an official overall Level 3 result while Writing/Speaking are self-reviewed. A full-format practice can be a **VSTEP simulation**, not an official score prediction.

## Authoring lifecycle

`draft -> active -> inactive`. Editing wording/explanation may update live content for future attempts. Changing assessed meaning or the correct answer should normally duplicate/version the question and deactivate the old one. Existing attempts remain bound to their snapshots either way.
