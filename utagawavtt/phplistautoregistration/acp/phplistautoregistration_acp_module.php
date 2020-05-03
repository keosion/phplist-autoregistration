<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\acp;

use utagawavtt\phplistautoregistration\libraries\phpListRESTApiClient;

class phplistautoregistration_acp_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');
		$this->tpl_name = 'phplistautoregistration_body';
		$this->page_title = $user->lang('ACP_PHPLISTAUTOREGISTRATION_TITLE');
		add_form_key('utagawavtt/phplistautoregistration');

		if ($request->is_set_post('submit')) {
			if (!check_form_key('utagawavtt/phplistautoregistration')) {
				trigger_error('FORM_INVALID');
			}

			$apiUrl = $request->variable('utagawavtt_phplistautoregistration_apiUrl', '');
			$login = $request->variable('utagawavtt_phplistautoregistration_login', 'phpbbRobot');
			$password = $request->variable('utagawavtt_phplistautoregistration_password', '');
			$cacheDir = $request->variable('utagawavtt_phplistautoregistration_cacheDir', 'cache');
			$listIDsAry = $request->variable('utagawavtt_phplistautoregistration_listIDs', array('' => 0));
            $listIDs = implode(',', $listIDsAry);
			$hiddenListIDsAry = $request->variable('utagawavtt_phplistautoregistration_hiddenListIDs', array('' => 0));
            $hiddenListIDs = implode(',', $hiddenListIDsAry);

            // save before testing so that the user doesnt' loose its modifications
			$config->set('utagawavtt_phplistautoregistration_apiUrl', $apiUrl);
			$config->set('utagawavtt_phplistautoregistration_login', $login);
			$config->set('utagawavtt_phplistautoregistration_password', $password);
			$config->set('utagawavtt_phplistautoregistration_cacheDir', $cacheDir);
			$config->set('utagawavtt_phplistautoregistration_listIDs', $listIDs);
			$config->set('utagawavtt_phplistautoregistration_hiddenListIDs', $hiddenListIDs);

            $config->set('utagawavtt_phplistautoregistration_configOK', '0');
            $this->_testApi(html_entity_decode($apiUrl), $login, $password, $cacheDir, $listIDsAry, $hiddenListIDsAry);
            $config->set('utagawavtt_phplistautoregistration_configOK', '1');

			trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_SETTING_SAVED') . adm_back_link($this->u_action));
		}


        $apiUrl = $config['utagawavtt_phplistautoregistration_apiUrl'];
        $login = $config['utagawavtt_phplistautoregistration_login'];
        $password = $config['utagawavtt_phplistautoregistration_password'];
        $cacheDir = $config['utagawavtt_phplistautoregistration_cacheDir'];
        $listIDs = $config['utagawavtt_phplistautoregistration_listIDs'];
        $hiddenListIDs = $config['utagawavtt_phplistautoregistration_hiddenListIDs'];

		$template->assign_vars(array(
			'U_ACTION'                                      => $this->u_action,
			'UTAGAWAVTT_PHPLISTAUTOREGISTRATION_APIURL'		=> $apiUrl,
			'UTAGAWAVTT_PHPLISTAUTOREGISTRATION_LOGIN'		=> $login,
			'UTAGAWAVTT_PHPLISTAUTOREGISTRATION_PASSWORD'	=> $password,
			'UTAGAWAVTT_PHPLISTAUTOREGISTRATION_CACHEDIR'	=> $cacheDir,
		));

        $existingLists = $this->_getLists(html_entity_decode($apiUrl), $login, $password, $cacheDir);
        $listIDsAry = explode(',', $listIDs);
        $hiddenListIDsAry = explode(',', $hiddenListIDs);
        if ($existingLists !== false && is_array($existingLists)) {
            foreach ($existingLists as $existingList) {
                $template->assign_block_vars('listsAvailable', array(
                        'ID' => $existingList->id,
                        'NAME' => $existingList->name,
                        'CHECKED' => in_array($existingList->id, $listIDsAry)
                    )
                );
                $template->assign_block_vars('hiddenListsAvailable', array(
                        'ID' => $existingList->id,
                        'NAME' => $existingList->name,
                        'CHECKED' => in_array($existingList->id, $hiddenListIDsAry)
                    )
                );
            }
        }
	}

    private function _testApi($apiUrl, $login, $password, $cacheDir, $listIDs, $hiddenListIDs) {
        global $user;

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($apiUrl, $login, $password, $user->data['user_id']);
        $phpList->tmpPath = dirname(__FILE__) . '/../../../../' . $cacheDir;
        if (!$phpList->login()) {
            trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_LOGIN_ERROR'), E_USER_WARNING);
        }

        $lists = $phpList->listsGet();
        // test if we can retreive lists
        if ($lists === false) {
            trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_CONNECT_ERROR'), E_USER_WARNING);
        }
        // test if all the lists are available
        $existingListIDs = array_map(function ($el) { return $el->id; }, $lists);
        foreach ($listIDs as $listID) {
           if (!in_array($listID, $existingListIDs)) {
               trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_LISTS_ERROR').$listID, E_USER_WARNING);
           }
        }
        foreach ($hiddenListIDs as $listID) {
           if (!in_array($listID, $existingListIDs)) {
               trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_LISTS_ERROR').$listID, E_USER_WARNING);
           }
        }
        // test if the administrator have subscriber management permission
        $subscriberID = $phpList->subscriberAdd('test@phplistautoregistration.com');
        if ($subscriberID === false) {
            trigger_error($user->lang('ACP_PHPLISTAUTOREGISTRATION_PRIVILEGE_ERROR'), E_USER_WARNING);
        }
        $phpList->subscriberDelete($subscriberID);

        $phpList->clearCookie();
        return true;
    }

    private function _getLists($apiUrl, $login, $password, $cacheDir) {
        global $user;

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($apiUrl, $login, $password, $user->data['user_id']);
        $phpList->tmpPath = dirname(__FILE__) . '/../../../../' . $cacheDir;

        if (!$phpList->login()) {
            return false;
        }
        $lists = $phpList->listsGet();
        if ($lists === false) {
            return false;
        }

        $phpList->clearCookie();
        return $lists;
    }
}
