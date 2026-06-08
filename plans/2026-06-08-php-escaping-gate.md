# PHP Escaping Gate

## Problem

The PHP app had no automated verification command. `find.php` failed PHP syntax
checks, and `index.php` rendered query parameters directly into HTML attributes
and Open Graph/Facebook URLs.

## TDD Evidence

1. Added `tests/check-index-escaping.php` and a Makefile verification gate.
2. Ran `make lint` and confirmed `find.php` failed to parse.
3. Ran `make test` and confirmed raw query text was rendered by `index.php`.
4. Added HTML/query escaping helpers, made `find.php` parse/fail closed, and
   reran the full gate.

## Verification

- `make lint`
- `make test`
- `make build`
- `make verify`
- `git diff --check`
