console.log('wut wut');
obo.model.gotoNextPage();
console.log(obo.model.getSection());

// modules begin:





function onTemplateLoadInitial()
{
	
	
	
	
	
	//@TODO 
	//ghetto stylesheet switcher
	$('#settings-button').click(function () {
		var h = $('#theme-blue').attr('href');
		if(h == '#')
		{
			$('#theme-blue').attr('href', '/assets/css/themes/blue.css');
		}
		else
		{
			$('#theme-blue').attr('href', '#');
		}
	});
}














































































































































// ------------------------ SETUP and INITIALIZATION ------------------------------//

// Constant Section ids
S_OVERVIEW = 'nav-overview';
S_CONTENT = 'nav-content';
S_PRACTICE = 'nav-practice';
S_ASSESSMENT = 'nav-assessment';
S_REVIEW = 4;

// Constant Viewer Mode Flags
var MODE_PREVIEW = 0;
var MODE_INSTANCE = 1;
var viewerMode = MODE_INSTANCE; // default to instance mode

// Constant Local Storage Flags
var USE_OPEN_DATABASE = false;
var USE_LOCAL_STORAGE = true;

var AUTOLOAD_FLASH = true;

var SESSION_TIMEOUT = 1000;

var CUSTOM_PAGE_WIDTH = 1064;
var CUSTOM_PAGE_HEIGHT = 798;

var sectionHashes =  new Array('Overview', 'Content', 'Practice', 'Assessment', 'Review');
var sectionIDs =  new Array('section-overview', 'section-content', 'section-practice', 'section-assessment', 'section-review');

// Keeps track of the currently open section
var currentSection = -1;
var currentSectionIndex = -1;

// Keeps track of the current page thats open
var curPage
var curPageIndex

// flag to keep track if they are taking the assessment.
var inAssessmentQuiz = false;

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

var loID;

$(window).load(function()
{
	obo.controllers.main.init();
}
	
	//@TODO
	//VideoJS.setupAllWhenReady();
	//flowplayer("player", "/assets/flowplayer/flowplayer-3.2.7.swf");
}

// ------------------------ SERVER COMMUNICATION ------------------------------//



// PLACE RESULTS INTO THE SELECT BOX
function onGetLOMeta(metaLO){}

