<?php
/**
 * bestandsverwaltung_settings_inventory_qrcode_controller
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
 *  Copyright (c) 2008-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_qrcode_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_qrcode_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_qrcode_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_qrcode_ident';

var $tpldir;

/**
* translation
* @access public
* @var array
*/
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
		$this->settings = $this->profilesdir.'bestandsverwaltung.qrcode.ini';
		$this->ini = $this->file->get_ini( $this->settings, true, true );
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
			$this->action = 'settings';
		}

		if($this->response->cancel()) {
			$this->action = 'settings';
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				default:
				case 'settings':
					$data[] = $this->settings(true);
					$data[] = $this->leitz();
				break;
				case 'leitz':
					$data[] = $this->settings();
					$data[] = $this->leitz(true);
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_qrcode_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		#$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function settings($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.qrcode.settings.class.php');
			$controller = new bestandsverwaltung_settings_inventory_qrcode_settings($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_qrcode_settings'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'settings' );
		$content['onclick'] = false;
		#$content['hidden'] = true;
		if($this->action === 'settings') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Leitz Template
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function leitz($visible = false) {
		$data = '';
		if( $visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.qrcode.leitz.class.php');
			$controller = new bestandsverwaltung_settings_inventory_qrcode_leitz($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}

		$content['label']   = 'Leitz Icon';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'leitz' );
		$content['onclick'] = false;
		$content['hidden']  = true;

		if(isset($this->ini['settings']['type'])) {
			switch($this->ini['settings']['type']) {
				case 'leitz_icon':
					$content['hidden'] = false;
				break;
			}
		}
		if($this->action === 'leitz'){
			$content['active']  = true;
		}
		return $content;
	}


}
?>
