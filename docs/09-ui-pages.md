# UI and Page Plan

## Experience principles

The application is a quiet personal study desk, not a gamified social product. It should make the next useful action obvious, protect exam focus, and work well on a small laptop or phone. Bootstrap supplies layout and accessible components; custom CSS should remain small.

Design priorities:

1. readable English content and predictable navigation;
2. visible save/timer state during attempts;
3. low cognitive load and keyboard operability;
4. honest separation between practice feedback and official VSTEP scoring;
5. compact management pages suitable for one owner.

## Sprint 4 shipped UI

The deployed Sprint 3 pages remain the Practice catalog, exercise detail/start page, attempt page, result page, and Manage Exercise views. Sprint 4 adds the result breakdown, dashboard recent-practice/review summary, History filters, and snapshot-based wrong-answer Review. Sprint 5 adds the mock-exam catalog/take/result pages. Sprint 6 adds learner Writing prompts/editor/review and Manage Writing prompt pages. Sprint 7 adds learner Speaking prompt/timer/recorder/review and Manage Speaking prompt pages. The objective take page still withholds correctness and explanations; History and Review use stored snapshots.

## Shared shell

- Top bar: product name, primary learner navigation, Manage link, logout.
- Desktop: constrained content width; optional section sidebar on long attempt/manage pages.
- Mobile: collapsed navigation, sticky bottom attempt actions, no horizontal question layouts.
- Global components: breadcrumbs, flash alerts, validation summary, empty states, pagination, status badges, confirmation modal only for consequential actions.
- Typography: default system sans-serif, approximately 16px body, 1.5 line height; passage text can use an 18px reading mode.
- Color: Bootstrap semantic colors are supplemental; correctness/status always also uses text/icon labels.

## Learner page matrix

| Page | Primary information | Components and actions | Mobile/accessibility notes |
|---|---|---|---|
| Login | Owner sign-in only | Password field, show/hide, submit, generic error | Proper label/autocomplete; no credential hint |
| Dashboard | Continue attempt, recent results, review due, content shortcuts | Summary cards, recent table, quick-start links | Cards stack; headings describe time frame |
| Vocabulary list | Topic, term, part of speech, review state | Search, topic/state filter, pagination, reveal translation | Filter form works without JS |
| Vocabulary review | Due/legacy/new card, topic, term, hidden meaning/example, schedule status | Native-details reveal; Again/Hard/Good/Easy POST buttons | Five-new-card cap; works without JS and exposes text labels |
| Vocabulary detail | Definition, example, pronunciation, notes | Play audio when present; update state | Text alternative for missing audio |
| Grammar list/detail | Syllabus order, lesson, examples, common mistakes | Topic filter, linked practice | Tables reflow; examples use semantic markup |
| Reading/listening library | Active content and difficulty | Filters, content card, linked exercise | Audio uses native controls and transcript policy |
| Practice catalog/detail | Skill/topic, question count, time limit, prior attempts | Start, resume, view history | Start explains feedback/timing before action |
| Attempt | Prompt/media, one or more questions, progress and timer | Answer controls, previous/next, flag, save indicator, submit | Native controls, keyboard order, sticky actions |
| Result | Raw correct/total, percentage, duration, topic breakdown | Review wrong, retry, return to catalog | Never label result an official VSTEP score |
| History | Date, type, title snapshot, result/state | Filters and row links | Responsive table becomes labeled cards |
| Wrong-answer review | Question/prompt snapshot, response, correct answer, explanation | Topic/type filters, link to source attempt | Correctness conveyed with text, not color alone |
| Exam catalog/detail | Sections, published durations, simulation caveats | Start/resume | Prominent irreversible submit warning |
| Writing list/prompt | Task, target length, planning and self-check | Editor, word count, save submission | Textarea retains content; count announced politely |
| Speaking list/prompt | Part, prompt, preparation/response guidance | Start local recording, stop, play/download, notes/rubric | Permission/HTTPS fallback; controls have text labels |

## Attempt-taking page

### Layout

- Header: exercise/exam title snapshot, section name, question progress, server-derived remaining time, save state.
- Main column: passage or player followed by the current question and answer choices.
- Navigator: numbered buttons labeled answered/unanswered/flagged/current; it does not reveal correctness.
- Footer: Previous, Save/Next, and Submit. Submit is visually distinct and requires a summary confirmation.

On Reading desktop layouts, passage and questions may use a two-column split with independent scrolling only after usability testing. The default is a single document flow, especially on mobile. Listening uses native audio controls only when the exercise policy permits playback controls; the server must not claim official replay behavior until verified.

