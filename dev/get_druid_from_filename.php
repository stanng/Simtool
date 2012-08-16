<?php


$filename_to_druid = array();
// Feigenbaum_SC340_1986-052_B01_F01_i0002.pdf
$csv = file_get_contents('druid_data/druid-to-filename-1st-acc.csv');
$lines = preg_split('/[\n\r]+/',$csv);
$filename_to_druid[1] = array();
foreach($lines as $line) {
  $fields = explode(',',$line);
  $filename_to_druid[1]['Feigenbaum_'.$fields[1]] = $fields[0];
}

// Feigenbaum_00013532.pdf
$csv = file_get_contents('druid_data/druid-to-filename-2nd-acc.csv');
$lines = preg_split('/[\n\r]+/',$csv);
$filename_to_druid[2] = array();
foreach($lines as $line) {
  $fields = explode(',',$line);
  $filename_to_druid[2]['Feigenbaum_'.substr('00000000'.$fields[1],-8)] = $fields[0];
}

// Feigenbaum_Airforce_0343.pdf
$csv = file_get_contents('druid_data/druid-to-filename-3rd-acc.csv');
$lines = preg_split('/[\n\r]+/',$csv);
$filename_to_druid[3] = array();
foreach($lines as $line) {
  $fields = explode(',',$line);
  $filename_to_druid[3]['Feigenbaum_Airforce_'.substr('0000'.$fields[1],-4)] = $fields[0];
}


$filename_text = $_REQUEST['filename-text'];
$filename_text = str_replace('.pdf','',$filename_text);

$pat = '/(B\d{1,2}[A_G]?_F\d{1,2}_[iI]\d{4})/';
preg_match($pat,$filename_text,$match);
if ($match) {
  $filename = 'Feigenbaum_SC340_1986-052_'.$match[1];
  echo json_encode(array('druid'=>$filename_to_druid[1][$filename]));
  die();
} 
$pat = '/(\d{8})/';
preg_match($pat,$filename_text,$match);
if ($match) {
  $filename = 'Feigenbaum_'.$match[1];
  echo json_encode(array('druid'=>$filename_to_druid[2][$filename]));
  die();
} 
$pat = '/(\d{4})/';
preg_match($pat,$filename_text,$match);
if ($match) {
  $filename = 'Feigenbaum_Airforce_'.$match[1];
  echo json_encode(array('druid'=>$filename_to_druid[3][$filename]));
  die();
} 


