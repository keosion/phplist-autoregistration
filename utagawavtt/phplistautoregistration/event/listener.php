<?php
namespace utagawavtt\phplistautoregistration\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use utagawavtt\phplistautoregistration\libraries\phpListRESTApiClient;

class listener implements EventSubscriberInterface
{
    /* @var \phpbb\user_loader */
	protected $user_loader;

    /* @var \phpbb\user */
	protected $user;

    /* @var \phpbb\config\config  */
	protected $config;

    /* @var \phpbb\template\template  */
	protected $template;

    /* @var \phpbb\request\request  */
	protected $request;

    /* @var \phpbb\db\driver\driver_interface  */
	protected $db;

	protected $apiURL;
	protected $login;
	protected $password;
	protected $cacheDir;
	protected $listIDs;

	protected $attributeIdCache;

	/**
	* Constructor
	*
	* @param \phpbb\user_loader                 $user_loader
	* @param \phpbb\user                        $user
	* @param \phpbb\config\config               $config
	* @param \phpbb\template\template           $template
	* @param \phpbb\request\request             $request
	* @param \phpbb\db\driver\driver_interface  $db
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\user $user, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db)
	{
		$this->user_loader = $user_loader;
		$this->user = $user;
        $this->config = $config;
        $this->template = $template;
        $this->request = $request;
        $this->db = $db;

        // extension configuration
        $this->apiURL = html_entity_decode($this->config['utagawavtt_phplistautoregistration_apiUrl']);
        $this->login = $this->config['utagawavtt_phplistautoregistration_login'];
        $this->password = $this->config['utagawavtt_phplistautoregistration_password'];
        $this->cacheDir = dirname(__FILE__) . '/../../../../' . $this->config['utagawavtt_phplistautoregistration_cacheDir'];
        $listIDs = explode(',', $this->config['utagawavtt_phplistautoregistration_listIDs']);
        $hiddenListIDs = explode(',', $this->config['utagawavtt_phplistautoregistration_hiddenListIDs']);
        $this->allListIDs = array_merge($listIDs, $hiddenListIDs);

        $this->attributeIdCache = [];
	}

    public static function getSubscribedEvents()
    {
        return array(
            'core.ucp_register_data_before' => 'ucp_register_data_before',
            'core.ucp_register_user_row_after' => 'ucp_register_user_row_after',
            'core.ucp_profile_reg_details_sql_ary' => 'ucp_profile_reg_details_sql_ary',
            'core.acp_users_overview_modify_data' => 'acp_users_overview_modify_data',
            'core.delete_user_before' => 'delete_user_before',
            'core.notification_manager_add_notifications' => 'notification_manager_add_notifications'
        );
    }

    /*
     * ucp_register_data_before
     *
     * Retrieve and assign template data
     *
     * args : submit
     *
     */
    public function ucp_register_data_before($event)
    {
        // import vars
        $submit = $event['submit'];

        $this->user->add_lang_ext('utagawavtt/phplistautoregistration', 'info_ucp_phplistautoregistration');

        $configOK = $this->config['utagawavtt_phplistautoregistration_configOK'];
        if (!$configOK) {
            return;
        }
        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            return;                         // Maybe we should warn someone that things went bad
        }

        $templateLists = array();

        $listsAll = $phpList->orderedListsGet();
        $listIDsAry = explode(',', $this->config['utagawavtt_phplistautoregistration_listIDs']);
        foreach ($listIDsAry as $listID) {
            $templateLists[$listID] = $listsAll[$listID];
            $templateLists[$listID]->checked = true;
        }

        if ($submit) {
            $registListIDs = $this->request->variable('phplistautoregistration_registListIDs', array('' => 0));
            foreach($templateLists as $templateListId => $templateList) {
                $templateLists[$templateListId]->checked = in_array($templateListId, $registListIDs);
            }
        }

        foreach ($templateLists as $templateListId => $templateList) {
            $this->template->assign_block_vars('lists', array(
                    'ID' => $templateListId,
                    'NAME' => $templateList->name,
                    'CHECKED' => $templateList->checked
                )
            );
        }

