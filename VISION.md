## TechCompanyPay.com Vision

TechCompanyPay.com is a legacy PHP site prototype for displaying public,
aggregated salary/title information for technology companies and locations.

The repository is useful as a historical hackathon-style web app with a simple
search page, social metadata, a database-backed result endpoint, and a noted
deployment-migration TODO.

The goal is to preserve the prototype while making data quality, privacy,
database safety, and legacy PHP risks explicit.

The current focus is:

Priority:

- Preserve the search-page and result-endpoint intent
- Keep database credentials empty and out of source control
- Keep backend connection failures generic in user-facing responses
- Treat non-scalar request parameters as empty input before rendering
- Bound reflected query input length before rendering or sharing
- Treat non-scalar search POST fields as empty input before query handling
- Require POST before the salary endpoint reaches database configuration
- Format salary output only after numeric and finite-value validation
- Keep the primary search functional without third-party JavaScript
- Preserve company-only and city-only filters in shared search URLs
- Require abort support before enabling bounded asynchronous search
- Require bounded `text/html` fragments before async search results reach the
  live DOM
- Preserve the 256 KiB UTF-8 byte response limit for multibyte HTML fragments
- Preserve the Content-Length preflight before asynchronous response body reads
- Keep the live search results busy state owned by the latest request
- Keep scripts and styles same-origin unless an external dependency is reviewed
- Maintain the disclaimer around data accuracy
- Keep basic response security headers on PHP entry points
- Prevent legacy pages from being embedded in third-party frames
- Deny unused browser device APIs from PHP entry points
- Constrain legacy page fetches with a Content-Security-Policy header
- Keep the page render and runtime independent of third-party assets
- Keep the archived local-demonstration boundary and production revival
  prerequisites explicit
- Keep completed maintenance plans under `docs/plans`
- Keep fixed-runner PHP and Node verification explicit in GitHub Actions
- Keep hosted checkout credential-free and the workflow surface singular
- Keep a scriptable baseline guard for required files and local metadata
- Keep the PHP 8 PDO prepared statement boundary environment-configured,
  exception-safe, and free of interpolated search input
- Keep bounded incremental PDO result rows fail-closed before salary rendering
- Keep a bounded encoded database result response shared across both salary tables and fail closed before partial output
- Treat the intentionally blank production SQL and undocumented schema as
  explicit revival blockers

Next priorities:

- Document the historical schema before restoring production queries
- Keep all restored queries parameterized and all rendered values escaped
- Document data sources, freshness, and removal policy

Contribution rules:

- One PR = one focused PHP, database, data, deployment, or documentation change.
- Do not commit database credentials or private salary data.
- Keep data-source and accuracy disclaimers visible.
- Separate deployment migration from feature changes.

## Security And Responsible Use

Canonical security policy and reporting:

- [`SECURITY.md`](SECURITY.md)

Salary and profile-derived data can affect real people. The project should make
data sources, aggregation, uncertainty, and removal expectations clear, and
should avoid exposing raw personal profile data.

## What We Will Not Merge (For Now)

- Database credentials
- Raw personal profile dumps
- SQL built from untrusted input
- Non-scalar request values reaching legacy query construction
- Third-party frame embedding without a documented rationale
- Removing Content-Security-Policy without a replacement browser boundary
- Accuracy claims without sourced data and caveats

This list is a roadmap guardrail, not a permanent rule.
Strong user demand and strong technical rationale can change it.
