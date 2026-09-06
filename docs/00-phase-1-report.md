# Phase 1 Research, Planning, and Technical Design

This is the controlling Phase 1 summary. Detailed specifications are linked in Section AM. Research and plan status: 30 August 2026.

## Delivery status after Phase 1

The Phase 1 design has since been implemented through Sprint 3. The InfinityFree production database now contains the Sprint 0–2 content plus the Sprint 3 assessment dependency block: 18 tables and 17 unique migration records. The shipped learner subset is the Exercise/Attempt/Result vertical; Writing, Speaking, and Exam tables exist for dependency order but remain empty until their approved later sprints. Sprint 3 is production-verified; Sprint 4 has not been started.

# A. Executive Summary

Build a private, single-owner, server-rendered Laravel study application for Vietnam's VSTEP.3-5 Level 3/B1 target. The MVP combines owner-curated vocabulary/grammar/reference material, objective Reading/Listening practice, configurable VSTEP-style mock exams, Writing practice, browser-local Speaking rehearsal, durable history and wrong-answer review.

Recommended implementation after approval: Laravel 13, PHP 8.3, Blade, Bootstrap, Eloquent and MariaDB/MySQL as one conventional monolith. Recommended free production path: InfinityFree Free, which currently supplies PHP 8.3, MySQL/MariaDB, HTTPS, FTP/File Manager and fixed `htdocs` hosting. Dependencies and assets are built locally because Free Hosting has no SSH, Composer, Node/npm or Artisan. Render Free + Neon Free PostgreSQL is a documented fallback, not the default; alwaysdata is retired from the active target.

The most important integrity decisions are server-side scoring/timing, immutable attempt snapshots, versioned saves, deactivation instead of destructive deletion, no answer keys before submission, and no invented official VSTEP score conversion. Phase 1 produces documentation only.

# B. Single-User Architecture Decision

- One owner uses the application; there is no registration, user directory, roles, permissions, tenant isolation, RBAC or `user_id`.
- A simple configured owner credential protects every study/manage/data route when deployed publicly. Login/health are the only public endpoints.
- The management area is named **Manage**, not Admin, because it is owner content curation rather than a role hierarchy.
- If multi-user scope is ever approved, it requires a new threat model, ownership schema and migration; the current design must not be stretched into it.

# C. B1 Research Findings

The target is the Vietnamese six-level framework's Level 3 through **VSTEP.3-5**, not Cambridge B1 Preliminary. The official test has four separately reported skills and a composite band. CEFR B1 describes an independent learner who can handle clear, familiar everyday/study/work material; understand straightforward factual texts; communicate with some confidence; and produce connected, reasonably controlled spoken/written language.

Content should emphasize high-frequency language, chunks/collocations, clear standard speech, familiar-to-general topics, evidence-based comprehension, connected production, reasons/examples, repair/paraphrase and control of frequent grammar. B1 does not mean flawless grammar or native accent. Detailed findings and editorial syllabi are in [Document 14](14-study-content-research.md).

# D. Verified Exam Structure

| Skill | Format verified from Decision 729/QĐ-BGDĐT |
|---|---|
| Listening | About 40 minutes; 3 parts; 35 multiple-choice questions |
| Reading | 60 minutes; 4 passages; 40 multiple-choice questions; about 1,900–2,050 total words in the Decision |
| Writing | 60 minutes; Task 1 letter/email about 120 words and one-third weight; Task 2 essay about 250 words and two-thirds weight |
| Speaking | About 12 minutes; social interaction, solution discussion/choice, topic development/follow-up |

Published reporting uses 0–10 per skill rounded to 0.5 and a similarly rounded four-skill mean: 4.0–5.5 Level 3, 6.0–8.0 Level 4, 8.5–10 Level 5, below 4.0 no level. No authoritative public raw-correct-count conversion was verified, so the app reports objective practice points/count/percentage only.

The ULIS format page currently says Reading totals 1,900–2,500 words, conflicting with the governing Decision. Strict simulations use 1,900–2,050 pending confirmation. Playback/replay, exact speaking preparation, note/navigation rules, official analytic rubrics and provider-interface details remain explicitly unverified/provider-specific.

# E. Source Evaluation

Primary sources used:

