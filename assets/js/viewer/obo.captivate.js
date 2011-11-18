// Handles scoring from captivate spy swfs and 

if(!window.obo)
{
	window.obo = {};
}

obo.captivate = function()
{
	// we store which id we are recording captivate data for. If this changes we need to clear out
	// our score data and start over.
	//var captivateID = -1;
	// we store the captivate score data for captivate 5 swfs since we are responsible for
	// calculating the grade
	var scoreDatas = {};
	
	// public method which ExternalInterface calls whenever it gets a captivate event.
	// look in the CaptivateSpy fla files for the ExternalInterface code
	var onCaptivateSpyEvent = function(event)
	{
		console.log('onCaptivateSpyEvent',event, event.type, event.id,scoreDatas);
		
		/*
		// if this event is for a different id then reset our data
		if(event.id != captivateID)
		{
			captivateID = event.id;
			scoreData = {responses:[], numQuestionsAnswered:0};
		}*/
		if(scoreDatas[event.id] == undefined)
		{
			scoreDatas[event.id] = {responses:[], numQuestionsAnswered:0};
		}
		
		var scoreData = scoreDatas[event.id];
		
		// we listen for two versions of captivate - 2 (AS2) and 5 (AS3):
		switch(event.version)
		{
			case 2:
				switch(event.type)
				{
					case 'score':
						obo.view.updateInteractiveScore(Math.round(event.data.percent * 100));
						break;
				}
				break;
			case 5:
			default: // assume newer version of captivate if we don't know
				switch(event.type)
				{
					case 'CPInteractiveItemSubmitEvent':
					case 'CPQuestionSubmitEvent':
						var page = obo.model.getPage();
						
						var questionEventData = event.data.questionEventData;
						
						var index = questionEventData.questionNumber;
						//check to see if the question was already answered
						if(scoreData.responses[index] == undefined)
						{
							scoreData.numQuestionsAnswered++;
						}
						
						// store response
						scoreData.responses[index] = (questionEventData.questionScore / questionEventData.questionMaxScore);
						
						// if all questions answered, send info to obojobo
						var total = 0;
						for(var i in scoreData.responses)
						{
							total += scoreData.responses[i];
						}
						
						var percent = Math.round((total / event.data.cpQuizInfoTotalQuestionsPerProject) * 100);
						
						obo.view.updateInteractiveScore(percent);
						break;
				}
				break;
		}
	};
	
	// removes all scoring information.
	var clearCaptivateData = function()
	{
		scoreDatas = {};
	};
	
	// @public:
	return {
		onCaptivateSpyEvent: onCaptivateSpyEvent,
		clearCaptivateData: clearCaptivateData
	}
}();