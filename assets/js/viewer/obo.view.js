/**
	The view acts as a view/controller and should write out HTML code and generally
	modify the look of the app. The controller logic communicates with the model, then
	the model asks to view to update (via render()).
	
	View can ignore the model for any events that are entirely view-specifc.
	The view also acts as a model for any entirely view-specific information (ie visitedPages)
*/

// @TODO (General)
// - linkize
// - createTime = NAN will freak out the JSON encoder (happens on editited questions)
// - AUTOLOAD_FLASH must = true
// - add in http:// on external link

if(!window.obo)
{
	window.obo = {};
}

obo.view = function()
{
	// @TODO: Make sure this is defined when we set it
	// for convience keep a reference to the URL sans fake # URL
	var baseURL = obo.util.getBaseURL();
	//var baseURL = location.origin + location.pathname + location.search;
	// @TODO can this be made more elegant?
	// this is needed if the user uses the back/foward buttons.
	// in this case we don't want to modify to the history stack,
	// but move through it instead.
	var preventUpdateHistoryOnNextRender = false;
	
	// number in ms that defines how long simple animations should take place
	var defaultAnimationDuration = 200;
	
	// flag used to specify the first time render() is called.
	// the view is 'unrendered' until the first render() completes.
	var unrendered = true;

	// flag to easily keep track if swfs are being hidden
	// (via hideSwfs and unhideSwfs)
	var swfsHidden = false;

	// we need to hold on to this timeout id so we can clear the timeout.
	// this prevents an issue where two quick showThrobber calls might
	// prematurely timeout the throbber
	var hideThrobberTimeoutId = -1;

	// Keep track of when we last asked CredHub for badge info.
	// Default to infinity - checks to see if the cred hub request
	// is expired will always pass.
	var lastCredHubRequestTime = Number.POSITIVE_INFINITY;

	// listen for postMessage events from media items
	var onPostMessage = function(event) {
		var data = $.parseJSON(event.data);

		// materia:
		if(typeof data.type !== 'undefined' && data.type === 'materiaScoreRecorded')
		{
			// loop through the window.frames object hunting for our iframe
			var $materiaIFrames = $('.materia-container');
			var $materiaIFrame;
			var len = $materiaIFrames.length;
			var $targetIFrame;

			for(var i = 0; i < len; i++)
			{
				$materiaIFrame = $($materiaIFrames[i]);

				if(event.source === window.frames[$materiaIFrame.attr('name')])
				{
					$targetIFrame = $materiaIFrame;
					break;
				}
			}
			if(typeof $targetIFrame !== 'undefined')
			{
				var $parent = $targetIFrame.parent();
				var targetSection = $parent.attr('data-section');
				var targetPage = $parent.attr('data-page');

				// we only show scores for interactive questions
				var p = obo.model.getPageObject(targetPage, targetSection);
				if(typeof p.itemType !== 'undefined' && p.itemType.toLowerCase() === 'media')
				{
					if(obo.model.getMode() === 'preview')
					{
						if(typeof data.score !== 'undefined')
						{
							updateInteractiveScore(parseInt(data.score), targetPage, targetSection);
						}
					}
					else
					{
						showThrobber();
						obo.model.updateQuestionScoreForCurrentAttempt(targetSection, targetPage, function(score) {
							// if we don't get back the response we expect we err on the side of the student
							// and give them the score
							if(score === false)
							{
								if(typeof data.score !== 'undefined')
								{
									updateInteractiveScore(parseInt(data.score, 10), targetPage, targetSection);
								}
							}
							else
							{
								markSubnavItem(targetSection, targetPage, 'answered');
								updateInteractiveScoreDisplay(score);
							}

							hideThrobber();
						});
					}
				}
			}
		}
	};
	if(typeof window.addEventListener !== 'undefined')
	{
		window.addEventListener('message', onPostMessage, false);
	}
	else if(typeof window.attachEvent !== 'undefined')
	{
		window.attachEvent('onmessage', onPostMessage);
	}
	
	var loadUI = function($element)
	{
		$element.append($('#template-main').html());
		setupUI();
	}
	
	var setupUI = function()
	{
		// update copyright year
		var d = new Date();
		$('.copyright-year').html(d.format('yyyy'));

		// listen for history events
		window.onpopstate = function(event) {
			preventUpdateHistoryOnNextRender = true;
			gotoPageFromURL();
		};
		
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
			hideSWFs();
		}).on('mouseleave', '.subnav-list.assessment li:has(ul)', function(event) {
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
			var pageID = $(event.currentTarget).attr('data-page-id');
			if(obo.model.getSection() != 'content')
			{
				obo.model.gotoSectionAndPage('content', pageID);
			}
			else
			{
				obo.model.gotoPage(pageID);
			}
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
			event.preventDefault();
			if(!$(event.target).hasClass('disabled'))
			{
				
				obo.model.gotoNextPage();
			}
		});
		// End live events.
		
		$('#log-out-button').click(function(event) {
			event.preventDefault();

			obo.model.logout();
		});

		// inject preview header if needed
		if(obo.model.getMode() === 'preview')
		{
			$('body').addClass('preview-mode');
			$('#wrapper').prepend('<div id="preview-mode-container"></div>');
			$('#preview-mode-container').append($('#template-preview-mode-notification').html());

			$('#enable-teach-view input')
				.change(function(event) {})
				.click(function(event) {
					obo.model.setTeachView($(this).is(':checked'));
				});
			if(obo.model.isTeachView())
			{
				setTeachViewSwitch(true, false);
			}
			$('#teach-view-switch').mousedown(function() {
				if(obo.model.isInAssessmentQuiz())
				{
					obo.dialog.showDialog({
						title: 'Notice',
						contents: 'The assessment quiz has been rebuilt.',
						center: true,
						closeCallback: function() {
							obo.dialog.closeDialogs();
							// @HACK: wipe out assessment captivate overlays
							if($('#swf-holder-assessment').length > 0)
							{
								$('#swf-holder-assessment').remove();
							}
							obo.captivate.clearCaptivateData('assessment');
							toggleTeachViewSwitch();
						},
						buttons: [
							{label: 'OK'}
						]
					});
				}
				else
				{
					toggleTeachViewSwitch();
				}
			});
			$('#preview-mode-help-container a').click(function(event) {
				event.preventDefault();
				togglePreviewModeHelp();
			});
			$('#preview-mode-bar a').click(function(event) {
				event.preventDefault();
				togglePreviewModeHelp();
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
		$('#nav-overview').attr('href', baseURL + '#/overview');
		$('#nav-content').attr('href', baseURL + '#/content/1');
		$('#nav-practice').attr('href', baseURL + '#/practice/start');
		$('#nav-assessment').attr('href', baseURL + '#/assessment/start');
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
	}

	var togglePreviewModeHelp = function() {
		var $help = $('#preview-mode-help-container');
		if(!$help.hasClass('showing'))
		{
			$('#preview-mode-help-container a').html('Click anywhere to close');
			$help.addClass('showing');
			hideSWFs();
			$help.stop().animate({
				top: 20
			}, defaultAnimationDuration * 3, function() {
				$('html').bind('click', togglePreviewModeHelp);
			});
			
		}
		else
		{
			$('#preview-mode-help-container a').html('More info');
			$help.removeClass('showing');
			$help.animate({
				top: -354
			}, defaultAnimationDuration * 3, function() {
				unhideSWFs();
			});
			$('html').unbind('click', togglePreviewModeHelp);
		}
	};

	var toggleTeachViewSwitch = function()
	{
		if($('#teach-view-switch').hasClass('enabled'))
		{
			setTeachViewSwitch(false);
		}
		else
		{
			setTeachViewSwitch(true);
		}
	};

	var setTeachViewSwitch = function(enabled, animate)
	{
		var $elem = $('#teach-view-switch');
		var duration = typeof animate !== 'undefined' && animate === false ? 0 : defaultAnimationDuration;

		$elem.removeClass('enabled');
		if(enabled)
		{
			$elem.addClass('enabled');
			$('#teach-view-switch-dial').stop().animate({
					left: '34px'
				}, duration, function() {
					obo.model.setTeachView(true);
				});
		}
		else
		{
			$elem.removeClass('enabled');
			$('#teach-view-switch-dial').stop().animate({
					left: '2px'
				}, duration, function() {
					obo.model.setTeachView(false);
				});
		}
	};
	
	// grabs the hash url and navigates to the page specified.
	// if possible, go to the page and return true
	// we assume the 'start' page is desired if no page index is specified
	var gotoPageFromURL = function()
	{
		var section = '';
		var pg = '';
		
		if(location.hash.length > 1)
		{
			var hashURL = location.hash.substr(1); //chop off '#'
			var rawTokens = hashURL.split('/');
			// need to remove empty strings
			var tokens = [];
			var len = rawTokens.length;
			for(var i = 0; i < len; i++)
			{
				if(rawTokens[i] !== '')
				{
					tokens.push(rawTokens[i]);
				}
			}
			if(tokens.length > 0)
			{
				section = tokens[0].toLowerCase();
				if(tokens.length > 1 && section != 'overview')
				{
					pg = tokens[1];
					obo.model.gotoSectionAndPage(tokens[0].toLowerCase(), pg);
				}
				else
				{
					obo.model.gotoSection(tokens[0].toLowerCase());
				}

				return;
			}
		}

		// catchall:
		obo.model.gotoSection('overview');
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
		return '/' + p.section + (typeof p.page === 'undefined' ? '' : '/' + p.page);
	}

	var getNextPageHashURL = function()
	{
		var p = obo.model.getNextPage();
		return '/' + p.section + (typeof p.page === 'undefined' ? '' : '/' + p.page);
	}
	
	var showThrobber = function(timeoutThrobber, fadeInThrobber)
	{
		// prevent an issue with two quick showThrobber calls - the first call
		// can timeout the second
		clearTimeout(hideThrobberTimeoutId);

		if(typeof fadeInThrobber === 'undefined')
		{
			fadeInThrobber = true;
		}

		$('body').append($('<div id="content-blocker"></div>'));
		$('#content').animate({
			opacity: .5
		},
		{
			duration: fadeInThrobber ? 1000 : 1
		});
		$('#content-blocker').animate({
			opacity: 1
		},
		{
			duration: fadeInThrobber ? 1000 : 1
		});

		if(typeof timeoutThrobber === 'undefined' || timeoutThrobber === true)
		{
			// we want to automatically give up if we never get a response after 10 seconds
			// (Just so we don't kill the website with an overlay)
			hideThrobberTimeoutId = setTimeout(hideThrobber, 10000);
		}
	};
	
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
		obo.captivate.clearCaptivateData('practice');
		obo.captivate.clearCaptivateData('assessment');
		
		obo.model.startAssessment();
	}
	
	var submitAssessment = function()
	{
		// @HACK: wipe out assessment captivate overlays
		if($('#swf-holder-assessment').length > 0)
		{
			$('#swf-holder-assessment').remove();
		}

		// clear out cred hub request time
		lastCredHubRequestTime = Number.POSITIVE_INFINITY;
		
		unlockNonAssessment();
		showThrobber(false, false);
		// @TODO: What happens if the server takes a dump?  Should I not clear out assessment?
		//viewState.assessment = []; // clear out assessment answers so new attempts are empty

		obo.model.submitAssessment();
		obo.captivate.clearCaptivateData('assessment');
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
	};
	
	var lockoutSections = function(sections)
	{
		var $li;
		var $a;
		var origTitle;
		var len = sections.length;
		for(var i = 0; i < len; i++)
		{
			$li = $('#nav-' + sections[i]).parent();
			$li.addClass('lockedout');
			// @TODO - This is really buggy on un-lockout
			//$($li.children()[0]).attr('data-lockout-message', 'You can visit this section after you complete the assessment quiz.');
		}
	};
	
	var lockoutNonAssessment = function()
	{
		lockoutSections(['overview', 'content', 'practice']);
	};
	
	var unlockNonAssessment = function()
	{
		var $li;
		var $a;
		$('.lockedout').each(function () {
			$li = $(this);
			$li.removeClass('lockedout');
			$($li.children()[0]).removeAttr('data-lockout-message');
		});
	};
	
	var buildOverviewPage = function()
	{
		$('#content').append($('#template-overview-page').html());
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
	};
	
	// this creates our 'you missed a page or two' subnav list for anything not 'visited'
	var createUnvisitedPageList = function()
	{
		// we added in 'finish', remove it when cloning
		var missedPages = $('.subnav-list > li:not(.visited)');
		missedPages.clone().appendTo('.missed-pages-list');
		$('.missed-pages-list a').click(onNavPageLinkClick);
	};
	
	// this creates our 'you missed a page or two' subnav list for anything not 'answered'
	var createUnansweredPageList = function()
	{
		var unanswered;

		// we added in 'finish', remove it when cloning
		if(obo.model.getSection() === 'assessment')
		{
			unanswered = $('.subnav-list.assessment > li:not(:last-child)').filter(function(index) {
				var $this = $(this);
				if($this.hasClass('answered'))
				{
					return false;
				}
				return $this.find('li.answered').length === 0;
			});
		}
		else
		{
			unanswered = $('.subnav-list > li:not(.answered)');
		}
		unanswered.clone().appendTo('.missed-pages-list');
		$('.missed-pages-list a').click(onNavPageLinkClick);
	};
	
	var buildPage = function(section, index)
	{
		// @TODO captivateSwitch
		// $('#swap-cap').hide();
		
		switch(section)
		{
			case 'overview':
				buildOverviewPage();
				break;

			case 'content':
				buildContentPage(index);
				break;

			case 'practice':
				buildQuestionPage(section, index);
				break;

			case 'assessment':
				buildQuestionPage(section, index);
				break;

		}
	};
	
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
			if(obo.model.getNumPagesWithViewStateProperty('content', 'visited') === obo.model.getLO().pages.length)
			{
				// all content pages seen
				$('#content').append($('#template-final-content-page-complete').html());
			}
			else
			{
				// some content pages missed
				$('#content').append($('#template-final-content-page-incomplete').html());
				createUnvisitedPageList();
				$('.next-section-button').attr('href', baseURL + '#/practice/start');
				hideSubnav();
			}
		}
		// standard page (1 - n)
		else
		{
			showAndUpdateNextPrevNav();
			
			var page = obo.model.getLO().pages[index - 1];
			
			var $pageHTML = $('<div id="content-'+index+'" class="page-layout page-layout-'+page.layoutID+'"></div>');
			buildPageHeader($pageHTML, page.title.length > 0 ? page.title : 'Page ' + index);
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

			// loop through each page item
			$(pageItems).each(function(itemIndex, item)
			{
				switch(item.component)
				{
					case 'MediaView':
						createPageItemMediaView(item, $target);
						break;

					case 'TextArea':
						$target.append(formatPageItemTextArea(item, parseInt(page.layoutID, 10) === 8));
						break;

				}
			});

			$('.text-item li').each(function(index, item)
			{
				if($(item).height() >= 30)
				{
					$(item).parent().find('li').css('padding-bottom', '.8em');
					return true;
				}
			});
			
			if(String(page.layoutID) === '8')
			{
				$pageHTML.append($target);
				$pageHTML.append('<span class="attribution">' + obo.util.createCombinedAttributionString(page.items) + '</span>');
			}
		}
	};
	
	var buildPageHeader = function($target, title)
	{
		$target.append('<h2>' + title + '</h2>');
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
						$('#content').append($('#template-practice-overview').html());

						var n = obo.model.getNumPagesOfSection('practice');
						$('.icon-dynamic-background').text(n).next().prepend(n + ' ');
						$('#start-practice-button').attr('href', baseURL + '#/practice/1');
						break;

					case 'assessment':
						$('#content').append($('#template-assessment-overview').html());

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
						var practiceMissed = obo.model.getNumPagesOfSection('practice') - obo.model.getNumPagesWithViewStateProperty('practice', 'answered');
						showMissingPractice =  practiceMissed > 0;

						// determine which content pages weren't seen
						var contentMissed = obo.model.getNumPagesOfSection('content') - obo.model.getNumPagesWithViewStateProperty('content', 'visited');
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
							$('.assessment-missed-section').hide();
							$('#assessment-info').hide();
							$('#assessment-info-closed').show();
							$('.assessment-close-date').html(obo.model.getInstanceCloseDate().format('mm/dd/yy "at" h:MM:ss TT'));
						}
						else if(numAttempts === 0)
						{
							$('.assessment-missed-section').hide();
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
							// we use css() instead of show() due to a FF 3.6 bug that was setting display to 'inline'
							$('.assessment-import-score-section').css('display', 'block');
							$('.assessment-missed-section').hide();

							$('.previous-score').html(importableScore);
							$('#do-import-previous-score-button').click(function(event) {
								event.preventDefault();

								var score = obo.model.getImportableScore();
								obo.dialog.showDialog({
									title: 'Confirm Score Importing: ' + score + '%',
									contents: '<strong>WARNING:</strong> Importing will forfeit all of your attempts, setting your final score for this learning object to <strong>' + score + '%</strong><br><br>Are you sure you want to import your previous score?',
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
							});

							// disable start assessment until they choose an option
							$('#start-assessment-button').addClass('disabled');
						}

						// modify 'start assessment' button if they are resuming assessment
						if(obo.model.isResumingPreviousAttempt())
						{
							$('#start-assessment-button').html('Resume Assessment <span class="triange-right"></span>');
						}

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
						if(obo.model.getNumPagesWithViewStateProperty('practice', 'answered') >= obo.model.getNumPagesOfCurrentSection())
						{
							// all practice questions answered
							$('#content').append($('#template-final-practice-page-complete').html());
							showSubnav();
						}
						else
						{
							// some practice questions not answered
							$('#content').append($('#template-final-practice-page-incomplete').html());
							createUnansweredPageList();
							$('.next-section-button').attr('href', baseURL + '#/assessment/start');
							hideSubnav();
						}
						break;

					case 'assessment':
						// check the number of visited questions vs the number of questions
						$('#content').append($('#template-final-assessment-page-complete').html());
						if(obo.model.getNumPagesWithViewStateProperty('assessment', 'answered') >= obo.model.getNumPagesOfCurrentSection())
						{
							// all practice questions seen
							showSubnav();
						}
						else
						{
							// some practice questions missed
							createUnansweredPageList();
							$('#submit-assessment-button').attr('href', baseURL + '#/assessment/scores');
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
					$('#content').append($('#template-score-results').html());
					$('#return-to-overview-button').attr('href', baseURL + '#/assessment/start');
					var recentScoreObject = scores[scores.length - 1];
					var recordedScore = obo.model.getFinalCalculatedScore();
					var attemptsRemaining = obo.model.getNumAttemptsRemaining();

					if(scores.length > 1)
					{
						$('#score-results').addClass('multiple-attempts');

						// build score table
						var endDate;
						var len = scores.length;
						for(var i = 0; i < len; i++)
						{
							endDate = new Date(scores[i].endTime * 1000);
							$('#attempt-history').append(
								'<tr><td>' + (parseInt(i, 10) + 1) + '.</td>' +
								'<td>' + scores[i].score + '%</td>' +
								'<td>' + endDate.format('mm/dd/yy - h:MM:ss TT') + '</td></tr>'
							);
						}
					}

					$('#attempt-score-result h2').html('Attempt ' + scores.length + ' Score:');
					$('#attempt-score').html(recentScoreObject.score + '%');
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

					// special case: we should hide the close notice if there are no access dates
					if(obo.model.instanceHasNoAccessDates())
					{
						$('#assessment-close-notice').hide();
					}

					var badgeInfo = obo.model.getBadgeInfo();
					if(badgeInfo)
					{
						$('#badge-info').show();

						if(badgeInfo.awarded)
						{
							var now = (new Date()).getTime();
							if(now - lastCredHubRequestTime <= _credhubTimeout)
							{
								$('#badges').show();
								createCredHubIFrameFromParams(badgeInfo.params);
								lastCredHubRequestTime = now;
							}
							else
							{
								$('.badges-expired').show();
							}
						}
						else
						{
							var minScore = parseFloat(badgeInfo.minScore);
							$('.badge-not-awarded').show();
							$('.badge-min-score').html(minScore == 100 ? '100%' : minScore + '% or higher');
						}
					}

					// we clear out the subnav so no item looks to be 'answered'
					// we could delete the subnav instead if that's faster
					resetSubnav('answered');
					resetSubnav('visited');
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

				var question = obo.model.getPageObject();
				
				// init container
				var page = $('<div id="' + baseid + '-' + index + '" class="question-page question-type-' + question.itemType + '"></div>');
				buildPageHeader(page, sectionName+' Question ' + index + ':');
				
				$('#content').append(page);
				
				// add switcher for questions with alternates:
				if(obo.model.currentQuestionIsAlternate())
				{
					page.append('<span id="question-alt-notice">(Question Alternate)</span>');
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
						createPageItemMediaView(question.items[0], questionPage);
						questionPage.append(formatPageItemTextArea(question.items[1]));
					}
					// text left, media right
					else if(question.items[0].component === 'TextArea' && question.items[1].component === 'MediaView')
					{
						page.addClass('page-layout-4');
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
							createPageItemMediaView(question.items[0], questionPage);
							break;

						case 'TextArea':
							page.addClass('page-layout-1');
							questionPage.append(formatPageItemTextArea(question.items[0]));
							break;
					}
				}

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
						page.append('<h3>Input your answer:</h3>');
						var $form = $('<div id="qa-form" class="shortanswer"></div>');
						page.append($form);

						//answers, allowCheckAnswer
						var qaFormSettings = {
							allowCheckAnswer: sectionName.toLowerCase() === 'practice'
						};
						if(obo.model.isTeachView())
						{
							var answers = [];
							var len = question.answers.length;
							for(var i = 0; i < len; i++)
							{
								answers.push(question.answers[i].answer);
							}
							qaFormSettings.answers = answers;
						}
						$form.qaForm(qaFormSettings);
						$form
							.bind('save', function(event, answerText, saved) {
								markSubnavItem(obo.model.getSection(), obo.model.getPage(), 'answered');

								if(saved === true)
								{
									obo.model.submitQuestion(answerText);
								}

								if(obo.model.getSection() === 'practice')
								{
									obo.model.setViewStatePropertyForPage('feedback', true);
									fillInQAAnswer(answerText);
								}
							})
							.bind('update', function(event, answerText) {
								clearSubnavItem(obo.model.getSection(), obo.model.getPage(), 'answered');
								obo.model.submitUnsavedQuestion(answerText);
							})
							.bind('onShowFeedback', function(event) {
								obo.model.setViewStatePropertyForPage('feedback', true);
							})
							.bind('onHideFeedback', function(event) {
								obo.model.setViewStatePropertyForPage('feedback', false);
							})
							.bind('onShowAnswers', function(event) {
								obo.model.setViewStatePropertyForPage('show-answers', true);
							})
							.bind('onHideAnswers', function(event) {
								obo.model.setViewStatePropertyForPage('show-answers', false);
							});
						break;

					case 'Media':
						// If this is an interactive practice question go ahead and mark it as answered
						// so if the media is buggy it won't trigger any notices to the user.
						var section = obo.model.getSection();
						if(section === 'practice')
						{
							obo.model.setViewStatePropertyForPage('answered', true);
							markSubnavItem(section, obo.model.getPage(), 'answered');
						}
						break;

				}
				break;

		}
	};

	var createCredHubIFrameFromParams = function(params)
	{
		$form = $('<form style="display:none;" method="POST" target="badges" action="' + _credhubUrl + '"></form>');
		for(var paramName in params)
		{
			$form.append('<input type="hidden" name="' + paramName + '" value="' + params[paramName] + '">');
		}
		$form.append('<input type="submit" value="submit-form">');
		$('body').append($form);
		$form.submit();
		$form.remove();
	};

	// selects a previous mc answer or fills in a short answer for when the user navigates
	// back to a previously answered question.
	var selectPreviousAnswer = function()
	{
		var prevResponse = obo.model.getPreviousResponse();
		var p = obo.model.getPageObject();

		if(typeof p !== 'undefined' && typeof p.itemType !== 'undefined')
		{
			switch(p.itemType.toLowerCase())
			{
				case 'mc':
					selectMCAnswer(prevResponse);
					break;

				case 'qa':
					fillInQAAnswer(prevResponse);
					break;

				case 'media':
					// we don't want to update the score display if we are in real assessment,
					// since that will only flash 'question score updated'.
					if(obo.model.getSection() === 'practice' || obo.model.isTeachView())
					{
						updateInteractiveScoreDisplay(prevResponse);
					}
					break;

			}
		}
	}
	
	// runs the js needed to update remote, display score results and feedback
	var onAnswerRadioClicked = function(event)
	{
		markSubnavItem(obo.model.getSection(), obo.model.getPage(), 'answered');
		
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
		if(typeof answerID === 'undefined')
		{
			return;
		}

		debug.time('mc');
		// hide existing score bubble (but show teach-view-items):
		$('.answer-preview:not(.teach-view-item)').remove();
		$('.answer-preview.teach-view-item').show();
		
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
			if(obo.model.getSection() === 'practice')
			{
				// hide teach-view-item bubbles as well
				$ansLi.children('.answer-preview').hide();

				showScoreForMCElement($ansLi, answer.weight, useAnimations);
			}
			
			// animate show feedback if it exists:
			if(obo.model.getSection() !== 'assessment' && answer.feedback)
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

	var showScoreForMCElement = function($answerLi, weight, useAnimations, className)
	{
		var weightCSS = '';
		switch(weight)
		{
			case '100': case 100:
				weightCSS = 'answer-preview-correct';
				break;

			case '0': case 0:
				weightCSS = 'answer-preview-wrong';
				break;

		}
		
		$bubble = $('<span ' + (useAnimations ? 'style="display:none;" ' : '') + 'class="answer-preview '+ weightCSS +'">'+weight+'%</span>');
		
		if(typeof className !== 'undefined')
		{
			$bubble.addClass(className);
		}

		$answerLi.append($bubble);

		if(useAnimations)
		{
			$bubble.fadeIn();
		}
	};
	
	//@remove (some of this)
	// graphically fill out a qa answer, doesn't call remote.
	var fillInQAAnswer = function(response)
	{
		var savedAnswer;
		if(typeof response === 'undefined')
		{
			response = '';
			savedAnswer = false;
		}
		else
		{
			response = response.toLowerCase();
			savedAnswer = true;
		}

		response = typeof response === 'undefined' ? '' : response.toLowerCase();

		$('#qa-form').qaForm('setText', response, obo.model.getViewStatePropertyForPage('answered'));

		var p = obo.model.getPageObject();
		var weight = 0;
		var len = p.answers.length;
		for(var i = 0; i < len; i++)
		{
			if(response === p.answers[i].answer.toLowerCase())
			{
				weight = 100;
				break;
			}
		}
		var feedbackText;
		if(weight < 100 && p.feedback && p.feedback.incorrect && p.feedback.incorrect.length > 0)
		{
			feedbackText = obo.util.cleanFlashHTML(p.feedback.incorrect);
		}
		
		// show the feedback if that's where the user left off
		if(obo.model.getSection() !== 'assessment' && obo.model.getViewStatePropertyForPage('feedback'))
		{
			$('#qa-form').qaForm('showFeedback', weight, feedbackText);
		}
		if(obo.model.getViewStatePropertyForPage('show-answers'))
		{
			$('#qa-form').qaForm('showAnswers');
		}
		return;
	};
	
	var updateInteractiveScore = function(score, _page, _section)
	{
		if(typeof _section === 'undefined')
		{
			_section = obo.model.getSection();
		}
		if(typeof _page === 'undefined')
		{
			_page = obo.model.getPage();
		}

		// we only show these updates if this is actually an interactive question
		var p = obo.model.getPageObject(_page, _section);
		if(typeof p.itemType !== 'undefined' && p.itemType.toLowerCase() === 'media')
		{
			markSubnavItem(_section, _page, 'answered');
			var oldScore = obo.model.getPreviousResponse(_section, _page);
			obo.model.submitQuestion(score, true, _section, _page);

			if(_section === obo.model.getSection() && _page.toString() === obo.model.getPage().toString())
			{
				updateInteractiveScoreDisplay(score, oldScore);
			}
		}
	};
	
	var updateInteractiveScoreDisplay = function(score, oldScore)
	{
		if(typeof score === 'undefined')
		{
			return;
		}
		
		var html = '';
		var showScore;
		if(obo.model.getSection() === 'practice' || obo.model.isTeachView())
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

		// flashy graphics if the score is updated
		if(isNaN(oldScore) || (!isNaN(oldScore) && oldScore != score))
		{
			// for the first update fade in:
			if($('.answer-preview').length === 0)
			{
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
				if(showScore)
				{
					// pulse the answer-preview
					$('.answer-preview').html(html);
					var origColor = $('.answer-preview p').css('color');
					$('.answer-preview p')
						.animate({color: '#b3e99d'}, defaultAnimationDuration)
						.animate({color: origColor}, defaultAnimationDuration)
						.animate({color: '#b3e99d'}, defaultAnimationDuration)
						.animate({color: origColor}, defaultAnimationDuration);
				}
				else
				{
					$('.answer-preview').fadeIn().delay(3000).fadeOut();
				}
			}
		}

		if(obo.model.getSection() === 'assessment' && obo.model.isTeachView())
		{
			$('.answer-preview').addClass('teach-view-item');
		}
		
	};
	
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
			
			$ansLi = $('<li class="answer"><input id="' + answer.answerID + '" type="radio" name="QID-'+questionID+'" value="'+answer.answerID+'"><label for="'+answer.answerID+'">' + answerText + '</label></li>');
			answersHTML.append($ansLi);

			if(obo.model.isTeachView())
			{
				showScoreForMCElement($ansLi, answer.weight, false, 'teach-view-item');
			}
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
		if($media = obo.media.createMedia(pageItem, $target))
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
		var scaleFactor = .9;
		var p = Number(pageItem.options.padding);
		pageItemHTML.width((pageItem.options.width - p * 2) * scaleFactor);
		pageItemHTML.height((pageItem.options.height - p * 2) * scaleFactor);
		pageItemHTML.css('left', pageItem.options.x * scaleFactor);
		pageItemHTML.css('top', pageItem.options.y * scaleFactor);
		pageItemHTML.css('padding', p * scaleFactor);
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
	
	// update history pushes to HTML5 history. 
	// browers that don't support it simply don't modify the history.
	var updateHistory = function(replaceState)
	{
		var newURL = baseURL + '#' + getHashURL();
		if(replaceState === true)
		{
			history.replaceState(null, null, newURL);
		}
		// only push if we're not adding a duplicate url to the stack:
		else
		{
			history.pushState(null, null, baseURL + '#' + getHashURL());
		}
	};
	
	var makePageNav = function(section, $target)
	{
		// clear previous subnav
		$('.subnav-list').remove();
		$('#nav-list .prev-page-button').remove();
		$('#nav-list .next-page-button').remove();
		
		var $pList = $('<ul class="subnav-list ' + section + '"></ul>');
		$target
			.append('<a class="page-navigation prev-page-button" href="#">Prev</a>')
			.append($pList)
			.append('<a class="page-navigation next-page-button" href="#">Next</a>');
		
		return $pList;
	}
	
	// makes the content page nav if none exists
	var makeContentPageNav = function() 
	{
		if($('.subnav-list.content').length === 0)
		{
			var pList = makePageNav('content', $('#nav-content').parent());
		
			var lo = obo.model.getLO();
			$(lo.pages).each(function(index, page){
				index++

				var pageHTML = $('<li ' + (obo.model.getViewStatePropertyForPage('visited', 'content', index) === true ? 'class="visited"' : '') + '><a class="subnav-item nav-P-'+index+'"  href="'+ baseURL +'#/content/' + index + '" title="'+ obo.util.strip(page.title) +'">' + index +'</a></li>');
				pList.append(pageHTML);
				pageHTML.children('a').click(onNavPageLinkClick);
			});
		}
	};
	
	// make the practice page nav if none exists
	var makePracticePageNav = function() 
	{
		if($('.subnav-list.practice').length === 0)
		{
			var qListHTML = makePageNav('practice', $('#nav-practice').parent());
			
			var lo = obo.model.getLO();
			$(obo.model.getPageObjects()).each(function(index, page)
			{
				index++;
				var qLink = $('<li><a class="subnav-item nav-PQ-'+index+'" href="'+ baseURL +'#/practice/' + index + '" title="Practice Question '+index+'">' + index +'</a></li>');
				if(obo.model.getViewStatePropertyForPage('visited', 'practice', index) === true)
				{
					qLink.addClass('visited');
				}
				if(obo.model.getViewStatePropertyForPage('answered', 'practice', index) === true)
				{
					qLink.addClass('answered');
				}
				qListHTML.append(qLink)
				qLink.children('a').click(onNavPageLinkClick);
			});
		}
	};
	
	var makeAssessmentPageNav = function()
	{
		var qListHTML = makePageNav('assessment', $('#nav-assessment').parent());
		
		var lo = obo.model.getLO();
		$(obo.model.getPageObjects()).each(function(qIndex, pageGroup)
		{
			qIndex++;
			var $li = $('<li></li>');
			if(obo.model.getViewStatePropertyForPage('visited', 'assessment', qIndex))
			{
				$li.addClass('visited');
			}

			if(obo.model.getViewStatePropertyForPage('answered', 'assessment', qIndex))
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
				$li.append(altListHTML);
				$(pageGroup).each(function(altIndex, page)
				{
					// skip the first - its shown right above this
					if(altIndex === 0)
					{
						return true;
					}

					var altVersion = String.fromCharCode(altIndex + 97);
					var altLink = $('<li><a class="subnav-item-alt nav-AQ-'+qIndex+altVersion+'" href="'+ baseURL +'#/assessment/' + qIndex + altVersion+'" title="Assessment Question '+qIndex+' Alternate '+ altVersion+'">'+ altVersion +'</a></li>');

					if(obo.model.getViewStatePropertyForPage('visited', 'assessment', qIndex + altVersion))
					{
						altLink.addClass('visited');
					}
					if(obo.model.getViewStatePropertyForPage('answered', 'assessment', qIndex + altVersion))
					//if(obo.model.isPageAnswered('assessment', qIndex + altVersion))
					{
						altLink.addClass('answered');
					}

					altListHTML.append(altLink);
					altLink.children('a').click(onNavPageLinkClick);
				});
			}
		});
		
		// append finish button:
		$finishButton = $('<li class="finish-button-container"><a id="finish-section-button" title="Finish this section" class="subnav-item" href="' + baseURL + '#/assessment/end/">Finish</a></li>');
		$finishButton.click(onFinishSectionButtonClick);
		qListHTML.append($finishButton);
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
			case 'content':
				selectedLinkID = '.nav-P-' + pageNumber;
				break;

			case 'practice':
				selectedLinkID = '.nav-PQ-' + pageNumber;
				break;

			case 'assessment':
				selectedLinkID = '.nav-AQ-' + pageNumber;
				break;

		}
		
		var $e = $(selectedLinkID);
		if($e.length > 0)
		{
			$e.parent('li').addClass(cssClass);
		}
	};

	var clearSubnavItem = function(section, pageNumber, cssClass)
	{
		var selectedLinkID = '';
		switch(section)
		{
			case 'content':
				selectedLinkID = '.nav-P-' + pageNumber;
				break;

			case 'practice':
				selectedLinkID = '.nav-PQ-' + pageNumber;
				break;

			case 'assessment':
				selectedLinkID = '.nav-AQ-' + pageNumber;
				break;

		}
		
		var $e = $(selectedLinkID);
		if($e.length > 0)
		{
			$e.parent('li').removeClass(cssClass);
		}
	};
	
	// removes cssClass from a subnav
	// @TODO: Does this work for the 'you missed a page or two' copies?
	var resetSubnav = function(cssClass)
	{
		$('.subnav-list li').removeClass(cssClass);
	};
	
	var getScoreMethodString = function()
	{
		switch(obo.model.getScoreMethod())
		{
			case 'm': return 'Average';
			case 'h': return 'Highest';
			case 'r': return 'Most recent';
		}
	};
	
	// @PUBLIC:
	var init = function($element)
	{
		// update google analytics
		if(typeof _gaq !== 'undefined')
		{
			debug.log('Google Mode: ' + obo.model.getMode());
			_gaq.push(['_setCustomVar', 2, 'mode', obo.model.getMode(), 2]);
		}

		loadUI($element);
	};
	
	var render = function()
	{
		debug.time('render');
		var section = obo.model.getSection();
		
		// clean up page:
		$('#content').empty();
		// clean up any overlayed captivates
		// @HACK we turn both parent and object visible for Safari
		$('#swf-holder object').css('visibility', 'hidden');
		$('#swf-holder .media-item').css('visibility', 'hidden');
		$('#swf-holder iframe').hide();
		// we also remove any flash alt text placeholders in the swf-holder
		// in case the user doesn't have flash installed
		$('#swf-holder .swf-placeholder').remove();
		
		var p = obo.model.getPage();
		
		switch(section)
		{
			case 'overview':
				buildPage('overview');
				hideSubnav();
				hideNextPrevNav();
				break;

			case 'content':
				makeContentPageNav();
				buildPage('content', p);
				break;

			case 'practice':
				if(p != 'start')
				{
					makePracticePageNav();
				}
				buildPage('practice', p);
				selectPreviousAnswer();
				break;

			case 'assessment':
				// we don't want to make the page nav until after startAssessment is called
				// (since before then we don't know which pages have been answered)
				if(p != 'start')
				{
					makeAssessmentPageNav();
				}
				buildPage('assessment', p);
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
			markSubnavItem(section, p, 'visited');
			markSubnavItem(section, p, 'selected');
		}
		
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
				
				if(section !== 'assessment')
				{
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
			}
			else
			{
				// notify user of a previous score import
				var importableScore = obo.model.getImportableScore();
				if(importableScore > -1)
				{
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
		
		if(!preventUpdateHistoryOnNextRender)
		{
			updateHistory();
		}
		else
		{
			// we still replace history to make sure we have a correct hash url:
			updateHistory(true);
			preventUpdateHistoryOnNextRender = false;
		}

		hideThrobber();
		
		// scroll to the top of the page
		window.scrollTo(0, 0);

		debug.timeEnd('render');

		if(typeof _gaq !== 'undefined')
		{
			
			var mode = obo.model.getMode();
			var id = mode == 'instance' ? obo.model.getLO().instanceData.instID : obo.model.getLO().loID;
			if(mode == 'instance') mode = 'view';
			debug.log('Google Url: /' + mode + '/' + id + getHashURL());
			_gaq.push(['_trackPageview', '/' + mode + '/' + id + getHashURL()]);
		}
		
	};
	
	// use this when attempting to display content over swfs using gpu wmodes
	var hideSWFs = function()
	{
		debug.log('hideSWFs');

		swfsHidden = true;

		$('object').css('visibility', 'hidden');
		$('object').parent().addClass('stripe-bg');

		$('iframe').hide();
		$('iframe').parent().addClass('stripe-bg');
	};
	
	// returns the currently overlayed swf (if there is one)
	// in the practice or assessment sections
	var getOverlayedMedia = function()
	{
		return $('#swf-holder .media-for-page-' + obo.model.getSection() + obo.model.getPage());
	}
	
	var unhideSWFs = function()
	{
		debug.log('unhideSWFs');

		swfsHidden = false;

		// unhide all content objects:
		$('#content object').css('visibility', 'visible');
		$('#content object').parent().removeClass('stripe-bg');
		$('#content iframe').show();
		$('#content iframe').parent().removeClass('stripe-bg');
		
		// unhide overlay swfs for the current page
		var $overlayedMedia = getOverlayedMedia();
		if($overlayedMedia.length > 0)
		{
			$overlayedMedia.children('object').css('visibility', 'visible');
			$overlayedMedia.children('iframe').show();
			$overlayedMedia.parent().removeClass('stripe-bg');
		}
	};

	// calls hideSWFs again if swfs are already hidden
	// needed if (for example) embedding a media after hideSWFs
	// has been called.
	var rehideSWFs = function()
	{
		if(swfsHidden)
		{
			hideSWFs();
		}
	}
	
	// tosses up a error dialog. error can be a string message or an error object
	// calls closeCallback if defined
	var displayError = function(error, closeCallback)
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

		var opts = {
			title: 'ERROR',
			contents: m
		};
		if(typeof closeCallback !== 'undefined')
		{
			opts.closeCallback = closeCallback;
		}
		obo.dialog.showDialog(opts);
		
		hideThrobber();
	};
	
	// notify the user when something went wrong, but we're not sure what
	var displayGenericError = function()
	{
		obo.dialog.showDialog({
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
		updateInteractiveScore: updateInteractiveScore,
		showThrobber: showThrobber,
		hideThrobber: hideThrobber,
		rehideSWFs: rehideSWFs
	};
}();
