<?php
/*---------------------------------------------------------------------------
* FeedX Debug Class - Displays information about FeedX snippet configuraiton 
* 	and provides a useful facility for building custom templates
*----------------------------------------------------------------------------
*
* Adapted from the Ditto 2 debug class by Mark Kaplan <www.modxcms.com>
*
*--------------------------------------------------------------------------*/

class WFDebug
{
    var $templates;

	function WFDebug($templates) {
		$this->templates = $templates;
	}

	function saveDebugConsole($debug_html, $wf_version) {
		global $modx;
		header('Content-Type: text/html; charset=' . $modx->config['modx_charset']);
		header('Content-Disposition: attachment; filename="wf-' . strtolower($wf_version) . '_debug_doc' . $modx->documentIdentifier . '.html"');
		exit($debug_html);
	}

	function render_link($wf)
	{
		global $modx;
		$base_path = str_replace($modx->config['base_path'], $modx->config['site_url'], $wf->config['base_path']);
		$placeholders = array(
			'[+open_url+]' => $modx->makeUrl($modx->documentIdentifier, '', 'dbg_dump=open'),
			'[+dbg_title+]' => 'Debug',
			'[+dbg_icon_url+]' => $base_path . 'includes/debug/bug.png',
			'[+save_url+]' => $modx->makeUrl($modx->documentIdentifier, '', 'dbg_dump=save'),
			'[+dbg_icon_url+]' => $base_path . 'includes/debug/bug.png',
			'[+open_dbg_console+]' => 'Open Debug Console',
			'[+save_dbg_console+]' => 'Save Debug Console',
		);
		return str_replace(array_keys($placeholders), array_values($placeholders), $this->templates['links']);
	}

	function render_popup($wf, $wf_version)
	{
		global $modx;

		if (count($wf->docs) == 0) {
			$wf->getData();
        }

		$cTabs = array();

		$cTabs['Info'] = $this->prepareBasicInfo($wf, $wf_version);
		$cTabs['MODx Info'] = $this->makeMODxInfo();
		$cTabs['Templates'] = $this->parameters2table($wf->templates, 'Templates', false) . $this->parameters2table($wf->placeHolders, 'Available Placeholders', false);

        if (count($wf->docs) > 0)
		{
            $cTabs = array_merge($cTabs, $this->prepareDocumentInfo($wf));
		}
		else
		{
            $cTabs['Data'] = 'No documents were returned.';
		}

		$tabs = '';
		foreach ($cTabs as $name=>$content) {
			$tabs .= $this->makeTab($name, $content);
		}

		$placeholders = array
		(
			'[+base_url+]' => $modx->config['site_url'] . 'manager',
			'[+wf_base_url+]' => str_replace($modx->config['base_path'], $modx->config['site_url'], $wf->config['base_path']),
			'[+theme+]' => $modx->config['manager_theme'],
			'[+title+]' => 'Wayfinder Debug',
			'[+content+]' => $tabs,
			'[+charset+]' => $modx->config['modx_charset']
		);
	
		return str_replace(array_keys($placeholders), array_values($placeholders), $this->templates['main']);
	}

	function makeTab($title,$content)
	{
		$output= '<div class="tab-page" id="tab_'  . $title  .  '">  
			    <h2 class="tab">' . $title. '</h2>  
			    <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tab_'  . $title  .  '" ) );</script> 
				';
		$output .= $content;
		$output.='</div>';
		return $output;
	}

	function makeMODxInfo()
	{
		global $modx;

		$output .= $this->parameters2table($modx->documentObject, 'Document Info');
		return $output;
    }

	function prepareBasicInfo($wf, $wf_version)
	{
		global $modx;

		$items['Version'] = 'Wayfinder ' . $wf_version;
        foreach ($wf->config as $k => $v) {
            $items[$k] = $v;
        }
        foreach ($wf->css as $k => $v) {
            $cssItems[$k] = $v;
        }

		return $this->parameters2table($items, 'Config', false, false) . $this->parameters2table($cssItems, 'CSS Classes', false, false);
	}

    // ---------------------------------------------------
	// Function: prepareDocumentInfo
	// Create the output for the Document Info tab
	// ---------------------------------------------------
	function prepareDocumentInfo($wf) {
        $dataTabs = array();
		if (count($wf->docs) > 0) {
			foreach ($wf->docs as $level => $parents) {
                $output = "";
                $parentLinks = "<ul><li><h2>Table of Contents</h2><ul>";
                foreach ($parents as $parentId => $children) {
                    $parentOutput = "";
                    foreach ($children as $docInfo) {
                        $header = str_replace(array('[+pagetitle+]','[+id+]'),array($docInfo['pagetitle'],$docInfo['id']),$this->templates["item"]);
                        $parentOutput .=  $this->parameters2table($docInfo,$header,true,true);
                    }
                    $output .= '<li><h2><a name="parent'.$parentId.'">Parent: ' . $parentId . '</a></h2>' . $parentOutput . '</li>';
                    $parentLinks .= '<li><a href="#parent'.$parentId.'">Parent: ' . $parentId . '</a></li>';
                }
                $dataTabs['Data (level ' . $level . ')'] = $parentLinks . '</ul>' . $output . '</ul>';
			}
		}
		return $dataTabs;
	}

