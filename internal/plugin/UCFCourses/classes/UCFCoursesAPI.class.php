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
		CURL Example Command

		curl http://endor:8000/obojobo/v1/client/wink/instructor/sections?app_key=aaa > result.html
	*/
	public function getSections($NID)
	{
		
		// build url
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/client/'.$NID.'/instructor/sections?app_key='.AppCfg::UCFCOURSES_APP_KEY;
		
		$request = new plg_UCFCourses_RestRequest($REQUESTURL, 'GET');
		$request->execute();
		print_r($request);
		
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
		CURL Example Command

		curl -d wc_instructor_id=tr_conover -d wc_section_id=6467766151071 -d column_name=test http://endor:8000/obojobo/v1/webcourses/gradebook/column/create?app_key=aaa > result.html
	*/
	public function createColumn($NID, $sectionID, $columnName)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/create';
		
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
		CURL Example Command

		curl -d wc_instructor_id=tr_conover -d wc_student_id=conover -d wc_section_id=6467766151071 -d score=30 http://endor:8000/obojobo/v1/webcourses/gradebook/column/update?app_key=aaa > result.html
	*/
	
	public function sendScore($instructorNID, $studentNID, $sectionID, $score)
	{
		$REQUESTURL = AppCfg::UCFCOURSES_URL_WEB . '/obojobo/v1/webcourses/gradebook/column/update';
	}
}
?>