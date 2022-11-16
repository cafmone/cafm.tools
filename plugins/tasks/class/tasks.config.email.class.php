<?php
/**
 * user_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_config_email
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
var $actions_name = 'email_action';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'label_default' => 'Default',
	'label_allow_custom' => 'Allow custom',
	'label_require_mail_to' => 'Require mail to',
	'default_active' => 'Use Email',
	'default_active_explanation' => 'Open Email dialog after changes',
	'default_auto' => 'Autosend',
	'default_auto_explanation' => 'Send Email automatically without dialog',
	'default_from' => 'from',
	'default_from_explanation' => 'use {login} for users email',
	'default_subject' => 'subject',
	'default_subject_explanation' => 'use %s as placeholder for id',
	'default_body' => 'body',
	'custom_from' => 'from',
	'custom_to' => 'to',
	'custom_subject' => 'subject',
	'custom_body' => 'body',
	'mail_to_supporter' => 'supporter',
	'mail_to_group' => 'group',
	'mail_to_reporter' => 'reporter',
	'action_edit' => 'edit',
	'lang_replacements' => 'Replacements',
	'saved' => 'Email settings have been saved',
	'saved_template' => 'Email template has been saved'
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
		$this->settings   = $controller->profilesdir.'/tasks.ini';
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->lang       = $controller->user->translate($this->lang, CLASSDIR.'plugins/tasks/lang/', 'tasks.config.email.ini');
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
					'label_default' => $this->lang['label_default'],
					'label_allow_custom' => $this->lang['label_allow_custom'],
					'label_require_mail_to' => $this->lang['label_require_mail_to'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'tasks.config.email.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			case 'template':
				$template = $this->response->html->request()->get('template');
				$params = explode('_', $template);

				$response = $this->template($template);
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg, 'template');
				}
				$title = $this->lang['default_body'];
				$vars = array(
					'lang_template' => $title,
					'lang_replacements' => $this->lang['lang_replacements'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'tasks.config.email.template.html');
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
		if(!$form->get_errors() && $response->submit()) {
			$ini = $form->get_request();
			if(!isset($ini) || $ini === '') {
				$ini = array();
			}
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['email']);
				$ini = array_merge($old,$ini);
			}	
			$error = $this->file->make_ini($this->settings, $ini, null );
			if($error === '') {
				$response->msg = $this->lang['saved'];
			} else {
				$response->error = $error;
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Template
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function template( $template ) {
		$response = $this->get_response('template');
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$template = $form->get_request('template');
			$template = wordwrap($template, 72, "\n", true);
			$error = $this->file->mkfile($this->controller->profilesdir.'tasks/templates/tasks.mail.tpl', $template, 'w+', true );
			if($error === '') {
				$response->msg = $this->lang['saved_template'];
			} else {
				$response->error = $error;
			}
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
			$ini = $this->file->get_ini( $this->settings );


			$d['active']['label']                     = $this->lang['default_active'];
			$d['active']['object']['type']            = 'htmlobject_input';
			$d['active']['object']['attrib']['name']  = 'email[active]';
			$d['active']['object']['attrib']['type']  = 'checkbox';
			$d['active']['object']['attrib']['title'] = $this->lang['default_active_explanation'];
			if(isset($ini['email']['active'])) {
				$d['active']['object']['attrib']['checked'] = true;
			}

			$d['auto']['label']                     = $this->lang['default_auto'];
			$d['auto']['object']['type']            = 'htmlobject_input';
			$d['auto']['object']['attrib']['name']  = 'email[auto]';
			$d['auto']['object']['attrib']['type']  = 'checkbox';
			$d['auto']['object']['attrib']['title'] = $this->lang['default_auto_explanation'];
			if(isset($ini['email']['auto'])) {
				$d['auto']['object']['attrib']['checked'] = true;
			}

			$d['from']['label']                     = $this->lang['default_from'];
			$d['from']['object']['type']            = 'htmlobject_input';
			$d['from']['object']['attrib']['name']  = 'email[from]';
			$d['from']['object']['attrib']['type']  = 'text';
			$d['from']['object']['attrib']['title'] = $this->lang['default_from_explanation'];
			if(isset($ini['email']['from'])) {
				$d['from']['object']['attrib']['value'] = $ini['email']['from'];
			}

			$d['subject']['label']                     = $this->lang['default_subject'];
			$d['subject']['object']['type']            = 'htmlobject_input';
			$d['subject']['object']['attrib']['name']  = 'email[subject]';
			$d['subject']['object']['attrib']['type']  = 'text';
			$d['subject']['object']['attrib']['title'] = $this->lang['default_subject_explanation'];
			if(isset($ini['email']['subject'])) {
				$d['subject']['object']['attrib']['value'] = $ini['email']['subject'];
			}

			$p = $this->response->get_string($this->actions_name, 'template', '?', true );
			$a = $this->response->html->a();
			$a->href  = $this->response->html->thisfile.$p;
			$a->label = $this->lang['action_edit'];
			$a->title = $this->lang['action_edit'];
			$a->css   = 'edit';
			$a->name  = '';
			$a->style = 'display: block; margin-top: 7px;';
			$d['body']['label']  = $this->lang['default_body'];
			$d['body']['object'] = $a;

			$elements = array(
				'supporter',
				'group',
				'reporter'
			);
			foreach ($elements as $v) {
				$d['to_'.$v]['label']                     = $this->lang['mail_to_'.$v];
				$d['to_'.$v]['object']['type']            = 'htmlobject_input';
				$d['to_'.$v]['object']['attrib']['name']  = 'email[to_'.$v.']';
				$d['to_'.$v]['object']['attrib']['type']  = 'checkbox';
				$d['to_'.$v]['object']['attrib']['value'] = '';
				if(isset($ini['email']['to_'.$v])) {
					$d['to_'.$v]['object']['attrib']['checked'] = true;		
				}

			}
			$elements = array(
				'from',
				'to',
				'subject',
				'body'
			);
			foreach ($elements as $v) {
				$d['custom_'.$v]['label']                 = $this->lang['custom_'.$v];
				$d['custom_'.$v]['object']['type']            = 'htmlobject_input';
				$d['custom_'.$v]['object']['attrib']['name']  = 'email[custom_'.$v.']';
				$d['custom_'.$v]['object']['attrib']['type']  = 'checkbox';
				$d['custom_'.$v]['object']['attrib']['value'] = '';
				if(isset($ini['email']['custom_'.$v])) {
					$d['custom_'.$v]['object']['attrib']['checked'] = true;
				}

			}
			$form->add($d);
		}
		else if( $mode === 'template' ) {
			$d['template']['object']['type']            = 'htmlobject_textarea';
			$d['template']['object']['attrib']['name']  = 'template';
			$d['template']['object']['attrib']['cols']  = 72;
			$d['template']['object']['attrib']['rows']  = 22;
			if(file_exists($this->controller->profilesdir.'/tasks/templates/tasks.mail.tpl')) {
				$d['template']['object']['attrib']['value'] = $this->file->get_contents($this->controller->profilesdir.'/tasks/templates/tasks.mail.tpl');
			}
			$form->add($d);
		}
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
