<?php
/**
 * bestandsverwaltung_controller
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

class bestandsverwaltung_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestandsverwaltung_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestandsverwaltung_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'bestandsverwaltung_ident';
/**
* path to tpldir
* @access public
* @var string
*/
var $tpldir;

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
	function __construct($file, $response, $db, $user) {
		$this->file = $file;
		$this->response = $response;
		$this->db = $db;
		$this->user = $user;
		$this->profilesdir = PROFILESDIR;
		$this->settings = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.ini', true, true);
		$this->classdir = CLASSDIR.'plugins/bestandsverwaltung/class/';
		$this->db = $db;
		if(isset($this->settings['settings']['db'])) {
			$this->db->db = $this->settings['settings']['db'];
		}
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
			if($this->action === 'process') {
				$this->action = 'select';
			}
			unset($_REQUEST['dbaction']);
		}

		if(!isset($this->db->type)) {
			$data  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$data = array();
			switch( $this->action ) {
				case '':
				default:
				case 'inventory':
					$data = $this->inventory(true);
				break;
				case 'gewerke':
					$data = $this->gewerke(true);
				break;
				case 'raumbuch':
					$data = $this->raumbuch(true);
				break;
				case 'recording':
					$data = $this->recording(true);
				break;
				case 'settings':
					$data = $this->settings(true);
				break;
				case 'download':
					$data = $this->__download();
				break;
			}
		}

		$content['label']   = 'x';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		$content['active']  = true;

		$tab = $this->response->html->tabmenu('bestand_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tab-content noborder';
		$tab->auto_tab = true;
		$tab->add(array($content));
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Inventory
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function inventory( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.inventory.controller.class.php');
			$controller = new bestandsverwaltung_inventory_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Raumbuch
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function raumbuch( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.raumbuch.controller.class.php');
			$controller = new bestandsverwaltung_raumbuch_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Recording
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function recording( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.recording.controller.class.php');
			$controller = new bestandsverwaltung_recording_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function settings( $visible = false ) {
		if($visible === true) {
			require_once($this->classdir.'bestandsverwaltung.settings.controller.class.php');
			$controller = new bestandsverwaltung_settings_controller($this);
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Gewerke
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function gewerke( $visible = false ) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.gewerke.controller.class.php');
			$controller = new bestandsverwaltung_gewerke_controller($this);
			#$controller->message_param = $this->message_param;
			#$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			#$controller->identifier_name  = $this->identifier_name;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function __download() {
		require_once(CLASSDIR.'/lib/file/file.mime.class.php');
		$path = $this->response->html->request()->get('path');
		$path = $this->profilesdir.'/webdav/bestand/'.$path;
		$file = $this->file->get_fileinfo($path);
		$mime = detect_mime($file['path']);

		$inline = $this->response->html->request()->get('inline');

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		if( $inline === 'true' && substr($mime, 0, 5) === 'image') {
			header("Content-disposition: inline; filename=".$file['name']);
		} else {
			header("Content-disposition: attachment; filename=".$file['name']);
		}
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
	}

}
?>
