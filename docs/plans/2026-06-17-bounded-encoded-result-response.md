# Bound Encoded Database Result Responses

Status: Completed

## Context

The search endpoint now limits each PDO query to 500 rows, but a single large
title or group value can still expand substantially during URL and HTML
encoding. The browser rejects asynchronous responses above 256 KiB, while the
server currently constructs and returns an encoded result without enforcing
the same byte boundary.

## Goal

Apply one byte-accurate 256 KiB budget to the complete encoded database result
response before any output is emitted, while preserving the existing row,
escaping, salary-formatting, database, and generic failure contracts.

## Requirements

1. Measure rendered output with byte length after URL and HTML encoding.
2. Share one positive byte budget across title and group result tables rather
   than allowing each table its own full allowance.
3. Accept a response exactly at the configured budget and reject the next byte.
4. Fail closed with only `No matches!` when rendering exceeds the budget; never
   emit a partial result table.
5. Preserve empty-result behavior, HTML escaping, LinkedIn URL encoding,
   salary formatting, the 500-row query cap, and supported PHP 8.2-8.5 syntax.
6. Add behavior, mutation, static, documentation, and completed-plan contracts
   without adding a runtime dependency or requiring a live database.

## Implementation Units

### Bounded Render Assembly

- Add a shared encoded-result byte constant and append guard in `find.php`.
- Refactor title and group rendering through bounded append operations so the
  combined response consumes one budget.
- Keep endpoint output buffered in memory until both tables render
  successfully, then emit once.

### Regression And Mutation Coverage

- Add focused PHP coverage for small responses, exact-budget success,
  one-byte overflow, encoded multibyte/HTML expansion, invalid budgets, and
  endpoint fail-closed output.
- Add hostile source mutations that weaken byte measurement, the inclusive
  boundary, shared-budget assembly, endpoint wiring, or regression
  registration.
- Register the new checks in `Makefile`, `scripts/check-baseline.sh`, and
  `tests/check-docs-plans.php`.

### Guidance And Evidence

- Update `README.md`, `SECURITY.md`, `VISION.md`, `CHANGES.md`, and `AGENTS.md`
  with the server-side encoded-response boundary.
- Mark this plan completed only after focused, mutation, repository,
  external-directory, syntax, artifact, secret, and exact-diff validation.

## Verification

- Run the focused encoded-response behavior and mutation contracts.
- Run `make check` from the repository and through the absolute Makefile path
  from `/tmp`.
- Run PHP syntax checks and the complete endpoint contract suite on the local
  supported runtime; rely on exact-head hosted PHP 8.2, 8.4, and 8.5 lanes for
  cross-version confirmation after push.
- Audit the exact diff, generated artifacts, credentials, conflicts, modes,
  binaries, dependencies, workflows, and upstream relationship before commit.

## Risks And Boundaries

- Byte accounting must occur after escaping because entity and percent
  encoding can expand otherwise short source values.
- The complete title-plus-group response must share one allowance; independent
  table limits would exceed the browser contract when concatenated.
- The endpoint remains intentionally disabled without reviewed SQL and database
  configuration. Tests use deterministic fake rows and fake PDO boundaries.
- This protects encoded response size, not live database latency, schema
  correctness, TLS, credentials, or deployment behavior.

## Assumptions

- The existing browser limit of 262,144 bytes is the authoritative maximum for
  the server-rendered asynchronous result payload.
- `strlen` is the portable PHP byte-length primitive for the supported runtime
  matrix.

## Work Completed

- Added a shared 262,144-byte append guard that measures final encoded chunks
  with `strlen` and accepts an exact-budget response.
- Routed title and group rendering through one cumulative buffer so independent
  table allowances cannot exceed the browser response contract.
- Preserved endpoint buffering and generic exception handling so overflow emits
  only `No matches!` with no partial table.
- Added focused behavior, five hostile mutations, static registration,
  guidance, changelog, and completed-plan evidence contracts.

## Verification Results

- The repository and external-directory `make check` passed with PHP and
  JavaScript syntax, all endpoint/browser contracts, documentation, workflow,
  and baseline checks.
- All five hostile encoded-result byte-budget mutations were rejected across
  validation, encoded byte measurement, exact-budget inclusion, cumulative
  accounting, and endpoint shared-budget wiring.
- Exact-budget, one-byte-overflow, encoded-entity, HTML/URL expansion, invalid
  limit, combined-table, and no-partial-output behaviors passed locally.
- Exact diff, generated-artifact and credential-pattern audits passed with only
  the intended implementation, tests, registrations, guidance, and plan paths.
- No live database, production schema, credentials, deployment, or rendered browser session was exercised.
