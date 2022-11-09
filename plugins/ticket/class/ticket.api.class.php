<?php
/**
 * ticket_api
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_api
{

var $lang = array(
	'ticket_id' => 'Ticket #%s',
	'link_config' => 'Settings',
	'link_account' => 'Account',
	'link_logout' => 'Logout',
	'select_reporter' => 'Please select a user',
	'error_insert_email' => 'Email must not be empty or select a name from the list above'
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
	function __construct($file, $response, $db, $user) {
		$this->db       = $db;
		$this->file     = $file;
		$this->response = $response;
		$this->user     = $user;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action($action = null) {
		$command = $this->response->html->request()->get('command');
		switch($command) {
			case 'get_supporters':
				$this->get_supporters();
			break;
			case 'get_changelog':
				$this->get_changelog();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get Supporters
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_supporters() {
		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			$result = $this->user->query->select('users2groups', array('login'), array('group', $id));
			if(is_array($result)) {
				$select        = $this->response->html->select();
				$select->css   = 'htmlobject_select form-control';
				$select->id    = 'supporter';
				$select->name  = 'supporter';
				foreach($result as $v) {
					$select->add(array($v['login']), array(0,0));
				}
				echo $select->get_string();
			}
		}	
	}

	//--------------------------------------------
	/**
	 * Get Changelog
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_changelog() {
		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			require_once(CLASSDIR.'plugins/ticket/ticket.controller.class.php');
			$controller = new ticket_controller($this->file, $this->response, $this->db, $this->user);
			echo $controller->changelog($id)->get();
		}	
	}

}
?>
