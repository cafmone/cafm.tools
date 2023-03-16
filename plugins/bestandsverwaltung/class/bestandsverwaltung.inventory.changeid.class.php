<?php
/**
 * bestandsverwaltung_inventory_update
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

class bestandsverwaltung_inventory_changeid
{

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
		$this->user        = $controller->user;
		$this->settings    = $controller->settings;
		$this->classdir    = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
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
		$form = $this->update();
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.inventory.changeid.html');
		$t->add($vars);
		$t->add($form);
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
	function update() {
		$form = $this->get_form();
		if(!$form->get_errors() && $this->response->submit()) {
			$error = '';
			$newid = $form->get_request('newid');
			$oldid = $form->get_static('id');
			if(isset($oldid) && $oldid !== '') {
				if($oldid !== $newid) {
					$check = $this->db->select('bestand', array('id'), array('id', $newid), 'id', 1);
					if($check === '') {

						// Changelog
						$check = $this->db->handler()->columns($this->db->db, 'changelog');
						if(is_array($check)) {
							$error = $this->db->update(
								'changelog',
								array('id' => $newid),
								array('id', $oldid)
							);
							if($error === '') {
								$user = $this->controller->user->get();
								$d = array();
								$d['id']           = $newid;
								$d['merkmal_kurz'] = 'ID';
								$d['old']          = $oldid;
								$d['new']          = $newid;
								$d['user']         = $user['login'];
								$d['date']         = time();
								$error = $this->db->insert('changelog',$d);
							}
						}

						// Files
						if($error === '') {
							$path = $this->profilesdir.'webdav/bestand/devices/';
							var_dump($path);
							if($this->file->exists($path.$oldid)) {
								$error = $this->file->rename($path.$oldid, $path.$newid);
							}
						}

						// Bestand
						if($error === '') {
							$error = $this->db->update(
								'bestand',
								array('id' => $newid),
								array('id', $oldid)
							);
						}

					} else {
						$error = 'Error: New ID already in use. Nothing to do.';
					}
				} else {
					$error = 'Error: New ID is equivalent to Old ID. Nothing to do.';
				}
			} else {
				$error = 'Error: No Old ID.';
			}

			if( $error === '' ) {
				$this->response->add('id', $newid);
				$msg = sprintf($this->lang['update_sucess'], $oldid, $newid);
				$this->response->redirect($this->response->get_url($this->actions_name, 'update', $this->message_param, $msg));
			} else {
				$_REQUEST[$this->message_param]['error'] = $error;
			}

		}
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param]['error'] = implode('<br>', $form->get_errors());
		}
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form() {

		$form = $this->response->get_form($this->actions_name, 'changeid');

		$d['newid']['label']                     = 'New Id';
		$d['newid']['required']                  = true;
		$d['newid']['object']['type']            = 'htmlobject_input';
		$d['newid']['object']['attrib']['name']  = 'newid';
		$d['newid']['object']['attrib']['type']  = 'text';

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}
}
?>
