<?php
/**
 * Wayfinder Class
 *
 * @package wayfinder
 * @version 2.5 beta 2
 * @author Kyle Jaebker (muddydogpaws.com)
 * @author Ryan Thrash (collabpad.com)
 */
class Wayfinder {
    /**
     * The array of config parameters
     * @access private
     * @var array $config
     */
	var $config;
    /**
     * The array of templates
     * @access private
     * @var array $templates
     */
	var $templates;
    /**
     * The array of css parameters
     * @access private
     * @var array $css
     */
	var $css;
    /**
     * The array of documents for processing
     * @access private
     * @var array $docs
     */
	var $docs = array();
    /**
     * The heirarchy of the startId document.
     * @access private
     * @var array $parentTree
     */
	var $parentTree = array();
    /**
     * The array of documents that have children.
     * @access private
     * @var array $hasChildren
     */
	var $hasChildren = array();
    /**
     * The array of placeholders to use in processing.
     * @access private
     * @var array $placeHolders
     */
	var $placeHolders = array();
    /**
     * The array of template variables to use in processing.
     * @access private
     * @var array $tvList
     */
	var $tvList = array();
    /**
     * The modx object.
     * @access private
     * @var object $modx
     */
    var $modx;
    /**
     * The version of Wayfinder.
     * @access private
     * @var string $version
     */
    var $version;
    //var $fp;
    
    /**
     * Default constructor
     *
     * @param object $modx The modx object
     * @return void
     */
    function __construct(&$modx) {
        $this->version = '2.5beta2';
        $this->modx = $modx;

        //$this->fp = FirePHP::getInstance(true);

        $this->placeHolders = array(
            'rowLevel' => array(
                '[+wf.wrapper+]',
                '[+wf.classes+]',
                '[+wf.classnames+]',
                '[+wf.link+]',
                '[+wf.title+]',
                '[+wf.linktext+]',
                '[+wf.id+]',
                '[+wf.attributes+]',
                '[+wf.docid+]',
                '[+wf.introtext+]',
                '[+wf.description+]',
                '[+wf.subitemcount+]',
                '[+wf.alias+]',
                '[+wf.level+]',
                '[+wf.iterator+]'
            ),
            'wrapperLevel' => array(
                '[+wf.wrapper+]',
                '[+wf.classes+]',
                '[+wf.classnames+]',
                '[+wf.outerid+]'
            ),
            'tvs' => array()
        );
    }

    /**
     * Default constructor for php4
     *
     * @param object $modx The modx object
     * @return void
     */
    function Wayfinder(&$modx) {
        $this->__construct($modx);
    }

    function initialize($config = array()) {

    }

    /**
     * Validates that the snippet code version is the same as the class version.
     *
     * @param string $snippetVersion The version number of the calling snippet.
     * @return string The error message if the version check is invalid.
     */
    function versionCheck($snippetVersion) {
        $output = '';
        if ($snippetVersion !== $this->version) {
            $output = <<<STOP
<div style="border: 1px solid red;font-weight: bold;margin: 10px;padding: 5px;">
    <p>Wayfinder cannot load because the snippet code version ({$snippetVersion}) isn't the same as the snippet included files version ({$this->version}). Possible cause is that you updated the Wayfinder files in the modx directory but didn't update the snippet code in the manager. The content for the updated snippet code can be found in wayfinder.snippet.php</p>
</div>
STOP;
        }
        return $output;
    }

    /**
     * Starts the processing for the current Wayfinder configuration.
     *
     * @return string The generated HTML menu.
     */
	function buildMenu() {
        //setup here checking array
		$this->parentTree = $this->modx->getParentIds($this->modx->documentIdentifier);
		$this->parentTree[] = $this->modx->documentIdentifier;

		//Load the templates
		$this->checkTemplates();
		//Register any scripts
		if ($this->config['cssTpl'] || $this->config['jsTpl']) {
		    $this->regJsCss();
		}
		//Get all of the documents
		$this->getData();
		if (!empty($this->docs)) {
			//Sort documents by level for proper wrapper substitution
			ksort($this->docs);
		} else {
			return;
		}

		//Loop through all of the menu levels
		foreach ($this->docs as $level => $subDocs) {
			//Loop through each document group (grouped by parent doc)
			foreach ($subDocs as $parentId => $docs) {
				//only process document group, if starting at root, hidesubmenus is off, or is in current parenttree
				if (!$this->config['hideSubMenus'] || $this->isHere($parentId) || $level <= 1) {
					//Build the output for the group of documents
					$menuPart = $this->buildSubMenu($docs,$level);
					//If we are at the top of the menu start the output, otherwise replace the wrapper with the submenu
					if (($level == 1 && (!$this->config['displayStart'] || $this->config['id'] == 0)) || ($level == 0 && $this->config['displayStart'])) {
						$output = $menuPart;
					} else {
                        $output = $this->processTemplate(array("[+wf.wrapper.{$parentId}+]"),array($menuPart),$output);
					}
				}
			}
		}
		//Return the final Menu
		return $output;
	}

