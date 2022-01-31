<?php
/**
 * bestandsverwaltung_settings_inventory_identifiers_select
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

class bestandsverwaltung_settings_inventory_identifiers_select
{
/**
* translation
* @access public
* @var string
*/
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
		$this->user       = $controller->user;
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;

		$filter = $this->response->html->request()->get('filter', true);
		if(isset($filter) && $filter !== '') {
			$this->filter = $filter;
			$this->response->add('filter', $filter);
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
		$response = $this->select();

		$i = $this->response->html->a();
		$i->href    = $this->response->get_url($this->actions_name, 'insert' );
		$i->title   = 'New';
		$i->css     = 'icon icon-plus btn btn-default btn-sm';
		$i->style   = 'margin: -3px 4px 0 0; display: inline-block;';
		$i->handler = 'onclick="phppublisher.wait();"';

		$s = '';
		$plugins = $this->file->get_ini($this->controller->profilesdir.'/plugins.ini');
		if(in_array('cafm.one', $plugins)) {
			$s = $this->response->html->a();
			$s->href    = $this->response->get_url($this->actions_name, 'sync' );
			$s->title   = 'sync';
			$s->css     = 'icon icon-sync btn btn-default btn-sm';
			$s->style   = 'margin: 5px 4px 0 0; display: inline-block;';
			$s->handler = 'onclick="phppublisher.wait();"';
		}

		$d = $this->response->html->a();
		$d->href    = $this->response->get_url($this->actions_name, 'download' );
		$d->title   = 'download';
		$d->css     = 'icon icon-download btn btn-default btn-sm';
		$d->style   = 'margin: 5px 4px 0 0; display: inline-block;';

		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.inventory.identifiers.select.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->table,'table');

		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
		$t->add($response->form, 'form');
		$t->add($i, 'insert');
		$t->add($s, 'sync');
		$t->add($d, 'download');

		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response();

		### TODO external function?
		$inventory = array();
		if(isset($this->controller->inventory_filter) && $this->controller->inventory_filter === true) {
			// bezeichner count in bestand
			$sql  = 'SELECT `id`,`bezeichner_kurz` as bezeichner ';
			$sql .= 'FROM bestand ';
			$sql .= 'GROUP BY id, bezeichner';
			$result = $this->db->handler()->query($sql);
			if(is_array($result)) {
				foreach($result as $r) {
					$inventory[] = $r['bezeichner'];
				}
			}
		}

		$table            = $response->html->tablebuilder( 'settings_identifiers_select', $response->get_array() );
		$table->sort      = 'kurz';
		$table->order     = 'ASC';
		$table->limit     = 50;
		$table->offset    = 0;
		$table->css       = 'htmlobject_table table table-bordered';
		$table->id        = 'fault_select';
		$table->sort_form = true;
		$table->sort_link = false;
		$table->autosort  = true;
		$table->identifier      = 'kurz';
		$table->identifier_name = $this->controller->identifier_name;
		$table->actions         = array(array('status' => 'status'));
		$table->actions_name    = $this->actions_name;

		$head   = array();
		if(isset($this->controller->inventory_filter) && $this->controller->inventory_filter === true) {
			$head['filter']['title'] =  '<span class="icon icon-filter"></span>';
			$head['filter']['sortable'] = false;
			$head['filter']['style'] = 'width: 60px;text-align:center;';
		}

		$head['kurz']['title'] = 'Kurz';
		$head['kurz']['sortable'] = true;

		$head['lang']['title'] = 'Lang';
		$head['lang']['sortable'] = true;

		$head['alias']['title'] = 'Alias';
		$head['alias']['sortable'] = true;

		$head['din']['title'] = 'DIN';
		$head['din']['sortable'] = true;

		$head['help']['title'] = 'Help';
		$head['help']['sortable'] = false;

		$head['status']['title'] = 'Status';
		$head['status']['sortable'] = true;

		$head['edit']['title'] = '&#160;';
		$head['edit']['style'] = 'width:40px;';
		$head['edit']['sortable'] = false;

		$body = array();

		$sql  = 'SELECT ';
		$sql .= 'b.bezeichner_kurz as bk, ';
		$sql .= 'b.bezeichner_lang as bl, ';
		$sql .= 'b.din_276 as din, ';
		$sql .= 'b.alias as alias, ';
		$sql .= 'b.status as status, ';
		$sql .= 'h.text as ht ';
		$sql .= 'FROM bezeichner AS b ';
		$sql .= 'LEFT JOIN bezeichner_help AS h ON (b.bezeichner_kurz=h.bezeichner_kurz) ';
		if(isset($this->filter)) {
			$i = 0;
			$sql .= 'WHERE ';
			foreach($this->filter as $k => $v) {
				if($i > 0) {
					$sql .= ' AND ' ;
				}
				if($k === 'status') {
					if($v === 'on') {
						$sql .= '(' ;
						$sql .= ' `b`.`status`=\''.$this->db->handler()->escape($v).'\' ';
						$sql .= 'OR `b`.`status`=\'\' ';
						$sql .= 'OR `b`.`status` IS NULL ';
						$sql .= ') ' ;
					} else {
						$sql .= ' `b`.`status`=\''.$this->db->handler()->escape($v).'\' ';
					}
				}
				else if($k === 'bezeichner_kurz') {
					$sql .= ' `b`.`bezeichner_kurz` LIKE \''.$this->db->handler()->escape($v).'\' ';
				}
				else if($k === 'bezeichner_lang') {
					$sql .= ' `b`.`bezeichner_lang` LIKE \''.$this->db->handler()->escape($v).'\' ';
				}
				$i++;
			}
		}
		$sql .= 'GROUP BY bk,bl,din,alias,status,ht';
		$data = $this->db->handler()->query($sql);

		$tparams = '';
		$params  = $this->response->html->request()->get($table->__id);
		if($params !== '') {
			foreach($params as $k => $v) {
				$tparams .= $this->response->get_params_string(array($table->__id.'['.$k.']' => $v), '&' );
			}
		}

		if(is_array($data)) {
			foreach($data as $d) {
				$a = $response->html->a();
				$a->href  = $response->get_url($this->actions_name, 'insert').'&bezeichner='.$d['bk'].$tparams;
				$a->title = 'edit';
				$a->css = 'icon icon-edit edit btn btn-default btn-sm';
				$a->style = 'margin: -3px 0 0 0; display: inline-block;';
				$a->handler = 'onclick="phppublisher.wait();"';

				// handle status = null
				if(!isset($d['status'])) {
					$d['status'] = 'on';
				}

				if(isset($this->controller->inventory_filter) && $this->controller->inventory_filter === true) {
					// Filter button
					$filter = '&#160;';
					$keys = array_keys($inventory, $d['bk']);
					if(is_array($keys)) {
						$matches = count($keys);
					}
					if($matches > 0) {
						$c = $this->controller->controller->controller->controller;
						$f = $this->response->html->a();
						$f->href    = $c->response->get_url($c->actions_name, 'inventory' ).'&filter[bezeichner]='.$d['bk'];
						#$f->title   = $this->lang['select']['title_filter'];
						$f->css     = 'btn btn-default btn-xs';
						$f->style   = 'margin: 0 0 0 0; display: block;';
						$f->target  = '_blank';
						$f->label   = $matches;
						$filter = $f->get_string();
					}
				}

				$b = array();
				if(isset($this->controller->inventory_filter) && $this->controller->inventory_filter === true) {
					$b['filter'] = $filter;
				}
				$b['kurz']   = $d['bk'];
				$b['lang']   = $d['bl'];
				$b['alias']  = $d['alias'];
				$b['din']    = $d['din'];
				$b['help']   = $d['ht'];
				$b['status'] = $d['status'];
				$b['edit']   = $a->get_string();

				$body[] = $b;
			}
		} else {
			if(is_string($data)) {
				$_REQUEST[$this->message_param] = $data;
			}
		}

		$table->max  = count($body);
		$table->head = $head;
		$table->body = $body;

		$response->table = $table;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select');
		$form->display_errors = false;

		// remove filter added from response
		$form->remove('filter');

		// Status
		$states[] = array('','');
		$states[] = array('on','On');
		$states[] = array('off','Off');
		$states[] = array('obsolete','Obsolete');

		$d['filter_status']['label']                       = 'Status';
		$d['filter_status']['css']                         = 'autosize float-right';
		$d['filter_status']['style']                       = 'clear:both;';
		$d['filter_status']['object']['type']              = 'htmlobject_select';
		$d['filter_status']['object']['attrib']['index']   = array(0,1);
		$d['filter_status']['object']['attrib']['options'] = $states;
		$d['filter_status']['object']['attrib']['style']   = 'width:120px;margin:0 80px 0 0;';
		$d['filter_status']['object']['attrib']['name']    = 'filter[status]';

		$d['filter_kurz']['label']                     = 'Kurz';
		$d['filter_kurz']['css']                       = 'autosize float-right';
		$d['filter_kurz']['style']                     = 'clear:both;';
		$d['filter_kurz']['object']['type']            = 'htmlobject_input';
		$d['filter_kurz']['object']['attrib']['style'] = 'width:200px;';
		$d['filter_kurz']['object']['attrib']['name']  = 'filter[bezeichner_kurz]';

		$d['filter_lang']['label']                     = 'Lang';
		$d['filter_lang']['css']                       = 'autosize float-right';
		$d['filter_lang']['style']                     = 'clear:both;';
		$d['filter_lang']['object']['type']            = 'htmlobject_input';
		$d['filter_lang']['object']['attrib']['style'] = 'width:200px;';
		$d['filter_lang']['object']['attrib']['name']  = 'filter[bezeichner_lang]';

		$form->add($d);

		$response->form = $form;
		return $response;
	}

}
?>
