<?php
require_once("./mysql.php");
mysql_select_db("ainews") or die ("Unable to select db: " . mysql_error());
$salt_user = "salt";
$salt_pw = "35473d24664035c02d92aba25c94d9c6";
$druid = $_REQUEST['druid'];
$howmany = $_REQUEST['howmany'];

// Get Candidate druids
$q_sim_druids="
select 
  candidate_druid ,
  similarity
from topic_similarity 
where topic_druid='$druid' 
order by similarity desc 
limit $howmany";

$result=mysql_query($q_sim_druids);
if (!$result) die ("DB Access Failed: " . mysql_error());

// return_array is built up with each pass of this loop.
$return_array = array();
while ($row = mysql_fetch_assoc($result)){
  $candidate_druid = $row['candidate_druid'];
  $similarity      = $row['similarity'];
  // Derived results. textfile_path is how doc is stored (as "url")
  $textfile_path="texts/" . $candidate_druid . ".txt";
  //$pdffile_path="file://localhost/Users/stanleyng/feigenbaum/pdfs/"    . $candidate_druid . ".pdf";
  $pdffile_path="http://${salt_user}:${salt_pw}@salt-dev.stanford.edu/assets/${candidate_druid}/${candidate_druid}.pdf";

  $q_details="select textlen, description, tags, notes from urllist where url='$textfile_path'";
  $result_details = mysql_query($q_details);
  $row_details    = mysql_fetch_assoc($result_details);
  $doclen  = $row_details['textlen'];
  $summary = $row_details['description'];
  $tags    = $row_details['tags'];
  $notes   = $row_details['notes'];

  $json_row = array(
       'pdf'     => "$pdffile_path",
       'summary' => "$summary",
       'druid'   => "$candidate_druid",
       'doclen'  => "$doclen",
       'cos_sim' => "$similarity",
       'tags'    => "$tags",
       'notes'   => "$notes");

  $return_array []= $json_row;
}

echo json_encode($return_array);
?>
