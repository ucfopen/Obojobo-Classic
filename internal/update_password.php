<?php
if (empty($argv[1]))
{
	print("Missing username\r\n");
	print("ex: php update_password.php <username>\r\n");
	print("ex: php update_password.php obojobo_admin\r\n");
	exit(1);
}

require_once('app.php');
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));
$q = $DBM->querySafe("SELECT * FROM obo_users where login='?'", $argv[1]);
if( ! ($r = $DBM->fetch_obj($q)))
{
	print("'{$argv[1]}' is not in the Obojobo user table\r\n");
	exit(1);
}

$password = substr(md5(uniqid()), 15);
exec("php ".__DIR__."/generate_password.php {$r->userID} $password --return-query", $queries);

foreach ($queries as $query)
{
	$q = $DBM->query($query);
}

echo("{$argv[1]}'s new password is '{$password}'\r\n");
