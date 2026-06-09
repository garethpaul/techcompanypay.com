# Scalar Search POST Input Guard

## Status: Completed

## Context

`find.php` accepts POST fields for the legacy company and city search endpoint.
The active database query strings are still intentionally blank, but if this
endpoint is restored later, array-style POST fields should not reach string
normalization or legacy query construction.

## Objectives

- Treat non-scalar search POST fields as empty input.
- Preserve existing `strip_tags` and 100-character truncation behavior for
  normal scalar strings.
- Add a CLI regression check that fails if array POST values emit warnings or
  return nested data.

## Work Completed

- Updated `tcp_post_value()` to return a string only for scalar POST values.
- Cast scalar values to strings before stripping and truncating them.
- Added `tests/check-find-scalar-inputs.php` and wired it into `make test`.
- Documented the POST request-shape guard in README, VISION, and CHANGES.

## Verification

- `php tests/check-find-scalar-inputs.php`
- `make lint`
- `make test`
- `make build`
- `make check`
- `make verify`
- `git diff --check`
