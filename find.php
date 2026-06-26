<?php
require_once __DIR__ . '/input.php';

define('TCP_TITLE_SQL', '');
define('TCP_GROUP_SQL', '');
define('TCP_MAX_RESULT_ROWS', 500);
define('TCP_MAX_RESULT_BYTES', 262144);

function tcp_send_security_headers() {
  if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'");
  }
}

function tcp_post_value($key) {
  return isset($_POST[$key]) && is_scalar($_POST[$key]) ? strip_tags(tcp_bounded_utf8((string) $_POST[$key])) : '';
}

function tcp_request_method($server = null) {
  $values = $server === null ? $_SERVER : $server;
  if (!is_array($values) || !isset($values['REQUEST_METHOD']) || !is_string($values['REQUEST_METHOD'])) {
    return '';
  }
  return $values['REQUEST_METHOD'];
}

function tcp_linkedin_title($title) {
  return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

function tcp_salary($value) {
  if (!is_numeric($value)) {
    return '0';
  }

  $numericSalary = (float) $value;
  return is_finite($numericSalary) ? number_format($numericSalary) : '0';
}

function tcp_database_config($environment = null) {
  $values = array();
  foreach (array('TCP_DB_DSN', 'TCP_DB_USER', 'TCP_DB_PASSWORD') as $name) {
    if ($environment === null) {
      $value = getenv($name);
    } else {
      $value = array_key_exists($name, $environment) ? $environment[$name] : false;
    }
    if ($value === false || !is_string($value)) {
      return null;
    }
    $values[$name] = $value;
  }

  if (trim($values['TCP_DB_DSN']) === '' || trim($values['TCP_DB_USER']) === '') {
    return null;
  }

  return array(
    'dsn' => $values['TCP_DB_DSN'],
    'user' => $values['TCP_DB_USER'],
    'password' => $values['TCP_DB_PASSWORD'],
  );
}

function tcp_pdo_options() {
  return array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  );
}

function tcp_create_database($config, $factory = null) {
  $options = tcp_pdo_options();
  if ($factory !== null) {
    return call_user_func($factory, $config['dsn'], $config['user'], $config['password'], $options);
  }

  return new PDO($config['dsn'], $config['user'], $config['password'], $options);
}

function tcp_query_rows($database, $sql, $term, $city, $maxRows = TCP_MAX_RESULT_ROWS) {
  if (!is_int($maxRows) || $maxRows < 1) {
    throw new InvalidArgumentException('database result row limit must be a positive integer');
  }
  if (trim($sql) === '') {
    return array();
  }

  $statement = $database->prepare($sql);
  if (!$statement) {
    throw new RuntimeException('database statement preparation failed');
  }
  if (!$statement->execute(array('term' => $term, 'city' => $city))) {
    throw new RuntimeException('database statement execution failed');
  }

  $rows = array();
  while (true) {
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
      break;
    }
    if (!is_array($row)) {
      throw new RuntimeException('database row fetch failed');
    }
    if (count($rows) === $maxRows) {
      throw new RuntimeException('database result row limit exceeded');
    }
    $rows[] = $row;
  }
  return $rows;
}

function tcp_validate_result_byte_limit($maxBytes) {
  if (!is_int($maxBytes) || $maxBytes < 1) {
    throw new InvalidArgumentException('encoded result byte limit must be a positive integer');
  }
}

function tcp_append_bounded_html(&$html, $chunk, $maxBytes) {
  tcp_validate_result_byte_limit($maxBytes);
  $chunkBytes = strlen($chunk);
  if ($chunkBytes > $maxBytes - strlen($html)) {
    throw new RuntimeException('encoded result byte limit exceeded');
  }
  $html .= $chunk;
}

function tcp_append_title_rows(&$html, $rows, $maxBytes) {
  if (count($rows) === 0) {
    tcp_append_bounded_html($html, 'No matches!', $maxBytes);
    return;
  }

  tcp_append_bounded_html($html, "<div id='titleData' class='tab'>\n<table cellspacing='0'><thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>", $maxBytes);
  foreach ($rows as $row) {
    $title = isset($row['title']) ? $row['title'] : '';
    $salary = isset($row['salary']) ? $row['salary'] : 0;
    $rowHtml = '<tr>';
    $rowHtml .= "<td><a href='https://www.linkedin.com/search/fpsearch?title=" . rawurlencode($title) . "'>" . tcp_linkedin_title($title) . '</a></td>';
    $rowHtml .= '<td>$' . tcp_salary($salary) . '</td>';
    $rowHtml .= "</tr>\n";
    tcp_append_bounded_html($html, $rowHtml, $maxBytes);
  }
  tcp_append_bounded_html($html, '</tbody></table></div>', $maxBytes);
}

function tcp_render_title_rows($rows, $maxBytes = TCP_MAX_RESULT_BYTES) {
  $html = '';
  tcp_append_title_rows($html, $rows, $maxBytes);
  return $html;
}

function tcp_append_group_rows(&$html, $rows, $maxBytes) {
  if (count($rows) === 0) {
    tcp_append_bounded_html($html, 'No matches!', $maxBytes);
    return;
  }

  tcp_append_bounded_html($html, "<div id='titleData' class='tab'>\n<table cellspacing='0'>\n<thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>", $maxBytes);
  foreach ($rows as $row) {
    $group = isset($row['group_name']) ? $row['group_name'] : '';
    $salary = isset($row['salary']) ? $row['salary'] : 0;
    $rowHtml = '<tr>';
    $rowHtml .= '<td>' . htmlspecialchars($group, ENT_QUOTES, 'UTF-8') . '</td>';
    $rowHtml .= '<td>$' . tcp_salary($salary) . '</td>';
    $rowHtml .= "</tr>\n";
    tcp_append_bounded_html($html, $rowHtml, $maxBytes);
  }
  tcp_append_bounded_html($html, '</tbody></table></div>', $maxBytes);
}

function tcp_render_group_rows($rows, $maxBytes = TCP_MAX_RESULT_BYTES) {
  $html = '';
  tcp_append_group_rows($html, $rows, $maxBytes);
  return $html;
}

function tcp_render_result_rows($titleRows, $groupRows, $maxBytes = TCP_MAX_RESULT_BYTES) {
  tcp_validate_result_byte_limit($maxBytes);
  $html = '';
  tcp_append_title_rows($html, $titleRows, $maxBytes);
  tcp_append_group_rows($html, $groupRows, $maxBytes);
  return $html;
}

function tcp_run_find_endpoint($factory = null, $environment = null, $titleSql = TCP_TITLE_SQL, $groupSql = TCP_GROUP_SQL, $server = null) {
  tcp_send_security_headers();
  if (tcp_request_method($server) !== 'POST') {
    if (!headers_sent()) {
      http_response_code(405);
      header('Allow: POST');
    }
    echo 'No matches!';
    return;
  }
  $config = tcp_database_config($environment);
  if ($config === null) {
    echo 'No matches!';
    return;
  }

  try {
    $database = tcp_create_database($config, $factory);
    $term = tcp_post_value('search_term');
    $city = tcp_post_value('city');
    $titleRows = tcp_query_rows($database, $titleSql, $term, $city);
    $groupRows = tcp_query_rows($database, $groupSql, $term, $city);
    $output = tcp_render_result_rows($titleRows, $groupRows);
    echo $output;
  } catch (Throwable $error) {
    echo 'No matches!';
  }
}

if (!defined('TCP_FIND_LIBRARY_ONLY')) {
  tcp_run_find_endpoint();
}
?>
