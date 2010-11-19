<?php

class plg_sets_Set
{
	public 	$id,		#modules unique identifier
		$creator,	#creator of the set
		$header,	#creator inputed header for set
		$footer,	#creator inputed footer for set
		$title,		#name/title of set
		$disabled,	#this set is disabled (boolean)
		$description,	#creators description of the set
		$style_sheet,	#custom style for the set
		$additionals,	#additional sections for this set
		$lo_section_order,	#y-index of where lo section will appear
		$learning_objects;	#learning objects for this set    
	
	private $API,		#Mindshare Repository to pull data from
		$module_dir,		#Where all the modules reside
		$template_dir;	#ref to template directory
	
	public static function cmpSection($x, $y)
	{
		if ($x->order == $y->order)
		{
			return 0;
		}
		return ($x->order < $y->order)?-1:1;
	}
		
	public function getAdditionalsHTML()
	{
		$out = '';
		if(count($this->additionals) > 0)
		{
			usort($this->additionals, array('plg_sets_Set','cmpSection'));
			
			foreach ($this->additionals as $section)
			{
				$out .= $section->toHTML();
			}
		}
		return $out;
	}
	
	# removeTextFormat:
	#	Removes the text format tags that come out of obojobo stuff
	private function removeTextFormat($string)
	{
		if (!is_string($string))
		{
			return $string;
		}
		$find    = "/\<[\/]?textformat[^\>]*\>/Ui";
		$replace = "";
		$string  = preg_replace($find, $replace, $string);
		return $string;
	}
	
	# testTextFormat:
	#	simple unit test for removeTextFormat
	private function testTextFormat()
	{
		$original = <<<HTML
<textformat name='arg' pirates='funny' nonyabusiness='none'>Whatttttt what what!!!?</textformat>
<textformat name='arg' pirates='funny' nonyabusiness='none'>apple</textformat>
peanut butter
HTML;
		$text     =  <<<HTML
Whatttttt what what!!!?
apple
peanut butter
HTML;
		// Active assert and make it quiet
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 1);
		assert_options(ASSERT_QUIET_EVAL, 1);
		
		// Create a handler function
		function my_assert_handler($file, $line, $code)
		{
		    echo "<hr>Assertion Failed:
			File '$file'<br />
			Line '$line'<br />
			Code '$code'<br /><hr />";
		}
		
