# Make Root Override Protection

## Status: Planned

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
