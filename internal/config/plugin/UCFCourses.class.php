<?php
class cfg_plugin_UCFCourses
{

	//--------------------   GENERAL   -----------------------//
	const ERROR_TYPE = 'plg_UCFCourses_Error';
	
	//--------------------  COURSE MAPPING ----------------------//
	
	const MAP_TABLE = ' plg_wc_grade_columns';
	const MAP_SECTION_ID = 'sectionID';
	const MAP_COL_ID = 'columnID';
	const MAP_COL_NAME = 'columnName';
	
	//-------------------- SCORE HISTORY --------------------//
	
	const LOG_TABLE = 'plg_wc_grade_log';
	const STUDENT = 'studentID';
	const SUCCESS = 'success';
	const SCORE = 'score';
	const TIME = 'createTime';
	
}
?>