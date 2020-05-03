<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\migrations;

class release_1_2_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['utagawavtt_phplistautoregistration_hiddenListIDs']);
	}

	static public function depends_on()
	{
		return array();
	}

	public function update_data()
	{
		return array(
			array('config.add', array('utagawavtt_phplistautoregistration_hiddenListIDs', '')),
		);
	}
}