- Decision 729/QĐ-BGDĐT (11 March 2015): controlling published VSTEP.3-5 format and bands.
- Ministry Circular 09/2026/TT-BGDĐT and its official 2026 summary: current test-organization governance, not a new score-conversion specification.
- ULIS VSTEP current format/score pages, 2026 computer-test notice and candidate guide: authoritative provider evidence, with provider/date limitations.
- Council of Europe CEFR Companion Volume 2020 and descriptor pages: primary B1 framework, not a VSTEP syllabus.
- Official Laravel, InfinityFree, Render, Neon, W3C and MDN documentation for current technical/deployment facts.

Commercial coaching pages, copied tests, social posts and unsourced conversion charts are not treated as authority. Full URLs, access date, claim use, reliability and limitations are recorded in [Document 14](14-study-content-research.md) and [Document 15](15-deployment-plan.md).

# F. CEFR B1 Findings

- Listening: main points and relevant detail in clear standard speech on familiar everyday/study/work topics.
- Reading: satisfactory understanding of straightforward factual texts in familiar/interest-related areas.
- Spoken interaction: manage familiar routine/non-routine exchanges, travel situations, information checking and opinions/reasons.
- Spoken production: sustain a straightforward connected description with reasonable fluency.
- Writing: produce straightforward connected familiar-topic text using a linear sequence of linked elements.
- Vocabulary: a good familiar-topic range with circumlocution; elementary vocabulary mostly controlled.
- Grammar: reasonable accuracy in familiar contexts; errors remain but intended meaning is usually clear.

These descriptors inform curriculum and self-review; they do not provide official VSTEP examiner scoring.

# G. Study Syllabus

Vocabulary: 16 editorial topic groups and about 1,030 eventual high-value words/chunks before deduplication, with an MVP pilot of 250–350. High priorities include personal life, daily routine, education, work, travel, health, shopping/services, technology, environment, community, problem-solving/opinion and linking/paraphrase language.

Grammar: 20 sequenced units covering sentence/questions, core present/past/perfect/future, modals, articles/quantifiers, reference, comparison, adverbs/prepositions, gerund/infinitive, relative clauses, conditionals, passive, reported speech, conjunction/complex clauses, past habits, degree/consequence and editing.

Reading progresses from detail/scan to gist, context vocabulary, reference, attitude/organization, inference and full timed sets. Listening progresses from short gist/detail through conversations, talks, attitude/paraphrase, note selection and full sets. Writing alternates correspondence and essays. Speaking rotates social interaction, solution choice and topic development. Exact topic objectives, counts, subcategories and exercises are in Document 14.

# H. Content Strategy

- Create original content by default; use public-domain/open-licensed content only after exact rights and attribution review.
- Treat official documents as format references, not an item bank. Do not copy past papers, commercial textbooks, broadcasts, provider branding or paid prep material.
- Activate through a checklist: draft → content review → answer/rationale review → source/licence review → active.
- Store skill/topic/type/editorial difficulty/objective/source/license/reviewer/explanation metadata.
- Separate learning mode (feedback, transcript, retries) from simulation mode (timed, feedback after submission).
- Pilot a small balanced bank before expansion; quality, explanations and durable rights matter more than item count.
- Integrate vocabulary/grammar into all four skills and use performance/error review to select next practice.

# I. Scope

## In Scope

- Owner login/logout and protected public deployment.
- Dashboard; vocabulary, grammar, Reading and Listening reference/practice.
- Reusable objective question bank with MVP single-choice and true/false.
- Exercises, configurable exams/sections/items, durable start/save/resume/submit/result.
- Raw points/count/percentage, topic/type breakdown, history and wrong-answer review.
- Writing prompts/responses/self-review without automatic official scoring.
- Speaking prompts, timers, local browser recording/play/download and saved textual self-review; no upload.
- Owner Manage CRUD, status/preview/composition/source licensing.
- Responsive accessible Blade/Bootstrap UI, security, tests, exports/backups and free deployment.

## Out of Scope

