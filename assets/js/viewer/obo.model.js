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
/*
obo.remote.makeCall('doPluginCall', ['Kogneato', 'getKogneatoEngineLink',  [1707, true]], function(event) {
	debug.log('good job');
	debug.log(event);
});
*/
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

		// start the verify timer
		verifyTimeIntervalID = setInterval(onVerifyTime, VERIFY_TIME_SECONDS * 1000);

		// start the idle timer
		$.idleTimeout('body', '#continue-session-button', {
			idleAfter: IDLE_TIME_BEFORE_WARN_SECONDS,
			warningLength: IDLE_TIME_BEFORE_LOGOUT_SECONDS,
			pollingInterval: 2,
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
	}

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
	}

	var startIdleTimer = function()
	{
		$.idleTimer(IDLE_TIME_BEFORE_WARN_SECONDS * 1000);
	}

	var isValidSection = function(_section)
	{
		return _section === 'overview' || _section === 'content' || _section === 'practice' || _section === 'assessment';
	};
	
	// returns true if the supplied page is in a valid format
	// technically this code would allow page '2b' for content, but this just
	// maps to 2, so it's harmless to allow it.
	var isValidPage = function(_section, page)
	{
		if(_section === undefined)
		{
			_section = section;
		}
		if(page === undefined)
		{
			page = pages[_section];
		}
		
		if(_section === 'overview')
		{
			return page === 'start';
		}
		else
		{
			return (_section === 'assessment' && page === 'scores') || page === 'start' || page === 'end' || pageIsNumericWithinBounds(_section, page)
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
		//var altIndex = '';
		// test to see if page is in the format <Number><Letter>
		/*
		if(isNaN(page) && page.length > 1 && !isNaN(page.substr(0, page.length - 1)))
		{
			index = page.substr(0, page.length - 1);
			//altIndex = page.substr(page.length);
		}*/
		if(_section === 'assessment' && isNaN(page))
		{
			index = getAssessmentPageIndex(page).index + 1;
			// @TODO - check the altIndex bounds
		}
		
		return index !== false && !isNaN(index) && index > 0 && index - 1 < getNumPagesOfSection(_section);
	};
	
	// utility function that turns a page like '2b' into {index:1, altIndex:1}
	var getAssessmentPageIndex = function(page)
	{
		// @TODO - save this regex maybe?
		var regex = /([0-9]+)([b-z]?)/;
		var match = regex.exec(page);
		
		if(match === null)
		{
			return false;
		}
		else
		{
			return {
				index: parseInt(match[1]) - 1,
				altIndex: match[2] === '' ? 0 : match[2].charCodeAt(0) - 97
			};
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
				view.displayError('This is not a valid LO!');
			}
			else
			{
				lo = result;
				processQuestions();
				
				// populate previous scores:
				if(lo.tracking && lo.tracking.prevScores && lo.tracking.prevScores.length)
				{
					for(var o in lo.tracking.prevScores)
					{
						scores = lo.tracking.prevScores.slice();
					}
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
			if(parseInt(lo.loID) < 1)
			{
				errors.push(100); // if the id is not a number above 0
			}
			if(lo.pages.length < 1)
			{
				errors.push(102); // there are no content pages
			}
			if(parseInt(lo.pGroup.qGroupID) < 1)
			{
				errors.push(104); // the practice group id isnt above 0
			}
			if(type === 'instance')
			{
				if(Number(lo.aGroup.qGroupID) < 1)
				{
					errors.push(108); // assessment group id isnt above 0
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
		var curQIndex = 0;

		if(mode === 'preview')
		{
			$(lo.aGroup.kids).each(function(index, page)
			{
				index++;
				if(curQIndex != page.questionIndex || page.questionIndex === 0)
				{
					curQIndex++;
					questions.assessment.push([]);
				}
				questions.assessment[curQIndex - 1].push(page);
			});
		}
		else
		{
			$(lo.aGroup.kids).each(function(index, page)
			{
				questions.assessment[curQIndex] = [];
				questions.assessment[curQIndex].push(page);
				curQIndex++;
			});
		}
	};
	
	// attempts to set the location variables to new values.
	// if callback is defined will call that function, returning
	// true if successful, false otherwise. If not it will automatically
	// call view.render if successful.
	var setLocation = function(newSection, newPage, callback)
	{
		debug.log('setLocation', newSection, newPage);

		if(newSection === undefined)
		{
			newSection = section;
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
		
		if(canAccessSection(newSection))
		{
			if(canAccessPage(newSection, newPage))
			{
				if(mode === 'instance')
				{
					if(newSection != section)
					{
						obo.remote.makeCall('trackSectionChanged', [lo.viewID, getSectionIndex(newSection)], processResponse);
					}
					else if(newPage != pages[section])
					{
						// we only track page changes on numeric pages:
						var pageID = getPageID(newPage);
						if(typeof pageID !== 'undefined')
						{
							obo.remote.makeCall('trackPageChanged', [lo.viewID, pageID, getSectionIndex()], processResponse);
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

				// special case - dealing with question alternates:
				var assessPageIndex = getAssessmentPageIndex(newPage);
				if(assessPageIndex.altIndex && assessPageIndex.altIndex.length > 0)
				{
					activequestions.assessment[assessPageIndex.index] = assessPageIndex.altIndex;
				}

				// special case - can't view scores page when no scores exist
				if(newSection === 'assessment' && newPage === 'scores' && obo.model.getScores().length === 0)
				{
					newPage = 'start';
				}

				// special case - attempting to view practice page but practice questions not loaded
				if(newSection === 'practice' && !isNaN(newPage) && typeof lo.pGroup.kids === 'undefined')
				{
					pendingPracticeQuestionsLoadedForPage = newPage;
					startPractice();
					return;
				}

				debug.log('setLocation complete', newSection, newPage);
				
				section = newSection;
				pages[section] = newPage;
				
				if(typeof callback === 'function')
				{
					callback(true);
				}
				else
				{
					view.render();
				}
			}
			// if can't access the page we want then at least attempt to access the start page of this section:
			else if(canAccessPage(newSection, 'start'))
			{
				section = newSection;
				pages[section] = section === 'content' ? 1 : 'start';
				
				if(typeof callback === 'function')
				{
					callback(true);
				}
				else
				{
					view.render();
				}
			}
		}
		else
		{
			if(typeof callback === 'function')
			{
				callback(false);
			}
		}
	}
	
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
			setLocation('practice', pendingPracticeQuestionsLoadedForPage);
			pendingPracticeQuestionsLoadedForPage = undefined;
		}
		else
		{
			setLocation('practice', 1);
		}
	}
	
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
				for(var i in result)
				{
					if(typeof result[i].savedAnswer !== 'undefined' && result[i].savedAnswer !== null)
					{
						switch(result[i].itemType.toLowerCase())
						{
							case 'mc':
								if(typeof result[i].savedAnswer.answerID !== 'undefined' && result[i].savedAnswer.answerID !== null)
								{
									responses['assessment'][parseInt(i) + 1] = result[i].savedAnswer.user_answer;
								}
								break;
							case 'qa':
								if(typeof result[i].savedAnswer.user_answer !== 'undefined' && result[i].savedAnswer.user_answer !== null)
								{
									responses['assessment'][parseInt(i) + 1] = result[i].savedAnswer.user_answer;
								}
								break;
							case 'media':
								if(typeof result[i].savedAnswer.user_answer !== 'undefined' && result[i].savedAnswer.user_answer !== null)
								{
									responses['assessment'][parseInt(i) + 1] = parseInt(result[i].savedAnswer.user_answer);
								}
								break;
						}
						
					}
				}
			}
			
			// insert a local score record (to be completed later)
			scores.push({
				score: 0,
				startTime: parseInt(new Date().getTime() / 1000),
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
					s.endTime = parseInt(new Date().getTime() / 1000);
				
					// @TODO - how does this work in preview mode?
					// clear out responses
					responses.assessment = [];
					lastResponse = [];

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
	}
	
	/*
	var practiceQuestionsLoaded = function()
	{
		return mode === 'preview' || lo.hasOwnProperty('pgroup');
	};
	
	var questions.assessmentLoaded = function()
	{
		return mode === 'preview' || lo.hasOwnProperty('agroup');
	}*/
	/*
	// determine if the standard pages (1-n) for the current section are available
	var sectionContentLoaded = function()
	{
		switch(section)
		{
			case 'overview': return true;
			case 'content': return true;
			case 'practice': return lo.hasOwnProperty('pgroup');
			case 'assessment': return lo.hasOwnProperty('agroup');
		}
	}*/
	
	var isPreviousScoreImported = function()
	{
		return mode === 'instance' && (attemptImportedThisVisit || (lo.tracking != null && lo.tracking.prevScores != null && lo.tracking.prevScores.length != null && lo.tracking.prevScores.length > 0 && lo.tracking.prevScores[0].linkedAttemptID != null && parseInt(lo.tracking.prevScores[0].linkedAttemptID) > 0));
	};
	
	var isInAssessmentQuiz = function()
	{
		return inAssessmentQuiz;
	};

	var getInstanceCloseDate = function()
	{
		if(mode === 'instance')
		{
			return new Date(parseInt(lo.instanceData.endTime) * 1000);
		}
		else
		{
			var d = new Date();
			d.setTime(d.getTime() + 1209600); // two weeks from now
			return d;
		}
	}
	
	var instanceIsClosed = function()
	{
		// must be an instance and between the start and end times
		return mode === 'instance' && (new Date(lo.instanceData.endTime * 1000)).getTime() <= (new Date()).getTime();
	};
	
	var currentQuestionIsAlternate = function()
	{
		return section === 'assessment' && getAssessmentPageIndex(getPage()).altIndex > 0;
	}
	
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
					var t = parseInt(new Date().getTime() / 1000);
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
		
		switch(getScoreMethod())
		{
			case 'm': // mean
				for(var i in scores)
				{
					s += parseFloat(scores[i].score);
				}
				s = s / parseFloat(scores.length);
				break;
			case 'r': // most recent
				s = scores.length === 0 ? 0 : parseFloat(scores[scores.length - 1].score);
				break;
			case 'h': // highest
			default:
				for(var i in scores)
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
	
	var getPage = function()
	{
		// make sure that if this is a page number it should be returned as numeric
		var p = pages[section];
		if(!isNaN(p))
		{
			p = parseInt(p);
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
		}
	}

	// Useful to determine if the section is "completeable"
	// depending on what flash version the user has installed.
	// Returns -1 if no flash is found.
	var getHighestFlashVersionInSection = function(section)
	{
		var version = -1;

		switch(section)
		{
			case 'content':
				for(var i in lo.pages)
				{
					version = Math.max(version, getHighestFlashVersionInPage(lo.pages[i]));
				}
				break;
			case 'practice':
				for(var i in lo.pGroup.kids)
				{
					version = Math.max(version, getHighestFlashVersionInPage(lo.pGroup.kids[i]));
				}
				break;
			case 'assessment':
				for(var i in lo.aGroup.kids)
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
		for(var i in page.items)
		{
			var pi = page.items[i];
			if(	typeof pi.media !== 'undefined' &&
				typeof pi.media.length !== 'undefined' &&
				pi.media.length > 0 &&
				typeof pi.media[0].itemType === 'string' &&
				(pi.media[0].itemType.toLowerCase() === 'swf' || pi.media[0].itemType.toLowerCase() === 'kogneato')
			) {
				//@TODO - Assume kogneato is flash version 10
				version = Math.max(version, pi.media[0].itemType.toLowerCase() === 'swf' ? parseInt(pi.media[0].version) : 10);
			}
		}

		return version;
	}
	
	// @TODO: use this more!
	// if page is not defined we use the current page
	var getPageObject = function(page)
	{
		var i = typeof page === 'undefined' ? getPage() : page;
		if(pageIsNumericWithinBounds(undefined, i))
		{
			switch(section)
			{
				case 'content': return lo.pages[i - 1];
				case 'practice': return questions.practice[i - 1][0];
				case 'assessment':
					if(mode === 'instance')
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
	}
	
	// returns a media object on the current page by its ID
	var getMediaObjectByID = function(mediaID)
	{
		var pageObject = getPageObject();
		var curPageItem;
		for(var i in pageObject.items)
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
	var getPageID = function(page)
	{
		var page = getPageObject(page);
		
		if(typeof page !== 'undefined')
		{
			switch(section)
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
	var getNumPagesOfSection = function(_section)
	{
		switch(_section)
		{
			case 'overview': return 0;
			case 'content': return mode === 'preview' ? lo.pages.length : parseInt(lo.summary.contentSize);
			case 'practice': return mode === 'preview' ? questions.practice.length : parseInt(lo.summary.practiceSize);
			case 'assessment': return mode === 'preview' ? questions.assessment.length : parseInt(lo.summary.assessmentSize);
		}
	};
	
	var getNumPagesOfCurrentSection = function()
	{
		return getNumPagesOfSection(section);
	};
	
	// @TODO - practice should follow the same model
	// we need a special function to return our assessment since we store it differently
	/*
	var getquestions.assessment = function()
	{
		return questions.assessment;
	};*/
	
	// @TODO is this used?
	/*
	var getCurrentquestions.assessment = function()
	{
		return questions.assessment[getAssessmentPageIndex(getPageOfCurrentSection()).index];
	};*/
	
	// @TODO: use this more!
	var getQGroup = function()
	{
		switch(section)
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
			//obo.view.displayError('Only preview mode is enabled currently.');
			//return;

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
		
		// lets set up the db and see if the lo we're looking for is already stored
		if(options.useOpenDatabase)
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
						// @TODO: WTF
						///////onGetLO(results.rows.item(0).loJSON);
						return;
					}
				});
			});
		}
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
				newPage = parseInt(page) - 1;
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

	var gotoPrevPage = function(callback)
	{
		var p = getPrevPage();
		setLocation(p.section, p.page, callback);
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
				if(parseInt(newPage) > getNumPagesOfCurrentSection())
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
			newPage = parseInt(page) + 1;
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

	var gotoNextPage = function(callback)
	{
		var p = getNextPage();
		setLocation(p.section, p.page, callback);
	};
	
	// advance to the start of next section
	var gotoStartPageOfNextSection = function(callback)
	{
		setLocation(getNextSection(), 'start', callback);
	};
	
	var gotoSectionAndPage = function(newSection, newPage, callback)
	{
		setLocation(newSection, newPage, callback);
	};
	
	// jump to any section, but prevent this if they are in an assessment quiz!
	// optionally you can supply a page as well
	var gotoSection = function(newSection, callback)
	{
		setLocation(newSection, undefined, callback);
	};
	
	// navigates to a page for the current section.
	// returns true if successful.
	// page can either be 'start', 'end', 'scores', a number 1-n or a number with a letter suffix
	// for question alternates in preview mode (2b, 3d, etc)
	var gotoPage = function(newPage, callback)
	{
		setLocation(undefined, newPage, callback);
	};
	
	// use this to submit a MC, short answer or media question.
	// for media, score = 0-100
	var submitQuestion = function(answerID_or_shortAnswerResponse_or_score, isMedia)
	{
		var page = getPage();
		var qGroup = getQGroup();

		if(mode === 'instance')
		{
			obo.remote.makeCall(isMedia === true ? 'trackSubmitMedia' : 'trackSubmitQuestion', [lo.viewID, qGroup.qGroupID, getPageID(), answerID_or_shortAnswerResponse_or_score], processResponse);
		}
		
		// store this response
		// (for question alternates in preview mode we just store one response -
		//	the last response, therefore we need to remove other alternates for
		// the same question)
		if(mode === 'preview' && section === 'assessment')
		{
			var pageIndex = getAssessmentPageIndex(page);
			clearAssessmentAltResponses(pageIndex.index);
		}

		responses[section][page] = answerID_or_shortAnswerResponse_or_score;
	};

	// for preview mode this removes all alt responses for a given question index 
	var clearAssessmentAltResponses = function(questionIndex)
	{
		if(mode === 'preview')
		{
			delete responses['assessment'][parseInt(questionIndex) + 1];

			var numAlts = questions.assessment[questionIndex].length;
			for(var i = 1; i < numAlts; i++)
			{
				delete responses['assessment'][String(parseInt(questionIndex) + 1) + String.fromCharCode(i + 97)];
			}
		}
	}
	
	// return the previous response, if it exists, for the current question
	// return 'undefined' if it doesn't exist
	// note that for media questions the previous response is really the score
	var getPreviousResponse = function()
	{
		return responses[section][getPage()];
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
			for(var i in responses['assessment'])
			{
				//lastResponseAltIndex = lastResponse[i];
				
				//curQuestion = lo.aGroup.kids[i - 1]; //i - 1 since responses uses page numbers as it's index
				curResponse = responses['assessment'][i];
				assessmentIndex = getAssessmentPageIndex(i);
				curQuestion = questions.assessment[assessmentIndex.index][assessmentIndex.altIndex];
				switch(curQuestion.itemType.toLowerCase())
				{
					case 'mc':
						for(var j in curQuestion.answers)
						{
							if(curQuestion.answers[j].answerID === curResponse)
							{
								total += parseInt(curQuestion.answers[j].weight);
							}
						}
						break;
					case 'qa':
						for(var j in curQuestion.answers)
						{
							if(curQuestion.answers[j].answer === curResponse)
							{
								total += 100;
							}
						}
						break;
					case 'media':
						total += parseInt(curResponse);
						break;
				}
				
			}
			//onSubmitAssessment(parseFloat(total) / parseFloat(lo.aGroup.kids.length));
			onSubmitAssessment(parseFloat(total) / parseFloat(questions.assessment.length));
		}
	};
	
	var isPageAnswered = function(section, page)
	{
		return responses[section][parseInt(page)] != undefined;
	};
	
	var getNumPagesAnswered = function(section)
	{
		var total = 0;
		for(var i in responses[section])
		{
			total++;
		}
		
		return total;
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
		logoutMessage = _logoutMessage

		obo.view.showThrobber();
		obo.remote.makeCall('doLogout', null, function(result) {
			obo.view.hideThrobber();

			if(logoutMessage.length == 0)
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
						$.modal.close();
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
			modal: false
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
		getScores: getScores,
		getNumAttemptsRemaining: getNumAttemptsRemaining,
		getImportableScore: getImportableScore,
		importPreviousScore: importPreviousScore,
		getScoreMethod: getScoreMethod,
		getFinalCalculatedScore: getFinalCalculatedScore,
		getMode: getMode,
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
		getPreviousResponse: getPreviousResponse,
		startAssessment: startAssessment,
		startPractice: startPractice,
		submitAssessment: submitAssessment,
		isPageAnswered: isPageAnswered,
		getNumPagesAnswered: getNumPagesAnswered,
		currentQuestionIsAlternate: currentQuestionIsAlternate,
		getInstanceCloseDate: getInstanceCloseDate,
		getFlashRequirementsForSection: getFlashRequirementsForSection,
		logout: logout
	};
}();