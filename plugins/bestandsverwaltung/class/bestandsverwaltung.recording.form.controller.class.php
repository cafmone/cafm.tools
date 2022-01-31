<?php
/**
 * bestandsverwaltung_recording_form_controller
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

class bestandsverwaltung_recording_form_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestand_form_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_form_msg';

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
		$this->controller = $controller;
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;

		// Validate user
		$groups = array();
		if(isset($this->controller->settings['settings']['supervisor'])) {
			$groups[] = $this->controller->settings['settings']['supervisor']; 
		}
		$this->is_valid = $this->controller->user->is_valid($groups);
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
				$this->action = 'attribs';
			}
	
			if($this->response->cancel()) {
				if($this->action === 'insert') {
					$this->action = 'attribs';
				}
			}

			if(!isset($this->db->type)) {
				$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
			} else {
				$this->response->add($this->actions_name, $this->action);

				$content = array();
				switch( $this->action ) {
					case '':
					default:
					case 'identifiers':
						$content[] = $this->identifiers( true );
						$content[] = $this->attribs();
						$content[] = $this->options();
						$content[] = $this->index();
					break;
					case 'attribs':
						$content[] = $this->identifiers();
						$content[] = $this->attribs( true );
						$content[] = $this->options();
						$content[] = $this->index();
					break;
					case 'options':
						$content[] = $this->identifiers();
						$content[] = $this->attribs();
						$content[] = $this->options( true );
						$content[] = $this->index();
					break;
					case 'index':
						$content[] = $this->identifiers();
						$content[] = $this->attribs();
						$content[] = $this->options();
						$content[] = $this->index( true );
					break;
					case 'insert':
						$content[] = $this->identifiers();
						$content[] = $this->insert( true );
						$content[] = $this->options();
						$content[] = $this->index();
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

		$tab = $this->response->html->tabmenu('bestand_recording_form_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs noprint';
		$tab->auto_tab = false;
		$tab->add($content);

		return $tab;
	}

	//--------------------------------------------
	/**
	 * Attribs
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function attribs($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.form.attribs.class.php');
			$controller = new bestandsverwaltung_recording_form_attribs($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_attribs'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'attribs' );
		$content['onclick'] = false;
		if($this->action === 'attribs'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Options
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function options($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.form.options.class.php');
			$controller = new bestandsverwaltung_recording_form_options($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_options'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'options' );
		$content['onclick'] = false;
		if($this->action === 'options'){
			$content['active']  = true;
		}
		return $content;
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
			require_once($this->classdir.'bestandsverwaltung.recording.form.insert.class.php');
			$controller = new bestandsverwaltung_recording_form_insert($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_attribs'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'attribs' );
		$content['onclick'] = false;
		if($this->action === 'insert'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Index
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function index($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.form.index.class.php');
			$controller = new bestandsverwaltung_recording_form_index($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_index'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'index' );
		$content['onclick'] = false;
		if($this->action === 'index'){
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
			require_once($this->classdir.'bestandsverwaltung.recording.form.identifiers.class.php');
			$controller = new bestandsverwaltung_recording_form_identifiers($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['label_identifiers'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'identifiers' );
		$content['onclick'] = false;
		if($this->action === 'identifiers'){
			$content['active']  = true;
		}
		return $content;
	}


}
?>
