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

class query_delrow
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
		$response = $this->delcolum();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/query.delrow.html');
			$t->add('Delete Row', 'label');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
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
	function delcolum() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$request = $this->response->html->request()->get($this->controller->identifier_name);
			$field   = key($request);
			$errors  = array();
			$message = array();
			if($field === 'AllRows') {
				$error = $this->db->delete($this->dbtable);
				if($error === '') {
					$message[] = sprintf('success', '');
				} else {
					$errors[] = $error;
				}
			} else {
				foreach($request[$field] as $key => $id) {
					$error = $this->db->delete($this->dbtable, array($field, $id));
					if($error === '') {
						$form->remove($this->controller->identifier_name.'['.$key.']');
						$message[] = sprintf('success', $id);
					} else {
						$errors[] = $error;
					}
				}
			}
			if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
			} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
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
		$form     = $response->get_form($this->actions_name, 'delrow');
		$request  = $response->html->request()->get($this->controller->identifier_name);

		if( $request !== '' ) {
			$field = key($request);
			if($field === 'AllRows') {
					$div = $this->response->html->div();
					$div->css = 'htmlobject_box';
					$div->style = 'text-align: center;padding: 10px 0 30px 0;';
					$div->add(sprintf('Are you sure to delete all rows in %s?', $this->dbtable));
					$d['param_f0']['object'] = $div;

					$d['param_f1']['object']['type']              = 'htmlobject_input';
					$d['param_f1']['object']['attrib']['type']    = 'hidden';
					$d['param_f1']['object']['attrib']['name']    = $this->controller->identifier_name.'[AllRows]';
					$d['param_f1']['object']['attrib']['value']   = 'all';

			} else {	 
				$i = 0;
				foreach($request[$field] as $id) {
					$d['param_f'.$i]['label']                       = $id;
					$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
					$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
					$d['param_f'.$i]['object']['attrib']['name']    = $this->controller->identifier_name.'['.$field.']['.$i.']';
					$d['param_f'.$i]['object']['attrib']['value']   = $id;
					$d['param_f'.$i]['object']['attrib']['checked'] = true;
					$i++;
				}
			}
			$form->add($d);
			$form->display_errors = false;
			$response->form = $form;
			return $response;
		}
	}

}
?>
