<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$source = file_get_contents(__DIR__ . '/../find.php');
if (strpos($source, 'die(') !== false) {
    fail('find.php must not expose backend connection errors with die()');
}

$_POST = array(
    'search_term' => '<script>alert(1)</script>',
    'city' => 'A&B',
);

ob_start();
include __DIR__ . '/../find.php';
$output = ob_get_clean();

if ($output !== 'No matches!') {
    fail('find.php must fail closed with a generic no-match response');
}

echo "find fail-closed checks passed" . PHP_EOL;