- Application code or infrastructure mutation in Phase 1.
- Multiple users, roles, permissions, admin/user split, public registration, complex auth.
- AI tutor/grading/generation, cloud speaking recording/transcription.
- Social/chat/collaboration/leaderboards, payments/subscriptions, adaptive recommendation.
- Native mobile app, push notifications, public API.
- Microservices, Kubernetes, Redis, WebSockets, queues/caches without measured need, enterprise monitoring.
- Unlicensed/scraped/leaked content or fabricated official score/band claims.

## Future Scope

Evidence-triggered candidates include spaced review, extra question types, validated imports, deeper analytics, PWA/offline reference, local recording history and a hosting migration. AI, cloud audio, multi-user and public sharing require separate design/security approval. See [Document 13](13-future-features.md).

# J. Functional Requirements

The owner can authenticate; manage and activate all curriculum resources; browse/filter study content; start/resume timed and untimed exercises/exams; save an answer safely; submit idempotently; see honest results/history/wrong answers; write and self-review responses; rehearse/locally record Speaking; manage prompt/audio/source metadata; preview before activation; export/restore data; and deactivate referenced content without corrupting history.

Detailed IDs, rules, acceptance notes and priorities are in [Document 02](02-requirements.md).

# K. Non-Functional Requirements

- Data integrity: transactions, constraints, immutable snapshots, idempotent submission and restoreable exports.
- Security/privacy: whole-site owner gate, CSRF, validation, escaped/sanitized output, secure cookies, no server speaking audio, no secret/answer leakage.
- Usability/accessibility: responsive at phone/tablet/desktop, keyboard-completable, WCAG 2.2 AA target, visible save/timer/error state, no autoplay.
- Performance: paginated/eager-loaded lists, full-attempt operations viable under 256 MB primary-host RAM, measured query/payload/media sizes.
- Reliability: refresh-safe saved work, server-authoritative deadline, deploy/backup/restore runbooks.
- Maintainability: standard Laravel conventions, minimal dependencies/abstractions, versioned snapshot schemas and documented decisions.
- Portability/cost: MySQL primary with a tested PostgreSQL fallback path; no automatic paid resource use.

# L. Module Design

| Module | Responsibility |
|---|---|
| Session/security | Owner login/logout, middleware, CSRF/throttling/headers |
| Dashboard/history | Continue actions, recent results, progress views |
| Curriculum reference | Topics, vocabulary/progress, grammar, passage/listening libraries |
| Question bank | Type-aware questions/options, sources and explanations |
| Practice | Exercise catalog/composition and reusable objective attempt flow |
| Exam | Configurable exam → ordered sections → ordered heterogeneous items |
| Attempt/results | Snapshot, versioned answer, timing, submission, scoring, review |
| Writing | Prompts, response snapshots/text and non-official self-check |
| Speaking | Prompts, local recording/timing and metadata/self-review |
| Manage | Personal content CRUD, filters, status, preview and ordering |
| Export/operations | Backup/export/restore, health, deployment checks |

# M. Use Cases

Critical use cases are: sign in/out; study/filter a vocabulary or grammar topic; begin/resume/save/submit an exercise; handle timeout or stale save; review a result/wrong answer; start/navigate/submit a mock; write/save/self-assess; grant/deny microphone and record locally; curate/activate/deactivate/preview resources; compose an exercise/exam; and export/restore history. Preconditions, main flows, alternatives and postconditions are specified in [Document 03](03-use-cases.md).

# N. Database Design

Nineteen domain tables, no `users` table:

- Curriculum: `topics`, `vocabularies`, `vocabulary_progress`, `grammar_lessons`, `passages`, `listening_contents`.
- Question/practice: `questions`, `question_options`, `exercises`, `exercise_questions`.
- Exam: `exams`, `exam_sections`, `exam_section_items`.
- Attempt: `attempts`, `attempt_answers`.
- Production: `writing_prompts`, `writing_submissions`, `speaking_prompts`, `speaking_submissions`.

Foreign keys/indexes/unique rules and application checks enforce valid parents/order/state. Queryable metadata is relational; versioned snapshot/optional editorial metadata may use JSON. Historical records restrict destructive deletes. Full columns, indexes, JSON schemas and deletion policy are in [Document 05](05-database-design.md).

# O. ERD

The complete Mermaid ERD is in [Document 05](05-database-design.md). Its central flow is:

