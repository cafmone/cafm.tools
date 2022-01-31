<?php
/**
 * bestandsverwaltung_config_update
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

class bestandsverwaltung_config_update
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
var $lang = array(
		"update_sucess" => "Settings updated successfully",
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->user = $controller->user;
		$this->db = $controller->db;
		$this->controller = $controller;
		$this->settings = PROFILESDIR.'bestandsverwaltung.ini';
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

		$mode = $this->response->html->request()->get('update');
		if($mode !== '') {
			$this->update($mode);
		}

		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.config.backup.html');

		$a        = $this->response->html->a();
		$a->href  = $this->response->get_url($this->actions_name, 'update').'&update=bestand';
		$a->label = 'Update Database';
		$a->title = 'update';

		$t->add($a, 'backup_db');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update( $mode ) {

		switch( $mode ) {
			case 'bestand':
				if($this->db->type === 'mysql') {

					$sql  = 'SELECT id,bezeichner_kurz,user,date, ';
					$sql .= 'GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'USER\' AND `tabelle`=\'SYSTEM\', wert, NULL ) ) AS \'DATE\' ';
					$sql .= 'FROM bestand ';
					$sql .= 'GROUP BY id ';
					$result = $this->db->handler()->query($sql);

					if(is_array($result)) {
						foreach($result as $id) {
							if(!isset($id['USER'])) {
								$d = array();
								$d['id']              = $id['id'];
								$d['bezeichner_kurz'] = $id['bezeichner_kurz'];
								$d['tabelle']         = 'SYSTEM';
								$d['merkmal_kurz']    = 'USER';
								$d['wert']            = $id['user'];
								$d['date']            = $id['date'];
								$error = $this->db->insert('bestand', $d);
								if($error !== '') {
									$errors[] = $error;
								}
							} else {
								echo 'ID: '.$id['id'].'<br>';
							}
						}

						if(isset($errors)) {
							$_REQUEST[$this->message_param] = implode('<br>', $errors);
						} else {
							$this->response->redirect(
								$this->response->get_url(
									$this->actions_name, 'update', $this->message_param, 'success'
								)
							);
						}
					}
				}
			break;
		}
	}

}
?>
