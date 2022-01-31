<?php
/**
 * query_column_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015 - 2018, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_export_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_export_action';
/**
* name identifier
* @access public
* @var string
*/
var $identifier_name = 'query_export_ident';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'query_export_msg';

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
		$this->classdir = $controller->classdir;
		$this->dbtable = $controller->dbtable;

		$this->response->add($controller->actions_name, 'export');
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
			case 'backup':
				$content[] = $this->backup(true);
				$content[] = $this->table();
			break;
			case 'table':
				$content[] = $this->backup();
				$content[] = $this->table(true);
			break;
		}

		$tab = $this->response->html->tabmenu('query_export_tab');
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
	function table($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.export.table.class.php');
			$controller = new query_export_table($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Table';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'table' );
		$content['onclick'] = false;
		if($this->action === 'table'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Backup
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function backup($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.export.backup.class.php');
			$controller = new query_export_backup($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Backup';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'backup' );
		$content['onclick'] = false;
		if($this->action === 'backup'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