```text
topics -> curriculum/questions -> exercises -> exercise_questions
                              \-> exams -> exam_sections -> exam_section_items

exercise OR exam -> attempts -> attempt_answers

writing_prompts -> writing_submissions
speaking_prompts -> speaking_submissions
```

`attempts` references exactly one exercise or exam. `exam_section_items` references exactly one objective question, Writing prompt or Speaking prompt. Those cross-field invariants are validated transactionally and tested.

# P. Question Engine

Use an explicit type registry and one shared `questions`/`question_options` structure. MVP types:

- `single_choice`: two or more ordered options and exactly one correct.
- `true_false`: exactly two semantic options and exactly one correct.

Future `multiple_choice` and `fill_blank` are possible only after answer/scoring/display rules are approved. Writing/Speaking remain separate prompt/submission domains rather than forced into an objective option table. Server validation defines allowed type/payload schemas; the client never provides trusted correctness. See [Document 06](06-question-engine.md).

# Q. Exercise vs Exam Design

Exercises are focused reusable ordered objective-question collections, normally one skill/topic and optionally timed with immediate post-submit feedback. Exams contain ordered sections; each section contains ordered heterogeneous references (objective, Writing or Speaking) with timing/instruction/points metadata.

Both reuse the same attempt lifecycle and `ScoringService`; there is no duplicated exam scoring engine. A configurable exam can model the verified structure, while strict-format validation warns on unverified/conflicting provider rules rather than hard-coding claims.

# R. Attempt Model

An attempt belongs to exactly one exercise or exam and records type, title/config snapshot, state (`in_progress`, `submitted`, or future explicit `abandoned`), completion reason (`manual` or `deadline`), start/expiry/submission times, raw totals/percentage, item counts, and timing metadata. Start snapshots the ordered assessment and pre-creates objective-answer and applicable manual-submission rows in one transaction. Submit locks/finalizes idempotently and never recomputes history from live content. Detailed transitions are in [Document 07](07-attempt-and-exam-flow.md).

# S. Answer Model

Each `attempt_answer` belongs to an attempt and carries source question ID when still available, position/section, snapshot schema/prompt/options/key/explanation, normalized response JSON, points, correctness, save version and timestamps. Objective answer shapes are type-specific; malformed or stale saves fail. Rows are pre-created so unanswered items and complete ordering are durable.

# T. Historical Snapshot Strategy

At attempt start, copy every field required to render and score the historical result: assessment title/instructions/timing, item order/points, source context, prompt, options, correct-answer representation, explanation and source labels, all with `snapshot_version`. Snapshots are immutable after start. Live edits affect future attempts only; source foreign keys are optional conveniences, not history authority. Writing/Speaking submissions likewise snapshot their prompt.

# U. Scoring Engine

A small deterministic `ScoringService` dispatches by snapshotted type, normalizes the stored response, compares it to the snapshotted key and writes earned/available points, correctness, raw count and percentage during the locked submission transaction. Missing answers earn zero; malformed stored payloads fail closed and are logged for repair. Rounding is explicitly tested. No client score is trusted.

The service does **not** infer VSTEP 0–10 or Level 3. Writing/Speaking have self-review only. If an authoritative versioned conversion/rubric is later obtained, it is a separately approved scoring profile with golden test cases.

# V. Timer and Autosave Design

- Server sets `started_at` and immutable `expires_at`; remaining time is derived from server time.
- Browser countdown is a user aid and resynchronizes after reload; it cannot extend the deadline.
- Answers save on explicit navigation/change with a short debounce where useful; typed text also receives a periodic save only if Writing draft policy is approved.
- A save supplies expected `save_version`; success increments it and returns server `saved_at`; mismatch returns `409` rather than overwriting.
- Saves reject submitted or deadline-passed attempts. Zero time locks inputs and requests idempotent server finalization.
- Page state visibly distinguishes saving/saved/offline/conflict/submitted. Refresh reloads the server's latest successful value.

# W. Laravel Architecture

A conventional Blade/Bootstrap Laravel monolith:

```text
HTTPS -> web/owner middleware -> controllers -> Form Requests/Eloquent
                                           \-> ScoringService
                                           \-> Laravel Storage
                                                     |
                                                MariaDB/MySQL
```

