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
$contentLengthPreflightPlan = $root . '/docs/plans/2026-06-14-search-content-length-preflight.md';
$pdoBoundaryPlan = $root . '/docs/plans/2026-06-15-pdo-database-boundary.md';
$pdoRowBudgetPlan = $root . '/docs/plans/2026-06-17-bounded-pdo-result-rows.md';
$encodedResultBudgetPlan = $root . '/docs/plans/2026-06-17-bounded-encoded-result-response.md';

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

if (!is_file($contentLengthPreflightPlan)) {
    fail('docs/plans/2026-06-14-search-content-length-preflight.md is missing');
}

if (!is_file($pdoBoundaryPlan)) {
    fail('docs/plans/2026-06-15-pdo-database-boundary.md is missing');
}

if (!is_file($pdoRowBudgetPlan)) {
    fail('docs/plans/2026-06-17-bounded-pdo-result-rows.md is missing');
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
    '"$(ROOT)/tests/check-find-pdo-boundary.php"',
    '"$(ROOT)/tests/check-pdo-row-budget-mutations.php"',
    '"$(ROOT)/tests/check-encoded-result-budget.php"',
    '"$(ROOT)/tests/check-encoded-result-budget-mutations.php"',
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

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-14-search-content-length-preflight.md') === false) {
    fail('README must index search Content-Length preflight evidence');
}

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-15-pdo-database-boundary.md') === false) {
    fail('README must index PDO database boundary evidence');
}

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-17-bounded-pdo-result-rows.md') === false) {
    fail('README must index bounded PDO result row evidence');
}

