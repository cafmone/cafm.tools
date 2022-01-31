<?php
/**
 * standort_settings_identifiers_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_identifiers_insert
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
		$this->settings   = $controller->settings;

		$identifier = $this->response->html->request()->get('identifier');
		if($identifier !== '') {
			$this->identifier = $this->db->handler()->escape($identifier);
			$this->response->add('identifier',$this->identifier);
		}

		if($this->response->html->request()->get('standort_identifiers_select') !== '') {
			$this->response->add('standort_identifiers_select',$this->response->html->request()->get('standort_identifiers_select'));
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
		if(isset($this->identifier)) {
			$sql  = 'SELECT ';
			$sql .= 'b.bezeichner_lang as bl ';
			$sql .= 'FROM '.$this->settings['query']['table'].'_identifiers AS b ';
			$sql .= 'WHERE b.bezeichner_kurz=\''.$this->identifier.'\' ';
			$values = $this->db->handler()->query($sql);
			if(is_array($values)) {
				$this->fields = $values[0];
			}
			$response = $this->update();
		} else {
			$response = $this->insert();
		}

		if(!isset($response->msg)) {
			$t = $this->response->html->template($this->tpldir.'/standort.settings.identifiers.insert.html');
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
		$error = '';
		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			// handle identifier
			$request = $form->get_request('bez');
			$check = $this->db->select($this->settings['query']['table'].'_identifiers', 'bezeichner_kurz', array('bezeichner_kurz'=>$request['bezeichner_kurz']));
			if($check === '') {
				// handle pos
				$pos = $this->db->select($this->settings['query']['table'].'_identifiers', 'pos', '', 'pos DESC', '1');
				if($pos === '') {
					$request['pos'] = 1;
				}
				elseif(is_array($pos)) {
					$request['pos'] = intval($pos[0]['pos'])+1;
				} else {
					$error = $pos;
				}
				if($error === '') {
					$error = $this->db->insert($this->settings['query']['table'].'_identifiers',$request);
				}
			} else {
				$error = sprintf($this->lang['error_exists'], $request['bezeichner_kurz']);
				$form->set_error('bez[bezeichner_kurz]',$error);
			}

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg_insert_success'], $request['bezeichner_kurz']);
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

			// handle identifier
			$identifier = $form->get_request('bez');
			$error = $this->db->update(
					$this->settings['query']['table'].'_identifiers',
					$identifier,
					array('bezeichner_kurz'=>$this->identifier)
				);

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = sprintf($this->lang['msg_update_success'], $this->identifier); 
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

		$columns = $this->db->handler()->columns($this->db->db, $this->settings['query']['table'].'_identifiers', 'bezeichner_lang');

		if(!isset($this->identifier)) {
			$d['id']['label']                         = $this->lang['table_short'];
			$d['id']['required']                      = true;
			$d['id']['validate']['regex']             = '/^[A-Z0-9_]+$/';
			$d['id']['validate']['errormsg']          = sprintf($this->lang['error_short_misspelled'], '[A-Z0-9_]');
			$d['id']['object']['type']                = 'htmlobject_input';
			$d['id']['object']['attrib']['name']      = 'bez[bezeichner_kurz]';
			if(isset($columns['bezeichner_kurz']['length'])) {
				$d['id']['object']['attrib']['maxlength'] = $columns['bezeichner_kurz']['length'];
			}
		} else {
			$d['id']['label']                        = $this->lang['table_short'].'&#160;';
			$d['id']['static']                       = true;
			$d['id']['object']['type']               = 'htmlobject_input';
			$d['id']['object']['attrib']['name']     = 'idxxx';
			$d['id']['object']['attrib']['disabled'] = true;
			$d['id']['object']['attrib']['value']    = $this->identifier;
		}

		$d['bezeichner_lang']['label']                    = $this->lang['table_short'];
		$d['bezeichner_lang']['required']                 = true;
		$d['bezeichner_lang']['object']['type']           = 'htmlobject_input';
		$d['bezeichner_lang']['object']['attrib']['name'] = 'bez[bezeichner_lang]';
		$d['bezeichner_lang']['object']['attrib']['style'] = 'width:400px;';
		if(isset($columns['bezeichner_lang']['length'])) {
			$d['bezeichner_lang']['object']['attrib']['maxlength'] = $columns['bezeichner_lang']['length'];
		}
		if(isset($fields['bl'])) {
			$d['bezeichner_lang']['object']['attrib']['value'] = $fields['bl'];
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;
		return $response;
	}

}
?>
