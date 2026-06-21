<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function render_index_with_canonical_url($canonicalUrl, $query) {
    $script = '$_GET = ' . var_export($query, true) . '; include "index.php";';
    $descriptors = array(
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $process = proc_open(
        array(PHP_BINARY, '-d', 'display_errors=1', '-r', $script),
        $descriptors,
        $pipes,
        dirname(__DIR__),
        array('TCP_CANONICAL_URL' => $canonicalUrl)
    );
    if (!is_resource($process)) {
        fail('unable to start isolated index render');
    }

    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    if ($status !== 0) {
        fail('isolated index render failed: ' . trim($error));
    }

    return $output;
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

$configuredPage = render_index_with_canonical_url(
    'https://archive.example/base',
    array('c' => 'A&B', 'l' => 'San Francisco')
);
$configuredMeta = '<meta property="og:url" content="https://archive.example/base/?c=A%26B&amp;l=San%20Francisco"/>';
if (substr_count($configuredPage, $configuredMeta) !== 1) {
    fail('configured production render must emit exact escaped og:url metadata');
}

$invalidPage = render_index_with_canonical_url(
    'javascript:alert(1)',
    array('c' => 'Open AI', 'l' => 'New York')
);
if (strpos($invalidPage, 'property="og:url"') !== false) {
    fail('configured invalid production render must omit og:url metadata');
}

echo "share URL checks passed" . PHP_EOL;
