// ------------------------ SETUP and INITIALIZATION ------------------------------//
var S_OVERVIEW = 'nav-overview';
var S_CONTENT = 'nav-content';
var S_PRACTICE = 'nav-practice';
var S_ASSESSMENT = 'nav-assessment';
var S_REVIEW = 4;
var MODE_PREVIEW = 0;
var MODE_INSTANCE = 1;

var USE_OPEN_DATABASE = false;
var USE_LOCAL_STORAGE = true;
var SESSION_TIMEOUT = 1000;

var sectionHashes =  new Array('Overview', 'Content', 'Practice', 'Assessment', 'Review');
var sectionIDs =  new Array('section-overview', 'section-content', 'section-practice', 'section-assessment', 'section-review');

var currentSection = -1;
var currentContentPage = -1;
var currentPracticePage = -1;
var currentAssessmentPage = -1;

var curPage

var curHash;

var prevContentPage = 1;
var prevPracticePage = 1;
var prevAssessmentPage = 1

var baseURL;
var instID;
var lastSessionCheck;
var loginOptions;
var instData;
var lo;
var assessmentQuestions
var viewerMode = MODE_INSTANCE;

$(window).load(function()
{	
	// TODO: testing debug code
	viewerMode = MODE_PREVIEW
	
	baseURL = $(location).attr('href');
	
	$('#navigation').append('<ul id="nav-list"><li><a class="nav-item" id="'+S_OVERVIEW+'" href="#">Ovierview</a></li><li><a class="nav-item" id="'+S_CONTENT+'" href="#">Content</a></li><li><a class="nav-item" id="'+S_PRACTICE+'" href="#">Practice</a></li><li><a class="nav-item" id="'+S_ASSESSMENT+'" href="#">Assessment</a></li></ul>');
	
	$('#nav-list li a').click(onNavSectionLinkClick);
	
	$('#nav-overview')[0].href = baseURL + 'overview/';
	$('#nav-content')[0].href = baseURL + 'page/1/';
	$('#nav-practice')[0].href = baseURL + 'practice/1/';
	$('#nav-assessment')[0].href = baseURL + 'assessment/1/';
	
	
	if(USE_OPEN_DATABASE)
	{
		// lets set up the db and see if the lo we're looking for is already stored
		var db = openDatabase('mydb', '1.0', 'my first database', 2 * 1024 * 1024);
		db.transaction(function (tx)
		{
			tx.executeSql('CREATE TABLE IF NOT EXISTS los (id unique, loJSON)');
		
			tx.executeSql('SELECT * FROM los WHERE id = ?', [loID], function (tx, results)
			{
				var len = results.rows.length, i;
				if(results.rows.length)
				{
					//console.log(results.rows.item(0).loJSON)
					onGetLO(results.rows.item(0).loJSON);
				}
				else
				{
					makeCall('getLO', onGetLO, [loID]);
				}
			});
		});
	}
	if(USE_LOCAL_STORAGE)
	{
		if(localStorage['lo'+loID])
		{
			onGetLO(localStorage['lo'+loID])
		}
		else
		{
			makeCall('getLO', onGetLO, [loID]);
		}	
	}
	

});

// ------------------------ SERVER COMMUNICATION ------------------------------//
function makeCall(method, callback, arguments)
{
	$.ajax(
	{
		url: "http://obo/assets/gateway-json.php/loRepository."+method+"/"+arguments.join("/"),
		context: document.body,
		dataType: 'text',
		success: callback
	});
}
// PLACE RESULTS INTO THE SELECT BOX
function onGetLOMeta(metaLO)
{


}
function onGetSessionValid(result)
{
	lastSessionCheck = new Date().UTC();
	// just go ahead and get the full lo
}
function onGetLO(result)
{
	lo = $.parseJSON(result);
	if(USE_OPEN_DATABASE)
	{
		var db = openDatabase('mydb', '1.0', 'my first database', 2 * 1024 * 1024);
		db.transaction(function (tx)
		{
			tx.executeSql('INSERT INTO los (id, loJSON) VALUES (?, ?)', [lo.loID, result]);
		});
	}
	if(USE_LOCAL_STORAGE)
	{
		localStorage['lo'+lo.loID] = result;
	}
	
	// process the question banks
	processAssessment();
	
	changeSection(S_OVERVIEW);
}

