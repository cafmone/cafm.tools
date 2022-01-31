<?php
/**
 * query_table_copy
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_table_copy
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
		$response = $this->copy();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/query.table.copy.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			return $t;
		} else {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, 'copy', $this->message_param, $response->msg
				)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Copy
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function copy() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$f      = $form->get_request(null, true);
			$source = $this->db->handler->escape($f['source']);
			$target = $this->db->handler->escape($f['target']);
			$data   = $this->db->handler->escape($f['data']);
			$error  = $this->db->handler()->query('CREATE TABLE '.$target.' LIKE '.$source.';');
			if(!isset($error) || $error === '') {
				if($data !== '') {
					$error = $this->db->handler()->query('INSERT '.$target.' SELECT * FROM '.$source.';');
				}
			}
			if(!isset($error) || $error === '') {
				$response->msg = sprintf('added table %s', $target);
			} else {
				$response->error = $error;
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
		$form     = $response->get_form($this->actions_name, 'copy');
		$form->add('','cancel');

		$d        = array();
		$tables   = $this->controller->controller->dbtables;
		$data     = array();
		if(is_array($tables)) {
			foreach($tables as $table) {
				$data[] = array($table);
			}
		}

		$d['source']['label']                       = 'Source';
		$d['source']['required']                    = true;
		$d['source']['object']['type']              = 'htmlobject_select';
		$d['source']['object']['attrib']['index']   = array(0,0);
		$d['source']['object']['attrib']['options'] = $data;
		$d['source']['object']['attrib']['name']   = 'source';

		$d['target']['label']                    = 'Target';
		$d['target']['required']                 = true;
		$d['target']['object']['type']           = 'htmlobject_input';
		$d['target']['object']['attrib']['name'] = 'target';

		$d['data']['label']                    = 'copy data';
		$d['data']['object']['type']           = 'htmlobject_input';
		$d['data']['object']['attrib']['type'] = 'checkbox';
		$d['data']['object']['attrib']['name'] = 'data';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

}
?>
