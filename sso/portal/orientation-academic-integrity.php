<?php
// This little masterpiece redirects the single sign on links coming out of the UCF portal to
// another page so that the sso variables aren't in the url.  Yea, sending a POST request
// would be a better idea but getting CS&T to update code in Peoplesoft pagelets is a little
// like watching babies invent the wheel.  So, we'll do it instead.  STAY AGILE!

require_once(dirname(__FILE__)."/../../internal/app.php");

// try loggin in using the current auth modules
\rocketD\auth\AuthManager::getInstance()->login('', '');

// 307 Temporary Redirect
header("Location: /sso/portal/academic-integrity-modules.php", true, 307);
