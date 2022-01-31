<?php
/**
 * query_table_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_table_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_table_action';
/**
* name identifier
* @access public
* @var string
*/
var $identifier_name = 'query_table_ident';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'query_table_msg';

var $tpldir;

var $lang = array(
	'label_bestand' => 'bestand #%s',
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
		#$this->settings = $this->file->get_ini(PROFILESDIR.'/bestand.ini');
		#$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestand/lang/', 'bestand.ini');
		$this->classdir = $controller->classdir;
		$this->dbtable = $controller->dbtable;

		$this->response->add($controller->actions_name, 'table');
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
		$ac = $this->response->html->request()->get($this->actions_name);
		if(is_array($ac)) {
			$this->action = key($ac);
		}
		else if($ac !== '') {
			$this->action = $ac;
		}
		else if($ac === '') {
			$this->action = 'backup';
		}
		if($this->response->cancel()) {
			$this->action = 'backup';
		}
		if(isset($action)) {
			$this->action = $action;
		}
	
		$tabs = array();
		switch( $this->action ) {
			case '':
			default:
			case 'insert':
				$content[] = $this->insert(true);
				$content[] = $this->copy();
				$content[] = $this->delete();
			break;
			case 'copy':
				$content[] = $this->insert();
				$content[] = $this->copy(true);
				$content[] = $this->delete();
			break;
			case 'delete':
				$content[] = $this->insert();
				$content[] = $this->copy();
				$content[] = $this->delete(true);
			break;
		}

		$tab = $this->response->html->tabmenu('query_table_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * insert table
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.table.insert.class.php');
			$controller = new query_table_insert($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'insert';
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
	 * Remove Table
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function delete($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.table.delete.class.php');
			$controller = new query_table_delete($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'delete';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'delete' );
		$content['onclick'] = false;
		if($this->action === 'delete'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Copy Table
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function copy($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.table.copy.class.php');
			$controller = new query_table_copy($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'copy';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'copy' );
		$content['onclick'] = false;
		if($this->action === 'copy'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
