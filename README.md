# techcompanypay.com

<!-- README-OVERVIEW-IMAGE -->
![Project overview](docs/readme-overview.svg)

## Overview

`garethpaul/techcompanypay.com` is a public sample, documentation, or utility project. TechCompanyPay.com repository

This README is based on the checked-in source, manifests, scripts, and repository metadata on the `master` branch. The project language mix found during review was: PHP (2).

## Repository Contents

- `README.md` - project overview and local usage notes
- `CHANGES.md` - maintenance history for PHP safety checks
- `Makefile` - local verification entry points
- `.gitignore` - local secret, dependency, log, and editor metadata ignores
- `TODO` - legacy deployment and product notes
- `docs/plans` - completed maintenance plans for the current baseline
- `find.php` - legacy search endpoint
- `index.php` - search page and sharing metadata
- `plans` - historical implementation notes
- `SECURITY.md` - security reporting and disclosure guidance
- `scripts/check-baseline.sh` - repository maintenance baseline guard
- `tests` - PHP syntax and output behavior checks
- `VISION.md` - project direction and maintenance guardrails

Additional scan context:

- Source directories: no top-level source directories detected
- Dependency and build manifests: none detected
- Entry points or build surfaces: none detected
- Test-looking files: tests/check-find-fail-closed.php, tests/check-index-escaping.php

## Getting Started

### Prerequisites

- Git
- PHP CLI for local syntax and output checks

### Setup

```bash
git clone https://github.com/garethpaul/techcompanypay.com.git
cd techcompanypay.com
```

The setup commands above are derived from repository files. Legacy mobile, Python, or JavaScript samples may require older SDKs or package versions than a modern workstation uses by default.

## Running or Using the Project

- Serve `index.php` with a PHP-capable web server for the legacy demo page.
- `find.php` is the legacy search endpoint and requires database constants to
  be configured before live use.

## Testing and Verification

- `make check` runs PHP syntax checks, query-escaping output tests,
  scalar-safe query input checks, and a fail-closed check for the unconfigured
  legacy search endpoint.
- Search endpoint checks also require non-scalar POST fields to normalize to
  empty strings before legacy query handling.
- Search endpoint checks also require database salary values to be formatted
  only after numeric validation, with invalid values displayed as `$0`.
- `make check` also verifies that PHP entry points keep basic response security
  headers, including frame denial and a deny-by-default browser permissions
  policy for camera, microphone, and geolocation. The header checks also require
  a Content-Security-Policy compatible with the legacy HTTPS widgets and inline
  snippets.
- `scripts/check-baseline.sh` checks required project files, completed
  docs-plan metadata, verification documentation, and local secret/editor
  metadata hygiene.
- `make check` rejects protocol-relative or insecure external script, image,
  and stylesheet references in the legacy page.
- `make check` also requires completed canonical plans under `docs/plans`.

When the required SDK or runtime is unavailable, use static checks and source review first, then verify on a machine that has the matching platform toolchain.

## Configuration and Secrets

- No required secret or credential file was identified in the repository scan. If you add integrations later, keep secrets out of git.

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
- See `docs/plans/2026-06-09-scalar-post-inputs.md` for the scalar search POST
  normalization guard.
- See `docs/plans/2026-06-09-salary-format-guard.md` for the salary output
  formatting guard.
- See `docs/plans/2026-06-08-explicit-https-assets.md` for the external asset
  URL guard.
- See `docs/plans/2026-06-09-scripted-baseline-check.md` for the scripted
  repository baseline guard and local secret/editor metadata ignores.

## Contributing

Keep changes small and tied to the project that is already present in this repository. For code changes, document the toolchain used, avoid committing generated dependency directories or local configuration, and update this README when setup or verification steps change.
