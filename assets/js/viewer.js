/*window.obo = {};
obo.viewer = function()
{
	// @private
	var myPrivate = 'fuck';
	var myPrivateMethod = function()
	{
		console.log('lol');
		console.log(myPrivate);
		//console.log(myPublic);
		console.log(this.myPublic); // this doesn't work!
	}
	
	console.log(myPrivate);
	
	myPrivateMethod();
	
	//console.log(myPublic);
	//myPublicMethod();
	
	return {
		abc: 12,
		myPublic: "sup",
		myShit: '12',
		myPublicMethod: function()
		{
			console.log('pub f');
			console.log(myPrivate); // reference private variables this way
			console.log(this.myPublic); // reference public variables this way
		}
	};
}();

App.Controllers.Main

obo.remote.getLO()

console.log(obo.viewer.abc);
obo.viewer.myPublicMethod();
*/
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
	// TODO: testing debug code
	viewerMode = MODE_PREVIEW
	baseURL = $(location).attr('href');
	
	// TODO: do this differently
	// Get the loid from the url variable
	//loID = getURLParam('loid');
	
	// load the main template's children into the page
	$('body').load('/assets/templates/viewer.html #template-main > *', onTemplateLoadInitial);
});

function onTemplateLoadInitial()
{
	// Set up all the navigation Links
	$('#nav-list li a').click(onNavSectionLinkClick); // universal listener for nav list links
	
	// set the href's so the mouse-over and default click funcionality do what we want
	$('#nav-overview')[0].href = baseURL + 'overview/';
	$('#nav-content')[0].href = baseURL + 'page/1/';
	$('#nav-practice')[0].href = baseURL + 'practice/start/';
	$('#nav-assessment')[0].href = baseURL + 'assessment/start/';
	$('#prev-page-button').click(onPrevPressed);
	$('#next-page-button').click(onNextPressed);
	$('.next-section-button').live('click', onNextPressed);
	$('#start-assessment-button').live('click', startAssessment);
	//@TODO
	$('#finish-section-button').live('click', onFinishSectionPressed);
	
	//OML tips:
	$('.oml').poshytip({
		className: 'tip-twitter',
		showTimeout: 0,
		alignTo: 'target',
		alignX: 'center',
		offsetY: 4,
		slide: false,
		showAniDuration: 100,
		hideAniDuration: 100,
		liveEvents: true,
		followCursor: false
	});
	$('.subnav-item').poshytip({
		className: 'tip-twitter',
		showTimeout: 0,
		alignTo: 'target',
		alignX: 'center',
		offsetY: 4,
		slide: false,
		fade: false,
		liveEvents: true,
		followCursor: false
	});
	
	
	// lets set up the db and see if the lo we're looking for is already stored
	if(USE_OPEN_DATABASE)
	{
		
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
	// Check local storage for the LO
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
	//@TODO
	//VideoJS.setupAllWhenReady();
	//flowplayer("player", "/assets/flowplayer/flowplayer-3.2.7.swf");
}

// ------------------------ SERVER COMMUNICATION ------------------------------//
function makeCall(method, callback, arguments)
{
	console.log('makeCall: ' + method + ', arguments: ' + arguments);
	
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

function onFinalContentPageLoaded(event)
{
	var missedPages = $('.subnav-list > li:not(.visited)')
	missedPages.clone().appendTo('.missed-pages-list')
	$('.missed-pages-list a').click(onNavPageLinkClick);
}

function changeSection(section)
{
	$('#content').empty(); // clear previous content
	$('.subnav-list').remove(); // clear previous subnav
	$('#nav-list li').removeClass('selected'); // reset the class for nav links
	switch(section)
	{
		case S_OVERVIEW:
			//$('#next-page-button').addClass('hide')
			currentSectionIndex = 0
			showOverviewPage();
			hideSubnav();
			hideNextPrevNav();
			updateHistory('overview/');
			break;
		case S_CONTENT:
			currentSectionIndex = 1
			makeContentPageNav();
			break;
		case S_PRACTICE:
			currentSectionIndex = 2
			makePracticePageNav();
			break;
		case S_ASSESSMENT:
			currentSectionIndex = 3
			makeAssessmentPageNav();
			break;
	}
	$('#'+section).parent('li').addClass('selected');
	currentSection = section;
	
	
	// $.history.load(sectionHashes[currentSection]);
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
				activateFLVs();
				activateSWFs();
				updateHistory('page/'+page+'/');
				$('#nav-content')[0].href = baseURL+'page/'+page+'/';
				visitedPages[pageIndex] = true;
				//$('#next-page-button').removeClass('hide');
			}
			// else the requested page is beyond the last page - show the final content page
			else
			{
				hideNextPrevNav();
				
				// check the number of visited pages vs the number of pages
				if(visitedPages.join('').split('true').length - 1 == lo.pages.length)
				{
					// all content pages seen
					$('#content').load('/assets/templates/viewer.html #template-final-content-page-complete');
				}
				else
				{
					// some content pages missed
					$('#content').load('/assets/templates/viewer.html #template-final-content-page-incomplete', onFinalContentPageLoaded);
				}
				curPageIndex = lo.pages.length;
				//$('#next-page-button').addClass('hide')
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

function showSubnav()
{
	$('.subnav-list').show();
	$('#finish-section-button').show();
}

function hideSubnav()
{
	$('.subnav-list').hide();
	$('#finish-section-button').hide();
}

function showNextPrevNav()
{
	$('#page-navigation').show();
}

function hideNextPrevNav()
{
	$('#page-navigation').hide();
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

function onNavSectionLinkClick(event)
{
	event.preventDefault();
	changePage(event.currentTarget.id, 'start');
}

function onNavPageLinkClick(event)
{
	event.preventDefault();

	// get the page number and section from the id	
	var pattern = /.*nav-(\w{1,2})-(\d{1,2})([a-zA-Z]*).*/gi; // pattern matching stuff like 'nav-P-2' for content page 2
	
	// check for a matching class
	if($(event.target).attr('class').match(pattern))
	{
		switch(RegExp.$1)
		{
			case 'P':
				changePage(S_CONTENT, parseInt(RegExp.$2));
				break;
			case 'PQ':
				changePage(S_PRACTICE, parseInt(RegExp.$2));
				break
			case 'AQ':
				changePage(S_ASSESSMENT, RegExp.$2+RegExp.$3);
				break;
		}
		
	}

}

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
function makeContentPageNav() 
{
	var pList = $('<ul class="subnav-list content"></ul>');
	$('#nav-content').parent().append(pList);
	
	$(lo.pages).each(function(index, page){
		index++
		var pageHTML = $('<li '+(visitedPages[index-1] == true ? 'class="visited"' : '')+'><a class="subnav-item nav-P-'+index+'"  href="'+ baseURL +'page/' + index + '" title="'+ strip(page.title) +'">' + index +'</a></li>');
		pList.append(pageHTML);
		pageHTML.children('a').click(onNavPageLinkClick);
	});	
	
}


function makePracticePageNav() 
{
	var qListHTML = $('<ul class="subnav-list practice"></ul>');
	$('#nav-practice').parent().append(qListHTML)
	
	$(lo.pGroup.kids).each(function(index, page)
	{
		index++;
		var qLink = $('<li '+(visitedPractice[index-1] == true ? 'class="visited"' : '')+'><a class="subnav-item nav-PQ-'+index+'" href="'+ baseURL +'practice/' + index + '" title="Practice Question '+index+'">' + index +'</a></li>');
		qListHTML.append(qLink)
		qLink.children('a').click(onNavPageLinkClick);
	});
}

function makeAssessmentPageNav() 
{
	var qListHTML = $('<ul class="subnav-list assessment"></ul>');
	$('#nav-assessment').parent().append(qListHTML)

	$(assessmentQuestions).each(function(qIndex, pageGroup)
	{
		qIndex++;
		var qLink = $('<li '+(visitedAssessment[qIndex] == true ? 'class="visited"' : '')+'><a class="subnav-item nav-AQ-'+qIndex+'" href="'+ baseURL +'assessment/' + qIndex + '" title="Assessment Question '+qIndex+'">' + qIndex +'</a></li>');
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
				var altLink = $('<li><a class="subnav-item-alt nav-AQ-'+qIndex+altVersion+'" href="'+ baseURL +'assessment/' + qIndex + altVersion+'" title="Assessment Question '+qIndex+' Alternate '+ altVersion+'">'+ altVersion +'</a></li>');
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
	var pageHTML = $('<div id="content-'+index+'" class="page-layout page-layout-'+page.layoutID+'"></div>');
	pageHTML.append('<h2>' + (page.title.length > 0 ? page.title : 'Page ' + index) + '</h2>'); // add title - defaults to "Page N" if there is no title
	
	var container = $('<div id="crappy-container"></div>');
	
	var pageItems = new Array();
	
	switch(page.layoutID)
	{
		case 4:
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
				container.append(formatPageItemMediaView(item));
				break;
			case 'TextArea':
				container.append(formatPageItemTextArea(item));
				break;
		}
		
	});
	
	pageHTML.append(container);
	
	if(String(page.layoutID) == '8')
	{
		container.addClass('custom-layout-page');
		var zoomFactor = container.width() / CUSTOM_PAGE_WIDTH;
		container.css('zoom', '.9');
	}
	else
	{
		container.addClass('template-page');
	}
	
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
		var pattern = /^<p.*?>(.*)<\/p>$/gi;
		answerText = answerText.replace(pattern, '$1');
		
		answersHTML.append('<li class="answer"><input id="'+answer.answerID+'" type="radio" name="QID-'+questionID+'" value="'+answer.answerID+'"><label for="'+answer.answerID+'">' + answerText + '</label><span class="answer-feedback"><h4>Review:</h4><p>' + cleanFlashHTML(answer.feedback) + '</p></span></li>');
		/*$(event.target).parent().append('<span class="answer-preview '+ weightCSS +'">'+answer.weight+'%</span><span class="answer-feedback"><h4>Review:</h4><p>' + cleanFlashHTML(answer.feedback) + '</p></span>');*/
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
			flashvars.file = "/media/" + mediaID +'/video.flv';
			//flashvars.file = "http://pseudo01.hddn.com/vod/demo.flowplayervod/flowplayer-700.flv";
			flashvars['controlbar.idlehide'] = true;
			flashvars['controlbar.position'] = 'over';
			flashvars.dock = true;
			/*$(val).parent('.page-item').css('height', $(val).css('height')).css('width', $(val).css('width'));*/
			swfobject.embedSWF( "/assets/jwplayer/player.swf", 'media-'+mediaID, '640px', '480px', "10",  "/assets/flash/expressInstall.swf", flashvars, params);
		});
	}
}

function formatCustomLayoutPageItem(pageItemHTML, pageItem)
{
	var p = Number(pageItem.options.padding);
	pageItemHTML.width(pageItem.options.width - p * 2);
	pageItemHTML.height(pageItem.options.height - p * 2);
	pageItemHTML.css('left', pageItem.options.x);
	pageItemHTML.css('top', pageItem.options.y);
	pageItemHTML.css('padding', p);
	if(pageItem.options.borderColor != -1)
	{
		pageItemHTML.css('border', '1px solid ' + getRGBA(pageItem.options.borderColor, pageItem.options.borderAlpha));
	}
	else
	{
		pageItemHTML.css('border', '0');
	}
	if(pageItem.options.backgroundColor != -1)
	{
		pageItemHTML.css('background-color', getRGBA(pageItem.options.backgroundColor, pageItem.options.backgroundAlpha));
	}
	
	if(pageItem.media.length > 0)
	{
		//Resize and center media element inside container div
		var mediaElement = pageItemHTML.children(":first");
		var containerWidth = pageItemHTML.width();
		var containerHeight = pageItemHTML.height();
		
		var scale = Math.min(Math.min(containerWidth, pageItem.media[0].width) / pageItem.media[0].width, Math.min(containerHeight, pageItem.media[0].height) / pageItem.media[0].height);
		mediaElement.width(pageItem.media[0].width * scale);
		mediaElement.height(pageItem.media[0].height * scale);
		
		mediaElement.css('top', ((containerHeight - mediaElement.height()) / 2));
	}
	
	return pageItemHTML;
}

//util
function getRGBA(colorInt, alpha)
{
	return 'rgba(' + ((colorInt >> 16) & 255) + ',' + ((colorInt >> 8) & 255) + ',' + (colorInt & 255) + ',' + alpha + ')';
}

function formatPageItemTextArea(pageItem)
{
	var pageItemHTML = $('<div class="text-item"></div>');
	if(pageItem.options)
	{
		pageItemHTML.addClass('custom-page-item');
		formatCustomLayoutPageItem(pageItemHTML, pageItem);
	}
	else
	{
		pageItemHTML.addClass('page-item');
	}
	
	//@TODO
	pageItemHTML.addClass('strict-html-conversion');
	
	pageItemHTML.append(cleanFlashHTML(pageItem.data));
	return pageItemHTML;
}

function formatPageItemMediaView(pageItem)
{
	var mediaHTML = displayMedia(pageItem.media[0]);
	if(pageItem.options)
	{
		mediaHTML.addClass('custom-page-item');
		formatCustomLayoutPageItem(mediaHTML, pageItem);
	}
	else
	{
		mediaHTML.addClass('page-item');
	}
	
	return mediaHTML;
}

function displayMedia(mediaItem)
{
	var mediaHTML = $('<div class="media-item"></div>');
	switch(mediaItem.itemType.toLowerCase())
	{
		case 'pic':
			mediaHTML.append('<img id="media-'+mediaItem.mediaID+'" class="pic" src="/media/'+ mediaItem.mediaID +'" title="'+mediaItem.title+'" alt="'+ mediaItem.title +'">');
			break;
		case 'swf':
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="swf" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;">SWF '+mediaItem.title+'</div>');
			mediaHTML.children('#media-'+mediaItem.mediaID).load('/assets/templates/viewer.html #swf-alt-text', onFlashAltLoaded);
			break;
		case 'flv':
			var style = '';
			if(mediaItem.width > 0 && mediaItem.height > 0)
			{
				 style = 'style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;"';
			}
			
			mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="flv" ' + style + '>FLV '+mediaItem.title+'</div>');
			mediaHTML.children('#media-'+mediaItem.mediaID).load('/assets/templates/viewer.html #flv-alt-text', onFLVAltLoaded);
			break;
		case 'youtube':
			mediaHTML.append('<iframe width="640" height="390" src="http://www.youtube.com/embed/' + mediaItem.url + '?rel=0" frameborder="0" allowfullscreen></iframe>');
			break;
		case 'kogneato':
			//@TODO: Dimensions???
			mediaHTML.append('<iframe src="https://kogneato.ucf.edu/embed/' + mediaItem.url + '" width="800" height="622" style="margin:0;padding:0;border:0;"></iframe>');
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
var meow = 1;
function cleanFlashHTML(input)
{
	
	
	meow++;
	if(false && meow % 2 == 0)
	{
		var s = cleanFlashHTMLStrict(input);
		return s;
	}
	
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
	input = input.replace(pattern, "<ul><li>$1</li></ul>"); //@TODO DOES THIS WORK??????????
	
	// kill extra </ul><ul> that are back to back - this will make proper lists
	pattern = /<\/ul><ul>/gi;
	input = input.replace(pattern, "");
	
	input = createOMLTags(input);
	
	return input;
}

//@TODO ul and p should have margin = 0
/** This attempts to recreate HTML from flash HTML exactly **/
function cleanFlashHTMLStrict(input)
{
	var pattern;
	var groups;
	var groupString;
	
	var pReplaceRules = [
		['align', 'text-align', ''],
		['face', 'font-family', ''],
		['color', 'color', ''],
		['size', 'font-size', 'px'],
		['letterspacing', 'letter-spacing', 'px']
	];
	
	var tfReplaceRules = [
		['indent', 'text-indent', 'px'],
		['leftmargin', 'margin-left', 'px'],
		['rightmargin', 'margin-right', 'px']
	];
	
	//Convert <textformat ...><li> into <li style='...'>
	var matchFound = true;
	while(matchFound)
	{
		pattern = /<\s*textformat([a-z=A-Z"'0-9 ]*)?\s*><\s*li\s*>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 2)
		{
			groupString = groups[1];
			var style = generateStyleFromFlashHTMLTag(groupString, tfReplaceRules);
			//We only want to add this span if there are styles associated with it
			if(style.length > 0)
			{
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<li style="' + style + '">') + input.substr(pattern.lastIndex);
			}
			else
			{
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<li>') + input.substr(pattern.lastIndex);
			}
		}
		else
		{
			matchFound = false;
		}
	}
	pattern = /<\/li><\/textformat>/gi;
	input = input.replace(pattern, "</li>");
	
	var matchFound = true;
	//@TODO: handle textformat with no style options
	//Convert <textformat> into <div>
	while(matchFound)
	{
		pattern = /<\s*textformat(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 2)
		{
			groupString = groups[1];
			var style = generateStyleFromFlashHTMLTag(groupString, tfReplaceRules);
			//We only want to add this span if there are styles associated with it
			if(style.length > 0)
			{
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<div style="' + style + '">') + input.substr(pattern.lastIndex);
			}
			else
			{
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<div>') + input.substr(pattern.lastIndex);
			}
		}
		else
		{
			matchFound = false;
		}
	}
	
	pattern = /<\/textformat>/gi;
	input = input.replace(pattern, "</div>");
	
	//Convert <p><font> into <p>
	var matchFound = true;
	while(matchFound)
	{
		pattern = /<\s*p(\s+align=(?:"|')(?:left|right|center|justify)(?:"|'))?\s*><\s*font(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 3)
		{
			groupString = groups[1] + ' ' + groups[2];
			input = input.substr(0, pattern.lastIndex).replace(pattern, '<p style="' + generateStyleFromFlashHTMLTag(groupString, pReplaceRules) + '">') + input.substr(pattern.lastIndex);
		}
		else
		{
			matchFound = false;
		}
	}

	pattern = /<\/font><\/p>/gi;
	input = input.replace(pattern, "</p>");
	
	//Convert single <font> into <span>
	var matchFound = true;
	while(matchFound)
	{
		pattern = /<\s*font(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 2)
		{
			groupString = groups[1];
			input = input.substr(0, pattern.lastIndex).replace(pattern, '<span style="' + generateStyleFromFlashHTMLTag(groupString, pReplaceRules) + '">') + input.substr(pattern.lastIndex);
		}
		else
		{
			matchFound = false;
		}
	}
	
	pattern = /<\/font>/gi;
	input = input.replace(pattern, "</span>");

	// remove any previously added ul tags
	pattern = /<\/?ul>/gi;
	input = input.replace(pattern, "");

	// add <ul></ul> arround list items
	pattern = /(<li(.*?)?>([\s\S]*?)<\/li>)/gi;
	input = input.replace(pattern, "<ul>$1</ul>");

	// kill extra </ul><ul> that are back to back - this will make proper lists
	pattern = /<\/ul><ul>/gi;
	input = input.replace(pattern, "");
	
	// find empty tags keeping space in them
	pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
	input = input.replace(pattern, "$2");
	
	pattern = /<(\w+)>(\s*?)<\/\1>/gi;
	input = input.replace(pattern, "$2");
	
	var matchFound = true;
	while(matchFound)
	{
		// find empty tags keeping space in them
		pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 3)
		{
			groupString = groups[2];
			input = input.substr(0, pattern.lastIndex).replace(pattern, groupString) + input.substr(pattern.lastIndex);
		}
		else
		{
			matchFound = false;
		}
	}

	var matchFound = true;
	while(matchFound)
	{
		pattern = /<(\w+)>(\s*?)<\/\1>/gi;
		groups = pattern.exec(input);
		if(groups && groups.length >= 3)
		{
			groupString = groups[1];
			input = input.substr(0, pattern.lastIndex).replace(pattern, groupString) + input.substr(pattern.lastIndex);
		}
		else
		{
			matchFound = false;
		}
	}
	
	input = createOMLTags(input);
	
	return input;
}

function generateStyleFromFlashHTMLTag(attribs, rules)
{
	var style = '';
	var reg;
	var match;
	for(var a in rules)
	{
		reg = new RegExp(rules[a][0] + '\s*=\s*(?:\'|")(.+?)(?:\'|")', 'gi');
		match = reg.exec(attribs)
		if(match != null && match.length >= 2)
		{
			style += rules[a][1] + ':' + match[1] + rules[a][2] + ';';
		}
	}
	return style;
}

function createOMLTags(input)
{
	var pattern;
	
	/*Find OML tooltips*/
	//@TODO: Img
	pattern = /\[\s*?tooltip\s+?text\s*?=\s*?(?:"|')(.+?)(?:"|')\s*?](.+)\[\/tooltip\]/gi;
	input = input.replace(pattern, '<span title="$1" class="oml oml-tip">$2</span>');
	
	/*Find OML page links*/
	pattern = /\[\s*?page\s+?id\s*?=\s*?(?:"|')(.+?)(?:"|')\s*?](.+)\[\/page\]/gi;
	
	//@TODO: Left or right arrow depending on if that would be foward or back
	input = input.replace(pattern, '<a class="oml oml-page-link" data-page-id="$1" href="' + location.href + 'page/$1" title="' + '&rarr; Page $1">$2</a>');
	/*input = input.replace(pattern, '<a class="oml oml-page-link" data-page-id="$1" href="http://www.google.com/" title="Go to page $1">$2</a>');*/
	//@TODO: Fix quote issues (" --> &quot;)
	
	return input;
}

function strip(html)
{
	return html.replace(/</g,'v').replace(/>/g,'&gt;').replace(/&/g,'&amp;').replace(/\"/g, '');
}

function getURLParam(strParamName)
{
	var strReturn = "";
	var strHref = window.location.href;
	if ( strHref.indexOf("?") > -1 )
	{
		var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
		var aQueryString = strQueryString.split("&");
		for ( var iParam = 0; iParam < aQueryString.length; iParam++ )
		{
			if ( aQueryString[iParam].indexOf(strParamName.toLowerCase() + "=") > -1 )
			{
				var aParam = aQueryString[iParam].split("=");
				strReturn = aParam[1];
				break;
			}
		}
	}
	return unescape(strReturn);
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