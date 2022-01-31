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

class query_table_insert
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
		$this->columns     = $this->controller->controller->__get_columns($this->dbtable);
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
		if(is_array($this->columns) || $this->columns === '') {
			$response = $this->insertcolumn();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				$t = $this->response->html->template($this->tpldir.'/query.table.insert.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'insert', $this->message_param, $response->msg
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
	function insertcolumn() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$f      = $form->get_request(null, true);
			$table  = $this->db->handler->escape($f['table']);
			$name   = $this->db->handler->escape($f['name']);
			$length = $this->db->handler->escape($f['length']);
			if(isset($f['increment']) && $f['increment'] !== '') {
				if($f['type'] === 'INT') {
					$sql  = 'CREATE TABLE `'.$this->db->db.'`.`'.$table.'` (';
					$sql .= ' `'.$name.'` '.$f['type'].'('.$length.') '.$f['null'];
					$sql .= ' AUTO_INCREMENT ';
					$sql .= ' PRIMARY KEY )';
					$sql .= ' ENGINE = '.$f['engine'];
					$sql .= ' COLLATE = '.$f['collate'].';';
				} else {
					$error = 'to auto_incremnet type must be int';
					$form->set_error('type', $error);
				}
			} else {
				$sql  = 'CREATE TABLE `'.$this->db->db.'`.`'.$table.'` (';
				$sql .= ' `'.$name.'` '.$f['type'].'('.$length.') '.$f['null'].')';
				$sql .= ' ENGINE = '.$f['engine'];
				$sql .= ' COLLATE = '.$f['collate'].';';
			}

			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$result = $this->db->handler()->query($sql);
				if(isset($result) && $result !== '') {
					$response->error = $result;
				} else {
					$response->msg = sprintf('added new table %s', $table);
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
		$form     = $response->get_form($this->actions_name, 'insert');
		$form->add('','cancel');

		$d        = array();

		$d['table']['label']                    = 'Name';
		$d['table']['required']                 = true;
		$d['table']['object']['type']           = 'htmlobject_input';
		$d['table']['object']['attrib']['name'] = 'table';

		$engine[] = array('MYISAM','MyISAM');
		$engine[] = array('INNODB','InnoDB');

		$d['engine']['label']                       = 'Engine';
		$d['engine']['required']                    = true;
		$d['engine']['object']['type']              = 'htmlobject_select';
		$d['engine']['object']['attrib']['index']   = array(0,1);
		$d['engine']['object']['attrib']['options'] = $engine;
		$d['engine']['object']['attrib']['name']    = 'engine';

		#$collate[] = array('utf8_bin','utf8_bin');
		$collate[] = array('utf8_general_ci','utf8_general_ci');

		$d['collate']['label']                       = 'Collate';
		$d['collate']['required']                    = true;
		$d['collate']['object']['type']              = 'htmlobject_select';
		$d['collate']['object']['attrib']['index']   = array(0,1);
		$d['collate']['object']['attrib']['options'] = $collate;
		$d['collate']['object']['attrib']['name']    = 'collate';

		$d['name']['label']                    = 'Column';
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

		$d['increment']['label']                    = 'auto_increment';
		$d['increment']['object']['type']           = 'htmlobject_input';
		$d['increment']['object']['attrib']['type'] = 'checkbox';
		$d['increment']['object']['attrib']['name'] = 'increment';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
