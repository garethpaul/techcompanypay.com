<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

foreach (array('index.php', 'find.php') as $entrypoint) {
    $source = file_get_contents(__DIR__ . '/../' . $entrypoint);
    foreach (array(
        "header('Content-Type: text/html; charset=UTF-8')",
        "header('X-Content-Type-Options: nosniff')",
        "header('Referrer-Policy: strict-origin-when-cross-origin')",
    ) as $headerCall) {
        if (strpos($source, $headerCall) === false) {
            fail($entrypoint . ' must send ' . $headerCall);
        }
    }
}

echo "security header checks passed" . PHP_EOL;
