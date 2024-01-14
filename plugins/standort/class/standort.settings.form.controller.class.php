<?php
/**
 * standort_settings_form_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
 require_once(CLASSDIR.'lib/formbuilder/formbuilder.controller.class.php');
 

class standort_settings_form_controller extends formbuilder_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_form_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_form_msg';

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
		$this->classdir = CLASSDIR.'lib/formbuilder/';
		$this->tpldir = CLASSDIR.'lib/formbuilder/templates/';
		if(isset($this->settings['query']['prefix'])) {
			$this->prefix = $this->settings['query']['prefix'];
		} else {
			$this->prefix = '';
		}
		$this->table_prefix = $this->prefix.'';
		$this->table_bezeichner = $this->prefix.'bezeichner';
	}

}
?>