function onGetSessionValid(result)
{
	lastSessionCheck = new Date().UTC();
	// just go ahead and get the full lo
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


function changeSection(section)
{
	
}

// page can be one of the following:
// - a number (1-#number of pages) to show a page
// - the string 'start' for either the overview page (if it exists) or the first page (1)
// - the string 'prevPage' or the number 0 for the last visited page for that section
// - or the string 'final' or a number > #number of pages for the ending page
function changePage(section, page)
{
	console.log('changePage(' + section + ',' + page + ')');
	
	if(section != currentSection)
	{
		// prevent them from changing sections in an assessment
		if(inAssessmentQuiz)
		{
			return;
		}
		else
		{
			changeSection(section);
		}
	}
	
	var selectedLinkID = ''; // used for assigning 'selected' class the clicked link
	switch(currentSection)
	{
		case S_OVERVIEW:
			hideSubnav();
			hideNextPrevNav();
			
			break;
		case S_CONTENT:
			showSubnav();
			
			if(page == 0 || page == 'prevPage')
			{
				page = prevContentPage;
			}
			else if(page == 'start')
			{
				// there is no content overview so take them to the first page
				page = 1;
			}
			
			selectedLinkID = '.nav-P-' + page;
			$('#content').empty();
			
			// requested page is less then the last page:
			if(page != 'final' && page <= lo.pages.length)
			{
				showNextPrevNav();
				
				var pageIndex = page - 1;
				$('#content').append( buildContentPage(page, lo.pages[pageIndex]) );
				curPage = lo.pages[pageIndex];
				curPageIndex = pageIndex;
				
				//$('#next-page-button').removeClass('hide');
			}
			// else the requested page is beyond the last page - show the final content page
			else
			{
				
			}
			
			break;
		case S_PRACTICE:
			if(page == 0 || page == 'prevPage')
			{
				page = prevPracticePage;
			}
			
			// show the practice overview if the requested page is 0 and no practice pages have been seen
			if(page == 'start' && visitedPractice.length == 0)
			{
				hideSubnav();
				hideNextPrevNav();
				
				$('#content').load('/assets/templates/viewer.html #practice-overview', onTemplateLoadPracticeOverview);
				curPageIndex = -1
			}
			else 
			{
				showSubnav();
				
				if(page == 'start')
				{
					page = 1;
				}
				
				// requested page is less then the last page, show the question:
				if(page != 'final' && page <= lo.pGroup.kids.length)
				{
					showNextPrevNav();
					
					var pageIndex = page - 1;
					$('.subnav-list').show();
					$('#next-page-button').removeClass('hide');
					selectedLinkID = '.nav-PQ-' + page;
					$('#content').empty();
					$('#content').append( buildQuestionPage('Practice', 'PQ', page, lo.pGroup.kids[pageIndex]) );
					curPage = lo.pGroup.kids[pageIndex];
					curPageIndex = pageIndex;
					curQuestionID = lo.pGroup.kids[pageIndex].questionID;
					activateFLVs();
					activateSWFs();
					updateHistory('practice/'+page+'/');
					$('#nav-practice')[0].href = baseURL+'practiece/'+page+'/';
					visitedPractice[pageIndex] = true;
					$('#next-page-button').removeClass('hide');
				}
				// show the final page
				else
				{
					hideNextPrevNav();
					
					// check the number of visited questions vs the number of questions
					if(visitedPractice.join('').split('true').length - 1 == lo.pGroup.kids.length)
					{
						// all practice questions seen
						$('#content').load('/assets/templates/viewer.html #template-final-practice-page-complete');
					}
					else
					{
						// some practice questions missed
						$('#content').load('/assets/templates/viewer.html #template-final-practice-page-incomplete', onFinalContentPageLoaded);
					}
					curPageIndex = lo.pGroup.kids.length;
					//$('#next-page-button').addClass('hide')
				}
			}
			
			prevPracticePage = page;
			
			break;
		case S_ASSESSMENT:
			// if a page is requested but they are not in assessment take them to the overview page
			if(page == 'start' || !inAssessmentQuiz)
			{
				// @TODO - we want to send them to the final page if they've already taken assessment
				hideSubnav();
				hideNextPrevNav();
				
				$('#content').load('/assets/templates/viewer.html #assessment-overview', onTemplateLoadAssessmentOverview);
				
				curPageIndex = -1;
			}
			// requested page is less then the last page
			else if(page <= assessmentQuestions.length)
			{
				showSubnav();
				showNextPrevNav();
				
				if(page == 0 ) page = prevAssessmentPage;
				selectedLinkID = '.nav-AQ-' + page;
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
				curPageIndex = page-1
				curQuestionID = assessmentQuestions[page-1][altIndex].questionID
			
				updateHistory('assessment/'+page+'/');
				$('#nav-assessment')[0].href = baseURL+'assessment/'+page+'/';
				prevAssessmentPage = page
				visitedAssessment[page] = true
				$('.subnav-list').removeClass('hide')
			}
			// requested page is beyond the last page - show the final assessment page
			else
			{
				
				
				// @TODO - when we have a score page this should be more robust
				// ask them to submit the assessment
				$('<div id="my-dialog" title="Ready to submit?"<p>If you are ready click "Submit".</p><button onclick="submitAssessment()">Submit</button>').dialog({
					modal: true
				});
				
				curPageIndex = assessmentQuestions.length;
			}
			break;
	}
	$('#nav-list ul.subnav-list li').removeClass('selected'); // reset the class for page links
	$(selectedLinkID).parent('li').addClass('selected'); // set the current selected
	$(selectedLinkID).parent('li').addClass('visited'); // set the current as visited
}



function startAssessment()
{
	inAssessmentQuiz = true;
	$('#nav-overview').parent().addClass('lockedout');
	$('#nav-content').parent().addClass('lockedout');
	$('#nav-practice').parent().addClass('lockedout');
	changePage(S_ASSESSMENT, 1);
}


function submitAssessment()
{
	$("#my-dialog").dialog('destroy');
	
	hideSubnav();
	hideNextPrevNav();
	
	inAssessmentQuiz = false;
	$('li').removeClass('lockedout');
	$('#content').load('/assets/templates/viewer.html #template-final-assessment-page-complete');
}

function onTemplateLoadPracticeOverview(event)
{
	$('.icon-dynamic-background').text(lo.pGroup.kids.length).next().prepend(lo.pGroup.kids.length)
}


function onTemplateLoadAssessmentOverview(event)
{
	// set the dynamic icons
	$('.icon-dynamic-background:eq(0)').text(assessmentQuestions.length).next().prepend(assessmentQuestions.length) // number of questions
	// TODO: add actual attempt count here instead of "3"
	$('.assessment-attempt-count').prepend(3) // number of assessments remaining
	
	var showMissingPractice = false
	var showMissingPages = false
	
	// determine which practice pages weren't seen
	var practiceMissed = lo.pGroup.kids.length - ( /* count trues */ visitedPractice.join('').split('true').length - 1 ) 
	showMissingPractice =  practiceMissed > 0 
	
	// determine which content pages weren't seen
	var contentMissed = lo.pages.length - ( /* count trues */ visitedPages.join('').split('true').length - 1 ) 
	showMissingPages =  contentMissed > 0 
	
	// Hide everything
	if(!showMissingPractice && !showMissingPages)
	{
		$('.assessment-missed-section').remove()
	}
	// just hide one or the other
	else
	{
		// Note this order is important, if you remove the 0 first, index 1 will become 0, booo
		if(!showMissingPractice)
		{
			$('.icon-missed-count:eq(1)').parent().parent().remove()
		}
		else
		{
			$('.icon-missed-count:eq(1)').text(practiceMissed)
		}
		if(!showMissingPages)
		{
			$('.icon-missed-count:eq(0)').parent().parent().remove()
		}
		else
		{
			$('.icon-missed-count:eq(0)').text(contentMissed)
		}
	}

	
	

	
}



// ------------------------ CLICK LISTENER CALLBACKS ------------------------------//



function onAnswerRadioClicked(event)
{
	$('.answer-preview').remove();
	$('.answer-feedback').hide();
	
	$(curPage.answers).each(function(itemIndex, answer)
	{
		if(answer.answerID == event.target.value)
		{
			if(viewerMode == MODE_INSTANCE)
			{
				makeCall('trackSubmitQuestion', onTrackSubmitQuestion, [lo.viewID, getCurrentQGroup().qGroupID, curPage.questionID, answer.answerID]);
			}
			
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
			//@TODO: If we are in preview mode or we are in practice then
			//@TODO: Handle no feedback
			var ansLi = $(event.target).parent();
			ansLi.children('.answer-feedback').show();
			if(viewerMode == MODE_PREVIEW)
			{
				ansLi.append('<span class="answer-preview '+ weightCSS +'">'+answer.weight+'%</span>');
			}
			return true;
		}
	});
}

function onTrackSubmitQuestion(event)
{
	//
}

//@TODO
/*
function onNextPressed(event)
{
	event.preventDefault();
	
	switch(currentSection)
	{
		case S_OVERVIEW:
			changePage(S_CONTENT, 0)
			break;
		case S_CONTENT:
			if(curPageIndex + 1 > lo.pages.length)
			{
				changePage(S_PRACTICE, 0)
			}
			else
			{
				changePage(S_CONTENT, curPageIndex + 2)
			}
			break;
		case S_PRACTICE:
			if(curPageIndex + 2 > lo.pGroup.kids.length)
			{
				changePage(S_ASSESSMENT, 0)
			}
			else
			{
				changePage(S_PRACTICE, curPageIndex + 2)
			}
			
			break;
		case S_ASSESSMENT:
			if(curPageIndex + 2 > assessmentQuestions.length)
			{
				//@TODO
				changePage(S_ASSESSMENT, 50);
			}
			else
			{
				changePage(S_ASSESSMENT, curPageIndex + 2);
			}
			break;
		
	}
}*/

function onFinishSectionPressed(event)
{
	changePage(currentSection, 'final');
}

function onPrevPressed(event)
{
	event.preventDefault();
	
	curPageIndex--;
	
	if(curPageIndex == -1)
	{
		switch(currentSection)
		{
			case S_OVERVIEW:
				break;
			case S_CONTENT:
				changePage(S_OVERVIEW, 'start');
				break;
			case S_PRACTICE:
				changePage(S_CONTENT, 'final');
				break;
			case S_ASSESSMENT:
				changePage(S_PRACTICE, 'final');
		}
	}
	else
	{
		changePage(currentSection, curPageIndex + 1);
	}
}

function onNextPressed(event)
{
	console.log('onNextPressed ' + currentSection + "," + curPageIndex);
	console.log('curPageIndex = ' + curPageIndex);
	
	event.preventDefault();
	
	switch(currentSection)
	{
		case S_OVERVIEW:
			// jump to the content section
			changePage(S_CONTENT, 'start');
			break;
		case S_CONTENT:
			if(curPageIndex + 1 > lo.pages.length)
			{
				// if we are at the end of content jump to practice
				changePage(S_PRACTICE, 'start');
			}
			else
			{
				changePage(S_CONTENT, curPageIndex + 2); // + 2 since the second param is 1-indexed not 0-indexed
			}
			break;
		case S_PRACTICE:
			if(curPageIndex + 1 > lo.pGroup.kids.length)
			{
				// if we are at the end of practice jump to assessment
				changePage(S_ASSESSMENT, 'start')
			}
			else
			{
				changePage(S_PRACTICE, curPageIndex + 2)
			}
			break;
		case S_ASSESSMENT:
			changePage(S_ASSESSMENT, curPageIndex + 2);
			/*
			if(curPageIndex + 1 >= assessmentQuestions.length)
			{
				// @TODO
				//changePage(S_ASSESSMENT, 50);
				
			}
			else
			{
				
			}*/
			break;
		
	}
}


// ======================== BUILDING CONTENT ======================== //


// ------------------------ Navigation Lists -------------------------//


// ------------------------ Page Content Displays -------------------------//




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












function onFlashAltLoaded()
{
	activateSWFs();
}

function onFLVAltLoaded()
{
	activateFLVs();
}



function getCurrentQGroup()
{
	switch(currentSection)
	{
		case S_PRACTICE:
			return lo.pGroup;
		case S_ASSESSMENT:
			return lo.aGroup;
	}
	
	return null;
}

//@TODO;
$(document).ready(function() {
	doit();
	//$.fancybox({content:'2lol'});
});


//@TODO
function doit()
{
	//Init fancybox since loading the template "erased" the automatic fancybox init.
	$.fancybox.init();
	
	$('.custom-layout-page').live('click', function(event) {
		/*
		console.log(event);
		$.fancybox.init();
		console.log($(event.currentTarget)[0].src);
		console.log('<img src="' + $(event.currentTarget)[0].src + '">');
		var src = $(event.currentTarget)[0].src;
		var mediaID = src.substring(src.lastIndexOf('/'));
		console.log(mediaID);
		console.log('<img src="/media' + mediaID + '">');
		//$.fancybox({content:$('<img src="/media' + mediaID + '">')});
		console.log($($(event.currentTarget)[0]).clone()[0]);
		//$.fancybox({content:$($(event.currentTarget)[0])});*/
		$.fancybox.init();
		$.fancybox({
			content: $('.custom-layout-page').html()
		});
		$.fancybox.resize();
		$.fancybox.center();
	});
}