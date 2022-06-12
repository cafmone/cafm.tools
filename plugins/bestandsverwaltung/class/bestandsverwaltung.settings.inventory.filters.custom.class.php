<?php
/**
 * bestandsverwaltung_settings_inventory_filters_custom
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
 *  Copyright (c) 2015-2019, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2019, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_inventory_filters_custom
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
		$this->db       = $controller->db;
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
		$str = '';
		$tables = $this->db->select('bestand_index','*',null,'`pos`');
		if(is_array($tables)) {
			foreach($tables as $t) {
				if($t['tabelle_kurz'] !== 'prozess') {
					$result = $this->db->select('bestand_'.$t['tabelle_kurz'],'*');
					if(is_array($result)) {
						$str .= '<h4>'.$t['tabelle_lang'].'</h4>';
						$str .= '<div style="margin: 0 0 20px 20px;">';
						foreach($result as $r) {
							$str .= '<a href="#" onclick="attribs.print(\''.$t['tabelle_kurz'].'::'.$r['merkmal_kurz'].'\')" title="Typ: '.$r['datentyp'].', Bezeichner: '.$r['bezeichner_kurz'].'" style="cursor:pointer;">'.$r['merkmal_lang'].'</a><br>';
						}
						$str .= '</div>';
					}
				}
			}
		}

		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.inventory.filters.custom.html');
		$t->add($vars);
		$t->add($form);
		$t->add($str, 'attribs');
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('filter_' => 'filter'));


		// add button to form field
		$filters = $t->get_elements('filter');
		if(is_array($filters)) {
			foreach($filters as $k => $v) {
				$button = '<a class="icon icon-info" onclick="attribs.popup(\''.$k.'\');" style="cursor:pointer;display:block;float:right;margin:8px 0 0 -25px;"></a>';
				$v->add($button);

			}
		}
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
			$request = $form->get_request();
			if($request === '') {
				$request = array();
			}
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				#unset($old['settings']);
				#unset($old['export']);
				unset($old['filter']);
				$request = array_merge($old, $request);
			}
			$error = $this->file->make_ini( $this->settings, $request );
			if( $error === '' ) {
				$msg = $this->lang['msg_update_sucess'];
				$this->response->redirect($this->response->get_url($this->actions_name, 'custom', $this->message_param, $msg));
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
		$ini  = $this->file->get_ini( $this->settings, true, true );
		
		var_dump($ini);
		
		
		
		$form = $this->response->get_form($this->actions_name, 'custom');

		// Filters

		for($i=0;$i<10;$i++) {
			$d['filter_f_'.$i]['label']                     = ''.$i+1;
			$d['filter_f_'.$i]['css']                       = 'autosize';
			$d['filter_f_'.$i]['style']                     = 'float:right; clear:both;';
			$d['filter_f_'.$i]['object']['type']            = 'htmlobject_input';
			$d['filter_f_'.$i]['object']['attrib']['type']  = 'text';
			$d['filter_f_'.$i]['object']['attrib']['id']    = 'filter_f_'.$i;
			$d['filter_f_'.$i]['object']['attrib']['name']  = 'filter['.$i.']';
			$d['filter_f_'.$i]['object']['attrib']['style'] = 'display:block;float:left;margin-right:25px; width:350px;';
			if(isset($ini['filter'][$i])) {
				$d['filter_f_'.$i]['object']['attrib']['value'] = $ini['filter'][$i];
			}
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
