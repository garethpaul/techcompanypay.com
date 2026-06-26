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

function run_budget_test($test, $source) {
    $command = 'TCP_FIND_SOURCE=' . escapeshellarg($source) . ' ' .
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test) . ' 2>&1';
    $output = array();
    $status = 0;
    exec($command, $output, $status);
    return array($status, $output);
}

$root = dirname(__DIR__);
$source = file_get_contents($root . '/find.php');
$input = file_get_contents($root . '/input.php');
$test = $root . '/tests/check-encoded-result-budget.php';
if ($source === false || $input === false || !is_file($test)) {
    fail('unable to read encoded result byte-budget sources');
}

$mutations = array(
    'disabled byte-limit validation' => array(
        'if (!is_int($maxBytes) || $maxBytes < 1) {',
        'if (false) {',
    ),
    'character-style chunk measurement' => array(
        '$chunkBytes = strlen($chunk);',
        '$chunkBytes = strlen(html_entity_decode($chunk, ENT_QUOTES, \'UTF-8\'));',
    ),
    'exact-budget rejection' => array(
        'if ($chunkBytes > $maxBytes - strlen($html)) {',
        'if ($chunkBytes >= $maxBytes - strlen($html)) {',
    ),
    'per-chunk instead of cumulative accounting' => array(
        'if ($chunkBytes > $maxBytes - strlen($html)) {',
        'if ($chunkBytes > $maxBytes) {',
    ),
    'independent endpoint table budgets' => array(
        '$output = tcp_render_result_rows($titleRows, $groupRows);',
        '$output = tcp_render_title_rows($titleRows) . tcp_render_group_rows($groupRows);',
    ),
);

$temporaryRoot = sys_get_temp_dir() . '/techcompanypay-encoded-budget-' . bin2hex(random_bytes(8));
if (!mkdir($temporaryRoot, 0700, true)) {
    fail('unable to create encoded budget mutation directory');
}

$failure = null;
try {
    $temporarySource = $temporaryRoot . '/find.php';
    $temporaryInput = $temporaryRoot . '/input.php';
    write_exact_file($temporarySource, $source);
    write_exact_file($temporaryInput, $input);

    list($controlStatus, $controlOutput) = run_budget_test($test, $temporarySource);
    if ($controlStatus !== 0) {
        throw new RuntimeException(
            'encoded result byte-budget mutation control failed: ' . implode("\n", $controlOutput)
        );
    }

    foreach ($mutations as $name => $replacement) {
        list($needle, $mutant) = $replacement;
        if (substr_count($source, $needle) !== 1) {
            throw new RuntimeException('mutation anchor must occur exactly once: ' . $name);
        }
        write_exact_file($temporarySource, str_replace($needle, $mutant, $source));

        list($status, $output) = run_budget_test($test, $temporarySource);
        if ($status === 0) {
            throw new RuntimeException('encoded result byte-budget mutation survived: ' . $name);
        }
    }
} catch (Throwable $error) {
    $failure = $error;
} finally {
    if (isset($temporarySource) && is_file($temporarySource)) {
        unlink($temporarySource);
    }
    if (isset($temporaryInput) && is_file($temporaryInput)) {
        unlink($temporaryInput);
    }
    if (is_dir($temporaryRoot)) {
        rmdir($temporaryRoot);
    }
}

if ($failure !== null) {
    fail($failure->getMessage());
}

echo 'encoded result byte-budget mutations rejected (' . count($mutations) . ' mutations).' . PHP_EOL;
