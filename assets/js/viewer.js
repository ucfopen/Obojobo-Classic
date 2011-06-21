// ------------------------ SETUP and INITIALIZATION ------------------------------//

// Constant Section ids
var S_OVERVIEW = 'nav-overview';
var S_CONTENT = 'nav-content';
var S_PRACTICE = 'nav-practice';
var S_ASSESSMENT = 'nav-assessment';
var S_REVIEW = 4;

// Constant Viewer Mode Flags
var MODE_PREVIEW = 0;
var MODE_INSTANCE = 1;
var viewerMode = MODE_INSTANCE; // default to instance mode

// Constant Local Storage Flags
var USE_OPEN_DATABASE = false;
var USE_LOCAL_STORAGE = true;

var AUTOLOAD_FLASH = true;

var SESSION_TIMEOUT = 1000;

var sectionHashes =  new Array('Overview', 'Content', 'Practice', 'Assessment', 'Review');
var sectionIDs =  new Array('section-overview', 'section-content', 'section-practice', 'section-assessment', 'section-review');

// Keeps track of the currently open section
var currentSection = -1;

// Keeps track of the current page thats open
var curPage

// Keeps track of the current question id
var curQuestionID

// Keeps track of the previously viewed pages for each section so that the user returns to the same page when switching sections
var prevContentPage = 1;
var prevPracticePage = 1;
var prevAssessmentPage = 1

// Keeps track of the base url to build the links from
var baseURL;

// timestamp of the last session check
var lastSessionCheck;

// the loaded learning object
var lo;

// array of assessment questions
var assessmentQuestions

// array of visited pages
var visitedPages = new Array();
var visitedPractice = new Array();
var visitedAssessment = new Array();

// array of selected answers
var questionAnswers = new Object();

$(window).load(function()
{	
	// TODO: testing debug code
	viewerMode = MODE_PREVIEW
	
	baseURL = $(location).attr('href');
	
	$('nav').append('<ul id="nav-list"><li><a class="nav-item" id="'+S_OVERVIEW+'" href="#">Ovierview</a></li><li><a class="nav-item" id="'+S_CONTENT+'" href="#">Content</a></li><li><a class="nav-item" id="'+S_PRACTICE+'" href="#">Practice</a></li><li><a class="nav-item" id="'+S_ASSESSMENT+'" href="#">Assessment</a></li></ul>');
	$('#nav-list li a').click(onNavSectionLinkClick);
	$('#nav-overview')[0].href = baseURL + 'overview/';
	$('#nav-content')[0].href = baseURL + 'page/1/';
	$('#nav-practice')[0].href = baseURL + 'practice/1/';
	$('#nav-assessment')[0].href = baseURL + 'assessment/1/';
	$('#next-page-button').live('click', onNextPressed);
	$('#prev-page-button').live('click', onPrevPressed);
	
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

function onImgLoad(e)
{
	console.log('image loaded')
	console.log(this)
}

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
function onGetLOMeta(metaLO){}

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
	
	$('#lo-title').text(strip(lo.title));
	document.title = strip(lo.title) + ' | Obojobo Learning Object'
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
			if(page == 0 ) page = prevContentPage;
			selectedLinkID = '#nav-P-' + page;
			$('#content').empty();
			$('#content').append( buildContentPage(page, lo.pages[page-1]) );
			$('.pic').load(onImgLoad);
			curPage = lo.pages[page-1]
			activateFLVs();
			activateSWFs();
			updateHistory('page/'+page+'/');
			$('#nav-content')[0].href = baseURL+'page/'+page+'/';
			prevContentPage = page;
			visitedPages[page] = true;
			break;
		case S_PRACTICE:
			if(page == 0 ) page = prevPracticePage;
			selectedLinkID = '#nav-PQ-' + page;
			$('#content').empty();
			$('#content').append( buildQuestionPage('Practice', 'PQ', page, lo.pGroup.kids[page-1]) );
			curPage = lo.pGroup.kids[page-1]
			curQuestionID = lo.pGroup.kids[page-1].questionID
			activateFLVs();
			activateSWFs();
			updateHistory('practice/'+page+'/');
			$('#nav-practice')[0].href = baseURL+'practiece/'+page+'/';
			prevPracticePage = page
			visitedPractice[page] = true
			break;
		case S_ASSESSMENT:
			if(page == 0 ) page = prevAssessmentPage;
			selectedLinkID = '#nav-AQ-' + page;
			var altIndex = 0;
			var pageRequested = page
			page = parseInt(page);
			// test to see if the page has alternates in it
			if(pageRequested != page)
			{
				// map the alphabetic letter to an index
				altIndex = pageRequested.charCodeAt(pageRequested.length-1) - 97
			}
			
			$('#content').empty();
			$('#content').append( buildQuestionPage('Assessment', 'AQ', page, assessmentQuestions[page-1][altIndex]) );
			curPage = assessmentQuestions[page-1][altIndex]
			curQuestionID = assessmentQuestions[page-1][altIndex].questionID
			
			updateHistory('assessment/'+page+'/');
			$('#nav-assessment')[0].href = baseURL+'assessment/'+page+'/';
			prevAssessmentPage = page
			visitedAssessment[page] = true
			break;
	}
	$('#nav-list ul.subnav-list li').removeClass('selected'); // reset the class for page links
	$(selectedLinkID).parent('li').addClass('selected'); // set the current selected
	$(selectedLinkID).parent('li').addClass('visited'); // set the current as visited

}

