<?php

$test_array = array("tag1, tag2, tag3","tag1, tag2, tag4");

require_once("./mysql.php");
//mysql_select_db("$dbname") or die ("Unable to select db: " . mysql_error());
mysql_select_db("ainews_dev") or die ("Unable to select db: " . mysql_error());
$salt_user = "salt";
$salt_pw = "35473d24664035c02d92aba25c94d9c6";

$query = "select tags from urllist where (tags != 'null' and tags not regexp '^ *$')";
$result=mysql_query($query);
if (!$result) die ("DB Access Failed: " . mysql_error());

$return_array = array();
while ($row = mysql_fetch_assoc($result)){
  $current_tags_string = $row['tags'];
  $current_tags_array = preg_split("/\s*,\s*/",$current_tags_string);
  
  if (in_array("",$current_tags_array)) {
    $current_tags_array = array_filter($current_tags_array,create_function("\$x","return \$x != '';"));
  }
  $return_array = array_merge($return_array, $current_tags_array); 
}
$temp =    array_values(array_unique($return_array));
//sort($temp);
usort($temp, strcasecmp);
echo json_encode($temp);

?>
