<?php
/**
 * bestandsverwaltung_settings_inventory_identifiers_insert
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
 *  Copyright (c) 2008-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_identifiers_insert
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
		$this->controller = $controller;
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->user       = $controller->user;

		$bezeichner = $this->response->html->request()->get('bezeichner');
		if($bezeichner !== '') {
			$this->bezeichner = $this->db->handler()->escape($bezeichner);
			$this->response->add('bezeichner',$this->bezeichner);
		}

		if($this->response->html->request()->get('settings_identifiers_select') !== '') {
			$this->response->add('settings_identifiers_select',$this->response->html->request()->get('settings_identifiers_select'));
		}

		$filter = $this->response->html->request()->get('filter', true);
		if(isset($filter) && $filter !== '') {
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
		if(isset($this->bezeichner)) {
			$sql  = 'SELECT ';
			$sql .= 'b.bezeichner_lang as bl, ';
			$sql .= 'b.status as status, ';
			$sql .= 'b.din_276 as din, ';
			$sql .= 'b.alias as alias, ';
			$sql .= 'h.text as ht ';
			$sql .= 'FROM bezeichner AS b ';
			$sql .= 'LEFT JOIN bezeichner_help AS h ON (b.bezeichner_kurz=h.bezeichner_kurz) ';
			$sql .= 'WHERE b.bezeichner_kurz=\''.$this->bezeichner.'\' ';
			$values = $this->db->handler()->query($sql);
			if(is_array($values)) {
				$this->fields = $values[0];
			}
			$response = $this->update();
		} else {
			$response = $this->insert();
		}

		if(!isset($response->msg)) {
			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.settings.inventory.identifiers.insert.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
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
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			// handle bezeichner
			$bezeichner = $form->get_request('bez');
			$check = $this->db->select('bezeichner', 'bezeichner_kurz', array('bezeichner_kurz'=>$bezeichner['bezeichner_kurz']));
			if($check === '') {
				$error = $this->db->insert('bezeichner',$bezeichner);
				// hnadle help
				if($error === '') {
					$help = $form->get_request('help');
					if($help !== '') {
						$d['bezeichner_kurz'] = $bezeichner['bezeichner_kurz'];
						$d['text'] = $help;
						$error = $this->db->insert('bezeichner_help',$d);
					}
				}
			} else {
				$error = 'ERROR: bezeichner_kurz '.$bezeichner['bezeichner_kurz'].' is already in use';
				$form->set_error('bez[bezeichner_kurz]',$error);
			}

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = $this->lang['update_sucess'];
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
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
		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			// handle bezeichner
			$bezeichner = $form->get_request('bez');
			$error = $this->db->update(
					'bezeichner',
					$bezeichner,
					array('bezeichner_kurz'=>$this->bezeichner)
				);
			// hnadle help
			if($error === '') {
				$help  = $form->get_request('help');
				$check = $this->db->select('bezeichner_help', 'bezeichner_kurz', array('bezeichner_kurz'=>$this->bezeichner));
				if($check !== '') {
					$error = $this->db->update(
							'bezeichner_help',
							array('text' => $help),
							array('bezeichner_kurz'=>$this->bezeichner)
						);
				} else {
					$d['bezeichner_kurz'] = $this->bezeichner;
					$d['text'] = $help;
					$error = $this->db->insert('bezeichner_help',$d);
				}
			}

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = $this->lang['update_sucess'];;
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'insert');

		$fields = array();
		if(isset($this->fields)) {
			$fields = $this->fields;
		}

		$hcolumns = $this->db->handler()->columns($this->db->db, 'bezeichner_help', 'text');
		$bcolumns = $this->db->handler()->columns($this->db->db, 'bezeichner', 'bezeichner_lang');

		if(isset($this->bezeichner)) {
			$d['id'] = '';
			if(isset($fields['bl'])) {
				$d['bezeichner'] = $fields['bl'].' ('.$this->bezeichner.')';
			} else {
				$d['bezeichner'] = $this->bezeichner;
			}
		} else {

			$d['bezeichner'] = 'New';

			$d['id']['label']                         = $this->lang['label_short'];
			$d['id']['required']                      = true;
			$d['id']['validate']['regex']             = '/^[A-Z0-9_]+$/';
			$d['id']['validate']['errormsg']          = sprintf('%s must be A-Z 0-9 or _', 'bezeichner_kurz');
			$d['id']['object']['type']                = 'htmlobject_input';
			$d['id']['object']['attrib']['name']      = 'bez[bezeichner_kurz]';
			if(isset($bcolumns['bezeichner_kurz']['length'])) {
				$d['id']['object']['attrib']['maxlength'] = $bcolumns['bezeichner_kurz']['length'];
			}
		}

		$d['bezeichner_lang']['label']                    = $this->lang['label_long'];
		$d['bezeichner_lang']['required']                 = true;
		$d['bezeichner_lang']['object']['type']           = 'htmlobject_input';
		$d['bezeichner_lang']['object']['attrib']['name'] = 'bez[bezeichner_lang]';
		$d['bezeichner_lang']['object']['attrib']['style'] = 'width:400px;';
		if(isset($bcolumns['bezeichner_lang']['length'])) {
			$d['bezeichner_lang']['object']['attrib']['maxlength'] = $bcolumns['bezeichner_lang']['length'];
		}
		if(isset($fields['bl'])) {
			$d['bezeichner_lang']['object']['attrib']['value'] = $fields['bl'];
		}

		$d['din']['label']                     = 'DIN 276';
		$d['din']['object']['type']            = 'htmlobject_input';
		$d['din']['object']['attrib']['name']  = 'bez[din_276]';
		$d['din']['object']['attrib']['style'] = 'width:100px;';
		if(isset($bcolumns['din_276']['length'])) {
			$d['din']['object']['attrib']['maxlength'] = $bcolumns['din_276']['length'];
		}
		if(isset($fields['din'])) {
			$d['din']['object']['attrib']['value'] = $fields['din'];
		}

		$d['alias']['label']                     = $this->lang['label_alias'];
		$d['alias']['object']['type']            = 'htmlobject_input';
		$d['alias']['object']['attrib']['name']  = 'bez[alias]';
		$d['alias']['object']['attrib']['style'] = 'width:400px;';
		if(isset($bcolumns['alias']['length'])) {
			$d['alias']['object']['attrib']['maxlength'] = $bcolumns['alias']['length'];
		}
		if(isset($fields['alias'])) {
			$d['alias']['object']['attrib']['value'] = $fields['alias'];
		}

		// Status
		$states[] = array('','');
		$states[] = array('on','On');
		$states[] = array('off','Off');
		$states[] = array('obsolete','Obsolete');

		$d['status']['label']                       = $this->lang['label_state'];
		$d['status']['object']['type']              = 'htmlobject_select';
		$d['status']['object']['attrib']['index']   = array(0,1);
		$d['status']['object']['attrib']['options'] = $states;
		$d['status']['object']['attrib']['name']    = 'bez[status]';
		if(isset($fields['status'])) {
			$d['status']['object']['attrib']['selected'] = array($fields['status']);
		}

		$d['help']['label']                    = 'Help';
		$d['help']['object']['type']           = 'htmlobject_textarea';
		$d['help']['object']['attrib']['name'] = 'help';
		$d['help']['object']['attrib']['cols'] = 50;
		$d['help']['object']['attrib']['rows'] = 10;
		$d['help']['object']['attrib']['style'] = 'width:400px;';
		if(isset($hcolumns['text']['length'])) {
			$d['help']['object']['attrib']['maxlength'] = $hcolumns['text']['length'];
			$d['help']['object']['attrib']['title'] = 'maxlength: '.$hcolumns['text']['length'];
		}
		if(isset($fields['ht'])) {
			$d['help']['object']['attrib']['value'] = $fields['ht'];
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;
		return $response;
	}

}
?>