function changeSection(section)
{
	console.log('change section ' +section);
	$('#content').empty(); // clear previous content
	$('.subnav-list').remove(); // clear previous subnav
	$('#nav-list li').removeClass('selected'); // reset the class for nav links
	switch(section)
	{
		case S_OVERVIEW:
			showOverviewPage();
			updateHistory('overview/');
			break;
		case S_CONTENT:
			showContentPageNav();
			break;
		case S_PRACTICE:
			showPracticePageNav();
			break;
		case S_ASSESSMENT:
			showAssessmentPageNav();
			break;
	}
	$('#'+section).parent('li').addClass('selected');
	currentSection = section;
	
	// $.history.load(sectionHashes[currentSection]);
}

// ------------------------ CLICK LISTENER CALLBACKS ------------------------------//

function onNavSectionLinkClick(event)
{
	event.preventDefault();
	changePage(event.currentTarget.id, 0)
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

function onAnswerRadioClicked(event)
{
	$('.answer-preview').remove();
	$(curPage.answers).each(function(itemIndex, answer)
	{
		if(answer.answerID == event.target.value)
		{
			questionAnswers[currentSection + curQuestionID] = answer.answerID
			var weightCSS = ''
			switch(answer.weight)
			{
				case '100':
				case 100:
					weightCSS = 'answer-preview-correct';
					break;
				case '0':
				case 0:
					weightCSS = 'answer-preview-wrong';
					break;
				
			}
			if(viewerMode == MODE_PREVIEW)
			{
				$(event.target).parent().append('<span class="answer-preview '+ weightCSS +'">'+answer.weight+'%</span>');
			}
			return true;
		}
	});
}

function onNextPressed(event)
{
	event.preventDefault();
	switch(currentSection)
	{
		case S_OVERVIEW:
			changePage(S_CONTENT, 0)
			break;
		case S_CONTENT:
			changePage(S_CONTENT, prevContentPage + 1)
			break;
		
	}
}

function onPrevPressed(event)
{
	event.preventDefault();
	switch(currentSection)
	{

		case S_CONTENT:
			changePage(S_CONTENT, prevContentPage - 1)
			break;
		
	}
}

// ======================== BUILDING CONTENT ======================== //


// ------------------------ Navigation Lists -------------------------//
function showContentPageNav() 
{
	var pList = $('<ul class="subnav-list content"></ul>');
	$('#nav-content').parent().append(pList);
	
	$(lo.pages).each(function(index, page){
		index++
		var pageHTML = $('<li '+(visitedPages[index] == true ? 'class="visited"' : '')+'><a class="subnav-item" id="nav-P-'+index+'" href="'+ baseURL +'page/' + index + '" title="'+ strip(page.title) +'">' + index +'</a></li>');
		pList.append(pageHTML);
		pageHTML.children('a').click(onNavPageLinkClick);
	});
}


function showPracticePageNav() 
{
	var qListHTML = $('<ul class="subnav-list practice"></ul>');
	$('#nav-practice').parent().append(qListHTML)
	
	$(lo.pGroup.kids).each(function(index, page)
	{
		index++;
		var qLink = $('<li '+(visitedPractice[index] == true ? 'class="visited"' : '')+'><a class="subnav-item" id="nav-PQ-'+index+'" href="'+ baseURL +'practice/' + index + '" title="Practice Question '+index+'">' + index +'</a></li>');
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
		var qLink = $('<li '+(visitedAssessment[qIndex] == true ? 'class="visited"' : '')+'><a class="subnav-item" id="nav-AQ-'+qIndex+'" href="'+ baseURL +'assessment/' + qIndex + '" title="Assessment Question '+qIndex+'">' + qIndex +'</a></li>');
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
				
				var altVersion = String.fromCharCode(altIndex + 97);
				var altLink = $('<li><a class="subnav-item-alt" id="nav-AQ-'+qIndex+altVersion+'" href="'+ baseURL +'assessment/' + qIndex + altVersion+'" title="Assessment Question '+qIndex+' Alternate '+ altVersion+'">'+ altVersion +'</a></li>');
				altListHTML.append(altLink);
				altLink.children('a').click(onNavPageLinkClick);
			});
		}
	});
}

