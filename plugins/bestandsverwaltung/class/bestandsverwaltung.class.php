<?php
/**
 * bestandsverwaltung
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
 * @copyright Copyright (c) 2008 - 2018, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung {

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param db $db
	 */
	//--------------------------------------------
	function __construct($db) {
		$this->db = $db;
	}


	function form( $bezeichner, $fields = null ) {
		// Get attribs from tables

		$tables = $this->db->select('bestand_index', array('tabelle_kurz','tabelle_lang'), null, 'pos');
		if(is_array($tables)) {
			foreach($tables as $table) {
				$this->tables[$table['tabelle_kurz']] = $table['tabelle_lang'];
			}
		} else {
			$this->tables = array();
		}

		$result = array();
		foreach($this->tables as $k => $t) {
			$sql  = 'SELECT * ';
			$sql .= 'FROM bestand_'.$k.' ';
			$sql .= 'WHERE `bezeichner_kurz`=\''.$bezeichner.'\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$bezeichner.'\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \'%,'.$bezeichner.',%\' ';
			$sql .= 'OR `bezeichner_kurz`LIKE \''.$bezeichner.',%\' ' ;
 			// Wildcard *
			$sql .= 'OR `bezeichner_kurz`=\'*\' ';
			$result[$k] = $this->db->handler()->query($sql);
		}

		// Assemble form
		$d = array();
		if(is_array($result) && count($result) > 0) {
			foreach ( $result as $k => $v ) {
				$fields = array();
				$found  = array();
				if(is_array($v)) {
					// handle fields
					#if(isset($this->fields[$k])) {
					#	$fields = $this->fields[$k];
					#	// unset field to find lost tables
					#	unset($this->lost[$k]);
					#}
					foreach ( $v as $r ) {
						// handle lost
						#$found[$r['merkmal_kurz']] = '';
						$addempty = true;
						if($k === 'prozess') {
							$addempty = false;
						}
						// handle todo
						if($k === 'TODO') {
							$table = 'todo_';
						} else {
							$table = 'bestand_';
						}
						$d = array_merge($d, $this->element($r, $k, $k, $fields, $table, $addempty));
					}
				} else {
					if(is_string($v) && $v !== '') {
						$d[$k.'_0'] = $v;
					}
				}
				// handle lost
				#$lost[$k] = array_diff_key($fields, $found);
				#if(count($lost[$k]) < 1) {
				#	unset($lost[$k]);
				#}
			}
		}
		return $d;

	}



	//--------------------------------------------
	/**
	 * Get Element for htmlobject_formbuilder
	 *
	 * @access public
	 * @param array $r
	 * @param string $prefix
	 * @param array $fields
	 * @param string $tableprefix = bestand_
	 * @param bool $addempty = true
	 * @return array
	 */
	//--------------------------------------------
	function element($r, $prefix, $name, $fields = array(), $tableprefix = 'bestand_', $addempty = true) {

		$mark = $r['merkmal_kurz'];
		$d[$prefix.'_'.$mark]['label'] = $r['merkmal_lang'];

		switch($r['datentyp']) {
			default:
			case '':
			case 'text':
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_input';
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['value'] = '';
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					$d[$prefix.'_'.$mark]['object']['attrib']['value'] = $fields[$r['merkmal_kurz']]['wert'];
				}
			break;
			case 'textarea':
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_textarea';
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				$d[$prefix.'_'.$mark]['object']['attrib']['cols']  = 30;
				$d[$prefix.'_'.$mark]['object']['attrib']['rows']  = 6;
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					$d[$prefix.'_'.$mark]['object']['attrib']['value'] = $fields[$r['merkmal_kurz']]['wert'];
				}
			break;
			case 'bool':
			case 'checkbox':
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_input';
				$d[$prefix.'_'.$mark]['object']['attrib']['type']  = 'checkbox';
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['value'] = '1';
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					$d[$prefix.'_'.$mark]['object']['attrib']['checked'] = true;
				}
			break;
			case 'int':
			case 'integer':
				$d[$prefix.'_'.$mark]['validate']['regex']         = '/^[0-9]+$/i';
				$d[$prefix.'_'.$mark]['validate']['errormsg']      = sprintf('%s must be number', $r['merkmal_lang']);
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_input';
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					$d[$prefix.'_'.$mark]['object']['attrib']['value'] = $fields[$r['merkmal_kurz']]['wert'];
				}
			break;
			case 'katalog':
				#$merkmal = $this->db->handler()->escape($r['merkmal_kurz']);
				#$where   = '`merkmal_kurz`=\''.$merkmal.'\'';
				#$options = $this->db->select('bestand_katalog','wert', $where);
				#if($options === ''){
				#	$where   = '`merkmal_kurz`=\''.$merkmal.'\'';
				#	$options = $this->db->select('bestand_katalog','wert', $where);
				#}
				$options = $this->db->select('bestand_katalog','wert', array('merkmal_kurz', $r['merkmal_kurz']));
				// add empty option
				if(is_array($options) && $addempty === true) {
					array_unshift($options, array('wert' => ''));
				}
				// mark as deprecated
				$d[$prefix.'_'.$mark]['css'] = 'deprecated';
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_select';
				$d[$prefix.'_'.$mark]['object']['attrib']['index'] = array('wert','wert');
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['options'] = $options;
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					$d[$prefix.'_'.$mark]['object']['attrib']['selected'] = array($fields[$r['merkmal_kurz']]['wert']);
				}
			break;
			case 'select':
				$sql  = 'SELECT ';
				$sql .= '`o`.`row`, ';
				$sql .= '`o`.`value` ';
				$sql .= 'FROM `'.$tableprefix.'option2attrib` AS o2a ';
				$sql .= 'RIGHT JOIN '.$tableprefix.'options AS o ON(o2a.option=o.option) ';
				$sql .= 'WHERE `o2a`.`attrib`=\''.$r['merkmal_kurz'].'\' ';
				//$sql .= 'ORDER BY `o`.`value`';
				$options = $this->db->handler()->query($sql);
				if(!is_array($options)) {
					$options = array();
				}
				// add empty option
				else if(is_array($options) && $addempty === true) {
					array_unshift($options, array('value' => '', 'row' => ''));
				}
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_select';
				$d[$prefix.'_'.$mark]['object']['attrib']['index'] = array('row','value');
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].']';
				$d[$prefix.'_'.$mark]['object']['attrib']['options'] = $options;
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					if(is_numeric($fields[$r['merkmal_kurz']]['wert']) === true) {
						$d[$prefix.'_'.$mark]['object']['attrib']['selected'] = array($fields[$r['merkmal_kurz']]['wert']);
					} else {
						// handle 
						foreach($options as $o) {
							if($o['value'] === $fields[$r['merkmal_kurz']]['wert']) {
								$d[$prefix.'_'.$mark]['object']['attrib']['selected'] = array($o['row']);
								break;
							}
						}
					}
				}
			break;
			case 'multiple':
				$sql  = 'SELECT ';
				$sql .= '`o`.`value` ';
				$sql .= 'FROM `'.$tableprefix.'option2attrib` AS o2a ';
				$sql .= 'RIGHT JOIN '.$tableprefix.'options AS o ON(o2a.option=o.option) ';
				$sql .= 'WHERE `o2a`.`attrib`=\''.$r['merkmal_kurz'].'\' ';
				//$sql .= 'ORDER BY `o`.`value`';
				$options = $this->db->handler()->query($sql);
				if(!is_array($options)) {
					$options = array();
				}
				$d[$prefix.'_'.$mark]['object']['type']            = 'htmlobject_select';
				$d[$prefix.'_'.$mark]['object']['attrib']['index'] = array('value','value');
				$d[$prefix.'_'.$mark]['object']['attrib']['name']  = $name.'['.$r['merkmal_kurz'].'][]';
				$d[$prefix.'_'.$mark]['object']['attrib']['options'] = $options;
				$d[$prefix.'_'.$mark]['object']['attrib']['multiple'] = true;
				$d[$prefix.'_'.$mark]['object']['attrib']['title'] = $r['merkmal_kurz'];
				if(array_key_exists($r['merkmal_kurz'], $fields)) {
					### TODO delimiter
					$d[$prefix.'_'.$mark]['object']['attrib']['selected'] = explode('[~]',$fields[$r['merkmal_kurz']]['wert']);
				}
			break;
		}

		if($d[$prefix.'_'.$mark] !== '') {
			if(
				isset($r['pflichtfeld']) && 
				$r['pflichtfeld'] == 1
			) {
				$d[$prefix.'_'.$mark]['required'] = true;;
			}
			if(
				isset($r['minimum']) && 
				$r['minimum'] !== '' && 
				$r['datentyp'] !== 'select'
			) {
				$d[$prefix.'_'.$mark]['object']['attrib']['minlength'] = $r['minimum'];
			}
			if(
				isset($r['maximum']) && 
				$r['maximum'] !== '' && 
				$r['datentyp'] !== 'select'
			) {
				$d[$prefix.'_'.$mark]['object']['attrib']['maxlength'] = $r['maximum'];
			}
		}

		return $d;
	}

}
?>
