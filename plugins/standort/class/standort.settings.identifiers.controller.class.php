<?php
/**
 * standort_settings_identifiers_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_identifiers_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_identifiers_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_identifiers_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_identifiers_ident';

var $tpldir;

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->file = $controller->file;
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->controller = $controller;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->settings = $controller->settings;
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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		else if($ar === '') {
			$this->action = 'select';
		}

		if($this->response->cancel()) {
			$this->action = 'select';
		}

		$this->response->add($this->actions_name, $this->action);
		$data = array();
		switch( $this->action ) {
			default:
			case 'select':
				$data[] = $this->select(true);
			break;
			case 'insert':
				$data[] = $this->insert(true);
			break;
			case 'sort':
				$data[] = $this->sort(true);
			break;
			case 'remove':
				$data[] = $this->remove(true);
			break;
		}

		$tab = $this->response->html->tabmenu('standort_identifiers_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.identifiers.select.class.php');
			$controller = new standort_settings_identifiers_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'select') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * sort
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function sort($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.identifiers.sort.class.php');
			$controller = new standort_settings_identifiers_sort($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'sort' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'sort') {
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * insert
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.identifiers.insert.class.php');
			$controller = new standort_settings_identifiers_insert($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'insert';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'insert' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'insert') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * remove
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function remove($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.identifiers.remove.class.php');
			$controller = new standort_settings_identifiers_remove($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = 'remove';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'remove') {
			$content['active']  = true;
		}
		return $content;
	}

}
?>
