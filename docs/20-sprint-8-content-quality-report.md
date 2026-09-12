# Sprint 8 content quality report

Date: 12 September 2026
Environment: disposable local SQLite database seeded from the repository
Command: `content:validate --json`

## Result

The report found 20 content records and 0 issues.

| Content area | Records |
|---|---:|
| Topics | 5 |
| Vocabulary | 3 |
| Grammar lessons | 1 |
| Reading passages | 1 |
| Listening content | 1 |
| Questions | 2 |
| Exercises | 1 |
| Mock exams | 1 |
| Writing prompts | 2 |
| Speaking prompts | 3 |
| Writing submissions | 0 |
| Speaking submissions | 0 |

The validator checked required fields, approved status/type/skill values, source provenance, active-topic relationships, passage word counts, listening audio metadata, active question contexts and option correctness, exercise/exam links, and submitted practice invariants. It does not change any record.

The local rehearsal used only the disposable SQLite database. It did not connect to or modify InfinityFree production data.
