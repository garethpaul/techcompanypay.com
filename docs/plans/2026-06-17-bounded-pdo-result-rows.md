# Bound PDO Result Rows Before Rendering

## Status: Completed

## Summary

Replace the unbounded `PDOStatement::fetchAll()` boundary in `find.php` with
incremental associative-row fetching under an explicit per-query budget.
Reject result sets that exceed the budget before rendering any partial salary
output, while preserving prepared statements, named parameters, generic
failure responses, and the intentionally blank production SQL boundary.

## Problem Frame

The browser client rejects asynchronous HTML responses above 256 KiB, but that
guard runs only after PHP has queried the database and built the response.
`tcp_query_rows()` currently calls `fetchAll(PDO::FETCH_ASSOC)`, which asks PDO
to materialize every remaining row before the endpoint can apply any bound.
A restored query that returns an unexpectedly large result set can therefore
consume unbounded PHP memory even though the browser later refuses the body.

The official PHP manual defines `PDOStatement::fetch()` as retrieving the next
row and returning `false` when no rows remain. That supports a dependency-free,
incremental boundary without changing the PDO configuration or inventing the
missing schema: <https://www.php.net/manual/en/pdostatement.fetch.php>.

## Priorities

1. Bound database result materialization before response rendering.
2. Fail closed on overflow so users never receive silently truncated salary
   data presented as a complete result set.
3. Preserve prepared execution, exact normalized parameter binding, escaping,
   salary formatting, and generic database failure handling.
4. Defer a server-side HTML byte budget and deployment modernization to
   separate changes; neither is required to remove the unbounded row fetch.

## Requirements

- **R1:** Define one explicit positive maximum row count for each title or
  group query.
- **R2:** `tcp_query_rows()` must fetch associative rows incrementally and must
  not call `fetchAll()`.
- **R3:** The effective row budget must be a positive integer; invalid test or
  future call-site overrides must fail before statement execution.
- **R4:** A result set at the exact budget must be returned unchanged.
- **R5:** The first row beyond the budget must raise an internal failure so the
  endpoint returns only the existing `No matches!` response, never a partial
  table or database detail.
- **R6:** Blank SQL must still return no rows without preparing or fetching.
- **R7:** Prepare, execute, row-fetch, invalid-budget, and overflow failures
  must remain covered with dependency-free fake statements and databases.
- **R8:** Static documentation contracts and completed plan evidence must make
  removal, inversion, or off-by-one weakening of the row budget detectable.

## Technical Decisions

- Use a named `TCP_MAX_RESULT_ROWS` constant and an optional helper parameter
  defaulting to that constant. Tests can exercise small exact-boundary cases
  without allocating the production budget. Reject invalid overrides before
  preparing a statement so every execution path retains a real bound.
- Call `fetch(PDO::FETCH_ASSOC)` until end-of-results, checking for overflow
  before appending the extra row. This keeps retained rows bounded and follows
  the repository's existing associative-fetch contract. With
  `PDO::ERRMODE_EXCEPTION`, driver failures throw and `false` is the normal
  end-of-results signal.
- Treat an over-budget result as a database-boundary failure. The endpoint's
  existing `Throwable` catch will preserve the generic response and prevent
  incomplete salary data from appearing authoritative.
- Keep the title and group budgets independent. A future schema-aware query can
  add SQL-level limits, but this application boundary must remain effective for
  injected queries and driver behavior.

## Implementation Units

### U1. Bound incremental PDO row fetching

**Files:** `find.php`

Introduce the result-row constant and update `tcp_query_rows()` to iterate with
`fetch(PDO::FETCH_ASSOC)`, retain at most the configured number of rows, and
throw on the first excess row. Preserve all current blank-SQL, prepare,
execute, parameter-binding, and endpoint exception behavior.

### U2. Characterize exact-budget and overflow behavior

**Files:** `tests/check-find-pdo-boundary.php`, `Makefile`

Update the fake statement to model incremental fetches. Assert zero-row,
exact-budget, one-row-over-budget, fetch-failure, and endpoint generic-response
behavior. Add focused mutation coverage for removing the bound, changing the
overflow comparison, reverting to `fetchAll()`, and returning partial rows.

### U3. Make the result budget durable

**Files:** `tests/check-docs-plans.php`, `README.md`, `SECURITY.md`, `VISION.md`,
`CHANGES.md`, `AGENTS.md`,
`docs/plans/2026-06-17-bounded-pdo-result-rows.md`

Register the plan and source/test contracts, document the server-side row
boundary, and truthfully record completed validation. Preserve the explicit
limitations: no live database, production schema, credentials, deployment, or
browser rendering is exercised.

## Verification

- Run the focused PDO boundary test and mutation-sensitive row-budget checks.
- Run `make check` from the repository and through the absolute Makefile path
  from an external directory.
- Run PHP and JavaScript syntax checks, documentation-plan checks, baseline
  checks, and all existing security and local-search contracts.
- Audit the exact diff, untracked files, generated artifacts, and tracked
  credential patterns.
- Require the exact-head hosted PHP 8.2, 8.4, and 8.5 verification jobs before
  treating the pull request as terminal green.

### Results

- The focused PDO boundary test passed exact-budget, invalid-budget,
  malformed-row, fetch-failure, overflow, and generic endpoint behavior.
- All four hostile PDO row-budget mutations were rejected: disabled validation,
  off-by-one overflow, all-at-once fetching, and partial overflow output.
- The repository and external-directory `make check` passed with PHP syntax,
  JavaScript syntax, all behavior contracts, documentation checks, workflow
  checks, and the scripted baseline gate.
- Exact-diff and untracked-file audits passed.
- The generated-artifact and credential-pattern audits passed.
- No live database, production schema, credentials, deployment, or rendered browser session was exercised.

## Risks And Boundaries

- The historical SQL and schema are absent, so this cannot prove query plans,
  database-side limits, or production cardinality.
- A row-count budget does not replace a future server-side encoded-byte budget
  for individual unusually large database fields.
- No live database, credentials, deployment, or rendered browser session will
  be exercised by the offline contract suite.