        $phpList->clearCookie();
    }

    /*
     * ucp_register_user_row_after
     *
     * Automatically add user to phplist lists on registration
     *
     * args : submit, user_row
     *
     */
    public function ucp_register_user_row_after($event)
    {
        // import vars
        $submit = $event['submit'];
        $user_row = $event['user_row'];

        $subscriberEmail = $user_row['user_email'];
        $subscriberUsername = $user_row['username'];

        if (!$submit) {
            return;
        }

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            return;                         // Maybe we should warn someone that things went bad
        }

        // if the user is not yet a subscriber, add it
        $subscriberID = $phpList->subscriberFindByEmail($subscriberEmail);
        if ($subscriberID === false) {
            $subscriberID = $phpList->subscriberAdd($subscriberEmail);

            if ($subscriberID === false) {
                $phpList->clearCookie();
                return;
            } else {
                $this->_addUpdate_attributes($subscriberID, ['username' => $subscriberUsername]);
            }
        }

        $registListIDs = $this->request->variable('phplistautoregistration_registListIDs', array('' => 0));
        $registHiddenListIDs =  explode(',', $this->config['utagawavtt_phplistautoregistration_hiddenListIDs']);
        $registAllListIDs = array_merge($registListIDs, $registHiddenListIDs);
        // add the subscriber to lists
        foreach ($this->allListIDs as $listID) {
            if (in_array($listID, $registAllListIDs)) {
                $phpList->listSubscriberAdd($listID, $subscriberID);
            }
        }

        $phpList->clearCookie();
    }

    /*
     * ucp_profile_reg_details_sql_ary
     *
     * Automatically update user in phplist subscribers on email update by user
     *
     * args : data
     *
     */
    public function ucp_profile_reg_details_sql_ary($event)
    {
        $this->user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');

        // import vars
        $data = $event['data'];
        $newEmailAddress = $data['email'];
        $oldEmailAddress = $this->user->data['user_email'];
        $newUsername = $data['username'];

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_UPDATE_ERROR'), E_USER_WARNING);
        }

        $subscriberID = $phpList->subscriberFindByEmail($oldEmailAddress);
        if ($subscriberID !== false) {
            $phpList->subscriberUpdate($subscriberID, $newEmailAddress);
            $phpList->subscriberRemoveFromBlacklist($newEmailAddress);
            $this->_addUpdate_attributes($subscriberID, ['username' => $newUsername]);
        }

        $phpList->clearCookie();
    }

    /*
     * acp_users_overview_modify_data
     *
     * Automatically update user in phplist subscribers on email update by admin
     *
     * args : data, user_row
     *
     */
    public function acp_users_overview_modify_data($event)
    {
        $this->user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');

        // import vars
        $data = $event['data'];
        $user_row = $event['user_row'];
        $newEmailAddress = $data['email'];
        $oldEmailAddress = $user_row['user_email'];
        $newUsername = $data['username'];

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_UPDATE_ERROR'), E_USER_WARNING);
        }

        $subscriberID = $phpList->subscriberFindByEmail($oldEmailAddress);
        if ($subscriberID !== false) {
            $phpList->subscriberUpdate($subscriberID, $newEmailAddress);
            $phpList->subscriberRemoveFromBlacklist($newEmailAddress);
            $this->_addUpdate_attributes($subscriberID, ['username' => $newUsername]);
        }

        $phpList->clearCookie();
    }

    /*
     * delete_user_before
     *
     * Automatically delete user in phplist subscribers on delete
     *
     * args : mode, retain_username, user_ids
     *
     */
    public function delete_user_before($event)
    {
        $this->user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');

        // import vars
        $user_ids_tmp = $event['user_ids'];
        $user_ids = is_array($user_ids_tmp) ? $user_ids_tmp : array($user_ids_tmp);

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_DELETE_ERROR'), E_USER_WARNING);
        }

        // delete each user in phplist subscribers
        foreach ($user_ids as $user_id) {
            $this->user_loader->load_users([$user_id]);
            $user_data = $this->user_loader->get_user($user_id);
            if ($user_data['user_id'] != $user_id) {
                continue;
            }
            $subscriberID = $phpList->subscriberFindByEmail($user_data['user_email']);
            if ($subscriberID !== false) {
                $phpList->subscriberDelete($subscriberID);
                $this->_remove_attributes($subscriberID);
            }
        }

        $phpList->clearCookie();
    }

    /*
     * notification_manager_add_notifications
     *
     * On each email notification to be sent, cancel if email adress is blacklisted
     *
     * args : notification_type_name, data, options, notify_users
     *
     */
    public function notification_manager_add_notifications($event)
    {
        $this->user->add_lang_ext('utagawavtt/phplistautoregistration', 'common');

        // import vars
        $users = $event['notify_users'];

        if (!is_array($users) || empty($users)) {
            return;
        }

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_EMAIL_ERROR'), E_USER_WARNING);
        }

        // filter users
        foreach ($users as $userId => $userNotifs) {
            // if user doesn't want to receive email, skip
            if (!in_array('notification.method.email', $userNotifs)) {
                continue;
            }
            // load user
            $this->user_loader->load_users([$userId]);
            $user_data = $this->user_loader->get_user($userId);
            if ($user_data['user_id'] != $userId) {
                continue;
            }
            // check blacklist
            $isBlacklisted = $phpList->subscriberIsBlacklisted($user_data['user_email']);
            if (!$isBlacklisted) {
                continue;
            }
            // user is blacklisted and want only emails -> remove notification
            if (count($userNotifs) === 1) {
                unset($users[$userId]);
                continue;
            }
            // user is blacklisted and want other notifications -> remove email notification only
            $userNotifsFiltered = array_filter($userNotifs, function ($userNotif) {
                return $userNotif !== 'notification.method.email';
            });
            $users[$userId] = $userNotifsFiltered;
        }

        $phpList->clearCookie();

        // export vars
        $event['notify_users'] = $users;
    }

    /*
     * _remove_attributes
     *
     * remove attribute in phplist table
     *
     * $subscriberID   integer
     *
     */
    protected function _remove_attributes($subscriberID)
    {
        $query = "DELETE FROM phplist_user_user_attribute WHERE userid=$subscriberID";
        $result = $this->db->sql_query($query);
        $this->db->sql_freeresult($result);
    }

    /*
     * _addUpdate_attributes
     *
     * add or update attributes in phplist table
     *
     * $subscriberID        integer
     * $attributes          array('attributeName' => 'attibuteValue, ...)
     *
     */
    protected function _addUpdate_attributes($subscriberID, $attributes)
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $attributeId = $this->_getAttributeId($attributeName);

            $query = "INSERT INTO phplist_user_user_attribute SET"
                            . " attributeid=$attributeId,"
                            . " userid=$subscriberID,"
                            . " value=\"$attributeValue\""
                            . " ON DUPLICATE KEY UPDATE value=\"$attributeValue\"";
            $result = $this->db->sql_query($query);
            $this->db->sql_freeresult($result);
        }
    }

    /*
     * _getAttributeId
     *
     * retreive attribute id given attribute name
     *
     * $attributeName       string
     *
     */
    protected function _getAttributeId($attributeName)
    {
        if (isset($this->attributeIdCache[$attributeName])) {
            return $this->attributeIdCache[$attributeName];
        }
        $query_attcheck = "SELECT id AS attr_id FROM phplist_user_attribute WHERE name='$attributeName'";
        $result = $this->db->sql_query($query_attcheck);
        $row = $this->db->sql_fetchrow($result);
        $attributeIdCache[$attributeName] = !$row['attr_id'] ? false : $row['attr_id'];
        $this->db->sql_freeresult($result);
        return $attributeIdCache[$attributeName];
    }

}
