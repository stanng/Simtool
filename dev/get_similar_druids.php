<?php
require_once("./mysql.php");
mysql_select_db("scottva3_eaf_similarity_data") or die ("Unable to select db: " . mysql_error());
//$salt_user = "salt";
//$salt_pw = "35473d24664035c02d92aba25c94d9c6";

$druid = $_REQUEST['druid'] ?: '';
$howmany = $_REQUEST['howmany'] ?: '10';
$mincos = $_REQUEST['mincos'] ?: '0.3';

//extract druid if it is part of a url, eg. //bb030qh9859
$pat = '/[a-z]{2}\d{3}[a-z]{2}\d{4}/';
preg_match($pat,$druid,$match);
if ($match) $druid = $match[0];

// Get Candidate druids
$q_sim_druids="
select 
  candidate_druid ,
  similarity
from topic_similarity 
where topic_druid='$druid' 
and similarity >= $mincos
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
  //  $pdffile_path="http://${salt_user}:${salt_pw}@salt-dev.stanford.edu/assets/${candidate_druid}/${candidate_druid}.pdf";
  $pdffile_path="https://saltworks.stanford.edu/assets/{$candidate_druid}.pdf";
  $thumbnail_path="https://saltworks.stanford.edu/assets/{$candidate_druid}.jpg";

  $q_details="select textlen, title, description, tags, notes, originator, date, document_type, document_subtype, containing_work, corporate_entity, number, extent, language, abstract, EAF_hard_drive_file_name, zotero_key from urllist where url='$textfile_path'";
  $result_details   = mysql_query($q_details);
  $row_details      = mysql_fetch_assoc($result_details);

  $doclen           = $row_details['textlen'];
  $title            = $row_details['title'];
  $summary          = $row_details['description'];
  $tags             = $row_details['tags'];
  $notes            = $row_details['notes'];
  $originator       = $row_details['originator'];
  $date             = $row_details['date'];
  $document_type    = $row_details['document_type'];
  $document_subtype = $row_details['document_subtype'];
  $containing_work  = $row_details['containing_work'];
  $corporate_entity = $row_details['corporate_entity'];
  $number           = $row_details['number'];
  $extent           = $row_details['extent'];
  $language         = $row_details['language'];
  $abstract         = $row_details['abstract'];
  $EAF_hard_drive_file_name = $row_details['EAF_hard_drive_file_name'];
  $zotero_key = $row_details['zotero_key'];

  $json_row = array(
    'druid'            => "$candidate_druid",
    'cos_sim'          => "$similarity",
    'pdf'              => "$pdffile_path",
    'thumbnail'        => "$thumbnail_path",
    //'title'            => "$title",
    //'summary'          => "$summary",
    //'doclen'           => "$doclen",
    //'tags'             => "$tags",
    //'notes'            => "$notes",
    //'originator'       => "$originator",
    //'date'             => "$date",
    //'document_type'    => "$document_type",
    //'document_subtype' => "$document_subtype",
    //'containing_work'  => "$containing_work",
    //'corporate_entity' => "$corporate_entity",
    //'number'           => "$number",
    //'extent'           => "$extent",
    //'language'         => "$language",
    //'abstract'         => "$abstract",
    //'EAF_hard_drive_file_name' => "$EAF_hard_drive_file_name",
    //'zotero_key' => "$zotero_key"
);

  $return_array []= $json_row;
}
$out = json_encode($return_array);
$out = str_replace('\/','/',$out);
echo $out;
?>