function processAssessment()
{
	// this is only necissary in preview mode to show the multiple questions per index
	assessmentQuestions = new Array();
	var curQIndex = 0;
	
	$(lo.aGroup.kids).each(function(index, page)
	{
		index++;
		if(curQIndex != page.questionIndex || page.questionIndex == 0)
		{
			curQIndex++
			assessmentQuestions.push(new Array());
		}
		assessmentQuestions[curQIndex-1].push(page);
	});
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

// ------------------------ SECTION & PAGE NAVIGATION ------------------------------//

function updateHistory(url)
{
	//history.pushState(null, null, baseURL+url);
}

function changePage(section, page)
{
	console.log('change page ' + page);
	if(section != currentSection)
	{
		changeSection(section);
	}
	var selectedLinkID = ''; // used for assigning 'selected' class the clicked link
	switch(currentSection)
	{
		case S_OVERVIEW:
			break;
		case S_CONTENT:
			selectedLinkID = '#nav-P-' + page;
			$('#content').empty();
			$('#content').append( buildContentPage(page, lo.pages[page-1]) );
			curPage = lo.pages[page-1]
			activateFLVs();
			activateSWFs();
			updateHistory('page/'+page+'/');
			$('#nav-content')[0].href = baseURL+'page/'+page+'/';
			prevContentPage = page;
			break;
		case S_PRACTICE:
			selectedLinkID = '#nav-PQ-' + page;
			$('#content').empty();
			$('#content').append( buildQuestionPage('PQ', page, lo.pGroup.kids[page-1]) );
			curPage = lo.pGroup.kids[page-1]
			activateFLVs();
			activateSWFs();
			updateHistory('practice/'+page+'/');
			$('#nav-practice')[0].href = baseURL+'practiece/'+page+'/';
			prevPracticePage = page
			break;
		case S_ASSESSMENT:
			selectedLinkID = '#nav-AQ-' + page;
			var altIndex = 0;
			var pageRequested = page
			page = parseInt(page);
			currentAssessmentPage = page-1
			// test to see if the page has alternates in it
			if(pageRequested != page)
			{
				// map the alphabetic letter to an index
				altIndex = pageRequested.charCodeAt(pageRequested.length-1) - 96
			}
			
			$('#content').empty();
			$('#content').append( buildQuestionPage('AQ', page, assessmentQuestions[page-1][altIndex]) );
			curPage = assessmentQuestions[page-1][altIndex]
			
			activateFLVs();
			activateSWFs();
			updateHistory('assessment/'+page+'/');
			$('#nav-assessment')[0].href = baseURL+'assessment/'+page+'/';
			prevAssessmentPage = page
			break;
	}
	$('#nav-list ul.subnav-list li a').removeClass('selected'); // reset the class for page links
	$(selectedLinkID).addClass('selected'); // reset the class for page links

}

function changeSection(section)
{
	console.log('change section ' +section);
	$('#content').empty(); // clear previous content
	$('.subnav-list').remove(); // clear previous subnav
	$('#nav-list li a').removeClass('selected'); // reset the class for nav links
	switch(section)
	{
		case S_OVERVIEW:
			showOverviewPage();
			updateHistory('overview/');
			$('#nav-overview').addClass('selected');
			break;
		case S_CONTENT:
			showContentPageNav();
			$('#nav-content').addClass('selected');
			break;
		case S_PRACTICE:
			showPracticePageNav();
			$('#nav-practice').addClass('selected');
			break;
		case S_ASSESSMENT:
			showAssessmentPageNav();
			$('#nav-assessment').addClass('selected');
			break;
	}
	currentSection = section;
	
	// $.history.load(sectionHashes[currentSection]);
}

// ------------------------ BUILDING CONTENT ------------------------------//

function showContentPageNav() 
{
	var pList = $('<ul class="subnav-list content"></ul>');
	$('#nav-content').parent().append(pList);
	
	$(lo.pages).each(function(index, page){
		index++
		var pageHTML = $('<li><a class="subnav-item" id="nav-P-'+index+'" href="'+ baseURL +'page/' + index + '" title="'+ page.title +'">' + index +'</a></li>');
		pList.append(pageHTML);
		pageHTML.children('a').click(onNavPageLinkClick);
	});
}

function onNavSectionLinkClick(event)
{
	event.preventDefault();
	changePage(event.currentTarget.id, 1)
}

function onNavPageLinkClick(event)
{
	event.preventDefault();
	
	// get the page number and section from the id
	pattern = /^nav-(\w*)-(\d*)(\w?)$/gi;
	event.currentTarget.id.match(pattern);
	
	switch(RegExp.$1)
	{
		case 'P':
			changePage(S_CONTENT, RegExp.$2);
			break;
		case 'PQ':
			changePage(S_PRACTICE, RegExp.$2);
			break
		case 'AQ':
			changePage(S_ASSESSMENT, RegExp.$2+RegExp.$3);
			break;
	}
}


function showPracticePageNav() 
{
	var qListHTML = $('<ul class="subnav-list practice"></ul>');
	$('#nav-practice').parent().append(qListHTML)
	
	$(lo.pGroup.kids).each(function(index, page)
	{
		index++;
		var qLink = $('<li><a class="subnav-item" id="nav-PQ-'+index+'" href="'+ baseURL +'practice/' + index + '" title="Practice Question '+index+'">' + index +'</a></li>');
		qListHTML.append(qLink)
		qLink.children('a').click(onNavPageLinkClick);
	});
}

function showAssessmentPageNav() 
{
	var qListHTML = $('<ul class="subnav-list assessment"></ul>');
	$('#nav-assessment').parent().append(qListHTML)

	$(assessmentQuestions).each(function(qIndex, pageGroup)
	{
		qIndex++;
		var qLink = $('<li><a class="subnav-item" id="nav-AQ-'+qIndex+'" href="'+ baseURL +'assessment/' + qIndex + '" title="Assessment Question '+qIndex+'">' + qIndex +'</a></li>');
		qListHTML.append(qLink);
		qLink.children('a').click(onNavPageLinkClick);
		// add nav for preview mode to show alts
		if(viewerMode == MODE_PREVIEW && pageGroup.length > 1 )
		{
			var altListHTML = $('<ul class="subnav-list-alts"></ul>');
			qLink.append(altListHTML)
			$(pageGroup).each(function(altIndex, page)
			{
				// skip the first - its shown right above this
				if(altIndex == 0)
				{
					return true;
				}
				
				var altVersion = String.fromCharCode(altIndex + 96);
				var altLink = $('<li><a class="subnav-item-alt" id="nav-AQ-'+qIndex+altVersion+'" href="'+ baseURL +'assessment/' + qIndex + altVersion+'" title="Assessment Question '+qIndex+' Alternate '+ altVersion+'">' + qIndex + altVersion +'</a></li>');
				altListHTML.append(altLink);
				altLink.children('a').click(onNavPageLinkClick);
			});
		}
	});
}
function showOverviewPage()
{	
	$('#content').append('<h1><span id="title">title</span> <span id="version">version</span></h1> Learn Time: <span id="learn-time">learn time</span> minutes. <h2>Objective:</h2> <span id="objective"></span><h2>Keywords</h2> <span id="key-words">key words</span></p><h2>Pages:</h2> <p>Content Pages: <span id="content-size">content-size</span></p> <p>Practice Questions: <span id="practice-size">practice-size</span></p> <p>Assessment Questions: <span id="assessment-size">assessment-size</span></p>');
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
function buildContentPage(index, page)
{
	console.log('pageID: ' +page.pageID);
	var pageHTML = $('<div id="content-'+index+'" class="page-layout-'+page.layoutID+'"></div>');
	pageHTML.append('<h3>' + page.title+ ' (layout '+page.layoutID+')</h3>'); // add title
	// loop through each page item
	$(page.items).each(function(itemIndex, item)
	{
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
	console.log('questionID: ' +question.questionID);
	
	// init container
	var page = $('<div id="'+baseid+'-'+index+'" class="question-page question-type-'+ question.itemType +'"></div>');
	page.append('<h3>Question ' + index + '</h3>'); // title
	
	// build page components
	$(question.items).each(function(itemIndex, item)
	{
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
	
	// build answer form input
	switch(question.itemType)
	{
		case 'MC':
			page.append(buildMCAnswers(question.questionID, question.answers));
			// listen to answer clicks
			$('.answer-list :input').die('click', answerClicked);
			$('.answer-list :input').live('click', answerClicked);
			
			break;
		case 'Media':
			break;
	}
	return page;
}

function answerClicked(event)
{
	switch(currentSection)
	{
		case S_PRACTICE:
		case S_ASSESSMENT:
			$(curPage.answers).each(function(itemIndex, answer)
			{
				if(answer.answerID == event.currentTarget.value)
				{
					console.log(answer.weight);
				}
			});
			break;
		default:
			break;
		}
}

function buildMCAnswers(questionID, answers)
{
	var answersHTML = $('<ul class="answer-list multiplechoice"></ul>');
	$(answers).each(function(itemIndex, answer)
	{
		var answerText = $.trim(cleanFlashHTML(answer.answer));
		
		// take extra step to remove containing p tags from answer text
		// convert lone <p>blah</p> tags to 'blah'
		pattern = /^<p.*>(.*)<\/p>$/gi;
		answerText = answerText.replace(pattern, '$1');		
		
		answersHTML.append('<li class="answer"><input id="'+answer.answerID+'" type="radio" name="QID-'+questionID+'" value="'+answer.answerID+'"><label for="'+answer.answerID+'">' + answerText + '</label></li>');
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
	
	$('.swf').each(function(index, val)
	{
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
	
	$('.flv').each(function(index, val)
	{
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

// Old learning objects were saved using flash's textfields - which suck at html
function cleanFlashHTML(input)
{	
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
	
	return input;
}
