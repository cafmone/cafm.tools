<?php
/**
 * tasks_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_insert
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
		$response = $this->insert();
		if(!isset($response->msg)) {
			$t = $this->response->html->template($this->tpldir.'tasks.insert.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($this->lang['label_supporter'], 'label_supporter');
			$t->add($this->lang['label_supporter_info'], 'label_supporter_info');
			$t->add($this->lang['label_reporter'], 'label_reporter');
			$t->add($this->lang['label_message'], 'label_message');
			$t->add($response->form);
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			$t->group_elements(array('param_' => 'form'));
			return $t;
		} else {
			if(isset($this->controller->settings['email']['active'])) {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'mail', $this->message_param, $response->msg
					).'&id='.$response->id.'&forwarder=insert'
				);
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'insert', $this->message_param, $response->msg
					)
				);
			}
		}
		
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->controller->get_response('insert');
		$form     = $response->form;
		$f        = $form->get_request();
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
			$f['created'] = time();
			if(!isset($response->error)) {

				$plugin = $form->get_static('plugin', true);
				if(isset($plugin)) {
					$f['plugin'] = htmlentities($plugin);
				}
				$referer = $form->get_static('referer', true);
				if(isset($referer)) {
					$f['referer'] = htmlentities($referer);
				}
				$tag = $form->get_static('tag', true);
				if(isset($tag)) {
					$f['tag'] = htmlentities($tag);
				}

				$error = $this->db->insert('tasks_tasks', $f);
				if(isset($error) && $error !== '') {
					$response->error = $error;
				} else {
					$lid = $this->db->last_insert_id();
					// handle upload
					if(
						isset($_FILES['attachment']) &&
						$_FILES['attachment']['name'] !== '' &&
						isset($this->controller->settings['settings']['attachment']) 
					) {
						$attachment = uniqid('f').'_'.$_FILES['attachment']['name'];
						$path = $this->controller->profilesdir.'/tasks/attachments/'.$lid;
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
										'tasks_attachments',
										array(
											'tasks' => $lid,
											//'notice' => '',
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
					if(isset($error) && $error !== '') {
						$response->error = $error;
					} else {
						$response->id  = $lid;
						$response->msg = sprintf($this->lang['msg_saved'], $lid);
					}
				}
			}
		} 
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

}
?>
