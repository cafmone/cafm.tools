<?php
/**
 * filebrowser_api
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */


class filebrowser_api
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'filebrowser_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'filebrowser_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'filebrowser_ident';
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
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->response   = $response;
		$this->user       = $user;
		$this->db         = $db;
		$this->file       = $file;
		$this->baseurl    = $GLOBALS['settings']['config']['baseurl'];

		require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
		$path = CLASSDIR.'/lib/phpcommander';
		$commander = new phpcommander($path, $this->response->html, $this->file, 'pc', $this->response->params);
		$commander->actions_name  = 'folders_action';
		$commander->message_param = $this->message_param;
		$commander->upload_multiple = true;
		$this->commander = $commander;
		$this->ini = $this->file->get_ini(PROFILESDIR.'filebrowser.ini');

		// handle folders
		if(!isset($this->ini['folders'])) {
			$this->ini['folders'] = array();
		}
		$this->ini['folders']['PROFILES'] = PROFILESDIR;
		$this->ini['folders']['HTTPDOCS'] = $GLOBALS['settings']['config']['basedir'];
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		#if(!isset($this->basedir)) {
		#	echo json_encode(array('file' => 'Error: Check settings basedir'));
		#} else {
			$action = $this->response->html->request()->get($this->actions_name);
			if($action !== '') {
				$this->response->add($this->actions_name, $action);
			}
			switch( $action ) {
				case 'md5':
					$this->md5(true);
				break;
			}
		#}
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 */
	//--------------------------------------------
	function md5($visible = false) {
		if($visible === true) {

			$result = array();
			$file = $this->response->html->request()->get('file');

			$controller = $this->commander->controller($this->ini['folders']);

			// handle path
			$folders = $controller->get_root();
			$dir = $controller->dir;
			$root = $controller->root;
			
			$path = '';
			if(isset($folders) && is_array($folders)) {
				$path  = $folders[$root]['path'];
				$path .= ''.$dir.'/';
			}

			if($file !== '' && $this->file->exists($path.$file)) {
				$result['file'] = $file;
				$result['md5']  = md5_file($path.$file);
			} else {
				$result['file'] = $file;
				$result['md5']  = 'not found';
			}
			echo json_encode($result);
		}
	}

}
?>
