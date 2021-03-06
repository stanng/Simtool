simtoolObj = {};
simtoolObj.loaded = false;//replace state instead of pushstate if you are loading for the first time
simtoolObj.popper = false;//do not push the state if you are doing a popstate (DUR!!!!)
simtoolObj.druidCache = {};
simtoolObj.druidListCache = {};
simtoolObj.historyBack = 0;
simtoolObj.historyForward = 0;
$(function() {


	simtool_url = "http://scottvanduyne.com/simtool/Simtool/dev/get_similar_druids.php";
	doc_url = "https://saltworks.stanford.edu/assets/sr470ty5702.jpg";
	doc_url = "https://saltworks.stanford.edu/catalog/druid:yq084bm9203";
	saltworks_stem = "https://saltworks.stanford.edu/catalog";
	exhibit_stem = "https://exhibits.stanford.edu/feigenbaum/catalog/";
	//var druid = gup('q');
	//var ps = {"druid":druid};
	//window.history.replaceState(ps,null,String(window.location).split("?")[0]+"?q="+druid);
	
	$('#back-button').click(function() {
		if (simtoolObj.historyBack > 1) {
		    history.back();
		    simtoolObj.historyBack--;
		    simtoolObj.historyForward++;
		}
		check_arrows();
	    });
	$('#forward-button').click(function() {
		if (simtoolObj.historyForward > 0) {
		    history.forward();
		    simtoolObj.historyForward--;
		    simtoolObj.historyBack++;
		}
		check_arrows();
	    });
	$('#submit-button').click(function(){
		$('#results').empty();
		var druid = $('#input-text').val();
		if (druid in simtoolObj.druidListCache)
		    load_items(simtoolObj.druidListCache[druid]);
		else{
		$.ajax({url:simtool_url,
			    data: {howmany:'15',
				mincos:'0.0',
				druid:$('#input-text').val()},
			    dataType:'json',
			    success: load_items
			    });
		}
		var ps = {"druid":druid};
		//push state
		if (!simtoolObj.popper) {
		    simtoolObj.historyForward=0;
		    simtoolObj.historyBack++;
		    if (!simtoolObj.loaded) 
			window.history.replaceState(ps,null,String(window.location).split("?")[0]+"?q="+druid);
		    else
			window.history.pushState(ps,null,String(window.location).split("?")[0]+"?q="+druid);	
		} else {
		    simtoolObj.popper = false;
		}
		simtoolObj.loaded = true;
		check_arrows();
	    })
	    var q=gup('q');
	//alert(q);
	if (q!="") {
	    $('#input-text').val(q);
	    $('#submit-button').click();
	}

	$(document).on('click',".simtoolLink",function() {
		var druid = $(this).attr("druid");
		var olddruid = gup("q");
		if (druid == olddruid)
		    return;
		//var ps = {"druid":druid};
		//load page
		load_listing(druid);
	    });
	
	window.addEventListener('popstate', function(event) {
		if (event.state == null) return false;
		var obj = event.state;
		var druid = obj.druid;
		simtoolObj.popper = true;
		load_listing(druid);
	    });

    });
function check_arrows() {
    //back arrow
    if (simtoolObj.historyBack <= 1)
	$("#back-button").css("visibility","hidden");
    else
	$("#back-button").css("visibility","visible");
    
    if (simtoolObj.historyForward >= 1)
	$("#forward-button").css("visibility","visible");
    else
	$("#forward-button").css("visibility","hidden");
}
function load_listing(druid) {
    $("#input-text").val(druid);
    $("#submit-button").click();
}
function gup(name) {//from lobo235 -- Thankx!
    name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    var regexS = "[\\?&]"+name+"=([^&#]*)";
    var regex = new RegExp( regexS );
    var results = regex.exec( window.location.href );
    if( results == null )
        return "";
    else
        //return decodeURIComponent(results[1]);
        return results[1];
}


