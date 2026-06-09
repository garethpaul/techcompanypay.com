# Permissions Policy Header Guard

## Status: Completed

## Context

`techcompanypay.com` already sends explicit content type, `nosniff`,
frame-deny, and referrer-policy headers from both PHP entry points. The legacy
salary-search prototype does not need browser camera, microphone, or
geolocation APIs, so those features should be denied by default.

## Objectives

- Add a `Permissions-Policy` header to both PHP entry points.
- Deny camera, microphone, and geolocation browser APIs.
- Preserve the existing security-header helper structure.
- Extend local verification so the header remains in place.

## Work Completed

- Added `Permissions-Policy: camera=(), microphone=(), geolocation=()` to
  `index.php`.
- Added the same header to `find.php`.
- Extended `tests/check-security-headers.php` to require the header.
- Updated README, VISION, and CHANGES.

## Verification

- Negative: `php tests/check-security-headers.php` failed before the PHP fix
  because `index.php` and `find.php` did not send the permissions policy.
- `php -l index.php`
- `php -l find.php`
- `php tests/check-security-headers.php`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Add a scoped Content Security Policy after inline scripts and legacy social
  widgets are inventoried.
- Consolidate duplicated response-header helpers if this prototype is revived.
