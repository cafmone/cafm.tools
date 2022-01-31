<?php
/**
 * query_column_edit
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_column_edit
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
		$this->columns    = $this->controller->controller->__get_columns($this->dbtable);

		$column = $this->response->html->request()->get('column');
		if($column !== '') {
			$this->column = $this->db->handler->escape($column);
			$this->response->add('column', $column);
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

		if(is_array($this->columns)) {
			$t = $this->response->html->template($this->tpldir.'/query.column.edit.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			if(!isset($this->column)) {

				$form = $this->response->get_form($this->actions_name, 'edit', false);
				$description = $this->controller->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'edit');

				$t->add($description, 'description');
				$t->add('','form');
				$t->add('','table');
				$t->add('','name');
				$t->add('','type');
				$t->add('','length');
				$t->add('','null');
				$t->add('','cancel');
				$t->add('','submit');
				return $t;
			} else {
				$response = $this->addcolumn();
				if(!isset($response->msg)) {
					if(isset($response->error)) {
						$_REQUEST[$this->message_param] = $response->error;
					}
					$t->add('', 'description');
					$t->add($response->form);
					$t->group_elements(array('param_' => 'form'));
					return $t;
				} else {
					$this->response->remove('column');
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'edit', $this->message_param, $response->msg
						)
					);
				}
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
	function addcolumn() {
		$response = $this->get_response();
		$form     = $response->form;

		if($response->cancel()) {
			$response->msg = '';
		}
		else if(!$form->get_errors() && $response->submit()) {
			$length = $this->db->handler->escape($form->get_request('length'));
			$type   = $this->db->handler->escape($form->get_request('type'));
			$null   = $this->db->handler->escape($form->get_request('null'));
			$name   = $this->db->handler->escape($form->get_request('name'));

			// check minimum length
			$sql = 'SELECT MAX(LENGTH(`'.$this->column.'`)) AS `max` FROM `'.$this->dbtable.'`';
			$max = $this->db->handler()->query($sql);
			if(isset($max[0]['max'])) {
				if($max[0]['max'] > $length) {
					$response->error = sprintf($this->response->html->lang['form']['error_minlength'], 'Length',$max[0]['max']);
					$form->set_error('length','');
				}
			}
			if(!isset($response->error)) {
				$sql  = 'ALTER TABLE `'.$this->db->db.'`.`'.$this->dbtable.'` ';
				// handle changed column name
				if($this->column === $name) {
					$sql .= 'MODIFY `'.$this->column.'` '.$type.'('.$length.') '.$null;
				} else {
					$sql .= 'CHANGE COLUMN `'.$this->column.'` `'.$name.'` '.$type.'('.$length.') '.$null;
				}
				$result = $this->db->handler()->query($sql);
				if(isset($result) && $result !== '') {
					$response->error = $result;
				} else {
					$response->msg = sprintf('modified column %s', $this->column);
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
		$form     = $response->get_form($this->actions_name, 'edit');

		$d = array();

		$d['table']['label']                        = 'Table';
		$d['table']['static']                       = true;
		$d['table']['object']['type']               = 'htmlobject_input';
		$d['table']['object']['attrib']['name']     = 'table';
		$d['table']['object']['attrib']['value']    = $this->dbtable;
		$d['table']['object']['attrib']['disabled'] = true;

		$d['name']['label']                        = 'Column';
		$d['name']['required']                     = true;
		$d['name']['object']['type']               = 'htmlobject_input';
		$d['name']['object']['attrib']['name']     = 'name';
		$d['name']['object']['attrib']['value']    = $this->column;

		$d['length']['label']                     = 'Length';
		$d['length']['required']                  = true;
		$d['length']['object']['type']            = 'htmlobject_input';
		$d['length']['object']['attrib']['name']  = 'length';
		$d['length']['object']['attrib']['value'] = $this->columns[$this->column]['length'];

		$type[] = array('INT','int');
		$type[] = array('TINYINT','bool');
		$type[] = array('VARCHAR','varchar');

		$selected = strtoupper($this->columns[$this->column]['type']);

		$d['type']['label']                        = 'Type';
		$d['type']['required']                     = true;
		$d['type']['object']['type']               = 'htmlobject_select';
		$d['type']['object']['attrib']['index']    = array(0,1);
		$d['type']['object']['attrib']['options']  = $type;
		$d['type']['object']['attrib']['name']     = 'type';
		$d['type']['object']['attrib']['selected'] = array($selected);

		$null[] = array('NULL','yes');
		$null[] = array('NOT NULL','no');

		($this->columns[$this->column]['null'] === 'no') ? $nul = 'NOT NULL' : $nul = 'NULL';

		$d['null']['label']                        = 'Null';
		$d['null']['required']                     = true;
		$d['null']['object']['type']               = 'htmlobject_select';
		$d['null']['object']['attrib']['index']    = array(0,1);
		$d['null']['object']['attrib']['options']  = $null;
		$d['null']['object']['attrib']['name']     = 'null';
		$d['null']['object']['attrib']['selected'] = array($nul);

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
