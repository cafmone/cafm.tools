<?php
/**
 * user_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'user_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'user_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'user_tab';
/**
* id for table
* @access public
* @var string
*/
var $prefix_table = 'user_table';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* path to translation
* @access public
* @var string
*/
var $langdir;
/**
* lang
* @access public
* @var string
*/
var $lang = array(
	'label_users' => 'Users',
	'label_groups' => 'Groups',
	'label_account' => 'My Account',
	'label_logout' => 'Logout',
	'setup_msg' => 'Please setup user settings'
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($file, $response, $user) {
		$this->user        = $user;
		$this->file        = $file;
		$this->response    = $response;
		$this->classdir    = CLASSDIR.'/lib/user/';
		$this->tpldir      = CLASSDIR.'/lib/user/templates/';
		$this->profilesdir = PROFILESDIR;

		$this->settings = $this->file->get_ini( $this->profilesdir.'/settings.ini' );

		require_once(CLASSDIR.'lib/db/query.class.php');
		$this->query = new query(CLASSDIR.'lib/db/');
		$s = $this->settings;
		
		if(isset($s['user'])) {
			if($s['user']['saveto'] === 'db') {
				$this->query->host = isset($s['query']['host']) ? $s['query']['host'] : null ;
				$this->query->db   = isset($s['query']['db'])   ? $s['query']['db']   : null ;
				$this->query->user = isset($s['query']['user']) ? $s['query']['user'] : null ;
				$this->query->pass = isset($s['query']['pass']) ? $s['query']['pass'] : null ;
				$this->query->type = isset($s['query']['type']) ? $s['query']['type'] : null ;
			}
			else if($s['user']['saveto'] === 'file') {
				$this->query->db   = $this->profilesdir.'/';
				$this->query->type = 'file';
			}
		}
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->action === '') {
			$this->action = 'users';
		}

		$admin = $this->query->select('groups');
		if(!is_array($admin)) {
			$error = $this->user->query->insert('groups', array('group' => 'Admin', 'rank' => 0));
		}
		if(isset($error) && $error !== '') {
			$_REQUEST[$this->message_param] = $_REQUEST[$this->message_param].'<br>'.$error;
		}
		if(!($this->user instanceof user)) {
			if(!isset($_REQUEST['user']['login']) || $_REQUEST['user']['login'] !== 'admin') {
				$_REQUEST['nomsg'] = '';
				$_REQUEST['user_msg'] = 'Please create user admin first';
				$_REQUEST['user']['login'] = 'admin';
				$_REQUEST['user']['lang'] = 'en';
				$_REQUEST['user']['group'][] = 'Admin';
				$_REQUEST['user']['password'] = 'x';
				$_REQUEST['user']['pass2'] = 'y';
				$_REQUEST['users_action'] = 'insert';
				$this->action = 'users';
			}
		}

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		switch( $this->action ) {
			case '':
			case 'users':
			default:
				$content[] = $this->users();
				$content[] = $this->groups( true );
			break;
			case 'groups':
				$content[] = $this->users( true );
				$content[] = $this->groups();
			break;
			case 'account':
				return $this->account();
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs nav nav-tabs';
		$tab->boxcss = 'tabs-content noborder';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Users
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function users( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once($this->classdir.'/user.users.class.php');
			$controller = new user_users($this);
			$controller->settings     = $this->settings;
			$controller->actions_name = 'users_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'user.users.ini');
			$data = $controller->action();	
		}
		$content['label']   = $this->lang['label_users'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'users' );
		$content['onclick'] = false;
		if($this->action === 'users'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Groups
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function groups( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once($this->classdir.'/user.groups.class.php');
			$controller = new user_groups($this->response, $this->query);
			$controller->actions_name = 'groups_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'user.groups.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_groups'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'groups' );
		$content['onclick'] = false;
		if($this->action === 'groups'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function account() {
		require_once($this->classdir.'/user.users.class.php');
		$controller               = new user_users($this);
		$controller->actions_name = 'users_action';
		$controller->settings     = $this->settings;
		$controller->lang         = $this->user->translate($controller->lang, $this->langdir, 'user.users.ini');
		$response                 = $controller->account();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$response->redirect(
				$response->get_url($this->actions_name, 'account', $this->message_param, $response->msg)
			);
		}

		$l = $this->response->html->a();
		$l->label = $this->lang['label_logout'];
		$l->css   = 'btn btn-default logout float-right';
		$l->style = 'margin-right:5px;';
		$l->href  = $this->response->get_url($this->actions_name, 'logout').'&subaction=submit';
		if(isset($this->settings['user']['authorize'])) {
			if($this->settings['user']['authorize'] === 'httpd') {
				$l->handler = 'onclick="UserLogout();return false;"';
			}
		}

		$data['cancel'] = '';
		$data['login']  = $this->lang['label_account'];
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
			));
		$t = $response->html->template($this->tpldir.'/user.account.html');
		$t->add($l,'logout');
		$t->add($response->form);
		$t->add($vars);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

}
?>
