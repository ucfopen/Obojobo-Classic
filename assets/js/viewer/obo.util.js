/**
	util simply contains helper functions
*/

if(!window.obo)
{
	window.obo = {};
}

obo.util = function()
{
	// @private
	
	//@TODO ul and p should have margin = 0
	/** This attempts to recreate HTML from flash HTML exactly **/
	var cleanFlashHTMLStrict = function(input)
	{
		var pattern;
		var groups;
		var groupString;

		var pReplaceRules = [
			['align', 'text-align', ''],
			['face', 'font-family', ''],
			['color', 'color', ''],
			['size', 'font-size', 'px'],
			['letterspacing', 'letter-spacing', 'px']
		];

		var tfReplaceRules = [
			['indent', 'text-indent', 'px'],
			['leftmargin', 'margin-left', 'px'],
			['rightmargin', 'margin-right', 'px']
		];

		//Convert <textformat ...><li> into <li style='...'>
		var matchFound = true;
		while(matchFound)
		{
			pattern = /<\s*textformat([a-z=A-Z"'0-9 ]*)?\s*><\s*li\s*>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 2)
			{
				groupString = groups[1];
				var style = generateStyleFromFlashHTMLTag(groupString, tfReplaceRules);
				//We only want to add this span if there are styles associated with it
				if(style.length > 0)
				{
					input = input.substr(0, pattern.lastIndex).replace(pattern, '<li style="' + style + '">') + input.substr(pattern.lastIndex);
				}
				else
				{
					input = input.substr(0, pattern.lastIndex).replace(pattern, '<li>') + input.substr(pattern.lastIndex);
				}
			}
			else
			{
				matchFound = false;
			}
		}
		pattern = /<\/li><\/textformat>/gi;
		input = input.replace(pattern, "</li>");

		var matchFound = true;
		//@TODO: handle textformat with no style options
		//Convert <textformat> into <div>
		while(matchFound)
		{
			pattern = /<\s*textformat(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 2)
			{
				groupString = groups[1];
				var style = generateStyleFromFlashHTMLTag(groupString, tfReplaceRules);
				//We only want to add this span if there are styles associated with it
				if(style.length > 0)
				{
					input = input.substr(0, pattern.lastIndex).replace(pattern, '<div style="' + style + '">') + input.substr(pattern.lastIndex);
				}
				else
				{
					input = input.substr(0, pattern.lastIndex).replace(pattern, '<div>') + input.substr(pattern.lastIndex);
				}
			}
			else
			{
				matchFound = false;
			}
		}

		pattern = /<\/textformat>/gi;
		input = input.replace(pattern, "</div>");

		//Convert <p><font> into <p>
		var matchFound = true;
		while(matchFound)
		{
			pattern = /<\s*p(\s+align=(?:"|')(?:left|right|center|justify)(?:"|'))?\s*><\s*font(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 3)
			{
				groupString = groups[1] + ' ' + groups[2];
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<p style="' + generateStyleFromFlashHTMLTag(groupString, pReplaceRules) + '">') + input.substr(pattern.lastIndex);
			}
			else
			{
				matchFound = false;
			}
		}

		pattern = /<\/font><\/p>/gi;
		input = input.replace(pattern, "</p>");

		//Convert single <font> into <span>
		var matchFound = true;
		while(matchFound)
		{
			pattern = /<\s*font(\s+.*?=(?:"|').*?(?:"|'))\s*>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 2)
			{
				groupString = groups[1];
				input = input.substr(0, pattern.lastIndex).replace(pattern, '<span style="' + generateStyleFromFlashHTMLTag(groupString, pReplaceRules) + '">') + input.substr(pattern.lastIndex);
			}
			else
			{
				matchFound = false;
			}
		}

		pattern = /<\/font>/gi;
		input = input.replace(pattern, "</span>");

		// remove any previously added ul tags
		pattern = /<\/?ul>/gi;
		input = input.replace(pattern, "");

		// add <ul></ul> arround list items
		pattern = /(<li(.*?)?>([\s\S]*?)<\/li>)/gi;
		input = input.replace(pattern, "<ul>$1</ul>");

		// kill extra </ul><ul> that are back to back - this will make proper lists
		pattern = /<\/ul><ul>/gi;
		input = input.replace(pattern, "");

		// find empty tags keeping space in them
		pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");

		pattern = /<(\w+)>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");

		var matchFound = true;
		while(matchFound)
		{
			// find empty tags keeping space in them
			pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 3)
			{
				groupString = groups[2];
				input = input.substr(0, pattern.lastIndex).replace(pattern, groupString) + input.substr(pattern.lastIndex);
			}
			else
			{
				matchFound = false;
			}
		}

		var matchFound = true;
		while(matchFound)
		{
			pattern = /<(\w+)>(\s*?)<\/\1>/gi;
			groups = pattern.exec(input);
			if(groups && groups.length >= 3)
			{
				groupString = groups[1];
				input = input.substr(0, pattern.lastIndex).replace(pattern, groupString) + input.substr(pattern.lastIndex);
			}
			else
			{
				matchFound = false;
			}
		}

		input = createOMLTags(input);

		return input;
	};
	
	var generateStyleFromFlashHTMLTag = function(attribs, rules)
	{
		var style = '';
		var reg;
		var match;
		for(var a in rules)
		{
			reg = new RegExp(rules[a][0] + '\s*=\s*(?:\'|")(.+?)(?:\'|")', 'gi');
			match = reg.exec(attribs)
			if(match != null && match.length >= 2)
			{
				style += rules[a][1] + ':' + match[1] + rules[a][2] + ';';
			}
		}
		return style;
	};

	var createOMLTags = function(input)
	{
		var pattern;

		/*Find OML tooltips*/
		//@TODO: Img
		pattern = /\[\s*?tooltip\s+?text\s*?=\s*?(?:"|')(.+?)(?:"|')\s*?](.+)\[\/tooltip\]/gi;
		input = input.replace(pattern, '<span title="$1" class="oml oml-tip">$2</span>');

		/*Find OML page links*/
		pattern = /\[\s*?page\s+?id\s*?=\s*?(?:"|')(.+?)(?:"|')\s*?](.+)\[\/page\]/gi;

		//@TODO: Left or right arrow depending on if that would be foward or back
		input = input.replace(pattern, '<a class="oml oml-page-link" data-page-id="$1" href="' + location.href + 'page/$1" title="' + '&rarr; Page $1">$2</a>');
		/*input = input.replace(pattern, '<a class="oml oml-page-link" data-page-id="$1" href="http://www.google.com/" title="Go to page $1">$2</a>');*/
		//@TODO: Fix quote issues (" --> &quot;)

		return input;
	};
	
	// @public
	getRGBA = function(colorInt, alpha)
	{
		return 'rgba(' + ((colorInt >> 16) & 255) + ',' + ((colorInt >> 8) & 255) + ',' + (colorInt & 255) + ',' + alpha + ')';
	};

	// Old learning objects were saved using flash's textfields - which suck at html
	cleanFlashHTML = function(input, strict)
	{
		if(strict === true)
		{
			return cleanFlashHTMLStrict(input);
		}

		// get rid of all the textformat tags
		var pattern = /<\/?textformat\s?.*?>/gi;
		input = input.replace(pattern, "");

		// combine <p><font>...</font></p> tags to just <p></p>
		pattern = /<p\s?(.*?)><font.*?(?:FACE="(\w+)").*?(?:SIZE="(\d+)").*?(?:COLOR="(#\d+)").*?>/gi;
		// input = input.replace(pattern, '<p style="font-family:$2;font-size:$3px;color:$4;">');
		input = input.replace(pattern, '<p>');

		pattern = /<\/font><\/p>/gi;
		input = input.replace(pattern, "</p>");

		// convert lone <font>...</font> tags to spans
		pattern = /<font.*?(?:KERNING="\d+")?.*?(?:FACE="(\w+)")?.*?(?:SIZE="(\d+)")?.*?(?:COLOR="(#\d+)")?.*?>/gi;
		// input = input.replace(pattern, '<span style="font-family:$1;font-size:$2px;color:$3;">');
		input = input.replace(pattern, '<span>');

		pattern = /<\/font>/gi;
		input = input.replace(pattern, "</span>");

		// find empty tags keeping space in them
		pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");

		pattern = /<(\w+)>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");

		// remove any previously added ul tags
		pattern = /<\/?ul>/gi;
		input = input.replace(pattern, "");

		// add <ul></ul> arround list items
		pattern = /<LI>([\s\S]*?)<\/LI>/gi;
		input = input.replace(pattern, "<ul><li>$1</li></ul>"); //@TODO DOES THIS WORK??????????

		// kill extra </ul><ul> that are back to back - this will make proper lists
		pattern = /<\/ul><ul>/gi;
		input = input.replace(pattern, "");

		input = createOMLTags(input);

		return input;
	};
	
	strip = function(html)
	{
		return html.replace(/</g,'v').replace(/>/g,'&gt;').replace(/&/g,'&amp;').replace(/\"/g, '');
	};

	//@TODO: Get rid of this?
	/*
	getURLParam = function(strParamName)
	{
		var strReturn = "";
		var strHref = window.location.href;
		if ( strHref.indexOf("?") > -1 )
		{
			var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
			var aQueryString = strQueryString.split("&");
			for ( var iParam = 0; iParam < aQueryString.length; iParam++ )
			{
				if ( aQueryString[iParam].indexOf(strParamName.toLowerCase() + "=") > -1 )
				{
					var aParam = aQueryString[iParam].split("=");
					strReturn = aParam[1];
					break;
				}
			}
		}
		return unescape(strReturn);
	};*/
	
	// given a answer array it will return the answer object matching answerID
	getAnswerByID = function(answers, answerID)
	{
		for(var i in answers)
		{
			if(answers[i].answerID == answerID)
			{
				return answers[i];
			}
		}

		return undefined;
	};
	
	isIOS = function()
	{
		return navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i);
	}
	
	return {
		getRGBA: getRGBA,
		cleanFlashHTML: cleanFlashHTML,
		strip: strip,
		getAnswerByID: getAnswerByID,
		isIOS: isIOS
	};
}();