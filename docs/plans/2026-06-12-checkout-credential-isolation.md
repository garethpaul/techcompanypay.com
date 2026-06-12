# Checkout Credential Isolation

## Status: Completed

## Context

The reviewed workflow already used immutable actions, read-only permissions,
fixed runners, bounded timeouts, and deterministic PHP/Node versions. Checkout
still retained the workflow token in the worktree, and the contract did not
reject an additional unreviewed workflow file.

## Objectives

- Disable checkout credential persistence.
- Keep the repository's hosted automation surface to one reviewed workflow.
- Add executable regression checks for both boundaries.
- Preserve the existing PHP matrix and complete `make check` command.

## Work Completed

- Added `persist-credentials: false` to the checkout step.
- Required exactly one workflow YAML file under `.github/workflows`.
- Required exactly one credential-isolation setting for the single checkout.
- Updated project and security documentation.

## Verification

- `php tests/check-ci-workflow.php`
- `php tests/check-docs-plans.php`
- `make check`
- `make -f /path/to/techcompanypay.com/Makefile check` from an external cwd
- `git diff --check`
