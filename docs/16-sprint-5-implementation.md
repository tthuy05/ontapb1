# Sprint 5 implementation — mock-exam composition and flow

Sprint 5 activates the existing exam dependency block without changing the
database schema. It supports owner-composed objective Reading and Listening
simulations, because Writing and Speaking remain later roadmap work.

## Composition contract

- An exam has a title, supported format label, optional overall time limit, and
  draft/active/inactive status.
- Sections are ordered from zero, use Reading or Listening, and currently use
  `free_within_section` navigation.
- Each section has at least one ordered item when active.
- Each Sprint 5 item references exactly one active objective question whose skill
  matches the section. A question cannot appear twice in one exam.
- Active questions require active topic/context dependencies and a valid
  single-choice or true/false option set with exactly one correct option.
- Configured section limits cannot exceed the overall exam limit.

The Manage form uses one line per section:

```text
reading | Reading practice | 300 | free_within_section
```

and one line per item:

```text
0 | 1 | 1.00
```

## Snapshot and scoring behavior

Starting an exam creates an append-only `attempts` record and one
`attempt_answers` row per item. The exam configuration, question text/options,
and Reading/Listening context are copied into snapshots before the learner sees
the section. Existing `ScoringService` logic is reused for objective scoring.

Results show raw points and percentages with section, skill, topic, and type
breakdowns; they are explicitly not official VSTEP bands.

## Schema and deployment decision

There is no Sprint 5 migration and no Sprint 5 SQL import. The existing Sprint 3
tables `exams`, `exam_sections`, and `exam_section_items` are sufficient. The
release builder ships application code and safe placeholders only; it never
ships `.env`, production runtime storage, local databases, tests, or secrets.

`scripts/set-infinityfree-ftp-config.ps1` stores an encrypted FTP password for
the current Windows user in the ignored `.infinityfree-ftp.json` file.
`scripts/deploy-infinityfree-ftp.ps1 -ListOnly` performs the required harmless
connectivity/protected-root check. The upload mode checks that production
already contains `.env` and `storage`, skips those paths, skips same-size files,
and performs no remote deletion.
