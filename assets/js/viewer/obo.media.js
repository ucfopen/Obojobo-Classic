// utility class for handling media

// this class uses the iframe API for YouTube, which doesn't work with IE7:
// "The end user must be using a browser that supports the HTML5 postMessage feature.
// Most modern browsers support postMessage, though Internet Explorer 7 does not support it."
// -from http://code.google.com/apis/youtube/iframe_api_reference.html
// @TODO - Provide alternate API backup?

if(!window.obo)
{
	window.obo = {};
}

obo.media = function()
{
	// @const
	var AUTOLOAD_FLASH = true;
	
	// @private
	
	var youTubeAPIReady = false;
	
	// we store a reference to the YT player instances and media objects
	//var ytPlayers = {};
	
	// counter used to create uniqueIDs for media.
	// this always increases, so each media will be uniquely identified.
	var mediaCount = 0;
	
	// params object used for all jwplayer/swf instances
	var params = null;
	
	// return the params object for swf embedding.
	// this allows us to call this when needed, so obo.model.getMode will be defined
	var getParams = function()
	{
		if(params != null)
		{
			return params
		}
		else
		{
			return {
				menu: 'false',
				allowScriptAccess: 'sameDomain',
				allowFullScreen: 'true',
				bgcolor: '#FFFFFF',
				align: 't',
				salign: 't',
				// we want high performance gpu, but in preview mode the popups for the subnavs (question alts)
				// can be blocked by the swf (gpu/direct swfs are the front most layer)
				wmode: obo.model.getMode() == 'preview' ? 'opaque' : 'direct'
			}
		};
	}
	
	// update the duration
	/*
	var reportDurationForYoutubeVideo = function(mediaID, duration)
	{
		// @TODO - In the future we should simply store the mediaID instead of grabbing it again
		var mediaObject = obo.model.getMediaObjectByID(mediaID);
		mediaObject.duration = duration;
		
		// @TODO - This doesn't work because it doesn't JSON encode the mediaObject.
		// we need to create a more specific call - perhaps updateMediaProperty
		obo.remote.makeCall('editMedia', [mediaObject, obo.model.getMode() == 'instance' ? obo.model.getLO().viewID : -1]);
	};*/
	
	//@TODO need to deal with the player variable
	var buildYouTubeVideo = function($placeholder)
	{
		var youtubeID = $placeholder.attr('data-youtube-id');
		
		var player = new YT.Player($placeholder.attr('id'), {
			width: 640,
			height: 390,
			videoId: youtubeID
			/*
			events: { //@TODO: make onStateChange work for multiple videos (send callback?)
				'onStateChange': onYouTubeStateChange
			}*/
		});
		//ytPlayers[$placeholder.attr('data-media-id')] = player;
		
		// remove it's placeholder status:
		$placeholder.removeClass('youtube-placeholder').addClass('youtube-container');
		
		return player;
	};
	
	var swfize = function()
	{
		console.log('swfize');
		
		if(AUTOLOAD_FLASH)
		{
			$('.swf-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('swf-placeholder').addClass('swf-container');
				var mediaID = $placeholder.attr('data-media-id'); //placeholder.id.split('media-')[1];	
				$placeholder.parent('.page-item').css('height', $placeholder.css('height')).css('width', $placeholder.css('width'));
				var url = '/media/';
				var captivateVersion = $placeholder.attr('data-captivate-version');
				if(captivateVersion && captivateVersion.length > 0)
				{
					console.log('captivateVersion', captivateVersion);
					switch(captivateVersion)
					{
						case '2':
							url = '/assets/flash/captivateSpyCP2.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							break;
						case '5':
							url = '/assets/flash/captivateSpyCP5.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							break;
					}
				}
				swfobject.embedSWF(url + mediaID, placeholder.id, '100%', '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
			});
		}
		
		obo.view.updateInteractiveScore(0);
	};
	/*
	var capize = function()
	{
		////mediaObject.source = './captivateSpy.swf?commChannel=' + 'bridgeData.channel' + '&captivateURL=' + escape('../getAsset.php?id=' + _mediaObject.id);
		console.log('capize');
		if(AUTOLOAD_FLASH)
		{
			$('.cap-placeholder').each(function(index, placeholder)
			{
				console.log(placeholder);
				console.log(placeholder.id);
				var $placeholder = $(placeholder);
				$placeholder.removeClass('swf-placeholder').addClass('swf-container');
				var mediaID = $placeholder.attr('data-media-id'); //placeholder.id.split('media-')[1];	
				$placeholder.parent('.page-item').css('height', $placeholder.css('height')).css('width', $placeholder.css('width'));
				swfobject.embedSWF( "/captivate/" + mediaID, placeholder.id, '100%', '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
			});
		}
	};*/
	
	// converts any youtube placeholder elements with an instance of the youtube player.
	var youtubeize = function()
	{
		if(AUTOLOAD_FLASH)
		{
			$('.youtube-placeholder').each(function(index, element) {
				buildYouTubeVideo($(this));
			});
		}
	};
	
	var jwplayerize = function()
	{
		if(AUTOLOAD_FLASH)
		{
			$('.flv-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('flv-placeholder').addClass('flv-container');
				//var mediaID = placeholder.id.split('media-')[1];
				var mediaID = $placeholder.attr('data-media-id');
				var flashvars = {
					file: "/media/" + mediaID +'/video.flv',
					screencolor: '#000000',
					dock: true,
					'controlbar.idlehide': true,
					'controlbar.position': 'over'
				}

				swfobject.embedSWF( "/assets/jwplayer/player.swf", placeholder.id, $placeholder.width() + 'px', $placeholder.height() + 'px', "10",  "/assets/flash/expressInstall.swf", flashvars, getParams());
			});
		}
	}
	
	// @public
	
	// to be called when a page changes which clears out video info
	/*
	var clear = function()
	{
		ytPlayers = {};
		youTubeVideoCount = 0;
	};*/
	
	// creates either a YouTube or non-YouTube video, appends to $target
	var createMedia = function(mediaObject, $target)
	{
		console.log('createMedia', mediaObject, $target);
		mediaObject.itemType = 'cap5';
		
		$target.attr('data-media-width', mediaObject.width).attr('data-media-height', mediaObject.height);
		
		switch(mediaObject.itemType.toLowerCase())
		{
			case 'pic':
				$target.append('<img id="pic-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="pic" src="/media/' + mediaObject.mediaID + '" title="' + mediaObject.title + '" alt="' + mediaObject.title + '">');
				break;
			case 'cap5':
			case 'swf':
				var maxWidth = parseInt($target.css('max-width').replace('px', ''));
				console.log('maxWidth', maxWidth);
				var mediaWidth = Math.min(maxWidth, mediaObject.width);
				console.log('mediaWidth', mediaWidth);
				var scaleFactor = mediaWidth / mediaObject.width;
				var mediaHeight = Math.ceil(mediaObject.height * scaleFactor);
				$swf = $('<div id="swf-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="swf-placeholder" style="height:' + mediaHeight + 'px;width:' + mediaWidth + 'px;">SWF ' + mediaObject.title + '</div>');
				
				var section = obo.model.getSection();
				if(section == 'practice' || section == 'assessment')
				{
					// there are two captivate connection methods - version 2-4 or version 5.
					$swf.attr('data-captivate-version', mediaObject.itemType.toLowerCase() == 'cap5' ? '5' : '2');
				}
				console.log('$swf', $swf);
				$target.append($swf);
				$target.children('#swf-' + mediaCount).load('/assets/templates/viewer.html #swf-alt-text', swfize);
				/*
				//$('.media-item').click(function(event) {
				setTimeout(function () {
					console.log('click');
					var $media = $target;
					$media.css('max-width', '');
					$('body').append($media);
					$media.css('position', 'absolute');
					$media.css('left', 0);
					$media.css('top', 0);
					$media.css('width', mediaObject.width + 'px');
					$media.css('height', mediaObject.height + 'px');
				}, 2000);
				//});*/
				
				$('#preview-mode-notification').click(function() {
					$('.question').addClass('modal');
					$('.media-item').attr('data-page-width', $('.media-item').width());
					$('.media-item').attr('data-page-height', $('.media-item').height());
					$('.media-item').css('max-width', '9999px');
					$('.media-item').width($('.media-item').attr('data-media-width')).height($('.media-item').attr('data-media-height'));
					$('.question').click(function() {
						$('.question').removeClass('modal');
						$('.media-item').css('max-width', '850px');
						$('.media-item').width($('.media-item').attr('data-page-width'));
						$('.media-item').height($('.media-item').attr('data-page-height'));
					});
					/*
					$('.media-item').css('position', 'fixed');
					$('.media-item').css('left', 0);
					$('.media-item').css('top', 0);
					$('.media-item').css('z-index', '9999');
					//$.modal($('.media-item'));*/
				});
				
				
				break;/*
			case 'cap':
				$target.append('<div id="cap-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="cap-placeholder" style="height:' + mediaObject.height + 'px;width:' + mediaObject.width + 'px;">SWF ' + mediaObject.title + '</div>');
				$target.children('#cap-' + mediaCount).load('/assets/templates/viewer.html #swf-alt-text', capize);
				//mediaObject.source = './captivateSpy.swf?commChannel=' + 'bridgeData.channel' + '&captivateURL=' + escape('../getAsset.php?id=' + _mediaObject.id);
				break;*/
			case 'kogneato':
				//@TODO: Dimensions???
				$target.append('<iframe src="https://kogneato.ucf.edu/embed/' + mediaObject.url + '" width="800" height="622" style="margin:0;padding:0;border:0;"></iframe>');
				break;
			case 'youtube':
				var $youtube = $('<div id="youtube-' + mediaCount + '" data-youtube-id="' + mediaObject.url + '" data-media-id="' + mediaObject.mediaID + '" class="youtube-placeholder"></div>');
				$youtube.width(640).height(390);
				$target.append($youtube);

				/*
				// we need to load the API if it hasn't been loaded
				if(!youTubeAPIReady)
				{
					// loading this script will call onYouTubePlayerAPIReady in the global namespace
					var script = document.createElement('script');
					script.src = 'http://www.youtube.com/player_api';
					var firstScriptTag = document.getElementsByTagName('script')[0];
					firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
				}
				else
				{
					// @TODO  - this is hack town
					setTimeout(function() {
						console.log('now lets youtubeize');
						youtubeize();
					}, 1);

				}*/
				/*
				obo.loader.loadScript('http://www.youtube.com/player_api', function() {
					setTimeout(function() {
						console.log('now lets youtubeize');
						youtubeize();
					}, 2000);
				});*/

				if(!youTubeAPIReady)
				{
					obo.loader.loadScript('http://www.youtube.com/player_api');
				}
				else
				{
					setTimeout(function() {
						youtubeize();
					}, 1);
				}
				break;
			case 'flv':
				var style = '';
				if(mediaObject.width > 0 && mediaObject.height > 0)
				{
					 style = 'style="height:' + mediaObject.height + 'px;width:' + mediaObject.width + 'px;"';
				}
				$target.append('<div id="flv-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="flv-placeholder" ' + style + '>FLV '+mediaObject.title+'</div>');
				$target.children('#flv-' + mediaCount).load('/assets/templates/viewer.html #flv-alt-text', jwplayerize);
				break;
			default:
				return false;
		}
		
		mediaCount++;
		return true;
	};
	
	// (you don't need to call this directly)
	/*
	var onYouTubeStateChange = function(event)
	{
		// update the duration in the DB
		console.log('onYouTubeStateChange');
		var mediaID = $(event.target.a.parentElement).attr('data-media-id');
		//console.log('youtubeID', youtubeID, players);
		var duration;
		if(ytPlayers[mediaID] && ytPlayers[mediaID].getDuration)
		{
			console.log(ytPlayers[mediaID]);
			duration = ytPlayers[mediaID].getDuration();
			if(duration > 0)
			{
				console.log(ytPlayers[mediaID].getDuration());
				reportDurationForYoutubeVideo(mediaID, duration);
			}
		}
	};*/
	
	var setYouTubeAPIReady = function(val)
	{
		youTubeAPIReady = val;
	};
	
	return {
		setYouTubeAPIReady: setYouTubeAPIReady,
		createMedia: createMedia,
		youtubeize: youtubeize,
	};
}();

//@TODO does this need to be in the global namespace?
// required callback by the youtube iframe API.
function onYouTubePlayerAPIReady(event)
{
	obo.media.setYouTubeAPIReady(true);
	obo.media.youtubeize();
}