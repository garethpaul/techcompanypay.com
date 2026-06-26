#!/usr/bin/env sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
README="$ROOT_DIR/README.md"
VISION="$ROOT_DIR/VISION.md"
TODO_FILE="$ROOT_DIR/TODO"
MAKEFILE="$ROOT_DIR/Makefile"
GITIGNORE="$ROOT_DIR/.gitignore"
DOCS_PLANS="$ROOT_DIR/docs/plans"

require_file() {
  path=$1
  if [ ! -f "$ROOT_DIR/$path" ]; then
    printf '%s\n' "Required file is missing: $path" >&2
    exit 1
  fi
}

for path in \
  ".gitignore" \
  "CHANGES.md" \
  "AGENTS.md" \
  ".github/workflows/check.yml" \
  "Makefile" \
  "README.md" \
  "SECURITY.md" \
  "VISION.md" \
  "TODO" \
	"assets/app.css" \
	"assets/app.js" \
  "find.php" \
  "input.php" \
  "index.php" \
  "tests/check-docs-plans.php" \
	"tests/check-external-assets.php" \
	"tests/check-local-search.php" \
	"tests/check-local-search.js" \
	"tests/check-share-url.php" \
  "tests/check-security-headers.php" \
	"tests/check-find-pdo-boundary.php" \
	"tests/check-find-request-method.php" \
	"tests/check-find-request-method-mutations.php" \
		"tests/check-pdo-row-budget-mutations.php" \
		"tests/check-encoded-result-budget.php" \
		"tests/check-encoded-result-budget-mutations.php" \
	"docs/plans/2026-06-10-local-search-and-ci.md" \
	"docs/plans/2026-06-10-deterministic-toolchains.md" \
	"docs/plans/2026-06-10-city-only-share-links.md" \
  "tests/check-ci-workflow.php" \
  "docs/plans/2026-06-10-php-ci-baseline.md" \
  "docs/plans/2026-06-08-techcompanypay-baseline.md" \
	"docs/plans/2026-06-15-pdo-database-boundary.md" \
		"docs/plans/2026-06-17-bounded-pdo-result-rows.md" \
	"docs/plans/2026-06-17-bounded-encoded-result-response.md" \
  "docs/plans/2026-06-09-scripted-baseline-check.md" \
  "docs/plans/2026-06-21-make-authority-isolation.md" \
  "docs/plans/2026-06-26-archive-status.md" \
  "docs/plans/2026-06-26-unicode-input-boundary.md" \
  "scripts/test-makefile-root.sh" \
  "scripts/check-baseline.sh"; do
  require_file "$path"
done

unicode_input_contract='Search inputs are bounded to 100 complete Unicode code points across PHP and asynchronous JavaScript paths.'
for document in "$ROOT_DIR/AGENTS.md" "$README" "$ROOT_DIR/SECURITY.md" "$VISION" "$ROOT_DIR/CHANGES.md"; do
  if ! grep -Fq "$unicode_input_contract" "$document"; then
    printf '%s\n' "Unicode input boundary contract is missing from: $document" >&2
    exit 1
  fi
done

if ! grep -Fq 'Keep the archived local-demonstration boundary' "$VISION"; then
  printf '%s\n' 'VISION must keep the archived local-demonstration boundary.' >&2
  exit 1
fi

if grep -Fq 'Add setup notes or archive status to the README' "$VISION"; then
  printf '%s\n' 'VISION must not retain the completed archive/setup roadmap item.' >&2
  exit 1
fi

for revival_contract in \
  'No production deployment or salary dataset is maintained.' \
  'deployment design, operational ownership, and rollback path'; do
  if ! grep -Fq "$revival_contract" "$TODO_FILE"; then
    printf '%s\n' "TODO production-revival contract is missing: $revival_contract" >&2
    exit 1
  fi
done

for project_status_contract in \
  '## Project Status' \
  'archived prototype' \
  'Local demonstration and repository verification remain supported.' \
  'No hosted production service or production database is maintained.' \
  'documented schema, data governance, and deployment design'; do
  if ! grep -Fq "$project_status_contract" "$README"; then
    printf '%s\n' "README project-status contract is missing: $project_status_contract" >&2
    exit 1
  fi
done

if ! grep -Fq "scripts/check-baseline.sh" "$MAKEFILE"; then
  printf '%s\n' "Makefile must run scripts/check-baseline.sh from make check." >&2
  exit 1
fi

for target in "lint:" "test:" "build:" "root-test:" "verify:" "check:"; do
  if ! grep -Fq "$target" "$MAKEFILE"; then
    printf '%s\n' "Makefile must expose the $target gate." >&2
    exit 1
  fi
done

for documented in "PHP CLI" "make check" "scripts/check-baseline.sh"; do
  if ! grep -Fq "$documented" "$README"; then
    printf '%s\n' "README must document $documented." >&2
    exit 1
  fi
done

for ignored in ".env" ".env.*" "*.local.php" "config.local.php" ".idea/" ".vscode/" "*.iml" "vendor/" "*.log"; do
  if ! grep -Fq "$ignored" "$GITIGNORE"; then
    printf '%s\n' ".gitignore must include $ignored" >&2
    exit 1
  fi
done

tracked_local=$(git -C "$ROOT_DIR" ls-files '.env' '.env.*' '*.local.php' 'config.local.php' '.idea' '.vscode' '*.iml' || true)
if [ -n "$tracked_local" ]; then
  printf '%s\n%s\n' "Local secrets or editor metadata must not be tracked:" "$tracked_local" >&2
  exit 1
fi

found_plan=0
for plan in "$DOCS_PLANS"/*.md; do
  [ -e "$plan" ] || continue
  found_plan=1
  if ! grep -Fq "Status: Completed" "$plan"; then
    printf '%s\n' "$plan must record completed status." >&2
    exit 1
  fi
  if ! grep -Fq "make check" "$plan"; then
    printf '%s\n' "$plan must document make check verification." >&2
    exit 1
  fi
done

if [ "$found_plan" -eq 0 ]; then
  printf '%s\n' "docs/plans must contain completed markdown plans." >&2
  exit 1
fi
