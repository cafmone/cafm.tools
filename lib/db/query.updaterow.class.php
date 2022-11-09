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

class query_updaterow
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
		$this->dbtable = $controller->dbtable;
		$this->identifier = $this->response->html->request()->get($this->controller->identifier_name);
		if($this->identifier !== '') {
			$this->response->add($this->controller->identifier_name, $this->identifier);
		}
		#$this->row = $this->response->html->request()->get('row');
		#if($this->row !== '') {
		#	$this->response->add('row', $this->row);
		#}
		$this->response->add('filter',$this->response->html->request()->get('filter'));
		$this->response->add('query_table',$this->response->html->request()->get('qt'));
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
		$response = $this->updatecolumn();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/query.updaterow.html');
			$t->add('Update Row', 'label');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
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
			$f = $form->get_request('column', true);
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$errors = array();
				$msg   = array();
				foreach($f as $k => $res) {
					$ident = $form->get_static('ident['.$k.']');
					if($ident !== '') {
						$result = $this->db->update($this->dbtable, $res, array($this->row, $ident));
						if(isset($result) && $result !== '') {
							$errors[] = $result;
						} else {
							$msg[] = 'updated '.$this->row.' '.$ident;
						}
					} else {
						echo 'missing ident';
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $msg);
				} else {
					$msg = array_merge($errors, $msg);
					$response->error = join('<br>', $msg);
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
		$form     = $response->get_form($this->actions_name, 'updaterow');
		$d        = array();
		$columns   = $this->controller->__get_columns($this->dbtable);

		// set row
		foreach($columns as $k => $column) {
			if(isset($column['extra']) && $column['extra'] === 'auto_increment') {
				$this->row = $column['column'];
				break;
			}
		}

		if(
			isset($this->row) &&
			isset($this->identifier[$this->row]) &&
			$this->identifier[$this->row] !== '' &&
			is_array($this->identifier[$this->row])
		) {
			$i = 0;
			foreach($this->identifier[$this->row] as $id) {
				$result   = $this->db->select($this->dbtable, '*', array($this->row, $id));
				if(isset($result[0])) {
					$j = 0;
					foreach($result[0] as $k => $v) {
						if($k === $this->row) {
							$d['column_ident_'.$i]['label']                        = $k;
							$d['column_ident_'.$i]['css']                          = 'update_head';
							$d['column_ident_'.$i]['static']                       = true;
							$d['column_ident_'.$i]['object']['type']               = 'htmlobject_input';
							$d['column_ident_'.$i]['object']['attrib']['name']     = 'ident['.$i.']';
							$d['column_ident_'.$i]['object']['attrib']['disabled'] = true;
							$d['column_ident_'.$i]['object']['attrib']['value']    = $v;
						} else {
							$d['column_'.$k.'_'.$i]['label']                         = $k;
							$d['column_'.$k.'_'.$i]['object']['type']                = 'htmlobject_input';
							$d['column_'.$k.'_'.$i]['object']['attrib']['name']      = 'column['.$i.']['.$k.']';
							if($columns[$k]['null'] === 'no') {
								$d['column_'.$k.'_'.$i]['required'] = true;
							}
							if(stripos($columns[$k]['type'],'int') !== false ) {
								$d['column_'.$k.'_'.$i]['validate']['regex']    = '/^[0-9]+$/i';
								$d['column_'.$k.'_'.$i]['validate']['errormsg'] = sprintf('%s must be number', $k);
							} else {
								$d['column_'.$k.'_'.$i]['object']['attrib']['maxlength'] = $columns[$k]['length'];
							}
							if(isset($v)) {
								$d['column_'.$k.'_'.$i]['object']['attrib']['value'] = $this->file->remove_utf8_bom($v);
							}
						}
						$j++;
					}
				}
				$i++;
			}
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
