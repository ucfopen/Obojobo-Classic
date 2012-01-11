<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<label for="instID">instID:</label><input type="text" name="instID" value="<?php echo $_REQUEST['instID'];?>" id="instID"><br>
	<input type="submit" name="submit" value="Look Up New Instance" >
	<?php echo rocketD_admin_tool_get_form_page_input(); ?>
</form>

<?php

	if($_REQUEST['instID'])
	{

		$API = \obo\API::getInstance();
		$iData = $API->getInstanceData($_REQUEST['instID']);

		echo "<pre>[ID]: $iData->instID\n[Name]: $iData->name\n[Course]: $iData->courseID\n[by] $iData->userName ($iData->userID)\nCourse Data:  ";
		print_r($iData->courseData);
		echo "==============================\n\n";

		$am = \rocketD\auth\AuthManager::getInstance();
		$nid = $am->getUserName($iData->userID);
		echo $nid . "'s Courses:<br>";
		flush();
		$PM = \rocketD\plugin\PluginManager::getInstance();
		$result = $PM->callAPI('UCFCourses', 'testOnlyGetCourses', array($nid), true);

		if(is_array($result['errors']) && count($result['errors']) > 0)
		{
			print_r($result['errors']);
		}
		else
		{
			foreach($result['courses'] AS $course)
			{
				switch($course->type)
				{
					case 'ps_only':
						echo "PS: $course->ps_prefix $course->ps_number $course->ps_section $course->ps_semester $course->ps_year $course->ps_title\n";
					    break;
					case 'wc_only':
						if(isset($_REQUEST['sectionID']) && $_REQUEST['sectionID'] == $course->wc_learning_context_id)
						{
							$selectedCourse = $course;
						}
						echo "WC: [<a href=\"$_SERVER[REQUEST_URI]&sectionID=$course->wc_learning_context_id\">$course->wc_learning_context_id</a>] $course->wc_course $course->wc_section\n";
						break;
					case 'related':
						if(isset($_REQUEST['sectionID']) && $_REQUEST['sectionID'] == $course->wc_learning_context_id)
						{
							$selectedCourse = $course;
						}

						echo "Related: [<a href=\"$_SERVER[REQUEST_URI]&sectionID=$course->wc_learning_context_id\">$course->wc_learning_context_id</a>] $course->wc_course $course->wc_section $course->ps_title\n";
						break;
				}
			}
		}

		echo "</pre>";

		if(!isset($_REQUEST['sectionID']))
		{
			?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
			<label for="sectionID">sectionID:</label><input type="text" name="sectionID" value="<?php echo $_REQUEST['sectionID'];?>" id="instID"><br>
			<input type="hidden" name="instID" value="<?php echo $_REQUEST['instID'];?>" id="instID">
			<input type="submit" name="submit" value="Setup Syncing" >
			<?php echo rocketD_admin_tool_get_form_page_input(); ?>
			</form>
			<?php
		}
		else
		{

			function getTermStr($o)
            {
                $sem = strtolower($o->semester);
                
                if($sem == 'fall') 
                {
                    $sem = 'Fall';
                }
                
                if($sem == 'spring')
                {
                    $sem = 'Spr';
                }
                
                if($sem == 'summer')
                {
                    $sem = 'Sum';
                }
                
                return $sem + " '" + substr($o->year, 2, 2);
            }

			echo "<pre>";
			// test ucf api
			$sectionID = $_REQUEST['sectionID'];
			$columnTitle = $iData->name;
			$instID = $_REQUEST['instID'];
			
			echo "instance ID: $instID\n";
			echo "sectionID: $sectionID\n";
			echo "title: $columnTitle\n";
			echo "Creating Column, please wait...\n\n";
			$t = microtime(true);
			flush();
			
			// The plugin requires you to be an instructor in the course
			// we'll need to become that user - session wise - for the next call

			// call the plugin to create the column
			$PM = \rocketD\plugin\PluginManager::getInstance();
			$column = $PM->callAPI('UCFCourses', 'createColumn', array($instID, $sectionID, $columnTitle . '8', $iData->userID), true);


			if( $column instanceof \rocketD\util\Error)
			{
				echo "!!!!!!!FAILED!!!!!!!!!\n";
				print_r($column);
			}
			else
			{
				// it worked - update the instance
				if($selectedCourse->type == 'related' || $selectedCourse->type == 'ps_only')
                {
					
                    $cName =  $selectedCourse->ps_prefix . ' ' . $selectedCourse->ps_number . ' (' + $selectedCourse->ps_section . ') - ' . getTermStr(o) . ': ' . $selectedCourse->ps_title;
                }
                else if($selectedCourse->type == 'wc_only')
                {
					$cName = $selectedCourse->wc_course . ' - ' . $selectedCourse->wc_section;
				}
				else
				{
					$cName = $iData->courseID;
				}

				$instData = $API->editInstance($iData->name, $iData->instID, $cName, $iData->startTime, $iData->endTime, $iData->attemptCount, $iData->scoreMethod, (bool)$iData->allowScoreImport);

				$t = (microtime(true) - $t);
				echo "created in $t sec \n-----------------------------------------\n";
				print_r($column);
				print_r($instData);
			}

			echo "</pre>";

		}


	}

?>