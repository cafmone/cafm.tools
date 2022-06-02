<?php
/**
 * standort_settings_inventory_select
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_select
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
		$this->standort   = $controller->standort;
		$this->parent     = $this->controller->controller;
		$this->plugins    = $this->file->get_ini(PROFILESDIR.'/plugins.ini');
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

		$t = $response->html->template($this->tpldir.'standort.settings.inventory.select.html');
		$t->add($response->form);
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'], 'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'], 'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'], 'baseurl');
		$t->add($response->data,'data');
		$t->group_elements(array('param_' => 'form'));

		$i = $this->response->html->a();
		$i->href    = $this->response->get_url($this->actions_name, 'insert' );
		$i->title   = $this->lang['title_insert'];
		$i->label   = $this->lang['button_insert'];
		$i->css     = 'btn btn-default float-right';
		$i->style   = 'margin: -3px 4px 0 0; display: inline-block;';
		$i->handler = 'onclick="phppublisher.wait();"';

		$t->add($i, 'insert');

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
		$options  = $this->standort->options();
		if(!is_array($options)) { $options = array(); }

		### TODO link
		// inventory filter
		$inventory = array();
		if(in_array('bestandsverwaltung', $this->plugins)) {
			require_once(CLASSDIR.'lib/db/query.class.php');
			$bsettings = $this->file->get_ini(PROFILESDIR.'bestandsverwaltung.ini');
			$db = new query(CLASSDIR.'lib/db');
			$db->db = $bsettings['settings']['db'];
			$db->type = $this->db->type;
			$db->user = $this->db->user;
			$db->pass = $this->db->pass;
			$devices = $db->select('bestand','id,wert',array('tabelle'=>'SYSTEM','merkmal_kurz'=>'RAUMBUCHID'));
			if(is_array($devices)) {
				foreach($devices as $d) {
					$inventory[] = $d['wert'];
				}
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
		$table->identifier      = 'id';
		$table->identifier_name = $this->controller->identifier_name;
		$table->actions         = array(array('identifiers' => $this->lang['table_identifier']));
		$table->actions_name    = $this->actions_name;

		$table->init();

		// handle no limit
		if((int)$table->limit === 0) {
			$table->limit = count($options);
		}

		$head = array();
		$head['filter']['title'] = '<span class="icon icon-filter"></span>';
		$head['filter']['sortable'] = false;
		$head['filter']['style'] = 'width: 60px;text-align:center;';
		$head['id']['title'] = $this->lang['table_id'];
		$head['id']['sortable'] = true;
		$head['id']['style'] = 'width:120px;';
		$head['ident']['title'] = $this->lang['table_identifier'];
		$head['ident']['sortable'] = true;
		$head['ident']['style'] = 'width:50px;';
		$head['path']['title'] = $this->lang['table_path'];
		$head['path']['sortable'] = true;
		$head['act']['title'] = '&#160;';
		$head['act']['sortable'] = false;
		$head['act']['style'] = 'width: 50px;';

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
				#if($y >= $table->offset && $y < ($table->offset + $table->limit)) {
					$d = array();
					$links = '';

					$u = $this->response->html->a();
					$u->href    = $this->response->get_url($this->actions_name, 'update' ).'&id='.$o['id'].$tparams;
					$u->title   = sprintf($this->lang['title_edit'], $o['id']);
					$u->css     = 'icon icon-edit edit btn btn-default btn-sm';
					$u->style   = 'margin: 0 0 0 0; display: block;';
					$u->handler = 'onclick="phppublisher.wait();"';
					$links .= $u->get_string();

					$i = $this->response->html->a();
					$i->href    = $this->response->get_url($this->actions_name, 'insert' ).'&parent='.$o['id'].$tparams;
					$i->title   = sprintf($this->lang['title_append'], $o['id']);
					$i->css     = 'icon icon-plus btn btn-default btn-sm';
					$i->style   = 'margin: 3px 0 0 0; display: block;';
					$i->handler = 'onclick="phppublisher.wait();"';
					$links .= $i->get_string();


					$r = $this->response->html->a();
					$r->css = 'icon icon-trash remove btn btn-default btn-sm';
					if(!isset($o['last'])) {
						$r->style = 'margin: 3px 0 0 0; display: inline-block; visibility:hidden;';
					} else {
						$r->href    = $this->response->get_url($this->actions_name, 'remove' ).'&id='.$o['id'].$tparams;
						$r->title   = sprintf($this->lang['title_remove'], $o['id']);
						$r->style   = 'margin: 3px 0 0 0; display: block;';
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
						$baseurl = $GLOBALS['settings']['config']['baseurl'].'login/?index_action=plugin&index_action_plugin=bestandsverwaltung&bestandsverwaltung_action=inventory';
						$f = $this->response->html->a();
						$f->href    = $baseurl.'&filter[raumbuch]='.$o['id'];
						$f->title   = $this->lang['title_filter'];
						$f->css     = 'btn btn-default btn-xs';
						$f->style   = 'margin: 0 0 0 0; display: block;';
						$f->target  = '_blank';
						$f->label   = $matches;
						$filter = $f->get_string();
					}

					// Fields
					$d['id']     = strval($o['id']);
					$d['ident']  = $o['identifier'];
					$d['path']   = $o['path'];
					$d['act'] = $links;
					$d['filter'] = $filter;
					$body[] = $d;
				#}
				$y++;
			}
		}

		$table->head = $head;
		$table->body = $body;

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
