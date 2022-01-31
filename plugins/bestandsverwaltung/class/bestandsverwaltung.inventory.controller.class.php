<?php
/**
 * bestandsverwaltung_inventory_controller
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
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_inventory_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'inventory_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'inventory_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'inventory_ident';

var $tpldir;

var $lang = array(
	'select' => array(
		'tab_filter_default' => 'Default',
		'tab_filter_custom' => 'Custom',
		'tab_filter_process' => 'Process',
		'tab_filter_todos' => 'Todos',
		'tab_filter_link' => 'Link',
		'label_identifier' => 'Identifier',
		'label_location' => 'Location',
		'label_date' => 'Date',
		'label_trade' => 'Trade',
		'label_identifier_hits' => 'Identifier hits',
		'button_group' => 'Group by ..',
		'button_update' => 'Update',
		'button_copy' => 'Copy',
		'button_qrcode' => 'Qrcode',
		'button_process' => 'Process',
		'button_location' => 'Location',
		'button_todos' => 'Todos',
		'button_ungroup' => 'Ungroup',
		'button_remove' => 'Remove',
		'button_identifier' => 'Identifier',
		'button_title_filter' => 'Open filter menu',
		'button_title_export' => 'Open export menu',
		'button_title_print' => 'Open print menu',
		'button_title_link2clipboard' => 'Copy link to clipboard',
		'button_title_group' => 'Group data',
		'button_title_refresh' => 'Refresh page',
		'button_title_down' => 'Jump to bottom of page',
		'button_title_up' => 'Jump to top of page',
		'button_title_back' => 'Jump back to survey',
	)	
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param bestandsverwaltung_controller $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller  = $controller;
		$this->file        = $controller->file;
		$this->response    = $controller->response->response();
		$this->db          = $controller->db;
		$this->user        = $controller->user;
		$this->settings    = $controller->settings;
		$this->classdir    = $controller->classdir;
		$this->ini         = $this->settings;
		$this->profilesdir = $controller->profilesdir;
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestandsverwaltung/lang/', 'bestandsverwaltung.inventory.controller.ini');
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

		if($this->response->cancel()) {
			if($this->action === 'process' || $this->action === 'remove' || $this->action === 'identifier'|| $this->action === 'update') {
				$this->action = 'select';
			}
			unset($_REQUEST['dbaction']);
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				case '':
				default:
				case 'select':
					$data = $this->select(true);
				break;
				case 'update':
					$data = $this->update(true);
				break;
				case 'copy':
					$data = $this->copy( true );
				break;
				case 'download':
					$data = $this->download();
				break;
				case 'qrcode':
					$data = $this->__qrcode(true);
				break;
				case 'printout':
					$data = $this->printout(true);
				break;
				case 'process':
					$data = $this->process(true);
				break;
				case 'raumbuch':
					$data = $this->raumbuch(true);
				break;
				case 'remove':
					$data = $this->remove(true);
				break;
				case 'identifier':
					$data = $this->identifier(true);
				break;
			}
		}

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('inventory_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = true;
		$tab->add(array($content));
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update( $visible = false, $hide_empty = false) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.update.class.php');
			$controller = new bestandsverwaltung_inventory_update($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->hide_empty = $hide_empty;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Copy
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function copy( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.copy.class.php');
			$controller = new bestandsverwaltung_inventory_copy($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			#$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.select.class.php');
			$controller = new bestandsverwaltung_inventory_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang['select'];
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * QRcode
	 *
	 * @access private
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function __qrcode( $visible = false ) {
		if($visible === true) {
			// handle empty
			$request  = $this->response->html->request()->get($this->identifier_name);
			if($request !== '') { 
				require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.inventory.qrcode.class.php');
				$controller = new bestandsverwaltung_inventory_qrcode($this);
				$controller->message_param = $this->message_param;
				$controller->actions_name = $this->actions_name;
				$controller->tpldir = $this->tpldir;
				$controller->identifier_name  = $this->identifier_name;
				$data = $controller->download();
				return $data;
			} else {
				return $this->select(true);
			}
		}
	}

	//--------------------------------------------
	/**
	 * Print
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function printout( $visible = false ) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.printout.class.php');
			$controller = new bestandsverwaltung_printout($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name  = $this->identifier_name;
			$data = $controller->action();
			return $data;
		}
	}


	//--------------------------------------------
	/**
	 * Change prozess
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function process( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.process.class.php');
			$controller = new bestandsverwaltung_inventory_process($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function remove( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.remove.class.php');
			$controller = new bestandsverwaltung_inventory_remove($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Change identifier
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function identifier( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.identifier.class.php');
			$controller = new bestandsverwaltung_inventory_identifier($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Change raumbuch
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function raumbuch( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.raumbuch.class.php');
			$controller = new bestandsverwaltung_inventory_raumbuch($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * Helper function to redirect downloads
	 * triggered by inventory update page
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function download() {
		$this->controller->__download();
	}

}
?>
