<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
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

try {
    foreach ($mutations as $name => $replacement) {
        list($needle, $mutant) = $replacement;
        if (substr_count($source, $needle) !== 1) {
            fail('mutation anchor must occur exactly once: ' . $name);
        }
        $mutatedSource = str_replace($needle, $mutant, $source);
        file_put_contents($temporaryRoot . '/find.php', $mutatedSource);
        file_put_contents($temporaryTests . '/check-find-pdo-boundary.php', $test);

        $command = escapeshellarg(PHP_BINARY) . ' ' .
            escapeshellarg($temporaryTests . '/check-find-pdo-boundary.php') . ' 2>&1';
        $output = array();
        $status = 0;
        exec($command, $output, $status);
        if ($status === 0) {
            fail('PDO row budget mutation survived: ' . $name);
        }
    }
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

echo 'PDO row budget mutations rejected (' . count($mutations) . ' mutations).' . PHP_EOL;
