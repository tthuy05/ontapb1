# Use Cases

## Actor

**Owner / Learner** is the only application actor. The hosting provider and browser are supporting systems, not application roles.

## Use-case inventory

| ID | Name | MVP |
|---|---|---:|
| UC-01 | Sign in and sign out | Yes, before public launch |
| UC-02 | View dashboard | Yes |
| UC-03 | Study vocabulary or grammar | Yes |
| UC-04 | Start or resume practice | Yes |
| UC-05 | Save or change an answer | Yes |
| UC-06 | Submit and review a result | Yes |
| UC-07 | Review a wrong answer and retry | Yes |
| UC-08 | Take a mock exam | After practice engine |
| UC-09 | Practice Writing | Later MVP |
| UC-10 | Practice Speaking with local recording | Later MVP |
| UC-11 | Manage personal content | Yes |
| UC-12 | Export or restore personal data | Manual in MVP; UI later |

## UC-01 - Sign in and sign out

- **Actor:** Owner / Learner
- **Goal:** Prevent public access to personal study data.
- **Preconditions:** Production owner-password hash and session configuration exist.
- **Trigger:** Owner opens a protected URL without a valid session.
- **Main flow:** The application redirects to `/login`; owner submits the password; server rate-limits, checks the environment-stored hash, regenerates the session ID, and redirects to the intended page; owner later posts `/logout`; the session is invalidated and CSRF token regenerated.
- **Alternative flow:** An already authenticated owner proceeds directly.
- **Exceptions:** Invalid password returns a generic error; repeated failures are throttled; unavailable session storage yields a safe error rather than bypass.
- **Postconditions:** A valid owner session exists, or the protected content remains inaccessible.

## UC-02 - View dashboard

- **Actor:** Owner / Learner
- **Goal:** Select the next useful study action without interpreting noisy analytics.
- **Preconditions:** Owner is authenticated; content/history may be empty.
- **Trigger:** Owner opens `/`.
- **Main flow:** System shows any resumable attempt, recent completed attempts, wrong-answer count, vocabulary review state, active content totals, and direct study/practice links.
- **Alternative flow:** With too little history, the page shows neutral totals and a first-study recommendation instead of a trend claim.
- **Exceptions:** A failed summary query produces a safe recoverable page and correlation ID, not partial private data or debug output.
- **Postconditions:** No data changes; owner chooses a study, practice, result, or Manage page.

## UC-03 - Study vocabulary or grammar

- **Actor:** Owner / Learner
- **Goal:** Learn a small topic-focused language unit and connect it to practice.
- **Preconditions:** At least one active vocabulary entry or grammar lesson exists.
- **Trigger:** Owner opens a study list and selects a topic/item.
- **Main flow:** Owner filters by topic/state, studies definitions/examples or explanation/common mistakes, uses approved pronunciation audio when present, updates vocabulary review state, and opens linked practice.
- **Alternative flow:** Empty filters explain how to clear filters or add content through Manage.
- **Exceptions:** Missing audio is reported without breaking textual study content; inactive records remain absent from normal learner browsing.
- **Postconditions:** Optional vocabulary progress changes are saved; reference content itself is unchanged.

## UC-04 - Start or resume practice

- **Actor:** Owner / Learner
- **Goal:** Obtain a stable, recoverable copy of an exercise for one attempt.
- **Preconditions:** Owner is authenticated; exercise is active and has at least one active supported question.
- **Trigger:** Owner selects **Start** or opens an unfinished attempt.
- **Main flow:** Server validates the exercise; inside one transaction it creates the attempt, derives the deadline, copies ordered question/context/answer-key snapshots into attempt-answer rows, and commits; server renders prompts without answer keys; existing saved responses appear when resuming.
- **Alternative flow:** If a resumable attempt exists, UI offers Resume or Start New; Start New creates a separate attempt.
- **Exceptions:** Empty/inactive exercise is rejected; concurrent duplicate starts may create distinct intentional attempts but never a half-built attempt.
- **Postconditions:** A complete `in_progress` attempt exists with an authoritative start time and snapshot.

## UC-05 - Save or change an answer

- **Actor:** Owner / Learner
- **Goal:** Preserve work during an unfinished attempt.
- **Preconditions:** Attempt is owned implicitly by the single owner, `in_progress`, and not past its server deadline.
- **Trigger:** Answer selection changes, focus leaves a text field, or a lightweight periodic save occurs.
- **Main flow:** Client sends only attempt-answer ID, response, and a client request token/version; server validates response shape against the snapshotted type/options; server updates the response and `answered_at`; client displays saved time.
- **Alternative flow:** A later selection replaces the prior response.
- **Exceptions:** Offline request remains visibly unsaved and retries; a stale version returns a conflict while a retried already-accepted request is safe; submitted/deadline-passed attempt returns a conflict response.
- **Postconditions:** The latest accepted response is durable and recoverable.

## UC-06 - Submit and review a result

