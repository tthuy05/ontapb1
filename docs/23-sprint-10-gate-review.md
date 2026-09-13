# Sprint 10 migration gate review

## Decision

Sprint 10 is an optional hosting migration, not an automatic cutover. The project remains on the approved InfinityFree primary deployment because the measured account state does not show a material hosting constraint.

No Render service, Neon database, Docker production image, DNS change, or production configuration change was created during this review.

## Evidence collected on 13 September 2026

- InfinityFree account `if0_42788092` is Active on the Free plan.
- Disk usage is 98 MB of 5 GB.
- Bandwidth usage is 4 MB of Unlimited.
- Inode usage is 9,504 of 80,000.
- Daily hits are 4 of 50,000.
- The hosted Laravel application, MySQL database, Sprint 0–9 data, and protected Owner routes are operating on the current primary host.
- The Sprint 9 production audit confirmed 20 tables, 19 migrations, 194 rows, preserved vocabulary progress, and a clean release upload that retained `.env` and `storage`.

These measurements do not meet the documented trigger “move only if InfinityFree limits materially block use”. The migration path therefore remains inactive.

## Compatibility readiness

- Laravel already contains a standard `pgsql` connection block and the migrations use portable Laravel schema primitives.
- A static application/migration audit found no PostgreSQL-specific application SQL. The only raw query is the portable aggregate projection in `DashboardController`; unsigned schema-builder types are used for existing MySQL/SQLite parity and require a real PostgreSQL run before any engine cutover.
- The local PHP runtime used for this project does not enable `pdo_pgsql` by default, but its bundled extension was loaded transiently for the compatibility run.
- Docker Compose is installed, but the Docker daemon was unavailable during the review.
- Render's current Free-instance guidance says Free instances should not be used for production applications: https://render.com/docs/free.
- A real PostgreSQL 18 parity run and local dump/restore rehearsal were completed below; no provider migration was created.

## Local verification

Completed without touching production:

- `composer validate --strict`: passed.
- Direct PHPUnit run against the configured in-memory SQLite test environment: passed, 101 tests and 657 assertions.
- Direct PHPUnit run against a temporary PostgreSQL 18 cluster: passed, 101 tests and 657 assertions.
- The two non-portable JSON database assertions found by the PostgreSQL run were changed to load the cast model value and assert the array, preserving the same behavior across engines.
- Restore rehearsal: all 19 migrations and Sprint 1–9 seeders were applied to a temporary PostgreSQL source database; a 98,631-byte custom-format `pg_dump` restored into a clean second database with matching 20-table, 19-migration and content counts. Restored `content:validate --json` reported 96 records and 0 issues.
- `git diff --check`: passed.
- The production-only InfinityFree SQL batches remain MySQL/MariaDB scripts and are not treated as PostgreSQL migration input.

The PostgreSQL parity and restore evidence is local readiness evidence. It does not activate a provider migration while InfinityFree remains healthy and the documented migration gate is unmet.

## Revisit trigger

Reopen Sprint 10 only after a measured InfinityFree constraint blocks the application, or after the owner explicitly chooses a provider migration despite the current healthy primary host. At that point, recheck provider terms, provision non-production Render/Neon resources, run the PostgreSQL parity suite and restore rehearsal, retain the InfinityFree export/release rollback path, and require a separate cutover approval.
