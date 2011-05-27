$(window).load(function()
{
	$.history.init(onURLChange, { unescape: ",/" }); // register URL history plugin callback
});

// URL navigation callback
function onURLChange(hash){
	if(hash == '')
	{		
		makeCall('getLOMeta', onGetLOMeta, [loID]);
		makeCall('getLoginOptions', onGetLoginOptions , []);
		makeCall('getSessionValid', onGetSessionValidInit, []);
	}
	else if(hash == 'Content')
	{
		//makeCall('getLO', onGetLO, [loID]);
	}
}

function makeCall(method, callback, arguments)
{
	console.log(method);
	$.ajax({
		url: "http://obo/assets/gateway-json.php/loRepository."+method+"/"+arguments.join("/"),
		context: document.body,
		dataType: 'json',
		success: callback
	});		
}


// PLACE RESULTS INTO THE SELECT BOX
function onGetLOMeta(metaLO)
{
	// console.log(metaLO);
	$("#title").text(metaLO.title);
	$("#version").text(metaLO.version + '.' + metaLO.subVersion);
	$("#language").text(metaLO.languageID);
	$("#objective").append(cleanFlashHTML(metaLO.objective));
	$("#learn-time").text(metaLO.learnTime);
	$("#key-words").text(metaLO.keywords.join(", "));
	$("#content-size").text(metaLO.summary.contentSize);
	$("#practice-size").text(metaLO.summary.practiceSize);
	$("#assessment-size").text(metaLO.summary.assessmentSize);
}

function onGetLoginOptions(result){}

function onGetSessionValidInit(result)
{
	// just go ahead and get the full lo
	makeCall('getLO', onGetLO, [loID]);
}

function onGetLO(result)
{
	$(result.pages).each(function(index, page){
		$('#section-content').append( buildContentPage(index, page) );
	});
	
	$(result.pGroup.kids).each(function(index, question){
		$('#section-practice').append(buildQuestionPage('practice', index, question));
	});
	
	$(result.aGroup.kids).each(function(index, question){
		$('#section-assessment').append(buildQuestionPage('assessment', index, question));
	});
	
	
	activateSWFs();
	activateFLVs();
}

function buildContentPage(index, page)
{
	var pageHTML = $('<div id="content-'+index+'" class="page-layout-'+page.layoutID+'"></div>');
	pageHTML.append('<h3>' + page.title+ ' (layout '+page.layoutID+')</h3>'); // add title
	// loop through each page item
	$(page.items).each(function(itemIndex, item){
		switch(item.component)
		{
			case 'MediaView':
				pageHTML.append(formatPageItemMediaView(item));
				break;
			case 'TextArea':
				pageHTML.append(formatPageItemTextArea(item));
				break;
		}
		
	});
	
	return pageHTML;
}

function buildQuestionPage(baseid, index, question)
{
	var page = $('<div id="'+baseid+'-'+index+'" class="question-page"></div>');
	page.append('<h3>Question ' + index + '</h3>');
	
	$(question.items).each(function(itemIndex, item){
		switch(item.component)
		{
			case 'MediaView':
				page.append(formatPageItemMediaView(item));
				break;
			case 'TextArea':
				page.append(formatPageItemTextArea(item));
				break;
		}
	});
	
	switch(question.itemType)
	{
		case 'MC':
			page.append(buildMCAnswers(question.questionID, question.answers));
			break;
		case 'Media':
			break;
		
	}
		
	return page;
}

function buildMCAnswers(questionID, answers)
{
	var answersHTML = $('<div class="answers multiplechoice"></div>');
	$(answers).each(function(itemIndex, answer){
		var myID = 'Q'+questionID+'A'+answer.answerID;
		answersHTML.append('<div class="answer"><input id="'+myID+'" type="radio" name="QID'+questionID+'" value="'+answer.answerID+'"><label for="'+myID+'">' + cleanFlashHTML(answer.answer) + '</label></div>');
	});
	return answersHTML;
}


function activateSWFs()
{
	var flashvars = new Object();
	
	var params = new Object();
	params.menu = "false";
	params.allowScriptAccess = "sameDomain";
	params.allowFullScreen = "true";
	params.bgcolor = "#869ca7";
	
	$('.swf').each(function(index, val){
		var mediaID = val.id.split('media-')[1];
		
		swfobject.embedSWF( "/media/"+mediaID, 'media-'+mediaID, parseInt($(val).css('width')), parseInt($(val).css('height')), "10",  "/assets/flash/expressInstall.swf", flashvars, params);
	});
}

