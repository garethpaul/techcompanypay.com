# PDO Database Boundary

## Status: Planned

## Summary

Replace the removed `mysql_*` runtime boundary in `find.php` with an
exception-safe PDO connection and parameterized query boundary that executes on
the repository's maintained PHP 8 matrix. Preserve the endpoint's generic
`No matches!` failure behavior and do not invent the archived site's missing
SQL schema, credentials, or production deployment configuration.

## Problem Frame

The repository verifies `find.php` on PHP 8.2, 8.4, and 8.5, but its configured
database path still calls `mysql_connect`, `mysql_select_db`,
`mysql_real_escape_string`, and other `ext/mysql` functions removed in PHP 7.
The current blank credentials make tests fail closed before those calls, which
hides the fact that any attempted revival would terminate on unsupported APIs.

The title and group SQL statements are also intentionally blank because the
historical schema is not present. This task can modernize the connection,
parameter binding, fetch, and error boundary without fabricating queries or
claiming live database compatibility.

## Priorities

1. Remove the PHP 8 runtime blocker and unsafe string-escaping query boundary.
2. Add dependency-free behavior tests for connection options, parameterized
   execution, row rendering, and generic failure handling.
3. Keep environment configuration, secrets, and database exceptions out of
   responses and tracked files.
4. Defer schema-specific SQL and live database validation until authoritative
   table and column definitions exist.

## Requirements

- **R1:** `find.php` must contain no `mysql_*` calls or legacy database
  constants with credential placeholders.
- **R2:** Database configuration must come from `TCP_DB_DSN`, `TCP_DB_USER`,
  and `TCP_DB_PASSWORD`; incomplete configuration must retain the exact generic
  `No matches!` response.
- **R3:** PDO construction must use exception error mode, associative fetches,
  and native prepared statements, while database exceptions remain internal.
- **R4:** Query execution must use prepared statements and named parameters for
  the normalized search term and city. User input must never be concatenated
  into SQL or manually escaped.
- **R5:** The absent title and group SQL must remain explicit configuration
  boundaries. Blank statements must not call the database and must preserve
  the no-match result.
- **R6:** Offline tests must characterize successful parameter binding and row
  rendering with injected fakes, plus connection, prepare, execute, and fetch
  failure paths without a PDO driver or live service.
- **R7:** Static contracts, documentation, and completed plan evidence must
  prevent regression to removed APIs, committed credentials, interpolated
  input, or unverified production claims.

## Technical Decisions

- Use a small PDO factory function rather than global connection side effects.
  Tests can inject a callable factory, while production uses the built-in PDO
  constructor only after complete environment configuration is present.
- Use a query helper that accepts a PDO-compatible object and returns rows.
  This keeps statement preparation, execution, and fetch behavior independently
  testable without adding Composer packages or a database service.
- Keep title and group SQL as named blank constants until the schema is known.
  Prepared execution is exercised with test-owned statements, but production
  does not issue invented SQL.
- Catch `Throwable` at the endpoint boundary and return only `No matches!`.
  No exception message, DSN, username, password, SQL, or submitted value may be
  reflected.

## Implementation Units

### U1. Introduce the PDO and prepared-query boundary

**Files:** `find.php`

Add environment configuration helpers, PDO options, injectable connection
construction, prepared execution with named parameters, and associative row
handling. Route both result groups through shared execution and rendering
helpers while preserving the existing HTML and salary/link escaping behavior.

### U2. Characterize the executable database contract

**Files:** `tests/check-find-pdo-boundary.php`, `tests/check-find-fail-closed.php`,
`Makefile`

Use dependency-free fake connection and statement objects to prove exact SQL
preparation, term/city parameter binding, row fetch behavior, no query for blank
statements, and generic handling for connection, prepare, execute, and fetch
failures. Keep the unconfigured include path's exact response covered.

### U3. Make the modernization durable

**Files:** `tests/check-docs-plans.php`, `scripts/check-baseline.sh`, `README.md`,
`SECURITY.md`, `VISION.md`, `CHANGES.md`, `AGENTS.md`,
`docs/plans/2026-06-15-pdo-database-boundary.md`

Require the PDO options, named parameters, environment-only configuration,
absence of `mysql_*`, new behavior test, documentation boundary, and truthful
completion evidence. Document that real schema queries and live deployment
remain deferred.

## Verification

- Run the focused PDO boundary and fail-closed PHP tests.
- Run PHP syntax checks and the complete repository `make check` gate from the
  repository and an external directory.
- Run isolated hostile mutations for removed API restoration, missing native
  prepares, interpolated input, leaked errors, missing tests, documentation
  drift, and incomplete plan status.
- Audit changed paths, generated artifacts, credential patterns, whitespace,
  local/upstream equality, and exact hosted head evidence.

## Scope Boundaries

- Do not add real credentials, `.env` files, database services, Composer
  dependencies, migrations, tables, columns, or production SQL.
- Do not claim a live MySQL deployment works without the historical schema.
- Do not change the browser request, response-size, timeout, abort, stale-result,
  busy-state, security-header, escaping, salary-format, or share-link contracts.
- Do not merge or close any stacked pull request without explicit authorization.

## References

- PHP migration guidance records that all `ext/mysql` functions were removed in
  PHP 7: <https://www.php.net/manual/en/migration70.incompatible.php>
- PDO prepared statements support named placeholders and bound execution:
  <https://www.php.net/manual/en/pdo.prepare.php>
- PDO MySQL uses emulated prepares by default, so this plan requires native
  prepares explicitly: <https://www.php.net/manual/en/ref.pdo-mysql.php>
