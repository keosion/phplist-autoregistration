<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_PHPLISTAUTOREGISTRATION_TITLE'			=> 'PhpList',
	'ACP_PHPLISTAUTOREGISTRATION_SETTINGS'		=> 'Settings',
));
