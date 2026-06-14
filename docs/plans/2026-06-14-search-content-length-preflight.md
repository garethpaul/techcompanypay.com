# Search Content-Length Preflight

## Status: Planned

## Context

Asynchronous salary search responses are limited to 256 KiB of UTF-8 HTML
before DOM insertion. The browser currently calls `response.text()` before
measuring that boundary, even when a response declares an already-oversized
`Content-Length`.

## Priority

Medium client reliability and resource safety. A trustworthy declared size can
reject a known-oversized response before buffering it, while the existing
measured-byte check remains necessary for missing, malformed, or inaccurate
headers.

## Requirements

- Read `Content-Length` only after the successful exact-`text/html` response
  contract passes.
- Accept only canonical non-negative decimal syntax as a declared byte length.
- Reject a declared length greater than 256 KiB before calling
  `response.text()`.
- Preserve exact-limit acceptance and ignore missing or malformed declarations.
- Retain the measured UTF-8 byte check before `innerHTML` as the final authority.
- Preserve timeout, cancellation, stale-response, busy-state, and native-form
  fallback behavior.
- Add fail-closed static contracts, offline behavior regressions, maintained
  documentation, and completed verification evidence.

## Scope Boundaries

- Do not trust `Content-Length` as proof that an accepted body is within the
  limit.
- Do not add streaming APIs, dependencies, server changes, or browser support
  requirements.
- Do not change the 256 KiB limit or user-facing generic error message.

## Implementation Units

1. Add a strict declared-length helper and preflight in `assets/app.js`.
2. Extend `tests/check-local-search.js` for oversized, exact-limit, missing,
   malformed, and underreported declarations.
3. Extend `tests/check-local-search.php`, maintained docs, and plan contracts.

## Verification

- focused JavaScript search behavior checks
- repository and external-directory `make check`
- hostile parser, ordering, final-byte-check, regression, documentation, and
  plan-status mutations
- generated-artifact, credential-pattern, exact-diff, and staged-path audits

## Risks

- Response headers can be absent or dishonest, so only the existing measured
  UTF-8 body check enforces the final DOM boundary.
- Browser behavior is exercised through the offline harness rather than a live
  database-backed deployment.
