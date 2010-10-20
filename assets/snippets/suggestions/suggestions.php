<h1>Vote for Existing Suggestions</h1>
<p>Help us determine what to build next by voting on these features.  You can vote each feature up or down once.</p>
<?php
require('connect.php');
require('suggestion.class.php');


// The following query uses a left join to select
// all the suggestions and in the same time determine
// whether the user has voted on them.

$qstr  = "
	SELECT s.*, if (v.username IS NULL,0,1) AS have_voted
	FROM suggestions AS s
	LEFT JOIN suggestions_votes AS v
	ON
	(
		s.id = v.suggestion_id
		AND v.username = '".$modx->getLoginUserName()."'
	)
	ORDER BY s.rating DESC, s.id DESC";

if($q = $DBM->query($qstr))
{
	// Generating the UL
	$str = '<ul class="suggestions">';
	// Using MySQLi's fetch_object method to create a new
	// object and populate it with the columns of the result query:
	while($suggestion = $DBM->fetch_obj($q, 'Suggestion'))
	{
		$str.= (string)$suggestion;	// Using the __toString() magic method.
	}
	
	$str .='</ul>';
}


echo $str;
?>
<h1>Suggest A Feature</h1>
<p>Add a suggestion of your own, type in a new suggestion for everyone to vote on.</p>
<form id="suggest" action="" method="post">
    <p>
        <input type="text" id="suggestionText" class="rounded" maxlength="255" />
        <input type="submit" value="Submit" id="submitSuggestion" />
    </p>
</form>