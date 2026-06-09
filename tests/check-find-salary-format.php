<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$_POST = array(
    'search_term' => 'Engineering',
    'city' => 'San Francisco',
);

ob_start();
include __DIR__ . '/../find.php';
ob_end_clean();

if (tcp_salary('1234.56') !== '1,235') {
    fail('numeric salary values should be formatted consistently');
}

if (tcp_salary('not a salary') !== '0') {
    fail('non-numeric salary values should format as 0');
}

if (tcp_salary(array('nested' => '1000')) !== '0') {
    fail('non-scalar salary values should format as 0');
}

echo "find salary format checks passed" . PHP_EOL;
