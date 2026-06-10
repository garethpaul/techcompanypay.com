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

foreach (array(
    'jquery-1.6.2.min.js',
    'core2.js',
    'd2dixsj1sor9iw.cloudfront.net',
    'connect.facebook.net',
    'apis.google.com/js/plusone.js',
    'statcounter.com/counter',
) as $obsoleteAsset) {
    if (strpos($source, $obsoleteAsset) !== false) {
        fail('index.php must not load obsolete runtime asset: ' . $obsoleteAsset);
    }
}

foreach (array('href="assets/app.css"', 'src="assets/app.js"') as $localAsset) {
    if (strpos($source, $localAsset) === false) {
        fail('index.php must load local runtime asset: ' . $localAsset);
    }
}

if (preg_match('/<script(?![^>]*\bsrc=)[^>]*>/i', $source)) {
    fail('index.php must not contain inline scripts');
}

if (preg_match('/<script[^>]+src=[\'\"]https?:\/\//i', $source)) {
    fail('index.php must not load remote scripts');
}

echo "external asset checks passed" . PHP_EOL;
