<?php
/**
 * bestandsverwaltung_api
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
 *  Copyright (c) 2015-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_api
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestandsverwaltung_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'bestand_ident';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'label_location' => 'Location',
	'label_date' => 'Date',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->response = $response;
		$this->user     = $user;
		$this->db       = $db;
		$this->file     = $file;
		$this->baseurl  = $GLOBALS['settings']['config']['baseurl'];
		$this->settings = $this->file->get_ini(PROFILESDIR.'bestandsverwaltung.ini');
		if(isset($this->settings['settings']['db'])) {
			$this->db->db = $this->settings['settings']['db'];
		}
		$this->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';
		$this->classdir = CLASSDIR.'plugins/bestandsverwaltung/class/';
		$this->profilesdir = PROFILESDIR;
		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestandsverwaltung/lang/', 'bestandsverwaltung.api.ini');

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {

		$action = $this->response->html->request()->get($this->actions_name);
		if($action !== '') {
			$this->response->add($this->actions_name, $action);
		}

		switch( $action ) {
			case 'update':
				$this->update(true);
			break;
			case 'download':
				$this->download(true);
			break;
			case 'help':
				$this->help(true);
			break;
			case 'details':
				$this->details(true);
			break;
			case 'gewerke':
				$this->gewerke(true);
			break;
			case 'bezeichner':
				$this->bezeichner(true);
			break;
			#case 'changelog':
			#	$this->changelog(true);
			#break;
			case 'raumbuch':
				$this->raumbuch(true);
			break;
			case 'process':
				$this->process(true);
			break;
			case 'marker':
				$this->marker(true);
			break;
			case 'printtodos':
				$this->printtodos(true);
			break;
			case 'tasks':
				$this->tasks(true);
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 */
	//--------------------------------------------
	function update($visible = false) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.inventory.update.class.php');

			// set action to point back here
			$this->response->add($this->actions_name,'update');

			$controller = new bestandsverwaltung_inventory_update($this);
			$controller->actions_name = 'inventory_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->popunder = true;
			$data = $controller->action();
			echo $data->get_string();
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access public
	 */
	//--------------------------------------------
	function download() {
		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.controller.class.php');
		$controller = new bestandsverwaltung_controller($this->file, $this->response, $this->db, $this->user);
		$data = $controller->download();
	}

	//--------------------------------------------
	/**
	 * Raumbuch
	 *
	 * @access public
	 */
	//--------------------------------------------
	function raumbuch() {
		if(in_array('standort', $this->plugins)) {
			$id = $this->db->handler()->escape($this->response->html->request()->get('id'));
			if($id !== '') {
				require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
				$this->raumbuch = new standort($this->db, $this->file);
				$this->response->html->help($this->raumbuch->parents($id));
			}
		}
	}

	//--------------------------------------------
	/**
	 * Help (Bezeichner)
	 *
	 * @access public
	 */
	//--------------------------------------------
	function help() {
		#sleep(3);
		$bezeichner = $this->response->html->request()->get('bezeichner');
		$result = $this->db->select('bezeichner_help', array('text'), array('bezeichner_kurz' => $bezeichner));
		if(is_array($result)) {
			foreach($result as $v) {
				echo $v['text'];
			}
		} else {
			echo $result;
		}
	}

	//--------------------------------------------
	/**
	 * Gewerke
	 *
	 * @access public
	 */
	//--------------------------------------------
	function gewerke($visible = false) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.settings.gewerke.api.class.php');
			$g = new bestandsverwaltung_settings_gewerke_api($this);

			$g->actions_name = 'xxx';
			$g->tpldir = CLASSDIR.'plugins/bestandsverwaltung/templates/';

			$g->gewerk(true);
		}
	}

	//--------------------------------------------
	/**
	 * Marker
	 *
	 * @access public
	 */
	//--------------------------------------------
	function marker($visible = false) {
		if($visible === true) {
			$id    = $this->db->handler()->escape($this->response->html->request()->get('id'));
			$color =  $this->db->handler()->escape($this->response->html->request()->get('color'));
			$check = $this->db->select('bestand','wert',array('id'=>$id,'tabelle'=>'SYSTEM','merkmal_kurz'=>'MARKER'));
			if($color === '' && $check !== '') {
				$error = $this->db->delete('bestand',array('id'=>$id,'tabelle'=>'SYSTEM','merkmal_kurz'=>'MARKER'));
			}
			else if($color !== '' && $check === '') {
				$check = $this->db->select('bestand','bezeichner_kurz,date',array('id'=>$id));
				if(is_array($check)) {
					$d['id']              = $id;
					$d['bezeichner_kurz'] = $check[0]['bezeichner_kurz'];
					$d['tabelle']         = 'SYSTEM';
					$d['merkmal_kurz']    = 'MARKER';
					$d['wert']            = $color;
					$d['date']            = $check[0]['date'];

					$error = $this->db->insert('bestand',$d);
				} else {
					if($check !== '') {
						$error = $check;
					}
				}
			}
			else if($color !== '' && $check !== '') {
				$error = $this->db->update(
						'bestand',
						array('wert'=>$color),
						array('id'=>$id,'tabelle'=>'SYSTEM','merkmal_kurz'=>'MARKER')
				);
			}

			if(isset($error) && $error !== '') {
				echo $error;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Bezeichner
	 *
	 * @access public
	 */
	//--------------------------------------------
	function bezeichner($visible = false) {
		if($visible === true) {

			$action = $this->response->html->request()->get('subaction');
			$this->response->add('subaction',$action);

			$key = $this->response->html->request()->get('key');
			if($key !== '') {
				$this->response->add('key',$key);
				$key = $this->db->handler()->escape($key);
			}

			$form = $this->response->get_form($this->actions_name, 'bezeichner');
			$cancel = $form->get_elements('cancel');
			$cancel->id = 'bezeichnercancel';
			$cancel->type = 'button';
			$form->add($cancel,'cancel');

			$d['gewerk']['object']['type']            = 'htmlobject_input';
			$d['gewerk']['object']['attrib']['type']  = 'hidden';
			$d['gewerk']['object']['attrib']['name']  = 'gewerk_kurz';
			$d['gewerk']['object']['attrib']['value'] = $key;

			$d['key'] = '';

			switch( $action ) {
				case 'insert':
					if($key !== '') {
						$r = $this->db->select('gewerke', array('gewerk_kurz','gewerk_lang'),array('gewerk_kurz', $key));
						if(isset($r[0]['gewerk_kurz'])) {
							$d['label'] = $r[0]['gewerk_lang'].' ('.$r[0]['gewerk_kurz'].')';
						}
						$r = $this->db->select('bezeichner', array('bezeichner_kurz','bezeichner_lang'),null,'bezeichner_lang');
						if(isset($r[0]['bezeichner_kurz'])) {
							$o = array();
							foreach($r as $k => $v) {
								$label = $v['bezeichner_lang'];
								$label = substr($label, 0, 55);
								strlen($label) < strlen($v['bezeichner_lang']) ? $label = $label.'...' : null;
								$o[$k][0] = $v['bezeichner_kurz'];
								$o[$k][1] = $o[$k][1] = $label.' ('.$v['bezeichner_kurz'].')';
							}
							$d['bezeichner']['label']                       = 'Add Bezeichner:';
							$d['bezeichner']['css']                         = 'autosize bezeichner';
							$d['bezeichner']['required']                    = true;
							$d['bezeichner']['object']['type']              = 'htmlobject_select';
							$d['bezeichner']['object']['attrib']['index']   = array(0,1);
							$d['bezeichner']['object']['attrib']['name']    = 'bezeichner_kurz[]';
							$d['bezeichner']['object']['attrib']['multiple'] = true;
							$d['bezeichner']['object']['attrib']['options'] = $o;
						} else {
							$error = $r;
						}
					} else {
						$error = 'Error: No Key';
					}
				break;
				case 'delete':
					if($key !== '') {
						$r = $this->db->select('gewerke', array('gewerk_kurz','gewerk_lang'),array('gewerk_kurz', $key));
						if(isset($r[0]['gewerk_kurz'])) {
							$d['label'] = $r[0]['gewerk_lang'].' ('.$r[0]['gewerk_kurz'].')';
						}
						$sql  = 'SELECT b.bezeichner_kurz, b.bezeichner_lang ';
						$sql .= 'FROM `bezeichner` as b, `gewerk2bezeichner` as g ';
						$sql .= 'WHERE g.gewerk_kurz=\''.$key.'\' ';
						$sql .= 'AND g.bezeichner_kurz=b.bezeichner_kurz ';
						$sql .= 'ORDER BY b.bezeichner_lang';
						$r = $this->db->handler()->query($sql);
						if(isset($r[0]['bezeichner_kurz'])) {
							$o = array();
							foreach($r as $k => $v) {
								$label = $v['bezeichner_lang'];
								$label = substr($label, 0, 55);
								strlen($label) < strlen($v['bezeichner_lang']) ? $label = $label.'...' : null;
								$o[$k][0] = $v['bezeichner_kurz'];
								$o[$k][1] = $label.' ('.$v['bezeichner_kurz'].')';
							}
							$d['bezeichner']['label']                       = 'Remove Bezeichner:';
							$d['bezeichner']['css']                         = 'autosize bezeichner';
							$d['bezeichner']['required']                    = true;
							$d['bezeichner']['object']['type']              = 'htmlobject_select';
							$d['bezeichner']['object']['attrib']['index']   = array(0,1);
							$d['bezeichner']['object']['attrib']['name']    = 'bezeichner_kurz[]';
							$d['bezeichner']['object']['attrib']['multiple'] = true;
							$d['bezeichner']['object']['attrib']['options'] = $o;
						} else {
							$error = 'Error: Key '.$key.' not found';
						}
					} else {
						$error = 'Error: No Key';
					}
				break;
				default:
					$error = 'Nothing to do';
				break;
			}

			$form->add($d);
			#$form->display_errors = false;

			if(!isset($error) && !$form->get_errors() && $this->response->submit()) {
				$error = '';
				$gewerk = $form->get_request('gewerk_kurz');
				$bezeichner = $form->get_request('bezeichner_kurz');
				switch( $action ) {
					case 'insert':
						// check exists
						foreach($bezeichner as $b) {
							$r = $this->db->select('gewerk2bezeichner','bezeichner_kurz', array('bezeichner_kurz' => $b, 'gewerk_kurz' => $key));
							if(!isset($r[0]['bezeichner_kurz'])) {
								$result = $this->db->insert('gewerk2bezeichner', array('gewerk_kurz' => $gewerk, 'bezeichner_kurz' => $b));
								if($result !== '') {
									$error .= $result;
								}
							}
						}
						if($error === '') {
							echo 'ok';
							exit;
						}
					break;
					case 'delete':
						foreach($bezeichner as $b) {
							#$r = $this->db->select('gewerk2bezeichner','bezeichner_kurz', array('bezeichner_kurz' => $b, 'gewerk_kurz' => $key));
							#if(!isset($r[0]['bezeichner_kurz'])) {
								$result = $this->db->delete('gewerk2bezeichner', array('gewerk_kurz' => $gewerk, 'bezeichner_kurz' => $b));
								if($result !== '') {
									$error .= $result;
								}
							#}
						}
						if($error === '') {
							echo 'ok';
							exit;
						}
					break;
				}
			}
			else if($form->get_errors()) {
				#$error = implode('<br>', $form->get_errors());
			}

			$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.api.bezeichner.html');
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
	
			#sleep(1);

			echo $t->get_string();
		}
	}

	//--------------------------------------------
	/**
	 * Details
	 *
	 * @access public
	 */
	//--------------------------------------------
	function details($visible = false) {
		if($visible === true) {
			$id = $this->response->html->request()->get('id');
			$mode = $this->response->html->request()->get('mode');

			if ($id !== '' && $mode !== '') {
				if ($mode === 'form') {
					require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.controller.class.php');
					$cont = new bestandsverwaltung_recording_controller($this);
					$cont->tpldir = $this->tpldir;
					$cont->message_param = 'inventory_update_msg';

					require_once($this->classdir.'bestandsverwaltung.recording.insert.class.php');
					$controller = new bestandsverwaltung_recording_insert($cont);
					$controller->actions_name = $this->actions_name;
					$controller->message_param = $this->message_param;
					$controller->tpldir = $this->tpldir;
					$controller->lang  = $cont->lang['insert'];
					$controller->doprint = true;
					$data = $controller->action();

					echo $data->get_string();
				}
				else if ($mode === 'text') {
					$result = $this->db->select('bestand', '*', array('id' => $id));
					if(is_array($result)) {
						echo '<div><b>ID: '.$result[0]['id'].'</b></div>';

						require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
						$gewerke = new gewerke($this->db);
						$gstr = $gewerke->bezeichner2gewerk($result[0]['bezeichner_kurz']);
						if($gstr !== '') {
							echo '<div style="margin: 5px 0 5px 0;">'.$gstr.'</div>';
						}

						$lang = $this->db->select('bezeichner', array('bezeichner_lang'), array('bezeichner_kurz' => $result[0]['bezeichner_kurz']));
						if(isset($lang[0]['bezeichner_lang'])) {
							echo '<div>'.$lang[0]['bezeichner_lang'].' ('.$result[0]['bezeichner_kurz'].')</div>';
						} else {
							echo '<div>'.$result[0]['bezeichner_kurz'].'</div>';
						}

						echo '<div>'.$this->lang['label_date'].': '.date('Y-m-d H:i:s',$result[0]['date']).'</div>';

						if(in_array('standort', $this->plugins)) {
							$standort = array_search('RAUMBUCHID', array_column($result, 'merkmal_kurz'));
							if($standort !== false) {
								require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
								$raumbuch = new standort($this->db, $this->file);
								$raumbuch->options = $raumbuch->options();
								if(isset($raumbuch->options[$raumbuch->indexprefix.$result[$standort]['wert']])) {
									echo '<div>'.$this->lang['label_location'].': '.$raumbuch->options[$raumbuch->indexprefix.$result[$standort]['wert']]['path'].' ('.$result[$standort]['wert'].')</div>';
								} else {
									echo '<div>'.$this->lang['label_location'].': '.$result[$standort]['wert'].'</div>';

								}
							}
						}

						$tmp = array();

						$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang',null,'`pos`');
						foreach($tables as $t) {
							$sql  = 'SELECT `merkmal_kurz`,`merkmal_lang` ';
							$sql .= 'FROM `bestand_'.$t['tabelle_kurz'].'` ';
							$sql .= 'WHERE (';
							$sql .= '`bezeichner_kurz` = \'*\' ';
							$sql .= 'OR `bezeichner_kurz` = \''.$result[0]['bezeichner_kurz'].'\' ';
							$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$result[0]['bezeichner_kurz'].'\' ';
							$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$result[0]['bezeichner_kurz'].',%\' ';
							$sql .= 'OR `bezeichner_kurz` LIKE \''.$result[0]['bezeichner_kurz'].',%\') ';
							$sql .= 'ORDER BY `row` ';
							$res = $this->db->handler->query($sql);
							if(is_array($res)) {
								foreach($res as $r) {
									if(isset($r['merkmal_lang']) && $r['merkmal_lang'] !== ''){ 
										$table[$t['tabelle_kurz']][$r['merkmal_kurz']] = $r['merkmal_lang'];
									} else {
										$table[$t['tabelle_kurz']][$r['merkmal_kurz']] = $r['merkmal_kurz'];
									}
								}
							}
						}
						// handle $result
						foreach($table as $x => $t) {
							foreach($t as $key => $label) {
								foreach($result as $v) {
									if( isset($v['tabelle']) && $v['tabelle'] === $x && $v['merkmal_kurz'] === $key) {
										$tmp[$x][] = '<div>'.$label.': '.$v['wert'].'</div>';
									}
								}
							}
						}
						
						/*
						foreach($result as $v) {
							if( isset($table[$v['tabelle']]) && isset($table[$v['tabelle']][$v['merkmal_kurz']]) ) {
								$tmp[$v['tabelle']][] = '<div>'.$table[$v['tabelle']][$v['merkmal_kurz']].': '.$v['wert'].'</div>';
							}
							else if($v['tabelle'] === 'TODO') {
								$tmp[$v['tabelle']][] = '<div>'.$v['merkmal_kurz'].': '.$v['wert'].'</div>';
							}
						}
						*/
						// handle headline
						if(is_array($tmp)) {
							if(is_array($tables)) {
								array_push($tables,array('tabelle_kurz'=>'TODO','tabelle_lang'=>'TODOS'));
								foreach($tables as $table) {
									if(isset($tmp[$table['tabelle_kurz']])) {
										echo '<br>';
										echo '<div><b>'.$table['tabelle_lang'].'</b></div>';
										foreach($tmp[$table['tabelle_kurz']] as $k => $v) {
											echo $v;
										}
										// unset tmp
										unset($tmp[$table['tabelle_kurz']]);
									}
								}
								// unset system
								unset($tmp['SYSTEM']);
							}
						}
					} else {
						echo $result;
					}
				}
				else if ($mode === 'todos') {
					require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.api.class.php');
					$controller = new cafm_one_api($this->file, $this->response, $this->db, $this->user);
					$controller->actions_name = $this->actions_name;
					$controller->message_param = $this->message_param;
					$controller->tpldir = $this->tpldir;
					#$controller->lang  = $cont->lang['insert'];
					#$controller->doprint = true;
					$data = $controller->todos(true);

					#echo $data->get_string();
				}
			}
		}
	}

	//--------------------------------------------
	/**
	 * Print Todos by bezeichner
	 *
	 * @access public
	 */
	//--------------------------------------------
	function printtodos($visible = false) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.api.class.php');
			$controller = new cafm_one_api($this->file, $this->response, $this->db, $this->user);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->todos(true);
		}
	}

	//--------------------------------------------
	/**
	 * Process
	 *
	 * @access public
	 */
	//--------------------------------------------
	function process($visible = false) {
		if($visible === true) {
			$id = $this->response->html->request()->get('id');
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.inventory.controller.class.php');
			$cont = new bestandsverwaltung_inventory_controller($this);
			$cont->tpldir = $this->tpldir;
			$cont->message_param = 'inventory_update_msg';

			require_once($this->classdir.'bestandsverwaltung.inventory.process.class.php');
			$controller = new bestandsverwaltung_inventory_process($cont);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;

			if($id !== '') {
				$_REQUEST[$cont->identifier_name][] = $id;
				$_REQUEST[$controller->response->id]['submit'] = 'submit';
				$data = $controller->update();
				if(isset($data->error)) {
					echo $data->error;
				}
				else if(isset($data->msg)) {
					echo 'ok';
				} else {
					echo 'nothing';
				}
			} else {
				$_REQUEST[$cont->identifier_name][] = '0';
				$data = $controller->update();

				$form = $data->form;
				$form->add('','cancel');
				$form->add('','submit');
				$form->add('','param_f0');
				$form->action = '#';
				$form->id = 'processform';

				echo $form->get_string();
			}
		}
	}

	//--------------------------------------------
	/**
	 * Taks
	 *
	 * @access public
	 */
	//--------------------------------------------
	function tasks($visible = false) {
		if($visible === true) {
			// plugin INFOS
			$params = '';
			$where = array();
			$elements = array(
				'referer',
				'tag',
				'value'
			);
			
			$referer = $this->response->html->request()->get('referer');
			$tag = $this->response->html->request()->get('tag');
			$value = $this->response->html->request()->get('value');
			
			if($referer === 'device' && $value !== '') {
				$_REQUEST['id'] = $value;
				$_REQUEST['mode'] = 'text';
				$this->details(true);

				$str  = '<div style="text-align:right;">';
				$str .= '<a class="btn btn-default icon icon-edit" ';
				$str .= 'target="_blank" ';
				$str .= 'href="?index_action=plugin';
				$str .= '&index_action_plugin=bestandsverwaltung';
				$str .= '&bestandsverwaltung_action=inventory';
				$str .= '&inventory_action=update';
				$str .= '&id='.$value.'"></a>';
				$str .= '</div>';
				
				echo $str;
				
			}


		}
	}

	//--------------------------------------------
	/**
	 * Changelog
	 *
	 * @access public
	 */
	//--------------------------------------------
/*
	function changelog($visible = false) {
		if($visible === true) {
			$id = $this->response->html->request()->get('id');
			$result = $this->db->select('changelog', 'merkmal_kurz,old,new,user,date', array('id' => $id), 'date DESC');
			if(is_array($result)) {
				echo '<table class="htmlobject_table table table-bordered">';
				echo '<tr>';
				foreach($result[0] as $k => $v) {
					echo '<th>'.$k.'</th>';
				}
				echo '</tr>';
				foreach($result as $value) {
					if(is_array($value)) {
						echo '<tr>';
						foreach($value as $k => $v) {
							if($k === 'date') {
								echo '<td>'.date('Y-m-d H:i:s',$v).'</td>';
							} else {
								echo '<td>'.$v.'</td>';
							}
						}
						echo '</tr>';
					}
				}
				echo '</table>';
			}
		}
	}
*/

}
?>
