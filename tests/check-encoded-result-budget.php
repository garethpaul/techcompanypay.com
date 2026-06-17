<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function expect_exception($callback, $class, $message) {
    try {
        $callback();
    } catch (Throwable $error) {
        if ($error instanceof $class) {
            return;
        }
        fail($message . ': unexpected ' . get_class($error));
    }
    fail($message . ': exception was not raised');
}

class EncodedBudgetStatement {
    private $rows;

    public function __construct($rows) {
        $this->rows = $rows;
    }

    public function execute($parameters) {
        return true;
    }

    public function fetch($mode = null) {
        if (count($this->rows) === 0) {
            return false;
        }
        return array_shift($this->rows);
    }
}

class EncodedBudgetDatabase {
    private $resultSets;

    public function __construct($resultSets) {
        $this->resultSets = $resultSets;
    }

    public function prepare($sql) {
        return new EncodedBudgetStatement(array_shift($this->resultSets));
    }
}

define('TCP_FIND_LIBRARY_ONLY', true);
$source = getenv('TCP_FIND_SOURCE');
require $source ? $source : __DIR__ . '/../find.php';

$encodedChunk = '';
tcp_append_bounded_html($encodedChunk, '&amp;', 5);
if ($encodedChunk !== '&amp;') {
    fail('encoded append must preserve the exact rendered bytes');
}
expect_exception(function () {
    $html = '';
    tcp_append_bounded_html($html, '&amp;', 4);
}, 'RuntimeException', 'encoded entity bytes must count toward the byte budget');

$multibyteChunk = '';
tcp_append_bounded_html($multibyteChunk, "\xC3\xA9", 2);
expect_exception(function () {
    $html = '';
    tcp_append_bounded_html($html, "\xC3\xA9", 1);
}, 'RuntimeException', 'multibyte UTF-8 bytes must count toward the byte budget');

$titleRows = array(array('title' => 'R&D <Lead>', 'salary' => '123456'));
$groupRows = array(array('group_name' => 'Platform & Data', 'salary' => '98765'));
$rendered = tcp_render_result_rows($titleRows, $groupRows);
if (strpos($rendered, 'R%26D%20%3CLead%3E') === false || strpos($rendered, 'R&amp;D &lt;Lead&gt;') === false) {
    fail('encoded result rendering must preserve URL and HTML escaping');
}
if (strlen($rendered) <= strlen('R&D <Lead>Platform & Data')) {
    fail('encoded result budget must measure expanded output bytes');
}

$exactBytes = strlen($rendered);
if (tcp_render_result_rows($titleRows, $groupRows, $exactBytes) !== $rendered) {
    fail('an encoded response exactly at the byte budget must succeed');
}
expect_exception(function () use ($titleRows, $groupRows, $exactBytes) {
    tcp_render_result_rows($titleRows, $groupRows, $exactBytes - 1);
}, 'RuntimeException', 'one-byte encoded response overflow must fail');

foreach (array(0, -1, 1.5, '262144') as $invalidLimit) {
    expect_exception(function () use ($invalidLimit) {
        tcp_render_result_rows(array(), array(), $invalidLimit);
    }, 'InvalidArgumentException', 'invalid encoded response byte limits must fail');
}

$largeValue = str_repeat('<&', 40000);
expect_exception(function () use ($largeValue) {
    tcp_render_result_rows(
        array(array('title' => $largeValue, 'salary' => 1)),
        array(),
        1024
    );
}, 'RuntimeException', 'HTML and URL expansion must count against the byte budget');

$titleBudgetValue = str_repeat('x', 70000);
$groupBudgetValue = str_repeat('x', 140000);
$database = new EncodedBudgetDatabase(array(
    array(array('title' => $titleBudgetValue, 'salary' => 1)),
    array(array('group_name' => $groupBudgetValue, 'salary' => 1)),
));
$environment = array(
    'TCP_DB_DSN' => 'sqlite::memory:',
    'TCP_DB_USER' => 'test',
    'TCP_DB_PASSWORD' => 'test',
);
$_POST = array('search_term' => 'engineering', 'city' => 'remote');
ob_start();
tcp_run_find_endpoint(function () use ($database) {
    return $database;
}, $environment, 'title sql', 'group sql');
$output = ob_get_clean();
if ($output !== 'No matches!') {
    fail('combined encoded response overflow must fail closed without partial output');
}

echo "encoded result byte-budget checks passed" . PHP_EOL;
