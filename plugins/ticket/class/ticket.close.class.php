<?php
/**
 * ticket_close
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_close
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
		$this->settings   = PROFILESDIR.'/ticket.ini';
		$this->user       = $controller->user;

		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			$this->id = $id;
			$this->response->add('id', $id);
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
		$response = $this->close();
		if($response instanceof htmlobject_response) {
			if($response->cancel()) {
				if(isset($this->id)) {
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'update', 'id', $this->id
						)
					);
				} else {
					$elements = $response->html->request()->get($this->identifier_name);
					if(is_array($elements) && count($elements) === 1) {
						$id = array_shift($elements);
						$this->response->redirect(
							$this->response->get_url(
								$this->actions_name, 'update', 'id', $id
							)
						);
					} else {
						$this->response->redirect( $this->response->get_url( $this->actions_name, 'select' ));
					}
				}
			}
			if(!isset($response->msg)) {
				$data['label'] = $this->lang['label_close'];
				$vars = array_merge(
					$data,
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'ticket.close.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
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
	 * Close
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function close() {
		$response = $this->get_response('close');
		
		$elements = array($this->id);

		$form = $response->form;
		if( $elements !== '' ) {
			$i = 0;
			foreach($elements as $id) {
				$d['param_f'.$i]['label']                       = sprintf($this->lang['label_ticket'], $id);
				$d['param_f'.$i]['css']                         = 'autosize';
				$d['param_f'.$i]['style']                       = 'float:right;clear:both;';
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($elements as $key => $id) {
					$error = $this->db->update( 'ticket_tickets', array('updater' => 'closed', 'updated' => time()), array('id', $this->id) );
					if($error === '') {
						$user = $this->user->get();
						$error = $this->db->insert(
							'ticket_changelog', 
							array(
								'ticket' => $this->id,
								'date' => time(),
								'login' => $user['login'],
								'option' => 'close',
								'from' => 'notice',
								'to' => 'closed'
							)
						);
						if(!isset($error) || $error === '') {
							$form->remove($this->identifier_name.'['.$key.']');
							// handle notice
							$notice = $form->get_request('message');
							$user   = $this->user->get();
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
								$this->notice_id = $this->db->last_insert_id();
								$message[] = sprintf($this->lang['msg_closed'], $id);
							} else {
								$errors[] = 'No User';
							}
						} else {
							$errors[] = $error;
						}
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$response->error = join('<br>', array_merge($errors, $message));
				}
			}
			else if($form->get_errors()) {
				$response->error = implode('<br>',$form->get_errors());
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

		$d = array();
		// notice (message)
		$d['message']['label']                     = $this->lang['notice_new'];
		$d['message']['required']                  = true;
		$d['message']['css']                       = 'autosize';
		$d['message']['id']                        = 'message_box';
		$d['message']['object']['type']            = 'htmlobject_textarea';
		$d['message']['object']['attrib']['name']  = 'message';
		$d['message']['object']['attrib']['id']    = 'message';
		$d['message']['object']['attrib']['value'] = '';
		$d['message']['object']['attrib']['cols']  = '30';
		$d['message']['object']['attrib']['rows']  = '6';

		$n_columns = $this->db->handler()->columns($this->db->db, 'ticket_notices');
		if(isset($n_columns['notice']['length'])) {
			$d['message']['object']['attrib']['maxlength'] = $n_columns['notice']['length'];
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
