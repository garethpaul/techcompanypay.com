# Changes

## 2026-06-08

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
