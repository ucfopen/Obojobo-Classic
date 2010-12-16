<?php
class cfg_obo_Instance
{
	const TABLE = 'obo_lo_instances';
	
	const ID = 'instID';
	const TITLE = 'name';
	const TIME = 'createTime';
	const COURSE = 'courseName';
	const START_TIME = 'startTime';
	const END_TIME = 'endTime';
	const ATTEMPT_COUNT = 'attemptCount';
	const SCORE_METHOD = 'scoreMethod';
	const SCORE_METHOD_HIGHEST = 'h';
	const SCORE_METHOD_MEAN = 'm';
	const SCORE_METHOD_RECENT = 'r';
	const SCORE_IMPORT = 'allowScoreImport';
	const SYNC_SCORES = 'syncScores';
	
	const DELETED_TABLE = 'obo_deleted_instances';
	const DELETED_SCORE_DATA = 'scoreData';

}
?>