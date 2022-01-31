<?php
/**
 * standort_settings_import_step1
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_import_step1
{
/**
* translation
* @access public
* @var string
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller  = $controller;
		$this->user        = $controller->user->get();
		$this->db          = $controller->db;
		$this->file        = $controller->file;
		$this->response    = $controller->response;
		$this->profilesdir = $controller->profilesdir;

		$this->datadir     = $this->controller->datadir;
		$this->path        = $this->controller->path;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {

		$response = $this->get_response();

		$upload   = '';
		if(!isset($response->error)) {
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.upload.class.php');
			$dres = $this->response->response();
			$dres->id = 'upload_standort';
			$commander = new phpcommander_upload($this->datadir, $dres, $this->file);
			$commander->actions_name = 'update_upload';
			$commander->message_param = 'upload_msg';
			$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
			$commander->allow_replace = true;
			$commander->allow_create = true;
			$commander->accept = '.xlsx';
			$commander->filename = 'standort.xlsx';
			$upload = $commander->get_template();
			if(isset($_REQUEST[$commander->message_param])) {
				$msg = $_REQUEST[$commander->message_param];
				unset($_REQUEST[$commander->message_param]);
				$this->response->redirect($this->response->get_url($this->actions_name, 'step1', $this->message_param, $msg));
			}
		} 
		else if(isset($response->error)) {
			$_REQUEST[$this->message_param]['error'] = $response->error;
		} 

		$t = $this->response->html->template($this->tpldir.'standort.settings.import.step1.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);
		$t->add($upload, 'upload');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'step1', false);

		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}


}
?>
