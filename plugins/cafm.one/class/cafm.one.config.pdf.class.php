<?php
/**
 * cafm_one_config_pdf
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_config_pdf
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* path to ini file
* @access public
* @var string
*/
var $settings;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		"url" => "Url",
		"user" => "User",
		"pass" => "Pass",
		"update_sucess" => "Settings updated successfully",
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $file, $response ) {
		$this->file     = $file;
		$this->response = $response;
		$this->settings = PROFILESDIR.'cafm.one.ini';
		$this->datadir =  PROFILESDIR.'cafm.one/templates/';

		require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
		$this->taetigkeiten = new cafm_one($this->file, $this->response);
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
	function action($action = null) {

		if($this->file->exists($this->datadir)){
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.upload.class.php');
			$response = $this->response->response();
			$response->id = 'upload';
			$commander = new phpcommander_upload($this->datadir, $response, $this->file);
			$commander->actions_name = 'update_upload';
			$commander->message_param = 'upload_msg';
			$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
			$commander->allow_replace = true;
			$commander->allow_create = true;
			$commander->accept = '.pdf';
			$commander->filename = 'Checklist.pdf';
			$upload = $commander->get_template();
			if(isset($_REQUEST[$commander->message_param])) {
				$msg = $_REQUEST[$commander->message_param];
				unset($_REQUEST[$commander->message_param]);
				$this->response->redirect($this->response->get_url($this->actions_name, 'pdf', $this->message_param, $msg));
			}
		}

		$down = $this->response->html->a();
		$down->href = $this->response->get_url($this->actions_name, 'download');
		$down->label = $this->lang['download_template'];

		$t = $this->response->html->template($this->tpldir.'cafm.one.config.pdf.html');
		$t->add($down, 'download');
		$t->add($upload, 'upload');
		$t->add($this->lang['upload_template'], 'label');

		return $t;
	}

}
?>
