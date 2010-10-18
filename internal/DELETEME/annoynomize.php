<?php
require_once(dirname(__FILE__)."/../app.php");

$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));

$firsts = array('Ringo', 'Paul', 'John', 'Yoko', 'George', 'Mick', 'Keith', 'Ian', 'Ian', 'Brian', 'Andre', 'Neil', 'David', 'Steven', 'Graham' );
$lasts = array('Starr', 'McCartney', 'Lennon', 'Ono', 'Harrison', 'Jagger', 'Richards', 'Stewart', 'Anderson' , 'Jones', '3000', 'Young', 'Crosby', 'Stills', 'Nash');


$q = $DBM->query("SELECT * FROM lo_users");
while( $r = $DBM->fetch_obj($q))
{
	$index = rand(0, count($firsts)-1 );
	$email = $firsts[$index] . "@dudes.ucf.edu";
	
	$q2 = $DBM->query("UPDATE lo_users SET first = '".$firsts[$index]."', last = '".$lasts[$index]."', email = '$email' WHERE userID = '". $r->userID."' AND userID != '1' AND userID != '3'");
}




$pass = md5(time());
$salt = md5(microtime(1));

$DBM->query("UPDATE lo_auth_ucf SET login = CONCAT('Anonymous', userID), password = '$pass', salt = '$salt'");


$DBM->query("UPDATE lo_auth_internal SET login = CONCAT('~Anonymous', userID), password = '$pass', salt = '$salt' WHERE userID != '3' AND userID != '1'");

?>