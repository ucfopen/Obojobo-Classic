<?php
class cfg_plugin_UCFCourses
{

	//--------------------   GENERAL   -----------------------//
	const ERROR_TYPE = 'plg_UCFCourses_Error';
	
	//--------------------   DATABASE   -----------------------//
	const DB_HOST = 'localhost';
	const DB_USER = 'root';
	const DB_PASS = 'root';
	const DB_NAME = 'los_OracleStandin';
	const DB_TYPE = 'mysql';
	
	//--------------------  COURSE TABLE ----------------------//
	const COURSE_TABLE = 'NM_COURSE';
	const PREFIX = 'COURSE_PREFIX';
	const NUMBER = 'COURSE_NUMBER';
	const SECTION = 'COURSE_SECTION';
	const TITLE = 'COURSE_TITLE';
	const COLLEGE = 'COLLEGE';
	const DEPT = 'DEPARTMENT';

	const NID = 'NETWORK_ID';
	const ID = 'REGISTRATION_KEY';
	
	const ROLL_TABLE = 'NM_CLASSROLL';
}
?>