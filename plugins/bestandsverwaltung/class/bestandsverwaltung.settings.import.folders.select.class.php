<?php
/**
 * bestandsverwaltung_settings_import_folders_select
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
 *  Copyright (c) 2015-2023, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2023, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_import_folders_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* path to ini file
* @access public
* @var string
*/
var $settings;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller = $controller->controller;
		$this->db        = $controller->db;
		$this->file      = $controller->file;
		$this->response  = $controller->response;
		$this->user      = $controller->user;
		#$this->settings  = $controller->settings;
		#$this->ini      = $controller->ini;
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

		$response = $this->import();
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.inventory.import.folders.select.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->table,'table');
		#$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		#$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		#$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
		$t->add($response->form, 'form');

		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function import() {
	
		$response = $this->get_response();

		// download
		$url  = $this->response->html->thisfile;
		$url .= '?index_action=plugin';
		$url .= '&index_action_plugin=bestandsverwaltung';
		$url .= '&'.$this->controller->controller->controller->actions_name.'=download';
		$url .= '&path=/devices/';
		$href_download  = $url;

		$inventory = array();
		$sql  = 'SELECT `id` ';
		$sql .= 'FROM bestand ';
		$sql .= 'GROUP BY id';
		$result = $this->db->handler()->query($sql);
		if(is_array($result)) {
			foreach($result as $r) {
				$inventory[$r['id']] = '';
			}
		}

		$folders = $this->file->get_folders(PROFILESDIR.'/webdav/bestand/devices/');
		$data = array();
		if(is_array($folders)) {
			foreach($folders as $folder) {
				if(!isset($inventory[$folder['name']])) {
					$data[] = $folder['name'];
				}
			}
		}

		$table                  = $response->html->tablebuilder( 'table_import_folders', $response->get_array() );
		$table->sort            = 'id';
		$table->order           = 'ASC';
		$table->limit           = 50;
		$table->offset          = 0;
		$table->css             = 'htmlobject_table table table-bordered';
		$table->id              = 'fault_select';
		$table->sort_form       = true;
		$table->sort_link       = false;
		$table->autosort        = true;
		$table->identifier      = 'id';
		$table->identifier_name = $this->identifier_name;
		$table->actions         = array(array('remove' => $this->lang['button_remove']), array('insert' => $this->lang['button_import']));
		$table->actions_name    = $this->actions_name;

		$head   = array();

		$head['id']['title'] = 'ID';
		$head['id']['style'] = 'width:180px;';
		$head['id']['sortable'] = true;

		$head['files']['title'] = 'Files';
		$head['files']['sortable'] = false;

		$head['insert']['title'] = '&#160;';
		$head['insert']['style'] = 'width:40px;';
		$head['insert']['sortable'] = false;

		$head['remove']['title'] = '&#160;';
		$head['remove']['style'] = 'width:40px;';
		$head['remove']['sortable'] = false;

		$tparams = '';
		$params  = $response->html->request()->get($table->__id);
		if($params !== '') {
			foreach($params as $k => $v) {
				$tparams .= $response->get_params_string(array($table->__id.'['.$k.']' => $v), '&' );
			}
		}

		$body = array();
		
		if(is_array($data)) {
			foreach($data as $d) {

				$path  = PROFILESDIR.'/webdav/bestand/devices/'.$d;
				$files = '';
				$f     = $this->file->get_files($path);
				foreach($f as $file) {
					$label = substr($file['name'], 0, 50);
					strlen($label) < strlen($file['name']) ? $label = $label.'...' : null;
					$a = $this->response->html->a();
					$a->href = $href_download.$d.'/'.$file['name'].'&inline=true';
					$a->label = $label;
					$a->target = '_blank';
					$files .= $a->get_string().'<br>';
				}

				$insert = $response->html->a();
				$insert->href  = $response->get_url($this->actions_name, 'insert').'&'.$this->identifier_name.'[]='.$d.$tparams;
				$insert->title = $this->lang['button_import'];
				$insert->css = 'icon icon-plus insert btn btn-default btn-sm';
				$insert->style = 'margin: -3px 0 0 0; display: inline-block;';
				$insert->handler = 'onclick="phppublisher.wait();"';
				
				$remove = $response->html->a();
				$remove->href  = $response->get_url($this->actions_name, 'remove').'&'.$this->identifier_name.'[]='.$d.$tparams;
				$remove->title = $this->lang['button_remove'];
				$remove->css = 'icon icon-trash remove btn btn-default btn-sm';
				$remove->style = 'margin: -3px 0 0 0; display: inline-block;';
				$remove->handler = 'onclick="phppublisher.wait();"';

				$b['id']     = $d;
				$b['files']  = $files;
				$b['insert'] = $insert->get_string();
				$b['remove'] = $remove->get_string();

				$body[] = $b;
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
		$form = $response->get_form($this->actions_name, 'folders');
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
