<?php
/**
 * bestandsverwaltung_recording_controller
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

class bestandsverwaltung_recording_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestand_recording_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_recording_msg';

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'label' => 'New Device',
	'tab_insert' => 'New Device',
	'insert' => array(
		'tab_step_1' => 'Step 1',
		'tab_step_2' => 'Step 2',
		'tab_slaves' => 'Slaves',
		'tab_files' => 'Files',
		'tab_data' => 'Data',
		'tab_changelog' => 'Changelog',
		'filter_todos' => 'Todos',
		'filter_trades' => 'Trades',
		'legend_system' => 'System',
		'legend_todos' => 'Todos',
		'button_labor_card' => 'Labor Card',
		'button_pdf' => 'PDF',
		'button_doc' => 'DOC',
		'button_back_new' => 'Record',
		'button_back_select' => 'Survey',
		'label_location' => 'Location',
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
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestandsverwaltung/lang/', 'bestandsverwaltung.recording.controller.ini');
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
			$this->action = 'step1';
		}
	
		if($this->response->cancel()) {
			if($this->action === 'step2') {
				$this->action = 'step1';
			}
		}

		if(!isset($this->db->type)) {
			$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);

			$c['label']   = $this->lang['tab_insert'];
			$c['value']   = $this->__tabs();
			$c['target']  = $this->response->html->thisfile;
			$c['request'] = $this->response->get_array($this->actions_name, 'step1' );
			$c['onclick'] = false;
			$c['hidden']  = true;
			$c['css']     = 'noborder';

			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'step1':
				case 'step2':
					$content[] = $c;
				break;
				case 'todos':
					$content[] = $this->todos( true );
				break;
			}
		}

		$tab = $this->response->html->tabmenu('bestand_recording_tab');
		$tab->message_param = 'inventory_insert_msg';
		$tab->css = 'htmlobject_tabs left noprint';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add($content);

		$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.controller.html');
		$t->add('<h2>'.$this->lang['label'].'</h2>', 'label');
		$t->add($tab, 'tab');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Tabs
	 *
	 * @access public
	 * @return htmlobject_tabs
	 */
	//--------------------------------------------
	function __tabs() {

		switch( $this->action ) {
			case 'step1':
				$content['step2'] = $this->insert();
				$content['step1'] = $this->step1(true);
			break;
			case 'insert':
				$content['step2'] = $this->insert(true);
				$content['step1'] = $this->step1();
			break;
			default:
				$content[] = '';
			break;
		}

		$tab = $this->response->html->tabmenu('inventory_insert_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs noprint right';
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
	function step1($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.step1.class.php');
			$controller = new bestandsverwaltung_recording_step1($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['insert'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['insert']['tab_step_1'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step1' );
		$content['onclick'] = false;
		if($this->action === 'step1'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Step 2
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.insert.class.php');
			$controller = new bestandsverwaltung_recording_insert($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['insert'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['insert']['tab_step_2'];
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
	 * Process
	 *
	 * @access public
	 * @return htmlobject_tabs
	 */
	//--------------------------------------------
/*
	function process($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.process.controller.class.php');
			$controller = new bestandsverwaltung_recording_process_controller($this);
			#$controller->actions_name = $this->actions_name;
			#$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['process'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['process']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'process' );
		$content['onclick'] = false;
		if($this->action === 'process'){
			$content['active']  = true;
		}
		return $content;
	}
*/

	//--------------------------------------------
	/**
	 * Form
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------

/*
	function form($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.form.controller.class.php');
			$controller = new bestandsverwaltung_recording_form_controller($this);
			#$controller->actions_name = $this->actions_name;
			#$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang['form'];
			$data = $controller->action();
		}
		$content['label']   =  $this->lang['form']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'form' );
		$content['onclick'] = false;
		if($this->action === 'form'){
			$content['active']  = true;
		}
		return $content;
	}
*/

	//--------------------------------------------
	/**
	 * Todos
	 *
	 * @access public
	 * @return htmlobject_tabs
	 */
	//--------------------------------------------
	function todos($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.todos.class.php');
			$controller = new bestandsverwaltung_recording_todos($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['process'];
			$data = $controller->action();
		}
		$content['label']   = 'Arbeitskarte';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'todos' );
		$content['onclick'] = false;
		if($this->action === 'todos'){
			$content['active']  = true;
		}
		return $content;
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
			$controller->lang  = $this->lang;
			$controller->datadir = PROFILESDIR.'/bestand/devices/';
			#$controller->identifier_name = $this->identifier_name;
			#$data = $controller->action();
			return $controller;
		}
	}

}
?>
