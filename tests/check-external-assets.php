<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$source = file_get_contents(__DIR__ . '/../index.php');

foreach (array('src', 'href') as $attribute) {
    if (preg_match('/\b' . $attribute . '\s*=\s*[\'"]\/\//i', $source)) {
        fail('index.php must use explicit HTTPS for ' . $attribute . ' assets');
    }

    if (preg_match('/\b' . $attribute . '\s*=\s*[\'"]http:\/\//i', $source)) {
        fail('index.php must not load insecure HTTP ' . $attribute . ' assets');
    }
}

if (preg_match('/\bjs\.src\s*=\s*[\'"]\/\//i', $source)) {
    fail('index.php dynamic script URLs must use explicit HTTPS');
}

if (preg_match('/\bjs\.src\s*=\s*[\'"]http:\/\//i', $source)) {
    fail('index.php dynamic script URLs must not use insecure HTTP');
}

if (strpos($source, 'https://connect.facebook.net/en_US/all.js#xfbml=1') === false) {
    fail('index.php must load the Facebook widget over explicit HTTPS');
}

echo "external asset checks passed" . PHP_EOL;
