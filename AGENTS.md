# AGENTS.md

## Repository purpose

`techcompanypay.com` is a legacy PHP salary-search sample with fail-closed
database behavior and offline response-safety tests.

## Development commands

- Supported runtime: PHP 8.2 and 8.4 in CI
- Full verification: `make check`
- Syntax checks: `make lint`
- Behavior and contract tests: `make test`

## Safety guidance

- Keep database credentials and local configuration out of git.
- Preserve generic fail-closed responses when database configuration or legacy
  database support is unavailable.
- Escape reflected HTML, validate salary values, and retain the checked response
  security headers.
- Do not invent the intentionally absent legacy SQL queries or production
  database schema.

## Workflow

1. Read the README, Makefile, PHP entry points, and relevant tests.
2. Run the narrowest behavior test first, then `make check`.
3. Update tests and documentation with behavior or workflow changes.
4. Record unavailable live database validation as a remaining risk.