    /**
     * Processes each level of documents to generate the menu output.
     *
     * @param array $menuDocs The documents to loop through.
     * @param int $level The level of the menu part being processed.
     * @return string The submenu HTML output.
     */
	function buildSubMenu($menuDocs,$level) {
		$subMenuOutput = '';
		$firstItem = 1;
		$counter = 1;
		$numSubItems = count($menuDocs);
		//Loop through each document to render output
		foreach ($menuDocs as $docId => $docInfo) {
			$docInfo['level'] = $level;
			$docInfo['first'] = $firstItem;
            $docInfo['counter'] = $counter;
			$firstItem = 0;
			//Determine if last item in group
			if ($counter == ($numSubItems) && $numSubItems > 0) {
				$docInfo['last'] = 1;
			} else {
				$docInfo['last'] = 0;
			}
			//Determine if document has children
			$docInfo['hasChildren'] = in_array($docInfo['id'],$this->hasChildren) ? 1 : 0;
			$numChildren = $docInfo['hasChildren'] ? count($this->docs[$level+1][$docInfo['id']]) : 0;
			//Render the row output
			$subMenuOutput .= $this->renderRow($docInfo,$numChildren);
			//Update counter for last check
			$counter++;
		}
		
		if ($level > 0) {
			//Determine which wrapper template to use
			if ($this->templates['innerTpl'] && $level > 1) {
				$usedTemplate = 'innerTpl';
			} else {
				$usedTemplate = 'outerTpl';
			}
            $useChunk = $this->templates[$usedTemplate];
			//Determine wrapper class
			if ($level > 1) {
				$wrapperClass = 'innercls';
			} else {
				$wrapperClass = 'outercls';
			}
			//Get the class names for the wrapper
			$classNames = $this->setItemClass($wrapperClass);
            $useClass = ($classNames) ? ' class="' . $classNames . '"' : '';

            //Process the wrapper
			$phArray['values'] = array($subMenuOutput,$useClass,$classNames);
            $this->addPlaceholders($phArray,'wrapperLevel',$docInfo);
			$subMenuOutput = $this->processTemplate($phArray['placeholders'], $phArray['values'],$useChunk);
		}
		//Return the submenu
		return $subMenuOutput;
	}
	
