<?php
/**
 * bestandsverwaltung_recording_insert
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
 *  Copyright (c) 2015-2019, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2019, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_recording_insert
{
/**
* hide empty fields
* @access public
* @var string
*/
var $hide_empty = false;

/**
* hide fields for print
* @access public
* @var bool
*/
var $doprint = false;

/**
* allow submit for read only users
* @access public
* @var bool
*/
var $allow_readonly;

/**
* output shown as popunder 
* @access public
* @var bool
*/
var $popunder = false;

/**
* prefix for tables
* @access public
* @var string
*/
var $table_prefix = 'bestand_';

/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_recording_insert_msg';

/**
* tables to compute
* @access private
* @var array
*/
var $__tables = array();

/**
* delimiter if request value is array
* input is type mutiple
* @access private
* @var array
*/
var $__delimiter = '[~]';

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
		$this->user       = $controller->user;
		$this->controller = $controller;
		$this->datadir    = PROFILESDIR.'/webdav/bestand/devices/';
		$this->fields     = array();

		$id = $this->response->html->request()->get('id');
		if( $id !== '') {
			// check id exists
			$res = $this->db->select('bestand', array('bezeichner_kurz'), array('id', $id), null, 1);
			if(is_array($res)) {
				$this->id = $id;
				$this->response->add('id', $this->id);
				$this->bezeichner = $res[0]['bezeichner_kurz'];
				// get current values
				$values = $this->db->select('bestand', array('row','tabelle','merkmal_kurz','wert','date'), array('id', $this->id));
				if(is_array($values)) {
					$this->date = $values[0]['date'];
					foreach ($values as $v) {
						$this->fields[$v['tabelle']][$v['merkmal_kurz']]['tabelle'] = $v['tabelle'];
						$this->fields[$v['tabelle']][$v['merkmal_kurz']]['row']     = $v['row'];
						$this->fields[$v['tabelle']][$v['merkmal_kurz']]['wert']    = $v['wert'];
					}
					$this->lost = $this->fields;
				}
			} else {
				$this->id = '[[notfound]]';
			}
		} else {
			$this->bezeichner = $this->response->html->request()->get('bezeichner');
		}

		// add bezeichner to response
		if(isset($this->bezeichner) && $this->bezeichner !== '') {
			$this->response->add('bezeichner', $this->bezeichner);
		}

		$tables = $this->db->select('bestand_index', array('tabelle_kurz','tabelle_lang'), null, 'pos');
		if(is_array($tables)) {
			foreach($tables as $table) {
				$this->tables[$table['tabelle_kurz']] = $table['tabelle_lang'];
			}
		} else {
			$this->tables = array();
		}

		// handle select params
		//$this->response->add('printout',$this->response->html->request()->get('printout'));
		//$this->response->add('export',$this->response->html->request()->get('export'));
		$this->response->add('filter',$this->response->html->request()->get('filter'));
		if($this->response->html->request()->get('bestand_select') !== '') {
			$this->response->add('bestand_select',$this->response->html->request()->get('bestand_select'));
		}

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.class.php');
		$this->bestandsverwaltung = new bestandsverwaltung($this->db);

		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');
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

		if(!isset($this->id)) {
			if($this->bezeichner === '') {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'step1', $this->message_param, ''
					)
				);
			}
		}

		// switch insert / update
		if(!isset($this->id)) {
			$response = $this->insert();
		} else {
			if($this->id !== '[[notfound]]') {
				$response = $this->update();
			} else {
				$this->controller->controller->response->redirect(
						$this->controller->controller->response->get_url(
						$this->controller->controller->actions_name, 'inventory', $this->controller->controller->message_param.'[error]', 'Error: ID not found'
					).$id.'&inventory_action=select'
				);
			}
		}

		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			// get realnames
			$bezeichner = $this->db->select('bezeichner', 'bezeichner_lang', array('bezeichner_kurz',$this->bezeichner));
			if(isset($bezeichner[0]['bezeichner_lang'])) {
				$bezeichner = $bezeichner[0]['bezeichner_lang'].' ('.$this->bezeichner.')';
			}

			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.insert.html');
			// disable form on print
			if($this->doprint === true) {
				$t->add('#', 'thisfile');
			} else {
				$t->add($this->response->html->thisfile, 'thisfile');
			}
			$t->add($response->form);
			$t->add($bezeichner, 'bezeichner');
			$t->add('','anchor_down');
			$t->add('','anchor_up');
			if($this->doprint === false) {
				$do  = '<div class="noprint" style="position: absolute; top: 2px; right: 10px;">';
				$do .= '<a href="#insertbottom" class="icon icon-menu-down"></a>';
				$do .= '</div>';
				$t->add($do,'anchor_down');

				$up  = '<div class="noprint" style="display: inline;text-align:right;margin:0 5px 0 20px;">';
				$up .= '<a id="insertbottom" href="#top" class="icon icon-menu-up"></a>';
				$up .= '</div>';
				$t->add($up,'anchor_up');
			}
			$t->group_elements(array('param_' => 'form'));

			// group SYSTEM
			$t->group_elements(array('system_' => 'system'));
			$element = $t->get_elements('system');
			if(isset($element)) {
				$field = $this->response->html->customtag('fieldset');
				$field->css = 'fieldset';
				$field->add('<legend>'.$this->lang['legend_system'].'</legend>');
				$field->add($element);
				$t->add($field,'SYSTEM');
			} else {
				$t->add('','SYSTEM');
			}

			// group custom form elements
			$data = NULL;
			foreach($this->tables as $k => $table) {
				$t->group_elements(array($k.'_' => $k));
				$element = $t->get_elements($k);
				if(isset($element)) {
					$field = $this->response->html->customtag('fieldset');
					$field->css = 'fieldset';
					$field->add('<legend>'.$table.'</legend>');
					$field->add($element);
					$data[] = $field;
				}
				$t->remove($k);
			}
			if(isset($data)) {
				$t->add($data,'CUSTOM');
			} else {
				$t->add('','CUSTOM');
			}

			// group todo form elements
			$data = NULL;
			$t->group_elements(array('TODO_' => 'TODO'));
			$element = $t->get_elements('TODO');
			if(isset($element)) {
				$field = $this->response->html->customtag('fieldset');
				$field->css = 'fieldset';
				$field->add('<legend>'.$this->lang['legend_todos'].'</legend>');
				$field->add($element);
				$data[] = $field;
			}
			$t->remove('TODO');
			if(isset($data)) {
				$t->add($data,'TODO');
			} else {
				$t->add('','TODO');
			}

			// group lost form elements
			$data = NULL;
			$t->group_elements(array('lost_' => 'LOST'));
			$element = $t->get_elements('LOST');
			if(isset($element)) {
				$field = $this->response->html->customtag('fieldset');
				$field->css = 'fieldset';
				$field->add('<legend style="color:red;">Lost&amp;Found</legend>');
				$field->add($element);
				$data[] = $field;
			}
			$t->remove('LOST');
			if(isset($data)) {
				$t->add($data,'LOST');
			} else {
				$t->add('','LOST');
			}

			if(isset($this->id)) {
				$t->add('<div style="margin: 30px 0 0 0;">ID: '.$this->id.'</div>','id');
				$t->add('<div>'.$this->lang['label_date'].': '.date('Y-m-d H:i',$this->date).'</div>','date');
			} else {
				$t->add('','id');
				$t->add('','date');
			}

			// handle tabs
			if(isset($this->id)) {
				// build tabs
				$content[0]['label']   = $this->lang['tab_data'];
				$content[0]['value']   = $t;
				$content[0]['target']  = '#recording_insert_tab0';
				$content[0]['request'] = null;
				$content[0]['onclick'] = true;
				$content[0]['active']  = true;
				$content[0]['id']  = 'Id1';

				// handle files
				if(
					method_exists($this->controller, 'files') === true &&
					$this->doprint === false
				) {
					$files = $this->controller->files(true);
					$files->message_param = $this->message_param;

					$str  = '<div class="files noprint fieldset">';
					$str .= $files->action()->get_string();
					$str .= '</div>';

					$content[1]['label']   = $this->lang['tab_files'];
					$content[1]['value']   = $str;
					$content[1]['target']  = '#recording_insert_tab1';
					$content[1]['request'] = null;
					$content[1]['onclick'] = true;
					$content[1]['id']  = 'Id2';
				}

				// changelog
				$str = '';
				$result = $this->db->select('changelog', 'merkmal_kurz,old,new,user,date', array('id' => $this->id), 'date DESC');
				if(is_array($result) && count($result) > 0) {
					$str .= '<table class="htmlobject_table table table-bordered" style="font-size:13px;margin:15px 0 0 0;">';
					$str .= '<tr class="htmlobject_tr headrow">';
					foreach($result[0] as $k => $v) {
						$str .= '<th class="htmlobject_th">'.$k.'</th>';
					}
					$str .= '</tr>';
					foreach($result as $value) {
						if(is_array($value)) {
							$str .= '<tr class="htmlobject_tr">';
							foreach($value as $k => $v) {
								if($k === 'date') {
									$str .= '<td class="htmlobject_td" style="width:180px;">'.date('Y-m-d H:i:s',$v).'</td>';
								} else {
									$str .= '<td class="htmlobject_td">'.$v.'</td>';
								}
							}
							$str .= '</tr>';
						}
					}
					$str .= '</table>';

					$content[2]['label']   = $this->lang['tab_changelog'];
					$content[2]['value']   = $str;
					$content[2]['target']  = '#recording_insert_tab2';
					$content[2]['request'] = null;
					$content[2]['onclick'] = true;
					$content[2]['id']  = 'Id3';
				}
				
				
				// handle active tab on page reload
				// file is active
				if($this->response->html->request()->get('pc', true) !== null) {
					$content[0]['active']  = false;
					$content[1]['active']  = true;
					$content[2]['active']  = false;
				}

				$tab = $this->response->html->tabmenu('recording_insert_tab');
				$tab->message_param = $this->message_param;
				$tab->css = 'tabs noprint';
				$tab->auto_tab = false;
				$tab->add($content);

				// if print - no tabs
				if( $this->doprint === true ) {
					$tab = $t;
				}

				$out = $this->response->html->div();
				$out->add('<div id="recording_insert_top" style="margin:-15px 0 15px 0; clear:both;" class="floatbreaker">&#160;</div>');
				$out->add($tab);
			} else {
				$out = $t;
				#$out = $this->response->html->div();
				#$out->add('<div id="recording_insert_top" style="margin:-15px 0 15px 0; clear:both;" class="floatbreaker">&#160;</div>');
				#$out->add($t);
			}
			
			return $out;

		} else {
			$id = '';
			if(isset($response->id)) {
				$id = '&id='.$response->id;
			}

			// handle params
			$filter = '';
			$params = $this->response->get_array();
			if(isset($params['filter'])) {
				unset($tmp);
				$tmp['filter'] = $params['filter'];
				$filter .= $this->response->get_params_string($tmp, '&');
			}
			if(isset($params['bestand_select'])) {
				unset($tmp);
				$tmp['bestand_select'] = $params['bestand_select'];
				$filter .= $this->response->get_params_string($tmp, '&');
			}
			unset($tmp);

			$this->controller->controller->response->redirect(
					$this->controller->controller->response->get_url(
					$this->controller->controller->actions_name, 'inventory', $this->message_param, $response->msg
				).$id.'&inventory_action=update'.$filter
			);

			// handle return if redirect disabled
			if(isset($response->id)) {
				return $response->id;
			}
		}

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

			$f['TODO']   = $form->get_request('TODO');
			$f['SYSTEM'] = $form->get_request('SYSTEM');
			if($f['SYSTEM'] === '') {
				$f['SYSTEM'] = array();
			}
			
			foreach($this->tables as $k => $t) {
				$f[$k] = $form->get_request($k);
			}
			$id   = $form->get_request('deviceid');
			$user = $this->controller->user->get();

			// check id
			$check = $this->db->select('bestand','id',array('id'=>$id));
			if($check !== '') {
				$form->set_error('deviceid','');
				$error = 'ID '.$id.' already in use';
			}

			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
			
				// auto add current user by System vars
				$f['SYSTEM']['USER'] = $user['login'];
				
				$d['id']              = $id;
				$d['bezeichner_kurz'] = $this->bezeichner;
				$d['date']            = time();
				foreach($f as $key => $value) {
					if(is_array($value)) {
						foreach($value as $k => $v) {
							$d['tabelle'] = $key;
							$d['merkmal_kurz'] = $k;
							$d['wert'] = (is_array($v)) ? implode($this->__delimiter, $v) : $v;
							$error = $this->db->insert('bestand', $d);
						}
					}
				}
				if(isset($error) && $error === '') {
					$response->id  = $d['id'];
					$response->msg = sprintf('successfully added %s', $response->id);
				} else {
					$response->error = $error;
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

		// handle id


		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$user = $this->controller->user->get();

			$lost = $form->get_request(null,true);
			unset($lost['TODO']);
			unset($lost['SYSTEM']);

			$r['SYSTEM'] = $form->get_request('SYSTEM', true);
			$r['TODO']   = $form->get_request('TODO', true);
			foreach($this->tables as $k => $t) {
				$r[$k] = $form->get_request($k, true);
				unset($lost[$k]);
			}

			// handle lost
			if(is_array($lost)) {
				foreach($lost as $k => $l) {
					if(is_array($l)) {
						$r[$k] = $l;
						$this->fields[$k] = $this->lost[$k];
					}
				}
			}

			$errors = array();
			foreach($r as $key => $value) {
				if($value !== '') {
					$fields = array();
					if(isset($this->fields[$key])) {
						$fields = $this->fields[$key];
					}
					$d = array();
					foreach($value as $k => $v) {
						// handle value is array
						if(is_array($v)) {
							$v = implode($this->__delimiter, $v);
						}
						$error = '';
						if(array_key_exists($k, $fields)) {
							// formfield set ?
							if($fields[$k]['wert'] !== $v) {
								// changed
								if($v === '') {
									// delete
									$error = $this->db->delete(
										'bestand',
										array('row',$fields[$k]['row'])
									);
								} else {
									// update
									$error = $this->db->update(
										'bestand',
										array('wert' => $v),
										array('row',$fields[$k]['row'])
									);	
								}

								// changelog
								if($error === '') {

									// handle string length
									$tmp = $this->db->handler()->columns($this->db->db, 'changelog');

									$old = substr($fields[$k]['wert'], 0, ($tmp['old']['length']) -3 );
									strlen($old) < strlen($fields[$k]['wert']) ? $old = $old.'...' : null;

									$new = substr($v, 0, ($tmp['new']['length']) -3 );
									strlen($new) < strlen($v) ? $new = $new.'...' : null;

									$d = array();
									$d['id']           = $this->id;
									$d['merkmal_kurz'] = $k;
									$d['old']          = $old;
									$d['new']          = $new;
									$d['user']         = $user['login'];
									$d['date']         = time();

									$error = $this->db->insert('changelog',$d);
								}
							} else {
								// unchanged
								// do nothing
								// echo 'unchanged '.$k.'-'.$v.'<br>';
							}
						} else {
							if($v !== '') {
								// formfield new
								$d = array();
								$d['id']              = $this->id;
								$d['bezeichner_kurz'] = $this->bezeichner;
								$d['tabelle']         = $key;
								$d['merkmal_kurz']    = $k;
								$d['wert']            = $v;
								// TODO remove date
								$d['date']            = $this->date;

								$error = $this->db->insert('bestand', $d);
								// changelog
								if($error === '') {

									// handle string length
									$tmp = $this->db->handler()->columns($this->db->db, 'changelog');
									$new = substr($v, 0, ($tmp['new']['length']) -3 );
									strlen($new) < strlen($v) ? $new = $new.'...' : null;

									$d = array();
									$d['id']           = $this->id;
									$d['merkmal_kurz'] = $k;
									$d['new']          = $new;
									$d['user']         = $user['login'];
									$d['date']         = time();

									$error = $this->db->insert('changelog',$d);
								}
							}
						}
						if($error !== '') {
							$errors[] = $error;
						}
					}
				}
			}

			if(isset($errors) && is_array($errors) && count($errors) > 0) {
				$response->error = implode('<br>', $errors);
			} else {
				$response->id  = $this->id;
				$response->msg = 'success';
			}
		}		
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		if(isset($this->id)) {
			$form = $response->get_form($this->actions_name, 'update');
			$form->add('','cancel');
		} else {
			$form = $response->get_form($this->actions_name, 'insert', true, $this->allow_readonly);
		}

		// disable submit on print
		if($this->doprint === true) {
			$form->add('','submit');
		}

		// Get attribs from tables
		foreach($this->tables as $k => $t) {
			$sql  = 'SELECT * ';
			$sql .= 'FROM bestand_'.$k.' ';
			$sql .= 'WHERE `bezeichner_kurz`=\''.$this->bezeichner.'\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$this->bezeichner.'\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$this->bezeichner.',%\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \''.$this->bezeichner.',%\' ' ;
 			// Wildcard *
			$sql .= 'OR `bezeichner_kurz`=\'*\' ';
			$sql .= 'ORDER BY `row` ';
			$result[$k] = $this->db->handler()->query($sql);
		}

		// Assemble form
		$d = array();
		if(is_array($result)) {
			foreach ( $result as $k => $v ) {
				$fields = array();
				$found  = array();
				if(is_array($v)) {
					// handle fields
					if(isset($this->fields[$k])) {
						$fields = $this->fields[$k];
						// unset field to find lost tables
						unset($this->lost[$k]);
					}
					foreach ( $v as $r ) {
						// handle lost
						$found[$r['merkmal_kurz']] = '';
						$addempty = true;
						if($k === 'prozess') {
							$addempty = false;
						}
						// handle todo
						if($k === 'TODO') {
							$table = 'todo_';
						} else {
							$table = 'bestand_';
						}
						$d = array_merge($d, $this->bestandsverwaltung->element($r, $k, $k, $fields, $table, $addempty));
					}
				} else {
					if(is_string($v) && $v !== '') {
						$d[$k.'_0'] = $v;
					}
				}
				// handle lost
				$lost[$k] = array_diff_key($fields, $found);
				if(count($lost[$k]) < 1) {
					unset($lost[$k]);
				}
			}
		}

		// handle plugins
		if(in_array('cafm.one', $this->plugins)) {
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
			$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
			$fields = array();
			if(isset($this->fields['TODO'])) {
				$fields = $this->fields['TODO'];
			}
			// check prefixes (todogroups)
			$prefixes = $this->response->html->request()->get('prefix');
			if(!is_array($prefixes)) {
				$prefixes = array();
				$tmp = $this->taetigkeiten->prefixes(true);
				if(is_array($tmp)) {
					foreach($tmp as $k => $v) {
						if(isset($v['bezeichner']) && (in_array('*', $v['bezeichner']) || in_array($this->bezeichner,$v['bezeichner']))) {
							$prefixes[$k] = $k;
						}
					}
				}
			}
			// get todo form fields
			$tattribs = $this->taetigkeiten->form($this->bezeichner, $fields);
			if(is_array($tattribs)) {
				foreach($tattribs as $k => $ta) {
					if(isset($ta['prefix']) && is_array($ta['prefix'])) {
						$check = array_intersect($prefixes, $ta['prefix']);
						if(count($check) < 1) {
							unset($tattribs[$k]);
						}
					}
				}
				$d = array_merge($d,$tattribs);
			} 
			else if(is_string($tattribs)) {
				$e = $this->response->html->div();
				$e->add($data);
				$e->style = 'margin: 15px;';
				$e->css = 'error';
				$d['TODO_ERROR']['object'] = $e;
			}
			else {
				#$d['TODO_NODATA'] = '';
			}
		}

		// ID
		if(!isset($this->id)) {
			$columns = $this->db->handler()->columns($this->db->db, 'bestand');
			$d['system_id']['label']                     = 'ID';
			$d['system_id']['required']                  = true;
			$d['system_id']['object']['type']            = 'htmlobject_input';
			$d['system_id']['object']['attrib']['name']  = 'deviceid';
			$d['system_id']['object']['attrib']['value'] = uniqid('');
			if(isset($columns['id']['length'])) {
				$d['system_id']['object']['attrib']['maxlength'] = $columns['id']['length'];
			}
		}

		// Raumbuch
		if(in_array('standort', $this->plugins)) {
			require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
			$raumbuch = new standort($this->db, $this->file);
			$options = $raumbuch->options();

			if(is_array($options) && count($options) > 0) {
				array_unshift($options, array('id' => '', 'path' => ''));

				$d['system_raumbuch']['label']                       = $this->lang['label_location'];
				$d['system_raumbuch']['object']['type']              = 'htmlobject_select';
				$d['system_raumbuch']['object']['attrib']['index']   = array('id','path');
				$d['system_raumbuch']['object']['attrib']['style']   = 'max-width:200px;';
				$d['system_raumbuch']['object']['attrib']['name']    = 'SYSTEM[RAUMBUCHID]';
				$d['system_raumbuch']['object']['attrib']['options'] = $options;
				$d['system_raumbuch']['object']['attrib']['title']   = $this->lang['label_location'];
				$d['system_raumbuch']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \'Standort\'); return false;"';

				if(isset($this->fields['SYSTEM'])) {
					if(array_key_exists('RAUMBUCHID', $this->fields['SYSTEM'])) {
						$d['system_raumbuch']['object']['attrib']['selected'] = array($this->fields['SYSTEM']['RAUMBUCHID']['wert']);
					}
				}
			}
		}

		// handle bestand column wert length
		$columns = $this->db->handler()->columns($this->db->db, 'bestand');
		foreach($d as $k => $element) {
			$type = '';
			if(isset($element['object']['type'])) {
				$type = $element['object']['type'];
			}
			if($type === 'htmlobject_input' || 
				$type === 'htmlobject_textarea')
			{
				if(isset($element['object']['attrib']['maxlength']) && 
					$element['object']['attrib']['maxlength'] !== '')
				{
					if($element['object']['attrib']['maxlength'] > $columns['wert']['length']) {
						$d[$k]['object']['attrib']['maxlength'] = $columns['wert']['length'];
					}
				} else {
					$d[$k]['object']['attrib']['maxlength'] = $columns['wert']['length'];
				}
			}
		}

		// back
		if(isset($this->id) && $this->doprint === false && $this->popunder === false) {
			$back = $this->response->html->a();
			$back->label = '<span class="icon icon-home" style="margin-right:10px;"></span>'.$this->lang['button_back_select'];
			$back->css = 'btn btn-default btn-sm update';
			$back->style = 'margin: 0 10px 0 0; float:left;';
			$back->href = $this->response->get_url($this->actions_name, 'select');
			$back->handler = 'onclick="phppublisher.wait();"';

			$new = $this->response->html->a();
			$new->label = '<span class="icon icon-plus" style="margin-right:10px;"></span>'.$this->lang['button_back_new'];
			$new->css = 'btn btn-default btn-sm update';
			$new->style = 'margin: 0 10px 0 0; float:left;';
			$new->href = $this->controller->controller->controller->controller->response->get_url($this->controller->controller->controller->controller->actions_name, 'recording');
			$new->handler = 'onclick="phppublisher.wait();"';

			$str  = '<div style="position: absolute;top: 0;right: 55px;" id="linksbox">';
			$str .= $back->get_string().$new->get_string();
			$str .= '<div style="clear:both;" class="floatbreaker">&#160;</div>';
			$str .= '</div>';
			$d['back'] = $str;
		} else {
			$d['back'] = '';
		}

		// handle lost
		if(isset($this->lost)) {
			unset($this->lost['TODO']);
			unset($this->lost['SYSTEM']);
			$lost = array_merge($lost, $this->lost);
		}
		if(isset($lost) && count($lost) > 0) {
			foreach($lost as $key => $l) {
				if(is_array($l)) {
					foreach($l as $k => $v) {
						$d['lost_'.$key.''.$k]['label']                     = $k;
						$d['lost_'.$key.''.$k]['object']['type']            = 'htmlobject_input';
						$d['lost_'.$key.''.$k]['object']['attrib']['name']  = $key.'['.$k.']';
						$d['lost_'.$key.''.$k]['object']['attrib']['value'] = $v['wert'];
						$d['lost_'.$key.''.$k]['object']['attrib']['title'] = 'row: '.$v['row'].' - tabelle: '.$key;
					}
				}
			}
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;
		return $response;
	}

}
?>
