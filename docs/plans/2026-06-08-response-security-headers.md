# Response Security Headers

## Status: Completed

## Context

`index.php` and `find.php` render legacy HTML responses. The output paths were
escaped and fail-closed, but neither entry point declared a content type or
basic response-hardening headers.

## Objectives

- Send an explicit UTF-8 HTML content type from both PHP entry points.
- Add `X-Content-Type-Options: nosniff`.
- Add a referrer policy that avoids leaking full query strings cross-origin.
- Cover the header contract in `make check` without needing a web server.

## Work Completed

- Added `tcp_send_security_headers()` to `index.php` and `find.php`.
- Sent content type, `nosniff`, and referrer policy headers before rendering.
- Added `tests/check-security-headers.php` and wired it into `make check`.
- Updated README, VISION, and CHANGES.

## Verification

- `php -l index.php`
- `php -l find.php`
- `php tests/check-security-headers.php`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Add a content security policy after inventorying required legacy third-party
  scripts and social widgets.
- Replace legacy `mysql_*` APIs before reviving live search functionality.
