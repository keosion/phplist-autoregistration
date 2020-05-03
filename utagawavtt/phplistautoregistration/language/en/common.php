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
	'ACP_PHPLISTAUTOREGISTRATION_API_CONNECTION'	=> 'API connection',
	'ACP_PHPLISTAUTOREGISTRATION_LISTS'             => 'PhpList lists',

	'ACP_PHPLISTAUTOREGISTRATION_APIURL'			=> 'API url',
	'ACP_PHPLISTAUTOREGISTRATION_APIURL_DESC'		=> 'With default configuration, it should be <i>http://yourwebsite/lists/admin/?page=call&pi=restapi</i>',
	'ACP_PHPLISTAUTOREGISTRATION_LOGIN'             => 'Login',
	'ACP_PHPLISTAUTOREGISTRATION_LOGIN_DESC'		=> 'Login used to connect to the phplist API (must be administrator with subscriber management permission).',
	'ACP_PHPLISTAUTOREGISTRATION_PASSWORD'			=> 'Password',
	'ACP_PHPLISTAUTOREGISTRATION_PASSWORD_DESC'		=> 'Password used to connect to the phplist API.',
	'ACP_PHPLISTAUTOREGISTRATION_CACHEDIR'			=> 'Cookie temporary directory',
	'ACP_PHPLISTAUTOREGISTRATION_CACHEDIR_DESC'		=> 'Cache directory relative to the phpBB forum root path. Default is \'cache\'.',
	'ACP_PHPLISTAUTOREGISTRATION_LISTIDS'			=> 'Visible lists',
	'ACP_PHPLISTAUTOREGISTRATION_LISTIDS_DESC'		=> 'Lists new users will be subscribed to if they accept.',
	'ACP_PHPLISTAUTOREGISTRATION_HIDDENLISTIDS'		=> 'Hidden lists',
	'ACP_PHPLISTAUTOREGISTRATION_HIDDENLISTIDS_DESC'=> 'Lists new users will be forced to subscribe to.',

    'ACP_PHPLISTAUTOREGISTRATION_NEED_CONF'         => 'API acces must be properly configured first.',

	'ACP_PHPLISTAUTOREGISTRATION_SETTING_SAVED'     => 'Tests success. Settings have been saved.',

    'ACP_PHPLISTAUTOREGISTRATION_DELETE_ERROR'      => 'Erreur lors de la désinscription à phplist - Connexion à l\'API REST impossible.',

    'ACP_PHPLISTAUTOREGISTRATION_LOGIN_ERROR'       => 'Unable to connect to phpList REST API.',
    'ACP_PHPLISTAUTOREGISTRATION_CONNECT_ERROR'     => 'Request to phpList REST API failed.',
    'ACP_PHPLISTAUTOREGISTRATION_LISTS_ERROR'       => 'Following list doesn\'t exist: ',
    'ACP_PHPLISTAUTOREGISTRATION_PRIVILEGE_ERROR'   => 'Provided administrator account doesn\'t have permission to edit subscribers.',

	'ACP_PHPLISTAUTOREGISTRATION_WARNING'           => 'WARNING: To use the current version of phpList API, we have to store admin unencrypted password phpBB database. <br>Please consider security issue and at least : <br>- limit administrator permissions that connects to API ; <br>- use a secure connection ; <br>- configure IP adress that are allowed to connect to phpList API. <br>For more informations, refer to the <a href=\'https://resources.phplist.com/plugin/restapi\'>restapi plugin configuration page</a>.'
));
