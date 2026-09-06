# Project Overview

## Phase status

This document is a Phase 1 planning artifact. No Laravel project, package, database, migration, route, model, controller, view, or feature has been created.

## Purpose

Build a private, single-owner web application for daily preparation for VSTEP.3-5 Level 3 (B1). The product should make the most important learning loop fast and dependable:

> Learn -> practice -> answer -> submit -> understand mistakes -> review -> retry -> track progress.

## Problem statement

Study material, practice attempts, explanations, and progress are otherwise fragmented across files and websites. The proposed application puts owner-created or legally reusable content, objective practice, attempt history, and weak-area review in one responsive interface accessible from a laptop or phone.

## Single-user model

- There is exactly one actor: **Owner / Learner**.
- All content, attempts, progress, and submissions belong implicitly to that owner.
- There are no roles, permissions, teacher/student separation, registration, or multi-user ownership columns.
- A public deployment will use a minimal owner-password session gate. This is access protection, not a user-management system.

## Target B1 standard

The planning assumption is **VSTEP.3-5 Level 3**, under Vietnam's six-level foreign-language proficiency framework. It is not Cambridge B1 Preliminary and the product will not mix exam formats. The mock-exam label must distinguish a `VSTEP simulation` from a `general B1 practice test`.

The verified statutory structure is based on Decision 729/QD-BGDDT dated 11 March 2015. Current test-governance context is supplied by Circular 09/2026/TT-BGDDT. Detailed findings and the one identified word-count conflict are in [14-study-content-research.md](14-study-content-research.md).

## Product scope

The first useful release centers on the content/question foundation and the full objective-practice loop: manage content, assemble exercises, start and recover attempts, save answers, submit, score on the server, review explanations, view history, review wrong answers, and retry. Vocabulary, grammar, reading, and listening then use that foundation. Mock exams follow after practice behavior is proven. Writing and speaking use guided self-practice without AI grading.

## Technical baseline

- PHP 8.3 (verified InfinityFree Free runtime; Laravel 13 compatible)
- Laravel 13 (planned; requires PHP 8.3 or newer)
- MySQL/MariaDB
- Blade, HTML, CSS, small native JavaScript modules, Bootstrap
- One Laravel monolith; no SPA, API-first split, queue, Redis, microservices, or container requirement for the primary host

No additional application package is recommended for the MVP. Bootstrap may be delivered as a compiled project asset or carefully pinned CDN asset; the implementation decision belongs to Sprint 0.

## Deployment goal

The finished application must have an HTTPS public URL and remain usable from another device. Normal restarts and deployments must not erase study records. The current primary recommendation is InfinityFree Free with PHP 8.3, MySQL/MariaDB, fixed `htdocs`, and local-build/manual upload workflow. Render Free plus Neon Free Postgres is the fallback. See [15-deployment-plan.md](15-deployment-plan.md).

## Success criteria

1. The owner can access the protected site from laptop and phone.
2. An objective exercise can be started, refreshed, resumed, submitted, scored, reviewed, and retried without losing data.
3. Correct answers are not sent before submission.
4. Old results remain understandable after live questions are edited.
5. Progress and owner-created content survive redeploys.
6. The first content batch is checked for B1 suitability, correctness, explanation quality, and licensing before expansion.

## Design principles

- Prefer an understandable relational model over a universal content abstraction.
- Reuse the attempt/scoring workflow without forcing exercises and exams into one table.
- Store queryable fields as columns and optional editorial metadata as JSON.
- Preserve history with attempt-time snapshots and content deactivation.
- Treat the server and database as the source of truth.
- Keep free-hosting limitations visible from the first sprint.
