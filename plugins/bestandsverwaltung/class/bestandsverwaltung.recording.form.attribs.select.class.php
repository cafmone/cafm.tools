<?php
/**
 * bestandsverwaltung_recording_form_attribs_select
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

class bestandsverwaltung_recording_form_attribs_select
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
	 * Init db settings
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function init() {
		// table
		$table = $this->response->html->request()->get('table');
		if($table !== '') {
			$this->table = $this->db->handler()->escape($table);
			$this->response->add('table',$this->table);
		} else {
			$tabellen = $this->db->select($this->table_prefix.'index', 'tabelle_kurz',null,'pos');
			if(is_array($tabellen)) {
				$this->table = $tabellen[0]['tabelle_kurz'];
			} else {
				$this->table = '';
			}
		}

		// attribs
		$sql  = 'SELECT * ';
		$sql .= 'FROM '.$this->table_prefix.$this->table.' ';
		if(isset($this->filter['merkmal']) && $this->filter['merkmal'] !== '') {
			// handle counter
			$this->filtered = count($this->db->handler()->query($sql));
			$m = $this->db->handler()->escape($this->filter['merkmal']);
			$sql .= 'WHERE `merkmal_kurz` LIKE \''.$m.'\' ';
		}
		$sql .= ' ORDER BY `row`';
		$this->attribs = $this->db->handler()->query($sql);
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
		$this->init();

		$response = $this->attribs();

		$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.form.attribs.select.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($response->form);
		$t->add($response->dataform);
		$t->add('', 'counter');

		if(isset($this->table)) {
			$a = $this->response->html->a();
			$a->title = $this->lang['button_title_add_attrib'];
			$a->css = 'icon icon-plus btn btn-sm btn-default noprint';
			$a->handler = 'onclick="phppublisher.wait(\'Loading ...\');"';
			$a->style = 'margin: 5px 10px 0 0;';
			$a->href = $this->response->get_url($this->actions_name,'insert').'&table='.$this->table;
			$t->add($a, 'new');
		}


		// Counter
		$counter = $this->response->html->div();
		if(isset($this->filtered)) {
			if(is_array($this->attribs)) {
				$count = $this->lang['label_attribs'].': '.count($this->attribs).' / '.$this->filtered;
			} else {
				$count = $this->lang['label_attribs'].': '.'0 / '.$this->filtered;
			}
		} else {
			$num   = count($this->attribs);
			$count = $this->lang['label_attribs'].': '.$num.' / '.$num;
		}
		$counter->add($count);
		$t->add($counter, 'counter');

		// assemble boxes 
		if(isset($this->attribs) && is_array($this->attribs)) {

			$i = 0;
			foreach($this->attribs as $r) {
			
				$a = $this->response->html->a();
				$a->title = sprintf($this->lang['button_title_edit_attrib'], $r['merkmal_kurz']);
				$a->css = 'icon icon-edit btn btn-sm btn-default noprint';
				$a->handler = 'onclick="phppublisher.wait(\'Loading ...\');"';
				$a->style = 'float:right;margin:0 0 0 3px';
				$a->href = $this->response->get_url($this->actions_name,'insert').'&row='.$r['row'].'&table='.$this->table;

				if($i !== 0) {
					$move = $this->response->html->a();
					$move->title = sprintf($this->lang['button_title_move_attrib'], $r['merkmal_kurz']);
					$move->css = 'icon icon-menu-up btn btn-sm btn-default noprint';
					$move->handler = 'onclick="phppublisher.wait(\'Loading ...\');"';
					$move->style = 'float:right;margin:0 0 0 0';
					$move->href = $this->response->get_url($this->actions_name,'move').'&row='.$r['row'].'&table='.$this->table;
				} else {
					$move = '';
					$i = 1;
				}

				$box = $this->response->html->div();
				$box->id = $r['merkmal_kurz'];
				$box->css = 'card';
				$box->style = 'margin: 0 0 20px 0;';
				$box->add('<div class="card-header">');
				$box->add($a);
				$box->add($move);
				$box->add('<div class="float-left">');
				$box->add($t->get_elements('data_'.$r['merkmal_kurz']));
				$box->add('</div>');
				$box->add('<div class="float-left" style="margin: 7px 0 0 10px"><i>'.$r['merkmal_kurz'].'</i></div>');
				$box->add('<div class="floatbreaker">&#160;</div>');
				$box->add('</div>');
				
				$box->add('<div class="card-body">');
				$box->add($t->get_elements('data_'.$r['merkmal_kurz'].'_bez'));
				$box->add('</div>');

				$t->remove('data_'.$r['merkmal_kurz']);
				$t->remove('data_'.$r['merkmal_kurz'].'_bez');
				$t->add($box, 'data_'.$r['merkmal_kurz']);
			}
		}

		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('filter_' => 'filter'));
		$t->group_elements(array('data_' => 'data'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Attribs
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function attribs() {
		$str = '';
		$sql  = 'SELECT bezeichner_kurz as bezeichner, bezeichner_lang as label ';
		$sql .= 'FROM '.$this->table_bezeichner.' ';
		$sql .= 'GROUP BY bezeichner_kurz, bezeichner_lang ';
		$sql .= 'ORDER BY bezeichner_kurz';

		$result = $this->db->handler()->query($sql);
		$bezeichner = array();
		if(is_array($result)) {
			foreach($result as $b) {
				$bezeichner[$b['bezeichner']] = $b['label'];
			}
			$mark = $b['bezeichner'];
		}

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select');

		$dataresponse = $response->response('data');
		$dataresponse->id = 'data';
		$dataform = $dataresponse->get_form('dummy', 'identifiers', false);

		$d = array();
		$f = array();

		if(isset($this->table)) {

			$used = array();
			if(is_array($this->attribs)) {
				$i = 1;
				foreach ( $this->attribs as $k => $r ) {

					$element = $this->bestandsverwaltung->element($r, 'data', 'data', array(), $this->table_prefix, false);
					$element['data_'.$r['merkmal_kurz']]['css'] = 'autosize';
					$element['data_'.$r['merkmal_kurz']]['style'] = 'margin-bottom:0;';
					$d = array_merge($d, $element);

					// box
					$box = $this->response->html->div();
					$box->css = 'list-group';

					if(isset($r['bezeichner_kurz']) && $r['bezeichner_kurz'] !== '') {
						$h = $this->response->html->customtag('div');
						$h->css = 'list-group';

						$tmp = explode(',', $r['bezeichner_kurz']);
						sort($tmp);

						foreach($tmp as $v) {
							if(array_key_exists($v, $bezeichner)) {
								$h->add('<div>'.$v.' - '.$bezeichner[$v].'</div>');
							} else {
								$h->add($v.'<br>');
							}
						}
					} else {
						$h = $this->response->html->customtag('div');
						$h->style = 'margin: 0 0 10px 10px;';
						$h->add('&#160;');
					}
					$box->add('<div>'.$h->get_string().'</div>');

					$d['data_'.$r['merkmal_kurz'].'_bez']['object'] = $box;
					$i++;
				}
			} else {
				if(is_string($result) && $result !== '') {
					$d['data_empty'] = $result;
				} else {
					$d['data_empty'] = '';
				}
			}

			$options = $this->db->select($this->table_prefix.'index', 'tabelle_kurz,tabelle_lang', null, 'pos');
			$f['tables']['label']                        = $this->lang['label_index'];
			$f['tables']['object']['type']               = 'htmlobject_select';
			$f['tables']['object']['attrib']['index']    = array('tabelle_kurz','tabelle_lang');
			$f['tables']['object']['attrib']['name']     = 'table';
			$f['tables']['object']['attrib']['id']       = 'table';
			$f['tables']['object']['attrib']['options']  = $options;
			$f['tables']['object']['attrib']['handler']  = 'onchange="phppublisher.wait();this.form.submit();"';
			$f['tables']['object']['attrib']['selected'] = array($this->table);

			$f['filter_merkmal']['label']                         = $this->lang['label_attrib_filter'];
			$f['filter_merkmal']['object']['type']                = 'htmlobject_input';
			$f['filter_merkmal']['object']['attrib']['name']      = 'filter[merkmal]';
			$f['filter_merkmal']['object']['attrib']['maxlength'] = 30;

		} else {
			$form->add('','submit');
			$div = $this->response->html->div();
			$div->add('Error: No data found in bestand_index');
			$d['data_Error']['object'] = $div;
			$d['tables'] = '';
			$d['new'] = '';
		}

		$dataform->display_errors = false;
		$dataform->add($d);

		$form->display_errors = false;
		$form->add($f);

		$response->form = $form;
		$response->dataform = $dataform;

		return $response;

	}

}
?>
