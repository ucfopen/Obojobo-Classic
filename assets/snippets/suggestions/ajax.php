<?php

if(!$modx->userLoggedIn())
{
	return;
}

require "connect.php";
require "suggestion.class.php";

// If the request did not come from AJAX, exit:
if($_SERVER['HTTP_X_REQUESTED_WITH'] !='XMLHttpRequest')
{
	exit;
}

// Converting the IP to a number. This is a more effective way
// to store it in the database:



if($_GET['action'] == 'vote')
{

	$vote = (int)$_GET['vote'];
	$id = (int)$_GET['id'];

	if($vote != -1 && $vote != 1)
	{
		exit;
	}
	trace('one');
	$ip	= sprintf('%u',ip2long($_SERVER['REMOTE_ADDR']));
	// The id, ip and day fields are set as a primary key.
	// The query will fail if we try to insert a duplicate key,
	// which means that a visitor can vote only once per day.
	$DBM->querySafe("
		INSERT INTO suggestions_votes (suggestion_id,ip,day,vote,username)
		VALUES (
			'?',
			'?',
			CURRENT_DATE,
			'?',
			'?'
		)", $id, $ip, $vote, $modx->getLoginUserName());

	if($DBM->affected_rows() > 0)
	{
		$DBM->query("
			UPDATE suggestions SET 
				".($vote == 1 ? 'votes_up = votes_up + 1' : 'votes_down = votes_down + 1').",
				rating = rating + $vote
			WHERE id = $id
		");
	}
	
trace('trhe');
	
}
else if($_GET['action'] == 'submit')
{
	if(get_magic_quotes_gpc())
	{
		array_walk_recursive($_GET,create_function('&$v,$k','$v = stripslashes($v);'));
	}
	// Stripping the content
	$_GET['content'] = htmlspecialchars(strip_tags($_GET['content']));
	
	if(mb_strlen($_GET['content'],'utf-8') < 3)
	{		
		exit;
	}
	
	$DBM->querySafe("INSERT INTO suggestions SET username = '?', suggestion = '?'", $modx->getLoginUserName(), $_GET['content']);
	// Outputting the HTML of the newly created suggestion in a JSON format.
	// We are using (string) to trigger the magic __toString() method of the object.
	echo json_encode(array(
		'html'	=> (string)(new Suggestion(array(
			'id'			=> $DBM->insertID,
			'suggestion'	=> $_GET['content']
		)))
	));
}


?>