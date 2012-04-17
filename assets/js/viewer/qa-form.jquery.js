// this will transform a input text field into what we need for the viewer
// settings:

(function($) {
	var TYPING_INTERVAL_SECONDS = 1;
	var UNSAVED_BUTTON_LABEL = 'Save Answer';
	var SAVED_BUTTON_LABEL = 'Answer Saved';

	var typingIntervalID;
	var elements;
	var lastSaved;

	var unsavedChanges = true;

	// if false then answers will not be shown. otherwise this should
	// be an array of strings representing the correct answers.
	var answers;

	var methods = {
		_init: function(settings) {
			answers = false;
			typingIntervalID = undefined;
			elements = {};
			lastSaved = undefined;

			if(typeof settings.answers !== 'undefined')
			{
				answers = settings.answers;
			}

			// build our UI:
			elements.$container = this;
			elements.$score = $('<span style="display:none;" class="answer-preview"></span>');
			elements.$input = $('<input autocapitalize="false" id="qa-input" placeholder="Enter answer here">');
			elements.$saved = $('<div class="saved-notice">&nbsp;</div>');
			elements.$button = $('<a href="#" class="button disabled" id="submit-qa-answer-button" role="button">' + UNSAVED_BUTTON_LABEL + '</a>');
			elements.$container
				.append(elements.$score)
				.append(elements.$input)
				.append(elements.$button)
				.append(elements.$saved);
			elements.$feedback = $('<span style="display:none;" class="answer-feedback"><h4>Review</h4></span>');
			elements.$feedbackTextContainer = $('<span id="feedback-text-container"></span>');
			elements.$feedback.append(elements.$feedbackTextContainer);
			elements.$container.append(elements.$feedback);

			if(answers !== false)
			{
				elements.$answers = $('<span class="answer-feedback answers"></span>');
				var list = '';
				for(i in answers)
				{
					list += '<li>' + answers[i] + '</li>';
				}
				elements.$answers.append('<h4>Answers</h4> <ul>' + list + '</ul>');
				elements.$container.append(elements.$answers);
			}

			// setup our events:
			elements.$input
				.keyup(function(event) {
					clearTimeout(typingIntervalID);

					if(event.keyCode == 13) //enter
					{
						methods._save();
					}
					else
					{
						if(methods.getText() != lastSaved)
						{
							unsavedChanges = true;
							methods.hideFeedback();
						}
						typingIntervalID = setTimeout(methods._update, TYPING_INTERVAL_SECONDS * 1000);
					}

					methods._updateButton();
				})
				.blur(function(event) {
					if(typeof lastSaved === 'undefined' || lastSaved !== methods.getText())
					{
						methods._update();
					}
				});
			if(typeof elements.$button !== 'undefined')
			{
				elements.$button.click(function(event) {
					event.preventDefault();

					if(!$(event.target).hasClass('disabled'))
					{
						methods._save();
					}
				});
			}

			// go ahead and focus in (unless on mobile which is disruptive)
			if(!obo.util.isMobile())
			{
				elements.$input.focus();
			}
		},

		// if savedAnswer is true then we display the 'Answer Saved' notice
		// (not needed in some cases)
		setText: function(s, savedAnswer)
		{
			if(typeof savedAnswer === 'undefined')
			{
				savedAnswer = false;
			}

			var newText = $.trim(s.toLowerCase());
			elements.$input.val(newText);
			if(savedAnswer)
			{
				lastSaved = newText;
				unsavedChanges = false;
				methods._updateButton('disabled');
			}
			else
			{
				methods._updateButton();
			}
			
		},

		getText: function()
		{
			return $.trim(elements.$input.val().toLowerCase());
		},

		showFeedback: function(score, feedbackText)
		{
			methods.hideFeedback();

			if(score === 0)
			{
				elements.$score.addClass('answer-preview-wrong');
			}
			else if(score === 100)
			{
				elements.$score.addClass('answer-preview-correct');
			}

			elements.$score
				.show()
				.html(score + '%');

			// position feedback bubble:
			var o = elements.$input.offset();
			o.left -= elements.$score.outerWidth() + 10;
			o.top += Math.floor((elements.$input.outerHeight() - elements.$score.outerHeight()) / 2);
			elements.$score.offset(o);

			if(typeof feedbackText !== 'undefined')
			{
				elements.$feedback.show();
				elements.$feedbackTextContainer.html(feedbackText);
			}

			methods._updateButton();

			elements.$container.trigger('onShowFeedback');
		},

		hideFeedback: function()
		{
			elements.$score
				.removeClass('answer-preview-wrong')
				.removeClass('answer-preview-correct');
			elements.$score.hide();
			elements.$feedback.hide();
			elements.$feedbackTextContainer.empty();

			methods._updateButton();

			elements.$container.trigger('onHideFeedback');
		},

		_isShowingFeedback: function()
		{
			return elements.$score.css('display') != 'none';
		},

		_save: function(force)
		{
			if(typeof force === 'undefined')
			{
				force = false;
			}

			var s = methods.getText();
			if(s !== '')
			{
				clearTimeout(typingIntervalID);
				typingIntervalID = undefined;

				var saved = false;
				
				if(force || typeof lastSaved === 'undefined' || s !== lastSaved)
				{
					saved = true;
					lastSaved = s;
				}

				unsavedChanges = false;
				elements.$container.trigger('save', [s, saved]);

				methods._updateButton();
			}
		},

		_update: function()
		{
			elements.$container.trigger('update', methods.getText());
		},

		_responseIsEmpty: function()
		{
			return methods.getText().length == 0;
		},

		_revert: function()
		{
			if(typeof lastSaved !== 'undefined')
			{
				methods._update();
				methods.setText(lastSaved);
				methods._update();
			}
		},

		// enable or disable the 'check answer' button
		_updateButton: function(buttonState)
		{
			if(typeof elements.$button !== 'undefined')
			{
				var s = methods.getText();

				var newButtonState;
				if(typeof buttonState !== 'undefined')
				{
					newButtonState = buttonState;
				}
				else
				{
					if(methods._responseIsEmpty() || !unsavedChanges)
					{
						newButtonState = 'disabled';
					}
					else
					{
						newButtonState = 'enabled';
					}
				}

				if(newButtonState == 'disabled')
				{
					elements.$button
						.addClass('disabled')
						.html(s === '' ? UNSAVED_BUTTON_LABEL : SAVED_BUTTON_LABEL);
				}
				else
				{
					elements.$button
						.removeClass('disabled')
						.html(UNSAVED_BUTTON_LABEL);
				}
			}
		}
	};

	$.fn.qaForm = function(method) {
		// method calling logic
		if(methods[method])
		{
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if(typeof method === 'object' || !method)
		{
			return methods._init.apply(this, arguments);
		}
		else
		{
			$.error('Method ' + method + ' does not exist on jQuery.qaInput!');
		}
	};
})(jQuery);