<?php
/**
 * bestandsverwaltung_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_addrow
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
		$this->dbtable    = $controller->dbtable;
		$this->columns     = $controller->__get_columns($this->dbtable);
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
		if(is_array($this->columns)) {
			$response = $this->updatecolumn();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}

				$form = $this->response->get_form($this->actions_name, 'addrow', false);
				$description = $this->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'select');

				$t = $this->response->html->template($this->tpldir.'/query.addrow.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($description, 'description');
				$t->add($response->form);
				$t->add('Add Row', 'label');
				$t->group_elements(array('param_' => 'form'));
				$t->group_elements(array('column_' => 'columns'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'select', $this->message_param, $response->msg
					)
				);
			}
		}
		else if(is_string($this->columns)) {
			return $this->columns;
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
	function updatecolumn() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$f = $form->get_request(null, true);
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$result = $this->db->insert($this->dbtable, $f);
				if(isset($result) && $result !== '') {
					$response->error = $result;
				} else {
					$response->msg = 'success';
				}
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
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
		$form     = $response->get_form($this->actions_name, 'addrow');
		$d        = array();
		$columns   = $this->columns;
		$data     = array();
		if(is_array($columns)) {
			foreach($columns as $column) {
				$d['column_'.$column['column']]['label'] = $column['column'];
				if($column['null'] === 'no') {
					$d['column_'.$column['column']]['required'] = true;
				}
				$d['column_'.$column['column']]['object']['type']                = 'htmlobject_input';
				$d['column_'.$column['column']]['object']['attrib']['name']      = $column['column'];
				$d['column_'.$column['column']]['object']['attrib']['maxlenght'] = $column['length'];
				if($column['extra'] === 'auto_increment') {
					$d['column_'.$column['column']]['static'] = true;
					$d['column_'.$column['column']]['required'] = false;
					$d['column_'.$column['column']]['object']['attrib']['disabled'] = true;
				}
				if(stripos($column['type'],'int') !== false ) {
					$d['column_'.$column['column']]['validate']['regex']    = '/^[0-9]+$/i';
					$d['column_'.$column['column']]['validate']['errormsg'] = sprintf('%s must be number', $column['column']);
				}
			}
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