Controllers coordinate; Form Requests validate complex writes; Eloquent expresses relationships/scopes/casts; Blade escapes output; transactions protect multi-row start/submit/composition; `ScoringService` is the only immediately justified domain service. No repositories, interfaces, events, queues or general workflow engine in MVP. Details and controller/directory plan: [Document 04](04-system-architecture.md).

# X. Route Plan

REST-like named routes cover login/logout/health; dashboard/study libraries; exercise/exam catalog; attempt start/show/section/answer-save/submit/result; history/review; Writing/Speaking submissions; and `/manage` resource CRUD, status, previews and ordering. Every non-login/health route uses `web` + `owner`; every write uses CSRF and state/parent validation. JSON saves return no answer key. Full method/URI/name/action table: [Document 08](08-route-design.md).

# Y. UI/UX Plan

Use a calm responsive study desk with a simple shared shell, readable passages, native semantic answer controls, keyboard flow, visible focus and text-labelled status. Attempt UI shows progress, navigator, server-derived countdown, save state and deliberate submit confirmation; mobile uses a single flow and sticky actions. Results lead with “Practice result” and raw values. Manage reuses index/form/preview/composer patterns and explains history-safe deactivation. Speaking progressively enhances with MediaRecorder and a no-microphone fallback. Full page matrix and state/accessibility rules: [Document 09](09-ui-pages.md).

# Z. Personal Content Management Plan

- `/manage` presents personal curriculum counts/warnings, not user administration.
- CRUD for topics, vocabulary, grammar, passages, listening content, objective questions, exercises/exams and Writing/Speaking prompts.
- Status transition is explicit (`draft`, `active`, `inactive`); referenced content is deactivated rather than destroyed.
- Complex composers use filtered existing items, full ordered updates, transaction and structural validation.
- Source category/reference/license/reviewer fields and learner preview are mandatory for activation-ready content.
- Editing warns that historical attempts use snapshots; it affects new attempts only.

# AA. Security Plan

Protect the whole app with one strong environment-configured owner password hash, normal Laravel session, regeneration, secure/HttpOnly/SameSite cookie, CSRF and login throttle. Validate/allow-list all writes; bind SQL parameters; restrict mass assignment; escape output/sanitize narrowly approved rich text; apply CSP/headers; disable debug; redact logs. Correct answers stay server-side until submission. File audio is size/type/path validated; arbitrary server-side URL fetch is disallowed; speaking audio is never uploaded. Backups/exports are personal data and kept encrypted/private. Threats and checklist: [Document 12](12-security.md).

# AB. Deployment Research

Current official research compared InfinityFree's PHP 8.3/MySQL/htdocs/FTP/phpMyAdmin/SSL/fair-use behavior with the Render + Neon fallback. InfinityFree advertises 5 GB disk, unlimited bandwidth, MySQL 8.0/MariaDB 11.4, no card and no expiry; its Terms reserve fair-use limits and do not guarantee backups. Free Hosting has no SSH/SFTP, Composer, Node/npm, cron, workers or Artisan, so releases and Sprint 0 SQL are prepared locally and uploaded through FTP/File Manager/phpMyAdmin. Render Free still warns against production use; Neon remains a PostgreSQL fallback only.

The complete comparison, sources and billing cautions are in [Document 15](15-deployment-plan.md). Terms must be rechecked before deployment.

# AC. Recommended Free Deployment Architecture

Primary:

```text
Browser --HTTPS/session--> InfinityFree Apache/PHP 8.3 + Laravel 13
                                      |            |
                                      |            `-- small reviewed release audio only
                                      `-- MariaDB/MySQL

Off-host private: repository + DB/application exports + original audio masters
Speaking: browser-local only
```

Sprint 0 deploys only a protected minimal vertical early to prove runtime, DB, HTTPS, persistence, quota and rollback. The free URL uses the provider subdomain. Add no card/paid service automatically. Render Docker + Neon PostgreSQL is activated only after measured blockage and explicit approval.

# AD. Database Hosting Decision

Use InfinityFree MySQL/MariaDB for development-production parity. Create one database/user and import the reviewed Sprint 0 bootstrap SQL through phpMyAdmin because Artisan cannot run on Free Hosting. Preserve portability through standard migrations, string states, validated JSON and minimal raw SQL. PostgreSQL/Neon is a fallback that requires compatibility tests for unsigned types, booleans/defaults, JSON, collation/case, indexes/raw SQL, plus rehearsal import and owner approval.

