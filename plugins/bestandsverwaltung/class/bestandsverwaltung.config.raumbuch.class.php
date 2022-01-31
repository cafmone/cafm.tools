<?php
/**
 * Raumbuch Template
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

class bestandsverwaltung_config_raumbuch
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cms_action';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'headline' => 'Template',
		'replacements' => 'Replacements',
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param user $user
	 */
	//-------------------------------------------
	function __construct($controller) {
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->user = $controller->user;
		$this->db = $controller->db;
		$this->datadir = PROFILESDIR.'bestand/templates/';
		$this->controller = $controller;
		$this->ini  = $this->file->get_ini(PROFILESDIR.'raumbuch.ini');
		$this->ebenen = $this->db->select('raumbuch_ebenen', array('ebene_kurz', 'ebene_lang'),null,'ebene_lang');
		$ebene = $this->response->html->request()->get('ebene');
		if($ebene !== '') {
			$this->ebene = $ebene;
		} else {
			$this->ebene = $this->ebenen[0]['ebene_kurz'];
		}
		$this->response->add('ebene',$this->ebene);
	}

	#function init() {
	#	$this->id = $this->response->html->request()->get($this->identifier_name);
	#}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function action( ) {
		#$this->init();

		#if($this->id !== '') {
		#	$this->download();
		#} else {
			return $this->editor();
		#}
	}

	//--------------------------------------------
	/**
	 * Content
	 *
	 * @access public
	 * @param bool $hidden
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function editor( $hidden = false) {
		$data = '';
		if( $hidden === false ) {
			require_once(CLASSDIR.'/lib/file/file.class.php');
			$file = new file(CLASSDIR.'/lib/file/');
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
			$commander = new phpcommander(CLASSDIR.'/lib/phpcommander', $this->response->html, $file->files(), 'pc', $this->response->params);
			if($commander->response->cancel()) {
				$this->response->redirect($this->response->get_url($this->actions_name,'content'));
			}
			$commander->colors = array();
			$commander->tpldir          = CLASSDIR.'/lib/phpcommander/templates';
			$commander->actions_name    = 'editor_action';
			$commander->message_param   = $this->message_param;
			$commander->identifier_name = 'ttt';
			$commander->allow['edit']   = true;
			$commander->allow['new']    = true;
			$commander->allow['delete'] = false;
			$commander->lang = $this->user->translate($commander->lang, CLASSDIR.'/lang', 'phpcommander.ini');

			$_REQUEST[$commander->identifier_name] = 'raumbuch-'.$this->ebene.'.html';
			$controller = $commander->controller($this->datadir);
			$tmp        = $controller->edit();

			$tmp->add('EditorContent', 'editorid');
			$tmp->add('', 'delete');

			$content = $tmp->get_elements('content');
			$content->wrap = 'off';
			$tmp->add($content, 'content');

			$content = $this->get_template();
			$form    = $this->get_response()->form;

			$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.config.raumbuch.html');
			$t->add($this->response->html->thisfile,'thisfile');
			$t->add($tmp->get_string(), 'editor');
			$t->add(implode('<br>', $content->__keys), 'keys');
			$t->add($form);
			$t->add($this->lang['replacements'], 'replacements');
			$t->add($this->lang['headline'], 'headline');
			$t->group_elements(array('param_' => 'form'));
			$data = $t;
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function get_template() {
		$t = $this->response->html->template($this->datadir.'raumbuch-'.$this->ebene.'.html');

		$tables = $this->db->select('raumbuch_tabellen', 'tabelle_kurz');
		if(is_array($tables)) {
			$t->add('', 'id');
			$t->add('', 'name');
			$t->add('', 'label');
			foreach($tables as $table) {
				$replace = $this->db->select('raumbuch_'.$table['tabelle_kurz'], 'merkmal_kurz', array('ebene_kurz' => $this->ebene));
				if(is_array($replace)) {
					foreach($replace as $q) {
						$t->add($q['merkmal_kurz'], $table['tabelle_kurz'].'_'.$q['merkmal_kurz']);
					}
				} else {
					$t->add('', '');
				}
			}
		}
		return $t;
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
		$form = $response->get_form($this->actions_name, 'select');

		$d['ebenen']['label']                        = 'Ebene';
		$d['ebenen']['css']                          = 'autowidth';
		$d['ebenen']['object']['type']               = 'htmlobject_select';
		$d['ebenen']['object']['attrib']['index']    = array('ebene_kurz','ebene_lang');
		$d['ebenen']['object']['attrib']['name']     = 'ebene';
		$d['ebenen']['object']['attrib']['options']  = $this->ebenen;
		$d['ebenen']['object']['attrib']['handler']  = 'onchange="phppublisher.wait();this.form.submit();"';
		$d['ebenen']['object']['attrib']['selected'] = array($this->ebene);

		$submit = $form->get_elements('submit');
		$submit->value = '&olarr;';

		$form->add($submit,'submit');
	
		$form->display_errors = false;
		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------

/*
	function download() {

		$content = $this->get_template();
		$path    = $this->datadir.'qrcode.LeitzLbl';
		$name    = 'qrcode.leitzab';
		$size    = strlen($content);

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: application/leitz");
		header("Content-Length: ".$size);
		header("Content-disposition: inline; filename=".$name);
		header("Accept-Ranges: ".$size);
		flush();
		echo $content;
		exit(0);

	}
*/

}
