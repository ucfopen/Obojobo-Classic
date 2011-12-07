<?php
ini_set('display_errors', '1');

echo '<pre>';
require(__DIR__ . '/../../internal/app.php');

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));


if(isset($_REQUEST['done']))
{
	exit('DONE ALREADY!!!');
}

// lets open up every media swf and update it's metadata
if(!isset($_REQUEST['loID']))
{
	if(isset($_REQUEST['mediaID']))
	{
		$q = $DBM->querySafe("SELECT * FROM obo_lo_media WHERE itemType = 'swf' AND mediaID >= '?' ORDER BY mediaID ", $_REQUEST['mediaID']);
	}
	else
	{
		$q = $DBM->query("SELECT * FROM obo_lo_media WHERE itemType = 'swf' ORDER BY mediaID ");
	}
	while($r = $DBM->fetch_obj($q))
	{
		echo "Media ID: " . $r->mediaID . "\n";
		$swf= new \obo\lo\media\SWF(__DIR__ . '/../../internal/media/'.$r->mediaID.'.swf');
		echo "height=[".$swf->height."]\n";
		echo "width=[".$swf->width."]\n";
		echo "totalFrames=[".$swf->totalFrames."]\n";
		echo "asVersion=[".$swf->asVersion."]\n";
		echo "swfVersion=[".$swf->version."]\n";
		
		
		$metadata = array(
			'version' => $swf->version,
			'asVersion' => $swf->asVersion
			);
		$meta = base64_encode(serialize($metadata));
		$DBM->query("UPDATE obo_lo_media SET 
					meta = '$meta',
					length = '$swf->totalFrames',
					height = '".$swf->height."',
					`width` = '".$swf->width ."'
					WHERE mediaID = '".$r->mediaID."'");
		unset($swf);
		echo "<script type='text/javascript'>history.replaceState(null, null, '{$_SERVER['PHP_SELF']}?mediaID=$r->mediaID');</script>\n";
		echo "==========================\n";
	}
}
echo('metadata done!');
flush();
// now we need make sure the media objects in the pages, questions
// find them by digging for the mapped lo's a media object is used in
// then open each page and question to fix the media there
// then invalidate memcache
// 

$pm = \obo\lo\PageManager::getInstance();
$qgm = \obo\lo\QuestionGroupManager::getInstance();
$mm = \obo\lo\MediaManager::getInstance();
$loIDs = array();
$medias = array();


if(isset($_REQUEST['loID']))
{
	$q = $DBM->querySafe("SELECT DISTINCT(obo_map_media_to_lo.loID), obo_los.* FROM obo_map_media_to_lo LEFT JOIN obo_los ON obo_los.loID = obo_map_media_to_lo.loID WHERE obo_map_media_to_lo.mediaID IN (SELECT mediaID FROM obo_lo_media WHERE itemType = 'swf' ORDER BY mediaID ) AND obo_map_media_to_lo.loID > '?' ORDER BY obo_map_media_to_lo.loID", $_REQUEST['loID']);
}
else
{
	$q = $DBM->query("SELECT DISTINCT(obo_map_media_to_lo.loID), obo_los.* FROM obo_map_media_to_lo LEFT JOIN obo_los ON obo_los.loID = obo_map_media_to_lo.loID WHERE obo_map_media_to_lo.mediaID IN (SELECT mediaID FROM obo_lo_media WHERE itemType = 'swf' ORDER BY mediaID ) ORDER BY obo_map_media_to_lo.loID");
}


while($r = $DBM->fetch_obj($q))
{
	echo "Lo ID: " . $r->loID . "\n";
	$loIDs [] = $r->loID;
	$pages = $pm->getPagesForLOID($r->loID);
	
	
	// loop through the pages
	foreach($pages AS &$page)
	{
		$pageHasUpdate = false;
		foreach($page->items AS &$item)
		{
			
			if(is_array($item->media) && count($item->media) > 0)
			{
				foreach($item->media AS &$media)
				{
					
					if(in_array($media->mediaID, $mediaIDs) && !isset($media->meta['asVersion']))
					{
						if(!isset($medias[$media->mediaID]))
						{
							$medias[$media->mediaID] = $mm->getMedia($media->mediaID);
						}
						$media = $medias[$media->mediaID];
						$pageHasUpdate = true;
					}
				}
			}
			
		}
		
		if($pageHasUpdate)
		{
			echo "Page $page->pageID updated \n";
			flush();
			$qstr = "UPDATE ".\cfg_obo_Page::TABLE." SET ".\cfg_obo_Page::PAGE_DATA."='?' WHERE pageID = '?'";
	 		$DBM->querySafe($qstr, base64_encode(serialize($page)), $page->pageID);
			
		}

	}
	
	// Practice groups
	$pGroup = $qgm->getGroup($r->pGroupID);
	foreach($pGroup->kids AS &$kid)
	{
		foreach($kid->items AS &$item)
		{
			$qHasUpdate = false;
			if(is_array($item->media) && count($item->media) > 0)
			{
				foreach($item->media AS &$media)
				{
					if(in_array($media->mediaID, $mediaIDs) && !isset($media->meta['asVersion']))
					{
						if(!isset($medias[$media->mediaID]))
						{
							$medias[$media->mediaID] = $mm->getMedia($media->mediaID);
						}
						$media = $medias[$media->mediaID];
						$qHasUpdate = true;
					}
				}
			}
			
			if($qHasUpdate)
			{
				echo "Question $kid->questionID updated \n";
				flush();
				$qstr = "UPDATE ".\cfg_obo_Question::TABLE." SET ".\cfg_obo_Question::DATA."='?' WHERE ".\cfg_obo_Question::ID." = '?'";
		 		$DBM->querySafe($qstr, base64_encode(serialize($kid)), $kid->questionID);
			}
		}
	}
	echo "PRACTICe Questions complete \n";
	flush();
	// Assessment Groups
	$aGroup = $qgm->getGroup($r->aGroupID);
	foreach($aGroup->kids AS &$kid)
	{
		foreach($kid->items AS &$item)
		{
			$qHasUpdate = false;
			if(is_array($item->media) && count($item->media) > 0)
			{
				foreach($item->media AS &$media)
				{
					if(in_array($media->mediaID, $mediaIDs) && !isset($media->meta['asVersion']))
					{
						if(!isset($medias[$media->mediaID]))
						{
							$medias[$media->mediaID] = $mm->getMedia($media->mediaID);
						}
						$media = $medias[$media->mediaID];
						$qHasUpdate = true;
					}
				}
			}
			
			if($qHasUpdate)
			{
				echo "Question $kid->questionID updated \n";
				flush();
				$qstr = "UPDATE ".\cfg_obo_Question::TABLE." SET ".\cfg_obo_Question::DATA."='?' WHERE ".\cfg_obo_Question::ID." = '?'";
		 		$DBM->querySafe($qstr, base64_encode(serialize($kid)), $kid->questionID);
			}
		}
	}
	echo "<script type='text/javascript'>history.replaceState(null, null, '{$_SERVER['PHP_SELF']}?loID=$r->loID');</script>\n";
	echo "==========================\n";
}
echo "<script type='text/javascript'>history.replaceState(null, null, '{$_SERVER['PHP_SELF']}?done=1');</script>\n";
echo('DONE!');
?>