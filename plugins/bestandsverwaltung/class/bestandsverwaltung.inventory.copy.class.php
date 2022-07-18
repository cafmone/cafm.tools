<?php
/**
 * bestandsverwaltung_inventory_copy
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
 *  Copyright (c) 2015-2017, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2017, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_inventory_copy
{

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
		$this->db          = $controller->db;
		$this->file        = $controller->file;
		$this->response    = $controller->response;
		$this->controller  = $controller;
		$this->user        = $controller->user->get();
		$this->profilesdir = PROFILESDIR;

		// handle select params
		$this->response->add('printout',$this->response->html->request()->get('printout'));
		$this->response->add('filter',$this->response->html->request()->get('filter'));
		$this->response->add('export',$this->response->html->request()->get('export'));
		if($this->response->html->request()->get('bestand_select') !== '') {
			$this->response->add('bestand_select',$this->response->html->request()->get('bestand_select'));
		}

		// Validate user
		$groups = array();
		if(isset($this->controller->settings['settings']['supervisor'])) {
			$groups[] = $this->controller->settings['settings']['supervisor']; 
		}
		$this->is_valid = $this->controller->user->is_valid($groups);
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
		$oldid  = $this->response->html->request()->get('id');
		$device = $this->db->select('bestand','*',array('id'=>$oldid));
		$error  = '';
		if(is_array($device) && $this->is_valid) {
			$newid                = uniqid('');
			$d['id']              = $newid;
			$d['date']            = time();
			$d['bezeichner_kurz'] = $device[0]['bezeichner_kurz'];
			foreach($device as $v) {
				$d['tabelle']         = $v['tabelle'];
				$d['merkmal_kurz']    = $v['merkmal_kurz'];
				$d['wert']            = $v['wert'];
				$error = $this->db->insert('bestand', $d);
				if($error !== '') {
					break;
				}
			}
			if($error === '') {
				// handle changelog
				$c['id']           = $newid;
				$c['merkmal_kurz'] = 'copy';
				$c['old']          = $oldid;
				$c['new']          = $newid;
				$c['user']         = $this->user['login'];
				$c['date']         = time();
				$error = $this->db->insert('changelog',$c);

				// handle files
				$path = $this->profilesdir.'/webdav/bestand/devices/';
				if($this->file->exists($path.$oldid)) {
					$error = $this->file->copy($path.$oldid, $path.$newid);
				}

				if($error !== '') {
					$error = 'Errors while copy '.$oldid.' to '.$newid.': '.$error;
					$this->response->redirect(
							$this->response->get_url(
							$this->actions_name, 'select', $this->message_param, $error
						)
					);
				} else {
					$msg = 'Copied '.$oldid.' to '.$newid;
					$this->response->redirect(
							$this->response->get_url(
							$this->actions_name, 'select', $this->message_param, $msg
						)
					);
				}
			} else {
				$error = 'Failed to copy '.$oldid.': '.$error;
				$this->response->redirect(
						$this->response->get_url(
						$this->actions_name, 'select', $this->message_param, $error
					)
				);
			}
		} else {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'select', $this->message_param, ''
				)
			);
		}
	}

}
?>
