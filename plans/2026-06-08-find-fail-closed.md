# Find Endpoint Fail-Closed Behavior

## Problem

`find.php` still used `or die(...)` for database connection and selection
failures. If credentials were configured but the backend failed, the endpoint
could expose implementation details instead of returning the same generic
no-match response used for unconfigured deployments.

## TDD Evidence

1. Added `tests/check-find-fail-closed.php` to reject `die()` in `find.php` and
   verify the unconfigured endpoint returns exactly `No matches!`.
2. Replaced connection and database-selection `die()` paths with explicit
   generic fail-closed responses.
3. Wired the new check into `make test`.

## Verification

- `make lint`
- `make test`
- `make verify`
- `git diff --check`
