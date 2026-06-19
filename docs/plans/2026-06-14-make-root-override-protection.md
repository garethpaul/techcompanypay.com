# Make Root Override Protection

## Status: Completed

## Context

The Makefile derives verification paths from its own location, but environment
and command-line assignments can replace `ROOT`. A hostile or stale value can
redirect PHP syntax checks, Node behavior tests, documentation contracts, and
the baseline guard away from the checked-out repository.

## Priority

Repository verification paths are a trust boundary. The Makefile must select
its own root while preserving intentional PHP and Node executable overrides.

## Objectives

- Make the repository-derived root authoritative over caller assignments.
- Preserve `PHP` and `NODE` tool overrides and their declaration order.
- Preserve all public Make aliases and the baseline shell gate.
- Exercise every alias from repository and external working directories under
  hostile environment and command-line root values.
- Add fail-closed, mutation-sensitive documentation and Make contracts.

## Implementation Units

### U1. Protect the repository root

**Files:** `Makefile`

Mark the root declaration as an explicit GNU Make override without changing
the tool variables, targets, or repository-relative command paths.

### U2. Preserve the verification contract

**Files:** `tests/check-docs-plans.php`, `README.md`

Require exactly one protected declaration, its placement after both tool
overrides, the public alias graph, root-anchored PHP/Node/baseline paths, README
indexing, and this plan's completed evidence.

## Verification

- Focused documentation-contract and complete offline behavior gates.
- Full `make check` locally and from an external working directory.
- All aliases with hostile environment and command-line root assignments.
- Declaration, duplicate, placement, alias, path, README, and plan mutations.
- Exact diff, protected-source/workflow, generated-artifact, secret, and
  whitespace audits.
- Exact-head PHP 8.2, 8.4, and 8.5 hosted verification.

## Scope Boundary

This change does not alter PHP or JavaScript behavior, database handling,
response limits, assets, dependencies, workflow policy, or live database
requirements.

## Work Completed

- Marked the repository root as an explicit GNU Make override.
- Added exact declaration, tool-order, alias, root-path, README, and plan
  contracts to the existing PHP documentation checker.
- Preserved PHP/Node overrides and all application, test, baseline, and
  workflow behavior.

## Verification Results

- `make check` passed PHP and JavaScript syntax, all PHP/Node behavior and
  contract tests, workflow policy, documentation plans, and the baseline shell
  guard on local PHP 7.4 and Node 20.
- The same complete gate passed from an external working directory.
- All five public aliases passed from both working-directory contexts with
  hostile environment and command-line `ROOT` assignments, for 20 cases.
- Explicit PHP and Node executable overrides remained effective.
- Eight protected-declaration, duplicate protected/unprotected assignment,
  placement, alias, path, README, and plan mutations were rejected.
- Plan-aware correctness, security, testing, maintainability, reliability, and
  project-standards review found no actionable findings.
- Exact diff, protected application/workflow path, generated-artifact,
  high-confidence secret, and whitespace audits passed.
- Browser automation was not run because `agent-browser` is unavailable; this
  build-contract change does not alter a route or client behavior.
- No cached PHP 8.x container was available, so exact-head hosted PHP 8.2, 8.4,
  and 8.5 jobs remain the runtime authority.
