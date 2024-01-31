<?php
/**
 * bestandsverwaltung_recording_todos
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

class bestandsverwaltung_recording_todos
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestand_recording_todos';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_recording_msg';
/**
*  date as formated string
*  @access public
*  @var string
*/
var $date_format = "Y-m-d H:i";

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

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
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');

		$tables = $this->db->select('bestand_index', array('tabelle_kurz','tabelle_lang'), null, 'pos');
		if(is_array($tables)) {
			foreach($tables as $table) {
				$this->tables[$table['tabelle_kurz']] = $table['tabelle_lang'];
			}
		} else {
			$this->tables = array();
		}

		require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
		$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);

### TODO diffrent path

		$this->pdftpl = $this->profilesdir.'cafm.one/templates/Checklist.pdf';

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		$prefix = $this->response->html->request()->get('prefix');
		// handle empty prefix
		if($prefix === '') {
			$tables = $this->taetigkeiten->tables();
			// no tables => this should not happen
			if(is_array($tables)) {
				$this->prefix = implode(',',array_keys($tables));
			} else {
				if(is_string($tables)) {
					echo $tables;
				} else {
					var_dump($tables);
				}
				exit;
			}
		}
		else if (is_array($prefix)) {
			$this->prefix = implode(',',array_keys($prefix));
		} else {
			$this->prefix = $prefix;
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
		if(!isset($this->db->type)) {
			$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->action = '';
			$ar = $this->response->html->request()->get($this->actions_name.'[todos]');
			if($ar !== '') {
				if(is_array($ar)) {
					$this->action = key($ar);
				} else {
					$this->action = $ar;
				}
			}
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.export.class.php');
			$export = new cafm_one_export($this->taetigkeiten);
			$export->actions_name = $this->actions_name;
			#### TODO inject tables?
			$export->tables = $this->tables;
			$export->action($this->action);
			exit();
		}
	}


}
?>