		// Set up the callback
		assert_options(ASSERT_CALLBACK, 'my_assert_handler');
		return assert($text===$this->removeTextFormat($original));
	
	}
	
	# getLearningObjectsHTML:
	#	Using learning object ids for this object, pull the metadata
	#	from the repository and return an HTML formatted string representing
	#	the learning objects
	public function getLearningObjectsHTML()
	{
		#Create a list if there is a list to create!
		$output = '';
		if (count($this->learning_objects))
		{
			$output = "<ul class='learningObjects'>\n";
		}
		foreach ($this->learning_objects as $instance)
		{
			$metadata = $this->repository->getInstanceData($instance->id);
			$loMan = nm_los_LOManager::getInstance();
			
			$lo = $loMan->getLO($metadata->loID);
			#Tidy up any missing data
			if (empty($metadata->name)) 
				$metadata->name = "No Title";
			if (empty($lo->objective)) 
				$lo->objective = false;
			if (empty($metadata->description))
				$metadata->desc->text = false;
			if (empty($lo->learnTime))
				$lo->learnTime = false;
			
			#Remove textformat where appropriate
			$metadata->name = $this->removeTextFormat($metadata->name);
			$metadata->objective  = $this->removeTextFormat($lo->objective);
			
			#Set up this lo's output
			$output .= "<li class='learningObject' id='lo_{$instance->id}'>\n";
			$output .= "<h2 class='title'><a href='{$GLOBALS['VIEWER_URL']}?view={$instance->id}'>{$metadata->name}</a></h2>\n";
			if ($lo->learnTime)
				$output .= "<div class='learnTime'>{$lo->learnTime} minute(s)</div>\n";
			if ($lo->objective)
				$output .= "<div class='objective'><h3>Objective</h3> {$lo->objective}</div>\n";
			$output .= "</li>\n";
		}
		if (count($this->learning_objects))
		{
			$output .= "</ul>\n";
		}
		return $output;
	}
	
	# loadSet:
	#	Using this object's id, we pull the modules data from the
	#	stored xml. Set this objects elements to the data pulled
	#	from the xml.  Throws an exception if module does not exist.
	public function loadSet()
	{
		$xml = @file_get_contents($this->module_dir.'data/sets/'.$this->id.'.xml');
		if (!$xml) throw new Exception("Set {$this->id} does not exist");
		
		$module = new SimpleXMLElement($xml);
		
		$this->creator          = (string)$module->creator;
		$this->disabled         = (boolean)(string)$module->disabled;
		$this->header           = (string)$module->header;
		$this->footer           = (string)$module->footer;
		$this->title            = (string)$module->title;
		$this->description      = (string)$module->description;
		$this->style_sheet      = (string)$module->style_sheet;
		$this->learning_objects = array();
		$this->additionals      = array();
		$this->lo_section_order = (int)$module->lo_section["order"];
		
		#Parse additional sections
		foreach ($module->additionals->section as $section)
		{
			$type = (string)$section['type'];
			switch ($type)
			{
			case 'asset':
				$temp = new plg_sets_Asset($section->asXML());
				break;
			case 'link':
				$temp = new plg_sets_Link($section->asXML());
				break;
			case 'text':
				$temp = new plg_sets_Text($section->asXML());
				break;
			default:break;
			}
			array_push($this->additionals, $temp);
		}
		
		#Parse learning objects
		foreach ($module->lo_section->learning_object as $learning_object)
		{
			$i = count($this->learning_objects);
			$this->learning_objects[$i] = new stdClass();
			$this->learning_objects[$i]->id = (int)$learning_object["id"];
			$this->learning_objects[$i]->instance = (boolean)(string)$learning_object["instance"];
		}
		
		#printer($this);
		return TRUE;
	}
	
	# getNewId:
	#	finds the next available id for the set, uses a lock
	#	file to ensure no two sets are created at the same time and thus
	#	get the same id.
	public function getNewId()
	{
		$lock_filename = $this->module_dir."data/sets/.lock";
		
		#Lock file tells us if something else is pulling an id
		while (file_exists($lock_filename))
		{
			$createTime = filectime($lock_filename);
			
			#if the lockfile was created longer than 5 seconds ago
			#delete it, the other script has probably stopped responding 
			if (time() - $createTime > 5)
			{
				unlink($lock_filename);
			}
		}
		#create lock file
		touch($lock_filename);
		
		#get new id
		$current_sets = scandir($this->module_dir.'data/sets/');
		sort($current_sets, SORT_NUMERIC);
		$new_id = array_pop($current_sets) + 1;
		
		#delete lock file
		unlink($lock_filename);
		return $new_id;
	}
	
	# saveSet:
	#	Writes this objects data to an xml file.  Returns TRUE upon
	#	success, throws an exception on failure.  Should determine
	#	if this is a new set to save or edit of older set.
	public function saveSet()
	{
		#determine if set already exists
		$exists = (isset($this->id)) ? file_exists($this->module_dir.'data/sets/'.$this->id.'.xml') : FALSE;
		
		if (!$exists)
		{
			#get new set id
			$this->id = $this->getNewId();
			
			#create new user xml file if necessary
			if (!file_exists($this->user_dir.$this->creator.'.xml'))
			{
				$xml = file_get_contents($this->template_dir."user.xml");
				$xml = str_replace("%%SITE_DOMAIN%%", "{$GLOBALS['SITE_DOMAIN']}", $xml);
				$xml = str_replace("%%USER%%", $this->creator, $xml);
				$xml = str_replace("%%SETS%%", null, $xml);
				file_put_contents($this->user_dir.$this->creator.'.xml', $xml);
			}
			
			#add set id to creator xml file
			$xml = file_get_contents($this->user_dir.$this->creator.'.xml');
			$user = new SimpleXMLElement($xml);
			
			$new_set = $user->addChild("set");
			$new_set->addAttribute("id", $this->id);
			
			file_put_contents($this->user_dir.$this->creator.'.xml', $user->asXML());
		}
		
		#compose xml
		$xml = file_get_contents($this->template_dir."set.xml");
		$xml = str_replace("%%SITE_DOMAIN%%", "{$GLOBALS['SITE_DOMAIN']}", $xml);
		$xml = str_replace("%%DISABLED%%", $this->disabled, $xml);
		$xml = str_replace("%%CREATOR%%", $this->creator, $xml);
		$xml = str_replace("%%HEADER%%", xmlEntities($this->header), $xml);
		$xml = str_replace("%%FOOTER%%", xmlEntities($this->footer), $xml);
		$xml = str_replace("%%TITLE%%", xmlEntities($this->title), $xml);
		$xml = str_replace("%%STYLE_SHEET%%", xmlEntities($this->style_sheet), $xml);
		$xml = str_replace("%%DESCRIPTION%%", xmlEntities($this->description), $xml);
		$xml = str_replace("%%LO_SECTION_ORDER%%", $this->lo_section_order, $xml);
		
		#create learning objects string
		$learning_objects = '';
		for ($i = 0; $i < count($this->learning_objects); $i++)
		{
			$learning_objects .= "<learning_object ".
				"id=\"{$this->learning_objects[$i]->id}\" ".
				"instance=\"".(boolean)$this->learning_objects[$i]->instance."\">".
				"</learning_object>\n";
		}
		
		#Create additional sections string
		$additional_sections = '';
		if(count($this->additionals) > 0)
		{
			foreach ($this->additionals as $section)
			{
				$additional_sections .= str_replace('<?xml version="1.0"?>', '', $section->toXML());
				$additional_sections .= "\n";
			}
		}
		
		$xml = str_replace("%%LEARNING_OBJECTS%%", $learning_objects, $xml);
		$xml = str_replace("%%ADDITIONAL_SECTIONS%%", $additional_sections, $xml);
		#save xml to file
		file_put_contents($this->module_dir.'data/sets/'.$this->id.'.xml', $xml);
	}
	
	# __construct:
	public function __construct($id=NULL, $API=NULL, $module_dir=NULL, $user_dir=NULL, $template_dir=NULL)
	{
		#$this->testTextFormat();
		// TODO: problem here as these values aren't in GLOBAL
		if ($API == NULL) $API = $GLOBALS['API'];
		if ($module_dir == NULL) $module_dir = $GLOBALS['MODULE_FOLDER'];
		if ($sets_dir == NULL) $sets_dir = $GLOBALS['SETS_FOLDER'];
		if ($user_dir == NULL) 	 $user_dir   = $GLOBALS['USER_FOLDER'];           
		if ($template_dir == NULL)	$template_dir = $GLOBALS['TEMPLATE_FOLDER'];
		#use id to load module data   
		$this->template_dir = $template_dir;
		$this->user_dir = $user_dir;
		$this->module_dir = $module_dir;
		$this->repository = $API;
		$this->learning_objects = array();
		$this->id = $id;
		
		#Creating a new module
		if ($this->id == NULL) return;
		#Loading a module from file
		try
		{
			$this->loadSet();
		}
		catch (Exception $e)
		{
			throw new Exception("Unable to initalize module, '".$e->getMessage()."'");
		}
	}
}

?>
