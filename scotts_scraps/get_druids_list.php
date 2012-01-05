<?php
  
/*
require_once("mysql.php");
mysql_select_db("ainews") or die ("Unable to select db: " . mysql_error());


$druid = "yh813hz5326";
$query = "select * from urllist where url = 'texts/${druid}.txt';";    
$result = mysql_query($query);
while($row = mysql_fetch_assoc($result)){
print_r($row);
}

die();


if (!$result) die (mysql_error("Asfd"));
$druid_array = array();
while($row = mysql_fetch_assoc($result)) {
  $druid_array []= str_replace(".txt","",str_replace("texts/","",$row['url']));
}
//print_r($druid_array);

//file_put_contents("mydruids",implode("\n",$druid_array));


  //ok, now add a column and write it back as zotero_key

  */
  //die();
require_once("mysql.php");
mysql_select_db("ainews") or die ("Unable to select db: " . mysql_error());

/*
$query = "ALTER TABLE urllist ADD COLUMN zotero_key VARCHAR(16);";
$result = mysql_query($query) or die(mysql_error());
die();
*/

$i = 0;
//$lines = explode("\n",file_get_contents("druids_and_keys"));
$lines = explode("\n",file_get_contents("extras"));
foreach ($lines as $line) {
  list($druid, $key) = explode(",",$line);
  $query = "update urllist set zotero_key = '$key' where url = 'texts/${druid}.txt';";    
  $i++;
  echo "$i\n";
  $result = mysql_query($query) or die("druid=$druid, key=$key\n".mysql_error());
  if (mysql_affected_rows() != 1 ) echo "druid=$druid, key=$key\n"."rows affected=".mysql_affected_rows()."\n";
}
