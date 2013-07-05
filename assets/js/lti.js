if(!window.obo)
{
	window.obo = {};
}

obo.lti = function()
{
	"use strict";

	var PROGESS_FAKE_DELAY_MS = 1000;
	var SEARCH_DELAY_MS = 250;
	var CHANGE_SECTION_FADE_DELAY_MS = 250;
	var MAX_ITEMS = 20;
	var VERIFY_TIME_SECONDS = 30;

	var MESSAGE_LOGOUT = 'You have been logged out. Please refresh the page and try again.';

	var searchIntervalId = -1;
	var verifyTimeIntervalId = -1;
	var data = {
		'My Instances':      {items:undefined, allItems:undefined, last:0},
		'Community Library': {items:undefined, allItems:undefined, last:0},
		'My Objects':        {items:undefined, allItems:undefined, last:0}
	};
	var searchStrings = {};

	// elements:
	var $template = $($('.template.obo-item')[0]);
	var $listContainer = $('#list-container');
	var $createInstanceForm = $('#create-instance');
	var $search = $('#search');

	var ltiUrl = null;

	// disable logs by defualt
	debug.setLevel(0);

	// searching:
	function search()
	{
		var section = $('.tab.selected').text();
		if(data[section].items === 'pending')
		{
			return;
		}

		var text = $.trim($search.val());
		if($search.attr('data-last-search') !== text)
		{
			var className = section.replace(' ', '-').toLowerCase();
			$('.' + className).children('ul').empty();
			filterList(text);
			populateSection(section);
			$('#list-container').scrollTop(0);
		}
		$search.attr('data-last-search', text);
	}

	function clearSearch()
	{
		var section = $('.tab.selected').text();
		var className = section.replace(' ', '-').toLowerCase();
		$('.' + className).children('ul').empty();
		$('#search').val('');
		data[section].items = data[section].allItems;
		data[section].last = 0;
		////populateSection(section);
		$search.attr('data-last-search', '');
	}

	function filterList(searchTerms)
	{
		var section = $('.tab.selected').text();
		var $list = $('.' + section.toLowerCase().replace(' ', '-'));
		//$lis = $list.find('.obo-item');
		var items = data[section].allItems;

		if(searchTerms.length === 0)
		{
			clearSearch();
			return;
		}

		var terms = searchTerms.split(' ');
		var numTerms = terms.length;

		var len = items.length;
		var item;
		var ss;
		var numMatches;
		var key;

		data[section].items = [];

		for(var i = 0; i < len; i++)
		{
			item = items[i];
			key = item.instID + ':' + item.loID;
			if(typeof searchStrings[key] === 'undefined')
			{
				searchStrings[key] = (
					(typeof item.title !== 'undefined' ? item.title : item.name) +
					(typeof item.version !== 'undefined' && typeof item.subVersion !== 'undefined' ? ' ' + item.version + '.' + item.subVersion : '') +
					(typeof item.loID !== 'undefined' ? ' lo:' + item.loID : '') +
					(typeof item.instID !== 'undefined' ? ' inst:' + item.instID : '')
				).toLowerCase();
			}
			ss = searchStrings[key];

			numMatches = 0;
			for(var j = 0; j < numTerms; j++)
			{
				if(ss.indexOf(terms[j]) >= 0)
				{
					numMatches++;
				}
			}
			if(numMatches === numTerms)
			{
				data[section].items.push(items[i]);
			}
		}

		data[section].last = 0;
	}







	// navigation
	function gotoSection(sectionId, skipFadeAnimation)
	{
		if(sectionId === 'create-instance')
		{
			createForm();
		}
		else if(sectionId === 'success')
		{
			$('.selected-instance-title').html(window.__previousResponse.body.name);
			$('.preview-link').attr('href', '/preview/' + window.__previousResponse.body.loID);
		}

		var $shownSection = $('section:not(:hidden)');
		var $newSection = $('#' + sectionId);
		if($shownSection.length === 0)
		{
			if(skipFadeAnimation)
			{
				$newSection.show();
			}
			else
			{
				$newSection.fadeIn(CHANGE_SECTION_FADE_DELAY_MS);
			}
		}
		else
		{
			if(skipFadeAnimation)
			{
				$shownSection.hide();
				$newSection.show();
			}
			else
			{
				$shownSection
					.fadeOut(CHANGE_SECTION_FADE_DELAY_MS, function() {
						$newSection.fadeIn(CHANGE_SECTION_FADE_DELAY_MS);
					});
			}
		}
	}

	function gotoTab(tab)
	{
		var tabName = tab.toLowerCase().replace(' ', '-');
		var $tab = $('.' + tabName + '-tab');
		$('.tab').removeClass('selected');
		$tab.addClass('selected');
		populateList($tab.text());
		$('#select-object')
			.removeClass('community-library-section')
			.removeClass('my-objects-section')
			.removeClass('my-instances-section')
			.addClass(tabName + '-section');
	}







	// form
	function createForm()
	{
		$('#create-instance').remove();

		$('body').append($createInstanceForm.clone());

		$('.button.cancel').click(function(event) {
			event.preventDefault();
			gotoSection('select-object');
		});

		$('#attempts').spinner({
			min: 1,
			max: 255,
			stop: function(event, ui) {
				if(parseInt($(this).val(), 10) === 1)
				{
					$('.score-method-container').hide();
				}
				else
				{
					$('.score-method-container').show();
				}
			}
		});
		$('#create-instance form').submit(function(event) {
			event.preventDefault();

			$('.error-notice').hide();
			$('.error-notice ul').empty();
			$(this).find('input').removeClass('error');

			var errors = getFormErrors();
			if(errors.length > 0)
			{
				var len = errors.length;
				$('.error-notice').show();
				for(var i = 0; i < len; i++)
				{
					$('.error-notice ul').append('<li>' + errors[i] +'</li>');
				}
			}
			else
			{
				$('#submit').addClass('loading');
				$('form input').attr('disabled', 'disabled');

				submitForm();
			}
		});
		$('input').focus(function(event) {
			$(this).removeClass('error');
		});
		var d = new Date();
		$('#start-date').val((d.getMonth() + 1) + '/' + d.getDate() + '/' + d.getFullYear());
	}

	function getFormErrors()
	{
		var inputs = getFormInput();

		var errors = [];
		var attemptsRegex = new RegExp("^[0-9]{1,3}$");

		var validAttempts = attemptsRegex.test(inputs.attempts);

		if(inputs.instName.length === 0)
		{
			errors.push('Missing instance name');
			$('#instance-name').addClass('error');
		}

		if(!validAttempts)
		{
			errors.push('Invalid attempts');
			$('#attempts').addClass('error');
		}

		return errors;
	}

	function getFormInput()
	{
		return {
			instName: $.trim($('#instance-name').val()),
			attempts: $('#attempts').val().replace(' ', ''),
			scoreMethod: $('#score-method :selected').val(),
			allowScoreImport: $('#import-scores:checked').length > 0
		};
	}

	function submitForm()
	{
		var inputs = getFormInput();

		var selectedLoId = $('.obo-item.selected').attr('data-lo-id');

		$('#progress h1').html(inputs.instName);
		$('.progressbar').progressbar();
		gotoSection('progress');

		setTimeout(function() {
			startProgressBar();
		}, 500);

		$.post('/lti/picker.php', {
			selectedLoId: selectedLoId,
			instanceName: inputs.instName,
			attempts: inputs.attempts,
			scoreMethod: inputs.scoreMethod,
			allowScoreImport: inputs.allowScoreImport,
			ltiInstanceToken: __ltiToken
		}, responseHandler);
	}

	function getRandInt(min, max)
	{
		return Math.floor(Math.random() * (max - min + 1)) + min;
	}

	function startProgressBar()
	{
		// create a random number of progress bar stops
		var availStops = [1,2,3,4,5,6,7,8,9];
		var stops = { tick:0 };
		for(var i = 0, len = getRandInt(3, 5); i < len; i++)
		{
			stops[availStops.splice(getRandInt(0, availStops.length), 1)] = true;
		}

		var intervalId = setInterval(function() {
			stops.tick++;
			if(typeof stops[stops.tick] !== 'undefined')
			{
				$('.progressbar').progressbar('value', stops.tick * 10);
			}
			if(stops.tick >= 10)
			{
				clearInterval(intervalId);

				// Either end the progress bar or continue to wait if no response
				// has been returned
				if(ltiUrl !== null)
				{
					finishProgressBar();
				}
				else
				{
					ltiUrl = 'pending';
				}
			}
		}, 200);

		$(document).on('keyup', function(event) {
			if(event.keyCode === 16) // shift
			{
				$('.progress-container').find('span').html('Reticulating splines...');
				$(document).off('keyup');
			}
		})
	}

	function finishProgressBar()
	{
		$('.progress-container').addClass('success');
		$('.progress-container').find('span').html('Success!');
		$('.progressbar').progressbar('value', 100);
		setTimeout(function() {
			window.location = ltiUrl;
		}, 1000);
	}

	function responseHandler(response)
	{
		response = $.parseJSON(response);

		if(successfulResponse(response))
		{
			window.__previousResponse = response;
			$('.selected-instance-title').html(window.__previousResponse.body.name);
			//gotoSection('success');

			//@TODO: Don't get this here
			var instID = response.body.instID;
			if(typeof __returnUrl !== 'undefined' && __returnUrl !== null && __returnUrl !== '')
			{
				var widgetURL = __webUrl + 'lti/assignment.php?instID=' + instID;
				var pending = ltiUrl === 'pending';
				ltiUrl = __returnUrl + '?embed_type=basic_lti&url=' + encodeURI(widgetURL);
				if(pending)
				{
					finishProgressBar();
				}
			}
		}
		else
		{
			handleError();
		}
	}







	// utility
	function hasMoreItems()
	{
		var d = data[$('.tab.selected').text()];
		return d.last < d.items.length;
	}










	// list pages
	function appendListItem(lo, $list)
	{
		var $clone = $template.clone();
		$clone.removeClass('template');
		$clone.find('.title').html(typeof lo.title !== 'undefined' ? lo.title : lo.name)
		$clone.find('.preview').attr('href', '/preview/' + lo.loID)
		if(typeof lo.version !== 'undefined')
		{
			$clone.find('.version').html(lo.version + '.' + lo.subVersion)
		}
		if(typeof lo.objective !== 'undefined')
		{
			$clone.find('.learning-objective').html(obo.util.cleanFlashHTML(lo.objective));
		}
		$clone
			.attr('data-lo-id', lo.loID)
			.attr('data-inst-id', typeof lo.instID !== 'undefined' ? lo.instID : 0);
		if(typeof lo.instID === 'undefined')
		{
			$clone.find('.button').html('Create Instance');
		}
		else
		{
			if(parseInt(lo.startTime, 10) === 0 && parseInt(lo.endTime, 10) === 0)
			{
				$clone.find('.availabilty').css('visibility', 'hidden');
			}
			else
			{
				var now = (new Date()).getTime() / 1000;
				var start = new Date(lo.startTime * 1000);
				var end = new Date(lo.endTime * 1000);
				var startStr = (start.getMonth() + 1) + '/' + start.getDate() + '/' + String(start.getFullYear()).substr(2,4);
				var endStr = (end.getMonth() + 1) + '/' + end.getDate() + '/' + String(end.getFullYear()).substr(2,4);

				if(!(now >= lo.startTime && now <= lo.endTime))
				{
					//$clone.addClass('unavailable');
					if(now < lo.startTime)
					{
						endStr += ' (Upcoming)';
					}
					else if(now > lo.endTime)
					{
						endStr += ' (Closed)';
					}
				}

				$clone.find('.start-date').html(startStr);
				$clone.find('.end-date').html(endStr);
			}
		}

		$clone.find('.button').click(onSelectClick);
		$clone.click(onItemClick);

		$list.append($clone);

		return $clone;
	}

	function populateList(section)
	{
		$listContainer.find('.section').hide();
		$search.val('');

		//$listContainer.removeClass('loading');

		//$('.no-items').hide();

		switch(section)
		{
			case 'My Instances':
				if(typeof data['My Instances'].items === 'undefined')
				{
					//$listContainer.addClass('loading');
					$('.my-instances')
						.show()
						.addClass('loading');
					data['My Instances'].items = 'pending';
					obo.remote.makeCall('getInstances', [], function(items) {
						if(remotingResultIsError(items))
						{
							handleError(items);
							return;
						}

						var now = (new Date()).getTime() / 1000;
						items.sort(function(a, b) {
							var aOpen = now >= a.startTime && now <= a.endTime;
							var bOpen = now >= b.startTime && now <= b.endTime;

							if(aOpen != bOpen)
							{
								return aOpen ? -1 : 1;
							}
							return a.endTime > b.endTime ? -1 : 1;
						});

						data['My Instances'].items = data['My Instances'].allItems = items;
						populateSection(section);
						//$listContainer.removeClass('loading');
						$('.my-instances').removeClass('loading');
						if($search.val() !== '')
						{
							search();
						}
					});
				}
				else
				{
					showList($('.my-instances'));
				}
			break;

			case 'Community Library':
				if(typeof data['Community Library'].items === 'undefined')
				{
					$('.community-library')
						.show()
						.addClass('loading');
					data['Community Library'].items = 'pending';
					obo.remote.makeCall('getLibraryLOs', [], function(items) {
						if(remotingResultIsError(items))
						{
							handleError(items);
							return;
						}

						data['Community Library'].allItems = data['Community Library'].items = items;
						populateSection(section);
						$('.community-library').removeClass('loading');
						//$listContainer.removeClass('loading');
						if($search.val() !== '')
						{
							search();
						}
					});
				}
				else
				{
					showList($('.community-library'));
				}
			break;

			case 'My Objects':
				if(typeof data['My Objects'].items === 'undefined')
				{
					//$listContainer.addClass('loading');
					$('.my-objects')
						.show()
						.addClass('loading');
					data['My Objects'].items = 'pending';
					obo.remote.makeCall('getLOs', [], function(items) {
						if(remotingResultIsError(items))
						{
							handleError(items);
							return;
						}

						items.sort(function(a, b) {
							if(a.createTime === b.createTime)
							{
								return 0;
							}

							return a.createTime > b.createTime ? -1 : 1;
						});

						// filter:
						var filteredItems = [];
						var len = items.length;
						for(var i = 0; i < len; i++)
						{
							if(items[i].subVersion === 0 && items[i].perms.publish === 1)
							{
								filteredItems.push(items[i]);
							}
						}

						data['My Objects'].allItems = data['My Objects'].items = filteredItems;
						populateSection(section);
						$('.my-objects').removeClass('loading');
						if($search.val() !== '')
						{
							search();
						}
					});
				}
				else
				{
					showList($('.my-objects'));
				}
			break;
		}
	}

	function populateSection(section)
	{
		var className = section.replace(' ', '-').toLowerCase();
		var items = data[section].items;
		var lastIndex = Math.min(Math.min(items.length, MAX_ITEMS) + data[section].last, items.length);
		var $section = $('.' + className);
		var $list = $section.children('ul');

		if(items.length === 0)
		{
			$section.find('.no-items').show();
		}
		else
		{
			$section.find('.no-items').hide();

			var len = lastIndex;
			for(var i = data[section].last; i < len; i++)
			{
				appendListItem(items[i], $list);
			}

			data[section].last = lastIndex;
		}

		// show this only if the user is still on this tab
		if($('.tab.selected').hasClass(className + '-tab'))
		{
			$section.show();
		}
		else
		{
			$section.hide();
		}
	}

	function showList($list)
	{
		clearSearch();
		populateSection($('.tab.selected').text());
		$list.show();
		$listContainer.scrollTop(0);
	}









	// UI:
	function setupUI()
	{
		$listContainer.find('ul.template').remove();
		$createInstanceForm.remove();

		$search.keyup(function(event) {
			clearInterval(searchIntervalId);

			if(event.keyCode === 27) //esc
			{
				clearSearch();
				var section = $('.tab.selected').text();
				populateSection(section);
			}
			else
			{
				searchIntervalId = setInterval(function() {
					clearInterval(searchIntervalId);
					search();
				}, SEARCH_DELAY_MS);
			}
		});

		$('#list-container').scroll(function() {
			var $this = $(this);
			var section = $('.tab.selected').text();
			var $list = $this.find('.' + section.replace(' ', '-').toLowerCase()).children('ul');
			if($list.height() - $this.scrollTop() <= $this.height()) {
				if(hasMoreItems() && $list.find('.click-to-expand').length === 0)
				{
					populateSection(section);
				}
			}
		});



		$('#refresh').click(function(event) {
			event.preventDefault();

			var section = $('.tab.selected a').text();
			if(typeof data[section].items !== 'undefined' && data[section].items !== 'pending')
			{
				var $list = $('.' + section.toLowerCase().replace(' ', '-'));
				data[section].items = undefined;
				data[section].last = 0;
				$list.find('ul').empty();
				$list.find('.no-items').hide();

				populateList(section);
			}
		});

		$('.back-button').click(function(event) {
			event.preventDefault();
			gotoSection('wizard');
		});

		$('.tab').click(function(event) {
			event.preventDefault();
			var $this = $(this);

			if(!$this.hasClass('selected'))
			{
				gotoTab($this.text());
			}
		});

		// wizard
		$('.community-library-button-container').click(function(event) {
			event.preventDefault();
			gotoSection('select-object');
			gotoTab('Community Library');
		});

		$('.personal-library-button-container').click(function(event) {
			event.preventDefault();
			gotoSection('select-object');
			gotoTab('My Objects');
		});
	}

	function onSelectClick(event)
	{
		event.preventDefault();

		var $this = $(this);
		var $oboItem = $this.parent().parent();

		gotoSection('create-instance');
		$('#instance-name').val($oboItem.find('.title').text());

		if(typeof $oboItem.attr('data-inst-id') === 'undefined' || $oboItem.attr('data-inst-id') === '0')
		{
			$('.instance-copy-note').hide();
		}
		else
		{
			$('.instance-copy-note').show();
		}

	}

	function onItemClick(event)
	{
		if(!$(event.target).hasClass('preview'))
		{
			event.preventDefault();
		}

		var $this = $(this);

		$('.obo-item').removeClass('selected');
		$this.addClass('selected');

		var $objective = $this.find('.learning-objective');
		if($objective.html().length === 0)
		{
			obo.remote.makeCall('getLOMeta', [$this.attr('data-lo-id')], function(result) {
				if(remotingResultIsError(result))
				{
					handleError(result);
					return;
				}

				if(typeof result.objective !== 'undefined')
				{
					$objective.html(obo.util.cleanFlashHTML(result.objective));
				}
				else
				{
					$objective.html('&nbsp;');
				}
			});
		}
	}

	function startPing()
	{
		verifyTimeIntervalId = setInterval(ping, VERIFY_TIME_SECONDS * 1000);
	}

	function stopPing()
	{
		clearInterval(verifyTimeIntervalId);
	}

	function ping()
	{
		obo.remote.makeCall('getSessionRoleValid', ['LibraryUser','ContentCreator'], function(result) {
			if(!(	result === true ||
					(	typeof result !== 'undefined' &&
						typeof result.validSession !== 'undefined' &&
						result.validSession === true &&
						typeof result.hasRoles === 'object' &&
						typeof result.hasRoles.length !== 'undefined' &&
						result.hasRoles.length > 0
					)
			)) {
				killPage(MESSAGE_LOGOUT);
			}
		});
	}

	function successfulResponse(response)
	{
		return typeof response !== 'undefined' && response !== null && typeof response.success !== 'undefined' && response.success === true && typeof response.body === 'object';
	}

	function remotingResultIsError(result)
	{
		return typeof result !== 'undefined' && typeof result.errorID !== 'undefined';
	}

	function handleError(result)
	{
		var isErrorObject = remotingResultIsError(result);
		if(isErrorObject && result.errorID === 1)
		{
			killPage(MESSAGE_LOGOUT);
		}
		else if(isErrorObject)
		{
			showError(result.errorID);
		}
		else
		{
			showError();
		}
	}

	function showError(errorID)
	{
		gotoSection('dead', true);

		var message = 'Sorry, something went wrong. Please try again.';
		if(typeof errorID !== 'undefined')
		{
			message += ' (' + errorID + ')';
		}

		$('#error-window p').html(message);
		$('#error-window').dialog({
			modal: true,
			close: function() {
				gotoSection('wizard');
			}
		});
	}

	function killPage(message)
	{
		gotoSection('dead', true);
		gotoSection = function(a, b) { };

		stopPing();

		$('#error-window p').html(message);
		$('#error-window')
			.dialog({
				modal: true,
				closeOnEscape: false
			})
			.parent().find('.ui-button').hide(); // remove close button
	}







	// initalize:
	setupUI();
	startPing();
	gotoSection('wizard');

}();
