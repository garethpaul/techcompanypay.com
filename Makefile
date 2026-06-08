.PHONY: lint test build verify

PHP ?= php

lint:
	$(PHP) -l index.php
	$(PHP) -l find.php

test:
	$(PHP) tests/check-index-escaping.php
	$(PHP) tests/check-find-fail-closed.php

build: lint

verify: lint test build
