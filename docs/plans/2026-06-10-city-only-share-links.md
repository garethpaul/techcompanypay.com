# City-Only Share Links

## Status: Completed

## Context

The search form accepts company and city independently, but canonical share
URLs emitted a query only when company was present. A city-only link therefore
lost its filter, and the browser bootstrap only auto-ran prefilled company
searches even when a city remained in the page.

## Work Completed

- Built canonical share query strings from each non-empty filter independently.
- Used RFC 3986 query encoding and omitted empty parameters.
- Triggered supported-browser search bootstrap when either prefilled field is
  present.
- Added exact PHP URL cases and executable JavaScript coverage for a city-only
  initial request.

## Verification

- `make check`
- Negative PHP and JavaScript source mutation checks
- `git diff --check`
