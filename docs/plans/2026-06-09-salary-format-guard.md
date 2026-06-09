# Salary Format Guard

## Status: Completed

## Context

`find.php` formatted result-row salaries with `number_format($salary)` directly.
If the legacy database is revived with malformed or non-numeric salary values,
that path can emit warnings or inconsistent output before the generic no-match
fallback has a chance to help.

## Objectives

- Centralize salary output formatting.
- Format only numeric salary values with `number_format`.
- Render malformed salary values as `$0`.
- Add PHP regression and static checks for the formatting boundary.

## Work Completed

- Added `tcp_salary()` to validate and format salary values.
- Replaced direct `number_format($salary)` calls in title and group result
  rows.
- Added `tests/check-find-salary-format.php` for numeric, non-numeric, and
  non-scalar salary values.
- Extended `tests/check-docs-plans.php` to preserve the helper and reject raw
  salary formatting.
- Updated README, VISION, and CHANGES.

## Verification

- `php -l find.php`
- `php tests/check-find-salary-format.php`
- `php tests/check-docs-plans.php`
- `make check`
- `git diff --check`

## Follow-Up Candidates

- Replace legacy `mysql_*` APIs if the site is revived.
- Parameterize all queries before reconnecting a live database.
