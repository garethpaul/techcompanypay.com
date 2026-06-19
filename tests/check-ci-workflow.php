<?php
$workflowPath = __DIR__ . '/../.github/workflows/check.yml';
$workflowFiles = glob(__DIR__ . '/../.github/workflows/*.{yml,yaml}', GLOB_BRACE);

if ($workflowFiles !== array($workflowPath)) {
    fwrite(STDERR, "Repository must keep exactly one reviewed CI workflow.\n");
    exit(1);
}

$workflow = file_get_contents($workflowPath);

if ($workflow === false) {
    fwrite(STDERR, "Unable to read CI workflow.\n");
    exit(1);
}

$contracts = array(
    "permissions:\n  contents: read",
    'concurrency:',
    'cancel-in-progress: true',
    'runs-on: ubuntu-24.04',
    'timeout-minutes: 10',
    "php-version: ['8.2', '8.4', '8.5']",
    'fail-fast: false',
    'workflow_dispatch:',
    'actions/checkout@df4cb1c069e1874edd31b4311f1884172cec0e10',
    'persist-credentials: false',
    'actions/setup-node@48b55a011bda9f5d6aeb4c2d9c7362e8dae4041e',
    "node-version: '24'",
    'shivammathur/setup-php@f3e473d116dcccaddc5834248c87452386958240',
    'run: make check',
);

foreach ($contracts as $contract) {
    if (strpos($workflow, $contract) === false) {
        fwrite(STDERR, "CI workflow contract is missing: {$contract}\n");
        exit(1);
    }
}

if (substr_count($workflow, 'persist-credentials: false') !== 1) {
    fwrite(STDERR, "Every checkout step must disable credential persistence.\n");
    exit(1);
}

if (preg_match('/uses:\s+[^\s]+@(v|master|main|latest)/', $workflow)) {
    fwrite(STDERR, "CI workflow must not use mutable action references.\n");
    exit(1);
}

echo "CI workflow checks passed\n";
