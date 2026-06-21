<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

putenv('TCP_CANONICAL_URL');
$_GET = array();
ob_start();
include __DIR__ . '/../index.php';
$page = ob_get_clean();

if (strpos($page, 'property="og:url"') !== false) {
    fail('unconfigured pages must omit og:url');
}

$cases = array(
    array(array('TCP_CANONICAL_URL' => 'https://archive.example'), '', '', 'https://archive.example/'),
    array(array('TCP_CANONICAL_URL' => 'https://archive.example/base/'), 'Open AI', '', 'https://archive.example/base/?c=Open%20AI'),
    array(array('TCP_CANONICAL_URL' => 'http://127.0.0.1:8000/demo'), '', 'New York', 'http://127.0.0.1:8000/demo/?l=New%20York'),
    array(array('TCP_CANONICAL_URL' => 'https://archive.example'), 'A&B', 'San Francisco', 'https://archive.example/?c=A%26B&l=San%20Francisco'),
);

foreach ($cases as $case) {
    $actual = tcp_share_url($case[1], $case[2], $case[0]);
    if ($actual !== $case[3]) {
        fail('unexpected share URL: ' . var_export($actual, true) . ' expected ' . $case[3]);
    }
}

$invalidEnvironments = array(
    array(),
    array('TCP_CANONICAL_URL' => false),
    array('TCP_CANONICAL_URL' => ''),
    array('TCP_CANONICAL_URL' => 'javascript:alert(1)'),
    array('TCP_CANONICAL_URL' => '//archive.example/'),
    array('TCP_CANONICAL_URL' => 'https://user:password@archive.example/'),
    array('TCP_CANONICAL_URL' => 'https://archive.example/?source=legacy'),
    array('TCP_CANONICAL_URL' => 'https://archive.example/#fragment'),
    array('TCP_CANONICAL_URL' => 'https://archive.example/" onmouseover="alert(1)'),
);

foreach ($invalidEnvironments as $environment) {
    if (tcp_share_url('Open AI', 'New York', $environment) !== null) {
        fail('invalid canonical URL must not produce a share URL');
    }
    if (tcp_share_meta('Open AI', 'New York', $environment) !== '') {
        fail('invalid canonical URL must not produce og:url metadata');
    }
}

$meta = tcp_share_meta(
    'A&B',
    'San Francisco',
    array('TCP_CANONICAL_URL' => 'https://archive.example')
);
$expectedMeta = '<meta property="og:url" content="https://archive.example/?c=A%26B&amp;l=San%20Francisco"/>';
if ($meta !== $expectedMeta) {
    fail('configured og:url metadata must preserve escaped filter encoding');
}

echo "share URL checks passed" . PHP_EOL;
