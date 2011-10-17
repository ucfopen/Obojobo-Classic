// remote handles ajax / server communication
if(!window.obo)
{
	window.obo = {};
}

//@TODO: Need to have our callbacks close the dialog
obo.dialog = function()
{
	//@public
	// convience wrapper function to simplify jquery ui dialog creation
	/*
	var closeDialog = function(dialogID)
	{
		if(dialogs[dialogID] != undefined)
		{
			var $dialog = dialogs[dialogID];
			//$dialog.remove();
			//delete dialogs[dialogID];
		}
	};*/
	
	var showDialog = function(options)
	{
		if($('#content-blocker').length == 0)
		{
			//$('body').append($('<div id="content-blocker"></div>'));
		}
		
		obo.view.hideSWFs();
		
		//var id = 'dialog-' + dialogID;
		//dialogID++;
		
		options.width = 400;
		options.resizable = false;
		options.modal = true;
		
		var $dialog = $('<div class="dialog"></div>');
		var $title = $('<div class="dialog-title-bar">' + options.title + '</div>');
		var $close = $('<a class="dialog-close-button simplemodal-close" href="#">Close</a>');
		/*$close.click(function(event) {
			obo.dialog.closeDialog($(event.target).parent().parent().attr('id'));
		});*/
		$title.append($close);
		var $p = $('<p>' + options.contents + '</p>');
		var $buttons = $('<div class="dialog-buttons"></div>');
		var $curButton;
		for(var i in options.buttons)
		{
			$curButton = $('<a class="button" role="button" href="#">' + options.buttons[i].label + '</a>');
			/*$curButton.click(function(event) {
				if(options.buttons[i].action != undefined)
				{
					options.buttons[i].action();
				}
				obo.dialog.closeDialog($(event.target).parent().parent().attr('id'));
			});*/
			$curButton.click(options.buttons[i].action);
			$buttons.append($curButton);
			$curButton.addClass('simplemodal-close');
		}
		$dialog.append($title);
		$dialog.append($p);
		$dialog.append($buttons);
		
		//$('body').append($dialog);
		
		//$dialog.css("top", (($(window).height() - $dialog.outerHeight()) / 3) + $(window).scrollTop() + "px");
		//$dialog.css("left", (($(window).width() - $dialog.outerWidth()) / 2) + $(window).scrollLeft() + "px");
		
		//dialogs[id] = $dialog;
		
		$.modal($dialog, {onClose: function(dialog) {
			obo.view.unhideSWFs();
			$.modal.close();
		}});
		
		//$('<div>' + options.contents + '</div>').dialog(options);
		return;
	};
	
	var showOKDialog = function(options)
	{
		options.buttons = [{label: 'OK'}];
		obo.dialog.showDialog(options);
	};
	
	return {
		showDialog: showDialog,
		showOKDialog: showOKDialog
	};
}();