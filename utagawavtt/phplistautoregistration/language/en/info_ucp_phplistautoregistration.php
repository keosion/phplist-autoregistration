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
	'UCP_PHPLISTAUTOREGISTRATION_TITLE'             => 'Subscriptions',
	'UCP_PHPLISTAUTOREGISTRATION_SETTINGS'          => 'Manage subscriptions',
	'UCP_PHPLISTAUTOREGISTRATION_LISTS'             => 'Lists :',
	'UCP_PHPLISTAUTOREGISTRATION_LISTS_TXT'         => 'Select lists you want to subscribe to',

	'UCP_PHPLISTAUTOREGISTRATION_NOT_REGISTERED'	=> 'No list',
));
