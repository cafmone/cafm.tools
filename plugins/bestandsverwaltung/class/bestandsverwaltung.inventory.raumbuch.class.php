<?php
/**
 * bestandsverwaltung_inventory_raumbuch
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

class bestandsverwaltung_inventory_raumbuch
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

		require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
		$this->raumbuch = new standort($this->db, $this->file);	}

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

		$this->response->add('printout',$this->response->html->request()->get('printout'));
		$this->response->add('filter',$this->response->html->request()->get('filter'));
		if($this->response->html->request()->get('bestand_select') !== '') {
			$this->response->add('bestand_select',$this->response->html->request()->get('bestand_select'));
		}

		$response = $this->update();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.inventory.process.html');
			$t->add('Raumbuch &auml;ndern', 'label');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			$t->group_elements(array('prozess_' => 'prozesses'));
			return $t;
		} else {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, 'select', $this->message_param, $response->msg
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
				$raumbuch = $form->get_request('raumbuch');
				$user    = $this->controller->user->get();
				$errors  = array();
				$message = array();
				if($raumbuch !== '') {
					foreach($request as $key => $id) {

						$old = $this->db->select('bestand','id,wert', array('id' => $id, 'tabelle' => 'SYSTEM', 'merkmal_kurz' => 'RAUMBUCHID' ));
						if($old !== '' && isset($old[0]['wert'])) {
							// update
							$error = $this->db->update(
								'bestand',
								array('wert' => $raumbuch),
								array('id' => $id,'tabelle' => 'SYSTEM', 'merkmal_kurz' => 'RAUMBUCHID' )
							);
							if($error !== '') {
								$errors[] = $error;
							} else {
								$form->remove($this->controller->identifier_name.'['.$key.']');
								$message[] = sprintf('success %s', $id);
							}
						} else {
							// insert
							$old = $this->db->select('bestand','anlage_kurz, bezeichner_kurz, user, date', array('id' => $id));
							if($old !== '' && isset($old[0]['bezeichner_kurz'])) {
								$d['id']              = $id;
								$d['anlage_kurz']     = $old[0]['anlage_kurz'];
								$d['bezeichner_kurz'] = $old[0]['bezeichner_kurz'];
								$d['tabelle']         = 'SYSTEM';
								$d['merkmal_kurz']    = 'RAUMBUCHID';
								$d['wert']            = $raumbuch;
								$d['user']            = $old[0]['user'];
								$d['date']            = $old[0]['date'];
								$error = $this->db->insert('bestand',$d);
								if($error !== '') {
									$errors[] = $error;
								} else {
									$form->remove($this->controller->identifier_name.'['.$key.']');
									$message[] = sprintf('success %s', $id);
								}
							} else {
								$errors[] = 'Could not insert '.$v.' for id '.$id;
							}
						}
					}

					if(count($errors) === 0) {
						$response->msg = join('<br>', $message);
					} else {
						$msg = array_merge($errors, $message);
						$response->error = join('<br>', $msg);
					}
				} else {
					$response->error = 'Nothing to do';
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
		$form     = $response->get_form($this->actions_name, 'raumbuch');
		$request  = $response->html->request()->get($this->controller->identifier_name);

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

			$raumbuch = $this->raumbuch->options();
				if(is_array($raumbuch)) {
				array_unshift($raumbuch, array('id' => '', 'label' => ''));
				$d['prozess_raumbuch']['label']                       = 'Raumbuch';
				$d['prozess_raumbuch']['object']['type']              = 'htmlobject_select';
				$d['prozess_raumbuch']['object']['attrib']['index']   = array('id','label');
				$d['prozess_raumbuch']['object']['attrib']['name']    = 'raumbuch';
				$d['prozess_raumbuch']['object']['attrib']['id']      = 'raumbuch';
				$d['prozess_raumbuch']['object']['attrib']['options'] = $raumbuch;
			} else {
				$d['prozess_raumbuch'] = '';
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
