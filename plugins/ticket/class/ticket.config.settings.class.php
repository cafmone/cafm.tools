<?php
/**
 * user_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_config_settings
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'admin_id';
/**
* name of message param
* @access public
* @var string
*/
var $message_param = 'Msg';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_action';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'label_form' => 'Form',
	'label_anonymous' => 'Anonymous',
	'label_settings' => 'Settings',
	'label_attachments' => 'Attachments',
	'group' => 'Group',
	'supporter' => 'Supporter',
	'reporter' => 'Reporter',
	'email' => 'Email',
	'salutation' => 'Salutation',
	'forename' => 'Forename',
	'lastname' => 'Lastname',
	'firm' => 'Firm',
	'office' => 'Office',
	'phone' => 'Phone',
	'path' => 'Path',
	'required' => 'required',
	'saved' => 'Ticket Settings have been saved',
	'error_folder_not_found' => 'Could not find folder %s'
);
/**
* path to templates dir
* @access public
* @var string
*/
var $tpldir = '';

	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array|string $root
	 * @param phpcommander $phpcommander
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller = $controller;
		$this->settings   = $controller->profilesdir.'/ticket.ini';
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->user       = $controller->user;
		$this->lang       = $this->user->translate($this->lang, CLASSDIR.'plugins/ticket/lang/', 'ticket.config.settings.ini');
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
		if($this->response->cancel()) {
			$this->action = 'update';
		}

		$content   = array();
		switch( $this->action ) {
			case '':
			case 'update':
				$response = $this->update();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'label_settings' => $this->lang['label_settings'],
					'label_form' => $this->lang['label_form'],
					'label_anonymous' => $this->lang['label_anonymous'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'ticket.config.settings.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response('update');
		$form     = $response->form;
		$ini      = $form->get_request();

		if(!$form->get_errors() && $response->submit()) {
			if(!isset($ini) || $ini === '') {
				$ini = array();
			}
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['form']);
				unset($old['required']);
				unset($old['reporter']);
				unset($old['settings']);
				unset($old['labels']);
				$ini = array_merge($old,$ini);
			}	
			$error = $this->file->make_ini($this->settings, $ini, null );
			if($error === '') {
				$response->msg = $this->lang['saved'];
			} else {
				$response->error = $error;
			}
		}			
		else if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response( $mode ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);

		if( $mode === 'update' ) {
			// FORM		
			$ini = $this->file->get_ini( $this->settings );

			$d['db']['label']                     = 'DB';
			$d['db']['object']['type']            = 'htmlobject_input';
			$d['db']['object']['attrib']['name']  = 'settings[db]';
			$d['db']['object']['attrib']['type']  = 'text';
			if(isset($ini['settings']['db'])) {
				$d['db']['object']['attrib']['value'] = $ini['settings']['db'];
			}

			// ATTACHMENTS
			$d['attachments']['label']                     = $this->lang['label_attachments'];
			$d['attachments']['object']['type']            = 'htmlobject_input';
			$d['attachments']['object']['attrib']['type']  = 'checkbox';
			$d['attachments']['object']['attrib']['name']  = 'settings[attachment]';
			if(isset($ini['settings']['attachment'])) {
				$d['attachments']['object']['attrib']['checked'] = true;
			}

			// USER
			$elements = array(
				'group',
				'supporter',
				'reporter'
			);
			foreach ($elements as $v) {
				$d['form_'.$v]['label']                     = $this->lang[$v];
				$d['form_'.$v]['css']                       = 'autosize float-right';
				$d['form_'.$v]['object']['type']            = 'htmlobject_input';
				$d['form_'.$v]['object']['attrib']['name']  = 'form['.$v.']';
				$d['form_'.$v]['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v]['object']['attrib']['value'] = '';
				if(isset($ini['form'][$v])) {
					$d['form_'.$v]['object']['attrib']['checked'] = true;
				}
				$d['form_'.$v.'_required']['label']                     = $this->lang['required'];
				$d['form_'.$v.'_required']['css']                       = 'autosize inverted checkbox';
				$d['form_'.$v.'_required']['object']['type']            = 'htmlobject_input';
				$d['form_'.$v.'_required']['object']['attrib']['name']  = 'required['.$v.']';
				$d['form_'.$v.'_required']['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v.'_required']['object']['attrib']['value'] = '';
				if(isset($ini['required'][$v])) {
					$d['form_'.$v.'_required']['object']['attrib']['checked'] = true;
				}
			}

			// CUSTOM
			$elements = array(
				'flag_01',
				'flag_02',
				'flag_03',
				'flag_04',
				'flag_05',
				'flag_06',
				'flag_07',
				'flag_08',
				'flag_09',
				'flag_10'
			);
			foreach ($elements as $v) {
				$d['form_'.$v.'_label']['required'] = true;
				$d['form_'.$v.'_label']['object']['type']                = 'htmlobject_input';
				$d['form_'.$v.'_label']['object']['attrib']['name']      = 'labels['.$v.']';
				$d['form_'.$v.'_label']['object']['attrib']['type']      = 'text';
				$d['form_'.$v.'_label']['object']['attrib']['css']       = 'float-right';
				$d['form_'.$v.'_label']['object']['attrib']['style']     = 'width: auto;margin: 0 0 0 0;';
				$d['form_'.$v.'_label']['object']['attrib']['maxlength'] = '30';
				$d['form_'.$v.'_label']['object']['attrib']['title']     = 'db field '.$v;
				if(isset($ini['labels'][$v])) {
					$d['form_'.$v.'_label']['object']['attrib']['value'] = $ini['labels'][$v];
				}
				$d['form_'.$v.'_box']['object']['type']            = 'htmlobject_input';
				$d['form_'.$v.'_box']['object']['attrib']['name']  = 'form['.$v.']';
				$d['form_'.$v.'_box']['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v.'_box']['object']['attrib']['value'] = '';
				$d['form_'.$v.'_box']['object']['attrib']['css']   = 'float-right';
				$d['form_'.$v.'_box']['object']['attrib']['style'] = 'margin: 0 0 0 15px;';
				if(isset($ini['form'][$v])) {
					$d['form_'.$v.'_box']['object']['attrib']['checked'] = true;
				}
				$d['form_'.$v.'_required']['label']                     = $this->lang['required'];
				$d['form_'.$v.'_required']['css']                       = 'autosize inverted checkbox';
				$d['form_'.$v.'_required']['object']['type']            = 'htmlobject_input';
				$d['form_'.$v.'_required']['object']['attrib']['name']  = 'required['.$v.']';
				$d['form_'.$v.'_required']['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v.'_required']['object']['attrib']['value'] = '';
				if(isset($ini['required'][$v])) {
					$d['form_'.$v.'_required']['object']['attrib']['checked'] = true;
				}
			}

/*
			// REPORTER
			$elements = array(
				'email',
				'salutation',
				'forename',
				'lastname',
				'firm',
				'office',
				'phone'
			);
			foreach ($elements as $v) {
				$d['reporter_'.$v]['label']                     = $this->lang[$v];
				$d['reporter_'.$v]['css']                       = 'autosize float-right';
				$d['reporter_'.$v]['object']['type']            = 'htmlobject_input';
				$d['reporter_'.$v]['object']['attrib']['name']  = 'reporter[reporter_'.$v.']';
				$d['reporter_'.$v]['object']['attrib']['type']  = 'checkbox';
				$d['reporter_'.$v]['object']['attrib']['value'] = '';
				if(isset($ini['reporter']['reporter_'.$v])) {
					$d['reporter_'.$v]['object']['attrib']['checked'] = true;
				}
				$d['reporter_'.$v.'_required']['label']                     = $this->lang['required'];
				$d['reporter_'.$v.'_required']['css']                       ='autosize inverted checkbox';
				$d['reporter_'.$v.'_required']['object']['type']            = 'htmlobject_input';
				$d['reporter_'.$v.'_required']['object']['attrib']['name']  = 'required[reporter_'.$v.']';
				$d['reporter_'.$v.'_required']['object']['attrib']['type']  = 'checkbox';
				$d['reporter_'.$v.'_required']['object']['attrib']['value'] = '';
				if(isset($ini['required']['reporter_'.$v])) {
					$d['reporter_'.$v.'_required']['object']['attrib']['checked'] = true;
				}
			}
*/
			$form->add($d);
		}
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access public
	 * @param string $msg
	 * @param string $mode
	 */
	//--------------------------------------------
	function __redirect( $msg, $mode = '' ) {
		$this->response->redirect($this->response->get_url($this->actions_name, $mode, $this->message_param, $msg));
	}

}
?>
