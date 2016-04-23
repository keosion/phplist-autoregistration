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
	'UCP_PHPLISTAUTOREGISTRATION_TITLE'             => 'Abonnements',
	'UCP_PHPLISTAUTOREGISTRATION_LISTS'             => 'Listes :',
	'UCP_PHPLISTAUTOREGISTRATION_LISTS_TXT'         => 'Sélectionnez les listes auxquelles vous voulez vous abonner',

	'UCP_PHPLISTAUTOREGISTRATION_NOT_REGISTERED'	=> 'Aucune liste',
));
