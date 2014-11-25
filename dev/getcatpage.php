<?php
$url = "https://saltworks.stanford.edu/catalog/druid:" . $_REQUEST['url'];
$str = file_get_contents($url);
$str = preg_replace("/[\\s\\n]+/"," ",$str);


$pat = "/<dd class=.blacklight-subseries_display.>(.*?)<\//";
preg_match($pat,$str,$match);
if ($match)
$subseries = $match[1];
else $subseries = "";

$pat = "/<dd class=.blacklight-notes_display.>(.*?)<\//";
preg_match($pat,$str,$match);
if ($match)
$notes = $match[1];
else $notes = "";

$pat = "/<dd class=.blacklight-originator_s.>(.*?)<\//";
preg_match($pat,$str,$match);
if ($match) 
$authors = $match[1];
else $authors = "";

$pat = "/<title>(.*?)<\/title>/";
preg_match($pat,$str,$match);
$title = str_replace(" - SALT (Self Archiving Legacy Toolkit)","",$match[1]);

$j = array();
$j['title'] = $title;
$j['subseries'] = $subseries;
$j['notes'] = $notes;
$j['authors'] = $authors;


echo json_encode($j);




