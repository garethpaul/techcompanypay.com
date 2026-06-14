# UTF-8 Search Response Byte Limit

## Status: Completed

## Context

The asynchronous salary search documents a 256 KiB HTML response limit, but
the browser currently checks JavaScript string length. UTF-16 code units are
not response bytes, so multibyte HTML can exceed the documented boundary and
still reach the results region.

## Priority

The bounded HTML contract protects a direct `innerHTML` boundary. Its unit must
match the documented byte limit for both ASCII and multibyte responses.

## Requirements

- Measure the encoded HTML fragment size in bytes before DOM insertion.
- Accept a response exactly at 256 KiB and reject any response above it.
- Keep content-type validation before body reading and size validation before
  `innerHTML` assignment.
- Require the browser byte-measurement primitive for asynchronous enhancement;
  otherwise preserve native form submission and skip prefilled async searches.
- Preserve abort, timeout, stale-response, busy-state, same-origin, and input
  bounds.
- Add fail-closed static contracts, multibyte behavior tests, and maintained
  documentation.

## Verification

- Focused Node behavior and PHP source-contract tests passed, including
  multibyte overflow and missing-`Blob` native-fallback cases.
- The repository and external-directory `make check` passed in an isolated
  Git-backed copy, covering PHP syntax, JavaScript syntax, PHP/Node behavior,
  documentation, workflow policy, and the scripted baseline.
- Eight hostile UTF-8 byte-limit mutations were rejected: primitive,
  measurement, threshold, ordering, multibyte-fixture, fallback,
  documentation, and plan-status regressions.
- Generated-artifact, credential-pattern, protected-path, and exact-diff audits
  passed before commit.

## Scope Boundary

This change does not raise the response cap, sanitize trusted same-origin
fragments, modify PHP/database behavior, add dependencies, or claim live
database coverage.
