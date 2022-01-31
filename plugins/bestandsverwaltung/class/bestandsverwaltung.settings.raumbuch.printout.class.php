<?php
/**
 * bestandsverwaltung_settings_raumbuch_printout
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

class bestandsverwaltung_settings_raumbuch_printout
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
		$this->response   = $controller->response;
		$this->settings   = $controller->settings;
		$this->fields     = array();
		$this->datadir    = PROFILESDIR.'bestand/';
		$this->tables     = $this->db->select('raumbuch_index',array('tabelle_kurz','tabelle_lang'));
		$this->raumbuch   = $controller->raumbuch;

		$id = $this->response->html->request()->get('id');
		if( $id !== '') {
			$this->id = $id;
			$this->response->add('id', $this->id);
		}

		$ebene = $this->response->html->request()->get('bezeichner');
		if( $ebene !== '') {
			$this->ebene = $ebene;
			$this->response->add('bezeichner', $this->ebene);
		}

		## TODO
		#$url  = $this->response->html->thisfile;
		$url  = '';
		$url .= '?index_action=plugin';
		$url .= '&index_action_plugin=bestandsverwaltung';
		$url .= '&bestandsverwaltung_action=download';
		$url .= '&path=/bestand/raumbuch/';
		$this->href_download  = $url;

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
		if(!isset($this->id)) {
			$response = $this->insert();
		} else {
			$response = $this->update();
		}

		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.printout.html');
			$t->add($this->response->html->thisfile,'thisfile');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->add($response, 'content');
			#$t->add('','files');
			$t->group_elements(array('param_' => 'form'));
			$t->group_elements(array('merkmal_' => 'merkmale'));
			return $t;
		} else {
			$id = '';
			if(isset($response->id)) {
				$id = '&id='.$response->id;
			}
			$this->controller->response->redirect(
					$this->controller->response->get_url(
					$this->controller->actions_name, 'update', $this->controller->message_param, $response->msg
				).$id
			);
		}
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
		#$form     = $response->form;
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
		$str = '';

		if(isset($this->id)) {
			$parents = $this->raumbuch->parents($this->id);
			foreach($parents as $parent) {

				// Template
				$template = '';

				$form = $response->get_form($this->actions_name, 'update');
				$form->add('','cancel');
				$form->add('','submit');
				$values = $this->db->select('raumbuch', array('row','id','bezeichner_kurz','tabelle','parent_id','merkmal_kurz','wert'), array('id', $parent));
				if(is_array($values)) {
					$fields['parent'] = $values[0]['parent_id'];
					$fields['bezeichner'] = $values[0]['bezeichner_kurz'];
					foreach ($values as $v) {
						if(isset($v['tabelle']) && $v['tabelle'] !== '') {
							$fields[$v['tabelle']][$v['merkmal_kurz']]['row']  = $v['row'];
							$fields[$v['tabelle']][$v['merkmal_kurz']]['wert'] = $v['wert'];
						} else {
							$fields[$v['merkmal_kurz']]['wert'] = $v['wert'];
							$fields[$v['merkmal_kurz']]['row']  = $v['row'];
						}
					}
					$ebene = $fields['bezeichner'];
				} else {
					$fields['NAME']['wert'] = $values;
				}

				$d = array();
				if(isset($ebene) && $ebene !== '') {

					// Template
					#$template = $this->datadir.'templates/raumbuch-'.$ebene.'.html';
					#if(!$this->file->exists($template)) {
						$template = '';
					#}

					// ID
					$h = $this->response->html->box();
					$h->label = 'ID:';
					$h->css = 'htmlobject_box printout';
					$h->add($values[0]['id']);
					$d['id']['object'] = $h;

					// Label 
					$label = $this->db->select('raumbuch_bezeichner','bezeichner_lang',array('bezeichner_kurz', $ebene));
					if(isset($label[0]['bezeichner_lang'])) {
						$d['label'] = $label[0]['bezeichner_lang'];
					} else {
						$d['label'] = '';
					}

					$result = array();
					foreach($this->tables as $table) {
						$result[$table['tabelle_kurz']]['data'] = $this->db->select($this->controller->table_prefix.$table['tabelle_kurz'],'*',array('bezeichner_kurz', $ebene));
						$result[$table['tabelle_kurz']]['title'] = $table['tabelle_lang'];
					}

					if(is_array($result) && count($result) > 0) {
						foreach ( $result as $k => $v ) {
							if(is_array($v) && isset($v['data']) && $v['data'] !== '') {
								$i = 0;
								foreach ( $v['data'] as $r ) {
									if(
										isset($fields[$k]) && 
										array_key_exists($r['merkmal_kurz'], $fields[$k])
									) {
										$div = $this->response->html->box();
										$div->css = 'htmlobject_box printout';
										$div->label = $r['merkmal_lang'].':';
										$div->add($fields[$k][$r['merkmal_kurz']]['wert']);
										if($template !== '') {
											$d[$k.'_'.$r['merkmal_kurz']]['object'] = $div;
										} else {
											$d[$k.'_'.'merkmal_'.$k.'_'.$i]['object'] = $div;
										}
									} else {
										if($template !== '') {
											$d[$k.'_'.$r['merkmal_kurz']] = '';
										}
									}
									$i++;
								}
							} else {
								if(is_string($v) && $v !== '') {
									if($template !== '') {
										$d[$k.'_'.$r['merkmal_kurz']] = $v;
									} else {
										$d[$k.'_'.'merkmal_'.$k.'_0'] = $v;
									}
								} else {
									if($template !== '') {
										$d[$k.'_'.$r['merkmal_kurz']] = '';
									} else {
										$d[$k.'_'.'merkmal_'.$k.'_0'] = '';
									}
								}
							}
						}
					}
				} else {
					$d['bezeichner'] = 'Error: Missing Ebene';
					$d['parent'] = '';
					$d['id'] = '';
					$d['name'] = '';
					$d['merkmale'] = '';
				}

				$form->display_errors = false;
				$form->add($d);

				// Template
				if(isset($template) && $template !== '') {
					$t = $response->html->template($template);
					$t->add($fields['NAME']['wert'], 'name');
					$t->add($form);

					$str .= $t->get_string();
				} else {
					$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.printout.ebene.html');
					$t->add($response->html->thisfile,'thisfile');
					$t->add($form);
					$t->add($fields['NAME']['wert'], 'name');

					$files = '';
					if(isset($values[0]) && isset($values[0]['id'])) {
						$f = $this->file->get_files($this->datadir.'/raumbuch/'.$values[0]['id']);
						if(is_array($f) && count($f) > 0) {
							$files = '<div style="text-align:center;margin:10px;">';
							foreach($f as $file) {
								$a = $this->response->html->a();
								$a->href = $this->href_download.$values[0]['id'].'/'.$file['name'];
								$a->label = $file['name'];
								$files .= $a->get_string().'<br>';
							}
							$files .= '</div>';
						}
					}

					$t->add($files,'files');
					$t->group_elements(array('merkmal_' => 'merkmale'));

					$str .= $t->get_string();
				}
			}
		}
		return $str;
	}

}
?>
