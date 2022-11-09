<?php
/**
 * ticket_notice
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_notice
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
	function __construct($response, $controller) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $response;
		$this->controller = $controller;
		$this->user       = $controller->user;

		$id = $this->response->html->request()->get('id');
		$this->id = $id;
		$this->response->add('id', $id);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param enum $action [insert|read]
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action($action = null) {

		if(isset($action)) {
			$this->action = $action;
		}
		if(!isset($this->action)) {
			$this->action = $this->response->html->request()->get($this->actions_name);
		}
		$this->response->add($this->actions_name, $this->action);

		switch($this->action) {
		case '':
		case 'read':
			$this->response->add($this->controller->actions_name, 'notice');
			return $this->read();
		break;
		case 'insert':
			$response = $this->insert();
			if(isset($response->msg)) {
				if(!isset($response->nid)) {
					$this->response->redirect(
						$this->response->get_url(
							$this->controller->actions_name, 'update', $this->message_param, $response->msg
						)
					);
				}
				else if (isset($response->nid)) {
					if(isset($this->controller->settings['email']['active'])) {
						$this->response->redirect(
							$this->response->get_url(
								$this->controller->actions_name, 'mail', $this->message_param, $response->msg
							).'&forwarder=update&notice_id='.$response->nid
						);
					} else {
						$this->response->redirect(
							$this->response->get_url(
								$this->actions_name, 'update', $this->message_param, $response->msg
							)
						);
					}
				}
				else if(!isset($response->nid)) {
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'update', $this->message_param, $response->msg
						)
					);
				}
			}
			if(isset($response->error)) {
				$_REQUEST['errormsg'] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'ticket.notice.insert.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($this->lang['notice_new'], 'label');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			return $t;
		break;
		case 'delete':
			$response = $this->delete();
			if(isset($response->msg)) {
				$this->response->redirect(
					$this->response->get_url(
						$this->controller->actions_name, 'update', $this->message_param, $response->msg
					).'#notices'
				);
			}
		break;
		case 'public':
			$response = $this->mkpublic();
			if(isset($response->msg)) {
				$this->response->redirect(
					$this->response->get_url(
						$this->controller->actions_name, 'update', $this->message_param, $response->msg
					).'#notice_'.$response->notice
				);
			}
			else if(isset($response->error)) {
				$this->response->redirect(
					$this->response->get_url(
						$this->controller->actions_name, 'update', $this->message_param, $response->error
					)
				);
			}
		break;
		}
	}

	//--------------------------------------------
	/**
	 * Read
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function read() {
		// notices
		$notices = '';
		$user    = $this->user->get();
		$result  = $this->db->select('ticket_notices', '*', array('ticket', $this->id));
		if(is_array($result)) {
			rsort($result);
			foreach($result as $k => $v) {
				$css = '';
				$display = true;
				if(isset($v['private']) && $v['private'] == 1) {
					$css = 'private';
					if($this->user->is_admin() === false) {
						if(isset($v['login']) && $v['login'] !== $user['login']) {
							$display = false;
						}
					}
				}
				if($display === true) {
					$del = $this->response->html->a();
					$del->label = '';
					$del->href  = $this->response->get_url($this->actions_name, 'delete').'&notice='.$v['id'].'&ticket='.$this->id;
					$del->title = $this->lang['notice_delete'];
					$del->css   = 'delete icon icon-trash';
					$del->handler = 'onclick="phppublisher.wait();"';

					// handle mkpublic
					$pub = '';
					if(isset($v['login']) && $v['login'] === $user['login']) {
						$pub = $this->response->html->a();
						if($css === 'private') {
							$pub->label = '';
							$pub->title = $this->lang['notice_make_public'];
							$pub->css   = 'private icon icon-lock';
							$pub->style = 'color: red';
						} else {
							$pub->label = '';
							$pub->title = $this->lang['notice_make_private'];
							$pub->css   = 'public icon icon-lock';
							$pub->style = 'color: #32CD32';
						}
						$pub->href = $this->response->get_url($this->actions_name, 'public').'&notice='.$v['id'];
						$pub->handler = 'onclick="phppublisher.wait();"';
					}

					$v['notice'] = htmlentities($v['notice'], ENT_COMPAT, 'UTF-8');
					$v['notice'] = str_replace("\n", '<br>', $v['notice']);
					$v['notice'] = str_replace("[[b]]", '<b>', $v['notice']);
					$v['notice'] = str_replace("[[/b]]", '</b>', $v['notice']);
					$v['notice'] = str_replace("[[i]]", '<i>', $v['notice']);
					$v['notice'] = str_replace("[[/i]]", '</i>', $v['notice']);
					$v['notice'] = str_replace("[[u]]", '<u>', $v['notice']);
					$v['notice'] = str_replace("[[/u]]", '</u>', $v['notice']);
					$v['notice'] = preg_replace('~\[\[a (.*)\]\](.*)\[\[/a\]\]~', '<a href="$1" target="_blank">$2</a>', $v['notice']);

					// handle links
					/*
					$regexUrl = '!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.,:~#?&;//=\]\[]+)!i';
					if(preg_match_all($regexUrl, $v['notice'], $urls)) {
						if(isset($urls[0])) {
							$urls[0] = array_unique($urls[0]);
							foreach($urls[0] as $url) {
								if(isset($url) && $url !== '') {
									$v['notice'] = str_replace($url, '<a href="'.$url.'" target="_blank" title="link: '.$url.'">'.$url.'</a>', $v['notice']);
								}
							}
						}
					}
					*/

					// attachment
					if(isset($this->controller->settings['settings']['attachment'])) {
						$result = $this->db->select('ticket_attachments', array('file', 'name'), array('notice', $v['id']));
						if(is_array($result)) {
							$a        = $this->response->html->a();
							$a->name  = '';
							$a->css   = 'attachment';
							$a->href  = $this->response->get_url($this->controller->actions_name, 'download').'&file='.$result[0]['file'];
							$a->label = $result[0]['name'];
							$v['notice'] = $v['notice'].'<br>'.$a->get_string();
						}
					}
					$login = (isset($v['login'])) ? $v['login'] : '';
					$n = $this->response->html->template($this->tpldir.'ticket.notice.read.html');
					$n->add($v['notice'], 'notice');
					$n->add($login, 'login');
					$n->add($css, 'css');
					$n->add($del, 'delete');
					$n->add($pub, 'public');
					$n->add('notice_'.$v['id'], 'notice_id');
					$n->add(date($this->controller->date_format, $v['date']), 'label');
					$notices .= $n->get_string();
				}
			}
		}
		return $notices;
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
		$response = $this->get_response();
		$form     = $response->form;
		$notice   = $form->get_request('notice');

		if(isset($notice) && $notice !== '') {
			if(!$form->get_errors() && $response->submit()) {
				if(!isset($response->error)) {
					$private = $form->get_request('private');
					$login   = '';
					$private = (isset($private) && $private !== '') ? 1 : 0;
					$user    = $this->user->get();
					if(isset($user)) {
						$login = $user['login'];
					}
					$date = time();
					$error = $this->db->insert(
						'ticket_notices',
						array(
							'ticket' => $this->id,
							'login' => $login,
							'notice' => $notice,
							'private' => $private,
							'date' => $date
						)
					);
					$lid = $this->db->last_insert_id();
					// set updater and updated
					if($private !== 1) {
						$error = $this->db->update(
							'ticket_tickets',
							array(
								'updated' => $date,
								'updater' => 'notice'
							),
							array( 'id', $this->id )
						);
					}

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
					if(isset($error) && $error !== '') {
						$response->error = $error;
					} else {
						if($private !== 1) {
							$response->nid  = $lid;
						}
						$response->msg = sprintf($this->lang['msg_saved'], $lid);
					}
				}
			}
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Public
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function mkpublic() {
		$response = $this->response;
		$user     = $this->user->get();
		$notice   = $response->html->request()->get('notice');
		if($notice !== '') {
			$result = $this->db->select('ticket_notices', array('private','login'), array('id', $notice));
			if(is_array($result)) {
				if($user['login'] === $result[0]['login']) {
					$private = ($result[0]['private'] == 1) ? 0 : 1;
					$error = $this->db->update('ticket_notices', array('private' => $private), array('id', $notice));
					if($error !== '') {
						$response->error = $error;
					} else {
						$response->msg = '';
					}
				} else {
					$response->error = 'Permission denied';
				}
			}
		} else {
			$reponse->msg = '';
		}
		$response->notice = $notice;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function delete() {
		$response = $this->response;
		$notice   = $response->html->request()->get('notice');
		$ticket   = $response->html->request()->get('ticket');
		if($ticket !== '' && $notice !== '') {
			// delete attachment
			$files = $this->db->select('ticket_attachments','row,file', array('notice', $notice));
			if(is_array($files)) {
				$row  = $files[0]['row'];
				$path = $this->controller->profilesdir.'ticket/attachments/'.$ticket.'/'.$files[0]['file'];
				if($this->file->exists($path)) {
					$error = $this->file->remove($path);
					if($error === '') {
						$error = $this->db->delete('ticket_attachments', array('row', $row));
					}
				}
			}

			if(!isset($error) || $error === '') {
				$error = $this->db->delete('ticket_notices', array('id', $notice));
			}

			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$response->msg = $this->lang['notice_deleted'];
			}
		}
		return $response;
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
		$form = $response->get_form($this->actions_name, 'insert');

		$submit = $form->get_elements('submit');
		$submit->value = $this->lang['notice_save'];
		$form->add($submit,'submit');

		// handle db field length
		$columns = $this->db->handler()->columns($this->db->db, 'ticket_notices');

		$ini = '';

		$t = $response->html->textarea();
		$t->id   = 'notice';
		$t->name = 'notice';
		$t->css  = 'form-control';
		if(isset($columns['notice']['length'])) {
			$t->maxlength = $columns['notice']['length'];
		}
		$d['notice']['label']  = $this->lang['notice_new'];
		$d['notice']['object'] = $t;

		$d['private']['label']                    = $this->lang['notice_private'];
		$d['private']['css']                      = 'autosize float-right';
		$d['private']['object']['type']           = 'htmlobject_input';
		$d['private']['object']['attrib']['type'] = 'checkbox';
		$d['private']['object']['attrib']['name'] = 'private';
		$d['private']['object']['attrib']['css']  = 'private';
		$d['private']['object']['attrib']['value']  = 1;
		if(isset($ini['private'])) {
			$d['private']['object']['attrib']['value'] = $ini['private'];
		}

		$d['attachment'] = '';
		if(isset($this->controller->settings['settings']['attachment'])) {
			$d['attachment'] = array();
			$d['attachment']['label']                    = '&#160;';
			$d['attachment']['css']                      = 'float-left';
			$d['attachment']['object']['type']           = 'htmlobject_input';
			$d['attachment']['object']['attrib']['type'] = 'file';
			$d['attachment']['object']['attrib']['css']  = 'form-control-file';
			$d['attachment']['object']['attrib']['id']   = 'attachment';
			$d['attachment']['object']['attrib']['name'] = 'attachment';
			$d['attachment']['object']['attrib']['size'] = '30';
			$d['attachment']['object']['attrib']['style'] = 'margin: 2px 0 0 0;';
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}
}
?>
