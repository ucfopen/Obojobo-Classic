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
	
	$q2 = $DBM->query("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::LOGIN." = CONCAT('Anonymous', ".\cfg_core_User::ID."). ", ".\cfg_core_User::FIRST." = '".$firsts[$index]."', ".\cfg_core_User::LAST." = '".$lasts[$index]."', ".\cfg_core_User::EMAIL." = '$email' WHERE ".\cfg_core_User::ID." = '". $r->userID."' AND ".\cfg_core_User::ID." != '1' AND ".\cfg_core_User::ID." != '3'");
}

// remove all the passwords from the database except su
$DBM->query("DELETE FROM obo_user_meta WHERE ".\cfg_core_User::ID." != '3' AND ".\cfg_core_User::ID." != '1'");


echo "done";

?>