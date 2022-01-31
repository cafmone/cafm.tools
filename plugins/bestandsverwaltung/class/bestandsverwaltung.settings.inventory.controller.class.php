<?php
/**
 * bestandsverwaltung_settings_inventory_controller
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

class bestandsverwaltung_settings_inventory_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_inventory_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_inventory_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_inventory_ident';

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
			$this->action = 'identifiers';
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				default:
				case 'identifiers':
					#$data[] = $this->process();
					$data[] = $this->qrcode();
					$data[] = $this->filters();
					$data[] = $this->form();
					$data[] = $this->identifiers(true);
				break;
				case 'form':
					#$data[] = $this->process();
					$data[] = $this->qrcode();
					$data[] = $this->filters();
					$data[] = $this->form(true);
					$data[] = $this->identifiers();
				break;
				case 'filters':
					#$data[] = $this->process();
					$data[] = $this->qrcode();
					$data[] = $this->filters(true);
					$data[] = $this->form();
					$data[] = $this->identifiers();
				break;
				case 'qrcode':
					#$data[] = $this->process();
					$data[] = $this->qrcode(true);
					$data[] = $this->filters();
					$data[] = $this->form();
					$data[] = $this->identifiers();
				break;
				case 'process':
					#$data[] = $this->process(true);
					$data[] = $this->qrcode();
					$data[] = $this->filters();
					$data[] = $this->form();
					$data[] = $this->identifiers();
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_inventory_tab');
		$tab->message_param = 'some_param';
		$tab->css = 'htmlobject_tabs right noprint';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * Identifiers
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function identifiers($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.identifiers.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_identifiers_controller($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_identifiers'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'identifiers' );
		$content['onclick'] = false;
		$content['css'] = 'noborder';
		if($this->action === 'identifiers') {
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Form
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function form($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.form.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_form_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_form'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'form' );
		$content['onclick'] = false;
		if($this->action === 'form'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * QRCode
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function qrcode($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.qrcode.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_qrcode_controller($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_qrcode'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'qrcode' );
		$content['onclick'] = false;
		$content['css'] = 'noborder';
		if($this->action === 'qrcode') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Filters
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function filters($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.filters.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_filters_controller($this);
			#$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_filters'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'filters' );
		$content['onclick'] = false;
		$content['css'] = 'noborder';
		if($this->action === 'filters') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Process
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function process($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.process.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_process_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_process'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'process' );
		$content['onclick'] = false;
		if($this->action === 'process'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