    /**
     * Renders a row item for the menu
     *
     * @param array $resource An array containing the document information for the row
     * @param int $numChildren The number of children that the document contains
     * @return string The HTML for the row item
     */
    function renderRow(&$resource,$numChildren) {
        $output = '';
		//Determine which template to use
        if ($this->config['displayStart'] && $resource['level'] == 0) {
			$usedTemplate = 'startItemTpl';
		} elseif ($resource['id'] == $this->modx->documentObject['id'] && $resource['isfolder'] && $this->templates['parentRowHereTpl'] && ($resource['level'] < $this->config['level'] || $this->config['level'] == 0) && $numChildren) {
            $usedTemplate = 'parentRowHereTpl';
        } elseif ($resource['id'] == $this->modx->documentObject['id'] && $this->templates['innerHereTpl'] && $resource['level'] > 1) {
            $usedTemplate = 'innerHereTpl';
        } elseif ($resource['id'] == $this->modx->documentObject['id'] && $this->templates['hereTpl']) {
            $usedTemplate = 'hereTpl';
        } elseif ($resource['isfolder'] && $this->templates['activeParentRowTpl'] && ($resource['level'] < $this->config['level'] || $this->config['level'] == 0) && $this->isHere($resource['id'],$resource['type'],$resource['content'])) {
            $usedTemplate = 'activeParentRowTpl';
        } elseif ($resource['isfolder'] && ($resource['template']=="0" || is_numeric(strpos($resource['link_attributes'],'rel="category"'))) && $this->templates['categoryFoldersTpl'] && ($resource['level'] < $this->config['level'] || $this->config['level'] == 0)) {
            $usedTemplate = 'categoryFoldersTpl';
        } elseif ($resource['isfolder'] && $this->templates['parentRowTpl'] && ($resource['level'] < $this->config['level'] || $this->config['level'] == 0) && $numChildren) {
            $usedTemplate = 'parentRowTpl';
        } elseif ($resource['level'] > 1 && $this->templates['innerRowTpl']) {
            $usedTemplate = 'innerRowTpl';
        } elseif ($resource['last'] && $this->templates['lastRowTpl']) {
            $usedTemplate = 'lastRowTpl';
        } elseif ($resource['first'] && $this->templates['firstRowTpl']) {
            $usedTemplate = 'firstRowTpl';
        } else {
            $usedTemplate = 'rowTpl';
        }
        //Get the template
        $useChunk = $this->templates[$usedTemplate];
		//Setup the new wrapper name and get the class names
        $useSub = $resource['hasChildren'] ? "[+wf.wrapper.{$resource['id']}+]" : "";
        $classNames = $this->setItemClass('rowcls',$resource);
        if ($classNames) $useClass = ' class="' . $classNames . '"';
        //Setup the row id if a prefix is specified
        if ($this->config['rowIdPrefix']) {
            $useId = ' id="' . $this->config['rowIdPrefix'] . $resource['id'] . '"';
        } else {
            $useId = '';
        }
		
        //Process the row
        $phArray['values'] = array($useSub,$useClass,$classNames,$resource['link'],$resource['title'],
            $resource['linktext'],$useId,$resource['link_attributes'],$resource['id'],
            $resource['introtext'],$resource['description'],$numChildren,$resource['alias'],$resource['level'],$resource['counter']);
        $this->addPlaceholders($phArray,'rowLevel',$resource);
        $output = $this->processTemplate($phArray['placeholders'], $phArray['values'], $useChunk);
        
		//Return the row
        return $output . $this->config['nl'];
    }

    /**
     * Adds the placholders for template processing.
     *
     * @param array $phArray Array containing the placeholders and values.
     * @param string $placeholderLevel The type of default placeholders to use.
     * @param array $resource The document info to retreive the placeholder values.
     * @return void
     */
    function addPlaceholders(&$phArray,$placeholderLevel,$resource) {
        //If tvs are used add them to the placeholder array
		if (!empty($this->tvList)) {
			$phArray['placeholders'] = array_merge($this->placeHolders[$placeholderLevel],$this->placeHolders['tvs']);
			foreach ($this->tvList as $tvName) {
				$phArray['values'][] = $resource[$tvName];
			}
		} else {
			$phArray['placeholders'] = $this->placeHolders['rowLevel'];
		}
    }

    /**
     * Processes the template for each item using either PHX or string replacement based on config.
     * @param array $placeholders Array containing the placeholders.
     * @param array $values Array containing the values for replacement.
     * @param string $chunk The template to process.
     * @return string The processed template.
     */
    function processTemplate($placeholders,$values,$chunk) {
        $output = '';
        $wrapper = '';
        $wrapperVal = '';
        if ($this->config['phx']) {
            $phxPlaceholders = array();
			$phLength = count($placeholders);
            for ($i = 0; $i < $phLength; $i++) {
                $key = str_replace(array('[+','+]'),array('',''),$placeholders[$i]);
                if (!(strpos($key,'wf.wrapper') === false)) {
                    $wrapper = '[+'.$key.'+]';
                    $wrapperVal = $values[$i];
                }
                $phxPlaceholders[$key] = $values[$i];
            }
            //remove wrapper placeholders or nesting fails
            $chunk = preg_replace('/\[\+(wf.wrapper.?[0-9]*?)\+\]/','<~$1~>',$chunk);

			$phx = new prePHx($chunk);
			$phx->setPlaceholders($phxPlaceholders);
            //get phx output and replace wrapper placeholders
            $output = preg_replace('/\<\~(wf.wrapper.?[0-9]*?)\~\>/','[+$1+]',$phx->output());
            //replace the wrapper placeholder passed in
            $output = str_replace($wrapper, $wrapperVal, $output);
		} else {
            $output = str_replace($placeholders,$values,$chunk);
		}
        return $output;
    }
	
