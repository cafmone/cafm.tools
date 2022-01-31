<?php
/**
 * query_column_delete
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_column_delete
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
		$this->columns     = $controller->controller->__get_columns($this->dbtable);
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
			$response = $this->delcolumn();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}

				$form = $this->response->get_form($this->actions_name, 'delete', false);
				$description = $this->controller->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'select');

				$t = $this->response->html->template($this->tpldir.'/query.column.delete.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($description, 'description');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'delete', $this->message_param, $response->msg
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
	function delcolumn() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$f = $form->get_request(null, true);
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$sql  = 'ALTER TABLE `'.$this->db->db.'`.`'.$this->dbtable.'` ';
				$sql .= 'DROP `'.$f['column'].'`';
				$result = $this->db->handler()->query($sql);
				if(isset($result) && $result !== '') {
					$response->error = $result;
				} else {
					$response->msg = sprintf('removed column %s', $f['column']);
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
		$form     = $response->get_form($this->actions_name, 'delete');
		$d        = array();
		$columns   = $this->columns;
		$data     = array();
		if(is_array($columns)) {
			foreach($columns as $column) {
				$data[] = array($column['column']);
			}
		}

		$d['columns']['label']                       = 'column';
		$d['columns']['object']['type']              = 'htmlobject_select';
		$d['columns']['object']['attrib']['index']   = array(0,0);
		$d['columns']['object']['attrib']['options'] = $data;
		$d['columns']['object']['attrib']['name']    = 'column';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