# AE. File/Audio Storage Decision

- Application assets: reproducible Git-controlled release.
- Small fixed original/licensed Listening/pronunciation audio: validated InfinityFree release assets or persistent path when size/licence permits, with DB path, hash, size and source metadata.
- Original audio masters: private off-host source.
- Speaking recordings: browser memory; optional deliberate local download; never server storage in MVP.
- No arbitrary hotlinking or unreviewed remote fetch. Measure pilot bytes/transfer and preserve a quota margin before bank expansion.

# AF. Backup Strategy

InfinityFree does not guarantee provider backups, so use three owner-controlled layers: portable application export, MariaDB/MySQL dump before migrations/content batches and regularly while active, and repository/original-audio masters. Store exports encrypted off-host, never in Git. Rehearse clean restore, asset hash verification, representative content/submission access and historical snapshot score reproduction.

# AG. Testing Plan

Unit-test normalization/scoring/word count; feature-test login/CSRF/CRUD/start/snapshot/save conflict/deadline/idempotent submit/result/review; browser-test MediaRecorder/permission/timer/composers/keyboard/responsive states; smoke-test actual production; and rehearse backup restore. Explicit regression tests prevent pre-submit answer-key leakage, live edits changing history, and “official score” labels. Use representative full-exam/content sizes and monitor query/memory/media quota. Full matrix and release gates: [Document 11](11-testing-plan.md).

# AH. Development Roadmap

After approval only:

0. foundation and an early InfinityFree proof;
1. topics/vocabulary/grammar;
2. Reading/Listening sources and objective bank;
3. exercises and durable attempts;
4. scoring/history/wrong review;
5. configurable mock exams;
6. Writing;
7. local Speaking recording;
8. hardening/accessibility/restore/content QA;
9. controlled content expansion;
10. optional approved Render/Neon migration.

Every sprint lists dependencies, tables/models, controllers/requests/routes/views, tests, deployment checks, risks and Definition of Done in [Document 10](10-development-plan.md). Do not start the next sprint with the current one incomplete.

# AI. Migration Order

1. `topics`
2. `vocabularies`
3. `vocabulary_progress`
4. `grammar_lessons`
5. `passages`
6. `listening_contents`
7. `questions`
8. `question_options`
9. `exercises`
10. `exercise_questions`
11. `writing_prompts`
12. `speaking_prompts`
13. `exams`
14. `exam_sections`
15. `exam_section_items`
16. `attempts`
17. `attempt_answers`
18. `writing_submissions`
19. `speaking_submissions`

Rollback is reverse order. Once meaningful production data exists, use new forward migrations rather than rewriting old migrations or using destructive `migrate:fresh`.

# AJ. Risk Register

