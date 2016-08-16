<?php
require_once (dirname( __FILE__ )."/../internal/app.php");

/*
@TODO: This is a hack becuase I can't send POST requests to the API.
We need to not use the API here and call the API directly.
*/

if(!empty($_POST) && !empty($_POST['selectedWidget']))
{
	$api = \obo\API::getInstance();

	$selectedWidget = json_decode($_POST['selectedWidget']);
	$m = array(
		'title' => $selectedWidget->name,
		'itemType' => 'kogneato',
		'descText' => '',
		'copyright' => 'Content from Materia.',
		'thumb' => 0,
		// Bahamut uses id, Ifrit uses GIID
		'url' => (!empty($selectedWidget->id) ? $selectedWidget->id : $selectedWidget->GIID),
		'size' => 0,
		'length' => 0,
		'width' => $selectedWidget->width,
		'height' => $selectedWidget->height,
		'meta' => $selectedWidget,
		'attribution' => ''
	);

	return $api->createExternalMediaLink($m);
	die();
}

$api = \obo\API::getInstance();
$results = $api->getLTIParams('select');

if(!is_array($results)) exit('There was an error, please try again later.');
?>

<!DOCTYPE html>
<html>
<head>
	<title>Select a Widget for use in Obojobo</title>
	<script src="js/jquery.js"></script>
	<script type="text/javascript">
		$(function() {
			$('#form').submit();
		});

		if(typeof window.addEventListener !== 'undefined')
		{
			window.addEventListener('message', onWidgetSelected, false);
		}
		else if(typeof window.attachEvent !== 'undefined')
		{
			window.attachEvent('onmessage', onWidgetSelected);
		}

		function onWidgetSelected(result)
		{
			$.post(window.location, {selectedWidget:result.data}, function(data) {
				window.close();
			});
		}
	</script>
</head>
<body>
	<form name="form" id="form" action="<?php echo $results['url']; ?>" method="POST" target="tool_form"  class="" data-tool-id="grade_passback" style="">
		<?php
			foreach($results['params'] as $key => $val)
			{
				echo "<input name='$key' id='$key' value='$val' hidden='true'>\n";
			}
		?>
	</form>
	<iframe name="tool_form" id="tool_form" style="position:absolute; left:0; right:0; top:0; bottom:0; width:100%; height:100%; border:none;"></iframe>
</body>
