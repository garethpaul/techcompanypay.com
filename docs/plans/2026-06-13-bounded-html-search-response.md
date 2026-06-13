# Bounded HTML Search Response

## Status: Planned

## Context

The progressive async search now requires abort support, enforces a 10-second
timeout, and prevents stale requests from overwriting newer results. A
successful response is still converted to text and assigned to `innerHTML`
without checking its media type or size. An unexpected same-origin response or
an oversized generated result can therefore cross the DOM insertion boundary.

## Priority

Only the expected bounded HTML fragment should reach the live results region;
all other successful HTTP responses must fail through the existing generic
error state.

## Requirements

- R1. Accept only `text/html` responses, normalizing case, surrounding
  whitespace, and an optional charset parameter.
- R2. Reject missing, malformed, or non-HTML content types before reading the
  response body.
- R3. Reject response text longer than 256 KiB before assigning `innerHTML`,
  while accepting a body exactly at the limit.
- R4. Preserve the same-origin POST, 100-character field limits, 10-second
  abort timeout, active-request cancellation, stale-response guard, generic
  error text, automatic prefilled search, and native form fallback.
- R5. Preserve PHP response headers, endpoint output, CSP, and the intentionally
  absent live database configuration.
- R6. Add behavior coverage, static contracts, hostile mutations,
  documentation, and full `make check` verification.

## Scope Boundaries

- Do not add an HTML sanitizer, external JavaScript dependency, or service
  worker.
- Do not change search fields, endpoint markup, timeout duration, share URLs,
  database APIs, or query definitions.
- Do not claim live database or browser rendering coverage from the offline
  Node/PHP harnesses.

## Implementation Units

### Response contract before DOM insertion

**Files:** `assets/app.js`

- Define a named 256 KiB response-text limit.
- Normalize the response `Content-Type` and require exact `text/html`.
- Enforce the text-length boundary before the latest request may update
  `innerHTML`.

### Regression coverage and maintenance record

**Files:** `tests/check-local-search.js`, `tests/check-local-search.php`,
`tests/check-docs-plans.php`, `README.md`, `SECURITY.md`, `VISION.md`,
`CHANGES.md`, `docs/plans/2026-06-13-bounded-html-search-response.md`

- Cover normalized HTML acceptance, exact-size acceptance, oversized
  rejection, and missing or non-HTML media-type rejection.
- Reject removed media-type checks, weakened size comparisons, changed limits,
  or bypassed regression coverage.

## Verification Plan

- `node tests/check-local-search.js`
- `php tests/check-local-search.php`
- PHP and JavaScript syntax checks
- `make check`
- focused response-contract mutations
- external-working-directory `make check`
- staged-path, generated-artifact, secret-pattern, and `git diff --check` audits

## Assumptions

- The existing PHP endpoint's `Content-Type: text/html; charset=UTF-8` header is
  the authoritative response contract for successful fragments.
- A JavaScript string-length limit bounds DOM insertion size, not transport
  bytes; the 10-second abort remains the transport-lifetime bound.
