<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$_GET = array();
ob_start();
include __DIR__ . '/../index.php';
ob_end_clean();

$cases = array(
    array('', '', 'https://techcompanypay.com/'),
    array('Open AI', '', 'https://techcompanypay.com/?c=Open%20AI'),
    array('', 'New York', 'https://techcompanypay.com/?l=New%20York'),
    array('A&B', 'San Francisco', 'https://techcompanypay.com/?c=A%26B&l=San%20Francisco'),
);

foreach ($cases as $case) {
    $actual = tcp_share_url($case[0], $case[1]);
    if ($actual !== $case[2]) {
        fail('unexpected share URL: ' . $actual . ' expected ' . $case[2]);
    }
}

echo "share URL checks passed" . PHP_EOL;
