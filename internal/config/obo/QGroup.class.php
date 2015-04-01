<?php
class cfg_obo_QGroup
{
	const TABLE = 'obo_lo_qgroups';
	const ID = 'qGroupID';
	const TITLE = 'name';
	const RAND = 'rand';
	const ALTS = 'allowAlts';
	const ALT_TYPE = 'altMethod';

	const MAP_TABLE = 'obo_map_questions_to_qgroup';
	const MAP_CHILD = 'childID';
	const MAP_ORDER = 'itemOrder';

	const MAP_ALT_TABLE = 'obo_map_qalts_to_qgroup';
	const MAP_ALT_INDEX = 'questionIndex';
}