	//---Helper Functions------------------------------------------------ //

	function modxPrep($value) {
		$value = (strpos($value,"<") !== FALSE) ? htmlentities($value) : $value;
		$value = str_replace("[","&#091;",$value);
		$value = str_replace("]","&#093;",$value);
		$value = str_replace("{","&#123;",$value);
		$value = str_replace("}","&#125;",$value);
		return $value;
	}

	function parameters2table($parameters, $header, $sort = true, $prep = true) {
		if (!is_array($parameters))
			return 'Error parsing debug data.';
		if ($sort === true)
			ksort($parameters);

		$output = '<table cellpadding="0" cellspacing="0">
				  <thead>
				    <tr>
				      <th colspan="2">'.$header.'</th>
				    </tr>
                  </thead>
                  <tbody>
		';
        $c = 1;
        $prepDone = false;
		foreach ($parameters as $key=>$value) {
			if (!is_string($value) && !is_float($value) && !is_int($value)) {
				if (is_array($value)) {
					$value = $this->array2List($value,true);
                    $prepDone = true;
				} elseif ($value === false || $value === true) {
                    $value = $value ? 'TRUE' : 'FALSE';
                } else {
					$name = gettype($value);
					$value = strtoupper($name{0}).substr($name,1);
				}
			}
            if (!$prepDone) {
                $v = ($prep == true) ? $this->modxPrep($value) : $value;
                $v = wordwrap($v,200,"\r\n",1);
            } else {
                $v = $value;
            }
            if ($c % 2) {
                $class = 'odd';
            } else {
                $class = 'even';
            }
			$output .= '
					    <tr class="'.$class.'">
					      <td class="key">'.$key.'</td>
					      <td>'.$v.'</td>
					    </tr>
			';
            $c++;
		}
		$output .= '
				  </tbody>
				</table>
				';

		return $output;
	}

    function array2List($array,$recursive = false) {

        if (empty($array) || !is_array($array)) {
	        return false;
	    }

        $output = "<ul>\n";

        foreach($array as $value) {
            if (is_array($value) && $recursive) {
                $output .= $this->array2List($value, true);
            } elseif ($value === false || $value === true) {
                $value = $value ? 'TRUE' : 'FALSE';
            }
            $output .= '<li>' . $this->modxPrep($value) . "</li>\n";
        }

        $output .= "</ul>\n";

        return $output;
    }

	/**
	 * Translate a result array into a HTML table
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.3.1
	 * @link        http://aidanlister.com/repos/v/function.array2table.php
	 * @param       array  $array      The result (numericaly keyed, associative inner) array.
	 * @param       bool   $recursive  Recursively generate tables for multi-dimensional arrays
	 * @param       bool   $return     return or echo the data
	 * @param       string $null       String to output for blank cells
	 */
	function array2table($array, $recursive = false, $return = false, $null = '&nbsp;') {
	    // Sanity check
	    if (empty($array) || !is_array($array)) {
	        return false;
	    }

	    if (!isset($array[0]) || !is_array($array[0])) {
	        $array = array($array);
	    }

	    // Start the table
	    $table = "<table>\n";
		$head = array_keys($array[0]);
	if (!is_numeric($head[0])) {
	    // The header
	    $table .= "\t<tr>";
	    // Take the keys from the first row as the headings
	    foreach (array_keys($array[0]) as $heading) {
	        $table .= '<th>' . $heading . '</th>';
	    }
	    $table .= "</tr>\n";
	}
	    // The body
	    foreach ($array as $row) {
	        $table .= "\t<tr>" ;
	        foreach ($row as $cell) {
	            $table .= '<td>';

	            // Cast objects
	            if (is_object($cell)) { $cell = (array) $cell; }

	            if ($recursive === true && is_array($cell) && !empty($cell)) {
	                // Recursive mode
	                $table .= "\n" . $this->array2table($cell, true, true) . "\n";
	            } else {
	                $table .= (strlen($cell) > 0) ?
                    $this->modxPrep((string) $cell) :
					$null;
	            }

	            $table .= '</td>';
	        }

	        $table .= "</tr>\n";
	    }

	    // End the table
	    $table .= '</table>';

	    // Method of output
	    if ($return === false) {
	        echo $table;
	    } else {
	        return $table;
	    }
	}
}

?>
