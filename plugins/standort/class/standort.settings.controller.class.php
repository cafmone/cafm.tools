<?php
/**
 * standort_settings_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_settings_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_settings_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_ident';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'tab_inventory' => 'Inventory',
	'tab_identifiers' => 'Identifiers',
	'tab_form' => 'Form',
	'tab_import' => 'Import',
	'inventory' => array(
		'table_id' => 'ID',
		'table_identifier' => 'Identifier',
		'table_path' => 'Path',
		'button_insert' => 'New',
		'title_insert' => 'New entry',
		'title_append' => 'Append new entry to ID %s',
		'title_edit' => 'Edit ID %s',
		'title_filter' => 'Show devices',
		'title_remove' => 'Remove ID %s',
		'tab_attribs' => 'Attributes',
		'tab_files' => 'Files',
		'label_name' => 'Name',
		'label_parent' => 'Parent',
		'label_identifier' => 'Identifier',
		'label_id' => 'ID',
		'confirm_remove' => 'Realy remove ID %s?',
		'error_exists' => 'ERROR: ID %s already in use',
		'error_has_children' => 'Not removing ID %s - ID has children',
		'msg_insert_success' => 'Created ID %s',
		'msg_update_success' => 'Updated ID %s',
		'msg_remove_success' => 'Removed ID %s',
	),
	'identifiers' => array(
		'button_insert' => 'New',
		'button_sort' => 'Sort',
		'button_remove' => 'Remove',
		'title_insert' => 'New identifier',
		'title_sort' => 'Sort identifiers',
		'title_edit' => 'Edit identifier %s',
		'label_pos' => 'Position',
		'label_short' => 'Short',
		'label_long' => 'Long',
		'confirm_remove' => 'Remove selected identifier(s)?',
		'error_short_misspelled' => 'Short must be %s only',
		'error_exists' => 'Error: Identifier %s is already in use',
		'msg_insert_success' => 'Created identifier %s',
		'msg_update_success' => 'Updated identifier %s',
		'msg_remove_success' => 'Removed identifier %s',
	),
	'import' => array(
		'tab_step1' => 'Step 1',
		'tab_step2' => 'Step 2',
		'tab_step3' => 'Step 3',
		'tab_step4' => 'Step 4',
		'label_sheet' => 'Sheet',
		'label_offset' => 'Offset',
		'label_columns' => 'Column(s)',
		'label_delimiter' => 'Delimiter',
		'label_idcolumn' => 'ID Column',
		'title_sheet' => 'Sheet to parse',
		'title_offset' => 'Row to start with',
		'title_columns' => 'Column(s) to parse e.g. B,C,AA',
		'title_delimiter' => 'Delimiter to use e.g. /',
		'title_idcolumn' => 'Column to use as id e.g. A',
		'error_NaN' => '%s must be a number',
		'error_misspelled' => '%s must be %s only',
		'error_import_duplicate_id' => 'Error: ID %s (row %s) is not unique',
		'error_duplicate_id' => 'Error: ID %s (row %s) already used',
		'error_duplicate_path' => 'Error: Ignoring row %s. Path already in database.',
		'error_nothing_todo' => 'Nothing Todo',
		'warning_duplicate_path' => 'Warning: Ignoring row %s. Path already in database.',
		'msg_step2_success' => 'Import file successfuly parsed',
	),
);

var $tpldir;

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
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->datadir  = $this->profilesdir.'/webdav/standort/devices/';
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/standort/lang/', 'standort.settings.controller.ini');
		require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
		$this->standort = new standort($this->db, $this->file);
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
	
		// Settings Error
		if(!isset($this->settings) || 
			!isset($this->settings['query']['prefix']) || 
			!isset($this->settings['query']['db'])
		) {
			$_REQUEST[$this->message_param]['error'] = 'Error: Please check plugin settings';
		}


		// Group switch
		$groups = array();
		if(isset($this->settings['settings']['supervisor'])) {
			$groups[] = $this->settings['settings']['supervisor'];
		}

		// check user is_valid
		if($this->user->is_valid($groups)) {

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
				$this->action = 'inventory';
			}
			$this->response->add($this->actions_name, $this->action);

			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'inventory':
					$content[] = $this->inventory(true);
					#$content[] = $this->db();
					$content[] = $this->identifiers();
					$content[] = $this->form();
					$content[] = $this->import();
				break;
				case 'form':
					$content[] = $this->inventory();
					#$content[] = $this->db();
					$content[] = $this->identifiers();
					$content[] = $this->form(true);
					$content[] = $this->import();
				break;
				case 'import':
					$content[] = $this->inventory();
					#$content[] = $this->db();
					$content[] = $this->identifiers();
					$content[] = $this->form();
					$content[] = $this->import(true);
				break;
				case 'identifiers':
					$content[] = $this->inventory();
					#$content[] = $this->db();
					$content[] = $this->identifiers(true);
					$content[] = $this->form();
					$content[] = $this->import();
				break;
				case 'db':
					$content[] = $this->inventory();
					$content[] = $this->db(true);
					$content[] = $this->identifiers();
					$content[] = $this->form();
					$content[] = $this->import();
				break;

				case 'download':
					$data = $this->download();
				break;
			}

			$tab = $this->response->html->tabmenu('standort_settings_tab');
			$tab->message_param = $this->message_param;
			$tab->css = 'htmlobject_tabs noprint';
			$tab->boxcss = 'tab-content';
			$tab->auto_tab = false;
			$tab->add($content);

			return $tab;

		} else {
			$div = $this->response->html->div();
			$div->add('Permission denied');
			return $div;
		}
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
			require_once($this->classdir.'standort.settings.inventory.controller.class.php');
			$controller = new standort_settings_inventory_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang['inventory'];
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_inventory'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'inventory' );
		$content['onclick'] = false;
		if($this->action === 'inventory'){
			$content['active']  = true;
		}
		return $content;
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
			require_once($this->classdir.'standort.settings.identifiers.controller.class.php');
			$controller = new standort_settings_identifiers_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['identifiers'];
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_identifiers'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'identifiers' );
		$content['onclick'] = false;
		if($this->action === 'identifiers'){
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
			require_once($this->classdir.'standort.settings.form.controller.class.php');
			$controller = new standort_settings_form_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_form'];
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
	 * Import
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function import($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.import.controller.class.php');
			$controller = new standort_settings_import_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['import'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_import'];
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
	 * Database
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function db($visible = false) {
		$data = '';
		if( $visible === true ) {

			if(isset($this->settings['query']['db'])) {
				require_once(CLASSDIR.'lib/db/query.controller.class.php');
				$controller = new query_controller($this->file, $this->response, $this->db, $this->user);
				$controller->tpldir = CLASSDIR.'lib/db/templates/';
				#$controller->lang  = $this->lang;
				$data = $controller->action();
			} else {
				$div = $this->response->html->div();
				$div->add('Error: Please check settings db');
				$data = $div;
			}
		}
		$content['label']   = 'Database';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'db' );
		$content['onclick'] = false;
		if($this->action === 'db'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function download() {
		require_once(CLASSDIR.'/lib/file/file.mime.class.php');
		$path = $this->response->html->request()->get('path');
		$path = $this->profilesdir.'/webdav/standort/'.$path;
		$file = $this->file->get_fileinfo($path);
		$mime = detect_mime($file['path']);

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		header("Content-disposition: inline; filename=".$file['name']);
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
	}

}
?>
