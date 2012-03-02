// requires :tabbable from jQuery-UI

// remote handles ajax / server communication
if(!window.obo)
{
	window.obo = {};
}

obo.dialog = function()
{
	var HIDE_SWF_INTERVAL_SECONDS = 1;

	var hideSwfIntervalID = undefined;
	var tabPlace;
	var tabArray = [];
	
	var closeDialogs = function()
	{
		$('#obo-dialog-container').remove();
		$('#obo-dialog-overlay').remove();
		clearInterval(hideSwfIntervalID);
		hideSwfIntervalID = undefined;
		$(document).off('keydown');
		obo.view.unhideSWFs();
	};
	
	var showDialog = function(options)
	{
		obo.view.hideSWFs();
		tabPlace = 0;
		
		// defaults:
		options.activelyHideSWFs = typeof options.activelyHideSWFs === 'undefined' ? true : options.activelyHideSWFs;
		options.modal = typeof options.modal === 'undefined' ? true : options.modal;
		options.escClose = typeof options.escClose === 'undefined' ? true : options.escClose;
		options.buttons = typeof options.buttons === 'undefined' ? [{label: 'OK'}] : options.buttons;

		// check to see if our modal blocker is already up
		if($('#obo-dialog-overlay').length == 0 && options.modal)
		{
			// add it
			$('body').append($('<div id="obo-dialog-overlay"></div>'));
		}
		else if($('#obo-dialog-overlay').length > 0 && !options.modal)
		{
			$('#obo-dialog-overlay').remove();
		}

		// check to see if our modal container exists
		if($('#obo-dialog-container').length == 0)
		{
			// add it
			$('body').append($('<div id="obo-dialog-container"></div>'));
		}
		else
		{
			// empty existing popups
			$('#obo-dialog-container').empty();
		}
		
		var $dialog = $('<div class="dialog"></div>');
		var $title = $('<div class="dialog-title-bar">' + options.title + '</div>');
		var $close = $('<a class="dialog-close-button" href="#">Close</a>');

		if(typeof options.closeButton === 'undefined' || options.closeButton !== false)
		{
			$title.append($close);
		}
		var $p = $('<p>' + options.contents + '</p>');
		var $buttons = $('<div class="dialog-buttons"></div>');
		var $curButton;
		for(var i in options.buttons)
		{
			$curButton = $('<a class="button" role="button" href="#">' + options.buttons[i].label + '</a>');
			if(typeof options.buttons[i].id !== 'undefined')
			{
				$curButton.attr('id', options.buttons[i].id);
			}
			$curButton.click(options.buttons[i].action);
			$buttons.append($curButton);
			$curButton.addClass('dialog-close-button');
		}
		$dialog.append($title);
		$dialog.append($p);
		$dialog.append($buttons);

		$dialog.width(typeof options.width === 'undefined' ? 400 : options.width);

		var closeCallback = typeof options.closeCallback !== 'undefined' ? options.closeCallback : function(dialog) {
			obo.dialog.closeDialogs();
		};

		$('#obo-dialog-container').append($dialog);

		// fix center
		var $dialog = $('.dialog');
		$dialog	.css('margin-left', -($dialog.width() / 2) + 'px')
				.css('margin-top', -($dialog.height() / 2) + 'px');

		// we set up our key listeners:
		$('.dialog-close-button').click(function(event) {
			event.preventDefault();
			closeCallback();
		});

		tabArray = $('.dialog').find(':tabbable');
		// kill any existing keydown from us first
		$(document).off('keydown');
		$(document).on('keydown', function(event) {
			var code = event.keyCode || event.which;
			if(options.escClose && code == 27) //ESC
			{
				//obo.dialog.closeDialogs();
				closeCallback();
			}
			else if(code == 9)
			{
				if(event.shiftKey)
				{
					tabPlace--;
					if(tabPlace == -1)
					{
						tabPlace = tabArray.length - 1;
					}
				}
				else
				{
					tabPlace++;
					if(tabPlace == tabArray.length)
					{
						tabPlace = 0;
					}
				}
				if(typeof tabArray[tabPlace] !== 'undefined')
				{
					tabArray[tabPlace].focus();
				}

				return false;
			}
		});

		// we start a timer to prevent swf content from popping up over our dialogs
		if(typeof hideSwfIntervalID === 'undefined' && options.activelyHideSWFs)
		{
			hideSwfIntervalID = setInterval(obo.view.rehideSWFs, HIDE_SWF_INTERVAL_SECONDS * 1000);
		}
		else if(typeof hideSwfIntervalID !== 'undefined' && !options.activelyHideSWFs)
		{
			clearInterval(hideSwfIntervalID);
			hideSwfIntervalID = undefined;
		}

		return;
	};

	console.log(obo.dialog);
	
	return {
		showDialog: showDialog,
		closeDialogs: closeDialogs
	};
}();