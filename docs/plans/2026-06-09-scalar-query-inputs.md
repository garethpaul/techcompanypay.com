# Scalar Query Input Guard

## Status: Completed

## Context

The landing page accepted `c` and `l` query parameters and passed them directly
to HTML escaping and share URL generation. Normal browser requests provide
strings, but PHP can expose repeated bracket-style query parameters as arrays.
Those arrays triggered warnings in helpers that expected strings.

## Objectives

- Treat non-scalar query parameters as empty input.
- Keep existing HTML escaping and share URL behavior for normal string input.
- Add a CLI regression check that fails if array query values emit warnings or
  render nested values.

## Work Completed

- Updated `tcp_get()` to return a string only for scalar query values.
- Added `tests/check-index-scalar-inputs.php` and wired it into `make test`.
- Documented the request-shape guard in README, VISION, and CHANGES.

## Verification

- `php tests/check-index-scalar-inputs.php`
- `make lint`
- `make test`
- `make build`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Apply the same scalar-input helper pattern to future POST-backed search
  fields if the legacy database integration is restored.
- Consolidate duplicate header helpers if more PHP entry points are added.
