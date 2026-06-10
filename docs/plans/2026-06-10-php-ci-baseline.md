# PHP CI Baseline

Status: Completed

## Goal

Run the repository's existing PHP syntax, behavior, security-header, asset, and
documentation checks on every push and pull request without requiring database
credentials.

## Changes

- Add a least-privilege GitHub Actions workflow with bounded execution time.
- Verify the supported PHP 8.2 and 8.4 runtimes.
- Pin third-party actions to immutable commit SHAs.
- Add a PHP regression test for the workflow's permissions, matrix, timeout,
  action pins, and shared `make check` command.

## Verification

- `make check`
- Parse the workflow as YAML.
- Confirm a negative workflow mutation is rejected by the contract test.

