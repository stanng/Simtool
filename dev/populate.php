<?php
require_once("mysql.php");
//mysql_select_db("$dbname") or die ("Unable to select db: " . mysql_error());
mysql_select_db("ainews_dev") or die ("Unable to select db: " . mysql_error());
foreach (array("all_1st_accession_20111209.json", "all_2nd_accession_20111209.json") as $jsonfile) {
  print "$jsonfile ...\n";
  $fp = fopen($jsonfile,'r');
  $count = 0;
  while (($line_of_json = fgets($fp)) !== false) {
    print $line_of_json;
    
    $arr = json_decode($line_of_json);
    //var_dump($arr);
    
    $druid_str = $arr->druid;
    if ( ! (preg_match("/[a-z]{2}\d{3}[a-z]{2}\d{4}/", $druid_str, $matches) ) ) {
      var_dump($matches);
      print "No druid found in [$druid_str]. Skipping...\n";
      continue;
    } else {
      $druid = $matches[0];
    }

    $url = "texts/${druid}.txt";

    // Store notes if any
    $notes = '';
    if ($arr->notes and $arr->notes != '' ) {
      $notes = "NOTE:\n" . implode("\nNOTE:\n",$arr->notes);
      $safe_notes=mysql_real_escape_string("$notes");
      $q = "update urllist set notes = '$safe_notes' where url = '$url'";
      //print "$q\n";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }

    // Do somthing if we found title
    if ($arr->title) {
      $title=$arr->title;
      $safe_title=mysql_real_escape_string($title);
      $q = "update urllist set title = '$safe_title' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }

    // Do somthing if we found tags
    $tags = '';
    if ($arr->tags) {
      $tags = implode(", ",$arr->tags);
      $safe_tags=mysql_real_escape_string($tags);
      $q = "update urllist set tags = '$safe_tags' where url = '$url'";
      //print "$q\n";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }

    // Do somthing if we found originator
    $originator = '';
    if ($arr->originator) {
      $originator = implode("; ",$arr->originator);
      $safe_originator=mysql_real_escape_string($originator);
      $q = "update urllist set originator = '$safe_originator' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }

    // Do somthing if we found date
    if ($arr->date) {
      $date=$arr->date;
      $safe_date=mysql_real_escape_string($date);
      $q = "update urllist set date = '$safe_date' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }

    // Do somthing if we found document_type
    if ($arr->document_type) {
      $document_type=$arr->document_type;
      $safe_document_type=mysql_real_escape_string($document_type);
      $q = "update urllist set document_type = '$safe_document_type' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found document_subtype
    if ($arr->document_subtype) {
      $document_subtype=$arr->document_subtype;
      $safe_document_subtype=mysql_real_escape_string($document_subtype);
      $q = "update urllist set document_subtype = '$safe_document_subtype' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found containing_work
    if ($arr->containing_work) {
      $containing_work=$arr->containing_work;
      $safe_containing_work=mysql_real_escape_string($containing_work);
      $q = "update urllist set containing_work = '$safe_containing_work' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found corporate_entity
    if ($arr->corporate_entity) {
      $corporate_entity=$arr->corporate_entity;
      $safe_corporate_entity=mysql_real_escape_string($corporate_entity);
      $q = "update urllist set corporate_entity = '$safe_corporate_entity' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found number
    if ($arr->number) {
      $number=$arr->number;
      $safe_number=mysql_real_escape_string($number);
      $q = "update urllist set number = '$safe_number' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found extent
    if ($arr->extent) {
      $extent=$arr->extent;
      $safe_extent=mysql_real_escape_string($extent);
      $q = "update urllist set extent = '$safe_extent' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found language
    if ($arr->language) {
      $language=$arr->language;
      $safe_language=mysql_real_escape_string($language);
      $q = "update urllist set language = '$safe_language' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found abstract
    if ($arr->abstract) {
      $abstract=$arr->abstract;
      $safe_abstract=mysql_real_escape_string($abstract);
      $q = "update urllist set abstract = '$safe_abstract' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    // Do somthing if we found EAF_hard_drive_file_name
    if ($arr->EAF_hard_drive_file_name) {
      $EAF_hard_drive_file_name=$arr->EAF_hard_drive_file_name;
      $safe_EAF_hard_drive_file_name=mysql_real_escape_string($EAF_hard_drive_file_name);
      $q = "update urllist set EAF_hard_drive_file_name = '$safe_EAF_hard_drive_file_name' where url = '$url'";
      $result = mysql_query($q);
      if (!$result) die ("DB write failed 11: " . mysql_error());
    }
  
    $count++;
  }
  fclose ($fp);
  print "\nDone with $jsonfile\n";
  print "Processed $count json records\n";
}
