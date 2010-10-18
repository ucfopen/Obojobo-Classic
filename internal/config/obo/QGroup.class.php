<?php
class cfg_obo_QGroup
{
	const TABLE = 'lo_qgroups';
	const ID = 'qGroupID';
	const TITLE = 'name';
	const RAND = 'rand';
	const ALTS = 'allowAlts';
	const ALT_TYPE = 'altMethod';

	const MAP_TABLE = 'lo_map_qgroup';
	const MAP_CHILD = 'childID';
	const MAP_TYPE = 'itemType';
	const MAP_ORDER = 'itemOrder';
	
	const MAP_ALT_TABLE = 'lo_map_qalts';
	const MAP_ALT_INDEX = 'questionIndex';
}
?>