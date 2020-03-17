<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['utagawavtt_phplistautoregistration_apiUrl']);
	}

	static public function depends_on()
	{
		return array();
	}

	public function update_data()
	{
		return array(
			array('config.add', array('utagawavtt_phplistautoregistration_apiUrl', '')),
			array('config.add', array('utagawavtt_phplistautoregistration_login', 'phpbbRobot')),
			array('config.add', array('utagawavtt_phplistautoregistration_password', '')),
			array('config.add', array('utagawavtt_phplistautoregistration_cacheDir', 'cache')),
			array('config.add', array('utagawavtt_phplistautoregistration_listIDs', '')),
			array('config.add', array('utagawavtt_phplistautoregistration_configOK', '0')),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PHPLISTAUTOREGISTRATION_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_PHPLISTAUTOREGISTRATION_TITLE',
				array(
					'module_basename'	=> '\utagawavtt\phplistautoregistration\acp\phplistautoregistration_acp_module',
					'modes'				=> array('settings'),
				),
			)),
			array('module.add', array(
				'ucp',
				'UCP_PROFILE',
				array(
					'module_basename'	=> '\utagawavtt\phplistautoregistration\ucp\phplistautoregistration_ucp_module',
                    'modes'				=> array('settings'),
				),
			)),
		);
	}
}
