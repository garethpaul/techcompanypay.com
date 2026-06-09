# Content Security Policy Header

## Status: Completed

## Context

`index.php` and `find.php` sent content type, nosniff, referrer, frame, and
permissions headers, but they did not send a Content-Security-Policy header.
The legacy page still depends on inline snippets and HTTPS third-party widgets,
so the first policy should constrain sources without claiming a full inline-free
modernization.

## Objectives

- Add a Content-Security-Policy header to both PHP entry points.
- Keep the policy compatible with the legacy inline snippets and HTTPS assets.
- Restrict default fetches to the site and HTTPS origins.
- Keep image loading constrained to the site, HTTPS, and data URLs.
- Deny plugin object loads, third-party framing, and unexpected base/form
  targets.
- Add static coverage so the header remains present.

## Work Completed

- Added the CSP header to `index.php`.
- Added the same CSP header to `find.php`.
- Included object, base URI, form action, and frame ancestor restrictions while
  preserving the legacy HTTPS widgets and inline snippets.
- Extended `tests/check-security-headers.php` to require the policy on both
  entry points.
- Extended `tests/check-docs-plans.php` to require this completed plan.
- Updated README, VISION, and CHANGES.

## Verification

- Negative: `make test` failed before the header fix because `index.php` did
  not send the Content-Security-Policy header.
- `php -l index.php`
- `php -l find.php`
- `make lint`
- `make test`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Move inline scripts into first-party files so the policy can drop
  `'unsafe-inline'`.
- Inventory third-party widgets before narrowing `script-src` and `frame-src`
  to explicit hostnames.
