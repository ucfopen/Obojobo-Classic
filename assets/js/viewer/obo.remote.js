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
			
		// allow us to load a flat json learning object in preview mode
		// ?loID=local19203 will load /19203.json from the server
		if(method == 'getLO' && args[0].substr(0,5) == 'local')
		{
			var callURL = "/"+args[0].substring(5)+'.json';
		}
		else
		{
			var callURL = "/api/json.php/loRepository."+method+"/"+args.join("/")+'/?contentType=application/json';
		}
		

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
		return typeof jsonResult !== 'undefined' && jsonResult !== null && typeof jsonResult.errorID !== 'undefined';
	};
	
	return {
		makeCall: makeCall,
		isError: isError
	};
}();