<?php
/**
 * bestandsverwaltung_recording_form_identifiers
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

class bestandsverwaltung_recording_form_identifiers
{

var $lang = array();
var $table_prefix = 'bestand_';
var $table_bezeichner = 'bezeichner';

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
		$this->settings   = $controller->settings;

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.class.php');
		$this->bestandsverwaltung = new bestandsverwaltung($this->db);

		$filter = $this->response->html->request()->get('filter');
		if($filter !== '') {
			$this->filter = $filter;
			$this->response->add('filter',$this->filter);
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

		// handle table
		$table = $this->response->html->request()->get('table');
		if($table !== '') {
			$this->table = $this->db->handler()->escape($table);
			$this->response->add('table',$this->table);
			// action remove bezeichner from merkmal
			$remove = $this->response->html->request()->get('remove', true);
			if(isset($remove)) {
				$attrib = $this->db->handler()->escape($this->response->html->request()->get('attrib'));
				$identifier = $this->db->handler()->escape($this->response->html->request()->get('identifier'));
				$result = $this->db->select($this->table_prefix.$this->table, '*', array('merkmal_kurz', $attrib));
				if(is_array($result)) {
					$row = $result[0]['row'];
					$bezeichner = explode(',', $result[0]['bezeichner_kurz']);
					if (($key = array_search($identifier, $bezeichner)) !== false) {
						unset($bezeichner[$key]);
						$bezeichner = implode(',', $bezeichner);
						if($bezeichner === '') {
							$bezeichner = null;
						}
						$error = $this->db->update(
							$this->table_prefix.$this->table,
							array('bezeichner_kurz' => $bezeichner),
							array('row',$row)
						);
						if($error === '') {
							$_REQUEST[$this->message_param] = $this->lang['msg_success'];
						} else {
							$_REQUEST[$this->message_param]['error'] = $error;
						}
					}
				}
			}
		} else {
			$tabellen = $this->db->select($this->table_prefix.'index', 'tabelle_kurz', null, 'pos');
			if(is_array($tabellen)) {
				$this->table = $tabellen[0]['tabelle_kurz'];
				$this->response->add('table',$this->table);
			}
		}

		$response = $this->identifiers();

		$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.identifiers.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($response->form);
		$t->add($response->dataform);
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('filter_' => 'filter'));
		$t->group_elements(array('data_' => 'data'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Devices
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function identifiers() {
		$str = '';
		$response = $this->response;

		#$sql  = 'SELECT bezeichner_kurz, COUNT(*) FROM bezeichner GROUP BY bezeichner_kurz HAVING COUNT(*) > 1';

		$sql  = 'SELECT ';
		$sql .= '`b`.`bezeichner_kurz` as bezeichner, ';
		$sql .= '`b`.`bezeichner_lang` as label ';
		$sql .= 'FROM `'.$this->table_bezeichner.'` AS b ';
		if(isset($this->filter['bezeichner']) && $this->filter['bezeichner'] !== '') {
			// handle counter
			$this->filtered = count($this->db->handler()->query($sql));
			$bez = $this->db->handler()->escape($this->filter['bezeichner']);
			$sql .= 'WHERE `b`.`bezeichner_kurz` LIKE \''.$bez.'\' ';
		}
		$sql .= 'GROUP BY bezeichner, label ';
		$sql .= 'ORDER BY bezeichner';

		$bezeichner = $this->db->handler()->query($sql);

		$form = $response->get_form($this->actions_name, 'identifiers');

		$f = array();
		$d = array();
		if(isset($this->table)) {

			$used_identifiers = 0;
			$output = array();
			if(is_array($bezeichner)) {
				foreach($bezeichner as $b) {
					$mark = $b['bezeichner'];
					$sql  = 'SELECT * ';
					$sql .= 'FROM '.$this->table_prefix.''.$this->table.' ';
					$sql .= 'WHERE `bezeichner_kurz`=\''.$b['bezeichner'].'\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$b['bezeichner'].'\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$b['bezeichner'].',%\' ';
					$sql .= 'OR `bezeichner_kurz`LIKE \''.$b['bezeichner'].',%\' ';
					$sql .= 'OR `bezeichner_kurz`=\'*\' ';
					$sql .= 'ORDER BY `bezeichner_kurz` ';
					$result = $this->db->handler()->query($sql);
					if(is_array($result)) {
						$output[$b['bezeichner']]['attribs'] = $result;
						$output[$b['bezeichner']]['label']   = $b['bezeichner'].' - '.$b['label'];
						$used_identifiers++;
					}
				}
			}

			$table            = $response->html->tablebuilder( 'table_identifiers', $response->get_array() );
			$table->sort      = 'bezeichner';
			$table->order     = 'ASC';
			$table->limit     = 25;
			$table->offset    = 0;
			$table->css       = 'htmlobject_table';
			$table->style     = 'width:100%;';
			$table->id        = 'form_identifiers_select';
			$table->sort_form = false;
			$table->sort_link = false;
			$table->autosort  = true;

			// use faked max
			$table->max = $used_identifiers;
			$table->init();

			$head = array();
			$head['content']['title'] = '&#160;';
			$head['content']['sortable'] = false;
			$head['bezeichner']['title'] = '&#160;';
			$head['bezeichner']['sortable'] = true;
			$head['bezeichner']['hidden'] = true;


			// link params
			$link  = $response->get_url($this->actions_name, 'identifiers');
			$link .= '&'.$table->__id.'[offset]='.$table->offset;

			$min  = $table->offset;
			$max  = $table->offset + $table->limit;
			$body = array();
			$i    = 0;
			foreach($output as $k => $o) {
				if($i >= $min && $i < $max) {
					$panel = $this->response->html->div();
					$panel->css = 'card';
					$panel->style = 'margin: 0 0 20px 0;';

					$h = $this->response->html->div();
					$h->css = 'card-header';
					$h->add($o['label']);

					$dataresponse = $response->html->response();
					$dataresponse->id = 'data_'.$k;
					$dataform = $dataresponse->get_form('dummy', 'identifiers', false);
					$x = array();
					foreach ( $o['attribs'] as $x => $r ) {

						$element = $this->bestandsverwaltung->element($r, 'data_'.$x, 'data', array(), $this->table_prefix, false);
						$dataform->add($element);

						// handel * in $o['attribs']['bezeichner_kurz']
						if(strpos($r['bezeichner_kurz'], '*') === false) {
							$key = key($element);

							$a = $this->response->html->a();
							$a->href    = $link.'&remove=true&identifier='.$k.'&attrib='.$r['merkmal_kurz'];
							$a->title   = sprintf($this->lang['button_title_remove_attrib_identifier'], $k);
							$a->css     = 'icon icon-trash btn btn-default btn-sm float-left';
							$a->style   = 'margin: 4px 0 0 8px; display: inline-block;';
							$a->handler = 'onclick="remove.confirm(this,\''.$k.'\',\''.$r['merkmal_kurz'].'\');return false;"';

							$tmp = $dataform->get_elements($key);
							$tmp->__elements[0]->css = $tmp->__elements[0]->css.' float-left';
							$tmp->__elements[0]->style ='width: auto;';
							$tmp->add($a);

							$dataform->add(array($key => $tmp));
						}
					}

					$panel->add($h->get_string());
					$panel->add('<div class="card-body">');
					$panel->add($dataform->get_elements());
					$panel->add('</div>');

					$body[] = array('content' => $panel, 'bezeichner' => $k);
				} else {
					$body[] = array('content' => 'empty', 'bezeichner' => $k);
				}
				$i++;
			}

			$table->head = $head;
			$table->body = $body;

			$d['data__0'] = $table;

			// Counter
			$counter = $this->response->html->div();
			$counter->add($this->lang['label_identifiers'].': ');
			if(isset($this->filtered)) {
				if(is_array($bezeichner)) {
					$count = count($bezeichner).' / '.$this->filtered;
				} else {
					$count = '0 / '.$this->filtered;
				}
			} else {
				$num   = count($bezeichner);
				$count = $num.' / '.$num;
			}
			$counter->add($count);
			$f['counter']['object'] = $counter;

			$options = $this->db->select($this->table_prefix.'index', 'tabelle_kurz,tabelle_lang', null, 'pos');
			$f['tables']['label']                        = $this->lang['label_index'];
			$f['tables']['object']['type']               = 'htmlobject_select';
			$f['tables']['object']['attrib']['index']    = array('tabelle_kurz','tabelle_lang');
			$f['tables']['object']['attrib']['name']     = 'table';
			$f['tables']['object']['attrib']['id']       = 'table';
			$f['tables']['object']['attrib']['options']  = $options;
			$f['tables']['object']['attrib']['handler']  = 'onchange="phppublisher.wait();this.form.submit();"';
			$f['tables']['object']['attrib']['selected'] = array($this->table);

			$f['filter_bezeichner']['label']                         = $this->lang['label_identifier'];
			$f['filter_bezeichner']['object']['type']                = 'htmlobject_input';
			$f['filter_bezeichner']['object']['attrib']['name']      = 'filter[bezeichner]';
			$f['filter_bezeichner']['object']['attrib']['title']     = 'Example: CO_A or BK%';
			$f['filter_bezeichner']['object']['attrib']['maxlength'] = 30;

			// unset double entry from response
			/*
			$form->remove('filter[showempty]');
			$f['filter_empty']['label']                    = 'show empty';
			$f['filter_empty']['object']['type']           = 'htmlobject_input';
			$f['filter_empty']['object']['attrib']['type'] = 'checkbox';
			$f['filter_empty']['object']['attrib']['name'] = 'filter[showempty]';
			*/
			$f['filter_empty'] = '';

		} else {
			$form->add('','submit');
			$div = $this->response->html->div();
			$div->add('Error: No data table found');
			$f['data_Error']['object'] = $div;
			$f['tables'] = '';
			$f['filter_empty'] = '';
			$f['counter'] = '0 / 0';
		}

		$form->display_errors = false;
		$form->add($f);

		$response->form = $form;
		$response->dataform = $d;

		return $response;
	}

}
?>
