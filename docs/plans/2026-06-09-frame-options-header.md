# Frame Options Header Guard

## Status: Completed

## Context

`techcompanypay.com` already sends explicit UTF-8 content type, `nosniff`, and a
referrer policy from both PHP entry points. The legacy pages do not need to be
embedded in third-party frames, so a frame-deny response header is a small,
low-risk clickjacking hardening step.

## Objectives

- Add a frame-deny response header to both PHP entry points.
- Preserve the existing security header helper shape.
- Extend local verification so the header is not accidentally removed.

## Work Completed

- Added `X-Frame-Options: DENY` to `index.php`.
- Added `X-Frame-Options: DENY` to `find.php`.
- Extended `tests/check-security-headers.php` to require the header.
- Documented the frame-options guard in README, VISION, and CHANGES.

## Verification

- `php -l index.php`
- `php -l find.php`
- `php tests/check-security-headers.php`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Add a scoped Content Security Policy once inline scripts and third-party
  widgets are inventoried.
- Consolidate duplicated response-header helpers if the legacy PHP entry points
  are revived for active maintenance.
