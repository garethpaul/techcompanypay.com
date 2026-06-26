# Archive Status Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use executing-plans to implement this plan task-by-task.

Status: Completed

**Goal:** Make the repository's supported local-demonstration and unsupported production boundaries explicit, then close the stale roadmap item.

**Architecture:** Keep the executable PHP sample unchanged. Add one README status section, align `TODO` and `VISION.md`, and enforce the boundary through the existing dependency-free PHP and shell documentation gates.

**Tech Stack:** Markdown, POSIX shell, PHP 8 contract tests, GNU Make.

---

### Task 1: Add the failing documentation contract

**Files:**
- Modify: `tests/check-docs-plans.php`
- Modify: `scripts/check-baseline.sh`

**Step 1: Write the failing test**

Require `README.md` to state that the project is an archived prototype, that local demonstration remains supported, that no production deployment or data service is maintained, and that production revival requires a documented schema, data governance, and deployment design.

**Step 2: Run it to make sure it fails**

Run: `php tests/check-docs-plans.php`
Expected: FAIL because the complete project-status contract is not present.

### Task 2: Document the minimal status boundary

**Files:**
- Modify: `README.md`
- Modify: `TODO`
- Modify: `VISION.md`
- Modify: `CHANGES.md`
- Modify: `docs/plans/2026-06-26-archive-status.md`

**Step 1: Implement the minimal documentation**

Add a concise `Project Status` section. Preserve local PHP demonstration and verification instructions, but make clear that no hosted service, production database, historical schema, data source, freshness guarantee, removal process, or deployment runbook is maintained.

**Step 2: Close the roadmap item**

Move archive/setup status into the maintained priorities and remove it from `Next priorities`. Keep schema, data governance, and deployment design as revival prerequisites.

**Step 3: Complete the plan and changelog**

Record `Status: Completed`, work, validation, scope boundaries, and `make check` evidence.

### Task 3: Validate hostile changes

**Files:**
- Test: `tests/check-docs-plans.php`
- Test: `scripts/check-baseline.sh`

**Step 1: Run focused and full tests**

Run: `php tests/check-docs-plans.php`
Expected: PASS.

Run: `make check`
Expected: PASS.

**Step 2: Run hostile mutations**

Remove each required status boundary in isolated copies and run `scripts/check-baseline.sh`.
Expected: Every mutation fails closed.

**Step 3: Commit**

Run: `git commit -m "docs: define archived project status"`

## Completed Work

- Added an explicit README project-status section that preserves local
  demonstration and repository verification while disclaiming production
  hosting and database support.
- Required documented schema, data governance, and deployment design before
  restoring queries or real salary data.
- Aligned `TODO`, `VISION.md`, and `CHANGES.md` with the maintained boundary.
- Added dependency-free PHP and shell contracts for the status language and
  this completed plan.

## Verification

- Containerized `make check` passed on Node 24.18.0 and PHP 8.2.31.
- Ten hostile isolated removals of required project-status, roadmap, and
  revival-prerequisite language were rejected.
- `git diff --check`

## Scope Boundaries

- No PHP, JavaScript, SQL, response, database, credential, or deployment
  behavior changed.
- No production service, historical schema, salary dataset, database, or
  browser session was exercised.
