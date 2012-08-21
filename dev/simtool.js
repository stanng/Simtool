// -*- C -*-

$(function(){
    if (window.location.toString().search("password=saltworks") < 0) {
      alert('invalid password');
      return false;
    }

    var howmany = 25; //up to 100 possible, but a drag on zotero cloud...
    //     could throttle zotero calls...FIXME see zotero_connect.php calls below
    $("#submit-main-druid")
      .click(function () {
	  var druid = $("#main-druid-entry").val();
	  __nav("submit",druid);
	  refresh_rows(druid, howmany);
	});
    
    /*    $("#main-druid-entry")
      .blur(function () { //trigger submit on blur
  var druid = $("#main-druid-entry").val();
	  __nav("submit",druid);
	  refresh_rows(druid, howmany);
	});
    */
    /*
      .keyup(function(event){
	  if(event.keyCode == 13){ //tregger submit on enter
	    var druid = $("#main-druid-entry").val();
	    __nav("submit",druid);
	    refresh_rows(druid, howmany);
	  }
      */
    
    $("#navigate-back")
      .click(function(){
	  var druid = __nav("back",false);
	  if (druid === false) {
	    $("#main-druid-entry").val("");
	  } else {
	    $("#main-druid-entry").val(druid);
	    refresh_rows(druid, howmany);
	  }
	});
    
    $("#navigate-forward")
      .click(function(){
	  var druid = __nav("forward",false);
	  if (druid === false) {
	    $("#main-druid-entry").val("");
	  } else {
	    $("#main-druid-entry").val(druid);
	    refresh_rows(druid, howmany);
	  }
	});
  });

function __nav(mode,druid) {
  //init static nav history array
  if (typeof __nav.history == 'undefined') {
    __nav.history = [];
    __nav.pointer = -1;
  }
  
  //alert("pointer="+__nav.pointer+"\n"+dump(__nav.history));
  
  switch (mode) {
  case "submit":
    __nav.pointer++;
    __nav.history[__nav.pointer] = druid;
    //truncate "future" history on submit... is this right?
    //__nav.history.length = __nav.pointer + 1;
    return druid;
    break;
    
  case "back":
    __nav.pointer = Math.max(__nav.pointer - 1, -1);
    if (__nav.pointer < 0) return false;
    druid = __nav.history[__nav.pointer];
    return druid;    
    break;
    
  case "forward":
    __nav.pointer = Math.min(__nav.pointer + 1, __nav.history.length);
    if (__nav.pointer > __nav.history.length - 1) return false;
    druid = __nav.history[__nav.pointer];
    return druid;    
    break;
  }
  return false;
}

function refresh_tags() {
  $.ajax({
    url: "zotero_connect.php",
	data: {mode: 'get-all-tags'},
	dataType: "json",
	success: generate_tags
	});
}

function generate_tags(data) {
  $("#tags-cloud").html("<div></div>");
  $("#tags-cloud div").css({position:'fixed',background:"#f0f0f0",top:'0',bottom:'0',overflow:'auto'});
  for (i = 0; i < data.length; i++){
    $("#tags-cloud div")
      .append($('<span></span>')
	      .html(data[i]));
  }
}

