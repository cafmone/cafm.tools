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
require_once(CLASSDIR.'lib/formbuilder/formbuilder.controller.class.php');

class bestandsverwaltung_settings_inventory_form_controller extends formbuilder_controller
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
		$this->controller = $controller;
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = CLASSDIR.'lib/formbuilder/';
		$this->tpldir = CLASSDIR.'lib/formbuilder/templates/';
		
		$this->table_prefix = 'bestand_';
		$this->table_bezeichner = 'bezeichner';

		// Validate user
		#$groups = array();
		#if(isset($this->controller->settings['settings']['supervisor'])) {
		#	$groups[] = $this->controller->settings['settings']['supervisor']; 
		#}
		#$this->is_valid = $this->controller->user->is_valid($groups);
	}

}
?>