| Risk | Probability | Impact | Mitigation |
|---|---|---|---|
| Question schema too rigid | Medium | High | Explicit type registry, versioned response/snapshot schemas, future types only with contract tests |
| Question schema too generic | Medium | High | Keep objective types strict; Writing/Speaking separate; validated JSON only for true variability |
| Exercise/exam logic duplication | Medium | High | Shared attempts/answers/scoring; exam only adds sections/composition |
| Scoring bug | Medium | High | Pure deterministic service, table-driven unit tests, idempotent locked submission, snapshots |
| Frontend-only timer | Low | High | Immutable server deadline; browser countdown display only |
| Answer loss after refresh/network conflict | Medium | High | Precreated rows, versioned saves, visible state, refresh from server, conflict response |
| Correct answers exposed early | Medium | High | Never render/return keys before submission; payload regression tests |
| Question edits break historical results | Medium | High | Immutable versioned attempt snapshots; history reads snapshots |
| Destructive content deletion | Medium | High | Foreign-key restrict and deactivate; export before content batches |
| Audio fills storage/transfer | High | High | Small pilot, compact original audio, local Speaking, quota measurement/margin |
| Fixed public `htdocs` root | Medium primary / High fallback | High | InfinityFree outer `.htaccess` denies internals and routes into `public`; fallback ships static assets and stores no runtime files |
| Free database/storage limits | Medium | High | Monitor actual bytes, paginate, small content batches, off-host exports, migration trigger |
| Free-tier fair-use/suspension | Medium | High | Conservative quota budget, owner off-host backups, periodic account activity; recheck terms |
| Provider sleep/cold start | Low primary / High fallback | Medium | Primary recommendation; disclose/test fallback cold starts |
| Deployment incompatibility | Medium | High | Sprint 0 early proof, production-like smoke, portable schema, rollback |
| Browser recording incompatibility | Medium | Medium | HTTPS, feature/codec detection, permission/error fallbacks, local download optional |
| Writing/Speaking scope mistaken for scoring | High | High | Explicit self-review labels, no official auto-score, future rubric requires source approval |
| Incorrect B1 content | Medium | High | CEFR-aligned editorial plan, review workflow, original small batches, error log |
| Incorrect exam structure/provider assumption | Medium | High | Versioned official sources, configurable rules, disclose conflict/unknowns, pre-activation validator |
| Copyright/licensing violation | Medium | High | Original-first strategy, source ledger, rights gate, no copied papers/course audio |
| Accidental multi-user complexity | Medium | Medium | No users/roles/user IDs; separate future redesign gate |
| Unexpected hosting charge | Low primary / Medium fallback | High | No card/upgrade/paid API without explicit approval; monitor quotas; provider suspension preferred |
| Free provider changes/discontinues plan | Medium | High | Recheck terms, portable repo/exports/audio masters, documented fallback |
| Owner device compromise | Low | High | Strong secret, secure session, logout, encrypted backups; acknowledge device remains trust boundary |

# AK. Open Decisions

The recommendations below form the proposed approval package; alternatives are documented so implementation does not need repeated clarification.

| Decision | Recommended option | Reason | Alternative |
|---|---|---|---|
| Target standard | VSTEP.3-5 Level 3 with CEFR B1 learning guidance | Matches request and official Vietnamese format | A generic CEFR or Cambridge product would require a different spec |
| Public security | One configured owner login protecting whole site | Appropriate minimum for public personal data/manage functions | Private-network-only deployment, but not the requested Internet path |
| Primary database | MariaDB/MySQL on InfinityFree | Preferred stack and no engine mismatch | Neon PostgreSQL only after fallback approval/testing |
| Snapshot depth | Full immutable render-and-score snapshots per attempt answer | Preserves results after content edits/deactivation | Versioned content joins are more fragile/complex |
| MVP question types | Single-choice and true/false | Covers official objective shape with low risk | Approve multi-answer/fill-in later with exact rules |
| Autosave | Versioned answer save on action/change; visible state; periodic text draft only if approved during Writing sprint | Reliable without noisy server churn | Manual save only or frequent interval saves |
| Retry | Allow new attempts; never reopen/overwrite submitted attempt | Clean history and simple integrity | Configurable exercise retry caps later |
| Delete policy | Restrict referenced deletes and deactivate | Protects history and bank relationships | Hard delete only unreferenced drafts |
| Writing drafts | Recommend periodic/versioned draft save for long response, final submission separate | Reduces loss | Submission-only/manual save reduces complexity |
| Speaking persistence | Local memory with explicit download; server stores notes/metadata only | Fits quota/privacy/free hosting | Server upload requires new storage/security plan |
| Transcript visibility | Hide during an attempt; reveal in learning review according to content policy | Supports fair practice and analysis | Always show in untimed study-only content |
| Score representation | Raw points/correct count/percentage and non-official self-check | No verified conversion/rubric | Add official band only after authoritative versioned method |
| JSON/CSV import-export | SQL dump + versioned JSON application export in MVP; CSV only later for simple flat vocabulary/topic authoring; defer general import until validation is stable | Recovery first; CSV cannot safely represent nested snapshots/exams | Build a validated batch importer when manual entry is a measured bottleneck |
| Free hosting | InfinityFree Free primary | PHP 8.3/MySQL/HTTPS and fixed `htdocs` hosting | Render Docker + Neon PostgreSQL fallback |
| Audio storage | Small fixed approved audio on persistent host; masters off-host; Speaking local | Fits learning need and quota risk | External object storage only after explicit review |
| Strict simulation provider | Use Decision 729 core and label provider-specific unknowns; choose target center before claiming interface fidelity | Avoids invention | Mirror a selected authorized provider after current confirmation |
| First content volume | Small reviewed pilot specified in Document 14 | Validates workflow/quota/quality early | Full 1,000-word/large-audio bank before use is slow and risky |