    /**
     * Determine style class for current item being processed
     *
     * @param string $classType The type of class to be returned
     * @param array $resource The document info of the item being processed
     * @return string The class string to use
     */
    function setItemClass($classType, &$resource = null) {
        $returnClass = '';
        $hasClass = 0;

        if ($classType === 'outercls' && !empty($this->css['outer'])) {
            //Set outer class if specified
            $returnClass .= $this->css['outer'];
            $hasClass = 1;
        } elseif ($classType === 'innercls' && !empty($this->css['inner'])) {
            //Set inner class if specified
            $returnClass .= $this->css['inner'];
            $hasClass = 1;
        } elseif ($classType === 'rowcls') {
            //Set row class if specified
            if (!empty($this->css['row'])) {
                $returnClass .= $this->css['row'];
                $hasClass = 1;
            }
            //Set first class if specified
            if ($resource['first'] && !empty($this->css['first'])) {
                $returnClass .= $hasClass ? ' ' . $this->css['first'] : $this->css['first'];
                $hasClass = 1;
            }
            //Set last class if specified
            if ($resource['last'] && !empty($this->css['last'])) {
                $returnClass .= $hasClass ? ' ' . $this->css['last'] : $this->css['last'];
                $hasClass = 1;
            }
            //Set level class if specified
            if (!empty($this->css['level'])) {
                $returnClass .= $hasClass ? ' ' . $this->css['level'] . $resource['level'] : $this->css['level'] . $resource['level'];
                $hasClass = 1;
            }
            //Set parentFolder class if specified
            if ($resource['isfolder'] && !empty($this->css['parent']) && ($resource['level'] < $this->config['level'] || $this->config['level'] == 0)) {
                $returnClass .= $hasClass ? ' ' . $this->css['parent'] : $this->css['parent'];
                $hasClass = 1;
            }
            //Set here class if specified
            if (!empty($this->css['here']) && $this->isHere($resource['id'],$resource['type'],$resource['content'])) {
                $returnClass .= $hasClass ? ' ' . $this->css['here'] : $this->css['here'];
                $hasClass = 1;
            }
            //Set self class if specified
            if (!empty($this->css['self']) && $resource['id'] == $this->modx->documentIdentifier) {
                $returnClass .= $hasClass ? ' ' . $this->css['self'] : $this->css['self'];
                $hasClass = 1;
            }
            //Set class for weblink
            if (!empty($this->css['weblink']) && $resource['type'] == 'reference') {
                $returnClass .= $hasClass ? ' ' . $this->css['weblink'] : $this->css['weblink'];
                $hasClass = 1;
            }
            //Set class for even items
			if (!empty($this->css['even']) && !($resource['counter'] % 2)) {
                $returnClass .= $hasClass ? ' ' . $this->css['even'] : $this->css['even'];
                $hasClass = 1;
			}
			//Set class for odd items
			if (!empty($this->css['odd']) && $resource['counter'] % 2) {
                $returnClass .= $hasClass ? ' ' . $this->css['odd'] : $this->css['odd'];
                $hasClass = 1;
			}
            //Set class for secure items
            if (!empty($this->css['secure']) && $resource['privateweb']) {
                $returnClass .= $hasClass ? ' ' . $this->css['secure'] : $this->css['secure'];
                $hasClass = 1;
            }
        }

        return $returnClass;
    }
	
    /**
     * Determine the "you are here" point in the menu
     *
     * @param int Document ID to find
     * @param string $type The type of current item being checked. Allows here checking to look at the content if a weblink.
     * @param string $content The content of the item.  Used for here checking of weblinks.
     * @return bool Returns true if the document ID was found
     */
    function isHere($did,$type='document',$content='') {
        $isHere = false;
        //If weblink to internal doc use link location
        if ($type == 'reference' && is_numeric($content)) {
            $isHere = in_array($content,$this->parentTree);
        }
        if (!$isHere) {
            $isHere = in_array($did,$this->parentTree);
        }
        return $isHere;
    }
	
