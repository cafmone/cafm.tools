<?php
/**
 * bestandsverwaltung_inventory_process
 *
 * This file is part of plugin bestandsverwaltung
 *
 *  This file is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU GENERAL PUBLIC LICENSE Version 2
 *  as published by the Free Software Foundation;
 *
 *  This file is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this file (see ../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_inventory_process
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
		$this->prozesses  = $this->db->select('bestand_prozess', array('merkmal_lang','merkmal_kurz','datentyp'));

		// Validate user
		$groups = array();
		if(isset($this->controller->settings['settings']['supervisor'])) {
			$groups[] = $this->controller->settings['settings']['supervisor']; 
		}
		$this->is_valid = $this->controller->user->is_valid($groups);

		require_once(CLASSDIR.'lib/formbuilder/formbuilder.class.php');
		$this->formbuilder = new formbuilder($this->db);
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
		if($this->is_valid) {
			$this->response->add('printout',$this->response->html->request()->get('printout'));
			$this->response->add('filter',$this->response->html->request()->get('filter'));
			$this->response->add('export',$this->response->html->request()->get('export'));
			if($this->response->html->request()->get('bestand_select') !== '') {
				$this->response->add('bestand_select',$this->response->html->request()->get('bestand_select'));
			}

			$response = $this->update();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.inventory.process.html');
				$t->add('Prozess Status &auml;ndern', 'label');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				$t->group_elements(array('process_' => 'processes'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'select', $this->message_param, $response->msg
					)
				);
			}
		} else {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, 'select', $this->message_param, 'Permission denied'
				)
			);
		}
		
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
		$response = $this->get_response();
		if(!isset($response->msg)) {
			$form     = $response->form;
			if(!$form->get_errors() && $response->submit()) {
				$request = $form->get_request($this->controller->identifier_name);
				$prozess = $form->get_request('prozess');
				$user    = $this->controller->user->get();
				$errors  = array();
				$message = array();
				foreach($request as $key => $id) {
					if(is_array($prozess)) {
						foreach($prozess as $k => $v) {
							$old = $this->db->select('bestand','wert', array('id' => $id, 'merkmal_kurz' => $k ));
							if($old !== '' && isset($old[0]['wert'])) {
								$old = $old[0]['wert'];
								// update
								$error = $this->db->update(
									'bestand',
									array('wert' => $v),
									array('id' => $id, 'merkmal_kurz' => $k )
								);	
								// changelog
								if($error === '') {
									$d = array();
									$d['id']           = $id;
									$d['merkmal_kurz'] = $k;
									$d['old']          = $old;
									$d['new']          = $v;
									$d['user']         = $user['login'];
									$d['date']         = time();
									$error = $this->db->insert('changelog',$d);
									if($error !== '') {
										$errors[] = $error;
									} else {
										$form->remove($this->controller->identifier_name.'['.$key.']');
										$message[] = sprintf('success %s', $id);
									}
								} else {
									$errors[] = $error;
								}
							} else {
								// insert
								$old = $this->db->select('bestand','bezeichner_kurz, date', array('id' => $id));
								if($old !== '' && isset($old[0]['bezeichner_kurz'])) {
									$d = array();
									$d['id']              = $id;
									$d['bezeichner_kurz'] = $old[0]['bezeichner_kurz'];
									$d['tabelle']         = 'prozess';
									$d['merkmal_kurz']    = $k;
									$d['wert']            = $v;
									$d['date']            = $old[0]['date'];
									$error = $this->db->insert('bestand',$d);
									if($error !== '') {
										$errors[] = $error;
									} else {
										$form->remove($this->controller->identifier_name.'['.$key.']');
										$message[] = sprintf('success %s', $id);
									}
								} else {
									$errors[] = 'Could not insert '.$v.' for id '.$id.' - Id not found';
								}
							}
						}
					}
				}

				if(count($errors) === 0) {
						$response->msg = join('<br>', $message);
				} else {
						$msg = array_merge($errors, $message);
						$response->error = join('<br>', $msg);
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
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'process');
		$request = $response->html->request()->get($this->controller->identifier_name);

		if( $request !== '' ) {
			$i = 0;
			foreach($request as $id) {
				$d['param_f'.$i]['label']                       = $id;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->controller->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			if(is_array($this->prozesses)) {
				foreach( $this->prozesses as $k => $r ) {
					$d = array_merge($d, $this->formbuilder->element($r, 'process', 'prozess'));
				}
			}

			$form->add($d);
			$form->display_errors = false;
			$response->form = $form;

			return $response;
		} else {
			$response->msg = '';
			return $response;
		}
	}

}
?>
