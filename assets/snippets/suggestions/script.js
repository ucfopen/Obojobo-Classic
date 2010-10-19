$(document).ready(function()
{
	
	var ul = $('ul.suggestions');
	
	// Listening of a click on a UP or DOWN arrow:
	
	$('div.vote span').live('click',function()
	{
		
		var elem		= $(this),
			parent		= elem.parent(),
			li			= elem.closest('li'),
			ratingDiv	= li.find('.rating'),
			id			= li.attr('id').replace('s',''),
			v			= 1;

		// If the user's already voted:
		
		if(parent.hasClass('inactive'))
		{
			return false;
		}
		
		parent.removeClass('active').addClass('inactive');
		
		if(elem.hasClass('down'))
		{
			v = -1;
		}
		
		// Incrementing the counter on the right:
		ratingDiv.text(v + +ratingDiv.text());
		
		// Turning all the LI elements into an array
		// and sorting it on the number of votes:
		
		var arr = $.makeArray(ul.find('li')).sort(function(l,r)
		{
			
			if($('.rating',r).text() == $('.rating',l).text())
			{
				return +$('.id',r).text() - +$('.id',l).text(); // if ratings are the same, sort based on the id
			}
			else
			{
				return +$('.rating',r).text() - +$('.rating',l).text(); // sort based on rating
			}
			
		});

		// Adding the sorted LIs to the UL
		ul.html(arr);
		
		// Sending an AJAX request
		$.get(jsonGateway,{action:'vote',vote:v,'id':id});
	});


	$('#suggest').submit(function()
	{
		
		var form		= $(this),
			textField	= $('#suggestionText');
		
		// Preventing double submits:
		if(form.hasClass('working') || textField.val().length<3)
		{
			return false;
		}
		
		form.addClass('working');
		
		$.getJSON(jsonGateway,{action:'submit',content:textField.val()},function(msg)
		{
			textField.val('');
			form.removeClass('working');
			
			if(msg.html)
			{
				// Appending the markup of the newly created LI to the page:
				$(msg.html).hide().appendTo(ul).slideDown();
			}
		});
		
		return false;
	});
});