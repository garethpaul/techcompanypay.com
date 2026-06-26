# POST-Only Search Endpoint

## Status: Completed

## Context

The browser form and asynchronous client submit salary searches with POST, but
`find.php` did not enforce that ownership boundary. GET, HEAD, and arbitrary
methods could therefore reach configured database creation with empty POST
terms, potentially running broad restored queries outside the intended search
flow.

## Design

Read `REQUEST_METHOD` through an injectable helper and require the exact POST
method before database configuration or creation. Non-POST and malformed
method metadata receive HTTP 405, an `Allow: POST` header, and the existing
generic `No matches!` body. Preserve the current POST database, row, byte,
escaping, and failure behavior.

## Work Completed

- Added a POST gate before database configuration and factory invocation.
- Added correct 405/Allow semantics without exposing backend details.
- Added containerized behavior coverage for GET, HEAD, PUT, DELETE, missing
  methods, and the retained POST path.
- Added six hostile mutations for the gate, polarity, status, Allow header,
  early return, and pre-configuration ordering.
- Updated PDO and encoded-response fixtures to declare their intended method.
- Updated repository guidance, security posture, vision, and change history.

## Verification

- The new request-method regression failed before implementation because GET
  reached the database factory.
- `docker run --rm -v "$PWD":/app -w /app php:8.4-cli php tests/check-find-request-method.php`
- `php tests/check-find-request-method-mutations.php` rejects six hostile
  method-boundary mutations.
- `/usr/bin/make check` using official PHP containers when PHP is unavailable
  on the host.
- `git diff --check`.

## Scope Boundaries

- SQL remains intentionally blank; database schema, credentials, query text,
  result rendering, row/byte budgets, frontend requests, and deployment are
  otherwise unchanged.
- No live database or production endpoint was queried.
