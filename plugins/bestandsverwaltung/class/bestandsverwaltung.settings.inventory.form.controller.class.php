<?php
/**
 * bestandsverwaltung_settings_inventory_form_controller
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

class bestandsverwaltung_settings_inventory_form_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_form_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'settings_form_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'settings_form_ident';

var $tpldir;

var $lang = array(
	'form' => array(
		'label' => 'Formular',
		'label_index' => 'Index',
		'label_devices' => 'Anlagen',
		'label_identifiers' => 'Bezeichner',
		'label_attribs' => 'Merkmale',
		'label_options' => 'Options',
		'msg_sorted' => 'Index neu sortiert',
	),
);


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
		$this->settings = $controller->settings;
		#$this->ini = $this->file->get_ini( $this->settings, true, true );
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
		require_once($this->classdir.'bestandsverwaltung.recording.form.controller.class.php');
		$controller = new bestandsverwaltung_recording_form_controller($this);
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang['form'];
		$data = $controller->action();

		return $data;
	}

}
?>
