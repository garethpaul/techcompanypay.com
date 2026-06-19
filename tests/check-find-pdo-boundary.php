<?php
function fail($message) {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

define('TCP_FIND_LIBRARY_ONLY', true);
require __DIR__ . '/../find.php';

class FakeStatement {
    public $executions = array();
    public $fetchModes = array();
    private $rows;
    private $rowIndex = 0;
    private $executeResult;
    private $fetchError;

    public function __construct($rows, $executeResult = true, $fetchError = null) {
        $this->rows = $rows;
        $this->executeResult = $executeResult;
        $this->fetchError = $fetchError;
    }

    public function execute($parameters) {
        $this->executions[] = $parameters;
        return $this->executeResult;
    }

    public function fetch($mode) {
        $this->fetchModes[] = $mode;
        if ($mode !== PDO::FETCH_ASSOC) {
            fail('query rows must request associative fetches');
        }
        if ($this->fetchError !== null) {
            throw $this->fetchError;
        }
        if (!array_key_exists($this->rowIndex, $this->rows)) {
            return false;
        }
        $row = $this->rows[$this->rowIndex];
        $this->rowIndex++;
        return $row;
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
        $statement->fetchModes !== array(PDO::FETCH_ASSOC, PDO::FETCH_ASSOC) ||
        $rows[0]['title'] !== 'R&D <Lead>') {
    fail('query helper must prepare exact SQL and bind normalized term and city values');
}

foreach (array(0, -1, 1.5, '2') as $invalidBudget) {
    $invalidDatabase = new FakeDatabase(array(new FakeStatement(array())));
    try {
        tcp_query_rows($invalidDatabase, $sql, 'term', 'city', $invalidBudget);
        fail('query helper must reject non-positive and non-integer row budgets');
    } catch (InvalidArgumentException $error) {
        if ($invalidDatabase->prepared !== array()) {
            fail('invalid row budgets must fail before statement preparation');
        }
    }
}

$budgetRows = array(
    array('title' => 'One', 'salary' => 1),
    array('title' => 'Two', 'salary' => 2),
);
$exactStatement = new FakeStatement($budgetRows);
$exactRows = tcp_query_rows(new FakeDatabase(array($exactStatement)), $sql, 'term', 'city', 2);
if ($exactRows !== $budgetRows || count($exactStatement->fetchModes) !== 3) {
    fail('query helper must return an exact-budget result set unchanged');
}

$overflowStatement = new FakeStatement(array_merge(
    $budgetRows,
    array(array('title' => 'Three', 'salary' => 3))
));
try {
    tcp_query_rows(new FakeDatabase(array($overflowStatement)), $sql, 'term', 'city', 2);
    fail('query helper must reject the first row beyond the result budget');
} catch (RuntimeException $error) {
    if ($error->getMessage() !== 'database result row limit exceeded' ||
            count($overflowStatement->fetchModes) !== 3) {
        fail('query helper must fail closed on the first excess result row');
    }
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
    function() { return new FakeDatabase(array(new FakeStatement(array(), true, new RuntimeException('secret fetch detail')))); },
    function() { return new FakeDatabase(array(new FakeStatement(array('invalid row')))); },
    function() {
        return new FakeDatabase(array(new FakeStatement(array_fill(0, TCP_MAX_RESULT_ROWS + 1, array(
            'title' => 'must not leak',
            'salary' => 1,
        )))));
    },
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
