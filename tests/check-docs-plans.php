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
$toolchainPlan = $root . '/docs/plans/2026-06-10-deterministic-toolchains.md';
$cityOnlyPlan = $root . '/docs/plans/2026-06-10-city-only-share-links.md';

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

if (!is_file($toolchainPlan)) {
    fail('docs/plans/2026-06-10-deterministic-toolchains.md is missing');
}

if (!is_file($cityOnlyPlan)) {
    fail('docs/plans/2026-06-10-city-only-share-links.md is missing');
}

$makefile = file_get_contents($root . '/Makefile');
if (strpos($makefile, 'scripts/check-baseline.sh') === false) {
    fail('Makefile must run scripts/check-baseline.sh from make check');
}
foreach (array(
    'ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))',
    'NODE ?= node',
    '"$(ROOT)/assets/app.js"',
    '"$(ROOT)/tests/check-local-search.js"',
) as $contract) {
    if (strpos($makefile, $contract) === false) {
        fail('Makefile must keep root-independent tool contract: ' . $contract);
    }
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
if (strpos($indexSource, 'http_build_query($params, \'\', \'&\', PHP_QUERY_RFC3986)') === false) {
    fail('index.php must build share URLs from independent encoded filters');
}

echo "docs plans checks passed" . PHP_EOL;
