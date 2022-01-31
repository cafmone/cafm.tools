<?php
/**
 * query_table_delete
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_table_delete
{

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param controller $phppublisher
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;

		$this->dbtable = $controller->dbtable;
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
		$response = $this->delcolum();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/query.table.delete.html');
			$t->add($this->response->html->thisfile, 'thisfile');
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

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function delcolum() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$f = $form->get_request(null, true);
			$confirm = $response->html->request()->get('confirm',true);
			if(isset($confirm) && $confirm === 'true' && $f['table'] !== '') {
				$table = $this->db->handler->escape($f['table']);
				if(isset($error) && $error !== '') {
					$response->error = $error;
				} else {
					$sql  = 'DROP TABLE `'.$this->db->db.'`.`'.$table.'` ';
					$result = $this->db->handler()->query($sql);
					if(isset($result) && $result !== '') {
						$response->error = $result;
					} else {
						// unset dbtable if table has been removed
						if($table === $this->dbtable) {
							$this->response->add('dbtable','');
						}
						$response->msg = sprintf('removed table %s', $table);
					}
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
		$form->add('','cancel');

		$d        = array();
		$tables   = $this->controller->controller->dbtables;
		$data     = array();
		$data[]   = array('');
		if(is_array($tables)) {
			foreach($tables as $table) {
				$data[] = array($table);
			}
		}

		$d['tables']['label']                       = 'Table';
		$d['tables']['required']                    = true;
		$d['tables']['object']['type']              = 'htmlobject_select';
		$d['tables']['object']['attrib']['index']   = array(0,0);
		$d['tables']['object']['attrib']['options'] = $data;
		$d['tables']['object']['attrib']['name']    = 'table';

		$d['confirm'] = '';
		$d['text'] = '';

		$form->add($d);

		$confirm = $form->get_static('confirm');
		if(!$form->get_errors() && $response->submit() && !isset($confirm) ) {
			unset($d);

			$d['tables']['object']['type']           = 'htmlobject_input';
			$d['tables']['object']['attrib']['type'] = 'hidden';
			$d['tables']['object']['attrib']['name'] = 'table';

			$d['confirm']['static']                    = true;
			$d['confirm']['object']['type']            = 'htmlobject_input';
			$d['confirm']['object']['attrib']['type']  = 'hidden';
			$d['confirm']['object']['attrib']['name']  = 'confirm';
			$d['confirm']['object']['attrib']['value'] = 'true';

			$d['text']  = '<div style="text-align: center;">Do you really want to delete '.$form->get_request('table').'?</div>';

			$form->add($d);
		}

		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
