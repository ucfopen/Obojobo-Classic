<?php
class plg_UCFCourses_UCFCoursesAPI extends core_plugin_PluginAPI
{

	const PUBLIC_FUNCTION_LIST = ''; // dont allow any direct calls
	private static $instance;
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	/**
	 * Sends an HTTP POST to the desired URL.  Requires PECL_HTTP http://pecl.php.net/package/pecl_http
	 *
	 * @param string $url 	Full URL to request
	 * @param array $postVars 	associative array of post variables to send
	 * @return array 'responseCode' is the http response code (ie 200 or 404) 'body' is the body of the response
	 * @author Ian Turgeon
	 */
	protected function send($url, $postVars=false)
	{

		$request = new HttpRequest($url, HTTP_METH_POST);
		if(is_array($postVars))
		{
			$request->addPostFields($postVars);
		}
		$response = $request->send();
		return array('responseCode' =>  $request->getResponseCode(), 'body' => $request->getResponseBody());
	}
	
	
	/**
	 *   --------------Retrieving Instructor Sections---------------
     *
	 *   URL: /obojobo/v1/client/<INSTRUCTOR_NETWORK_ID>/instructor/sections
     *
	 *   On Success
     *
	 *   Returns a JSON object in the data attribute with the following attributes: ps_only, wc_only, and related. Those attributes are guaranteed to be lists of items with the following forms:
     *
	 *   ps_only item attributes: prefix, number, section, term, reg_key, title
	 *   wc_only item attribtues: course, section, learning_context_id
	 *   related item attributes: ps (contains ps_only item attributes), wc (contains wc_only item attributes)
     *
	 *   ps_only contains PoepleSoft sections that the specified instructor owns that do not correspond to any Webcourses section
	 *   wc_only contains Webcourses sections that the specified instructor is enrolled in as Section Instructor that does not have any corresponding PeopleSoft section
	 *   related contains PeopleSoft and Webcourses sections that are linked
	 *   Possible Errors
     *
	 *   0, User does not exist in PeopleSoft
	 *   1, User does not exist in Webcourses
	 *
	 * @param string $NID Webcourses Vista user id of the Section Instructor (typically NID)
	 * @return void
	 * @author Ian Turgeon
	 */
	public function getCourses()
	{
		$AM = core_auth_AuthManager::getInstance();
		if($AM->verifySession())
		{
			$userID = $AM->getSessionUserID();
			// current user must have a UCF NID to get course data
			if(!$this->isNIDAccount($userID))
			{
				return false;
			}
			$NID = $AM->getUserName($userID);
			
			$result = $this->sendGetCourseRequest($NID);
			return  $result;
		}
		else
		{
			return core_util_Error::getError(2);
		}
	}
	
	
	public function getInstanceCourseData($insID)
	{
		$courseData = new stdClass();
		
		$qstr = "SELECT * FROM ". cfg_plugin_UCFCourses::MAP_TABLE . " WHERE ". cfg_obo_Instance::ID . " = '?'";
		$q = $this->DBM->querySafe($qstr, $insID);
		if($r = $this->DBM->fetch_obj($q))
		{
			if($r->{cfg_plugin_UCFCourses::MAP_COL_ID} > 0)
			{
				$courseData->type = 'sync';
				$courseData->gradeColumn = $r->{cfg_plugin_UCFCourses::MAP_COL_NAME};
			}
			else
			{
				$courseData->type = 'linked';
			}
			
			$courseData->id = $r->{cfg_plugin_UCFCourses::MAP_SECTION_ID};
			$coursePlugin->plugin = 'UCFCourses';
		}
		else
		{
			$courseData->type = 'none';
		}
		return $courseData;
	}
	