function load_items(json) {
    var druid = $('#input-text').val();
    simtoolObj.druidListCache[druid] = json;
    var parent_div = $('#results');
    var cnt = json.length;
    for (var i = 0; i < cnt; i++) {
	(function(i) {
	    var druid = json[i].druid;
	    var img_src = json[i].thumbnail;
	    var cos_sim = json[i].cos_sim;
	    var rating_text = "MIGHT BE RELATED";
	    if (cos_sim == 1.0) {
		rating_text = "ORIGINAL DOCUMENT";
		rating_color = "red";
		rating_color = "#F44336";
	    } else if (cos_sim > 0.9) {
		rating_text = "NEAR DUPLICATE";
		rating_color = "red";
		rating_color = "#F44336";
	    } else if (cos_sim > 0.75) {
		rating_text = "HIGHLY RELATED";
		rating_color = "red";
		rating_color = "#F44336";
	    } else if (cos_sim > 0.6) {
		rating_text = "HIGHLY RELEVANT";
		rating_color = "orange";
		rating_color = "#FF9800";
	    } else if (cos_sim > 0.45) {
		rating_text = "VERY SIMILAR";
		rating_color = "green";
		rating_color = "#4CAF50";
	    } else if (cos_sim > 0.3) {
		rating_text = "SIMILAR";
		rating_color = "blue";
		rating_color = "#2196F3";
	    } else if (cos_sim > 0.15) {
		rating_text = "POSSIBLY RELATED";
		rating_color = "purple";
		rating_color = "#673AB7";
	    }
	    rating_text += " ("+(cos_sim.substring(0,4))+")";

	    var item_div = $('<div/>')
		.addClass("simtoolItemDiv"); 
	    if (i == 0)
		item_div.addClass("simtoolItemDiv-first");
	    //if (i != 0 && rating_color)
	    item_div.css("border-left","6px solid "+rating_color);
	    var details_div = $('<div/>')
		.addClass("simtoolDetailDiv"); 
	    var img_div = $('<img/>')
		.attr("src",img_src).attr('width',100)
		.addClass("simtoolDetailImg");
	    var rating_div = $('<div/>')
		.attr('id','rating_'+druid)
		.addClass("simtoolRating")
		.text(rating_text);
	    if (rating_color)
		rating_div.css("color",rating_color);
	    //var link_url = saltworks_stem+"/druid:"+druid;
	    var link_url = exhibit_stem+druid;
	    var link_div = $('<a/>')
		//.addClass("simtoolLink")
		.addClass("simtoolSUNLink")
		.attr('id','SUNLink_'+druid)
		.attr("href",link_url)
		.attr("target","_blank")
		.attr('druid',druid)
		.text(druid)
		.attr('id','title_'+druid);
	    //if (i == 0) {
	    //link_div.addClass("simtoolLink-first");
	    //}
	    var authors_div = $('<div/>')
		.attr('id','authors_'+druid)
		.addClass("simtoolAuthors");
	    var subseries_div = $('<div/>')
		.attr('id','subseries_'+druid)
		.addClass("simtoolSubseries");
	    var notes_div = $('<div/>')
		.attr('id','notes_'+druid)
		.addClass("simtoolNotes");
	    var tags_div = $('<div/>')
		.attr('id','tags_'+druid)
		.addClass("simtoolTags");
	    if (i != 0) {
		var SUNLink = $('<div/>')
		    //.addClass("simtoolSUNLink")
		    //.addClass("simtoolLink")
		    .addClass("simtoolLinkDiv")
		    //.attr("href",link_url)
		    //.attr("target","_blank")
		    //.text("View in Stanford Digital Repository")
		    .html("<div><b>Similarity Search:</b> <span class='"+(i == 0 ? "simtoolLink-first" : "")+" simtoolLink' druid='"+druid+"'>"+druid+"</span></div>");
	    } 		
	    
	    details_div
		.append(link_div)
		.append(rating_div)
		.append(authors_div)
		.append(notes_div)
		.append(tags_div)
		.append(subseries_div);
	    if (i != 0) {
		details_div
		    .append(SUNLink);
	    }
	    
	    item_div
		.append(img_div)
		.append(details_div);
	    parent_div
		.append(item_div);
	    
		if (druid in simtoolObj) {
		    var j = simtoolObj[druid];
		    if (j.title == "") {
			item_div.html("<div><b>Removed for Privacy</b></div>");
			item_div.append("<div style='color:"+rating_div.css('color')+"'>"+rating_div.html()+"</div>");
			item_div.append("<div><b>Similarity Search:</b> <span class='simtoolLink' druid='"+druid+"'>"+druid+"</span></div>");
			
			return;
		    }
		    $("#title_"+druid).html(j.title);
		    $("#notes_"+druid).html("<b>Notes:</b> " +j.notes);
		    if (j.tags) $("#tags_"+druid).html("<b>Tags:</b> " + j.tags);
		    else $("#tags_"+druid).remove();
		    $("#authors_"+druid).html("<b>Author:</b> " + j.authors);
		    $("#subseries_"+druid).html("<b>Location:</b> "+j.subseries+", DRUID: " + j.druid);
		}
		else {
		    $.ajax({url:"getcatpage.php",
				dataType: "json",
				data: {url:druid},
				error:function(){},//alert("error");},
				success: function (j) {
				simtoolObj[druid] = j;
				if (j.title == "") {
				    item_div.html("<div><b>Removed for Privacy</b></div>");
				    item_div.append("<div style='color:"+rating_div.css('color')+"'>"+rating_div.html()+"</div>");
				    item_div.append("<div><b>Similarity Search:</b> <span class='simtoolLink' druid='"+druid+"'>"+druid+"</span></div>");
				    item_div.css("font-size","14px");
			
				    return;
				}
				$("#title_"+druid).html(j.title);
				$("#notes_"+druid).html("<b>Notes:</b> " +j.notes);
				if (j.tags) $("#tags_"+druid).html("<b>Tags:</b> " + j.tags);
				else $("#tags_"+druid).remove();
				$("#authors_"+druid).html("<b>Author:</b> " + j.authors);
				$("#subseries_"+druid).html("<b>Location:</b> "+j.subseries+", DRUID: " + j.druid);
			    }
			});
		}
	})(i); //end of enclosure
    } //end of for loop
} //end of function
/*
//add these items from the catalog page ajax results...
<dd class="blacklight-subseries_display">New World Vistas Related Materials1992 -       1995</dd>

<dd class='blacklight-notes_display'>Presentation Manuscript (2008); American Association for Artificial Intelligence;
various paper abstracts/notes

<dd class="blacklight-originator_s">Edward Feigenbaum</dd>

</dl>
*/
