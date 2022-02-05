<?php
/**
 * bestandsverwaltung_recording_form_index
 *
 * This file is part of plugin bestandsverwaltung
 *
 *  This file is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU GENERAL PUBLIC LICENSE Version 2
 *  as published by the Free Software Foundation;
 *
 *  This file is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this file (see ../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_recording_form_index
{

var $lang = array();

var $table_prefix = 'bestand_';

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

		$tabellen = $this->db->select($this->table_prefix.'index', '*',null,'pos');
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
		$form     = $response->get_form($this->actions_name, 'index');

		$form->add('','cancel');

		if(isset($this->tables)) {
			$options = array();
			foreach($this->tables as $v){
				$options[] = array($v['row'], $v['tabelle_lang']);
			}
			#$d['select']['label']                        = '';
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
							$this->table_prefix.'index', 
							array('pos' => ($k+1)), 
							array('row' => $v));
						if($error !== '') {
							$errors[] = $error;
							break;
						}
					}

					if(!isset($errors)) {
						$msg = $this->lang['msg_sorted'];
						$this->response->redirect($this->response->get_url($this->actions_name, 'index', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param] = implode('<br>', $errors);
					}
				}
			}
			else if($form->get_errors()) {
				$_REQUEST[$this->message_param] = join('<br>', $form->get_errors());
			}
		}

		$t = $response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.index.sort.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($form);
		$t->add('plugin_select', 'id');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update( ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'index');

		$form->add('','cancel');

		if(isset($this->tables)) {
			$options = array();
			foreach($this->tables as $v){
				$options[] = array($v['row'], $v['tabelle_lang']);
			}
			#$d['select']['label']                        = '';
			#$d['select']['object']['type']               = 'htmlobject_select';
			#$d['select']['object']['attrib']['index']    = array(0, 1);
			#$d['select']['object']['attrib']['id']       = 'plugin_select';
			#$d['select']['object']['attrib']['name']     = 'index[]';
			#$d['select']['object']['attrib']['options']  = $options;
			#$d['select']['object']['attrib']['multiple'] = true;
			#$d['select']['object']['attrib']['style']    = 'width:250px;height: 200px;';
			#$d['select']['object']['attrib']['css']      = 'picklist';
	
			$form->add($d);
			$request = $form->get_request('index');
			if(!$form->get_errors() && $response->submit()) {
				if(is_array($request)) {
					foreach($request as $k => $v) {
						$error = $this->db->update(
							$this->table_prefix.'index', 
							array('pos' => ($k+1)), 
							array('row' => $v));
						if($error !== '') {
							$errors[] = $error;
							break;
						}
					}

					if(!isset($errors)) {
						$msg = $this->lang['msg_sorted'];
						$this->response->redirect($this->response->get_url($this->actions_name, 'index', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param] = implode('<br>', $errors);
					}
				}
			}
			else if($form->get_errors()) {
				$_REQUEST[$this->message_param] = join('<br>', $form->get_errors());
			}
		}

		$t = $response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.index.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($form);
		$t->add('plugin_select', 'id');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
