# Changes

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
