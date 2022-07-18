<?php
/**
 * standort_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_controller
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
var $message_param = 'standort_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_ident';
/**
* path to tpldir
* @access public
* @var string
*/
var $tpldir;

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
	function __construct($file, $response, $db, $user) {
		$this->file = $file;
		$this->response = $response;
		$this->db = $db;
		$this->user = $user;
		$this->profilesdir = PROFILESDIR;
		$this->settings = $this->file->get_ini($this->profilesdir.'/standort.ini', true, true);
		$this->classdir = CLASSDIR.'plugins/standort/class/';
		$this->db = $db;
		if(isset($this->settings['query']['db'])) {
			$this->db->db = $this->settings['query']['db'];
		}
	}

	//--------------------------------------------
	/**
	 * Json
	 *
	 * @access public
	 * @return mixed
	 */
	//--------------------------------------------
	function json() {
		require_once($this->classdir.'standort.json.class.php');
		$controller = new standort_json($this);
		$controller->tpldir = $this->tpldir;
		$controller->actions_name = $this->actions_name;
		$data = $controller->action();
		return $data;
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

		if($this->response->cancel()) {
			if($this->action === 'process') {
				$this->action = 'select';
			}
			unset($_REQUEST['dbaction']);
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				case '':
				default:
				case 'inventory':
					$data = $this->inventory(true);
				break;
				case 'settings':
					$data = $this->settings(true);
				break;
				case 'download':
					$data = $this->__download();
				break;
			}
		}

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('standort_main');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = true;
		$tab->add(array($content));
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Inventory
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function inventory( $visible = false ) {
		if($visible === true) {

/*
			require_once($this->classdir.'standort.inventory.controller.class.php');
			$controller = new standort_inventory_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
			return $data;
*/
		}
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function settings( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'standort.settings.controller.class.php');
			$controller = new standort_settings_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function __download() {
		require_once(CLASSDIR.'/lib/file/file.mime.class.php');
		$path = $this->response->html->request()->get('path');
		$path = $this->profilesdir.'/webdav/standort/'.$path;
		$file = $this->file->get_fileinfo($path);
		$mime = detect_mime($file['path']);

		$inline = $this->response->html->request()->get('inline');

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		if( $inline === 'true' && substr($mime, 0, 5) === 'image') {
			header("Content-disposition: inline; filename=".$file['name']);
		} else {
			header("Content-disposition: attachment; filename=".$file['name']);
		}
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
	}

}
?>
