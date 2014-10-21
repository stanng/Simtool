<?php
  // log php errors
  @ini_set('log_errors','On'); // enable or disable php error logging (use 'On' or 'Off')
  @ini_set('display_errors','Off'); // enable or disable public display of errors (use 'On' or 'Off')
  @ini_set('error_log','error_log.log'); // path to server-writable log file

  //usage: get_eaf_metadata.php?mode=read&druid=ASDF
  // returns json object including fields: druid, itemKey, accession, json, etag 
  //usage: get_eaf_metadata.php?mode=write&druid=ASDF&etag=1234&json={}

//set up druid-to-itemKey lookup tables (should be in a database, probs)
$druid_to_zkey = array();
$druid_to_zkey[1] = convert_druid_to_key_csv_to_array("druid_data/druid-to-zkey-1st-acc.csv");
$druid_to_zkey[2] = convert_druid_to_key_csv_to_array("druid_data/druid-to-zkey-2nd-acc.csv");
$druid_to_zkey[3] = convert_druid_to_key_csv_to_array("druid_data/druid-to-zkey-3rd-acc.csv");

//get input params
$mode = $_REQUEST['mode'];
$druid = $_REQUEST['druid'];
$etag = $_REQUEST['etag'];
$json = $_REQUEST['json'];
$accession = $_REQUEST['accession'];
$itemKey = $_REQUEST['itemKey'];

$note_itemKeys = $_REQUEST['note-itemKeys'];
$note_etags = $_REQUEST['note-etags'];
$note_json = $_REQUEST['note-json'];

switch ($mode) {
case 'get-all-tags':
  $all_tags = array();
  for ($accession = 1; $accession <= 3; $accession++) {
    $tags = get_all_tags_from_accession($accession);
    $all_tags = array_merge($all_tags,$tags);
  }
    $all_tags = array_unique($all_tags);
    usort($all_tags, strcasecmp);

  echo json_encode($all_tags);  
  break;

case 'read':
  $temp = get_itemKey_from_druid($druid);
  $itemKey = $temp['itemKey'];
  $accession = $temp['accession'];
  if (!$itemKey) die("{error: \"No itemKey for druid:$druid\"}");
  $z = get_zotero_by_itemKey($itemKey,$accession);
  $m = zotero_to_lowood($z['json']);
  if (isset($m['error'])) {
    echo json_encode(array('json'=>$m,
			 'druid'=>$druid,
			 'itemKey'=>$itemKey,
			 'accession'=>$accession,
			 'etag'=>'0'));
  } else {
    echo json_encode(array('json'=>$m,
			 'druid'=>$druid,
			 'itemKey'=>$itemKey,
			 'accession'=>$accession,
			 'etag'=>$z['etag']));
  }
  break;

case 'write':
  //echo "this is a test";die();
  if ($etag == '0') {
    $m = array('error'=>"ERROR no etag",'document_type' => 'manuscript');
    echo json_encode(array('json'=>$m,
			   'druid'=>$druid,
			   'itemKey'=>$itemKey,
			   'accession'=>$accession,
			   'etag'=>"0"));
  } else {
    $m = $json;
    $z = lowood_to_zotero($m);  //objct
    $new_z = write_back_to_zotero($itemKey,$accession,$z,$etag);
    $new_m = zotero_to_lowood($new_z['json']);
    if ($new_m === false) {
      $new_m = array('error'=>"BAD RETURN form Zotero.");
    }
    echo json_encode(array('json'=>$new_m,
			   'druid'=>$druid,
			   'itemKey'=>$itemKey,
			   'accession'=>$accession,
			   'etag'=>$new_z['etag']));
  }
  break;

case 'readnotes':
  $temp = get_itemKey_from_druid($druid);
  $itemKey = $temp['itemKey'];
  $accession = $temp['accession'];
  if (!$itemKey) die("{error: \"No itemKey for druid:$druid\"}");
  $notes = get_notes_by_parent_itemKey($itemKey,$accession);
  echo json_encode(array('druid'=>$druid,'notes'=>$notes));
  break;

case 'writenotes':
  if (!is_array($note_etags)) $note_etags = json_decode($note_etags);
  if (!is_array($note_itemKeys)) $note_itemKeys = json_decode($note_itemKeys);
  if (!is_array($note_json)) $note_json = json_decode($note_json);

  //add new notes, delete old notes, and read new content and return
  $notes = add_notes_to_itemKey($itemKey,$accession,$note_json);
  echo json_encode(array('druid'=>$druid, 'notes'=>$notes));
  //$x = array();
  //delete old:
  $delete_infos = '';
  for ($i = 0; $i < count($note_etags); $i++) {
    $delete_info = delete_zotero_item($note_itemKeys[$i],$accession,$note_etags[$i]);
    //$delete_infos .= "\n\n$delete_info";
  }
  //echo json_encode(array('druid'=>$druid,'notes' => "[NOTE]".$delete_infos));

  die();
  
  $notes = get_zotero_notes_by_itemKey($itemKey,$accession);
  echo json_encode($notes);
  break;
  
  
default:
  break;
}

