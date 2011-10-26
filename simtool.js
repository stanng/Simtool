// -*- C -*-

$(function(){
    $("#submit-main-druid")
      .click(function () {
	       var druid = $("#main-druid-entry").val();
	       var howmany = 35;  //return 35 druids for now... FIXME
	       __nav("submit",druid);
	       refresh_rows(druid, howmany);
	     });
    
    $("#navigate-back")
      .click(function(){
	       var druid = __nav("back",false);
	       var howmany = 35;
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
	       var howmany = 35;
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

function refresh_rows(druid,howmany) {
$.ajax({
    url: "get_data.php",
	data: {druid: druid, howmany: howmany},
	dataType: "json",
	success: generate_rows
	});
}

function generate_rows(data) {
  $("tbody").html("");
  for (i=0; i<data.length; i++) {
    var this_druid = data[i].druid;
    var this_pdf = data[i].pdf;
    
    var tr = $("<tr/>");    
    var make_new_center_button = $('<td valign="top"> <input type="button" class="make-new-center" value="NEW" /> </td>')
      .click(function(){
	       var druid = $(this)
		 .parent()
		 .find(".display-druid")
		 .html();
	       $("#main-druid-entry").val(druid);
	       $("#submit-main-druid").click();
	     });
    tr.append(make_new_center_button);
    
    var pdf_button = $('<td valign="top"> <input type="button" class="show-pdf" value="PDF" /> </td>')
      .click(function (){
	       var druid = $(this)
		 .parent()
		 .find(".display-druid")
		 .html();
	       window.open(get_pdf_name(druid),"_blank");
	     });
    tr.append(pdf_button);
    
    var summary_button = $('<td valign="top"> <div/><input type="button" value="SUMMARY" /> </td>')
      .hover(function () {$("div",this).show()},
	     function () {$("div",this).hide()});
    $("div", summary_button)
      .css({'position':'absolute','width':'300px','z-index':'10','background':'beige'})
      .html(data[i].summary)
      .hide();
    tr.append(summary_button);
    
    tr.append($('<td valign="top"> <span class="display-druid" >'+data[i].druid+'</span></td>'));
    tr.append($('<td valign="top"> <span class="display-doclen" >'+data[i].doclen+'</span></td>'));
    tr.append($('<td valign="top"> <span class="display-cos-sim" >'+data[i].cos_sim+'</span></td>'));
    
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

    $("tbody").append(tr);
  }
  $("tbody").children("tr:first").css('background','lightgreen');    
  return false;
}

function get_pdf_name(druid) {
  var salt_user = "salt";
  var salt_pw = "35473d24664035c02d92aba25c94d9c6";
  return "http://"+salt_user+":"+salt_pw+"\@salt-dev.stanford.edu/assets/"+druid+"/"+druid+".pdf";
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
