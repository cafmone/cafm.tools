<?php
/**
 * standort_settings_inventory_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_inventory_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_inventory_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_inventory_ident';

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
		$this->standort = $controller->standort;
		$this->datadir = $controller->datadir;
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
				case '':
				default:
				case 'select':
					$data = $this->select(true);
				break;
				case 'insert':
					$data = $this->insert(true);
				break;
				case 'update':
					$data = $this->update(true);
				break;
				case 'remove':
					$data = $this->remove(true);
				break;
				case 'identifiers':
					$data = $this->identifiers(true);
				break;
				case 'download':
					$data = $this->download();
				break;
		}

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('standort_inventory_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add(array($content));

		return $tab;
	}

	//--------------------------------------------
	/**
	 * identifiers
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function identifiers($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.identifiers.class.php');
			$controller = new standort_settings_inventory_identifiers($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.insert.class.php');
			$controller = new standort_settings_inventory_insert($this);
			$controller->message_param = 'standort_insert';
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.insert.class.php');
			$controller = new standort_settings_inventory_insert($this);
			$controller->message_param = 'standort_insert';
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.select.class.php');
			$controller = new standort_settings_inventory_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function remove($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.remove.class.php');
			$controller = new standort_settings_inventory_remove($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Files
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function files( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'standort.settings.inventory.files.class.php');
			$controller = new standort_settings_inventory_files($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->datadir = $this->datadir;
			return $controller;
		}
	}
}
?>
