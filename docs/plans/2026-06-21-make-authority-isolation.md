# Make Authority Isolation

Status: Completed

## Context

The verification Makefile protected direct `ROOT` assignment but derived the
checkout path with a whitespace-sensitive expression. Caller-selected shells,
non-executing Make modes, preload files, and additional `-f` programs could
break or replace verification before checked-in targets completed.

## Work Completed

- Derived the repository root from the loaded Makefile with quoting that
  preserves spaces, quotes, backticks, and literal dollar characters.
- Fixed the recipe shell and shell flags while preserving trusted PHP and Node
  executable overrides.
- Rejected bypassing Make modes, caller `MAKEFLAGS`, preload metadata,
  overridden Makefile metadata, and visible additional files.
- Added a bounded authority harness across all six public targets and pinned
  hosted dispatch to `/usr/bin/make`.

## Verification

- `make root-test` passed 30 target/authority cases, one literal-dollar tool
  case, one raw tool Make-syntax rejection, two `MAKEFILE_LIST` rejections, two
  contained startup-boundary cases, and ten mode-flag rejections.
- Repository and external-directory `make check` passed on PHP 7.4 and Node 20.
- Exact-head hosted PHP 8.2, 8.4, and 8.5 jobs remain required before merge.

## Trust Boundary

GNU Make can execute preload and earlier additional-file parse expressions
before a checked-in Makefile can reject them. Trusted automation must invoke
only this repository Makefile. PHP and Node remain trusted caller inputs so
local toolchains and the hosted matrix continue to work; their raw values are
frozen before Make expansion and shell-quoted.

## Scope Boundary

This change does not alter PHP or JavaScript behavior, database handling,
response limits, assets, dependencies, or live database requirements.
