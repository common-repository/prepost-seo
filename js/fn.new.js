var prepostText = '';
jQuery(function($){
// Configrations
var remoteUrl = 'https://www.prepostseo.com/';

// SEO Scores
var titlePoints = 5;
var slugPoints = 5;
var desPoints = 10;
var keywordsPoints = 5;
var wordsPointsLow = 10;
var wordsPointsMed = 15;
var wordsPointsHigh = 20;
var ratioPoints = 5;
var h1Points = 10;
var h2Points = 5;
var h3Points = 3;
var h4Points = 2;
var intLinksPoints = 5
var extLinksPointsLow = 5;
var extLinksPointsHigh = 10;
var imgsPoints = 5;
var brokenLinksPoints = 10;

// Animation Bar Pointing 
var totalbarval = 0;
var totalYellowbarval = 0;
var totalRedbarval = 0;
var animationRunning = 0;
var activeActions = 0;
var actionsComplete = 0;
var improvements = new Array();
// Configations & Data
var postText = '';
var postContent = '';
var previewHTML = '';
var prepostHtml = '';

var accKey = '';
var accountOK = 1;
var pps_block_editor = false;

function pps_start_checking_main()
{
	if(document.body.classList.contains( 'block-editor-page' )){
		pps_block_editor = true;
	}
	addAnalyzeBtn();
	accKey = $("#ppsMainAccKey").html();
	var adminURL = $("#ppsAdminURL").html();

	$(document).on('click', '#AnalyzePost', function(){

		if (!$('#pps_check_plagiarism').is(":checked")) {
			$('.progress-palg-bar').hide();
		}else{
			$('.progress-palg-bar').show();
		}
		
		$('.match_table tbody').empty();
		$('#docView').empty();
		$('#checkStatus').html('checking content...');
		$("#totalBar").animate({width: '0%'});
		var data = $('form#post').serializeArray();
		var values = {};
		$.each(data, function(i, field) {
			values[field.name] = field.value;
		});
		$.ajax({
			url : adminURL + "post.php",
			type: "post",
			data: values,
			dataType:"JSON",
			success: function(resp){
				console.log(resp);
			}
		});
		
		var plagBtn = $(this);
		if(plagBtn.hasClass("disable")){
			return false;
		}
		$("#sba_results").show();
		$("#statusImg").show();
		changeCstatus("Getting data from text editor...");
		
		// $("#contentDetails").show();
		checkSEO(accKey);										
		
		
		
	});
}
setTimeout(function(){

	( function( wp ) {
		var prepost_seo_panel = document.getElementsByClassName('prepost-seo-panel');
		if(prepost_seo_panel.length > 0){
			if(prepost_seo_panel[0].classList.contains('is-opened') === false)
				wp.data.dispatch( 'core/edit-post' ).toggleEditorPanelOpened( 'perpost-seo-setting-panel/perpost-seo-panel' ); //prepost_seo_panel[0].click();
		
			
				var pps_panel_check_box = ["pps_check_density","pps_check_linksStatus","pps_check_grammar","pps_check_plagiarism"];
				$.each(pps_panel_check_box, function(indx, box){
					$('input#'+box).parent().append('<svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" role="img" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M15.3 5.3l-6.8 6.8-2.8-2.8-1.4 1.4 4.2 4.2 8.2-8.2"></path></svg>');
					$('input#'+box).click();
				});

		}
	} )( window.wp );
		
	pps_start_checking_main();

},1500);


function strip_html_tags(str)
{
	
   if ((str===null) || (str===''))
       return false;
  else
   str = str.toString();
   	str = str.replace(/<p>/g, '');
	str = str.replace(/<\/p>/g, ' &lt;br&gt; ');
	if(str.indexOf('&lt;br&gt;') != false){
		str = str.slice(0, str.length - ' &lt;br&gt; '.length);
	}
	str = str.replace(/(?:\r\n|\r|\n)/g, '');
	str = str.replace(/[\t ]+\</g, "<");
	str = str.replace(/\>[\t ]+\</g, "");
	str = str.replace(/\>[\t ]+$/g, ">");
	str = str.replace(/<[^>]*>/g,' ');
	str = str.replace(/\s\s+/g, ' ');
	str = str.replace(/&nbsp;/g, '');
    return str.trim();
}
	
function addAnalyzeBtn()
{
	// Start Analyze Button
	if(typeof $("#ppsLastPlag").html() !== 'undefined')
	{
		var statusHtml = '<div class="misc-pub-section"><span class="pps-icon-file"></span> Plagiarism: '+$("#ppsLastPlag").html()+' <br><span class="pps-last-date">Checked on: <b>'+$("#ppsLastDate").html()+'</b></span></div>';
		if(pps_block_editor){
			$(".editor-post-trash").parent().parent().append(statusHtml);
		}else{
			$("#misc-publishing-actions").append(statusHtml);
		}
	}
	var htmlBtn = '<div class="sba_btnCheck_box">';
	htmlBtn += '<span class="values" style="display:none;"><input type="checkbox" id="pps_check_contentStatus" checked disabled />Contnet Status</span>'
		+ '<span class="values"><input type="checkbox"  id="pps_check_linksStatus" checked />Links Status</span>'
		+ '<span class="values"><input type="checkbox"  id="pps_check_density" checked />Density</span>'
		+ '<span class="values"><input type="checkbox"  id="pps_check_grammar" checked />Check Grammar</span>'
		+ '<span class="values"><input type="checkbox"  id="pps_check_plagiarism" checked />Check Plagiarism</span>';
	htmlBtn += '<span class="button button-primary button-large sba_btnCheck" id="AnalyzePost">Analyze This Post</span>';
	htmlBtn += '</div>'; 
	$("#major-publishing-actions").append(htmlBtn);
	// End Analyze Button
}




function checkSEO(accountKey)
{

	if($('#pps-seo .notice-error').length > 0)	$('#pps-seo .notice-error').remove();

	$("#content-tmce").click();
	if(!pps_block_editor){
		if(!$("#wp-content-wrap").hasClass("tmce-active"))
		{
			alert("Please Select Visual Display in the text editor..")
			return false;
		}
	}

	postContent = get_tinymce_content();				
	postText = postContent.replace(/(<([^>]+)>)/ig,"").replace(/\s+/g, " ");

	accKey = accountKey;
	
	windowScrolling();
	initiateActions();
	
	doAction(checkStatus);
	$("#contentDetails").show();
	doAction(getPreviewHtml);
	
	activeTab("contentStatus");
	jQuery('.pps-tab-wrap a:nth-child(1)').click();
	doAction(checkSEO_step2);
	
	doAction(checkSEO_checkImgs);
	
	doAction(checkSEO_step3);
	doAction(checkSEO_Grammar);
	doAction(checkSEO_step4);
	doAction(checkSEO_step5);
}

function checkStatus()
{
	activeActions  = 1;
	
	$("#pluginStatus").html("");
	var key = $("#ppsMainAccKey").html();
	var version = $("#ppsPluginVersion").html();
	var plugDir = $("#pluginDir").html();
	var adminURL = $("#ppsAdminURL").html();
	$.ajax({
		url : adminURL + "post.php",
		type: "post",
		data: {"key": key, "v":version, "pps_check_status": 1},
		dataType:"JSON",
		success: function(res){
			if(res.status != "ok")
			{
				var alertHTML = '<span class="alert alert_' + res.status + '">'
				+ res.msg
				+ '</span>';
				$("#pluginStatus").html(alertHTML)
				accountOK = 0;
			} else {
				accountOK = 1;
			}
			activeActions = 0;
		}
	});
	
}


function checkSEO_step2()
{
	if(previewHTML < 1)
	{
		activeActions = 0;
		var infoMsg = '<span class="alert alert_info">SEO Score checker, can only be checked when you will publish your post or save it as draft</span>';
		$("#contentStatus").html(infoMsg);
		return true;
	}
	
	if(accKey.length < 10)
	{
		$(".currentStatus").hide();
		return false;
	}
	changeCstatus("Checking Content...");
	checkPostTitle();
	checkPostUrl();
	checkMetaDescription(previewHTML);
	checkMetaKeywords(previewHTML);
	contentStatus(postText, postContent);
	
	checkHeadings(previewHTML);
	checkLinks(previewHTML);
	
	checkImgs(prepostHtml);
	addPointsLoadBar(totalbarval);
	increaseGreenbar(totalbarval);
	increaseYellowbar(totalYellowbarval);
	increaseRedbar(totalRedbarval);
	
}

function checkSEO_checkImgs()
{
	if(accKey.length < 10)
	{
		return false;
	}
	hideCstatus();
	if (!$('#pps_check_imgsStatus').is(":checked")) 
	{	
		return false;
	}
	activeTab("imgsStatus");
	changeCstatus("Checking Images Status...");
	checkImgsDetails(postContent);
}

function checkSEO_step3()
{
	if(accKey.length < 10) 
	{ 
		return false; 
	}
	hideCstatus();
	if (!$('#pps_check_linksStatus').is(":checked")) 
	{	
		return false;
	}
	activeTab("linksStatus");
	changeCstatus("Checking Broken Links...");
	checkBrokenLinks(prepostHtml);
}

function checkSEO_step4()
{
	if(accKey.length < 10)
	{
		return false;
	}

	if (!$('#pps_check_density').is(":checked")) {
		return false;
	}
	hideCstatus();
	activeTab("densityStatus");
	jQuery('.pps-tab-wrap a:nth-child(2)').click();
	//changeCstatus("Checking Keywords Density...");
	$("#_prepost_defaultOpenr").click();
	// cont();
	
	checkDensity(prepostText);
	checkReadability(prepostText);
}


function checkSEO_Grammar()
{
	if(accKey.length < 10)
	{
		return false;
	}

	if (!$('#pps_check_grammar').is(":checked")) 
	{	
		return false; 
	}
	hideCstatus();
	activeTab("grammarStatus");
	changeCstatus("Checking Grammar Errors...");
	checkGrammar(prepostText, accKey);
}




function checkSEO_step5()
{
	hideCstatus();
	if(accKey.length < 10)
	{
		return false;
	}
	showImprovements();
	
	if (!$('#pps_check_plagiarism').is(":checked")) {
		return false;
	}
	
	
	// activeTab("plagResult");
	jQuery('.pps-tab-wrap a:nth-child(3)').click();
	changeCstatus("Checking Post Plagiarism...");
	sendRequests(prepostText, accKey);
}


function activeTab(name){
	$("#"+name).show();
}





function addStatus(type, title, content)
{
	var html = '<span class="notice ' + type + '"><p>'
               + '<b class="labelN">' + title + ' : </b><br>'
               +  content 
               +  '</p></span>';
	$("#contentStatus").append(html);
}




function convertToSlug(Text)
{
	return Text
		.toLowerCase()
		.replace(/[^\w ]+/g,'')
		.replace(/ +/g,'-')
		;
}

function get_tinymce_content(){

	if(pps_block_editor){
		return wp.data.select( 'core/editor' ).getEditedPostAttribute( 'content' );
	}
	if (jQuery("#wp-content-wrap").hasClass("tmce-active")){
		return tinyMCE.activeEditor.getContent();
	}else{
		return jQuery('#html_text_area_id').val();
	}
}

function getPreviewHtml()
{
	
	if(!pps_block_editor){
		if(!$("#sample-permalink").is(":visible"))
		{
			return false;
		} 
	}
	
	activeActions  = 1;
	changeCstatus("Getting data from Live Preview...");
	var url = pps_block_editor ?  $(".editor-post-preview") : $("#post-preview");
	$.ajax({
		url: url.attr("href"),
		async:true,
		success: function(data)
		{
			previewHTML = data;
			//console.log(previewHTML);
			prepostHtml = $(previewHTML).find('prepostseo').html();
			prepostText = strip_html_tags(prepostHtml);
			activeActions  = 0;
		},
		error: function(request, status, error)
		{
			return false;
			activeActions  = 0;
		}
	});
}


function changeCstatus(val)
{
	$("#statusImg").show();
	$("#cStats").html(val);
}
function hideCstatus()
{
	$("#statusImg").hide();
	$("#cStats").html("");
}


function loadBar(start, end) {
    //var progressbar = $('#progress_bar');

    var value = start;
    var max = end;
    var diff = max - value;
    time = (1000 / diff) * 0.5;
    //alert(time);
    if (end < 1) {
        loading();
        return false;
    }

    var loading = function() {
        value += 1;

        //addValue = progressbar.val(value);

        $('.progress-value').html(value + '%');
        var $ppc = $('.progress-pie-chart'),
            deg = 360 * value / 100;
        if (value > 50) {
            $ppc.addClass('gt-50');
        }
        $('.ppc-progress-fill').css('transform', 'rotate(' + deg + 'deg)');
        $('.ppc-percents .score').html(value);

        if (value == max) {
			animationRunning = 0;
            clearInterval(animate);
        } else {
			animationRunning = 1;
		}
    };

    var animate = setInterval(function() {
        loading();
    }, time);
}


function startBar()
{
	$("#pbar").attr("data-percent", 0);
	totalbarval = 0;
}

function initiateActions()
{
	$("#contentStatus").html("");
	$('.ppc-percents .score').html(0);
	$("#pbar").attr("data-percent", 0);
	$(".currentStatus").show();
	totalYellowbarval = 0;
	totalRedbarval = 0;
	improvements.splice(0,50);
	startBar();
	$("#greenBar").animate({"width" : 0 +"%"}, 100);
	$("#greenBar").attr("start", 0);
	$("#yellowBar").animate({"width" : 0 +"%"}, 100);
	$("#yellowBar").attr("start", 0);
	$("#redBar").animate({"width" : 0 +"%"}, 100);
	$("#redBar").attr("start", 0);
}

function addPointsLoadBar(add)
{
	var start = parseInt($("#pbar").attr("data-percent"));
	//alert(start);
	var end = start+add;
	
	loadBar(start,end);
	$("#pbar").attr("data-percent", end);

	var seo_icon_clr = end <= 30 ? '#ff4e42' : (end > 30 && end) <= 70 ? '#ffa400' : end > 70 ? '#0cce6b' : '#000'; 
	$('.seo-tab-icon').css('color', seo_icon_clr);
	
}

function increaseGreenbar(add)
{
	start = parseInt($("#greenBar").attr("start"));
	end = start + add;
	$("#greenBar").animate({"width" : end +"%"}, 500);
	$("#greenBar").attr("start", end);
}

function increaseYellowbar(add)
{
	start = parseInt($("#yellowBar").attr("start"));
	end = start + add;
	$("#yellowBar").animate({"width" : end +"%"}, 500);
	$("#yellowBar").attr("start", end);
}

function increaseRedbar(add)
{
	start = parseInt($("#redBar").attr("start"));
	end = start + add;
	$("#redBar").animate({"width" : end +"%"}, 500);
	$("#redBar").attr("start", end);
}
function plusGreenBar(points)
{
	totalbarval = totalbarval + parseInt(points);
}
function plusYellowBar(points)
{
	totalYellowbarval = totalYellowbarval + parseInt(points);
}
function plusRedBar(points)
{
	totalRedbarval = totalRedbarval + parseInt(points);
}

function showImprovements()
{
	var htmlImp = '<span class="label label_warning">SEO improvement Suggestions:</span>'
                   + '<p>According to our survery your post needs the following improvements, '
                   + 'Please do the following changes, so that google left no calue to rank down your post.</p>'
                   + '<ul>';
	for(i = 0; i < improvements.length ; i++)
	{
		htmlImp += '<li><span>&#9679;</span>' + improvements[i] + '</li>';
	}
	htmlImp += '</ul>';
	$(".improvements").html(htmlImp);
}


function showAlert(type, msg)
{
	html = '<span class="alert alert_' + type + '">'
            + msg
            + '</span>';
	$("#alerts").html(html);
}




function windowScrolling()
{
	$("#pps-meta-box").removeClass("closed");
	let elemOff = document.querySelector('#pps-meta-box');
	elemOff.scrollIntoView(true, {
		behavior: 'smooth',
	});	
}

function doAction(fn)
{
		var interval = setInterval(function()
		{
			if(activeActions == 0)
			{
				clearInterval(interval);
				fn();
			}
		},100);
}






function checkPostTitle()
{
	
	postTitle = pps_block_editor ? $(".editor-post-title__input").val() : $("#title").val();
	titleLabel = '<span class="label label_tick">Post Title</span>';
	var type = 'success';
	if(postTitle.length < 65 && postTitle.length > 1)
	{
		titleStatus = '<b>Title:</b> ' + postTitle + ' <span class="green">(Good)</span>'
					+ '<br><span class="f12"><b>Length:</b> ' + postTitle.length 
					+ ' character(s) <br> Thats Good to have title length less then 65 characters..</span>';
		plusGreenBar(titlePoints);
	} else if(postTitle.length < 10) {
		plusRedBar(titlePoints);
		type = 'error';
		titleStatus = '<b>Title:</b> ' + postTitle + ' (Not Found)'
					+ '<br><span class="f12"><b>Length:</b> ' + postTitle.length 
					+ ' character(s) <br> Post Title length is very short..</span>';
		improvements.push("Title Length is very short..");
	} else {
		plusRedBar(titlePoints);
		titleStatus = '<b>Title:</b> ' + postTitle + ' <span class="red">(Warning)</span>'
					+ '<br><span class="f12"><b>Length:</b> ' + postTitle.length 
					+ ' character(s) <br> Post title characters length is more than 65, try to short your post title </span>';
		improvements.push("Title Length is more than 65 characters, lower down it.");
		type = 'warning';
	}
	addStatus(type, "Post Title", titleStatus);
}


function checkPostUrl()
{
	var postTitle = pps_block_editor ? $(".editor-post-title__input").val() : $("#title").val();
	var postSlug = pps_block_editor ? convertToSlug($(".editor-post-title__input").html()) : $("#editable-post-name-full").html();
	var percent = 0;
	var totalKeyMatches = 0;
	if(postTitle.length > 10)
	{
		newTitle = postTitle.removeStopWords();
		slug = convertToSlug(newTitle);
		parts = slug.split('-');
		for(i=0; i < parts.length; i++)
		{
			if(postSlug.indexOf(parts[i]) != -1)
			{
				totalKeyMatches++;
			}
		}
		
		if(parts.length > 0)
		{
			percent = (totalKeyMatches/parts.length)*100;
			percent = percent.toFixed(0);
		}
	}
	type = 'success';
	if(percent > 50)
	{
		plusGreenBar(slugPoints);
		slugMsg = '<b>Good</b> <br>'
			+ '<span>Looks like more than 50% of title keywords matched with tha post url</span>';
	}else{
		plusYellowBar(slugPoints);
		type = 'warning';
		slugMsg = '<b>Title Keys words not matched with url</b> <br>'
			+ '<span>Our automated system detect that, keywords in the title are less then 50% matched with you post url</span>';
		
		improvements.push("Try to add keywords in your post url.");
	}
	
	addStatus(type, "Post URL", slugMsg);
}

function checkMetaDescription(data)
{
	desFound = 0;
	
	$(data).filter('meta').each(function()
	{
		if(typeof $(this).attr("name") !== "undefined" && $(this).attr("name").toLowerCase() == "description")
		{
			desFound = 1;
			desContent = $(this).attr("content");
		}
	});
	
	type = 'error';
	desMsg = '<b class="red">Meta Description Not Found</b> <br>'
	+ '<span class="f12">(Meta Description have a very high impect on your page SEO, You must solve this issue)</span>';
	if(desFound == 1)
	{
		if(desContent.length > 160)
		{
			plusYellowBar(desPoints);
			type = 'warning';
			desMsg = 'Meta Description Found <br>'
			+ '<span class="f12 red">(Meta Description characters length is more than 160, '
			+ 'But most search eangines allow meta description less than 160, '
			+ 'try to reduce this length to improve you post SEO)</span>';			
		
			improvements.push("Meta Description length is more than 160 chars. But Google only allow you to add less than 160 chars.");
		} else {
			plusGreenBar(desPoints);
			type = 'success';
			desMsg = '<span class="green">Meta Description Found</span> <br>'
			+ '<span class="f12">(Meta Description have a very high impect on your page SEO)</span>';
		}
	}else {
		plusRedBar(desPoints);
		
		improvements.push("Add Meta Description for this post, because it highly effects you page SEO");
	}
	addStatus(type, "Meta Description", desMsg);
}

function checkMetaKeywords(data)
{
	found = 0;
	$(data).filter('meta').each(function()
	{
		if(typeof $(this).attr("name") !== "undefined" && $(this).attr("name").toLowerCase() == "keywords")
		{
			found = 1;
		}
	});
	
	var type = 'success';
	desMsg = '<span class="green">Meta Keywords Not Found</span> <br>'
	+ '<span class="f12">(According to new Google updates, keywords are no longer important for your website SEO)</span>';
	if(found == 1)
	{
		type = 'warning';
		plusRedBar(keywordsPoints);
		desMsg = '<b class="red">Meta keywords Found !</b> <br>'
		+ '<span class="f12">(According to new Google updates, keywords are no longer important for your website SEO)</span>';			
		improvements.push("Meta Keywords tag is no longer accepted by google, remove it.");
	}else {
		plusGreenBar(keywordsPoints);
	}
	addStatus(type, "Meta Keywords", desMsg);
}

function contentStatus(){  
	var val   = $.trim(prepostText), // Remove spaces from b/e of string
		words = val.replace(/\s+/gi, ' ').split(' ').length, // Count word-splits
		chars = val.length;                                  // Count characters
		if(!chars) words=0;
		var ratio = prepostText.length / postContent.length;
		ratio = ratio.toFixed(2)*100;
	  	
		var wordMsg = '';
		if(words > 500) {
			plusGreenBar(wordsPointsHigh);			
			wordMsg = "Post length is "+words+" Words, No doubt its great.";
		} else if(words > 250) {
			plusGreenBar(15);
			plusYellowBar(5);
			wordMsg = "Post length is "+words+" Words , its good. but you can make it better by increasing it to 450+.";
			improvements.push(wordMsg);
		} else if (words > 50) {
			plusGreenBar(10);
			plusYellowBar(10);
			wordMsg = "Post total words are less than 250, increase post content.";
			improvements.push(wordMsg);
		} else {
			plusRedBar(20);
			wordMsg = "Post total words are less than 50, please improve your post content and try to increase words at least up to 250";
			improvements.push(wordMsg);
		}
		
		if(words > 150)
		{
			var type = 'success';
			wordStatus = '<b>' + words + '</b> Words <span class="green">(Good)</span>';
		} else {
			type = 'warning';
			wordStatus = '<b>' + words + ' Words</b> '
			+ '<br> <span class="f12"> (Content length is very short)</span>';
		}
		
		addStatus(type, "Post Total Words", wordStatus + "<br> " + wordMsg);
		
		
		var type = 'success';
		ratioMsg = ratio + '%';
		if(ratio < 30)
		{
			type = 'warning';
			ratioMsg += '<br><spab class="f12">text to HTML ratio is very low you can improve this ratio by using less html tags</span>';
			improvements.push("Text to html ratio is very low, try to remove some tags or increase content.");
		} else {
			
			totalbarval = totalbarval + parseInt(ratioPoints);
		}
		addStatus(type, "Text to HTML ratio", ratioMsg);
		
}

function checkHeadings(mixedHtml)
{
	var h1Status, h2Status ,h3Status, h4Status;
	var h1s = 0;
	var h2s = 0;
	var h3s = 0;
	var h4s = 0;
	
	$(mixedHtml).find("h1").each(function(){
		h1s++;
	});
	$(mixedHtml).find("h2").each(function(){
		h2s++;
	});
	$(mixedHtml).find("h3").each(function(){
		h3s++;
	});
	$(mixedHtml).find("h4").each(function(){
		h4s++;
	});
	
		var type = 'success';
	if(h1s == 1)
	{
		plusGreenBar(h1Points);
		h1Status = '<b class="green">' + h1s + ' Found</b> <br> <span class="f12"> You have one H1 heading in single page on front end.';
		h1Status += 'it is good, because you must have only 1 h1-heading on one page</span>'; 
	} else if(h1s > 1)
	{
		plusYellowBar(h1Points);
		type = 'warning';
		h1Status = '<b>' + h1s + ' Found</b> <br>  <span class="f12">(You have more than one H1 Headings in single Page)</span>';
	
		improvements.push("On Live Preview, This post page have more than one H1 tags, keep only one H1 tag, remove all the rest.");
	} else {
		plusRedBar(h1Points);
		type = 'error';
		h1Status = '<b>Not Found</b> (Warning) <br>  <span class="f12">(H1 heading is very important to improve your ranking in the seacrh engines..)</span>';
	
		improvements.push("H1 tag not found, on live preview on this post, Add at least One H1 Tag.");
	}
	
	addStatus(type, "H1 Heading", h1Status);	
		
	if(h2s > 0)
	{
		plusGreenBar(h2Points);
		type = 'success';
		h2Status = '<b>' + h2s + ' Found</b>';
	} else {
		plusYellowBar(h2Points);
		type = 'warning';
		h2Status = '<b class="red">Not Found</b>';
		h2Status += '<br> <span class="f12">(H2 Headings can improve your ranking in the search engines. If you can add H2 headings in your articles then that is very good) </span>';
	
		improvements.push("H2 tags can help this post to boost in Search Engines");
	}
	
	addStatus(type, "H2 Headings", h2Status);
	
	if(h3s > 0)
	{
		plusGreenBar(h3Points);
		type = 'success';
		h3Status = '<b class="green">' + h3s + ' Found</b>';
	} else {
		plusYellowBar(h3Points);
		type = 'info';
		h3Status = '<b> Not Found</b>';
		h3Status += '<br> <span class="f12">(H3 Headings can improve your ranking in the search engines. If you can add H3 headings in your articles then that is very good)</span>';
	
		improvements.push("H3 tags can help this post to boost in Search Engines");
	}
	addStatus(type, "H3 Headings", h3Status);
	
	if(h4s > 0)
	{
		plusGreenBar(h4Points);
		type = 'success';
		h4Status = '<b>' + h4s + ' Found</b> <b class="green">(Good)</b>';
	} else {
		plusYellowBar(h4Points);
		type = 'info';
		h4Status = 'Not Found';
	
		improvements.push("H4 tags can help this post to boost in Search Engines");
	}
	
	addStatus(type, "H4 Headings", h4Status);
}
function checkLinks(mixedHtml)
{
	var internal_links = new Array();
		var external_links = new Array();
		var int_dofollow = 0;
		var ext_dofollow = 0;
		var totalLinks = 0;
		var url    = window.location.href;
		var hotname = get_hostname(url);
		
		
		$(mixedHtml).find('a').each(function()
		{
			totalLinks++;
			//var obj = $(this);
			var linkUrl = $(this).attr("href");
			if(linkUrl.length > 6)
			{
				var linkHost = get_hostname(linkUrl);
				if(linkHost && linkHost.toLowerCase() == hotname.toLowerCase())
				{
					internal_links.push(linkUrl);
					if(!$(this).attr("rel") || $(this).attr("rel").toLowerCase() != "nofollow")
					{
						int_dofollow++;
					}
					
				} else {
					external_links.push(linkUrl);
					
					if(!$(this).attr("rel") || $(this).attr("rel").toLowerCase() != "nofollow")
					{
						ext_dofollow++;
					}
				}
			}
			
		});
		
		var int_nofollow = internal_links.length - int_dofollow;
		var ext_nofollow = external_links.length - ext_dofollow;
		
		
		
		if(int_dofollow > 0 && int_dofollow < 5)
		{
			plusGreenBar(intLinksPoints);
		}else {
			plusYellowBar(intLinksPoints);
			improvements.push("Try to linking your blog posts with each other.");
		}
		
		
		if(ext_dofollow < 1)
		{
			if(external_links.length > 0)
			{
				plusGreenBar(extLinksPointsLow);
				improvements.push("Remove External links..");
				
			} else {
				plusGreenBar(extLinksPointsHigh);
			}
		} else {
			plusRedBar(extLinksPointsHigh);
			improvements.push("Make External Do-follow links, nofollow.");
		}
		
		
		if(totalLinks < 1)
		{
			plusGreenBar(brokenLinksPoints);
		}
		
		
		
		internal_links_label = '<span class="label">Internal Links</span>';
		internal_links_msg = 'Not Found';
		if(internal_links.length > 0)
		{
			internal_links_msg = '<b>'+ internal_links.length + ' links found</b>'
			+ '<br> <span class="f12">(doFollow: <b>'+ int_dofollow +'</b> , noFollow: <b>'+ int_nofollow +'</b> )</span>';
		}
		addStatus("info", "Internal Links", internal_links_msg);
		
		
		
		
		external_links_label = '<span class="label">External Links</span>';
		external_links_msg = 'Not Found';
		var type = 'success';
		if(external_links.length > 0)
		{
			external_links_msg = '<b>'+ external_links.length + ' links found</b>'
			+ '<br> <span class="f12">(doFollow: <b>'+ ext_dofollow +'</b> , noFollow: <b>'+ ext_nofollow +'</b> )</span>';
			type = 'warning';
			if(ext_dofollow > 2)
			{
				type = 'error';
				external_links_msg += '<br> <span class="f12">More dofollow external links can down your raning in the search engines.</span>';
			}
		}
		addStatus(type, "External Links", external_links_msg);
}
function checkImgs(mixedHtml)
{
	var imgLabel = '<span class="label">Images</span>';
	var imgStatus = 'No Image Found';
	var missingAlts = 0;
	var totalImgs = $(mixedHtml).find('img').length;
	//console.log(totalImgs);
	if(totalImgs > 0)
	{
	
		imgStatus = '<b>' + totalImgs + ' Found</b>';
		$(mixedHtml).find('img').each(function()
		{
			if(!$(this).attr("alt") || $.trim($(this).attr("alt")).length < 1)
			{
				missingAlts++;
			}
			
		});
		var type = 'success';
		if(missingAlts > 0)
		{
			type = 'warning';
			imgStatus += '<br><span class="f12 red">(' + missingAlts + ' images missing <b>Alt</b> attribute)</span>';
		}else{
			imgStatus += '<br><span class="f12 green">(All image(s) have alt attribute)</span>';
		}
		
	}
	
	if(missingAlts < 1)
	{
		plusGreenBar(imgsPoints);
	} else {
		plusYellowBar(imgsPoints);
		improvements.push("Add Alt attributes to all images");
	}
	
	addStatus(type, "Images", imgStatus);
}



function checkReadability(text)
{
	text = text.replace(/&lt;br&gt; /g, '');
	var adminURL = $("#ppsAdminURL").html();
	var result = true;
	activeActions = 1;
	$('.densityStatsTable').hide();
	$("#readabilityResults").html("<center>Loading: Checking Post Readability.....</center>");
	$.ajax({
		url : adminURL + "post.php",
		type: "post",
		data: {data:text, pps_check_readability : 1},
		dataType:"json",
		async: true, 
		success: function(resp)
		{

			// console.log(resp);
			$("#readabilityResults").html("");
			if(resp.error == "1")
			{
				var htmlIn = '<span class="sec_heading">Post Readability Not Found..</span>';
				$("#readabilityResults").append(htmlIn);
				addStatus('none', "Post Readability", "Result Not Found..");
			}else{

				var obj = resp.result;
				
				$('.densityStatsTable').show();

				var read_s = obj.FleschKincaidReadingEase;
				var read_icon_clr = read_s <= 30 ? '#ff4e42' : (read_s > 30 && read_s) <= 70 ? '#ffa400' : read_s > 70 ? '#0cce6b' : '#000'; 
				$('.readability-tab-icon').css('color', read_icon_clr);

				// #readingLevelsTable
				// $('#flesch_reading_ease_score').html(obj.FleschKincaidReadingEase);
				// $('#flesch_kincaid_grade_level').html(obj.FleschKincaidGradeLevel);
				// $('#coleman_liau_index').html(obj.ColemanLiauIndex);
				// $('#automated_readability_index').html(obj.AutomatedReadabilityIndex);
				// $('#avarage_index').html(obj.avarageIndex);

				// #textStatisticTable
				$('#stat_t_w').html(obj.totalWords);
				$('#stat_k_s_w_r').html(obj.keywords);
				$('#stat_k_t_w_r').html(obj.kwRatio);
				$('#stat_u_w').html(obj.totalWordsUnique);
				$('#stat_u_k').html(obj.keywordsUnique);
				$('#stat_t_c_w_s').html(obj.chars);

				// #agvTable
				$('#avg_c_p_w').html(obj.charsPerWord);
				$('#avg_w_p_s').html(obj.wordsPerSentence);
				$('#avg_s_p_w').html(obj.syllablesPerWord);
				$('#avg_s_p_s').html(obj.syllablesPerSentence);

				//#readingTable
				$('#estimate_reading_time').html(obj.readingTime);
				$('#estimate_speaking_time').html(obj.speakingTime);

				//#lengthTable
				$('#length_total_sentences').html(obj.sentences);
				$('#length_syllables').html(obj.syllables);
				$('#length_longest_sentence').html('<center><b>'+obj.longestSentenceWords+' Words</b></center>\
													<p>'+obj.longestSentence+'</p>');


				var data_pps_ease = $('tr[data-pps-ease]');
				if(data_pps_ease.length > 0 && read_s){
					$.each(data_pps_ease, function(i,tr){
						var row_ease = $(tr).attr('data-pps-ease');
						var row_ease_n = row_ease.split('-');
						if(  read_s <= Number(row_ease_n[0]) && read_s >= Number(row_ease_n[1])){
							// console.log(row_ease);
							$(tr).css('background','#dde9f0');
							$(tr).addClass('pps-ease-here');
						}else{
							$(tr).removeClass('pps-ease-here');
							$(tr).css('background','#fff');
						}
					});
				}
								
			}
		
			result = true;
			activeActions = 0;
		}
	});
	return result;
	
}
function checkDensity(text)
{
	text = text.replace(/&lt;br&gt; /g, '');
	plugDir = $("#pluginDir").html();
	var adminURL = $("#ppsAdminURL").html();
	var result = true;
	activeActions = 1;
	$('#densityTable').hide();
	$("#densityResults").html("<center>Loading: Checking Keywords Density.....</center>");
	$.ajax({
		url : adminURL + "post.php",
		type: "post",
		data: {data:text, pps_check_density : 1},
		dataType:"json",
		async: true, 
		success: function(resp)
		{
			$("#densityResults").html("");
			if(resp.error == 1)
			{
				var htmlIn = '<span class="sec_heading">Keywords Density Not Found..</span>';
				$("#densityResults").append(htmlIn);
				addStatus('none', "Keywords Density", "Keywords Density Not Found..");
			}else{
				
				$('#densityTable').show();
				
				var bbn=''; d1=''; d2=''; d3 = '';
				for(i=0; i < resp.results_1.length; i++){	
					bbn = resp.results_1.length-1 === i ? 'bbn' : '';
					d1 += '<div class="col-md-12 w_tbody text-left '+bbn+'"><div class="col-md-6 col-xs-6 br-right p15">' 
						+ resp.results_1[i]['term'] + '</div><div class="col-md-6 col-xs-6 p15"><span class="density">' 
						+ resp.results_1[i]['density'] + '%</span> </div></div>';
				};
				d1 += '<div class="clear"></div>';
				
				for(i=0; i < resp.results_2.length; i++){	
					bbn = resp.results_2.length-1 === i ? 'bbn' : '';
					d2 += '<div class="col-md-12 w_tbody text-left '+bbn+'"><div class="col-md-6 col-xs-6 br-right p15">' 
						+ resp.results_2[i]['term'] + '</div><div class="col-md-6 col-xs-6 p15"><span class="density">' 
						+ resp.results_2[i]['density'] + '%</span> </div></div>';
				};
				d2 += '<div class="clear"></div>';
				
				for(i=0; i < resp.results_3.length; i++){	
					bbn = resp.results_3.length-1 === i ? 'bbn' : '';
					d3 += '<div class="col-md-12 w_tbody text-left '+bbn+'"><div class="col-md-6 col-xs-6 br-right p15">' 
						+ resp.results_3[i]['term'] + '</div><div class="col-md-6 col-xs-6 p15"><span class="density">' 
						+ resp.results_3[i]['density'] + '%</span> </div></div>';
				};
				d3 += '<div class="clear"></div>';

				jQuery("#keyword_density-tab-1-table").html(d1);
				jQuery("#keyword_density-tab-2-table").html(d2);
				jQuery("#keyword_density-tab-3-table").html(d3);
			}
		
			result = true;
			activeActions = 0;
		}
	});
	return result;
	
}

function checkBrokenLinks(html)
{
	plugDir = $("#pluginDir").html();
	var adminURL = $("#ppsAdminURL").html();
	var linksArray = new Array();
	$(html).find('a').each(function()
	{
		linksArray.push($(this).attr("href"));
	});
	var start = 0;
	var brokenLinks = 0;
	if(linksArray.length > 0)
	{		
		activeActions = 1;
		var htmlLinks = '<table><tr><td width="1000" id="brokenLinks" align="center"><span class="label label_tick">Links Status</span>'
			+': <span id="br_st" class="green"><span id="br_no">0</span> Broken Link(s) Found</span></td></tr>'
			+ '<tr><td width="700"><table class="densityTabel linksTable" width="100%">'
			+ '<tr class="tr_head1"><td width="10%">Sr#</td><td width="80%">Link URL</td><td width="10%">Status</td></tr>';
		
		for(i=0; i < linksArray.length; i++)
		{
			var no = i+1;
			htmlLinks += '<tr><td>' + no 
				+ '</td><td id="linkno_' + no + '">' + linksArray[i] 
				+ '</td><td id="status_' + no + '">---</td></tr>'; 
		}
		
		htmlLinks += '</table></td></tr></table>';
		
		$("#linksStatus").html(htmlLinks);
		
		
		
		function sendLinksReq()
		{
				if(start < linksArray.length)
				{
					var imgLoad = '<img src="' + plugDir + 'imgs/loading3.gif" />';
					$("#status_" + no).html(imgLoad);
					$.ajax({
						url : adminURL + "post.php",
						data: {url:linksArray[start], "pps_check_broken" : 1},
						type: "post",
						success: function(response)
						{
							var no = start+1;
							if (response == "true")
							{
								var imgst = '<img src="' + plugDir + 'imgs/tick.png" />';
								$("#status_" + no).html(imgst);
							} else {
								brokenLinks++;
								var imgst = '<img src="' + plugDir + 'imgs/cross.png" />';
								$("#status_" + no).html(imgst);
								var linkH = '<span class="red">' + linksArray[start] + '</span>';
								$("#linkno_" + no).html(linkH);
								$("#brokenLinks .label").removeClass("label_tick").addClass("label_error");
								$("#br_no").html(brokenLinks);
							}
							
							if(brokenLinks > 0)
							{
								$("#br_st").addClass('red').removeClass('green');
								$("#br_st").show();
							}
							
							start++;
							sendLinksReq();
						}
					});
				} else {
					if(brokenLinks < 1) {
						
						increaseGreenbar(brokenLinksPoints);
						
						var animateNew = setInterval(function(){
						if(animationRunning == 0)
						{
							animationRunning = 1;
							clearInterval(animateNew)
							addPointsLoadBar(brokenLinksPoints);
						}
						}, 100);
					} else {
						increaseRedbar(brokenLinksPoints);
						improvements.push('<span class="red">Broken Links Found</span>, Remove broken links from your post, or its gonna ruined your post SEO.');
					}
					activeActions = 0;
					actionsComplete = 1;
				}
		}
		sendLinksReq();
	} else {
		actionsComplete = 1;
	}
}



function checkGrammar(content, accKey)
{
	activeActions = 1;
	var currentLang = $('#currentLang').text().replace('_','-') || "en-US";
	var adminURL = $("#ppsAdminURL").html();
	content = content.replace(/(<([^>]+)>)/ig,"").replace(/\s+/g, " ").replace(/&lt;br&gt; /g, '');
	$("#grammarStatus").html("<center>Loading: Checking Grammar.....</center>");
	$.ajax({
		url : adminURL + "post.php",
		type: "post",
		data: {data:content, pps_check_grammar : 1, key:accKey, language: currentLang},
		
		async: true, 
		success: function(resp)
		{
			resp = jQuery.parseJSON(resp);
			// console.log(resp);
			grammarHtml = '';
			
			if(resp.error == "0"){

				if(resp.result == 0){
					grammarHtml += '<center><br><big><strong class="green">Great!</strong> No Grammar or Spelling Error Found in your Post</big></center>';
				}else{
					grammarHtml += '<center><br><big><strong class="red">Opps! </strong>'+resp.result+' Grammar or Spelling Error Found in your Post</big><br/>\
					<button type="button" title="'+remoteUrl+'grammar-checker" id="viewGrammarDetails" class="button button-primary button-large" >Check Errors Deatils</button></center>';
				}

			}else if(resp.error == "1"){

				grammarHtml += '<center><br><big><strong class="red">Opps! </strong>'+resp.result+'</big>\
				<button type="button" title="'+remoteUrl+'grammar-checker" id="viewGrammarDetails" class="button button-primary button-large" >Check From Site</button></center>';
				
			}else{
				grammarHtml += '<center><button type="button" title="'+remoteUrl+'grammar-checker" id="viewGrammarDetails" class="button button-primary button-large" >Check From Site</button></center>';
			}
			
			$("#grammarStatus").html(grammarHtml);
			$("#gDForm").append('<input type="text" name="language" value="'+currentLang+'" style="display:none;" /><textarea name="remoteData" style="display:none;">'+content+'</textarea>');
			$("#viewGrammarDetails").click(function(){
				$("#gDForm").submit();
			});
			activeActions = 0;
			actionsComplete = 1;
		}
	});
}


	
function get_hostname(url) {
    var m = url.match(/^http:\/\/[^/]+/);
	if(m)
	{
		return m[0];
	}
    var n = url.match(/^https:\/\/[^/]+/);
	if(n)
	{
		return n[0];
	}
	return null;
}





/// Plagiarism 

function sendRequests(innerText, accKey)
{
	//console.log(innerText);
	var mainSite = remoteUrl;
	var plagBtn = $("#AnalyzePost");
	// $("#plagResult").show();
	
	function doneRequests()
	{
		$("#loadGif").hide();
		plagBtn.removeClass("disable");
		$("#checkStatus").html("COMPLETE");
		$(".currentStatus").hide();
	}
	plagBtn.addClass("disable");
	
	$(".uniqueCount").html(0 + '%');
	$(".plagCount").html(0 + '%');
	$(".inexactTotal").html(0 + '%');
	$(".exactTotal").html(0 + '%');
	$(".exactTotal").attr('data-exact', 0);
	$(".inexactTotal").attr('data-inexact', 0);
	$(".plagCount").attr('data-plug', 0);
	$(".uniqueCount").attr('data-unique', 0);
	moveBar(0, 0);
	$('#docView').html("");
	$("#howUnique").show();
	$("#totalCount").html(0);
	// var totalU = 0;
	$(".resultsBars").html("");
	$("#result-main").show();
	$('.overallBox').hide();
	$('.palOverallResult').hide();
	$("#loadGif").show();
	$("#checkStatus").html("Checking Content...");
	// $("#plagResultsT").show();
	
	innerText = innerText.replace(/"|'|“|”/g, "");
	var roundUnique = 0;
	var isPlagOnce = 0;
	var totalChecked = 0;
	var barWidth=5;
	var plagbar = document.getElementById('totalBar');
	var progWords = prepostText.split(' ').length;
	function setProgress(){
		
		if(barWidth < 90){
			barWidth+= 5;				
			 $("#totalBar").animate({width: barWidth + '%'});
			 $('#totalCount').html(barWidth);
			//plagbar.style.width = ;
		}
     //console.log(bar.style.width);
	}
	if(progWords < 500){
		var setItrval = setInterval(setProgress,500);
	}else if(progWords < 700){
		var setItrval = setInterval(setProgress,1000);
	}else if(progWords < 1000){
		var setItrval = setInterval(setProgress,2000);
	}
	doRequest();
	//var uparts = 100/values.length;
	//uparts = parseFloat(uparts.toFixed(2));
	function doRequest() {

		plugDir = $("#pluginDir").html();
		var adminURL  = $("#ppsAdminURL").html();
		$.ajax({
			type: 'POST',
			url : adminURL + "post.php",
			data : {"query-list" : innerText, "key" : accKey, "pps_check_plag" : 1},
			async:true,
			success: function(response){

				$("#plagResult").show();
				$('.progress-palg-bar').show();
				

				var obj = JSON.parse(response);
				// console.log(obj);
				if(obj.error == "1")
				{
					errorHtml = '<span class="statBox plagSta">'
					+ '<span class="label label_warning">Account Authentication Error</span></span>';
					$(".resultsBars").html(errorHtml);
					$(".progress-palg-bar").hide();
					$("._prepost_tab").hide();
					doneRequests();
					return false;
				}
				if(obj.error == "2")
				{
					errorHtml = obj.data.display1;
					$(".plaglimitresultsBtn").show();
					$(".plaglimitresultsBars").html(errorHtml);
					$(".plaglimitresultsBtn").html(obj.data.display3);
					// $('#plagResultsT').hide();
					$('.overallBox').hide();
					$('.palOverallResult').hide();
					doneRequests();

					$('#manual-check').click(function(){
						$('.plaglimitresultsBars').toggle( "slow" );
					});

					$(".check-site-btn").click(function(){
						$("#checkFromSite").remove();
						var html = '<form action="https://www.prepostseo.com/plagiarism-checker" target="_blank" method="post" id="checkFromSite" style="display:none;"><textarea name="unique-content-data">'+innerText+'</textarea></form>';
						$('body').append(html);
						$("#checkFromSite").submit();
						$("#checkFromSite").remove();
					});

					//htmlIn = obj.data.display2;
					var plagHtml = '<span class="alert alert_warning">'+ obj.data.display2 + '</span>';
					$('#plagalerts').html(plagHtml);
					$(".progress-palg-bar").hide();
					$("._prepost_tab").hide();
					return false;
				}

				$('.overallBox').show();
					
				var perCount = 100 -  obj.paraPercent;
				countTo($(".exactTotal"), obj.plagP - obj.paraPercent);
				countTo($(".inexactTotal"), obj.paraPercent);
				countTo($('.plagCount'), obj.plagP );
				countTo($('.uniqueCount'), obj.uniqueP);

				$(".exactTotal").attr('data-exact', obj.plagP - obj.paraPercent);
				$(".inexactTotal").attr('data-inexact', obj.paraPercent);
				$(".plagCount").attr('data-plug', obj.plagP);
				$(".uniqueCount").attr('data-unique', obj.uniqueP);

				if(obj.paraPercent > 0){
					moveBar(obj.plagP - obj.paraPercent, (obj.plagP - obj.paraPercent) + obj.paraPercent);
				}else{
					moveBar(obj.plagP);
				}

				var plag_icon_clr = perCount >= 30 ? '#ff4e42' : (perCount < 30 && perCount) > 18 ? '#ffa400' : perCount <= 18 ? '#0cce6b' : '#000'; 
				$('.plagiarism-tab-icon').css('color', plag_icon_clr);

				$('.plaglimitresultsBars').hide();
				$('#plagalerts').hide();
				// $('#plagResultsT').show();
				$("._prepost_tab").show();
				
				$('.palOverallResult').show();
				$('#prepost_sources tbody').html(obj.match);
				//console.log(obj.match);
				$(".plaglimitresultsBtn").hide();
				$('#docView').html(obj.docView);
				clearInterval(setItrval);
				$("#totalBar").animate({width: '100%'});
				$('#totalCount').html("100");
				$('.progress-palg-bar').hide();
				$('#reportBox').show();
				document.getElementById("_prepost_defaultOpen").click();

				ppsSaveMeta(obj.plagP);
				// compareResults();
				doneRequests();
				
			}
		}); 
	}
		/*if(values.length > 0){
			doRequest(0);
		}*/

	$('.switchSimilar').on('click', function () {

		var parafase = $(".inexactTotal").attr('data-inexact');
		var unique = $('.uniqueCount').attr('data-unique');
		var plag = $('.plagCount').attr('data-plug');

		// console.log('parafase = ', parafase);
		// console.log('unique = ', unique);
		// console.log('plag = ', plag);

		if($(this).is(':checked')) {
			$(this).attr('checked','true');
			countTo($('.uniqueCount'), parseInt(unique)  );
			countTo($('.plagCount'), parseInt(plag) );
			var perCount = 100 - parseInt(unique) -  parseInt(parafase);
			moveBar( perCount , perCount + parseInt(parafase));
			$('.inexact-match').addClass('active');
		}else{
			$(this).removeAttr('checked');
			var perCount = 100 - parseInt(unique) -  parseInt(parafase);
			countTo($('.plagCount'), parseInt(plag) - parseInt(parafase) );
			countTo($('.uniqueCount'), parseInt(unique) + parseInt(parafase) );
			moveBar(perCount, perCount);
			$('.inexact-match').removeClass('active');
		}
			
	});
	
	function countTo(obj, limit) {
		$({
			countNum: parseInt(obj.text())
		}).animate({
			countNum: limit
		}, {
			duration: 400,
			easing: 'linear',
			step: function () {
				obj.text(Math.floor(this.countNum) + "%");
			},
			complete: function () {
				obj.text(this.countNum + "%");
			}
		});
	}
	function moveBar(percent, totalMatch = false){
		var progress=1311 - (1311 * ((Number(percent) / 100))); 
		var progressSimilar=1311 - (1311 * ((Number(totalMatch) / 100))); 
		$('.st0').animate(
		{'stroke-dashoffset': progress+'px'}, 700);
		$('.st0-similar').animate(
		{'stroke-dashoffset': progressSimilar+'px'}, 700);
	}
	
}

function ppsSaveMeta(data,score)
{
	var adminURL  = $("#ppsAdminURL").html();
	var postId = $("#post_ID").val();
	var seoScore = $('.ppc-percents .score').html();
	var ignoreUrl = $("#wp-admin-bar-view a").attr('href');
	var rView = $('#docView').html();
	$.ajax({
		type: 'POST',
		url : adminURL + "post.php",
		data : {"data" : data,"score":seoScore,"id": postId, "pps_post_meta" : 1,"rView":rView,"ignoreUrl":ignoreUrl,content:prepostText},
		async:true
	});
}


$("#genrateReport").click(function(){
	$("#rReport").remove();
	var html = '<form action="https://www.prepostseo.com/plagiarism-checker-download-report" target="_blank" method="post" id="rReport" style="display:none;">'+
    	'<input type="hidden" name="ignore" id="rIgnore" value="" />'+
        '<input type="hidden" name="unique" id="rUnique" value="0" />'+
        '<input type="hidden" name="plagiarized" id="rPlag" value="0" />'+
        '<textarea name="content" id="rContent"></textarea>'+
		'<textarea name="doc_view" id="rView"></textarea>'+
    '</form>';
	$('body').append(html);
	$("#rUnique").val($("#uniqueCount").val());$("#rPlag").val($("#plagCount").html());
	$("#rContent").val(prepostText);
	$("#rIgnore").val($("#wp-admin-bar-view a").attr('href'));
	$("#rView").val($('#docView').html());/*$("#rMatched").val($('#matches').html());*/
	$("#rReport").submit();
	$("#rReport").remove();
});
$('.genrateReportAllPost').click(function(){
	var html = '<form action="https://www.prepostseo.com/plagiarism-checker-download-report" target="_blank" method="post" id="rReport" style="display:none;">'
	html += $(this).siblings('.details_single_pps').html();
	html += '</form>';
	// console.log(html);
	$('body').append(html);
	$("#rReport").submit();
	$("#rReport").remove();
});

$('#genrateReportSinglePost').click(function(){
	var html = '<form action="https://www.prepostseo.com/plagiarism-checker-download-report" target="_blank" method="post" id="rReport" style="display:none;">'
	html += $('.details_single_pps').html();
	html += '</form>';
	$('body').append(html);
	$("#rReport").submit();
	$("#rReport").remove();
});
});

function resettext(){ var psection = $("#textbox");psection.val('');$("#cc").empty();$("#wc").empty();$("#cc").text('0');$("#wc").text('0');}

// function cont(){
// 	var wc = 0;var count = 0;
// 	count = prepostText;
// 	jQuery("#cc").empty();jQuery("#cc").append(count.length);
// 	if(count == 0){
// 		jQuery("#wc").empty();jQuery("#wc").append(wc);
// 	}else{
// 		jQuery('#wc-res').show();
// 		wc = count.trim().split(/\s+/).length;
// 		jQuery("#wc").empty();jQuery("#wc").append(wc);getwccInfo();



// 	}
// }


function preosttabs(evt, cityName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("_prepost_tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("_prepost_tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(cityName).style.display = "block";
  jQuery(evt.target).addClass("active"); 
  //evt.currentTarget.className += " active";
  
  evt.preventDefault();
}
function readtabs(evt, tabid, content, tab, active = 'active') {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName(content);
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName(tab);
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" "+active, "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabid).style.display = "inline-block";
  jQuery(evt.target).addClass(active); 
  //evt.currentTarget.className += " active";
  
  evt.preventDefault();
}


function pps_accordian_click(){
	var acc = document.getElementsByClassName("pps-collapsible-header");
	var i;
	for (i = 0; i < acc.length; i++) {
	acc[i].addEventListener("click", function() {
		// console.log(this);
		jQuery(this).find("span").toggleClass("dashicons-arrow-up-alt2 dashicons-arrow-down-alt2");
		var panel = this.nextElementSibling;
		if (panel.style.maxHeight) {
			panel.style.maxHeight = null;
			jQuery(panel).removeClass("active"); 
			jQuery(this).removeClass("active"); 
		} else {
			jQuery(panel).addClass("active"); 
			jQuery(this).addClass("active"); 
			panel.style.maxHeight = panel.scrollHeight + "px";
		} 
	});
	}
}

pps_accordian_click();
