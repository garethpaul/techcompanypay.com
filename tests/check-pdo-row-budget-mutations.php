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

function run_boundary_test($path) {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path) . ' 2>&1';
    $output = array();
    $status = 0;
    exec($command, $output, $status);
    return array($status, $output);
}

$root = dirname(__DIR__);
$source = file_get_contents($root . '/find.php');
$test = file_get_contents($root . '/tests/check-find-pdo-boundary.php');
if ($source === false || $test === false) {
    fail('unable to read PDO row budget sources');
}

$mutations = array(
    'disabled budget validation' => array(
        'if (!is_int($maxRows) || $maxRows < 1) {',
        'if (false) {',
    ),
    'off-by-one overflow check' => array(
        'if (count($rows) === $maxRows) {',
        'if (count($rows) > $maxRows) {',
    ),
    'all-at-once row fetch' => array(
        '$row = $statement->fetch(PDO::FETCH_ASSOC);',
        '$row = $statement->fetchAll(PDO::FETCH_ASSOC);',
    ),
    'partial overflow return' => array(
        "throw new RuntimeException('database result row limit exceeded');",
        'return $rows;',
    ),
);

$temporaryRoot = sys_get_temp_dir() . '/techcompanypay-pdo-budget-' . bin2hex(random_bytes(8));
$temporaryTests = $temporaryRoot . '/tests';
if (!mkdir($temporaryTests, 0700, true)) {
    fail('unable to create mutation test directory');
}

$failure = null;
try {
    $temporarySource = $temporaryRoot . '/find.php';
    $temporaryTest = $temporaryTests . '/check-find-pdo-boundary.php';
    write_exact_file($temporarySource, $source);
    write_exact_file($temporaryTest, $test);

    list($controlStatus, $controlOutput) = run_boundary_test($temporaryTest);
    if ($controlStatus !== 0) {
        throw new RuntimeException(
            'PDO row budget mutation control failed: ' . implode("\n", $controlOutput)
        );
    }

    foreach ($mutations as $name => $replacement) {
        list($needle, $mutant) = $replacement;
        if (substr_count($source, $needle) !== 1) {
            throw new RuntimeException('mutation anchor must occur exactly once: ' . $name);
        }
        $mutatedSource = str_replace($needle, $mutant, $source);
        write_exact_file($temporarySource, $mutatedSource);

        list($status, $output) = run_boundary_test($temporaryTest);
        if ($status === 0) {
            throw new RuntimeException('PDO row budget mutation survived: ' . $name);
        }
    }
} catch (Throwable $error) {
    $failure = $error;
} finally {
    foreach (array(
        $temporaryTests . '/check-find-pdo-boundary.php',
        $temporaryRoot . '/find.php',
    ) as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    if (is_dir($temporaryTests)) {
        rmdir($temporaryTests);
    }
    if (is_dir($temporaryRoot)) {
        rmdir($temporaryRoot);
    }
}

if ($failure !== null) {
    fail($failure->getMessage());
}

echo 'PDO row budget mutations rejected (' . count($mutations) . ' mutations).' . PHP_EOL;
