<?php
/**
 * QR Template
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
 *  Copyright (c) 2015-2017, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2017, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_qrcode_leitz
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
		'headline' => 'QRCode',
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
		$this->datadir = $controller->profilesdir.'bestand/templates/';
		$this->controller = $controller;
		$this->ini = $controller->ini;
	}

	function init() {
		$this->id = $this->response->html->request()->get($this->identifier_name);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function action( ) {
		$this->init();
		if($this->id !== '') {
			$this->download();
		} else {
			return $this->editor();
		}
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
			if(isset($this->ini['settings']['type'])) {
				switch($this->ini['settings']['type']) {
					case 'leitz_icon':
						require_once(CLASSDIR.'/lib/file/file.class.php');
						$file = new file(CLASSDIR.'/lib/file/');
						require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
						$commander = new phpcommander(CLASSDIR.'/lib/phpcommander', $this->response->html, $file->files(), 'pc', $this->response->params);
						if($commander->response->cancel()) {
							$this->response->redirect($this->response->get_url($this->actions_name,'content'));
						}
						$commander->colors = array();
						$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
						$commander->actions_name = 'cms_action';
						$commander->message_param = $this->message_param;
						$commander->identifier_name = 'ttt';
						$commander->allow['edit'] = true;
						$commander->allow['delete'] = false;
						$commander->lang = $this->user->translate($commander->lang, CLASSDIR.'/lang', 'phpcommander.ini');

						$_REQUEST[$commander->identifier_name] = 'qrcode.LeitzLbl';
						$controller = $commander->controller($this->datadir);
						$tmp        = $controller->edit();

						$tmp->add('', 'delete');
						$tmp->add('', 'close');

						$content = $tmp->get_elements('content');
						$content->wrap = 'off';
						$tmp->add($content, 'content');

						$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.settings.inventory.qrcode.leitz.html');
						$t->add($tmp->get_string(), 'editor');
						$content = $this->get_template();
						if(isset($content->__keys)) {
							unset($content->__keys['?']);
							$t->add(implode('<br>', $content->__keys), 'keys');
						}
						$t->add($this->lang['replacements'], 'replacements');
						$t->add($this->lang['headline'], 'headline');
						$data = $t;
					break;
					default:
						$data = 'nothing to do';
					break;
				}
			}
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
		$t = $this->response->html->template($this->datadir.'qrcode.LeitzLbl');
		$t->add('', 'id');
		$t->add('', 'bezeichner_lang');
		$t->add('', 'bezeichner_kurz');
		if(isset($this->ini['replacements']) && is_array($this->ini['replacements'])) {
			foreach($this->ini['replacements'] as $qk => $qr) {
				if($qk !== 'typ') {
					$t->add($qr, $qr);
				}
			}
		}
		return $t;
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function download() {
		if(isset($this->ini['qrcode']['typ'])) {
			switch($this->ini['qrcode']['typ']) {
				case 'leitz_icon':
					$content = $this->get_template();
					$name    = 'qrcode.LeitzAB';
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
				break;
				default:
					$pdf = $this->get_template();
					$pdf->Output('qrcodes.pdf', 'D');
				break;
			}
		}
	}

}
