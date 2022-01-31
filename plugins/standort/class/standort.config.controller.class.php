<?php
/**
 * standort_config_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_action';
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'standort_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
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
	'config' => 'Settings',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct( $file, $response, $db, $user ) {
		$this->classdir = CLASSDIR.'/plugins/standort/class/';
		$this->file     = $file;
		$this->settings = PROFILESDIR.'standort.ini';
		$this->ini      = $this->file->get_ini($this->settings);
		$this->response = $response;
		$this->langdir  = CLASSDIR.'/plugins/standort/lang';
		$this->tpldir   = CLASSDIR.'/plugins/standort/templates';
		$this->user     = $user;
		$this->db = $db;

		if(isset($this->ini['query']['db'])) {
			$this->db->db = $this->ini['query']['db'];
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

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'settings':
				$content[] = $this->settings(true);
			#	$content[] = $this->database();
			break;
			#case 'database':
			#	$content[] = $this->settings();
			#	$content[] = $this->database(true);
			#break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function settings($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'standort.config.settings.class.php');
			$controller = new standort_config_settings($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = 'Settings';
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

			if(isset($this->ini['query']['db'])) {
				require_once(CLASSDIR.'lib/db/query.controller.class.php');
				$controller = new query_controller($this->file, $this->response, $this->db, $this->user);
				$controller->tpldir = CLASSDIR.'lib/db/templates/';
				$controller->lang  = $this->lang;
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
		$content['request'] = $this->response->get_array($this->actions_name, 'database' );
		$content['onclick'] = false;
		if($this->action === 'database'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
