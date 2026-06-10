# techcompanypay.com

<!-- README-OVERVIEW-IMAGE -->
![Project overview](docs/readme-overview.svg)

## Overview

`garethpaul/techcompanypay.com` is an archived salary-search prototype for
technology companies and locations. It preserves the original PHP demo while
keeping its public-data disclaimer and legacy deployment constraints explicit.

The maintained surface uses PHP, repository-owned JavaScript and CSS, shell
verification, and PHP contract tests.

## Repository Contents

- `README.md` - project overview and local usage notes
- `CHANGES.md` - maintenance history for PHP safety checks
- `Makefile` - local verification entry points
- `.gitignore` - local secret, dependency, log, and editor metadata ignores
- `TODO` - legacy deployment and product notes
- `assets` - repository-owned browser JavaScript and responsive styles
- `docs/plans` - completed maintenance plans for the current baseline
- `find.php` - legacy search endpoint
- `index.php` - search page and sharing metadata
- `plans` - historical implementation notes
- `SECURITY.md` - security reporting and disclosure guidance
- `scripts/check-baseline.sh` - repository maintenance baseline guard
- `tests` - PHP syntax and output behavior checks
- `VISION.md` - project direction and maintenance guardrails

Additional scan context:

- Source directories: `assets`, `scripts`, and `tests`
- Dependency and build manifests: none detected
- Entry points: `index.php` and `find.php`
- Verification surface: `Makefile`, `scripts/check-baseline.sh`, and `tests/*.php`

## Getting Started

### Prerequisites

- Git
- PHP CLI for local syntax and output checks
- Node.js 18 or newer for JavaScript syntax validation
- PHP 8.2 or 8.4 for parity with hosted verification

### Setup

```bash
git clone https://github.com/garethpaul/techcompanypay.com.git
cd techcompanypay.com
```

## Running or Using the Project

- Start a local server with `php -S 127.0.0.1:8000`, then open
  `http://127.0.0.1:8000/`.
- `find.php` is the legacy search endpoint and requires database constants to
  be configured before live use.
- The search form submits directly to `find.php` without JavaScript and uses a
  bounded same-origin asynchronous request when modern browser APIs are
  available.

## Testing and Verification

- `make check` runs PHP syntax checks, query-escaping output tests,
  scalar-safe query input checks, and a fail-closed check for the unconfigured
  legacy search endpoint. It also validates the local browser script with Node
  and executes dependency-free search behavior coverage for fallback,
  failures, input bounds, and out-of-order responses. Static contracts enforce
  local assets, CSP, and CI workflow boundaries.
- Query-string values rendered into the page or share metadata are bounded
  before escaping so long reflected inputs do not expand the response.
- Search endpoint checks also require non-scalar POST fields to normalize to
  empty strings before legacy query handling.
- Search endpoint checks also require database salary values to be formatted
  only after numeric validation, with invalid values displayed as `$0`.
- `make check` also verifies that PHP entry points keep basic response security
  headers, including frame denial and a deny-by-default browser permissions
  policy for camera, microphone, and geolocation. The header checks also require
  a Content-Security-Policy that limits executable scripts and styles to
  repository-owned same-origin assets.
- `scripts/check-baseline.sh` checks required project files, completed
  docs-plan metadata, verification documentation, and local secret/editor
  metadata hygiene.
- `make check` rejects obsolete or remote runtime scripts plus
  protocol-relative and insecure external asset references.
- `make check` also requires completed canonical plans under `docs/plans`.
- GitHub Actions runs the same `make check` gate on PHP 8.2 and 8.4 with
  read-only permissions, bounded jobs, and immutable action pins.
- Narrow targets are available as `make lint`, `make test`, `make build`, and
  `make verify`.

The browser search can be exercised without database credentials; the
unconfigured endpoint intentionally returns `No matches!`.

## Configuration and Secrets

- Live result queries require the database constants at the top of `find.php`.
  Keep real values in an ignored local configuration mechanism if the archived
  site is revived; do not commit credentials.

## Security and Privacy Notes

- Review changes touching network requests, sockets, or service endpoints; examples from the scan include find.php, index.php.
- Review changes touching file, media, JSON, XML, CSV, OCR, or data parsing; examples from the scan include index.php.
- Review changes touching database, model, or persistence code; examples from the scan include find.php.

## Maintenance Notes

- See `SECURITY.md` for vulnerability reporting and safe research guidance.
- See `VISION.md` for project direction and contribution guardrails.
- See `docs/plans/2026-06-08-techcompanypay-baseline.md` for the canonical PHP
  search safety baseline.
- See `docs/plans/2026-06-08-response-security-headers.md` for the response
  header guard.
- See `docs/plans/2026-06-09-frame-options-header.md` for the frame-deny
  response header guard.
- See `docs/plans/2026-06-09-permissions-policy-header.md` for the browser
  permissions policy guard.
- See `docs/plans/2026-06-09-content-security-policy-header.md` for the
  Content-Security-Policy response header guard.
- See `docs/plans/2026-06-09-scalar-query-inputs.md` for the scalar query
  normalization guard.
- See `docs/plans/2026-06-09-query-length-guard.md` for the bounded query
  string input guard.
- See `docs/plans/2026-06-09-scalar-post-inputs.md` for the scalar search POST
  normalization guard.
- See `docs/plans/2026-06-09-salary-format-guard.md` for the salary output
  formatting guard.
- See `docs/plans/2026-06-08-explicit-https-assets.md` for the external asset
  URL guard.
- See `docs/plans/2026-06-09-scripted-baseline-check.md` for the scripted
  repository baseline guard and local secret/editor metadata ignores.
- See `docs/plans/2026-06-10-local-search-and-ci.md` for the self-contained
  browser runtime, progressive form fallback, and stricter script policy.
- The legacy `mysql_*` database API and intentionally blank SQL statements are
  not production-ready. A revival should migrate to PDO or mysqli with
  parameterized queries before adding real credentials or data.

## Contributing

Keep changes small and tied to the project that is already present in this repository. For code changes, document the toolchain used, avoid committing generated dependency directories or local configuration, and update this README when setup or verification steps change.
