<?php
define(HOST, "");
define(USER, "");
define(PW, "");
define(DB, "");

$connect = mysql_connect("$HOST", "$USER", "$PW")
or die('Could not connect to mysql server.' );

mysql_select_db(DB, $connect)
or die('Could not select database.');

$term = strip_tags(substr($_POST['search_term'],0, 100));
$city = strip_tags(substr($_POST['city'],0, 100));
$city = mysql_escape_string($city);
$term = mysql_escape_string($term);

$titlesql = ""

$titleresult = mysql_query($titlesql);
$titlestring = "<div id='titleData' class='tab'>\n<table cellspacing='0'><thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>";

if (mysql_num_rows($titleresult) > 0){
  while($row = mysql_fetch_object($titleresult)){
  $titlestring .= '<tr>';
	$titlestring .= "<td><a href='http://www.linkedin.com/search/fpsearch?title=" .$row->LCA_CASE_JOB_TITLE. "'>" .$row->LCA_CASE_JOB_TITLE. "</a></td>";
    $titlestring .= "<td>$".number_format($row->LCA_CASE_WAGE_RATE_FROM)."</td>";

    $titlestring .= "</tr>\n";
  }
  $titlestring .= '</tbody></table></div>';

}else{
  $titlestring = "No matches!";
} 
echo $titlestring;

$groupsql = ""

$groupresult = mysql_query($groupsql);
$groupstring = "<div id='titleData' class='tab'>\n<table cellspacing='0'>\n<thead><tr><td>Function Group</td><td>Average Salary</td></tr></thead><tbody>";

if (mysql_num_rows($groupresult) > 0){
  while($row = mysql_fetch_object($groupresult)){
	$groupstring .= '<tr>';
    $groupstring .= "<td>".$row->ROW."</td>";
    $groupstring .= "<td>$".number_format($row->ROW)."</td>";
    $groupstring .= "</tr>\n";
  }
  $groupstring .= '</tbody></table></div>';

}else{
  $groupstring = "No matches!";
} 
echo $groupstring;

mysql_close($connect);

?>
