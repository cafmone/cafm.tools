<?php
/**
 * formbuilder_attribs_insert
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
 *  along with this file (see ../../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2024, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2024, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../../LICENSE.TXT)
 * @version 1.0
 */

class formbuilder_attribs_insert
{
/**
* prefix for form tables
* @access public
* @var string
*/
var $table_prefix;
/**
* identifier table
* @access public
* @var string
*/
var $table_bezeichner;

var $lang = array();
var $datatypes = array(
	array('checkbox'),
	array('integer'),
	#array('katalog'), // deprecated
	array('select'),
	array('multiple'),
	array('text'),
	array('textarea')
);

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

		$table = $this->response->html->request()->get('table');
		if($table !== '') {
			$this->table = $table;
			$this->response->add('table',$this->table);
		} else {
			$this->table = 'merkmale';
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

		$row = $this->response->html->request()->get('row');
		if( $row !== '') {
			$this->row = $this->db->handler()->escape($row);
			$this->response->add('row', $this->row);
			$values = $this->db->select($this->table_prefix.''.$this->table, '*', array('row', $this->row));
			if(is_array($values)) {
				$this->fields = $values[0];
			}
		}

		if(isset($this->row)) {
			$response = $this->update();
		} else {
			$response = $this->insert();
		}

		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}

