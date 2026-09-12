# Security Plan

## Security posture

The application is private and single-user, but it is still internet-accessible and holds study history. Use one configured owner credential and a normal Laravel session. Do not create roles, permissions, registration, password reset, social login, or a `users` table.

The whole application—including study pages, attempts, management, files, and JSON saves—is protected. Only a minimal health route and the login form/action are public.

## Owner authentication

- Configure a non-identifying owner login value if desired and a strong password hash in environment/secrets; never commit the plaintext password or hash in a public repository.
- Verify with Laravel's hashing facilities and a timing-safe workflow.
- Regenerate the session ID after login; invalidate the session and regenerate the CSRF token at logout.
- Use generic failure text and throttle by IP/session characteristics. Do not reveal whether a login identifier exists.
- Cookies: `Secure` in production, `HttpOnly`, suitable `SameSite` (normally `Lax`), and HTTPS only.
- Set a reasonable idle/session lifetime for personal use; an exam page should warn before ordinary session expiry but must not weaken the server deadline.
- Document an operator-only credential rotation/recovery procedure through host environment settings. No email recovery endpoint.

## Authorization

An `owner` middleware gate is mandatory for protected route groups. Because there is only one owner, object ownership columns add no value; authorization still checks state and parent relationships:

- an answer belongs to the requested attempt;
- an exam section belongs to the attempt's snapshotted exam;
- draft/inactive resources do not start learner attempts;
- submitted attempts and finalized submissions cannot be altered outside explicit allowed metadata;
- Manage actions are unreachable without the same owner session.

Use `404` where object disclosure would be unnecessary. Do not rely on hidden links or the single-user assumption as authorization.

## Request protection and validation

- CSRF on all session-authenticated writes, including fetch saves.
- Form Requests for structured writes; allow-list statuses, skills, question types and MIME/extensions.
- Apply length/count/numeric limits before persistence. Validate nested option/item arrays for uniqueness and maximum size.
- Normalize objective responses on the server; reject shapes not valid for the snapshotted type.
- Use Eloquent/query bindings, never concatenate user data into SQL.
- Mass assign only explicit fields. Route IDs and source/path fields are not blindly fillable.
- Throttle login, health, answer-save bursts and expensive import actions proportionately.

## Output and content safety

- Blade escaped output is the default. If grammar/passage rich text is required, use a narrowly configured HTML sanitizer and store/preview its allowed subset.
- Markdown rendering, if later approved, must disable raw HTML and unsafe URLs.
- Do not place answer keys/explanations in take-page markup, data attributes, JavaScript bundles, or pre-submit/save JSON.

## Sprint 4 review safety

History and wrong-answer Review are owner-protected. Review eligibility requires a submitted attempt and `is_correct=false`; detail pages use the immutable question/context snapshots rather than live question joins. Blade escaping remains enabled for prompts, options, context, and explanations. Sprint 4 adds no credentials, files, database tables, or destructive operations.
- Production disables debug output and detailed exception pages. Log a request ID, not secrets or full candidate responses.
- Set baseline headers: HSTS after HTTPS is stable, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, a restrictive `Permissions-Policy` with microphone allowed only for self, and a practical Content Security Policy tested with Laravel/Bootstrap assets.

## Sprint 6 Writing safety

Writing submissions are owner-protected, CSRF-protected, length-limited, and validated through Form Requests. The server derives `word_count`, stores an immutable prompt snapshot, rejects stale draft versions, and prevents edits after submission. Responses and self-checks are not scored as official VSTEP results. The production release and targeted view correction excluded `.env`, credentials, and runtime storage.

## Sprint 7 Speaking safety

Speaking submissions are owner-protected, CSRF-protected, length-limited, and validated through a Form Request. The server accepts only duration, notes, self-assessment, save version, and prompt snapshot metadata. The browser requests microphone access only in a secure context with `Permissions-Policy: microphone=(self)` and uses `MediaRecorder` locally; it never submits a Blob or file input. CSP allows `blob:` only for local media playback. Permission denial, missing devices, unsupported APIs, and codec differences degrade to prompt/timer/notes rather than blocking study. Submitted metadata is immutable and is not an official VSTEP score.

## Secrets and configuration

- Environment/host secret store: `APP_KEY`, owner password hash, database credentials, production URL, session settings and optional health-check token.
- Repository: `.env.example` with placeholders only. `.env`, dumps, exports, logs, locally downloaded recordings and host credentials are ignored.
- Generate a unique production `APP_KEY`; losing/changing it can invalidate encrypted data/session cookies, so include it in a protected recovery record.
- Use least-privilege DB credentials for the application. Use a separate operator credential only if the host supports it and a concrete maintenance need exists.
- Never expose `phpinfo`, Telescope/debug bars, database web tools, or raw storage directories publicly.

