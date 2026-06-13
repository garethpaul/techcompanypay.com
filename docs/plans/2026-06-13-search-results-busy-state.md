# Search Results Busy State

## Status: Completed

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

## Work Completed

- Marked the polite live results region busy when an asynchronous search
  starts.
- Cleared busy state only from the latest request's shared settlement path.
- Added behavior coverage for active, successful, failed, timed-out, stale,
  unsupported, missing-abort, and prefilled searches.
- Added fail-closed source/test contracts and maintenance documentation.

## Verification

- `node tests/check-local-search.js` passed the focused offline behavior suite.
- `php tests/check-local-search.php` passed the static search contract.
- Full `make check` passed PHP and JavaScript syntax, all PHP/Node behavior and
  contract checks, workflow policy, documentation plans, and the baseline
  shell guard.
- The same full gate passed from an external working directory.
- Eight focused mutations covering missing/late start state, missing or stale
  clearing, removed regression assertions, documentation drift, and regressed
  plan status were rejected.
- The single workflow YAML parsed successfully; diff whitespace,
  generated-artifact, and intended-diff secret audits passed.
- Plan-aware correctness, accessibility, testing, and maintainability review
  found no actionable findings.
- Browser smoke testing was unavailable because `agent-browser` is not
  installed; no live database or screen-reader claim is made from the offline
  Node harness.
