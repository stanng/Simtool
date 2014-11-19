<?php
header('Access-Control-Allow-Origin: *');

$url = $_REQUEST['url'];
$text = file_get_contents($url);
if ($text === false) {
  $title =  "";
  $obj = null;
} else {
  //$pat = '/<[title|TITLE]>(.*?)<\/[title|TITLE]>/';
  $pat = "/<title\>(.*?)<\/title/i";
  preg_match($pat,$text,$match);
  if (isset($match[1])) $title = trim($match[1]);
  else $title = "";
  $obj['title'] = $title;

  $pat = '/blacklight-subseries_display.+?>(.*?)<\/dd/';
  preg_match($pat,$text,$match);
  if (isset($match[1])) $subseries = trim($match[1]);

}


//returning these itmes
<dd class="blacklight-subseries_display">New World Vistas Related Materials1992 -       1995</dd>




<dd class='blacklight-notes_display'>Presentation Manuscript (2008); American Association for Artificial Intelligence;
  various paper abstracts/notes

      <dd class="blacklight-originator_s">Edward Feigenbaum</dd>

</dl>
