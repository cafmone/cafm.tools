<?php
/**
 * standort_settings_import_step4
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_import_step4
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
		$this->profilesdir = $controller->profilesdir;
		$this->datadir     = $this->profilesdir.'import/standort/';
		$this->path        = $this->datadir.'standort.xlsx';

		$cache = $this->file->get_ini($this->datadir.'standort.cache.ini');
		$this->params = $cache['params'];
		unset($cache['params']);
		if(isset($cache['errors'])) {
			$this->errors = $cache['errors'];
			unset($cache['errors']);
		}
		$this->cache = $cache;

		$this->ids = array();
		$this->path = array();
		$standort = $this->controller->standort;
		$standort->delimiter = $this->params['delimiter'];
		$tmp = $standort->options();
		if(is_array($tmp)) {
			foreach($tmp as $v) {
				$this->path['p#'.$v['id']] = $v['path'];
				$this->ids[] = $v['id'];
			}
		}
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
		$error = $this->import($this->cache);
		if(isset($error) && $error !== '') {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'step3', $this->message_param.'[error]', $error)
			);
		} else {
			$this->file->remove($this->datadir.'standort.cache.ini');
			$this->file->remove($this->datadir.'standort.xlsx');
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'step1', $this->message_param.'[sucess]', 'sucessfully imported')
			);
		}
	}

	//--------------------------------------------
	/**
	 * Import 1. step
	 *
	 * @access public
	 * @return [string|empty]
	 */
	//--------------------------------------------
	function import($cache) {

		$errors = array();
		$data   = array();

		// sort $cache by depth - shortest last
		$cache = $this->__sort( $cache, 'depth','DESC' );

		if(is_array($cache) && count($cache) > 0) {
			$i = 0;
			foreach($cache as $k => $r) {
				$error = '';
				$str   = $r['path'];
				if($str !== '') {
					if(isset($this->params['id']) && $this->params['id'] !== '') {
						if(in_array($r['id'], $this->ids)) {
							$error = sprintf($this->lang['error_duplicate_id'], $r['id'], $r['row']);
						}
					}
					if($error === '') {
						if(in_array($str, $this->path)) {
							$error = sprintf($this->lang['error_duplicate_path'], $r['row']);
						}
					}
					if($error === '') {
						$parents = explode($this->controller->standort->delimiter, str_replace("\t",'',$str));
						$return  = $this->__array2arrays( $r['id'], $parents, 0, $r['row']);
						$data    = array_replace_recursive ($data, $return);
					} else {
						$errors[] = $error;
					}
				}
				$i++;
			}
		}
		elseif(is_array($cache) && count($cache) === 0) {
			$errors[] = $this->lang['error_nothing_todo'];
		}

		if(count($errors) < 1) {
			$errors = $this->insert($data);
		}

		if(isset($errors) && $errors !== '') {
			natsort($errors);
			$error = implode('<br>', $errors);
		}
		return $error;
	}

	//------------------------------------------------
	/**
	 * Sort array [ids] by key [sort]
	 *
	 * @access protected
	 * @param array $ids
	 * @param string $sort
	 * @param enum $order [ASC/DESC]
	 * @return array
	 */
	//------------------------------------------------
	function __sort($ids, $sort, $order = '') {
		if($order !== '') {
			if($order == 'ASC') $sort_order = SORT_ASC;
			if($order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		$column = array();
		reset($ids);
		foreach($ids as $val) {
			if(isset($val[$sort])) {
				$column[] = $val[$sort];
			}
		}
		if(count($ids) === count($column)) {
			array_multisort($column, $sort_order, $ids);
		}
		return $ids;
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
	 * @param string $masterid id to set for last value
	 * @param array $array array to convert
	 * @param integer $num array index
	 * @return array
	 */
	//---------------------------------------
	function __array2arrays( $masterid, $array, $num, $row, $path = '') {
		$r = array();
		if(isset($array[$num])) {
			// set path
			if($path !== '') {
				$path = $path.$this->params['delimiter'].$array[$num];
			} else {
				$path = $array[$num];
			}
			$r[$array[$num]]['label'] = $array[$num];
			$r[$array[$num]]['row']   = 'row '.$row.' / '.($num+1);

			### TODO more testing
			// try to find path in database -> get id if exsist
			$key = array_search($path, $this->path);
			if($key !== false) {
				$r[$array[$num]]['id'] = str_replace('p#', '', $key);
				unset($r[$array[$num]]['label']);
			} else {
				$r[$array[$num]]['id'] = uniqid('i');
			}

			// parse children
			if(isset($array[$num+1])) {
				$r[$array[$num]]['children'] = $this->__array2arrays($masterid, $array, $num+1, $row, $path);
			} else {
				// set masterid if element has no children
				$r[$array[$num]]['id'] = $masterid;
			}
		}
		return $r;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function insert($tree, $parent = '') {

		$errors = array();
		if(is_array($tree) && count($tree) > 0) {
			foreach($tree as $k => $v) {

				if(isset($v['label'])) {
					if($v['label'] !== '') {
						$o = array();

						## TODO handle duplicate id

						$o['id'] = $v['id'];
						$o['bezeichner_kurz'] = 'UNDEFINED';
						$o['parent_id'] = $parent;
						$o['tabelle'] = null;
						$o['merkmal_kurz'] = 'NAME';
						$o['wert'] = $v['label'];

						$error = $this->db->insert($this->controller->settings['query']['table'], $o);
						if($error === '') {
							if(isset($v['children'])) {
								$error = $this->insert($v['children'], $v['id']);
								if(is_array($error) && count($error) > 0) {
									$errors = array_merge($error, $errors);
								}
							}
						} else {
							$errors[] = $error;
						}
					} else {
						$errors[] = 'Error importing '. $v['row'].' - Missing label';
					}
				} else {
					if(isset($v['children'])) {
						$error = $this->insert($v['children'], $v['id']);
						if(is_array($error) && count($error) > 0) {
							$errors = array_merge($error, $errors);
						}
					}
				}
			}
		}
		return $errors;
	}

}
?>
