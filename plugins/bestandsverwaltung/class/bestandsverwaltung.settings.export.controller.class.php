<?php
/**
 * bestandsverwaltung_settings_export_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_export_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_export_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_export_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_export_ident';

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
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;

		$plugins = $this->file->get_ini($this->profilesdir.'/plugins.ini');
		if(in_array('taetigkeiten', $plugins)) {
			$this->arbeitskarte = true;
		}
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
			$this->action = 'conject';
		}

		// handle plugins
		$arbeitskarte = '';
		if(isset($this->arbeitskarte)) {
			$arbeitskarte = $this->arbeitskarte();
		}


		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				default:
				case 'conject':
					$data[] = $this->speedikon();
					$data[] = $this->conject(true);
					$data[] = $arbeitskarte;
				break;
				case 'speedikon':
					$data[] = $this->speedikon(true);
					$data[] = $this->conject();
					$data[] = $arbeitskarte;
				break;
				case 'arbeitskarte':
					$data[] = $this->speedikon();
					$data[] = $this->conject();
					$data[] = $this->arbeitskarte(true);
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_export_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs right';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}

	//--------------------------------------------
	/**
	 * arbeitskarte
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function arbeitskarte($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.export.arbeitskarte.class.php');
			$controller = new bestandsverwaltung_settings_export_arbeitskarte($this);
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Arbeitskarte';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'arbeitskarte' );
		$content['onclick'] = false;
		if($this->action === 'arbeitskarte') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * conject
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function conject($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.export.conject.class.php');
			$controller = new bestandsverwaltung_settings_export_conject($this);
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Conject';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'conject' );
		$content['onclick'] = false;
		if($this->action === 'conject') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Speedikon
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function speedikon($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.export.speedikon.class.php');
			$controller = new bestandsverwaltung_settings_export_speedikon($this);
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Speedikon';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'speedikon' );
		$content['onclick'] = false;
		if($this->action === 'speedikon') {
			$content['active']  = true;
		}
		return $content;
	}

}
?>