die();


function delete_zotero_item($itemKey,$accession,$etag) {
  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 
  //  return $accession;

  $ch = curl_init();
  $url = "https://api.zotero.org/groups/$lib/items/$itemKey?key=$privateKey";
  $headers = array(
		   "If-Match: \"$etag\"");

  //  "Expect: ");  
  //this is to get rid of the bogus "Expect: 100-continue" (actually this is only needed on PUT?)
  
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $url);
  //curl_setopt($ch, CURLOPT_DELETE, true); //this doesn't work for some reason...
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  //for debug:
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function add_notes_to_itemKey($itemKey,$accession,$items) {

  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 

  //return note text to html (convent \n's to <p>'s)
  $clean_items = array();
  foreach ($items as $item) {
    $item['note'] = trim($item['note']);
    $item['note'] = '<p>'.str_replace("\n",'</p><p>',$item['note']).'</p>'; //replace p element with returns
    $clean_items []= $item;
  }  

  $ch = curl_init();
  $url = "https://api.zotero.org/groups/$lib/items/$itemKey/children?key=$privateKey&content=json";
  $data_string = json_encode(array('items'=>$clean_items));

  //  echo "data_string:".str_replace("\\\\n","\\n",str_replace("\\\"",'"',json_encode($data_string)));
  
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, 
	      array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($data_string))
	      );
  
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  
  //for debug:
  //curl_setopt($ch, CURLOPT_HEADER, true);
  //curl_setopt($ch, CURLOPT_VERBOSE, true);
  $output = curl_exec($ch);
  curl_close($ch);
  return extract_notes($output);
}


function get_notes_by_parent_itemKey($itemKey, $accession) {
  //group library group numbers:  1st accession, 2nd accession, 3rd accession
  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 
  
  $read_url = "https://api.zotero.org/groups/$lib/items/$itemKey/children?key=$privateKey&content=json";
  $zdata = file_get_contents($read_url);
  $zdata = str_replace('zapi:key','zapikey',$zdata);
  $xml = simplexml_load_string($zdata);
  $entries = $xml->entry;
  $notes = array();
  foreach ($entries as $entry) {
    $note_itemKey = (string)$entry->zapikey;
    $content = $entry->content[0];
    $json = json_decode($content);
    $json->note = str_replace('<p>',"\n",$json->note); //replace p element with returns
    $json->note = str_replace('<\p>',"",$json->note); //replace p element with returns
    $json->note = strip_tags($json->note); //clear out other html tags
    $json->note = preg_replace('/[^A-Za-z\d\!\@\#\$\%\^\&\*\(\)\{\}\[\]\;\:\'\"\,\.\`\~\<\>\/\?\n]+/'," ",$json->note); //normalize spaces and non printing characters
    $json->note = preg_replace('/[\s\n]*\n[\s\n]*/',"\n  ",$json->note); //make all line gaps one linefeed
    $json->note = trim($json->note); 

    $pat = '/.*zapi:etag="(.*?)"/';
    preg_match($pat,$content->asXML(),$match);

    $etag = $match[1];
    $notes []= array('itemKey'=>$note_itemKey,'json'=>$json,'etag'=>$etag);
  }
  return $notes;
}

