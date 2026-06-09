# Explicit HTTPS Assets

## Status: Completed

## Context

`index.php` already used HTTPS for most third-party assets, but the Facebook
widget still used a protocol-relative script URL. Legacy pages should avoid
ambiguous external asset loading so local review can see exactly which network
scheme each asset uses.

## Objectives

- Keep the existing legacy social widget behavior.
- Replace protocol-relative external assets with explicit HTTPS.
- Add a `make check` guard for insecure or protocol-relative asset URLs.
- Keep README, VISION, and CHANGES aligned with the new guard.

## Work Completed

- Changed the Facebook widget loader to `https://connect.facebook.net`.
- Added `tests/check-external-assets.php` to reject protocol-relative and
  insecure HTTP asset references.
- Wired the asset checker into `make check`.
- Updated repository maintenance documentation.

## Verification

- `php -l index.php`
- `php tests/check-external-assets.php`
- `make check`
- `make verify`
- `git diff --check`

## Follow-Up Candidates

- Inventory the remaining third-party scripts and social widgets before adding
  an enforcing content security policy.
- Replace deprecated social widgets if the prototype is revived.