function refresh_rows(druid,howmany) {
  //var druid = druid.match(/[a-z]{2}\d{3}[a-z]{2}\d{4}/)[0];
  var match = druid.match(/[a-z]{2}\d{3}[a-z]{2}\d{4}/);
  if (match != null) {
    druid = match[0];
    
    $.ajax({
      url: "get_data.php",
	  data: {druid: druid, howmany: howmany},
	  dataType: "json",
	  success: generate_rows
	  });
  } 
  else {
    //try filenames
    $.ajax({
      url: "get_druid_from_filename.php",
	  data: {'filename-text': druid},
	  dataType: "json",
	  success: (function (data) {
	      $.ajax({
		url: "get_data.php",
		    data: {druid: data.druid, howmany: howmany},
		    dataType: "json",
		    success: generate_rows
		    });
	    })
	  });
  }
}
function generate_rows(data) {
  $("#data-tbody").html("");
  for (var i=0; i<data.length; i++) {
    var this_druid = data[i].druid;
    var this_pdf = data[i].pdf;
    
    var tr = $('<tr class="document-row" id="druid-'+this_druid+'"/>');    

    //first cell, druid, pdf, summary buttons
    var pdf_button = $('<input type="button" class="show-pdf" value="PDF" />')
      .click(function (){
	  var druid = $(this).parents('.document-row').attr('id').substr(6);
	  window.open(get_pdf_name(druid),"_blank");
	});
    
    var summary_display_div = $('<div class="summary-display-div"/>')
      .css({'position':'absolute','width':'300px','z-index':'10','background':'beige'})
      .html(data[i].zotero_key+"--"+data[i].summary)
      .hide();

    var summary_button = $('<input type="button" value="SUM" /></div>')
      .hover(function () {$("div",this).show()},
	     function () {$("div",this).hide()});

    var td = $('<td valign="top"></td>');
    td.append($('<span class="display-druid" >'+data[i].druid+'</span>'));
    td.append($('<br/>'));
    td.append(pdf_button);
    //td.append(summary_display_div);
    //td.append(summary_button);
    tr.append(td);

    //word count cell
    //tr.append($('<td valign="top"> <span class="display-doclen" >'+data[i].doclen+'</span></td>'));
    

    //cos sim cell
    var make_new_center_button = $('<button class="make-new-center" ><img src="reload_icon&48.png" width="16px"/></button>')
      .click(function(){
	  var druid = $(this).parents('.document-row').attr('id').substr(6);
	  //alert(druid);
	  $("#main-druid-entry").val(druid);
	  $("#submit-main-druid").click();
	});
    
    td = $('<td valign="top"></td>');
    td.append($('<span class="display-cos-sim" >'+(Math.round(1000*data[i].cos_sim)/1000).toFixed(3)+'</span>'));
    td.append($('<br/>'));
    td.append(make_new_center_button);    
    tr.append(td);
    
    
    //metadata fiels cell
    var metadata_textarea = $('<textarea class="metadata-textarea"></textarea>');
    var archive_fields_store = $('<div class="archive-fields-store" style="display:none;"></div>');
    var document_type_pulldown = $('<select class="document-type-pulldown" style="color:darkblue;"> <option value="book"> Book </option>  <option value="bookSection"> Book Section </option>  <option value="computerProgram"> Computer Program </option>  <option value="conferencePaper"> Conference Paper </option>  <option value="email"> Email </option>  <option value="journalArticle"> Journal Article </option>  <option value="letter"> Letter </option>  <option value="manuscript"> Manuscript </option>  <option value="report"> Report </option><option value="thesis"> Thesis </option></select>');

    //metadata cell
    td = $('<td valign="top"></td>');
    td.append(document_type_pulldown);
    td.append($('<br/>'));
    td.append(metadata_textarea);
    td.append(archive_fields_store);
    tr.append(td);

    //tags cell
    var tags_textarea = $('<textarea class="tags-textarea"></textarea>');
    td = $('<td valign="top"></td>');
    td.append(tags_textarea);
    tr.append(td);

    var notes_textarea = $('<textarea class="notes-textarea"></textarea>');
    var notes_etag_store = $('<div class="notes-etag-store" style="display:none;"></div>');
    td = $('<td valign="top"></td>');
    td.append(notes_textarea);
    td.append(notes_etag_store);
    tr.append(td);

    $("#data-tbody").append(tr);
    
    //LOAD METADATA AND TAGS FROM ZOTERO
    var this_druid = $(tr).attr('id').substr(6);
    $.ajax({
      url: "zotero_connect.php", 
	  data: {mode: 'read', druid: this_druid}, 
	  dataType: "json",
	  success: refresh_simtool_row_data
	  }
      );
    $.ajax({
      url: "zotero_connect.php", 
	  data: {mode: 'readnotes', druid: this_druid}, 
	  dataType: "json",
	  success: refresh_row_notes
	  }
      );
 
  }
  $("#data-tbody").children("tr:first").css('background','lightgreen');    

  refresh_tags();
  return false;
}

function refresh_row_notes (data) {
  var druid = data.druid;
  var tr = $("#druid-"+druid); 
    ////store etags and itemKeys
  var e = $(tr).find('.notes-etag-store');
  var notes_textarea = $(tr).find('.notes-textarea');
  var note_array = [];
  //alert(dump(data));
  
  for(i = 0; i < data.notes.length; i++) {
    $(e).attr('etag'+i,data.notes[i].etag);
    $(e).attr('itemKey'+i,data.notes[i].itemKey);
    note_array.push(data.notes[i].json['note']);
  }
  //alert(dump(note_array));
  var notes_text = '[NOTE]'+(note_array.join('\n[NOTE]'));
 $(tr).find('.notes-textarea')
    .text(notes_text)
    .unbind()
    .change(function(){
	save_notes($(tr))
	  })
    .show('fast');
}


