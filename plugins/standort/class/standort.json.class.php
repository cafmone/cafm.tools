<?php
/**
 * standort_json
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_json
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
	function __construct($controller) {
		$this->controller = $controller;
		$this->response   = $controller->response;
		$this->user       = $controller->user;
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->baseurl    = $GLOBALS['settings']['config']['baseurl'];
		$this->settings   = $this->file->get_ini(PROFILESDIR.'standort.ini');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {

		// demo switch
		$user = $this->user->get();
		if(isset($user['group']) && in_array($this->demogroup,$user['group'])) {
			$this->isdemo = true;
		}

		$action = $this->response->html->request()->get($this->actions_name);
		if($action !== '') {
			$this->response->add($this->actions_name, $action);
		}
		switch( $action ) {
			default:
			case '':
			case 'tree':
				$this->tree(true);
			break;
		}
	}

	//--------------------------------------------
	/**
	 * tree
	 *
	 * @access public
	 */
	//--------------------------------------------
	function tree( $visible = false ) {
		if($visible === true) {
			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: text/javascript; charset=utf-8");
			header('Content-disposition: '.$this->disposition.'; filename=todos.tables.js');
			flush();
			echo json_encode($tables);
			exit(0);
		}
	}


}
?>
