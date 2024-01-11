<?php
/**
 * bestandsverwaltung_settings_inventory_export_config
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

class bestandsverwaltung_settings_inventory_export_config
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
				#if($t['tabelle_kurz'] !== 'prozess') {
					$result = $this->db->select('bestand_'.$t['tabelle_kurz'],'*');
					if(is_array($result)) {
						$str .= '<h4>'.$t['tabelle_lang'].'</h4>';
						$str .= '<div style="margin: 0 0 20px 20px;">';
						foreach($result as $r) {
							$str .= '<a href="#" onclick="attribs.print(\''.$t['tabelle_kurz'].'::'.$r['merkmal_kurz'].'\')" title="Typ: '.$r['datentyp'].', Bezeichner: '.$r['bezeichner_kurz'].'" style="cursor:pointer;">'.$r['merkmal_lang'].'</a><br>';
						}
						$str .= '</div>';
					}
				#}
			}
		}

		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.inventory.export.config.html');
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
			$error = '';
			$request = $form->get_request();
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['export']);
				unset($old['headrow']);
				$request = array_merge($old, $request);
			}
			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['msg_update_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'config', $this->message_param, $msg));
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
		$form = $this->response->get_form($this->actions_name, 'config');

		// Export

		$d['bom']['label']                    = 'BOM';
		$d['bom']['object']['type']           = 'htmlobject_input';
		$d['bom']['object']['attrib']['type'] = 'checkbox';
		$d['bom']['object']['attrib']['name'] = 'export[bom]';
		if(isset($ini['export']['bom'])) {
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
		$d['linefeed']['object']['attrib']['style']   = 'width:80px;';
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
		$d['delimiter']['object']['attrib']['style']   = 'width:80px;';
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
		$d['enclosure']['object']['attrib']['style']   = 'width:80px;';
		if(isset($ini['export']['enclosure'])) {
			$d['enclosure']['object']['attrib']['selected'] = array($ini['export']['enclosure']);
		} else {
			$d['enclosure']['object']['attrib']['selected'] = array('quot');
		}

		// headrow
		$d['headrow_id']['label']                       = 'ID';
		$d['headrow_id']['object']['type']              = 'htmlobject_input';
		$d['headrow_id']['object']['attrib']['name']    = 'headrow[id]';
		if(isset($ini['headrow']['id'])) {
			$d['headrow_id']['object']['attrib']['value'] = $ini['headrow']['id'];
		}

		$d['headrow_kurz']['label']                       = 'bezeichner_kurz';
		$d['headrow_kurz']['object']['type']              = 'htmlobject_input';
		$d['headrow_kurz']['object']['attrib']['name']    = 'headrow[kurz]';
		if(isset($ini['headrow']['kurz'])) {
			$d['headrow_kurz']['object']['attrib']['value'] = $ini['headrow']['kurz'];
		}

		$d['headrow_lang']['label']                       = 'bezeichner_lang';
		$d['headrow_lang']['object']['type']              = 'htmlobject_input';
		$d['headrow_lang']['object']['attrib']['name']    = 'headrow[lang]';
		if(isset($ini['headrow']['lang'])) {
			$d['headrow_lang']['object']['attrib']['value'] = $ini['headrow']['lang'];
		}

		$d['headrow_din']['label']                       = 'din_276';
		$d['headrow_din']['object']['type']              = 'htmlobject_input';
		$d['headrow_din']['object']['attrib']['name']    = 'headrow[din]';
		if(isset($ini['headrow']['din'])) {
			$d['headrow_din']['object']['attrib']['value'] = $ini['headrow']['din'];
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
