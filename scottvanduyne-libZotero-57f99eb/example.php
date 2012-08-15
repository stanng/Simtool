?php
require_once './config.php'; //library credentials

require_once './build/libZoteroSingle.php';
//public function __construct($libraryType = null, $libraryID = 'me', $libraryUrlIdentifier = null, 
//$apiKey = null, $baseWebsiteUrl="http://www.zotero.org", $cachettl=0)

$userID = '25950'; //'Desired Types...'
$userSlug = '25950';

//$userID = '34657'; //1st Accession
//$userSlug = '34657';

$library = new Zotero_Library($libraryType, $userID, $userSlug, $apiKey);
//print_r($library);
//die();


//get some tags
/*
$tags = $library->fetchTags(array('limit'=>5, 'order'=>'title', 'sort'=>'desc'));
//var_dump($tags);
foreach($tags as $tag){
    if($tag->numItems > 0){
        echo $tag->name . " - " . $tag->numItems . "\n";
    }
    else{
        echo $tag->name . " - has no items\n"; 
    }
}

//die();
*/

//get groups the key has access to
//need more complete groups information in normal responses before this is useful
//but for now it can get IDs at least

//$r = $library->getAccessibleGroups($userID);
//print_r($r);
//var_dump($r);die;

//get permissions for the key
//$permissions = $library->getKeyPermissions();
//print_r($permissions);
//die();

//load some existing items
/*
$items = $library->loadItemsTop(array('limit'=>70));
//var_dump($items);
foreach($items as $item){
 echo "----item:" . $item->content . "\n\n";
}
//print_r($items);
die();
*/

/*
//load the items currently in the trash
$items = $library->loadTrashedItems(array('limit'=>10));
foreach($items as $item){
    echo "Trashed item with title: " . $item->get('title') . "\n";
    //echo "now deleting item \n";
    //$library->deleteItem($item);
}
*/

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

$items = $library->loadItems(array('itemKey'=>'5327VG8I'));
$existingItem = $items[0];
//echo "EXISTING ITEM==";print_r($existingItem);

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


$existingItem->set('date', '2011');
//$existingItem->set('deleted', 1);
$updateItemResponse = $library->writeUpdatedItem($existingItem);
echo $updateItemResponse;
if($updateItemResponse->isError()){
    die("Error updating Zotero item\n\n");
}
echo "Item updated\n\n";


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