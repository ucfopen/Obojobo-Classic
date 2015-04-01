<?php

$_SESSION = array();

function createTestCase($customParams, $endpoint, $user = false, $passbackUrl = false)
{
	$key = \AppCfg::LTI_OAUTH_KEY;
	$secret = \AppCfg::LTI_OAUTH_SECRET;

	$baseParams = array(
		'resource_link_id'     => 'test-resource',
		'context_id'           => 'test-context',
		'lis_result_sourcedid' => 'test-source-id',
		'roles'                => 'Learner'
	);

	$params = array_merge($baseParams, $customParams);

	if(!$user)
	{
		$api = \obo\API::getInstance();
		$user = $api->getUser();
	}
	$params = \lti\OAuth::buildPostArgs($user, $endpoint, $params, $key, $secret, $passbackUrl);
	trace('****************built params');
	trace($params);

	return array($params, $endpoint);
}

function getAdminUser()
{
	$AM = \rocketD\auth\AuthManager::getInstance();
	return $AM->fetchUserByID(6661);
}

function createNewRandomUser()
{
	//($userID=0, $login='', $first='', $last='', $mi='', $email='', $createTime=0, $lastLogin=0)
	$rand  = substr(md5(microtime()), 0, 10);
	$login = 'testltiuser' . $rand;
	$email = 'testltiuser' . $rand . '@obojobo.com';
	$first = 'Unofficial Test';
	$last  = 'User';

	return new rocketD\auth\User(0, $login, $first, $last, 'T', $email);
}

$ltiUrl           = \AppCfg::URL_WEB.'/lti';
$pickerUrl        = $ltiUrl.'/picker.php';
$assignmentUrl    = $ltiUrl.'/assignment.php';
$validateUrl      = $ltiUrl.'/test/validate';
$gradePassbackUrl = $ltiUrl.'/lti-test-return.php';
$launchReturnUrl  = $ltiUrl.'/return.php';
$copiedPickerUrl  = $assignmentUrl.'?instID=666'; //@TODO <- Don't hardcode an instanceID here

// As Instructor
$instructor = createTestCase(array('roles' => 'Instructor'), $pickerUrl, getAdminUser());

// As NewInstructor
$instructorNew = createTestCase(array('roles' => 'Instructor'), $pickerUrl, createNewRandomUser());

// As Instructor (-> assignment)
$instructorAssignment = createTestCase(array('roles' => 'Instructor'), $assignmentUrl, getAdminUser());

// As Learner
$learner = createTestCase(array(), $assignmentUrl, getAdminUser(), $gradePassbackUrl);

// As New Learner
$learnerNew = createTestCase(array(), $assignmentUrl, createNewRandomUser(), $gradePassbackUrl);

// As Learner (-> picker)
$learnerPicker = createTestCase(array(), $pickerUrl, getAdminUser());

// As Unknown Role
$unknownRole = createTestCase(array('roles' => 'An Invalid Role'), $assignmentUrl, getAdminUser());

// As Unknown Assignment
$unknownAssignment = createTestCase(array('resource_link_id' => 'an-invalid-resource'), $assignmentUrl, getAdminUser());

// As Unknown User
$mockUser = new rocketD\auth\User();
$unknownUser = createTestCase(array(), $assignmentUrl, $mockUser);

// As Test User
$testUser = new rocketD\auth\User(0, '', 'Test', 'Student', '', 'notifications@instructure.com');
$asTestUser = createTestCase(array(), $assignmentUrl, $testUser, $gradePassbackUrl);

// Picker redirect
$pickerRedirect = createTestCase(array(
	'roles' => 'Instructor',
	'launch_presentation_return_url' => $launchReturnUrl,
	'selection_directive' => 'select_link')
	, $pickerUrl, createNewRandomUser()
);

$copiedPicker = createTestCase(array(), $copiedPickerUrl, createNewRandomUser(), $gradePassbackUrl);

// Validation
$validation = createTestCase(array(), $validateUrl, getAdminUser());

// render page:
$smarty = \rocketD\util\Template::getInstance();

$smarty->assign('instructorParams', $instructor[0]);
$smarty->assign('instructorEndpoint', $instructor[1]);

$smarty->assign('instructorNewParams', $instructorNew[0]);
$smarty->assign('instructorNewEndpoint', $instructorNew[1]);

$smarty->assign('instructorAssignmentParams', $instructorAssignment[0]);
$smarty->assign('instructorAssignmentEndpoint', $instructorAssignment[1]);

$smarty->assign('learnerParams', $learner[0]);
$smarty->assign('learnerEndpoint', $learner[1]);

$smarty->assign('learnerNewParams', $learnerNew[0]);
$smarty->assign('learnerNewEndpoint', $learnerNew[1]);

$smarty->assign('learnerPickerParams', $learnerPicker[0]);
$smarty->assign('learnerPickerEndpoint', $learnerPicker[1]);

$smarty->assign('unknownRoleParams', $unknownRole[0]);
$smarty->assign('unknownRoleEndpoint', $unknownRole[1]);

$smarty->assign('unknownAssignmentParams', $unknownAssignment[0]);
$smarty->assign('unknownAssignmentEndpoint', $unknownAssignment[1]);

$smarty->assign('unknownUserParams', $unknownUser[0]);
$smarty->assign('unknownUserEndpoint', $unknownUser[1]);

$smarty->assign('testUserParams', $asTestUser[0]);
$smarty->assign('testUserEndpoint', $asTestUser[1]);

$smarty->assign('pickerRedirectParams', $pickerRedirect[0]);
$smarty->assign('pickerRedirectEndpoint', $pickerRedirect[1]);

$smarty->assign('copiedPickerParams', $copiedPicker[0]);
$smarty->assign('copiedPickerEndpoint', $copiedPicker[1]);

$smarty->assign('validationParams', $validation[0]);
$smarty->assign('validationEndpoint', $validation[1]);

$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-test.tpl');
echo $response;
