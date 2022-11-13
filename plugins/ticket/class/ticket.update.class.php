<?php
/**
 * ticket_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_update
{

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
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->user       = $controller->user;

		$id = $this->response->html->request()->get('id');
		$this->id = $id;
		$this->response->add('id', $id);

		$filter = $this->response->html->request()->get('filter');
		if($filter !== '') {
			$this->response->add('filter', $filter);
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
		$response = $this->update();
		if($response instanceof htmlobject_response) {
			if(!isset($response->msg)) {
				$t = $this->response->html->template($this->tpldir.'ticket.update.html');
				$t->add($this->response->html->thisfile, 'thisfile');

				$a = $this->response->html->a();
				$a->label = '<span class="icon icon-menu-left"></div>';
				$a->href = $this->response->get_url($this->actions_name, 'select');
				$a->style = 'float:left;text-decoration:none;margin: 0 0 4px 0;';
				$a->handler = 'onclick="phppublisher.wait();"';
				$a->title = $this->lang['back_to_overview'];
				$t->add($a, 'back');

				$t->add($this->lang['label_reply'], 'label_reply');
				$t->add($this->lang['label_supporter'], 'label_supporter');
				$t->add($this->lang['label_supporter_info'], 'label_supporter_info');
				$t->add($this->lang['label_reporter'], 'label_reporter');
				$t->add($this->lang['label_notice'], 'label_notice');
				$t->add($this->lang['notice_new'], 'notice_new');
			
				if(isset($response->created)) {
					$t->add('<small style="padding: 0 0 0 10px;">'.$response->created.'</small>','date');
				} else {
					$t->add('','date');
				}

				$t->add($this->lang['label_message'].' '.$this->id, 'label_message');
				$t->add($this->lang['label_changelog'], 'label_changelog');

				$t->add($this->controller->notice('read'), 'notices');
				$t->add($this->controller->notice('insert'), 'notice');

				$t->add($this->controller->changelog($this->id)->get(), 'changelog');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				else if(isset($_REQUEST['errormsg'])) {
					$_REQUEST[$this->message_param]['error'] = $_REQUEST['errormsg'];
				} 
			} else {
				if(isset($this->controller->settings['email']['active'])) {
					$lid = '';
					if(isset($this->notice_id)) {
						$lid = '&notice_id='.$this->notice_id;
					}
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'mail', $this->message_param, $response->msg
						).'&id='.$this->id.'&forwarder=update'.$lid
					);
				} else {
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'update', $this->message_param, $response->msg
						)
					);
				}
			}
		} else {
			$div = $this->response->html->div();
			$div->css = 'errormsg full';
			$div->add($response);
			$t = $this->response->html->div();
			$t->id = 'ticket';
			$t->add($div);
		}
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function update() {
		$response = $this->controller->get_response('update');
		if($response instanceof htmlobject_response) {
			$form = $response->form;
			$f    = $form->get_request(null, true);
			if($response->submit()) {
				$s = $this->controller->settings;
				if(isset($f['reporter']) && $f['reporter'] === '--empty--') {
					if(isset($s['reporter']) && is_array($s['reporter'])) {
						if(isset($s['required']) && is_array($s['reporter'])) {
							foreach($s['reporter'] as $k => $v) {
								if(isset($s['required'][$k]) && !isset($f[$k])) {
									$form->set_error($k, sprintf($this->response->html->lang['form']['error_required'], $this->lang[$k]));
								}
							}
						}
					}
					$f['reporter'] = '';
				}
			}
			if(!$form->get_errors() && $response->submit()) {
				// unset action
				unset($f[$this->actions_name]);
				unset($f['subject']);
				unset($f['description']);
				unset($f['attachment']);
				unset($f['plugin']);
				unset($f['referer']);
				$notice = $f['message'];
				unset($f['message']);

				// changelog needs time
				$f['updated'] = time();
				$updated = $this->controller->changelog($this->id)->set($f);
				if($updated === true) {
					// set updater to notice
					$f['updater'] = 'update';
					$error = $this->db->update('ticket_tickets', $f, array('id', $this->id));
					if(!isset($error) || $error === '') {
						if($notice !== '') {
							// handle notice
							$user = $this->user->get();
							if(isset($user)) {
								$login = $user['login'];
								$date  = time();
								$error = $this->db->insert(
									'ticket_notices',
									array(
										'ticket' => $this->id,
										'login' => $login,
										'notice' => $notice,
										'private' => 0,
										'date' => $date
									)
								);
								if($error !== '') {
									$response->error = $error;
								} else {
									$response->msg = sprintf($this->lang['msg_updated'], $this->id);
								}
							} else {
								$response->error = 'No User';
							}
						} else {
							$response->msg = sprintf($this->lang['msg_updated'], $this->id);
						}
					} else {
						$response->error = $error;
					}
				}
			}
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
		}
		return $response;
	}

/*
	$lid = $this->db->last_insert_id();
	if(isset($lid) && $lid !== '') {
		$this->notice_id = $lid;
		// handle upload
		if(
			$error === '' &&
			isset($_FILES['attachment']) &&
			$_FILES['attachment']['name'] !== '' &&
			isset($this->controller->settings['settings']['attachment']) 
		) {
			$attachment = uniqid('f').'_'.$_FILES['attachment']['name'];
			$path = $this->controller->profilesdir.'/ticket/attachments/'.$this->id;
			if(!$this->file->exists($path)) {
				$error = $this->file->mkdir($path);
			}
			if(!isset($error) || $error === '') {
				require_once(CLASSDIR.'lib/file/file.upload.class.php');
				$upload = new file_upload($this->file);
				$error = $upload->upload('attachment', $path, $attachment);
				if($error !== '') {
					$error = 'Upload error: '.$error['msg'];
				} else {
					// handle upload (db)
					if(isset($attachment)) {
						$error = $this->db->insert(
							'ticket_attachments',
							array(
								'ticket' => $this->id,
								'notice' => $lid,
								'file' => $attachment,
								'name' => $_FILES['attachment']['name'],
								'type' => $_FILES['attachment']['type'],
								'size' => $_FILES['attachment']['size']
							)
						);
					}
				}
			}
		}
	}
*/							


}
?>
