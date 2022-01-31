<?php
/**
 * smtp_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class smtp_settings
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'smtp_action';
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
		"lang_smtp" => "Smtp",
		"smtp" => array(
			"agent" => "Agent",
			"off" => "OFF",
			"sendmail" => "PHP",
			"smtp" => "SMTP",
			"port" => "Port",
			"user" => "User",
			"pass" => "Pass",
			"url" => "Server",
			"from" => "From"
		),
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
		$this->settings = PROFILESDIR.'settings.ini';
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
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		switch( $this->action ) {
			case '':
			case 'update':
				return $this->update();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update() {
		$form = $this->get_form();
		if(!$form->get_errors() && $this->response->submit()) {
			$errors  = array();
			$request = $form->get_request(null, true);
			if($request['smtp']['agent'] === 'mail') {
				if($request['smtp']['from'] === '') {
					$error = sprintf($this->response->html->lang['form']['error_required'], $this->lang['smtp']['from']);
					$form->set_error('smtp[from]', $error);
					$errors[] = $error;
				}
			}
			else if($request['smtp']['agent'] === 'sock') {
				if($request['smtp']['from'] === '') {
					$error = sprintf($this->response->html->lang['form']['error_required'], $this->lang['smtp']['from']);
					$form->set_error('smtp[from]', $error);
					$errors[] = $error;
				}
				if($request['smtp']['port'] === '') {
					$error = sprintf($this->response->html->lang['form']['error_required'], $this->lang['smtp']['port']);
					$form->set_error('smtp[port]', $error);
					$errors[] = $error;
				}
				if($request['smtp']['user'] === '') {
					$error = sprintf($this->response->html->lang['form']['error_required'], $this->lang['smtp']['user']);
					$form->set_error('smtp[user]', $error);
					$errors[] = $error;
				}
				if($request['smtp']['url'] === '') {
					$error = sprintf($this->response->html->lang['form']['error_required'], $this->lang['smtp']['url']);
					$form->set_error('smtp[url]', $error);
					$errors[] = $error;
				}
			}

			if(count($errors) < 1) {
				$old = $this->file->get_ini( $this->settings );
				if(is_array($old)) {
					unset($old['smtp']);
					$request = array_merge($old,$request);
				}		
				$error = $this->file->make_ini( $this->settings, $request );
				if($error !== '') {
					$errors[] = $error;
				}
			}

			if(count($errors) < 1) {
				$msg = $this->lang['update_sucess'];
				$this->response->redirect($this->response->get_url($this->actions_name, 'update', $this->message_param, $msg));
			} else {
				$_REQUEST[$this->message_param] = implode('<br>', $errors);
			}
		} 
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
		}
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'smtp.settings.html');
		$t->add($this->lang['lang_smtp'], 'lang_smtp');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Get Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form() {
		$ini  = $this->file->get_ini( $this->settings );
		$form = $this->response->get_form($this->actions_name, 'update');

		// SMTP
		$smtp[] = array('off', $this->lang['smtp']['off']);
		$smtp[] = array('mail', $this->lang['smtp']['sendmail']);
		$smtp[] = array('sock', $this->lang['smtp']['smtp']);

		$d['smtp_agent']['label']                     = $this->lang['smtp']['agent'];
		$d['smtp_agent']['required']                  = true;
		$d['smtp_agent']['object']['type']            = 'htmlobject_select';
		$d['smtp_agent']['object']['attrib']['name']  = 'smtp[agent]';
		$d['smtp_agent']['object']['attrib']['index'] = array(0,1);
		$d['smtp_agent']['object']['attrib']['options'] = $smtp;
		if(isset($ini['smtp']['agent'])) {
			$d['smtp_agent']['object']['attrib']['selected'] = array($ini['smtp']['agent']);
		}

		$d['smtp_port']['label']                     = $this->lang['smtp']['port'];
		$d['smtp_port']['object']['type']            = 'htmlobject_input';
		$d['smtp_port']['object']['attrib']['name']  = 'smtp[port]';
		$d['smtp_port']['object']['attrib']['type']  = 'text';
		$d['smtp_port']['object']['attrib']['size']  = 5;
		if(isset($ini['smtp']['port'])) {
			$d['smtp_port']['object']['attrib']['value'] = $ini['smtp']['port'];
		}

		$d['smtp_user']['label']                     = $this->lang['smtp']['user'];
		$d['smtp_user']['object']['type']            = 'htmlobject_input';
		$d['smtp_user']['object']['attrib']['name']  = 'smtp[user]';
		$d['smtp_user']['object']['attrib']['type']  = 'text';
		if(isset($ini['smtp']['user'])) {
			$d['smtp_user']['object']['attrib']['value'] = $ini['smtp']['user'];
		}
		$d['smtp_pass']['label']                     = $this->lang['smtp']['pass'];
		$d['smtp_pass']['object']['type']            = 'htmlobject_input';
		$d['smtp_pass']['object']['attrib']['name']  = 'smtp[pass]';
		$d['smtp_pass']['object']['attrib']['type']  = 'text';
		if(isset($ini['smtp']['pass'])) {
			$d['smtp_pass']['object']['attrib']['value'] = $ini['smtp']['pass'];
		}

		$d['smtp_url']['label']                     = $this->lang['smtp']['url'];
		$d['smtp_url']['object']['type']            = 'htmlobject_input';
		$d['smtp_url']['object']['attrib']['name']  = 'smtp[url]';
		$d['smtp_url']['object']['attrib']['type']  = 'text';
		if(isset($ini['smtp']['url'])) {
			$d['smtp_url']['object']['attrib']['value'] = $ini['smtp']['url'];
		}

		$d['smtp_from']['label']                     = $this->lang['smtp']['from'];
		$d['smtp_from']['object']['type']            = 'htmlobject_input';
		$d['smtp_from']['object']['attrib']['name']  = 'smtp[from]';
		$d['smtp_from']['object']['attrib']['type']  = 'text';
		if(isset($ini['smtp']['from'])) {
			$d['smtp_from']['object']['attrib']['value'] = $ini['smtp']['from'];
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
