<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

define('TCP_FIND_LIBRARY_ONLY', true);
$findSource = getenv('TCP_FIND_SOURCE');
if ($findSource === false || $findSource === '') {
    $findSource = __DIR__ . '/../find.php';
}
require $findSource;

$source = file_get_contents($findSource);
if ($source === false) {
    fail('unable to read find.php request method source');
}
$methodGate = strpos($source, "if (tcp_request_method(\$server) !== 'POST') {");
$databaseConfig = strpos($source, '$config = tcp_database_config($environment);');
if ($methodGate === false || $databaseConfig === false || $methodGate > $databaseConfig) {
    fail('POST method gate must run before database configuration');
}
if (strpos($source, "header('Allow: POST');") === false) {
    fail('non-POST responses must advertise only POST');
}

$environment = array(
    'TCP_DB_DSN' => 'test:dsn',
    'TCP_DB_USER' => 'test-user',
    'TCP_DB_PASSWORD' => 'test-password',
);

function capture_request_method($server, &$factoryCalls, $environment) {
    $_POST = array('search_term' => 'Engineering', 'city' => 'San Francisco');
    http_response_code(200);
    ob_start();
    tcp_run_find_endpoint(function() use (&$factoryCalls) {
        $factoryCalls++;
        return new stdClass();
    }, $environment, '', '', $server);
    return array(ob_get_clean(), http_response_code());
}

foreach (array(
    array('REQUEST_METHOD' => 'GET'),
    array('REQUEST_METHOD' => 'HEAD'),
    array('REQUEST_METHOD' => 'PUT'),
    array('REQUEST_METHOD' => 'DELETE'),
    array(),
    array('REQUEST_METHOD' => array('POST')),
    'invalid server metadata',
) as $server) {
    $factoryCalls = 0;
    list($output, $status) = capture_request_method($server, $factoryCalls, $environment);
    if ($output !== 'No matches!' || $status !== 405 || $factoryCalls !== 0) {
        fail('non-POST requests must fail closed before database creation');
    }
}

$factoryCalls = 0;
list($output, $status) = capture_request_method(array('REQUEST_METHOD' => 'POST'), $factoryCalls, $environment);
if ($output !== 'No matches!No matches!' || $status !== 200 || $factoryCalls !== 1) {
    fail('POST requests must retain the configured search path');
}

echo "find request method checks passed" . PHP_EOL;
