<?php
/**
 * bestandsverwaltung_settings_gewerke_controller
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

class bestandsverwaltung_settings_gewerke_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_gewerke_action';
/**
* name of action buttons
* @access public
* @var string
*/
var $subactions_name = 'sub_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'gewerke_msg';

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'label' => 'Gewerke',
	'select' => array(
		'label' => '&Uuml;bersicht',
	),
	'taetigkeiten' => array(
		'label' => 'T&auml;tigkeiten',
	),
	'plans' => array(
		'label' => 'Planung',
	)
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
		$this->controller = $controller;
		$this->file = $controller->file;
		// derive response
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		#$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestand/lang/', 'bestand.ini');
		$this->classdir = $controller->classdir;
		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');
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
		else if($ar === '') {
			$this->action = 'select';
		}

		if(!isset($this->db->type)) {
			$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'select':
					$content[] = $this->select( true );
				break;
				case 'pdf':
					$content[] = $this->pdf( true );
				break;
			}
		}

		$tab = $this->response->html->tabmenu('bestand_gewerke_tab');
		$tab->message_param = 'gewerke_msg';
		$tab->css = 'htmlobject_tabs left noprint noborder';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = false;
		$tab->add($content);

		return $tab;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.gewerke.select.class.php');
			$controller = new bestandsverwaltung_settings_gewerke_select($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang  = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = 'x';
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * PDF
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function pdf($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.gewerke.pdf.class.php');
			$controller = new bestandsverwaltung_settings_gewerke_pdf($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = 'PDF';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'pdf');
		$content['onclick'] = false;
		if($this->actions_name === 'pdf'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