function extract_notes($zdata) {
  $zdata = str_replace('zapi:key','zapikey',$zdata);
  $xml = simplexml_load_string($zdata);
  $entries = $xml->entry;
  $notes = array();
  foreach ($entries as $entry) {
    $note_itemKey = (string)$entry->zapikey;
    $content = $entry->content[0];
    $json = json_decode($content);
    $json->note = str_replace('<p>',"\n",$json->note); //replace p element with returns
    $json->note = str_replace('<\p>',"",$json->note); //replace p element with returns
    $json->note = strip_tags($json->note); //clear out other html tags
    $json->note = preg_replace('/[^A-Za-z\d\!\@\#\$\%\^\&\*\(\)\{\}\[\]\;\:\'\"\,\.\`\~\<\>\/\?\n]+/'," ",$json->note); //normalize spaces and non printing characters
    $json->note = preg_replace('/[\s\n]*\n[\s\n]*/',"\n  ",$json->note); //make all line gaps one linefeed
    $json->note = trim($json->note); 
    
    $pat = '/.*zapi:etag="(.*?)"/';
    preg_match($pat,$content->asXML(),$match);
    
    $etag = $match[1];
    $notes []= array('itemKey'=>$note_itemKey,'json'=>$json,'etag'=>$etag);
  }
  return $notes;
}
  
function convert_druid_to_key_csv_to_array($file) {
  $csv = file_get_contents($file);
  $lines = preg_split('/[\n\r]+/',$csv);
  $arr = array();
  foreach($lines as $line) {
    $fields = explode(',',$line);
    $arr[$fields[0]] = $fields[1];
  }
  return $arr;
}

function get_itemKey_from_druid($druid) {
  global $druid_to_zkey;
  $found_it = false;
  for($acc = 1; $acc <= 3; $acc++) {
    if (isset($druid_to_zkey[$acc][$druid])) {
      $itemKey  = $druid_to_zkey[$acc][$druid];
      $accession = $acc;
      $found_it = true;
      break;
    }
  }
  if (!$found_it) {
    return false;
  }
  return array('accession' => $accession, 'itemKey' => $itemKey);
}

function get_zotero_by_itemKey($itemKey, $accession) {
  //group library group numbers:  1st accession, 2nd accession, 3rd accession
  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 
  
  $read_url = "https://api.zotero.org/groups/$lib/items/$itemKey?key=$privateKey&content=json";

  $return = file_get_contents($read_url);
  //echo json_encode(array("z"=>$return));die();//ASDFASDF

  $xml = simplexml_load_string($return);
  $content = $xml->content[0];
  $json = json_decode($content);
  $pat = '/.*zapi:etag="(.*?)"/';
  preg_match($pat,$content->asXML(),$match);
  $etag = $match[1];
  return array('json'=>$json,'etag'=>$etag);
}

function get_all_tags_from_accession($accession) {
  //note could look at <zapi:numItems>

  //group library group numbers:  1st accession, 2nd accession, 3rd accession
  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 
  
  $read_url = "https://api.zotero.org/groups/$lib/tags?key=$privateKey&limit=199";
  $return = file_get_contents($read_url);
  $xml = simplexml_load_string($return);
  $entries = $xml->entry;
  $tags = array();
  foreach($entries as $entry) {
    $tags []= (string) $entry->title;
  }
  //$pat = '/.*zapi:etag="(.*?)"/';
  //preg_match($pat,$content->asXML(),$match);
  //$etag = $match[1];
  return $tags;//array('json'=>$json,'etag'=>$etag);
}

