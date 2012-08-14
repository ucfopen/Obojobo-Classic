// requires :tabbable from jQuery-UI

if(!window.obo)
{
	window.obo = {};
}

obo.dialog = function()
{
	var HIDE_SWF_INTERVAL_SECONDS = 1;
	var MAX_TICKS = 10;

	var hideSwfIntervalID = undefined;
	var tabPlace;
	var tabArray = [];
	var ticks;
	
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
		ticks = 0;
		
		// defaults:
		options.activelyHideSWFs = typeof options.activelyHideSWFs === 'undefined' ? true : options.activelyHideSWFs;
		options.modal = typeof options.modal === 'undefined' ? true : options.modal;
		options.escClose = typeof options.escClose === 'undefined' ? true : options.escClose;
		options.buttons = typeof options.buttons === 'undefined' ? [{label: 'OK'}] : options.buttons;
		options.center = typeof options.center === 'undefined' ? false : options.center;

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
		if(options.center)
		{
			$p.css('text-align', 'center');
		}
		var $buttons = $('<div class="dialog-buttons"></div>');
		var $curButton;
		var len = options.buttons.length;
		for(var i = 0; i < len; i++)
		{
			$curButton = $('<a class="button" role="button" href="#">' + options.buttons[i].label + '</a>');
			if(typeof options.buttons[i].id !== 'undefined')
			{
				$curButton.attr('id', options.buttons[i].id);
			}
			if(typeof options.buttons[i].action === 'function')
			{
				$curButton.on('click', options.data, options.buttons[i].action);
			}
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
			closeCallback($dialog);
		});

		tabArray = $('.dialog').find(':tabbable');
		// kill any existing keydown from us first
		$(document).off('keydown');
		$(document).on('keydown', function(event) {
			var code = event.keyCode || event.which;
			if(options.escClose && code == 27) //ESC
			{
				//obo.dialog.closeDialogs();
				closeCallback($dialog);
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
			hideSwfIntervalID = setInterval(function() {
				ticks++;
				if(ticks > MAX_TICKS)
				{
					clearInterval(hideSwfIntervalID);
				}
				else
				{
					obo.view.rehideSWFs();
				}
			}, HIDE_SWF_INTERVAL_SECONDS * 1000);
		}
		else if(typeof hideSwfIntervalID !== 'undefined' && !options.activelyHideSWFs)
		{
			clearInterval(hideSwfIntervalID);
			hideSwfIntervalID = undefined;
		}

		return;
	};
	
	return {
		showDialog: showDialog,
		closeDialogs: closeDialogs
	};
}();