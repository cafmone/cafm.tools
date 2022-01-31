<?php


class phppublisher_config_users extends user_controller
{


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
				#$content[] = $this->permissions( true );
			break;
			case 'groups':
				$content[] = $this->users( true );
				$content[] = $this->groups();
				#$content[] = $this->permissions( true );
			break;
			#case 'permissions':
			#	$content[] = $this->users( true );
			#	$content[] = $this->groups( true );
			#	$content[] = $this->permissions();
			#break;
			case 'account':
				return $this->account();
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Permissions
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
/*
	function permissions( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once(CLASSDIR.'/phppublisher.config.permissions.class.php');
			$controller = new phppublisher_config_permissions( $this->response, $this->file, $this->user, $this->query);
			$controller->settings     = $this->settings;
			$controller->actions_name = 'permissions_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'user.permissions.ini');
			$data = $controller->action();
		}
		$content['label']   = 'Permissions';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'permissions' );
		$content['onclick'] = false;
		if($this->action === 'permissions'){
			$content['active']  = true;
		}
		return $content;
	}
*/


}
?>
