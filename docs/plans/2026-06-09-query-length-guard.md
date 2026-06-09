# Query Length Guard

## Status: Completed

## Context

`find.php` already bounds POST search fields before stripping tags and querying.
`index.php` normalized non-scalar query values, but scalar query values were
reflected into input fields and share metadata at their original length. Escaping
kept the values safe as HTML, but long query strings could still expand the page
and generated share URL unnecessarily.

## Objectives

- Preserve existing scalar query behavior for normal company and city values.
- Keep non-scalar query values fail-closed as empty strings.
- Bound reflected query values before rendering or sharing them.
- Add regression and static validation for the bounded query input path.

## Work Completed

- Limited scalar `tcp_get()` values to 100 characters.
- Added a regression check for long query values in
  `tests/check-index-scalar-inputs.php`.
- Extended `tests/check-docs-plans.php` to require the query-length plan and
  source-level bound.
- Updated README, VISION, and CHANGES notes for bounded query values.

## Verification

- `php -l index.php`
- `php -l find.php`
- `php tests/check-index-scalar-inputs.php`
- `php tests/check-docs-plans.php`
- `make lint`
- `make check`
- `make verify`
- `git diff --check`
