<?php
$ASE_timestamp = '1264103416';
$ASE_time = 'January 21, 2010, 2:50 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_doc_raw = <<<'NOWDOC'
a:37:{s:2:"id";s:2:"52";s:4:"type";s:8:"document";s:11:"contentType";s:9:"text/html";s:9:"pagetitle";s:40:"Importing Obojobo Scores Into Webcourses";s:9:"longtitle";s:0:"";s:11:"description";s:0:"";s:5:"alias";s:40:"importing-obojobo-scores-into-webcourses";s:15:"link_attributes";s:0:"";s:9:"published";s:1:"1";s:8:"pub_date";s:1:"0";s:10:"unpub_date";s:1:"0";s:6:"parent";s:2:"11";s:8:"isfolder";s:1:"0";s:9:"introtext";s:0:"";s:7:"content";s:5216:"<markdown>
Importing Obojobo Scores into Webcourses
=================================

This guide covers how to take scores inside Obojobo and import them into your online Webcourses course.
<script type="text/javascript">
		// SWFOBJECT
		var flashvars = new Object();
flashvars.file = "/assets/video/exportingScores.mov";
flashvars.image = "/assets/video/exportingScores.jpg";		

		var params = new Object();
		params.menu = "false";
		params.allowScriptAccess = "always";
		params.allowFullScreen = "true";
		params.base = "/assets/flash/";

		var attributes = new Object(); 
		attributes.id = "video";
		attributes.name = "video";

		swfobject.embedSWF( "/assets/flash/player.swf", "video", "640", "456", "10",  "/assets/flash/expressInstall.swf", flashvars, params, attributes);
</script>
<div id="video">
<div style="margin: 0 auto; margin-top: 4em; border: thin solid gray; padding: 20px; width: 500px; color: #222222; font-family: Verdana,sans-serif; font-size: 73%; line-height: 130%;">
            <a style="padding-right: 20px; width: 158px; float: left; border: 0px;" href="http://www.adobe.com/go/getflashplayer"><img src="/assets/images/get_adobe_flash_player.png" alt="Download Flash Player" /></a>
            <p style="padding: 0; margin: 0; float: left; width: 320px;">
                This video requires that you have the Flash Player plug-in (version 10 or greater) installed.<br /><a href="http://www.adobe.com/go/getflashplayer">Click here to download the latest version.</a>
            </p>
            <div style="clear:both;"></div>
        </div>
</div>

1. Exporting scores from Obojobo
================================

1. In the Repository, navigate to the 'Published Instances' tab.
1. Select the instance you wish to collect scores from.
1. Click on the 'Assessment Scores' tab.
1. Click on the purple 'Download Scores' button.

<img src="/assets/images/help/create/download-scores.png" alt="Download Scores Button" />

This will download a .csv ("comma seperated value") file.  This is simply a text file with the score information collected from Obojobo.  If you open this file in a spreadsheet program such as Microsoft Excel or OpenOffice Calc you can see a formatted view of the data that was exported.  You will need this file later, so save it in a location you will remember. 

<img src="/assets/images/help/create/csv.png" alt="CSV File" />

2. Create a new column in your Webcourses course
=========================================================

1. Open the 'Gradebook' for your Webcourses course.
<img src="/assets/images/help/create/webcourses-gradebook.png" width="600" alt="Webcourses Gradebook" />
1. Create a new numeric column to hold the scores you will be importing.  To do this, click on **'Create Column'** and select **'Numeric'**.
<img style="display: block;" src="/assets/images/help/create/create-new-column.png" alt="Creating a new column." />
1. This will bring up a form to create a new column.
<img src="/assets/images/help/create/create-column-form.png" width="600" alt="New column form" />
1. Supply a Column Label (in the example image we have simply used "Obojobo Scores")
1. Set the Decimals combo-box to **0**
1. Set the Maximum value field to **100** (All scores in Obojobo are simply 0-100)
1. Click '**Save**'.

3. Importing the Obojobo scores .csv file into Webcourses
=========================================================

1. Click on the '**Import From Spreadsheet**' button.
<img src="/assets/images/help/create/click-on-import.png" width="601" alt="Import From Spreadsheet button" />
1. This will bring up the import page.  Select the .csv file you exported from Obojobo in the first section.
1. Leave the other values as their defaults ('Comma' and 'Unicode (UTF-8)') and click '**Upload**'.
<img src="/assets/images/help/create/import-file-form.png" alt="Import From Spreadsheet form" />
1. The next page will allow you to match information contained in the uploaded csv with your Webcourses gradebook.  You should see that the "User ID" field has a green check graphic indicating that users contained in Webcourses and Obojobo have matched up.  Leave the 'First Name' and 'Last Name' columns as their default value of '**- Do not import -**'.
1. Click on the combo-box next to 'Score' and select the name of the column you created in section 2.  In the case of this example, that would be "Obojobo Scores".
1. Click on the '**Import**' button.
<img src="/assets/images/help/create/import-file-form-2.png" alt="Import From Spreadsheet form" />
1. The next page will inform you of any omitted columns and/or any additonal users present in the uploaded .csv file that are not in your Webcourses course.  You should see that the Last Name and First Name columns were not imported, which is expected and will not affect the import.  Click '**OK**' to finish the procedure.
<img style="display:block;" src="/assets/images/help/create/import-file-form-4.png" alt="Import From Spreadsheet form" />

Now you should see the Obojobo scores show up in the column you created.
<img src="/assets/images/help/create/final.png" alt="Successful import" />

</markdown>";s:8:"richtext";s:1:"1";s:8:"template";s:1:"4";s:9:"menuindex";s:1:"8";s:10:"searchable";s:1:"1";s:9:"cacheable";s:1:"1";s:9:"createdby";s:1:"8";s:9:"createdon";s:10:"1247694002";s:8:"editedby";s:1:"8";s:8:"editedon";s:10:"1264103416";s:7:"deleted";s:1:"0";s:9:"deletedon";s:1:"0";s:9:"deletedby";s:1:"0";s:11:"publishedon";s:10:"1247766876";s:11:"publishedby";s:1:"8";s:9:"menutitle";s:0:"";s:7:"donthit";s:1:"0";s:11:"haskeywords";s:1:"0";s:11:"hasmetatags";s:1:"0";s:10:"privateweb";s:1:"1";s:10:"privatemgr";s:1:"0";s:13:"content_dispo";s:1:"0";s:8:"hidemenu";s:1:"0";}';
$ASE_doc_map_to_tv_value_raw = <<<'NOWDOC'
a:1:{i:0;a:4:{s:2:"id";s:2:"13";s:9:"tmplvarid";s:1:"5";s:9:"contentid";s:2:"52";s:5:"value";s:70:"<script type="text/javascript" src="/assets/js/swfobject.js"></script>";}}'
NOWDOC;
$ASE_doc_map_to_group_raw = <<<'NOWDOC'
a:1:{i:0;a:3:{s:2:"id";s:2:"19";s:14:"document_group";s:1:"1";s:8:"document";s:2:"52";}}'
NOWDOC;
?>