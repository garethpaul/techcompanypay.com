<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function write_exact_file($path, $contents) {
    $written = file_put_contents($path, $contents);
    if ($written !== strlen($contents)) {
        throw new RuntimeException('unable to write mutation test file: ' . $path);
    }
}

function run_method_test($test, $source) {
    $command = 'TCP_FIND_SOURCE=' . escapeshellarg($source) . ' ' .
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test) . ' 2>&1';
    $output = array();
    $status = 0;
    exec($command, $output, $status);
    return array($status, $output);
}

$root = dirname(__DIR__);
$source = file_get_contents($root . '/find.php');
$test = $root . '/tests/check-find-request-method.php';
if ($source === false || !is_file($test)) {
    fail('unable to read request method boundary sources');
}

$gate = "  if (tcp_request_method(\$server) !== 'POST') {\n" .
    "    if (!headers_sent()) {\n" .
    "      http_response_code(405);\n" .
    "      header('Allow: POST');\n" .
    "    }\n" .
    "    echo 'No matches!';\n" .
    "    return;\n" .
    "  }\n";
$config = "  \$config = tcp_database_config(\$environment);\n";
$gateWithoutReturn = "  if (tcp_request_method(\$server) !== 'POST') {\n" .
    "    if (!headers_sent()) {\n" .
    "      http_response_code(405);\n" .
    "      header('Allow: POST');\n" .
    "    }\n" .
    "    echo 'No matches!';\n" .
    "  }\n";
$mutations = array(
    'disabled method gate' => array(
        "if (tcp_request_method(\$server) !== 'POST') {",
        'if (false) {',
    ),
    'inverted method gate' => array(
        "tcp_request_method(\$server) !== 'POST'",
        "tcp_request_method(\$server) === 'POST'",
    ),
    'successful non-POST status' => array(
        'http_response_code(405);',
        'http_response_code(200);',
    ),
    'incorrect allowed method' => array(
        "header('Allow: POST');",
        "header('Allow: GET');",
    ),
    'missing early return' => array(
        $gate,
        $gateWithoutReturn,
    ),
    'database configuration before method gate' => array(
        $gate . $config,
        $config . $gate,
    ),
);

$temporaryRoot = sys_get_temp_dir() . '/techcompanypay-request-method-' . bin2hex(random_bytes(8));
if (!mkdir($temporaryRoot, 0700, true)) {
    fail('unable to create request method mutation directory');
}

$failure = null;
try {
    $temporarySource = $temporaryRoot . '/find.php';
    write_exact_file($temporarySource, $source);

    list($controlStatus, $controlOutput) = run_method_test($test, $temporarySource);
    if ($controlStatus !== 0) {
        throw new RuntimeException(
            'request method mutation control failed: ' . implode("\n", $controlOutput)
        );
    }

    foreach ($mutations as $name => $replacement) {
        list($needle, $mutant) = $replacement;
        if (substr_count($source, $needle) !== 1) {
            throw new RuntimeException('mutation anchor must occur exactly once: ' . $name);
        }
        write_exact_file($temporarySource, str_replace($needle, $mutant, $source));

        list($status, $output) = run_method_test($test, $temporarySource);
        if ($status === 0) {
            throw new RuntimeException('request method mutation survived: ' . $name);
        }
    }
} catch (Throwable $error) {
    $failure = $error;
} finally {
    if (isset($temporarySource) && is_file($temporarySource)) {
        unlink($temporarySource);
    }
    if (is_dir($temporaryRoot)) {
        rmdir($temporaryRoot);
    }
}

if ($failure !== null) {
    fail($failure->getMessage());
}

echo 'request method mutations rejected (' . count($mutations) . ' mutations).' . PHP_EOL;
