<?php
/**
 * bestandsverwaltung_settings_inventory_import_controller
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
 *  Copyright (c) 2015-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_import_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_import_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_import_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_import_ident';

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
		$this->settings = $controller->settings;
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
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}

		if($this->action === '') {
			$this->action = 'folders';
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				default:
				case 'folders':
					$data[] = $this->folders(true);
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_import_tab');
		$tab->message_param = 'some_param';
		$tab->css = 'htmlobject_tabs noprint';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * Folders
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function folders($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.import.folders.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_import_folders_controller($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_folders'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'folders' );
		$content['onclick'] = false;
		$content['css'] = 'noborder';
		if($this->action === 'folders') {
			$content['active']  = true;
		}
		return $content;
	}

}
?>
