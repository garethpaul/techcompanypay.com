<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

set_error_handler(function($severity, $message) {
    fail('find.php emitted a warning for non-scalar POST input: ' . $message);
});

$_POST = array(
    'search_term' => array('nested' => '<script>alert(1)</script>'),
    'city' => array('nested' => 'A&B'),
);

ob_start();
include __DIR__ . '/../find.php';
ob_end_clean();

if (tcp_post_value('search_term') !== '') {
    fail('array POST values should normalize to an empty search term');
}

if (tcp_post_value('city') !== '') {
    fail('array POST values should normalize to an empty city');
}

$_POST = array(
    'search_term' => '<b>Engineering</b>',
    'city' => 'San Francisco',
);

if (tcp_post_value('search_term') !== 'Engineering') {
    fail('scalar POST search terms should still be stripped and returned');
}

if (tcp_post_value('city') !== 'San Francisco') {
    fail('scalar POST city values should still be returned');
}

restore_error_handler();

echo "find scalar input checks passed" . PHP_EOL;
