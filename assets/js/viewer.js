
// 'constants' for each section
var S_OVERVIEW = 0;
var S_CONTENT = 1;
var S_PRACTICE = 2;
var S_ASSESSMETN = 3;
var S_REVIEW = 4;

var SESSION_TIMEOUT = 1000;

var sectionHashes =  new Array('Overview', 'Content', 'Practice', 'Assessment', 'Review');
var sectionIDs =  new Array('section-overview', 'section-content', 'section-practice', 'section-assessment', 'section-review');

var currentSection = -1;
var currentContentPage = -1;
var currentPracticePage = -1;
var currentAssessmentPage = -1;

var curHash;

var lastSessionCheck;
var loginOptions;
var instData;
var lo;

$(window).load(function()
{
	$.history.init(onURLChange, { unescape: ",/" }); // register URL history plugin callback
});

function changePage(section, page)
{
	console.log('change page ' + page);
	if(section != currentSection)
	{
		changeSection(section);
	}
	switch(currentSection)
	{
		case S_OVERVIEW:
			break;
		case S_CONTENT:
			$('#' + sectionIDs[currentSection]).append( buildContentPage(page, lo.pages[page]) );
			break;
		case S_PRACTICE:
			break;
		case S_ASSESSMETN:
			break;
	}
}

function changeSection(section)
{
	console.log('change section ' +section);
	switch(section)
	{
		case S_OVERVIEW:
			showOverviewPage();
			break;
		case S_CONTENT:
			showContentPageNav();
			break;
		case S_PRACTICE:
			break;
		case S_ASSESSMETN:
			break;
	}
	cleanSection(currentSection); // clean previous 
	currentSection = section;
	$.history.load(sectionHashes[currentSection]);
}

function cleanSection(section)
{
	$('#' + sectionIDs[section]).empty();	
}

function showContentPageNav (argument) 
{
	$(lo.pages).each(function(index, page){
		$('#navigation').append('<a href="#Content-Page' + index + '">Page ' + index +'</a> ');
	});
	
}


function showOverviewPage()
{	
	$('#section-overview').append('<h1><span id="title">title</span> <span id="version">version</span></h1> Learn Time: <span id="learn-time">learn time</span> minutes. <h2>Objective:</h2> <span id="objective"></span><h2>Keywords</h2> <span id="key-words">key words</span></p><h2>Pages:</h2> <p>Content Pages: <span id="content-size">content-size</span></p> <p>Practice Questions: <span id="practice-size">practice-size</span></p> <p>Assessment Questions: <span id="assessment-size">assessment-size</span></p> <a href="#Content">Load Content</a>');
	$("#title").text(lo.title);
	$("#version").text(lo.version + '.' + lo.subVersion);
	$("#language").text(lo.languageID);
	$("#objective").append(cleanFlashHTML(lo.objective));
	$("#learn-time").text(lo.learnTime);
	$("#key-words").text(lo.keywords.join(", "));
	$("#content-size").text(lo.summary.contentSize);
	$("#practice-size").text(lo.summary.practiceSize);
	$("#assessment-size").text(lo.summary.assessmentSize);

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


}

// function onGetLoginOptions(result){}

function onGetSessionValid(result)
{
	lastSessionCheck = new Date().UTC();
	// just go ahead and get the full lo
}

function onGetLO(result)
{
	
	lo = result;
	changeSection(S_OVERVIEW);
	//changeSection(S_OVERVIEW);
	
	return
	
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

function checkSession()
{
	var now = new Date().UTC();
	console.log(now)
	if(lastSessionCheck + SESSION_TIMEOUT < now)
	{
		makeCall('getSessionValid', onGetSessionValid , []);
		return 3;
	}
	else
	{
		// if(
	}
}


// URL navigation callback
function onURLChange(hash)
{
	if(curHash != hash)
	{
		curHash = hash;
		console.log('url change ' + hash)
		if(hash == '')
		{
			if(!lo)
			{
				makeCall('getLO', onGetLO, [loID]);
			}
		}
		else if(hash == 'Overview')
		{
			if(!lo)
			{
				makeCall('getLO', onGetLO, [loID]);
			}
			else
			{
				changeSection(S_OVERVIEW);
			}
		}
		else if(hash == 'Content')
		{
			changePage(S_CONTENT, 0);
		}
		else if(hash == 'Practice')
		{
			//makeCall('getLO', onGetLO, [loID]);
		}
		else if(hash == 'Assessment')
		{
		
		}
		else if(hash == 'Review')
		{
		
		}
	}
}