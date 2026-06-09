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
        "header('X-Frame-Options: DENY')",
        "header('Referrer-Policy: strict-origin-when-cross-origin')",
        "header('Permissions-Policy: camera=(), microphone=(), geolocation=()')",
        "header(\"Content-Security-Policy: default-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: data:; frame-src https:; connect-src 'self'\")",
    ) as $headerCall) {
        if (strpos($source, $headerCall) === false) {
            fail($entrypoint . ' must send ' . $headerCall);
        }
    }
}

echo "security header checks passed" . PHP_EOL;
