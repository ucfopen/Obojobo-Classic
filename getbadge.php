<?php
require_once("internal/app.php");
$API = \obo\API::getInstance();


$loggedIn = $API->getSessionValid();


if($loggedIn === true )
{
        $url     = \AppCfg::CREDHUB_URL;
        $data    = $_POST;
        $headers = ['Content-Type: application/x-www-form-urlencoded', 'Accept: text/html'];
        $data    = http_build_query($data);


        // use php streams
        $stream_context = stream_context_create(['http' => ['method' => 'POST', 'content' => $data, 'header' => implode("\r\n", $headers)]]);
        try
        {
                $file = fopen($url, 'r', false, $stream_context);
		fpassthru($file);
		fclose($file);
                exit();
        }
        catch (\Exception $e)
        {
                trace($e, true);
        }
}
else
{
        header('HTTP/1.0 401 Unauthorized');
}

