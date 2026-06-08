# Changes

## 2026-06-08

- Added a Makefile verification gate for PHP syntax checks and output escaping
  tests.
- Escaped search query parameters before rendering them into HTML attributes
  and sharing metadata.
- Made `find.php` parse under PHP and fail closed when database configuration
  or legacy mysql support is unavailable.
- Updated embedded first-party/share URLs to HTTPS where possible.
