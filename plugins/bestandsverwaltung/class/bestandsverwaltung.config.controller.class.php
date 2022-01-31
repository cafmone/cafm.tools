<?php
/**
 * bestandsverwaltung_config_controller
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

class bestandsverwaltung_config_controller
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
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'bestandsverwaltung_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'bestandsverwaltung_ident';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'config' => 'Settings',
	'lang' => 'Lang',
	'colors' => 'Colors',
	'publish' => 'Publish',
	'ckeditor' => 'CKEditor',
	'export' => 'Export',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct( $file, $response, $db, $user ) {
		$this->classdir = CLASSDIR.'/plugins/bestandsverwaltung/class/';
		$this->file     = $file;
		$this->settings = PROFILESDIR.'bestandsverwaltung.ini';
		$this->ini      = $this->file->get_ini($this->settings);
		$this->response = $response;
		$this->langdir  = CLASSDIR.'/plugins/bestandsverwaltung/lang';
		$this->tpldir   = CLASSDIR.'/plugins/bestandsverwaltung/templates';
		$this->user     = $user;
		$this->db = $db;

		if(isset($this->ini['settings']['db'])) {
			$this->db->db = $this->ini['settings']['db'];
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

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'settings':
				$content[] = $this->settings(true);
				$content[] = $this->backup();
				$content[] = $this->database();
				//$content[] = $this->update();
			break;
			case 'database':
				$content[] = $this->settings();
				$content[] = $this->backup();
				$content[] = $this->database(true);
				//$content[] = $this->update();
			break;
			case 'backup':
				$content[] = $this->settings();
				$content[] = $this->backup(true);
				$content[] = $this->database();
				//$content[] = $this->update();
			break;
			case 'update':
				$content[] = $this->settings();
				$content[] = $this->backup();
				$content[] = $this->database();
				//$content[] = $this->update(true);
			break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function settings($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'bestandsverwaltung.config.settings.class.php');
			$controller = new bestandsverwaltung_config_settings($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = 'Settings';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'settings' );
		$content['onclick'] = false;
		if($this->action === 'settings'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Backup
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function backup($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'bestandsverwaltung.config.backup.class.php');
			$controller = new bestandsverwaltung_config_backup($this->file, $this->response, $this->db, $this->user);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = 'Backup';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'backup' );
		$content['onclick'] = false;
		if($this->action === 'backup'){
			$content['active']  = true;
		}
		return $content;

	}

	//--------------------------------------------
	/**
	 * Database
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function database($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once(CLASSDIR.'lib/db/query.controller.class.php');
			$controller = new query_controller($this->file, $this->response, $this->db, $this->user);
			$controller->tpldir = CLASSDIR.'lib/db/templates/';
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Database';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'database' );
		$content['onclick'] = false;
		if($this->action === 'database'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'bestandsverwaltung.config.update.class.php');
			$controller = new bestandsverwaltung_config_update($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$data = $controller->action();
		}
		$content['label']   = 'Update';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Allow
	 *
	 * @access public
	 * @return array htmlobject_formbuilder
	 */
	//--------------------------------------------
	function allow($group, $ini = null) {
		require_once($this->classdir.'cms.controller.class.php');
		$controller = new cms_controller($this->file, null, null);
		$i = 0;	
		foreach($controller->allow as $key => $value) {
			$d['param_f'.$i]['label']                    = $key;
			$d['param_f'.$i]['object']['type']           = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type'] = 'checkbox';
			$d['param_f'.$i]['object']['attrib']['name'] = $group.'['.$key.']';
			if(isset($ini[$key])) {
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
			} else if ($ini === null && $controller->allow[$key] === true) {
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
			}
			$i++;
		}
		return $d;
	}

}
?>