    /**
     * Add the specified CSS and Javascript chunks to the page
     *
     * @return void
     */
    function regJsCss() {
        //Check and load the CSS 
        if ($this->config['cssTpl']) {
			$cssChunk = $this->fetch($this->config['cssTpl']);
            if ($cssChunk) {
                $this->modx->regClientCSS($cssChunk);
            }
        }
        //Check and load the Javascript
        if ($this->config['jsTpl']) {
			$jsChunk = $this->fetch($this->config['jsTpl']);
            if ($jsChunk) {
                $this->modx->regClientStartupScript($jsChunk);
            }
        }
    }
	
    /**
     * Get the required resources from the database to build the menu
     *
     * @return array The resource array of documents to be processed
     */
	function getData() {
		$ids = array();
		$ids = $this->modx->getChildIds($this->config['id'],$this->config['level']);

		//Get all of the ids for processing
		if ($this->config['displayStart'] && $this->config['id'] !== 0) {
			$ids[] = $this->config['id'];
		}
		if (!empty($ids)) {
			//Setup the fields for the query
			$fields = "sc.id, sc.menutitle, sc.pagetitle, sc.introtext, sc.menuindex, sc.published, sc.hidemenu, sc.parent, sc.isfolder, sc.description, sc.alias, sc.longtitle, sc.type,if(sc.type='reference',sc.content,'') as content, sc.template, sc.link_attributes, sc.privateweb";
	        //Get the table names
	        $tblsc = $this->modx->getFullTableName("site_content");
	        $tbldg = $this->modx->getFullTableName("document_groups");
	        //Add the ignore hidden option to the where clause
	        if ($this->config['ignoreHidden']) {
	            $menuWhere = '';
	        } else {
	            $menuWhere = ' AND sc.hidemenu=0';
	        }
			//add the include docs to the where clause
			if ($this->config['includeDocs']) {
				$menuWhere .= " AND sc.id IN ({$this->config['includeDocs']})";
			}
			//add the exclude docs to the where clause
			if ($this->config['excludeDocs']) {
				$menuWhere .= " AND (sc.id NOT IN ({$this->config['excludeDocs']}))";
			}
			//add the limit to the query
			if ($this->config['limit']) {
				$sqlLimit = " LIMIT 0, {$this->config['limit']}";
			} else {
				$sqlLimit = '';
			}
			//Determine sorting
			if (strtolower($this->config['sortBy']) == 'random') {
				$sort = 'rand()';
				$dir = '';
			} else {
				// modify field names to use sc. table reference
				$sort = 'sc.'.implode(',sc.',preg_replace("/^\s/i","",explode(',',$this->config['sortBy'])));
			}
			
            $access = "";
            if (!$this->config['ignoreSecurity']) {
                // get document groups for current user
                if($docgrp = $this->modx->getUserDocGroups()) $docgrp = implode(",",$docgrp);
                // build query
                $access = 'AND ('.($this->modx->isFrontend() ? "sc.privateweb=0" : "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0").(!$docgrp ? "" : " OR dg.document_group IN ({$docgrp})").')';
            }
			$sql = "SELECT DISTINCT {$fields} FROM {$tblsc} sc LEFT JOIN {$tbldg} dg ON dg.document = sc.id WHERE sc.published=1 AND sc.deleted=0 {$access}{$menuWhere} AND sc.id IN (".implode(',',$ids).") ORDER BY {$sort} {$this->config['sortOrder']} {$sqlLimit};";
			//run the query
			$result = $this->modx->dbQuery($sql);
	        $resourceArray = array();
			$numResults = @$this->modx->recordCount($result);
			$level = 1;
			$prevParent = -1;
			//Setup startlevel for determining each items level
			if ($this->config['id'] == 0) {
				$startLevel = 0;
			} else {
				$startLevel = count($this->modx->getParentIds($this->config['id']));
				$startLevel = $startLevel ? $startLevel+1 : 1;
			}
			$resultIds = array();
			//loop through the results
			for($i=0;$i<$numResults;$i++)  {
				$tempDocInfo = $this->modx->fetchRow($result);
				$resultIds[] = $tempDocInfo['id'];
				//Create the link
				$linkScheme = $this->config['fullLink'] ? 'full' : '';
				if ($this->config['useWeblinkUrl'] !== 'FALSE' && $tempDocInfo['type'] == 'reference') {
					if (is_numeric($tempDocInfo['content'])) {
						$tempDocInfo['link'] = $this->modx->makeUrl(intval($tempDocInfo['content']),'','',$linkScheme);
					} else {
						$tempDocInfo['link'] = $tempDocInfo['content'];
					}
				} elseif ($tempDocInfo['id'] == $this->modx->config['site_start']) {
					$tempDocInfo['link'] = $this->modx->config['site_url'];
				} else {
					$tempDocInfo['link'] = $this->modx->makeUrl($tempDocInfo['id'],'','',$linkScheme);
				}
				//determine the level, if parent has changed
				if ($prevParent !== $tempDocInfo['parent']) {
					$level = count($this->modx->getParentIds($tempDocInfo['id'])) + 1 - $startLevel;
				}
				//add parent to hasChildren array for later processing
				if (($level > 1 || $this->config['displayStart']) && !in_array($tempDocInfo['parent'],$this->hasChildren)) {
					$this->hasChildren[] = $tempDocInfo['parent'];
				}
				//set the level
				$tempDocInfo['level'] = $level;
				$prevParent = $tempDocInfo['parent'];
				//determine other output options
				$useTextField = (empty($tempDocInfo[$this->config['textOfLinks']])) ? 'pagetitle' : $this->config['textOfLinks'];
				$tempDocInfo['linktext'] = $tempDocInfo[$useTextField];
				$tempDocInfo['title'] = $tempDocInfo[$this->config['titleOfLinks']];
				//If tvs were specified keep array flat otherwise array becomes level->parent->doc
				if (!empty($this->tvList)) {
					$tempResults[] = $tempDocInfo;
				} else {
					$resourceArray[$tempDocInfo['level']][$tempDocInfo['parent']][] = $tempDocInfo;
				}
	        }
			//Process the tvs
			if (!empty($this->tvList) && !empty($resultIds)) {
				$tvValues = array();
				//loop through all tvs and get their values for each document
				foreach ($this->tvList as $tvName) {
					$tvValues = array_merge_recursive($this->appendTV($tvName,$resultIds),$tvValues);
				}
				//loop through the document array and add the tvar values to each document
				foreach ($tempResults as $tempDocInfo) {
					if (array_key_exists("#{$tempDocInfo['id']}",$tvValues)) {
						foreach ($tvValues["#{$tempDocInfo['id']}"] as $tvName => $tvValue) {
							$tempDocInfo[$tvName] = $tvValue;
						}
					}
					$resourceArray[$tempDocInfo['level']][$tempDocInfo['parent']][] = $tempDocInfo;
				}
			}
		}
		//set final docs
        $this->docs = $resourceArray;
	}
	