## File and audio security

The MVP stores no speaking recordings on the server. Browser blobs remain local until discarded or explicitly downloaded by the owner.

For fixed listening/pronunciation assets:

- prefer original or clearly licensed audio;
- allow-list audio extensions/MIME, set a conservative maximum size, generate server filenames, and ignore the original path;
- keep upload staging outside the public web root; publish only validated files through Laravel storage or an intentionally mapped read-only directory;
- reject executable/polyglot-looking files and path traversal;
- prevent directory listings and force correct content types;
- never fetch an arbitrary user-entered remote URL server-side (SSRF risk); external references are metadata until explicitly downloaded by the owner through a controlled process.

## Attempt integrity

- Server time controls deadline; browser countdown is display only.
- Start and submit run in transactions. Submit locks the attempt, is idempotent, and scores snapshots.
- Optimistic `save_version` prevents silent lost updates.
- Snapshot JSON includes a schema version and only the fields required for durable results.
- Correctness is computed server-side. Client-provided score, elapsed time, correctness or answer key is ignored.
- State transitions are allow-listed and audited with timestamps.

## Database, backup, and privacy

- Foreign keys, unique indexes and transactional writes protect history.
- Production DB is not public to the internet unless the provider requires a secured endpoint; restrict network/user privileges where available.
- InfinityFree does not guarantee provider backups, so create encrypted/off-host periodic database/application exports as described in the deployment plan.
- Exports and dumps contain personal writing/history. Store them in a private encrypted location and define manual retention (for example, keep recent monthly copies and delete superseded ones deliberately).
- No analytics, advertising trackers or third-party microphone/cloud scoring in MVP.

## Dependency and operational security

- Pin dependencies through Composer/npm lockfiles and keep Laravel/PHP within supported security windows.
- Before release, review dependency advisories and only add packages with a real need.
- Restrict production write permissions to Laravel's required runtime directories and approved upload storage.
- Cache production configuration/routes/views only after environment is correct; run with `APP_DEBUG=false`.
- Monitor authentication failures, repeated 4xx/5xx, disk/DB quota and host suspension notices. Do not log plaintext passwords, tokens, full answer bodies or recorded media.
- The owner must monitor InfinityFree free-plan activity/fair-use notices and retain off-host exports; there is no provider backup guarantee.

## Threats and controls

| Threat | Principal controls | Residual risk |
|---|---|---|
| Internet password guessing | Strong secret, throttle, generic error, HTTPS | Free host may offer limited network controls |
| Session theft/fixation | HTTPS, secure cookies, regeneration, timeout, CSP | Compromised owner device remains high impact |
| CSRF | Laravel tokens, SameSite cookies, POST logout | Browser extensions can still act with owner privilege |
| XSS from study content | Escaped output, sanitizer allow-list, CSP | Rich text expands review burden |
| Answer-key leakage | Server rendering discipline, response tests | Owner can inspect DB by design |
| Lost/overwritten answers | Versioned saves, transactions, snapshots, exports | Connectivity failure before first successful save |
| Malicious/oversized audio | Type/size/path controls, no speaking upload | Sophisticated media parser flaws in browser |
| Dependency compromise | Minimal packages, locks, advisories, supported versions | Upstream and host supply-chain risk |
| Free-host suspension/quota | Admin activity reminder, quota monitoring, off-host exports | Service terms can change without guarantee |

## Security verification checklist

- Protected route inventory test passes.
- HTTPS redirect/cookies/headers verified on production.
- Debug and directory listings are off.
- Login/session/CSRF/throttle tests pass.
- Answer keys absent before submission.
- File/path validation tests pass and speaking audio is absent from network/storage.
- Dependencies show no unresolved known critical vulnerability.
- Restore from an off-host export succeeds.
- Owner secret rotation and host inactivity reminders are documented.

## Sprint 3 production verification

The deployed response headers include `Content-Security-Policy`, `Permissions-Policy`, `Referrer-Policy`, HSTS, `X-Content-Type-Options: nosniff`, and `X-Frame-Options: DENY`. Same-origin `/health` returned only `{"status":"ok"}`. The Practice take page contained prompt/options and persisted responses but no correct key or explanation; those appeared only after server-side submission. The release archive contained no `.env`, and the existing production `.env` and runtime `storage` were preserved.

InfinityFree's edge blocked direct navigation to `/health` and protected source paths during this verification run; the application-level same-origin health check passed and the blocked paths disclosed no file content. This is recorded as a provider-edge limitation, not bypassed.

## Deferred security work

If future scope adds multiple users, public sharing, cloud recording, AI services, email, or payments, this threat model and schema must be redesigned before implementation. The current one-owner gate must not be stretched into an improvised multi-user system.
