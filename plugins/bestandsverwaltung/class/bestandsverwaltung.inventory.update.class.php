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
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_inventory_update
{
/**
* hide fields for print
* @access public
* @var bool
*/
var $doprint = false;
/**
* output shown as popunder 
* @access public
* @var bool
*/
var $popunder = false;
/**
* hide empty fields
* @access public
* @var string
*/
var $hide_empty = false;

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

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.recording.controller.class.php');
		$cont = new bestandsverwaltung_recording_controller($this);
		$cont->tpldir = $this->tpldir;
		$cont->message_param = 'inventory_update_msg';

		require_once($this->classdir.'bestandsverwaltung.recording.insert.class.php');
		$controller = new bestandsverwaltung_recording_insert($cont);
		$controller->actions_name = $this->actions_name;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $cont->lang['insert'];
		$controller->doprint = $this->doprint;
		$controller->popunder = $this->popunder;
		$data = $controller->action();

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('inventory_update_tab');
		$tab->message_param = 'inventory_update_msg';
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add(array($content));

		return $tab;
	}

}
?>