function write_back_to_zotero($itemKey,$accession,$obj,$etag) {

  $libs = array(null,34657,34658,71904);
  $lib = $libs[$accession];
  $privateKey = "ekNP007RTJDaKmi4olmXsKaj"; 

  //fix up tags=NULL (needs to be [])
  if ($obj->tags === null) $obj->tags = array();

  //create temporary PUT file
  $new_content = json_encode($obj);

  $putData = tmpfile();
  fwrite($putData,$new_content);
  fseek($putData, 0);
  $ch = curl_init();
  $write_url = "https://api.zotero.org/groups/$lib/items/$itemKey?key=$privateKey";

  $headers = array("Content-Type: application/json",
		   "If-Match: \"$etag\"",
		   "Expect: ");  //this is to get rid of the bogus "Expect: 100-continue"
  //echo json_encode(array('headers'=>$headers,'url'=>$write_url,'new_content'=>$new_content));die();  


  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $write_url);
  curl_setopt($ch, CURLOPT_PUT, true);
  curl_setopt($ch, CURLOPT_INFILE, $putData);
  curl_setopt($ch, CURLOPT_INFILESIZE, strlen($new_content));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);  
  

  //ASDFASDFASDF
  //looks like PUT is sending bogus tags data, why? originatos is working?

  //for debug:
  //curl_setopt($ch, CURLOPT_HEADER, true);
  //curl_setopt($ch, CURLOPT_VERBOSE, true);

  $return = curl_exec($ch);
  fclose($putData);
  curl_close($ch);

  //return new etag and content
  $xml = simplexml_load_string($return);
  if ($xml === false) return false;

  $content = $xml->content[0];
  $json = json_decode($content);
  $pat = '/.*zapi:etag="(.*?)"/';
  preg_match($pat,$content->asXML(),$match);

  $etag = $match[1];

  return array('json'=>$json,'etag'=>$etag);
}


function zotero_to_lowood($z) {  
  $m = array();
  $m['document_type'] = $z->itemType;
  if (!$z->itemType) return false;
  
  $supported_item_types = array('thesis',
				'book',
				'manuscript',
				'computerProgram',
				'bookSection',
				'report',
				'letter',
				'conferencePaper',
				'journalArticle',
				'email');

  if (!in_array($m['document_type'],$supported_item_types)) {
    $m['error'] = "\"".$m['document_type']."\" type not supported. Please change in Zotero.";
    $m['document_type'] = 'manuscript';
    return $m;
  };

  //standard fields on all docs
  $m['url'] = $z->url;

  //for testing... 
  $m['tags'] = $z->tags;

  //(lowood)
  $m['document_type'] = $z->itemType;
  $m['originators'] = array();
  foreach ($z->creators as $creator) {
    $m['originators'][] = combine_fields(' ', array($creator->name,
						    $creator->firstName,
						    $creator->lastName));
  }

  //need NOTES!

  switch ($z->itemType) {
  case 'thesis':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = $z->thesisType;
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->university;
    $m['extent'] = $z->numPages;
    $m['language'] = $z->language;
    $m['containing_work'] = parse_extra_for($z->extra,'containing_work');
    $m['number'] = parse_extra_for($z->extra,'number');
    break;

  case 'book':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->publisher;
    $m['extent'] = $z->numPages;
    $m['language'] = $z->language;
    $m['containing_work'] = $z->series;
    $m['number'] = trim($z->seriesNumber.' '.$z->volume);
    break;

  case 'manuscript':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = $z->manuscriptType;
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = parse_extra_for($z->extra,'place');
    $m['corporate_entity'] = parse_extra_for($z->extra,'corporate_entity');
    $m['extent'] = $z->numPages;
    $m['language'] = $z->language;
    $m['containing_work'] = parse_extra_for($z->extra,'containing_work');
    $m['number'] = parse_extra_for($z->extra,'number');
    break;

  case 'computerProgram':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->company;
    $m['extent'] = parse_extra_for($z->extra,'extent');
    $m['language'] = $z->programmingLanguage;
    $m['containing_work'] = combine_fields(', ',array($z->seriesTitle,$z->system));
    $m['number'] = $z->version;
    break;

  case 'bookSection':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->publisher;
    $m['extent'] = $z->pages;
    $m['language'] = $z->language;
    $m['containing_work'] = combine_fields(', ',array($z->bookTitle,$z->series));
    $m['number'] = combine_fields(', ',array($z->seriesNumber,$z->volume));;
    break;

  case 'report':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = $z->reportType;
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->institution;
    $m['extent'] = $z->pages;
    $m['language'] = $z->language;
    $m['containing_work'] = $z->seriesTitle;
    $m['number'] = $z->reportNumber;
    break;

  case 'letter':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;

    //lowood fields
    $m['document_subtype'] = $z->letterType;
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = parse_extra_for($z->extra,'place');
    $m['corporate_entity'] = parse_extra_for($z->extra,'corporate_entity');
    $m['extent'] = parse_extra_for($z->extra,'extent');
    $m['language'] = $z->language;
    $m['containing_work'] = parse_extra_for($z->extra,'containing_work');
    $m['number'] = parse_extra_for($z->extra,'number');
    break;

  case 'conferencePaper':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;

    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = $z->place;
    $m['corporate_entity'] = $z->publisher;
    $m['extent'] = $z->pages;
    $m['language'] = $z->language;
    $m['containing_work'] = combine_fields(', ',array($z->proceedingsTitle,$z->conferenceName,$z->series)); 
    $m['number'] = $z->volume;
    break;

  case 'journalArticle':
    //archive fields
    $m['archiveLocation'] = $z->archiveLocation;
    $m['callNumber'] = $z->callNumber;
    
    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->title;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = parse_extra_for($z->extra,'place');
    $m['corporate_entity'] = parse_extra_for($z->extra,'corporate_entity');
    $m['extent'] = $z->pages;
    $m['language'] = $z->language;
    $m['containing_work'] = combine_fields(', ',array($z->publicationTitle,$z->series,$z->seriesTitle));
    $m['number'] = combine_fields(', ',array($z->volume,$z->issue));
    break;

  case 'email':
    //archive fields
    $m['archiveLocation'] = parse_extra_for($z->extra,'archiveLocation');
    $m['callNumber'] = parse_extra_for($z->extra,'callNumber');

    //lowood fields
    $m['document_subtype'] = parse_extra_for($z->extra,'document_subtype');
    $m['title'] = $z->subject;
    $m['abstract'] = $z->abstractNote;
    $m['date'] = $z->date;
    $m['place'] = parse_extra_for($z->extra,'place');
    $m['corporate_entity'] = parse_extra_for($z->extra,'corporate_entity');
    $m['extent'] = parse_extra_for($z->extra,'extent');
    $m['language'] = $z->language;
    $m['containing_work'] = parse_extra_for($z->extra,'containing_work');
    $m['number'] = parse_extra_for($z->extra,'number');
    break;
    
  default:
    break;
  }

  return $m;
}

