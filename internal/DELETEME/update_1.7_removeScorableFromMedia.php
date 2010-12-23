<p>This script will remove 'scorable' from blobs in lo_los_questions and lo_los_pages.  <a href="<?php echo $_SERVER['PHP_SELF']; ?>?run=1">run!</a></p>
<p>if scoreable exists, youll see an ID of the item.  If you see &lt;*, it updated the serialized data, :) means no update was needed </p>
<p>search for "ERROR" or "!fail"</p>
<p>When completed, run this script again, all items should be :) <a href="<?php echo $_SERVER['PHP_SELF']; ?>">test</a></p>
<pre>
<?php
require_once(dirname(__FILE__)."/../app.php");

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$DBM->startTransaction();

echo "lo_los_questions:\n\n";
$qs = "SELECT * FROM ".\cfg_obo_Question::TABLE." WHERE 1";
$q = $DBM->querySafe($qs);
if($q)
{
	while($r = $DBM->fetch_obj($q))
	{
		$data = base64_decode($r->questionData);
		
		// remove the scorable key
		if(preg_match('/s:8:"scorable";i:0;/', $data))
		{
			$data = preg_replace('/s:8:"scorable";i:0;/', '', $data);
			print_r($r->questionID);
			echo " ";
			$qs2 = "UPDATE ".\cfg_obo_Question::TABLE." SET questionData='?' WHERE questionID = ?";
			$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->questionID);
			if(!$q2)
			{
				echo "\n\nERROR\n\n";
				$DBM->rollback();
				die();
			}
		}
		
		// fix the serialized index for the media object to reduce the number of members by one
		if(preg_match('/"\obo\lo\Media":(\d+):/', $data, $matches))
		{
			$x = @unserialize($data);
			if($x === false)
			{
				$data = preg_replace('/"\obo\lo\Media":(\d+):/', '"\obo\lo\Media":'.($matches[1]-1).':', $data);
				$x = @unserialize($data);
				if($x === false)
				{
					echo '!fail ';
				}
				else
				{
					echo "<* ";
					$qs2 = "UPDATE ".\cfg_obo_Question::TABLE." SET questionData='?' WHERE questionID = ?";
					$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->questionID);
					if(!$q2)
					{
						echo "\n\nERROR\n\n";
						$DBM->rollback();
						die();
					}
				}
			}
			else
			{
				echo ':) ';
			}
		}
	}
}

echo "\n\nlo_los_pages:\n\n";
$qs = "SELECT * FROM ".\cfg_obo_Page::TABLE." WHERE 1";
$q = $DBM->querySafe($qs);
if($q)
{
	while($r = $DBM->fetch_obj($q))
	{
		$data = base64_decode($r->pageData);
		
		// remove the scorable key
		if(preg_match('/s:8:"scorable";i:0;/', $data))
		{
			$data = preg_replace('/s:8:"scorable";i:0;/', '', $data);
			print_r($r->pageID);
			echo " ";
			$qs2 = "UPDATE ".\cfg_obo_Page::TABLE." SET pageData='?' WHERE pageID = ?";
			$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->pageID);
			if(!$q2)
			{
				echo "\n\nERROR\n\n";
				$DBM->rollback();
				die();
			}
		}
		
		// fix the serialized index for the media object to reduce the number of members by one
		
		if(preg_match('/"\obo\lo\Media":(\d+):/', $data, $matches))
		{
			$x = @unserialize($data);
			if($x === false)
			{
				$data = preg_replace('/"\obo\lo\Media":(\d+):/', '"\obo\lo\Media":'.($matches[1]-1).':', $data);
				$x = @unserialize($data);
				if($x === false)
				{
					echo '!fail ';
				}
				else
				{
					echo "<* ";
					$qs2 = "UPDATE ".\cfg_obo_Page::TABLE." SET pageData='?' WHERE pageID = ?";
					$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->pageID);
					if(!$q2)
					{
						echo "\n\nERROR\n\n";
						$DBM->rollback();
						die();
					}
				}
			}
		}
		else
		{
			echo ':) ';
		}
	}
}

echo "\n\nDONE\n\n";
if($_GET['run'] == 1)
{
	echo "COMMIT!";
	$DBM->commit();
}
else
{
	echo "ROLLBACK!";
	$DBM->rollback();
}
?>