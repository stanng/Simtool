<?php

require_once("./mysql.php");
mysql_select_db("ainews_dev") or die ("Unable to select db: " . mysql_error());

$druid = $_REQUEST['druid'];
if (isset($_REQUEST['tags'])) echo update_record($druid,"tags",$_REQUEST['tags']);
if (isset($_REQUEST['notes'])) echo update_record($druid,"notes",$_REQUEST['notes']);
if (isset($_REQUEST['title'])) echo update_record($druid,"title",$_REQUEST['title']);
if (isset($_REQUEST['originator'])) echo update_record($druid,"originator",$_REQUEST['originator']);
if (isset($_REQUEST['date'])) echo update_record($druid,"date",$_REQUEST['date']);
if (isset($_REQUEST['document_type'])) echo update_record($druid,"document_type",$_REQUEST['document_type']);
if (isset($_REQUEST['document_subtype'])) echo update_record($druid,"document_subtype",$_REQUEST['document_subtype']);
if (isset($_REQUEST['containing_work'])) echo update_record($druid,"containing_work",$_REQUEST['containing_work']);
if (isset($_REQUEST['corporate_entity'])) echo update_record($druid,"corporate_entity",$_REQUEST['corporate_entity']);
if (isset($_REQUEST['number'])) echo update_record($druid,"number",$_REQUEST['number']);
if (isset($_REQUEST['extent'])) echo update_record($druid,"extent",$_REQUEST['extent']);
if (isset($_REQUEST['language'])) echo update_record($druid,"language",$_REQUEST['language']);
if (isset($_REQUEST['abstract'])) echo update_record($druid,"abstract",$_REQUEST['abstract']);
if (isset($_REQUEST['EAF_hard_drive_file_name'])) echo update_record($druid,"EAF_hard_drive_file_name",$_REQUEST['EAF_hard_drive_file_name']);

function update_record($druid, $mode, $data) {
  $url="texts/" . $druid . ".txt";

  $unix_time = time();

  switch ($mode) {
  case "title":
    $safe_title=mysql_real_escape_string($data); 
    $q="update urllist set title='$safe_title', last_changed = '$unix_time' where url = '$url';";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "tags":
    $safe_tags=mysql_real_escape_string($data); 
    $q="update urllist set tags='$safe_tags', last_changed = '$unix_time' where url = '$url';";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "notes":
    $safe_notes=mysql_real_escape_string($data); 
    $q="update urllist set notes='$safe_notes', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "originator":
    $safe_originator=mysql_real_escape_string($data); 
    $q="update urllist set originator='$safe_originator', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "date":
    $safe_date=mysql_real_escape_string($data); 
    $q="update urllist set date='$safe_date', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "document_type":
    $safe_document_type=mysql_real_escape_string($data); 
    $q="update urllist set document_type='$safe_document_type', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "document_subtype":
    $safe_document_subtype=mysql_real_escape_string($data); 
    $q="update urllist set document_subtype='$safe_document_subtype', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "containing_work":
    $safe_containing_work=mysql_real_escape_string($data); 
    $q="update urllist set containing_work='$safe_containing_work', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "corporate_entity":
    $safe_corporate_entity=mysql_real_escape_string($data); 
    $q="update urllist set corporate_entity='$safe_corporate_entity', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "number":
    $safe_number=mysql_real_escape_string($data); 
    $q="update urllist set number='$safe_number', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "extent":
    $safe_extent=mysql_real_escape_string($data); 
    $q="update urllist set extent='$safe_extent', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
  case "language":
    $safe_language=mysql_real_escape_string($data); 
    $q="update urllist set language='$safe_language', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
  case "abstract":
    $safe_abstract=mysql_real_escape_string($data); 
    $q="update urllist set abstract='$safe_abstract', last_changed = '$unix_time' where url = '$url'";
    $result=mysql_query($q);
    if (!$result) die ("DB write failed 11: " . mysql_error());
    break;
    return "updated $mode: \"$data\"";
  }
}

?>