function refresh_simtool_row_data(data) {
  var druid = data.druid;
  var tr = $("#druid-"+druid); 
  
  //store hidden fields
  var formatted_archive_fields = format_archive_fields(data.json);
  //alert(formatted_archive_fields);
  $(tr).find('.archive-fields-store') //document_type pull down
    .text(formatted_archive_fields)
    .attr({accession:data.accession, //storing zotero record info needed for edit
	  itemKey:data.itemKey,
	  etag:data.etag}); 
  //alert($(this).html());	    
  //populate metadata fields cell
  var formatted_metadata = format_metadata(data.json);
  //document type pulldown
  $(tr).find('.document-type-pulldown') //document_type pull down
    .val(formatted_metadata.document_type)
    .unbind()
    .change(function(){
	save_metadata($(tr))
	  })
    .show('fast');

  //open text fields	
  $(tr).find('.metadata-textarea')
    .val(formatted_metadata.text)
    .unbind()
    .change(function(){
	save_metadata($(tr))
	  })
    .show('fast');
  //
  var formatted_tags = format_tags(data.json);
  $(tr).find('.tags-textarea')
    .val(formatted_tags)
    .unbind()
    .change(function(){
	save_metadata($(tr));
      }).
    show('fast');
}

function get_pdf_name(druid) {
  var url = "https://saltworks.stanford.edu/assets/"+druid+".pdf";
  return url;
}

