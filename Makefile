.PHONY: build check lint test verify

PHP ?= php

lint:
	$(PHP) -l index.php
	$(PHP) -l find.php

test:
	$(PHP) tests/check-index-escaping.php
	$(PHP) tests/check-find-fail-closed.php
	$(PHP) tests/check-docs-plans.php

build: lint

verify: lint test build

check: verify
