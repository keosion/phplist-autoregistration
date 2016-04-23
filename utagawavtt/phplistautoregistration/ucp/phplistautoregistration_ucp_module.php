<?php
/**
*
* @package phpBB Extension - UtagawaVTT Phplist autoregistration
*
*/

namespace utagawavtt\phplistautoregistration\ucp;

use utagawavtt\phplistautoregistration\libraries\phpListRESTApiClient;

class phplistautoregistration_ucp_module
{
	var $u_action;
    var $phpList = null;
    var $apiUrl = null;
    var $login = null;
    var $password = null;
    var $cacheDir = null;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');
		$this->tpl_name = 'phplistautoregistration_ucp_body';
		$this->page_title = $user->lang('UCP_PHPLISTAUTOREGISTRATION_TITLE');
		add_form_key('utagawavtt/phplistautoregistration/ucp');

        // retrieve config data
        $this->apiUrl = html_entity_decode($config['utagawavtt_phplistautoregistration_apiUrl']);
        $this->login = $config['utagawavtt_phplistautoregistration_login'];
        $this->password = $config['utagawavtt_phplistautoregistration_password'];
        $this->cacheDir = $config['utagawavtt_phplistautoregistration_cacheDir'];

        $listIDs = $config['utagawavtt_phplistautoregistration_listIDs'];
        $configOK = $config['utagawavtt_phplistautoregistration_configOK'];
        $listIDsAry = explode(',', $listIDs);

		$template->assign_vars(array(
			'U_ACTION'      => $this->u_action
		));

        // exit now if the api access configuration is not valid
        if (!$configOK) {
            return;
        }

        // connect to Api, exit if it fails
        $this->_connectToAPI($user->data['user_id']);
        if (!$this->phpList->login()) {
            return;
        }

        // get phplist subscriber id
        $subscriberId = $this->phpList->subscriberFindByEmail($user->data['user_email']);
        if ($subscriberId === false) {
            $subscriberId = $this->phpList->subscriberAdd($user->data['user_email']);
        }

        // process form submission if needed
		if ($request->is_set_post('submit')) {
			if (!check_form_key('utagawavtt/phplistautoregistration/ucp')) {
				trigger_error('FORM_INVALID');
			}

            $regLists = $request->variable('phplistautoregistration_registListIDs', array('' => 0));
            $unRegLists = array_diff($listIDsAry, $regLists);
            $this->_updateLists($subscriberId, $regLists, $unRegLists);

            $message = $user->lang['PROFILE_UPDATED']
                        .'<br /><br />'
                        .sprintf($user->lang['RETURN_UCP'], "<a href='$this->u_action'>", '</a>');
            trigger_error($message);
		}

        // retrieve and assign template data
        $listsRegistered = $this->_getListsRegistered($subscriberId);
        $listsAll = $this->_getLists();

        foreach ($listIDsAry as $listID) {
            $template->assign_block_vars('lists', array(
                    'ID' => $listID,
                    'NAME' => $listsAll[$listID]->name,
                    'CHECKED' => in_array($listID, $listsRegistered)
                )
            );
        }

        $this->phpList->clearCookie();
	}

    /*
     * _getListsRegistered
     *
     * Get subscribed lists for the given $subscriberId
     */
    private function _getListsRegistered($subscriberId) {
        $lists = $this->phpList->listsSubscriber($subscriberId);
        $listsOrdered = array();
        foreach ($lists as $list) {
            $listsOrdered[] = $list->id;
        }
        return $listsOrdered;
    }

    /*
     * _getLists
     *
     * Get all lists sorted by list id
     */
    private function _getLists() {
        return $this->phpList->orderedListsGet();
    }

    /*
     * _updateLists
     *
     * Add and/or delete subscriber to given lists
     */
    private function _updateLists($subscriberId, $regLists, $unRegLists) {
        foreach($regLists as $regList) {
            $this->phpList->listSubscriberAdd($regList, $subscriberId);
        }
        foreach($unRegLists as $unRegList) {
            $this->phpList->listSubscriberDelete($unRegList, $subscriberId);
        }
    }

    /*
     * _connectToAPI
     *
     * Create and configure connection client
     */
    private function _connectToAPI($uid) {
        $this->phpList = new phpListRESTApiClient($this->apiUrl, $this->login, $this->password, $uid);
        $this->phpList->tmpPath = dirname(__FILE__) . '/../../../../' . $this->cacheDir;
    }
}
