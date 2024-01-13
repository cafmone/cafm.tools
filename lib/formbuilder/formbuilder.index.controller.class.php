<?php
/**
 * formbuilder_index_controller
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
 *  along with this file (see ../../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2024, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2024, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../../LICENSE.TXT)
 * @version 1.0
 */

class formbuilder_index_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'form_index_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'form_index_msg';
/**
* path to tpldir
* @access public
* @var string
*/
var $tpldir;
/**
* prefix for form tables
* @access public
* @var string
*/
var $table_prefix;
/**
* identifier table
* @access public
* @var string
*/
var $table_bezeichner;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->profilesdir = PROFILESDIR;
		$this->classdir = CLASSDIR.'lib/formbuilder/';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}

		if($this->response->cancel()) {
			$this->action = 'sort';
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			#$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				case '':
				default:
				case 'sort':
					$data = $this->sort(true);
				break;
				case 'edit':
					$data = $this->edit(true);
				break;
			}
		}

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'sort' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('form_index_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = true;
		$tab->add(array($content));
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Sort
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function sort( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'formbuilder.index.sort.class.php');
			$controller = new formbuilder_index_sort($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->table_prefix = $this->table_prefix;
			$controller->table_bezeichner = $this->table_bezeichner;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function edit( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'formbuilder.index.edit.class.php');
			$controller = new formbuilder_index_edit($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->table_prefix = $this->table_prefix;
			$controller->table_bezeichner = $this->table_bezeichner;
			$data = $controller->action();
			return $data;
		}
	}

}
?>
