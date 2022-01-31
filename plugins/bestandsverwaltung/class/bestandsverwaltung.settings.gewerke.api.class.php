<?php
/**
 * bestandsverwaltung_settings_gewerke_api
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

class bestandsverwaltung_settings_gewerke_api
{

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
	 * Gewerke
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function gewerk($visible=false) {
		if($visible === true) {
			$action = $this->response->html->request()->get('jsaction');
			$this->response->add('jsaction',$action);

			$form = $this->response->get_form($this->actions_name, 'gewerke');
			$cancel = $form->get_elements('cancel');
			$cancel->id = 'gewerkecancel';
			$cancel->type = 'button';
			$form->add($cancel,'cancel');

			$key = $this->response->html->request()->get('key');
			if($key !== '') {
				$key = $this->db->handler()->escape($key);
			}

			$columns = $this->db->handler()->columns($this->db->db, 'gewerke');

			$d = array();
			#$d['parent'] = array();
			#$d['id']     = array();
			#$d['text']   = array();

			switch( $action ) {
				case 'insert':

					$d['parent']['object']['type']           = 'htmlobject_input';
					$d['parent']['object']['attrib']['type'] = 'hidden';
					$d['parent']['object']['attrib']['name'] = 'parent';
					if($key !== '') {
						$d['parent']['object']['attrib']['value'] = $key;
					}

					$d['id']['label']                         = 'ID';
					$d['id']['required']                      = true;
					$d['id']['validate']['regex']             = '/^[A-Z0-9_]+$/';
					$d['id']['validate']['errormsg']          = sprintf('%s must be A-Z 0-9 or _', 'ID');
					$d['id']['object']['type']                = 'htmlobject_input';
					$d['id']['object']['attrib']['name']      = 'gewerk_kurz';
					if(isset($columns['gewerk_kurz']['length'])) {
						$d['id']['object']['attrib']['maxlength'] = $columns['gewerk_kurz']['length'];
					}

					$d['text']['label']                         = 'Text';
					$d['text']['required']                      = true;
					$d['text']['object']['type']                = 'htmlobject_input';
					$d['text']['object']['attrib']['name']      = 'gewerk_lang';
					if(isset($columns['gewerk_lang']['length'])) {
						$d['text']['object']['attrib']['maxlength'] = $columns['gewerk_lang']['length'];
					}
				break;
				case 'update':
					if($key !== '') {
						$r = $this->db->select('gewerke','gewerk_lang',array('gewerk_kurz', $key));
						if(isset($r[0]['gewerk_lang'])) {

							$d['parent'] = '';

							$d['id']['static']                    = true;
							$d['id']['object']['type']            = 'htmlobject_input';
							$d['id']['object']['attrib']['type']  = 'hidden';
							$d['id']['object']['attrib']['name']  = 'key';
							$d['id']['object']['attrib']['value'] = $key;

							$d['text']['label']                         = 'Text';
							$d['text']['required']                      = true;
							$d['text']['object']['type']                = 'htmlobject_input';
							$d['text']['object']['attrib']['name']      = 'gewerk_lang';
							$d['text']['object']['attrib']['value']     = $r[0]['gewerk_lang'];
							if(isset($columns['gewerk_lang']['length'])) {
								$d['text']['object']['attrib']['maxlength'] = $columns['gewerk_lang']['length'];
							}
						} else {
							$error = '<b>ERROR</b>: No Label for Key '.$key;
						}
					} else {
						$error = '<b>ERROR</b>: No Key';
					}
				break;
				case 'delete':
					if($key !== '') {
						$r = $this->db->select('gewerke','gewerk_lang',array('gewerk_kurz', $key));
						if(isset($r[0]['gewerk_lang'])) {

							$d['id']['static']                    = true;
							$d['id']['object']['type']            = 'htmlobject_input';
							$d['id']['object']['attrib']['type']  = 'hidden';
							$d['id']['object']['attrib']['name']  = 'key';
							$d['id']['object']['attrib']['value'] = $key;

							$d['text'] = '<div style="text-align: center;padding: 50px 0 0 0;">Remove '.$r[0]['gewerk_lang'].' ('.$key.') ?</div>';
						} else {
							$error = '<b>ERROR</b>: Key '.$key.' not found';
						}
					} else {
						$error = '<b>ERROR</b>: No Key';
					}
				break;
				default:
					$error = 'Nothing to do';
				break;
			}

			$form->add($d);
			$form->display_errors = false;

			if(!isset($error) && !$form->get_errors() && $this->response->submit()) {
				$f = $form->get_request();
				switch( $action ) {
					case 'insert':
						// check exists
						$r = $this->db->select('gewerke','gewerk_kurz', array('gewerk_kurz', $f['gewerk_kurz']));
						if(isset($r[0])) {
							$error = '<b>ERROR</b>: ID in use<br>';
							$form->set_error('gewerk_kurz','');
						} else {
							// id must not be a number -> array key bug
							if( !ctype_digit($f['gewerk_kurz']) ) {
								$result = $this->db->insert('gewerke', $f);
								if($result === '') {
									echo 'ok';
									exit;
								} else {
									$error = $result;
								}
							} else {
								$error = 'ID must not be a digit';
							}
						}
					break;
					case 'update':
						$result = $this->db->update('gewerke', array('gewerk_lang' => $f['gewerk_lang']), array('gewerk_kurz', $key));
						if($result === '') {
							echo 'ok';
							exit;
						} else {
							$error = $result;
						}
					break;
					case 'delete':
						$sql  = 'SELECT gewerk_kurz ';
						$sql .= 'FROM `gewerke` ';
						$sql .= 'WHERE parent=\''.$key.'\' ';
						$sql .= 'OR parent LIKE \'%,'.$key.'\' ';
						$sql .= 'OR parent LIKE \'%,'.$key.',%\' ';
						$sql .= 'OR parent LIKE \''.$key.',%\' ';
						$check = $this->db->handler()->query($sql);
						if($check === '') {
							$result = $this->db->delete('gewerk2bezeichner', array('gewerk_kurz', $key));
							if($result === '') {
								$result = $this->db->delete('gewerke', array('gewerk_kurz', $key));
								if($result === '') {
									echo 'ok';
									exit;
								} else {
									$error = $result;
								}
							} else {
								$error = $result;
							}
						} else {
							$error = '<b>ERROR</b>: '.$key.' has children';
						}
					break;
				}
			}
			else if($form->get_errors()) {
				$error = implode('<br>', $form->get_errors());
			}

			$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.api.gewerke.html');
			$t->add($this->response->html->thisfile,'thisfile');
			$t->add($form);
			$t->add('','error');
			$t->add('none','errordisplay');
			if(isset($error)) {
				$t->add($error,'error');
				$t->add('block','errordisplay');
			}
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->group_elements(array('param_' => 'form'));
			echo $t->get_string();
		}
	}

}
?>
