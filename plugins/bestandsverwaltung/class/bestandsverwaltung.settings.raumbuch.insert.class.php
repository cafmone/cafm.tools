<?php
/**
 * bestandsverwaltung_settings_raumbuch_insert
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

class bestandsverwaltung_settings_raumbuch_insert
{
/**
* translation
* @access public
* @var string
*/
var $lang = array();
/**
* hide empty fields
* @access public
* @var string
*/
var $hide_empty = false;

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
		$this->controller = $controller;
		$this->user       = $controller->user->get();
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response->response();
		$this->settings   = $controller->settings;
		$this->fields     = array();
		$this->datadir    = PROFILESDIR.'bestand/raumbuch/';
		$this->tables     = $this->db->select('raumbuch_index',array('tabelle_kurz','tabelle_lang'));
		$this->raumbuch   = $controller->raumbuch;

		$id = $this->response->html->request()->get('id');
		if( $id !== '') {
			$this->id = $id;
			$this->response->add('id', $this->id);
			$values = $this->db->select('raumbuch', array('row','bezeichner_kurz','tabelle','parent_id','merkmal_kurz','wert'), array('id', $this->id));
			if(is_array($values)) {
				$this->fields['parent'] = $values[0]['parent_id'];
				$this->fields['bezeichner'] = $values[0]['bezeichner_kurz'];
				foreach ($values as $v) {
					if(isset($v['tabelle']) && $v['tabelle'] !== '') {
						$this->fields[$v['tabelle']][$v['merkmal_kurz']]['row']     = $v['row'];
						$this->fields[$v['tabelle']][$v['merkmal_kurz']]['wert']    = $v['wert'];
					} else {
						$this->fields[$v['merkmal_kurz']]['wert'] = $v['wert'];
						$this->fields[$v['merkmal_kurz']]['row']  = $v['row'];
					}
				}
				$this->ebene = $this->fields['bezeichner'];
			}
		}

		$ebene = $this->response->html->request()->get('bezeichner');
		if( $ebene !== '') {
			$this->ebene = $ebene;
			$this->response->add('bezeichner', $this->ebene);
		}

		$parent = $this->response->html->request()->get('parent');
		if( $parent !== '') {
			$this->parent = $parent;
			$this->response->add('parent', $this->parent);
		}

		$table = $this->response->html->request()->get('standort_select');
		if( $table !== '') {
			$this->response->add('standort_select', $table);
		}
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
	function action() {

		// switch insert / update
		if(isset($this->id)) {
			$response = $this->update();
		} else {
			if(isset($this->ebene)) {
				$response = $this->insert();
			} else {
				$response = $this->prolog();
			}
		}

		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.insert.html');
			$t->add($response->html->thisfile,'thisfile');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			$t->group_elements(array('merkmal_' => 'merkmale'));

			if(isset($this->id)) {
				$files = '';
				$upload = '';
				if($this->file->exists($this->datadir)){
					// build tabs
					$content[0]['label']   = $this->lang['insert']['tab_attribs'];
					$content[0]['value']   = $t;
					$content[0]['target']  = '#raumbuch_insert_tab0';
					$content[0]['request'] = null;
					$content[0]['onclick'] = true;
					$content[0]['active']  = true;
					$content[0]['id']  = 'Id1';

					// handle files
					if(method_exists($this->controller, 'files')) {
						$files = $this->controller->files(true);
						$files->message_param = $this->message_param;

						$str = '<div class="files noprint fieldset">';
						$str .= $files->action()->get_string();
						$str .= '</div>';

						$content[1]['label']   = $this->lang['insert']['tab_files'];
						$content[1]['value']   = $str;
						$content[1]['target']  = '#raumbuch_insert_tab1';
						$content[1]['request'] = null;
						$content[1]['onclick'] = true;
						$content[1]['id']  = 'Id2';
					}
					if(isset($_REQUEST[$files->message_param])) {
						$_REQUEST[$this->message_param] = $_REQUEST[$files->message_param];
					}
				}

				$tab = $this->response->html->tabmenu('raumbuch_insert_tab');
				$tab->message_param = $this->message_param;
				$tab->css = 'tabs noprint';
				$tab->auto_tab = false;
				$tab->add($content);

				$out = $this->response->html->div();
				$out->add('<div id="recording_insert_top" style="margin:-15px 0 15px 0; clear:both;" class="floatbreaker">&#160;</div>');
				$out->add($tab);

			} else {

				// build tabs
				$content[0]['label']   = '&#160;';
				$content[0]['value']   = $t;
				$content[0]['target']  = '#raumbuch_insert_tab0';
				$content[0]['request'] = null;
				$content[0]['onclick'] = true;
				$content[0]['active']  = true;
				$content[0]['id']      = 'Id1';
				$content[0]['hidden']  = true;

				$tab = $this->response->html->tabmenu('raumbuch_insert_tab');
				$tab->message_param = $this->message_param;
				$tab->css = 'tabs noprint';
				$tab->boxcss = 'tab-content noborder';
				$tab->auto_tab = false;
				$tab->add($content);

				$out = $tab;
			}
			return $out;
		} else {
			$id = '';
			if(isset($response->id)) {
				$id = '&id='.$response->id;
			}
			$this->controller->response->redirect(
					$this->controller->response->get_url(
					$this->controller->actions_name, 'update', $this->message_param, $response->msg
				).$id
			);
		}
	}


	//--------------------------------------------
	/**
	 * Prolog
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function prolog() {
		$response = $this->response->response();
		$response->id = 'insert_prolog';
		$form = $response->get_form($this->actions_name, 'insert');

		$options = $this->raumbuch->levels();
		array_unshift($options, array('id' => '', 'label' => ''));

		$d['bezeichner']['label']                        = $this->lang['insert']['label_identifier'];
		$d['bezeichner']['style']                        = 'margin: 30px 0 20px 0;';
		$d['bezeichner']['required']                     = true;
		$d['bezeichner']['object']['type']               = 'htmlobject_select';
		$d['bezeichner']['object']['attrib']['index']    = array('id','label');
		$d['bezeichner']['object']['attrib']['options']  = $options;
		$d['bezeichner']['object']['attrib']['name']     = 'bezeichner';

		$d['id'] = '';
		$d['parent'] = '';
		$d['name'] = '';
		$d['merkmale'] = '';
		$d['bottomlink'] = '';
		$d['toplink'] = '';
		$d['none'] = 'none';

		$form->display_errors = false;
		$form->add($d);

		if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		else if($response->cancel()) {
			$this->controller->response->redirect(
					$this->controller->response->get_url(
					$this->controller->actions_name, 'select'
				)
			);
		}
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			foreach($this->tables as $table) {
				$f[$table['tabelle_kurz']] = $form->get_request($table['tabelle_kurz']);
			}
			$user = $this->controller->user->get();
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				// static
				$d['id']              = $form->get_request('newid');
				$d['bezeichner_kurz'] = $form->get_static('bezeichner');
				$d['parent_id']       = $form->get_static('parent');

				// dynamic
				$d['tabelle']      = NULL;
				$d['merkmal_kurz'] = 'NAME';
				$d['wert']         = $form->get_request('NAME');

				$check = $this->db->select('raumbuch', array('row'), array('id', $d['id']));
				if($check === '') {
					$error = $this->db->insert('raumbuch', $d);
					if($error === '') {
						foreach($f as $key => $value) {
							if(is_array($value)) {
								foreach($value as $k => $v) {
									$d['tabelle'] = $key;
									$d['merkmal_kurz'] = $k;
									$d['wert'] = $v;
									$error = $this->db->insert('raumbuch', $d);
								}
							}
						}
						if($error === '') {
							$response->id  = $d['id'];
							$response->msg = sprintf($this->lang['insert']['msg_added'], $d['id']);
						} else {
							$response->error = $error;
						}
					} else {
						$response->error = $error;
					}
				} else {
					$response->error = sprintf($this->lang['insert']['error_exists'], $d['id']);
					$form->set_error('newid',$response->error); 
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
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			foreach($this->tables as $table) {
				$r[$table['tabelle_kurz']] = $form->get_request($table['tabelle_kurz'], true);
			}
			$user   = $this->controller->user->get();
			$parent = $form->get_static('parent');
			$errors = array();
			foreach($r as $key => $value) {
				if($value !== '') {
					$error = '';
					foreach($value as $k => $v) {
						if(isset($this->fields[$key]) && array_key_exists($k, $this->fields[$key])) {
							// formfield set
							if($this->fields[$key][$k]['wert'] !== $v) {
								// changed
								if($v === '') {
									// delete
									$error = $this->db->delete(
										'raumbuch',
										array('row',$this->fields[$key][$k]['row'])
									);
								} else {
									// update
									$error = $this->db->update(
										'raumbuch',
										array('wert' => $v),
										array('row',$this->fields[$key][$k]['row'])
									);	
								}
							} else {
								// unchanged
								// do nothing
								// echo 'unchanged '.$k.'-'.$v.'<br>';
							}
						} else {
							if($v !== '') {
								// formfield new
								$d['id']              = $this->id;
								$d['bezeichner_kurz'] = $form->get_static('bezeichner');
								$d['parent_id']       = $parent;
								$d['tabelle']         = $key;
								$d['merkmal_kurz']    = $k;
								$d['wert']            = $v;
								$error = $this->db->insert('raumbuch', $d);
							}
						}
						if($error !== '') {
							$errors[] = $error;
						}
					}
				}
			}

			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				// change parent
#				if(isset($this->fields['parent']) && $this->fields['parent'] !== $response->html->request()->get('parent')) {
#					$error = $this->db->update(
#						'raumbuch',
#						array('parent_id' => $response->html->request()->get('parent')),
#						array('id',$this->id)
#					);
#				}
#				if(isset($error) && $error !== '') {
#					$response->error = $error;
#				} else {
					// change Name
					if(isset($this->fields['NAME']['wert']) && $this->fields['NAME']['wert'] !== $response->html->request()->get('NAME')) {
						$error = $this->db->update(
							'raumbuch',
							array('wert' => $response->html->request()->get('NAME')),
							array('id' => $this->id, 'row' => $this->fields['NAME']['row'], 'merkmal_kurz' => 'NAME')
						);
					}
					if(isset($error) && $error !== '') {
						$response->error = $error;
					} else {
						$response->id  = $this->id;
						$response->msg = sprintf($this->lang['insert']['msg_updated'], $this->id);
					}
				}
			}
#		}
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
		$fields   = $this->fields;
		$columns  = $this->db->handler()->columns($this->db->db, 'raumbuch');

		$d = array();
		$d['toplink'] = '<div class="noprint" style="display: inline;text-align:right;margin:0 5px 0 10px;"><a id="insertbottom" href="#top">&#9650;</a></div>';
		$d['bottomlink'] = '<div class="noprint" style="position: absolute; top: 2px; right: 10px;"><a href="#insertbottom">&#9660;</a></div>';
		$d['float'] = 'none';

		if(isset($this->id)) {
			$form = $response->get_form($this->actions_name, 'update');
		} else {
			$form = $response->get_form($this->actions_name, 'insert');
		}

		if(!isset($this->id)) {
			$d['id']['label']                        = $this->lang['insert']['label_id'];
			$d['id']['required']                     = true;
			$d['id']['object']['type']               = 'htmlobject_input';
			$d['id']['object']['attrib']['name']     = 'newid';
			$d['id']['object']['attrib']['value']    = uniqid('s');
			if(isset($columns['id']['length'])) {
				$d['id']['object']['attrib']['maxlength'] = $columns['id']['length'];
			}
		} else {
			$d['id']['label']                        = $this->lang['insert']['label_id'];
			$d['id']['static']                       = true;
			$d['id']['object']['type']               = 'htmlobject_input';
			$d['id']['object']['attrib']['name']     = 'idxxx';
			$d['id']['object']['attrib']['disabled'] = true;
			$d['id']['object']['attrib']['value']    = $this->id;
		}

		if(isset($this->ebene) && $this->ebene !== '') {
			$d['bezeichner']['label']                        = $this->lang['insert']['label_identifier'];
			$d['bezeichner']['static']                       = true;
			$d['bezeichner']['object']['type']               = 'htmlobject_input';
			$d['bezeichner']['object']['attrib']['name']     = 'bezeichner';
			$d['bezeichner']['object']['attrib']['disabled'] = true;
			$d['bezeichner']['object']['attrib']['value']    = $this->ebene;

			$d['name']['label']                    = $this->lang['insert']['label_name'];
			$d['name']['required']                 = true;
			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['name'] = 'NAME';
			if(array_key_exists('NAME', $fields)) {
				$d['name']['object']['attrib']['value'] = $fields['NAME']['wert'];
			}
			if(isset($columns['wert']['length'])) {
				$d['name']['object']['attrib']['maxlength'] = $columns['wert']['length'];
			}

			if(isset($this->parent) && $this->parent !== '') {
				$d['parent']['label']                        = $this->lang['insert']['label_parent'];
				$d['parent']['static']                       = true;
				$d['parent']['object']['type']               = 'htmlobject_input';
				$d['parent']['object']['attrib']['name']     = 'parent';
				$d['parent']['object']['attrib']['value']    = $this->parent;
				$d['parent']['object']['attrib']['disabled'] = true;
			} 
			else if(array_key_exists('parent', $fields)) {
				$d['parent']['label']                        = $this->lang['insert']['label_parent'];
				$d['parent']['static']                       = true;
				$d['parent']['object']['type']               = 'htmlobject_input';
				$d['parent']['object']['attrib']['name']     = 'parent';
				$d['parent']['object']['attrib']['value']    = $fields['parent'];
				$d['parent']['object']['attrib']['disabled'] = true;
			} else {
				$d['parent'] = '';
			}

/*
			if(isset($this->id)) {
				$div = $response->html->box();
				$div->css   = 'htmlobject_box';
				$div->label = '<label>ID</label>';
				$div->add('<input type="text" value="'.$this->id.'">');
				$d['id']['object'] = $div;
			} else {
				$d['id'] = '';
			}
*/

			$result = array();
			foreach($this->tables as $table) {
				$result[$table['tabelle_kurz']]['data'] = $this->db->select($this->controller->table_prefix.$table['tabelle_kurz'],'*',array('bezeichner_kurz', $this->ebene));
				$result[$table['tabelle_kurz']]['title'] = $table['tabelle_lang'];
			}
			if(is_array($result) && count($result) > 0) {
				foreach ( $result as $k => $v ) {
					if(is_array($v) && isset($v['data']) && is_array($v['data']) && $v['data'] !== '') {

						$h = $this->response->html->customtag('h3');

						$h->add($v['title']);
						$d['merkmal_'.$k.'_head']['object'] = $h;
						$i = 0;
						foreach ( $v['data'] as $r ) {
							switch($r['datentyp']) {
								default:
								case '':
								case 'text':
									$d['merkmal_'.$k.'_'.$i]['label']                     = $r['merkmal_lang'];
									$d['merkmal_'.$k.'_'.$i]['object']['type']            = 'htmlobject_input';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['name']  = $k.'['.$r['merkmal_kurz'].']';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['value'] = '';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['title'] = $r['merkmal_kurz'];
									if(
										isset($fields[$k]) && 
										array_key_exists($r['merkmal_kurz'], $fields[$k])
									) {
										$d['merkmal_'.$k.'_'.$i]['object']['attrib']['value'] = $fields[$k][$r['merkmal_kurz']]['wert'];
									}
									else if($this->hide_empty === true) {
										$d['merkmal_'.$k.'_'.$i] = '';
									}
								break;
								case 'bool':
									$d['merkmal_'.$k.'_'.$i]['label']                     = $r['merkmal_lang'];
									$d['merkmal_'.$k.'_'.$i]['object']['type']            = 'htmlobject_input';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['type']  = 'checkbox';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['name']  = $k.'['.$r['merkmal_kurz'].']';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['value'] = '1';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['title'] = $r['merkmal_kurz'];
									if(
										isset($fields[$k]) && 
										array_key_exists($r['merkmal_kurz'], $fields[$k])
									) {
										$d['merkmal_'.$k.'_'.$i]['object']['attrib']['checked'] = true;
									}
									else if($this->hide_empty === true) {
										$d['merkmal_'.$k.'_'.$i] = '';
									}
								break;
								case 'int':
								case 'integer':
									$d['merkmal_'.$k.'_'.$i]['label']                     = $r['merkmal_lang'];
									$d['merkmal_'.$k.'_'.$i]['validate']['regex']         = '/^[0-9]+$/i';
									$d['merkmal_'.$k.'_'.$i]['validate']['errormsg']      = sprintf('%s must be number', $r['merkmal_lang']);
									$d['merkmal_'.$k.'_'.$i]['object']['type']            = 'htmlobject_input';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['name']  = $k.'['.$r['merkmal_kurz'].']';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['title'] = $r['merkmal_kurz'];
									if(
										isset($fields[$k]) && 
										array_key_exists($r['merkmal_kurz'], $fields[$k])
									) {
										$d['merkmal_'.$k.'_'.$i]['object']['attrib']['value'] = $fields[$k][$r['merkmal_kurz']]['wert'];
									}
									else if($this->hide_empty === true) {
										$d['merkmal_'.$k.'_'.$i] = '';
									}
								break;
								case 'katalog':
									$merkmal = $this->db->handler()->escape($r['merkmal_kurz']);
									$where   = '`bezeichner_kurz`=\''.$r['bezeichner_kurz'].'\' AND `merkmal_kurz`=\''.$merkmal.'\'';
									$options = $this->db->select('raumbuch_katalog','wert', $where);
									if($options === ''){
										$where   = '`merkmal_kurz`=\''.$merkmal.'\'';
										$options = $this->db->select('raumbuch_katalog','wert', $where);
									}
									$options = $this->db->select('raumbuch_katalog','wert', array('merkmal_kurz', $r['merkmal_kurz']));
									$d['merkmal_'.$k.'_'.$i]['label']                     = $r['merkmal_lang'];
									$d['merkmal_'.$k.'_'.$i]['object']['type']            = 'htmlobject_select';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['index'] = array('wert','wert');
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['name']  = $k.'['.$r['merkmal_kurz'].']';
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['options'] = $options;
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['title'] = $r['merkmal_kurz'];
									if(
										isset($fields[$k]) && 
										array_key_exists($r['merkmal_kurz'], $fields[$k])
									) {
										$d['merkmal_'.$k.'_'.$i]['object']['attrib']['selected'] = array($fields[$k][$r['merkmal_kurz']]['wert']);
									}
									else if($this->hide_empty === true) {
										$d['merkmal_'.$k.'_'.$i] = '';
									}
								break;
							}

							if($d['merkmal_'.$k.'_'.$i] !== '') {
								if(isset($r['pflichtfeld']) && $r['pflichtfeld'] == 1) {
									$d['merkmal_'.$k.'_'.$i]['required'] = true;
								}
								if(isset($r['minimum']) && $r['minimum'] !== '') {
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['minlength'] = $r['minimum'];
								}
								if(isset($r['maximum']) && $r['maximum'] !== '') {
									$d['merkmal_'.$k.'_'.$i]['object']['attrib']['maxlength'] = $r['maximum'];
								}
							}
							$i++;
						}
					} else {
						if(is_string($v) && $v !== '') {
							$d['merkmal_'.$k.'_0'] = $v;
						} else {
							$d['merkmal_'.$k.'_0'] = '';
						}
					}
				}
			}
		} else {
			$d['bezeichner'] = 'Error: Missing Bezeichner';
			$d['parent'] = '';
			$d['id'] = '';
			$d['name'] = '';
			$d['merkmale'] = '';
			$d['bottomlink'] = '';
			$d['toplink'] = '';
			$d['float'] = 'none';
		}
	
		$form->display_errors = false;
		$form->add($d);

		$response->form = $form;
		return $response;
	}

}
?>