   /**
    * Append a TV to the resource array
    *
    * @param string $tvname Name of the Template Variable to append
    * @param array $docIds An array of document IDs to append the TV to
    * @return array A resource array with the TV information
    */
	function appendTV($tvname,$docIDs) {
		$baspath= $this->modx->config["base_path"] . "manager/includes";
	    include_once $baspath . "/tmplvars.format.inc.php";
	    include_once $baspath . "/tmplvars.commands.inc.php";

		$tb1 = $this->modx->getFullTableName("site_tmplvar_contentvalues");
		$tb2 = $this->modx->getFullTableName("site_tmplvars");

		$query = "SELECT stv.name,stc.tmplvarid,stc.contentid,stv.type,stv.display,stv.display_params,stc.value";
		$query .= " FROM ".$tb1." stc LEFT JOIN ".$tb2." stv ON stv.id=stc.tmplvarid ";
		$query .= " WHERE stv.name='".$tvname."' AND stc.contentid IN (".implode($docIDs,",").") ORDER BY stc.contentid ASC;";
		$rs = $this->modx->db->query($query);
		$tot = $this->modx->db->getRecordCount($rs);
		$resourceArray = array();
		for($i=0;$i<$tot;$i++)  {
			$row = @$this->modx->fetchRow($rs);
			$resourceArray["#{$row['contentid']}"][$row['name']] = getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'],$row['contentid']);   
		}

		if ($tot != count($docIDs)) {
			$query = "SELECT name,type,display,display_params,default_text";
			$query .= " FROM $tb2";
			$query .= " WHERE name='".$tvname."' LIMIT 1";
			$rs = $this->modx->db->query($query);
			$row = @$this->modx->fetchRow($rs);
			foreach ($docIDs as $id) {
                $defaultOutput = getTVDisplayFormat($row['name'], $row['default_text'], $row['display'], $row['display_params'], $row['type'], $id);
				if (!isset($resourceArray["#{$id}"])) {
					$resourceArray["#{$id}"][$tvname] = $defaultOutput;
				}
			}
		}
		return $resourceArray;
	}
	
