<?php

//php:: given itemKey, and accession: get the notes (up to 5)contents etags, itemkeys
//js: store all as text in textarea prepent [NOTE], attrubies etag1, etag2 , itemKey1, itemKey2 ... ...Key5
//js: on edit: separate text into notes (up to 5).  build items data_string
//js: hide text
//php: given itemlist, add notes
//php: delete existing notes,
//
//php: get the notes again (as in step 1) and populate notes-textarea

$accession  = asdf;
$itemKey = $_REQUEST['itemKey'];
$note_itemKeys = $_REQUEST['note-itemKeys'];
$note_etags = $_REQUEST['note-etags'];
$notes = $_REQUEST['notes'];

switch ($mode) {
case 'readnotes':
  $notes = get_zotero_notes_by_itemKey($itemKey);
  echo json_encode($notes);
  break;

case 'writenotes':
  //$items = array('items'=>array($return[0]['json'])));
  $etags_array = json_decode($note_etags);
  $itemKeys_array = json_decode($note_itemKeys);
  $notes_array = json_decode($notes);

  //add new notes, delete old notes, and read new content and return
  add_notes_to_itemKey($itemKey,$note_array);

  //delete old:
  for ($i = 0; $i < count($etags); $i++) {
    delete_zotero_item($itemKeys[$i],$etags[$i]);
  }
  $notes = get_zotero_notes_by_itemKey($itemKey);
  echo json_encode($notes);
  break;

default:
  break;
}

function delete_zotero_item($itemKey,$etag) {
  $ch = curl_init();
  $url = "https://api.zotero.org/users/69335/items/$itemKey?key=gIuRNxwnnnBfesL11AYw6T9Z";
  $headers = array(
		   "If-Match: \"$etag\"",
		   "Expect: ");  //this is to get rid of the bogus "Expect: 100-continue"
  
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_DELETE, true);
  //for debug:
  curl_setopt($ch, CURLIOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  
  $output = curl_exec($ch);
  curl_close($ch);
}

function add_notes_to_itemKey($itemKey,$items) {
  $ch = curl_init();
  $url = "https://api.zotero.org/users/69335/items/$itemKey/children?key=gIuRNxwnnnBfesL11AYw6T9Z";
  $data_string = json_encode($items);
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
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  
  $output = curl_exec($ch);
}

function get_notes_by_parent_itemKey($itemKey) {
  $zdata = file_get_contents("https://api.zotero.org/users/69335/items/$itemKey/children?content=json&key=gIuRNxwnnnBfesL11AYw6T9Z");
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
    $json->note = trim($json->note); 
    $json->note = strip_tags($json->note); //clear out other html tags
    $pat = '/.*zapi:etag="(.*?)"/';
    preg_match($pat,$content->asXML(),$match);

    $etag = $match[1];
    $notes []= array('itemKey'=>$note_itemKey,'json'=>$json,'etag'=>$etag);
  }
  return $notes;
}


////////////////////////////



// GET ALL notes for a key

$return = file_get_contents("https://api.zotero.org/users/69335/items/$itemKey/children?content=json&key=gIuRNxwnnnBfesL11AYw6T9Z");
//print_r($return);die();


//<zapi:key>TGWKWBMW</zapi:key>



//one text area with [NOTE] asdadsfasdfasdf [NOTE] asdfasdf
//a new note is created by typing [NOTE]
 

//$x = get_zotero_by_itemKey('RPESBB24');
//print_r($x);
//die();

// GET ALL THE KEYS
//   $return = file_get_contents("https://api.zotero.org/users/69335/items?format=keys&key=gIuRNxwnnnBfesL11AYw6T9Z");
//  echo $return;die();

//test create a note
$itemKey = 'RPESBB24';
$return = get_zotero_notes_by_itemKey($itemKey);

