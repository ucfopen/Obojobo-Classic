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
			return params;
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
			};
		}
	};

	var materiaize = function()
	{
		debug.log('materiaize');
		$('.materia-placeholder').each(function(index, placeholder)
		{
			var $placeholder = $(placeholder);
			$placeholder.removeClass('materia-placeholder').addClass('materia-container');
			var giid = $placeholder.attr('data-giid');
//public function getLTIParams($mode, $itemID=null, $loID=null, $pageOrQuestionID=null, $pageItemIndex=null, $visitKey=null)
			//preview, content, question
			//content=not interactive question
			//preview=interactive questions not in inst mode

			var p = obo.model.getPageObject();
			var itemIndex = $placeholder.parents('figure').attr('data-item-index');

			var params = ['mode', giid, obo.model.getLO().loID, obo.model.getPageID(), itemIndex];
			if(obo.model.getMode() === 'preview')
			{
				params[0] = 'preview';
			}
			else
			{
				params.push(obo.model.getLO().viewID);
				
				if(typeof p.itemType !== 'undefined' && p.itemType.toLowerCase() === 'media')
				{
					params[0] = 'question';
				}
				else
				{
					params[0] = 'content';
				}
			}
			obo.remote.makeCall('getLTIParams', params, function(result) {
				//result could be {errorID: X, message: Y}
				if(result.params !== 'undefined')
				{
					var $form = $('<form action="' + _materiaLtiUrl + '" method="POST" target="' + $placeholder.attr('id') + '"></form>');
					
					var $input;
					for(var param in result.params)
					{
						$input = $('<input name="' + param + '" value="' + result.params[param] + '" type="hidden">');
						$form.append($input);
					}
					
					$('body').append($form);
					$form.submit();
					$form.remove();
				}
			});
		});

		positionOverlayMedia();
	};
	
	var swfize = function()
	{
		if(AUTOLOAD_FLASH)
		{
			$('.swf-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('swf-placeholder').addClass('swf-container');
				var mediaID = $placeholder.attr('data-media-id');
				var url = '/media/';
				var asVersion = $placeholder.attr('data-as-version');
				if(asVersion && asVersion.length > 0)
				{
					switch(asVersion)
					{
						case '2':
							url = '/assets/flash/captivateSpyCP2.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							break;
						case '3':
							url = '/assets/flash/captivateSpyCP5.swf?id=' + placeholder.id + '&callback=obo.captivate.onCaptivateSpyEvent&captivateURL=/media/';
							break;
					}
				}
				var w = $placeholder.width();
				var h = $placeholder.height();
				swfobject.embedSWF(url + mediaID, placeholder.id, w > 0 ? w : '100%', h > 0 ? h : '100%', "10",  "/assets/flash/expressInstall.swf", {}, getParams());
			});
		}
	};
	
	// converts any youtube placeholder elements with an instance of the youtube player.
	var youtubeize = function()
	{
		if(AUTOLOAD_FLASH)
		{
			$('.youtube-placeholder').each(function(index, placeholder)
			{
				var $placeholder = $(placeholder);
				$placeholder.removeClass('youtube-placeholder').addClass('youtube-container');
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
	var createMedia = function(pageObject, $target)
	{
		var mediaObject = pageObject.media[0];
		var pageItemOptions = pageObject.options;

		if(typeof mediaObject !== 'undefined')
		{
			var section = obo.model.getSection();
			var page = obo.model.getPage();
		
			var $mediaElement = $('<figure class="media-item"></figure>');
		
			debug.log('create media', mediaObject);
		
			// some useful attributes - we also include page and section for flash overlay hack purposes
			$mediaElement
				.attr('data-media-width', mediaObject.width)
				.attr('data-media-height', mediaObject.height)
				.attr('data-media-id', mediaObject.mediaID)
				.addClass('media-for-page-' + section + page)
				.attr('data-section', section)
				.attr('data-page', page)
				.attr('data-item-index', obo.model.getIndexOfItem(pageObject));
		
			switch(mediaObject.itemType.toLowerCase())
			{
				case 'embed':
					var $embed = $(mediaObject.descText);
					$mediaElement.append($embed);
					$target.append($mediaElement);
					break;
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
					
						var o = $('.media-item-standin').offset();
						$('.media-item').offset({left: o.left});
						$('#swf-holder').offset({top: o.top});
					}
					else
					{
						$mediaElement.width(mediaObject.width).height(mediaObject.height);
					}

					$('.reload-media-button').remove();
					if(isCaptivate)
					{
						var $reloadMediaButton = $('<a class="reload-media-button" role="button" href="#">Reload media</a>');
						$reloadMediaButton.click(function(event) {
							event.preventDefault();

							var $figure = $('.media-for-page-' + obo.model.getSection() + obo.model.getPage());
							if($figure.length > 0)
							{
								var $object = $figure.find('object');
								if($object.length > 0)
								{
									obo.captivate.clearCaptivateDataForID($object.attr('id'));
									reloadSWF($object);
								}
							}
						});
						$mediaElement.append($reloadMediaButton);
					}
					
				
					break;
				case 'kogneato':
					// some sanity values in case we're getting bad sizes back.
					if(mediaObject.width < 100 || mediaObject.height < 100)
					{
						mediaObject.width = 800;
						mediaObject.height = 600;
					}

					// standin represents our media stand-in - where the swf would be placed normally.
					// we define standin if needed to act as a positioning guide for captivates.
					var $standin = null;

					var isInteractive = (section === 'practice' || section === 'assessment') && obo.model.getPageObject().itemType.toLowerCase() === 'media';
					if(isInteractive)
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
							$mediaElement.find('iframe').show();
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

					// frameborder=0 will remove iframe borders for IE8. it's not valid html though, so we only use it if we have to.
					var $materia = $('<iframe ' + (obo.util.isIE8() ? 'frameborder="0"' : '') + 'id="materia-' + mediaCount + '" name="materia-' + mediaCount + '" data-media-id="' + mediaObject.mediaID + '" data-giid="' + mediaObject.url + '" class="materia-placeholder"></iframe>');
					if($mediaElement.find('iframe').length === 0)
					{
						$mediaElement.append($materia);
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
							mediaObject.width = YOUTUBE_WIDTH_FOR_CONTENT_PAGE;
							mediaObject.height = YOUTUBE_HEIGHT_FOR_CONTENT_PAGE;
						}
						else
						{
							mediaObject.width = YOUTUBE_WIDTH_FOR_QUIZ;
							mediaObject.height = YOUTUBE_HEIGHT_FOR_QUIZ;
						}
					}
					$target.append($mediaElement);
					$mediaElement.append($youtube);
				
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

				if(typeof $materia !== 'undefined')
				{
					$materia.width(mediaObject.width).height(mediaObject.height);
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
			if(typeof $materia !== 'undefined')
			{
				$materia.load('/assets/templates/viewer.html #swf-alt-text', materiaize);
				$materia = undefined;
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
	};
	
	return {
		createMedia: createMedia,
		positionOverlayMedia: positionOverlayMedia
	};
}();