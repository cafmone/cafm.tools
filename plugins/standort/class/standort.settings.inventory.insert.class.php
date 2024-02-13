<?php
/**
 * standort_settings_inventory_insert
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_insert
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
		$this->datadir    = $this->controller->datadir;

		$tables     = $this->db->select($this->settings['query']['prefix'].'index',array('tabelle_kurz','tabelle_lang'));
		if(is_array($tables)) {
			foreach($tables as $table) {
				$this->tables[$table['tabelle_kurz']] = $table['tabelle_lang'];
			}
		} else {
			$this->tables = array();
		}
		
		$this->standort   = $controller->standort;

		$id = $this->response->html->request()->get('id');
		if( $id !== '') {
			$this->id = $id;
			$this->response->add('id', $this->id);
			$values = $this->db->select($this->settings['query']['content'], array('row','bezeichner_kurz','tabelle','parent_id','merkmal_kurz','wert'), array('id', $this->id));
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

		require_once(CLASSDIR.'lib/formbuilder/formbuilder.class.php');
		$this->formbuilder = new formbuilder($this->db);
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
			$t = $response->html->template($this->tpldir.'standort.settings.inventory.insert.html');
			$t->add($response->html->thisfile,'thisfile');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));

### TODO no elements when in prolog
			
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
			
			
			$t->group_elements(array('merkmal_' => 'merkmale'));

			if(isset($this->id)) {
				$files = '';
				$upload = '';
				if($this->file->exists($this->datadir)){

					// handle files
					if(method_exists($this->controller, 'files')) {
						$files = $this->controller->files(true);
						$files->message_param = $this->message_param;

						$str = '<div class="files noprint fieldset">';
						$str .= $files->action()->get_string();
						$str .= '</div>';

						$content[1]['label']   = $this->lang['tab_files'];
						$content[1]['value']   = $str;
						$content[1]['target']  = '#standort_insert_tab1';
						$content[1]['request'] = null;
						$content[1]['onclick'] = true;
						$content[1]['id']  = 'Id2';
					}

					// build tabs
					$content[0]['label']   = $this->lang['tab_attribs'];
					$content[0]['value']   = $t;
					$content[0]['target']  = '#standort_insert_tab0';
					$content[0]['request'] = null;
					$content[0]['onclick'] = true;
					$content[0]['active']  = true;
					$content[0]['id']  = 'Id1';

					if(isset($_REQUEST[$files->message_param])) {
						$_REQUEST[$this->message_param] = $_REQUEST[$files->message_param];
					}
				}

				$tab = $this->response->html->tabmenu('standort_insert_tab');
				$tab->message_param = $this->message_param;
				$tab->css = 'tabs right noprint';
				$tab->auto_tab = false;
				$tab->add($content);

				$out = $this->response->html->div();
				$out->add('<div id="recording_insert_top" style="margin:-15px 0 15px 0; clear:both;" class="floatbreaker">&#160;</div>');
				$out->add($tab);

			} else {

				// build tabs
				$content[0]['label']   = '&#160;';
				$content[0]['value']   = $t;
				$content[0]['target']  = '#standort_insert_tab0';
				$content[0]['request'] = null;
				$content[0]['onclick'] = true;
				$content[0]['active']  = true;
				$content[0]['id']      = 'Id1';
				$content[0]['hidden']  = true;

				$tab = $this->response->html->tabmenu('standort_insert_tab');
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
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'update', $this->message_param, $response->msg
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

		$options = $this->standort->levels();
		array_unshift($options, array('id' => '', 'label' => ''));

		$d['bezeichner']['label']                        = $this->lang['label_identifier'];
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
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'select'
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
				$f[$table] = $form->get_request($table);
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

				$check = $this->db->select($this->settings['query']['content'], array('row'), array('id', $d['id']));
				if($check === '') {
					$error = $this->db->insert($this->settings['query']['content'], $d);
					if($error === '') {
						foreach($f as $key => $value) {
							if(is_array($value)) {
								foreach($value as $k => $v) {
									$d['tabelle'] = $key;
									$d['merkmal_kurz'] = $k;
									$d['wert'] = $v;
									$error = $this->db->insert($this->settings['query']['content'], $d);
								}
							}
						}
						if($error === '') {
							$response->id  = $d['id'];
							$response->msg = sprintf($this->lang['msg_insert_success'], $d['id']);
						} else {
							$response->error = $error;
						}
					} else {
						$response->error = $error;
					}
				} else {
					$response->error = sprintf($this->lang['error_exists'], $d['id']);
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
				$r[$table] = $form->get_request($table, true);
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
										$this->settings['query']['content'],
										array('row',$this->fields[$key][$k]['row'])
									);
								} else {
									// update
									$error = $this->db->update(
										$this->settings['query']['content'],
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
								$error = $this->db->insert($this->settings['query']['content'], $d);
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
#						$this->settings['query']['content'],
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
							$this->settings['query']['content'],
							array('wert' => $response->html->request()->get('NAME')),
							array('id' => $this->id, 'row' => $this->fields['NAME']['row'], 'merkmal_kurz' => 'NAME')
						);
					}
					if(isset($error) && $error !== '') {
						$response->error = $error;
					} else {
						$response->id  = $this->id;
						$response->msg = sprintf($this->lang['msg_update_success'], $this->id);
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
		$columns  = $this->db->handler()->columns($this->db->db, $this->settings['query']['content']);

		$d = array();
		$d['toplink'] = '<div class="noprint" style="display: inline;text-align:right;margin:0 5px 0 10px;"><a class="icon icon-menu-up" id="insertbottom" href="#top"></a></div>';
		$d['bottomlink'] = '<div class="noprint" style="position: absolute; top: 2px; right: 10px;"><a class="icon icon-menu-down" href="#insertbottom"></a></div>';
		$d['float'] = 'none';

		if(isset($this->id)) {
			$form = $response->get_form($this->actions_name, 'update');
		} else {
			$form = $response->get_form($this->actions_name, 'insert');
		}

		if(!isset($this->id)) {
			$d['id']['label']                        = $this->lang['label_id'];
			$d['id']['required']                     = true;
			$d['id']['object']['type']               = 'htmlobject_input';
			$d['id']['object']['attrib']['name']     = 'newid';
			$d['id']['object']['attrib']['value']    = uniqid('s');
			if(isset($columns['id']['length'])) {
				$d['id']['object']['attrib']['maxlength'] = $columns['id']['length'];
			}
		} else {
			$d['id']['label']                        = $this->lang['label_id'];
			$d['id']['static']                       = true;
			$d['id']['object']['type']               = 'htmlobject_input';
			$d['id']['object']['attrib']['name']     = 'idxxx';
			$d['id']['object']['attrib']['disabled'] = true;
			$d['id']['object']['attrib']['value']    = $this->id;
		}

		if(isset($this->ebene) && $this->ebene !== '') {
			$d['bezeichner']['label']                        = $this->lang['label_identifier'];
			$d['bezeichner']['static']                       = true;
			$d['bezeichner']['object']['type']               = 'htmlobject_input';
			$d['bezeichner']['object']['attrib']['name']     = 'bezeichner';
			$d['bezeichner']['object']['attrib']['disabled'] = true;
			$d['bezeichner']['object']['attrib']['value']    = $this->ebene;

			$d['name']['label']                    = $this->lang['label_name'];
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
				$d['parent']['label']                        = $this->lang['label_parent'];
				$d['parent']['static']                       = true;
				$d['parent']['object']['type']               = 'htmlobject_input';
				$d['parent']['object']['attrib']['name']     = 'parent';
				$d['parent']['object']['attrib']['value']    = $this->parent;
				$d['parent']['object']['attrib']['disabled'] = true;
			} 
			else if(array_key_exists('parent', $fields)) {
				$d['parent']['label']                        = $this->lang['label_parent'];
				$d['parent']['static']                       = true;
				$d['parent']['object']['type']               = 'htmlobject_input';
				$d['parent']['object']['attrib']['name']     = 'parent';
				$d['parent']['object']['attrib']['value']    = $fields['parent'];
				$d['parent']['object']['attrib']['disabled'] = true;
			} else {
				$d['parent'] = '';
			}

			// Get attribs from tables
			$result = array();
			if(is_array($this->tables)) {
				foreach($this->tables as $k => $t) {
					$sql  = 'SELECT * ';
					$sql .= 'FROM '.$this->settings['query']['prefix'].$k.' ';
					$sql .= 'WHERE `bezeichner_kurz`=\''.$this->ebene.'\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$this->ebene.'\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$this->ebene.',%\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \''.$this->ebene.',%\' ' ;
		 			// Wildcard *
					$sql .= 'OR `bezeichner_kurz`=\'*\' ';
					$sql .= 'ORDER BY `row` ';
					$result[$k] = $this->db->handler()->query($sql);
				}
			}
			if(is_array($result) && count($result) > 0) {
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
							
							$table = $this->settings['query']['prefix'];
							
							
							$d = array_merge($d, $this->formbuilder->element($r, $k, $k, $fields, $table, $addempty));
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

			#### TODO
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
