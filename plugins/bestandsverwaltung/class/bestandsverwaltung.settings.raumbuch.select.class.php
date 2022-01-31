<?php
/**
 * bestandsverwaltung_settings_raumbuch_select
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

class bestandsverwaltung_settings_raumbuch_select
{
/**
* translation
* @access public
* @var string
*/
var $lang = array(

);

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
		$this->raumbuch   = $controller->raumbuch;
		$this->parent     = $this->controller->controller;
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

		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.select.html');
		$t->add($response->form);
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'], 'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'], 'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'], 'baseurl');
		$t->add($response->data,'data');
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
		$options  = $this->raumbuch->options();
		if(!is_array($options)) { $options = array(); }

		// inventory filter
		$inventory = array();
		$devices = $this->db->select('bestand','id,wert',array('tabelle'=>'SYSTEM','merkmal_kurz'=>'RAUMBUCHID'));
		if(is_array($devices)) {
			foreach($devices as $d) {
				$inventory[] = $d['wert'];
			}
		}

		$table = $this->response->html->tablebuilder('standort_select', $response->get_array());
		$table->sort            = 'path';
		$table->order           = 'ASC';
		$table->limit           = 50;
		$table->offset          = 0;
		$table->css             = 'htmlobject_table table table-bordered';
		$table->id              = 'bestand_select';
		$table->sort_form       = true;
		$table->sort_link       = false;
		$table->sort_buttons    = array('sort','order','limit','offset','refresh');
		$table->autosort        = true;
		$table->max             = count($options);

		$table->init();

		// handle no limit
		if((int)$table->limit === 0) {
			$table->limit = count($options);
		}

		$head = array();
		$head['filter']['title'] = '<span class="icon icon-filter"></span>';
		$head['filter']['sortable'] = false;
		$head['filter']['style'] = 'width: 60px;text-align:center;';
		$head['id']['title'] = 'ID';
		$head['id']['sortable'] = true;
		$head['id']['style'] = 'width:150px;';
		$head['path']['title'] = 'Path';
		$head['path']['sortable'] = true;
		$head['action']['title'] = '&#160;';
		$head['action']['sortable'] = false;
		$head['action']['style'] = 'width: 100px;';

		$tparams = '';
		$params  = $this->response->html->request()->get($table->__id);
		if($params !== '') {
			foreach($params as $k => $v) {
				$tparams .= $this->response->get_params_string(array($table->__id.'['.$k.']' => $v), '&' );
			}
		}

		$body = array();
		if(is_array($options) && count($options) > 0) {
			$y = 0;
			foreach($options as $k => $o) {
				if($y >= $table->offset && $y < ($table->offset + $table->limit)) {
					$d = array();
					$links = '';

					$u = $this->response->html->a();
					$u->href    = $this->response->get_url($this->actions_name, 'update' ).'&id='.$o['id'].$tparams;
					$u->title   = $this->lang['select']['title_edit'];
					$u->css     = 'icon icon-edit edit btn btn-default btn-xs';
					$u->style   = 'margin: -3px 4px 0 0; display: inline-block;';
					$u->handler = 'onclick="phppublisher.wait();"';
					$links .= $u->get_string();

					$i = $this->response->html->a();
					$i->href    = $this->response->get_url($this->actions_name, 'insert' ).'&parent='.$o['id'].$tparams;
					$i->title   = $this->lang['select']['title_insert'];
					$i->css     = 'icon icon-plus btn btn-default btn-xs';
					$i->style   = 'margin: -3px 4px 0 0; display: inline-block;';
					$i->handler = 'onclick="phppublisher.wait();"';
					$links .= $i->get_string();


					$r = $this->response->html->a();
					$r->css = 'icon icon-trash remove btn btn-default btn-xs';
					if(!isset($o['last'])) {
						$r->style = 'margin: -3px 0 0 0; display: inline-block; visibility:hidden;';
					} else {
						$r->href    = $this->response->get_url($this->actions_name, 'remove' ).'&id='.$o['id'].$tparams;
						$r->title   = $this->lang['select']['title_remove'];
						$r->style   = 'margin: -3px 0 0 0; display: inline-block;';
						$r->handler = 'onclick="phppublisher.wait();"';
					}
					$links .= $r->get_string();


					// Filter
					$filter = '&#160;';
					$keys = array_keys($inventory, $o['id']);
					if(is_array($keys)) {
						$matches = count($keys);
					}
					if($matches > 0) {
						$c = $this->controller->controller->controller;
						$f = $this->response->html->a();
						$f->href    = $c->response->get_url($c->actions_name, 'inventory' ).'&filter[raumbuch]='.$o['id'];
						$f->title   = $this->lang['select']['title_filter'];
						$f->css     = 'btn btn-default btn-xs';
						$f->style   = 'margin: 0 0 0 0; display: block;';
						$f->target  = '_blank';
						$f->label   = $matches;
						$filter = $f->get_string();
					}

					// Fields
					$d['id']     = $o['id'];
					$d['path']   = $o['label'];
					$d['action'] = $links;
					$d['filter'] = $filter;
					$body[] = $d;
				}
				$y++;
			}

		}

		$table->head = $head;
		$table->body = $body;

		$i = $this->response->html->a();
		$i->href    = $this->response->get_url($this->actions_name, 'insert' );
		$i->title   = $this->lang['select']['title_insert'];
		$i->css     = 'icon icon-plus btn btn-default btn-xs';
		$i->style   = 'margin: -3px 4px 0 0; display: inline-block;';
		$i->handler = 'onclick="phppublisher.wait();"';
		$table->add_headrow($i);

		$response->data = $table;
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
		$form = $response->get_form($this->actions_name, 'select');
		$response->form = $form;
		return $response;
	}

}
?>
