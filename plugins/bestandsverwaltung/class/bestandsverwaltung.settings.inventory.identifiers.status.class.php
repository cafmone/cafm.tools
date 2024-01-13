<?php
/**
 * bestandsverwaltung_settings_inventory_identifiers_status
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
 *  Copyright (c) 2015-2019, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_identifiers_status
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
		$this->response->add('filter',$this->response->html->request()->get('filter'));
		if($this->response->html->request()->get('settings_identifiers_select') !== '') {
			$this->response->add('settings_identifiers_select',$this->response->html->request()->get('settings_identifiers_select'));
		}

		$response = $this->update();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.settings.inventory.identifiers.status.html');
			$t->add($this->lang['headline_update_state'], 'label');
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
				$status = $form->get_request('status');
				$errors  = array();
				$message = array();
				if($status !== '') {
					foreach($request as $key => $id) {
						// update
						$error = $this->db->update(
							'bezeichner',
							array('status' => $status),
							array('bezeichner_kurz' => $id)
						);	
						if($error === '') {
							$form->remove($this->controller->identifier_name.'['.$key.']');
							$message[] = sprintf('success %s', $id);
						} else {
							$errors[] = $error;
						}
					}
				} else {
					$errors[] = 'nothing to do!';
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
		$form     = $response->get_form($this->actions_name, 'status');
		$request = $response->html->request()->get($this->controller->identifier_name);

		if( $request !== '' ) {
			$i = 0;
			foreach($request as $id) {
				$d['param_f'.$i]['label']                       = $id;
				$d['param_f'.$i]['css']                         = 'autosize float-right';
				$d['param_f'.$i]['style']                       = 'clear:both;';
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->controller->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}

			// Status
			$states[] = array('','');
			$states[] = array('on','On');
			$states[] = array('off','Off');
			$states[] = array('obsolete','Obsolete');

			$d['status']['label']                       = $this->lang['label_state'];
			$d['status']['css']                         = 'autosize';
			$d['status']['style']                       = 'margin: 0 0 0 50px;';
			$d['status']['object']['type']              = 'htmlobject_select';
			$d['status']['object']['attrib']['index']   = array(0,1);
			$d['status']['object']['attrib']['options'] = $states;
			$d['status']['object']['attrib']['name']    = 'status';

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
