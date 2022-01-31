<?php
/**
 * bestandsverwaltung_settings_inventory_identifiers_controller
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

class bestandsverwaltung_settings_inventory_identifiers_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_identifiers_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_identifiers_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_identifiers_ident';
/**
* inventory filter
* @access public
* @var bool
*/
var $inventory_filter = true;

var $tpldir;

var $lang = array();

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
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->controller = $controller;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		else if($ar === '') {
			$this->action = 'select';
		}

		if($this->response->cancel()) {
			$this->action = 'select';
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				default:
				case 'select':
					$data[] = $this->select(true);
				break;
				case 'insert':
					$data[] = $this->insert(true);
				break;
				case 'sync':
					$data[] = $this->sync(true);
				break;
				case 'status':
					$data[] = $this->status(true);
				break;
				case 'download':
					$data[] = $this->download(true);
				break;
			}
		}

		$tab = $this->response->html->tabmenu('settings_identifiers_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.identifiers.select.class.php');
			$controller = new bestandsverwaltung_settings_inventory_identifiers_select($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'select') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * insert
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.identifiers.insert.class.php');
			$controller = new bestandsverwaltung_settings_inventory_identifiers_insert($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			#$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
		}
		$content['label']   = 'insert';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'insert' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'insert') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * status
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function status($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.identifiers.status.class.php');
			$controller = new bestandsverwaltung_settings_inventory_identifiers_status($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'status';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'status' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'status') {
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * sync
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function sync($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.inventory.identifiers.sync.class.php');
			$controller = new bestandsverwaltung_settings_inventory_identifiers_sync($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'sync';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'sync' );
		$content['onclick'] = false;
		$content['hidden'] = true;
		$content['css'] = 'noborder';
		if($this->actions_name === 'sync') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function download($visible = false) {
		$data = '';
		if($visible === true) {

			// handle filter (status)
			$where = '';
			$filter = $this->response->html->request()->get('filter', true);
			if(isset($filter) && is_array($filter)) {
				if(isset($filter['status']) && $filter['status'] !== '') {
					if($filter['status'] === 'on') {
						$where .= '`b`.`status`=\''.$this->db->handler()->escape($filter['status']).'\' OR `b`.`status` IS NULL ';
					} else {
						$where .= '`b`.`status`=\''.$this->db->handler()->escape($filter['status']).'\' '; 
					}
				}
				if($where !== '') {
					$where = 'WHERE '.$where;
				}
			}

			$sql  = 'SELECT ';
			$sql .= 'b.bezeichner_lang as bezeichner_lang, ';
			$sql .= 'b.bezeichner_kurz as bezeichner_kurz, ';
			$sql .= 'b.din_276 as din_276, ';
			$sql .= 'b.alias as alias, ';
			$sql .= 'b.status as status, ';
			$sql .= 'h.text as help ';
			$sql .= 'FROM bezeichner AS b ';
			$sql .= 'LEFT JOIN bezeichner_help AS h ON (b.bezeichner_kurz=h.bezeichner_kurz) ';
			$sql .= $where;
			$sql .= 'GROUP BY bezeichner_kurz, bezeichner_lang, din_276, alias, status, help ';
			$sql .= 'ORDER BY bezeichner_lang';
			$result = $this->db->handler()->query($sql);

			if(is_array($result)) {

				$name = 'Musteranlagen.csv';

				header("Pragma: public");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: must-revalidate");
				header("Content-type: text/csv; charset=utf-8");
				header("Content-disposition: attachment; filename=$name");
				header('Content-Transfer-Encoding: binary');
				flush();
				echo pack('H*','EFBBBF');

				echo '"bezeichner_lang";';
				echo '"bezeichner_kurz";';
				echo '"din_276";';
				echo '"alias";';
				echo '"status";';
				echo '"help";';
				echo "\r\n";

				foreach($result as $key => $row) {
					foreach($row as $k => $r) {
						echo '"'.$r.'";';
					}
					echo "\r\n";
				}
				exit();
			}
		}
	}

}
?>
