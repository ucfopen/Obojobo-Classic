<?php
if (empty($argv[1]) || empty($argv[2]))
{
	print("Missing password or userid?\r\n");
	print("ex: php generate_password.php <userid> <new password>\r\n");
	print("ex: php generate_password.php 236 myNewPassWord123\r\n");
	exit();
}

# generate a uniqe salt
$salt = md5(uniqid());

# md5 the new password
$md5Password = md5(trim($argv[2]));

# create the new salted password to be stored in the database
$dbPassword = md5($salt.$md5Password);

print("\n=============== SQL for password '$argv[2]' =============\r\n");

echo "INSERT INTO `obo_user_meta` (`userID`, `meta`, `value`) VALUES ($argv[1], 'salt', '$salt') ON DUPLICATE KEY UPDATE `value` = '$salt';\r\n";
echo "INSERT INTO `obo_user_meta` (`userID`, `meta`, `value`) VALUES ($argv[1], 'password', '$dbPassword') ON DUPLICATE KEY UPDATE `value` = '$dbPassword';\r\n";
