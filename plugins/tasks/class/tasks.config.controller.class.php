<?php
/**
 * tasks_config_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tasks_config_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'tasks_config_msg';

var $prefix_tab = 'tasks_config_tab';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	"tab_settings" => "Settings",
	"tab_email" => "Email",
	"tab_form" => "Form"
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->db          = $db;
		$this->file        = $file;
		$this->response    = $response;
		$this->user        = $user;
		$this->classdir    = CLASSDIR.'plugins/tasks/class/';
		$this->profilesdir = PROFILESDIR;
		$this->settings = $this->file->get_ini($this->profilesdir.'/tasks.ini');
		$this->db = $db;
		if(isset($this->settings['settings']['db'])) {
			$this->db->db = $this->settings['settings']['db'];
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
			$this->action = 'settings';
		}


		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		$loaders = array();
		switch( $this->action ) {
			case '':
			default:
			case 'settings':
				$content[] = $this->settings(true);
				$content[] = $this->form();
				$content[] = $this->email();
				$content[] = $this->database();
			break;
			case 'form':
				$content[] = $this->settings();
				$content[] = $this->form(true);
				$content[] = $this->email();
				$content[] = $this->database();
			break;
			case 'email':
				$content[] = $this->settings();
				$content[] = $this->form();
				$content[] = $this->email(true);
				$content[] = $this->database();
			break;
			case 'database':
				$content[] = $this->settings();
				$content[] = $this->form();
				$content[] = $this->email();
				$content[] = $this->database(true);
			break;
		};
		if(!isset($this->db->type)) {
			$content['label']   = $this->lang['tab_settings'];
			$content['value']   = '<div style="padding:50px;"><b>Error:</b> Check your db settings</div>';
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'settings' );
			$content['onclick'] = false;
			$content = array($content);
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		#$tab->floatbreaker = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @param bool $visible
	 * @return array
	 */
	//--------------------------------------------
	function settings( $visible = false ) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'tasks.config.settings.class.php');
			$controller = new tasks_config_settings($this);
			$controller->actions_name = 'settings_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_settings'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'settings' );
		$content['onclick'] = false;
		if($this->action === 'settings'){
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
	function database($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once(CLASSDIR.'lib/db/query.controller.class.php');
			$controller = new query_controller($this->file, $this->response, $this->db, $this->user);
			$controller->tpldir = CLASSDIR.'lib/db/templates/';
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Database';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'database' );
		$content['onclick'] = false;
		if($this->action === 'database'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Email
	 *
	 * @access public
	 * @param bool $visible
	 * @return array
	 */
	//--------------------------------------------
	function email( $visible = false ) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'tasks.config.email.class.php');
			$controller = new tasks_config_email($this);
			$controller->actions_name = 'tasks_email_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_email'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'email' );
		$content['onclick'] = false;
		if($this->action === 'email'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Form
	 *
	 * @access public
	 * @param bool $visible
	 * @return array
	 */
	//--------------------------------------------
	function form( $visible = false ) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'tasks.config.form.class.php');
			$controller = new tasks_config_form($this);
			$controller->actions_name = 'form_action';
			$controller->message_param = 'form_msg';
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

}
?>
