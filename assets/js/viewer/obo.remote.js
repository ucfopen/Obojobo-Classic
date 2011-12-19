// remote handles ajax / server communication
if(!window.obo)
{
	window.obo = {};
}

obo.remote = function()
{
	var makeCall = function(method, args, callback)
	{
		debug.log('makeCall: ' + method + ', args: ' + args);
		
		if(!callback)
		{
			callback = $.noop();
		}

		if(!args || typeof args === 'undefined')
		{
			args = [];
		}
		
		var callURL = "/assets/gateway-json.php/loRepository."+method+"/"+args.join("/")+'/?contentType=application/json';
		// force content type to be json so we don't have to parse every return
		// we also automatically filter every call to check for errors
		$.ajax(
		{
			url: callURL,
			context: document.body,
			dataType: 'json',
			success: callback
		});
	};
	
	// return true if jsonResult is an error object
	var isError = function(jsonResult)
	{
		return typeof jsonResult !== 'undefined' && typeof jsonResult.errorID !== 'undefined';
	};
	
	return {
		makeCall: makeCall,
		isError: isError
	};
}();