### State messages

- `Saving…`, `Saved at 14:32`, `Offline—changes not saved`, and `This attempt has been submitted` are visible text.
- A timer warning appears at five minutes and one minute without relying only on animation or sound.
- When time reaches zero, inputs lock while the server submission is requested. A failed request offers retry and preserves currently typed data in the page until resolved.
- Stale save conflicts explain that a newer response exists and offer reload; the browser must not silently overwrite it.

### Objective answer controls

- Single choice: radio buttons in a `fieldset` with `legend`.
- True/false: two radios, not a visual-only switch.
- Multi-select (later): checkboxes plus an explicit number-of-selections instruction.
- Fill-in (later): text input with documented normalization and no client-side correctness logic.

## Results and feedback

Exercise results may show correct answer and explanation immediately after submission. Mock-exam feedback may be delayed until the entire exam is submitted. The result hierarchy is:

1. status and honest label such as “Practice result”;
2. raw score and percentage for auto-scored items;
3. time used and completion details;
4. breakdown by section/topic/type;
5. links to incorrect answers.

Writing and Speaking are not auto-scored in the MVP. Their pages show response metadata and a CEFR-informed self-check, explicitly labeled as practice guidance rather than an official rating.

## Management UI

### Shared patterns

- Index: page title, “Create” action, filter/search row, paginated table, status and source/licence badges.
- Form: Basics, Study content, Source/licensing, and Publication sections. Required fields are marked in text.
- Edit pages warn when content has historical attempts: edits affect future attempts only because past attempts use snapshots.
- Delete actions state why a referenced item cannot be removed and offer deactivation.
- Preview pages render the learner view without enabling attempt creation.

### Complex editors

- Question editor changes fields by selected type, but server validation remains authoritative. Correct-option controls cannot save an impossible configuration.
- Exercise composer offers filtered item search, add/remove, drag or button reordering, points, and a validation summary.
- Exam composer nests ordered sections and ordered items. A structural summary checks durations, item counts, skill labels, and unresolved drafts before activation.
- Passage/listening editors attach existing questions rather than duplicating question content.

## Empty, loading, and error states

- Empty libraries explain how to add the first item in Manage.
- Slow save controls are disabled only for the individual request, not the whole page.
- Missing audio displays a textual problem and continues to show any permitted transcript.
- Microphone denial explains how to grant permission and still allows timed speaking practice with notes.
- `404`, `419`, `429`, and `500` pages use plain recovery actions and reveal no secrets.

## Accessibility and responsive acceptance

- Meet WCAG 2.2 AA as the project target: semantic landmarks/headings, visible focus, keyboard completion, sufficient contrast, labels/instructions, and useful error association.
- Do not auto-play audio. Captions/transcripts are available in study mode when licensing/pedagogy permits.
- Respect reduced motion and browser zoom to 200%.
- Validate core pages at roughly 360px, 768px, and desktop widths, plus keyboard-only operation.
- Use browser-native recording/audio behavior with feature detection; never make microphone support the only way to complete a speaking session.

## Browser recording research

The MVP uses `navigator.mediaDevices.getUserMedia()` only in a secure context and the standard `MediaRecorder` API for browser-local capture. It feature-detects the API and selects a format only after checking `MediaRecorder.isTypeSupported()`; it must not hard-code one WebM/MP4 codec for all browsers. Permission denial, missing input device, unsupported codec/API and recording errors all retain the prompt/timer/notes workflow. Media tracks are stopped after recording, and no Blob is posted to the application.

Technical references accessed 29 August 2026:

- W3C MediaStream Recording Working Draft, 16 March 2026: <https://www.w3.org/TR/mediastream-recording/>
- MDN `getUserMedia()` secure-context/permission behavior: <https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia>
- MDN `MediaRecorder.isTypeSupported()`: <https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder/isTypeSupported_static>

## Sprint 7 UI behavior

Speaking pages show the prompt, Part label, preparation/speaking durations, ideas, follow-up questions, and a self-review checklist. The progressive enhancement exposes text-labelled Start, Stop, Play, Download, timer, and status controls. Unsupported or denied microphone paths retain the prompt, timer, notes, and draft actions. The recording is a browser Blob only; the submission form contains duration and self-review metadata, never a file input or audio upload.

## UI deliverable boundary

This is an interaction specification only. No Blade template, CSS, JavaScript, Bootstrap installation, or visual asset is created in Phase 1.
