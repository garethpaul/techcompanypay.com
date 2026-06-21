#!/usr/bin/env sh
set -eu
PATH=/usr/bin:/bin
export PATH

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && /bin/pwd -P)
TEMP_ROOT=$(mktemp -d "${TMPDIR:-/tmp}/techcompanypay-root-control-XXXXXX")
trap 'rm -rf "$TEMP_ROOT"' EXIT HUP INT TERM
unset MAKEFILES MAKEFILE_LIST MAKEFLAGS MFLAGS MAKEOVERRIDES ROOT SHELL

CONTROL_DIR="$TEMP_ROOT/control"
CHECKOUT="$TEMP_ROOT/tech pay's [gate] \"quoted\" \`touch TECHPAY_BACKTICK_MARKER\`"
ATTACKER_ROOT="$TEMP_ROOT/attacker-root"
COMMAND_LOG="$TEMP_ROOT/commands.log"
FAKE_SHELL_LOG="$TEMP_ROOT/fake-shell.log"
mkdir -p "$CONTROL_DIR" "$CHECKOUT/scripts" "$ATTACKER_ROOT"
CONTROL_DIR=$(CDPATH= cd -- "$CONTROL_DIR" && /bin/pwd -P)
CHECKOUT=$(CDPATH= cd -- "$CHECKOUT" && /bin/pwd -P)
MAKEFILE="$CHECKOUT/Makefile"
cp "$ROOT_DIR/Makefile" "$MAKEFILE"

FAKE_PHP="$TEMP_ROOT/trusted php's \"quoted\" \`touch TECHPAY_PHP_MARKER\` \$literal"
FAKE_NODE="$TEMP_ROOT/trusted node's \"quoted\" \`touch TECHPAY_NODE_MARKER\` \$literal"
for tool in "$FAKE_PHP" "$FAKE_NODE"; do
  cat >"$tool" <<'TOOL'
#!/bin/sh
printf '%s|%s|%s\n' "$PWD" "$0" "$*" >> "$TECHPAY_COMMAND_LOG"
TOOL
  chmod +x "$tool"
done
cat >"$CHECKOUT/scripts/check-baseline.sh" <<'BASELINE'
#!/bin/sh
printf '%s|%s|baseline\n' "$PWD" "$0" >> "$TECHPAY_COMMAND_LOG"
BASELINE
chmod +x "$CHECKOUT/scripts/check-baseline.sh"
cat >"$CHECKOUT/scripts/test-makefile-root.sh" <<'ROOT_TEST'
#!/bin/sh
printf '%s|%s|root-test\n' "$PWD" "$0" >> "$TECHPAY_COMMAND_LOG"
ROOT_TEST
chmod +x "$CHECKOUT/scripts/test-makefile-root.sh"

FAKE_SHELL="$TEMP_ROOT/fake-shell"
cat >"$FAKE_SHELL" <<EOF
#!/bin/sh
printf '%s\n' invoked >> '$FAKE_SHELL_LOG'
exec /bin/sh "\$@"
EOF
chmod +x "$FAKE_SHELL"

run_case() {
  target=$1 mode=$2
  rm -f "$COMMAND_LOG" "$FAKE_SHELL_LOG"
  output="$TEMP_ROOT/output"
  set +e
  case "$mode" in
    default) (cd "$CONTROL_DIR" && TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" PHP="$FAKE_PHP" NODE="$FAKE_NODE" "$target") >"$output" 2>&1 ;;
    command-root) (cd "$CONTROL_DIR" && TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" ROOT="$ATTACKER_ROOT" PHP="$FAKE_PHP" NODE="$FAKE_NODE" "$target") >"$output" 2>&1 ;;
    environment-root) (cd "$CONTROL_DIR" && ROOT="$ATTACKER_ROOT" TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" PHP="$FAKE_PHP" NODE="$FAKE_NODE" "$target") >"$output" 2>&1 ;;
    command-shell) (cd "$CONTROL_DIR" && TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" SHELL="$FAKE_SHELL" PHP="$FAKE_PHP" NODE="$FAKE_NODE" "$target") >"$output" 2>&1 ;;
    environment-shell) (cd "$CONTROL_DIR" && SHELL="$FAKE_SHELL" TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" PHP="$FAKE_PHP" NODE="$FAKE_NODE" "$target") >"$output" 2>&1 ;;
  esac
  status=$?
  set -e
  if [ "$status" -ne 0 ]; then cat "$output" >&2; exit "$status"; fi
  [ ! -e "$FAKE_SHELL_LOG" ]
  grep -Fq "$CHECKOUT" "$COMMAND_LOG"
}

