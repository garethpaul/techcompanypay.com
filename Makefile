.PHONY: build check lint test verify

PHP ?= php

lint:
	$(PHP) -l index.php
	$(PHP) -l find.php

test:
	$(PHP) tests/check-index-escaping.php
	$(PHP) tests/check-index-scalar-inputs.php
	$(PHP) tests/check-find-scalar-inputs.php
	$(PHP) tests/check-find-salary-format.php
	$(PHP) tests/check-find-fail-closed.php
	$(PHP) tests/check-security-headers.php
	$(PHP) tests/check-external-assets.php
	$(PHP) tests/check-docs-plans.php
	$(PHP) tests/check-ci-workflow.php

build: lint

verify: lint test build

check: verify
	scripts/check-baseline.sh
