<?php
/**
 * formbuilder_index_edit
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
 *  along with this file (see ../../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2024, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2024, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../../LICENSE.TXT)
 * @version 1.0
 */

class formbuilder_index_edit
{

var $lang = array();
/**
* prefix for form tables
* @access public
* @var string
*/
var $table_prefix;
/**
* identifier table
* @access public
* @var string
*/
var $table_bezeichner;

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
	
		$this->table = $this->table_prefix.'index';

		$tabellen = $this->db->select($this->table, '*',null,'`pos`');
		if(is_array($tabellen)) {
			$this->tables = $tabellen;
		}
		$response = $this->update();
		return $response;
		
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
		$form     = $response->get_form($this->actions_name, 'edit');
		$form->display_errors = false;

		if(isset($this->tables)) {
		
			$i = 0;
			foreach($this->tables as $table) {
			
				$d['data_f_'.$i]['label']                     = $table['tabelle_kurz'];
				$d['data_f_'.$i]['css']                       = 'autosize float-right clearfix';
				$d['data_f_'.$i]['style']                     = 'float:right;clear:both;';
				$d['data_f_'.$i]['required']                  = true;
				$d['data_f_'.$i]['object']['type']            = 'htmlobject_input';
				$d['data_f_'.$i]['object']['attrib']['type']  = 'text';
				$d['data_f_'.$i]['object']['attrib']['name']  = 'data['.$table['tabelle_kurz'].']';
				$d['data_f_'.$i]['object']['attrib']['value'] = $table['tabelle_lang'];
				$i++;
			
			}
			$form->add($d);

			$request = $form->get_request('data');
			if(!$form->get_errors() && $response->submit()) {
				if(is_array($request)) {
					foreach($request as $k => $v) {
						$error = $this->db->update(
							$this->table, 
							array('tabelle_lang' => $v), 
							array('tabelle_kurz' => $k));
						if($error !== '') {
							$errors[] = $error;
							break;
						}
					}

					if(!isset($errors)) {
						$msg = $this->lang['msg_success'];
						$this->response->redirect($this->response->get_url($this->actions_name, 'sort', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param]['error'] = implode('<br>', $errors);
					}
				}
			}
			else if($form->get_errors()) {
				$_REQUEST[$this->message_param]['error'] = join('<br>', $form->get_errors());
			}
		}

		$t = $response->html->template($this->tpldir.'/formbuilder.index.edit.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('data_' => 'data'));
		return $t;
	}

}
?>
