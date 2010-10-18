<?php

class nm_los_TrackingManager extends core_db_dbEnabled
{
	private static $instance;
	private $selectString;
	
	function __construct()
	{
		$this->defaultDBM();
		$this->selectString = cfg_core_User::ID.", `".cfg_obo_Track::TYPE."`, ".cfg_obo_Track::TIME.", ".cfg_obo_Instance::ID.", UNCOMPRESS(".cfg_obo_Track::DATA.") as data";
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
		if($trackable instanceof nm_los_tracking_Trackable)
		{
			$clone = clone $trackable;
			//remove unwanted data
			unset($clone->createTime);
			unset($clone->instID);
			unset($clone->userID);
			
			$qstr = "INSERT INTO `".cfg_obo_Track::TABLE."` (`".cfg_core_User::ID."`, `".cfg_obo_Track::TYPE."`, `".cfg_obo_Track::TIME."`, `".cfg_obo_Instance::ID."`, `".cfg_obo_Track::DATA."`) VALUES ('?', '".get_class($trackable)."', '?', '?', COMPRESS('?'))";
	
			if(!($q = $this->DBM->querySafe($qstr, $trackable->userID, $trackable->createTime, $trackable->instID, serialize($clone))))
			{
				$this->DBM->rollback();
				error_log("ERROR: track query 1".mysql_error());
				return false;
			}
			if(nm_los_Validator::isPosInt($trackable->userID) &&  nm_los_Validator::isPosInt($trackable->instID) )
			{
				
				core_util_Cache::getInstance()->clearInteractionsByInstanceAndUser($trackable->instID, $trackable->userID);
			}
			return true;
		}
		else if($trackable instanceof nm_los_Error)
		{
			if(isset($GLOBALS['CURRENT_INSTANCE_DATA']))
			{
				$instID = $GLOBALS['CURRENT_INSTANCE_DATA']['instID'];
			}
			else
			{
				$instID	 = 0;
			}
			
			$qstr = "INSERT INTO `".cfg_obo_Track::TABLE."` (`".cfg_core_User::ID."`, `".cfg_obo_Track::TYPE."`, `".cfg_obo_Track::TIME."`, `".cfg_obo_Instance::ID."`, `".cfg_obo_Track::DATA."`) VALUES ('{$_SESSION['userID']}', '?', '".time()."', '{$instID}', COMPRESS('?'))";
			if(!($q = $this->DBM->querySafe($qstr, $trackable->errorID, serialize($trackable->data) ) ) )
			{
   				error_log("ERROR: track query 2".mysql_error());
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
	/*
	NOT BEING USE USED 12/10/08
	public function getAllTrackingData($userID = 0, $instID = 0)
	{
		if(!is_numeric($userID) || $userID < 1 || !is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}

		$qstr = "SELECT ".self::SELECT_STRING." FROM ".self::table." WHERE `userID` = '?' AND `instID` = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $userID, $instID)))
		{
			$this->DBM->rollback();
			//echo "ERROR: getAllTrackingData";
			error_log("ERROR: getAllTrackingData".mysql_error());
			//exit;
			return false;
		}
		$trackingData = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$data = $this->deserializeData($r->data);
			$data->userID = (int)$r->userID;
			$data->type = $r->type;
			$data->createTime = (int)$r->createTime;
			$data->instID = (int)$r->instID;
			$trackingData[] = $data;
		}
		
		return $trackingData;
	}
	*/
	
	//@TODO only allow this for the creator of the instance and SU of course
	/* NOT USED 12/10/08
	public function getTrackingDataByInstance($instID = 0)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		$qstr = "SELECT ".self::SELECT_STRING." FROM ".self::table." WHERE `instID` = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
			$this->DBM->rollback();
			error_log("ERROR: getTrackingDataByInstance  ".mysql_error());
			return false;
		}
		$trackingData = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$data = $this->deserializeData($r->data);
			$data->userID = (int)$r->userID;
			$data->type = $r->type;
			$data->createTime = (int)$r->createTime;
			$data->instID = (int)$r->instID;
			$trackingData[] = $data;
		}
		
		return $trackingData;
	}
	*/

	public function getInteractionLogByInstance($instID=0)
	{
		
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			$permman = nm_los_PermissionsManager::getInstance();
			if( ! $permman->getUserPerm($instID, cfg_obo_Perm::TYPE_INSTANCE, cfg_obo_Perm::WRITE, $_SESSION['userID']) )
			{
				// check 2nd Perms system to see if they have write or own
				$pMan = nm_los_PermManager::getInstance();
				$perms = $pMan->getPermsForUserToItem($_SESSION['userID'], cfg_core_Perm::TYPE_INSTANCE, $instID);
				if(!is_array($perms) && !in_array(cfg_core_Perm::P_READ, $perms) && !in_array(cfg_core_Perm::P_OWN, $perms) )
				{
					
					
					return core_util_Error::getError(4);
				}
			}
		}
		
		
		$trackQ = "SELECT $this->selectString FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Instance::ID." = '?'	ORDER BY ".cfg_core_User::ID.", ".cfg_obo_Track::TIME;
		return $this->getInteractionLogs($this->DBM->querySafe($trackQ, $instID));
		
	}

	
	public function getInteractionLogByMaster($loID=0, $totalsOnly=true)
	{
		// must be SU 
		// this is likely to use too many resources to execute
		$q = $this->DBM->querySafe("SELECT ".cfg_obo_Instance::ID." FROM ".cfg_obo_Instance::TABLE." WHERE ".cfg_obo_LO::ID." = '?'", $loID);
		$loIDs = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$loIDs[] = $r->{cfg_obo_Instance::ID};
		}
		if(count($loIDs) > 0)
		{
			$trackQ = "SELECT $this->selectString FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Instance::ID." IN (" . implode(",", $loIDs) . ") ORDER BY ".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", ".cfg_obo_Track::TIME;
			return $this->getInteractionLogs($this->DBM->query($trackQ), $totalsOnly);
		}
	 	return array();
	}
	
	public function getInteractionLogTotals()
	{
		// must be user, instance owner, or SU
		$trackQ = "SELECT $this->selectString FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Instance::ID." != 0 AND ".cfg_obo_Track::TIME." > 1214193600 ORDER BY ".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", ".cfg_obo_Track::TIME;
		return $this->getInteractionLogs($this->DBM->query($trackQ), true, 10);
	}
	
	public function getInteractionLogByUser($userID=0)
	{
		// must be SU or this user
		$trackQ = "SELECT $this->selectString FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Instance::ID." != 0 AND ".cfg_core_User::ID." = '?'	ORDER BY ".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", ".cfg_obo_Track::TIME;		
		return $this->getInteractionLogs($this->DBM->querySafe($trackQ, $userID));
	}
	
	public function getInteractionLogByUserAndInstance($instID=0, $userID=0)
	{
		// must own instance or be user or SU	
		// check memcache
 		
		if($tracking = core_util_Cache::getInstance()->getInteractionsByInstanceAndUser($instID, $userID))
		{
			return $tracking;
		}
		
		
		$trackQ = "SELECT $this->selectString FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Instance::ID." = '?' AND ".cfg_core_User::ID." = '?' ORDER BY ".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", ".cfg_obo_Track::TIME;
		$return = $this->getInteractionLogs($this->DBM->querySafe($trackQ, $instID, $userID));
		
		core_util_Cache::getInstance()->setInteractionsByInstanceAndUser($instID, $userID, $return);
		
		return $return;	
	}
	
	public function getInteractionLogByVisit($vid=0)
	{
		// must be user, instance owner, or SU
		
	}

	protected function getInteractionLogs($query, $totalsOnly=false)
	{

		if($query)
		{
			$AM = nm_los_AttemptsManager::getInstance();;
			$los = array();
			$visits = array();
			$curSection = 0;
			$curInst = 0; // track cur instance to keep db hits low 
			$overallSectionTime = array('overview' => 0, 'content' => 0, 'practice' => 0, 'assessment' => 0, 'total' => 0);
			$overallPageViews  = array('content' => array('total'=>0,'unique'=>0), 'practice' => array('total'=>0,'unique'=>0), 'assessment' => array('total'=>0,'unique'=>0));
			$sectionNames = array('overview', 'content', 'practice', 'assessment');
			while($r = $this->DBM->fetch_obj($query))
			{
				switch($r->{cfg_obo_Track::TYPE})
				{
					case 'nm_los_tracking_Visited':
						// print and tally totals for previous visit
						$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
						if(isset($thisVisit))
						{
							$sectionTime['total'] += ($thisVisit[count($thisVisit) - 1]->createTime - $thisVisit[0]->createTime);
							$sectionTime['other'] = $sectionTime['total'] - $sectionTime['overview'] - $sectionTime['content'] - $sectionTime['practice'] - $sectionTime['assessment'];
							$overallSectionTime['total'] += $sectionTime['total'];
							$overallSectionTime['overview'] += $sectionTime['overview'];
							$overallSectionTime['content'] += $sectionTime['content'];
							$overallSectionTime['practice'] += $sectionTime['practice'];
							$overallSectionTime['assessment'] += $sectionTime['assessment'];
							if(!$totalsOnly)
							{
								$visits[] = array('instID' => $curInst, 'userID' => $tmpUID, 'visitID' => $thisVisit[0]->data->visitID, 'loID' => $lo->loID , 'createTime' => $thisVisit[0]->createTime, 'sectionTime' => $sectionTime, 'pageViews' => $pageViews, 'logs' => $thisVisit);	
							}
						}

						// fetch the LO if we havn't already
						if($curInst != $r->{cfg_obo_Instance::ID})
						{
								
							$loQ = "SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_Instance::TABLE." WHERE ".cfg_obo_Instance::ID."='?'";
							$loR = $this->DBM->fetch_obj($this->DBM->querySafe($loQ, $r->{cfg_obo_Instance::ID}));
							// the inst wasnt in lo_instances, check for them in the deleted table
							if(!is_object($loR))
							{
								$loQ = "SELECT ".cfg_obo_LO::ID." FROM lo_instances_deleted WHERE ".cfg_obo_LO::ID."='?'";
								$loR = $this->DBM->fetch_obj($this->DBM->querySafe($loQ, $r->{cfg_obo_Instance::ID}));
							}
							if(is_object($loR))
							{
								// check to make sure we havn't opened this one before
								if(!isset($los[$loR->{cfg_obo_LO::ID}]))
								{
									if( $loR->{cfg_obo_LO::ID} != $lo->loID)
									{
										$lo = new nm_los_LO();
										$loFound = $lo->dbGetFull($this->DBM, $loR->{cfg_obo_LO::ID});
									}
									else
									{
										$lo = new nm_los_LO();
										$loFound = $lo->dbGetFull($this->DBM, $loR->{cfg_obo_LO::ID});	
									}
									$los[$loR->{cfg_obo_LO::ID}] =  $lo;
									$curInst = $r->{cfg_obo_Instance::ID};
								}
								else // already loaded, just use the one in memory
								{
									$lo = $los[$loR->{cfg_obo_LO::ID}];
									$curInst = $r->{cfg_obo_Instance::ID};
								}
							}
							else
							{
								trace('LO for instance ' .$r->{cfg_obo_Instance::ID} . ' was missing when parsing the tracking table.', true);
							}
						}
						// Initialize this visit
						$prevPageView = false;
						$thisVisit = array();
						$sectionTime = array('overview' => 0, 'content' => 0, 'practice' => 0, 'assessment' => 0, 'total' => 0);
						$pageViews = array('content' => array('total' => 0,'unique' => 0), 'practice' => array('total'=>0,'unique'=>0), 'assessment' => array('total'=>0,'unique'=>0));
					
						//$r->data = $this->deserializeData($r->data);
						$thisVisit[] = $r;
						$curSection = $sectionNames[0];

						break;

					case 'nm_los_tracking_SectionChanged':
						if($loFound)
						{
							$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
							$sectionTime[$curSection] += ($r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime);
							//if((int)$r->data->to != 3) 
							$curSection = $sectionNames[(int)$r->{cfg_obo_Track::DATA}->to];
							$thisVisit[] = $r;
						
							if(!$totalsOnly && isset($prevPageView) && is_object($prevPageView) )
							{
								$prevPageView->viewTime = $r->{cfg_obo_Track::TIME} - $prevPageView->createTime;
								unset($prevPageView);
							}
						}
						break;

					case 'nm_los_tracking_PageChanged':
						if($loFound)
						{					
							$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
							$toSection = (int) ($r->{cfg_obo_Track::DATA}->in == 0 ? 1 : $r->{cfg_obo_Track::DATA}->in);
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
												if($page->pageID == $r->{cfg_obo_Track::DATA}->to)
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
												if($page->questionID == $r->{cfg_obo_Track::DATA}->to)
												{
													$r->qType = $page->itemType;
													//if(!$totalsOnly) $r->qText = substr(($page->itemType == 'M' ? preg_replace("/[\n\r]/","", strip_tags($page->media[0]->title)) : preg_replace("/[\n\r]/","",strip_tags($page->items[0]->{cfg_obo_Page::ITEM_DATA}))), 0, 120);
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
												
												if($page->questionID == $r->{cfg_obo_Track::DATA}->to)
												{
													if($page->questionIndex) $r->altIndex = $altIndex;
													$r->normalIndex = $realQNum;
													$r->qType = $page->itemType;
													if(!$totalsOnly) $r->qText = substr(($page->{cfg_obo_Question::TYPE} == 'Media' ? preg_replace("/[\n\r]/","", strip_tags($page->items[0]->media[0]->{cfg_obo_Media::TITLE})) : preg_replace("/[\n\r]/","", strip_tags($page->items[0]->{cfg_obo_Page::ITEM_DATA}))), 0, 120);
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
													if($page->questionID == $r->{cfg_obo_Track::DATA}->to)
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
							if(nm_los_Validator::isPosInt($pageIndex))
							{
								$pageViews[$curSection][$pageIndex] = isset($pageViews[$curSection][$pageIndex]) ? $pageViews[$curSection][$pageIndex] + 1 : 1;
								$pageViews[$curSection]['total'] = $pageViews[$curSection]['total'] + 1;
								$thisPageID = ($page instanceOf nm_los_Question ? $page->questionID : $page->pageID);
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

					case 'nm_los_tracking_StartAttempt':
						if($loFound)
						{
							if(!$totalsOnly)
							{
								$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
								$r->attemptData = $AM->getAttemptDetails($r->{cfg_obo_Track::DATA}->attemptID);
								if(isset($prevPageView) && is_object($prevPageView))
								{
									$prevPageView->viewTime = $r->{cfg_obo_Track::TIME} - $prevPageView->createTime;
									unset($prevPageView);
								}
								
								// if this is the assessment section AND the assessment uses randomization or alternate questions, get the questions in order
								if(array_search($curSection, $sectionNames) == 3  &&  ($lo->aGroup->rand == 1  ||  $lo->aGroup->allowAlts == 1) )
								{
									$currentAttemptOrder = $AM->filterQuestionsByAttempt($lo->aGroup->kids, $r->{cfg_obo_Track::DATA}->attemptID);
								}
								else
								{
									unset($currentAttemptOrder);
								}
							}
							$thisVisit[] = $r;
						}
						break;

					case 'nm_los_tracking_EndAttempt':
						if($loFound)
						{
							$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
							$sectionTime[$curSection] += $r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
							
							if(!$totalsOnly && isset($prevPageView) && is_object($prevPageView))
							{
								$prevPageView->viewTime = $r->{cfg_obo_Track::TIME} - $prevPageView->createTime;
								unset($prevPageView);
							}
						}
						break;
					case 'nm_los_tracking_SubmitQuestion':
						if($loFound)
						{					
							if(!$totalsOnly)
							{
								$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});						// find question in lo
								$secNum = array_search($curSection, $sectionNames);
								$parentGroup = $secNum==2 ? $lo->pGroup->kids : $lo->aGroup->kids;
								$r->score = 0;
								$question = 0;
								$qIndex =  '?';
								$aIndex = '?'; 
								foreach($parentGroup AS $key => $qu)
								{
									if($qu->questionID == $r->{cfg_obo_Track::DATA}->questionID)
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
												if($a->answerID == $r->{cfg_obo_Track::DATA}->answer)
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
												if($a->answer == $r->{cfg_obo_Track::DATA}->answer)
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
							$sectionTime[$curSection] += $r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
						}
						break;

					case 'nm_los_tracking_SubmitMedia':
						if($loFound)
						{
							if(!$totalsOnly)
							{
								$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
								// if this log is a repeat of the previous log dont store it (submitMedia is sometimes sent more then it should be)
								if($thisVisit[count($thisVisit)-1]->itemType == 'nm_los_tracking_SubmitMedia')
								{
									$prevLog = $thisVisit[count($thisVisit)-1];
									if($prevLog->data->score == $r->{cfg_obo_Track::DATA}->score && $prevLog->{cfg_obo_Track::DATA}->questionID == $r->{cfg_obo_Track::DATA}->questionID && $r->{cfg_obo_Track::TIME} == $prevLog->createTime)
									{
										break;
									}
								}						
								$secNum = array_search($curSection, $sectionNames);
								$parentGroup = $secNum==2 ? $lo->pGroup->kids : $lo->aGroup->kids;
								$question = 0;
								$qIndex =  '?';
								$aIndex = '?'; 
								foreach($parentGroup AS $key => $qu)
								{
									if($qu->questionID == $r->{cfg_obo_Track::DATA}->questionID)
									{
										$question = $qu;
										$qIndex =  $key+1; 
										break; // question located
									}
								}
						
								$r->score = $r->{cfg_obo_Track::DATA}->score;
								$r->page = $qIndex;
							}
							$sectionTime[$curSection] += $r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
							$thisVisit[] = $r;
						}
						break;
					case 'nm_los_tracking_MediaRequested':
						$sectionTime[$curSection] += $r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
						$r->{cfg_obo_Track::DATA} = $this->deserializeData($r->{cfg_obo_Track::DATA});
						$thisVisit[] = $r;
						break;
					default:
						$sectionTime[$curSection] += $r->{cfg_obo_Track::TIME} - $thisVisit[count($thisVisit) - 1]->createTime;
						unset($r->{cfg_obo_Track::DATA}); // dont allow error tracing to get to the user
						$thisVisit[] = $r;
						break;

				}
				$tmpUID = $r->{cfg_core_User::ID};
				unset($r->{cfg_core_User::ID});
				unset($r->{cfg_obo_Instance::ID});
				if(isset($outOfTime) && $outOfTime == true)
				{
					break;
				}				
			}

			$sectionTime['total'] += ($thisVisit[count($thisVisit) - 1]->createTime - $thisVisit[0]->createTime);
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
			if(!$totalsOnly)
			{
				$visits[] = array('instID' => $curInst, 'userID' => $tmpUID, 'visitID' => $thisVisit[0]->data->visitID, 'loID' => $lo->loID , 'createTime' => $thisVisit[0]->createTime, 'sectionTime' => $sectionTime, 'pageViews' => $pageViews, 'logs' => $thisVisit);
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
		//$return['lastInstanceID'] = $r->{cfg_obo_Instance::ID};
		return $return;
	}
	
	protected function deserializeData($data)
	{
		$data = unserialize($data);
		unset($data->userID);
		unset($data->createTime);
		unset($data->instID);
		return $data;
	}

	public function trackCleanOrphans($runTime)
	{
		$this->track(new nm_los_tracking_CleanOrphans(0, 0, 0, $runTime));
	}

	public function trackDeleteInstance($instID)
	{
		$this->track(new nm_los_tracking_DeleteInstance(0, 0, $instID));
	}

	public function trackDeleteLO($loID, $numDeleted)
	{
		$this->track(new nm_los_tracking_DeleteLO(0, 0, 0, $loID, $numDeleted));
	}

	public function trackVisit()
	{
		$this->track(new nm_los_tracking_Visited(0, 0, 0, $GLOBALS['CURRENT_INSTANCE_DATA']['visitID']));
	}
	
	public function trackStartAttempt()
	{
		$this->track(new nm_los_tracking_StartAttempt(0, 0, 0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}
	
	public function trackEndAttempt()
	{
		$this->track(new nm_los_tracking_EndAttempt(0, 0, 0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}	
	
	public function trackImportScore()
	{
		$this->track(new nm_los_tracking_ImportScore(0,0,0,$GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}

	public function trackResumeAttempt()
	{
		$this->track(new nm_los_tracking_ResumeAttempt(0, 0, 0, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']));
	}

	public function trackSubmitQuestion($qGroupID, $questionID, $answer)
	{
		$this->track(new nm_los_tracking_SubmitQuestion(0, 0, 0, (int)$qGroupID, (int)$questionID, $answer));
	}
	
	public function trackMergeUser($userIDFrom, $userIDTo)
	{
		$this->track(new nm_los_tracking_MergeUser($userIDTo, 0, 0, $userIDFrom, $userIDTo));
	}
	
	public function trackSubmitMedia($qGroupID, $questionID, $score)
	{
		$this->track(new nm_los_tracking_SubmitMedia(0, 0, 0, (int)$qGroupID, (int)$questionID, (int)$score));
	}
	
	public function trackPageChanged($pageID, $section)
	{
		$this->track(new nm_los_tracking_PageChanged(0, 0, 0, $pageID, $section));
	}
	
	public function trackSectionChanged($section)
	{
		$this->track(new nm_los_tracking_SectionChanged(0, 0, 0, $section));
	}
	
	public function trackMediaDownloaded($mediaID)
	{
		$this->track(new nm_los_tracking_MediaDownloaded(0, 0, 0, $mediaID));
	}
	
	public function trackMediaRequested($mediaID)
	{
		$this->track(new nm_los_tracking_MediaRequested(0, 0, 0, $mediaID));
	}
	
	public function trackMediaRequestCompleted($mediaID)
	{
		$this->track(new nm_los_tracking_MediaRequestedCompleted(0, 0, 0, $mediaID));
	}
	
	public function trackNextPreviousUsed($dir)
	{
	    $this->track(new nm_los_tracking_NextPreviousUsed(0, 0, 0, $dir));
	}
	
	public function trackLoggedIn()
	{
		$this->track(new nm_los_tracking_LoggedIn(0, 0, 0));
	}
	
	public function trackLogInAttempt($userID, $userName, $code)
	{
		$this->trace(new nm_los_tracking_LoginAttempt($userID,0,0,$userName,$code));
	}
	
    public function trackLoggedOut()
	{
		$this->track(new nm_los_tracking_LoggedOut(0, 0, 0));
	}


    public function getInstanceTrackingData($userID = 0, $instID = 0)
    {
        if(!is_numeric($userID) || $userID < 1 || !is_numeric($instID) || $instID < 1)
		{
            return false;
        }   
		$SM = nm_los_ScoreManager::getInstance();
        $trackingArr = new stdClass();
		$trackingArr->prevScores = $SM->getAssessmentScores($instID, $userID);

        $qstr = "SELECT UNCOMPRESS(".cfg_obo_Track::DATA.") as data FROM ".cfg_obo_Track::TABLE." WHERE `".cfg_obo_Track::TYPE."`='nm_los_tracking_PageChanged' AND `".cfg_obo_Instance::ID."` = '?' AND `".cfg_core_User::ID."` = '?'";
		if(!($q = $this->DBM->querySafe($qstr, $instID, $userID)))
		{
		    $this->DBM->rollback();
		    error_log("ERROR: getInstanceTrackingData query 2  ".mysql_error());
			return false;
		}

		$trackingArr->contentVisited = array();
		while($r = $this->DBM->fetch_obj($q))
		{
		    $data = $this->deserializeData($r->{cfg_obo_Track::DATA});
		    if($data->in == nm_los_tracking_PageChanged::CONTENT)
			{
                $trackingArr->contentVisited[] = $data->to;
			}
		}
		$trackingArr->contentVisited = array_values(array_unique($trackingArr->contentVisited));

        return $trackingArr;
    }
	
}
?>