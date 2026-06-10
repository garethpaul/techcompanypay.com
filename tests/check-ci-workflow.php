<?php
$workflowPath = __DIR__ . '/../.github/workflows/check.yml';
$workflow = file_get_contents($workflowPath);

if ($workflow === false) {
    fwrite(STDERR, "Unable to read CI workflow.\n");
    exit(1);
}

$contracts = array(
    "permissions:\n  contents: read",
    'timeout-minutes: 10',
    "php-version: ['8.2', '8.4']",
    'fail-fast: false',
    'actions/checkout@df4cb1c069e1874edd31b4311f1884172cec0e10',
    'shivammathur/setup-php@f3e473d116dcccaddc5834248c87452386958240',
    'run: make check',
);

foreach ($contracts as $contract) {
    if (strpos($workflow, $contract) === false) {
        fwrite(STDERR, "CI workflow contract is missing: {$contract}\n");
        exit(1);
    }
}

if (preg_match('/uses:\s+[^\s]+@(v|master|main|latest)/', $workflow)) {
    fwrite(STDERR, "CI workflow must not use mutable action references.\n");
    exit(1);
}

echo "CI workflow checks passed\n";