- **Actor:** Owner / Learner
- **Goal:** Finalize the attempt and understand performance.
- **Preconditions:** Attempt is in progress; unanswered questions are allowed but confirmed.
- **Trigger:** Owner confirms Submit or server deadline is reached.
- **Main flow:** Server locks/re-reads the attempt in a transaction; if still in progress, it evaluates snapshot answer keys, records correctness and awarded points, calculates totals and counts, marks `submitted`, and records time; result page reveals answers and explanations.
- **Alternative flow:** A deadline-passed attempt is finalized using only responses received by the deadline and records completion reason `deadline`; duplicate submission returns the already stored result.
- **Exceptions:** Scoring failure rolls back the entire finalization; no partial completed result is exposed.
- **Postconditions:** The attempt is immutable for ordinary answer writes and appears in history/wrong-answer review.

## UC-07 - Review a wrong answer and retry

- **Actor:** Owner / Learner
- **Goal:** Revisit recurring weak points and practice again.
- **Preconditions:** At least one submitted objective answer is incorrect.
- **Trigger:** Owner opens `/review/wrong-answers`.
- **Main flow:** System groups submitted incorrect attempt answers by live question while retaining each historical occurrence; owner filters by skill/topic, inspects chosen/correct answer and explanation, opens the source exercise, and starts a fresh attempt.
- **Alternative flow:** If live content is inactive, the historical snapshot remains viewable but cannot start a new attempt until content is reactivated or copied.
- **Exceptions:** A deleted external audio file is reported; its transcript snapshot remains available where licensed.
- **Postconditions:** Review is recorded implicitly by access in MVP or explicitly later; retry is always a new attempt.

## UC-08 - Take a mock exam

- **Actor:** Owner / Learner
- **Goal:** Practice an ordered, timed set of VSTEP-like sections.
- **Preconditions:** Active exam has valid ordered sections/questions; format label is visible.
- **Trigger:** Owner starts the exam.
- **Main flow:** Server snapshots exam configuration and questions; UI shows section navigation, answer status, and server-derived countdown; answers autosave; submission scores objective sections and presents ungraded Writing/Speaking status separately.
- **Alternative flow:** A general B1 test follows the same engine but is never presented as a VSTEP simulation.
- **Exceptions:** Browser clock changes do not affect deadline; late requests are rejected/finalized according to server time.
- **Postconditions:** Completed exam attempt and section breakdown are stored.

## UC-09 - Practice Writing

- **Actor:** Owner / Learner
- **Goal:** Plan, draft, and self-check a VSTEP-style writing response.
- **Preconditions:** Active prompt with task instructions and checklist exists.
- **Trigger:** Owner opens a prompt and starts a draft.
- **Main flow:** Owner reviews task analysis, writes, sees a word count, saves a draft, completes a checklist, submits, and may reveal a model response afterward.
- **Alternative flow:** Owner resumes a draft or creates a new submission for the same prompt.
- **Exceptions:** AI grading is unavailable by design; model answers are instructional, not official scoring.
- **Postconditions:** Draft/submission text, word count, timestamps, and self-check are stored.

## UC-10 - Practice Speaking with local recording

- **Actor:** Owner / Learner
- **Goal:** Rehearse preparation and delivery without creating storage cost or privacy exposure.
- **Preconditions:** HTTPS or localhost, compatible browser, microphone, and active prompt.
- **Trigger:** Owner selects Record.
- **Main flow:** Browser requests microphone permission; UI selects a supported MIME format, counts preparation and speaking time, records into a local Blob, stops tracks, and provides playback/download; optional self-assessment is saved without audio.
- **Alternative flow:** Owner practices with timers only or downloads the file locally.
- **Exceptions:** Permission denial, missing device, unsupported API, or recording error produces a useful fallback; no upload occurs silently.
- **Postconditions:** Recording remains client-side unless the owner explicitly downloads it; the server may retain only practice metadata/reflection.

## UC-11 - Manage personal content

- **Actor:** Owner / Learner
- **Goal:** Maintain a small, trustworthy personal question bank and syllabus.
- **Preconditions:** Owner session is valid.
- **Trigger:** Owner opens `/manage`.
- **Main flow:** Owner filters content, creates/edits a record, enters source/license details, previews learner rendering, validates, and activates it; referenced content is deactivated rather than deleted.
- **Alternative flow:** Draft content can remain inactive; owner can duplicate an old question before changing its assessed meaning.
- **Exceptions:** Invalid relationships, duplicate positions, unsafe files, or deletion with history are rejected with specific errors.
- **Postconditions:** A valid content version is available or a draft remains isolated from practice.

## UC-12 - Export or restore personal data

- **Actor:** Owner / Learner
- **Goal:** Recover from host or operator failure.
- **Preconditions:** Database credentials/hosting backup access are available.
- **Trigger:** Scheduled monthly backup or recovery need.
- **Main flow:** Owner downloads a database dump and a file archive to a separate device; after restoration to an empty compatible environment, migrations are applied only as required and smoke tests verify counts/history/assets.
- **Alternative flow:** A future JSON export restores content/history through validated import.
- **Exceptions:** Restore to a non-empty database requires an explicit merge plan; never overwrite production casually.
- **Postconditions:** A separately stored, tested recovery point exists.
