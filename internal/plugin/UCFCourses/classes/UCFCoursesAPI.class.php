<?php
class plg_UCFCourses_UCFCoursesAPI extends core_plugin_PluginAPI
{
	// Block all API calls directly referencing this api

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
	
	/*
		--------------Retrieving Instructor Sections---------------

		URL: /obojobo/v1/client/<INSTRUCTOR_NETWORK_ID>/instructor/sections

		On Success

		Returns a JSON object in the data attribute with the following attributes: ps_only, wc_only, and related. Those attributes are guaranteed to be lists of items with the following forms:

		ps_only item attributes: prefix, number, section, term, reg_key, title
		wc_only item attribtues: course, section, learning_context_id
		related item attributes: ps (contains ps_only item attributes), wc (contains wc_only item attributes)

		ps_only contains PoepleSoft sections that the specified instructor owns that do not correspond to any Webcourses section
		wc_only contains Webcourses sections that the specified instructor is enrolled in as Section Instructor that does not have any corresponding PeopleSoft section
		related contains PeopleSoft and Webcourses sections that are linked
		Possible Errors

		0, User does not exist in PeopleSoft
		1, User does not exist in Webcourses
	*/
	public function getCourses($NID)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/client/'.$NID.'/instructor/sections?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'GET');
		$request->execute();
		$result = $this->decodeJSON($request->getResponseBody());

		$courses = array();
		// reformat the return
		if(count($result->data->ps_only) > 0)
		{
			foreach($result->data->ps_only AS $ps)
			{
				$courses[] = $ps;
				$ps->type = 'ps_only';
			}
		}
		if(count($result->data->wc_only) > 0)
		{
			foreach($result->data->wc_only AS $ps)
			{
				$courses[] = $ps;
				$ps->type = 'wc_only';
			}
		}
		if(count($result->data->related) > 0)
		{
			foreach($result->data->related AS $ps)
			{
				$courses[] = $ps;
				$ps->type = 'related';
			}
		}
		$errors = $this->parseErrors($result->errors);
		if($errors && count($courses) == 0)
		{
			return $errors;
		}
		
		return $courses;
	}
	
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
	
	protected function decodeJSON($json)
	{
		// convert learning_context_id values as a string
		$pattern = '/"learning_context_id": (\d+)/i';
		$replacement = '"learning_context_id": "$1"';
		return json_decode(preg_replace($pattern, $replacement, $json));
	}
	
	/*
		--------------------- Creating a Grade Book Column ------------------------

		URL: /obojobo/v1/webcourses/gradebook/column/create
		Required POST params:

		wc_instructor_id: Webcourses Vista user id of the Section Instructor (typically NID)
		wc_section_id: Webcourses Vista learning context id
		column_name: desired grade book column name will be prefixed with OBOJOBO_
		On Success

		In msgs JSON attribute:

		0, Gradebook column created successfully
		In data JSON attribute:

		column_id (This is the ID of the created grade book column)
		Possible Errors

		In errors JSON attribute:

		0, User does not exist in PeopleSoft
		1, User does not exist in Webcourses
		2, User is not instructor in specified section
		3, Failed to initialize Webcourses Vista session
		6, Unable to fetch gradebook columns
		4, Gradebook column with specified name already exists
		7, Unable to create gradebook column

	*/
	public function createColumn($NID, $sectionID, $columnName)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/create?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'POST');
		$request->buildPostBody(array('wc_instructor_id' => $NID, 'wc_section_id' => $sectionID, 'column_name' => $columnName));
		$request->execute();
		$result = $this->decodeJSON($request->getResponseBody());
		
		$return = array();
		$return['columnID'] = isset($result->data->column_id) ? $result->data->column_id : 0;
		$return['msg'] = $result->msgs[0];
		
		
		$errors = $this->parseErrors($result->errors);
		if($errors && $return['columnID'] == 0)
		{
			return $errors;
		}
	}
	
	
	/*
		Updating Grade Book Column Value

		URL: /obojobo/v1/webcourses/gradebook/column/update
		Required POST Params:

		wc_instructor_id: Webcourses Vista user id of the Section Instructor (typically NID)
		wc_student_id: Webcourses Vista user id of the Section Student (typically NID)
		wc_section_id: Webcourses Vista learning context id
		score: numeric between 0 and 100
		On Success

		In msgs JSON attribute:

		1, Gradebook column value set successfully
		Possible Errors

		In errors JSON attribute:

		10, Specified score is incorrect format or out of acceptable range (OBOJOBO)
		0, User does not exist in PeopleSoft
		1, User does not exist in Webcourses
		3, Failed to initialize Webcourses Vista session
		12, Obojobo Gradebook column for this section does not exist or is unknown
		8, Unable to fetch section student IDs
		9, Unable to fetch member description for specified member
		11, Specified user is not a member of section gradebook

	*/
	
	public function sendScore($instructorNID, $studentNID, $sectionID, $columnID, $score)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/update?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'POST');
		$request->buildPostBody(array('wc_instructor_id' => $instructorNID, 'wc_student_id' => $studentNID, 'wc_section_id' => $sectionID, 'column_id' => $columnID, 'score' => $score));
		$request->execute();
		$result = $this->decodeJSON($request->getResponseBody());
		
		print_r($request);
		
	}
}
?>