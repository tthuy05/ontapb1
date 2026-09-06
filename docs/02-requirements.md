# Requirements

## Assumptions

- One owner uses the application.
- VSTEP.3-5 Level 3 is the target pending approval.
- Internet deployment and persistent history are mandatory outcomes.
- Objective items are automatically scored; Writing and Speaking are self-reviewed in the MVP.

## Functional requirements

### Access and dashboard

- **FR-001** The public deployment shall require the owner to authenticate before accessing study, history, or management data.
- **FR-002** The system shall provide an explicit logout and invalidate the owner session.
- **FR-003** The dashboard shall show recent attempts, wrong answers awaiting review, content totals, and simple skill summaries derived from stored records.
- **FR-004** The dashboard shall not claim meaningful trends until enough completed attempts exist.

### Content and study modules

- **FR-010** The owner shall browse active topics and filter them by study area.
- **FR-011** The owner shall study vocabulary by topic, including term, part of speech, meaning, pronunciation text, example, and notes when present.
- **FR-012** The owner shall record a simple vocabulary state: new, learning, learned, or review.
- **FR-013** The owner shall read grammar lessons with objectives, explanation, examples, common mistakes, and linked practice.
- **FR-014** Reading content shall present an original/licensed passage and its linked questions.
- **FR-015** Listening content shall play an audio asset and keep its transcript hidden until submission or an explicit reveal permitted by the exercise.
- **FR-016** Writing practice shall provide a prompt, guidance, word count, checklist, draft/submission history, and optional model answer reveal.
- **FR-017** Speaking practice shall provide the prompt, preparation/speaking timers, microphone permission handling, local recording, and playback; server recording storage is not required.

### Question bank and composition

- **FR-020** The owner shall create, edit, preview, search, filter, activate, and deactivate questions.
- **FR-021** Each question shall record skill, type, B1 level, difficulty, prompt, explanation, source category, and relevant topic/context.
- **FR-022** The MVP shall score single-choice and true/false items. The data contract shall leave room for multiple-choice and fill-blank items without implementing their scoring prematurely.
- **FR-023** Questions may be standalone or linked to exactly one Reading passage or Listening content record.
- **FR-024** The owner shall assemble ordered exercises from reusable questions and assign point values.
- **FR-025** The owner shall assemble ordered mock-exam sections from reusable objective questions and Writing/Speaking prompts.
- **FR-026** An exam shall be labelled `vstep_simulation` or `general_b1`; neither label implies official material.

### Attempt, answer, and result flow

- **FR-030** Starting an exercise or exam shall create one server-side in-progress attempt and immutable attempt-question snapshots.
- **FR-031** Each retry shall create a new attempt.
- **FR-032** Answer selection shall be saved to the server with version-checked, retry-safe update behavior.
- **FR-033** Refreshing or reopening an in-progress attempt shall restore saved answers and the server-derived remaining time.
- **FR-034** Submission shall be atomic and idempotent.
- **FR-035** The server shall retrieve answer keys, calculate objective results, and never accept client correctness or score values.
- **FR-036** Results shall show score, maximum, percentage, correct/incorrect/unanswered counts, section breakdown, selected answer, correct answer, explanation, and time spent where applicable.
- **FR-037** A submitted, deadline-passed, or explicitly abandoned attempt shall reject normal answer changes.
- **FR-038** The system shall list attempt history and keep each attempt meaningful after live content edits.
- **FR-039** Wrong-answer review shall be derived from submitted attempt answers, grouped by current question where useful, and shall support retrying an appropriate exercise.

### Personal content management and data portability

- **FR-040** `/manage` shall be the owner's content workspace, not an Admin role or separate application.
- **FR-041** Content referenced by history shall be deactivated rather than destructively deleted by default.
- **FR-042** Uploads shall be restricted by allowed MIME type, extension, size, and generated filename.
- **FR-043** The first content seed/import shall be a deliberately small validation batch.
- **FR-044** Manual SQL/database export and application-level JSON export shall be documented; JSON import can follow after the export contract is tested.

## Non-functional requirements

### Usability and accessibility

- **NFR-001** Primary flows shall work at 360 px mobile width and on current desktop browsers.
- **NFR-002** Practice controls shall be keyboard accessible, visibly focused, and labelled; status shall not rely on color alone.
- **NFR-003** Body text shall remain readable without horizontal scrolling; question navigation shall collapse appropriately on mobile.
- **NFR-004** Destructive actions shall require clear confirmation and explain historical-data consequences.

### Performance

- **NFR-010** Normal owner pages should target a warm response under one second on local development; free-host cold starts are documented exceptions.
- **NFR-011** Growing lists shall paginate, initially 20-30 records per page.
- **NFR-012** Controllers shall eager-load displayed relationships and avoid N+1 queries.
- **NFR-013** Audio shall be streamed as files, not loaded into MySQL or embedded in page payloads.

### Reliability and integrity

- **NFR-020** An acknowledged saved answer shall survive refresh and redeploy.
- **NFR-021** Start and submit operations that change several records shall use database transactions.
- **NFR-022** Duplicate submission and stale autosave requests shall not create duplicate scores or reverse a completed state.
- **NFR-023** Database constraints and application validation shall enforce valid relationships and one target per attempt.
- **NFR-024** Normal deployment shall use forward migrations only and shall never run `migrate:fresh` against personal production data.

### Security and privacy

- **NFR-030** Use Laravel CSRF protection, escaped Blade output, parameter binding/Eloquent, server-side validation, secure sessions, and secret environment variables.
- **NFR-031** Correct-answer and explanation data shall not be serialized to the taking page before submission.
- **NFR-032** Public deployment shall protect all private pages with the owner-session middleware and rate-limit login attempts.
- **NFR-033** Production shall use HTTPS, `APP_DEBUG=false`, secure cookies, and no committed secrets.
- **NFR-034** Microphone capture shall require explicit browser permission and shall default to no server upload.

### Maintainability

- **NFR-040** Follow Laravel conventions and keep business rules out of Blade and route closures.
- **NFR-041** Use Form Requests for non-trivial writes; do not create them for simple read actions.
- **NFR-042** Introduce a scoring service because the same isolated rules serve exercises and exams; do not add repositories, CQRS, event sourcing, or speculative interfaces.
- **NFR-043** Keep SQL portable where practical, but optimize for the approved production database.

### Deployment and cost

- **NFR-050** The application shall run within the selected free-plan CPU, RAM, storage, and inactivity constraints.
- **NFR-051** All durable content and history shall live in persistent MySQL/MariaDB; local filesystem persistence shall be used only on a host verified to provide it.
- **NFR-052** The owner shall be able to recover from the last provider backup and from a separate manual export.
- **NFR-053** The approved configuration shall not require a payment card or permit automatic charges by default.

## Acceptance priorities

The acceptance order is: correct standard -> dependable practice loop -> persistent history -> security -> free deployment -> content breadth. Advanced statistics or visual polish cannot compensate for failures in start/save/submit/review.
