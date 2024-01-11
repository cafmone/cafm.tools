<?php
/**
 * bestandsverwaltung_config_settings
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

class bestandsverwaltung_config_settings
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
		"lang_query" => "Database",
		"lang_export" => "Export",
		"lang_printout" => "Printout",
		"lang_permissions" => "Permissions",
		"query" => array(
			"type" => "Type",
			"host" => "Host",
			"db" => "DB",
			"user" => "User",
			"pass" => "Pass"
		),
		"update_sucess" => "Settings updated successfully",
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->file     = $controller->file;
		$this->response = $controller->response;
		$this->user     = $controller->user;
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

		$form = $this->update();
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.config.settings.html');
		$t->add($this->lang['lang_query'], 'lang_db');
		$t->add($this->lang['lang_permissions'], 'lang_permissions');
		$t->add($this->lang['lang_export'], 'lang_export');
		$t->add($this->lang['lang_printout'], 'lang_printout');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('qrcode_' => 'qrcode'));
		$t->group_elements(array('filter_' => 'filter'));
		$t->group_elements(array('print_' => 'print'));
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
			$request = $form->get_request();
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['settings']);
				#unset($old['export']);
				#unset($old['filter']);
				$request = array_merge($old, $request);
			}
			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['update_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'settings', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param] = $error;
					}
			} else {
				$_REQUEST[$this->message_param] = $error;
			}
		} 
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
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
		$ini  = $this->file->get_ini( $this->settings, true, true );
		$form = $this->response->get_form($this->actions_name, 'settings');

		$d['db']['label']                     = 'DB';
		$d['db']['required']                  = true;
		$d['db']['object']['type']            = 'htmlobject_input';
		$d['db']['object']['attrib']['name']  = 'settings[db]';
		$d['db']['object']['attrib']['type']  = 'text';
		if(isset($ini['settings']['db'])) {
			$d['db']['object']['attrib']['value'] = $ini['settings']['db'];
		}

		// Permissions
		$groups = $this->user->list_groups();
		if(!isset($groups)) {
			$groups = array();
		}
		array_unshift($groups, '');
		$d['supervisor']['label']                       = 'Supervisor group';
		$d['supervisor']['object']['type']              = 'htmlobject_select';
		$d['supervisor']['object']['attrib']['index']   = array(0,0);
		$d['supervisor']['object']['attrib']['options'] = $groups;
		$d['supervisor']['object']['attrib']['name']    = 'settings[supervisor]';
		if(isset($ini['settings']['supervisor'])) {
			$d['supervisor']['object']['attrib']['selected'] = array($ini['settings']['supervisor']);
		}

		$d['changeid']['label']                       = 'Admin group can change ID';
		$d['changeid']['object']['type']              = 'htmlobject_input';
		$d['changeid']['object']['attrib']['type']   = 'checkbox';
		$d['changeid']['object']['attrib']['options'] = $groups;
		$d['changeid']['object']['attrib']['name']    = 'settings[changeid]';
		if(isset($ini['settings']['changeid'])) {
			$d['changeid']['object']['attrib']['checked'] = true;
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
