<pre>
<?php
require_once(dirname(__FILE__)."/../app.php");


$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
//$DBM->startTransaction();


$c = 0;
$los = array();
/****** DIG THROUGH THE PAGES ************/
$q = $DBM->querySafe("SELECT * FROM ".cfg_obo_LO::TABLE);
while($r = $DBM->fetch_obj($q))
{
	$lo = new nm_los_LO();
	$lo->dbGetFull($DBM, $r->{cfg_obo_LO::ID});
	
	$s = serialize($lo);
	
	$los[$r->{cfg_obo_LO::ID}] = array();

	// search for mediaIDs

	// catches string or int types ----ex:   s:7:"mediaID";s:10:"34"   OR   s:7:"mediaID";i:2129
	if($c += preg_match_all('/s:7:"mediaID";(?:(?:i:)|(?:s:\d+:"))(\d+)/', $s, $matches))
	{
		foreach($matches[1] AS $match)
		{
			$los[$r->{cfg_obo_LO::ID}][] = $match;
		}
	}
	
	$los[$r->{cfg_obo_LO::ID}] = array_unique($los[$r->{cfg_obo_LO::ID}]);
	if(count($los[$r->{cfg_obo_LO::ID}]) == 0)	unset($los[$r->{cfg_obo_LO::ID}]);
}

// loop through LOs
foreach($los AS $loID => $mediaIDs)
{
	// loop through each LO's Media
	foreach($mediaIDs AS $mediaID)
	{
		$DBM->querySafe("INSERT INTO ".cfg_obo_Media::MAP_TABLE." SET ".cfg_obo_Media::ID." = '?', ".cfg_obo_LO::ID." = '?'", $mediaID, $loID);
	}
}

echo count($los) . " - loIDs \n";
//print_r($los);


// echo count($questionIDs) . " - questions \n";
// print_r($questionIDs);

echo "$c - ";

?>