	protected function sendGetCourseRequest($NID)
	{
		//$NID = 'wink';
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/client/'.$NID.'/instructor/sections?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		
		$result = $this->send($REQUESTURL);
	
		// check for http response code of 200
		if($result['responseCode'] != 200)
		{
			return array('courses' => array(), 'errors' => array(core_util_Error::getError(1008, 'HTTP RESPONSE: ' . $result['responseCode'])));
		}
	
		$response = $this->decodeJSON($result['body']);
		
		$courses = $response->data;
		$errors = $this->parseErrors($response->errors);
		
		// add semester info when availible
		if(is_array($courses))
		{
			foreach($courses as $course)
			{
				if($course->type == 'ps_only' || $course->type == 'related')
				{
					$semester = $this->term_code2term_string($course->ps_term);
					$course->semester = $semester['semester'];
					$course->year = $semester['year'];
					$course->start = $semester['start'];
					$course->end = $semester['end'];
				}
			}
		}

		return array('courses' => $courses, 'errors' => $errors);
	}
	
	protected function isNIDAccount($userID)
	{
		$AM = core_auth_AuthManager::getInstance();
		$val = $AM->getAuthModuleForUserID($userID);
		if($val instanceof plg_UCFAuth_UCFAuthModule)
		{
			return true;
		}
		return false;
	}
	
	/**
	 *	--------------------- Creating a Grade Book Column ------------------------
     *
	 *	URL: /obojobo/v1/webcourses/gradebook/column/create
	 *	Required POST params:
     *
	 *	wc_instructor_id: Webcourses Vista user id of the Section Instructor (typically NID)
	 *	wc_section_id: Webcourses Vista learning context id
	 *	column_name: desired grade book column name will be prefixed with 'obo:'
	 *	On Success
     *
	 *	In msgs JSON attribute:
     *
	 *	0, Gradebook column created successfully
	 *	In data JSON attribute:
     *
	 *	column_id (This is the ID of the created grade book column)
	 *	Possible Errors
     *
	 *	In errors JSON attribute:
     *
	 *	0, User does not exist in PeopleSoft
	 *	1, User does not exist in Webcourses
	 *	2, User is not instructor in specified section
	 *	3, Failed to initialize Webcourses Vista session
	 *	6, Unable to fetch gradebook columns
	 *	4, Gradebook column with specified name already exists
	 *	7, Unable to create gradebook column
	 *
	 * @param string $instID	Instance ID for the instance
	 * @param string $sectionID 	Webcourses Vista learning context id
	 * @param string $columnName 	desired grade book column name will be prefixed with 'obo:'
	 * @return void
	 * @author Ian Turgeon
	 */
	public function createColumn($instID, $sectionID, $columnName)
	{
		// TODO: require user to have rights to the instance
		$AM = core_auth_AuthManager::getInstance();
		if($AM->verifySession())
		{
			$userID = $AM->getSessionUserID();
			// current user must have a UCF NID to create a course column
			if(!$this->isNIDAccount($userID))
			{
				return false;
			}
			
			// Do they have the rights to edit the instance?
			$IM = nm_los_InstanceManager::getInstance();
			if(!$IM->userCanEditInstance($userID, $instID))
			{
				return false;
			}
			
			$NID = $AM->getUserName($userID);
			
			// send request
			$result = $this->sendCreateColumnRequest($NID, $sectionID, $columnName);

			// it worked? 
			if($result['columnID'] > 0)
			{
				$sql = "INSERT INTO
							".cfg_plugin_UCFCourses::MAP_TABLE."
						SET
							".cfg_obo_Instance::ID." = '?',
							".cfg_plugin_UCFCourses::MAP_SECTION_ID." = '?',
							".cfg_core_User::ID." = '?',
							".cfg_plugin_UCFCourses::MAP_COL_ID." = '?',
							".cfg_plugin_UCFCourses::MAP_COL_NAME." = '?'
						ON DUPLICATE KEY UPDATE
							".cfg_plugin_UCFCourses::MAP_SECTION_ID." = '?',
							".cfg_core_User::ID." = '?',
							".cfg_plugin_UCFCourses::MAP_COL_ID." = '?',
							".cfg_plugin_UCFCourses::MAP_COL_NAME." = '?'";
							
				$this->DBM->querySafe($sql, $instID, $sectionID, $userID, $result['columnID'], 'obo:' . $columnName, $sectionID, $userID, $result['columnID'], 'obo:' . $columnName);
			}
			
			return $result;
		}
		else
		{
			return core_util_Error::getError(1);
		}
	}

