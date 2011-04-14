<pre>
<?php
// ADDs visitid to each log and moves the hard to get to data from a compresses serialized string to sortable columns
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));



// sync visits with visitIDs
$q = $DBM->query("SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE instID > 0 ORDER BY instID, userID, createTime");


$lastUserID = 0;
$lastInstID = 0;
$curVisitID = 0;
$trackingIDsToUpdate = array();
$curUserID = 0;

while($r = $DBM->fetch_obj($q))
{
	// echo "[{$r->trackingID}]{$r->itemType}<br>";
	// if this is a visit log, keep track of the visit id, and set the visit IDs of the the previous visit
	if($r->itemType == 'Visited')
	{

		$data = deserializeData($r->data);
		// echo "Visit Found: {$data->visitID}<br>";
		flush();
		if($data->visitID > 0) 
		{
			// echo "vist {$r->data->visitID}<br>";
			flush();
			if(count($trackingIDsToUpdate) > 0 )
			{
				echo "updating... {$curVisitID} -> " . implode(',', $trackingIDsToUpdate) . "<Br>";
				flush();
				// update visit IDs of the previous visit
				$DBM->query("UPDATE obo_logs SET visitID = '".$curVisitID."' WHERE trackingID IN (" . implode(',', $trackingIDsToUpdate) . ")");
			}
			// reset the trackingIDs
			$trackingIDsToUpdate = array();
			// now keep track of this log's visit id 
			$curVisitID = $data->visitID;
			$curUserID = $r->userID;
		}
	}
	// make sure the log is for this same user - otherwise it couldnt be part of this visit
	if($curUserID == $r->userID)
	{
		$trackingIDsToUpdate[] = $r->trackingID; // keep tracking ids
	}
	else
	{
		echo "$r->trackingID Problem<br>";
		flush();
	}
}

// the visit for this log had no id or something - falls through the cracks of the above query
//$DBM->query("UPDATE obo_logs SET visitID = '66813' WHERE userID = 7318 AND instID = 1888");


function deserializeData($data)
{
	$data = preg_replace_callback('/(\d+):"(nm_los_tracking_)/', 'fixObject', $data);
	$data = unserialize($data);
	return $data;
}

function fixObject($matches)
{
	return ($matches[0]-7) . ':"\\obo\\log\\';
}

// process the last trackingIDs
if(count($trackingIDsToUpdate) > 0 )
{
	$DBM->query("UPDATE obo_logs SET visitID = '".$curVisitID."' WHERE trackingID IN (" . implode(',', $trackingIDsToUpdate) . ")");
}


$DBM->query("UPDATE obo_logs SET visitID = '".$curVisitID."' WHERE trackingID IN (" . implode(',', $trackingIDsToUpdate) . ")");

exit('done');
?>