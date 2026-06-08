<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$root = dirname(__DIR__);
$canonical = $root . '/docs/plans/2026-06-08-techcompanypay-baseline.md';

if (!is_file($canonical)) {
    fail('docs/plans/2026-06-08-techcompanypay-baseline.md is missing');
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

echo "docs plans checks passed" . PHP_EOL;
