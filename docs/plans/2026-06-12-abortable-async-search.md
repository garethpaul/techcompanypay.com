# Abortable Async Search

## Status: Completed

## Context

The progressive-enhancement gate enables asynchronous search when `fetch` and
`URLSearchParams` exist. Without `AbortController`, the enhanced path cannot
enforce its 10-second timeout; a hung request can leave the search button
disabled indefinitely even though the native POST form remains available.

## Priority

Only enable asynchronous search when every capability required by its bounded
request lifecycle is present. Otherwise preserve the native form submission.

## Requirements

- R1. Require `fetch`, `URLSearchParams`, and `AbortController` before enabling
  asynchronous search.
- R2. Preserve native form submission when any required capability is absent.
- R3. Preserve the 10-second abort timeout, stale-response guard, request
  cancellation, error message, and successful rendering path.
- R4. Add JavaScript behavior coverage for missing `AbortController` on submit
  and automatic prefilled search.
- R5. Add static contracts, hostile mutations, documentation, and full
  `make check` verification.

## Scope Boundaries

- Do not change endpoint behavior, search fields, timeout duration, response
  HTML, share URLs, CSP, or legacy database code.
- Do not add a polyfill or external JavaScript dependency.
- Live database behavior remains outside this offline pass.

## Implementation Units

### Progressive-enhancement capability gate

**Files:** `assets/app.js`

- Require abort support before intercepting form submission or starting an
  automatic prefilled request.

### Regression coverage and maintenance record

**Files:** `tests/check-local-search.js`, `tests/check-local-search.php`,
`tests/check-docs-plans.php`, `README.md`, `SECURITY.md`, `VISION.md`,
`CHANGES.md`, `docs/plans/2026-06-12-abortable-async-search.md`

- Cover missing-abort fallback and reject a weakened capability check.

## Verification Plan

- `node tests/check-local-search.js`
- `php tests/check-local-search.php`
- `make check`
- focused capability mutations
- external-directory `make check`
- `git diff --check`

## Verification Record

- `node tests/check-local-search.js` passed, including native submit and
  prefilled-search fallback when `AbortController` is absent.
- `php tests/check-local-search.php` passed with the abort capability, signal,
  and behavior-test contracts enforced.
- Four focused hostile mutations were rejected: removing the capability gate,
  restoring optional controller construction, and removing either missing-abort
  behavior assertion.
- `php -l tests/check-docs-plans.php` and `git diff --check` passed.
- `make check` passed the complete PHP, JavaScript, documentation, CI-contract,
  and baseline verification suite.

## Remaining Risks

- Offline harnesses do not exercise browser-specific networking or a live PHP
  database response.
