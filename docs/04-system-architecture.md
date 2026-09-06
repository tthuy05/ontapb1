# System Architecture

## Decision

Use a conventional server-rendered Laravel monolith. It is the smallest architecture that supports protected CRUD, relational content, durable attempts, server scoring, responsive pages, and deployment to a native PHP host.

```text
Browser (Blade + Bootstrap + small JS)
        |
     HTTPS
        v
Laravel web application
  middleware -> controllers -> validation -> Eloquent
                      |              |
                ScoringService   Laravel Storage
                      |
               MySQL / MariaDB
```

## Planned versions and compatibility

- Laravel 13, released 17 March 2026, supports PHP 8.3-8.5 and receives security fixes through 17 March 2028.
- Use PHP 8.3 for the verified InfinityFree Free runtime; Laravel 13 supports it.
- InfinityFree Free provides PHP 8.3, MySQL/MariaDB, Apache PHP hosting, fixed `htdocs`, FTP/File Manager and HTTPS. Composer, Node/npm, SSH and Artisan are unavailable on the free plan, so releases are built locally.
- Development and primary production both use MySQL/MariaDB to remove cross-database surprises.

Version selection is approved in Phase 1 but installed only after implementation approval.

## Responsibilities

### Browser

- Render accessible Blade HTML and Bootstrap components.
- Maintain navigation state, countdown display, answer-save requests, word count, and local microphone recording.
- Never calculate trusted score or receive objective answer keys before submission.

### Middleware

- Web session, cookies, CSRF, and request-forgery protection.
- Minimal owner-session gate for all private routes.
- Login and sensitive-write rate limits where appropriate.

### Controllers

- Coordinate requests, authorize the single-owner session, invoke validation/domain operations, and select responses.
- Do not contain large scoring algorithms or complex rendering queries.

Planned learner controllers: `DashboardController`, `VocabularyController`, `GrammarController`, `ReadingController`, `ListeningController`, `PracticeController`, `AttemptController`, `ExamController`, `ResultController`, `HistoryController`, `ReviewController`, `WritingController`, and `SpeakingController`.

Planned management namespace: `Manage\TopicController`, `VocabularyController`, `GrammarLessonController`, `PassageController`, `ListeningContentController`, `QuestionController`, `ExerciseController`, `ExamController`, `WritingPromptController`, and `SpeakingPromptController`. The namespace is **Manage**, not Admin.

### Form Requests

Use for non-trivial write payloads and cross-field rules: question create/update, exercise/exam composition, attempt answer save, upload, writing submission, and content activation. Simple login and small state toggles may use inline validation when clearer.

### Domain services

- `ScoringService` is justified: deterministic type-specific scoring is shared by exercise and exam submission and benefits from isolated unit tests.
- Attempt start/submit orchestration should initially remain in focused controller actions/private methods. Introduce `AttemptService` only if transaction and snapshot logic makes controllers materially difficult to read.
- No repositories, interfaces, events, listeners, queues, or generic workflow engine in the MVP.

### Models and database

Eloquent models express relationships and casts; database foreign keys/checks/unique indexes protect invariants. Query scopes may cover `active`, common filters, and submitted attempts. Historical snapshots are authoritative for results; live content is authoritative for new attempts.

### Storage

Primary host storage is persistent but limited. Store file paths and metadata in MySQL, never audio binaries. Fixed small Listening assets may be committed or uploaded to a controlled persistent directory. Speaking recordings remain browser-local by default.

## Request flows

### Read page

`HTTPS -> web/owner middleware -> controller -> scoped eager-loaded query -> Blade -> escaped HTML`

### Content write

`POST -> CSRF/owner middleware -> Form Request -> transaction if relational -> redirect with status`

### Answer save

`fetch PUT -> CSRF/owner -> validate attempt state/deadline/snapshot option/version -> update -> saved timestamp JSON`

### Submit

`POST -> lock attempt -> verify state/deadline -> ScoringService over snapshots -> update all answer outcomes and totals -> mark submitted -> commit -> result redirect`

## Dependency direction

```text
Topics and source metadata
  -> Passages / Listening content / Questions / Vocabulary / Grammar
  -> Question options
  -> Exercises + exercise-question ordering
  -> Exams + sections + heterogeneous section-item ordering
  -> Attempts + snapshotted attempt answers
  -> Scoring
  -> Results / History / Wrong-answer review
```

Writing and Speaking prompt/submission modules are parallel and do not block the objective practice engine.

## Planned directory structure

```text
app/
  Http/
    Controllers/
      Manage/
    Middleware/
    Requests/
  Models/
  Services/
resources/
  views/
    auth/
    dashboard/
    vocabulary/
    grammar/
    reading/
    listening/
    practice/
    exams/
    results/
    history/
    review/
    writing/
    speaking/
    manage/
    components/
routes/
  web.php
database/
  migrations/
  seeders/
  factories/
tests/
  Feature/
  Unit/
docs/
```

This is a file plan only; no listed application directory has been created in Phase 1.

## Deployment-aware choices

- Do not depend on Redis, long-running workers, WebSockets, root access, or a persistent container layer.
- Use database sessions/cache only if the selected host's filesystem/session behavior proves unreliable; initially use standard file sessions on InfinityFree's persistent `htdocs/storage` and verify in Sprint 0.
- Run asset compilation locally/CI and deploy built assets if Node memory or disk pressure is problematic; Node is not needed at runtime.
- Point the web root at Laravel's `public/` directory so `.env`, vendor source, and storage are not web-readable.
- Keep health checks shallow and never reveal secrets/database details.
- Store all configuration in `.env` locally and provider-side protected configuration in production.

## Architecture boundaries

| Concern | Decision |
|---|---|
| User model | None; one environment-configured owner credential |
| API | No general public API; only small session-authenticated JSON endpoints for autosave |
| Frontend framework | None; Blade + Bootstrap + native JavaScript |
| Question abstraction | Shared objective question table; Writing/Speaking use dedicated prompts |
| Exercise/exam reuse | Separate structures, shared question records and attempt/scoring behavior |
| History | Per-attempt snapshots plus restrict/deactivate live content |
| Background work | None in MVP |
| Deployment | Native PHP/MySQL primary; Docker/Postgres fallback only if needed |

## Technical references

- [Laravel 13 release/support policy](https://laravel.com/framework/docs/13.x/releases)
- [Laravel 13 deployment requirements](https://laravel.com/framework/docs/13.x/deployment)
- [InfinityFree PHP 8.3 announcement](https://forum.infinityfree.com/t/free-hosting-is-now-upgraded-to-php-8-3/109714)
- [InfinityFree Laravel deployment guidance](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578)
