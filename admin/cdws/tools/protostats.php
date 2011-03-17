<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Prototype Stats</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css" type="text/css" media="all" /> 
	<link rel="stylesheet" href="images/style.css" type="text/css" media="all" /> 
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
	<script src="js/jquery.tablesorter.pager.js"></script>
	
	<script type="text/javascript" charset="utf-8">
	$(window).load(function()
	{
	
		$.ajax({
			url: "/remoting/json.php/loRepository.getUser",
			context: document.body,
			dataType: 'json',
			success: function(msg)
				{
					console.log(msg);
					$('span.first').append(msg.first);
					$('span.last').append(msg.last);
					getMyLOs();
				}
		});
		
		function getMyLOs()
		{
			$.ajax({
				url: "/remoting/json.php/loRepository.getLOs",
				context: document.body,
				dataType: 'json',
				success: function(los)
					{
						console.log(los);
						var loBox = $('#mylos');
						var options = loBox.attr('options');
						
						$.each(los, function(text, lo)
						{
							var d = new Date(lo.createTime * 1000);
							options[options.length] = new Option(lo.title + " v." + lo.version + "." + lo.subVersion + ' ' + (d.getMonth()+1) + '/' + d.getDate() + '/' + d.getFullYear(), lo.loID);
						});
					}
			});
		}
		
		function showResults(results)
		{
			$('#results-table').remove();
			$('#results').append('<table id="results-table" class="tablesorter"><thead><tr class="table-header"></tr></thead><tbody></tbody></table>');
			for(index in results[0])
			{
				$('#results-table tr.table-header').append('<th>'+index+'</th>');
			};

			$(results).each(function(index,val){
				$('#results-table tbody').append('<tr><td>'+val.num+'</td><td>'+val.day+'</td><td>'+val.month+'</td><td>'+val.year+'</td></tr>');
			});
			
//			$("#results-table").tablesorter();
			$("#results-table").tablesorter({widthFixed: true, widgets: ['zebra']}).tablesorterPager({container: $("#pager")});
			
		}
		
		$( "#start_date" ).datepicker();
		$( "#start_date" ).datepicker('option', 'dateFormat', 'yy/mm/dd');
		$( "#end_date" ).datepicker();
		$( "#end_date" ).datepicker('option', 'dateFormat', 'yy/mm/dd');
		
		$('#submit').click(function(){
			
			var los = new Array();
			$("#mylos option:selected").each(function(index,val){
				los.push($(this).val());
			});
			los = '['+los.join()+']';
			
			var s = $('#start_date').datepicker('getDate').getTime()/1000;
			var e = $('#end_date').datepicker('getDate').getTime()/1000;
			
			$.ajax({
				url: "/remoting/json.php/loRepository.getLOStats/"+los+'/'+ $('input:radio[name=stat]:checked').val() + '/'+ s +'/' + e,
				context: document.body,
				dataType: 'json',
				success: showResults
			});
			return false;
		});
		
	});
	</script>
	<style type="text/css" media="screen">
	</style>
</head>
<body>
<div id="name">You Are: <span class="first"></span> <span class="last"></span></div>
<h2>Choose Learning Object</h2>
<form action="protostats_submit" method="get" accept-charset="utf-8">

<select name="some_name" id="mylos" multiple onchange="" size="15"></select><br>

<h2>Choose Stat</h2>

<input type="radio" name="stat" value="1" id="instance_count"><label for="instance_count">Instances Created</label><br>
<input type="radio" name="stat" value="2" id="student_count"><label for="student_count">Student Views</label><br>
<input type="radio" name="stat" value="3" id="derivative_count"><label for="derivative_count">Derivatives Created</label><br>
<input type="radio" name="stat" value="4" id="assessment_count"><label for="assessment_count">Assessments Completed</label><br>

<input type="radio" name="stat" value="5" id="who_created_instances"><label for="who_created_instances">Who Created Instances</label><br>
<input type="radio" name="stat" value="6" id="which_courses"><label for="which_courses">Which Courses</label><br>

<input type="radio" name="stat" value="7" id="question_answers"><label for="question_answers">Question Answer Values</label><br>
<input type="radio" name="stat" value="8" id="content_views"><label for="content_views">Content Views</label><br>
<input type="radio" name="stat" value="9" id="scores"><label for="scores">Scores</label><br>
<input type="radio" name="stat" value="10" id="attempt"><label for="attempt">Attempt</label><br>

<h2>Choose Timeframe</h2>
<p>Start Date: <input type="text" id="start_date" size="30"/></p>


<p>End Date: <input type="text" id="end_date" size="30"/></p>



	<p><input id="submit" type="submit" value="Generate &rarr;"></p>
</form>

<div id="results"></div>
<div id="pager" class="pager">
	<form>
		<img src="../addons/pager/icons/first.png" class="first"/>
		<img src="../addons/pager/icons/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="../addons/pager/icons/next.png" class="next"/>
		<img src="../addons/pager/icons/last.png" class="last"/>
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</form>
</div>

</body>
</html>
