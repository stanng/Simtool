<?php
  ////////
///////  //$url = "https://saltworks.stanford.edu/catalog/druid:" . $_REQUEST['url'];

$url = "https://purl.stanford.edu/" . $_REQUEST['url'];
//$url = "https://purl.stanford.edu/hj978sk5432";

$str = file_get_contents($url);
$str = preg_replace("/[\\s\\n]+/"," ",$str);

$pat = "/<dt>(.*?)<\/dt>(?:<dd>(.*?)<\/dd>)+(<\/dt>|<dt>|<\/dl>)/";
$pat = "/<dt>(.*?)<\/dt>((?:<dd>.*?<\/dd>)+)/";
preg_match_all($pat,$str,$matches);

$pat = "/<section.*?<h1>(.*?)<\/h1>/";
preg_match($pat,$str,$titlematch);
$title = htmlspecialchars_decode($titlematch[1]);

$data = array();
$keys = $matches[1];
foreach ($keys as $ind => $key) {
  $pat = "/<dd>(.*?)<\/dd>/";
  $str = $matches[2][$ind];
  preg_match_all($pat,$str,$ms);
  $data[$key] = implode('; ',$ms[1]);
}


if (isset($data['Location'])) $subseries = htmlspecialchars_decode($data['Location']);
else $subseries = ""; 

if (isset($data['Note'])) $notes = htmlspecialchars_decode($data['Note']);
else $notes = ""; 

if (isset($data['Donor tags'])) $tags = htmlspecialchars_decode($data['Donor tags']);
else $tags = ""; 

$authorArr = array();
if (isset($data['Author/Creator'])) $authorArr []= htmlspecialchars_decode($data['Author/Creator']);
if (isset($data['Contributor'])) $authorArr []= htmlspecialchars_decode($data['Contributor']);
$authors = implode("; ",$authorArr);

$j = array();
$j['title'] = $title;
$j['subseries'] = $subseries;
$j['notes'] = $notes;
$j['authors'] = $authors;
$j['tags'] = $tags;
$j['druid'] = $_REQUEST['url'];

echo json_encode($j);




