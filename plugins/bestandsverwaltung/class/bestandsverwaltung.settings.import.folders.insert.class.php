<?php
/**
 * bestandsverwaltung_settings_import_folders_insert
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
 *  Copyright (c) 2015-2023, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2023, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_import_folders_insert
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

		// Validate user
		$groups = array();
		if(isset($this->controller->settings['settings']['supervisor'])) {
			$groups[] = $this->controller->settings['settings']['supervisor']; 
		}
		$this->is_valid = $this->controller->user->is_valid($groups);
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
			if($this->response->html->request()->get('table_import_folders') !== '') {
				$this->response->add('table_import_folders',$this->response->html->request()->get('table_import_folders'));
			}
			$response = $this->update();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.settings.inventory.import.folders.insert.html');
				$t->add('Import Folder(s)', 'label');
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
			$form = $response->form;
			if(!$form->get_errors() && $response->submit()) {

				#$request = $form->get_request($this->controller->identifier_name);
				$request = $form->get_request('folders');
				$prozess = $form->get_request('prozess');
				$user    = $this->controller->user->get();
				$errors  = array();
				$message = array();

				$date = time();
				$bezeichner = $form->get_request('bezeichner');
				$user = $this->controller->user->get();

				foreach($request as $key => $id) {
					$d = array();
					$d['id']              = $id;
					$d['bezeichner_kurz'] = $bezeichner;
					$d['tabelle']         = 'SYSTEM';
					$d['merkmal_kurz']    = 'USER';
					$d['wert']            = $user['login'];
					$d['date']            = $date;
					$error = $this->db->insert('bestand',$d);
					if($error === '') {
						if(is_array($prozess)) {
							foreach($prozess as $k => $v) {
								$d = array();
								$d['id']              = $id;
								$d['bezeichner_kurz'] = $bezeichner;
								$d['tabelle']         = 'prozess';
								$d['merkmal_kurz']    = $k;
								$d['wert']            = $v;
								$d['date']            = $date;
								$error = $this->db->insert('bestand',$d);
								if($error !== '') {
									$errors[] = $error;
								}
							}
						}
					}
					if($error === '') {
						$message[] = sprintf('success %s', $id);
					} else {
						$errors[] = $error;
					}
				}

				// handle messages
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
		$form     = $response->get_form($this->actions_name, 'insert');

		// handle first impact with required field
		$request = $response->html->request()->get($this->controller->identifier_name);
		if($request === '') {
			$request = $response->html->request()->get('folders');
		}
		
		if( $request !== '' ) {
			$i = 0;
			foreach($request as $id) {
				$d['param_f'.$i]['label']                       = $id;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = 'folders['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			// handle prozess table to form
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.class.php');
			$ARRAYprozesses = $this->db->select('bestand_prozess', array('merkmal_lang','merkmal_kurz','datentyp'), array('bezeichner_kurz' => '*'));
			$OBJbestandsverwaltung = new bestandsverwaltung($this->db);
			if(is_array($ARRAYprozesses)) {
				foreach( $ARRAYprozesses as $k => $r ) {
					$d = array_merge($d, $OBJbestandsverwaltung->element($r, 'process', 'prozess'));
				}
			}
			
			// handle bezeichner
			$bezeichner = $this->db->select('bezeichner', '*', array('status'=>'on'));
			if(is_array($bezeichner)) {
				array_unshift($bezeichner, array('bezeichner_kurz' => '', 'bezeichner_lang' => ''));
				$d['bezeichner']['required']                    = true;
				$d['bezeichner']['label']                       = $this->lang['label_identifier'];
				$d['bezeichner']['object']['type']              = 'htmlobject_select';
				$d['bezeichner']['object']['attrib']['index']   = array('bezeichner_kurz','bezeichner_lang');
				$d['bezeichner']['object']['attrib']['name']    = 'bezeichner';
				$d['bezeichner']['object']['attrib']['id']      = 'bezeichner';
				$d['bezeichner']['object']['attrib']['options'] = $bezeichner;
				$d['bezeichner']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['label_identifier'].'\'); return false;"';
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
