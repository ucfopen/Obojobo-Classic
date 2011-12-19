// remote handles ajax / server communication
if(!window.obo)
{
	window.obo = {};
}

obo.remote = function()
{
	var makeCall = function(method, args, callback)
	{
		debug.log('__makeCall: ' + method + ', args: ' + args);
		//debug.log("http://obo/assets/gateway-json.php/loRepository."+method+"/"+arguments.join("/")+'/?contentType=application/json');
		
		if(!callback)
		{
			callback = $.noop();
		}

		if(!args || typeof args === 'undefined')
		{
			args = [];
		}
		
		var callURL = "/assets/gateway-json.php/loRepository."+method+"/"+args.join("/")+'/?contentType=application/json';
		debug.log(callURL);
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
		return jsonResult && jsonResult.hasOwnProperty('errorID');
	};
	
	return {
		makeCall: makeCall,
		isError: isError
	};
}();

/*<mx:RemoteObject id="losService" fault="faultHandler(event)" requestTimeout="40" showBusyCursor="true" source="loRepository" destination="loRepository" >
        <mx:method name="doLogout" result="onLogout(event)" />
        <mx:method name="getSessionValid" /> <!-- result="onVerifySession(event)" /> -->
        <mx:method name="getSessionRoleValid" /> <!-- result="onVerifySessionRole(event)" />-->   
        <mx:method name="getLO" result="onGetLOFull(event)" />
        <mx:method name="createInstanceVisit" result="onGetInstance(event)" />
        <mx:method name="trackSubmitQuestion" result="onSubmitQuestion(event)" />
        <mx:method name="trackSubmitMedia" result="onSubmitMedia(event)" />
        <mx:method name="trackAttemptStart" result="onStartAttempt(event)" />
        <mx:method name="trackAttemptEnd" result="onEndAttempt(event)" />
        <mx:method name="trackSectionChanged" result="onTrackSectionChanged(event)" />
        <mx:method name="trackPageChanged" result="onTrackPageChanged(event)" />
        <mx:method name="getLOMeta" result="onGetLOMeta(event);" />
        <mx:method name="getInstanceData" result="onGetLOMeta(event);" />
        <mx:method name="doLogin" />
        <mx:method name="getUser" result="onGetUserResponse(event)" />
        <mx:method name="doImportEquivalentAttempt" result="onImportEquivalentAttempt(event);"/>
        <mx:method name="trackVisitResume" result="onResumeVisit(event);" />
        <mx:method name="getPasswordReset"/>
        <mx:method name="editPassword"  />        
        <mx:method name="editPasswordWithKey" />
        <mx:method name="getLoginOptions"/>
        <mx:method name="editMedia" />
        <mx:method name="trackClientError" />
	   <mx:method name="doPluginCall" />*/