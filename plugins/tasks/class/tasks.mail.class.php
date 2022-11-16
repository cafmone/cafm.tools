<?php
/**
 * tasks_mail
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_mail
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tasks_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'tasks_msg';

var $tpldir;

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
		$this->file = $controller->file;
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->controller = $controller;

		$id = $this->response->html->request()->get('id');
		$this->id = $id;
		$this->response->add('id', $id);

		$forwarder = $this->response->html->request()->get('forwarder');
		$this->forwarder = $forwarder;
		$this->response->add('forwarder', $forwarder);

		$noticeid = $this->response->html->request()->get('notice_id');
		if($noticeid !== '') {
			$this->response->add('notice_id', $noticeid);
		}

		$result = $this->db->select('tasks_tasks', '*', array('id', $this->id));
		if(is_array($result)) {
			$result = array_shift($result);
			unset($result['comment']);
			unset($result['updater']);
			$this->tasks = $result;
		}
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
			$this->response->redirect(
				$this->response->get_url(
					$this->controller->actions_name, $this->forwarder, 'id', $this->id
				)
			);
		}

		// handle missing mail
		if($this->forwarder === 'insert' && 
			(
				!isset($this->tasks['group']) && 
				!isset($this->tasks['supporter'])
			)
		) {
			$msg = sprintf($this->lang['msg_saved'], $this->id);
			$this->response->redirect(
				$this->response->get_url(
					$this->controller->actions_name, 'insert', $this->message_param, $msg
				)
			);
		}
		## TODO check $this->tasks['group'] is null?
		else if($this->forwarder === 'update' && 
			(
				!isset($this->tasks['group']) && 
				!isset($this->tasks['supporter']) && 
				!isset($this->tasks['reporter']) && 
				!isset($this->tasks['reporter_email'])
			)
		) {
			$this->response->redirect(
				$this->response->get_url(
					$this->controller->actions_name, 'update', 'id', $this->id
				)
			);
		}

		$response = $this->mail();
		$t = $response->html->template($this->tpldir.'/tasks.mail.html');
		$t->add('', 'error');
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		else if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url(
					$this->controller->actions_name, $this->forwarder, 'id', $this->id
				).'&'.$this->message_param.'='.$response->msg
			);
		}
		$vars = array(
			'thisfile' => $response->html->thisfile,
			'label_mail' => $this->lang['label_mail'],
			'mail_to' => $this->lang['mail_to']
		);

		$t->add($vars);
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;

	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function mail() {
		$response = $this->get_response('update');
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			if(!isset($this->controller->settings['email']['custom_from'])) {
				$from = $form->get_static('from');
			} else {
				$from = $form->get_request('from');
			}
			if(!isset($this->controller->settings['email']['custom_subject'])) {
				$subject = $form->get_static('subject');
			} else {
				$subject = $form->get_request('subject');
			}

			if(!isset($this->controller->settings['email']['custom_body'])) {
				$body = wordwrap($form->get_static('body'), 72, "\n");
			} else {
				$body = wordwrap($form->get_request('body'), 72, "\n");
			}

			$address = $this->__getmail($form);

			// custom to
			if(isset($this->controller->settings['email']['custom_to'])) {
				$to = $form->get_request('to');
				if(isset($to) && $to !== '') {
					$address[$form->get_request('to')] = $form->get_request('to');
				}
			}

			if(is_array($address) && count($address) > 0) {
				require_once(CLASSDIR.'/lib/smtp/smtp.class.php');
				$smtp = new smtp();
				$ok   = array();
				$ko   = array();
				foreach($address as $k => $mail) {
					if($mail !== '') {
						if(isset($GLOBALS['settings']['smtp']['agent']) && $GLOBALS['settings']['smtp']['agent'] !== 'off') {
							#$smtp->debug = true;
							$smtp->url     = $GLOBALS['settings']['smtp']['url'];
							$smtp->user    = $GLOBALS['settings']['smtp']['user'];
							$smtp->pass    = $GLOBALS['settings']['smtp']['pass'];
							$smtp->port    = $GLOBALS['settings']['smtp']['port'];
							$smtp->agent   = $GLOBALS['settings']['smtp']['agent'];
							$smtp->mime    = 'text/plain';
							$smtp->subject = $subject;
							$smtp->body    = $body;
							$smtp->to      = $mail;
							$smtp->from    = $from;
							if($GLOBALS['settings']['smtp']['agent'] === 'sock') {
								$smtp->from = $GLOBALS['settings']['smtp']['from'];
							}
							$error = $smtp->connect();
							if(!isset($error)) {
								$ok[] = $k;
							} else {
								$ko[] = $k;
							}
						} else {
							$ko[] = $k.' (no email)';
						}
					}
				}
				if(!isset($error)) {

					if(isset($this->controller->settings['email']['custom_body'])) {
						$body .= "\n\n".sprintf($this->lang['mail_send_to'], implode(', ', $ok));
						if(count($ko) >= 1) {
							$body .= "\n".sprintf($this->lang['mail_not_send_to'], implode(', ', $ko));
						}
					} else {
						$body = sprintf($this->lang['mail_send_to'], implode(', ', $ok));
						if(count($ko) >= 1) {
							$body .= "\n".sprintf($this->lang['mail_not_send_to'], implode(', ', $ko));
						}
					}

					$date = time();
					$error = $this->db->insert(
						'tasks_notices',
						array(
							'tasks' => $this->id,
							'login' => 'sendmail',
							'notice' => $body,
							'private' => 0,
							'date' => $date
						)
					);
					$response->msg = $this->lang['mail_send'];
				} else {
					$response->error = $error;
				}
			} else {
				$response->error = $this->lang['error_no_mailadress'];
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get mailadresse
	 *
	 * @access private
	 * @param htmlobject_formbuilder $form
	 * @return array
	 */
	//--------------------------------------------
	function __getmail($form) {
		$mail  = array();
		$users = array();
		if($form->get_request('to_group') || $form->get_static('to_group')) {
			$result = $this->user->query->select('users2groups', array('login'), array('group', $this->tasks['group']));
			if(is_array($result)) {
				foreach($result as $v) {
					$users[] = $v['login'];
				}
			}
		}

		if($form->get_request('to_supporter') || $form->get_static('to_supporter')) {
			if($this->tasks['supporter'] !== '') {
				$users[] = $this->tasks['supporter'];
			}
		}

		if($form->get_request('to_reporter') || $form->get_static('to_reporter')) {
			if($this->tasks['reporter'] !== '') {
				$users[] = $this->tasks['reporter'];
			}
		}

		$users = array_unique($users);
		foreach($users as $v) {
			$result = $this->user->query->select('users', array('email'), array('login', $v));
			if(is_array($result)) {
				$result = array_shift($result);
				$mail[$v] = $result['email'];
			} else {
				$mail[$v] = '';
			}
		}
		return $mail;
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

		$user     = $this->user->get();
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);

		// handle automail
		if(isset($this->controller->settings['email']['auto'])) {
			unset($this->controller->settings['email']['custom_from']);
			unset($this->controller->settings['email']['custom_to']);
			unset($this->controller->settings['email']['custom_subject']);
			unset($this->controller->settings['email']['custom_body']);
			if($user['login'] === $this->tasks['supporter']) {
				$this->controller->settings['email']['to_reporter'] = 'true';
			}
			else if($user['login'] === $this->tasks['reporter']) {
				$this->controller->settings['email']['to_supporter'] = 'true';
			}
			else {
				$this->controller->settings['email']['to_reporter'] = 'true';
			}
			// submit form
			$_REQUEST[$response->id]['submit'] = 'true';
		}

		if( $mode === 'update' ) {
			$to = '';
			// tasks INFOS
			$elements = array(
				'category',
				'priority',
				'status',
				'severity',
				'project',
				'platform'
			);
			foreach($elements as $v) {
				if(isset($this->tasks[$v])) {
					$option = $this->db->select('tasks_form', array('option'), array('id', $this->tasks[$v]));
					if(is_array($option)) {
						$option = array_shift($option);
						$this->tasks[$v] = $option['option'];
					}
					else {
						$this->tasks[$v] = '';
					}
				}
			}

			$d['from']['label']                    = $this->lang['mail_from'];
			$d['from']['object']['type']           = 'htmlobject_input';
			$d['from']['object']['attrib']['type'] = 'text';
			$d['from']['object']['attrib']['name'] = 'from';
			if(!isset($this->controller->settings['email']['custom_from'])) {
				$d['from']['static']                       = true;	
				$d['from']['object']['attrib']['disabled'] = true;
			} else {
				$d['from']['required'] = true;
			}
			if(isset($GLOBALS['settings']['smtp']['from'])) {
				$d['from']['object']['attrib']['value'] = $GLOBALS['settings']['smtp']['from'];
			}
			if(isset($this->controller->settings['email']['from'])) {
				if($this->controller->settings['email']['from'] === '{login}') {
					if(isset($user) && isset($user['email'])) {
						$d['from']['object']['attrib']['value'] = $user['email'];
					}
				} else {
					$d['from']['object']['attrib']['value'] = $this->controller->settings['email']['from'];
				}
			}

			$d['to'] = '';
			if(isset($this->controller->settings['email']['custom_to'])) {
				$d['to'] = array();
				$d['to']['label']                    = $this->lang['mail_to'];
				$d['to']['object']['type']           = 'htmlobject_input';
				$d['to']['object']['attrib']['type'] = 'text';
				$d['to']['object']['attrib']['id']   = 'to';
				$d['to']['object']['attrib']['name'] = 'to';
			}
			$d['to_group'] = '';
			if(isset($this->controller->settings['form']['group']) && isset($this->tasks['supporter'])) {
				$d['to_group'] = array();
				$d['to_group']['label'] = $this->lang['group'];
				$d['to_group']['css']   = 'autosize';
				if(isset($this->controller->settings['email']['to_group'])) {
					$d['to_group']['static'] = true;
					$d['to_group']['object']['attrib']['checked']  = true;
					$d['to_group']['object']['attrib']['disabled'] = true;
				}
				$d['to_group']['object']['type']           = 'htmlobject_input';
				$d['to_group']['object']['attrib']['type'] = 'checkbox';
				$d['to_group']['object']['attrib']['name'] = 'to_group';
				#if(isset($this->tasks['group'])) {
					$d['to_group']['object']['attrib']['value'] = $this->tasks['group'];
				#}
			}
			$d['to_supporter'] = '';
			if(isset($this->controller->settings['form']['supporter']) && isset($this->tasks['supporter'])) {
				$d['to_supporter'] = array();
				$d['to_supporter']['label'] = $this->lang['supporter'];
				$d['to_supporter']['css']   = 'autosize';
				if(isset($this->controller->settings['email']['to_supporter'])) {
					$d['to_supporter']['static'] = true;
					$d['to_supporter']['object']['attrib']['checked']  = true;
					$d['to_supporter']['object']['attrib']['disabled'] = true;
				}
				else if($user['login'] === $this->tasks['reporter']) {
					$d['to_supporter']['object']['attrib']['checked']  = true;
				}
				$d['to_supporter']['object']['type']           = 'htmlobject_input';
				$d['to_supporter']['object']['attrib']['type'] = 'checkbox';
				$d['to_supporter']['object']['attrib']['name'] = 'to_supporter';
				#if(isset($this->tasks['supporter'])) {
					$d['to_supporter']['object']['attrib']['value'] = $this->tasks['supporter'];
				#}
			}

			$d['to_reporter'] = '';
			if(isset($this->controller->settings['form']['reporter'])) {
				$d['to_reporter'] = array();
				$d['to_reporter']['label'] = $this->lang['reporter'];
				$d['to_reporter']['css']   = 'autosize';
				if(isset($this->controller->settings['email']['to_reporter'])) {
					$d['to_reporter']['static'] = true;
					$d['to_reporter']['object']['attrib']['checked']  = true;
					$d['to_reporter']['object']['attrib']['disabled'] = true;
				}
				else if($user['login'] !== $this->tasks['reporter']) {
					$d['to_reporter']['object']['attrib']['checked']  = true;
				}
				$d['to_reporter']['object']['type']           = 'htmlobject_input';
				$d['to_reporter']['object']['attrib']['type'] = 'checkbox';
				$d['to_reporter']['object']['attrib']['name'] = 'to_reporter';
				if(isset($this->tasks['reporter'])) {
					$d['to_reporter']['object']['attrib']['value'] = $this->tasks['reporter'];
				}
			}

			$d['subject']['label']                    = $this->lang['mail_subject'];
			$d['subject']['object']['type']           = 'htmlobject_input';
			$d['subject']['object']['attrib']['type'] = 'text';
			$d['subject']['object']['attrib']['name'] = 'subject';
			if(isset($this->controller->settings['email']['subject'])) {
				$d['subject']['object']['attrib']['value'] = sprintf($this->controller->settings['email']['subject'], $this->id);
			} else {
				$d['subject']['object']['attrib']['value'] = sprintf($this->lang['label_tasks'], $this->id);
			}
			if(isset($this->controller->settings['email']['custom_subject'])) {
				$d['subject']['required'] = true;
			} else {
				$d['subject']['static']                       = true;
				$d['subject']['object']['attrib']['disabled'] = true;
			}

			$d['body']['label']                    = $this->lang['mail_body'];
			$d['body']['object']['type']           = 'htmlobject_textarea';
			$d['body']['object']['attrib']['name'] = 'body';
			if(isset($this->controller->settings['email']['custom_body'])) {
				$d['body']['required'] = true;
			} else {
				$d['body']['static']                       = true;	
				$d['body']['object']['attrib']['disabled'] = true;
			}

			// Mail Template
			// Handle reporter
			$tplvars = $this->tasks;
			if($tplvars['reporter'] === '' ) {
				if($tplvars['reporter_salutation'] !== '') {
					$reporter[] = $this->lang['reporter_salutation_'.$tplvars['reporter_salutation']];
				}
				if($tplvars['reporter_forename'] !== '') {
					$reporter[] = $tplvars['reporter_forename'];
				}
				if($tplvars['reporter_lastname'] !== '') {
					$reporter[] = $tplvars['reporter_lastname'];
				}
				if(is_array($reporter)) {
					$tplvars['reporter'] = implode(' ', $reporter);
				}
			}			
			if(is_array($tplvars)) {
				foreach($tplvars as $k => $v) {
					switch($k) {
						case 'id':
							$tplvars[$k] = sprintf($this->lang['label_tasks'], $v);
						break;
						case 'reporter_email':
						case 'reporter_salutation':
						case 'reporter_forename':
						case 'reporter_lastname':
						case 'reporter_firm':
						case 'reporter_office':
						case 'reporter_phone':
							unset($tplvars[$k]);
						break;
						case 'updated':
							if($this->forwarder === 'update') {
								$tplvars[$k] = $this->lang[$k].': '.date("Y/m/d - H:i", $v);
							} else {
								$tplvars[$k] = '';
							}
						break;
						case 'created':	
							$tplvars[$k] = $this->lang[$k].': '.date("Y/m/d - H:i", $v);
						break;
						default:
							$label = $k;
							if(isset($this->controller->settings['labels'][$k])) {
								$label = $this->controller->settings['labels'][$k];
							}
							else if(isset($this->lang[$k])) {
								$label = $this->lang[$k];
							}
							$tplvars[$k] = wordwrap($label.': '.$v, 72, "\n");
						break;
					}
				}
			}
			$t = $response->html->template($this->controller->profilesdir.'/tasks/templates/tasks.mail.tpl');
			$t->add($tplvars);
			$body = $t->get_string();
			$nid = $response->html->request()->get('notice_id');
			if($nid !== '') {
				$result = $this->db->select('tasks_notices', array('notice'), array('id', $nid));
				if(is_array($result)) {
					$result = array_shift($result);
					$result = wordwrap($result['notice'], 72, "\n");
					$body = $result."\n\n".$body;
				}
			}

			// link
			$url  = $_SERVER['REQUEST_SCHEME'].'://';
			$url .= $_SERVER['SERVER_NAME'];
			$url .= $this->response->html->thisurl.'/';
			$url .= $this->controller->response->get_url($this->controller->actions_name, 'update');
			$url .= '&id='.$this->id;
			$url .= '#notices';

			$d['body']['object']['attrib']['value'] = $body."\n".$url;

			$form->add($d);
		}
		$response->form = $form;
		$form->display_errors = false;

		return $response;
	}

}
?>
