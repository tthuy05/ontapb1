# Route Design

## Conventions

- All pages are server-rendered Blade responses. Small `fetch` requests are used only for answer saving and local page enhancements.
- Named routes use `manage.*` for content management, never `admin.*`.
- Every route except login and health is inside `web` plus `owner` middleware.
- Route-model binding is scoped by the parent where a nested identifier could otherwise be confused.
- `POST` creates or starts, `PUT` replaces a saved answer, `PATCH` changes part of a resource/state, and `DELETE` is used only when the deletion policy permits it.
- State-changing requests require CSRF. JSON save endpoints return validation errors and the server save version/time.

## Sprint 4–7 shipped route subset

The deployed route inventory includes the following new learner flow; the other future routes in this design remain unimplemented until their approved sprints.

| Method | URI | Name | Purpose |
|---|---|---|---|
| GET | `/practice` | `practice.index` | Browse active exercises with filters |
| GET | `/practice/{exercise}` | `practice.show` | Show exercise instructions and start action |
| POST | `/practice/{exercise}/attempts` | `practice.attempts.store` | Create a snapshotted exercise attempt |
| GET | `/attempts/{attempt}` | `attempts.show` | Resume/take an in-progress attempt |
| PUT | `/attempts/{attempt}/answers/{attemptAnswer}` | `attempts.answers.update` | Save one validated answer |
| POST | `/attempts/{attempt}/submit` | `attempts.submit` | Finalize and score the attempt |
| GET | `/attempts/{attempt}/result` | `attempts.result` | Render the practice result |

The shipped owner routes include `/history`, `/review/wrong-answers`, snapshot-based wrong-answer detail, the Sprint 5 mock-exam catalog/take/result flow, the Sprint 6 learner/Manage Writing flow, and the Sprint 7 learner/Manage Speaking flow. Speaking recording is browser-local and its server routes accept metadata only.

## Public and session routes

| Method | URI | Name | Action | Protection |
|---|---|---|---|---|
| GET | `/login` | `login` | Show owner sign-in form | Guest; redirect an active session |
| POST | `/login` | `login.store` | Validate the configured owner credential, regenerate session | Guest, CSRF, login throttle |
| POST | `/logout` | `logout` | Invalidate session and regenerate CSRF token | Owner, CSRF |
| GET | `/health` | `health` | Minimal application/DB health response without secrets | Public, throttled; optional deployment token for detailed checks |

The health endpoint must not reveal versions, environment values, paths, stack traces, table names, or credentials.

## Learner routes

### Dashboard and study reference

| Method | URI | Name | Controller action | Purpose |
|---|---|---|---|---|
| GET | `/` | `dashboard` | `DashboardController@index` | Progress summary and continue actions |
| GET | `/vocabulary` | `vocabulary.index` | `VocabularyController@index` | Filter active words by topic/review state |
| GET | `/vocabulary/{vocabulary}` | `vocabulary.show` | `VocabularyController@show` | Word detail and examples |
| PATCH | `/vocabulary/{vocabulary}/progress` | `vocabulary.progress.update` | `VocabularyController@updateProgress` | Mark learning/review state |
| GET | `/grammar` | `grammar.index` | `GrammarController@index` | Active grammar syllabus |
| GET | `/grammar/{grammarLesson}` | `grammar.show` | `GrammarController@show` | Lesson, examples, and linked practice |
| GET | `/reading` | `reading.index` | `ReadingController@index` | Reading content and linked exercises |
| GET | `/reading/{passage}` | `reading.show` | `ReadingController@show` | Passage preview/study view; no answer keys |
| GET | `/listening` | `listening.index` | `ListeningController@index` | Listening content and linked exercises |
| GET | `/listening/{listeningContent}` | `listening.show` | `ListeningController@show` | Player/transcript according to reveal policy |

### Exercises and attempts

