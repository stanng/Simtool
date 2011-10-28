<?php

require_once("mysql.php");
mysql_select_db("ainews") or die ("Unable to select db: " . mysql_error());

foreach (array("first_accession_20111024.json", "second_accession_20111024.json") as $jsonfile) {
  $fp = fopen($jsonfile,'r');
  $count = 0;
  while (($line_of_json = fgets($fp)) !== false) {
    //echo $line_of_json;
    
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
    
    // Do somthing if we found notes
    $tags = '';
    if ($arr->tags) {
      $tags = implode(", ",$arr->tags);
      $safe_tags=mysql_real_escape_string($tags);
      $q = "update urllist set tags = '$safe_tags' where url = '$url'";
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
  
    $count++;
  }
  fclose ($fp);
  print "\nDone with $jsonfile\n";
  print "Processed $count json records\n";
}

?>
