<?php
class cfg_obo_Question
{
	const TABLE = 'lo_questions';
	const ID = 'questionID';
	const TYPE = 'itemType';
	const DATE = 'createTime';
	
	const MAP_ITEM_TABLE = 'lo_map_qitems';
	const MAP_ITEM_ORDER = 'itemOrder';

	const MAP_ANS_TABLE = 'lo_map_qa';
	const MAP_ANS_WEIGHT = 'weight';
	const MAP_ANS_FEEDBACK = 'feedback';
	const MAP_ANS_ORDER = 'itemOrder';
	
	
	const MAP_FEEDBACK_TABLE = 'lo_map_feedback';
	const MAP_FEEDBACK_CORRECT = 'correct';
	const MAP_FEEDBACK_INCORRECT = 'incorrect';
	
	
	const QTYPE_MEDIA = 'Media';
	const QTYPE_MULTI_CHOICE = 'MC';
	const QTYPE_SHORT_ANSWER = 'SA';

}
?>