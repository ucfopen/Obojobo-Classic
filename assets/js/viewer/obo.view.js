/**
	The view acts as a view/controller and should write out HTML code and generally
	modify the look of the app. The controller logic communicates with the model, then
	the model asks to view to update (via render()).
	
	View can ignore the model for any events that are entirely view-specifc.
	The view also acts as a model for any entirely view-specific information (ie visitedPages)
*/

// @TODO (General)
// - tipTip does'nt seem to be working for 'you missed a page or two' pages
// - for all a href="#" add role="button" (ARIA)
// - block access to assessment if score already imported
// - prevent being able to simply type 'obo.model.getLO()'
// - when import message comes up have it replace the 'you missed a page or two' div
// - delte SA text, then hit esc, it doesn't close out of editing mode

// - Wider tooltips, with different color for Finish

// - q alts
// - linkize
// - Max width 65% on media (also handle YT)
// - tip tip bugs out when you unlock non-assessment
// - createTime = NAN will freak out the JSON encoder (happens on editited questions)
// - AUTOLOAD_FLASH must = true
// - What happens when you start 'back'ing while in assessment?
// - Make swfs fit if they would extend past teh frame of media item
// - media questions dont have bottom padding
// - add in http:// on external links 
// - For content section the popup text says 'Finish This Section' instead of what it should say
// - reload media!

if(!window.obo)
{
	window.obo = {};
}

