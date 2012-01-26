<?php
require_once './build/libZoteroSingle.php';

$libraryType = 'group'; //user or group
$lib1ID = '34657'; //1st accession
$lib1Slug = '34657';
$lib2ID = '34658'; //2nd accession
$lib2Slug = '34658';
$apiKey = 'gIuRNxwnnnBfesL11AYw6T9Z';


//for testing use this:
$libraryID = '25950'; //'Desired Types...'
$librarySlug = '25950';

$library = new Zotero_Library($libraryType, $libraryID, $librarySlug, $apiKey);
$itemKey = '5327VG8I';
$ret = get_item($library,$itemKey);
var_dump($ret);
die();
//given 

function get_item($library, $itemKey) {
  $items = $library->loadItems(array('itemKey'=>$itemKey));
  if (!$items) return false;
  $existingItem = $items[0];
  preg_match('/.*?>([^<]*)/',$existingItem->content, $match);
  $doc_json = json_decode($match[1]);
  
  $items = $library->loadItems(array('itemKey'=>$itemKey,'target'=>'children'));
  $doc_json->notes = array();
  foreach ($items as $item) {
    preg_match('/.*?>([^<]*)/',$item->content, $match);
    $note_json = json_decode($match[1]);
    $doc_json->notes []= strip_tags($note_json->note);
  }
  return $doc_json;
}
die();
/*
" \u0022
' \u0027
& \u0026
< \u003c
> \u003e
*/


//(1) need to get metada, tags and notes from a given key;
//do this for all 100 items on the simtool page and update simtool db and webpage live is needed
// we can replace this with a zotero pluging updater later...

//(2) need to update zotero with tags, notes and metadata on each tag, note, or metadata update from simtool
//make the call every time the user leves the tet area with a change.-- live update! 

//function get_all_tags($lib)
//this function can't work easily, since there is a 100 item limit...

//create a new item of type book
//echo "Asdf";
/*


$newItem = $library->getTemplateItem('book');
//print_r($newItem);

$newItem->set('title', 'This is a Book created from the API');
$newItem->set('abstractNote', 'Created using a zotero php library and the write api');
//print_r($newItem);
//die();
$createItemResponse = $library->createItem($newItem);
print_r($createItemResponse);
echo "\n\n----------\n\n";
if($createItemResponse->isError()){
    echo $createItemResponse->getStatus() . "\n";
    echo $createItemResponse->getBody() . "\n";
    die("Error creating Zotero item\n\n");
}
echo "Item created\n\n";
*/


//$existingItem = new Zotero_Item($createItemResponse->getBody());
//echo "GET BODY==";print_r($createItemResponse->getBody());

//add child note
$newNoteItem = $library->getTemplateItem('note');
$newNoteItem->apiObject['note'] = "this is a test note";
echo "NOTE==";print_r($newNoteItem);

$addNoteResponse = $library->addNotes($existingItem, $newNoteItem);
if($addNoteResponse->isError()){
    echo $addNoteResponse->getStatus() . "\n";
    echo $addNoteResponse->getBody() . "\n";
    die("error adding child note to item");
}
echo "added child note\n";

/*
$existingItem->set('date', '2011');
//$existingItem->set('deleted', 1);
$updateItemResponse = $library->writeUpdatedItem($existingItem);
if($updateItemResponse->isError()){
    die("Error updating Zotero item\n\n");
}
echo "Item updated\n\n";
*/

//$items = $library->loadItems(array('itemKey'=>'A937MNXH'));
//$items = $library->loadItems(array('itemKey'=>'5327VG8I'));
//echo "here it is: ".$items[0]->title;

//$library->loadAllCollections(array());
//var_dump($item);
//$item->set('title', 'newtitle');
//$updateResponse = $library->updateItem($item->itemKey);
//$deleteResponse = $library->deleteItem($item);
//var_dump($updateResponse);
/*
$item = $library->getTemplateItem('book');
var_dump($item);
*/




?>