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

obo.model = function(view, opts)
{
	
	
	//@PRIVATE
	
	// A reference to the view
	var view;
	
	// which mode ('preview'|'instance')
	var mode;
	
	// the lo data object
	var lo;
	
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
	
	// the current section ('overview', 'content', 'practice' or 'assessment')
	var section = 'overview';
	
	// store which page is active for each section ('start', 'end', or 1-n)
	var pages = {
		overview: 'start',
		content: 'start',
		practice: 'start',
		assessment: 'start'
	};
	
	// flag to represent if they are currently taking the assessment
	var inAssessmentQuiz = false;
	
	// in preview mode we want to store which question alternates we are viewing
	//var activeAssessmentQuestions = [];
	
	// holds callback reference for loadLO/loadInstance
	var loadCallback;
	
	// we need a flag to store if an attempt was imported this visit since we only get this data
	// when we first load.
	var attemptImportedThisVisit;
	
	// returns true if the supplied page is in a valid format
	// technically this code would allow page '2b' for content, but this just
	// maps to 2, so it's harmless to allow it.
	var isValidPage = function(_section, page)
	{
		if(_section == undefined)
		{
			_section = section;
		}
		if(page == undefined)
		{
			page = pages[_section];
		}
		
		if(_section == 'overview')
		{
			return page == 'start';
		}
		else
		{
			return (_section == 'assessment' && page == 'scores') || page == 'start' || page == 'end' || pageIsNumericWithinBounds(_section, page)
		}
	};
	
	// determines if a user can view a page, bascially a wrapper for
	// logic with inAssessmentQuiz
	var canAccessPage = function(_section, page)
	{
		if(_section == undefined)
		{
			_section = section;
		}
		if(page == undefined)
		{
			page = pages[_section];
		}
		
		if(isValidPage(_section, page))
		{
			if(inAssessmentQuiz)
			{
				// in a assessment quiz a user can only access numeric pages inside the assessment, OR the end page
				return _section == 'assessment' && (pageIsNumericWithinBounds(_section, page) || page == 'end');
			}
			else
			{
				// outside assessment a user can access only the start or score pages in assessment or any other section
				return (_section == 'assessment' && (page == 'start' || page == 'scores')) || _section != 'assessment';
			}
		}
	};
	
	// returns true if the page is a numeric type and is within the bounds
	// of the section (ie would return false when checking if a page 20 exists for
	// a three question assessment)
	var pageIsNumericWithinBounds = function(_section, page)
	{
		if(_section == undefined)
		{
			_section = section;
		}
		if(page == undefined)
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
		if(_section == 'assessment' && isNaN(page))
		{
			index = getAssessmentPageIndex(page).index + 1;
			//@TODO - check the altIndex bounds
		}
		
		return index !== false && !isNaN(index) && index > 0 && index - 1 < getNumPagesOfSection(_section);
	};
	/*
	var currentPageIsNumericWithinBounds = function()
	{
		return pageIsNumericWithinBounds(section, getPage());
	}*/
	
	// utility function that turns a page like '2b' into {index:1, altIndex:1}
	var getAssessmentPageIndex = function(page)
	{
		//@TODO - save this regex maybe?
		var regex = /([0-9]+)([b-z]?)/;
		var match = regex.exec(page);
		
		if(match == null)
		{
			return false;
		}
		else
		{
			return {
				index: parseInt(match[1]) - 1,
				altIndex: match[2] == '' ? 0 : match[2].charCodeAt(0) - 97
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
		console.log('processResponse', response);
		
		if(obo.remote.isError(response))
		{
			switch(response.errorID)
			{
				case 1: // not logged in
					view.displayError('You are not logged in!');
					//@TODO: Log the user out
					//@TODO: send client error
					break;
				case 4: // insufficient permissions
					view.displayError('You do not have permissions!');
					//@TODO: Log the user out
					//@TODO: send client error
					break;
				case 5: // bad visit key
					view.displayError('You already have this object open!');
					//@TODO: send client error
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
				view.displayError('This is not a valid LO!');
			}
			else
			{
				lo = result;
				processQuestions();
		
				//@TODO
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
		console.log('onLoadInstance');
		console.log(result);
		
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
			if(type == 'instance')
			{
				if(Number(lo.aGroup.qGroupID) < 1)
				{
					errors.push(108); // assessment group id isnt above 0
				}
			}
			else if(type == 'lo')
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
		
		//@TODO
		/*if(errors.length > 0)
		{
			losService.getOperation('trackClientError').send(102, '', errors)
		}*/
		
		return errors;
	};
	
	// this is only necessary in preview mode to show the multiple questions per index
	var processQuestions = function()
	{
		questions.practice = [];
		$(lo.pGroup.kids).each(function(index, page) {
			questions.practice.push([page]);
		});
		
		questions.assessment = [];
		var curQIndex = 0;
		$(lo.aGroup.kids).each(function(index, page)
		{
			index++;
			if(curQIndex != page.questionIndex || page.questionIndex == 0)
			{
				curQIndex++
				questions.assessment.push([]);
			}
			questions.assessment[curQIndex-1].push(page);
		});
	};
	
	var setLocation = function(newSection, newPage)
	{
		if(newSection == undefined)
		{
			newSection = section;
		}
		if(newPage == undefined)
		{
			newPage = pages[newSection];
		}
		
		if(canAccessPage(newSection, newPage))
		{
			if(mode == 'instance')
			{
				if(newSection != section)
				{
					obo.remote.makeCall('trackSectionChanged', [lo.viewID, getSectionIndex()], processResponse);
				}
				else if(newPage != pages[section])
				{
					obo.remote.makeCall('trackPageChanged', [lo.viewID, getPageID(), getSectionIndex()], processResponse);
				}
			}
			
			// special case - overview only has a start page
			if(newSection == 'overview')
			{
				newPage = 'start';
			}
			// special case - no start page for content
			if(newSection == 'content' && newPage == 'start')
			{
				newPage = 1;
			}
			
			// special case - dealing with question alternates:
			var assessPageIndex = getAssessmentPageIndex(newPage);
			if(assessPageIndex.altIndex && assessPageIndex.altIndex.length > 0)
			{
				activequestions.assessment[assessPageIndex.index] = assessPageIndex.altIndex;
			}
			
			section = newSection;
			pages[section] = newPage;
			//alert('model::setLocation render');
			view.render();
			
			return true;
		}
		
		return false;
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
	
	var getSectionIndex = function()
	{
		switch(section)
		{
			case 'overview': return 0;
			case 'content': return 1;
			case 'practice': return 2;
			case 'assessment': return 3;
		}
		
		return -1;
	};
	
	var loadAssessment = function(result)
	{
		// we'll have a result if this was called via trackSubmitStart
		if(mode == 'preview' || result && processResponse(result))
		{
			if(mode == 'instance')
			{
				// overwrite our aGroup with the set from trackSubmitStart
				lo.aGroup.kids = result;
				// tease out the saved answers from the response
				for(var i in result)
				{
					if(result[i].savedAnswer && result[i].savedAnswer.answerID)
					{
						responses['assessment'][parseInt(i) + 1] = result[i].savedAnswer.answerID;
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
			//@TODO: Recover from errors
			if(processResponse(result))
			{
				if(!isNaN(result))
				{
					// update our 'local' score record
					var s = scores[scores.length - 1];
					s.score = result;
					s.endTime = parseInt(new Date().getTime() / 1000);
				
					//@TODO - how does this work in preview mode?
					// clear out responses
					responses.assessment = [];
				}
			}
		}
		
		//@TODO: Do this with practice as well?
		// to keep things simple we destroy aGroup since we get that on startAssessment
		if(mode == 'instance')
		{
			//@TODO: right now we're getting back the assessment questions!
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
		return mode == 'preview' || lo.hasOwnProperty('pgroup');
	};
	
	var questions.assessmentLoaded = function()
	{
		return mode == 'preview' || lo.hasOwnProperty('agroup');
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
		return mode == 'instance' && (attemptImportedThisVisit || (lo.tracking != null && lo.tracking.prevScores != null && lo.tracking.prevScores.length != null && lo.tracking.prevScores.length > 0 && lo.tracking.prevScores[0].linkedAttemptID != null && parseInt(lo.tracking.prevScores[0].linkedAttemptID) > 0));
	};
	
	// @public
	var isInAssessmentQuiz = function()
	{
		return inAssessmentQuiz;
	};
	
	var instanceIsClosed = function()
	{
		// must be an instance and between the start and end times
		return mode == 'instance' && (new Date(lo.instanceData.endTime * 1000)).getTime() <= (new Date()).getTime();
	};
	
	var currentQuestionIsAlternate = function()
	{
		return section == 'assessment' && getAssessmentPageIndex(getPage()).altIndex > 0;
	}
	
	var getScores = function()
	{
		return scores;
	};
	/*
	var getNumAttempts = function()
	{
		if(mode == 'preview')
		{
			return '?';
		}
		
		return isPreviousScoreImported() ? 0 : lo.instanceData.attemptCount;
	};*/
	
	var getNumAttemptsRemaining = function()
	{
		// no real limit in preview mode:
		if(mode == 'preview')
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
		if(mode == 'instance' && !isResumingPreviousAttempt() && lo.instanceData != null && lo.instanceData.allowScoreImport != null && lo.instanceData.allowScoreImport == 1 && scores.length == 0 && lo.equivalentAttempt != null && lo.equivalentAttempt.score != null && lo.equivalentAttempt.score > 0)
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
		return mode == 'preview' ? 'h' : lo.instanceData.scoreMethod;
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
				s = scores.length == 0 ? 0 : parseFloat(scores[scores.length - 1].scores);
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
		return pages[section];
	};
	
	//@TODO: use this more!
	var getPageObject = function()
	{
		var i = getPage();
		console.log('getPageObject', i);
		if(pageIsNumericWithinBounds(undefined, i))
		{
			switch(section)
			{
				case 'content': return lo.pages[i - 1];
				case 'practice': return questions.practice[i - 1][0];
				case 'assessment':
					if(mode == 'instance')
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
			if(curPageItem.component.toLowerCase() == 'mediaview')
			{
				if(curPageItem.media && curPageItem.media.length > 0 && curPageItem.media[0].mediaID && curPageItem.media[0].mediaID == mediaID)
				{
					return curPageItem.media[0];
				}
			}
		}
		
		return undefined;
	};
	
	// return the page or question id of the current page
	var getPageID = function()
	{
		var page = getPageObject();
		
		switch(section)
		{
			case 'content':
				return page.pageID;
			case 'practice':
			case 'assessment':
				return page.questionID;
		}
	};
	
	// return how many standard pages (not overview nor end) are in a section
	// abstracts between instance summary data and lo data
	var getNumPagesOfSection = function(_section)
	{
		switch(_section)
		{
			case 'overview': return 0;
			case 'content': return mode == 'preview' ? lo.pages.length : parseInt(lo.summary.contentSize);
			case 'practice': return questions.practice.length;
			case 'assessment': return questions.assessment.length;
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
	
	//@TODO is this used?
	/*
	var getCurrentquestions.assessment = function()
	{
		return questions.assessment[getAssessmentPageIndex(getPageOfCurrentSection()).index];
	};*/
	
	//@TODO: use this more!
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
		return mode == 'preview' ? lo.title : lo.instanceData.name;
	};
	
	// returns true if the user still has an attempt under way
	var isResumingPreviousAttempt = function()
	{
		return lo && lo.tracking && lo.tracking.isInAttempt && lo.tracking.isInAttempt == true;
	};
	
	// load either an instance or LO based on params
	var load = function(params, callback, opts)
	{
		if(params['instID'].length > 0)
		{
			obo.view.displayError('Only preview mode is enabled currently.');
			return;
			// instance
			//loadInstance(params['instID'], callback, opts);
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
		//@TODO: Do these work?
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
						//@TODO: WTF
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
	
	var gotoPrevPage = function()
	{
		var page = getPage();
		var newPage;
		var newSection = undefined;
		
		if(page == 'start')
		{
			newPage = 'end';
			newSection = getPrevSection();
		}
		else if(page == 'end')
		{
			newPage = getNumPagesOfCurrentSection();
		}
		else if(page == 1)
		{
			if(section == 'content')
			{
				newSection = 'overview';
			}
			else
			{
				newPage = 'start';
			}
		}
		else
		{
			// special <number><letter> pages for assessment preview
			if(mode == 'preview' && section == 'assessment')
			{
				var assessIndex = getAssessmentPageIndex(page);
				if(assessIndex.altIndex == 0)
				{
					newPage = String(assessIndex.index);
					var altIndex = questions.assessment[assessIndex.index - 1].length - 1;
					if(altIndex > 0)
					{
						newPage += String.fromCharCode(altIndex + 97);
					}
				}
				else if(assessIndex.altIndex == 1)
				{
					newPage = assessIndex.index - 1;
				}
				else
				{
					newPage = String(assessIndex.index - 1) + String.fromCharCode(assessIndex.altIndex + 96);
				}
			}
			else
			{
				newPage = parseInt(page) - 1;
			}
		}
		
		setLocation(newSection, newPage);
	};
	
	// go to the next page, slightly difficult since page can be
	// 'start', 'end', or a number, or a qalt page
	var gotoNextPage = function()
	{
		var page = getPage();
		var newPage;
		var newSection = undefined;
		
		if(section == 'overview')
		{
			newSection = 'content';
			newPage = 1;
		}
		else if(page == 'start')
		{
			newPage = 1;
		}
		else if(page == 'end')
		{
			newPage = 'start';
			newSection = getNextSection();
		}
		// special <number><letter> pages for assessment preview
		else if(mode == 'preview' && section == 'assessment')
		{
			var assessIndex = getAssessmentPageIndex(page);
			
			// + 2 since pages are indexed at 1 but getAssessmentPageIndex returns 0-indexed values
			if(assessIndex.altIndex == questions.assessment[assessIndex.index].length - 1)
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
		else if(page == getNumPagesOfCurrentSection())
		{
			newPage = 'end';
		}
		else
		{
			newPage = parseInt(page) + 1;
		}
		
		setLocation(newSection, newPage);
	};
	
	// advance to the start of next section
	var gotoStartPageOfNextSection = function()
	{
		return setLocation(getNextSection(), 'start');
	};
	
	var gotoSectionAndPage = function(newSection, newPage)
	{
		return setLocation(newSection, newPage);
	};
	
	// jump to any section, but prevent this if they are in an assessment quiz!
	// optionally you can supply a page as well
	var gotoSection = function(newSection)
	{
		return setLocation(newSection);
	};
	
	// navigates to a page for the current section.
	// returns true if successful.
	// page can either be 'start', 'end', 'scores', a number 1-n or a number with a letter suffix
	// for question alternates in preview mode (2b, 3d, etc)
	var gotoPage = function(newPage)
	{
		return setLocation(undefined, newPage);
	};
	
	// use this to submit a MC, short answer or media question.
	// for media, score = 0-100
	var submitQuestion = function(answerID_or_shortAnswerResponse_or_score)
	{
		var page = getPage();
		var qGroup = getQGroup();
		
		console.log('submitQuestion(' + answerID_or_shortAnswerResponse_or_score + ')');
		if(mode == 'instance')
		{
			obo.remote.makeCall(qGroup.itemType.toLowerCase() == 'media' ? 'trackSubmitMedia' : 'trackSubmitQuestion', [lo.viewID, qGroup.qGroupID, getPageID(), answerID_or_shortAnswerResponse_or_score], processResponse);
		}
		
		// store this response
		// (for question alternates in preview mode we just store one response -
		//	the last response)
		if(mode == 'preview' && section == 'assessment')
		{
			page = getAssessmentPageIndex(page).index + 1;
		}
		responses[section][page] = answerID_or_shortAnswerResponse_or_score;
	};
	
	// return the previous response, if it exists, for the current question
	// return 'undefined' if it doesn't exist
	var getPreviousResponse = function()
	{
		return responses[section][getPage()];
	};
	
	var startAssessment = function()
	{
		if(mode == 'instance')
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
		if(mode == 'instance')
		{
			obo.remote.makeCall('trackAttemptEnd', [lo.viewID, lo.aGroup.qGroupID], onSubmitAssessment);
		}
		else
		{
			var total = 0;
			var curQuestion;
			var curResponse;
			
			// @TODO: This is O(n^2)
			for(var i in responses['assessment'])
			{
				curQuestion = lo.aGroup.kids[i - 1]; //i - 1 since responses uses page numbers as it's index
				curResponse = responses['assessment'][i];
				for(var j in curQuestion.answers)
				{
					switch(curQuestion.itemType.toLowerCase())
					{
						case 'mc':
							if(curQuestion.answers[j].answerID == curResponse)
							{
								total += parseInt(curQuestion.answers[j].weight);
							}
							break;
						case 'qa':
							if(curQuestion.answers[j].answer == curResponse)
							{
								total += 100;
							}
							break;
					}
				}
			}
			onSubmitAssessment(parseFloat(total) / parseFloat(lo.aGroup.kids.length));
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
	/*
	var getResponses = function(section)
	{
		return responses[section];
	};*/
	
	return {
		isInAssessmentQuiz: isInAssessmentQuiz,
		instanceIsClosed: instanceIsClosed,
		getScores: getScores,
		//getNumAttempts: getNumAttempts,
		getNumAttemptsRemaining: getNumAttemptsRemaining,
		getImportableScore: getImportableScore,
		importPreviousScore: importPreviousScore,
		//isPreviousScoreImported: isPreviousScoreImported,
		getScoreMethod: getScoreMethod,
		getFinalCalculatedScore: getFinalCalculatedScore,
		getMode: getMode,
		getPage: getPage,
		getPageObject: getPageObject,
		getPageObjects: getPageObjects,
		getMediaObjectByID: getMediaObjectByID,
		//getPageID: getPageID,
		getNumPagesOfSection: getNumPagesOfSection,
		getNumPagesOfCurrentSection: getNumPagesOfCurrentSection,
		//getquestions.assessment: getquestions.assessment,
		//getCurrentquestions.assessment: getCurrentquestions.assessment,
		//getQGroup: getQGroup,
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
		submitAssessment: submitAssessment,
		isPageAnswered: isPageAnswered,
		getNumPagesAnswered: getNumPagesAnswered,
		//getResponses: getResponses,
		currentQuestionIsAlternate: currentQuestionIsAlternate
	};
};