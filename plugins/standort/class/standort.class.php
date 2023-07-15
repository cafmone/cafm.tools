<?php
/**
 * standort
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort
{

var $delimiter   = ' / ';
var $indexprefix   = 'p';


private $__delimiter = '{{+}}';
private $__tablemaster;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param db $db
	 */
	//--------------------------------------------
	function __construct($db, $file) {
		$this->db = $db;
		$this->file = $file;
		$this->settings = $this->file->get_ini(PROFILESDIR.'standort.ini');
		if(isset($this->settings)) {
			if(isset($this->settings['query'])) {
				// handle db
				if(isset($this->settings['settings']['db'])) {
					require_once(CLASSDIR.'lib/db/query.class.php');
					$this->db = new query(CLASSDIR.'lib/db');
					$this->db->host = $db->host;
					$this->db->type = $db->type;
					$this->db->user = $db->user;
					$this->db->pass = $db->pass;
					$this->db->db   = $this->settings['settings']['db'];
				} else {
					$this->db = $db;
				}
				// handle tablemaster
				if(isset($this->settings['query']['content'])) {
					$this->__tablemaster = $this->settings['query']['content'];
				} else {
					#throw new Exception('Standort setting table is missing. Please check settings.');
				}
			} else {
				#throw new Exception('Standort settings are missing. Please check settings.');
			}
		} else {
			#throw new Exception('Standort settings are missing. Please check settings.');
		}

	}

	//--------------------------------------------
	/**
	 * Levels
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function levels() {
		$options = array();
		$levels  = $this->db->handler()->query('SELECT * FROM '.$this->settings['query']['identifiers'].' ORDER BY `pos`');
		if(is_array($levels)) {
			$i = 1;
			foreach($levels as $level) {
				$options[] = array('id' => $level['bezeichner_kurz'],'label' => $level['bezeichner_lang']);
			}
		}
		return $options;
	}

	//--------------------------------------------
	/**
	 * Tree
	 *
	 * @access public
	 * @return [string|array] returns string on error
	 */
	//--------------------------------------------
	function tree() {

		if(isset($this->__tree)) {
			return $this->__tree;
		} else {
			$sql  = 'SELECT `'.$this->__tablemaster.'`.`id` AS id, ';
			$sql .= '`'.$this->__tablemaster.'`.`bezeichner_kurz` AS identifier, ';
			$sql .= '`'.$this->__tablemaster.'`.`parent_id` AS parent, ';
			$sql .= 'GROUP_CONCAT(DISTINCT if( ( `tabelle`=\'\' OR ISNULL(`tabelle`) ) AND `merkmal_kurz`=\'NAME\', wert, NULL ) ) as NAME ';
			$sql .= 'FROM `'.$this->__tablemaster.'` GROUP BY id, identifier, parent ORDER BY NAME';
			$result = $this->db->handler()->query($sql);

			$prefix = $this->indexprefix;

			$tree = array();
			if(is_array($result)) {
				// build this result
				$this->__result = array();
				foreach($result as $k => $r) {
					// add prefix -> long int array key bug
					$this->__result[$prefix.$r['id']] = $r;
					if(isset($r['parent']) && $r['parent'] !== '') {
						$this->__children[$r['parent']][] = $r['id'];
					} else {
						$this->__children['#EMPTY#'][] = $r['id'];
					}
				}
				unset($result);
				if(isset($this->__children['#EMPTY#'])) {
					$empty = array_unique($this->__children['#EMPTY#']);
					foreach($empty as $r) {

						$tmp    = $this->__result[$prefix.$r];
						$parent = $tmp['id'];
						$path   = $tmp['NAME'];
### TODO
						$tree[$tmp['id']]['identifier'] = $tmp['identifier'];
						$tree[$tmp['id']]['label'] = $tmp['NAME'];
						$tree[$tmp['id']]['path']  = $path;
						$tree[$tmp['id']]['parent']  = $tmp['parent'];

						unset($this->__result[$prefix.$r]);
						if(count($this->__result) > 0) {
							$return = $this->__tree($parent, $path);
							if(is_array($return) && count($return) > 0) {
								$tree[$tmp['id']]['children'] = $return;
							}
						}
					}
				}
			}
			if(count($tree) > 0) {
				$this->__tree = $tree;
			}
			return $tree;
		}
	}

	//--------------------------------------------
	/**
	 * Tree
	 *
	 * @access private
	 * @param string $parent
	 * @return array
	 */
	//--------------------------------------------
	private function __tree($parent, $path = '') {
		$tree   = '';
		$result = array();
		$prefix = $this->indexprefix;

		if(isset($this->__children[$parent])) {
			$tree   = array();
			$result = $this->__children[$parent];
		}

		foreach($result as $k => $id) {
			if($this->__result[$prefix.$id]['parent'] === $parent) {
				$r = $this->__result[$prefix.$id];
				$x = $r['id'];
				$y = $path.$this->__delimiter.$r['NAME'];
#### TODO
				$tree[$r['id']]['identifier'] = $r['identifier'];
				$tree[$r['id']]['label'] = $r['NAME'];
				$tree[$r['id']]['path']  = $y;
				$tree[$r['id']]['parent']  = $r['parent'];

				unset($this->__result[$prefix.$id]);
				if(count($this->__result) > 0) {
					$return = $this->__tree($x, $y);
					if($return !== '') {
						$tree[$r['id']]['children'] = $return;
					}
				}
			}
		}
		return $tree;
	}

	//--------------------------------------------
	/**
	 * Options
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function options($tree = null) {
		$options = array();
		$prefix = $this->indexprefix;
		if(!isset($tree)) {
			$tree = $this->tree();
		}

		if(is_array($tree) && count($tree) > 0) {
			foreach($tree as $k => $v) {
				$k = (string) $k;
				$options[$prefix.$k]['id'] = $k;
				$options[$prefix.$k]['label'] = $v['label'];
				$options[$prefix.$k]['path'] = str_replace($this->__delimiter, $this->delimiter, $v['path']);
##### TODO
				if(isset($v['identifier'])){
					$options[$prefix.$k]['identifier'] = $v['identifier'];
				}
				if(isset($v['parent'])){
					$options[$prefix.$k]['parent'] = $v['parent'];
				}

				if(isset($v['children'])) {
					$tmp = $this->options($v['children']);
					$options = array_merge($options,$tmp);
				} else {
					$options[$prefix.$k]['last'] = true;
				}
			}
		}
		return $options;
	}

	//--------------------------------------------
	/**
	 * Path
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function path($tree = null) {
		$r = array();
		if(!isset($tree)) {
			$tree = $this->tree();
		}
		if(is_array($tree) && count($tree) > 0) {
			foreach($tree as $k => $v) {
				$r[$k] = str_replace($this->__delimiter, $this->delimiter, $v['path']);
				if(isset($v['children'])) {
					$tmp = $this->path($v['children']);
					$r = array_merge($r,$tmp);
				}
			}
		}
		return $r;
	}

	//--------------------------------------------
	/**
	 * Childern
	 *
	 * @access public
	 * @param string $id
	 * @return array
	 */
	//--------------------------------------------
	function children($id) {
		$id = $this->db->handler()->escape($id);

		$sql  = 'SELECT '.$this->__tablemaster.'.id AS id '; 
		$sql .= 'FROM '.$this->__tablemaster.' WHERE parent_id=\''.$id.'\' GROUP BY id';
		$children = $this->db->handler()->query($sql);
		if(is_array($children)) {
			foreach($children as $c) {
				$res = $this->children($c['id']);
				if(is_array($res)) {
					$children = array_merge($children,$res);
				}
			}
		}
		return $children;
	}

	//--------------------------------------------
	/**
	 * Parents
	 *
	 * @access public
	 * @param string $id
	 * @return array
	 */
	//--------------------------------------------
	function parents($id) {
		$str = explode(',', $this->__parents($id));
		$str = array_reverse($str);
		return $str;
	}

	//--------------------------------------------
	/**
	 * Parents
	 *
	 * @access private
	 * @param string $id
	 * @return string
	 */
	//--------------------------------------------
	private function __parents($id) {
		$str = $id;
		$parent = $this->db->select(''.$this->__tablemaster.'', array('parent_id'), array('id'=>$id),null,'1');
		if(isset($parent[0]['parent_id'])) {
			$str .= ','.$this->__parents($parent[0]['parent_id']);
		}
		return $str;
	}

}
?>
