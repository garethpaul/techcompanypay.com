# Scripted Baseline Check

## Status: Completed

## Context

The repository had a useful PHP Makefile gate and docs-plan checks, but it did
not have a scriptable repository baseline guard or a `.gitignore` for local
database configuration, dependency folders, logs, and editor metadata.

## Objectives

- Keep `make check` as the root verification command.
- Add a script-level baseline guard for required repository files.
- Check completed docs-plan metadata without needing to inspect PHP tests.
- Keep local secrets, generated dependency folders, logs, and editor metadata
  out of the legacy PHP sample.

## Work Completed

- Added `.gitignore` coverage for local environment files, local PHP config,
  `vendor/`, logs, and common editor metadata.
- Added `scripts/check-baseline.sh`.
- Wired the script into `make check` after the existing verification gate.
- Added PHP docs-plan coverage that keeps the scripted baseline guard in the
  Makefile.
- Updated README, VISION, and CHANGES.

## Verification

- `php tests/check-docs-plans.php`
- `make check`
- `git diff --check`

## Follow-Up Candidates

- Add a `.env.example` if the legacy database configuration is revived.
- Add Composer metadata only if dependencies become part of the maintained
  baseline.
