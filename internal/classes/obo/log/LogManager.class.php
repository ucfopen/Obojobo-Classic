<?php
namespace obo\log;
class LogManager extends \rocketD\db\DBEnabled
{
	private static $instance;
	
	function __construct()
	{
		$this->defaultDBM();
	}
	
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	public function track($trackable)
	{		
		if($trackable instanceof \obo\log\Trackable)
		{

			$VM = \obo\VisitManager::getInstance();
			$visitID = $VM->getCurrentVisitID();
			
			$qstr = "INSERT INTO `".\cfg_obo_Track::TABLE."` (`".\cfg_core_User::ID."`, `".\cfg_obo_Track::TYPE."`, `".\cfg_obo_Track::TIME."`, ".\cfg_obo_LO::ID.", `".\cfg_obo_Instance::ID."`, visitID, valueA, valueB, valueC) VALUES ('?', '?', '?', '?', '?', '?', '?', '?', '?')";
			if(!($q = $this->DBM->querySafe($qstr, $trackable->userID, $trackable->logType, $trackable->createTime, $GLOBALS['CURRENT_INSTANCE_DATA']['loID'], $trackable->instID, $visitID, $trackable->valueA, $trackable->valueB, $trackable->valueC)))
			{
				$this->DBM->rollback();
				return false;
			}
			if(\obo\util\Validator::isPosInt($trackable->userID) &&  \obo\util\Validator::isPosInt($trackable->instID) )
			{
				
				\rocketD\util\Cache::getInstance()->clearInteractionsByInstanceAndUser($trackable->instID, $trackable->userID);
			}
			return true;
		}
		else if($trackable instanceof \obo\util\Error)
		{
			// TODO: dont store all this in the database
			if(isset($GLOBALS['CURRENT_INSTANCE_DATA']))
			{
				$instID = $GLOBALS['CURRENT_INSTANCE_DATA']['instID'];
			}
			else
			{
				$instID	 = 0;
			}
			
			$qstr = "INSERT INTO `".\cfg_obo_Track::TABLE."` (`".\cfg_core_User::ID."`, `".\cfg_obo_Track::TYPE."`, `".\cfg_obo_Track::TIME."`, `".\cfg_obo_Instance::ID."`) VALUES ('{$_SESSION['userID']}', '?', '".time()."', '{$instID}')";
			if(!($q = $this->DBM->querySafe($qstr, $trackable->errorID ) ) )
			{
				$this->DBM->rollback();
				return false;
			}
			return true;
		}
		else
		{
			return false; // error: invalid input: not of type trackable
		}

	}
	
