<?php
/**
 * bestandsverwaltung_settings_raumbuch_controller
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
 *  Copyright (c) 2015-2017, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_raumbuch_controller
{
/**
* prefix for raumbuch tables
* @access public
* @var string
*/
var $table_prefix = 'raumbuch_';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_raumbuch_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'raumbuch_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'raumbuch_ident';

var $tpldir;

var $lang = array(
	#'label_raumbuch' => 'Standort #%s',
	'label_select' => '&Uuml;bersicht',
	'label_form' => 'Formular',
	'label_import' => 'Import',
	'import' => array(
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
	),
	'remove' => array(
		'confirm' => 'Realy remove ID %s?',
		'not_empty' => 'Found %s devices for this location!',
		'success' => 'successfully removed %s',
		'error_has_children' => 'Not removing ID %s - ID has children',
	),
	'select' => array(
		'title_insert' => 'New entry',
		'title_edit' => 'Edit',
		'title_filter' => 'Show devices',
		'title_remove' => 'Remove',
	),
	'insert' => array(
		'tab_attribs' => 'Attributes',
		'tab_files' => 'Files',
		'label_name' => 'Name',
		'label_parent' => 'Parent',
		'label_identifier' => 'Identifier',
		'label_id' => 'ID',
		'error_exists' => 'ERROR: ID %s already in use',
		'msg_added' => 'Successfully added ID %s',
		'msg_updated' => 'Successfully updated ID %s',
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
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
		$this->raumbuch = new standort($this->db, $this->file);
		$this->datadir  = PROFILESDIR.'/bestand/raumbuch/';
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
			if($this->action === 'form') {
				$this->action = 'form';
			}
			else if($this->action === 'import') {
				$this->action = 'import';
			} else {
				$this->action = 'select';
			}
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'select':
					$content[] = $this->import();
					$content[] = $this->form();
					$content[] = $this->select(true);
				break;
				case 'insert':
					$content[] = $this->import();
					$content[] = $this->form();
					$content[] = $this->insert(true);
				break;
				case 'update':
					$content[] = $this->import();
					$content[] = $this->form();
					$content[] = $this->update(true);
				break;
				case 'remove':
					$content[] = $this->import();
					$content[] = $this->form();
					$content[] = $this->remove(true);
				break;
				case 'form':
					$content[] = $this->import();
					$content[] = $this->form(true);
					$content[] = $this->select();
				break;
				case 'import':
					$content[] = $this->import(true);
					$content[] = $this->form();
					$content[] = $this->select();
				break;

				case 'printout':
					$data = $this->printout();
				break;
				case 'download':
					$data = $this->download();
				break;
			}
		}

		#$content['label']   = 'x';
		#$content['hidden']  = true;
		#$content['value']   = $data;
		#$content['target']  = $this->response->html->thisfile;
		#$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		#$content['onclick'] = false;
		#$content['active']  = true;

		$tab = $this->response->html->tabmenu('raumbuch_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs right noprint';
		$tab->boxcss = 'tab-content';
		$tab->auto_tab = false;
		$tab->add($content);

		return $tab;
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
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.insert.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_insert($this);
			$controller->message_param = 'standort_insert';
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = 'Insert';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'insert' );
		$content['onclick'] = false;
		if($this->action === 'insert'){
			$content['active']  = true;
		}
		return $content;
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
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.insert.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_insert($this);
			$controller->message_param = 'standort_insert';
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_select'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
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
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.select.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_select'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
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
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.remove.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_remove($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_select'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
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
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.form.controller.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_form_controller($this);
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['form'];
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
	 * Import
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function import($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.import.class.php');
			$controller = new bestandsverwaltung_settings_raumbuch_import($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['import'];
			$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_import'];
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
	 * Printout
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function printout() {
		require_once($this->classdir.'bestandsverwaltung.settings.raumbuch.printout.class.php');
		$controller = new bestandsverwaltung_settings_raumbuch_printout($this);
		$controller->message_param = $this->message_param;
		$controller->actions_name = $this->actions_name;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$controller->identifier_name = $this->identifier_name;
		$data = $controller->action();
		return $data;
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
		$path = PROFILESDIR.''.$path;
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
			require_once($this->classdir.'bestandsverwaltung.recording.files.class.php');
			$controller = new bestandsverwaltung_recording_files($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->datadir = $this->datadir ;
			return $controller;
		}
	}


}
?>