	/**
	 * Links a Instance to a course *** REMOVES WEBCOURSES COLUMN LINKAGE ****  You must call create Column again to link to a course column
	 *
	 * @param string $instID 
	 * @param string $sectionID 
	 * @return void
	 * @author Ian Turgeon
	 */
	public function setInstanceCourseLink($instID, $sectionID)
	{
		// TODO: require user to have rights to the instance
		$AM = core_auth_AuthManager::getInstance();
		if($AM->verifySession())
		{
			$userID = $AM->getSessionUserID();
			// current user must have a UCF NID to create a course column
			if(!$this->isNIDAccount($userID))
			{
				return false;
			}
			
			// Do they have the rights to edit the instance?
			$IM = nm_los_InstanceManager::getInstance();
			if(!$IM->userCanEditInstance($userID, $instID))
			{
				return false;
			}
			
			$sql = "INSERT INTO
						".cfg_plugin_UCFCourses::MAP_TABLE."
					SET
						".cfg_obo_Instance::ID." = '?',
						". cfg_plugin_UCFCourses::MAP_SECTION_ID." = '?',
						". cfg_core_User::ID." = '?',
						".cfg_plugin_UCFCourses::MAP_COL_ID." = '',
						".cfg_plugin_UCFCourses::MAP_COL_NAME." = ''
					ON DUPLICATE KEY UPDATE 
						". cfg_plugin_UCFCourses::MAP_SECTION_ID." = '?',
						". cfg_core_User::ID." = '?',
						".cfg_plugin_UCFCourses::MAP_COL_ID." = '',
						".cfg_plugin_UCFCourses::MAP_COL_NAME." = ''";
			
			return (bool) $this->DBM->querySafe($sql, $instID, $sectionID, $userID, $sectionID, $userID);
		}
		else
		{
			return core_util_Error::getError(1);
		}
	}

	
	protected function sendCreateColumnRequest($NID, $sectionID, $columnName)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/create?app_key='.AppCfg::UCFCOURSES_APP_KEY;

		$postVars = array('wc_instructor_id' => $NID, 'wc_section_id' => $sectionID, 'column_name' => $columnName);
		
		$result = $this->send($REQUESTURL, $postVars);
	
		// check for http response code of 200
		if($result['responseCode'] != 200)
		{
			return array('courses' => array(), 'errors' => array(core_util_Error::getError(1008, 'HTTP RESPONSE: ' . $result['responseCode'])));
		}
	
		$response = $this->decodeJSON($result['body']);

		$columnID = 0;
		// column created successfully 
		if(isset($response->data->column_id) && $response->data->column_id > 0)
		{
			$columnID =  $response->data->column_id;
		}
		// column not created, return errors or just return what we got
		$errors = $this->parseErrors($response->errors);
		
