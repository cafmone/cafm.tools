<?php
/**
 * standort_settings_inventory_identifiers
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_identifiers
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
		$this->settings   = $controller->settings;

		$this->identifiers  = $this->db->select($this->settings['query']['table'].'_identifiers', array('bezeichner_kurz','bezeichner_lang'));
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

		if($this->response->html->request()->get('standort_select') !== '') {
			$this->response->add('standort_select',$this->response->html->request()->get('standort_select'));
		}

		$response = $this->update();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'/standort.settings.inventory.identifiers.html');
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
	 * Update
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		if(!isset($response->msg)) {
			$form     = $response->form;
			if(!$form->get_errors() && $response->submit()) {
				$request = $form->get_request($this->controller->identifier_name);
				$identifier = $form->get_request('identifier');
				$errors  = array();
				$message = array();

				if($request !== '' && $identifier !== '') {
					foreach($request as $key => $id) {
						$error = $this->db->update(
							$this->settings['query']['table'],
							array('bezeichner_kurz' => $identifier),
							array('id' => $id)
						);
						if($error !== '') {
							$errors[] = $error; 
						} else {
							$message[] = 'Successfully updated ID '.$id;
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
		$form     = $response->get_form($this->actions_name, 'identifiers');
		$request  = $response->html->request()->get($this->controller->identifier_name);

		if( $request !== '' ) {
			$i = 0;
			foreach($request as $id) {
				$d['param_f'.$i]['label']                       = $id;
				$d['param_f'.$i]['css']                         = 'autosize';
				$d['param_f'.$i]['style']                       = 'float:right; clear:both;';
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->controller->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			if(is_array($this->identifiers)) {
				$options = $this->identifiers;
				array_unshift($options, array('bezeichner_kurz' => '', 'bezeichner_lang' => ''));
				
				$d['identifiers']['label']                       = 'New Identifier';
				$d['identifiers']['css']                         = 'autosize';
				$d['identifiers']['object']['type']              = 'htmlobject_select';
				$d['identifiers']['object']['attrib']['index']   = array('bezeichner_kurz', 'bezeichner_lang');
				$d['identifiers']['object']['attrib']['name']    = 'identifier';
				$d['identifiers']['object']['attrib']['options'] = $options;
			} else {
				$d['identifiers'] = '';
			}

			$form->add($d);
			$form->display_errors = false;
			$response->form = $form;

			return $response;
		} else {
			$response->msg = '';
			return $response;
		}
	}

}
?>