if (strpos(file_get_contents($root . '/README.md'), 'docs/plans/2026-06-17-bounded-encoded-result-response.md') === false) {
    fail('README must index bounded encoded result response evidence');
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

if (is_file($contentLengthPreflightPlan)) {
    $body = file_get_contents($contentLengthPreflightPlan);
    foreach (array(
        'Status: Completed',
        'repository and external-directory `make check` passed',
        'hostile Content-Length preflight mutations were rejected',
    ) as $evidence) {
        if (strpos($body, $evidence) === false) {
            fail('docs/plans/2026-06-14-search-content-length-preflight.md must record verification evidence: ' . $evidence);
        }
    }
}

if (is_file($pdoBoundaryPlan)) {
    $body = file_get_contents($pdoBoundaryPlan);
    foreach (array(
        'Status: Completed',
        'repository and external-directory `make check` passed',
        'hostile PDO boundary mutations were rejected',
        'generated-artifact and credential-pattern audits passed',
        'No live database, production schema, credentials, or deployment was exercised',
    ) as $evidence) {
        if (strpos($body, $evidence) === false) {
            fail('docs/plans/2026-06-15-pdo-database-boundary.md must record verification evidence: ' . $evidence);
        }
    }
}

if (is_file($pdoRowBudgetPlan)) {
    $body = file_get_contents($pdoRowBudgetPlan);
    foreach (array(
        'Status: Completed',
        'repository and external-directory `make check` passed',
        'four hostile PDO row-budget mutations were rejected',
        'generated-artifact and credential-pattern audits passed',
        'No live database, production schema, credentials, deployment, or rendered browser session was exercised',
    ) as $evidence) {
        if (strpos($body, $evidence) === false) {
            fail('docs/plans/2026-06-17-bounded-pdo-result-rows.md must record verification evidence: ' . $evidence);
        }
    }
}

if (is_file($encodedResultBudgetPlan)) {
    $body = file_get_contents($encodedResultBudgetPlan);
    foreach (array(
        'Status: Completed',
        'repository and external-directory `make check` passed',
        'five hostile encoded-result byte-budget mutations were rejected',
        'generated-artifact and credential-pattern audits passed',
        'No live database, production schema, credentials, deployment, or rendered browser session was exercised',
    ) as $evidence) {
        if (strpos($body, $evidence) === false) {
            fail('docs/plans/2026-06-17-bounded-encoded-result-response.md must record verification evidence: ' . $evidence);
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
    if (strpos($body, 'content-length preflight') === false) {
        fail($documentation . ' must document the Content-Length preflight');
    }
    if (strpos($body, 'pdo prepared statement boundary') === false) {
        fail($documentation . ' must document the PDO prepared statement boundary');
    }
    if (strpos($body, 'bounded incremental pdo result rows') === false) {
        fail($documentation . ' must document bounded incremental PDO result rows');
    }
    if (strpos($body, 'bounded encoded database result response') === false) {
        fail($documentation . ' must document the bounded encoded database result response');
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
if (preg_match('/mysql_[a-z_]+\s*\(/i', $findSource)) {
    fail('find.php must not call removed mysql_* APIs');
}
foreach (array(
    "define('TCP_TITLE_SQL', '');",
    "define('TCP_GROUP_SQL', '');",
    "array('TCP_DB_DSN', 'TCP_DB_USER', 'TCP_DB_PASSWORD')",
    'PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION',
    'PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC',
    'PDO::ATTR_EMULATE_PREPARES => false',
    "define('TCP_MAX_RESULT_ROWS', 500);",
    "define('TCP_MAX_RESULT_BYTES', 262144);",
    'function tcp_query_rows($database, $sql, $term, $city, $maxRows = TCP_MAX_RESULT_ROWS)',
    'if (!is_int($maxRows) || $maxRows < 1) {',
    '$statement = $database->prepare($sql);',
    '$statement->execute(array(\'term\' => $term, \'city\' => $city))',
    '$row = $statement->fetch(PDO::FETCH_ASSOC);',
    'if (count($rows) === $maxRows) {',
    "throw new RuntimeException('database result row limit exceeded');",
    'function tcp_append_bounded_html(&$html, $chunk, $maxBytes)',
    '$chunkBytes = strlen($chunk);',
    'if ($chunkBytes > $maxBytes - strlen($html)) {',
    "throw new RuntimeException('encoded result byte limit exceeded');",
    'function tcp_render_result_rows($titleRows, $groupRows, $maxBytes = TCP_MAX_RESULT_BYTES)',
    '$output = tcp_render_result_rows($titleRows, $groupRows);',
    '} catch (Throwable $error) {',
    "if (!defined('TCP_FIND_LIBRARY_ONLY'))",
) as $contract) {
    if (strpos($findSource, $contract) === false) {
        fail('find.php must retain PDO boundary contract: ' . $contract);
    }
}
if (strpos($findSource, 'fetchAll(') !== false) {
    fail('find.php must not materialize unbounded PDO result sets with fetchAll');
}
if (preg_match('/\$sql\s*\.=|\$sql\s*=\s*[^;]*\$(?:term|city)/', $findSource)) {
    fail('find.php must not interpolate search input into SQL');
}

$pdoTestSource = file_get_contents($root . '/tests/check-find-pdo-boundary.php');
foreach (array(
    'foreach (array(\'TCP_DB_DSN\', \'TCP_DB_USER\', \'TCP_DB_PASSWORD\') as $missing)',
    'unset($incomplete[$missing])',
    'PDO options must require exceptions, associative rows, and native prepares',
    'query helper must prepare exact SQL and bind normalized term and city values',
    'query helper must reject non-positive and non-integer row budgets',
    'query helper must return an exact-budget result set unchanged',
    'query helper must reject the first row beyond the result budget',
    'blank SQL must return no rows without touching the database',
    "new FakeStatement(array(array('title' => 'must not leak', 'salary' => 1)))",
    'database failures must return only the generic no-match response',
) as $contract) {
    if (strpos($pdoTestSource, $contract) === false) {
        fail('PDO boundary test must retain mutation-sensitive assertion: ' . $contract);
    }
}

$pdoMutationSource = file_get_contents($root . '/tests/check-pdo-row-budget-mutations.php');
foreach (array(
    'disabled budget validation',
    'off-by-one overflow check',
    'all-at-once row fetch',
    'partial overflow return',
) as $mutation) {
    if (strpos($pdoMutationSource, $mutation) === false) {
        fail('PDO row budget mutation suite must retain hostile mutation: ' . $mutation);
    }
}

$indexSource = file_get_contents($root . '/index.php');
if (strpos($indexSource, 'substr((string) $_GET[$key], 0, 100)') === false) {
    fail('index.php must bound scalar query values before rendering or sharing');
}
if (strpos($indexSource, 'http_build_query($params, \'\', \'&\', PHP_QUERY_RFC3986)') === false) {
    fail('index.php must build share URLs from independent encoded filters');
}

echo "docs plans checks passed" . PHP_EOL;
