# Finite Salary Format

## Status: Completed

## Context

`is_numeric()` accepts scientific-notation strings. Values such as `1e309` are
numeric input but overflow to infinity when converted to the float required by
`number_format()`, allowing non-currency output through the existing malformed
salary fallback.

## Design

Keep the existing numeric check, convert once to a float, and require
`is_finite()` before formatting. Non-numeric, infinite, and NaN values all use
the existing `0` fallback without exposing database details or partial output.

## Work Completed

- Added finite-value validation to the centralized salary formatter.
- Added regressions for positive and negative scientific overflow, explicit
  infinities, and NaN.
- Updated the static source contract and maintenance documentation.

## Verification

- `php tests/check-find-salary-format.php`
- `/usr/bin/make check`
- `git diff --check`

## PHP Evidence

- PHP numeric strings include exponent notation:
  https://www.php.net/manual/en/language.types.numeric-strings.php
- PHP provides `is_finite()` to distinguish finite floats from infinity and
  NaN: https://www.php.net/manual/en/function.is-finite.php

## Scope Boundaries

- Database configuration, SQL placeholders, row/byte budgets, HTML escaping,
  response headers, frontend search behavior, and normal salary rounding are
  unchanged.
- No live database query was executed.
