<?php
define('HOST', '');
define('USER', '');
define('PW', '');
define('DB', '');

function tcp_send_security_headers() {
  if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: data:; frame-src https:; connect-src 'self'");
  }
}

function tcp_post_value($key) {
  return isset($_POST[$key]) && is_scalar($_POST[$key]) ? strip_tags(substr((string) $_POST[$key], 0, 100)) : '';
}

function tcp_linkedin_title($title) {
  return htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
}

function tcp_salary($value) {
  return is_numeric($value) ? number_format((float) $value) : '0';
}

tcp_send_security_headers();

if (HOST === '' || USER === '' || DB === '' || !function_exists('mysql_connect')) {
  echo 'No matches!';
  return;
}

function tcp_no_matches() {
  echo 'No matches!';
}

$connect = mysql_connect(HOST, USER, PW);
if (!$connect) {
  tcp_no_matches();
  return;
}

if (!mysql_select_db(DB, $connect)) {
  tcp_no_matches();
  mysql_close($connect);
  return;
}

$term = tcp_post_value('search_term');
$city = tcp_post_value('city');
$city = mysql_real_escape_string($city);
$term = mysql_real_escape_string($term);

$titlesql = "";

$titleresult = $titlesql === '' ? false : mysql_query($titlesql);
$titlestring = "<div id='titleData' class='tab'>\n<table cellspacing='0'><thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>";

if ($titleresult && mysql_num_rows($titleresult) > 0){
  while($row = mysql_fetch_object($titleresult)){
    $title = isset($row->title) ? $row->title : '';
    $salary = isset($row->salary) ? $row->salary : 0;
	$titlestring .= '<tr>';
	$titlestring .= "<td><a href='https://www.linkedin.com/search/fpsearch?title=" . rawurlencode($title) . "'>" . tcp_linkedin_title($title) . "</a></td>";
    $titlestring .= "<td>$".tcp_salary($salary)."</td>";

    $titlestring .= "</tr>\n";
  }
  $titlestring .= '</tbody></table></div>';

}else{
  $titlestring = "No matches!";
} 
echo $titlestring;

$groupsql = "";

$groupresult = $groupsql === '' ? false : mysql_query($groupsql);
$groupstring = "<div id='titleData' class='tab'>\n<table cellspacing='0'>\n<thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>";

if ($groupresult && mysql_num_rows($groupresult) > 0){
  while($row = mysql_fetch_object($groupresult)){
    $group = isset($row->group_name) ? $row->group_name : '';
    $salary = isset($row->salary) ? $row->salary : 0;
	$groupstring .= '<tr>';
    $groupstring .= "<td>".htmlspecialchars($group, ENT_QUOTES, 'UTF-8')."</td>";
    $groupstring .= "<td>$".tcp_salary($salary)."</td>";
    $groupstring .= "</tr>\n";
  }
  $groupstring .= '</tbody></table></div>';

}else{
  $groupstring = "No matches!";
} 
echo $groupstring;

mysql_close($connect);

?>
