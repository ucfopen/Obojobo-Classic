<?php
/**
 * This class contains all logic pertaining to ComputerData
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to ComputerData
 */
namespace obo\log;
class ComputerDataManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	public function addComputerData($data)
	{
		$this->defaultDBM();
		$qstr = "INSERT INTO `".\cfg_obo_ComputerData::TABLE."`
				(`".\cfg_core_User::ID."`,
				`".\cfg_obo_ComputerData::IP."`,
				`".\cfg_obo_ComputerData::TIME."`,
				`".\cfg_obo_ComputerData::APP_WIDTH."`,
				`".\cfg_obo_ComputerData::APP_HEIGHT."`,
				`".\cfg_obo_ComputerData::ACCESSIBILITY."`,
				`".\cfg_obo_ComputerData::DEBUGER."`,
				`".\cfg_obo_ComputerData::LANG."`,
				`".\cfg_obo_ComputerData::FILE_READ_DISABLE."`,
				`".\cfg_obo_ComputerData::MANU."`,
				`".\cfg_obo_ComputerData::OS."`,
				`".\cfg_obo_ComputerData::TYPE."`,
				`".\cfg_obo_ComputerData::RES_WIDTH."`,
				`".\cfg_obo_ComputerData::RES_HEIGHT."`,
				`".\cfg_obo_ComputerData::VER."`,
				`".\cfg_obo_ComputerData::REFERRER."`,
				`".\cfg_obo_ComputerData::USER_AGENT."`,
				`".\cfg_obo_ComputerData::USER_TIME."`,
				`".\cfg_obo_ComputerData::USER_TIME_OFFSET."`)
				VALUES ( '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?')";

		if(!($q = $this->DBM->querySafe($qstr,$_SESSION['userID'], $_SERVER['REMOTE_ADDR'], time(),
										$data['appX'], $data['appY'], $data['hasAccessibility'],
										$data['isDebugger'], $data['language'],
										$data['localFileReadDisable'], $data['manufacturer'],
										$data['os'], $data['playerType'], $data['screenResolutionX'],
										$data['screenResolutionY'], $data['version'], $_SERVER['HTTP_REFERER'],
										$_SERVER['HTTP_USER_AGENT'], $data['userTime'], $data['userTimeOffset'])))
		{
		    trace(mysql_error(), true);
			return false;
		}

		return true;
	}
}
