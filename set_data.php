<?php
require_once("./mysql.php");
mysql_select_db("ainews") or die ("Unable to select db: " . mysql_error());

$druid = $_REQUEST['druid'];
if (isset($_REQUEST['tags'])) echo update_record($druid,"tags",$_REQUEST['tags']);
if (isset($_REQUEST['notes'])) echo update_record($druid,"notes",$_REQUEST['notes']);
if (isset($_REQUEST['title'])) echo update_record($druid,"title",$_REQUEST['title']);

function update_record($druid, $mode, $data) {
  $url="texts/" . $druid . ".txt";

  switch ($mode) {
  case "title":
    $safe_title=mysql_real_escape_string($data); 
    $q="update urllist set title='$safe_title' where url = '$url';";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "tags":
    $safe_tags=mysql_real_escape_string($data); 
    $q="update urllist set tags='$safe_tags' where url = '$url';";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "notes":
    $safe_notes=mysql_real_escape_string($data); 
    $q="update urllist set notes='$safe_notes' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    mysql_query($q);
  }
  return "updated $mode: \"$data\"";
}

?>
