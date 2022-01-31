<?php
/**
 * standort_settings_import_step3
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_import_step3
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
		$response = $this->parse();

		$params  = $this->lang['label_sheet'].': '.$this->params['sheet'].'<br>';
		$params .= $this->lang['label_offset'].': '.$this->params['offset'].'<br>';
		$params .= $this->lang['label_columns'].': '.$this->params['column'].'<br>';
		$params .= $this->lang['label_delimiter'].': '.$this->params['delimiter'].'<br>';
		if($this->params['id'] !== '') {
			$params .= $this->lang['label_idcolumn'].': '.$this->params['id'].'<br>';
		}

		$errors = '';
		if(isset($this->errors)) {
			$str  = '<br>';
			$str .= '<a data-toggle="collapse" class="btn btn-warning" href="#warningbox" aria-expanded="false">Warnings ('.count($this->errors).')</a>';
			$str .= '<div id="warningbox" class="collapse" style="clear:both;margin-top:15px;">';
			$str .= '<div class="alert alert-warning">';
			$str .= implode('<br>', $this->errors);
			$str .= '</div>';
			$str .= '</div>';
			$errors = $str;
		}

		$t = $this->response->html->template($this->tpldir.'standort.settings.import.step3.html');
		$t->add($this->response->html->thisfile,'thisfile');
		$t->add($response->table, 'table');
		$t->add($response->form, 'form');
		$t->add($params, 'params');
		$t->add($errors, 'errors');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * parse
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function parse() {

		$response = $this->get_response();

		// cache

		$cache = $this->cache;

		// Output table
		if(is_array($cache)) {
			$table = $this->response->html->tablebuilder('bestand_select', $response->get_array());
			$table->form_action     = $response->html->thisfile;
			$table->sort            = 'row';
			$table->order           = 'ASC';
			$table->limit           = 50;
			$table->offset          = 0;
			$table->css             = 'htmlobject_table table table-bordered';
			$table->id              = 'bestand_select';
			$table->sort_form       = true;
			$table->sort_link       = false;
			$table->sort_buttons    = array('sort','order','limit','offset', 'refresh');
			$table->autosort        = true;

### TODO ident -> cache array key is not row anymore

			#$table->identifier      = 'key';
			#$table->identifier_name = 'ident';
			#$table->actions_name    = 'xlxs_action';
			#$table->actions = array(
			#						array('remove' => 'remove')
			#					);

			$table->max = count($cache);

			$head = array();
			$head['key']['title'] = 'key';
			$head['key']['hidden'] = true;
			$head['key']['sortable'] = false;

			$head['row']['title'] = 'Row';
			$head['row']['style'] = 'width:75px;';
			$head['row']['sortable'] = true;

			$head['depth']['title'] = 'Depth';
			$head['depth']['style'] = 'width:40px;';
			$head['depth']['sortable'] = true;

			$head['id']['title'] = 'ID';
			$head['id']['sortable'] = true;
			$head['id']['style'] = 'width:auto;white-space:nowrap;';

			$head['path']['title'] = 'Path';
			$head['path']['sortable'] = true;

			$body = array();

			$table->init();
			$count = count($cache);
			$limit = $table->limit + $table->offset;
			if($table->limit === '0' || $count < $table->limit) {
				$limit = $count;
			}

			for($i = $table->offset; $i < $limit; $i++) {
				if(isset($cache[$i])) {
					$d = array();
					if(isset($this->params['delimiter']) && $this->params['delimiter'] !== '') {
						$cache[$i]['path'] = str_replace($this->params['delimiter'], '<span class="importdelimiter">'.$this->params['delimiter'].'</span>', $cache[$i]['path']);
					}
					$d = $cache[$i];
					$d['key'] = $i;
					$body[] = $d;
				} else {
					break;
				}
			}

			$table->head = $head;
			$table->body = $body;

			$import = $this->response->html->button();
			$import->name    = $this->actions_name;
			$import->css     = 'form-control btn btn-default btn-inline submit';
			$import->style   = 'width: 150px;margin-top: 3px;';
			$import->label   = 'Import';
			$import->value   = 'step4';
			$import->type    = 'submit';
			$import->handler = 'onclick="phppublisher.wait();"';

			$table->add_bottomrow('<div style="text-align:center;">'.$import->get_string().'</div>');

			$response->table = $table;
			return $response;
		} else {
			$error = $content;
		}

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
		$form     = $response->get_form($this->actions_name, 'step3');

		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}


	//--------------------------------------------
	/**
	 * Import 1. step
	 *
	 * @access public
	 * @return [string|empty]
	 */
	//--------------------------------------------
	function __import($cache) {

		// sort $cache by depth - shortest last
		$cache = $this->__sort( $cache, 'depth','DESC' );

		$standort = $this->controller->standort;
		$standort->delimiter = $this->params['delimiter'];
		$this->__check = $standort->path();

		$errors = array();
		$data   = array();
		if(is_array($cache)) {
			$i = 0;
			foreach($cache as $k => $r) {
				$error = '';
				$str   = $r['path'];
				if($str !== '') {
					if(isset($this->params['id']) && $this->params['id'] !== '') {
						if(array_key_exists($r['id'], $this->__check)) {
							$error = 'Error: Row '.$r['row'].' ID '.$r['id'].' already in use';
						}
					} else {
						if(in_array($str, $this->__check)) {
							$error = 'Error: Row '.$r['row'].' Path '.$str.' already in use' ;
						}
					}
					if($error === '') {
						$parents = explode($standort->delimiter, str_replace("\t",'',$str));
						$return  = $this->__array2arrays( $r['id'], $parents, 0, $r['row']);
						$data    = array_replace_recursive ($data, $return);
					} else {
						$errors[] = $error;
					}
				}
				$i++;
			}
		}

		if(count($errors) < 1) {
			$errors = $this->insert($data);
		}

### TODO unset $cache without errors -> write new cache file

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
			$r[$array[$num]]['id']    = uniqid('i');
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


#$this->response->html->help($tree);

		$errors = array();
		if(is_array($tree) && count($tree) > 0) {
			foreach($tree as $k => $v) {

				if(isset($v['label'])) {
					if($v['label'] !== '') {
						$o = array();
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
#var_dump($errors);

		return $errors;
	}

}
?>
