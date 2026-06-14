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
$credentialIsolationPlan = $root . '/docs/plans/2026-06-12-checkout-credential-isolation.md';
$abortableSearchPlan = $root . '/docs/plans/2026-06-12-abortable-async-search.md';
$boundedSearchResponsePlan = $root . '/docs/plans/2026-06-13-bounded-html-search-response.md';
$searchBusyStatePlan = $root . '/docs/plans/2026-06-13-search-results-busy-state.md';
$rootOverridePlan = $root . '/docs/plans/2026-06-14-make-root-override-protection.md';
$utf8ResponseByteLimitPlan = $root . '/docs/plans/2026-06-14-utf8-search-response-byte-limit.md';

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

if (!is_file($credentialIsolationPlan)) {
    fail('docs/plans/2026-06-12-checkout-credential-isolation.md is missing');
}

if (!is_file($abortableSearchPlan)) {
    fail('docs/plans/2026-06-12-abortable-async-search.md is missing');
}

if (!is_file($boundedSearchResponsePlan)) {
    fail('docs/plans/2026-06-13-bounded-html-search-response.md is missing');
}

if (!is_file($searchBusyStatePlan)) {
    fail('docs/plans/2026-06-13-search-results-busy-state.md is missing');
}

if (!is_file($rootOverridePlan)) {
    fail('docs/plans/2026-06-14-make-root-override-protection.md is missing');
}

if (!is_file($utf8ResponseByteLimitPlan)) {
    fail('docs/plans/2026-06-14-utf8-search-response-byte-limit.md is missing');
}

$makefile = file_get_contents($root . '/Makefile');
if (strpos($makefile, 'scripts/check-baseline.sh') === false) {
    fail('Makefile must run scripts/check-baseline.sh from make check');
}
$rootDeclaration = 'override ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))';
preg_match_all('/^(?:override[[:space:]]+)?ROOT[[:space:]]*[:+?]?=/m', $makefile, $rootAssignments);
if (count($rootAssignments[0]) !== 1 || substr_count($makefile, $rootDeclaration) !== 1) {
    fail('Makefile must contain exactly one protected repository-root declaration');
}
$toolAndRootBlock = "PHP ?= php\nNODE ?= node\n" . $rootDeclaration;
if (substr_count($makefile, $toolAndRootBlock) !== 1) {
    fail('Makefile must keep PHP and Node overrides before the protected repository root');
}
foreach (array(
    '.PHONY: build check lint test verify',
    'build: lint',
    'verify: lint test build',
    'check: verify',
    '"$(ROOT)/scripts/check-baseline.sh"',
    '"$(ROOT)/index.php"',
    '"$(ROOT)/find.php"',
    '"$(ROOT)/assets/app.js"',
    '"$(ROOT)/tests/check-local-search.js"',
) as $contract) {
    if (strpos($makefile, $contract) === false) {
        fail('Makefile must keep root-independent tool contract: ' . $contract);
    }
}

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-14-make-root-override-protection.md') === false) {
    fail('README must index Make root override protection evidence');
}

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-14-utf8-search-response-byte-limit.md') === false) {
    fail('README must index UTF-8 search response byte-limit evidence');
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

if (is_file($utf8ResponseByteLimitPlan)) {
    $body = file_get_contents($utf8ResponseByteLimitPlan);
    foreach (array(
        'repository and external-directory `make check` passed',
        'hostile UTF-8 byte-limit mutations were rejected',
    ) as $evidence) {
        if (strpos($body, $evidence) === false) {
            fail('docs/plans/2026-06-14-utf8-search-response-byte-limit.md must record verification evidence: ' . $evidence);
        }
    }
}

foreach (array('README.md', 'SECURITY.md', 'VISION.md', 'CHANGES.md') as $documentation) {
    $body = strtolower(file_get_contents($root . '/' . $documentation));
    if (strpos($body, 'busy state') === false) {
        fail($documentation . ' must document the async search busy state');
    }
    if (strpos($body, 'utf-8 byte response limit') === false) {
        fail($documentation . ' must document the UTF-8 byte response limit');
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
