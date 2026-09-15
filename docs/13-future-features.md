# Future Features and Explicit Deferrals

## Decision rule

Future work is considered only after the MVP is used with real study content. A feature needs a concrete learning benefit, a maintenance/storage/security cost estimate, and evidence that the simpler workflow is insufficient. This prevents a personal study tool from becoming a platform prematurely.

## Near-term candidates after MVP

| Candidate | Benefit | Prerequisite / trigger | Main cost or risk |
|---|---|---|---|
| Spaced-review scheduler | Implemented locally in Sprint 11 with deterministic due/new queue | Production SQL/release approval and real-use feedback | Scheduling logic must remain understandable; no notification service is added |
| Additional objective types | More varied practice | Bank needs multi-select/fill-in and scoring rules are approved | Normalization/scoring complexity |
| Content batch import/export | Faster curated bank maintenance | Manual entry is a measured bottleneck | Validation, duplicate and licensing errors |
| Search across all content | Faster retrieval | Content volume makes module filters insufficient | Index/query and ranking work |
| Study-plan dashboard | Weekly targets and gap visibility | Stable use and meaningful history | Can become gamified/noisy |
| PWA/offline reading | Resilience during poor connectivity | Owner has recurring offline need | Cache invalidation and unsaved-attempt conflicts |
| Better result analytics | Identify topic/type weaknesses | Enough attempts for stable signal | Misleading conclusions from small samples |
| Locally stored speaking history | Keep selected recordings on device | Browser download workflow is inconvenient | Quota, portability, accidental loss; Sprint 7 keeps only server metadata |

## Larger features requiring a new design approval

### AI feedback or scoring

Possible uses include writing feedback, speaking transcription/pronunciation guidance, and question drafting. Before adoption, decide provider, cost cap, privacy, data retention, prompt-injection handling, output review, and how to label unvalidated estimates. AI output must never be represented as an official VSTEP score. An offline/manual feedback workflow remains the baseline.

### Server-side speaking audio

Uploading recordings requires authenticated private delivery, quota/retention policy, MIME validation, possibly transcoding, backups and deletion controls. It is deliberately excluded while the primary host has only 1 GB shared storage.

### Multi-user accounts

This would require `users`, ownership on records, policies, registration/invitations, password reset, isolation tests, abuse prevention, privacy and a migration plan. Do not add `user_id` speculatively to the current schema.

### Public sharing or content marketplace

Sharing introduces permissions, moderation, copyright/takedown, stable public URLs and abuse controls. The current source ledger is necessary but not sufficient.

### Native mobile application

A responsive web/PWA path should be exhausted first. Native clients would require a versioned API, token authentication, sync/conflict resolution and app-store operations.

## Content and exam improvements

- Expand original/open-licensed question banks using the inventory targets in Document 14.
- Add calibrated difficulty only after item performance data is large enough; initial `B1-easy/core/stretch` labels are editorial, not psychometric.
- Obtain an authoritative public scoring/conversion specification before any official-looking 0–10 or Level 3 calculation.
- Resolve the official Reading word-count discrepancy and provider-specific rules such as audio replay, preparation time and interface behavior before strict simulation claims.
- If an official, reproducible analytic Writing/Speaking rubric becomes available, version it by source and date; keep self-assessment distinct from examiner scoring.
- Consider A2 refresh and B2 stretch collections only as clearly labeled support around the Level 3 target, not as a mixed exam definition.

## Technical improvements

- Introduce a dedicated `AttemptService` only if implemented orchestration becomes difficult to test/read in controllers.
- Use queues only after a real asynchronous job exists (large import, media processing, notifications). The primary free host's limits must be rechecked.
- Add cache only after profiling identifies repeated expensive reads; invalidation must follow content activation/edit.
- Add object storage only when persistent media exceeds the approved host plan and privacy/licensing permit it.
- Move to Render + Neon or another provider only after a measured InfinityFree constraint, using the migration plan in Document 15.
- Add observability services only if local/provider logs cannot diagnose failures and the privacy/cost trade-off is accepted.

## Explicitly out of scope for this project phase and MVP

- application implementation during Phase 1;
- multi-user/RBAC/admin roles/social features;
- public registration, sharing or leaderboards;
- payments/subscriptions;
- scraped, leaked, or unlicensed exam/textbook/audio content;
- automatic official VSTEP score claims;
- cloud speaking upload, transcription or AI assessment;
- native mobile apps and a public API;
- complex microservices, repositories/CQRS/event buses, Redis, queues or containers on the primary deployment;
- automatic spending or paid upgrades.

## Review cadence

After four weeks of real MVP use, review friction notes, content coverage, failed/slow pages, disk/DB quota and study outcomes. Choose at most one learning feature and one maintenance improvement for the next iteration. Recheck official exam rules and hosting terms before any change that depends on them.
