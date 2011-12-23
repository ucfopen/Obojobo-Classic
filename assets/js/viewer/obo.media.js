// utility class for handling media

if(!window.obo)
{
	window.obo = {};
}

obo.media = function()
{
	// @const
	var AUTOLOAD_FLASH = true;
	
	// default sizes for youtube content:
	var YOUTUBE_WIDTH_FOR_CONTENT_PAGE = 600;
	var YOUTUBE_HEIGHT_FOR_CONTENT_PAGE = 365;
	var YOUTUBE_WIDTH_FOR_QUIZ = 500;
	var YOUTUBE_HEIGHT_FOR_QUIZ = 304;
	
	// @private
	
	//var youTubeAPIReady = false;
	
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
				wmode: 'direct'/*,
				// we want high performance gpu, but in preview mode the popups for the subnavs (question alts)
				// can be blocked by the swf (gpu/direct swfs are the front most layer)
				wmode: obo.model.getMode() === 'preview' ? 'opaque' : 'direct'*/
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
		obo.remote.makeCall('editMedia', [mediaObject, obo.model.getMode() === 'instance' ? obo.model.getLO().viewID : -1]);
	};*/
	
	// @TODO need to deal with the player variable
	/*
	var buildYouTubeVideo = function($placeholder)
	{
		var youtubeID = $placeholder.attr('data-youtube-id');
		debug.log('buildYouTubeVideo=', youtubeID);
		var player = new YT.Player($placeholder.attr('id'), {
			width: $placeholder.width(),
			height: $placeholder.height(),
			videoId: youtubeID
			
			//events: { // @TODO: make onStateChange work for multiple videos (send callback?)
			//	'onStateChange': onYouTubeStateChange
			//}
		});
		//ytPlayers[$placeholder.attr('data-media-id')] = player;
		
		// remove it's placeholder status:
		$placeholder.removeClass('youtube-placeholder').addClass('youtube-container');
		
		return player;
	};
	*/
	var kogneatoize = function()
	{
		debug.log('kogneatoize');
		if(AUTOLOAD_FLASH)
		{
			$('.kogneato-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('kogneato-placeholder').addClass('kogneato-container');
				var giid = $placeholder.attr('data-giid');
				obo.remote.makeCall('doPluginCall', ['Kogneato', 'getKogneatoEngineLink',  [giid]], function(event) {
					debug.log('kogneatoize 2');
					debug.log(event);
					$('.kogneato-container').each(function(index, container)
					{
						var $container = $(container);
						var w = $container.width();
						var h = $container.height();
						debug.log(w, h);
						var url = '/assets/flash/kogneato-widget.swf?id=' + container.id + '&callback=obo.kogneato.onKogneatoEvent&kogneatoURL=' + event.url;
						swfobject.embedSWF(url, container.id, w > 0 ? w : '100%', h > 0 ? h : '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
					});
				});
			});
		}
	};
	
	var swfize = function()
	{
		//return;
		if(AUTOLOAD_FLASH)
		{
			$('.swf-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('swf-placeholder').addClass('swf-container');
				var mediaID = $placeholder.attr('data-media-id'); //placeholder.id.split('media-')[1];	
				//$placeholder.parent('.media-item').css('height', $placeholder.css('height')).css('width', $placeholder.css('width'));
				var url = '/media/';
				var asVersion = $placeholder.attr('data-as-version');
				if(asVersion && asVersion.length > 0)
				{
					switch(asVersion)
					{
						case '2':
							url = '/assets/flash/captivateSpyCP2.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							// @TODO hack captivate switch
							//$('#swf-holder').append('<a id="swap-cap" onclick="switchCaptivateSpy(event)" data-cur-version="2" data-element-id="' + placeholder.id + '" data-width="' + $placeholder.width() + '" data-height="' + $placeholder.height() + '" data-media-id="' + mediaID + '" style="position:relative; top:-25px;" href="#">Loaded as AS2 - Click to reload as AS3</a>');
							break;
						case '3':
							url = '/assets/flash/captivateSpyCP5.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							// @TODO
							//$('#swf-holder').append('<a id="swap-cap" onclick="switchCaptivateSpy(event)" data-cur-version="3" data-element-id="' + placeholder.id + '" data-width="' + $placeholder.width() + '" data-height="' + $placeholder.height() + '" data-media-id="' + mediaID + '" style="position:relative; top:-25px;" href="#">Loaded as AS3 - Click to reload as AS2</a>');
							break;
					}
				}
				//alert('placeholder:'+ $placeholder.width()+','+ $placeholder.height());
				var w = $placeholder.width();
				var h = $placeholder.height();
				//swfobject.embedSWF(url + mediaID, placeholder.id, '100%', '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
				swfobject.embedSWF(url + mediaID, placeholder.id, w > 0 ? w : '100%', h > 0 ? h : '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
			});
		}
		
		//obo.view.updateInteractiveScore(0);
	};
	/*
	var capize = function()
	{
		////mediaObject.source = './captivateSpy.swf?commChannel=' + 'bridgeData.channel' + '&captivateURL=' + escape('../getAsset.php?id=' + _mediaObject.id);
		debug.log('capize');
		if(AUTOLOAD_FLASH)
		{
			$('.cap-placeholder').each(function(index, placeholder)
			{
				debug.log(placeholder);
				debug.log(placeholder.id);
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
			$('.youtube-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('youtube-placeholder').addClass('youtube-container');
				//var mediaID = placeholder.id.split('media-')[1];
				//var mediaID = $placeholder.attr('data-media-id');
				var youtubeURL = $placeholder.attr('data-youtube-id');
				var params = getParams();
				params.allowScriptAccess = "always";
				swfobject.embedSWF('http://www.youtube.com/v/' + youtubeURL + '', placeholder.id, $placeholder.width() + 'px', $placeholder.height() + 'px', "10",  "/assets/flash/expressInstall.swf", null, params);
			});
		}
	};
	
	var youtubeizeIFrame = function()
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
	var getMediaItemByMediaID = function(mediaID)
	{
		var result = $('.media-item[data-media-id="' + mediaID + '"]');
		if(result.length > 0)
		{
			return $(result[0]);
		}
		else
		{
			return undefined;
		}
	};

	var reloadSWF = function($objectElement)
	{
		/*
		(function($objectElement) {
			$objectElement.hide();
			setTimeout(function() {
				$objectElement.show();
			}, 1);
		})($objectElement);*/
		var url = obo.util.getURLFromEmbeddedSwf($objectElement);
		if(typeof url !== 'undefined')
		{
			var width = $objectElement.width();
			var height = $objectElement.height();
			var id = $objectElement.attr('id');
			var $parent = $($objectElement).parent();
			$objectElement.remove();
			var $new = $('<div></div>');
			$new.attr('id', id);
			$parent.prepend($new);

			//@TODO - This simply requires flash 10, but we know what it should require
			swfobject.embedSWF(url, id, width, height, "10",  "/assets/flash/expressInstall.swf", {}, getParams(), {});
		}
	};
	
	// creates either a YouTube or non-YouTube video, appends to $target
	// pageItemOptions is for custom layout page items only
	var createMedia = function(mediaObject, $target, pageItemOptions)
	{
		if(mediaObject)
		{
			var section = obo.model.getSection();
			var page = obo.model.getPage();
		
			var $mediaElement = $('<figure class="media-item"></figure>');
			//obo.util.doLater(function() { alert($mediaElement.css('list-style-type')); });
		
			debug.log('create media', mediaObject.width, mediaObject.height);
			//debug.log('createMedia', mediaObject, $target);
			//mediaObject.itemType = 'cap5';
		
			// some useful attributes - we also include page and section for flash overlay hack purposes
			$mediaElement.attr('data-media-width', mediaObject.width).attr('data-media-height', mediaObject.height).attr('data-media-id', mediaObject.mediaID);
			$mediaElement.addClass('media-for-page-' + section + page);
		
			switch(mediaObject.itemType.toLowerCase())
			{
				case 'pic':
					$target.append($mediaElement);
					$img = $('<img id="pic-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="pic" src="/media/' + mediaObject.mediaID + '" title="' + mediaObject.title + '" alt="' + mediaObject.title + '">');
					$img.one('load', function() {
						$(this).css('background', 'white');
					}).each(function() {
						if(this.complete)
						{
							$(this).trigger('load');
						}
					});
					$mediaElement.append($img);
					//$mediaElement.width(mediaObject.width).height(mediaObject.height);
					break;
				case 'cap5': // @TODO - is 'cap5' used?
				case 'swf':
					// standin represents our media stand-in - where the swf would be placed normally.
					// we define standin if needed to act as a positioning guide for captivates.
					var $standin = null;
				
					// we assume this is a captivate if it is a swf in an interactive question
					var isCaptivate = (section === 'practice' || section === 'assessment') && obo.model.getPageObject().itemType.toLowerCase() === 'media';
				
					// we need to do our swf hack for practice and assessment interactive-only questions
				
					if(isCaptivate)
					{
						//$('#swap-cap').show();
					
						createSwfHolder(section);
					
						// define standin, since we need to overlay captivates
						$standin = $('<div class="media-item-standin"></div>');
					
						// if this captivate already is being overlayed then don't overlay it again!
						if($('.media-for-page-' + section + page).length > 0)
						{
							$mediaElement = $($('.media-for-page-' + section + page)[0]);
							// @HACK we turn both parent and object visible for Safari
							$mediaElement.css('visibility', 'visible');
							$mediaElement.find('object').css('visibility', 'visible');
						}
						else
						{
							$('#swf-holder-' + section).append($mediaElement);
						}
					
						$target.append($standin);
					
					}
					else
					{
						$target.append($mediaElement);
					}
					/*
					var maxWidth = parseInt($mediaElement.css('max-width').replace('px', ''));
					if(isNaN(maxWidth))
					{
						maxWidth = 9999999;
					}
					alert('now=',maxWidth);
					var mediaWidth = Math.min(maxWidth, mediaObject.width);
					var scaleFactor = mediaWidth / mediaObject.width;
					var mediaHeight = Math.ceil(mediaObject.height * scaleFactor);
					debug.log('mediaObject', mediaObject, 'maxWidth', maxWidth, 'mediaWidth', mediaWidth, 'mediaHeight', mediaHeight);
					*/
					//$swf = $('<div id="swf-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="swf-placeholder" style="height:' + mediaHeight + 'px;width:' + mediaWidth + 'px;"></div>');
					$swf = $('<div id="swf-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="swf-placeholder"></div>');
					if(isCaptivate)
					{
						// there are two captivate connection methods - version 2-4 or version 5.
						//$swf.attr('data-captivate-version', mediaObject.itemType.toLowerCase() === 'cap5' ? '5' : '2');
						// assume AS3 if no version found
						$swf.attr('data-as-version', (typeof mediaObject.meta !== 'undefined' && mediaObject.meta !== null && typeof mediaObject.meta.asVersion !== 'undefined' ? mediaObject.meta.asVersion : '3'));
					}
				
					// append swf unless it's already there, which is the case if we're using hack overlays
					// @TODO - can't rely on <object> since IE might use something else
					if($mediaElement.find('object').length === 0)
					{
						$mediaElement.append($swf);
					}
				
					if($standin != null)
					{
						$standin.width(mediaObject.width).height(mediaObject.height);
					
						//setTimeout(function() {
							var o = $('.media-item-standin').offset();
							//alert('O='+ o.left + ',' + o.top);
							$('.media-item').offset({left: o.left});
							$('#swf-holder').offset({top: o.top});
						//}, 1);
					}
					else
					{
						$mediaElement.width(mediaObject.width).height(mediaObject.height);
					}
				
				
					/*
					//$('.media-item').click(function(event) {
					setTimeout(function () {
						debug.log('click');
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
				
					// popout:
				
					// @TODO: Testing
					/*
					$('#preview-mode-notification').click(function() {
						var objects = document.getElementsByTagName('object');
						alert(objects);
						document.body.appendChild(objects[0]);
						/*
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
						});*/
						/*
						$('.media-item').css('position', 'fixed');
						$('.media-item').css('left', 0);
						$('.media-item').css('top', 0);
						$('.media-item').css('z-index', '9999');
						//$.modal($('.media-item'));*/
					//});

					$('.reload-media-button').remove();
					if(isCaptivate)
					{
						var $reloadMediaButton = $('<a class="reload-media-button" role="button" data-media-id="' + mediaObject.mediaID + '" href="#">Reload media</a>');
						$reloadMediaButton.click(function(event) {
							event.preventDefault();
							var $link = $(event.target);
							var mediaID = $link.attr('data-media-id');
							var $e = getMediaItemByMediaID(mediaID);
							if(typeof $e !== 'undefined')
							{
								reloadSWF($e.find('object'));
							}
						});
						$mediaElement.append($reloadMediaButton);
					}
					
				
					break;/*
				case 'cap':
					$target.append('<div id="cap-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="cap-placeholder" style="height:' + mediaObject.height + 'px;width:' + mediaObject.width + 'px;">SWF ' + mediaObject.title + '</div>');
					$target.children('#cap-' + mediaCount).load('/assets/templates/viewer.html #swf-alt-text', capize);
					//mediaObject.source = './captivateSpy.swf?commChannel=' + 'bridgeData.channel' + '&captivateURL=' + escape('../getAsset.php?id=' + _mediaObject.id);
					break;*/
				case 'kogneato':
					// some sanity values in case we're getting bad sizes back.
					if(mediaObject.width < 100 || mediaObject.height < 100)
					{
						mediaObject.width = 800;
						mediaObject.height = 600;
					}
					/*
					mediaObject.width = 640;
					mediaObject.height = 480;
					mediaObject.width = 1000;
					mediaObject.height = 1000;
*/
					// standin represents our media stand-in - where the swf would be placed normally.
					// we define standin if needed to act as a positioning guide for captivates.
					var $standin = null;
				
					// we need to do our swf hack for practice and assessment interactive-only questions
					
					var inQuiz = section === 'practice' || section === 'assessment';
					if(inQuiz)
					{
						//$('#swap-cap').show();
					
						createSwfHolder(section);
					
						// define standin, since we need to overlay captivates
						$standin = $('<div class="media-item-standin"></div>');
					
					// if this kogneato already is being overlayed then don't overlay it again!
						if($('.media-for-page-' + section + page).length > 0)
						{
							$mediaElement = $($('.media-for-page-' + section + page)[0]);
							// @HACK we turn both parent and object visible for Safari
							$mediaElement.css('visibility', 'visible');
							$mediaElement.find('object').css('visibility', 'visible');
						}
						else
						{
							$('#swf-holder-' + section).append($mediaElement);
						}
					
						$target.append($standin);
					
					}
					else
					{
						$target.append($mediaElement);
					}
					
					$kogneato = $('<div id="kogneato-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" data-giid="' + mediaObject.url + '" class="kogneato-placeholder"></div>');
					
					// append swf unless it's already there, which is the case if we're using hack overlays
					// @TODO - can't rely on <object> since IE might use something else
					if($mediaElement.find('object').length === 0)
					{
						$mediaElement.append($kogneato);
					}
				
					if($standin != null)
					{
						$standin.width(mediaObject.width).height(mediaObject.height);
						
						positionOverlayMedia();
					}
					else
					{
						$mediaElement.width(mediaObject.width).height(mediaObject.height);
					}
					break;
				case 'youtube':
					var $youtube = $('<div id="youtube-' + mediaCount + '" data-youtube-id="' + mediaObject.url + '" data-media-id="' + mediaObject.mediaID + '" class="youtube-placeholder"></div>');
				
					// @TODO - should all of these media types check for pageItemOptions?
					// if pageItemOptions has width and height defined then this is a custom layout item,
					// so define the dimensions based off of the pageItem
					if(pageItemOptions != null && pageItemOptions.width > 0 && pageItemOptions.height > 0)
					{
						$youtube.width(pageItemOptions.width - pageItemOptions.padding * 2).height(pageItemOptions.height - pageItemOptions.padding * 2);
					}
					else
					{
						// otherwise use default youtube dimensions
						if(obo.model.getSection() === 'content')
						{
							//$youtube.width(YOUTUBE_WIDTH_FOR_CONTENT_PAGE).height(YOUTUBE_HEIGHT_FOR_CONTENT_PAGE);
							//$mediaElement.width(YOUTUBE_WIDTH_FOR_CONTENT_PAGE).height(YOUTUBE_HEIGHT_FOR_CONTENT_PAGE);
							mediaObject.width = YOUTUBE_WIDTH_FOR_CONTENT_PAGE;
							mediaObject.height = YOUTUBE_HEIGHT_FOR_CONTENT_PAGE;
						}
						else
						{
							//$youtube.width(YOUTUBE_WIDTH_FOR_QUIZ).height(YOUTUBE_HEIGHT_FOR_QUIZ);
							//$mediaElement.width(YOUTUBE_WIDTH_FOR_QUIZ).height(YOUTUBE_HEIGHT_FOR_QUIZ);
							mediaObject.width = YOUTUBE_WIDTH_FOR_QUIZ;
							mediaObject.height = YOUTUBE_HEIGHT_FOR_QUIZ;
						}
					}
					$target.append($mediaElement);
					$mediaElement.append($youtube);
				
					//@TODO - This is iFrame embed code which doesn't play nice with IE8
					/*
					if(!youTubeAPIReady)
					{
						obo.loader.loadScript('http://www.youtube.com/player_api');
					}
					else
					{
						setTimeout(function() {
							youtubeize();
						}, 1);
					}*/
					//youtubeize();
				
					break;
				case 'flv':
					var style = '';
					if(mediaObject.width > 0 && mediaObject.height > 0)
					{
						 style = 'style="height:' + mediaObject.height + 'px;width:' + mediaObject.width + 'px;"';
					}
					$target.append($mediaElement);
					$mediaElement.append('<div id="flv-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" class="flv-placeholder" ' + style + '></div>');
					$mediaElement.children('#flv-' + mediaCount).load('/assets/templates/viewer.html #flv-alt-text', jwplayerize);
					break;
				default:
					return false;
			}
		
			// @TODO
			//alert(parseInt($mediaElement.css('max-width').replace('px', '')));
			//alert($mediaElement.css('max-height'));
		
			// calculate dimensions, based on the media object and max-widths/max-heights.
			// this results in us being able to create the media-item container at the
			// correct dimensions so we don't have flashy expanding divs.
			// we don't need to do this if we use inline-block or table displays for media-items,
			// but that causes other problems!
			if(pageItemOptions === null || pageItemOptions === false)
			{
				// @TODO - Get this data from the stylesheet!
				var layoutID = obo.model.getPageObject().layoutID;
				var targetWidth = $target.width();
			
				var maxWidth = $mediaElement.css('max-width');
				var maxHeight = $mediaElement.css('max-height');
			
				if(maxWidth.indexOf('px') != -1)
				{
					maxWidth = parseInt(maxWidth.substr(0, maxWidth.length - 2));
				}
				else if(maxWidth.indexOf('%') != -1)
				{
					maxWidth = maxWidth.substr(0, maxWidth.length - 1);
					maxWidth = targetWidth * parseInt(maxWidth) / 100;
				}
				if(maxHeight.indexOf('px') != -1)
				{
					maxHeight = parseInt(maxHeight.substr(0, maxHeight.length - 2));
				}
				else if(maxHeight.indexOf('%') != -1)
				{
					maxHeight = maxHeight.substr(0, maxHeight.length - 1);
					maxHeight = targetHeight * parseInt(maxHeight) / 100;
				}
			
				if(maxWidth <= 0)
				{
					maxWidth = 999999999;
				}
				if(maxHeight <= 0)
				{
					maxHeight = 999999999;
				}
			
				if(mediaObject.width > maxWidth)
				{
					mediaObject.height = Math.floor(mediaObject.height * maxWidth / mediaObject.width);
					mediaObject.width = maxWidth;
				}
			
				if(mediaObject.height > maxHeight)
				{
					mediaObject.width = Math.floor(mediaObject.width * maxHeight / mediaObject.height);
					mediaObject.height = maxHeight;
				}
			
				$mediaElement.width(mediaObject.width).height(mediaObject.height);
			
				if(typeof $youtube !== 'undefined')//if(mediaObject.itemType.toLowerCase() === 'youtube')
				{
					$youtube.width(mediaObject.width).height(mediaObject.height);
				}
			
				if(typeof $swf !== 'undefined')
				{
					$swf.width(mediaObject.width).height(mediaObject.height);
				}
			
				if(typeof $img !== 'undefined')
				{
					$img.width(mediaObject.width).height(mediaObject.height);
					$img = undefined;
				}

				// attribution
				if(typeof mediaObject.attribution !== 'undefined' && mediaObject.attribution !== null && (mediaObject.attribution === true || mediaObject.attribution.toString() === '1'))
				{
					$caption = $('<figcaption>' + obo.util.cleanAttributionCopyrightHTML(mediaObject.copyright) + '</figcaption>');
					$mediaElement.append($caption);
					$mediaElement.height($mediaElement.height() + $caption.height());
				}
			}
		
			if(typeof $youtube !== 'undefined')//if(mediaObject.itemType.toLowerCase() === 'youtube')
			{
				$youtube.load('/assets/templates/viewer.html #swf-alt-text', youtubeize);
				$youtube = undefined;
			}
			if(typeof $swf !== 'undefined')
			{
				$swf.load('/assets/templates/viewer.html #swf-alt-text', swfize);
				$swf = undefined;
			}
			if(typeof $kogneato !== 'undefined')
			{
				$kogneato.load('/assets/templates/viewer.html #swf-alt-text', kogneatoize);
				$kogneato = undefined;
			}

			debug.log('dimensions', $mediaElement.width(), $mediaElement.height());

			mediaCount++;
			return $mediaElement;
		}
	};

	var positionOverlayMedia = function()
	{
		var $standin = $('.media-item-standin');
		if($standin.length > 0)
		{
			var o = $('.media-item-standin').offset();
			$('.media-item').offset({left: o.left});
			$('#swf-holder').offset({top: o.top});
		}
	};
	
	// (you don't need to call this directly)
	/*
	var onYouTubeStateChange = function(event)
	{
		// update the duration in the DB
		debug.log('onYouTubeStateChange');
		var mediaID = $(event.target.a.parentElement).attr('data-media-id');
		//debug.log('youtubeID', youtubeID, players);
		var duration;
		if(ytPlayers[mediaID] && ytPlayers[mediaID].getDuration)
		{
			debug.log(ytPlayers[mediaID]);
			duration = ytPlayers[mediaID].getDuration();
			if(duration > 0)
			{
				debug.log(ytPlayers[mediaID].getDuration());
				reportDurationForYoutubeVideo(mediaID, duration);
			}
		}
	};*/
	/*
	var setYouTubeAPIReady = function(val)
	{
		youTubeAPIReady = val;
	};*/

	// add our swf holder mechanism if it doesn't exist already:
	var createSwfHolder = function(section)
	{
		if($('#swf-holder-' + section).length === 0)
		{
			if($('#swf-holder').length === 0)
			{
				$('body').append($('<div id="swf-holder"></div>'));
			}
		
			$('#swf-holder').append($('<div id="swf-holder-' + section + '"></div>'));
		}
	}
	
	return {
		//setYouTubeAPIReady: setYouTubeAPIReady,
		createMedia: createMedia,
		positionOverlayMedia: positionOverlayMedia
		//youtubeize: youtubeize
	};
}();
/*
// @TODO does this need to be in the global namespace?
// required callback by the youtube iframe API.
function onYouTubePlayerAPIReady(event)
{
	obo.media.setYouTubeAPIReady(true);
	obo.media.youtubeize();
}*/