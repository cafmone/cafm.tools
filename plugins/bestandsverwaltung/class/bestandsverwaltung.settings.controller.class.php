<?php
/**
 * bestandsverwaltung_settings_controller
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

class bestandsverwaltung_settings_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_action';

/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_msg';

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'inventory' => array(
		'tab' => 'Inventory',
		'tab_identifiers' => 'Identifiers',
		'tab_form' => 'Form',
		'tab_qrcode' => 'QRCode',
		'tab_qrcode_settings' => 'Settings',
		'tab_filters' => 'Filters',
		'tab_filters_custom' => 'Custom',

		'tab_export' => 'Export',
		'tab_export_config' => 'Settings',

		'label_process' => 'Process',
		'label_state' => 'State',
		'label_short' => 'Short',
		'label_long' => 'Long',
		'label_alias' => 'Alias',
		'label_height' => 'Height (mm)',
		'label_width' => 'Width (mm)',
		'label_border' => 'Border (mm)',
		'label_fontsize' => 'Font (mm)',
		'label_type' => 'Type',
		'label_id_only' => 'ID only',
		'label_id_auto' => 'Auto',
		'label_id_custom' => 'Custom',
		'label_identifier' => 'Identifier',
		'legend_type' => 'Type',
		'legend_size' => 'Size',
		'legend_url' => 'Url',
		'legend_replacements' => 'Replacements',
		'headline_sync_identifiers' => 'Synchronize identifiers',
		'headline_update_state' => 'Change state',
		'headline_add_identifier' => 'New Identifier',
		'button_sync' => 'Synchronize',
		'button_remove' => 'Remove',
		'button_title_add_identifier' => 'Add new identifier',
		'button_title_sync_identifiers' => 'Sync identifiers',
		'button_title_sync_identifier' => 'Sync identifier %s',
		'button_title_download_identifier' => 'Download identifier list',
		'button_title_edit_identifier' => 'Edit identifier %s',
		'button_title_insert_identifier' => 'Insert identifier %s',
		'msg_update_sucess' => 'Updated successfully',
		'msg_insert_sucess' => 'Added successfully',
		'error_no_url' => 'Error: No url',
	),
	'import' => array(
		'tab' => 'Import',
		'tab_folders' => 'Folders',
		'label_identifier' => 'Identifier',
		'button_import' => 'Import',
		'button_remove' => 'Remove',
	),
	'gewerke' => array(
		'tab' => 'Trades',
		'button_title_add' => 'Add new trade',
		'button_title_clip' => 'Clip trades',
		'button_title_unclip' => 'Unclip trades',
		'button_title_download' => 'Download PDF',
		'button_title_debug' => 'Debug trades',
	),
);

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
		$this->controller = $controller;
		$this->file = $controller->file;
		// derive response
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->plugins = $this->file->get_ini($this->profilesdir.'/plugins.ini');

		// Validate user
		$groups = array();
		if(isset($this->controller->settings['settings']['supervisor'])) {
			$groups[] = $this->controller->settings['settings']['supervisor']; 
		}
		$this->is_valid = $this->controller->user->is_valid($groups);
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestandsverwaltung/lang/', 'bestandsverwaltung.settings.controller.ini');
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
		if($this->is_valid) {
			$this->action = '';
			$ar = $this->response->html->request()->get($this->actions_name);
			if($ar !== '') {
				$this->action = $ar;
			} 
			else if(isset($action)) {
				$this->action = $action;
			}
			else if($ar === '') {
				$this->action = 'select';
			}

			if(!isset($this->db->type)) {
				$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
			} else {
				$this->response->add($this->actions_name, $this->action);
				$content = array();
				switch( $this->action ) {
					case '':
					default:
					case 'inventory':
						$content[] = $this->inventory( true );
						$content[] = $this->import();
						$content[] = $this->gewerke();
					break;
					case 'import':
						$content[] = $this->inventory();
						$content[] = $this->import( true );
						$content[] = $this->gewerke();
					break;
					case 'gewerke':
						$content[] = $this->inventory();
						$content[] = $this->import();
						$content[] = $this->gewerke( true );
					break;
				}
			}
		} else {
			$data['label']   = '';
			$data['value']   = 'Permission denied';
			$data['target']  = '';
			$data['request'] = '';
			$data['onclick'] = false;
			$data['hidden']  = true;
			$data['css']     = 'noborder';

			$content[] = $data;
		}

		$tab = $this->response->html->tabmenu('bestand_settings_tab');
		$tab->message_param = 'settings_msg';
		$tab->css = 'htmlobject_tabs left noprint';
		$tab->auto_tab = false;
		$tab->add($content);

		$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.settings.controller.html');
		$t->add($tab, 'tab');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Inventory
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function inventory($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.controller.class.php');
			$controller = new bestandsverwaltung_settings_inventory_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['inventory'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['inventory']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'inventory' );
		$content['onclick'] = false;
		$content['css']     = 'noborder';
		if($this->action === 'inventory'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * import
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function import($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.import.controller.class.php');
			$controller = new bestandsverwaltung_settings_import_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['import'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['import']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'import' );
		$content['onclick'] = false;
		if($this->action === 'import'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Gewerke
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function gewerke($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.gewerke.controller.class.php');
			$controller = new bestandsverwaltung_settings_gewerke_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['gewerke'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['gewerke']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'gewerke' );
		$content['onclick'] = false;
		$content['css']     = 'noborder';
		if($this->action === 'gewerke'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * export
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
/*
	function export($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.export.controller.class.php');
			$controller = new bestandsverwaltung_settings_export_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['export'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['export']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'export' );
		$content['onclick'] = false;
		if($this->action === 'export'){
			$content['active']  = true;
		}
		return $content;
	}
*/

}
?>
