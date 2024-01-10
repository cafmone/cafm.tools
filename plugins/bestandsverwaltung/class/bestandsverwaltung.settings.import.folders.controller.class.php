<?php
/**
 * bestandsverwaltung_settings_inventory_folders_import_controller
 *
 * This file is part of plugin bestandsverwaltung
 *
 *  This file is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU GENERAL PUBLIC LICENSE Version 2
 *  as published by the Free Software Foundation;
 *
 *  This file is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this file (see ../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2008-2023, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2023, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_import_folders_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_import_folders_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_import_folders_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_import_folders_ident';
/**
* inventory filter
* @access public
* @var bool
*/
var $inventory_filter = true;

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

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
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
				case 'remove':
					$data[] = $this->remove(true);
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_identifiers_tab');
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
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.import.folders.select.class.php');
			$controller = new bestandsverwaltung_settings_inventory_import_folders_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->identifier_name = $this->identifier_name;
			$controller->tpldir = $this->tpldir;
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
	 * insert
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.import.folders.insert.class.php');
			$controller = new bestandsverwaltung_settings_inventory_import_folders_insert($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
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
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.import.folders.remove.class.php');
			$controller = new bestandsverwaltung_settings_inventory_import_folders_remove($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = 'insert';
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
