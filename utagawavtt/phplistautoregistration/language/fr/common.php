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
    'ACP_PHPLISTAUTOREGISTRATION_API_CONNECTION'	=> 'Connexion à l\'API',
    'ACP_PHPLISTAUTOREGISTRATION_LISTS'             => 'Listes PhpList',

	'ACP_PHPLISTAUTOREGISTRATION_APIURL'			=> 'Url de l\'API',
	'ACP_PHPLISTAUTOREGISTRATION_APIURL_DESC'		=> 'Pour la configuration par défaut, cela devrait être <i>http://yourwebsite/lists/admin/?page=call&pi=restapi</i>',
	'ACP_PHPLISTAUTOREGISTRATION_LOGIN'             => 'Nom d\'utilisateur',
	'ACP_PHPLISTAUTOREGISTRATION_LOGIN_DESC'		=> 'Nom d\'utilisateur utilisé pour se connecter à l\'API de phplist (doit être administrateur avec le privilège de gestion des abonnés).',
	'ACP_PHPLISTAUTOREGISTRATION_PASSWORD'			=> 'Mot de passe',
	'ACP_PHPLISTAUTOREGISTRATION_PASSWORD_DESC'		=> 'Mot de passe utilisé pour se connecter à l\'API de phplist.',
	'ACP_PHPLISTAUTOREGISTRATION_CACHEDIR'			=> 'Dossier temporaire du cookie',
	'ACP_PHPLISTAUTOREGISTRATION_CACHEDIR_DESC'		=> 'Dossier utilisé pour stocker le cookie de connexion. Chemin relatif depuis la racine du forum phpBB. Par défaut : \'cache\'.',
	'ACP_PHPLISTAUTOREGISTRATION_LISTIDS'			=> 'Listes',
	'ACP_PHPLISTAUTOREGISTRATION_LISTIDS_DESC'		=> 'Listes auxquelles les nouveaux utilisateurs seront inscrits.',

    'ACP_PHPLISTAUTOREGISTRATION_NEED_CONF'         => 'L\'accès à l\'API doit d\'abord être configuré.',

    'ACP_PHPLISTAUTOREGISTRATION_SETTING_SAVED'     => 'Tests réussis. Paramètres enregistrés.',

    'ACP_PHPLISTAUTOREGISTRATION_DELETE_ERROR'      => 'Erreur lors de la désinscription à phplist - Connexion à l\'API REST impossible.',

    'ACP_PHPLISTAUTOREGISTRATION_LOGIN_ERROR'       => 'Connexion à l\'API REST impossible.',
    'ACP_PHPLISTAUTOREGISTRATION_CONNECT_ERROR'     => 'Echec de la requête à l\'API REST.',
    'ACP_PHPLISTAUTOREGISTRATION_LISTS_ERROR'       => 'La liste suivante n\'existe pas : ',
    'ACP_PHPLISTAUTOREGISTRATION_PRIVILEGE_ERROR'   => 'Le compte lié aux identifiants fournis n\'a pas les droits d\'édition des souscriptions.',

	'ACP_PHPLISTAUTOREGISTRATION_WARNING'           => 'ATTENTION : Pour utiliser la version actuelle de l\'API de phpList, nous devons stocker le mot de passe administrateur en clair dans la base de données de phpBB. <br>Par mesure de sécurité : <br>- limitez les droits du compte administrateur choisi pour se connecter à l\'API ; <br>- utilisez une connexion sécurisée ; <br>- configurez la liste d\'adresses IP autorisées à accéder à l\'API. <br>Pour plus d\'informations, voir la <a href=\'https://resources.phplist.com/plugin/restapi\'>page de configuration du plugin restapi de phplist</a>.'
));
