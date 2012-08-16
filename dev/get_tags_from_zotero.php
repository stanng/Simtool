<?php
 
$return_array = array("tag1", "tag2", "tag3", "tag1", "tag2", "tag4");
$temp =    array_values(array_unique($return_array));

usort($temp, strcasecmp);
echo json_encode($temp);

?>
