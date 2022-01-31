<?php
/**
 * query_column_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_column_insert
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
			$response = $this->addcolumn();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}

				$form = $this->response->get_form($this->actions_name, 'add', false);
				$description = $this->controller->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'select');

				$t = $this->response->html->template($this->tpldir.'/query.column.insert.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($description, 'description');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'add', $this->message_param, $response->msg
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
	function addcolumn() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$f      = $form->get_request(null, true);
			$name   = $this->db->handler->escape($f['name']);
			$length = $this->db->handler->escape($f['length']);
			$before = $this->db->handler->escape($f['before']);

			if(isset($before) && $before !== '') {
				reset($this->columns);
				$keys = array_flip(array_keys($this->columns));
				$prev = $keys[$before]-1;
				if($prev !== -1) {
					$keys = array_keys($this->columns);
					$append = ' AFTER `'.$keys[$prev].'`';
				} else {
					$append = ' FIRST';
				}
			} else {
				$append = '';
			}

			if(isset($f['increment']) && $f['increment'] !== '') {
				if($f['type'] === 'INT') {
					$sql  = 'ALTER TABLE `'.$this->db->db.'`.`'.$this->dbtable.'` ';
					$sql .= 'ADD `'.$name.'` '.$f['type'].'('.$length.') '.$f['null'];
					$sql .= ' auto_increment '.$append.',';
					$sql .= ' ADD PRIMARY KEY ( `'.$name.'` )';
				} else {
					$error = 'to auto_incremnet type must be int';
					$form->set_error('type', $error);
					$sql = '';
				}
			} else {
				$sql  = 'ALTER TABLE `'.$this->db->db.'`.`'.$this->dbtable.'` ';
				$sql .= 'ADD COLUMN `'.$name.'` '.$f['type'].'('.$length.') '.$f['null'].$append;
			}



			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$result = $this->db->handler()->query($sql);
				if(isset($result) && $result !== '') {
					$response->error = $result;
				} else {
					$response->msg = sprintf('added new column %s', $name);
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
		$form     = $response->get_form($this->actions_name, 'add');
		$form->add('','cancel');

		$d        = array();

		$d['name']['label']                    = 'Name';
		$d['name']['required']                 = true;
		$d['name']['object']['type']           = 'htmlobject_input';
		$d['name']['object']['attrib']['name'] = 'name';

		$d['length']['label']                    = 'Length';
		$d['length']['required']                 = true;
		$d['length']['object']['type']           = 'htmlobject_input';
		$d['length']['object']['attrib']['name'] = 'length';

		$type[] = array('INT','int');
		$type[] = array('TINYINT','bool');
		$type[] = array('VARCHAR','varchar');

		$d['type']['label']                        = 'Type';
		$d['type']['required']                     = true;
		$d['type']['object']['type']               = 'htmlobject_select';
		$d['type']['object']['attrib']['index']    = array(0,1);
		$d['type']['object']['attrib']['options']  = $type;
		$d['type']['object']['attrib']['name']     = 'type';
		$d['type']['object']['attrib']['selected'] = array('VARCHAR');

		$null[] = array('NULL','yes');
		$null[] = array('NOT NULL','no');

		$d['null']['label']                       = 'Null';
		$d['null']['required']                    = true;
		$d['null']['object']['type']              = 'htmlobject_select';
		$d['null']['object']['attrib']['index']   = array(0,1);
		$d['null']['object']['attrib']['options'] = $null;
		$d['null']['object']['attrib']['name']    = 'null';

		$increment = true;
		foreach($this->columns as $column) {
			if(isset($column['extra']) && $column['extra'] === 'auto_increment') {
				$increment = false;
				break;
			}
		}

		$d['increment'] = '';
		if($increment === true) {
			$d['increment'] = array();
			$d['increment']['label']                    = 'auto_increment';
			$d['increment']['object']['type']           = 'htmlobject_input';
			$d['increment']['object']['attrib']['type'] = 'checkbox';
			$d['increment']['object']['attrib']['name'] = 'increment';
		}

		$columns = array();
		foreach($this->columns as $k => $column) {
			$columns[] = array($k,$column['column']);
		}
		array_unshift($columns, array('',''));

		$d['before']['label']                       = 'insert before';
		$d['before']['object']['type']              = 'htmlobject_select';
		$d['before']['object']['attrib']['index']   = array(0,1);
		$d['before']['object']['attrib']['options'] = $columns;
		$d['before']['object']['attrib']['name']    = 'before';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