			$t = $this->response->html->template($this->tpldir.'/formbuilder.attribs.insert.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
			$t->add($this->lang['label_identifiers'],'label_identifiers');
			$t->add($response->counter,'counter');
			$t->group_elements(array('param_' => 'form'));
			$t->group_elements(array('available_' => 'available'));
			$t->group_elements(array('selected_' => 'selected'));

		} else {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'select', $this->message_param, $response->msg
				).'#'.$response->id
			);
		}

		return $t;
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
			$bezeichner = $form->get_request('bezeichner');
			$attribs = $form->get_request('attribs', true);

			// handle option
			$option = $form->get_request('option');
			if($attribs['datentyp'] === 'select' && $option === '') {
				$error = sprintf($this->response->html->lang['form']['error_required'], 'Options');
				$form->set_error('option', '');
			}
			elseif($attribs['datentyp'] === 'multiple' && $option === '') {
				$error = sprintf($this->response->html->lang['form']['error_required'], 'Options');
				$form->set_error('option', '');
			}

			if(!isset($error)) {
				$check = $this->db->select($this->table_prefix.''.$this->table, 'merkmal_kurz', array('merkmal_kurz' => $attribs['merkmal_kurz']));
				if(!is_array($check)) {
					$attribs['bezeichner_kurz'] = '';
					if($bezeichner !== '') {
						$attribs['bezeichner_kurz'] = implode(',',$bezeichner);
					}
					if($attribs['pflichtfeld'] !== '') {
						$attribs['pflichtfeld'] = 1;
					}
					$error = $this->db->insert($this->table_prefix.''.$this->table, $attribs);
				} else {
					$error = 'in use';
					$form->set_error('attribs[merkmal_kurz]','');
				}
				if(!isset($error) || $error === '') {
					if($option !== '') {
						// insert option
						$error = $this->db->insert(
								$this->table_prefix.'option2attrib',
								array('option' => $option, 'attrib' => $attribs['merkmal_kurz'])
							);
					}
				}
			}

			// handle error
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg_added_attrib'], $attribs['merkmal_kurz']);
				$response->id  = $attribs['merkmal_kurz'];
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
			$bezeichner = $form->get_request('bezeichner');
			$attribs = $form->get_request('attribs', true);

			// handle changing merkmal_kurz (id)
			if($this->fields['merkmal_kurz'] !== $attribs['merkmal_kurz']) {

				### TODO change id by $this->table_prefix 
				# e.g. bestand_ => bestand.merkmal_kurz
				# e.g. todo_ => ..taetigkeit2merkmal merkmal_kurz

				// reset change for now
				$id = $this->fields['merkmal_kurz'];
			} else {
				$id = $attribs['merkmal_kurz'];
			}

			// handle option
			$option = $form->get_request('option');
			if($attribs['datentyp'] === 'select' && $option === '') {
				$error = sprintf($this->response->html->lang['form']['error_required'], 'Options');
				$form->set_error('option', '');
			}
			elseif($attribs['datentyp'] === 'multiple' && $option === '') {
				$error = sprintf($this->response->html->lang['form']['error_required'], 'Options');
				$form->set_error('option', '');
			}

			if(($attribs['datentyp'] === 'select' || $attribs['datentyp'] === 'multiple') && $option !== '') {
				// check for existing options
				$check = $this->db->select($this->table_prefix.'option2attrib','`row`,`option`', array('attrib'=>$id));
				if(isset($check[0])) {
					// update
					$error = $this->db->update(
							$this->table_prefix.'option2attrib',
							array('option'=>$option, 'attrib' => $attribs['merkmal_kurz']),
							array('row'=>$check[0]['row'])
						);
				} 
				elseif($check === '') {
					// insert
					$error = $this->db->insert(
							$this->table_prefix.'option2attrib',
							array('option' => $option, 'attrib' => $attribs['merkmal_kurz'])
						);
				} else {
					$error = $check;
				}
			}
			elseif($attribs['datentyp'] !== 'select' && $attribs['datentyp'] !== 'multiple' && $option === '') {
				// check for existing options
				$check = $this->db->select($this->table_prefix.'option2attrib','`row`,`option`', array('attrib'=>$id));
				if(isset($check[0])) {
					// delete
					$error = $this->db->delete(
							$this->table_prefix.'option2attrib',
							array('row'=>$check[0]['row'])
						);
				} 
				else if($check !== '') {
					$error = $check;
				}
			}

			if(!isset($error) || $error === '') {
				// handle $id
				$attribs['merkmal_kurz']    = $id;
				$attribs['bezeichner_kurz'] = '';
				if($bezeichner !== '') {
					$attribs['bezeichner_kurz'] = implode(',',$bezeichner);
				}
				if($attribs['pflichtfeld'] !== '') {
					$attribs['pflichtfeld'] = 1;
				}

				$error = $this->db->update(
					$this->table_prefix.''.$this->table,
					$attribs,
					array('row',$this->row)
				);
			}
			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg_updated_attrib'], $attribs['merkmal_kurz']);
				$response->id  = $attribs['merkmal_kurz'];
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

		if(isset($this->row)) {
			#$d['row']['label']                        = 'Row';
			#$d['row']['static']                       = true;
			$d['row']['object']['type']               = 'htmlobject_input';
			$d['row']['object']['attrib']['type']     = 'hidden';
			$d['row']['object']['attrib']['name']     = 'dummyrow';
			$d['row']['object']['attrib']['value']    = $this->row;
			$d['row']['object']['attrib']['disabled'] = true;
		} else {
			$d['row'] = '';
		}

		$d['table']['label']                        = $this->lang['label_index'];
		$d['table']['static']                       = true;
		$d['table']['object']['type']               = 'htmlobject_input';
		$d['table']['object']['attrib']['name']     = 'dummytable';
		$d['table']['object']['attrib']['value']    = $this->table;
		$d['table']['object']['attrib']['disabled'] = true;

		$d['id']['label']                         = 'ID';
		$d['id']['required']                      = true;
		$d['id']['validate']['regex']             = '/^[A-Z0-9_]+$/';
		$d['id']['validate']['errormsg']          = sprintf('%s must be A-Z 0-9 or _', 'ID');
		$d['id']['object']['type']                = 'htmlobject_input';
		$d['id']['object']['attrib']['name']      = 'attribs[merkmal_kurz]';
		$d['id']['object']['attrib']['maxlength'] = 50;
		if(isset($this->fields['merkmal_kurz'])) {
			$d['id']['object']['attrib']['value'] = $this->fields['merkmal_kurz'];
		}

		$d['label']['label']                         = $this->lang['label_label'];
		$d['label']['required']                      = true;
		$d['label']['object']['type']                = 'htmlobject_input';
		$d['label']['object']['attrib']['name']      = 'attribs[merkmal_lang]';
		$d['label']['object']['attrib']['maxlength'] = 255;
		if(isset($this->fields['merkmal_lang'])) {
			$d['label']['object']['attrib']['value'] = $this->fields['merkmal_lang'];
		}

