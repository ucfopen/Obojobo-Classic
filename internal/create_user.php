<?php
require_once('app.php');

$u = getopt("", ['username:', 'name:', 'email:', 'password:']);

if(empty($u['username']) || empty($u['name']) || empty($u['email']) || empty($u['password']))
{
	echo("Error, missing arguments\r\n");
	echo("EX: php create_user.php --username sampleuser --name 'Ian E Turgeon' --email test@mail.com --password rocketduck\r\n");
	exit(1);
}

$name = explode(' ', $u['name']);


$AuthMod = new \rocketD\auth\ModInternal();
$result = $AuthMod->createNewUser($u['username'], $name[0], $name[1], $name[2], $u['email'], ["MD5Pass" => md5($u['password'])]);

print_r($result);

if(!$result['success']){
	echo("Error creating user.\r\n");
	exit(1);
}

$PermMan = \obo\perms\RoleManager::getInstance();
$PermMan->addUsersToRoles_SystemOnly([$result['userID']], [\obo\perms\Role::CONTENT_CREATOR, \obo\perms\Role::SUPER_STATS]);

echo("User created.\r\n");