targets='build check lint root-test test verify'
modes='default command-root environment-root command-shell environment-shell'
executed=0
for target in $targets; do
  for mode in $modes; do
    run_case "$target" "$mode"
    executed=$((executed + 1))
  done
done
[ "$executed" -eq 30 ]

rm -f "$COMMAND_LOG"
(cd "$CONTROL_DIR" && TECHPAY_COMMAND_LOG="$COMMAND_LOG" /usr/bin/make --no-print-directory --file "$MAKEFILE" PHP="$FAKE_PHP" NODE="$FAKE_NODE" check) >/dev/null 2>&1
grep -Fq "$FAKE_PHP" "$COMMAND_LOG"
grep -Fq "$FAKE_NODE" "$COMMAND_LOG"
for marker in TECHPAY_BACKTICK_MARKER TECHPAY_PHP_MARKER TECHPAY_NODE_MARKER; do [ ! -e "$CONTROL_DIR/$marker" ]; done

MAKE_SYNTAX_MARKER="$TEMP_ROOT/php-make-syntax-ran"
MALICIOUS_PHP="\$(shell /usr/bin/touch '$MAKE_SYNTAX_MARKER')"
if (cd "$CONTROL_DIR" && /usr/bin/make --no-print-directory --file "$MAKEFILE" "PHP=$MALICIOUS_PHP" lint) >"$TEMP_ROOT/php-syntax.out" 2>&1; then exit 1; fi
[ ! -e "$MAKE_SYNTAX_MARKER" ]

if (cd "$CONTROL_DIR" && /usr/bin/make --no-print-directory --file "$MAKEFILE" MAKEFILE_LIST=/tmp/untrusted check) >"$TEMP_ROOT/command-list.out" 2>&1; then exit 1; fi
grep -Fq "MAKEFILE_LIST must not be overridden" "$TEMP_ROOT/command-list.out"
if (cd "$CONTROL_DIR" && MAKEFILE_LIST=/tmp/untrusted /usr/bin/make --environment-overrides --no-print-directory --file "$MAKEFILE" check) >"$TEMP_ROOT/environment-list.out" 2>&1; then exit 1; fi
grep -Fq "MAKEFILE_LIST must not be overridden" "$TEMP_ROOT/environment-list.out"

PRELOADED="$TEMP_ROOT/preloaded.mk"; PRELOAD_MARKER="$TEMP_ROOT/preload-startup-ran"; printf '%s\n' "\$(shell /usr/bin/touch '$PRELOAD_MARKER')" >"$PRELOADED"
if (cd "$CONTROL_DIR" && MAKEFILES="$PRELOADED" /usr/bin/make --no-print-directory --file "$MAKEFILE" check) >"$TEMP_ROOT/preloaded.out" 2>&1; then exit 1; fi
grep -Fq "MAKEFILES must be empty" "$TEMP_ROOT/preloaded.out"; [ -e "$PRELOAD_MARKER" ]
EARLIER="$TEMP_ROOT/earlier.mk"; EARLIER_MARKER="$TEMP_ROOT/earlier-startup-ran"; printf '%s\n' "\$(shell /usr/bin/touch '$EARLIER_MARKER')" >"$EARLIER"
if (cd "$CONTROL_DIR" && /usr/bin/make --no-print-directory --file "$EARLIER" --file "$MAKEFILE" check) >"$TEMP_ROOT/multiple.out" 2>&1; then exit 1; fi
grep -Fq "repository Makefile path could not be resolved" "$TEMP_ROOT/multiple.out"; [ -e "$EARLIER_MARKER" ]

for flag in -n --just-print --dry-run --recon -t --touch -q --question -i --ignore-errors; do
  if (cd "$CONTROL_DIR" && /usr/bin/make "$flag" --no-print-directory --file "$MAKEFILE" check) >"$TEMP_ROOT/flag.out" 2>&1; then exit 1; fi
  grep -Fq "non-executing or error-ignoring MAKEFLAGS are not supported" "$TEMP_ROOT/flag.out"
done

printf '%s\n' "Makefile root tests passed: 30 executed target/authority cases, 1 literal-dollar tool case, 1 raw tool Make-syntax rejection, 2 MAKEFILE_LIST rejections, 2 contained startup-boundary cases, and 10 mode-flag rejections"