| Method | URI | Name | Controller action | Purpose |
|---|---|---|---|---|
| GET | `/practice` | `practice.index` | `PracticeController@index` | Browse active exercises by skill/topic |
| GET | `/practice/{exercise}` | `practice.show` | `PracticeController@show` | Instructions, item count, time limit |
| POST | `/practice/{exercise}/attempts` | `practice.attempts.store` | `AttemptController@storeForExercise` | Create an immutable exercise attempt snapshot |
| GET | `/attempts/{attempt}` | `attempts.show` | `AttemptController@show` | Resume/take a valid attempt |
| PUT | `/attempts/{attempt}/answers/{attemptAnswer}` | `attempts.answers.update` | `AttemptController@updateAnswer` | Validate and save one objective answer |
| POST | `/attempts/{attempt}/submit` | `attempts.submit` | `AttemptController@submit` | Idempotently finalize and score |
| GET | `/attempts/{attempt}/result` | `attempts.result` | `ResultController@show` | Result summary and allowed feedback |
| GET | `/history` | `history.index` | `HistoryController@index` | Paginated attempt history and filters |
| GET | `/review/wrong-answers` | `review.wrong.index` | `ReviewController@index` | Derive submitted incorrect answers |
| GET | `/review/wrong-answers/{attemptAnswer}` | `review.wrong.show` | `ReviewController@show` | Snapshot-based explanation and context |

An `attemptAnswer` must belong to the route's attempt. Saves are rejected when the attempt is submitted, past `expires_at`, or its expected save version is stale.

### Mock exams

| Method | URI | Name | Controller action | Purpose |
|---|---|---|---|---|
| GET | `/exams` | `exams.index` | `ExamController@index` | Browse active mock exams |
| GET | `/exams/{exam}` | `exams.show` | `ExamController@show` | Exam structure, rules, duration |
| POST | `/exams/{exam}/attempts` | `exams.attempts.store` | `AttemptController@storeForExam` | Create one full exam snapshot |
| GET | `/attempts/{attempt}/sections/{examSection}` | `attempts.sections.show` | `AttemptController@showSection` | Render current snapshotted exam section |
| PUT | `/attempts/{attempt}/writing/{examSectionItem}` | `attempts.writing.update` | `AttemptController@updateWriting` | Version-save the pre-created exam Writing response/self-check |
| PUT | `/attempts/{attempt}/speaking/{examSectionItem}` | `attempts.speaking.update` | `AttemptController@updateSpeaking` | Version-save the pre-created Speaking notes/metadata; no audio |

The generic objective-answer save/submit/result routes handle the objective parts of mock exams; the two manual-item routes preserve Writing/Speaking drafts before overall submission. Section/item access validates that each record belongs to the attempt's snapshotted exam and that navigation is permitted.

### Writing and speaking practice

| Method | URI | Name | Controller action | Purpose |
|---|---|---|---|---|
| GET | `/writing` | `writing.index` | `WritingController@index` | Browse active prompts |
| GET | `/writing/{writingPrompt}` | `writing.show` | `WritingController@show` | Prompt, planning guide, self-check rubric |
| POST | `/writing/{writingPrompt}/submissions` | `writing.submissions.store` | `WritingController@store` | Save a response and prompt snapshot |
| GET | `/writing/submissions/{writingSubmission}` | `writing.submissions.show` | `WritingController@showSubmission` | Review saved response/self-assessment |
| PATCH | `/writing/submissions/{writingSubmission}` | `writing.submissions.update` | `WritingController@updateSubmission` | Update notes/self-assessment while editable |
| GET | `/speaking` | `speaking.index` | `SpeakingController@index` | Browse active prompts |
| GET | `/speaking/{speakingPrompt}` | `speaking.show` | `SpeakingController@show` | Prompt, preparation timer, local recorder |
| POST | `/speaking/{speakingPrompt}/submissions` | `speaking.submissions.store` | `SpeakingController@store` | Save prompt snapshot and written self-review |
| GET | `/speaking/submissions/{speakingSubmission}` | `speaking.submissions.show` | `SpeakingController@showSubmission` | Review notes/metadata; recording remains local |
| PATCH | `/speaking/submissions/{speakingSubmission}` | `speaking.submissions.update` | `SpeakingController@updateSubmission` | Update self-assessment while editable |

The MVP does not upload speaking audio. Browser recording is held in memory or downloaded deliberately by the owner; the server stores only optional duration, notes, and rubric selections.

