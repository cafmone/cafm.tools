<?php
/**
 * bbestandsverwaltung_settings_inventory_qrcode_settings
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

class bestandsverwaltung_settings_inventory_qrcode_settings
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
var $lang = array();

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
		$this->settings = $controller->settings;
		$this->ini      = $controller->ini;
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
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.inventory.qrcode.settings.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($this->lang['legend_type'], 'legend_type');
		$t->add($this->lang['legend_size'], 'legend_size');
		$t->add($this->lang['legend_url'], 'legend_url');
		$t->add($this->lang['legend_replacements'], 'legend_replacements');
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
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
			$request = $form->get_request('qrcode');

			$type = $request['settings']['type'];
			if($type === 'qrcode') {
				if(isset($request['url']['type'])) {
					if($request['url']['type'] === 'custom' && !isset($request['url']['path'])) {
						$error = $this->lang['error_no_url'];
						$form->set_error('qrcode[url][path]', '');
					}
					else if($request['url']['type'] !== 'custom') {
						unset($request['url']['path']);
					}
				} else {
					$error = 'Error: Please select an Url type';
					$form->set_error('qrcode[url][type]', '');
				}
			}

			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['msg_update_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'config', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param]['error'] = $error;
					}
			} else {
				$_REQUEST[$this->message_param]['error'] = $error;
			}
		} 
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param]['error'] = implode('<br>', $form->get_errors());
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
		$ini  = $this->ini;
		$form = $this->response->get_form($this->actions_name, 'config');

		// Qrcode
		$qtypes[] = array('barcode','Barcode C39');
		$qtypes[] = array('qrcode','QRCode L');
		$qtypes[] = array('leitz_icon','Leitz Icon');

		$d['qrcode_type']['label']                       = $this->lang['label_type'];
		$d['qrcode_type']['object']['type']              = 'htmlobject_select';
		$d['qrcode_type']['object']['attrib']['index']   = array(0,1);
		$d['qrcode_type']['object']['attrib']['options'] = $qtypes;
		$d['qrcode_type']['object']['attrib']['id']      = 'qrcode_type';
		$d['qrcode_type']['object']['attrib']['name']    = 'qrcode[settings][type]';
		$d['qrcode_type']['object']['attrib']['handler'] = 'onchange="qrcode_toggle(this);"';
		if(isset($ini['settings']['type'])) {
			$d['qrcode_type']['object']['attrib']['selected'] = array($ini['settings']['type']);
		} else {
			$d['qrcode_type']['object']['attrib']['selected'] = array('barcode');
		}

		$d['qrcode_heigth']['label']                     = $this->lang['label_height'];
		$d['qrcode_heigth']['validate']['regex']         = '/^[0-9]+$/i';
		$d['qrcode_heigth']['validate']['errormsg']      = sprintf('%s must be number', 'Heigth');
		$d['qrcode_heigth']['object']['type']            = 'htmlobject_input';
		$d['qrcode_heigth']['object']['attrib']['name']  = 'qrcode[size][height]';
		$d['qrcode_heigth']['object']['attrib']['style'] = 'width:80px;';
		if(isset($ini['size']['height'])) {
			$d['qrcode_heigth']['object']['attrib']['value'] = $ini['size']['height'];
		}

		$d['qrcode_width']['label']                     = $this->lang['label_width'];
		$d['qrcode_width']['validate']['regex']         = '/^[0-9]+$/i';
		$d['qrcode_width']['validate']['errormsg']      = sprintf('%s must be number', 'Width');
		$d['qrcode_width']['object']['type']            = 'htmlobject_input';
		$d['qrcode_width']['object']['attrib']['name']  = 'qrcode[size][width]';
		$d['qrcode_width']['object']['attrib']['style'] = 'width:80px;';
		if(isset($ini['size']['width'])) {
			$d['qrcode_width']['object']['attrib']['value'] = $ini['size']['width'];
		}

		$d['qrcode_border']['label']                     = $this->lang['label_border'];
		$d['qrcode_border']['validate']['regex']         = '/^[0-9]+$/i';
		$d['qrcode_border']['validate']['errormsg']      = sprintf('%s must be number', 'Border');
		$d['qrcode_border']['object']['type']            = 'htmlobject_input';
		$d['qrcode_border']['object']['attrib']['name']  = 'qrcode[size][border]';
		$d['qrcode_border']['object']['attrib']['style'] = 'width:80px;';
		if(isset($ini['size']['border'])) {
			$d['qrcode_border']['object']['attrib']['value'] = $ini['size']['border'];
		}

		$d['qrcode_font']['label']                     = $this->lang['label_fontsize'];
		$d['qrcode_font']['validate']['regex']         = '/^[0-9]+$/i';
		$d['qrcode_font']['validate']['errormsg']      = sprintf('%s must be number', 'Font');
		$d['qrcode_font']['object']['type']            = 'htmlobject_input';
		$d['qrcode_font']['object']['attrib']['name']  = 'qrcode[size][font]';
		$d['qrcode_font']['object']['attrib']['style'] = 'width:80px;';
		if(isset($ini['size']['font'])) {
			$d['qrcode_font']['object']['attrib']['value'] = $ini['size']['font'];
		}

		for($i=0;$i<6;$i++) {
			$d['qrcode_qr_'.$i]['label']                    = 'merkmal_kurz';
			$d['qrcode_qr_'.$i]['object']['type']           = 'htmlobject_input';
			$d['qrcode_qr_'.$i]['object']['attrib']['type'] = 'text';
			$d['qrcode_qr_'.$i]['object']['attrib']['name'] = 'qrcode[replacements]['.$i.']';
			if(isset($ini['replacements'][$i])) {
				$d['qrcode_qr_'.$i]['object']['attrib']['value'] = $ini['replacements'][$i];
			}
		}


		$d['qrcode_url_1']['label']                     = $this->lang['label_id_only'];
		$d['qrcode_url_1']['object']['type']            = 'htmlobject_input';
		$d['qrcode_url_1']['object']['attrib']['type']  = 'radio';
		$d['qrcode_url_1']['object']['attrib']['name']  = 'qrcode[url][type]';
		$d['qrcode_url_1']['object']['attrib']['value'] = 'id';
		if(isset($ini['url']['type']) && $ini['url']['type'] === 'id') {
			$d['qrcode_url_1']['object']['attrib']['checked'] = true;
		}

		$d['qrcode_url_2']['label']                     = $this->lang['label_id_auto'];
		$d['qrcode_url_2']['object']['type']            = 'htmlobject_input';
		$d['qrcode_url_2']['object']['attrib']['type']  = 'radio';
		$d['qrcode_url_2']['object']['attrib']['name']  = 'qrcode[url][type]';
		$d['qrcode_url_2']['object']['attrib']['value'] = 'auto';
		if(isset($ini['url']['type']) && $ini['url']['type'] === 'auto') {
			$d['qrcode_url_2']['object']['attrib']['checked'] = true;
		}

		$d['qrcode_url_3']['label']                     = $this->lang['label_id_custom'];
		$d['qrcode_url_3']['object']['type']            = 'htmlobject_input';
		$d['qrcode_url_3']['object']['attrib']['type']  = 'radio';
		$d['qrcode_url_3']['object']['attrib']['name']  = 'qrcode[url][type]';
		$d['qrcode_url_3']['object']['attrib']['value'] = 'custom';
		if(isset($ini['url']['type']) && $ini['url']['type'] === 'custom') {
			$d['qrcode_url_3']['object']['attrib']['checked'] = true;
		}

		$d['qrcode_url_input']['object']['type']            = 'htmlobject_input';
		$d['qrcode_url_input']['object']['attrib']['type']  = 'text';
		$d['qrcode_url_input']['object']['attrib']['name']  = 'qrcode[url][path]';
		$d['qrcode_url_input']['object']['attrib']['title'] = 'Replacement: {id}';
		if(isset($ini['url']['path'])) {
			$d['qrcode_url_input']['object']['attrib']['value'] = $ini['url']['path'];
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
