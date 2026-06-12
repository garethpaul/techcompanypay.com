<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$indexSource = file_get_contents(__DIR__ . '/../index.php');
$scriptSource = file_get_contents(__DIR__ . '/../assets/app.js');

foreach (array(
    'id="searchform" method="post" action="find.php"',
    'id="search_results" role="status" aria-live="polite"',
    'src="assets/app.js" defer',
) as $contract) {
    if (strpos($indexSource, $contract) === false) {
        fail('index.php must keep local search contract: ' . $contract);
    }
}

foreach (array(
    "form.addEventListener('submit'",
    "typeof window.AbortController === 'function'",
    'var requestController = new window.AbortController();',
    "method: 'POST'",
    'signal: requestController.signal',
    "window.fetch(form.action, options)",
    "company.value.slice(0, 100)",
    "city.value.slice(0, 100)",
    "requestNumber === latestRequest",
    "requestController.abort()",
    "}, 10000)",
    "results.textContent = 'Search is temporarily unavailable. Please try again.'",
    "company.value.trim() !== '' || city.value.trim() !== ''",
) as $contract) {
    if (strpos($scriptSource, $contract) === false) {
        fail('assets/app.js must keep search contract: ' . $contract);
    }
}

if (strpos($scriptSource, "typeof window.AbortController === 'function' ?") !== false) {
    fail('assets/app.js must not enable asynchronous search without guaranteed abort support');
}

$behaviorSource = file_get_contents(__DIR__ . '/check-local-search.js');
foreach (array(
    'missing AbortController should keep native submission',
    'missing AbortController should not start an asynchronous submit',
    'missing AbortController should not start a prefilled search',
) as $contract) {
    if (strpos($behaviorSource, $contract) === false) {
        fail('tests/check-local-search.js must keep missing-abort coverage: ' . $contract);
    }
}

if (preg_match('/https?:\/\//i', $scriptSource)) {
    fail('assets/app.js must keep search requests same-origin');
}

echo "local search checks passed" . PHP_EOL;
