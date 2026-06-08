<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$_GET = array(
    'c' => '"><script>alert(1)</script>',
    'l' => 'A&B',
    't' => ''
);

ob_start();
include __DIR__ . '/../index.php';
$html = ob_get_clean();

if (strpos($html, $_GET['c']) !== false) {
    fail('raw company query parameter was rendered');
}

if (strpos($html, '&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;') === false) {
    fail('escaped company query parameter was not rendered');
}

if (strpos($html, 'A&amp;B') === false) {
    fail('escaped city query parameter was not rendered');
}

echo "index escaping checks passed" . PHP_EOL;
