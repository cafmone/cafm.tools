<?php
/**
 * bestandsverwaltung_recording
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

class bestandsverwaltung_recording_step1
{

var $next = 'insert';

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
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->user = $controller->user;
		$this->controller = $controller;

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');
		if(in_array('cafm.one', $this->plugins)) {
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
			$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
			$this->taetigkeiten = $this->taetigkeiten->prefixes();
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
		$response = $this->insert();
		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}

			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.recording.step1.html');
			$t->add($response->form);
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->group_elements(array('bezeichner_' => 'bezeichner'));
			$t->group_elements(array('param_' => 'form'));
			return $t;
		} else {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, $this->next, $this->message_param, $response->msg
				)
			);
		}
		
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->get_response('insert');
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$f = $form->get_request(null, true);
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$this->response->add('bezeichner', $f['bezeichner']);
				$response->msg = '';
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
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'step1', false);
		$gewerk   = $this->response->html->request()->get('filter[gewerk]');
		$todos    = $this->response->html->request()->get('filter[todos]');

		$where = '';
		if ($todos !== '' && $gewerk !== ''){
			if(isset($this->taetigkeiten[$todos]['bezeichner'])) {
				$bezeichner = array_intersect($this->gewerke->gewerk2sql($gewerk, true), $this->taetigkeiten[$todos]['bezeichner']);
			}
			if(is_array($bezeichner) && count($bezeichner) > 0) {
				$i = 0;
				foreach($bezeichner as $b) {
					if($b === '*') {
						$where = '';
						break;
					}
					if($i === 0) {
						$where = 'WHERE `b`.`bezeichner_kurz`=\''.$b.'\'';
					} else{
						$where .= ' OR `b`.`bezeichner_kurz`=\''.$b.'\'';
					}
					$i = 1;
				}
			}
			else if(!isset($bezeichner) || $bezeichner === '' || count($bezeichner) < 1) {
				$where = 'WHERE `b`.`bezeichner_kurz`=\'SOME_VALUE##\'';
			}
		}
		else if ($gewerk !== ''){
			$where = '';
			$gewerk = $this->gewerke->gewerk2sql($gewerk, true);
			if(is_array($gewerk)) {
				$i = 0;
				$where = 'WHERE ';
				foreach($gewerk as $b) {
					if($i === 0) {
						$where .= ' `b`.`bezeichner_kurz`=\''.$b.'\'';
					} else{
						$where .= ' OR `b`.`bezeichner_kurz`=\''.$b.'\'';
					}
					$i = 1;
				}
			}
		}
		else if ($todos !== ''){
			if(isset($this->taetigkeiten[$todos]['bezeichner'])) {
				$i = 0;
				foreach($this->taetigkeiten[$todos]['bezeichner'] as $b) {
					if($b === '*') {
						$where = '';
						break;
					}
					if($i === 0) {
						$where = 'WHERE `b`.`bezeichner_kurz`=\''.$b.'\'';
					} else{
						$where .= ' OR `b`.`bezeichner_kurz`=\''.$b.'\'';
					}
					$i = 1;
				}
			}
		}

		$sql  = 'SELECT ';
		$sql .= '`b`.`bezeichner_kurz` AS bezeichner_kurz, ';
		$sql .= '`b`.`bezeichner_lang` AS bezeichner_lang, ';
		$sql .= '`b`.`status` AS status, ';
		$sql .= '`b`.`din_276` as din, ';
		$sql .= '`h`.`text` as ht ';
		$sql .= 'FROM bezeichner AS b ';
		$sql .= 'LEFT JOIN bezeichner_help AS h ON (`b`.`bezeichner_kurz`=`h`.`bezeichner_kurz`) ';
		$sql .= $where;
		$sql .= ' GROUP BY `b`.`bezeichner_kurz`, bezeichner_lang, status, din, ht ';
		$sql .= 'ORDER BY bezeichner_lang ';
		$result = $this->db->handler()->query($sql);

		$d = array();
		if(is_array($result)) {
			$i = 1;
			foreach ( $result as $r ) {
				$div = $this->response->html->div();

### TODO

				// handle status
				if(isset($r['status']) && $r['status'] === 'off') {
					$a = $this->response->html->customtag('span');
					$a->add($r['bezeichner_lang'].' ('.$r['bezeichner_kurz'].')');
				} else {

					$span = $this->response->html->div();
					$span->style = 'width: 90px; display: inline-block;';
					if(isset($r['din'])) {
						$span->add($r['din']);
					} else {
						$span->add('&#160;');
					}
					$div->add($span);

					$a = $this->response->html->a();
					$a->id = $r['bezeichner_kurz'];
					$a->label = $r['bezeichner_lang'].' ('.$r['bezeichner_kurz'].')';
					$a->href = $this->response->get_url($this->actions_name, $this->next).'&bezeichner='.$r['bezeichner_kurz'];
					$a->handler = 'onclick="phppublisher.wait(\'Loading ...\');"';
					$div->add($a);

					if(isset($r['ht'])) {
						$span = $this->response->html->a();
						$span->handler = 'onclick="bezeichnerhelp.init(this);"';
						$span->customattribs = 'data-toggle="popover" data-trigger="focus" role="button" data-placement="right"';
						$span->id = uniqid('h');
						$span->tabindex = '0';
						$span->title = $r['bezeichner_kurz'];
						$span->css = 'icon icon-info';
						$span->style = 'margin: 0 0 0 10px;';
						$div->add($span);
					}
				}

				$d['bezeichner_'.$r['bezeichner_kurz']]['object'] = $div;
				$i++;
			}
		} else {
			if($result === '') {
				$d['bezeichner_XX'] = 'No Result';
			} else {
				$d['bezeichner_XX'] = '';
				$_REQUEST[$this->message_param] = $result;
			}
		}


		$gewerke = $this->gewerke->options();
		if(is_array($gewerke)) {
			array_unshift($gewerke, array('id' => '', 'label' => ''));
			$d['filter_gewerk']['label']                       = $this->lang['filter_trades'];
			$d['filter_gewerk']['css']                         = 'autosize';
			$d['filter_gewerk']['style']                       = 'float:right;clear:both;';
			$d['filter_gewerk']['object']['type']              = 'htmlobject_select';
			$d['filter_gewerk']['object']['attrib']['index']   = array('id','label');
			$d['filter_gewerk']['object']['attrib']['name']    = 'filter[gewerk]';
			$d['filter_gewerk']['object']['attrib']['id']      = 'filter_gewerk';
			$d['filter_gewerk']['object']['attrib']['options'] = $gewerke;
			$d['filter_gewerk']['object']['attrib']['style']   = 'width: 450px;';
			$d['filter_gewerk']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.submit = true; phppublisher.select.init(this, \''.$this->lang['filter_trades'].'\'); return false;"';

		} else {
			$d['filter_gewerk'] = '';
		}

		if(isset($this->taetigkeiten)) {
			$todos = $this->taetigkeiten;
			if(is_array($todos)) {
				array_unshift($todos, array('prefix' => '', 'lang' => ''));
				$d['filter_todos']['label']                       = $this->lang['filter_todos'];
				$d['filter_todos']['css']                         = 'autosize';
				$d['filter_todos']['style']                       = 'float:right;clear:both;';
				$d['filter_todos']['object']['type']              = 'htmlobject_select';
				$d['filter_todos']['object']['attrib']['index']   = array('prefix','lang');
				$d['filter_todos']['object']['attrib']['name']    = 'filter[todos]';
				$d['filter_todos']['object']['attrib']['id']      = 'filter_todos';
				$d['filter_todos']['object']['attrib']['options'] = $todos;
				$d['filter_todos']['object']['attrib']['style']   = 'width: 450px;';
				$d['filter_todos']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.submit = true; phppublisher.select.init(this, \''.$this->lang['filter_todos'].'\'); return false;"';
			} else {
				$d['filter_todos'] = '';
			}
		} else {
			$d['filter_todos'] = '';
		}

		$form->add($d);
		$response->form = $form;
		$form->display_errors = false;
		return $response;
	}

}
?>
