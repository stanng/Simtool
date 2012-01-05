// -*- C -*-

$(function(){
    $("#submit-main-druid")
      .click(function () {
	       var druid = $("#main-druid-entry").val();
	       var howmany = 100;  //return 35 druids for now... FIXME
	       __nav("submit",druid);
	       refresh_rows(druid, howmany);
	     });
    
    $("#navigate-back")
      .click(function(){
	       var druid = __nav("back",false);
	       var howmany = 100;
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
	       var howmany = 100;
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
    url: "get_tags.php",
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
var druid = druid.match(/[a-z]{2}\d{3}[a-z]{2}\d{4}/)[0];
$.ajax({
    url: "get_data.php",
	data: {druid: druid, howmany: howmany},
	dataType: "json",
	success: generate_rows
	});
}

function generate_rows(data) {
  $("#data-tbody").html("");
  for (var i=0; i<data.length; i++) {
    var this_druid = data[i].druid;
    var this_pdf = data[i].pdf;
    
    var tr = $("<tr/>");    

    var make_new_center_button = $('<button class="make-new-center" ><img src="reload_icon&48.png" width="16px"></img></button>')
      .click(function(){
	       var druid = $(this)
		 .parent().parent() //a little hacky: FIXME
		 .find(".display-druid")
		 .html();
	       alert(druid);
	       $("#main-druid-entry").val(druid);
	       $("#submit-main-druid").click();
	     });
    
    var pdf_button = $('<td valign="top"> <input type="button" class="show-pdf" value="PDF" /> </td>')
      .click(function (){
	       var druid = $(this)
		 .parent()
		 .find(".display-druid")
		 .html();
	       window.open(get_pdf_name(druid),"_blank");
	     });
    tr.append(pdf_button);
    
    var summary_button = $('<td valign="top"> <div/><input type="button" value="SUM" /> </td>')
      .hover(function () {$("div",this).show()},
	     function () {$("div",this).hide()});
    $("div", summary_button)
      .css({'position':'absolute','width':'300px','z-index':'10','background':'beige'})
      .html(data[i].zotero_key+"--"+data[i].summary)
      .hide();
    tr.append(summary_button);
    
    var td = $('<td valign="top"></td>');
    td.append($('<span class="display-druid" >'+data[i].druid+'</span>'));
    td.append($('<br/>'));
    td.append(pdf_button);
    td.append(summary_button);
    tr.append(td);

    tr.append($('<td valign="top"> <span class="display-doclen" >'+data[i].doclen+'</span></td>'));

    var td = $('<td valign="top"></td>');
    td.append($('<span class="display-cos-sim" >'+Math.round(1000*data[i].cos_sim)/1000+'</span>'));
    td.append($('<br/>'));
    td.append(make_new_center_button);    
    tr.append(td);

    var metadata_field = $('<td valign="top"> <textarea></textarea></td>')
      $("textarea", metadata_field).val(format_metadata(data[i]))
      .change(function(){
		var druid = $(this)
		  .parent() //td
		  .parent() //tr
		  .find(".display-druid")
		  .html();
		//FIXME... save metadata?
		save_metadata(druid, $(this).val());
	      });
    tr.append(metadata_field);

    var tags_field = $('<td valign="top"> <textarea></textarea></td>')
      $("textarea", tags_field).val(data[i].tags)
      .change(function(){
		var druid = $(this)
		  .parent() //td
		  .parent() //tr
		  .find(".display-druid")
		  .html();

		save_tags(druid, $(this).val());
	      });
    tr.append(tags_field);
    
    var notes_field = $('<td valign="top"> <textarea></textarea></td>')
      $("textarea", notes_field).val(data[i].notes)
      .change(function(){
		var druid = $(this)
		  .parent()
		  .parent()
		  .find(".display-druid")
		  .html();
		save_notes(druid, $(this).val());
	      });
    tr.append(notes_field);

    $("#data-tbody").append(tr);
  }
  $("#data-tbody").children("tr:first").css('background','lightgreen');    

  refresh_tags();
  return false;
}

function get_pdf_name(druid) {
  var salt_user = "salt";
  var salt_pw = "35473d24664035c02d92aba25c94d9c6";
  return "http://"+salt_user+":"+salt_pw+"\@salt-dev.stanford.edu/assets/"+druid+"/"+druid+".pdf";
}

function save_metadata(druid, metadata) {
  //lets just get the title first:
  //assume [TITLE] titlwlekn p [PAGES] asdlksd [CORPORATE_ENTITY] sdfgas, etc.
  var title = metadata.split(/\[TITLE\]/)[1];
  title = $.trim(title.split(/\[[A-Z_\-\s]*\]/)[0]);
  var originator = metadata.split(/\[ORIGINATOR\]/)[1];
  originator = $.trim(originator.split(/\[[A-Z_\-\s]*\]/)[0]);
  var date = metadata.split(/\[DATE\]/)[1];
  date = $.trim(date.split(/\[[A-Z_\-\s]*\]/)[0]);
  var document_type = metadata.split(/\[DOCUMENT_TYPE\]/)[1];
  document_type = $.trim(document_type.split(/\[[A-Z_\-\s]*\]/)[0]);
  var document_subtype = metadata.split(/\[DOCUMENT_SUBTYPE\]/)[1];
  document_subtype = $.trim(document_subtype.split(/\[[A-Z_\-\s]*\]/)[0]);
  var containing_work = metadata.split(/\[CONTAINING_WORK\]/)[1];
  containing_work = $.trim(containing_work.split(/\[[A-Z_\-\s]*\]/)[0]);
  var corporate_entity = metadata.split(/\[CORPORATE_ENTITY\]/)[1];
  corporate_entity = $.trim(corporate_entity.split(/\[[A-Z_\-\s]*\]/)[0]);
  var number = metadata.split(/\[NUMBER\]/)[1];
  number = $.trim(number.split(/\[[A-Z_\-\s]*\]/)[0]);
  var extent = metadata.split(/\[EXTENT\]/)[1];
  extent = $.trim(extent.split(/\[[A-Z_\-\s]*\]/)[0]);
  var language = metadata.split(/\[LANGUAGE\]/)[1];
  language = $.trim(language.split(/\[[A-Z_\-\s]*\]/)[0]);
  var abstract = metadata.split(/\[ABSTRACT\]/)[1];
  abstract = $.trim(abstract.split(/\[[A-Z_\-\s]*\]/)[0]);


  /**********
  +  $doclen           = $row_details['textlen'];
  +  $title            = $row_details['title'];
  +  $summary          = $row_details['description'];
  +  $tags             = $row_details['tags'];
  +  $notes            = $row_details['notes'];
  +  $originator       = $row_details['originator'];
  +  $date             = $row_details['date'];
  +  $document_type    = $row_details['document_type'];
  +  $document_subtype = $row_details['document_subtype'];
  +  $containing_work  = $row_details['containing_work'];
  +  $corporate_entity = $row_details['corporate_entity'];
  +  $number           = $row_details['number'];
  +  $extent           = $row_details['extent'];
  +  $language         = $row_details['language'];
  +  $abstract         = $row_details['abstract'];
*********/
  $.ajax({
    url: "set_data.php",
	data: {druid: druid, 
	  title: title,
	  originator: originator,
	  date: date,
	  document_type: document_type,
	  document_subtype: document_subtype,
	  containing_work: containing_work,
	  corporate_entity: corporate_entity,
	  number: number,
	  extent: extent,
	  language: language,
	  abstract: abstract},
	dataType: "text",
	success: confirm
	});
}

function save_tags(druid, tags) {
  $.ajax({
    url: "set_data.php",
	data: {druid: druid, tags: tags},
	dataType: "text",
	success: confirm
	});
}

function save_notes(druid, notes) {
  $.ajax({
    url: "set_data.php",
	data: {druid: druid, notes: notes},
	dataType: "text",
	success: confirm
	});
}

function confirm(msg) {
  //alert(msg);
}

function format_metadata (data) {
  var field_values = [];
  var field_names = [];
  field_names.push("[TITLE]");
  field_values.push(data.title);
  field_names.push("[DOCUMENT_TYPE]");
  field_values.push(data.document_type);
  field_names.push("[DOCUMENT_SUBTYPE]");
  field_values.push(data.document_subtype);
  field_names.push("[ORIGINATOR]");
  field_values.push(data.originator);
  field_names.push("[DATE]");
  field_values.push(data.date);
  field_names.push("[CONTAINING_WORK]");
  field_values.push(data.containing_work);
  field_names.push("[CORPORATE_ENTITY]");
  field_values.push(data.corporate_entity);
  field_names.push("[NUMBER]");
  field_values.push(data.number);
  field_names.push("[EXTENT]");
  field_values.push(data.extent);
  field_names.push("[LANGUAGE]");
  field_values.push(data.language);
  field_names.push("[ABSTRACT]");
  field_values.push(data.abstract);
  var text = "";
  for (var i=0;i<field_names.length;i++) {
    text += field_names[i]+" "+field_values[i]+"\n";
  }
  return text;
}
