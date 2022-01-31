<?php
/**
 * bestandsverwaltung_settings_export_arbeitskarte
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

class bestandsverwaltung_settings_export_arbeitskarte
{
/**
* translation
* @access public
* @var string
*/
var $lang = array();

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
		$this->controller  = $controller;
		$this->user        = $controller->user->get();
		$this->db          = $controller->db;
		$this->file        = $controller->file;
		$this->response    = $controller->response;
		$this->settings    = $controller->settings;
		$this->profilesdir = $controller->profilesdir;

		if($this->response->html->request()->get('debug') !== '' && 
			$this->response->html->request()->get('debug') === 'true' 
		) {
			$this->debug = true;
		} else {
			$this->debug = null;
		}

		$this->tmpfile = $this->profilesdir.'export/export.arbeitskarte.tmp';

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/raumbuch.class.php');
		$this->raumbuch = new raumbuch($this->db);
		$this->raumbuch->delimiter = '/';
		$this->raumbuch->options = $this->raumbuch->options();
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
	function action() {
		$response = $this->select();
		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.export.arbeitskarte.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('filter_' => 'filter'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->doexport();
		if(isset($response->msg)) {
			$msg = 'updated';
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		else if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function doexport() {
		$response = $this->get_response();
		$settings = $this->controller->settings;
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			#$params = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.arbeitskarte.ini');
			#$params['tables'] = $form->get_request('tables');
			#$error = $this->file->make_ini( $this->profilesdir.'/bestandsverwaltung.export.arbeitskarte.ini', $params );
			$error = '';
			if( $error === '' ) {
				if(!$this->file->exists($this->tmpfile)) {
					#$error = $this->file->mkfile($this->tmpfile,'');
					if( $error === '' ) {

$GLOBALS['TEST'] = $settings;

$_GET['xxx'] = 'Fred';


#exec('php -f '.CLASSDIR.'plugins/bestandsverwaltung/cgi/export.php >/dev/null &2>/dev/null &');

exec('php -f '.CLASSDIR.'plugins/bestandsverwaltung/cgi/export.php', $output);

var_dump($output);
exit();

#echo 'do it';


					} else {
						$response->error = $error;
					}
				} else {
					$response->error = 'Export running. Please try again later.';
				}
			} else {
				$response->error = $error;
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$settings = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.arbeitskarte.ini');
		$d        = array();

/*
		$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang', null, 'pos');
		$d['attribs']['label']                        = 'Attribute';
		$d['attribs']['required']                     = true;
		$d['attribs']['css']                          = 'autosize pull-right';
		$d['attribs']['style']                        = 'clear:both;';
		$d['attribs']['object']['type']               = 'htmlobject_select';
		$d['attribs']['object']['attrib']['index']    = array('tabelle_kurz','tabelle_lang');
		$d['attribs']['object']['attrib']['name']     = 'tables[]';
		$d['attribs']['object']['attrib']['multiple'] = true;
		$d['attribs']['object']['attrib']['options']  = $tables;
		$d['attribs']['object']['attrib']['size']     = count($tables);
		$d['attribs']['object']['attrib']['style']    = 'width: 300px;';
		if(isset($settings['tables'])) {
			$d['attribs']['object']['attrib']['selected'] = $settings['tables'];
		}
*/

		$form = $response->get_form($this->actions_name, 'arbeitskarte');

		$submit = $form->get_elements('submit');
		$s = $this->response->html->button();
		$s->type  = 'submit';
		$s->css   = $submit->css;
		$s->name  = $submit->name;
		$s->label = $submit->value;
		$s->value = $submit->value;

		$form->add($s,'submit');

		$form->add('','cancel');
		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
