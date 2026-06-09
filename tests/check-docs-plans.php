<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$root = dirname(__DIR__);
$canonical = $root . '/docs/plans/2026-06-08-techcompanypay-baseline.md';
$salaryPlan = $root . '/docs/plans/2026-06-09-salary-format-guard.md';
$cspPlan = $root . '/docs/plans/2026-06-09-content-security-policy-header.md';
$scriptedBaselinePlan = $root . '/docs/plans/2026-06-09-scripted-baseline-check.md';
$queryLengthPlan = $root . '/docs/plans/2026-06-09-query-length-guard.md';

if (!is_file($canonical)) {
    fail('docs/plans/2026-06-08-techcompanypay-baseline.md is missing');
}

if (!is_file($salaryPlan)) {
    fail('docs/plans/2026-06-09-salary-format-guard.md is missing');
}

if (!is_file($cspPlan)) {
    fail('docs/plans/2026-06-09-content-security-policy-header.md is missing');
}

if (!is_file($scriptedBaselinePlan)) {
    fail('docs/plans/2026-06-09-scripted-baseline-check.md is missing');
}

if (!is_file($queryLengthPlan)) {
    fail('docs/plans/2026-06-09-query-length-guard.md is missing');
}

$makefile = file_get_contents($root . '/Makefile');
if (strpos($makefile, 'scripts/check-baseline.sh') === false) {
    fail('Makefile must run scripts/check-baseline.sh from make check');
}

$plans = glob($root . '/docs/plans/*.md');
if (!$plans) {
    fail('docs/plans must contain at least one completed plan');
}

foreach ($plans as $plan) {
    $body = file_get_contents($plan);
    if (strpos($body, 'Status: Completed') === false || strpos($body, 'make check') === false) {
        fail(str_replace($root . '/', '', $plan) . ' must record completed status and make check verification');
    }
}

$findSource = file_get_contents($root . '/find.php');
if (strpos($findSource, 'function tcp_salary($value)') === false) {
    fail('find.php must centralize salary formatting');
}
if (strpos($findSource, 'is_numeric($value) ? number_format((float) $value) : \'0\'') === false) {
    fail('find.php must format only numeric salary values');
}
if (strpos($findSource, 'number_format($salary)') !== false) {
    fail('find.php must not pass raw database salary values to number_format');
}

$indexSource = file_get_contents($root . '/index.php');
if (strpos($indexSource, 'substr((string) $_GET[$key], 0, 100)') === false) {
    fail('index.php must bound scalar query values before rendering or sharing');
}

echo "docs plans checks passed" . PHP_EOL;
