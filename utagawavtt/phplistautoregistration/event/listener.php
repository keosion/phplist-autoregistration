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

	protected $apiURL;
	protected $login;
	protected $password;
	protected $cacheDir;
	protected $listIDs;

	/**
	* Constructor
	*
	* @param \phpbb\user_loader			$user_loader
	* @param \phpbb\user        		$user
	* @param \phpbb\config\config		$config
	* @param \phpbb\template\template	$template
	* @param \phpbb\request\request     $request
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\user $user, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request $request)
	{
		$this->user_loader = $user_loader;
		$this->user = $user;
        $this->config = $config;
        $this->template = $template;
        $this->request = $request;

        // extension configuration
        $this->apiURL = html_entity_decode($this->config['utagawavtt_phplistautoregistration_apiUrl']);
        $this->login = $this->config['utagawavtt_phplistautoregistration_login'];
        $this->password = $this->config['utagawavtt_phplistautoregistration_password'];
        $this->cacheDir = dirname(__FILE__) . '/../../../../' . $this->config['utagawavtt_phplistautoregistration_cacheDir'];
        $this->listIDs = explode(',', $this->config['utagawavtt_phplistautoregistration_listIDs']);
	}

    public static function getSubscribedEvents()
    {
        return array(
            'core.ucp_register_data_before' => 'ucp_register_data_before',
            'core.ucp_register_user_row_after' => 'ucp_register_user_row_after',
            'core.ucp_profile_reg_details_sql_ary' => 'ucp_profile_reg_details_sql_ary',
            'core.acp_users_overview_modify_data' => 'acp_users_overview_modify_data',
            'core.delete_user_before' => 'delete_user_before'
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

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            return;                         // Maybe we should warn someone that things went bad
        }

        if ($submit) {
            // if the user is not yet a subscriber, add it
            $subscriberID = $phpList->subscriberFindByEmail($subscriberEmail);
            if ($subscriberID === false) {
                $subscriberID = $phpList->subscriberAdd($subscriberEmail);
            }
            if ($subscriberID === false) {
                $phpList->clearCookie();
                return;
            }

            $registListIDs = $this->request->variable('phplistautoregistration_registListIDs', array('' => 0));
            // add the subscriber to lists
            foreach ($this->listIDs as $listID) {
                if (in_array($listID, $registListIDs)) {
                    $phpList->listSubscriberAdd($listID, $subscriberID);
                }
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

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_DELETE_ERROR'), E_USER_WARNING);
        }

        $subscriberID = $phpList->subscriberFindByEmail($oldEmailAddress);
        if ($subscriberID !== false) {
            $phpList->subscriberUpdate($subscriberID, $newEmailAddress);
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

        // configure and connect to the API
        $phpList = new phpListRESTApiClient($this->apiURL, $this->login, $this->password);
        $phpList->tmpPath = $this->cacheDir;
        if (!$phpList->login()) {
            trigger_error($this->user->lang('ACP_PHPLISTAUTOREGISTRATION_DELETE_ERROR'), E_USER_WARNING);
        }

        $subscriberID = $phpList->subscriberFindByEmail($oldEmailAddress);
        if ($subscriberID !== false) {
            $phpList->subscriberUpdate($subscriberID, $newEmailAddress);
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
            }
        }

        $phpList->clearCookie();
    }
}
