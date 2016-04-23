<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\acp;

class phplistautoregistration_acp_info
{
	function module()
	{
		return array(
			'filename'	=> '\utagawavtt\phplistautoregistration\acp\phplistautoregistration_acp_module',
			'title'		=> 'ACP_PHPLISTAUTOREGISTRATION_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_PHPLISTAUTOREGISTRATION_SETTINGS',
					'auth'	=> 'ext_utagawavtt/phplistautoregistration && acl_a_board',
					'cat'	=> array('ACP_PHPLISTAUTOREGISTRATION_TITLE'),
				),
			),
		);
	}
}
