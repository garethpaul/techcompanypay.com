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
    'actions/checkout@34e114876b0b11c390a56381ad16ebd13914f8d5',
    'shivammathur/setup-php@bf6b4fbd49ca58e4608c9c89fba0b8d90bd2a39f',
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

