/**
	The model holds the relevant data only. The model doesn't know anything about the
	HTML or the DOM.  It knows the view only to ask it to update itself.
	It shouldn't otherwise use functions in the view to manipulate the view.
	
	This module isn't self-run - you need to create an instance of it.
*/
if(!window.obo)
{
	window.obo = {};
}

obo.model = function()
{
	var VERIFY_TIME_SECONDS = 30;
	var IDLE_TIME_BEFORE_WARN_SECONDS = 1800000; //30 mins
	var IDLE_TIME_BEFORE_LOGOUT_SECONDS = 120; //1 minute

	var verifyTimeIntervalID;

	var $idleCountdown;

	var init = function(viewModule, options)
	{
		view = viewModule;
		opts = options;

		// pull data from local storage
		if(Modernizr.localstorage)
		{
			if(typeof localStorage.teachView !== 'undefined' && localStorage.teachView === 'true' || localStorage.teachView === 'false')
			{
				teachView = localStorage.teachView === 'true';
			}
		}

		// start the verify timer
		verifyTimeIntervalID = setInterval(onVerifyTime, VERIFY_TIME_SECONDS * 1000);

		// start the idle timer
		$.idleTimeout('body', '#continue-session-button', {
			idleAfter: IDLE_TIME_BEFORE_WARN_SECONDS,
			warningLength: IDLE_TIME_BEFORE_LOGOUT_SECONDS,
			failedRequests: 0, //hackish way to disable keepalive functionality
			onIdle: function() {
				obo.dialog.showDialog({
					title: 'Your session is about to expire',
					contents: 'This learning object will be locked in <span class="idle-countdown">' + IDLE_TIME_BEFORE_LOGOUT_SECONDS + ' seconds</span>.<br><br>Do you want to continue your session?',
					modal: true,
					closeButton: false,
					escClose: false,
					buttons: [
						{label: 'No, Logout', action:function() {
							logout();
						}},
						{label: 'Yes, Continue', id:'continue-session-button'}
					]
				});
			},
			onTimeout: function() {
				$idleCountdown = undefined;
				//logout("You have been logged out due to inactivity. Click 'OK' to log in again.");
				killPage("This learning object has been locked due to inactivity. Refresh this page to continue viewing this object.");
			},
			onCountdown: function(counter) {
				$('.idle-countdown').html(counter == 1 ? counter + ' second' : counter + ' seconds');
			}
		});
	};
	var logoutMessage = '';
	
	// @PRIVATE
	
	// A reference to the view
	var view;
	
	// which mode ('preview'|'instance')
	var mode;

	// if in preview mode this flag determines if they should see all assessment questions and answer weights
	var teachView = false;
	
	// the lo data object
	var lo;
	
	//var scores = [{score:55, startTime:0, endTime:443848530},{score:96, startTime:0, endTime:0},{score:88, startTime:0, endTime:0}];
	var scores = [];

	// we store practice and assessment questions in a special array.
	// useful for preview mode (we group qalts so we can display those easier)
	var questions = {
		practice: [],
		assessment: []
	};

	// a multi dimensional array storing questions and their alternates
	var assessmentQuestionsForTeachView = [];
	
	// we need to track the practice and asssessment responses to redisplay them
	// NOTE: These are indexed by page numbers so the first page (page 1) is at [1], not 0!
	var responses = {
		practice: [],
		assessment: []
	};

	// track the last answered question alternate for each assessment question
	// for preview mode.
	var lastResponse = [];
	
	// the current section ('overview', 'content', 'practice' or 'assessment')
	var section = '';
	
	// store which page is active for each section ('start', 'end', or 1-n)
	var pages = {
		overview: 'start',
		content: 'start',
		practice: 'start',
		assessment: 'start'
	};

	// store view information to a specific page (page is visited, page has feedback displayed, etc)
	var viewState = {
		overview: [],
		content: [],
		practice: [],
		assessment: []
	};

	// keep track of which practice page we are attempting to view when the questions
	// haven't been loaded yet.
	var pendingPracticeQuestionsLoadedForPage;
	
	// flag to represent if they are currently taking the assessment
	var inAssessmentQuiz = false;
	
	// in preview mode we want to store which question alternates we are viewing
	//var activeAssessmentQuestions = [];
	
	// holds callback reference for loadLO/loadInstance
	var loadCallback;
	
	// we need a flag to store if an attempt was imported this visit since we only get this data
	// when we first load.
	var attemptImportedThisVisit;
	
	var onVerifyTime = function()
	{
		if(mode == 'instance')
		{
			obo.remote.makeCall('getSessionValid', null, onGetSessionValid);
		}
		else
		{
			obo.remote.makeCall('getSessionRoleValid', ['LibraryUser','ContentCreator'], onGetSessionValid);
		}
	};

	var onGetSessionValid = function(result)
	{
		if(!(	result === true ||
				(	typeof result !== 'undefined' &&
					typeof result.validSession !== 'undefined' &&
					result.validSession === true &&
					typeof result.hasRoles === 'object' &&
					typeof result.hasRoles.length !== 'undefined' &&
					result.hasRoles.length > 0
				)
		)) {
			logout("You've been logged out. Click 'OK' to login again.");
		}
	};

	var startIdleTimer = function()
	{
		$.idleTimer(IDLE_TIME_BEFORE_WARN_SECONDS * 1000);
	};

	var isValidSection = function(_section)
	{
		return _section === 'overview' || _section === 'content' || _section === 'practice' || _section === 'assessment';
	};
	
	// returns true if the supplied page is in a valid format
	// technically this code would allow page '2b' for content, but this just
	// maps to 2, so it's harmless to allow it.
	var isValidPage = function(_section, page)
	{
		if(typeof _section === 'undefined')
		{
			_section = section;
		}
		if(typeof page === 'undefined')
		{
			page = pages[_section];
		}
		
		if(_section === 'overview')
		{
			return page === 'start';
		}
		else
		{
			return (_section === 'assessment' && page === 'scores') || page === 'start' || page === 'end' || pageIsNumericWithinBounds(_section, page);
		}
	};
	
	var canAccessSection = function(_section)
	{
		return isValidSection(_section) && ((inAssessmentQuiz && _section == 'assessment') || !inAssessmentQuiz);
	};
	
	// determines if a user can view a page, bascially a wrapper for
	// logic with inAssessmentQuiz
	var canAccessPage = function(_section, page)
	{
		if(_section === undefined)
		{
			_section = section;
		}
		if(page === undefined)
		{
			page = pages[_section];
		}
		
		if(isValidPage(_section, page))
		{
			if(inAssessmentQuiz)
			{
				// in a assessment quiz a user can only access numeric pages inside the assessment, OR the end page
				return _section === 'assessment' && (pageIsNumericWithinBounds(_section, page) || page === 'end');
			}
			else
			{
				// outside assessment a user can access only the start or score pages in assessment or any other section
				return (_section === 'assessment' && (page === 'start' || page === 'scores')) || _section != 'assessment';
			}
		}

		return false;
	};
	
	// returns true if the page is a numeric type and is within the bounds
	// of the section (ie would return false when checking if a page 20 exists for
	// a three question assessment)
	var pageIsNumericWithinBounds = function(_section, page)
	{
		if(_section === undefined)
		{
			_section = section;
		}
		if(page === undefined)
		{
			page = pages[_section];
		}
		
		var index = page;
		var altIndex = undefined;
		//var altIndex = '';
		// test to see if page is in the format <Number><Letter>
		/*
		if(isNaN(page) && page.length > 1 && !isNaN(page.substr(0, page.length - 1)))
		{
			index = page.substr(0, page.length - 1);
			//altIndex = page.substr(page.length);
		}*/
		// special case to allow '1b' for assessment in preview mode
		if(mode === 'preview' && _section === 'assessment' && isNaN(page))
		{
			var o = getAssessmentPageIndex(page);
			index = o.index + 1;
			altIndex = o.altIndex;
		}
		
		return 	index !== false &&
				!isNaN(index) &&
				index > 0 &&
				index - 1 < getNumPagesOfSection(_section) &&
				(altIndex === undefined || altIndex < questions.assessment[index - 1].length);
	};
	
	// utility function that turns a page like '2b' into {index:1, altIndex:1}
	//@TODO: currently only works up to 'z' (not 'aa', 'ab', etc)
	var getAssessmentPageIndex = function(page)
	{
		if(typeof page !== 'string')
		{
			page = String(page);
		}
		page = page.toLowerCase();

		// @TODO - save this regex maybe?
		var regex = /([0-9]+)([a-z]*)/;
		var match = regex.exec(page);
		
		if(match === null)
		{
			return false;
		}
		else
		{
			return {
				index: parseInt(match[1], 10) - 1,
				altIndex: (match[2] === '' || match[2].length > 1) ? 0 : match[2].charCodeAt(0) - 97
			}
		}
	};
	
	// @TODO: Should this just display all errors it gets???
	// inspects a server response for errors and respond accordingly
	// if the error requires specific action it will be taken (ie display error and log out)
	// otherwise it will just display the error
	// returns true if no errors found, false otherwise
	var processResponse = function(response)
	{
		debug.log('processResponse', response);
		if(obo.remote.isError(response))
		{
			switch(response.errorID)
			{
				case 1: // not logged in
					//view.displayError('You are not logged in! Click OK to login');
					logout("You are not logged in. Click 'OK' to login again.");
					// @TODO: Log the user out
					// @TODO: send client error
					break;
				case 4: // insufficient permissions
					killPage('You do not have permissions to access this object.');
					// @TODO: Log the user out
					// @TODO: send client error
					break;
				case 5: // bad visit key
					killPage('You have already opened this learning object in another viewer window.  Only one viewer window per learning object is allowed open at a time.');
					// @TODO: send client error
					break;
				case 2010: // instance closed
					view.displayError("The assessment for this object closed on " + obo.model.getInstanceCloseDate().format('mm/dd/yy "at" h:MM:ss TT.'));
					break;
				case 4006:
					//var externalSystemName = getExternalSystemName();
					//killPage("You cannot access this instance directly since it is being used in an external system. Please log into the external system instead.");
					window.location = obo.util.getWebURL() + 'error/no-access';
					break;
				default:
					view.displayError(response);
					break;
			}
			
			return false;
		}
		
		return true;
	};
	
	// event handler to get the lo
	var onGetLO = function(result)
	{
		if(processResponse(result) === true)
		{
			if(checkForValidLO(result, 'lo').length > 0)
			{
				//view.displayError('This is not a valid LO!');
				killPage('This Learning Object is not valid. Ensure that the URL is correct and try again.');
			}
			else
			{
				lo = result;
				processQuestions();
		
				// @TODO
				var options = {};
				if(options.useOpenDatabase)
				{
					var db = openDatabase('mydb', '1.0', 'my first database', 2 * 1024 * 1024);
					db.transaction(function (tx)
					{
						tx.executeSql('INSERT INTO los (id, loJSON) VALUES (?, ?)', [lo.loID, result]);
					});
				}
				if(options.useLocalStorage)
				{
					localStorage['lo'+lo.loID] = result;
				}
				
				// call the callback to let the view respond
				loadCallback();
			}
		}
	};
	
	var onLoadInstance = function(result)
	{
		if(processResponse(result) === true)
		{
			if(checkForValidLO(result, 'instance').length > 0)
			{
				killPage('This Learning Object is not valid. Ensure that the URL is correct and try again.');
			}
			else
			{
				lo = result;
				processQuestions();
				
				// populate previous scores:
				if(typeof lo.tracking !== 'undefined' && typeof lo.tracking.prevScores !== 'undefined' && typeof lo.tracking.prevScores.length !== 'undefined')
				{
					// we don't really need to clone this array
					scores = lo.tracking.prevScores;
				}
				
				// @TODO - is this a good idea?
				// set the scores page as the default if there are scores
				// and they are not in the middle of an assessment
				if(scores.length > 0 && !isResumingPreviousAttempt())
				{
					pages.assessment = 'scores';
				}
				
				loadCallback();
			}
		}
	};
	
	// checks lo for validity and returns an errors array (empty for no errors).
	// type = ('lo'|'instance')
	var checkForValidLO = function(lo, type)
	{
		var errors = [];
		
		try
		{
			if(parseInt(lo.loID, 10) < 1)
			{
				errors.push(100); // if the id is not a number above 0
			}
			if(lo.pages.length < 1)
			{
				errors.push(102); // there are no content pages
			}
			if(parseInt(lo.pGroup.qGroupID, 10) < 1)
			{
				errors.push(104); // the practice group id isnt above 0
			}
			if(type === 'instance')
			{
				if(Number(lo.aGroup.qGroupID) < 1)
				{
					errors.push(108); // assessment group id isnt above 0
				}
				if(parseInt(lo.instanceData.startTime, 10) === 0 && parseInt(lo.instanceData.endTime, 10) === 0 && lo.instanceData.externalLink.length === 0)
				{
					errors.push(111); // invalid start/end times
				}
			}
			else if(type === 'lo')
			{
				// if in preview mode, there needs to be practice and assessment questions
				if(lo.aGroup.kids.length < 1)
				{
					errors.push(110); // no assessment questions
				}
				if(lo.pGroup.kids.length < 1)
				{
					errors.push(106); // no practice questions			
				}
			}
		}
		catch(error)
		{
			errors.push(101);
		}
		
		// @TODO
		/*if(errors.length > 0)
		{
			losService.getOperation('trackClientError').send(102, '', errors)
		}*/
		
		return errors;
	};
	
	// instead of looking into pGroup or aGroup use the questions object, which
	// this function builds. in preview mode this will show multiple questions per index
	// in a multi-dimensional array.
	var processQuestions = function()
	{
		processPracticeQuestions();
		processAssessmentQuestions();
	};

	var processPracticeQuestions = function()
	{
		questions.practice = [];
		$(lo.pGroup.kids).each(function(index, page) {
			questions.practice.push([page]);
		});
	};
	
	var processAssessmentQuestions = function()
	{
		questions.assessment = [];

		if(mode === 'preview')
		{
			buildTeachViewAssessmentQuiz();

			if(teachView)
			{
				// copy our teach view questions to questions.assessment:
				var len = assessmentQuestionsForTeachView.length;
				for(var i = 0; i < len; i++)
				{
					questions.assessment[i] = assessmentQuestionsForTeachView[i];
				}
			}
			else
			{
				buildFakeAssessmentQuiz();
			}
		}
		else
		{
			var curQIndex = 0;
			$(lo.aGroup.kids).each(function(index, page)
			{
				questions.assessment[curQIndex] = [];
				questions.assessment[curQIndex].push(page);
				curQIndex++;
			});
		}
	};

	// creates a multi-dim array containing an array of question alts in an array of question
	// indicies
	var buildTeachViewAssessmentQuiz = function()
	{
		var curQIndex = 0;
		assessmentQuestionsForTeachView = [];
		$(lo.aGroup.kids).each(function(index, page)
		{
			index++;
			if(curQIndex != page.questionIndex || page.questionIndex === 0)
			{
				curQIndex++;
				assessmentQuestionsForTeachView.push([]);
			}
			assessmentQuestionsForTeachView[curQIndex - 1].push(page);
		});
	};

	// if in preview mode without teach view we need to build an assessment quiz
	// just like the server does when in instance mode.
	// options: randomized and keep vs reselect
	var buildFakeAssessmentQuiz = function()
	{
		var aGroup = obo.model.getLO().aGroup;
		var allowAlts = aGroup.allowAlts === "1" || aGroup.allowAlts === 1 || aGroup.allowAlts === true;
		var isRandom = aGroup.rand === "1" || aGroup.rand === 1 || aGroup.rand === true;
		var altMethod = aGroup.altMethod; //'r' (reselect) or 'k' (keep)
		var numQuestions;
		var i;
		var len;

		if(allowAlts && (altMethod === 'r' || questions.assessment.length === 0))
		{
			questions.assessment = [];
			len = assessmentQuestionsForTeachView.length;
			for(i = 0; i < len; i++)
			{
				numQuestions = assessmentQuestionsForTeachView[i].length;
				questions.assessment[i] = [assessmentQuestionsForTeachView[i][Math.floor(Math.random() * numQuestions)]];
			}
		}
		else if(questions.assessment.length === 0)
		{
			len = aGroup.kids.length;
			for(i = 0; i < len; i++)
			{
				questions.assessment[i] = [aGroup.kids[i]];
			}
		}

		if(isRandom)
		{
			questions.assessment.sort(function() { return 0.5 - Math.random(); });
		}
	};
	
	// attempts to set the location variables to new values.
	// will "downgrade" to the start page of sections
	// or go to the overview section if all else fails.
	// calls view.render when finished.
	var setLocation = function(newSection, newPage)
	{
		// we store where we are now, so we can send tracking logs
		// at the end if needed.
		var currentSection = section;
		var currentPage = pages[currentSection];

		debug.log('setLocation', newSection, newPage);

		if(newSection === undefined)
		{
			newSection = section;
		}
		// special case: if we have a garbage section then simply
		// redirect to the overview section.
		if(!isValidSection(newSection))
		{
			newSection = 'overview';
			newPage = 'start';
		}
		if(newPage === undefined)
		{
			newPage = pages[newSection];

			// special case: if user is navigating to a new section
			// make sure they don't return to the end page.
			// instead take them to the last page of that section.
			if(newPage === 'end')
			{
				switch(newSection)
				{
					case 'content': newPage = getNumPagesOfSection('content'); break;
					case 'practice': newPage = getNumPagesOfSection('practice'); break;
				}
			}
		}

		// special case - overview only has a start page
		if(newSection === 'overview')
		{
			newPage = 'start';
		}
		// special case - no start page for content
		if(newSection === 'content' && newPage === 'start')
		{
			newPage = 1;
		}

		// if the user is resuming a previous attempt, but they are not
		// yet in the assessment quiz then only allow them to view
		// the overview page, the start assessment page or the scores page:
		if(isResumingPreviousAttempt() && !inAssessmentQuiz)
		{
			if(newSection !== 'overview')
			{
				if(newSection === 'assessment')
				{
					if(newPage !== 'start' && newPage !== 'scores')
					{
						newPage = 'start'
					}
				}
				else
				{
					newSection = 'overview';
				}
			}
			else
			{
				newPage = 'start';
			}
		}

		// special case - attempting to view practice page but practice questions not loaded
		if(!inAssessmentQuiz && newSection === 'practice' && !isNaN(newPage) && typeof lo.pGroup.kids === 'undefined')
		{
			pendingPracticeQuestionsLoadedForPage = newPage;
			newSection = 'practice';
			newPage = 'start';
			//page = newPage;
			
		}

		if(canAccessSection(newSection))
		{
			if(canAccessPage(newSection, newPage))
			{
				// special case - dealing with question alternates:
				var assessPageIndex = getAssessmentPageIndex(newPage);
				if(typeof assessPageIndex.altIndex !== 'undefined')
				{
					//activequestions.assessment[assessPageIndex.index] = assessPageIndex.altIndex;

					// simplify "5a" to "5", "5B" to "5b"
					if(typeof newPage === 'string')
					{
						newPage = newPage.toLowerCase();
						if(newPage.indexOf("a") == newPage.length - 1)
						{
							newPage = newPage.substr(0, newPage.length - 1);
						}
					}
				}

				// special case - can't view scores page when no scores exist
				if(newSection === 'assessment' && newPage === 'scores' && obo.model.getScores().length === 0)
				{
					newPage = 'start';
				}

				debug.log('setLocation complete', newSection, newPage);
				
				section = newSection;
				pages[section] = newPage;
				/*
				if(typeof callback === 'function')
				{
					callback(true);
				}
				else
				{
					view.render();
				}*/
			}
			// if can't access the page we want then at least attempt to access the start page of this section:
			else if(newSection !== 'content' && canAccessPage(newSection, 'start'))
			{
				section = newSection;
				pages[section] = 'start';
			}
			else if(newSection === 'content' && canAccessPage(newSection, 1))
			{
				section = newSection;
				pages[section] = 1;
			}
		}

		// update, if needed
		if(section != currentSection || pages[section] != currentPage)
		{
			// update google analytics
			if(typeof _gaq !== 'undefined' && section != currentSection)
			{
				debug.log('Google Section: ' + getSection());
				_gaq.push(['_setCustomVar', 1, 'section', getSection(), 2]);
			}
			// send tracking logs
			if(mode === 'instance')
			{
				// track both if section changed
				if(section != currentSection)
				{
					var sectionIndex = getSectionIndex(newSection);
					var pageID = getPageID();
					
					// In order to make two consecutive tracking calls we wrap
					// them in a closure:
					var Caller = function(viewID, pageID, sectionIndex) {
						obo.remote.makeCall('trackSectionChanged', [viewID, sectionIndex], function(result) {
							if(processResponse(result))
							{
								if(typeof pageID !== 'undefined')
								{
									obo.remote.makeCall('trackPageChanged', [lo.viewID, pageID, sectionIndex], processResponse);
								}
							}
						});
					};
					new Caller(lo.viewID, pageID, sectionIndex);
				}
				else if(pages[section] != currentPage)
				{
					// we only track page changes on numeric pages:
					var pageID = getPageID();
					if(typeof pageID !== 'undefined')
					{
						obo.remote.makeCall('trackPageChanged', [lo.viewID, pageID, getSectionIndex()], processResponse);
					}
				}
			}
		}

		debug.log('setLocation result', section, pages[section]);

		// mark this page as visited (if a standard page)
		if(pageIsNumericWithinBounds())
		{
			setViewStatePropertyForPage('visited', true);
		}

		if(!isNaN(pendingPracticeQuestionsLoadedForPage))
		{
			startPractice();
		}
		else
		{
			// render
			view.render();
		}
	};

	var getViewStateForPage = function(_section, page)
	{
		if(typeof _section === 'undefined')
		{
			_section = section;
		}
		if(typeof page === 'undefined')
		{
			page = getPage();
		}

		if(typeof viewState[_section][page] === 'undefined')
		{
			return {};
		}

		return viewState[_section][page];
	};

	var getViewStatePropertyForPage = function(property, _section, page)
	{
		var state = getViewStateForPage(_section, page);
		if(typeof state === 'undefined' || typeof state[property] === 'undefined')
		{
			return undefined;
		}

		return state[property];
	};

	var setViewStatePropertyForPage = function(property, value, _section, page)
	{
		if(typeof _section === 'undefined')
		{
			_section = section;
		}
		if(typeof page === 'undefined')
		{
			page = getPage();
		}

		if(typeof viewState[_section][page] === 'undefined')
		{
			viewState[_section][page] = {};
		}

		viewState[_section][page][property] = value;
	};

	// returns the number of "true"s for property in viewState[section]
	var getNumPagesWithViewStateProperty = function(_section, property)
	{
		var total = 0;
		for(var i in viewState[_section])
		{
			if(typeof viewState[_section][i] !== 'undefined' && typeof viewState[_section][i][property] !== 'undefined' && viewState[_section][i][property] === true)
			{
				total++;
			}
		}

		return total;
	};
	
	var getPrevSection = function()
	{
		switch(section)
		{
			case 'overview':
			case 'content': return 'overview';
			case 'practice': return 'content';
			case 'assessment': return 'practice';
		}
	};
	
	var getNextSection = function()
	{
		switch(section)
		{
			case 'overview': return 'content';
			case 'content': return 'practice';
			case 'practice':
			case 'assessment': return 'assessment';
		}
	};
	
	var getSectionIndex = function(_section)
	{
		if(typeof _section === 'undefined')
		{
			_section = section;
		}

		switch(_section)
		{
			case 'overview': return 0;
			case 'content': return 1;
			case 'practice': return 2;
			case 'assessment': return 3;
		}
		
		return -1;
	};

	var loadPractice = function(result)
	{
		// we'll have a result if this was called via trackSubmitStart
		if(mode === 'preview' || (result && processResponse(result)))
		{
			if(mode === 'instance')
			{
				// overwrite our pGroup with the set from trackSubmitSTart
				lo.pGroup.kids = result;
				processPracticeQuestions();
			}
		}

		if(!isNaN(pendingPracticeQuestionsLoadedForPage))
		{
			var newPage = pendingPracticeQuestionsLoadedForPage;
			pendingPracticeQuestionsLoadedForPage = undefined;
			setLocation('practice', newPage);
			
		}
		else
		{
			setLocation('practice', 1);
		}
	};
	
	var loadAssessment = function(result)
	{
		// we'll have a result if this was called via trackSubmitStart
		if(mode === 'preview' || (result && processResponse(result)))
		{
			if(mode === 'instance')
			{
				// overwrite our aGroup with the set from trackSubmitStart
				lo.aGroup.kids = result;
				processAssessmentQuestions();
				// tease out the saved answers from the response
				var len = result.length;
				for(var i = 0; i < len; i++)
				{
					if(typeof result[i].savedAnswer !== 'undefined' && result[i].savedAnswer !== null)
					{
						var index = parseInt(i, 10) + 1;
						switch(result[i].itemType.toLowerCase())
						{
							case 'mc':
								if(typeof result[i].savedAnswer.answerID !== 'undefined' && result[i].savedAnswer.answerID !== null)
								{
									responses['assessment'][index] = result[i].savedAnswer.user_answer;
									setViewStatePropertyForPage('answered', true, 'assessment', index);
								}
								break;
							case 'qa':
								if(typeof result[i].savedAnswer.user_answer !== 'undefined' && result[i].savedAnswer.user_answer !== null)
								{
									responses['assessment'][index] = result[i].savedAnswer.user_answer;
									setViewStatePropertyForPage('answered', true, 'assessment', index);
								}
								break;
							case 'media':
								if(typeof result[i].savedAnswer.user_answer !== 'undefined' && result[i].savedAnswer.user_answer !== null)
								{
									responses['assessment'][index] = parseInt(result[i].savedAnswer.user_answer, 10);
									setViewStatePropertyForPage('answered', true, 'assessment', index);
								}
								break;
						}
						
					}
				}

				// By deleting the practice questions we ensure that returning to the practice
				// section will start a new attempt and re-grab the questions.
				// If we don't do this then LTI integrations won't have the correct AttemptID!
				delete lo.pGroup.kids;
			}
			else if(mode === 'preview' && !teachView)
			{
				// rebuild fake assessment quiz if not in teach view
				buildFakeAssessmentQuiz();
			}

			// clear out the practice section (since we'll be starting a new attempt)
			clearPractice();
			pages['practice'] = 'start';

			// insert a local score record (to be completed later)
			scores.push({
				score: 0,
				startTime: parseInt(new Date().getTime() / 1000, 10),
				endTime: 0
			});
		
			inAssessmentQuiz = true;

			//setSection('assessment');
			//setPageOfCurrentSection(1);
			setLocation('assessment', 1);
		}
	};
	
	var onSubmitAssessment = function(result)
	{
		if(result != undefined)
		{
			// @TODO: Recover from errors
			if(processResponse(result))
			{
				if(!isNaN(result))
				{
					// update our 'local' score record
					var s = scores[scores.length - 1];
					s.score = Math.round(result);
					s.endTime = parseInt(new Date().getTime() / 1000, 10);
				
					// @TODO - how does this work in preview mode?
					// clear out responses
					clearAssessment();

					// we are no longer resuming an attempt
					if(typeof lo.tracking !== 'undefined' && typeof lo.tracking.isInAttempt != 'undefined')
					{
						lo.tracking.isInAttempt = false;
					}
				}
			}
		}
		
		// @TODO: Do this with practice as well?
		// to keep things simple we destroy aGroup since we get that on startAssessment
		if(mode === 'instance')
		{
			// @TODO: right now we're getting back the assessment questions!
			//delete lo.aGroup;
		}
		
		inAssessmentQuiz = false;
		
		//setSection('assessment');
		//setPageOfCurrentSection('scores');
		setLocation('assessment', 'scores');
	};

	var clearPractice = function()
	{
		responses.practice = [];
		viewState.practice = [];
	};

	var clearAssessment = function()
	{
		responses.assessment = [];
		viewState.assessment = [];
		lastResponse = [];
	};
	
	var isPreviousScoreImported = function()
	{
		return mode === 'instance' && (attemptImportedThisVisit || (lo.tracking != null && lo.tracking.prevScores != null && lo.tracking.prevScores.length != null && lo.tracking.prevScores.length > 0 && lo.tracking.prevScores[0].linkedAttemptID != null && parseInt(lo.tracking.prevScores[0].linkedAttemptID, 10) > 0));
	};
	
	var isInAssessmentQuiz = function()
	{
		return inAssessmentQuiz;
	};

	var getInstanceCloseDate = function()
	{
		if(mode === 'instance')
		{
			return new Date(parseInt(lo.instanceData.endTime, 10) * 1000);
		}
		else
		{
			var d = new Date();
			d.setTime(d.getTime() + 1209600); // two weeks from now
			return d;
		}
	};

	// false if this is an externally linked instance, meaning it doesn't have
	// open/close dates defined (these are defined on the host system)
	var instanceHasNoAccessDates = function()
	{
		return mode === 'instance' && typeof lo.instanceData !== 'undefined' && typeof lo.instanceData.externalLink !== 'undefined' && lo.instanceData.externalLink && String(lo.instanceData.externalLink).length > 0;
	};

	// get the name of the external system that this instance is hosted in.
	// Returns undefined if no external system data is found
	/*var getExternalSystemName = function()
	{
		return instanceHasNoAccessDates() ? lo.instanceData.externalLink : undefined;
	};*/
	
	var instanceIsClosed = function()
	{
		// must be an instance and between the start and end times
		//return mode === 'instance' && (new Date(lo.instanceData.endTime * 1000)).getTime() <= (new Date()).getTime();
		return mode === 'instance' && !instanceHasNoAccessDates() && (new Date(lo.instanceData.endTime * 1000)).getTime() <= (new Date()).getCorrectedTime();
	};
	
	var currentQuestionIsAlternate = function()
	{
		return section === 'assessment' && getAssessmentPageIndex(getPage()).altIndex > 0;
	};

	// this will ask the server what the question score is for a specific question,
	// and save the score. an optional callback allows you to respond to this new information.
	var updateQuestionScoreForCurrentAttempt = function(_section, _page, callback)
	{
		//callback(false);
		//return;
		if(typeof _section === 'undefined')
		{
			_section = section;
		}
		if(typeof _page === 'undefined')
		{
			_page = getPage();
		}
		var questionID = getPageID(_page, _section);
		if(typeof questionID !== 'undefined')
		{
			obo.remote.makeCall('getAttemptQuestionScore', [lo.viewID, questionID], function(serverScore) {
				if(typeof serverScore !== 'undefined' && !isNaN(serverScore))
				{
					setViewStatePropertyForPage('answered', true, _section, _page);
					saveResponse(serverScore, _section, _page);

					if(typeof callback !== 'undefined')
					{
						callback(serverScore);
					}
				}
				else
				{
					// server doesn't have a score for some reason.
					//@TODO: HANDLE THIS CASE!!!
				}
			});
		}
	};
	
	var getScores = function()
	{
		return scores;
	};
	/*
	var getNumAttempts = function()
	{
		if(mode === 'preview')
		{
			return '?';
		}
		
		return isPreviousScoreImported() ? 0 : lo.instanceData.attemptCount;
	};*/
	
	var getNumAttemptsRemaining = function()
	{
		// no real limit in preview mode:
		if(mode === 'preview')
		{
			return '?';
		}
		
		// there are no attemps remaining if there is an imported score
		return isPreviousScoreImported() ? 0 : lo.instanceData.attemptCount - scores.length;
	};
	
	// returns -1 if there is no importable score, otherwise returns the score that may be imported.
	// if they are resuming an attempt then they can't import their score either.
	// if the importable score is 0 we treat this as non-importable (why would you want to import a failing grade?)
	var getImportableScore = function()
	{
		if(	mode === 'instance' &&
			!isResumingPreviousAttempt() &&
			typeof lo.instanceData !== 'undefined' &&
			typeof lo.instanceData.allowScoreImport !== 'undefined' && 
			(lo.instanceData.allowScoreImport === '1' || lo.instanceData.allowScoreImport === 1) &&
			scores.length === 0 &&
			typeof lo.equivalentAttempt !== 'undefined' &&
			lo.equivalentAttempt !== null &&
			typeof lo.equivalentAttempt.score !== 'undefined' &&
			parseFloat(lo.equivalentAttempt.score) > 0)
		{
			return lo.equivalentAttempt.score;
		}
		
		return -1;
	};
	
	var importPreviousScore = function()
	{
		obo.remote.makeCall('doImportEquivalentAttempt', [lo.viewID], function(result) {
			if(processResponse(result))
			{
				if(result === true)
				{
					attemptImportedThisVisit = true;
					
					// add the previous score into our scores array
					var t = parseInt(new Date().getTime() / 1000, 10);
					scores.push({
						score: lo.equivalentAttempt.score,
						startTime: t,
						endTime: t
					});
				
					// send them to the scores page
					//setSection('assessment');
					//setPageOfCurrentSection('scores');
					setLocation('assessment', 'scores');
				}
				else
				{
					// not sure what went wrong
					view.displayGenericError();
				}
			}
		});
	};
	
	// return the score method character (m, r, or h)
	// (useful to abstract out preview mode vs instance mode)
	var getScoreMethod = function()
	{
		return mode === 'preview' ? 'h' : lo.instanceData.scoreMethod;
	};
	
	// returns a rounded int representing final calculated score (based on score mode)
	var getFinalCalculatedScore = function()
	{
		var s = 0;
		var i;
		var numScores = scores.length;
		
		switch(getScoreMethod())
		{
			case 'm': // mean
				for(i = 0; i < numScores; i++)
				{
					s += parseFloat(scores[i].score);
				}
				s = s / parseFloat(numScores);
				break;
			case 'r': // most recent
				s = numScores === 0 ? 0 : parseFloat(scores[numScores - 1].score);
				break;
			case 'h': // highest
				//<--- intentional fallthrough!
			default:
				for(i = 0; i < numScores; i++)
				{
					s = Math.max(s, parseFloat(scores[i].score));
				}
				break;
		}
		
		return Math.round(s);
	};
	
	var getMode = function()
	{
		return mode;
	};

	var isTeachView = function()
	{
		return mode === 'preview' && teachView;
	};

	var setTeachView = function(val)
	{
		if(mode === 'preview')
		{
			teachView = val;

			processAssessmentQuestions();

			if(inAssessmentQuiz)
			{
				clearAssessment();
				setLocation('assessment', 1);
			}

			if(Modernizr.localstorage)
			{
				localStorage.teachView = teachView ? 'true' : 'false';
			}

			if((section === 'practice' || section === 'assessment') && pageIsNumericWithinBounds())
			{
				view.render();
			}
		}
	};
	
	var getPage = function()
	{
		// make sure that if this is a page number it should be returned as numeric
		var p = pages[section];
		if(!isNaN(p))
		{
			p = parseInt(p, 10);
		}

		return p;
	};

	var getFlashRequirementsForSection = function(section)
	{
		var highestVersion = getHighestFlashVersionInSection(section);
		
		return {
			containsFlashContent: highestVersion > 0,
			installedMajorVersion: swfobject.getFlashPlayerVersion().major,
			highestMajorVersion: highestVersion
		};
	};

	// Useful to determine if the section is "completeable"
	// depending on what flash version the user has installed.
	// Returns -1 if no flash is found.
	var getHighestFlashVersionInSection = function(_section)
	{
		var version = -1;
		var i;
		var len;

		switch(_section)
		{
			case 'content':
				len = lo.pages.length;
				for(i = 0; i < len; i++)
				{
					version = Math.max(version, getHighestFlashVersionInPage(lo.pages[i]));
				}
				break;
			case 'practice':
				len = lo.pGroup.kids;
				for(i = 0; i < len; i++)
				{
					version = Math.max(version, getHighestFlashVersionInPage(lo.pGroup.kids[i]));
				}
				break;
			case 'assessment':
				len = lo.aGroup.kids;
				for(i = 0; i < len; i++)
				{
					version = Math.max(version, getHighestFlashVersionInPage(lo.aGroup.kids[i]));
				}
				break;
		}

		return version;
	};

	// returns -1 if no flash found.
	var getHighestFlashVersionInPage = function(page)
	{
		var version = -1;
		var len = page.items.length;
		for(var i = 0; i < len; i++)
		{
			var pi = page.items[i];
			if(	typeof pi.media !== 'undefined' &&
				typeof pi.media.length !== 'undefined' &&
				pi.media.length > 0 &&
				typeof pi.media[0].itemType === 'string' &&
				(pi.media[0].itemType.toLowerCase() === 'swf' || pi.media[0].itemType.toLowerCase() === 'kogneato')
			) {
				//@TODO - Assume kogneato is flash version 10
				version = Math.max(version, pi.media[0].itemType.toLowerCase() === 'swf' ? parseInt(pi.media[0].version, 10) : 10);
			}
		}

		return version;
	};
	
	// @TODO: use this more!
	// if page is not defined we use the current page
	var getPageObject = function(page, _section)
	{
		var i = typeof page === 'undefined' ? getPage() : page;

		if(typeof _section === 'undefined')
		{
			_section = section;
		}

		if(pageIsNumericWithinBounds(_section, i))
		{
			switch(_section)
			{
				case 'content': return lo.pages[i - 1];
				case 'practice': return questions.practice[i - 1][0];
				case 'assessment':
					if(mode === 'instance' || (mode === 'preview' && !teachView))
					{
						return questions.assessment[i - 1][0];
					}
					else
					{
						// need to handle question alternates
						var pageIndex = getAssessmentPageIndex(i);
						return questions.assessment[pageIndex.index][pageIndex.altIndex];
					}
			}
		}
	};
	
	// return all page objects of the current section
	var getPageObjects = function()
	{
		switch(section)
		{
			case 'content': return lo.pages;
			case 'practice': return questions.practice;
			case 'assessment': return questions.assessment;
		}
	};

	// returns the index of an item for the current page
	var getIndexOfItem = function(item)
	{
		var items = getPageObject().items;
		var len = items.length;
		for(var i = 0; i < len; i++)
		{
			if(items[i] === item)
			{
				return i;
			}
		}

		return -1;
	};
	
	// returns a media object on the current page by its ID
	var getMediaObjectByID = function(mediaID)
	{
		var pageObject = getPageObject();
		var curPageItem;
		var len = pageObject.items;
		for(var i = 0; i < len; i++)
		{
			curPageItem = pageObject.items[i];
			if(curPageItem.component.toLowerCase() === 'mediaview')
			{
				if(curPageItem.media && curPageItem.media.length > 0 && curPageItem.media[0].mediaID && curPageItem.media[0].mediaID === mediaID)
				{
					return curPageItem.media[0];
				}
			}
		}
		
		return undefined;
	};
	
	// return the page or question id of a page
	// if page is not defined we'll assume the current page.
	// if page is a non-numeric page we return undefined
	var getPageID = function(pageIndex, _section)
	{
		var page = getPageObject(pageIndex, _section);
		if(typeof _section === 'undefined')
		{
			_section = section;
		}
		
		if(typeof page !== 'undefined')
		{
			switch(_section)
			{
				case 'content':
					return page.pageID;
				case 'practice':
				case 'assessment':
					return page.questionID;
			}
		}

		return undefined;
	};
	
	// return how many standard pages (not overview nor end) are in a section
	// abstracts between instance summary data and lo data
	// note that for assessment this will return how many questions a student
	// should see, not the total number of questions (all alternates).
	var getNumPagesOfSection = function(_section, includeAlternates)
	{
		switch(_section)
		{
			case 'overview': return 0;
			case 'content': return mode === 'preview' ? lo.pages.length : parseInt(lo.summary.contentSize, 10);
			case 'practice': return mode === 'preview' ? questions.practice.length : parseInt(lo.summary.practiceSize, 10);
			case 'assessment':
				if(mode === 'preview')
				{
					if(typeof includeAlternates === 'undefined')
					{
						includeAlternates = false;
					}
					if(includeAlternates)
					{
						return lo.aGroup.kids.length;
					}
					else
					{
						return questions.assessment.length;
					}
				}
				else
				{
					return parseInt(lo.summary.assessmentSize, 10);
				}
		}
	};
	
	var getNumPagesOfCurrentSection = function()
	{
		return getNumPagesOfSection(section);
	};
	
	// @TODO: use this more!
	var getQGroup = function(_section)
	{
		if(typeof _section === 'undefined')
		{
			_section = getSection();
		}

		switch(_section)
		{
			case 'practice': return lo.pGroup;
			case 'assessment': return lo.aGroup;
		}
	};

	var getLO = function()
	{
		return lo;
	};
	
	// for los we should return 'title', but for instances we want the instance title
	var getTitle = function()
	{
		return mode === 'preview' ? lo.title : lo.instanceData.name;
	};
	
	// returns true if the user still has an attempt under way
	var isResumingPreviousAttempt = function()
	{
		return lo && lo.tracking && lo.tracking.isInAttempt && lo.tracking.isInAttempt === true;
	};
	
	// load either an instance or LO based on params
	var load = function(params, callback, opts)
	{
		if(params['instID'].length > 0)
		{
			// instance
			loadInstance(params['instID'], callback, opts);
		}
		else if(params['loID'].length > 0)
		{
			// lo
			loadLO(params['loID'], callback, opts);
		}
	};
	
	// load an instance (which sets the mode to 'instance')
	var loadInstance = function(instID, callback, opts)
	{
		mode = 'instance';
		
		loadCallback = callback;
		
		obo.remote.makeCall('createInstanceVisit', [instID], onLoadInstance);
	};
	
	// load an LO (which sets the mode to 'preview')
	var loadLO = function(loID, callback, opts)
	{
		mode = 'preview';
		
		// loadLO loads an lo given the id, returns result to callback
		loadCallback = callback;
		
		// set up options:
		// @TODO: Do these work?
		var options = {
			useOpenDatabase: false,
			useLocalStorage: false
		};
		
		$.extend(options, opts);
		
		// Check local storage for the LO
		if(options.useLocalStorage)
		{
			if(localStorage['lo'+loID])
			{
				onGetLO(localStorage['lo'+loID]);
				return;
			}
		}
		// can't find it, get it from server
		obo.remote.makeCall('getLO', [loID], onGetLO);
	};
	
	var getSection = function()
	{
		return section;
	};
	
	var getPrevPage = function()
	{
		var page = getPage();
		var newPage;
		var newSection = undefined;
		
		if(page === 'start')
		{
			newPage = 'end';
			newSection = getPrevSection();
		}
		else if(page === 'end')
		{
			newPage = getNumPagesOfCurrentSection();
			if(mode === 'preview' && section === 'assessment' && questions.assessment[newPage - 1].length > 1)
			{
				newPage = String(newPage) + String.fromCharCode(questions.assessment[newPage - 1].length - 1 + 97);
			}
		}
		else if(page === 1)
		{
			newPage = 'start';
			if(section === 'content')
			{
				newSection = 'overview';
			}
		}
		else
		{
			// special <number><letter> pages for assessment preview
			if(mode === 'preview' && section === 'assessment')
			{
				var assessIndex = getAssessmentPageIndex(page);
				if(assessIndex.altIndex >= 1)
				{
					assessIndex.altIndex--;
				}
				else
				{
					assessIndex.index--;
					assessIndex.altIndex = questions.assessment[assessIndex.index].length - 1;
				}

				newPage = (assessIndex.index + 1).toString() + (assessIndex.altIndex == 0 ? '' : String.fromCharCode(assessIndex.altIndex + 97));
			}
			else
			{
				newPage = parseInt(page, 10) - 1;
			}
		}
		
		if(typeof newSection === 'undefined')
		{
			newSection = section;
		}

		return {
			section: newSection,
			page: newPage
		}
	};

	var gotoPrevPage = function()
	{
		var p = getPrevPage();
		setLocation(p.section, p.page);
	}
	
	// go to the next page, slightly difficult since page can be
	// 'start', 'end', or a number, or a qalt page
	var getNextPage = function()
	{
		var page = getPage();
		var newPage;
		var newSection = undefined;
		
		if(section === 'overview')
		{
			newSection = 'content';
			newPage = 1;
		}
		else if(page === 'start')
		{
			newPage = 1;
		}
		else if(page === 'end')
		{
			newPage = 'start';
			newSection = getNextSection();
		}
		// special <number><letter> pages for assessment preview
		else if(mode === 'preview' && section === 'assessment')
		{
			var assessIndex = getAssessmentPageIndex(page);
			
			// + 2 since pages are indexed at 1 but getAssessmentPageIndex returns 0-indexed values
			if(assessIndex.altIndex === questions.assessment[assessIndex.index].length - 1)
			{
				newPage = assessIndex.index + 2;
				if(parseInt(newPage, 10) > getNumPagesOfCurrentSection())
				{
					newPage = 'end';
				}
			}
			else
			{
				newPage = String(assessIndex.index + 1) + String.fromCharCode(assessIndex.altIndex + 1 + 97);
			}
		}
		else if(page === getNumPagesOfCurrentSection())
		{
			newPage = 'end';
		}
		else
		{
			newPage = parseInt(page, 10) + 1;
		}

		if(typeof newSection === 'undefined')
		{
			newSection = section;
		}

		return {
			section: newSection,
			page: newPage
		}
	};

	var gotoNextPage = function()
	{
		var p = getNextPage();
		setLocation(p.section, p.page);
	};
	
	// advance to the start of next section
	var gotoStartPageOfNextSection = function()
	{
		setLocation(getNextSection(), 'start');
	};
	
	var gotoSectionAndPage = function(newSection, newPage)
	{
		setLocation(newSection, newPage);
	};
	
	// jump to any section, but prevent this if they are in an assessment quiz!
	// optionally you can supply a page as well
	var gotoSection = function(newSection)
	{
		setLocation(newSection, undefined);
	};
	
	// navigates to a page for the current section.
	// returns true if successful.
	// page can either be 'start', 'end', 'scores', a number 1-n or a number with a letter suffix
	// for question alternates in preview mode (2b, 3d, etc)
	var gotoPage = function(newPage)
	{
		setLocation(undefined, newPage);
	};

	// this function simply saves the response. it doesn't talk to the server.
	var saveResponse = function(answerID_or_shortAnswerResponse_or_score, _section, _page)
	{
		if(typeof _section === 'undefined')
		{
			_section = getSection();
		}
		if(typeof _page === 'undefined')
		{
			_page = getPage();
		}

		responses[_section][_page] = answerID_or_shortAnswerResponse_or_score;
	}
	
	// use this to submit a MC, short answer or media question.
	// for media, score = 0-100
	var submitQuestion = function(answerID_or_shortAnswerResponse_or_score, isMedia, _section, page)
	{
		if(typeof _section === 'undefined')
		{
			_section = getSection();
		}
		if(typeof page === 'undefined')
		{
			page = getPage();
		}

		var qGroup = getQGroup(_section);

		if(mode === 'instance')
		{
			obo.remote.makeCall(isMedia === true ? 'trackSubmitMedia' : 'trackSubmitQuestion', [
				lo.viewID,
				qGroup.qGroupID,
				getPageID(page, _section),
				answerID_or_shortAnswerResponse_or_score
			], processResponse);
		}
		
		// store this response
		// (for question alternates in preview mode we just store one response -
		//	the last response, therefore we need to remove other alternates for
		// the same question)
		if(mode === 'preview' && _section === 'assessment')
		{
			var pageIndex = getAssessmentPageIndex(page);
			clearAssessmentAltResponses(pageIndex.index);
		}

		setViewStatePropertyForPage('answered', true, _section, page);
		saveResponse(answerID_or_shortAnswerResponse_or_score, _section, page);
	};

	// use this to submit a question that is not considered 'saved' or 'submitted'
	// we want to not allow this if the same data is already saved.
	var submitUnsavedQuestion = function(answerID_or_shortAnswerResponse_or_score)
	{
		if(getPreviousResponse() != answerID_or_shortAnswerResponse_or_score)
		{
			setViewStatePropertyForPage('answered', false);
			saveResponse(answerID_or_shortAnswerResponse_or_score);
		}
	}

	// for preview mode this removes all alt responses for a given question index 
	var clearAssessmentAltResponses = function(questionIndex)
	{
		if(mode === 'preview')
		{
			delete responses['assessment'][parseInt(questionIndex, 10) + 1];

			var numAlts = questions.assessment[questionIndex].length;
			for(var i = 1; i < numAlts; i++)
			{
				delete responses['assessment'][String(parseInt(questionIndex, 10) + 1) + String.fromCharCode(i + 97)];
			}
		}
	}
	
	// return the previous response, if it exists, for the current question
	// return 'undefined' if it doesn't exist
	// note that for media questions the previous response is really the score
	var getPreviousResponse = function(_section, _page)
	{
		if(typeof _section === 'undefined')
		{
			_section = getSection();
		}
		if(typeof _page === 'undefined')
		{
			_page = getPage();
		}

		return responses[_section][_page];
	};

	var startPractice = function()
	{
		if(mode === 'instance' && typeof lo.pGroup.kids === 'undefined')
		{
			obo.remote.makeCall('trackAttemptStart', [lo.viewID, lo.pGroup.qGroupID], loadPractice);
		}
		else
		{
			loadPractice();
		}
	};
	
	var startAssessment = function()
	{
		if(mode === 'instance')
		{
			obo.remote.makeCall('trackAttemptStart', [lo.viewID, lo.aGroup.qGroupID], loadAssessment);
		}
		else
		{
			loadAssessment();
		}
	};
	
	var submitAssessment = function()
	{
		if(mode === 'instance')
		{
			obo.remote.makeCall('trackAttemptEnd', [lo.viewID, lo.aGroup.qGroupID], onSubmitAssessment);
		}
		else
		{
			var total = 0;
			var curQuestion;
			var curResponse;
			var assessmentIndex;
			// @TODO: This is O(n^2)
			var len;
			var i;
			var j;

			for(i in responses.assessment)
			{
				//lastResponseAltIndex = lastResponse[i];
				
				//curQuestion = lo.aGroup.kids[i - 1]; //i - 1 since responses uses page numbers as it's index
				curResponse = responses.assessment[i];
				assessmentIndex = getAssessmentPageIndex(i);
				curQuestion = questions.assessment[assessmentIndex.index][assessmentIndex.altIndex];
				switch(curQuestion.itemType.toLowerCase())
				{
					case 'mc':
						len = curQuestion.answers.length;
						for(j = 0; j < len; j++)
						{
							if(curQuestion.answers[j].answerID === curResponse)
							{
								total += parseInt(curQuestion.answers[j].weight, 10);
							}
						}
						break;
					case 'qa':
						len = curQuestion.answers.length;
						for(j = 0; j < len; j++)
						{
							if(curQuestion.answers[j].answer.toLowerCase() === curResponse)
							{
								total += 100;
							}
						}
						break;
					case 'media':
						total += parseInt(curResponse, 10);
						break;
				}
				
			}
			//onSubmitAssessment(parseFloat(total) / parseFloat(lo.aGroup.kids.length));
			onSubmitAssessment(parseFloat(total) / parseFloat(questions.assessment.length));
		}
	};

	// if logoutMessage is defined then the user will be shown a dialog first.
	// otherwise they will be kicked to the login page.
	var logout = function(_logoutMessage)
	{
		clearInterval(verifyTimeIntervalID);

		if(typeof _logoutMessage === 'undefined')
		{
			_logoutMessage = '';
		}
		logoutMessage = _logoutMessage;

		obo.view.showThrobber();
		obo.remote.makeCall('doLogout', null, function(result) {
			obo.view.hideThrobber();

			if(logoutMessage.length === 0)
			{
				redirectToLoginPage();
			}
			else
			{
				obo.dialog.showDialog({
					title: 'Logout',
					contents: logoutMessage,
					modal: true,
					closeButton: false,
					escClose: false,
					closeCallback: function(dialog) {
						obo.dialog.closeDialogs();
						redirectToLoginPage();
					},
					buttons: [
						{label: 'OK'}
					]
				});
			}
		});
	};

	// doesn't log the user out, but wipes out the content
	// and displays a message as to why the page was killed
	var killPage = function(message)
	{
		clearInterval(verifyTimeIntervalID);
		$('body').empty();
		$('html').addClass('older-browser-background');
		//view.displayError(message);
		obo.dialog.showDialog({
			title: 'Error',
			contents: message,
			closeButton: false,
			escClose: false,
			modal: false,
			activelyHideSWFs: false,
			buttons: []
		});
	}

	var redirectToLoginPage = function()
	{
		location.href = obo.util.getBaseURL();
	}
	
	return {
		init: init,
		isInAssessmentQuiz: isInAssessmentQuiz,
		instanceIsClosed: instanceIsClosed,
		instanceHasNoAccessDates: instanceHasNoAccessDates,
		getScores: getScores,
		getNumAttemptsRemaining: getNumAttemptsRemaining,
		getImportableScore: getImportableScore,
		importPreviousScore: importPreviousScore,
		getScoreMethod: getScoreMethod,
		getFinalCalculatedScore: getFinalCalculatedScore,
		getMode: getMode,
		isTeachView: isTeachView,
		setTeachView: setTeachView,
		getPage: getPage,
		getPrevPage: getPrevPage,
		getNextPage: getNextPage,
		getPageObject: getPageObject,
		getPageObjects: getPageObjects,
		getMediaObjectByID: getMediaObjectByID,
		getNumPagesOfSection: getNumPagesOfSection,
		getNumPagesOfCurrentSection: getNumPagesOfCurrentSection,
		getLO: getLO,
		getTitle: getTitle,
		isResumingPreviousAttempt: isResumingPreviousAttempt,
		load: load,
		loadInstance: loadInstance,
		loadLO: loadLO,
		getSection: getSection,
		gotoPrevPage: gotoPrevPage,
		gotoNextPage: gotoNextPage,
		gotoStartPageOfNextSection: gotoStartPageOfNextSection,
		gotoSectionAndPage: gotoSectionAndPage,
		gotoSection: gotoSection,
		gotoPage: gotoPage,
		submitQuestion: submitQuestion,
		submitUnsavedQuestion: submitUnsavedQuestion,
		getPreviousResponse: getPreviousResponse,
		startAssessment: startAssessment,
		startPractice: startPractice,
		submitAssessment: submitAssessment,
		currentQuestionIsAlternate: currentQuestionIsAlternate,
		getInstanceCloseDate: getInstanceCloseDate,
		getFlashRequirementsForSection: getFlashRequirementsForSection,
		logout: logout,
		getViewStateForPage: getViewStateForPage,
		getViewStatePropertyForPage: getViewStatePropertyForPage,
		setViewStatePropertyForPage: setViewStatePropertyForPage,
		getNumPagesWithViewStateProperty: getNumPagesWithViewStateProperty,
		updateQuestionScoreForCurrentAttempt: updateQuestionScoreForCurrentAttempt,
		getPageID: getPageID,
		getIndexOfItem: getIndexOfItem
	};
}();