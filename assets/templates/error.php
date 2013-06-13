<?php

if(!isset($_GET['msgType']))
{
	header("HTTP/1.0 404 Not Found");
	exit();
}

switch($_GET['msgType'])
{
	case 'lti':
		$msg = 'Your session has expired. Refresh this page to continue.';
		break;
	case 'no-access':
		$msg = 'You cannot access this instance directly since it is being used in an external system. Please login to the external system instead.';
		break;
	default:
		header("HTTP/1.0 404 Not Found");
		exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Obojobo</title>

<link type="text/css" rel="stylesheet" href="/min/b=assets/css&amp;f=themes/default.css,error.css" media="screen">

</head>
<body>
	<p><?php echo $msg; ?></p>
</body>
</html>