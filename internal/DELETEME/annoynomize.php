<?php
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$firsts = array('Ringo', 'Paul', 'John', 'Yoko', 'George', 'Mick', 'Keith', 'Ian', 'Ian', 'Brian', 'Andre', 'Neil', 'David', 'Steven', 'Graham' );
$lasts = array('Starr', 'McCartney', 'Lennon', 'Ono', 'Harrison', 'Jagger', 'Richards', 'Stewart', 'Anderson' , 'Jones', '3000', 'Young', 'Crosby', 'Stills', 'Nash');


$q = $DBM->query("SELECT * FROM ".\cfg_core_User::TABLE);
while( $r = $DBM->fetch_obj($q))
{
	$index = rand(0, count($firsts)-1 );
	$email = $firsts[$index] . "@dudes.ucf.edu";
	
	$q2 = $DBM->query("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::FIRST." = '".$firsts[$index]."', ".\cfg_core_User::LAST." = '".$lasts[$index]."', ".\cfg_core_User::EMAIL." = '$email' WHERE ".\cfg_core_User::ID." = '". $r->userID."' AND ".\cfg_core_User::ID." != '1' AND ".\cfg_core_User::ID." != '3'");
}


$pass = md5(time());
$salt = md5(microtime(1));

$DBM->query("UPDATE ".\cfg_plugin_AuthModUCF::TABLE." SET ".\cfg_plugin_AuthModUCF::USER_NAME." = CONCAT('Anonymous', ".\cfg_core_User::ID."), ".\cfg_plugin_AuthModUCF::PASS." = '$pass', ".\cfg_plugin_AuthModUCF::SALT." = '$salt'");

$DBM->query("UPDATE ".\cfg_core_AuthModInternal::TABLE." SET ".\cfg_core_AuthModInternal::USER_NAME." = CONCAT('~Anonymous', ".\cfg_core_User::ID."), ".\cfg_core_AuthModInternal::PASS." = '$pass', ".\cfg_core_AuthModInternal::SALT." = '$salt' WHERE ".\cfg_core_User::ID." != '3' AND ".\cfg_core_User::ID." != '1'");

// TODO: Empty MODX  web_users, web_groups and  web_users_attributes

echo "done";

?>