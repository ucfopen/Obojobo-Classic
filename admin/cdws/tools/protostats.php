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
			// REMOTE - GET USER
			$.ajax({
				url: "/remoting/json.php/loRepository.getUser",
				context: document.body,
				dataType: 'json',
				success: function(msg)
					{
						$('span.first').append(msg.first);
						$('span.last').append(msg.last);
						getMyLOs();
					}
			});
		
			// REMOTE - GET LEARNING OBJECTS
			function getMyLOs()
			{
				$.ajax({
					url: "/remoting/json.php/loRepository.getLOs",
					context: document.body,
					dataType: 'json',
					success: onGetMyLOs
				});
			}
		
			// PLACE RESULTS INTO THE SELECT BOX
			function onGetMyLOs(los)
			{
				console.log(los);
				var loBox = $('#mylos');
				var options = loBox.attr('options');

				// sort alphabetically
				los = $(los).sort(function(a,b){
					if(a.title.toLowerCase() > b.title.toLowerCase())
					{
						return 1
					}
					else if(a.title.toLowerCase() == b.title.toLowerCase())
					{
						return a.version > b.version ? 1 : -1
					}
					else
					{
						return -1
					}
				});
			
				$.each(los, function(text, lo)
				{
					if(lo.version > 0 && lo.subVersion == 0)
					{
						var d = new Date(lo.createTime * 1000);
						options[options.length] = new Option(lo.title + " v." + lo.version + "." + lo.subVersion + ' ' + (d.getMonth()+1) + '/' + d.getDate() + '/' + d.getFullYear(), lo.loID);
					}
				});
			}
		
			// ON SUBMIT
			$('#submit').click(function(){
			
				var los = new Array();
				$("#mylos option:selected").each(function(index,val){
					los.push($(this).val());
				});
				los = '['+los.join()+']';
			
				var s = $('#start_date').datepicker('getDate').getTime()/1000;
				var e = $('#end_date').datepicker('getDate').getTime()/1000;
			
				getStats(los, $('input:radio[name=stat]:checked').val(), s, e, $('input:radio[name=resolution]:checked').val());
				return false;
			});
		
			function getStats(los, statID, startDate, endDate, resolution)
			{
				$.ajax({
					url: "/remoting/json.php/loRepository.getLOStats/"+los+'/'+ statID + '/'+ startDate +'/' + endDate + '/' + resolution,
					context: document.body,
					dataType: 'json',
					success: onGetStats
				});
			}
		
			// PLACE RESULTS IN A TABLE
			function onGetStats(results)
			{
				// Clear previous results
				$('#results-table').remove();
			
				// build the table
				$('#results').append('<table id="results-table" class="tablesorter"><thead><tr class="table-header"></tr></thead><tbody></tbody></table>');

				// Build the header row
				for(index in results[0])
				{
					$('#results-table tr.table-header').append('<th>'+index+'</th>');
				};
			
				// Place each data row
				$(results).each(function(index,val){
					var row = '<tr>'
					for(index in val)
					{
						row += '<td>'+ val[index] +'</td>';
					}
					row += '</tr>'
					$('#results-table tbody').append(row);
				});
			
				// Enable the table sorter
				$("#results-table").tablesorter({widthFixed: true, widgets: ['zebra']}).tablesorterPager({container: $("#pager")});
			
			}
			
			// SET UP THE DATE PICKERS
			$(function() {
				var dates = $( "#start_date, #end_date" ).datepicker({
					defaultDate: "+1w",
					changeMonth: true,
					numberOfMonths: 3,
					onSelect: function( selectedDate ) {
						var option = this.id == "start_date" ? "minDate" : "maxDate",
							instance = $( this ).data( "datepicker" ),
							date = $.datepicker.parseDate(
								instance.settings.dateFormat ||
								$.datepicker._defaults.dateFormat,
								selectedDate, instance.settings );
						dates.not( this ).datepicker( "option", option, date );
					}
				});
			});
		
		});
	</script>
	<style type="text/css" media="screen">
		div.ui-datepicker{
		 font-size:10px;
		}
	</style>
</head>
<body>
<div id="name">You Are: <span class="first"></span> <span class="last"></span></div>
<h2>Choose Learning Object</h2>
<form action="protostats_submit" method="get" accept-charset="utf-8">

<select name="some_name" id="mylos" multiple onchange="" size="15"></select><br>

<h2>Choose Stat</h2>
<fieldset>
    <legend>Instances Data:</legend>
	<input type="radio" name="stat" value="1" id="instance_count"><label for="instance_count">Instances Created</label><br>
	<input type="radio" name="stat" value="2" id="student_count"><label for="student_count">Student Views [3]</label><br>
	<input type="radio" name="stat" value="3" id="derivative_count"><label for="derivative_count">Derivatives Created [x]</label><br>
	<input type="radio" name="stat" value="4" id="assessment_count"><label for="assessment_count">Assessments Completed</label><br>
</fieldset>
<fieldset>
    <legend>Owner Data:</legend>
	<input type="radio" name="stat" value="5" id="who_created_instances"><label for="who_created_instances">Who Created Instances</label><br>
	<input type="radio" name="stat" value="6" id="which_courses"><label for="which_courses">Which Courses</label><br>
</fieldset>
<fieldset>
    <legend>Student Data:</legend>
	<input type="radio" name="stat" value="7" id="question_answers"><label for="question_answers">Question Answer Values [x]</label><br>
	<input type="radio" name="stat" value="8" id="content_views"><label for="content_views">Question and Content Page Views</label><br>
	<input type="radio" name="stat" value="9" id="scores"><label for="scores">Scores [x]</label><br>
	<input type="radio" name="stat" value="10" id="attempt"><label for="attempt">Attempt [x]</label><br>
</fieldset>
<h2>Choose Timeframe</h2>


<label for="start_date">From</label>
<input type="text" id="start_date" name="start_date"/>
<label for="end_date">to</label>
<input type="text" id="end_date" name="end_date"/>

<h2>Choose Data Resolution</h2>
<input type="radio" name="resolution" value="all" id="resolution_all"><label for="resolution_all">All Time</label><br>
<input type="radio" name="resolution" value="year" id="resolution_year"><label for="resolution_year">Years</label><br>
<input type="radio" name="resolution" value="month" id="resolution_month"><label for="resolution_month">Months</label><br>
<input type="radio" name="resolution" value="day" id="resolution_day"><label for="resolution_day">Days</label><br>
<input type="radio" name="resolution" value="hour" id="resolution_hour"><label for="resolution_hour">Hours</label><br>


	<p><input id="submit" type="submit" value="Generate &rarr;"></p>
</form>

<div id="results"></div>
<div id="pager" class="pager">
	<form>
		<img src="images/first.png" class="first"/>
		<img src="images/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="images/next.png" class="next"/>
		<img src="images/last.png" class="last"/>
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
