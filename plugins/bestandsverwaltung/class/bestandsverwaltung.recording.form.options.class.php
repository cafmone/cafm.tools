<?php
/**
 * bestandsverwaltung_recording_form_options
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

class bestandsverwaltung_recording_form_options
{

var $lang = array();

var $table_prefix = 'bestand_';

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param controller $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->user       = $controller->user;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {

		$sql  = 'SELECT `option` ';
		$sql .= 'FROM '.$this->table_prefix.'option2attrib ';
		$sql .= 'GROUP BY `option`';
		$used = $this->db->handler()->query($sql);

		$this->used = array();
		if(is_array($used)) {
			foreach($used as $u) {
				$this->used[] = $u['option'];
			}
		}

		$response = $this->options();
		$new = $this->get_new_form();

		$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.options.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($response, 'data');
		$t->add($this->lang['headline_new_option'], 'label');
		$t->add($new);
		$t->group_elements(array('param_' => 'form'));

		return $t;
	}

	//--------------------------------------------
	/**
	 * Options
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function options() {
		$div = $this->response->html->div();

		$sql  = 'SELECT * ';
		$sql .= 'FROM `'.$this->table_prefix.'options` ';
		//$sql .= 'ORDER BY `option`, `value` ';
		$options = $this->db->handler()->query($sql);

		if(is_array($options)) {
			foreach($options as $kat) {
				$data[$kat['option']][] = $kat;
			}
			foreach($data as $options) {
				$form = $this->get_form($options);
				$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.options.form.html');
				$t->add($this->response->html->thisfile,'thisfile');
				$t->add($options[0]['option'],'id');

				if(!in_array($options[0]['option'], $this->used)) {
					$t->add('(unused)','unused');
				} else {
					$t->add('','unused');
				}

				$t->add($form);
				$t->group_elements(array('param_' => 'form'));
				$t->group_elements(array('kat_' => 'options'));
				$div->add($t);
			}
		} else {
			$div->add($options);
		}
		return $div;
	}

	//--------------------------------------------
	/**
	 * Get Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form($options) {
		$response = $this->response->response();
		$response->id = $options[0]['option'];
		$form = $response->get_form($this->actions_name, 'options');
		$form->display_errors = false;
		$i = 0;
		foreach($options as $kat) {
			$d['kat_'.$i]['object']['type']                = 'htmlobject_input';
			$d['kat_'.$i]['object']['attrib']['type']      = 'text';
			$d['kat_'.$i]['object']['attrib']['style']     = 'margin: 0 0 8px 0;';
			$d['kat_'.$i]['object']['attrib']['name']      = 'options['.$kat['row'].']';
			$d['kat_'.$i]['object']['attrib']['value']     = $kat['value'];
			$d['kat_'.$i]['object']['attrib']['maxlength'] = 255;
			$i++;
		}

		$d['kat_id']['object']['type']                = 'htmlobject_input';
		$d['kat_id']['object']['attrib']['type']      = 'hidden';
		$d['kat_id']['object']['attrib']['name']      = 'id';
		$d['kat_id']['object']['attrib']['value']     = $response->id;
		$d['kat_id']['object']['attrib']['maxlength'] = 255;

		$d['new']['label']                         = $this->lang['label_new_option'];
		$d['new']['css']                           = 'autosize float-right';
		$d['new']['object']['type']                = 'htmlobject_input';
		$d['new']['object']['attrib']['type']      = 'text';
		$d['new']['object']['attrib']['name']      = 'new';
		$d['new']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		if(!$form->get_errors() && $response->submit()) {
			$options = $form->get_request('options', true);
			$id      = $form->get_request('id');
			$new     = $form->get_request('new');
			$errors  = array();
			$message = array();
			foreach($options as $k => $v) {
				if($v === '') {
					$error = $this->db->delete($this->table_prefix.'options',array('row' => $k));
					if($error !== '') {
						$errors[] = $error;
					} else {
						$message[] = 'Removed '.$id.' row '.$k;
					}
				} else {
					$error = $this->db->update($this->table_prefix.'options',array('value' => $v),array('row' => $k));
					if($error !== '') {
						$errors[] = $error;
					} else {
						$message[] = 'Updated '.$id.' row '.$k;
					}
				}
			}

			if(isset($new) && $new !== '') {
				$error = $this->db->insert($this->table_prefix.'options',array('value' => $new, 'option' => $id));
				if($error !== '') {
					$errors[] = $error;
				} else {
					$message[] = 'Added '.$id;
				}
			}

			if(count($errors) === 0) {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'options', $this->message_param, join('<br>', $message)
					)
				);
			} else {
				$msg = array_merge($errors, $message);
				$_REQUEST[$this->message_param] = join('<br>', $msg);
			}
		}
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
		}
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get New Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_new_form() {
		$response = $this->response->response();
		$response->id = 'new_option';
		$form = $response->get_form($this->actions_name, 'options');
		$form->display_errors = false;

		$d['new_id']['label']                         = 'ID';
		$d['new_id']['required']                      = true;
		$d['new_id']['object']['type']                = 'htmlobject_input';
		$d['new_id']['object']['attrib']['type']      = 'text';
		$d['new_id']['object']['attrib']['name']      = 'id';
		$d['new_id']['object']['attrib']['value']     = '';
		$d['new_id']['object']['attrib']['maxlength'] = 50;

		$d['new_value']['label']                         = $this->lang['label_new_option'];
		$d['new_value']['required']                      = true;
		$d['new_value']['object']['type']                = 'htmlobject_input';
		$d['new_value']['object']['attrib']['type']      = 'text';
		$d['new_value']['object']['attrib']['name']      = 'new';
		$d['new_value']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		if(!$form->get_errors() && $response->submit()) {
			$id      = $form->get_request('id');
			$new     = $form->get_request('new');
			$errors  = array();
			$message = array();

			if(isset($new) && $new !== '') {
				$error = $this->db->insert($this->table_prefix.'options',array('value' => $new, 'option' => $id));
				if($error !== '') {
					$errors[] = $error;
				} else {
					$message[] = 'Added '.$id;
				}
			}
			if(count($errors) === 0) {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'options', $this->message_param, join('<br>', $message)
					)
				);
			} else {
				$msg = array_merge($errors, $message);
				$_REQUEST[$this->message_param] = join('<br>', $msg);
			}
		}
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param]['error'] = implode('<br>', $form->get_errors());
		}
		return $form;
	}

}
?>