obo.view = function()
{
	// @PRIVATE:
	
	// @TODO: Make sure this is defined when we set it
	// for convience keep a reference to the URL sans fake # URL
	var baseURL = location.href.substr(0, location.href.indexOf('#') === -1 ? location.href.length : location.href.indexOf('#'));
	//var baseURL = location.origin + location.pathname + location.search;
	debug.log('location=');
	debug.log(location);
	// @TODO can this be made more elegant?
	// this is needed if the user uses the back/foward buttons.
	// in this case we don't want to modify to the history stack,
	// but move through it instead.
	var preventUpdateHistoryOnNextRender = false;

	// we need to keep track of the last url pushed to history.
	// this is a workaround until all browsers support checking history.state
	// this allows us to not push the same url twice
	//var lastHistoryState = '';
	
	// some view-based state info - need to keep track of which pages have already been visited
	var visited = {
		content: [],
		practice: [],
		assessment: []
	};
	
	// number in ms that defines how long simple animations should take place
	var defaultAnimationDuration = 200;
	
	// we want to keep track of which questions have been answered
	/*
	var answered = {
		practice: [],
		assessment: []
	};*/
	
	// flag used to specify the first time render() is called.
	// the view is 'unrendered' until the first render() completes.
	var unrendered = true;
	
	var loadUI = function($element)
	{
		$element.load('/assets/templates/viewer.html #template-main > *', setupUI);
		
		//$element.loadShiv('/assets/templates/viewer.html #template-main > *', {}, setupUI);
		/*
		$.ajax({
			url: '/assets/templates/viewer.html',
			data: {},
			success: function (data) {
				console.log('wtf');
				//$element.html($(data).find("#template-main"));
				var f = $(data).find('#template-main');
				debug.log(f.text());
				debug.log($(f).text());
				$element.html(f);
				setupUI();
			},
			dataType: 'html'
		});*/
	}
	
	var setupUI = function()
	{
		debug.log('setup ui');
		/*
		$(window).resize(function() {
			debug.log('resize');
			$('.media-item').offset($('.media-item-standin').offset());
		});
		*/
		// listen for history events
		if(Modernizr.history)
		{
			window.onpopstate = function(event) {
				debug.log('_________________________ASDFSADFSDAFSD');
				preventUpdateHistoryOnNextRender = true;
				gotoPageFromURL();
			};
		}
		
		// Live events:
		$(document).on('click', '.next-section-button', function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoStartPageOfNextSection();
			}
		}).on('click', '#get-started-button', function(event) {
			event.preventDefault();

			if(!$(event.target).hasClass('disabled'))
			{
				// @TODO instead of doing this hack the model should
				// let gotoNextPage take you to the nextPage you can access
				obo.model.isResumingPreviousAttempt() ? obo.model.gotoSectionAndPage('assessment', 'start') : obo.model.gotoStartPageOfNextSection();
			}
		}).on('mouseenter', '.subnav-list.assessment li:has(ul)', function(event) {
			debug.log('mouseenter');
			$ul = $(this).find('ul');
			$('object').each(function(i, e) {
				// hide swfs if alts menu would overlap
				// 40 is a fudge value to account for padding
				if($ul.offset().top + $ul.height() + 40 >= $(e).offset().top)
				{
					hideSWFs();
					return false; //break
				}
			});
		}).on('mouseleave', '.subnav-list.assessment li:has(ul)', function(event) {
			debug.log('mouseleave');
			unhideSWFs();
		}).on('click', '.begin-section-button', function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoPage(1);
			}
		}).on('click', '#start-practice-button', function(event) {
			// start the practice
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				startPractice();
			}
		}).on('click', '#start-assessment-button', function(event) {
			// start the assessment
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				startAssessment();
			}
		}).on('click', '#return-to-overview-button', function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoPage('start');
			}
		}).on('click', '#view-scores-button', function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoPage('scores');
			}
		}).on('click', '.oml-page-link', function(event) {
			event.preventDefault();
			
			// @TODO- check to see if this works
			var pageID = $(event.target).attr('data-page-id');
			if(obo.model.getSection() != 'content')
			{
				obo.model.gotoSectionAndPage('content', pageID);
			}
			else
			{
				obo.model.gotoPage(pageID);
			}
		}).on('click', '#submit-qa-answer-button', function(event) {
			event.preventDefault();
			
			$form = $(event.target).parent();
			$a = $($form.find('#submit-qa-answer-button')[0]);
			if(!$a.hasClass('disabled'))
			{
				$input = $($form.find('#qa-input')[0]);
				if($input.attr('disabled') === 'disabled')
				{
					// edit mode:
					setQAFormMode('editing');
				}
				else
				{
					// saved mode:
					saveQAResponse();
					fillInQAAnswer($('#qa-input').val());
					setQAFormMode('saved');
				}
			}
		}).on('focus', '#qa-input', function(event) {
			$('#qa-input').unbind('keydown').keydown(function(event) {
				debug.log('got a key down, keycode is ' + event.keyCode);
				if(event.keyCode === 13) // enter
				{
					// opera crashes and FF doesn't work correctly
					// without this 1ms delay:
					setTimeout(function() {
						$($('#submit-qa-answer-button')[0]).click();
					}, 1);
				}
				else if(event.keyCode === 27) // ESC
				{
					var lastVal = $('#qa-input').attr('data-last-val');
					if(lastVal != undefined)
					{
						$('#qa-input').val($('#qa-input').attr('data-last-val'));
						$($('#submit-qa-answer-button')[0]).click();
					}
				}
			});
		}).on('blur', '#qa-input', function(event) {
			$('#qa-input').unbind('keydown');
			saveQAResponse();
		}).on('keyup', '#qa-input', function(event) {
			updateQAButton();
		}).on('click', '#submit-assessment-button', function(event) {
			event.preventDefault();
			submitAssessment();
		}).on('click', '#go-left', function(event) {
			event.preventDefault();
			obo.model.gotoPrevPage();
		}).on('click', '#go-right', function(event) {
			event.preventDefault();
			obo.model.gotoNextPage();
		}).on('click', '.prev-page-button', function(event) {
			event.preventDefault();
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoPrevPage();
			}
		}).on('click', '.next-page-button', function(event) {
			debug.log('npb CLICK');
			event.preventDefault();
			if(!$(event.target).hasClass('disabled'))
			{
				
				obo.model.gotoNextPage();
			}
		});
		// End live events.
		
		// inject preview header if needed
		if(obo.model.getMode() === 'preview')
		{
			$('body').prepend($('<div id="preview-mode-notification"><p>(Previewing)</p><a id="close-preview-bar-button" role="button" href="#">Close</a></div>'));
			$('#preview-mode-notification').click(function(event) {
				event.preventDefault();
				$(event.target).parent().hide();

				// special case - reposition media if there is overlayed media on the page:
				obo.media.positionOverlayMedia();
			});
		}
		// for convience we map a H1 title click to the overview page
		$('#lo-title').click(function(event) {
			obo.model.gotoSection('overview');
		});
		// Set up all the navigation Links
		$('#nav-list li a').click(function(event) {
			event.preventDefault();
			
			var $e = $(event.currentTarget).parent();
			//only go to this section if it's not locked out or disabled
			if(!$e.hasClass('disabled') && !$e.hasClass('lockedout'))
			{
				obo.model.gotoSection(event.currentTarget.id.replace('nav-', ''));
			}
		});
		
		// set the href's so the mouse-over and default click funcionality do what we want
		/*
		$('#nav-overview')[0].href = baseURL + 'overview/';
		$('#nav-content')[0].href = baseURL + 'page/1/';
		$('#nav-practice')[0].href = baseURL + 'practice/start/';
		$('#nav-assessment')[0].href = baseURL + 'assessment/start/';*/
		$('#nav-overview').attr('href', baseURL + '#/overview');
		$('#nav-content').attr('href', baseURL + '#/content/1');
		$('#nav-practice').attr('href', baseURL + '#/practice/start');
		$('#nav-assessment').attr('href', baseURL + '#/assessment/start');
		/*
		// navigation handlers:
		$('.prev-page-button').click(function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoPrevPage();
			}
		});
		$('.next-page-button').click(function(event) {
			event.preventDefault();
			
			if(!$(event.target).hasClass('disabled'))
			{
				obo.model.gotoNextPage();
			}
		});*/
		
		// setup tooltips:
		var o = {maxWidth:'200px', delay:0, fadeIn:0, fadeOut:0, defaultPosition:'top', live:true, deactivationOnClick:true};
		// oml tooltips
		$('.oml').tipTip(o);
		// we only want to shop subnav tips for content pages (practice and assessment don't have titles)
		$('.subnav-list.content .subnav-item').tipTip(o);
		// we also want the subnavs on the content 'you missed a page or two' page:
		$('#template-final-content-page-incomplete .subnav-item').tipTip(o);
		
		// set page title
		var t = obo.util.strip(obo.model.getTitle());
		$('#lo-title').text(t);
		$('#lo-title').attr('title', t);
		document.title = t + ' | Obojobo Learning Object';
		
		// hack in ability to touch labels for iOS
		if(obo.util.isIOS())
		{
			$('label[for]').live('click', function ()
			{
				var el = $(this).attr('for');
				if ($('#' + el + '[type=radio], #' + el + '[type=checkbox]').attr('selected', !$('#' + el).attr('selected')))
				{
					return;
				}
				else
				{
					$('#' + el)[0].focus();
				}
			});
		}
		
		// we prevent this from altering the history, which will break
		// the back button
		preventUpdateHistoryOnNextRender = true;
		gotoPageFromURL();
		updateHistory(getHashURL(), true);
	}
	
	// grabs the hash url and navigates to the page specified.
	// if possible, go to the page and return true
	// we assume the 'start' page is desired if no page index is specified
	var gotoPageFromURL = function()
	{
		debug.log('GO TO PAGE FROM URL');
		var section = '';
		var pg = '';
		
		//var url = location.href;
		//var hashIndex = url.indexOf('#');
		if(location.hash.length > 1)
		{
			var hashURL = location.hash.substr(1); //chop off '#'
			var rawTokens = hashURL.split('/');
			
			// need to remove empty strings
			var tokens = [];
			for(var i in rawTokens)
			{
				if(rawTokens[i] != '')
				{
					tokens.push(rawTokens[i]);
				}
			}
			
			if(tokens.length > 0)
			{
				section = tokens[0].toLowerCase();
				//if(section === 'overview' || section === 'content' || section === 'practice' || section === 'assessment')
				//{
				if(tokens.length > 1 && section != 'overview')
				{
					pg = tokens[1];
					obo.model.gotoSectionAndPage(tokens[0].toLowerCase(), pg, onGotoPageFromURL);
				}
				else
				{
					obo.model.gotoSection(tokens[0].toLowerCase(), onGotoPageFromURL);
				}
				//}
			}
		}
		else
		{
			render();
		}
	}

	var onGotoPageFromURL = function(successful)
	{
		debug.log('::::::::::::', successful);
		if(successful)
		{
			render();
		} 
		else
		{
			obo.model.gotoPage('start');
			//obo.model.gotoSection('overview');
		}
	}
	
	// generates the fake # URL which gotoPageFromURL can use to navigate to the page
	var getHashURL = function()
	{
		var section = obo.model.getSection();
		var page = obo.model.getPage();
		// for simplicity only we omit 'start' if in overview section
		var url = '/' + section + (page === 'start' && section === 'overview' ? '' : '/' + page);
		return url;
		//return '/' + obo.model.getSection() + '/' + obo.model.getPage();
	}

	// returns what the hash url should be for the previous page
	var getPrevPageHashURL = function()
	{
		var p = obo.model.getPrevPage();
		debug.log('p=', p);
		return '/' + p.section + (typeof p.page === 'undefined' ? '' : '/' + p.page);
	}

	var getNextPageHashURL = function()
	{
		var p = obo.model.getNextPage();
		return '/' + p.section + (typeof p.page === 'undefined' ? '' : '/' + p.page);
	}
	
	var showThrobber = function()
	{
		$('body').append($('<div id="content-blocker"></div>'));
		$('#content').animate({
			opacity: .5
		},
		{
			duration: 1000
		});
		$('#content-blocker').animate({
			opacity: 1
		},
		{
			duration: 1000
		});
		
		// we want to automatically give up if we never get a response after 10 seconds
		// (Just so we don't kill the website with an overlay)
		setTimeout(hideThrobber, 10000);
	}
	
	var hideThrobber = function()
	{
		$('#content').stop();
		$('#content').css('opacity', 1); // remove opacity attribute (reset to 1)
		$('#content-blocker').remove();
	}

	var startPractice = function()
	{
		showThrobber();
		obo.model.startPractice();
	}
	
	// @TODO instead of wrapper functions should these be callbacks from obo.model.startAssessment?
	var startAssessment = function()
	{
		showThrobber();

		// @HACK: wipe out practice captivate overlays
		if($('#swf-holder-practice').length > 0)
		{
			$('#swf-holder-practice').remove();
		}
		obo.captivate.clearCaptivateData();
		
		obo.model.startAssessment();
	}
	
	var submitAssessment = function()
	{
		// @HACK: wipe out assessment captivate overlays
		if($('#swf-holder-assessment').length > 0)
		{
			$('#swf-holder-assessment').remove();
		}
		
		unlockNonAssessment();
		showThrobber();
		// @TODO: What happens if the server takes a dump?  Should I not clear out assessment?
		visited.assessment = []; // clear out assessment answers so new attempts are empty
		obo.model.submitAssessment();
		obo.captivate.clearCaptivateData();
	};
	
	var showSubnav = function()
	{
		$('.subnav-list').show();
		var pList = $('.subnav-list');
			$($('#content')[0]).css('margin-top', (pList.height() + 40) + 'px');
	};

	var hideSubnav = function()
	{
		$('.subnav-list').hide();
			$($('#content')[0]).css('margin-top', 0);
	};

	var showAndUpdateNextPrevNav = function()
	{
		$('.page-navigation').show();
		$('.prev-page-button').attr('href', baseURL + '#' + getPrevPageHashURL());
		$('.next-page-button').attr('href', baseURL + '#' + getNextPageHashURL());
	};

	var hideNextPrevNav = function()
	{
		$('.page-navigation').hide();
		debug.log('HIDE next prev nav');
	};
	
	var lockoutSections = function(sections)
	{
		var $li;
		var $a;
		var origTitle;
		for(var i in sections)
		{
			$li = $('#nav-' + sections[i]).parent();
			$li.addClass('lockedout');
			// @TODO - This is really buggy on un-lockout
			//$($li.children()[0]).attr('data-lockout-message', 'You can visit this section after you complete the assessment quiz.');
		}
		
		// setup tooltip:
		/*
		$('#nav-list li a').tipTip({
			maxWidth: '200px',
			delay: 0,
			fadeIn: 250,
			fadeOut: 250,
			defaultPosition: 'bottom',
			attribute: 'data-lockout-message',
			edgeOffset: -20
		});*/
	}
	
	var lockoutNonAssessment = function()
	{
		lockoutSections(['overview', 'content', 'practice']);
	}
	
	var unlockNonAssessment = function()
	{
		var $li;
		var $a;
		$('.lockedout').each(function () {
			$li = $(this);
			$li.removeClass('lockedout');
			$($li.children()[0]).removeAttr('data-lockout-message');
		});
	}
	
	// @TODO
	/*
	var onChangeSection = function()
	{
		$('#content').empty(); // clear previous content
		$('.subnav-list').remove(); // clear previous subnav
		$('#nav-list li').removeClass('selected'); // reset the class for nav links
		
		obo.model.section = 'blah';
	};*/
	
	var buildOverviewPage = function()
	{
		$('#content').load('/assets/templates/viewer.html #overview-page', function() {
			var lo = obo.model.getLO();
			var t = obo.util.strip(obo.model.getTitle());
			$("#overview-blurb-title").text(t);
			$("#title").text(t);
			$("#version").text(lo.version + '.' + lo.subVersion);
			$("#language").text(lo.languageID);
			$("#objective").append(obo.util.cleanFlashHTML(lo.objective));
			$("#learn-time").text(lo.learnTime + ' Min.');
			$("#key-words").text(lo.keywords.join(", "));
			$("#content-size").text(lo.summary.contentSize + ' Pages');
			$("#practice-size").text(lo.summary.practiceSize + ' Questions');
			$("#assessment-size").text(lo.summary.assessmentSize + ' Questions');
			$('#get-started-button').attr('href', baseURL + obo.model.isResumingPreviousAttempt() ? '#/assessment/start' : '#/content/1');
		});
	};
	
	// this creates our 'you missed a page or two' subnav list for anything not 'visited'
	var createUnvisitedPageList = function()
	{
		// we added in 'finish', remove it when cloning
		var missedPages = $('.subnav-list > li:not(.visited)');
		missedPages.clone().appendTo('.missed-pages-list');
		$('.missed-pages-list a').click(onNavPageLinkClick);
	}
	
	// this creates our 'you missed a page or two' subnav list for anything not 'answered'
	var createUnansweredPageList = function()
	{
		// we added in 'finish', remove it when cloning
		if(obo.model.getSection() === 'assessment')
		{
			var unanswered = $('.subnav-list > li:not(.answered)').not(':last-child');
		}
		else
		{
			var unanswered = $('.subnav-list > li:not(.answered)');
		}
		unanswered.clone().appendTo('.missed-pages-list');
		$('.missed-pages-list a').click(onNavPageLinkClick);
	}
	
	var buildPage = function(section, index)
	{
		// clean up any overlayed captivates
		// @HACK we turn both parent and object visible for Safari
		$('#swf-holder object').css('visibility', 'hidden');
		$('#swf-holder .media-item').css('visibility', 'hidden');
		
		// @TODO captivateSwitch
		$('#swap-cap').hide();
		
		switch(section)
		{
			case 'overview': buildOverviewPage(); break;
			case 'content': buildContentPage(index); break;
			case 'practice': buildQuestionPage(section, index); break;
			case 'assessment': buildQuestionPage(section, index); break;
		}
	}
	
	// creates a content page (index = 'start', 'end' or a standard page 1-n)
	var buildContentPage = function(index)
	{
		// remove the last selected nav item
		$('#nav-list ul.subnav-list li').removeClass('selected');
		
		// every content pages shows the subnav
		showSubnav();
		
		if(index === 'start')
		{
			// should never get here - content doesn't have start pages.
			// let's build page 1 instead
			buildContentPage(1);
		}
		else if(index === 'end')
		{
			// hide next/prev since we're showing a 'next section' button
			hideNextPrevNav();
			
			// check the number of visited pages vs the number of pages
			if(getNumPagesVisited('content') === obo.model.getLO().pages.length)
			{
				// all content pages seen
				$('#content').load('/assets/templates/viewer.html #template-final-content-page-complete');
			}
			else
			{
				// some content pages missed
				$('#content').load('/assets/templates/viewer.html #template-final-content-page-incomplete', function() {
					createUnvisitedPageList();
					$('.next-section-button').attr('href', baseURL + '#/practice/start');
				});
				hideSubnav();
			}
			//curPageIndex = lo.pages.length;
		}
		// standard page (1 - n)
		else
		{
			showAndUpdateNextPrevNav();
			
			var page = obo.model.getLO().pages[index - 1]
			
			var $pageHTML = $('<div id="content-'+index+'" class="page-layout page-layout-'+page.layoutID+'"></div>');
			//$pageHTML.append('<h2>' + (page.title.length > 0 ? page.title : 'Page ' + index) + '</h2>'); // add title - defaults to "Page N" if there is no title
			//$pageHTML.append('<nav id="page-navigation-top" class="page-navigation"><ul><li><a class="prev-page-button" href="#" role="button">Prev</a></li><li><a class="next-page-button" href="#" role="button">Next</a></li></ul></nav>');
			buildPageHeader($pageHTML, page.title.length > 0 ? page.title : 'Page ' + index);
			//$pageHTML.append('<nav id="page-navigation-top"></nav>');
			$('#content').append($pageHTML);
			
			// for custom layout the html target is a container div for the custom layout page,
			// otherwise it is simply the content div.
			var $target;
			if(String(page.layoutID) === '8')
			{
				var CUSTOM_PAGE_WIDTH = 1064;
				var CUSTOM_PAGE_HEIGHT = 798;
				
				$target = $('<div id="custom-layout-page"></div>');
				var zoomFactor = $target.width() / CUSTOM_PAGE_WIDTH;
				// @TODO:
				//$target.css('-ms-zoom', '.9');
				//$target.css('zoom', '1');
			}
			else
			{
				// @TODO - get rid of .template-page
				//container.addClass('template-page');
				$target = $pageHTML;
			}

			var pageItems = [];


			switch(page.layoutID)
			{
				case 4: case '4':
					pageItems[0] = page.items[1];
					pageItems[1] = page.items[0];
					break;
				default:
					pageItems = $(page.items);
					break;
			}

			//pageItems = $(page.items);

			// loop through each page item
			$(pageItems).each(function(itemIndex, item)
			{
				switch(item.component)
				{
					case 'MediaView':
						createPageItemMediaView(item, $target);
						break;
					case 'TextArea':
						$target.append(formatPageItemTextArea(item, parseInt(page.layoutID) === 8));
						break;
				}

			});
			
			if(String(page.layoutID) === '8')
			{
				$pageHTML.append($target);
				$pageHTML.append('<span class="attribution">' + obo.util.createCombinedAttributionString(page.items) + '</span>');
			}
			
			//$('#content').append($pageHTML);
		}
	};
	
	var buildPageHeader = function($target, title)
	{
		$target.append('<h2>' + title + '</h2>');
		//$target.append('<nav id="page-navigation-top" class="page-navigation"><ul><li><a class="prev-page-button" href="#" role="button">Prev</a></li><li><a class="next-page-button" href="#" role="button">Next</a></li></ul></nav>');
	};

	var buildQuestionPage = function(section, index)
	{
		var sectionName = section.substr(0, 1).toUpperCase() + section.substr(1);
		var baseid = section === 'practice' ? 'PQ' : 'AQ';
		
		switch(index)
		{
			case 'start':
				hideSubnav();
				hideNextPrevNav();
				
				switch(sectionName.toLowerCase())
				{
					case 'practice':
						$('#content').load('/assets/templates/viewer.html #practice-overview', function() {
							var n = obo.model.getNumPagesOfSection('practice');
							$('.icon-dynamic-background').text(n).next().prepend(n + ' ');
							$('#start-practice-button').attr('href', baseURL + '#/practice/1');
						});
						break;
					case 'assessment':
						$('#content').load('/assets/templates/viewer.html #assessment-overview', function() {
							var flashRequirements = obo.model.getFlashRequirementsForSection('assessment');
							var canViewFlash = !flashRequirements.containsFlashContent || flashRequirements.installedMajorVersion >= flashRequirements.highestMajorVersion;

							var numAssessment = obo.model.getNumPagesOfSection('assessment');
							var numAttempts = obo.model.getNumAttemptsRemaining();
							$('#start-assessment-button').attr('href', baseURL + '#/assessment/1');
							$('#view-scores-button').attr('href', baseURL + '#/assessment/scores/');

							// set the dynamic icons
							$('.icon-dynamic-background:eq(0)').text(numAssessment).next().prepend(numAssessment + ' ') // number of questions
							$('.assessment-attempt-count').prepend(numAttempts + ' '); // number of assessments remaining
							var s;
							switch(obo.model.getScoreMethod())
							{
								case 'h': //highest
									s = 'Your highest attempt score counts';
									break;
								case 'm': //mean
									s = 'Your final score is the average of your attempt scores';
									break;
								case 'r': //most recent
									s = 'Your last attempt score counts';
									break;
							}
							$('.final-score-method').html(s);

							var showMissingPractice = false;
							var showMissingPages = false;

							var lo = obo.model.getLO();

							// determine which practice pages weren't seen
							var practiceMissed = obo.model.getNumPagesOfSection('practice') - obo.model.getNumPagesAnswered('practice');
							showMissingPractice =  practiceMissed > 0;

							// determine which content pages weren't seen
							var contentMissed = obo.model.getNumPagesOfSection('content') - getNumPagesVisited('content');
							showMissingPages =  contentMissed > 0 ;

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

							if(obo.model.instanceIsClosed())
							{
								$('#assessment-info').hide();
								$('#assessment-info-closed').show();
							}
							else if(numAttempts === 0)
							{
								$('#assessment-info').hide();
								$('#assessment-info-no-attempts').show();
							}

							// disable button if they don't have any more attempts (0 if they have imported a score)
							// or if the instance is closed
							// of they are missing flash or are on a flashless device
							if(numAttempts === 0 || obo.model.instanceIsClosed() || !canViewFlash)
							{
								$('#start-assessment-button').addClass('disabled');
							}

debug.log(flashRequirements);
							if(!canViewFlash)
							{
								if(flashRequirements.installedMajorVersion === 0)
								{
									if(obo.util.isIOS())
									{
										$('#ios-flash-notice').show();
									}
									else
									{
										$('#no-flash-notice').show();
									}
								}
								else
								{
									$('#old-version-flash-notice').show();
								}
							}

							// show/hide 'view scores' button
							if(obo.model.getScores().length === 0)
							{
								$('#view-scores-button').remove();
							}

							// previous score importable notice
							var importableScore = obo.model.getImportableScore();
							if(importableScore != -1)
							{
								$('.assessment-import-score-section').show();
								$('.assessment-missed-section').hide();

								$('.previous-score').html(importableScore);
								$('#do-import-previous-score-button').click(function(event) {
									event.preventDefault();

									var score = obo.model.getImportableScore(); 
									obo.dialog.showDialog({
										title: 'Confirm Score Importing: ' + score + '%',
										contents: '<strong>WARNING:</strong> Importing will forfeit of your attempts, setting your final score for this learning object to <strong>' + score + '%</strong><br><br>Are you sure you want to import your previous score?',
										modal: true,
										width: 600,
										buttons: [
											{label: 'Cancel'},
											{label: 'Import Score: ' + score + '%', action: function() {
												showThrobber();
												obo.model.importPreviousScore();
											}}
										]
									});
									
								});
								$('#dont-import-previous-score-button').click(function(event) {
									event.preventDefault();

									obo.dialog.showDialog({
										title: 'Are You Sure?',
										contents: 'By choosing not to import you will need to complete the assessment attempt. Once you begin an attempt you can no longer import your previous score.<br><br>Are you sure you don\'t want to import?',
										modal: true,
										width: 600,
										buttons: [
											{label: 'Cancel'},
											{label: 'Do Not Import', action: function() {
												$('.assessment-import-score-section').remove();
												$('.assessment-missed-section').show();
												$('#start-assessment-button').removeClass('disabled');
											}}
										]
									});
									//$('.assessment-import-score-section').remove();
									//$('#start-assessment-button').removeClass('disabled');
								});

								// disable start assessment until they choose an option
								$('#start-assessment-button').addClass('disabled');
							}

							// modify 'start assessment' button if they are resuming assessment
							if(obo.model.isResumingPreviousAttempt())
							{
								$('#start-assessment-button').html('Resume Assessment <span class="triange-right"></span>');
							}
						});
						break;
				}
				break;
			case 'end':
				hideNextPrevNav();
				
				switch(obo.model.getSection())
				{
					case 'practice':
						// @TODO - Should this be a review?
						// check the number of visited questions vs the number of questions
						if(obo.model.getNumPagesAnswered('practice') >= obo.model.getNumPagesOfCurrentSection())
						{
							// all practice questions answered
							$('#content').load('/assets/templates/viewer.html #template-final-practice-page-complete');
							showSubnav();
						}
						else
						{
							// some practice questions not answered
							$('#content').load('/assets/templates/viewer.html #template-final-practice-page-incomplete', function() {
								createUnansweredPageList();
								$('.next-section-button').attr('href', baseURL + '#/assessment/start');
							});
							hideSubnav();
						}
						break;
					case 'assessment':
						// check the number of visited questions vs the number of questions
						if(obo.model.getNumPagesAnswered('assessment') >= obo.model.getNumPagesOfCurrentSection())
						{
							// all practice questions seen
							$('#content').load('/assets/templates/viewer.html #template-final-assessment-page-complete');
							showSubnav();
						}
						else
						{
							// some practice questions missed
							$('#content').load('/assets/templates/viewer.html #template-final-assessment-page-incomplete', function() {
								createUnansweredPageList();
								$('#submit-assessment-button').attr('href', baseURL + '#/assessment/scores');
							});
							hideSubnav();
						}
						break;
				}
				break;
			case 'scores':
				hideSubnav();
				hideNextPrevNav();
				
				var scores = obo.model.getScores();
				if(scores.length > 0)
				{
					$('#content').load('/assets/templates/viewer.html #score-results', function() {
						$('#return-to-overview-button').attr('href', baseURL + '#/assessment/start');
						var recentScore = scores[scores.length - 1].score;
						var recordedScore = obo.model.getFinalCalculatedScore();
						var attemptsRemaining = obo.model.getNumAttemptsRemaining();

						if(scores.length > 1)
						{
							$('#score-results').addClass('multiple-attempts');

							// build score table
							var endDate;
							for(i in scores)
							{
								endDate = new Date(scores[i].endTime * 1000);
								$('#attempt-history').append(
									'<tr><td>' + (parseInt(i) + 1) + '.</td>' + 
									'<td>' + scores[i].score + '%</td>' + 
									'<td>' + endDate.format('mm/dd/yy - h:MM:ss TT') + '</td></tr>'
								);
							}
						}
						debug.log('scores equals ', scores);
						
						$('#attempt-score-result h2').html('Attempt ' + scores.length + ' Score:');
						$('#attempt-score').html(recentScore + '%');
						$('.recorded-score').html(recordedScore + '%');
						$('.attempts-remaining').html(attemptsRemaining + ' Attempt' + (attemptsRemaining === 1 ? '' : 's'));
						var note = '';
						switch(obo.model.getScoreMethod())
						{
							case 'h': //highest
								note = '(This is your highest attempt score)';
								break;
							case 'r': //recent
								note = '(This is your latest attempt score)';
								break;
							case 'm': //mean
								note = '(This is your attempt score average)';
								break;
						}
						$('#recorded-score-note').html(note);

						if(obo.model.instanceIsClosed())
						{
							$('#recent-attempt p.assessment-closed').show();
							$('#assessment-close-notice').hide();
						}
						else if(attemptsRemaining == 0)
						{
							$('#recent-attempt p.out-of-attempts').show();
						}
						else
						{
							$('#recent-attempt p.assessment-open').show();
						}

						$('.assessment-close-date').html(obo.model.getInstanceCloseDate().format('mm/dd/yy "at" h:MM:ss TT'));
						/*
						var $ul = $('#scores-list');
						var scores = obo.model.getScores();
						var $li;
						var t;
						for(var i = 0; i < scores.length; i++)
						{
							t = scores[i].endTime - scores[i].startTime;
							$ul.append($('<li>#' + (i + 1) + ' | ' + t + " duration | submitted " + scores[i].endTime + " | " + scores[i].score + '</li>'));
						}
						$ul.append($('<li>' + getScoreMethodString() + ' score: ' + obo.model.getFinalCalculatedScore() + '%</li>'));
						*/
						// we clear out the subnav so no item looks to be 'answered'
						// we could delete the subnav instead if that's faster
						resetSubnav('answered');
						resetSubnav('visited');
					});
				}
				else
				{
					// we should never get here
					obo.model.gotoPage('start');
				}
				
				break;
			default:
				showSubnav();
				showAndUpdateNextPrevNav();
				
				//var question = obo.model.getQGroup().kids[index - 1];
				var question = obo.model.getPageObject();
				
				// init container
				var page = $('<div id="' + baseid + '-' + index + '" class="question-page question-type-' + question.itemType + '"></div>');
				//page.append('<h2>'+sectionName+' Question ' + index + ':</h2>'); // title
				buildPageHeader(page, sectionName+' Question ' + index + ':');
				
				$('#content').append(page);
				
				// add switcher for questions with alternates:
				//var numVersions = obo.model.getCurrentAssessmentQuestions().length;
				if(obo.model.currentQuestionIsAlternate())
				{
					page.append('<span id="question-alt-notice">(Question Alternate)</span>');
					
					/*var $list = $('<ul id="switch-question-version-list"></ul>');
					for(var i = 97; i < 97 + numVersions; i++)
					{
						$list.append('<li><a href="#">' + String.fromCharCode(i) + '</a></li>');
					}
					page.append($list);*/
				}
				
				var questionPage = $('<div class="question"></div>');
				page.append(questionPage);
				// question has multiple page items
				if(question.items.length > 1)
				{
					// media left, text right
					if(question.items[0].component === 'MediaView' && question.items[1].component === 'TextArea')
					{
						page.addClass('page-layout-2');
						//questionPage.append(formatPageItemMediaView(question.items[0]));
						createPageItemMediaView(question.items[0], questionPage);
						questionPage.append(formatPageItemTextArea(question.items[1]));
					}
					// text left, media right
					else if(question.items[0].component === 'TextArea' && question.items[1].component === 'MediaView')
					{
						page.addClass('page-layout-4');
						//questionPage.append(formatPageItemMediaView(question.items[1]));
						createPageItemMediaView(question.items[1], questionPage);
						questionPage.append(formatPageItemTextArea(question.items[0]));
					}
					// media left, media right
					else if(question.items[0].component === 'MediaView' && question.items[1].component === 'MediaView')
					{
						page.addClass('page-layout-9');
						createPageItemMediaView(question.items[0], questionPage);
						createPageItemMediaView(question.items[1], questionPage);
					}
					// text left, text right
					else if(question.items[0].component === 'TextArea' && question.items[1].component === 'TextArea')
					{
						page.addClass('page-layout-6');
						questionPage.append(formatPageItemTextArea(question.items[0]));
						questionPage.append(formatPageItemTextArea(question.items[1]));
					}
				}
				// question has a single page item
				else
				{
					switch(question.items[0].component)
					{
						case 'MediaView':
							page.addClass('page-layout-7');
							//questionPage.append(formatPageItemMediaView(question.items[0]));
							createPageItemMediaView(question.items[0], questionPage);
							break;
						case 'TextArea':
							page.addClass('page-layout-1');
							questionPage.append(formatPageItemTextArea(question.items[0]));
							break;
					}
				}
				//page.append(questionPage);

				// build answer form input
				switch(question.itemType)
				{
					case 'MC':
						page.append('<h3>Choose one of the following answers:</h3>');
						page.append(buildMCAnswers(question.questionID, question.answers));
						// listen to answer clicks (resets all previous listeners first)
						$('.answer-list :input').die('click', onAnswerRadioClicked).live('click', onAnswerRadioClicked);

						break;
					case 'QA':
						page.append('<h3>Input your answer and click "Save Answer":</h3>');
						var $form = $('<div id="qa-form" class="shortanswer"></div>');
						$form.append('<input id="qa-input" placeholder="Enter answer here"></input>');
						$form.append('<a href="#" class="button disabled" id="submit-qa-answer-button" role="button">Save Answer</a>');
						page.append($form);
						break;
					case 'Media':
						break;
				}
				
				//$('#content').append(page);
				break;
		}
	};
	
	// selects a previous mc answer or fills in a short answer for when the user navigates
	// back to a previously answered question.
	var selectPreviousAnswer = function()
	{
		var prevResponse = obo.model.getPreviousResponse();
		
		if(prevResponse != undefined)
		{
			switch(obo.model.getPageObject().itemType.toLowerCase())
			{
				case 'mc':
					selectMCAnswer(prevResponse);
					break;
				case 'qa':
					fillInQAAnswer(prevResponse);
					break;
				case 'media':
					updateInteractiveScoreDisplay(prevResponse);
					break;
			}
		}
	}
	
	// runs the js needed to update remote, display score results and feedback
	var onAnswerRadioClicked = function(event)
	{
		setPageAsAnswered(obo.model.getSection(), obo.model.getPage());
		
		var answer = obo.util.getAnswerByID(obo.model.getPageObject().answers, event.target.value);
		// we don't let the user resubmit the question they just clicked on (c'mon, that's just silly)
		if(answer != undefined && event.target.value != obo.model.getPreviousResponse())
		{
			obo.model.submitQuestion(answer.answerID);
			selectMCAnswer(answer.answerID);
			
			return true;
		}
		
		return false;
	};
	
	// graphically selects an mc answer. doesn't call remote.
	var selectMCAnswer = function(answerID)
	{
		debug.time('mc');
		// hide existing score bubble:
		$('.answer-preview').remove();
		
		// animate, then remove existing feedback:
		$('.answer-feedback').css('border-bottom', 'none').slideUp(defaultAnimationDuration, function() {
			$(this).remove();
		});
		
		// jquery doesn't like selecting ids with periods, which are in newer answerIDs.
		// we need to escape the period using two backslashes (\\)
		// http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F
		var $ans = $('#' + answerID.replace('\.', '\\\.'));
		var $ansLi = $ans.parent();
		var answer = obo.util.getAnswerByID(obo.model.getPageObject().answers, answerID);
		
		if(typeof answer !== 'undefined')
		{
			// automatically check the radio button if it's not already checked.
			// (if we need to check it then we assume we are displaying a previous response,
			//	and therefore we also want to cancel any animations)
			var useAnimations = $ans.prop('checked');
			$ans.prop('checked', true);
			
			// only show if we are in practice or we are in preview mode
			if(obo.model.getSection() === 'practice' || obo.model.getMode() === 'preview')
			{
				var weightCSS = ''
				switch(answer.weight)
				{
					case '100': case 100:
						weightCSS = 'answer-preview-correct';
						break;
					case '0': case 0:
						weightCSS = 'answer-preview-wrong';
						break;
				}
				
				$bubble = $('<span ' + (useAnimations ? 'style="display:none;" ' : '') + 'class="answer-preview '+ weightCSS +'">'+answer.weight+'%</span>');
				$ansLi.append($bubble);
				$bubble.fadeIn();
			}
			
			// animate show feedback if it exists:
			if(answer.feedback)
			{
				//@TODO - this is probably slowing this function way down, need to cache this
				var feedbackText = obo.util.cleanFlashHTML(answer.feedback);
				if(feedbackText.length > 0)
				{
					var $feedback = $('<span ' + (useAnimations ? 'style="display:none;" ' : '') + ' class="answer-feedback"><h4>Review:</h4><p>' + feedbackText + '</p></span>');
					$ansLi.append($feedback);
					$feedback.slideDown(defaultAnimationDuration);
				}
			}
		}
		debug.timeEnd('mc');
	};
	
	var saveQAResponse = function()
	{
		// save response if we are on a short answer question and it is actively being edited:
		if($('#qa-form').length > 0 && $('#qa-input').attr('disabled') === undefined)
		{
			setPageAsAnswered(obo.model.getSection(), obo.model.getPage());
			var response = $.trim($('#qa-input').val().toLowerCase());
			obo.model.submitQuestion(response);
			//fillInQAAnswer(response);
		}
	}
	
	// graphically fill out a qa answer, doesn't call remote.
	var fillInQAAnswer = function(response)
	{
		response = response.toLowerCase();
		
		// hide existing score bubble or feedback:
		$('.answer-preview').remove();
		$('.answer-feedback').remove();
		
		var $qaForm = $('#qa-form');
		
		// fill in the text if not already supplied (if this is called to recall a previous response)
		var $input = $('#qa-input');
		if($input.val() != response)
		{
			$input.val(response);
			setQAFormMode('saved');
		}
		
		var p = obo.model.getPageObject();
		if(obo.model.getMode() === 'preview')
		{
			var weightCSS = 'answer-preview-wrong';
			var weight = 0;
			for(var i in p.answers)
			{
				if(response === p.answers[i].answer.toLowerCase())
				{
					weightCSS = 'answer-preview-correct';
					weight = 100;
					break;
				}
			}
			
			$qaForm.prepend('<span class="answer-preview '+ weightCSS +'">'+weight+'%</span>');
			
			// show feedback if it exists:
			if(weight < 100 && p.feedback && p.feedback.incorrect && p.feedback.incorrect.length > 0)
			{
				$qaForm.append($('<span class="answer-feedback"><h4>Review:</h4>' + obo.util.cleanFlashHTML(p.feedback.incorrect) + '</span>'));
			}
			
			updateQAButton();
		}
	}
	
	// toggles between editing an answer or having a saved answer. mode = 'editing' | 'saved'
	var setQAFormMode = function(mode)
	{
		$a = $('#submit-qa-answer-button');
		$input = $('#qa-input');
			
		switch(mode)
		{
			case 'editing':
				$input.removeAttr('disabled');
				$input.focus();
				$input.select();
				$a.html('Save Answer');
				$input.attr('data-last-val', $input.val());
				break;
			case 'saved':
				$a.focus();
				$input.attr('disabled', 'disabled');
				$input.attr('data-last-val', $input.val());
				$a.html('Edit Answer');
				break;
		}
	};
	
	var updateQAButton = function()
	{
		if($('#qa-input').val().length === 0)
		{
			$('#submit-qa-answer-button').addClass('disabled');
		}
		else
		{
			$('#submit-qa-answer-button').removeClass('disabled');
		}
	}
	
	var updateInteractiveScore = function(score)
	{
		setPageAsAnswered(obo.model.getSection(), obo.model.getPage());
		
		var oldScore = obo.model.getPreviousResponse();
		debug.log('updateInteractiveScore', score, oldScore);
		obo.model.submitQuestion(score, true);
		
		updateInteractiveScoreDisplay(score, oldScore);
	}
	
	var updateInteractiveScoreDisplay = function(score, oldScore)
	{
		var html = '';
		var showScore;
		if(obo.model.getSection() === 'practice' || obo.model.getMode() === 'preview')
		{
			// display the score
			html = 'Current Score:<p>' + score + '%</p>';
			showScore = true;
		}
		else
		{
			// only display that the score was updated
			html = 'Question score updated';
			showScore = false;
		}
		
		debug.log('abcd');
		
		// flashy graphics if the score is updated
		if(isNaN(oldScore) || (!isNaN(oldScore) && oldScore != score))
		{
			debug.log('jkl');
			
			// for the first update fade in:
			if($('.answer-preview').length === 0)
			{
				debug.log('123');
				
				$answerPreview = $('<div style="display: none;" class="answer-preview">' + html + '</div>');
				$('.question').append($answerPreview);
				if(showScore)
				{
					$answerPreview.fadeIn();
				}
				else
				{
					$answerPreview.fadeIn().delay(1000).fadeOut();
				}
			}
			else
			{
				debug.log('456');
				
				if(showScore)
				{
					// pulse the answer-preview
					$('.answer-preview').html(html);
					/*
					$('.answer-preview').animate({backgroundColor: '#a2b6c4'}, 200).animate({backgroundColor: '#d6d6d6'}, 200).animate({backgroundColor: '#a2b6c4'}, 200).animate({backgroundColor: '#d6d6d6'}, 200);
					$('.answer-preview p').animate({backgroundColor: '#a2b6c4'}, 200).animate({backgroundColor: '#efe1a8'}, 200).animate({backgroundColor: '#a2b6c4'}, 200).animate({backgroundColor: '#efe1a8'}, 200);*/
					//$('.answer-preview').animate({fontSize: 21}, 200).animate({fontSize: 20}, 200);
					//$('.answer-preview p').animate({backgroundColor: 'white'}, 200).animate({backgroundColor: '#efe1a8'}, 200).animate({backgroundColor: 'white'}, 200).delay(1000).animate({backgroundColor: '#efe1a8'}, 2000);
					$('.answer-preview p')
						.animate({color: '#b3e99d'}, defaultAnimationDuration)
						.animate({color: 'white'}, defaultAnimationDuration)
						.animate({color: '#b3e99d'}, defaultAnimationDuration)
						.animate({color: 'white'}, defaultAnimationDuration);
				}
				else
				{
					$('.answer-preview').fadeIn().delay(3000).fadeOut();
				}
			}
		}
		
	}
	
	var buildMCAnswers = function(questionID, answers)
	{
		var answersHTML = $('<ul class="answer-list multiplechoice"></ul>');
		$(answers).each(function(itemIndex, answer)
		{
			var answerText = $.trim(obo.util.cleanFlashHTML(answer.answer));
			// take extra step to remove containing p tags from answer text
			// convert lone <p>blah</p> tags to 'blah'
			var pattern = /^<p.*?>(.*)<\/p>$/gi;
			answerText = answerText.replace(pattern, '$1');
			
			// blank answers mess up padding, let's fix that:
			if(answerText.length === 0)
			{
				answerText = '&nbsp;';
			}
			
			answersHTML.append('<li class="answer"><input id="' + answer.answerID + '" type="radio" name="QID-'+questionID+'" value="'+answer.answerID+'"><label for="'+answer.answerID+'">' + answerText + '</label></li>');
			/*$(event.target).parent().append('<span class="answer-preview '+ weightCSS +'">'+answer.weight+'%</span><span class="answer-feedback"><h4>Review:</h4><p>' + cleanFlashHTML(answer.feedback) + '</p></span>');*/
		});
		return answersHTML;
	};
	
	var formatPageItemTextArea = function(pageItem, strictHTMLConversion)
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
		
		pageItemHTML.append(obo.util.cleanFlashHTML(pageItem.data, strictHTMLConversion));
		return pageItemHTML;
	};
	
	var createPageItemMediaView = function(pageItem, $target)
	//var formatPageItemMediaView = function(pageItem)
	{
		//var mediaHTML = displayMedia(pageItem.media[0]);
		if($media = obo.media.createMedia(pageItem.media[0], $target, pageItem.options))
		{
			if(pageItem.options)
			{
				$media.addClass('custom-page-item');
				formatCustomLayoutPageItem($media, pageItem);
			}
			else
			{
				$media.addClass('page-item');
			}
			
			return $media;
		}
	};
	
	var formatCustomLayoutPageItem = function(pageItemHTML, pageItem)
	{
		var p = Number(pageItem.options.padding);
		pageItemHTML.width(pageItem.options.width - p * 2);
		pageItemHTML.height(pageItem.options.height - p * 2);
		pageItemHTML.css('left', pageItem.options.x);
		pageItemHTML.css('top', pageItem.options.y);
		pageItemHTML.css('padding', p);
		if(pageItem.options.borderColor != -1)
		{
			pageItemHTML.css('border', '1px solid ' + obo.util.getRGBA(pageItem.options.borderColor, pageItem.options.borderAlpha));
		}
		else
		{
			pageItemHTML.css('border', '0');
		}
		if(pageItem.options.backgroundColor != -1)
		{
			pageItemHTML.css('background-color', obo.util.getRGBA(pageItem.options.backgroundColor, pageItem.options.backgroundAlpha));
		}

		if(pageItem.media.length > 0)
		{
			//Resize and center media element inside container div
			var $mediaElement = pageItemHTML.children(":first");
			var containerWidth = pageItemHTML.width();
			var containerHeight = pageItemHTML.height();

			var scale = Math.min(Math.min(containerWidth, pageItem.media[0].width) / pageItem.media[0].width, Math.min(containerHeight, pageItem.media[0].height) / pageItem.media[0].height);
			var w = pageItem.media[0].width * scale;
			var h = pageItem.media[0].height * scale;
			$mediaElement.width(w).height(h);
			
			// we need to wrap this mediaElement in a div to give it position
			// (some media, like swfs, will replace the placeholder with the actual media
			// which will clear out our positioning css)
			$mediaPositionWrapper = $('<div></div>');
			$mediaPositionWrapper.css('top', ((containerHeight - h) / 2));
			$mediaElement.wrap($mediaPositionWrapper);
		}

		return pageItemHTML;
	};
	/*
	var createMediaOLD = function(mediaItem, $target)
	{
		
		
		//$target = $('#swf-holder');
		//$('object').css('visibility', 'hidden');
		//$('.media-item').width(400).height(300);
		
		var $mediaElement = $('<div class="media-item-standin"></div>');
		$target.append($mediaElement);
		
		// we wrap this in a 1ms set timeout so that we can perform dimension calculations
		// on $mediaElement, since apparently it's not instantenously in the DOM.
		var $swfHolder = $('#swf-holder-2');
		var $mediaElement2 = $('<div class="media-item"></div>');
		$('.media-item').css('visibility', 'hidden');
		$('.media-item object').css('visibility', 'hidden');
		//$('.media-item').hide();
		$swfHolder.prepend($mediaElement2);
		setTimeout(function() {
			var targetPage = obo.model.getSection() + obo.model.getPage();
			var $targetMediaItem = null;
			debug.log('lookum for', targetPage);
			$('.media-item').each(function(index, item) {
				debug.log($(item).attr('data-page'));
				if($(item).attr('data-page') === targetPage)
				{
					debug.log('daddy found it sweetums!');
					$targetMediaItem = $(item);
				} 
			});
			if($targetMediaItem != null)
			{
				//$targetMediaItem.show();
				$targetMediaItem.css('visibility', 'visible');
				$targetMediaItem.find('object').css('visibility', 'visible');
			}
			else
			{
				obo.media.createMedia(mediaItem, $mediaElement2);
				var o = $mediaElement.offset();
				debug.log('o=', o);
				$mediaElement2.offset({left: o.left});
				$('#swf-holder').offset({top: o.top});
			}
			
		}, 1);
		
		
		//return $mediaHTML;
		return $mediaElement;
	};*/
	
	// update history pushes to HTML5 history. 
	// browers that don't support it simply don't modify the history.
	var updateHistory = function(url, replaceState)
	{
		//alert('update history');
		debug.log('______________updateHistory', baseURL + '#' + getHashURL(), replaceState);
		if(Modernizr.history)
		{
			var newURL = baseURL + '#' + getHashURL();
			//console.log('compare newURL', newURL, ' to', lastHistoryState);
			//if(newURL != lastHistoryState)
			//{
				if(replaceState === true)
				{
					history.replaceState(null, null, newURL);
				}
				// only push if we're not adding a duplicate url to the stack:
				else
				{
					history.pushState(null, null, baseURL + '#' + getHashURL());
				}
			//}
			
			
			//lastHistoryState = newURL;
		}
	};
	
	var makePageNav = function(section, $target)
	{
		// clear previous subnav
		$('.subnav-list').remove();
		debug.log('>>>', $('.nav-list .prev-page-button'));
		$('#nav-list .prev-page-button').remove();
		$('#nav-list .next-page-button').remove();
		
		var $pList = $('<ul class="subnav-list ' + section + '"></ul>');
		$target.append('<a class="page-navigation prev-page-button" href="#">Prev</a>').append($pList).append('<a class="page-navigation next-page-button" href="#">Next</a>');
		
		return $pList;
	}
	
	// makes the content page nav if none exists
	var makeContentPageNav = function() 
	{
		//if($('.nav-P-1').length === 0)
		if($('.subnav-list.content').length === 0)
		{
			//var pList = $('<ul class="subnav-list content"></ul>');
			//$('#nav-content').parent().append('<a class="page-navigation prev-page-button" href="#">Prev</a>').append(pList).append('<a class="page-navigation next-page-button" href="#">Next</a>');
			var pList = makePageNav('content', $('#nav-content').parent());
		
			var lo = obo.model.getLO();
			$(lo.pages).each(function(index, page){
				index++
				var pageHTML = $('<li ' + (isPageVisited('content', index) === true ? 'class="visited"' : '') + '><a class="subnav-item nav-P-'+index+'"  href="'+ baseURL +'#/content/' + index + '" title="'+ obo.util.strip(page.title) +'">' + index +'</a></li>');
				pList.append(pageHTML);
				pageHTML.children('a').click(onNavPageLinkClick);
			});
			//appendFinishButton(pList);
			
			// @TODO - a js hack to 
			/*setTimeout(function() {
				$($('.page-layout h2')[0]).css('padding-bottom', (pList.height() + 40) + 'px');
			}, 1);*/
		}
	};
	
	// make the practice page nav if none exists
	var makePracticePageNav = function() 
	{
		if($('.subnav-list.practice').length === 0)
		{
			//var qListHTML = $('<ul class="subnav-list practice"></ul>');
			//$('#nav-practice').parent().append(qListHTML);
			var qListHTML = makePageNav('practice', $('#nav-practice').parent());
			
			var lo = obo.model.getLO();
			$(obo.model.getPageObjects()).each(function(index, page)
			{
				index++;
				var qLink = $('<li '+(isPageVisited('practice', index) === true ? 'class="visited"' : '')+'><a class="subnav-item nav-PQ-'+index+'" href="'+ baseURL +'#/practice/' + index + '" title="Practice Question '+index+'">' + index +'</a></li>');
				qListHTML.append(qLink)
				qLink.children('a').click(onNavPageLinkClick);
			});
			//appendFinishButton(qListHTML);
		}
	};
	
	var makeAssessmentPageNav = function() 
	{
		// @TODO - does this work with question alts?
		// rebuild page nav if it doesn't exist or it has the wrong number of items
		var numItems = $('.subnav-list.assessment li').length;
		if($('.subnav-list.assessment').length === 0 || (obo.model.getNumPagesOfSection('assessment') != numItems))
		{
			//var qListHTML = $('<ul class="subnav-list assessment"></ul>');
			//$('#nav-assessment').parent().append(qListHTML)
			var qListHTML = makePageNav('assessment', $('#nav-assessment').parent());
			
			var lo = obo.model.getLO();
			$(obo.model.getPageObjects()).each(function(qIndex, pageGroup)
			{
				qIndex++;
				var $li = $('<li></li>');
				if(isPageVisited('assessment', qIndex))
				{
					$li.addClass('visited');
				}
				if(obo.model.isPageAnswered('assessment', qIndex))
				{
					$li.addClass('answered');
				}
				$li.append($('<a class="subnav-item nav-AQ-'+qIndex+'" href="'+ baseURL +'#/assessment/' + qIndex + '" title="Assessment Question '+qIndex+'">' + qIndex +'</a>'));
				qListHTML.append($li);
				$li.children('a').click(onNavPageLinkClick);
				// add nav for preview mode to show alts
				if(obo.model.getMode() === 'preview' && pageGroup.length > 1 )
				{
					var altListHTML = $('<ul class="subnav-list-alts"></ul>');
					$li.append(altListHTML)
					$(pageGroup).each(function(altIndex, page)
					{
						// skip the first - its shown right above this
						if(altIndex === 0)
						{
							return true;
						}

						var altVersion = String.fromCharCode(altIndex + 97);
						var altLink = $('<li><a class="subnav-item-alt nav-AQ-'+qIndex+altVersion+'" href="'+ baseURL +'#/assessment/' + qIndex + altVersion+'" title="Assessment Question '+qIndex+' Alternate '+ altVersion+'">'+ altVersion +'</a></li>');
						altListHTML.append(altLink);
						altLink.children('a').click(onNavPageLinkClick);
					});
				}
			});
			
			// append finish button:
			$finishButton = $('<li><a id="finish-section-button" title="Finish this section" class="subnav-item" href="' + baseURL + '#/assessment/end/">Finish</a></li>');
			$finishButton.click(onFinishSectionButtonClick);
			qListHTML.append($finishButton);
		}
		
	};
	
	var onFinishSectionButtonClick = function(event)
	{
		event.preventDefault();
		if(!$(event.target).hasClass('disabled'))
		{
			obo.model.gotoPage('end');
		}
	}
	
	var onNavPageLinkClick = function(event)
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
				case 'PQ':
					obo.model.gotoPage(parseInt(RegExp.$2));
					break
				case 'AQ':
					obo.model.gotoPage(RegExp.$2 + RegExp.$3);
					break;
			}
		}
	};
	
	// add a class to a subnav item:
	var markSubnavItem = function(section, pageNumber, cssClass)
	{
		var selectedLinkID = '';
		switch(section)
		{
			case 'content': selectedLinkID = '.nav-P-' + pageNumber; break;
			case 'practice': selectedLinkID = '.nav-PQ-' + pageNumber; break;
			case 'assessment': selectedLinkID = '.nav-AQ-' + pageNumber; break;
		}
		
		var $e = $(selectedLinkID);
		if($e.length > 0)
		{
			$e.parent('li').addClass(cssClass);
		}
	};
	
	// removes cssClass from a subnav
	// @TODO: Does this work for the 'you missed a page or two' copies?
	var resetSubnav = function(cssClass)
	{
		$('.subnav-list li').removeClass(cssClass);
	}
	
	var setPageAsVisited = function(section, pageNumber)
	{
		if(!isNaN(pageNumber))
		{
			visited[section][pageNumber - 1] = true;
		}
		
		markSubnavItem(section, pageNumber, 'visited');
	};
	
	// flag a question page as having been answered (view only)
	var setPageAsAnswered = function(section, pageNumber)
	{
		if(!isNaN(pageNumber))
		{
		//	answered[section][pageNumber - 1] = true;
			markSubnavItem(section, pageNumber, 'answered');
		}
	}
	
	var setPageAsSelected = function(section, pageNumber)
	{
		markSubnavItem(section, pageNumber, 'selected');
	};
	
	var isPageVisited = function(section, pageNumber)
	{
		return visited[section][pageNumber - 1];
	};
	
	var getNumPagesVisited = function(section)
	{
		return visited[section].join('').split('true').length - 1;
	};
	
	var getScoreMethodString = function()
	{
		switch(obo.model.getScoreMethod())
		{
			case 'm': return 'Average';
			case 'h': return 'Highest';
			case 'r': return 'Most recent';
		}
	}
	
	// @PUBLIC:
	var init = function($element)
	{
		debug.log('view::init');
		loadUI($element);
	};
	
	var render = function()
	{
		debug.log('VIEW.RENDER');
		debug.time('render');
		var section = obo.model.getSection();
		
		if(unrendered)
		{
			
		}

		// clear out old content
		// @TODO wht is this ??? selectedLinkID = '.nav-P-' + page;
		$('#content').empty();
		
		var p = obo.model.getPage();
		
		switch(section)
		{
			case 'overview':
				buildPage('overview');
				hideSubnav();
				hideNextPrevNav();
				//updateHistory('overview/');
				break;
			case 'content':
				//$('#nav-content')[0].href = baseURL + 'page/' + p + '/';
				makeContentPageNav();
				buildPage('content', p);
				//updateHistory('page/' + p + '/');
				break;
			case 'practice':
				//$('#nav-content')[0].href = baseURL + 'practice/' + p + '/';
				if(p != 'start')
				{
					makePracticePageNav();
				}
				buildPage('practice', p);
				//updateHistory('practice/' + p + '/');
				// fill out a previous response:
				selectPreviousAnswer();
				break;
			case 'assessment':
				//$('#nav-content')[0].href = baseURL + 'assessment/' + p + '/';
				// we don't want to make the page nav until after startAssessment is called
				// (since before then we don't know which pages have been answered)
				if(p != 'start')
				{
					makeAssessmentPageNav();
				}
				buildPage('assessment', p);
				//updateHistory('assessment/' + p + '/');
				// fill out a previous response:
				selectPreviousAnswer();
				if(obo.model.isInAssessmentQuiz())
				{
					lockoutNonAssessment();
				}
				// disable next/prev links on the first and last natural pages
				$('.page-navigation li a').removeClass('disabled');
				if(p === 1)
				{
					$('.prev-page-button').addClass('disabled');
				}
				
				break;
		}
		
		// change the selected nav item if we are rendering a new section
		var $n = $('#nav-' + section);
		if($n.find('.selected').length === 0)
		{
			$('#nav-list li').removeClass('selected');
			$n.parent('li').addClass('selected');
		}
		
		// mark the page as both visited and selected
		if(section != 'overview')
		{
			setPageAsVisited(section, p);
			setPageAsSelected(section, p);
		}
		
		// $.history.load(sectionHashes[currentSection]);*/
		
		// actions to take for the first render:
		if(unrendered)
		{
			unrendered = false;
			
			// we want to force the user to the assessment if they are 
			// coming back from a previously un-submitted attempt.
			if(obo.model.isResumingPreviousAttempt())
			{
				// @TODO - we lock out assessment but they could still get in?
				lockoutSections(['content', 'practice']);
				
				/*
				obo.dialog.showOKDialog('Resume assessment attempt', 'You are in the middle of an assessment attempt.', true, 'Jump to Assessment', function() {
					// @TODO - we lock out assessment but they could still get in?
					lockoutSections(['content', 'practice']);
					obo.model.gotoSection('assessment');
				});*/
				/*The last time you visited "Citing Sources Using MLA Style" you were in the middle of an assessment attempt, navigate to the assessment section to continue where you left off.*/
				obo.dialog.showDialog({
					title: 'Resume assessment attempt',
					contents: 'The last time you visited <strong>"' + obo.model.getTitle() + '"</strong> you were in the middle of an assessment attempt. Visit the assessment section to continue where you left off.',
					modal: true,
					width: 600,
					buttons: [
						{label: 'Jump to Assessment', action: function() {
							if(!(obo.model.getSection() === 'assessment' && obo.model.getPage() === 'start'))
							{
								obo.model.gotoSectionAndPage('assessment', 'start');
							}
						}}
					]
				});
			}
			else
			{
				// notify user of a previous score import
				var importableScore = obo.model.getImportableScore();
				if(importableScore > -1)
				{
					/*
					obo.dialog.showYesNoDialog('Import previous score', $('<p>You already take this, import?</p>'), true, 'Review Content', null, 'Jump to Assessment', function() {
						obo.model.gotoSection('assessment');
					});*/
					obo.dialog.showDialog({
						title: 'Import previous score',
						contents: 'You have previously completed this Learning Object for a different course and your instructor is allowing you to import your high score of <strong>' + importableScore + '%</strong>.<br><br>Visit the Assessment section to view your scoring options.',
						width: 570,
						buttons: [
							{label:'Review Content'},
							{label: 'Jump to Assessment', action: function() {
								if(!(obo.model.getSection() === 'assessment' && obo.model.getPage() === 'start'))
								{
									obo.model.gotoSectionAndPage('assessment', 'start');
								}
							}}
						]
					})
				}
			}
		}
		
		// @TODO
		//setTimeout(function() {
			/*
			var pList = $('.subnav-list');
				$($('#content')[0]).css('margin-top', (pList.height() + 40) + 'px');
			*/
		//}, 1);
		
		if(!preventUpdateHistoryOnNextRender)
		{
			updateHistory();
		}
		else
		{
			debug.log('________History update prevented!');
			// we still replace history to make sure we have a correct hash url:
			updateHistory('whu', true);
			preventUpdateHistoryOnNextRender = false;
		}
		
		
		/*
		setTimeout(function() {
			debug.log('sup');
			$('#content-blocker').activity({
				width: 15,
				length: 15
			});
		}, 10);*/
		
		//$('.oml').tipTip({delay: 0, fadeIn: 0, fadeOut: 0});
		//$('.subnav-list.content .subnav-item').tipTip({delay: 0, fadeIn: 0, fadeOut: 0});
		
		hideThrobber();
		
		debug.timeEnd('render');
	};
	
	// use this when attempting to display content over swfs using gpu wmodes
	var hideSWFs = function()
	{
		$('object').css('visibility', 'hidden');
		$('object').parent().addClass('stripe-bg');
	};
	
	// returns the currently overlayed swf (if there is one)
	// in the practice or assessment sections
	var getOverlayedSWF = function()
	{
		return $('#swf-holder .media-for-page-' + obo.model.getSection() + obo.model.getPage() + ' object');
	}
	
	var unhideSWFs = function()
	{
		// unhide all content objects:
		$('#content object').css('visibility', 'visible');
		$('#content object').parent().removeClass('stripe-bg');
		
		// unhide overlay swfs for the current page
		getOverlayedSWF().css('visibility', 'visible').parent().removeClass('stripe-bg');
	};
	
	// tosses up a error dialog. error can be a string message or an error object
	var displayError = function(error)
	{
		// @TODO: make it better:
		var m = '';
		if(typeof error === 'object')
		{
			m = '#' + error.errorID + ': ' + error.message;
		}
		else
		{
			m = error;
		}
		
		debug.log('ERROR: ' + error + ',' + m);
		
		//obo.dialog.showOKDialog('ERROR', $('<p>' + m + '</p>'), false, 'OK');
		obo.dialog.showOKDialog({
			title: 'ERROR',
			contents: m
		});
		
		hideThrobber();
	};
	
	// notify the user when something went wrong, but we're not sure what
	var displayGenericError = function()
	{
		obo.dialog.showOKDialog({
			title: 'Oops!',
			contents: 'There was a problem, please try again.'
		});
		
		hideThrobber();
	};
	
	return {
		init: init,
		render: render,
		hideSWFs: hideSWFs,
		unhideSWFs: unhideSWFs,
		displayError: displayError,
		displayGenericError: displayGenericError,
		updateInteractiveScore: updateInteractiveScore
	};
}();