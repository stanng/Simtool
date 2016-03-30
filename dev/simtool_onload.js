$(function() {


	simtool_url = "http://scottvanduyne.com/simtool/Simtool/dev/get_similar_druids.php";
	doc_url = "https://saltworks.stanford.edu/assets/sr470ty5702.jpg";
	doc_url = "https://saltworks.stanford.edu/catalog/druid:yq084bm9203";
	saltworks_stem = "https://saltworks.stanford.edu/catalog";


	$('#submit-button').click(function(){
		$('#results').empty();
		$.ajax({url:simtool_url,
			    data: {howmany:'15',
				mincos:'0.0',
				druid:$('#input-text').val()},
			    dataType:'json',
			    success: load_items
			    });
	    })
	    var q=gup('q');
	//alert(q);
	if (q!="") {
	    $('#input-text').val(q);
	    $('#submit-button').click();
	}


    });


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
    var parent_div = $('#results');
    var cnt = json.length;
    for (var i = 0; i < cnt; i++) {
	(function(i) {
	    var druid = json[i].druid;
	    var img_src = json[i].thumbnail;
	    var cos_sim = json[i].cos_sim;
	    var rating_text = "MIGHT BE RELATED";
	    if (cos_sim == 1.0) rating_text = "ORIGINAL DOCUMENT";
	    else if (cos_sim > 0.9) rating_text = "NEAR DUPLICATE";
	    else if (cos_sim > 0.75) rating_text = "NEAR DUPLICATE";
	    else if (cos_sim > 0.6) rating_text = "HIGHLY RELEVANT";
	    else if (cos_sim > 0.45) rating_text = "VERY SIMILAR";
	    else if (cos_sim > 0.3) rating_text = "SIMILAR";
	    else if (cos_sim > 0.15) rating_text = "POSSIBLY RELATED";
	    rating_text += " ("+(cos_sim.substring(0,4))+")";

	    var item_div = $('<div/>')
		.addClass("simtoolItemDiv"); 
	    var details_div = $('<div/>')
		.addClass("simtoolDetailDiv"); 
	    var img_div = $('<img/>')
		.attr("src",img_src).attr('width',100)
		.addClass("simtoolDetailImg");
	    var rating_div = $('<div/>')
		.attr('id','rating_'+druid)
		.addClass("simtoolRating")
		.text(rating_text);
	    var link_url = saltworks_stem+"/druid:"+druid;
	    var link_div = $('<a/>')
		.addClass("simtoolLink")
		.attr("href",link_url)
		.attr("target","_blank")
		.text(druid)
		.attr('id','title_'+druid);
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
	    
	    details_div
		.append(rating_div)
		.append(link_div)
		.append(authors_div)
		.append(notes_div)
		.append(tags_div)
		.append(subseries_div);
	    item_div
		.append(img_div)
		.append(details_div);
	    parent_div
		.append(item_div)
		
	    $.ajax({url:"getcatpage.php",
			dataType: "json",
			data: {url:druid},
			error:function(){},//alert("error");},
			success: function (j) {
			$("#title_"+druid).text(j.title);
			$("#notes_"+druid).html("<b>Notes:</b> " +j.notes);
			$("#tags_"+druid).html("<b>Tags:</b> " + j.tags);
			$("#authors_"+druid).html("<b>Author:</b> " + j.authors);
			$("#subseries_"+druid).html("<b>Location:</b> "+j.subseries+", DRUID: " + j.druid);

		    }
		});
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
