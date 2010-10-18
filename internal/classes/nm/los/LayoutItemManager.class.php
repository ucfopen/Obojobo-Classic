<?php
/**
 * This class handles all logic for Layout Items
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all logic for Layout Items
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_LayoutItemManager extends core_db_dbEnabled
{
	private static $instance;
	
	public function __construct()
	{
        $this->defaultDBM();	    
	}
	
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	/**
	 * Gets all layout items from a list of item ids
	 * @param $itmlst (string) Space-separated list of item ids
	 */
	public function getItemsFromList($layoutItemList){
		$itmArr = array();
		if(nm_los_Validator::isString($layoutItemList))
		{
			$idArr = explode(" ", $layoutItemList);
			foreach($idArr as $layoutItemID)
			{
				$itmArr[] = $this->getItem($layoutItemID);
			}
		}
		return $itmArr;
	}
	
	/**
	 * Gets a layout item from the database
	 * @param $layoutItemID (number) layout item ID
	 */
	public function getItem($layoutItemID)
	{
		if(nm_los_Validator::isPosInt($layoutItemID))
		{
        	$qstr = "SELECT * FROM ".cfg_obo_Layout::ITEM_TABLE." WHERE ".cfg_obo_Layout::ITEM_ID."='?' LIMIT 1";
			$q = $this->DBM->querySafe($qstr, $layoutItemID);
			$r = $this->DBM->fetch_obj($q);
			$itm = new nm_los_LayoutItem((int)$r->{cfg_obo_Layout::ITEM_ID}, $r->{cfg_obo_Layout::TITLE}, $this->getComponent($r->{cfg_obo_Layout::ITEM_COMP}), (int)$r->{cfg_obo_Layout::ITEM_X}, (int)$r->{cfg_obo_Layout::ITEM_Y}, (int)$r->{cfg_obo_Layout::ITEM_W}, (int)$r->{cfg_obo_Layout::ITEM_H}, $r->{cfg_obo_Layout::ITEM_DATA});
		}
		return $itm;
	}
	
	/**
	 * Component names are stored in a separate table to avoid repetiton, this function retrieves the name
	 * @param $componentID (number) component ID
	 */
	private function getComponent($componentID)
	{
	    $qstr = "SELECT ".cfg_obo_Layout::COMP_TITLE." FROM ".cfg_obo_Layout::COMP_TABLE." WHERE ".cfg_obo_Layout::COMP_ID."='?' LIMIT 1";
	    
		$q = $this->DBM->querySafe($qstr, $componentID);
		$r = $this->DBM->fetch_obj($q);
		
		return $r->{cfg_obo_Layout::COMP_TITLE};
	}
}
?>
