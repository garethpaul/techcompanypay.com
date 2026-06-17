.PHONY: build check lint test verify

PHP ?= php
NODE ?= node
override ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))

lint:
	$(PHP) -l "$(ROOT)/index.php"
	$(PHP) -l "$(ROOT)/find.php"
	$(NODE) --check "$(ROOT)/assets/app.js"

test:
	$(PHP) "$(ROOT)/tests/check-index-escaping.php"
	$(PHP) "$(ROOT)/tests/check-index-scalar-inputs.php"
	$(PHP) "$(ROOT)/tests/check-share-url.php"
	$(PHP) "$(ROOT)/tests/check-find-scalar-inputs.php"
	$(PHP) "$(ROOT)/tests/check-find-salary-format.php"
	$(PHP) "$(ROOT)/tests/check-find-fail-closed.php"
	$(PHP) "$(ROOT)/tests/check-find-pdo-boundary.php"
	$(PHP) "$(ROOT)/tests/check-pdo-row-budget-mutations.php"
	$(PHP) "$(ROOT)/tests/check-security-headers.php"
	$(PHP) "$(ROOT)/tests/check-external-assets.php"
	$(PHP) "$(ROOT)/tests/check-local-search.php"
	$(NODE) "$(ROOT)/tests/check-local-search.js"
	$(PHP) "$(ROOT)/tests/check-docs-plans.php"
	$(PHP) "$(ROOT)/tests/check-ci-workflow.php"

build: lint

verify: lint test build

check: verify
	"$(ROOT)/scripts/check-baseline.sh"
