# Local Search Runtime and CI

## Status: Completed

## Context

The primary salary search interaction depended on remotely hosted jQuery 1.6.2
and `core2.js` assets that were no longer reachable from the maintenance
environment. Without those scripts, submitting the form posted back to
`index.php`, which does not process searches. Obsolete social and analytics
widgets also required a permissive script policy. The default branch had no
hosted verification; initial branch groundwork added a PHP matrix before the
browser runtime work began.

## Objectives

- Restore asynchronous salary searches with repository-owned JavaScript.
- Preserve a functional form POST fallback when JavaScript is unavailable.
- Remove obsolete runtime scripts and tighten Content-Security-Policy.
- Keep the page readable without a remote stylesheet dependency.
- Enforce the complete repository check on PHP 8.2 and 8.4 in GitHub Actions.

## Work Completed

- Added a bounded same-origin search client with loading, error, cancellation,
  and prefilled-query behavior.
- Pointed the native form action at `find.php` for progressive fallback.
- Added a local responsive stylesheet and removed unreachable runtime assets,
  obsolete sharing widgets, external share artwork, and legacy analytics.
- Restricted scripts and styles to same-origin resources in both PHP response
  policies.
- Added executable browser-script coverage plus local-search and external-asset
  regression contracts.
- Added a least-privilege PHP 8.2/8.4 CI matrix with immutable action pins, then
  extended `make check` with JavaScript syntax and local-runtime contracts.

## Verification

- `make check`
- `node --check assets/app.js`
- `node tests/check-local-search.js`
- Negative source and workflow mutation checks
- PHP built-in server smoke test
- `git diff --check`