	public function getInteractionLogByInstance($instID=0, $skipPerms=false)
	{
		if($skipPerms!==true)  // needed for the cron job to execute this function
		{
			$roleMan = \obo\perms\RoleManager::getInstance();
			if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
			{
				if(!$roleMan->isLibraryUser())
				{
					return \rocketD\util\Error::getError(4);
				}
				$permman = \obo\perms\PermissionsManager::getInstance();
				if( ! $permman->getUserPerm($instID, \cfg_obo_Perm::TYPE_INSTANCE, \cfg_obo_Perm::WRITE, $_SESSION['userID']) )
				{
					// check 2nd Perms system to see if they have write or own
					$pMan = \obo\perms\PermManager::getInstance();
					$perms = $pMan->getPermsForUserToItem($_SESSION['userID'], \cfg_core_Perm::TYPE_INSTANCE, $instID);
					if(!is_array($perms) && !in_array(\cfg_core_Perm::P_READ, $perms) && !in_array(\cfg_core_Perm::P_OWN, $perms) )
					{
						return \rocketD\util\Error::getError(4);
					}
				}
			}
		}
		
		$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Instance::ID." = '?'	ORDER BY ".\cfg_core_User::ID.", ".\cfg_obo_Track::TIME;
		return $this->getInteractionLogs($this->DBM->querySafe($trackQ, $instID));
		
	}

	
	public function getInteractionLogByMaster($loID=0, $totalsOnly=true)
	{
		// must be SU 
		// this is likely to use too many resources to execute
		$q = $this->DBM->querySafe("SELECT ".\cfg_obo_Instance::ID." FROM ".\cfg_obo_Instance::TABLE." WHERE ".\cfg_obo_LO::ID." = '?'", $loID);
		$loIDs = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$loIDs[] = $r->{\cfg_obo_Instance::ID};
		}
		if(count($loIDs) > 0)
		{
			$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Instance::ID." IN (" . implode(",", $loIDs) . ") ORDER BY ".\cfg_obo_Instance::ID.", ".\cfg_core_User::ID.", ".\cfg_obo_Track::TIME;
			return $this->getInteractionLogs($this->DBM->query($trackQ), $totalsOnly);
		}
	 	return array();
	}
	
	public function getInteractionLogTotals()
	{
		// must be user, instance owner, or SU
		$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Instance::ID." != 0 AND ".\cfg_obo_Track::TIME." > 1214193600 ORDER BY ".\cfg_obo_Instance::ID.", ".\cfg_core_User::ID.", ".\cfg_obo_Track::TIME;
		return $this->getInteractionLogs($this->DBM->query($trackQ), true, 10);
	}
	
	public function getInteractionLogByUser($userID=0)
	{
		// must be SU or this user
		$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Instance::ID." != 0 AND ".\cfg_core_User::ID." = '?'	ORDER BY ".\cfg_obo_Instance::ID.", ".\cfg_core_User::ID.", ".\cfg_obo_Track::TIME;		
		return $this->getInteractionLogs($this->DBM->querySafe($trackQ, $userID));
	}
	
	public function getInteractionLogByUserAndInstance($instID=0, $userID=0)
	{
		// must own instance or be user or SU
		// check memcache
 		
		if($tracking = \rocketD\util\Cache::getInstance()->getInteractionsByInstanceAndUser($instID, $userID))
		{
			return $tracking;
		}
		
		$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Instance::ID." = '?' AND ".\cfg_core_User::ID." = '?' ORDER BY ".\cfg_obo_Instance::ID.", ".\cfg_core_User::ID.", ".\cfg_obo_Track::TIME;
		$return = $this->getInteractionLogs($this->DBM->querySafe($trackQ, $instID, $userID));
		
		\rocketD\util\Cache::getInstance()->setInteractionsByInstanceAndUser($instID, $userID, $return);
		
		return $return;
	}
	
	public function getInteractionLogByVisit($visitID=0)
	{
		// must be user, instance owner, or SU
		
		// if($tracking = \rocketD\util\Cache::getInstance()->getInteractionsByVisit($visitID))
		// {
		// 	return $tracking;
		// }
		
		$trackQ = "SELECT * FROM ".\cfg_obo_Track::TABLE." WHERE ".\cfg_obo_Visit::ID." = '?' ORDER BY ".\cfg_obo_Track::TIME;
		$return = $this->getInteractionLogs($this->DBM->querySafe($trackQ, $visitID));
		
		// \rocketD\util\Cache::getInstance()->setInteractionsByVisit($visitID, $return);
		
		return $return;
	}

	protected function getInteractionLogs($query, $totalsOnly=false)
	{

		if($query)
		{
			$AM = \obo\AttemptsManager::getInstance();
			$missingInstances = array();
			$missingLOs = array();
			$los = array();
			$visits = array();
			$curSection = 0;
			$loFound = false;
			$curInst = 0; // track cur instance to keep db hits low 
			$overallSectionTime = array('overview' => 0, 'content' => 0, 'practice' => 0, 'assessment' => 0, 'total' => 0);
			$sectionTime = array('overview' => 0, 'content' => 0, 'practice' => 0, 'assessment' => 0, 'total' => 0, 'other' => 0);
			$overallPageViews  = array('content' => array('total'=>0,'unique'=>0), 'practice' => array('total'=>0,'unique'=>0), 'assessment' => array('total'=>0,'unique'=>0));
			$sectionNames = array('overview', 'content', 'practice', 'assessment');
			$visitStarted = false;
			$thisVisit = array();
			$pageViews = array('content' => array('total' => 0,'unique' => 0), 'practice' => array('total'=>0,'unique'=>0), 'assessment' => array('total'=>0,'unique'=>0));
			while($r = $this->DBM->fetch_obj($query))
			{
				switch($r->{\cfg_obo_Track::TYPE})
				{
					case 'Visited':
						// print and tally totals for previous visit
						
						// total up the time from the previous visit's data
						if($visitStarted  && isset($sectionTime))
						{
							$sectionTime['total'] += ($thisVisit[count($thisVisit) - 1]->createTime - $thisVisit[0]->createTime);
							$sectionTime['other'] = $sectionTime['total'] - $sectionTime['overview'] - $sectionTime['content'] - $sectionTime['practice'] - $sectionTime['assessment'];
							$overallSectionTime['total'] += $sectionTime['total'];
							$overallSectionTime['overview'] += $sectionTime['overview'];
							$overallSectionTime['content'] += $sectionTime['content'];
							$overallSectionTime['practice'] += $sectionTime['practice'];
							$overallSectionTime['assessment'] += $sectionTime['assessment'];
							if(!$totalsOnly && isset($lo))
							{
								$visits[] = array('instID' => $curInst, 'userID' => $tmpUID, 'visitID' => $thisVisit[0]->visitID, 'loID' => $thisVisit[0]->loID , 'createTime' => $thisVisit[0]->createTime, 'sectionTime' => $sectionTime, 'pageViews' => $pageViews, 'logs' => $thisVisit);	
							}
						}

						// fetch the LO if we havn't already
						if($curInst != $r->{\cfg_obo_Instance::ID})
						{
								
							$loQ = "SELECT ".\cfg_obo_LO::ID." FROM ".\cfg_obo_Instance::TABLE." WHERE ".\cfg_obo_Instance::ID."='?'";
							$loR = $this->DBM->fetch_obj($this->DBM->querySafe($loQ, $r->{\cfg_obo_Instance::ID}));
							if(is_object($loR))
							{
								// check to make sure we havn't opened this one before
								if(!isset($los[$loR->{\cfg_obo_LO::ID}]))
								{
									if($loFound == false || $loR->{\cfg_obo_LO::ID} != $lo->loID)
									{
										$lo = new \obo\lo\LO();
										$loFound = $lo->dbGetFull($this->DBM, $loR->{\cfg_obo_LO::ID});
										if($loFound)
										{
											$los[$loR->{\cfg_obo_LO::ID}] =  $lo;
										}
										else
										{
											$missingLOs[] = $loR->{\cfg_obo_LO::ID};
										}
										
									}
								}
								else // already loaded, just use the one in memory
								{
									$lo = $los[$loR->{\cfg_obo_LO::ID}];
									
								}
								$curInst = $r->{\cfg_obo_Instance::ID};
							}
							else
							{
								$missingInstances[] =  $r->{\cfg_obo_Instance::ID};
							}
						}
						// Initialize this visit
						$prevPageView = false;
						$visitStarted = true;
						$thisVisit = array();
						$sectionTime = array('overview' => 0, 'content' => 0, 'practice' => 0, 'assessment' => 0, 'total' => 0);
						$pageViews = array('content' => array('total' => 0,'unique' => 0), 'practice' => array('total'=>0,'unique'=>0), 'assessment' => array('total'=>0,'unique'=>0));
					
						//$r->data = $this->deserializeData($r->data);
						$thisVisit[] = $r;
						$curSection = $sectionNames[0];

						break;

					case 'SectionChanged':
						if($loFound)
						{
							$sectionTime[$curSection] += ($r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime);
							//if((int)$r->data->to != 3) 
							$curSection = $sectionNames[(int)$r->{\cfg_obo_Track::TO}];
							$thisVisit[] = $r;
						
							if(!$totalsOnly && isset($prevPageView) && is_object($prevPageView) )
							{
								$prevPageView->viewTime = $r->{\cfg_obo_Track::TIME} - $prevPageView->createTime;
								unset($prevPageView);
							}
						}
						break;

					case 'PageChanged':
						if($loFound)
						{					
							$toSection = (int) ($r->{\cfg_obo_Track::IN} == 0 ? 1 : $r->{\cfg_obo_Track::IN});
							$pageIndex = '?';
							if(isset($lo->pages) && count($lo->pages > 0))
							{ 
								switch($toSection)
								{
									case '1': // Content Pages
										if(is_array($lo->pages))
										{
											foreach($lo->pages AS $key => $page)
											{
												if($page->pageID == $r->{\cfg_obo_Track::TO})
												{
													$r->title = $page->title;
													$pageIndex =  1 + (int)$key;
													break;
												}
											}
										}
									break;
									case '2': // Practice Questions
										if(is_array($lo->pGroup->kids))
										{
											foreach($lo->pGroup->kids AS $key => $page)
											{
												if($page->questionID == $r->{\cfg_obo_Track::TO})
												{
													$r->qType = $page->itemType;
													//if(!$totalsOnly) $r->qText = substr(($page->itemType == 'M' ? preg_replace("/[\n\r]/","", strip_tags($page->media[0]->title)) : preg_replace("/[\n\r]/","",strip_tags($page->items[0]->{\cfg_obo_Page::ITEM_DATA}))), 0, 120);
													$pageIndex =  1 + (int)$key;
													break;
												}
											}
										}
										break;
									case '3': // Assessment Questions
										if(is_array($lo->aGroup->kids))
										{
											//
											$altIndex = 1;
											$realQNum = 0;
											$curAlt = -1;
											foreach($lo->aGroup->kids AS $key => $page)
											{
												if($page->questionIndex == 0 || $curAlt !== $page->questionIndex)
												{
													$altIndex = 1;
													$realQNum++;
													$curAlt = $page->questionIndex;
													
												}
												else{
													$altIndex++;
												}
												
												if($page->questionID == $r->{\cfg_obo_Track::TO})
												{
													if($page->questionIndex) $r->altIndex = $altIndex;
													$r->normalIndex = $realQNum;
													$r->qType = $page->itemType;
													if(!$totalsOnly)
													{
														$r->qText = substr(($page->{\cfg_obo_Question::TYPE} == 'Media' ? preg_replace("/[\n\r]/","", strip_tags($page->items[0]->media[0]->{\cfg_obo_Media::TITLE})) : preg_replace("/[\n\r]/","", strip_tags($page->items[0]->data))), 0, 120);
													}
													$pageIndex =  1 + (int)$key;
													$r->realIndex = 1 + $pageIndex;
													break;
												}
											}
											// if quiz is randomized or has alts, figure out what page this one was as shown to the user
											if(isset($currentAttemptOrder) && count($currentAttemptOrder) > 0)
											{
												foreach($currentAttemptOrder AS $key => $page)
												{
													if($page->questionID == $r->{\cfg_obo_Track::TO})
													{
														$r->realIndex = $pageIndex;
														$pageIndex =  1 + (int)$key;
														break;
													}
												}
											}
										}
										break;
								}
							}
							$sectionTime[$curSection] += $r->createTime - $thisVisit[count($thisVisit) - 1]->createTime;
							$curSection = $sectionNames[$toSection];
							if(\obo\util\Validator::isPosInt($pageIndex))
							{
								$pageViews[$curSection][$pageIndex] = isset($pageViews[$curSection][$pageIndex]) ? $pageViews[$curSection][$pageIndex] + 1 : 1;
								$pageViews[$curSection]['total'] = $pageViews[$curSection]['total'] + 1;
								$thisPageID = ($page instanceOf \obo\lo\Question ? $page->questionID : $page->pageID);
								$overallPageViews[$curSection][$thisPageID] = isset($overallPageViews[$curSection][$thisPageID]) ? $overallPageViews[$curSection][$thisPageID] + 1 : 1;
								//$overallPageViews[$curSection][$page->questionID] = $overallPageViews[$curSection][(int)$page->questionID] + 1;
								$overallPageViews[$curSection]['total'] = $overallPageViews[$curSection]['total'] + 1;
								$overallPageViews['total'] = isset($overallPageViews['total']) ? $overallPageViews['total'] + 1 : 1;
								$pageViews['total'] =  isset($pageViews['total']) ? $pageViews['total'] + 1 : 1;
								$overallPageViews[$curSection] ++;
								$pageViews[$curSection]['unique'] = count($pageViews[$curSection])-2;
								$pageViews['unique'] = $pageViews['content']['unique'] + $pageViews['practice']['unique'] + $pageViews['assessment']['unique'];
							}
							$r->page = $pageIndex;
							if(!$totalsOnly && isset($prevPageView) && is_object($prevPageView))
							{
								$prevPageView->viewTime = $r->createTime - $prevPageView->createTime;
								unset($prevPageView);
							}						
							$thisVisit[] = $r;
							if(!$totalsOnly) $prevPageView = $thisVisit[count($thisVisit)-1];
						}
						break;

					case 'StartAttempt':
						if($loFound)
						{
							if(!$totalsOnly)
							{
								//@ZACH: This is my hack attack to get repository code to work
								//for the 1.8 release!
								//I changed Attempt::ID to valueA and added a few things!
								if($r->valueA > 0)
								{
									//$r->data = array('attemptID' => $r->valueA);
									$r->attemptData = $AM->getAttemptDetails($r->valueA);
									//$r->attemptID = $r->valueA;
									//trace($r);
								}
								if(isset($prevPageView) && is_object($prevPageView))
								{
									$prevPageView->viewTime = $r->{\cfg_obo_Track::TIME} - $prevPageView->createTime;
									unset($prevPageView);
								}
								
								// if this is the assessment section AND the assessment uses randomization or alternate questions, get the questions in order
								// trace($r);
								if(array_search($curSection, $sectionNames) == 3  &&  ($lo->aGroup->rand == 1  ||  $lo->aGroup->allowAlts == 1) && $r->{\cfg_obo_Track::A} > 0 )
								{
									$currentAttemptOrder = $AM->filterQuestionsByAttempt($lo->aGroup->kids, $r->{\cfg_obo_Track::A});
								}
								else
								{
									unset($currentAttemptOrder);
								}
							}
							$thisVisit[] = $r;
						}
						break;

					case 'EndAttempt':
						if($loFound)
						{
							$sectionTime[$curSection] += $r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
							
							if(!$totalsOnly && isset($prevPageView) && is_object($prevPageView))
							{
								$prevPageView->viewTime = $r->{\cfg_obo_Track::TIME} - $prevPageView->createTime;
								unset($prevPageView);
							}
						}
						break;
					case 'SubmitQuestion':
						if($loFound)
						{					
							if(!$totalsOnly)
							{
								$secNum = array_search($curSection, $sectionNames);
								$parentGroup = $secNum==2 ? $lo->pGroup->kids : $lo->aGroup->kids;
								$r->score = 0;
								$question = 0;
								$qIndex =  '?';
								$aIndex = '?'; 
								foreach($parentGroup AS $key => $qu)
								{
									if($qu->questionID == $r->{\cfg_obo_Track::QID})
									{
										$question = $qu;
										$qIndex =  $key+1; 
										break; // question located
									}
								}
								$answer = 0;

								if($question)
								{
									// locate answer given if possible
									switch($question->itemType)
									{
										case 'MC':
											foreach($question->answers AS $key=> $a)
											{
												if($a->answerID == $r->{\cfg_obo_Track::ANSWER})
												{
													$aIndex = $key+1;
													$answer = $a;
													break;
												}
											}
											break;
										case 'QA':
											foreach($question->answers AS $key => $a)
											{
												if($a->answer == $r->{\cfg_obo_Track::ANSWER})
												{
													$aIndex = $key+1;
													$answer = $a;
													break;
												}
											}
											break;
										case 'Media':
											break;

									}
								}

								$r->page = $qIndex;
								if(is_object($answer))
								{
									$r->score = $answer->weight;
								}
								$r->answerIndex = $aIndex;
							}
							$sectionTime[$curSection] += $r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
						}
						break;

					case 'SubmitMedia':
						if($loFound)
						{
							if(!$totalsOnly)
							{
								$secNum = array_search($curSection, $sectionNames);
								$parentGroup = $secNum==2 ? $lo->pGroup->kids : $lo->aGroup->kids;
								$question = 0;
								$qIndex =  '?';
								$aIndex = '?'; 
								foreach($parentGroup AS $key => $qu)
								{
									if($qu->questionID == $r->{\cfg_obo_Track::QID})
									{
										$question = $qu;
										$qIndex =  $key+1; 
										break; // question located
									}
								}
						
								$r->score = $r->{\cfg_obo_Track::SCORE};
								$r->page = $qIndex;
							}
							$sectionTime[$curSection] += $r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
						}
						break;
					case 'MediaRequested':
						$sectionTime[$curSection] += $r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
						$thisVisit[] = $r;
						break;
					default:
						if(isset($thisVisit)){
							$sectionTime[$curSection] += $r->{\cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
						}
						break;

				}
				$tmpUID = $r->{\cfg_core_User::ID};
				unset($r->{\cfg_core_User::ID});
				unset($r->{\cfg_obo_Instance::ID});
				if(isset($outOfTime) && $outOfTime == true)
				{
					break;
				}				
			}
			
			if(count($thisVisit) > 1)
			{
				$sectionTime['total'] += ($thisVisit[count($thisVisit) - 1]->createTime - $thisVisit[0]->createTime);
			}
			$sectionTime['other'] = $sectionTime['total'] - $sectionTime['overview'] - $sectionTime['content'] - $sectionTime['practice'] - $sectionTime['assessment'];
			$overallSectionTime['total'] += $sectionTime['total'];
			$overallSectionTime['overview'] += $sectionTime['overview'];
			$overallSectionTime['content'] += $sectionTime['content'];
			$overallSectionTime['practice'] += $sectionTime['practice'];
			$overallSectionTime['assessment'] += $sectionTime['assessment'];
			$pageViews['content']['unique'] = count($pageViews['content'])-2;	
			$pageViews['practice']['unique'] = count($pageViews['practice'])-2;	
			$pageViews['assessment']['unique'] = count($pageViews['assessment'])-2;
			$pageViews['unique'] = $pageViews['content']['unique'] + $pageViews['practice']['unique'] + $pageViews['assessment']['unique'];
			if(!$totalsOnly && isset($lo))
			{
				$visits[] = array('instID' => $curInst, 'userID' => $tmpUID, 'visitID' => $thisVisit[0]->visitID, 'loID' => $thisVisit[0]->loID , 'createTime' => $thisVisit[0]->createTime, 'sectionTime' => $sectionTime, 'pageViews' => $pageViews, 'logs' => $thisVisit);
			}
			
		}
		
		$return = array();
		$return['numRecords'] = $this->DBM->fetch_num($query);
		$return['sectionTime'] = $overallSectionTime;
		$overallPageViews['content']['unique'] = count($overallPageViews['content'])-2;	
		$overallPageViews['practice']['unique'] = count($overallPageViews['practice'])-2;	
		$overallPageViews['assessment']['unique'] = count($overallPageViews['assessment'])-2;
		$overallPageViews['unique'] = $overallPageViews['content']['unique'] + $overallPageViews['practice']['unique'] + $overallPageViews['assessment']['unique'];
		$return['pageViews'] = $overallPageViews;
		$return['visitLog'] = $visits;
		
		if(count($missingInstances) > 0)
		{
			trace('missing instances');
			trace(array_unique($missingInstances));
		}
		if(count($missingLOs) > 0)
		{
			trace('missing LOs');
			trace(array_unique($missingLOs));
		}
		return $return;
	}
	
	protected function deserializeData($data)
	{
		$data = preg_replace_callback('/(\d+):"(nm_los_tracking_)/', array( &$this, 'fixObject'), $data);
		$data = unserialize($data);
		unset($data->userID);
		unset($data->createTime);
		unset($data->instID);
		return $data;
	}
	
	public function fixObject($matches)
	{
		return ($matches[0]-7) . ':"\\obo\\log\\';
	}

	public function trackDeleteInstance($instID)
	{
		$this->track(new \obo\log\Trackable('DeleteInstance',0,$instID));
	}

	public function trackDeleteLO($loID, $numDeleted)
	{
		$this->track(new \obo\log\Trackable('DeleteLO',0,0, $loID, $numDeleted));
	}

	public function trackVisit()
	{
		$this->track(new \obo\log\Trackable('Visited'));
	}
	
	public function trackStartAttempt()
	{
		$this->track(new \obo\log\Trackable('StartAttempt',0,0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}
	
	public function trackEndAttempt()
	{
		$this->track(new \obo\log\Trackable('EndAttempt',0,0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}	
	
	public function trackImportScore(/*$currentAttemptID, $linkedAttemptID*/)
	{
		$this->track(new \obo\log\Trackable('ImportScore',0,0/* , $currentAttemptID, $linkedAttemptID*/));
	}

	public function trackResumeAttempt()
	{
		$this->track(new \obo\log\Trackable('ResumeAttempt',0,0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}

	public function trackSubmitQuestion($qGroupID, $questionID, $answer)
	{
		$this->track(new \obo\log\Trackable('SubmitQuestion', 0, 0, $questionID, $answer, (int)$qGroupID));
	}
	
	public function trackMergeUser($userIDFrom, $userIDTo)
	{
		$this->track(new \obo\log\Trackable('MergeUser', 0, 0, $userIDFrom, $userIDTo));
	}
	
	public function trackSubmitMedia($qGroupID, $questionID, $score)
	{
		$this->track(new \obo\log\Trackable('SubmitMedia', 0, 0, $questionID, (int)$score, (int)$qGroupID));
	}
	
	public function trackPageChanged($pageID, $section)
	{
		$this->track(new \obo\log\Trackable('PageChanged', 0, 0, $pageID, $section));
	}
	
	public function trackSectionChanged($section)
	{
		$this->track(new \obo\log\Trackable('SectionChanged', 0, 0, $section));
	}
	
	public function trackMediaDownloaded($mediaID)
	{
		$this->track(new \obo\log\Trackable('MediaDownloaded', 0, 0, $mediaID));
	}
	
	public function trackMediaRequested($mediaID)
	{
		$this->track(new \obo\log\Trackable('MediaRequest', 0, 0, $mediaID));
	}
	
	public function trackMediaRequestCompleted($mediaID)
	{
		$this->track(new \obo\log\Trackable('MediaRequestCompleted', 0, 0, $mediaID));
	}
	
	public function trackLoggedIn()
	{
		$this->track(new \obo\log\Trackable('LoggedIn', 0, 0));
	}
	
	public function trackLogInAttempt($userID, $userName, $code)
	{
		$this->track(new \obo\log\Trackable('LoginAttempt', 0, 0, $code, $userName));
	}
	
    public function trackLoggedOut()
	{
		$this->track(new \obo\log\Trackable('LoggedOut', 0, 0));
	}

    public function getInstanceTrackingData($userID = 0, $instID = 0)
    {
        if(!is_numeric($userID) || $userID < 1 || !is_numeric($instID) || $instID < 1)
		{
            return false;
        }   
		$SM = \obo\ScoreManager::getInstance();
        $trackingArr = new \stdClass();
		$trackingArr->prevScores = $SM->getAssessmentScores($instID, $userID);

        $qstr = "SELECT ".\cfg_obo_Track::TO." FROM 
				".\cfg_obo_Track::TABLE." 
				WHERE 
					`".\cfg_obo_Track::TYPE."`='PageChanged'
					AND `".\cfg_obo_Instance::ID."` = '?'
					AND `".\cfg_core_User::ID."` = '?'
					AND `".\cfg_obo_Track::IN."` = '".\obo\lo\Page::SECTION_CONTENT."'";
		if(!($q = $this->DBM->querySafe($qstr, $instID, $userID)))
		{
		    $this->DBM->rollback();
		    error_log("ERROR: getInstanceTrackingData query 2  ".mysql_error());
			return false;
		}

		$trackingArr->contentVisited = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$trackingArr->contentVisited[] = $r->{\cfg_obo_Track::TO};
		}
		$trackingArr->contentVisited = array_values(array_unique($trackingArr->contentVisited));

		return $trackingArr;
	}
	
}
?>