## Content-management routes

All routes below use prefix `/manage`, name prefix `manage.`, owner middleware, CSRF on writes, validation, and database constraints. Index pages support pagination, search, status, topic, skill, and source filters where relevant.

| Resource key / base URI | Controller | Special actions/rules |
|---|---|---|
| Dashboard — `/manage` | `Manage\DashboardController@index` | `GET`, name `manage.dashboard`; draft/active counts and content warnings |
| `topics` — `/manage/topics` | `Manage\TopicController` | Restrict deletion while referenced; learner `show` is unnecessary |
| `vocabulary` — `/manage/vocabulary` | `Manage\VocabularyController` | Validate attribution and optional pronunciation-audio path |
| `grammar` — `/manage/grammar` | `Manage\GrammarLessonController` | Preview sanitized lesson content |
| `reading` — `/manage/reading` | `Manage\PassageController` | Calculate word count and require source metadata |
| `listening` — `/manage/listening` | `Manage\ListeningContentController` | Validate audio reference, transcript and licensing |
| `questions` — `/manage/questions` | `Manage\QuestionController` | Type-specific options and correctness rules |
| `exercises` — `/manage/exercises` | `Manage\ExerciseController` | Ordered objective-item composer and preview |
| `exams` — `/manage/exams` | `Manage\ExamController` | Section/heterogeneous-item composer, validation and preview |
| `writing` — `/manage/writing` | `Manage\WritingPromptController` | Task, word target and self-check guidance |
| `speaking` — `/manage/speaking` | `Manage\SpeakingPromptController` | Part and preparation/response guidance |

Except the dashboard and noted Topics detail omission, every resource key expands to the following explicit routes. `{base}` is its base URI above, `{resource}` is its singular route-model-bound identifier, and `{key}` is the resource key used in the name.

| Method | URI | Name | Controller action | Purpose/security note |
|---|---|---|---|---|
| GET | `{base}` | `manage.{key}.index` | `index` | Protected paginated/filterable list |
| GET | `{base}/create` | `manage.{key}.create` | `create` | Protected creation form |
| POST | `{base}` | `manage.{key}.store` | `store` | CSRF + resource Form Request; create draft |
| GET | `{base}/{resource}` | `manage.{key}.show` | `show` | Protected detail/learner-style preview data |
| GET | `{base}/{resource}/edit` | `manage.{key}.edit` | `edit` | Protected edit form and history warning |
| PUT | `{base}/{resource}` | `manage.{key}.update` | `update` | CSRF + resource Form Request; transactional when relational |
| DELETE | `{base}/{resource}` | `manage.{key}.destroy` | `destroy` | CSRF; only unreferenced drafts, otherwise reject/deactivate |
| PATCH | `{base}/{resource}/status` | `manage.{key}.status.update` | `updateStatus` | Validate deliberate `draft`/`active`/`inactive` transition |

Exercise and exam previews add `GET {base}/{resource}/preview` named `manage.{key}.preview`. Their complete-order updates add `PATCH {base}/{resource}/items/order` named `manage.{key}.items.order.update`; the exam variant validates nested section/item membership and both run in a transaction. Topics omit only `show`; their other standard actions and status route remain.

## Response and error policy

- Browser form success redirects with a short flash message; validation returns old input and field errors.
- Answer saves return JSON with `saved_at`, incremented `save_version`, and normalized response only; never the correct answer.
- `404` covers missing/inaccessible resources, `409` stale save versions or finalized attempts, `422` validation, and `429` throttling.
- Expired attempts redirect to submission/result when expiry finalization succeeds; otherwise show a recoverable error with no editable controls.
- Production error pages disclose no implementation detail and retain a correlation/request ID in logs.

## Route acceptance checks

- Every protected route fails closed without an owner session.
- Every write route rejects missing/invalid CSRF.
- Nested items cannot be accessed through another parent ID.
- Answer keys are absent from take-page HTML and save responses.
- Inactive resources cannot start new attempts but remain visible through historical snapshots.
- Route names are used in Blade instead of hard-coded URLs.
