<?php
/**
 * gewerke
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

class gewerke {

var $indent = '&#160;&#160;&#160;&#160;';
var $delimiter = '(++)';

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

	//---------------------------------------
	/**
	 * Get Gewerke List
	 * 
	 * @access public
	 * @return array
	 */
	//---------------------------------------
	function listGewerke( $offset=null, $includes=null, $clip='', $din=false ) {

		$sql  = 'SELECT  g.gewerk_kurz, b.bezeichner_kurz, b.bezeichner_lang, b.din_276 ';
		$sql .= 'FROM `bezeichner` as b, `gewerk2bezeichner` as g ';
		$sql .= 'WHERE b.bezeichner_kurz=g.bezeichner_kurz ';
		if(isset($includes)) {
			$includes = 'AND (b.bezeichner_kurz=\''.implode('\' OR b.bezeichner_kurz=\'', $includes).'\')';
			$sql .= $includes;
		}
		if($din === true) {
			$sql .= 'ORDER BY b.din_276,b.bezeichner_lang';
		} else {
			$sql .= 'ORDER BY b.bezeichner_lang';
		}
		$bez = $this->db->handler()->query($sql);

#echo '<pre>';
#print_r($bez);
#echo '</pre>';

		$bezeichner = array();
		if(is_array($bez)) {
			foreach($bez as $b) {

### TODO din_276
				$din_276 = '';
				if($din === true && isset($b['din_276'])) {
					$din_276 = $b['din_276'].' ';
				}

				$bezeichner[$b['gewerk_kurz']][$b['bezeichner_kurz']] = $din_276.$b['bezeichner_lang'].' ('.$b['bezeichner_kurz'].')';
			}
		}

#$bezeichner['ZUTRITT']['ZKA'] = 'ccc';


		$sql  = 'SELECT gewerk_kurz, gewerk_lang, parent ';
		$sql .= 'FROM `gewerke` ';
		if(isset($offset)) {
			$sql .= 'WHERE gewerk_kurz=\''.$offset.'\' ';
			$sql .= 'OR parent=\''.$offset.'\' ';
			$sql .= 'OR parent LIKE \'%,'.$offset.'\' ';
			$sql .= 'OR parent LIKE \'%,'.$offset.',%\' ';
			$sql .= 'OR parent LIKE \''.$offset.',%\' ';
		}
		$sql .= 'ORDER BY gewerk_lang ';
		$gewerke = $this->db->handler()->query($sql);

		$gewerk = array();
		#$gewerk['clip'] = array();

		if(is_array($gewerke)) {
			$i = 1;
			foreach($gewerke as $g) {

				if($clip === $g['gewerk_kurz']) {
					$gewerk['clip'] = explode(',', $g['parent']);
					array_push($gewerk['clip'], $g['gewerk_kurz']);
				}

				if(isset($g['parent'])) {
					$parents = explode(',', $g['parent']);
					array_push($parents, $g['gewerk_kurz']);
					$return = $this->array2arrays($parents, 0, $g['gewerk_lang'], $bezeichner);
					$gewerk = array_merge_recursive($gewerk, $return);
				} else {
					$gewerk[$g['gewerk_kurz']]['label'] = $g['gewerk_lang'];
					if(array_key_exists($g['gewerk_kurz'], $bezeichner)) {
						$sql = '';
						foreach($bezeichner[$g['gewerk_kurz']] as $k => $b) {
							if($sql === '') {
								$sql .= $k;
							} else {
								$sql .= $this->delimiter.$k;
							}
						}
						$gewerk[$g['gewerk_kurz']]['sql'] = $sql;
						$gewerk[$g['gewerk_kurz']]['bezeichner'] = $bezeichner[$g['gewerk_kurz']];
					}
				}
			}
		}

#echo '<pre>';
#print_r($gewerk);
#echo '</pre>';

		return $gewerk;
	}

	//---------------------------------------
	/**
	 * Multidimesional array from array
	 * 
	 * Example:
	 * input array(a,b,c)
	 * output array(a => array(b => array(c =>'value')))
	 *
	 * @access public
	 * @param array $array array to convert
	 * @param integer $num array index
	 * @param string $value value
	 * @return array
	 */
	//---------------------------------------
	function array2arrays( $array, $num, $value, $bezeichner) {
		$sql = '';
		if(isset($array[$num])) {
			if(isset($array[$num+1])) {
				$r[$array[$num]] = $this->array2arrays($array, $num+1, $value, $bezeichner);
				if(
					isset($r[$array[$num]][$array[$num+1]]) && 
					isset($r[$array[$num]][$array[$num+1]]['sql'])
				) {
					$sql = $r[$array[$num]][$array[$num+1]]['sql'];
				}
			} else {
				$r[$array[$num]]['label'] = $value;
				if(array_key_exists($array[$num], $bezeichner)) {
					foreach($bezeichner[$array[$num]] as $k => $b) {
						if($sql === '') {
							$sql .= $k;
						} else {
							$sql .= $this->delimiter.$k;
						}
					}
					$r[$array[$num]]['bezeichner'] = $bezeichner[$array[$num]];
				}
			}
			if($sql !== '') {
				$r[$array[$num]]['sql'] = $sql;
			}
		} else {
			$r = $value;
		}
		return $r;
	}

	//--------------------------------------------
	/**
	 * Get bezeichner sql string by gewerk
	 *
	 * @access public
	 * @param string $gewerk
	 * @param bool $array
	 * @return string|array
	 */
	//--------------------------------------------
	function gewerk2sql($gewerk, $array=false) {

		$gewerk  = $this->db->handler()->escape($gewerk);
		$gewerke = $this->__getChildren($gewerk);
		$str = 'WHERE gewerk_kurz=\''.$gewerk.'\'';
		if(is_array($gewerke)) {
			foreach($gewerke as $g) {
				$str .= ' OR gewerk_kurz=\''.$g['g'].'\'';
			}
		}

		$sql  = 'SELECT bezeichner_kurz as b ';
		$sql .= 'FROM `gewerk2bezeichner` ';
		$sql .= $str;
		$bezeichner = $this->db->handler()->query($sql);

		$str = '';
		$i = 0;
		if(is_array($bezeichner)) {
			if($array === false) {
				foreach($bezeichner as $b) {
					if($i === 0) {
						$str .= ' bezeichner_kurz=\''.$b['b'].'\'';
					} else{
						$str .= ' OR bezeichner_kurz=\''.$b['b'].'\'';
					}
					$i = 1;
				}
			}
			else if($array === true) {
				$str = array();
				foreach($bezeichner as $b) {
					$str[] = $b['b'];
				}
			}
		}
		return $str;
	}

	//---------------------------------------
	/**
	 * Get gewerk by bezeichner
	 *
	 * @access public
	 * @param string $bezeichner
	 * @return string
	 */
	//---------------------------------------
	function bezeichner2gewerk($bezeichner) {

		$str = '';
		$bezeichner  = $this->db->handler()->escape($bezeichner);

		$sql  = 'SELECT g.gewerk_lang AS label, g.gewerk_kurz AS gewerk, g.parent AS parent ';
		$sql .= 'FROM `gewerk2bezeichner` AS g2b,`gewerke` AS g ';
		$sql .= 'WHERE g2b.bezeichner_kurz=\''.$bezeichner.'\' ';
		$sql .= 'AND g.gewerk_kurz=g2b.gewerk_kurz ';
		$sql .= 'ORDER BY g.gewerk_lang';
		$gewerke = $this->db->handler()->query($sql);

		if(is_array($gewerke)) {
			foreach($gewerke as $g) {
				if(isset($g['parent']) && $g['parent'] !== '') {
					$parents = explode(',', $g['parent']);
					foreach($parents as $p) {
						if($p !== '') {
							$label = $this->db->select('gewerke','gewerk_lang',array('gewerk_kurz' => $p));
							if(is_array($label)) {
								$str .= ''.$label[0]['gewerk_lang'].' / ';
							}
						}
					}
					$str .= ''.$g['label'].'<br>';
				} else {
					$str .= '<div>'.$g['label'].'</div>';
				}
			}
		}
		return $str;
	}

	//---------------------------------------
	/**
	 * Get children by gewerk
	 *
	 * @access protected
	 * @param string $gewerk
	 * @return array
	 */
	//---------------------------------------
	function __getChildren($gewerk) {
		$sql  = 'SELECT gewerk_kurz as g ';
		$sql .= 'FROM `gewerke` ';
		$sql .= 'WHERE parent LIKE \''.$gewerk.'\' ';
		$sql .= 'OR parent LIKE \'%'.$gewerk.'\' ';
		$sql .= 'OR parent LIKE \'%'.$gewerk.'%\' ';
		$sql .= 'OR parent LIKE \''.$gewerk.'%\' ';
		$sql .= 'ORDER BY gewerk_lang ';
		$gewerke = $this->db->handler()->query($sql);

		return $gewerke;
	}

	//---------------------------------------
	/**
	 * Count bestand by gewerk
	 *
	 * @access protected
	 * @param string $gewerk
	 * @param string $key
	 * @param string $value
	 * @return integer
	 */
	//---------------------------------------
	function count($gewerk, $key = null, $value = null) {
		$count   = 0;
		$gewerk  = $this->db->handler()->escape($gewerk);
		$where   = $this->gewerk2sql($gewerk);
		if($where !== '') {
			$sql  = 'SELECT id ';
			if(isset($key) && isset($value)) {
				$sql .= ', GROUP_CONCAT(DISTINCT if( ';
				$sql .= '`merkmal_kurz`=\''.$key.'\' AND `wert`=\''.$value.'\', ';
				$sql .= 'wert, NULL ) ) AS \'WE\' ';
			}
			$sql .= 'FROM `bestand` ';
			$sql .= 'WHERE '.$where.' ';
			$sql .= 'GROUP BY id ';
			if(isset($key) && isset($value)) {
				$sql .= 'HAVING WE=\''.$value.'\' ';
			}
			$ids = $this->db->handler()->query($sql);
		}
		if(isset($ids) && is_array($ids)) {
			$count = count($ids);
		}
		return $count;
	}

	//---------------------------------------
	/**
	 * Options array
	 *
	 * @access public
	 * @return array(aray(id=>'',label=>''))
	 */
	//---------------------------------------
	function options($includes=null) {
		$opts  = array();
		$order = array();

		if(isset($includes)) {
			$sql  = 'SELECT ';
			$sql .= 'b.bezeichner_kurz, ';
			$sql .= 'g.gewerk_kurz, g.gewerk_lang, g.parent ';
			$sql .= 'FROM `gewerke` as g ';
			$sql .= 'LEFT JOIN `gewerk2bezeichner` as g2b ON (g.gewerk_kurz=g2b.gewerk_kurz) ';
			$sql .= 'LEFT JOIN `bezeichner` as b ON (g2b.bezeichner_kurz=b.bezeichner_kurz) ';
			if(isset($includes)) {
				$sql .= 'WHERE b.bezeichner_kurz=\''.implode('\' OR b.bezeichner_kurz=\'', $includes).'\'';
			}
			$sql .= 'GROUP BY g.gewerk_kurz ';
			#$sql .= 'ORDER BY g.gewerk_lang ';

			$bez = $this->db->handler()->query($sql);
			$bezeichner = array();
			if(is_array($bez)) {
				foreach($bez as $b) {
					$bezeichner = array_merge(explode(',',$b['parent']), $bezeichner) ;
					$bezeichner[] = $b['gewerk_kurz'];
				}
			}
		}

		$sql  = 'SELECT gewerk_kurz, gewerk_lang, parent ';
		$sql .= 'FROM `gewerke` ';
		$sql .= 'ORDER BY gewerk_lang ';
		$gewerke = $this->db->handler()->query($sql);

		if(is_array($gewerke)) {
			$i = 0;
			foreach($gewerke as $k => $g) {
				if(isset($g['parent'])) {

					if(!isset($bezeichner) || in_array($g['gewerk_kurz'], $bezeichner)) {

						$parents = explode(',',$g['parent']);
						$parent  = $parents[count($parents)-1];
						$opts[$i]['parent'] = $parent;
						$opts[$i]['level']  = count($parents);
						$opts[$i]['id']     = $g['gewerk_kurz'];
						$opts[$i]['label']  = $g['gewerk_lang'];

						$order[count($parents)][$parent][] = $i;
					}
				} else {
					$opts[$i]['parent'] = '';
					$opts[$i]['level']  = 0;
					$opts[$i]['id']     = $g['gewerk_kurz'];
					$opts[$i]['label']  = $g['gewerk_lang'];

					$order['root'][$i] = $g['gewerk_kurz'];
				}
				$i++;
			}

			$str = '';
			foreach($order['root'] as $k => $r) {
				$str .= $r.$this->delimiter.$opts[$k]['label']."\n";
				$str .= $this->__options($r, $opts, $order, 1, $opts[$k]['label']);
			}

			$options = array();
			$tmp = explode("\n", $str);
			if(is_array($tmp)) {
				foreach($tmp as $t) {
					if($t !== '') {
						$tp = explode($this->delimiter, $t);
						$options[$tp[0]] = array('id' => $tp[0],'label' => $tp[1]);
					}
				}
			}
			return $options;

		} else {
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Generate options
	 *
	 * @access protected
	 * @param string $key
	 * @param array $opts
	 * @param array $order
	 * @param int $level
	 * @return string
	 */
	//--------------------------------------------
	function __options($key, $opts, $order, $level, $path = '') {
		if(isset($order[$level])) {
			if(array_key_exists($key, $order[$level])) {
				$str = '';
				foreach($order[$level][$key] as $k => $r) {
					$opt = $opts[$r];
					if($path !== '') {
						$p = $path.' / '.$opt['label'];
					} else {
						$p = $opt['label'];
					}
					//$str .= $opt['id'].$this->delimiter.str_repeat($this->indent,$level).$opt['label']."\n";
					$str .= $opt['id'].$this->delimiter.$p."\n";

					if(isset($order[$level+1])) {
						$str .= $this->__options($opt['id'], $opts, $order, $level+1, $p);
					}
				}
				return $str;
			}
		}
	}

}
?>
