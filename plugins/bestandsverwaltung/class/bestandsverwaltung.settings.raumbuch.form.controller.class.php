<?php
/**
 * bestandsverwaltung_settings_raumbuch_form_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_raumbuch_form_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'raumbuch_form_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'raumbuch_form_msg';

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'label' => 'Merkmale',
		'label_index' => 'Index',
		'label_devices' => 'Anlagen',
		'label_identifiers' => 'Bezeichner',
		'label_attribs' => 'Merkmale',
		'label_options' => 'Options',
		'msg_sorted' => 'Index neu sortiert',
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
		$this->response = $controller->response;
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
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

		$tab = $this->response->html->tabmenu('raumbuch_form_tab');
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
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.form.attribs.class.php');
			$controller = new bestandsverwaltung_recording_form_attribs($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$controller->table_prefix = 'raumbuch_';
			$controller->table_bezeichner = 'raumbuch_bezeichner';

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
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.form.options.class.php');
			$controller = new bestandsverwaltung_recording_form_options($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$controller->table_prefix = 'raumbuch_';
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
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.form.insert.class.php');
			$controller = new bestandsverwaltung_recording_form_insert($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$controller->table_prefix = 'raumbuch_';
			$controller->table_bezeichner = 'raumbuch_bezeichner';

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
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.form.index.class.php');
			$controller = new bestandsverwaltung_recording_form_index($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$controller->table_prefix = 'raumbuch_';
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
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.form.identifiers.class.php');
			$controller = new bestandsverwaltung_recording_form_identifiers($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$controller->table_prefix = 'raumbuch_';
			$controller->table_bezeichner = 'raumbuch_bezeichner';

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
