// A very simple head.js - this will load scripts as needed with a callback.
// We store the state of each script so we won't load any script twice.
// This is an alternative to jQuery's getScript and head.js.

if(!window.obo)
{
	window.obo = {};
}

obo.loader = function()
{
	// @PRIVATE:
	
	var scripts = {};
	
	var loadFunction = function(url)
	{
		if(scripts[url] != undefined)
		{
			scripts[url].status = 'loaded';
			if(scripts[url].callback != undefined)
			{
				scripts[url].callback();
			}
		}
	}
	
	// @PUBLIC:
	
	// loadScript either loads the script if it hasn't been loaded,
	// does nothing if it is currently loading, or simply calls the
	// callback if the script has already loaded.
	var loadScript = function(url, callback)
	{
		if(scripts[url] == undefined)
		{
			//load
			var script = document.createElement('script');
			script.setAttribute('src', url);
			script.setAttribute('type', 'text/javascript');
			script.src = url;
			scripts[url] = {status: 'loading', callback:callback};
			script.onload = loadFunction(url);
			//script.onreadystatechange = loadFunction(url);
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
		}
		else if(scripts[url].status == 'loaded')
		{
			if(scripts[url].callback)
			{
				scripts[url].callback();
			}
		}
		else if(scripts[url].status == 'loading')
		{
			// do nothing
		}
	}
	
	return {
		loadScript: loadScript
	};
}();