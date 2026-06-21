.DEFAULT_GOAL := check
.PHONY: __repository-make-authority build check lint root-test test verify
.SECONDEXPANSION:

PHP ?= php
NODE ?= node
override PHP := $(value PHP)
override NODE := $(value NODE)
export PHP NODE
override SHELL := /bin/sh
override .SHELLFLAGS := -c

ifneq ($(filter command line,$(origin MAKEFLAGS)),)
$(error MAKEFLAGS must not be overridden for repository verification)
endif
override REPOSITORY_MAKE_FIRST_FLAGS := $(firstword $(MAKEFLAGS))
ifneq ($(filter -%,$(REPOSITORY_MAKE_FIRST_FLAGS)),)
override REPOSITORY_MAKE_FIRST_FLAGS :=
endif
override REPOSITORY_MAKE_SHORT_FLAGS := $(REPOSITORY_MAKE_FIRST_FLAGS) $(filter-out --%,$(filter -%,$(MAKEFLAGS)))
ifneq ($(findstring n,$(REPOSITORY_MAKE_SHORT_FLAGS)),)
$(error non-executing or error-ignoring MAKEFLAGS are not supported for repository verification)
endif
ifneq ($(findstring t,$(REPOSITORY_MAKE_SHORT_FLAGS)),)
$(error non-executing or error-ignoring MAKEFLAGS are not supported for repository verification)
endif
ifneq ($(findstring q,$(REPOSITORY_MAKE_SHORT_FLAGS)),)
$(error non-executing or error-ignoring MAKEFLAGS are not supported for repository verification)
endif
ifneq ($(findstring i,$(REPOSITORY_MAKE_SHORT_FLAGS)),)
$(error non-executing or error-ignoring MAKEFLAGS are not supported for repository verification)
endif
ifneq ($(filter --just-print --dry-run --recon --touch --question --ignore-errors,$(MAKEFLAGS)),)
$(error non-executing or error-ignoring MAKEFLAGS are not supported for repository verification)
endif
ifneq ($(strip $(MAKEFILES)),)
$(error MAKEFILES must be empty; repository verification requires this Makefile to be loaded alone)
endif
override MAKEFILES :=
ifneq ($(origin MAKEFILE_LIST),file)
$(error MAKEFILE_LIST must not be overridden)
endif
override ROOT := $(shell path='$(subst ','"'"',$(value MAKEFILE_LIST))'; path=$$(printf '%s' "$$path" | /usr/bin/sed 's/^ //'); [ -f "$$path" ] || exit 1; directory=$$(/usr/bin/dirname -- "$$path"); CDPATH= cd -- "$$directory" && /bin/pwd -P)
export ROOT
ifeq ($(strip $(ROOT)),)
$(error repository Makefile path could not be resolved)
endif

build check lint root-test test verify: $$(if $$(filter file,$$(origin MAKEFILE_LIST)),,$$(error MAKEFILE_LIST must not be overridden))
build check lint root-test test verify: $$(if $$(shell path=$$$$(/usr/bin/printf '%s' '$$(subst ','"'"',$$(MAKEFILE_LIST))' | /usr/bin/sed 's/^ //') && [ -f "$$$$path" ] && /usr/bin/printf '%s' ok),,$$(error repository Makefile must be loaded alone))
build check lint root-test test verify: __repository-make-authority

__repository-make-authority::
	@:

lint:
	"$$PHP" -l "$$ROOT/index.php"
	"$$PHP" -l "$$ROOT/find.php"
	"$$NODE" --check "$$ROOT/assets/app.js"

test:
	"$$PHP" "$$ROOT/tests/check-index-escaping.php"
	"$$PHP" "$$ROOT/tests/check-index-scalar-inputs.php"
	"$$PHP" "$$ROOT/tests/check-share-url.php"
	"$$PHP" "$$ROOT/tests/check-find-scalar-inputs.php"
	"$$PHP" "$$ROOT/tests/check-find-salary-format.php"
	"$$PHP" "$$ROOT/tests/check-find-fail-closed.php"
	"$$PHP" "$$ROOT/tests/check-find-pdo-boundary.php"
	"$$PHP" "$$ROOT/tests/check-pdo-row-budget-mutations.php"
	"$$PHP" "$$ROOT/tests/check-encoded-result-budget.php"
	"$$PHP" "$$ROOT/tests/check-encoded-result-budget-mutations.php"
	"$$PHP" "$$ROOT/tests/check-security-headers.php"
	"$$PHP" "$$ROOT/tests/check-external-assets.php"
	"$$PHP" "$$ROOT/tests/check-local-search.php"
	"$$NODE" "$$ROOT/tests/check-local-search.js"
	"$$PHP" "$$ROOT/tests/check-docs-plans.php"
	"$$PHP" "$$ROOT/tests/check-ci-workflow.php"

build: lint

root-test:
	/bin/sh "$$ROOT/scripts/test-makefile-root.sh"

verify: root-test lint test build

check: verify
	"$$ROOT/scripts/check-baseline.sh"
