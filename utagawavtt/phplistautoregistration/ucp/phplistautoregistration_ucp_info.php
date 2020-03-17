<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\ucp;

class phplistautoregistration_ucp_info
{
	function module()
	{
		return array(
			'filename'	=> '\utagawavtt\phplistautoregistration\ucp\phplistautoregistration_ucp_module',
			'title'		=> 'UCP_PROFILE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'UCP_PHPLISTAUTOREGISTRATION_SETTINGS',
					'auth'	=> 'acl_u_chgprofileinfo',
					'cat'	=> array('UCP_PROFILE'),
				),
			),
		);
	}
}
