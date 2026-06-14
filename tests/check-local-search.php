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
    "typeof window.Blob === 'function'",
    'var requestController = new window.AbortController();',
    "method: 'POST'",
    'signal: requestController.signal',
    "window.fetch(form.action, options)",
    "company.value.slice(0, 100)",
    "city.value.slice(0, 100)",
    "results.setAttribute('aria-busy', 'true')",
    "requestNumber === latestRequest",
    "requestController.abort()",
    "}, 10000)",
    'var MAX_SEARCH_RESPONSE_LENGTH = 256 * 1024;',
    "responseContentType(response) !== 'text/html'",
    'return new window.Blob([html]).size;',
    'responseByteLength(html) > MAX_SEARCH_RESPONSE_LENGTH',
    "results.removeAttribute('aria-busy')",
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
    'response exactly at limit should render',
    'oversized response should not render',
    'multibyte response above byte limit should not render',
    'non-HTML response should be rejected before reading its body',
    'missing content type should be rejected before reading its body',
    'active search should mark results busy',
    'stale request should not clear newer busy state',
    'successful search should clear busy state',
    'failed search should clear busy state',
    'timed out search should clear busy state',
    'native fallback should not mark results busy',
    'prefilled search should mark results busy',
    'missing Blob should keep native submission',
    'missing Blob should not start an asynchronous submit or prefilled search',
) as $contract) {
    if (strpos($behaviorSource, $contract) === false) {
        fail('tests/check-local-search.js must keep required behavior coverage: ' . $contract);
    }
}

if (strpos($scriptSource, "if (requestNumber === latestRequest) {\n          activeRequest = null;\n          submit.disabled = false;\n          results.removeAttribute('aria-busy');\n        }") === false) {
    fail('assets/app.js must clear busy state only when the latest request settles');
}

if (strpos($scriptSource, "results.textContent = 'Searching salary data…';\n    submit.disabled = true;\n    results.setAttribute('aria-busy', 'true');\n\n    var options") === false) {
    fail('assets/app.js must mark results busy before starting the async request');
}

$byteLimitPosition = strpos($scriptSource, 'if (responseByteLength(html) > MAX_SEARCH_RESPONSE_LENGTH)');
$innerHtmlPosition = strpos($scriptSource, 'results.innerHTML = html;');
if ($byteLimitPosition === false || $innerHtmlPosition === false || $byteLimitPosition > $innerHtmlPosition) {
    fail('assets/app.js must enforce the UTF-8 byte response limit before DOM insertion');
}

if (preg_match('/https?:\/\//i', $scriptSource)) {
    fail('assets/app.js must keep search requests same-origin');
}

echo "local search checks passed" . PHP_EOL;