    /**
     * Get a list of all available TVs
     *
     * @return array An array of all available TV names
     */
	function getTVList() {
		$table = $this->modx->getFullTableName("site_tmplvars");
		$tvs = $this->modx->db->select("name", $table);
		// TODO: make it so that it only pulls those that apply to the current template
		$dbfields = array();
		while ($dbfield = $this->modx->db->getRow($tvs))
			$dbfields[] = $dbfield['name'];
		return $dbfields;
	}
	
    /**
     * Check that templates are valid
     *
     * @return void
     */
    function checkTemplates() {
		$nonWayfinderFields = array();

        foreach ($this->templates as $n => $v) {
            $templateCheck = $this->fetch($v);
            if (empty($v) || !$templateCheck) {
                if ($n === 'outerTpl') {
                    $this->templates[$n] = '<ul[+wf.classes+]>[+wf.wrapper+]</ul>';
                } elseif ($n === 'rowTpl') {
                    $this->templates[$n] = '<li[+wf.id+][+wf.classes+]><a href="[+wf.link+]" title="[+wf.title+]" [+wf.attributes+]>[+wf.linktext+]</a>[+wf.wrapper+]</li>';
				} elseif ($n === 'startItemTpl') {
					$this->templates[$n] = '<h2[+wf.id+][+wf.classes+]>[+wf.linktext+]</h2>[+wf.wrapper+]';
                } else {
                    $this->templates[$n] = FALSE;
                }
            } else {
                $this->templates[$n] = $templateCheck;
				$check = $this->findTemplateVars($templateCheck);
				if (is_array($check)) {
					$nonWayfinderFields = array_merge($check, $nonWayfinderFields);
				}
            }			
        }
		
		if (!empty($nonWayfinderFields)) {
			$nonWayfinderFields = array_unique($nonWayfinderFields);
			$allTvars = $this->getTVList();

			foreach ($nonWayfinderFields as $field) {
				if (in_array($field, $allTvars)) {
					$this->placeHolders['tvs'][] = "[+{$field}+]";
					$this->tvList[] = $field;
				}
			}
		}
    }

    /**
     * Fetch a template from the database or filesystem
     *
     * @param string $tpl Template to be fetched
     * @return string|bool Template HTML or false if no template was found
     */
	function fetch($tpl){
		$template = "";
		if ($this->modx->getChunk($tpl) != "") {
			$template = $this->modx->getChunk($tpl);
		} else if(substr($tpl, 0, 6) == "@FILE:") {
			$template = $this->get_file_contents(substr($tpl, 6));
		} else if(substr($tpl, 0, 6) == "@CODE:") {
			$template = substr($tpl, 6);
		} else {
			$template = FALSE;
		}
		return $template;
	}

    /**
     * Substitute function for file_get_contents()
     *
     * @param string $filename Name of file to be fetched
     * @return string The file contents
     */
	function get_file_contents($filename) {
		if (!function_exists('file_get_contents')) {
			$fhandle = fopen($filename, "r");
			$fcontents = fread($fhandle, filesize($filename));
			fclose($fhandle);
		} else	{
			$fcontents = file_get_contents($filename);
		}
		return $fcontents;
	}
    
    /**
     * Find all TV names in the template
     *
     * @param string $tpl The template code to be processed
     * @return array|bool An array containing the TV names or false if no names were found
     */
	function findTemplateVars($tpl) {
		preg_match_all('~\[\+(.*?)\+\]~', $tpl, $matches);
		$cnt = count($matches[1]);
				
		$tvnames = array ();
		for ($i = 0; $i < $cnt; $i++) {
			if (strpos($matches[1][$i], "wf.") === FALSE) {
				$tvnames[] =  $matches[1][$i];
			}
		}

		if (count($tvnames) >= 1) {
			return array_unique($tvnames);
		} else {
			return false;
		}
	}
}

?>