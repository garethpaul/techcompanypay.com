# Changes

## 2026-06-10

- Added least-privilege GitHub Actions verification on PHP 8.2 and 8.4.
- Added regression coverage for the workflow matrix, timeout, action pins,
  permissions, and shared `make check` command.
- Restored the primary salary search with bounded, same-origin JavaScript and a
  functional native form fallback.
- Replaced unreachable remote runtime assets with repository-owned JavaScript
  and responsive CSS.
- Removed obsolete sharing and analytics scripts, then restricted scripts and
  styles to same-origin resources with regression coverage.
- Extended the existing PHP CI matrix with JavaScript syntax and local-search
  contract checks through `make check`.

## 2026-06-09

- Bounded scalar query-string values before rendering or sharing them, with PHP
  regression coverage and static validation.
- Added a Content-Security-Policy header to both PHP entry points while
  preserving compatibility with legacy inline snippets and HTTPS widgets.
- Added `scripts/check-baseline.sh` and `.gitignore` coverage for required
  files, completed plan metadata, verification docs, and local secret/editor
  metadata hygiene.
- Added numeric validation for database salary formatting so malformed result
  values render as `$0` instead of reaching `number_format()` directly.
- Added a `Permissions-Policy` header to deny camera, microphone, and
  geolocation APIs from both PHP entry points.
- Normalized non-scalar search POST fields to empty strings before legacy query
  handling.
- Normalized non-scalar index query parameters to empty strings before
  rendering or share URL generation.
- Added a regression check that treats PHP warnings from array query inputs as
  failures.
- Added `X-Frame-Options: DENY` to both PHP entry points.
- Extended response-header checks to preserve the frame-deny guard.

## 2026-06-08

- Replaced the protocol-relative Facebook script URL with explicit HTTPS and
  added external asset URL checks.
- Added explicit UTF-8 content type, `nosniff`, and referrer-policy headers for
  PHP entry points with static verification.
- Added `make check` as the shared repository verification alias.
- Replaced backend connection `die()` paths in `find.php` with a generic
  fail-closed no-match response.
- Added a PHP test that rejects exposed backend connection errors and verifies
  the unconfigured search endpoint response.
- Added a Makefile verification gate for PHP syntax checks and output escaping
  tests.
- Escaped search query parameters before rendering them into HTML attributes
  and sharing metadata.
- Made `find.php` parse under PHP and fail closed when database configuration
  or legacy mysql support is unavailable.
- Updated embedded first-party/share URLs to HTTPS where possible.
- Added canonical `docs/plans` coverage and a PHP docs-plan check under
  `make check`.