// ------------------------ Page Content Displays -------------------------//
function showOverviewPage()
{	
	$('#content').load('/assets/templates/viewer.html #overview-page', onOverviewPageLoaded);
	// $('#content').append('<h1><span id="title">title</span> <span id="version">version</span></h1> Learn Time: <span id="learn-time">learn time</span> minutes. <h2>Objective:</h2> <span id="objective"></span><h2>Keywords</h2> <span id="key-words">key words</span></p><h2>Pages:</h2> <p>Content Pages: <span id="content-size">content-size</span></p> <p>Practice Questions: <span id="practice-size">practice-size</span></p> <p>Assessment Questions: <span id="assessment-size">assessment-size</span></p>');

}

function onOverviewPageLoaded()
{
	$("#overview-blurb-title").text(strip(lo.title));
	$("#title").text(strip(lo.title));
	$("#version").text(lo.version + '.' + lo.subVersion);
	$("#language").text(lo.languageID);
	$("#objective").append(cleanFlashHTML(lo.objective));
	$("#learn-time").text(lo.learnTime + ' Min.');
	$("#key-words").text(lo.keywords.join(", "));
	$("#content-size").text(lo.summary.contentSize + ' Pages');
	$("#practice-size").text(lo.summary.practiceSize + ' Questions');
	$("#assessment-size").text(lo.summary.assessmentSize + ' Questions');	
	
}