function get_pdf_nameDEPRECATED(druid) {
  var salt_user = "salt";
  var salt_pw = "35473d24664035c02d92aba25c94d9c6";
  //return "http://"+salt_user+":"+salt_pw+"\@salt-dev.stanford.edu/assets/"+druid+"/"+druid+".pdf";
  return "http://saltworks.stanford.edu/assets/"+druid+".pdf";
  //https://saltworks.stanford.edu/assets/mz904yp4860.pdf
}

 function save_metadata(tr) {
   //alert("save_data:"+$(tr).attr('id'));
   var druid = $(tr).attr('id').substr(6); // druid
   var accession = $(tr).find('.archive-fields-store').attr('accession'); //accession
   var itemKey = $(tr).find('.archive-fields-store').attr('itemKey'); //itemType
   var etag = $(tr).find('.archive-fields-store').attr('etag'); //etag
   var document_type = $(tr).find('.document-type-pulldown').val(); //document_type
   var metadata = $(tr).find('.metadata-textarea').val(); //main metadata
   var tag_string = $(tr).find('.tags-textarea').val(); //tag string
   var archive_fields = $(tr).find('.archive-fields-store').text(); //archive fields

  //archive fields
  var url = archive_fields.split(/\[URL\]/)[1];
  if (typeof url == 'undefined') {
    alert("[url] tag corrupted");
    return false;
  }
  url = $.trim(url.split(/\[[A-Z_\-\s]*\]/)[0]);

  var callNumber = archive_fields.split(/\[CALL_NUMBER\]/)[1];
  if (typeof callNumber == 'undefined') {
    alert("[callNumber] tag corrupted");
    return false;
  }
  callNumber = $.trim(callNumber.split(/\[[A-Z_\-\s]*\]/)[0]);

  var archiveLocation = archive_fields.split(/\[ARCHIVE_LOCATION\]/)[1];
  if (typeof archiveLocation == 'undefined') {
    alert("[archiveLocation] tag corrupted");
    return false;
  }
  archiveLocation = $.trim(archiveLocation.split(/\[[A-Z_\-\s]*\]/)[0]);

  //lowood fields
  var title = metadata.split(/\[TITLE\]/)[1];
  if (typeof title == 'undefined') {
    alert("[TITLE] tag corrupted");
    return false;
  }
  title = $.trim(title.split(/\[[A-Z_\-\s]*\]/)[0]);

  var originators_string = metadata.split(/\[ORIGINATORS\]/)[1];
  if (typeof originators_string == 'undefined') {
    alert("[ORIGINATORS] tag corrupted");
    return false;
  }
  originators_string = $.trim(originators_string.split(/\[[A-Z_\-\s]*\]/)[0]);
  //count ";", if none, count "\n", if none, split by /,/ else split by /[;\n]/  
  if (originators_string.indexOf(';') < 0 && originators_string.indexOf("\n") < 0) {
    var raw_originators = originators_string.split(/,/);
  } else {
    var raw_originators = originators_string.split(/[;\n]+/);
  }
  var originators_array = [];
  for (var i = 0;i<raw_originators.length;i++) {
    var originator = $.trim(raw_originators[i]);
    if (originator.length == 0) continue;
    originators_array.push(originator);
  }

  var date = metadata.split(/\[DATE\]/)[1];
  if (typeof date == 'undefined') {
    alert("[DATE] tag corrupted");
    return false;
  }
  date = $.trim(date.split(/\[[A-Z_\-\s]*\]/)[0]);
  var document_subtype = metadata.split(/\[DOCUMENT_SUBTYPE\]/)[1];
  if (typeof document_subtype == 'undefined') {
    alert("[DOCUMENT_SUBTYPE] tag corrupted");
    return false;
  }
  document_subtype = $.trim(document_subtype.split(/\[[A-Z_\-\s]*\]/)[0]);
  var containing_work = metadata.split(/\[CONTAINING_WORK\]/)[1];
  if (typeof containing_work == 'undefined') {
    alert("[CONTAINING_WORK] tag corrupted");
    return false;
  }
  containing_work = $.trim(containing_work.split(/\[[A-Z_\-\s]*\]/)[0]);
  var corporate_entity = metadata.split(/\[CORPORATE_ENTITY\]/)[1];
  if (typeof corporate_entity == 'undefined') {
    alert("[CORPPORATE_ENTITY] tag corrupted");
    return false;
  }
  corporate_entity = $.trim(corporate_entity.split(/\[[A-Z_\-\s]*\]/)[0]);
  var number = metadata.split(/\[NUMBER\]/)[1];
  if (typeof number == 'undefined') {
    alert("[NUMBER] tag corrupted");
    return false;
  }
  number = $.trim(number.split(/\[[A-Z_\-\s]*\]/)[0]);
  var extent = metadata.split(/\[EXTENT\]/)[1];
  if (typeof extent == 'undefined') {
    alert("[EXTENT] tag corrupted");
    return false;
  }
  extent = $.trim(extent.split(/\[[A-Z_\-\s]*\]/)[0]);
  var language = metadata.split(/\[LANGUAGE\]/)[1];
  if (typeof language == 'undefined') {
    alert("[LANGUAGE] tag corrupted");
    return false;
  }
  language = $.trim(language.split(/\[[A-Z_\-\s]*\]/)[0]);

  var abstract = metadata.split(/\[ABSTRACT\]/)[1];
  if (typeof abstract == 'undefined') {
    alert("[ABSTRACT] tag corrupted");
    return false;
  }
  abstract = $.trim(abstract.split(/\[[A-Z_\-\s]*\]/)[0]);

  var place = metadata.split(/\[PLACE\]/)[1];
  if (typeof place == 'undefined') {
    alert("[PLACE] tag corrupted");
    return false;
  }
  place = $.trim(place.split(/\[[A-Z_\-\s]*\]/)[0]);

  var raw_tags = tag_string.split(/[,;\n]+/);
  var tags_array = [];

  for (var i = 0;i<raw_tags.length;i++) {
    var tag = $.trim(raw_tags[i]);
    if (tag.length == 0) continue;
    tags_array.push({tag:tag});
  }
    
  var m = {url:url,
	   archiveLocation:archiveLocation,
	   callNumber:callNumber,
	   document_type:document_type,
	   document_subtype:document_subtype,
	   title:title,
	   date:date,
	   originators:originators_array,
	   place:place,
	   corporate_entity:corporate_entity,
	   containing_work:containing_work,
	   number:number,
	   abstract:abstract,
	   extent:extent,
	   language:language,
	   tags:tags_array
  };

  //alert(JSON.stringify(m));
  var write_data = {mode: 'write',
		    itemKey: itemKey,
		    accession: accession,
		    etag: etag,
		    druid: druid,
		    json: m};
  //alert("Write:"+dump(write_data));
  $("#druid-"+druid).find('.metadata-textarea').hide('fast');
  $("#druid-"+druid).find('.tags-textarea').hide('fast');
  $("#druid-"+druid).find('.document-type-pulldown').hide('fast');
  //alert(dump(write_data));
  $.ajax({
    url: "zotero_connect.php", 
	data: write_data,
	dataType: "json",//json
	success: refresh_simtool_row_data 
	/*
	(function(data) {
	    alert("return from Write:"+dump(data));
	    //var this_druid = $(tr).attr('id').substr(6);
	    $.ajax({
	      url: "zotero_connect.php", 
		  data: {mode: 'read', druid: druid}, 
		  dataType: "json",
		  success: refresh_simtool_row_data
		  }
	      ); 
	})
	*/
	});
 }


