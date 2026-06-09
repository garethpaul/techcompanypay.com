<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

set_error_handler(function($severity, $message) {
    fail('index.php emitted a warning for non-scalar query input: ' . $message);
});

$_GET = array(
    'c' => array('nested' => '<script>alert(1)</script>'),
    'l' => array('nested' => 'A&B'),
    't' => ''
);

ob_start();
include __DIR__ . '/../index.php';
$html = ob_get_clean();
restore_error_handler();

if (strpos($html, 'Array') !== false) {
    fail('array query parameters should not render as Array');
}

if (strpos($html, '<script>alert(1)</script>') !== false) {
    fail('array query parameters should not render raw nested values');
}

$_GET = array(
    'c' => str_repeat('A', 150),
);

if (tcp_get('c') !== str_repeat('A', 100)) {
    fail('scalar query parameters should be bounded before rendering or sharing');
}

echo "index scalar input checks passed" . PHP_EOL;
