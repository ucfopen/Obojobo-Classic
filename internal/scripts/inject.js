if(!window.obojobo)
{
	//Include CSS:
	var cssNode = document.createElement('link');
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = 'http://10.171.155.45/nm/los/obojobo_website/internal/scripts/inject.css';
	cssNode.media = 'screen';
	document.getElementsByTagName("head").item(0).appendChild(cssNode);
	
	window.obojobo.data = {instData:[], calls:[]}};
	
	window.obojobo.writeBadge = function() {
		var scripts = document.getElementsByTagName('script');
		var latestScriptTag = scripts[ scripts.length - 1 ];
		var instIDStr = latestScriptTag.getAttribute("id").substr(14);
		var instIDs = instIDStr.split(',');
		var e = document.getElementById('obojobo-badge-' + instIDStr);

		for(var i = 0; i < instIDs.length; i++)
		{
			window.obojobo.data.calls[instIDs[i]] = {scriptTag:e, instIDs:instIDs};
			var script = document.createElement('script');
			script.setAttribute('type', 'text/javascript');
			script.setAttribute('src', 'http://10.171.155.45/nm/los/obojobo_website/remoting/json.php/loRepository.getInstanceData/' + instIDs[i] + '/?callback=window.obojobo.jsonCallback');
			document.getElementsByTagName("head").item(0).appendChild(script);
		}
	};
	
	window.obojobo.jsonCallback = function(json) {
		if(json != null && json.instID != null)
		{
			window.obojobo.data.instData[json.instID] = json;
			var instIDs = window.obojobo.data.calls[json.instID].instIDs;
			var numReturnedCalls = 0;
			for(var i = 0; i < instIDs.length; i++)
			{
				if(window.obojobo.data.instData[instIDs[i]])
				{
					numReturnedCalls++;
				}
			}

			if(numReturnedCalls == instIDs.length)
			{
				var script = window.obojobo.data.calls[json.instID].scriptTag;
				
				var div = document.createElement('div');
				div.className = 'obojobo-badge';
				var container = document.createElement('div');
				container.className = 'obojobo-container';
				var header = document.createElement('div');
				header.className = 'obojobo-header';
				var logo = document.createElement('div');
				logo.className = 'obojobo-logo';
				var about = document.createElement('div');
				about.className = 'obojobo-about';
				about.innerHTML = "You have an assignment in Obojobo, UCF's Learning Object system. Click the links below to begin. View the <a href='0'>Student Quick Start Guide</a> for more information.";
				header.appendChild(logo);
				header.appendChild(about);
				var content = document.createElement('div');
				content.className = 'obojobo-content';
				var p = document.createElement('p');
				if(instIDs.length == 1)
				{
					p.innerHTML = 'Complete this learning object:';
				}
				else
				{
					p.innerHTML = 'Complete these learning objects:';
				}
				var ul = document.createElement('ul');
				var curInstID;
				var curJSON;
				var li;
				var a;
				var div2;
				for(var i = 0; i < instIDs.length; i++)
				{
					curInstID = instIDs[i];
					curJSON = window.obojobo.data.instData[curInstID];
					
					li = document.createElement('li');
					a = document.createElement('a');
					a.setAttribute('href', 'http://10.171.155.45/view/' + curInstID);
					a.setAttribute('target', 'blank');
					a.innerHTML = curJSON.name;
					div2 = document.createElement('div');
					div2.className = 'obojobo-due-date';
					div2.innerHTML = 'Due by <strong>' + window.obojobo.formatDate(curJSON.endTime) + '</strong>';
					li.appendChild(a);
					li.appendChild(div2);
					
					ul.appendChild(li);
				}
				
				content.appendChild(p);
				content.appendChild(ul);
				
				container.appendChild(header);
				container.appendChild(content);
				div.appendChild(container);
				
				//Replace script tag with div:
				var parent = script.parentNode;
				console.log(script);
				console.log(parent);
				parent.replaceChild(div, script);
			}
		}
	};
	
	window.obojobo.formatDate = function(secs) {
		var d = new Date(secs * 1000);
		ampm = d.getHours() > 12 ? 'PM' : 'AM';
		return (d.getMonth() + 1) + '/' + d.getDate() + '/' + d.getFullYear() + ' at ' + ((d.getHours() + 1) % 12) + ':' + window.obojobo.zeroFill(d.getMinutes(), 2) + ' ' + ampm;
	};
	
	window.obojobo.zeroFill = function(n, size) {
		var r = n + '';
		while(r.length < size)
		{
			r = "0" + r;
		}

		return r;
	};
}

window.obojobo.writeBadge();