function save_notes(tr) {
  var max_notes = 8;
  //alert("save_notes:"+$(tr).attr('id'));
  var druid = $(tr).attr('id').substr(6); // druid
  var accession = $(tr).find('.archive-fields-store').attr('accession'); //accession
  var itemKey = $(tr).find('.archive-fields-store').attr('itemKey'); //itemType
  var note_metadata = $(tr).find('.notes-textarea').val(); //notes text
  
  var etags = [];
  var itemKeys = [];
  for (i=0;i<max_notes;i++) {
    var etag = $(tr).find('.notes-etag-store').attr('etag'+i);
    if (typeof etag != 'undefined' && etag != '') etags.push(etag);
    var key = $(tr).find('.notes-etag-store').attr('itemKey'+i);
    if (typeof key != 'undefined' && key != '') itemKeys.push(key);
  }
  
  var notes = note_metadata.split(/\[NOTE\]/);
  note_array = [];
  if (typeof notes != 'undefined') 
    for (var i = 0; i < notes.length; i++) {
      var note = $.trim(notes[i]);
      if (note.length == 0) continue;
      var note_item = {'itemType': 'note',
		       //'tags':[],
		       'note':note};
      note_array.push(note_item);
  }

  if (etags.length > 0 || note_array.length > 0) {
    var write_data = {'mode': 'writenotes',
		      'note-itemKeys': itemKeys,
		      'note-etags': etags,
		      'note-json': note_array,
		      'druid': druid,
		      'itemKey': itemKey,
		      'accession': accession};
    //alert("WRITING..."+dump(write_data));
    $("#druid-"+druid).find('.notes-textarea').hide('fast');
    $.ajax({
      url: "zotero_connect.php",
	  data: write_data,
	  dataType: "json",
	  success: refresh_row_notes
	  });
  }
}

function confirm(msg) {
  //alert(msg);
}

function format_tags(data) {
  if (typeof data.tags != 'undefined') {
    var tag_array = [];
    for (var i=0; i<data.tags.length; i++) {
      tag_array.push(data.tags[i].tag);
    }
    return tag_array.join(', ');
  }
  return '';
}

function format_archive_fields(data) {
  var field_values = [];
  var field_names = [];
  field_names.push("[CALL_NUMBER]");
  field_values.push(data.callNumber);
  field_names.push("[URL]");
  field_values.push(data.url);
  field_names.push("[ARCHIVE_LOCATION]");
  field_values.push(data.archiveLocation);
  var text = "";
  for (var i=0;i<field_names.length;i++) {
    text += field_names[i]+" "+field_values[i]+"\n";
  }
  return text;
}
function format_metadata(data) {
  //alert(dump(data));  
  if (typeof data.error != 'undefined') {
    return {document_type: 'manuscript', text:data.error};
  }
  var field_values = [];
  var field_names = [];
  field_names.push("[TITLE]");
  field_values.push(data.title);
  field_names.push("[DATE]");
  field_values.push(data.date);
  field_names.push("[ORIGINATORS]");
  field_values.push(data.originators);
  field_names.push("[DOCUMENT_SUBTYPE]");
  field_values.push(data.document_subtype);
  field_names.push("[CONTAINING_WORK]");
  field_values.push(data.containing_work);
  field_names.push("[NUMBER]");
  field_values.push(data.number);
  field_names.push("[EXTENT]");
  field_values.push(data.extent);
  field_names.push("[CORPORATE_ENTITY]");
  field_values.push(data.corporate_entity);
  field_names.push("[PLACE]");
  field_values.push(data.place);
  field_names.push("[LANGUAGE]");
  field_values.push(data.language);
  field_names.push("[ABSTRACT]");
  field_values.push(data.abstract);
  var text = "";
  for (var i=0;i<field_names.length;i++) {
    text += field_names[i]+" "+field_values[i]+"\n";
  }
  return {document_type: data.document_type, text:text}
}