function parse_extra_for($extra,$field) {
  $pat = '/\[([a-z]+[A-Z_]?[a-z]*)\]/';
  $array = preg_split($pat,$extra,null,PREG_SPLIT_DELIM_CAPTURE);
  $array[] = '';
  foreach($array as $key => $value) {
    if ($value == $field) return trim($array[$key+1]);
  }
  return '';
}

function combine_fields($delim,$fields) {
  foreach ($fields as $key => $field) {
    if (!$field) unset($fields[$key]);
  }
  return implode($delim,$fields);
}

//////////////////////////////////
function lowood_to_zotero($m) {  
  $supported_item_types = array('thesis',
				'book',
				'manuscript',
				'computerProgram',
				'bookSection',
				'report',
				'letter',
				'conferencePaper',
				'journalArticle',
				'email');

  if (!in_array($m['document_type'],$supported_item_types)) return false;
  
  $z = (object) null;
  //standard fields on all docs
  $z->url = $m['url'];
  
  //for testing... 
  $z->tags = $m['tags'];
  
  //(lowood)
  $z->itemType = $m['document_type'];
  
  switch ($z->itemType) {
  case 'thesis':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->thesisType = $m['document_subtype'];
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract'];
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->university = $m['corporate_entity'];
    $z->numPages = $m['extent'];
    $z->language = $m['language'];
    $z->extra = build_extra(array('containing_work' => $m['containing_work'],
				  'number' => $m['number'])); 
    break;

  case 'book':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->extra = build_extra(array('document_subtype' => $m['document_subtype']));
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract'];
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->publisher = $m['corporate_entity'];
    $z->numPages = $m['extent'];
    $z->language = $m['language'];
    $z->series = $m['containing_work'];
    $z->volume = $m['number'];
    break;
    
  case 'manuscript':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->manuscriptType = $m['document_subtype'];
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->numPages = $m['extent'];
    $z->language = $m['language'];
    $z->extra = build_extra(array('place' => $m['place'],
				  'corporate_entity' => $m['corporate_entity'],
				  'containing_work' => $m['containing_work'],
				  'number' => $m['number']));
    break;

  case 'computerProgram':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'programmer');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->company = $m['corporate_entity'];
    $z->programmingLanguage = $m['language'];
    $z->seriesTitle = $m['containing_work'];
    $z->system = '';
    $z->extra = build_extra(array('document_subtype' => $m['document_subtype'], 
				  'extent' => $m['extent']
				  ));
    $z->version = $m['number'];
    break;

  case 'bookSection':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->extra = build_extra(array('document_subtype' => $m['document_subtype']));
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->publisher = $m['corporate_entity'];
    $z->pages = $m['extent'];
    $z->language = $m['language'];
    $z->bookTitle = $m['containing_work'];
    $z->series = '';
    $z->volume = $m['number'];
    $z->seriesNumber = '';
    break;

  case 'report':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->reportType = $m['document_subtype'];
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->institution = $m['corporate_entity'];
    $z->pages = $m['extent'];
    $z->language = $m['language'];
    $z->seriesTitle = $m['containing_work'];
    $z->reportNumber = $m['number'];
    $z->extra = build_extra(array());
    break;

  case 'letter':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];

    //lowood fields
    $z->letterType = $m['document_subtype'];
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->language = $m['language'];
    $z->extra = build_extra(array('place'=>$m['place'],
				  'corporate_entity' => $m['corporate_entity'],
				  'extent' => $m['extent'],
				  'containing_work' => $m['containing_work'],
				  'number' => $m['number']));
    break;

  case 'conferencePaper':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];

    //lowood fields
    $z->extra = build_extra(array('document_subtype' => $m['document_subtype']));
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->place = $m['place'];
    $z->publisher = $m['corporate_entity'];
    $z->pages = $m['extent'];
    $z->language = $m['language'];
    $z->proceedingsTitle = $m['containing_work'];
    $z->conferenceName = '';
    $z->series = '';
    $z->volume = $m['number'];
    break;

  case 'journalArticle':
    //archive fields
    $z->archiveLocation = $m['archiveLocation'];
    $z->callNumber = $m['callNumber'];
    
    //lowood fields
    $z->title = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->extra = build_extra(array('document_subtype' => $m['document_subtype'],
				  'place' => $m['place'],
				  'corporate_entity' => $m['corporate_entity']));
    $z->pages = $m['extent'];
    $z->language = $m['language'];
    $z->publicationTitle = $m['containing_work'];
    $z->series = '';
    $z->seriesTitle = '';
    $z->issue = $m['number'];
    $z->volume = '';
    break;

  case 'email':


    //archive fields
    $z->extra = build_extra(array('archiveLocation' => $m['archiveLocation'],
				  'callNumber' => $m['callNumber'],
    //lowood fields
				  'document_subtype' => $m['document_subtype'],
				  'place' => $m['place'],
				  'corporate_entity' => $m['corporate_entity'],
				  'extent' => $m['extent'],
				  'containing_work' => $m['containing_work'],
				  'number' => $m['number']));
    $z->subject = $m['title'];
    $z->creators = build_creators($m['originators'],'author');
    $z->abstractNote = $m['abstract']; 
    $z->date = $m['date'];
    $z->language = $m['language'];
    break;
    
  default:
    break;
  }
  return $z;
}

function build_extra($field_data) {
  $extra = '';
  foreach ($field_data as $field => $value) {
    $extra .= "[$field] $value\n";
  }
  return $extra;
}

function build_creators($originators,$creatorType) {
  $creators = array();
  foreach ($originators as $originator) {
    $creator = (object) null;
    $creator->creatorType = $creatorType;
    $creator->name = $originator;
    $creators[] = $creator;
  }
  return $creators;
}

