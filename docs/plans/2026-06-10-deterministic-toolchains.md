# Deterministic Toolchains

Status: Completed

## Context

The existing PHP matrix covered 8.2 and 8.4, but it used a moving Ubuntu label
and relied on whatever Node version happened to be installed on the runner.
Make targets also failed when invoked outside the repository.

## Changes

- Fixed CI to Ubuntu 24.04 and added workflow concurrency cancellation.
- Added pinned Node 24 setup before JavaScript checks.
- Expanded PHP coverage to 8.2, 8.4, and 8.5.
- Added exact action-version comments while retaining immutable SHA pins.
- Anchored every Makefile PHP, Node, and shell path to the repository root.
- Extended workflow, docs-plan, and baseline tests to preserve the toolchain.

## Verification

- `make check`
- `make -f /path/to/techcompanypay.com/Makefile check` from outside the repository
- negative workflow mutation checks
- `git diff --check`
- GitHub Actions PHP matrix
