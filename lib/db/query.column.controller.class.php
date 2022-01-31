<?php
/**
 * query_column_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_column_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_column_action';
/**
* name identifier
* @access public
* @var string
*/
var $identifier_name = 'query_column_ident';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'query_column_msg';

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

		$this->response->add($controller->actions_name, 'column');
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
			$this->action = 'edit';
		}
		#if($this->response->cancel()) {
		#	$this->action = 'edit';
		#}
		if(isset($action)) {
			$this->action = $action;
		}
	
		$tabs = array();
		switch( $this->action ) {
			case '':
			default:
			case 'edit':
				$content[] = $this->edit(true);
				$content[] = $this->add();
				$content[] = $this->delete();
			break;
			case 'add':
				$content[] = $this->edit();
				$content[] = $this->add(true);
				$content[] = $this->delete();
			break;
			case 'delete':
				$content[] = $this->edit();
				$content[] = $this->add();
				$content[] = $this->delete(true);
			break;
		}

		$tab = $this->response->html->tabmenu('query_column_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Add column
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function add($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.column.insert.class.php');
			$controller = new query_column_insert($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'insert';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Edit column
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function edit($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.column.edit.class.php');
			$controller = new query_column_edit($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'edit';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove column
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function delete($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.column.delete.class.php');
			$controller = new query_column_delete($this);
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

}
?>