/*
		$datatypes[] = array('');
		$datatypes[] = array('checkbox');
		$datatypes[] = array('integer');
		$datatypes[] = array('katalog'); // deprecated
		$datatypes[] = array('select');
		$datatypes[] = array('text');
		$datatypes[] = array('textarea');
*/
		array_unshift($this->datatypes, array(''));

		$d['datentyp']['label']                       = $this->lang['label_datatype'];
		$d['datentyp']['required']                    = true;
		$d['datentyp']['object']['type']              = 'htmlobject_select';
		$d['datentyp']['object']['attrib']['index']   = array(0,0);
		$d['datentyp']['object']['attrib']['id']      = 'datentyp';
		$d['datentyp']['object']['attrib']['name']    = 'attribs[datentyp]';
		$d['datentyp']['object']['attrib']['options'] = $this->datatypes;
		$d['datentyp']['object']['attrib']['handler'] = 'onchange="ChangeOptions(\'datentyp\');"';
		if(isset($this->fields['datentyp'])) {
			$d['datentyp']['object']['attrib']['selected'] = array($this->fields['datentyp']);
		}

		// Options
		$sql  = 'SELECT `option` ';
		$sql .= 'FROM `'.$this->table_prefix.'options` ';
		$sql .= 'GROUP BY `option` ';
		$sql .= 'ORDER BY `option` ';
		$options = $this->db->handler()->query($sql);

		if(is_array($options)) {
			array_unshift($options, array('option'=>''));
		}

		$d['options']['label']                        = $this->lang['label_options'];
		$d['options']['object']['type']               = 'htmlobject_select';
		$d['options']['object']['attrib']['index']    = array('option','option');
		$d['options']['object']['attrib']['id']       = 'option';
		$d['options']['object']['attrib']['name']     = 'option';
		$d['options']['object']['attrib']['options']  = $options;
		if(
			isset($this->fields['merkmal_kurz']) && 
			isset($this->fields['datentyp']) && 
			( $this->fields['datentyp'] === 'select' || 
				$this->fields['datentyp'] === 'multiple' )
		) {
			$sql  = 'SELECT `option` ';
			$sql .= 'FROM `'.$this->table_prefix.'option2attrib` ';
			$sql .= 'WHERE `attrib`=\''.$this->fields['merkmal_kurz'].'\'';
			$selected = $this->db->handler()->query($sql);
			if(is_array($selected)) {
				$d['options']['object']['attrib']['selected'] = array($selected[0]['option']);
			}
		}

		$d['pflichtfeld']['label']                     = $this->lang['label_mandatory'];
		$d['pflichtfeld']['object']['type']            = 'htmlobject_input';
		$d['pflichtfeld']['object']['attrib']['type']  = 'checkbox';
		$d['pflichtfeld']['object']['attrib']['name']  = 'attribs[pflichtfeld]';
		if(isset($this->fields['pflichtfeld']) && $this->fields['pflichtfeld'] === '1') {
			$d['pflichtfeld']['object']['attrib']['checked'] = true;
		}
		
		$d['minimum']['label']                         = $this->lang['label_min'];
		$d['minimum']['validate']['regex']             = '/^[0-9]+$/i';
		$d['minimum']['validate']['errormsg']          = sprintf($this->response->html->lang['form']['error_NaN'], $this->lang['label_min']);
		$d['minimum']['object']['type']                = 'htmlobject_input';
		$d['minimum']['object']['attrib']['id']        = 'minimum';
		$d['minimum']['object']['attrib']['name']      = 'attribs[minimum]';
		$d['minimum']['object']['attrib']['maxlength'] = 5;
		if(isset($this->fields['minimum'])) {
			$d['minimum']['object']['attrib']['value'] = $this->fields['minimum'];
		}

		$d['maximum']['label']                         = $this->lang['label_max'];
		$d['maximum']['validate']['regex']             = '/^[0-9]+$/i';
		$d['maximum']['validate']['errormsg']          = sprintf($this->response->html->lang['form']['error_NaN'], $this->lang['label_max']);
		$d['maximum']['object']['type']                = 'htmlobject_input';
		$d['maximum']['object']['attrib']['id']        = 'maximum';
		$d['maximum']['object']['attrib']['name']      = 'attribs[maximum]';
		$d['maximum']['object']['attrib']['maxlength'] = 5;
		if(isset($this->fields['maximum'])) {
			$d['maximum']['object']['attrib']['value'] = $this->fields['maximum'];
		}

### TODO change table bezeichner

