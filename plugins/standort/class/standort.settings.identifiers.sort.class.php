<?php
/**
 * standort_settings_identifiers_sort
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_identifiers_sort
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
		$this->user       = $controller->user;
		$this->settings   = $controller->settings;
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

		$tabellen = $this->db->select($this->settings['query']['identifiers'].'', array('row','bezeichner_lang'),null,'pos');
		if(is_array($tabellen)) {
			$this->tables = $tabellen;
		}

		$response = $this->sort();
		return $response;
	}

	//--------------------------------------------
	/**
	 * Sort
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sort( ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'sort');

		#$form->add('','cancel');

		if(isset($this->tables)) {
			$options = array();
			foreach($this->tables as $v){
				$options[] = array($v['row'], $v['bezeichner_lang']);
			}
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['index']    = array(0, 1);
			$d['select']['object']['attrib']['id']       = 'plugin_select';
			$d['select']['object']['attrib']['name']     = 'index[]';
			$d['select']['object']['attrib']['options']  = $options;
			$d['select']['object']['attrib']['multiple'] = true;
			$d['select']['object']['attrib']['style']    = 'width:250px;height: 200px;';
			$d['select']['object']['attrib']['css']      = 'picklist';
	
			$form->add($d);
			$request = $form->get_request('index');
			if(!$form->get_errors() && $response->submit()) {
				if(is_array($request)) {
					foreach($request as $k => $v) {
						$error = $this->db->update(
							$this->settings['query']['identifiers'], 
							array('pos' => ($k+1)), 
							array('row' => $v));
						if($error !== '') {
							$errors[] = $error;
							break;
						}
					}

					if(!isset($errors)) {
						$msg = $this->lang['msg_sorted'];
						$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param] = implode('<br>', $errors);
					}
				}
			}
			else if($form->get_errors()) {
				$_REQUEST[$this->message_param] = join('<br>', $form->get_errors());
			}
		}

		$t = $response->html->template($this->tpldir.'/standort.settings.identifiers.sort.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($form);
		$t->add('plugin_select', 'id');
		$t->group_elements(array('param_' => 'form'));
		return $t;


	}


}
?>