function buildContentPage(index, page)
{
	console.log('pageID: ' +page.pageID);
	var pageHTML = $('<div id="content-'+index+'" class="page-layout page-layout-'+page.layoutID+'"></div>');
	pageHTML.append('<h2>' + (page.title.length > 0 ? page.title : 'Page ' + index) + '</h2>'); // add title - defaults to "Page N" if there is no title
	
	var pageItems = new Array();
	
	switch(page.layoutID)
	{
		case '4':
			pageItems[0] = page.items[1];
			pageItems[1] = page.items[0];
			break;
		default:
			pageItems = $(page.items);
			break;
	}
	// loop through each page item
	$(pageItems).each(function(itemIndex, item)
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
	// 
	pageHTML.append('<div id="page-nav"><a id="prev-page-button" href="'+baseURL + 'page/' + (parseInt(index)-1) +'">&laquo; Prev</a><a id="next-page-button" href="#'+baseURL + 'page/' + (parseInt(index)+1) +'">Next &raquo;</a></div>');
	// pageHTML.append('<img src="/assets/images/viewer/next-page-bg.png" width="79" height="40" alt="Next Page Bg">');
	
	return pageHTML;
}

function buildQuestionPage(sectionName, baseid, index, question)
{
	// init container
	var page = $('<div id="'+baseid+'-'+index+'" class="question-page question-type-'+ question.itemType +'"></div>');
	page.append('<h2>'+sectionName+' Question ' + index + ':</h2>'); // title
	var questionPage = $('<div class="question"></div>');
	// question has multiple page items
	if(question.items.length > 1)
	{
		// media left, text right
		if(question.items[0].component == 'MediaView' && question.items[1].component == 'TextArea')
		{
			page.addClass('page-layout-2');
			questionPage.append(formatPageItemMediaView(question.items[0]));
			questionPage.append(formatPageItemTextArea(question.items[1]));
		}
		// text left, media right
		if(question.items[0].component == 'TextArea' && question.items[1].component == 'MediaView')
		{
			page.addClass('page-layout-4');
			questionPage.append(formatPageItemMediaView(question.items[1]));
			questionPage.append(formatPageItemTextArea(question.items[0]));
		}
	}
	// question has a single page item
	else
	{
		switch(question.items[0].component)
		{
			case 'MediaView':
				questionPage.append(formatPageItemMediaView(question.items[0]));
				break;
			case 'TextArea':
				questionPage.append(formatPageItemTextArea(question.items[0]));
				break;
		}
	}
	page.append(questionPage);
	
	
	// build answer form input
	switch(question.itemType)
	{
		case 'MC':
			page.append('<h3>Choose one of the following answers:</h3>');
			page.append(buildMCAnswers(question.questionID, question.answers));
			// listen to answer clicks (resets all previous listeners first)
			$('.answer-list :input').die('click', onAnswerRadioClicked).live('click', onAnswerRadioClicked);
			
			break;
		case 'Media':
			break;
	}
	return page;
}

function buildMCAnswers(questionID, answers)
{
	var answersHTML = $('<ul class="answer-list multiplechoice"></ul>');
	$(answers).each(function(itemIndex, answer)
	{
		var answerText = $.trim(cleanFlashHTML(answer.answer));
		// take extra step to remove containing p tags from answer text
		// convert lone <p>blah</p> tags to 'blah'
		pattern = /^<p.*?>(.*)<\/p>$/gi;
		answerText = answerText.replace(pattern, '$1');		
		
		answersHTML.append('<li class="answer"><input id="'+answer.answerID+'" type="radio" name="QID-'+questionID+'" value="'+answer.answerID+'"><label for="'+answer.answerID+'">' + answerText + '</label></li>');
	});
	return answersHTML;
}

function activateSWFs()
{
	if(AUTOLOAD_FLASH)
	{
		var flashvars = new Object();
	
		var params = new Object();
		params.menu = "false";
		params.allowScriptAccess = "sameDomain";
		params.allowFullScreen = "true";
		params.bgcolor = "#FFFFFF";
		params.align = 't';
		params.salign = 't';
		params.wmode = (viewerMode == MODE_PREVIEW ? 'opaque' : 'gpu');

	
		$('.swf').each(function(index, val)
		{
			var mediaID = val.id.split('media-')[1];	
			$(val).parent('.page-item').css('height', $(val).css('height')).css('width', $(val).css('width'));
			swfobject.embedSWF( "/media/"+mediaID, 'media-'+mediaID, '100%', '100%', "10",  "/assets/flash/expressInstall.swf", flashvars, params);

		});
	}
}

function activateFLVs()
{
	if(AUTOLOAD_FLASH)
	{
		var params = new Object();
		params.menu = "false";
		params.allowScriptAccess = "sameDomain";
		params.allowFullScreen = "true";
		params.bgcolor = "#FFFFFF";
		params.align = 't';
		params.salign = 't';
		params.wmode = 'direct';
		
	
		$('.flv').each(function(index, val)
		{
			var mediaID = val.id.split('media-')[1];
		
			var flashvars = new Object();
			flashvars.file = "/media/"+mediaID+'/video.flv';
			flashvars['controlbar.idlehide'] = true;
			flashvars['controlbar.position'] = 'over';
			flashvars.dock = true;
			$(val).parent('.page-item').css('height', $(val).css('height')).css('width', $(val).css('width'));
			swfobject.embedSWF( "/assets/jwplayer/player.swf", 'media-'+mediaID, '100%', '100%', "10",  "/assets/flash/expressInstall.swf", flashvars, params);
		});
	}
}

function formatPageItemTextArea(pageItem)
{
	var pageItemHTML = $('<div class="page-item text-item"></div>');
	pageItemHTML.append(cleanFlashHTML(pageItem.data));
	return pageItemHTML;
}

function formatPageItemMediaView(pageItem)
{

	return displayMedia(pageItem.media[0]);
}

function displayMedia(mediaItem)
{
	var mediaHTML = $('<div class="page-item media-item"></div>');
	switch(mediaItem.itemType)
	{
		case 'pic':
			mediaHTML.append('<img id="media-'+mediaItem.mediaID+'" class="pic" src="/media/'+ mediaItem.mediaID +'" title="'+mediaItem.title+'" alt="'+ mediaItem.title +'">');
			break;
		case 'swf':
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="swf" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;">SWF '+mediaItem.title+'</div>');
			mediaHTML.children('#media-'+mediaItem.mediaID).load('/assets/templates/viewer.html #swf-alt-text', onFlashAltLoaded);
			break;
		case 'flv':
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="flv" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;">FLV '+mediaItem.title+'</div>');
			mediaHTML.children('#media-'+mediaItem.mediaID).load('/assets/templates/viewer.html #flv-alt-text', onFLVAltLoaded);
			break;
	}
	return mediaHTML;
}

function onFlashAltLoaded()
{
	activateSWFs();
}

function onFLVAltLoaded()
{
	activateFLVs();
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


function strip(html)
{
	return html.replace(/</g,'v').replace(/>/g,'&gt;').replace(/&/g,'&amp;').replace(/\"/g, '');
}

$.fn.imagesLoaded = function(callback){
  var elems = this.filter('img'),
      len   = elems.length;
      
  elems.bind('load',function(){
      if (--len <= 0){ callback.call(elems,this); }
  }).each(function(){
     // cached images don't fire load sometimes, so we reset src.
     if (this.complete || this.complete === undefined){
        var src = this.src;
        // webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
        // data uri bypasses webkit log warning (thx doug jones)
        this.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
        this.src = src;
     }  
  }); 

  return this;
};