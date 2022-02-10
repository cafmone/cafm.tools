<?php
/**
 * bestandsverwaltung_recording_form_attribs_move
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

class bestandsverwaltung_recording_form_attribs_move
{

var $lang = array();
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
	
		$table = $this->db->handler()->escape($this->response->html->request()->get('table'));
		$row   = $this->db->handler()->escape($this->response->html->request()->get('row'));
		$msg   = '';
	
		if($table !== '' && $row !== '') {
			$result = $this->db->select($this->table_prefix.$table, '*', null, '`row`');
			if(is_array($result)) { 
				foreach($result as $k => $v) {
					if(isset($v['row']) && $v['row'] === $row) {
						if(isset($result[$k-1]) && isset($result[$k-1]['row'])) {

							$source = $row;
							$new    = $v;
							unset($new['row']);
							foreach($new as $x => $n) {
								if(!isset($n)) {
									$new[$x] = '';
								}
							}

							$target = $result[$k-1]['row'];
							$old    = $result[$k-1];
							unset($old['row']);
							foreach($old as $x => $n) {
								if(!isset($n)) {
									$old[$x] = '';
								}
							}

							$error = $this->db->update(
								$this->table_prefix.$table,
								$new,
								array('row'=>$target)
							);
							$error = $this->db->update(
								$this->table_prefix.$table,
								$old,
								array('row'=>$source)
							);

							if($error === '') {
								$msg = sprintf($this->lang['msg_moved_attrib'], $new['merkmal_kurz']);
							}
						}
					}
				}
			} else {
				if($result !== '') {
					$error = $result;
				}
			}
		} else {
			$msg = 'nothing to do';
		}
		
		
		if(!isset($error) || $error === '') {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, 'select', $this->message_param, $msg
				).'&table='.$table
			);
		} else {
			$this->response->redirect(
				$this->response->get_url(
					$this->actions_name, 'select', $this->message_param.'[error]', $error
				).'&table='.$table
			);
		}

	}

}
?>
