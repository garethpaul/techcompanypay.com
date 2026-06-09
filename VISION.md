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
- Maintain the disclaimer around data accuracy
- Keep basic response security headers on PHP entry points
- Prevent legacy pages from being embedded in third-party frames
- Keep third-party assets on explicit HTTPS URLs
- Keep completed maintenance plans under `docs/plans`
- Treat old PHP `mysql_*` APIs and incomplete SQL as legacy risks

Next priorities:

- Add setup notes or archive status to the README
- Replace legacy database APIs if the site is revived
- Parameterize all queries and escape all rendered user input
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
- Third-party frame embedding without a documented rationale
- Accuracy claims without sourced data and caveats

This list is a roadmap guardrail, not a permanent rule.
Strong user demand and strong technical rationale can change it.