function activateFLVs()
{
	var params = new Object();
	params.menu = "false";
	params.allowScriptAccess = "sameDomain";
	params.allowFullScreen = "true";
	params.bgcolor = "#869ca7";
	
	$('.flv').each(function(index, val){
		var mediaID = val.id.split('media-')[1];
		
		var flashvars = new Object();
		flashvars.file = "/media/"+mediaID+'/video.flv';
		flashvars.dock = true;
		
		swfobject.embedSWF( "/assets/jwplayer/player.swf", 'media-'+mediaID, parseInt($(val).css('width')), parseInt($(val).css('height')), "10",  "/assets/flash/expressInstall.swf", flashvars, params);
	});
}

function formatPageItemTextArea(pageItem)
{
	var pageItemHTML = $('<div class="page-item"></div>');
	pageItemHTML.append(cleanFlashHTML(pageItem.data));
	return pageItemHTML;
}

function formatPageItemMediaView(pageItem)
{
	var pageItemHTML = $('<div class="page-item"></div>');
	pageItemHTML.append(displayMedia(pageItem.media[0]));
	return pageItemHTML;
}

function displayMedia(mediaItem)
{
	var mediaHTML = $('<div class="mediaItem"></div>');
	switch(mediaItem.itemType)
	{
		case 'pic':
			mediaHTML.append('<img id="media-'+mediaItem.mediaID+'" class="pic" src="/media/'+ mediaItem.mediaID +'" title="'+mediaItem.title+'" alt="'+ mediaItem.title +'">');
			break;
		case 'swf':
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="swf" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;">SWF '+mediaItem.title+'</div>');
			break;
		case 'flv':
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="flv" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;background-color:#ccc;">FLV '+mediaItem.title+'</div>');
			break;
		
	}
	return mediaHTML
}

function cleanFlashHTML(input)
{
	// console.log(input);
	
	// get rid of all the textformat tags
	var pattern = /<\/?textformat\s?.*?>/gi;
	input = input.replace(pattern, "");

	// combine <p><font>...</font></p> tags to just <p></p>
	pattern = /<p\s?(.*?)><font.*?(?:FACE="(\w+)").*?(?:SIZE="(\d+)").*?(?:COLOR="(#\d+)").*?>/gi;
	// input = input.replace(pattern, '<p style="font-family:$2;font-size:$3px;color:$4;">');
	input = input.replace(pattern, '<p>');

	pattern = /<\/font><\/p>/gi;
	input = input.replace(pattern, "</p>");

	// convert lone <font>...</font> tags to spans
	pattern = /<font.*?(?:KERNING="\d+")?.*?(?:FACE="(\w+)")?.*?(?:SIZE="(\d+)")?.*?(?:COLOR="(#\d+)")?.*?>/gi;
	// input = input.replace(pattern, '<span style="font-family:$1;font-size:$2px;color:$3;">');
	input = input.replace(pattern, '<span>');

	pattern = /<\/font>/gi;
	input = input.replace(pattern, "</span>");

	// find empty tags keeping space in them
	pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
	input = input.replace(pattern, "$2");
	
	pattern = /<(\w+)>(\s*?)<\/\1>/gi;
	input = input.replace(pattern, "$2");

	// remove any previously added ul tags
	pattern = /<\/?ul>/gi;
	input = input.replace(pattern, "");
	
	// add <ul></ul> arround list items
	pattern = /<LI>([\s\S]*?)<\/LI>/gi;
	input = input.replace(pattern, "<ul><li>$1</li></ul>");
	
	// kill extra </ul><ul> that are back to back - this will make proper lists
	pattern = /<\/ul><ul>/gi;
	input = input.replace(pattern, "");
	
	// console.log(input);

	return input;
}


var currentAnchor = null;
//Function which chek if there are anchor changes, if there are, sends the ajax petition
function checkAnchor(){
	//Check if it has changes
	if(currentAnchor != document.location.hash){
		currentAnchor = document.location.hash;
		//if there is not anchor, the loads the default section
		if(!currentAnchor)
			query = "section=home";
		else
		{
			//Creates the  string callback. This converts the url URL/#main&id=2 in URL/?section=main&id=2
			var splits = currentAnchor.substring(1).split('&');
			//Get the section
			var section = splits[0];
			delete splits[0];
			//Create the params string
			var params = splits.join('&');
			var query = "section=" + section + params;
		}
		//Send the petition
		$.get("callbacks.php",query, function(data){
			$("#content").html(data);
		});
	}
}
