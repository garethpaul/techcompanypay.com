<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

define('TCP_FIND_LIBRARY_ONLY', true);
require __DIR__ . '/../find.php';

class FakeStatement {
    public $executions = array();
    private $rows;
    private $executeResult;
    private $fetchResult;

    public function __construct($rows, $executeResult = true, $fetchResult = null) {
        $this->rows = $rows;
        $this->executeResult = $executeResult;
        $this->fetchResult = $fetchResult;
    }

    public function execute($parameters) {
        $this->executions[] = $parameters;
        return $this->executeResult;
    }

    public function fetchAll($mode) {
        if ($mode !== PDO::FETCH_ASSOC) {
            fail('query rows must request associative fetches');
        }
        return $this->fetchResult === null ? $this->rows : $this->fetchResult;
    }
}

class FakeDatabase {
    public $prepared = array();
    private $statements;
    private $prepareResult;

    public function __construct($statements = array(), $prepareResult = null) {
        $this->statements = $statements;
        $this->prepareResult = $prepareResult;
    }

    public function prepare($sql) {
        $this->prepared[] = $sql;
        if ($this->prepareResult !== null) {
            return $this->prepareResult;
        }
        return array_shift($this->statements);
    }
}

function capture_endpoint($factory, $environment, $titleSql, $groupSql) {
    ob_start();
    tcp_run_find_endpoint($factory, $environment, $titleSql, $groupSql);
    return ob_get_clean();
}

$complete = array(
    'TCP_DB_DSN' => 'mysql:host=db.example.test;dbname=pay;charset=utf8mb4',
    'TCP_DB_USER' => 'reader',
    'TCP_DB_PASSWORD' => '',
);
if (tcp_database_config($complete) !== array(
    'dsn' => $complete['TCP_DB_DSN'],
    'user' => 'reader',
    'password' => '',
)) {
    fail('complete environment configuration must preserve DSN, user, and password');
}
foreach (array('TCP_DB_DSN', 'TCP_DB_USER', 'TCP_DB_PASSWORD') as $missing) {
    $incomplete = $complete;
    unset($incomplete[$missing]);
    if (tcp_database_config($incomplete) !== null) {
        fail('missing ' . $missing . ' must disable database access');
    }
}

$options = tcp_pdo_options();
if ($options[PDO::ATTR_ERRMODE] !== PDO::ERRMODE_EXCEPTION ||
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] !== PDO::FETCH_ASSOC ||
        $options[PDO::ATTR_EMULATE_PREPARES] !== false) {
    fail('PDO options must require exceptions, associative rows, and native prepares');
}

$created = array();
$sentinel = new stdClass();
$factory = function($dsn, $user, $password, $factoryOptions) use (&$created, $sentinel) {
    $created = array($dsn, $user, $password, $factoryOptions);
    return $sentinel;
};
if (tcp_create_database(tcp_database_config($complete), $factory) !== $sentinel ||
        $created !== array($complete['TCP_DB_DSN'], 'reader', '', $options)) {
    fail('database factory must receive exact environment configuration and PDO options');
}

$statement = new FakeStatement(array(array('title' => 'R&D <Lead>', 'salary' => '1234.5')));
$database = new FakeDatabase(array($statement));
$sql = 'SELECT title, salary FROM salaries WHERE title = :term AND city = :city';
$rows = tcp_query_rows($database, $sql, 'R&D <Lead>', 'A&B');
if ($database->prepared !== array($sql) ||
        $statement->executions !== array(array('term' => 'R&D <Lead>', 'city' => 'A&B')) ||
        $rows[0]['title'] !== 'R&D <Lead>') {
    fail('query helper must prepare exact SQL and bind normalized term and city values');
}

$blankDatabase = new FakeDatabase();
try {
    $blankRows = tcp_query_rows($blankDatabase, '  ', 'term', 'city');
} catch (Throwable $error) {
    fail('blank SQL must return no rows without touching the database');
}
if ($blankRows !== array() || $blankDatabase->prepared !== array()) {
    fail('blank SQL must return no rows without touching the database');
}

$titleHtml = tcp_render_title_rows(array(array('title' => 'R&D <Lead>', 'salary' => '1234.5')));
if (strpos($titleHtml, 'R%26D%20%3CLead%3E') === false ||
        strpos($titleHtml, 'R&amp;D &lt;Lead&gt;') === false ||
        strpos($titleHtml, '$1,235') === false) {
    fail('title rows must preserve URL encoding, HTML escaping, and salary formatting');
}
$groupHtml = tcp_render_group_rows(array(array('group_name' => '<Ops>', 'salary' => 'bad')));
if (strpos($groupHtml, '&lt;Ops&gt;') === false || strpos($groupHtml, '$0') === false) {
    fail('group rows must preserve HTML escaping and invalid salary fallback');
}

$_POST = array('search_term' => '<b>Engineering</b>', 'city' => 'San Francisco');
$titleStatement = new FakeStatement(array(array('title' => 'Engineering', 'salary' => 100000)));
$groupStatement = new FakeStatement(array(array('group_name' => 'Product', 'salary' => 90000)));
$endpointDatabase = new FakeDatabase(array($titleStatement, $groupStatement));
$endpointOutput = capture_endpoint(function() use ($endpointDatabase) {
    return $endpointDatabase;
}, $complete, 'TITLE :term :city', 'GROUP :term :city');
if (strpos($endpointOutput, 'Engineering') === false || strpos($endpointOutput, 'Product') === false ||
        $titleStatement->executions !== array(array('term' => 'Engineering', 'city' => 'San Francisco')) ||
        $groupStatement->executions !== array(array('term' => 'Engineering', 'city' => 'San Francisco'))) {
    fail('endpoint must bind normalized inputs and render both result sets');
}

$failureFactories = array(
    function() { throw new RuntimeException('secret connection detail'); },
    function() { return new FakeDatabase(array(), false); },
    function() { return new FakeDatabase(array(new FakeStatement(array(), false))); },
    function() { return new FakeDatabase(array(new FakeStatement(array(), true, false))); },
    function() {
        return new FakeDatabase(array(
            new FakeStatement(array(array('title' => 'must not leak', 'salary' => 1))),
            new FakeStatement(array(), false),
        ));
    },
);
foreach ($failureFactories as $failureFactory) {
    $output = capture_endpoint($failureFactory, $complete, 'SELECT :term, :city', 'GROUP :term, :city');
    if ($output !== 'No matches!' || strpos($output, 'secret') !== false || strpos($output, 'must not leak') !== false) {
        fail('database failures must return only the generic no-match response');
    }
}

echo "find PDO boundary checks passed" . PHP_EOL;
