# Search Results Busy State

## Status: Planned

## Context

The progressive salary search updates a polite live region with loading,
success, and failure content. It disables the submit button while the current
request is active, but the results region does not expose that its content is
being updated. Assistive technology therefore receives text changes without a
durable in-progress state.

## Priority

The live results region should be busy only while the latest asynchronous
search is active. Superseded requests must not clear the state owned by a newer
request, and native form fallback must remain unchanged.

## Requirements

- R1. Set `aria-busy="true"` on the results region before starting an
  asynchronous request.
- R2. Clear `aria-busy` when the latest request settles after success, failure,
  or timeout.
- R3. Keep a stale or aborted predecessor unable to clear the busy state for a
  newer active request.
- R4. Preserve same-origin POST, input bounds, response media-type and size
  validation, timeout, cancellation, stale-response protection, prefilled
  search, and native fallback behavior.
- R5. Add mutation-sensitive behavior and static contracts, maintenance
  documentation, and full `make check` verification.

## Implementation Units

### Active-request accessibility state

**Files:** `assets/app.js`

Mark the live results region busy when the latest asynchronous search starts
and clear that state only from the latest request's shared settlement path.

### Regression coverage and maintenance record

**Files:** `tests/check-local-search.js`, `tests/check-local-search.php`,
`tests/check-docs-plans.php`, `README.md`, `SECURITY.md`, `VISION.md`,
`CHANGES.md`, `docs/plans/2026-06-13-search-results-busy-state.md`

Cover start, success, failure, timeout, stale-request, unsupported-browser, and
prefilled-search semantics. Reject missing, late, unconditional, or stale
busy-state updates.

## Verification Plan

- `node tests/check-local-search.js`
- `php tests/check-local-search.php`
- JavaScript and PHP syntax checks
- full `make check` locally and from an external working directory
- focused busy-state mutations
- staged-path, generated-artifact, secret-pattern, and `git diff --check` audits

## Scope Boundaries

- Do not change endpoint markup, search fields, result HTML, timeout duration,
  response limits, share URLs, database behavior, or workflow policy.
- Do not add dependencies or claim browser/screen-reader validation from the
  offline Node harness.