# AL. Recommended MVP

Approve Sprints 0–8 as the functional MVP, with a small original/open-licensed content pilot. The owner can securely study curated reference content; complete reliable objective practice and a configurable VSTEP-style mock; review raw results/history/errors; save Writing practice; and rehearse Speaking locally. Manage supports all required curation and source/licensing controls. Production runs on InfinityFree Free with MariaDB/MySQL, off-host exports and no paid dependency.

MVP completion means local and hosted critical flows/tests pass, data can be restored, core pages are accessible/responsive, history is snapshot-stable, results are honestly labeled, the pilot content is reviewed/licensed, quota is measured, and deployment/security runbooks work. It does not mean the full long-term content inventory is complete.

# AM. Technical Documentation Summary

| Document | Coverage |
|---|---|
| [01 — Project Overview](01-project-overview.md) | Purpose, principles, product boundary, success criteria |
| [02 — Requirements](02-requirements.md) | Functional/non-functional requirements and acceptance direction |
| [03 — Use Cases](03-use-cases.md) | Actor flows, alternatives and postconditions |
| [04 — System Architecture](04-system-architecture.md) | Laravel monolith, responsibilities, components and flows |
| [05 — Database Design](05-database-design.md) | Tables/columns/relationships/indexes/deletion, full ERD |
| [06 — Question Engine](06-question-engine.md) | Types, contracts, validation, normalization and snapshots |
| [07 — Attempt and Exam Flow](07-attempt-and-exam-flow.md) | Lifecycle, timing, saves, submit and historical behavior |
| [08 — Route Design](08-route-design.md) | HTTP method/URI/name/action/protection and errors |
| [09 — UI Pages](09-ui-pages.md) | Page matrix, attempt/manage state, accessibility/responsive plan |
| [10 — Development Plan](10-development-plan.md) | Sprints, dependencies, artifacts, tests, deployment and DoD |
| [11 — Testing Plan](11-testing-plan.md) | Security/data/score/browser/performance/restore test matrix |
| [12 — Security](12-security.md) | Owner auth, threats, input/output/files/secrets/operations |
| [13 — Future Features](13-future-features.md) | Evidence-triggered candidates and explicit deferrals |
| [14 — Study Content Research](14-study-content-research.md) | Sources, exam/CEFR, syllabi, skill plans, licensing and bank QA |
| [15 — Deployment Plan](15-deployment-plan.md) | Current free-tier comparison, recommended/fallback architecture, deployment/backup |

# AN. Approval Checkpoint

> **PHASE 1 COMPLETE — WAITING FOR APPROVAL.**
>
> No application implementation, migration execution, database modification, package installation, infrastructure deployment, or feature coding has been performed.
>
> Items requiring approval:
>
> 1. VSTEP.3-5 Level 3 / CEFR B1 target and handling of unverified provider details.
> 2. Study syllabus, pilot inventory, content review and licensing strategy.
> 3. MVP/in-scope/out-of-scope boundaries.
> 4. Strict single-owner architecture with no users/roles/RBAC.
> 5. One owner login protecting the public deployment.
> 6. Nineteen-table MariaDB/MySQL database design and migration order.
> 7. MVP question types and future type policy.
> 8. Attempt, answer, server scoring, timer and autosave model.
> 9. Full immutable historical snapshot strategy and deactivate-first policy.
> 10. Laravel 13/PHP 8.3 conventional monolith architecture (PHP 8.3 is the verified InfinityFree Free runtime).
> 11. Personal Manage content-curation design.
> 12. InfinityFree Free primary deployment and Render + Neon fallback; alwaysdata is retired.
> 13. MariaDB/MySQL production choice; PostgreSQL only on approved fallback.
> 14. Small persistent fixed audio plus browser-local Speaking strategy.
> 15. UI/UX and WCAG 2.2 AA target.
> 16. Testing, backup/restore and development roadmap.
> 17. Recommended options in the Open Decisions table.
>
> I will not begin implementation until explicit approval is received.
