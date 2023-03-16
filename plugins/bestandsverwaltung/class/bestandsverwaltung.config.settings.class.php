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
				unset($old['export']);
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

		// Export

		$d['bom']['label']                    = 'BOM';
		$d['bom']['object']['type']           = 'htmlobject_input';
		$d['bom']['object']['attrib']['type'] = 'checkbox';
		$d['bom']['object']['attrib']['name'] = 'export[bom]';
		if(isset($ini['export']['bom'])) {
			$d['bom']['object']['attrib']['checked'] = true;
		} else {
			$d['bom']['object']['attrib']['checked'] = true;
		}

		$o = array();
		$o[] = array('\n');
		$o[] = array('\r\n');

		$d['linefeed']['label']                       = 'Linefeed';
		$d['linefeed']['object']['type']              = 'htmlobject_select';
		$d['linefeed']['object']['attrib']['index']   = array(0,0);
		$d['linefeed']['object']['attrib']['options'] = $o;
		$d['linefeed']['object']['attrib']['name']    = 'export[linefeed]';
		if(isset($ini['export']['linefeed'])) {
			$d['linefeed']['object']['attrib']['selected'] = array($ini['export']['linefeed']);
		} else {
			$d['linefeed']['object']['attrib']['selected'] = array('\r\n');
		}

		$o = array();
		$o[] = array(',');
		$o[] = array(';');
		$o[] = array('\t');

		$d['delimiter']['label']                       = 'Delimiter';
		$d['delimiter']['object']['type']              = 'htmlobject_select';
		$d['delimiter']['object']['attrib']['index']   = array(0,0);
		$d['delimiter']['object']['attrib']['options'] = $o;
		$d['delimiter']['object']['attrib']['name']    = 'export[delimiter]';
		if(isset($ini['export']['delimiter'])) {
			$d['delimiter']['object']['attrib']['selected'] = array($ini['export']['delimiter']);
		} else {
			$d['delimiter']['object']['attrib']['selected'] = array(';');
		}

		$o = array();
		$o[] = array('','');
		$o[] = array("'","'");
		$o[] = array('quot','&#34;');

		$d['enclosure']['label']                       = 'Enclosure';
		$d['enclosure']['object']['type']              = 'htmlobject_select';
		$d['enclosure']['object']['attrib']['index']   = array(0,1);
		$d['enclosure']['object']['attrib']['options'] = $o;
		$d['enclosure']['object']['attrib']['name']    = 'export[enclosure]';
		if(isset($ini['export']['enclosure'])) {
			$d['enclosure']['object']['attrib']['selected'] = array($ini['export']['enclosure']);
		} else {
			$d['enclosure']['object']['attrib']['selected'] = array('quot');
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
