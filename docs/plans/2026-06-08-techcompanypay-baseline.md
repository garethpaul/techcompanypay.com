# TechCompanyPay.com Baseline

## Status: Completed

## Context

`techcompanypay.com` is a legacy PHP prototype for searching public,
aggregated salary/title information. The current maintenance baseline should
preserve the prototype while keeping user input escaping and backend failure
behavior checked locally.

## Objectives

- Keep database credentials empty and out of source control.
- Escape rendered search parameters before placing them in HTML and metadata.
- Fail closed with a generic no-match response when backend configuration or
  legacy PHP database support is unavailable.
- Run PHP syntax and output checks through `make check`.
- Maintain completed maintenance plans under `docs/plans`.

## Work Completed

- Confirmed `make check` runs PHP syntax checks and output behavior tests.
- Added canonical `docs/plans` coverage for the current PHP safety baseline.
- Added a PHP docs-plan test that requires completed plans with `make check`
  verification.
- Updated README, VISION, and CHANGES to make the baseline discoverable.

## Verification

- `php -l index.php`
- `php -l find.php`
- `php tests/check-index-escaping.php`
- `php tests/check-find-fail-closed.php`
- `php tests/check-docs-plans.php`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Replace legacy `mysql_*` APIs if the prototype is revived.
- Document data sources, freshness, accuracy caveats, and removal policy.
