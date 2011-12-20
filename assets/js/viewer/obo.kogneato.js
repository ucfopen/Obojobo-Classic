// Handles scoring from captivate spy swfs and 

if(!window.obo)
{
	window.obo = {};
}

obo.kogneato = function()
{
	// public method which ExternalInterface calls whenever it gets a kogneato event.
	// look in the kogneato-widget fla files for the ExternalInterface code
	var onKogneatoEvent = function(event)
	{
		debug.log('onKogneatoEvent',event, event.type, event.id);
		if(typeof event.data !== 'undefined' && !isNaN(event.data))
		{
			obo.view.updateInteractiveScore(Math.round(event.data));
		}
	};
	
	// @public:
	return {
		onKogneatoEvent: onKogneatoEvent
	}
}();