<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

$API = nm_los_API::getInstance();
$result = $API->getSessionRoleValid(array('SuperUser'));
if(! in_array('SuperUser', $result['hasRoles']) )
{
	exit();
}

$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
//if(!$DBM->db_select('los_backup')) exit('unable to connect to backup database');


$stats = array();
   
   // Total Seconds in assessment
   $stats[] = array ('name' => '0_TotalSecondsInAssessment', 'value' => "SELECT SUM(".cfg_obo_Attempt::END_TIME."-".cfg_obo_Attempt::START_TIME.") AS TOTAL_SECONDS FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_Attempt::END_TIME." != '0' AND ".cfg_obo_Attempt::START_TIME." > 1214193600");

   // Count completed Assessments
   $stats[] = array ('name' => '1_CountCompletedAssessments', 'value'=>"SELECT COUNT(*) AS COMPLETED_ASSESSMENTS FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.") AND ".cfg_obo_Attempt::END_TIME." !='0' AND ".cfg_obo_Attempt::START_TIME." > 1214193600");
	$stats[] = array ('name' => '1a_CountCompletedAssessments', 'value'=>"SELECT COUNT(DISTINCT A.".cfg_obo_Attempt::ID.") AS COMPLETED_ASSESSMENTS FROM ".cfg_obo_Attempt::TABLE." AS A JOIN ".cfg_obo_LO::TABLE." AS O ON  O.".cfg_obo_LO::AGROUP." = A.".cfg_obo_QGroup::ID." WHERE ".cfg_obo_Attempt::END_TIME." !='0' AND ".cfg_obo_Attempt::START_TIME." > 1214193600");
   // Number of scored Questions
   $stats[] = array ('name' => '2_NumberOfScoredQuestions', 'value'=>"SELECT COUNT(*) AS ANSWERED_QUESTIONS FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Answer::ID." !=0 AND ".cfg_obo_Answer::TEXT." != '' AND ".cfg_obo_Attempt::ID." IN (SELECT ".cfg_obo_Attempt::ID." FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.") AND ".cfg_obo_Attempt::END_TIME." !='0' AND ".cfg_obo_Attempt::START_TIME." >   1214193600 )");

   //Average Score for all Questions
   $stats[] = array ('name' => '3_AverageScoreForAllQuestions', 'value'=>"SELECT AVG(".cfg_obo_Score::SCORE.") AS AVG_SCORE FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Answer::ID." !=0 AND ".cfg_obo_Answer::TEXT." != '' AND ".cfg_obo_Attempt::ID." IN (SELECT ".cfg_obo_Attempt::ID." FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.") AND ".cfg_obo_Attempt::END_TIME." !='0' AND ".cfg_obo_Attempt::START_TIME." >   1214193600 )");

   //Percent of Content Pages With Media
   // TODO: Fix this, changes to media requires some changes
   //$stats[] = array ('name' => '4_PercentOfContentPagesWithMedia', 'value'=>"SELECT  (SELECT COUNT(*) FROM lo_map_media WHERE item_type ='i') / (SELECT COUNT(*) FROM lo_pages WHERE q_id=0 ) * 100 AS PERCENT_WITH_MEDIA");

   //count of content pages with media
   // TODO: FIx
   //$stats[] = array ('name' => '5_CountOfContentPagesWithMedia', 'value'=>"SELECT COUNT(*) AS PAGES_WITH_MEDIA FROM lo_map_media WHERE item_type ='i'");

   // Average file Size
   $stats[] = array ('name' => '6_AverageFileSize', 'value'=>"SELECT AVG(".cfg_obo_Media::SIZE.") as SIZE_IN_BYTES  FROM ".cfg_obo_Media::TABLE." WHERE ".cfg_obo_Media::SIZE." !=0");

   // Answers with Feedback
//   $stats[] = array ('name' => '7_AnswersWithFeedback', 'value'=>"SELECT COUNT(*) AS Q_WITH_FEEDBACK FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::MAP_ANS_FEEDBACK." != ''");

   //Ave length of feedback (in characters)
//   $stats[] = array ('name' => '8_AveCharLengthOfFeedback', 'value'=>"SELECT AVG(len) AS AV_FEEDBACK_LENGTH FROM (SELECT CHAR_LENGTH(".cfg_obo_Question::MAP_ANS_FEEDBACK.") AS len  FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::MAP_ANS_FEEDBACK." != '') AS LENGTHS");

   //Percent of answers with partial values
//   $stats[] = array ('name' => '9_PercentOfAnswersWithPartialValues', 'value'=>"SELECT ((SELECT COUNT(*) FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::MAP_ANS_WEIGHT." != 0 AND ".cfg_obo_Question::MAP_ANS_WEIGHT." != 100) / (SELECT COUNT(*) FROM ".cfg_obo_Question::MAP_ANS_TABLE.") )*100 AS PERCENT_PARTIAL_SCORE");

   // Total Page Views (content and questions)
   $stats[] = array ('name' => '10_TotalContentAndQuestionPageViews', 'value'=>"SELECT COUNT(*) AS TOTAL_PAGE_VIEWS FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Track::TYPE." ='nm_los_tracking_PageChanged' AND ".cfg_obo_Track::TIME." > 1214193600");

   // Count of next/prev button used in content;
   //$stats['11_CountOfNextPrevButtonUsedInContent'] = "SELECT COUNT(uid) AS NEXT_PREV_USED FROM obo_logs WHERE type='nm_los_tracking_NextPreviousUsed' AND time > 1214193600";
   // Media Views
   $stats[] = array ('name' => '12_MediaViews', 'value'=>"SELECT COUNT(".cfg_core_User::ID.") AS MEDIA_VIEWS FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Track::TYPE."='nm_los_tracking_MediaRequested' AND ".cfg_obo_Track::TIME." > 1214193600");

   // Percent of Assessment Attempts not completed
   $stats[] = array ('name' => '13_PercentOfAssessmentAttemptsNotCompleted', 'value'=>"SELECT COUNT(*) / (SELECT COUNT(*) FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_Attempt::START_TIME." > 1214193600 AND ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.")) * 100  AS PERCENT FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_Attempt::END_TIME." = 0 AND ".cfg_obo_Attempt::START_TIME." > 1214193600 AND ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.")");

   // Number of practice sessions started
   $stats[] = array ('name' => '14_NumberOfPracticeSessionsStarted', 'value'=>"SELECT COUNT(*) AS PRACTICE_STARTED_COUNT FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::PGROUP." FROM ".cfg_obo_LO::TABLE.") AND ".cfg_obo_Attempt::START_TIME." > 1214193600");

   // number of assessment sessions started
   $stats[] = array ('name' => '15_NumberOfAssessmentSessionsStarted', 'value'=>"SELECT COUNT(*) AS ASSESSMENT_STARTED_COUNT FROM ".cfg_obo_Attempt::TABLE." WHERE ".cfg_obo_QGroup::ID." IN (SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE.") AND ".cfg_obo_Attempt::START_TIME." > 1214193600");

   // Number of instances deleted
   $stats[] = array ('name' => '16_NumberOfInstancesDeleted', 'value'=>"SELECT COUNT(*) AS DELETED_INST_COUNT FROM ".cfg_obo_Instance::DELETED_TABLE." WHERE 1");

   // Number of master objects deleted
   $stats[] = array ('name' => '17_NumberOfMasterObjectsDeleted', 'value'=>"SELECT COUNT(*) AS DELETED_MASTER_COUNT FROM ".cfg_obo_LO::DEL_TABLE." WHERE 1");

   // Total learning object draft saves
   $stats[] = array ('name' => '18_TotalLearningObjectDraftSaves', 'value'=>"SELECT MAX(".cfg_obo_LO::ID.") AS NUM_DRAFT_SAVES FROM ".cfg_obo_LO::TABLE."");

   // Total tracking logs saved
   $stats[] = array ('name' => '19_TotalTrackingLogsSaved', 'value'=>"SELECT COUNT(*) AS NUM_TRACKING_LOGS FROM ".cfg_obo_Track::TABLE."");

   // Total number of users
   $stats[] = array ('name' => '20_TotalNumberOfUsers', 'value'=>"SELECT COUNT(*) AS NUM_USERS FROM ".cfg_core_User::TABLE."");

   //Question Type Usage
   $stats[] = array ('name' => '21_QuestionTypeUsage', 'value'=>"SELECT ".cfg_obo_Question::TYPE." AS QUESTION_TYPE, COUNT(*) AS COUNT FROM ".cfg_obo_Question::TABLE." GROUP BY QUESTION_TYPE");

   // Instances Per User
   $stats[] = array ('name' => '22_InstancesPerUser', 'value'=>"SELECT CONCAT(U.last, ', ' , U.first) AS USER_NAME, COUNT(I.".cfg_core_User::ID.") as NUM_INSTANCES FROM ".cfg_obo_Instance::TABLE." AS I, ".cfg_core_User::TABLE." AS U WHERE U.".cfg_core_User::ID." = I.".cfg_core_User::ID." GROUP BY I.".cfg_core_User::ID." ORDER BY U.".cfg_core_User::LAST);

   //Versions of SWF Media
   $stats[] = array ('name' => '23_VersionsOfSWFMedia', 'value'=>"SELECT version AS SWF_VERSION, COUNT(*) AS COUNT  FROM ".cfg_obo_Media::TABLE." WHERE ".cfg_obo_Media::TYPE."='swf' GROUP BY ".cfg_obo_Media::VER."");

   //media type count
   $stats[] = array ('name' => '24_MediaTypeCount', 'value'=>"SELECT ".cfg_obo_Media::TYPE." AS TYPE, COUNT(*) AS NUMBER  FROM ".cfg_obo_Media::TABLE." GROUP BY ".cfg_obo_Media::TYPE);

   //Keyword Popularity
   $stats[] = array ('name' => '25_KeywordPopularity', 'value'=>"SELECT K.".cfg_obo_Keyword::NAME." AS KEYWORD, count(M.".cfg_obo_Keyword::MAP_ITEM.") as COUNT FROM ".cfg_obo_Keyword::MAP_TABLE." AS M, ".cfg_obo_Keyword::TABLE." AS K WHERE M.".cfg_obo_Keyword::ID." = K.".cfg_obo_Keyword::ID." GROUP BY K.".cfg_obo_Keyword::ID." ORDER BY KEYWORD");

   //Page Layouts use in Masters
//   $stats[] = array ('name' => '26_PageLayoutsUseInMasters', 'value'=>"SELECT L.name AS LAYOUT, count(P.".cfg_obo_Page::ID.") as COUNT FROM ".cfg_obo_Page::TABLE." AS P, ".cfg_obo_Layout::TABLE." AS L WHERE P.".cfg_obo_Page::ID." IN (SELECT ".cfg_obo_Page::ID." FROM ".cfg_obo_Page::MAP_TABLE." WHERE ".cfg_obo_LO::ID." IN (SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::VER." != '0' AND ".cfg_obo_LO::SUB_VER." ='0')) AND L.".cfg_obo_Layout::ID." = P.".cfg_obo_Layout::ID."  Group By LAYOUT");

   //Page Layouts use in Masters
//   $stats[] = array ('name' => '27_PageLayoutsAll', 'value'=>"SELECT ".cfg_obo_Layout::ID." AS LAYOUT, count(".cfg_obo_Page::ID.") as COUNT FROM ".cfg_obo_Page::TABLE." Group By ".cfg_obo_Layout::ID."");

   //Resolution by Views
   $stats[] = array ('name' => '28_ResolutionByViews', 'value'=>"SELECT CONCAT_WS('x', ".cfg_obo_ComputerData::RES_WIDTH.", ".cfg_obo_ComputerData::RES_HEIGHT.") As SCREEN_RESOLUTION, COUNT(".cfg_obo_ComputerData::TIME.") as COUNT  FROM ".cfg_obo_ComputerData::TABLE." WHERE ".cfg_core_User::ID." !='0' Group By SCREEN_RESOLUTION ORDER BY ".cfg_obo_ComputerData::RES_WIDTH.", ".cfg_obo_ComputerData::RES_HEIGHT);

   // Visits by day
   $stats[] = array ('name' => '29_InstanceTotalHitsByDay', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_obo_Track::TIME."), '%m-%d-%Y') AS DATE, COUNT(".cfg_obo_Track::TIME.") AS VISITS FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Track::TYPE." = 'nm_los_tracking_Visited' AND ".cfg_obo_Track::TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_obo_Track::TIME);

   // Instance Views by date
   $stats[] = array ('name' => '30_InstanceIndividualViewsByDay', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(T.".cfg_obo_Track::TIME."), '%m-%d-%Y') AS DATE, COUNT(T.".cfg_obo_Track::TIME.") AS VISITS, CONCAT(L.title, ' v.', L.".cfg_obo_LO::VER.") AS MASTER FROM ".cfg_obo_Track::TABLE." AS T, ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_LO::TABLE." AS L WHERE T.".cfg_obo_Track::TYPE." = 'nm_los_tracking_Visited' AND T.".cfg_obo_Instance::ID." = I.".cfg_core_User::ID." AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." AND T.".cfg_obo_Track::TIME." > 1214193600 GROUP BY DATE, MASTER ORDER BY T.".cfg_obo_Track::TIME.", MASTER");

   // Flash Version Counts
   $stats[] = array ('name' => '31_FlashVersionCounts', 'value'=>"SELECT ".cfg_obo_ComputerData::VER." AS FLASH_PLUGIN_VERSION, COUNT(".cfg_obo_ComputerData::TIME.") as COUNT  FROM ".cfg_obo_ComputerData::TABLE." WHERE ".cfg_core_User::ID." !='0' Group By ".cfg_obo_ComputerData::VER);

   //Error Count
   $stats[] = array ('name' => '32_ErrorCount', 'value'=>"SELECT ".cfg_obo_Track::TYPE." AS ERROR_TYPE, COUNT(*) AS COUNT FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Track::TYPE." > 0 AND ".cfg_obo_Track::TIME." > 1214193600 GROUP BY ".cfg_obo_Track::TYPE." ORDER By ".cfg_obo_Track::TYPE);

   //Error Counts by day
   $stats[] = array ('name' => '33_ErrorCountsByDay', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(T.".cfg_obo_Track::TIME."), '%m-%d-%Y') AS DATE, ".cfg_obo_Track::TYPE." AS TYPE, COUNT(".cfg_obo_Track::TYPE.") AS COUNT FROM ".cfg_obo_Track::TABLE." AS T WHERE ".cfg_obo_Track::TYPE." NOT LIKE 'nm_los%' AND ".cfg_obo_Track::TIME." > 1214193600 GROUP BY DATE, ".cfg_obo_Track::TYPE." ORDER BY ".cfg_obo_Track::TIME.", ".cfg_obo_Track::TYPE);

   // New Student Users by hour of day
   $stats[] = array ('name' => '34_NewStudentUsersByHourOfDay', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."), '%H:00') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." NOT IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." group by ".cfg_core_User::ID.") AND ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME);

   // New Faculty Users by hour of day
   $stats[] = array ('name' => '35_NewFacultyUsersByHourOfDay', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."), '%H:00') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID." = (SELECT ".cfg_obo_Role::ID." from ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ROLE." = 'ContentCreator')) AND     ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME."");

   // New Student Users by Date

   $stats[] = array ('name' => '36_NewStudentUsersByDate', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."), '%m-%d-%Y') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." NOT IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." group by ".cfg_core_User::ID.") AND ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME."");

   // New Faculty Users by Date
   $stats[] = array ('name' => '37_NewFacultyUsersByDate', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."), '%m-%d-%Y') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID." = (SELECT ".cfg_obo_Role::ID." from ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ROLE." = 'ContentCreator')) AND     ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME."");

   // New Student Users by Date
   $stats[] = array ('name' => '38_NewStudentUsersbyDayOfWeek', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."),  '%a') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." NOT IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." group by ".cfg_core_User::ID.") AND ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME."");

   // New Faculty Users by Date
   $stats[] = array ('name' => '39_NewFacultyUsersByDayOfWeek', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_core_User::CREATED_TIME."),  '%a') AS DATE, COUNT(".cfg_core_User::ID.") AS COUNT FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID." IN (SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID." = (SELECT ".cfg_obo_Role::ID." from ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ROLE." = 'ContentCreator')) AND ".cfg_core_User::CREATED_TIME." > 1214193600 GROUP BY DATE ORDER BY ".cfg_core_User::CREATED_TIME);

   // Total Visists Per Master
   $stats[] = array ('name' => '40_TotalVisistsPerMaster', 'value'=>"SELECT  CONCAT(L.".cfg_obo_LO::TITLE.", ' v. ',L.".cfg_obo_LO::VER.")  AS MASTER_TITLE, COUNT( T.".cfg_core_User::ID.") AS VISITS FROM ".cfg_obo_Track::TABLE." AS T, ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_LO::TABLE." AS L WHERE T.".cfg_obo_Instance::ID." != '0' AND  T.".cfg_obo_Track::TYPE."= 'nm_los_tracking_Visited' AND I.".cfg_obo_Instance::ID." = T.".cfg_obo_Instance::ID." AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." AND T.".cfg_obo_Track::TIME." > 1214193600 GROUP BY L.".cfg_obo_LO::ID."");

   // Total Visits Per Instance
   $stats[] = array ('name' => '41_TotalVisitsPerInstance', 'value'=>"SELECT  CONCAT(L.".cfg_obo_LO::TITLE.", ' v. ',L.".cfg_obo_LO::VER.")  AS MASTER_TITLE, I.".cfg_obo_Instance::TITLE." AS INSTANCE_NAME, I.".cfg_obo_Instance::COURSE." AS INSTANCE_COURSE, CONCAT(U.".cfg_core_User::FIRST.", ' ', U.".cfg_core_User::LAST.") AS INTANCE_OWNER, COUNT( T.".cfg_core_User::ID.") AS VISITS FROM ".cfg_obo_Track::TABLE." AS T, ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_LO::TABLE." AS L, ".cfg_core_User::TABLE." AS U WHERE T.".cfg_obo_Instance::ID." != '0' AND  T.".cfg_obo_Track::TYPE."= 'nm_los_tracking_Visited' AND I.".cfg_obo_Instance::ID." = T.".cfg_obo_Instance::ID." AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." AND U.".cfg_core_User::ID." = I.".cfg_core_User::ID." AND T.".cfg_obo_Track::TIME." > 1214193600 GROUP BY T.".cfg_obo_Instance::ID."");

   //Instance View Count By Date
   $stats[] = array ('name' => '42_InstanceViewCountByDate', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(T.".cfg_obo_Track::TIME."), '%m-%d-%Y') AS DATE, COUNT(T.".cfg_obo_Track::TIME.") AS VISITS, CONCAT(L.".cfg_obo_LO::TITLE.", ' v.', L.".cfg_obo_LO::VER.") AS MASTER FROM ".cfg_obo_Track::TABLE." AS T, ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_LO::TABLE." AS L WHERE T.".cfg_obo_Track::TYPE." = 'nm_los_tracking_Visited' AND T.".cfg_obo_Instance::ID." = I.".cfg_obo_Instance::ID." AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." AND T.".cfg_obo_Track::TIME." > 1214193600 GROUP BY DATE, MASTER ORDER BY T.".cfg_obo_Track::TIME.", MASTER");

   //Instance Count of Masters
   $stats[] = array ('name' => '43_InstanceCountOfMasters', 'value'=>"SELECT CONCAT(L.".cfg_obo_LO::TITLE.", '  v.', L.".cfg_obo_LO::VER.") AS MASTER_TITLE, count(I.".cfg_obo_LO::ID.") as INSTANCES FROM ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_LO::TABLE." AS L WHERE L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." GROUP BY I.".cfg_obo_LO::ID." ORDER BY MASTER_TITLE ASC");

   //Individual Question Scores For Masters
   $stats[] = array ('name' => '44_IndividualQuestionScoresForMasters', 'value'=>"SELECT CONCAT(L.".cfg_obo_LO::TITLE.", ' v.', L.".cfg_obo_LO::VER.") AS MASTER_TITLE, MQ.".cfg_obo_QGroup::MAP_ORDER." + 1 AS QUESTION_NUMBER, AVG(S.".cfg_obo_Score::SCORE.") AS AVERGE, STD(S.".cfg_obo_Score::SCORE.") AS STANDARD_DEV, MIN(S.".cfg_obo_Score::SCORE.") AS MIN_SCORE, MAX(S.".cfg_obo_Score::SCORE.") AS MAX_SCORE, COUNT(S.".cfg_obo_Score::SCORE.") AS COUNT_SCORES   FROM ".cfg_obo_QGroup::MAP_TABLE." AS MQ, ".cfg_obo_LO::TABLE." AS L, ".cfg_obo_Score::TABLE." AS S WHERE L.".cfg_obo_LO::VER." > 0 AND L.".cfg_obo_LO::SUB_VER." = 0 AND S.".cfg_obo_QGroup::ID." = L.".cfg_obo_LO::AGROUP." AND MQ.".cfg_obo_QGroup::MAP_CHILD." = S.".cfg_obo_Score::ITEM_ID." AND MQ.".cfg_obo_QGroup::ID." = S.".cfg_obo_QGroup::ID." GROUP BY S.".cfg_obo_Score::ITEM_ID.", L.".cfg_obo_LO::ID." ORDER BY MASTER_TITLE, QUESTION_NUMBER");

   //Individual Question Scores by Instance
 
$stats[] = array ('name' => '45_IndividualQuestionScoresForInstances', 'value'=>"SELECT CONCAT(L.".cfg_obo_LO::TITLE.", ' v.', L.".cfg_obo_LO::VER.") AS MASTER_TITLE,  I.".cfg_obo_Instance::TITLE." AS INSTANCE_NAME, I.".cfg_obo_Instance::COURSE." AS INSTANCE_COURSE, U.".cfg_core_User::LAST." AS OWNER_LAST_NAME, U.".cfg_core_User::FIRST." AS OWNER_FIRST_NAME, MQ.".cfg_obo_QGroup::MAP_ORDER." + 1 AS QUESTION_NUMBER, AVG(S.".cfg_obo_Score::SCORE.") AS AVERGE,
STD(S.".cfg_obo_Score::SCORE.") AS STANDARD_DEV,
MIN(S.".cfg_obo_Score::SCORE.") AS MIN_SCORE,
MAX(S.".cfg_obo_Score::SCORE.") AS MAX_SCORE,
COUNT(S.".cfg_obo_Score::SCORE.") AS COUNT_SCORES
FROM ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_Score::TABLE." AS S, ".cfg_obo_Visit::TABLE." AS V, ".cfg_obo_Attempt::TABLE." AS AT, ".cfg_obo_LO::TABLE." AS L, ".cfg_obo_QGroup::MAP_TABLE." AS MQ, ".cfg_core_User::TABLE." AS U
WHERE S.".cfg_obo_Attempt::ID." = AT.".cfg_obo_Attempt::ID."
AND AT.".cfg_obo_Visit::ID." = V.".cfg_obo_Visit::ID."
AND I.".cfg_obo_Instance::ID." = V.".cfg_obo_Instance::ID."
AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID."
AND MQ.".cfg_obo_QGroup::MAP_CHILD." = S.".cfg_obo_Score::ITEM_ID."
AND MQ.".cfg_obo_QGroup::ID." = S.".cfg_obo_QGroup::ID."
AND S.".cfg_obo_QGroup::ID." = L.".cfg_obo_LO::AGROUP."
AND U.".cfg_core_User::ID." = I.".cfg_core_User::ID."
GROUP BY I.".cfg_obo_Instance::ID.", QUESTION_NUMBER
ORDER BY MASTER_TITLE, I.".cfg_obo_Instance::ID.", QUESTION_NUMBER");


// TODO:
$stats[] = array ('name' => '46_StudentsScoresByInstance', 'value' => "SELECT  L.".cfg_obo_LO::ID." AS MAST_ID, L.".cfg_obo_LO::TITLE." AS MAST_TITLE, I.".cfg_obo_Instance::ID." AS INST_ID, I.".cfg_obo_Instance::TITLE." AS INST_TITLE, I.".cfg_obo_Instance::COURSE." AS INST_COURSE, user2.".cfg_core_User::ID." AS INST_OWNER_ID, user2.".cfg_core_User::LAST." AS INST_OWNER_LAST, user2.".cfg_core_User::FIRST." AS INST_OWNER_FIRST, a.".cfg_core_User::ID." AS STUDENT_ID, user1.".cfg_core_User::LAST." AS STUDENT_LAST, user1.".cfg_core_User::FIRST." AS STUDENT_FIRST, AVG(AT.".cfg_obo_Attempt::SCORE.") AS AVE_SCORE, STD(AT.".cfg_obo_Attempt::SCORE.") AS STDEV_SCORE, MIN(AT.".cfg_obo_Attempt::SCORE.") AS MIN_SCORE, MAX(AT.".cfg_obo_Attempt::SCORE.") AS MAX_SCORE, MAX(AT.".cfg_obo_Attempt::SCORE.") - MIN(AT.".cfg_obo_Attempt::SCORE.") AS SCORE_INCREASE, COUNT(AT.".cfg_obo_Attempt::ID.") AS ATTEMPTS_COUNT, tmp_visit.hits AS INSTANCE_VISITS
	FROM ".cfg_obo_Attempt::TABLE." AS AT
	LEFT JOIN ".cfg_obo_Visit::TABLE." AS a ON a.".cfg_obo_Visit::ID." = AT.".cfg_obo_Visit::ID."
	LEFT JOIN ".cfg_core_User::TABLE." AS user1 ON user1.".cfg_core_User::ID." = a.".cfg_core_User::ID."
	LEFT JOIN tmp_visit ON tmp_visit.".cfg_obo_Instance::ID." = a.".cfg_obo_Instance::ID." AND tmp_visit.".cfg_core_User::ID." = a.".cfg_core_User::ID."
	LEFT JOIN ".cfg_obo_Instance::TABLE." AS I ON I.".cfg_obo_Instance::ID." = a.".cfg_obo_Instance::ID."
	LEFT JOIN ".cfg_core_User::TABLE." AS user2 ON user2.".cfg_core_User::ID." = I.".cfg_core_User::ID."
	LEFT JOIN ".cfg_obo_LO::TABLE." AS L ON L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID."
	WHERE AT.".cfg_obo_Attempt::END_TIME." != 0
	GROUP BY a.".cfg_core_User::ID.", a.".cfg_obo_Instance::ID."
	ORDER BY L.".cfg_obo_LO::ID.", a.".cfg_obo_Instance::ID." ASC, a.".cfg_core_User::ID." ASC;");
      

// $stats[] = array ('name' => '47_TotalViewTimeBySection', 'logTotalsArrayKey'=> 'sectionTime');
// $stats[] = array ('name' => '48_TotalUniquePageViews', 'logTotalsArrayKey'=> 'uniquePageViews');
// $stats[] = array ('name' => '49_TotalPageViewsBySection', 'logTotalsArrayKey'=> 'totalPageViews');


 

$stats[] = array ('name' => '50_MasterAssessmentAvoidingPlagiarism1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(4373, $DBM));

$stats[] = array ('name' => '51_MasterAssessmentCreatinSearchStrategy1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(4408, $DBM));

$stats[] = array ('name' => '52_MasterAssessmentCitingSourcesUsintMLA1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(4407, $DBM));

$stats[] = array ('name' => '53_MasterAssessmentEvaluatingWebSites2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(4370, $DBM));

$stats[] = array ('name' => '54_PlayerVersionByMonth', 'value'=>"SELECT DATE_FORMAT(FROM_UNIXTIME(".cfg_obo_ComputerData::TIME."), '%m-%Y') AS DATE, COUNT(".cfg_obo_ComputerData::VER.") AS VISITS, SUBSTRING(".cfg_obo_ComputerData::VER.", 5) AS VERSIONS FROM ".cfg_obo_ComputerData::TABLE." WHERE ".cfg_obo_ComputerData::TIME." != '0' AND ".cfg_obo_ComputerData::IP." != '127.0.0.1' AND ".cfg_obo_ComputerData::IP." != '10.173.87,90' GROUP BY DATE, VERSIONS ORDER BY DATE");

$stats[] = array ('name' => '55_SystemHitsByUser', 'value'=>"SELECT COUNT(".cfg_obo_ComputerData::TABLE.".".cfg_core_User::ID.") AS VISITS, ".cfg_core_User::LAST.", ".cfg_core_User::FIRST.", FROM_UNIXTIME(".cfg_core_User::CREATED_TIME.") AS CREATED, FROM_UNIXTIME(".cfg_core_User::LOGIN_TIME.") AS LAST_LOGIN FROM ".cfg_core_User::TABLE.", ".cfg_obo_ComputerData::TABLE." WHERE ".cfg_obo_ComputerData::TABLE.".".cfg_core_User::ID." != '0' AND ".cfg_core_User::TABLE.".".cfg_core_User::ID." = ".cfg_obo_ComputerData::TABLE.".".cfg_core_User::ID."  GROUP BY ".cfg_obo_ComputerData::TABLE.".".cfg_core_User::ID." ORDER BY  VISITS DESC, ".cfg_core_User::LAST.", ".cfg_core_User::FIRST);

$stats[] = array ('name' => '56_MasterAssessmentEvaluatingWebSites3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7811, $DBM));

$stats[] = array ('name' => '57_MasterAssessmentEvaluatingWebSites4', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7841, $DBM));

$stats[] = array ('name' => '58_MasterAssessmentFocusingInformationSearch1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7820, $DBM));

$stats[] = array ('name' => '59_MasterAssessmentCreatinSearchStrategy2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7819, $DBM));

$stats[] = array ('name' => '60_MasterAssessmentAvoidingPlagiarism2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7792, $DBM));

$stats[] = array ('name' => '61_MasterAssessmentMaximizingGoogleScholarSearches2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7814, $DBM));

$stats[] = array ('name' => '62_MasterAssessmentCitingSourcesUsintMLA2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7801, $DBM));

$stats[] = array ('name' => '63_MasterAssessmentCitingSourcesUsintAPA2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7802, $DBM));

$stats[] = array ('name' => '64_MasterAssessmentAvoidingPlagiarism3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13836, $DBM));

$stats[] = array ('name' => '65_MasterAssessmentRecognizingResearchStudy2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(7711, $DBM));

$stats[] = array ('name' => '66_MasterAssessmentCreatinSearchStrategy3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13810, $DBM));

$stats[] = array ('name' => '67_MasterAssessmentCitingSourcesUsintMLA3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13832, $DBM));

$stats[] = array ('name' => '68_MasterAssessmentEvaluatingWebSites5', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13848, $DBM));

$stats[] = array ('name' => '69_MasterAssessmentFocusingInformationSearch2', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13849, $DBM));

$stats[] = array ('name' => '70_MasterAssessmentMaximizingGoogleScholarSearches3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13851, $DBM));

$stats[] = array ('name' => '71_MasterAssessmentCitingSourcesUsintAPA3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13830, $DBM));

$stats[] = array ('name' => '72_MasterAssessmentRecognizingResearchStudy3', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13853, $DBM));

$stats[] = array ('name' => '73_MasterAssessmentRefWorks1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(14303, $DBM));

$stats[] = array ('name' => '74_MasterAssessmentUnderstandingTheInformationCycle1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(14315, $DBM));

$stats[] = array ('name' => '75_MasterAssessmentSelectingArticles1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(13887, $DBM));

$stats[] = array ('name' => '76_MasterAssessmentLiteratureReview1', 'value'=> 'getQuestionAnswersByMaster' , 'args' => array(14427, $DBM));



$startTime = 1259647200;
$endTime = time();
$loIDs = implode(',', array(7841, 7820, 7819, 7814, 7802, 7801, 7792, 7711, 13810, 13832, 13848,13849,13851,13830,13853 ));

$stats[] = array ('name' => '9999_StudentVisitContactList', 'value'=>"SELECT U.".cfg_core_User::LAST.", U.".cfg_core_User::FIRST.", U.".cfg_core_User::EMAIL.", GROUP_CONCAT(DISTINCT L.".cfg_obo_LO::TITLE." ORDER BY L.".cfg_obo_LO::TITLE." ASC SEPARATOR ', ') AS master_titles, count(DISTINCT I.".cfg_obo_LO::ID.") AS unique_masters_visited, count(*) AS raw_total_visits, FROM_UNIXTIME(MIN(T.".cfg_obo_Track::TIME.")) AS first_log, FROM_UNIXTIME(MAX(T.".cfg_obo_Track::TIME.")) AS last_log  FROM ".cfg_core_User::TABLE." AS U, ".cfg_obo_Instance::TABLE." AS I, ".cfg_obo_Track::TABLE." AS T, ".cfg_obo_LO::TABLE." AS L WHERE L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID." AND U.".cfg_core_User::ID." = T.".cfg_core_User::ID." AND I.".cfg_obo_Instance::ID." = T.".cfg_obo_Instance::ID." AND T.".cfg_obo_Track::TYPE." = 'nm_los_tracking_Visited' AND I.".cfg_obo_LO::ID." IN ($loIDs) AND T.".cfg_obo_Track::TIME." >= '$startTime' AND T.".cfg_obo_Track::TIME." <= '$endTime' AND U.".cfg_core_User::ID." NOT IN ( SELECT DISTINCT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." )  GROUP BY U.".cfg_core_User::LAST.", U.".cfg_core_User::FIRST);

$stats[] = array ('name' => '9999_OwnersOfNewInstances', 'value'=>
	"SELECT 
		U.".cfg_core_User::LAST.",
		U.".cfg_core_User::FIRST.",
		U.".cfg_core_User::EMAIL.",
		GROUP_CONCAT(
			DISTINCT L.".cfg_obo_LO::TITLE."
			ORDER BY L.".cfg_obo_LO::TITLE."
			ASC SEPARATOR ', ') AS master_titles,
		count(*) AS total_instances
	FROM
		".cfg_core_User::TABLE." AS U,
		".cfg_obo_Instance::TABLE." AS I,
		".cfg_obo_LO::TABLE." AS L
	WHERE
		U.".cfg_core_User::ID." = I.".cfg_core_User::ID."
	AND
		I.".cfg_obo_LO::ID." IN ($loIDs)
	AND 
		I.".cfg_obo_Instance::START_TIME." >= $startTime 
	AND 
		I.".cfg_obo_Instance::START_TIME." <= $endTime
	AND
		L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID."
	GROUP BY
		U.".cfg_core_User::ID);


$stats[] = array ('name' => '9999_RecentActiveAuthors', 'value'=>
	"SELECT
		DISTINCT U.".cfg_core_User::EMAIL."
	FROM
		".cfg_core_User::TABLE." AS U
	LEFT JOIN
		".cfg_obo_Instance::TABLE." AS I
	ON  I.".cfg_core_User::ID." = U.".cfg_core_User::ID."
	LEFT JOIN
		".cfg_obo_Perm::TABLE." AS P
	ON P.".cfg_core_User::ID." = U.".cfg_core_User::ID."
	WHERE
		I.".cfg_obo_Instance::TIME." > ". strtotime("-2 month") ."
	OR
	(
			P.".cfg_obo_Perm::TYPE." = '".cfg_obo_Perm::TYPE_LO."'
		AND
			P.".cfg_obo_Perm::ITEM." IN 
				(
					SELECT
						".cfg_obo_LO::ID."
					FROM
						".cfg_obo_LO::TABLE."
					WHERE
						".cfg_obo_LO::TIME." > ". strtotime("-2 month") ."
				)
	)
	");
	
	
$stats[] = array ('name' => '9999_InstancesAffectedByDate', 'value'=>
	"SELECT
		DISTINCT U.".cfg_core_User::EMAIL."
	FROM
		".cfg_core_User::TABLE." AS U
	LEFT JOIN
		".cfg_obo_Instance::TABLE." AS I
	ON  I.".cfg_core_User::ID." = U.".cfg_core_User::ID."
	WHERE
		I.".cfg_obo_Instance::END_TIME." > '1268370000'
	AND
		I.".cfg_obo_Instance::START_TIME." < '1268370000'
	");


if(count($_REQUEST['statSelect']) < 1 || !isset($_REQUEST['num_stats_to_run']) || !isset($_REQUEST['delay']))
{
	?>
	<h1>Select the Stat items you want</h1>
	<form id="frm1" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
		<script type="text/javascript" charset="utf-8">
			function checkedAll (setVal) {
				var aa= document.getElementById('frm1');
				for (var i =0; i < aa.elements.length; i++) 
				{
				 aa.elements[i].checked = setVal;
				}
			}
			</script>
			
		</script>
		<p>
			<a href="javascript:checkedAll(true);">Check all</a> <a href="javascript:checkedAll(false);">Uncheck all</a><br><br>

	
	
	<?php
	
	foreach($stats AS $key => $statItem)
	{
		echo '<input type="checkbox" name="statSelect[]" value="'.$key.'" id="statSelect'.$key.'"> <label for="statSelect'.$key.'">'.$statItem['name'].'</label><br>';
	}
	
	?>
	<br>
	<a href="javascript:checkedAll(true);">Check all</a> <a href="javascript:checkedAll(false);">Uncheck all</a><br><br>
	<label for="num_stats_to_run">Num Stats To Run per refresh:</label> <input type="text" name="num_stats_to_run" value="1" id="num_stats_to_run">
	<br>
	<label for="delay">AutoRun Delay (s):</label> <input type="text" name="delay" value="30" id="delay">
	<br/>
		<input type="submit" value="Continue &rarr;"></p>
	</form>
	<?php
	exit();
}
// select items from the stats array
$statsToProcess = array();
$count = 0;
foreach($_REQUEST['statSelect'] AS $key => $value)
{
	$statsToProcess[] = $stats[$value];
	unset($_REQUEST['statSelect'][$key]);
	$count++;
	if( $count >= $_REQUEST['num_stats_to_run']) break;
}

$output = buildOutput($DBM, $statsToProcess);

?>

	<html>
		<head>
			<?php

				// auto refresh if theres any left, and if auto is on
				if( count($_REQUEST['statSelect']) > 0 )
				{
					$selected = '&statSelect[]=' . implode('&statSelect[]=', $_REQUEST['statSelect']);
					echo '<meta http-equiv="refresh" content="'.$_REQUEST['delay'].';'.$_SERVER['PHP_SELF'].'?&num_stats_to_run='.$_REQUEST['num_stats_to_run'].'&delay='.$_REQUEST['delay'].$selected.'" />';
				}
			?>
			
		</head>
		<?php
		
		 echo $output;
		
			if( count($_REQUEST['statSelect']) > 0 )
			{
				?>
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>">STOP NOW</a>
				<?php
			}
			else
			{
				echo '<h1>COMPLETE. <a href="'.$_SERVER['PHP_SELF'].'">Return</a></h1>';
			}
		
		?>
	</html>

<?php


function buildOutput($DBM,  &$statsArray){
	foreach($statsArray AS &$item)
	{
		$fileName = $item['name'];
		$execValue = $item['value'];
		$starttime = microtime(1);
		$output = '';
		// 46 needs a temp table
		if($item['name'] == '46_StudentsScoresByInstance'){
			
			$DBM->query("create temporary table tmp_visit (
				".cfg_obo_Instance::ID." bigint,
				".cfg_core_User::ID." bigint,
				hits int,
				INDEX (".cfg_obo_Instance::ID.", ".cfg_core_User::ID.")
			)");

			$DBM->query("insert into tmp_visit (".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", hits)
				select a.".cfg_obo_Instance::ID.", a.".cfg_core_User::ID.", COUNT(*)
				FROM  ".cfg_obo_Visit::TABLE." AS a
				GROUP BY a.".cfg_core_User::ID.", a.".cfg_obo_Instance::ID."");
		}
		
		$output .= "{$fileName}.csv starting ....";
		if(function_exists($execValue) && is_array($item['args']))
		{
			//$item['args']['statsFileName'] = './stats/'.$fileName.'.csv';
			//call_user_func_array($execValue, $item['args']);
			writeCSVFromString('./stats/'.$fileName.'.csv', call_user_func_array($execValue, $item['args']) );
		}
		else if(isset($item['logTotalsArrayKey']))
		{
			$TM = nm_los_TrackingManager::getInstance();
			if(!isset($v))
			{
				$v = $TM->getInteractionLogTotals();
			}
			writeCSVFromArray('./stats/'.$fileName.'.csv', $v[$item['logTotalsArrayKey']]);
			
		}
		else if(is_array($execValue))
		{
			writeCSVFromArray('./stats/'.$fileName.'.csv', $execValue);
		}
		else
		{
			writeCSVFromQuery('./stats/'.$fileName.'.csv', $DBM, $execValue);	
		}
		$output .=  "complete (". (microtime(1) - $starttime )."s)\n";
	}
	core_util_Log::trace($output);
	return $output;
}

function writeCSVFromQuery($fileName, $DBM,  $query){
	$fh = fopen($fileName, 'w') or die("can't open file");
	$stringData ='';
	if($q = $DBM->query($query))
	{
		while($r = $DBM->fetch_assoc($q)){
			if(!isset($keys)) $keys = '"' . implode('","' , array_keys($r)) . "\"\n";
			$stringData .= '"' . implode('","' , $r) . "\"\n";
		}
	}
	else
	{
		echo mysql_error();
	}
	$stringData = $keys . $stringData;
	fwrite($fh, $stringData);
	fclose($fh);
}


function writeCSVFromString($fileName, $string){
	$fh = fopen($fileName, 'w') or die("can't open file");
	fwrite($fh, $string);
	fclose($fh);
}
function writeCSVFromArray($fileName, $array){
	if(is_array($array))
	{
		$fh = fopen($fileName, 'w') or die("can't open file");
		$stringData ='';
		$keys = '"' . implode('","' , array_keys($array)) . "\"\n";
		$stringData .= '"' . implode('","' , $array) . "\"\n";
	
		$stringData = $keys . $stringData;
		fwrite($fh, $stringData);
		fclose($fh);
	}
}

function getQuestionAnswersByMaster($lo_id, $DBM, $file)
{	
	$times = array();
	$lo = new nm_los_LO();
	if(!$lo->dbGetFull($DBM, $lo_id))
	{
		trace('ERROR no master object found');
		return;
	}
	$ret = '"INSTANCE_ID","STUDENT_ID","MM_DD_YY","TIME","ATTEMPT_NUMBER","QUESTION_ID","QUIZ_QUESTION_NUMBER","ANSWER_NUMBER","ANSWER_ID_GIVEN","STUDENT_SCORE","CHOSEN_ANSWER_TEXT"'."\n";
	
	// find instances of an LO
//	$qstr = "SELECT * FROM `".cfg_obo_Instance::TABLE."` WHERE `".cfg_obo_LO::ID."` = '?'";
	$qstr = "SELECT ".cfg_obo_Instance::ID." FROM ".cfg_obo_Instance::TABLE." WHERE ".cfg_obo_LO::ID." = '?' UNION DISTINCT SELECT ".cfg_obo_Instance::ID." FROM obo_deleted_instances WHERE ".cfg_obo_LO::ID." = '?'";
	
	$q = $DBM->querySafe($qstr, $lo_id, $lo_id);
	$instances = array();
	while($r = $DBM->fetch_obj($q))
	{
		$instances[] = $r->{cfg_obo_Instance::ID};
	}
	// prefetch all the attempts
	$attempts = array();
	$q = $DBM->querySafe("SELECT * FROM obo_log_attempts WHERE loID = '?' AND qGroupID = '?'", $lo_id, $lo->aGroup->qGroupID);
	while($r = $DBM->fetch_obj($q))
	{
		$attempts[$r->attemptID] = true;
	}

	trace(count($instances) . ' instances');
	
	$instances = implode(',', $instances);

	$q = $DBM->querySafeTrace("SELECT *, UNCOMPRESS(".cfg_obo_Track::DATA.") as data FROM ".cfg_obo_Track::TABLE." WHERE (".cfg_obo_Track::TYPE." = 'nm_los_tracking_SubmitQuestion' OR ".cfg_obo_Track::TYPE." = 'nm_los_tracking_StartAttempt' OR ".cfg_obo_Track::TYPE." = 'nm_los_tracking_SubmitMedia')  AND ".cfg_obo_Instance::ID." IN ($instances) ORDER BY ".cfg_obo_Instance::ID.", ".cfg_core_User::ID.", ".cfg_obo_Track::TIME);
	$userAttempts = array();
	while($r = $DBM->fetch_obj($q))
	{

			if($r->{cfg_obo_Track::TYPE} == 'nm_los_tracking_StartAttempt')
			{
				
				$r->data = unserialize($r->data);

				if(isset($attempts[$r->data->attemptID]))
				{
					if(isset($userAttempts[$r->{cfg_core_User::ID}]))
					{
						$userAttempts[$r->{cfg_core_User::ID}]++;
					}
					else
					{
						$userAttempts[$r->{cfg_core_User::ID}] = 1;
					}
				}
				
			}
			elseif($r->{cfg_obo_Track::TYPE} == 'nm_los_tracking_SubmitQuestion')
			{
				$r->data = unserialize($r->data);
				if($lo->aGroup->qGroupID == $r->data->qGroupID)
				{
					$parentGroup = $lo->aGroup->kids;
				}
				else
				{
					continue;
				}

				$question = 0;
				$qIndex =  '?';
				$aIndex = '?';
				foreach($parentGroup AS $key => $qu)
				{
					if($qu->{cfg_obo_Question::ID} == $r->data->questionID)
					{
						$question = $qu;
						$qIndex =  $key+1; 
						break; // question located
					}
				}

				$answer = 0;
				if($question)
				{
					// locate answer given if possible
					switch($question->{cfg_obo_Question::TYPE})
					{
						case 'MC':
							foreach($question->answers AS $key=> $a)
							{
								if($a->{cfg_obo_Answer::ID} == $r->data->answer)
								{
									$aIndex = $key+1;
									$answer = $a;
									break;
								}
							}
							break;
						case 'QA':
							foreach($question->answers AS $a)
							{
								if($a->{cfg_obo_Answer::TEXT} == $r->data->answer)
								{
									$aIndex = $key+1;
									$answer = $a;
									break;
								}
							}
							break;
						case 'Media':
							break;
					}
				}

				$r->page = $qIndex;
				if(is_object($answer))
				{
					$r->score = $answer->weight;
					$r->answerIndex = $aIndex;
					$ret .= '"'. $r->{cfg_obo_Instance::ID}.'","'
							. $r->{cfg_core_User::ID}.'","'
							. date("n/j/Y\",\"G:i:s",$r->{cfg_obo_Track::TIME}).'","'
							. (isset($userAttempts[$r->{cfg_core_User::ID}]) ? $userAttempts[$r->{cfg_core_User::ID}] : 1) .'","'
							. $r->data->questionID . '","'
							. $r->page.'","'
							. $r->answerIndex.'","'
							. strip_tags($r->data->answer).'","'
							. $r->score.'","'
							. ($r->score == 100 ? 'correct' : strip_tags($answer->{cfg_obo_Answer::TEXT}))."\"\n";
				}
				
			}
			else if($r->{cfg_obo_Track::TYPE} == 'nm_los_tracking_SubmitMedia')
			{
				$r->data = unserialize($r->data);
				if($lo->aGroup->qGroupID == $r->data->qGroupID)
				{
					$parentGroup = $lo->aGroup->kids;
				}
				else
				{
					continue;
				}

				$question = 0;
				$qIndex =  '?';
				$aIndex = '?'; 			
				foreach($parentGroup AS $key => $qu)
				{
					if($qu->{cfg_obo_Question::ID} == $r->data->questionID)
					{
						$question = $qu;
						$qIndex =  $key+1; 
						break; // question located
					}
				}
				$r->page = $qIndex;
				$ret .= '"'. $r->{cfg_obo_Instance::ID}.'","'
							. $r->{cfg_core_User::ID} . '","'
							. date("n/j/Y\",\"G:i:s",$r->{cfg_obo_Track::TIME}).'","'
							. (isset($userAttempts[$r->{cfg_core_User::ID}]) ? $userAttempts[$r->{cfg_core_User::ID}] : 1).'","'
							. $r->data->questionID . '","'
							. $r->page . '","M","M","'
							. $r->data->score.'","M"'."\n";
			}

		
	}
	return $ret;
	
}

?>