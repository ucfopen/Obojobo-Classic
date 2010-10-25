<?php
class plg_UCFCourses_UCFCoursesAPI extends core_plugin_PluginAPI
{

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


	public function getCurrentSemester()
	{
		
		if($semesters = core_util_Cache::getInstance()->getCurrentSemester())
		{
			return $semesters;
		}
		else
		{
			$result = $this->getSemesterForDate(time());
			core_util_Cache::getInstance()->setCurrentSemester($result);
			return $result;
		}
	}
	
	public function getSemesterForDate($date)
	{
		if($date>0)
		{
			trace($date);
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Semester::TABLE." WHERE  ".cfg_obo_Semester::END_TIME." > '?' ORDER BY ".cfg_obo_Semester::END_TIME." ASC", $date);
			if($r = $this->DBM->fetch_obj($q))
			{
				$semester = new nm_los_Semester($r);
				return $semester;
			}
		}

		return new nm_los_Semester();
	}
	
	public function getSemesters()
	{
		
		if($semesters = core_util_Cache::getInstance()->getSemesters())
		{
			return $semesters;
		}
		else
		{
			$semesters = array();
			$q = $this->DBM->query("SELECT * FROM ".cfg_obo_Semester::TABLE." ORDER BY ".cfg_obo_Semester::START_TIME." ASC");
			if($r = $this->DBM->fetch_obj($q))
			{
				$semesters[] = new nm_los_Semester($r);
			}
			core_util_Cache::getInstance()->setSemesters($semesters);
			return $semesters;
		}
	}

	public function getLinkedCourseDetails($instID)
	{
		$qstr = "SELECT * FROM ".cfg_plugin_UCFCourses::MAP_GRADES ." WHERE ".cfg_obo_Instance::ID." = '?'";
		if(!$q = $this->DBM->querySafe($qstr, $instID))
		{
			// query error
		}
		if($r = $this->DBM->fetch_obj($q))
		{
			
			$r->{cfg_plugin_UCFCourses::MAP_SECTION_ID};
			MAP_COL_ID
		}
		return false;
		
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
	public function getCourses($NID)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/client/'.$NID.'/instructor/sections?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'GET');
		$request->execute();
		$resultInfo = $request->getResponseInfo();
		
		// check for http response code of 200
		if($resultInfo['http_code'] != 200)
		{
			$error = AppCfg::ERROR_TYPE;
			return new $error(1008, 'HTTP RESPONSE: '. $resultInfo['http_code']);
		}
		
		$result = $this->decodeJSON($request->getResponseBody());
		$courses = $result->data;
		
		$errors = $this->parseErrors($result->errors);
		if($errors && count($courses) == 0)
		{
			return $errors;
		}
		
		return $courses;
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
	 * @param string $NID 	Webcourses Vista user id of the Section Instructor (typically NID)
	 * @param string $sectionID 	Webcourses Vista learning context id
	 * @param string $columnName 	desired grade book column name will be prefixed with 'obo:'
	 * @return void
	 * @author Ian Turgeon
	 */
	public function createColumn($NID, $sectionID, $columnName)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/create?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'POST');
		$request->buildPostBody(array('wc_instructor_id' => $NID, 'wc_section_id' => $sectionID, 'column_name' => $columnName));
		$request->execute();
		$resultInfo = $request->getResponseInfo();

		// check for http response code of 200
		if($resultInfo['http_code'] != 200)
		{
			$error = AppCfg::ERROR_TYPE;
			return new $error(1008, 'HTTP RESPONSE: '. $resultInfo['http_code']);
		}
		
		$result = $this->decodeJSON($request->getResponseBody());
		
		$return = array();
		$return['columnID'] = isset($result->data->column_id) ? $result->data->column_id : 0;
		$return['msg'] = $result->msgs[0];
		
		
		$errors = $this->parseErrors($result->errors);
		if($errors && $return['columnID'] == 0)
		{
			return $errors;
		}
		return $return;
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
	 * @param string $instructorNID 	Webcourses Vista user id of the Section Instructor (typically NID)
	 * @param string $studentNID 	Webcourses Vista user id of the Section Student (typically NID)
	 * @param string $sectionID 	Webcourses Vista learning context id
	 * @param string $columnID 	Webcourses Vista column id of the grade book column to insert the score into
	 * @param string $score 	numeric between 0 and 100
	 * @return void
	 * @author Ian Turgeon
	 */
	public function sendScore($instructorNID, $studentNID, $sectionID, $columnID, $score)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/update?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'POST');
		$request->buildPostBody(array('wc_instructor_id' => $instructorNID, 'wc_student_id' => $studentNID, 'wc_section_id' => $sectionID, 'column_id' => $columnID, 'score' => $score));
		$request->execute();
		$resultInfo = $request->getResponseInfo();
		// check for http response code of 200
		if($resultInfo['http_code'] != 200)
		{
			$error = AppCfg::ERROR_TYPE;
			return new $error(1008, 'HTTP RESPONSE: '. $resultInfo['http_code']);
		}

		$result = $this->decodeJSON($request->getResponseBody());
		$return = false;
		// look to see if the msg was successfull
		if(isset($result->msgs[0]) && substr($result->msgs[0], 0, 1) == "1")
		{
			$return = true;
		}
		
		$errors = $this->parseErrors($result->errors);
		if($errors && $return == false)
		{
			return $errors;
		}
		
		return $return;
		
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
				$error = AppCfg::ERROR_TYPE;
				$returnErrors[] = new $error(1008, $rError);
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
		$term = array();
		$term["year"] = 0;
		$term["semester"] = "";

		$tc = $term_code;
		if ($tc % 3 == 0)
		{
			$term["semester"] = 'Spring';
		}
		else
		{
			$tc = $tc - 10;
			if ($tc % 3 == 0)
			{
				$term["semester"] = 'Summer';
			}
			else
			{
				$tc = $tc - 10;
				$term["semester"] = 'Fall';
			}
		}

		$term["year"] = ( ($tc/10) /3 ) + 1964;

		return $term;
	}
}
?>