/*
		$bezeichner = $this->db->select($this->table_bezeichner, 'bezeichner_kurz,bezeichner_lang', null, 'bezeichner_lang');
		if(is_array($bezeichner)) {
			$selected = array();
			if(isset($this->fields['bezeichner_kurz'])) {
				$selected = explode(',',$this->fields['bezeichner_kurz']);
			}

			// Add Wildcard
			array_unshift($bezeichner, array('bezeichner_kurz' => '*', 'bezeichner_lang' => ''));

			$i = 0;
			foreach($bezeichner as $b) {
				$d['bezeichner_bez'.$i]['label']                     = $b['bezeichner_lang'].' ('.$b['bezeichner_kurz'].')';
				$d['bezeichner_bez'.$i]['css']                       = 'autosize nowrap inverted checkbox';
				$d['bezeichner_bez'.$i]['object']['type']            = 'htmlobject_input';
				$d['bezeichner_bez'.$i]['object']['attrib']['type']  = 'checkbox';
				$d['bezeichner_bez'.$i]['object']['attrib']['name']  = 'bezeichner['.$b['bezeichner_kurz'].']';
				$d['bezeichner_bez'.$i]['object']['attrib']['value'] = $b['bezeichner_kurz'];
				if(in_array($b['bezeichner_kurz'], $selected)) {
					$d['bezeichner_bez'.$i]['object']['attrib']['checked'] = true;
				}
				$i++;
			}
		}
*/

		$bezeichner = $this->db->select($this->table_bezeichner, 'bezeichner_kurz,bezeichner_lang,status', null, 'bezeichner_lang');
		$selected = array();
		if(is_array($bezeichner)) {
			if(isset($this->fields['bezeichner_kurz']) && $this->fields['bezeichner_kurz'] !== '') {
				$selected = explode(',',$this->fields['bezeichner_kurz']);
			}
			// Add Wildcard
			array_unshift($bezeichner, array('bezeichner_kurz' => '*', 'bezeichner_lang' => 'Alle'));

			$i = 0;
			$cselected = 0;

			foreach($bezeichner as $b) {
				if(in_array($b['bezeichner_kurz'], $selected)) {

					$label = $b['bezeichner_lang'] .' ('.$b['bezeichner_kurz'].')';
					if(isset($b['status']) && $b['status'] === 'off') {
						$label .= ' &#x271D;';
					}

					$d['selected_bez'.$i]['label'] = $label;
					if($b['bezeichner_kurz'] !== '*') {
						$d['selected_bez'.$i]['id'] = $b['bezeichner_kurz'];
					}
					$d['selected_bez'.$i]['css']                         = 'autosize nowrap inverted checkbox';
					$d['selected_bez'.$i]['id']                          = $b['bezeichner_kurz'];
					$d['selected_bez'.$i]['object']['type']              = 'htmlobject_input';
					$d['selected_bez'.$i]['object']['attrib']['type']    = 'checkbox';
					$d['selected_bez'.$i]['object']['attrib']['name']    = 'bezeichner['.$b['bezeichner_kurz'].']';
					$d['selected_bez'.$i]['object']['attrib']['value']   = $b['bezeichner_kurz'];
					$d['selected_bez'.$i]['object']['attrib']['handler'] = '';
					$d['selected_bez'.$i]['object']['attrib']['checked'] = true;

					$cselected ++;

				} else {

					$label = $b['bezeichner_lang'] .' ('.$b['bezeichner_kurz'].')';
					if(isset($b['status']) && $b['status'] === 'off') {
						$label .= ' &#x271D;';
					}

					$d['available_bez'.$i]['label'] = $label;
					if($b['bezeichner_kurz'] !== '*') {
						$d['available_bez'.$i]['id'] = $b['bezeichner_kurz'];
					}
					$d['available_bez'.$i]['css']                         = 'autosize nowrap inverted checkbox';
					$d['available_bez'.$i]['id']                          = $b['bezeichner_kurz'];
					$d['available_bez'.$i]['object']['type']              = 'htmlobject_input';
					$d['available_bez'.$i]['object']['attrib']['type']    = 'checkbox';
					$d['available_bez'.$i]['object']['attrib']['name']    = 'bezeichner['.$b['bezeichner_kurz'].']';
					$d['available_bez'.$i]['object']['attrib']['value']   = $b['bezeichner_kurz'];
					$d['available_bez'.$i]['object']['attrib']['handler'] = '';

				}
				$i++;
			}
		} 

		// handle empty available
		if(!is_array($bezeichner)) {
			$d['available_bez0'] = '';
			$bezeichner = array('xx');
		}
		// handle empty selected
		if(count($selected) < 1) {
			$d['selected_bez0'] = '';
			$cselected = 0;
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;

		$response->counter = $cselected.' / '.(count($bezeichner));

		return $response;
	}

}
?>
