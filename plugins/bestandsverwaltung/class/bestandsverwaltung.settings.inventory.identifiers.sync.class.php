<?php
/**
 * bestandsverwaltung_settings_inventory_identifiers_sync
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
 *  Copyright (c) 2008-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_identifiers_sync
{

var $lang = array();

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
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->user       = $controller->user;

		$this->plugins = $this->file->get_ini($controller->profilesdir.'/plugins.ini');
		if(in_array('cafm.one', $this->plugins)) {
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
			$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
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
	function action($action = null) {

		if(isset($this->taetigkeiten)) {

			$errors  = array();
			$response = $this->response;

			$sql  = 'SELECT ';
			$sql .= 'b.bezeichner_kurz as bezeichner_kurz, ';
			$sql .= 'b.bezeichner_lang as bezeichner_lang, ';
			$sql .= 'b.status as status, ';
			$sql .= 'b.din_276 as din_276, ';
			$sql .= 'h.text as help ';
			$sql .= 'FROM bezeichner AS b ';
			$sql .= 'LEFT JOIN bezeichner_help AS h ON (b.bezeichner_kurz=h.bezeichner_kurz) ';
			$sql .= 'GROUP BY bezeichner_kurz,bezeichner_lang,status,din_276,help';
			$data = $this->db->handler()->query($sql);

			$fields = array();
			if(is_array($data)) {
				foreach($data as $d) {
					$fields[$d['bezeichner_kurz']] = $d;
				}
			} else {
				//var_dump($data);
			}
			$bezeichner = $this->taetigkeiten->identifiers();

			if(is_array($bezeichner)) {
				$confirm = $this->response->html->request()->get('confirm');
				if($confirm === '') {
					$confirm = array();
				}
				$body = array();

				foreach($bezeichner as $b) {
					$update = array();
					if(array_key_exists($b['bezeichner_kurz'], $fields)) {

						// lang
						if(
							isset($b['bezeichner_lang']) && 
							$b['bezeichner_lang'] !== '' && 
							$b['bezeichner_lang'] !== $fields[$b['bezeichner_kurz']]['bezeichner_lang']
						){
							$update['bezeichner_lang'] = $b['bezeichner_lang'];
						}

						// din
						if(
							isset($b['din_276']) && 
							$b['din_276'] !== '' && 
							$b['din_276'] !== $fields[$b['bezeichner_kurz']]['din_276']
						){
							$update['din_276'] = $b['din_276'];
						}

						// help
						if(
							isset($b['help']) && 
							$b['help'] !== '' && 
							$b['help'] !== $fields[$b['bezeichner_kurz']]['help']
						){
							$update['help'] = $b['help'];
						}

						// status
						if(
							isset($b['status']) && 
							$b['status'] === 'obsolete' && 
							$b['status'] !== $fields[$b['bezeichner_kurz']]['status']
						){
							$update['status'] = $b['status'];
						}

						// handle update
						if(count($update) > 0) {
							if(in_array($b['bezeichner_kurz'], $confirm)) {
								$error = '';
								if(isset($update['help'])) {
									if(isset($fields[$b['bezeichner_kurz']]['help'])) {
										$error = $this->db->update(
												'bezeichner_help',
												array('text' => $b['help']),
												array('bezeichner_kurz'=>$b['bezeichner_kurz'])
											);
									} else {
										$h = array();
										$h['bezeichner_kurz'] = $b['bezeichner_kurz'];
										$h['text'] = $b['help'];
										$error = $this->db->insert('bezeichner_help',$h);
									}
								}
								// remove help
								unset($update['help']);
								if($error === '' && count($update) > 0) {
									$error = $this->db->update(
											'bezeichner',
											$update,
											array('bezeichner_kurz'=>$b['bezeichner_kurz'])
										);
								}
								if($error !== '') {
									$errors[] = $error;
								} else {
									$sucessmsg = 'successfully updated';
								}
							} else {
								$i = $this->response->html->a();
								$i->href    = $this->response->get_url($this->actions_name, 'sync' ).'&confirm['.$b['bezeichner_kurz'].']='.$b['bezeichner_kurz'];
								$i->title   = sprintf($this->lang['button_title_sync_identifier'], $b['bezeichner_kurz']);
								$i->css     = 'icon icon-sync btn btn-default btn-sm';
								$i->style   = 'margin: 0 0 0 0; display: inline-block;';
								$i->handler = 'onclick="phppublisher.wait();"';

								$x = array();
								$x['mode'] = 'update: '.implode(', ', array_keys($update));
								$x['bezeichner'] = $b['bezeichner_kurz'];
								$x['lang'] = $b['bezeichner_lang'];
								$x['func'] = $i->get_string();

								$body[] = $x;
							}
						}
					} else {
						if(!isset($b['status'])) {
							$b['status'] = 'on';
						}
						if(in_array($b['bezeichner_kurz'], $confirm)) {
							$error = '';
							if(isset($b['help'])) {
								$check = $this->db->select('bezeichner_help', 'bezeichner_kurz', array('bezeichner_kurz'=>$b['bezeichner_kurz']));
								if($check !== '') {
									$error = $this->db->update(
											'bezeichner_help',
											array('text' => $b['help']),
											array('bezeichner_kurz'=>$b['bezeichner_kurz'])
										);
								} else {
									$h = array();
									$h['bezeichner_kurz'] = $b['bezeichner_kurz'];
									$h['text'] = $b['help'];
									$error = $this->db->insert('bezeichner_help',$h);
								}
							}
							// remove help
							unset($b['help']);
							if($error === '' && count($b) > 0) {
								$error = $this->db->insert('bezeichner', $b);
							}
							if($error !== '') {
								$errors[] = $error;
							} else {
								$sucessmsg = 'successfully inserted';
							}
						} else {
							$i = $this->response->html->a();
							$i->href    = $this->response->get_url($this->actions_name, 'sync' ).'&confirm['.$b['bezeichner_kurz'].']='.$b['bezeichner_kurz'];
							$i->title   = sprintf($this->lang['button_title_insert_identifier'], $b['bezeichner_kurz']);
							$i->css     = 'icon icon-plus btn btn-default btn-sm';
							$i->style   = 'margin: 0 0 0 0; display: inline-block;';
							$i->handler = 'onclick="phppublisher.wait();"';

							$x = array();
							$x['mode'] = 'insert';
							$x['bezeichner'] = $b['bezeichner_kurz'];
							$x['lang'] = $b['bezeichner_lang'];
							$x['func'] = $i->get_string();

							$body[] = $x;
						}
					}
				}
			}

		} else {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'select'
				)
			);
		}

		// handle error
		if(count($errors) > 0) {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'sync',
					$this->message_param,
					implode('<br>', $errors)
				)
			);
		}
		else if(isset($sucessmsg)) {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'sync',
					$this->message_param,
					$sucessmsg
				)
			);
		}


		if(isset($body)) {
			$table                  = $response->html->tablebuilder( 'settings_identifiers_sync', $response->get_array() );
			$table->form_action     = $this->response->html->thisfile;
			$table->sort            = 'bezeichner';
			$table->order           = 'ASC';
			$table->limit           = count($body);
			$table->offset          = 0;
			$table->css             = 'htmlobject_table table table-bordered';
			$table->id              = 'fault_select';
			$table->sort_form       = false;
			$table->sort_link       = true;
			$table->autosort        = true;
			$table->identifier      = 'bezeichner';
			$table->identifier_name = 'confirm';
			$table->actions_name    = $this->actions_name;
			$table->actions         = array(array('sync'=>$this->lang['button_sync']));

			$head   = array();
			$head['bezeichner']['title'] = 'Kurz';
			$head['bezeichner']['sortable'] = true;
			$head['bezeichner']['style'] = 'width:100px;';

			$head['lang']['title'] = 'Lang';
			$head['lang']['sortable'] = true;

			$head['mode']['title'] = 'Changes';
			$head['mode']['sortable'] = false;

			$head['func']['title'] = '&#160;';
			$head['func']['sortable'] = false;
			$head['func']['style'] = 'width:20px;';

			$table->max  = count($body);
			$table->head = $head;
			$table->body = $body;
			$table->add_headrow('<h3>'.$this->lang['headline_sync_identifiers'].'</h3>');

			#$response->table = $table;
			return $table;
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
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			// handle bezeichner
			$bezeichner = $form->get_request('bez');
			$check = $this->db->select('bezeichner', 'bezeichner_kurz', array('bezeichner_kurz'=>$bezeichner['bezeichner_kurz']));
			if($check === '') {
				$error = $this->db->insert('bezeichner',$bezeichner);
				// hnadle help
				if($error === '') {
					$help = $form->get_request('help');
					if($help !== '') {
						$d['bezeichner_kurz'] = $bezeichner['bezeichner_kurz'];
						$d['text'] = $help;
						$error = $this->db->insert('bezeichner_help',$d);
					}
				}
			} else {
				$error = 'ERROR: bezeichner_kurz '.$bezeichner['bezeichner_kurz'].' is already in use';
				$form->set_error('bez[bezeichner_kurz]',$error);
			}

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = 'sucess';
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
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			// handle bezeichner
			$bezeichner = $form->get_request('bez');
			$error = $this->db->update(
					'bezeichner',
					$bezeichner,
					array('bezeichner_kurz'=>$this->bezeichner)
				);
			// hnadle help
			if($error === '') {
				$help  = $form->get_request('help');
				$check = $this->db->select('bezeichner_help', 'bezeichner_kurz', array('bezeichner_kurz'=>$this->bezeichner));

### TODO check is null -> insert

				if($check !== '') {
					$error = $this->db->update(
							'bezeichner_help',
							array('text' => $help),
							array('bezeichner_kurz'=>$this->bezeichner)
						);
				} else {
					$d['bezeichner_kurz'] = $this->bezeichner;
					$d['text'] = $help;
					$error = $this->db->insert('bezeichner_help',$d);
				}
			}

			// handle error
			if($error !== '') {
				$response->error = $error;
			} else {
				$response->msg = 'sucess';
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
		$form = $response->get_form($this->actions_name, 'insert');

		$fields = array();
		if(isset($this->fields)) {
			$fields = $this->fields;
		}

		$hcolumns = $this->db->handler()->columns($this->db->db, 'bezeichner_help', 'text');
		$bcolumns = $this->db->handler()->columns($this->db->db, 'bezeichner', 'bezeichner_lang');

		if(isset($this->bezeichner)) {
			$d['id'] = '';
			if(isset($fields['bl'])) {
				$d['bezeichner'] = $fields['bl'].' ('.$this->bezeichner.')';
			} else {
				$d['bezeichner'] = $this->bezeichner;
			}
		} else {

			$d['bezeichner'] = 'New';

			$d['id']['label']                         = 'bezeichner_kurz';
			$d['id']['required']                      = true;
			$d['id']['validate']['regex']             = '/^[A-Z0-9_]+$/';
			$d['id']['validate']['errormsg']          = sprintf('%s must be A-Z 0-9 or _', 'bezeichner_kurz');
			$d['id']['object']['type']                = 'htmlobject_input';
			$d['id']['object']['attrib']['name']      = 'bez[bezeichner_kurz]';
			if(isset($bcolumns['bezeichner_kurz']['length'])) {
				$d['id']['object']['attrib']['maxlength'] = $bcolumns['bezeichner_kurz']['length'];
			}
		}

		$d['bezeichner_lang']['label']                    = 'bezeichner_lang';
		$d['bezeichner_lang']['required']                 = true;
		$d['bezeichner_lang']['object']['type']           = 'htmlobject_input';
		$d['bezeichner_lang']['object']['attrib']['name'] = 'bez[bezeichner_lang]';
		$d['bezeichner_lang']['object']['attrib']['style'] = 'width:400px;';
		if(isset($bcolumns['bezeichner_lang']['length'])) {
			$d['bezeichner_lang']['object']['attrib']['maxlength'] = $bcolumns['bezeichner_lang']['length'];
		}
		if(isset($fields['bl'])) {
			$d['bezeichner_lang']['object']['attrib']['value'] = $fields['bl'];
		}

		// Status
		$states[] = array('','');
		$states[] = array('on','On');
		$states[] = array('off','Off');
		$states[] = array('obsolete','Obsolete');

		$d['status']['label']                       = $this->lang['label_state'];
		$d['status']['required']                    = true;
		$d['status']['object']['type']              = 'htmlobject_select';
		$d['status']['object']['attrib']['index']   = array(0,1);
		$d['status']['object']['attrib']['options'] = $states;
		$d['status']['object']['attrib']['name']    = 'bez[status]';
		if(isset($fields['status'])) {
			$d['status']['object']['attrib']['selected'] = array($fields['status']);
		}

		$d['help']['label']                    = 'help';
		$d['help']['object']['type']           = 'htmlobject_textarea';
		$d['help']['object']['attrib']['name'] = 'help';
		$d['help']['object']['attrib']['cols'] = 50;
		$d['help']['object']['attrib']['rows'] = 10;
		$d['help']['object']['attrib']['style'] = 'width:400px;';
		if(isset($hcolumns['text']['length'])) {
			$d['help']['object']['attrib']['maxlength'] = $hcolumns['text']['length'];
			$d['help']['object']['attrib']['title'] = 'maxlength: '.$hcolumns['text']['length'];
		}
		if(isset($fields['ht'])) {
			$d['help']['object']['attrib']['value'] = $fields['ht'];
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;
		return $response;
	}

}
?>