		return array('columnID' => $columnID, 'errors' => $errors);
	}
	
	
	/**
	 *	--------------------- Inserting a grade into webcourses -----------------------
	 * 
	 *	Updating Grade Book Column Value
	 *	URL: /obojobo/v1/webcourses/gradebook/column/update
	 *	Required POST Params:
     *
	 *	wc_instructor_id: Webcourses Vista user id of the Section Instructor (typically NID)
	 *	wc_student_id: Webcourses Vista user id of the Section Student (typically NID)
	 *	wc_section_id: Webcourses Vista learning context id
	 *	score: numeric between 0 and 100
	 *	On Success
     *
	 *	In msgs JSON attribute:
     *
	 *	1, Gradebook column value set successfully
	 *	Possible Errors
     *
	 *	In errors JSON attribute:
     *
	 *	10, Specified score is incorrect format or out of acceptable range (OBOJOBO)
	 *	0, User does not exist in PeopleSoft
	 *	1, User does not exist in Webcourses
	 *	3, Failed to initialize Webcourses Vista session
	 *	12, Obojobo Gradebook column for this section does not exist or is unknown
	 *	8, Unable to fetch section student IDs
	 *	9, Unable to fetch member description for specified member
	 *	11, Specified user is not a member of section gradebook
	 *	18, Section specified does not exist
	 *	
	 * @param int $instID 	Obojobo Learning Object Instance ID for the instance scored
	 * @param int $studentUserID 	Webcourses Vista user id of the Section Student (typically NID)
	 * @param string $score 	numeric between 0 and 100
	 * @return void
	 * @author Ian Turgeon
	 */
	public function sendScore($instID, $studentUserID, $score)
	{
		// TODO: restrict this to not allow nm_los_API to call it
		$AM = core_auth_AuthManager::getInstance();
		if($AM->verifySession())
		{	
			// get the course data for the selected instance
			$sql = "SELECT * FROM ".cfg_plugin_UCFCourses::MAP_TABLE." WHERE ".cfg_obo_Instance::ID." = '?'";
			$q = $this->DBM->querySafe($sql, $instID);
			if(!$r = $this->DBM->fetch_obj($q))
			{
				// WHA! No column info availible
				trace("No column info availible: $instID, $studentUserID, $score", true);
				return false;
			}
			
			$instructorID = $r->{cfg_core_User::ID};
			$instructorNID = $AM->getUserName($instructorID);
			$sectionID = $r->{cfg_plugin_UCFCourses::MAP_SECTION_ID};
			$columnID = $r->{cfg_plugin_UCFCourses::MAP_COL_ID};
			$columnName = $r->{cfg_plugin_UCFCourses::MAP_COL_NAME};
			
			$currentUserID = $AM->getSessionUserID();
			$currentNID = $AM->getUserName($currentUserID);
			if(!$studentNID = $AM->getUserName($studentUserID))
			{
				// OH NOOS!  student user cant be found
				trace("Couldn't locate the student to send score: $instID, $studentUserID, $score", true);
				return false;
			}
			// student must be a NID user
			if(!$this->isNIDAccount($studentUserID))
			{
				trace("Student isnt a NID user: $instID, $studentUserID, $score", true);
				return false;
			}
			
			// if studentUserID isnt current user, make sure the current user has rights to the instance
			// this will either have to be called by the user
			if($studentUserID != $currentUserID)
			{
				$IM = nm_los_InstanceManager::getInstance();
				if(!$IM->userCanEditInstance($currentUserID, $instID))
				{
					return core_util_Error::getError(4);
				}
			}
			
			// Send the score set request
			$result = $this->sendScoreSetRequest($instructorNID, $studentNID, $sectionID, $columnID, $score);
			
			// log the result
			$this->logScoreSet($instID, $currentUserID, $studentUserID, $sectionID, $columnID, $columnName, $score, ($result['scoreSent'] === true) );

			return $result;
		}
		else // user isnt logged in
		{
			return core_util_Error::getError(1);
		}
	}
	
	protected function sendScoreSetRequest($instructorNID, $studentNID, $sectionID, $columnID, $score)
	{		
		// Begin the service request
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/update?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		
		$postVars = array('wc_instructor_id' => $instructorNID, 'wc_student_id' => $studentNID, 'wc_section_id' => $sectionID, 'column_id' => $columnID, 'score' => $score);
		
		$result = $this->send($REQUESTURL, $postVars);
	
		// check for http response code of 200
		if($result['responseCode'] != 200)
		{
			return array('courses' => array(), 'errors' => array(core_util_Error::getError(1008, 'HTTP RESPONSE: ' . $result['responseCode'])));
		}
	
		$response = $this->decodeJSON($result['body']);
		
		$scoreSent = false;
		// look to see if the msg was successfull
		if(isset($response->msgs[0]) && substr($response->msgs[0], 0, 1) == "1")
		{
			$scoreSent = true;
		}

		$errors = $this->parseErrors($response->errors);

		return array('scoreSent' => $scoreSent, 'errors' => $errors);
	}
	
	protected function logScoreSet($instID, $currentUserID, $studentUserID, $sectionID, $columnID, $columnName, $score, $success)
	{
		$time = time();
		core_util_Log::profile('webcourses_score_log', "'$instID','$time','$currentUserID','$studentUserID','$sectionID','$columnID','$columnName','$score','$success'\n");
		$sql = "INSERT INTO ".cfg_plugin_UCFCourses::LOG_TABLE." SET ".cfg_obo_Instance::ID." = '?', ".cfg_core_User::ID." = '?', ".cfg_plugin_UCFCourses::STUDENT." = '?', ".cfg_plugin_UCFCourses::TIME." = '?', ".cfg_plugin_UCFCourses::MAP_SECTION_ID." = '?', ".cfg_plugin_UCFCourses::MAP_COL_ID." = '?', ".cfg_plugin_UCFCourses::MAP_COL_NAME." = '?', ".cfg_plugin_UCFCourses::SCORE." = '?', ".cfg_plugin_UCFCourses::SUCCESS." ='?'
		ON DUPLICATE KEY UPDATE ".cfg_core_User::ID." = '?', ".cfg_plugin_UCFCourses::SCORE." = '?', ".cfg_plugin_UCFCourses::TIME." = '?', ".cfg_plugin_UCFCourses::SUCCESS." = '?'";
		$q = $this->DBM->querySafe($sql, $instID, $currentUserID, $studentUserID, $time, $sectionID, $columnID, $columnName, $score, (int)$success, /* on duplicate -> */ $currentUserID, $score, $time, $success);
	}
	
	public function getScoreLogsForInstance($instID)
	{
		$qstr = "SELECT ".cfg_core_User::ID.", ".cfg_plugin_UCFCourses::STUDENT.", ".cfg_plugin_UCFCourses::TIME.", ".cfg_plugin_UCFCourses::MAP_COL_NAME.", ".cfg_plugin_UCFCourses::SCORE.", ".cfg_plugin_UCFCourses::SUCCESS." FROM ".cfg_plugin_UCFCourses::LOG_TABLE." WHERE ".cfg_obo_Instance::ID." = '?'";
		
		$q = $this->DBM->querySafe($qstr, $instID);
		$result = $this->DBM->getAllRows($q);
		
		$scores = array();
		foreach($result AS $score)
		{
			$scores[$score->{cfg_plugin_UCFCourses::STUDENT}] = $score;
		}
		return $scores;
	}
	
	/**
	 * Loop through errors returned and create an error for each one
	 *
	 * @param array $errors 	Array of errors returned from the remote API
	 * @return Array/Boolean	Returns an array of error objects or false if there are none
	 * @author Ian Turgeon
	 */
	protected function parseErrors($errors)
	{
		// check for errors
		if(count($errors) > 0)
		{
			$returnErrors = array();
			// log each error
			foreach($errors AS $rError)
			{
				$rErrorNumber = explode(',', $rError, 1); // parse error code out of strings like "0, User does not exist in PeopleSoft"
				$code = false;
				switch($rErrorNumber[0])
				{
					case 0:
					case 1:
						$code = 7001;
						break;
					case 2:
						$code = 7002;
						break;
					case 3:
						$code = 7003;
						break;
					case 4:
						$code = 7005;
						break;
					case 6:
						$code = 7006;
						break;
					case 7:
						$code = 7007;
						break;
					case 8:
						$code = 7009;
						break;
					case 9:
						$code = 7010;
						break;
					case 10:
						$code = 7008;
						break;
					case 11:
						$code = 7011;
						break;
					case 12:
						$code = 7006;
						break;
					case 18:
						$code = 7012;
						break;
				}
				
				// code found
				if($code)
				{
					$returnErrors[] = core_util_Error::getError($code);
				}
				// code not found, use general error and return the error string
				else
				{
					$returnErrors[] = core_util_Error::getError(7013, $rError);
				}
			}
			return $returnErrors;
		}
		return false;
	}
	
	/**
	 * Convenience function to make sure bit integers are not screwed up by php, it converts them to strings
	 *
	 * @param string $json 	Raw JSON string
	 * @return object	Decoded JSON object/array with learning_context_id's and column_id's as strings instead of ints
	 * @author Ian Turgeon
	 */
	protected function decodeJSON($json)
	{
		// convert learning_context_id values as a string
		
		$pattern = '/"((?:wc_learning_context_id)|(?:column_id)|(?:ps_reg_key))": (\d+)/i';
		$replacement = '"$1": "$2"';
		return json_decode(preg_replace($pattern, $replacement, $json));
	}
	
	/**
	* how this works: the first term code (0) was Spring of 1964
	* if the term_code mod 3 is 0, we know the semester is Spring, if not,
	* subtract 10, mod 3 and we have summer, otherwise it's fall
	* to get the year, we take the final term_code, divide by 3 (3 semesters per year)
	* and divide by 10 (since it's in multiples of 10) and add it to 1964
	* 
	 * @param string $term_code ucf term code (1260, 1360, etc)
	 * @return array array( 'year' => 2000, 'semester' => 'Spring')
	 */
	protected function term_code2term_string($term_code)
	{
		$term = array('year' => 0, 'semester' => '');
	
		$tc = $term_code;
		if ($tc % 3 == 0)
		{
			$term['semester'] = 'Spring';
		}
		else
		{
			$tc = $tc - 10;
			if ($tc % 3 == 0)
			{
				$term['semester'] = 'Summer';
			}
			else
			{
				$tc = $tc - 10;
				$term['semester'] = 'Fall';
			}
		}
	
		$term['year'] = ( ($tc/10) /3 ) + 1964;
		
		// get the start and end time for semesters based on the term code
		// TODO: make the termcode work better
		
		$termCodeLookup = array();
		$termCodeLookup[1400] = array('start' => '1281571200', 'end' => '1292543999'); // fall 2010
		$termCodeLookup[1410] = array('start' => '1292544000', 'end' => '1304639999'); // spring 2011
		$termCodeLookup[1420] = array('start' => '1304640000', 'end' => '1279670340'); // summer 2011
		$termCodeLookup[1430] = array('start' => '1279670341', 'end' => '1323910740'); // fall 2011 
		$termCodeLookup[1440] = array('start' => '1323910741', 'end' => '1336089540'); // spring 2012
		
		$term['start'] = $termCodeLookup[$term_code]['start'];
		$term['end'] = $termCodeLookup[$term_code]['end'];
	
		return $term;
	}
	
	// public function getCurrentSemester()
	// {
	// 	
	// 	if($semesters = core_util_Cache::getInstance()->getCurrentSemester())
	// 	{
	// 		return $semesters;
	// 	}
	// 	else
	// 	{
	// 		$result = $this->getSemesterForDate(time());
	// 		core_util_Cache::getInstance()->setCurrentSemester($result);
	// 		return $result;
	// 	}
	// }
	// 
	// public function getSemesterForDate($date)
	// {
	// 	if($date>0)
	// 	{
	// 		trace($date);
	// 		$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Semester::TABLE." WHERE  ".cfg_obo_Semester::END_TIME." > '?' ORDER BY ".cfg_obo_Semester::END_TIME." ASC", $date);
	// 		if($r = $this->DBM->fetch_obj($q))
	// 		{
	// 			$semester = new nm_los_Semester($r);
	// 			return $semester;
	// 		}
	// 	}
	// 	return new nm_los_Semester();
	// }
	// 
	// public function getSemesters()
	// {
	// 	
	// 	if($semesters = core_util_Cache::getInstance()->getSemesters())
	// 	{
	// 		return $semesters;
	// 	}
	// 	else
	// 	{
	// 		$semesters = array();
	// 		$q = $this->DBM->query("SELECT * FROM ".cfg_obo_Semester::TABLE." ORDER BY ".cfg_obo_Semester::START_TIME." ASC");
	// 		if($r = $this->DBM->fetch_obj($q))
	// 		{
	// 			$semesters[] = new nm_los_Semester($r);
	// 		}
	// 		core_util_Cache::getInstance()->setSemesters($semesters);
	// 		return $semesters;
	// 	}
	// }
}
?>