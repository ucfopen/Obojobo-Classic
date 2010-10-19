<?php
class cfg_obo_LO
{
	const TABLE = 'lo_los';
	const ID = 'loID';
	const MASTER = 'isMaster';
	const TITLE = 'title';
	const DESC = 'notesID'; // TODO: get rid of this
	const NOTES = 'notes';
	const OBJECTIVE = 'objective';
	const LEARN_TIME = 'learnTime';
	const PGROUP = 'pGroupID';
	const AGROUP = 'aGroupID';
	const VER = 'version';
	const SUB_VER = 'subVersion';
	const ROOT_LO = 'rootLoID';
	const PARENT_LO = 'parentLoID';
	const TIME = 'createTime';
	const COPYRIGHT = 'copyright';
	const NUM_PAGES = 'numPages';
	const NUM_PRACTICE = 'numPQuestions';
	const NUM_ASSESSMENT = 'numAQuestions';
	
	const MAP_AUTH_TABLE = 'lo_map_authors';
	
	const DEL_TABLE = 'lo_los_deleted';
	const DEL_DATA = 'cache';
	
}
?>