$ch = curl_init();
$url = "https://api.zotero.org/users/69335/items/$itemKey/children?key=gIuRNxwnnnBfesL11AYw6T9Z";
//$note_p_array = explode("\n",$return[0]['json']->note);
//$note_string = "<p>".implode("</p><p>",$note_p_array)."</p>";
//$return[0]['json']->note = $note_string;
$data_string = json_encode(array('items'=>array($return[0]['json'])));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, 
	    CURLOPT_HTTPHEADER, 
	    array(
		  'Content-Type: application/json',
		  'Content-Length: ' . strlen($data_string))
	    );

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);

  //for debug:
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  
  $output = curl_exec($ch);
  echo $output;
die();






$itemKeys = array('ETWKMCJP',
		  '24BN3RQT',
		  'T58A2JD5',
		  'RPESBB24',
		  'A2CM2UDA',
		  'NPDPXTJA',
		  'RZCXDB52',
		  'KKAX84N5',
		  'GPTD7VHQ',
		  'IW7MTZJD',
		  'QZH6HR5P',
		  '5HAP2FA3');
/*
5QA382HH ??
T58A2JD5: email
RPESBB24: thesis
A2CM2UDA: book
NPDPXTJA: manuscript
RZCXDB52: computerProgram
KKAX84N5: bookSection
GPTD7VHQ: report
IW7MTZJD: letter
QZH6HR5P: conferencePaper
5HAP2FA3: journalArticle
*/

////////////////////test
/*
foreach ($itemKeys as $itemKey) {
  $z = get_zotero_by_itemKey($itemKey);
  $m = zotero_to_lowood($z['json']);
  if (!$m) continue;  
  echo "$itemKey: {$m['document_type']}\n";
}
die();
*/


/*
if (count($argv) == 1) {
  echo implode(', ',$itemKeys)."\n";
  die();
}
*/
//////////////////////test
$itemKey = 'RPESBB24';//$argv[1];
$z = get_zotero_by_itemKey($itemKey);
echo "ORIGINAL RECORD:\n";
print_r($z['json']);
$m = zotero_to_lowood($z['json']);
echo "ZOTERO_TO_LOWOOD:\n";
print_r($m);
$m['document_type'] = $argv[1];

$z_prime = lowood_to_zotero($m);
echo "LOWOOD_TO_ZOTERO:\n";
print_r($z_prime);

write_back_to_zotero($itemKey,$z_prime,$z['etag']);


die();




  function get_zotero_by_itemKey($itemKey) {
    $zdata = file_get_contents("https://api.zotero.org/users/69335/items/$itemKey?content=json&key=gIuRNxwnnnBfesL11AYw6T9Z");
    $xml = simplexml_load_string($zdata);
    $content = $xml->content[0];
    $json = json_decode($content);
    $pat = '/.*zapi:etag="(.*?)"/';
    preg_match($pat,$content->asXML(),$match);
    $etag = $match[1];
    return array('json'=>$json,'etag'=>$etag);
  }




//$newtag = "TEST_TAG_29";
//$json->tags []= json_decode("{\"tag\":\"$newtag\"}");


function write_back_to_zotero($itemKey,$obj,$etag) {
  $new_content = json_encode($obj);
  $putData = tmpfile();
  fwrite($putData,$new_content);
  fseek($putData, 0);
  
  $ch = curl_init();
  $url = "https://api.zotero.org/users/69335/items/$itemKey?key=gIuRNxwnnnBfesL11AYw6T9Z";
  $headers = array("Content-Type: application/json",
		   "If-Match: \"$etag\"",
		   "Expect: ");  //this is to get rid of the bogus "Expect: 100-continue"
  
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_PUT, true);
  curl_setopt($ch, CURLOPT_INFILE, $putData);
  curl_setopt($ch, CURLOPT_INFILESIZE, strlen($new_content));
  
  //for debug:
  curl_setopt($ch, CURLIOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  
  $output = curl_exec($ch);
  echo $output;
fclose($putData);
  curl_close($ch);
}



/////////////
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

  if (!in_array($z->itemType,$supported_item_types)) return false;

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

  if (!in_array($m['document_type'],$supported_item_types)) die("bad document_type:".print_r($m,1));
  
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
