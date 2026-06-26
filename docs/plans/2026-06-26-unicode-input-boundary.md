# Unicode Input Boundary

Status: Completed

## Goal

Keep the documented 100-character search limit without splitting valid UTF-8
sequences in PHP or surrogate pairs in JavaScript.

## Scope

- Share one bounded UTF-8 helper across PHP query and form inputs.
- Bound browser form values by complete Unicode code points.
- Fail closed on malformed UTF-8 and unpaired JavaScript surrogates.
- Preserve non-scalar request handling and the existing 100-character limit.

## Verification

- Run focused PHP and JavaScript boundary regressions.
- Run hostile mutations that restore byte or UTF-16 code-unit slicing.
- Run `make check` with the repository's fixed PHP and Node toolchains.
- Run `git diff --check` and review the exact proposed diff.

## Outcome

The PHP and asynchronous JavaScript paths now retain at most 100 complete
Unicode code points and reject malformed input before downstream processing.
