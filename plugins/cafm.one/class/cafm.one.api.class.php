<?php
/**
 * cafm_one_api
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_api
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cafm_one_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'cafm_one_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'cafm_one_ident';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'label_comment' => 'Comment',
	'button_enable'  => 'on',
	'button_disable' => 'off',
	'button_toggle'  => 'toggle',
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
		$this->tpldir = CLASSDIR.'plugins/cafm.one/templates/';
		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		#$this->_init();
		$action = $this->response->html->request()->get($this->actions_name);
		if($action !== '') {
			$this->response->add($this->actions_name, $action);
		}

		switch( $action ) {
			case 'todos':
				$this->todos(true);
			break;
			case 'disable':
				$this->disable(true);
			break;
			case 'interval':
				$this->interval(true);
			break;
			case 'toggle':
				$this->toggle(true);
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Init cms api
	 *
	 * @access public
	 */
	//--------------------------------------------
	function _init() {
		$this->siteDir = realpath( $this->datadir.'/'.$this->dir );
		if(isset($this->ini['config']['use_approval'])) {
			$this->tempDir = $this->siteDir.'/tmp';
		} else {
			$this->tempDir = $this->siteDir;
		}
	}

	//--------------------------------------------
	/**
	 * taetigkeiten
	 *
	 * @access public
	 */
	//--------------------------------------------
	function todos($visible = false) {
		if($visible === true) {

			$id = $this->response->html->request()->get('id');
			$bezeichner = $this->response->html->request()->get('bezeichner');
			$prefix = $this->response->html->request()->get('prefix');
			$interval = $this->response->html->request()->get('interval');
			$label = $this->response->html->request()->get('label');
			$mode = $this->response->html->request()->get('mode');

			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
			$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);

			// handle empty prefix
			if($prefix === '') {
				$tables = $this->taetigkeiten->prefixes();
				if(is_array($tables)) {
					$prefix = implode(',',array_keys($tables));
				}
			}

			if($label !== '' && $label === 'false') {
				$label = false;
			} else {
				$label = true;
			}

			$device = '';
			if($id !== '') {
				$device = $this->db->select('bestand','merkmal_kurz,wert',array('id'=>$id, 'tabelle'=>'TODO'));
			}

			$todofields = array();
			if(is_array($device)) {
				foreach($device as $v) {
					$todofields[$v['merkmal_kurz']] = $v['wert'];
				}
			}

			$result = $this->db->select('bestand', '*', array('id' => $id));
			if(is_array($result)) {
				$bezeichner = $result[0]['bezeichner_kurz'];
				$device = array();
				foreach($result as $v) {
					// Translate
					if($v['tabelle'] !== 'SYSTEM') {
						$lang = $this->db->select('bestand_'.$v['tabelle'], array('merkmal_lang'), array('merkmal_kurz' => $v['merkmal_kurz']));
						if(isset($lang[0]['merkmal_lang'])) {
							$device[$v['tabelle']][] = '<div>'.$lang[0]['merkmal_lang'].': '.$v['wert'].'</div>';
						} else {
							$device[$v['tabelle']][] = '<div>'.$v['merkmal_kurz'].': '.$v['wert'].'</div>';
						}
						$form[] = '<input type="hidden" name="'.$v['tabelle'].'['.$v['merkmal_kurz'].']" value="'.$v['wert'].'">';
					}
				}

				if(is_array($device) && count($device) > 0 && $mode === '') {
					echo '<form action="index.php" method="POST">';
					echo '<input type="hidden" name="index_action" value="plugin">';
					echo '<input type="hidden" name="index_action_plugin" value="bestandsverwaltung">';
					echo '<input type="hidden" name="bestandsverwaltung_action" value="recording">';
					echo '<input type="hidden" name="id" value="'.$id.'">';
					echo '<input type="hidden" name="bezeichner" value="'.$bezeichner.'">';
					echo '<input type="hidden" name="prefix" value="'.$prefix.'">';
					echo '<input type="hidden" name="interval" value="'.$interval.'">';
					echo implode('', $form);
					echo '<div class="clearfix" style="margin-bottom: 15px;width: 100%;">';
					echo ' <div class="float-left">';
					echo '  <label class="" style="border: 0 none;margin: 5px 0 0 0;"><b>Arbeitskarte</b></label>';
					echo ' </div>';
					echo ' <div class="float-right">';
					echo '  <div class="input-group">';
					echo '   <input title="Download as PDF document" type="submit" name="bestand_recording_action[todos][pdf]" value="PDF" class="btn btn-default">';
					echo '  </div>';
					echo ' </div>';
					echo '</div>';
					echo '</form>';
				}

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
				echo '<div>Datum: '.date('Y-m-d H:i:s',$result[0]['date']).'</div>';

				if(in_array('standort', $this->plugins)) {
					$standort = array_search('RAUMBUCHID', array_column($result, 'merkmal_kurz'));
					if($standort !== false) {
						require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
						$raumbuch = new standort($this->db, $this->file);
						$raumbuch->options = $raumbuch->options();
						if(isset($raumbuch->options[$raumbuch->indexprefix.$result[$standort]['wert']])) {
							echo '<div>Standort: '.$raumbuch->options[$raumbuch->indexprefix.$result[$standort]['wert']]['label'].' ('.$result[$standort]['wert'].')</div>';
						}
					}
				}


### TODO derive from bestand.api.class

				if(is_array($device)) {
					$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang',null,'`pos`');
					foreach($tables as $t) {
						$sql  = 'SELECT `merkmal_kurz`,`merkmal_lang`,`datentyp` ';
						$sql .= 'FROM `bestand_'.$t['tabelle_kurz'].'` ';
						$sql .= 'WHERE (';
						$sql .= '`bezeichner_kurz` = \'*\' ';
						$sql .= 'OR `bezeichner_kurz` = \''.$result[0]['bezeichner_kurz'].'\' ';
						$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$result[0]['bezeichner_kurz'].'\' ';
						$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$result[0]['bezeichner_kurz'].',%\' ';
						$sql .= 'OR `bezeichner_kurz` LIKE \''.$result[0]['bezeichner_kurz'].',%\') ';
						$res = $this->db->handler->query($sql);
						if(is_array($res)) {
							foreach($res as $r) {
								if(isset($r['merkmal_lang']) && $r['merkmal_lang'] !== ''){ 
									$table[$t['tabelle_kurz']][$r['merkmal_kurz']]['label'] = $r['merkmal_lang'];
								} else {
									$table[$t['tabelle_kurz']][$r['merkmal_kurz']]['label'] = $r['merkmal_kurz'];
								}
								$table[$t['tabelle_kurz']][$r['merkmal_kurz']]['type'] = $r['datentyp'];
							}
						}
					}

					// handle options
					$opts = $this->db->select('bestand_options',array('row','value'));
					if(is_array($opts)) {
						$options = array();
						foreach($opts as $option) {
							$options[$option['row']] = $option['value'];
						}
						unset($opts);
					}

					// handle $result
					foreach($result as $v) {
						if( isset($table[$v['tabelle']]) && isset($table[$v['tabelle']][$v['merkmal_kurz']]) ) {
							$value = $v['wert'];
							if(isset($options)) {
								if($table[$v['tabelle']][$v['merkmal_kurz']]['type'] === 'select') {
									if(is_numeric($value) && isset($options[$value])) {
										$value = $options[$value];
									}
								}
							}
							$tmp[$v['tabelle']][] = '<div>'.$table[$v['tabelle']][$v['merkmal_kurz']]['label'].': '.$value.'</div>';
						}
						else if($v['tabelle'] === 'TODO') {
							$tmp[$v['tabelle']][] = '<div>'.$v['merkmal_kurz'].': '.$v['wert'].'</div>';
						}
					}

					// handle headline
					if(isset($tmp) && is_array($tmp)) {
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
				}
			} else {
				if($result !== '') {
					echo $result;
				} else {
					echo '<form action="index.php" method="POST">';
					echo '<input type="hidden" name="index_action" value="plugin">';
					echo '<input type="hidden" name="index_action_plugin" value="bestandsverwaltung">';
					echo '<input type="hidden" name="bestandsverwaltung_action" value="recording">';
					echo '<input type="hidden" name="bezeichner" value="'.$bezeichner.'">';
					echo '<input type="hidden" name="prefix" value="'.$prefix.'">';
					echo '<input type="hidden" name="interval" value="'.$interval.'">';
					echo '<div class="clearfix" style="margin-bottom: 15px;width: 100%;">';
					echo ' <div class="float-left">';
					echo '  <label class="" style="border: 0 none;margin: 5px 0 0 0;"><b>Arbeitskarte</b></label>';
					echo ' </div>';
					echo ' <div class="float-right">';
					echo '  <div class="input-group">';
					echo '   <input title="Download as PDF document" type="submit" name="bestand_recording_action[todos][pdf]" value="PDF" class="btn btn-default">';
					echo '  </div>';
					echo ' </div>';
					echo '</div>';
					echo '</form>';
				}
			}

			// get disabled todos
			$mode = '';
			$disabled = array();
			if(is_array($device)) {
				$tdisabled = $this->db->select('todos_disabled','*',array('device'=>$id));
				if(is_array($tdisabled)) {
					foreach($tdisabled as $td) {
						$disabled[$td['todo']] = $td;
					}
				}
				$mode = 'confirm';
			}

			$output = $this->taetigkeiten->details2html($bezeichner, $todofields, $prefix, $interval, $id, $disabled, $mode);
			if($output !== '') {
				echo $output;
			}
		}
	}
	
	//--------------------------------------------
	/**
	 * Disable
	 *
	 * @access private
	 */
	//--------------------------------------------
	function disable($visible = false) {
		if($visible === true) {

			$id     = $this->response->html->request()->get('id');
			$prefix = $this->response->html->request()->get('prefix');
			$todo   = $this->response->html->request()->get('todo');

			if($id !== '' && $prefix !== '' && $todo !== '') {
				$this->response->add('id',$id);
				$this->response->add('prefix',$prefix);
				$this->response->add('todo',$todo);

				$form = $this->response->get_form($this->actions_name, 'disable');

				$check = $this->db->select('todos_disabled','*',array('device'=>$id, 'prefix'=>$prefix, 'todo'=>$todo));
				if(is_array($check)) {
					$submit = $form->get_elements('submit');
					$submit->value = $this->lang['button_enable'];
					$submit->name = $this->response->id.'[submit][on]';
					$form->add($submit,'submit');
					$str  = '<div style="text-align:center;">';
					$str .= '<div style="display:inline-block;">';
					$str .= date('Y-m-d H:i:s', $check[0]['date']).' ('.$check[0]['user'].')<br><br>';
					$str .= $check[0]['comment'];
					$str .= '</div>';
					$str .= '</div>';
					$d['comment'] = $str;
				} else {
					if($check === '') {
						$submit = $form->get_elements('submit');
						$submit->value = $this->lang['button_disable'];
						$submit->name = $this->response->id.'[submit][off]';
						$form->add($submit,'submit');

						$columns = $this->db->handler()->columns($this->db->db, 'todos_disabled');
						$d['comment']['label']                    = $this->lang['label_comment'];
						$d['comment']['css']                      = 'autosize';
						$d['comment']['object']['type']           = 'htmlobject_textarea';
						$d['comment']['object']['attrib']['name'] = 'comment';
						$d['comment']['object']['attrib']['cols'] = 32;
						$d['comment']['object']['attrib']['rows'] = 4;
						if(isset($columns['comment']['length'])) {
							$d['comment']['object']['attrib']['maxlength'] = $columns['comment']['length'];
						}
					} else {
						echo '<div class="msgBox alert alert-danger">'.$check.'</div>';
					}
				}

				$form->id = 'todos_disable_form';
				$form->display_errors = false;
				$form->remove($this->response->id.'[cancel]');
				$form->add($d);

				if(!$form->get_errors() && $this->response->submit()) {
					$mode = $this->response->html->request()->get($this->response->id.'[submit]');
					if(is_array($mode)) {
						$mode = key($mode);
						$data = array();
						if($mode === 'off') {
							$user = $this->user->get();
							$data['device']  = $form->get_static('id');
							$data['prefix']  = $form->get_static('prefix');
							$data['todo']    = $form->get_static('todo');
							($form->get_request('comment') !== '') ? $data['comment'] = $form->get_request('comment') : $data['comment'] = 'No Comment';
							$data['user']    = $user['login'];
							$data['date']    = time();
							$error = $this->db->insert('todos_disabled', $data);
							if($error === '') {
								echo $id.$data['todo'].',off;;';
								exit();
							} else {
								echo '<div class="msgBox alert alert-danger">'.$error.'</div>';
							}
						}
						else if($mode === 'on') {
							$data['device']  = $form->get_static('id');
							$data['prefix']  = $form->get_static('prefix');
							$data['todo']    = $form->get_static('todo');
							$error = $this->db->delete('todos_disabled', $data);
							if($error === '') {
								echo $id.$data['todo'].',on;;';
								exit();
							} else {
								echo '<div class="msgBox alert alert-danger">'.$error.'</div>';
							}
						}
					} else {
						// something went wrong
						echo 'error';
					}
				}

				$t = $this->response->html->template($this->tpldir.'/cafm.one.api.disable.html');
				// disable form on print
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($form);
				$t->group_elements(array('param_' => 'form'));

				echo $t->get_string();
			} else {
				echo '<div class="msgBox alert alert-danger"><b>Error:</b> Missing parameter(s)</div>';
			}
		}
	}
	
	//--------------------------------------------
	/**
	 * Toggle
	 *
	 * @access private
	 */
	//--------------------------------------------
	function toggle($visible = false) {
		if($visible === true) {
		
			$id     = $this->response->html->request()->get('id');
			$prefix = $this->response->html->request()->get('prefix');
			$todo   = $this->response->html->request()->get('todo');

			if($id !== '' && $prefix !== '' && $todo !== '') {
			
				$this->response->add('id',$id);
				$this->response->add('prefix',$prefix);
				$this->response->add('todo',$todo);

				$form = $this->response->get_form($this->actions_name, 'toggle');
				
				$submit = $form->get_elements('submit');
				$submit->value = $this->lang['button_toggle'];
				$submit->name = $this->response->id.'[submit]';
				$form->add($submit,'submit');
				$form->id = 'todos_disable_form';
				$form->display_errors = false;
				$form->remove($this->response->id.'[cancel]');

				$columns = $this->db->handler()->columns($this->db->db, 'todos_disabled');
				$d['comment']['label']                    = $this->lang['label_comment'];
				$d['comment']['css']                      = 'autosize';
				$d['comment']['object']['type']           = 'htmlobject_textarea';
				$d['comment']['object']['attrib']['name'] = 'comment';
				$d['comment']['object']['attrib']['cols'] = 32;
				$d['comment']['object']['attrib']['rows'] = 4;
				if(isset($columns['comment']['length'])) {
					$d['comment']['object']['attrib']['maxlength'] = $columns['comment']['length'];
				}
				$form->add($d);
				
				if(!$form->get_errors() && $this->response->submit()) {
					$user = $this->user->get();
					$data['device']  = $form->get_static('id');
					$data['prefix']  = $form->get_static('prefix');
					($form->get_request('comment') !== '') ? $data['comment'] = $form->get_request('comment') : $data['comment'] = 'No Comment';
					$data['user']    = $user['login'];
					$data['date']    = time();

					$msg = '';
					$todos = explode(',', $form->get_static('todo'));
					if(is_array($todos)) {
						foreach($todos as $do) {
							$data['todo'] = $do;
							$check = $this->db->select('todos_disabled','*',array('device'=>$id, 'prefix'=>$prefix, 'todo'=>$do));
							if(is_array($check)) {
								$error = $this->db->delete('todos_disabled', '`row`=\''.$check[0]['row'].'\'');
								if($error === '') {
									$msg .= $id.$data['todo'].',on;;';
								} else {
									echo '<div class="msgBox alert alert-danger">'.$error.'</div>';
									exit();
								}
							} 
							elseif($check === '') {
								$error = $this->db->insert('todos_disabled', $data);
								if($error === '') {
									$msg .= $id.$data['todo'].',off;;';
								} else {
									echo '<div class="msgBox alert alert-danger">'.$error.'</div>';
									exit();
								}
							} else {
								echo '<div class="msgBox alert alert-danger">'.$error.'</div>';
								exit();
							}
						}
					}
					if($msg !== '') {
						echo $msg;
						exit();
					}
				}
				
				$t = $this->response->html->template($this->tpldir.'/cafm.one.api.disable.html');
				// disable form on print
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($form);
				$t->group_elements(array('param_' => 'form'));

				echo $t->get_string();
			}
		}
	}

	//--------------------------------------------
	/**
	 * Interval
	 *
	 * @access private
	 */
	//--------------------------------------------
	function interval($visible = false) {
		if($visible === true) {
			$prefix = $this->response->html->request()->get('prefix');
			if($prefix !== '') {
				require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
				$taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
				$interval = $taetigkeiten->interval($prefix);
				$d = array();
				if(is_array($interval)) {
					array_unshift($interval,array('key'=>'','label'=>''));
					$d['filter_interval']['label']                       = 'Interval';
					$d['filter_interval']['css']                         = '';
					$d['filter_interval']['object']['type']              = 'htmlobject_select';
					$d['filter_interval']['object']['attrib']['index']   = array('key','label');
					$d['filter_interval']['object']['attrib']['name']    = 'filter[todos][interval]';
					$d['filter_interval']['object']['attrib']['options'] = $interval;
					$d['filter_interval']['object']['attrib']['style']   = 'width: 300px;';
				}

				$attribs = $taetigkeiten->attribs($prefix);
				if(is_array($attribs)) {
					foreach($attribs as $a) {
						$d = array_merge($d, $a['element']);
					}
				}

				$response = $this->response->html->response();
				$response->id = 'todos';
				$form = $response->get_form('xxx', '',false);
				$form->add($d);
				$form->remove('xxx');

				$elements = $form->get_elements();
				if(is_array($elements)) {
					foreach($elements as $e) {
						echo $e->get_string();
					}
				}
			}
		}
	}

}
?>
