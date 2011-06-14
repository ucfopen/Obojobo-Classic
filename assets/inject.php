<?php
require_once(dirname(__FILE__)."/../app.php");

$instIDs = explode(',', $_GET['instIDs']);
$AM = \rocketD\auth\AuthManager::getInstance();


echo "var e = document.getElementById('obojobo-badge-".$_GET['instIDs']."');\n";
echo "if(e) {\n";
$s = <<<JS
var html = "<div style='font-family:\"Lucida Grande\",\"Verdana\",\"Arial\",sans-serif;width:425px;border:thin solid #e3d9c1;background:-webkit-gradient(linear,left top,left bottom,from(#fdfbf3),to(#f9ebca));'><div style='background-color:white;border:thin solid #d6c59c;margin:5px;'><div style='float:left;border-bottom:thin solid #d6c59c;background-color:#f9f9f9;width:413px;'><div style='background:url(\"assets/images/logo.png\");width:92px;height:54px;float:left;'></div><div style='width:305px;font-size:11px;color:#303030;float:left;padding:5px;padding-left:10px;'>You have an assignment in Obojobo, UCF's Learning Object system. Click the links below to begin. View the <a href='0'>Student Quick Start Guide</a> for more information.</div></div><div style='clear:both;padding:10px;'><p style='font-size:10pt;font-weight:bold;color:#565656;margin:0;padding:0;'>Complete these learning objects:</p><ul><li style='color:#A0A0A0;padding-bottom:7px;'><a style='font-size:12pt;font-weight:bold;' href='a'>Citing Sources using MLA Style</a><div style='font-size:8pt;color:#464646;'>Due by <strong>6/12/11 at 9pm</strong></div></li><li style='color:#A0A0A0;padding-bottom:7px;'><a style='font-size:12pt;font-weight:bold;' href='b'>Citing Sources using APA Style for use in Medical courses II</a><div style='font-size:8pt;color:#464646;'>Due by <strong>6/13/11 at 11:59pm</strong></div></li><li style='color:#A0A0A0;padding-bottom:0px;'><a style='font-size:12pt;font-weight:bold;' href='c'>Citing Sources using APA Style for use in Medical courses III</a><div style='font-size:8pt;color:#464646;'>Due by <strong>6/13/11 at 11:59pm</strong></div></li></ul></div></div></div>";

JS;
echo $s;
echo "e.innerHTML = html;\n";
echo "}";
?>