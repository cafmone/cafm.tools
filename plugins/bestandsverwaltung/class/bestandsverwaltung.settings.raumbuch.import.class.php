<?php
/**
 * bestandsverwaltung_settings_raumbuch_import
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
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2018, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_settings_raumbuch_import
{
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'label_sheet' => 'Sheet',
	'label_offset' => '',
	'label_columns' => '',
	'label_delimiter' => '',
	'label_idcolumn' => '',
	'title_sheet' => 'Sheet to parse',
);

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
		$this->settings    = $this->profilesdir.'bestandsverwaltung.standort.import.ini';
		$this->datadir     = $this->profilesdir.'import/standort/';
		$this->path        = $this->datadir.'standort.xlsx';
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

		$action = $this->response->html->request()->get($this->actions_name.'[import]');
		if(is_array($action)) {
			$this->action = key($action);
		} else {
			$this->action = $action;
		}

		if($this->response->cancel()) {
			if($this->action === 'remove') {
				// unset params
				unset($_REQUEST[$this->actions_name]);
				$this->action = 'select';
			}
		}

		$t = '';
		switch ($this->action) {
			case '':
			case 'select':
				$t = $this->select();
			break;
			case 'remove':
				$t = $this->remove();
			break;
			case 'parse':
				$t = $this->parse();
			break;
			case 'download':
				$t = $this->download();
			break;
		}

		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {

		$response = $this->get_response();
		if($response->form->get_errors()) {
			$response->error = implode('<br>', $response->form->get_errors());
		}

		$upload   = '';
		if(!isset($response->error) && !$this->file->exists($this->path)) {
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.upload.class.php');
			$dres = $this->response->response();
			$dres->id = 'upload_standort';
			$commander = new phpcommander_upload($this->datadir, $dres, $this->file);
			$commander->actions_name = 'update_upload';
			$commander->message_param = 'upload_msg';
			$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
			$commander->allow_replace = true;
			$commander->allow_create = true;
			$commander->accept = '.xlsx';
			$commander->filename = 'standort.xlsx';
			$upload = $commander->get_template();
			if(isset($_REQUEST[$commander->message_param])) {
				$msg = $_REQUEST[$commander->message_param];
				unset($_REQUEST[$commander->message_param]);
				$this->response->redirect($this->response->get_url($this->actions_name, 'import', $this->message_param, $msg));
			}
		} 
		else if(isset($response->error)) {
			$_REQUEST[$this->message_param]['error'] = $response->error;
		} else {
			// everything fine? load parse
			if($response->submit()) {
				// save settings
				$params = $this->file->get_ini($this->settings);
				$params['place'] = $this->response->html->request()->get('place');
				$this->file->make_ini($this->settings, $params);
				// remove cache
				if($this->file->exists($this->datadir.'standort.cache.ini')) {
					$this->file->remove($this->datadir.'standort.cache.ini');
				}
				return $this->parse();
			}
		}

		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.import.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);
		$t->add($upload, 'upload');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$form = $this->response->get_form($this->actions_name.'[import][remove]', 'remove');
		if(!$form->get_errors() && $this->response->submit()) {
			$error = $this->file->remove($this->path);
			if($error === '') {
				if($this->file->exists($this->datadir.'standort.cache.ini')) {
					$error = $this->file->remove($this->datadir.'standort.cache.ini');
					if($error === '') {
						$msg = 'sucess';
					} else {
						$msg = $error;
					}
				} else {
					$msg = 'sucess';
				}
			} else {
				$msg = $error;
			}
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'import', $this->message_param, $msg)
			);
		}
		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.settings.raumbuch.import.remove.html');
		$t->add($this->response->html->thisfile,'thisfile');
		$t->add($form);
		$t->add('Realy remove standort.xlsx?','confirm');
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

		$response = $this->response->response();
		$response->id = 'import_parse';
		$response->add($this->actions_name.'[import][parse]', 'parse');

		// cache
		if(!$this->file->exists($this->datadir.'standort.cache.ini')) {

			$cache   = array();
			$idcheck = array();

			$params = $this->response->html->request()->get('place');
			if(!isset($params['delimiter']) || $params['delimiter'] === '') {
				$params['delimiter'] = ' / ';
			}

			$sheet  = $params['sheet'];
			$offset = $params['offset'];
			$cols   = explode(',',$params['column']);
			$idcol  = $params['id'];
			$delim  = $params['delimiter'];

			require_once(CLASSDIR.'lib/file/file.xlsx.class.php');
			$xlsx = new file_xlsx($this->file, $this->response->html);
			$xlsx->sheet = $sheet;
			$xlsx->row   = $offset;
			$xlsx->cols  = $cols;
			if($idcol !== '') {
				array_unshift($xlsx->cols, $idcol);
			}

			$content = $xlsx->parse($this->path);
			if(is_array($content)) {
				// store import infos
				$cache['params'] = $params;

				$i = 0;
				foreach($content as $k => $c) {
					$p = '';
					if(count($cols) > 1) {
						$x = 0;
						foreach($cols as $col) {
							if(isset($c[$col]) && $c[$col] !== '') {
								if($x === 0) {
									$p = $c[$col];
								} else {
									$p .= $delim.$c[$col];
								}
								$x++;
							} else {
								break;
							}
						}
					} else {
						$p = $c[$cols[key($cols)]];
						$x = count(explode($delim, $p));
					}
					$cache[$i]['row'] = $k;
					if($idcol !== '' && $c[$idcol] !== '') {
						$cache[$i]['id'] = $c[$idcol];
					} else {
						$cache[$i]['id'] = uniqid('s');
					}
					$cache[$i]['path'] = $p;
					$cache[$i]['depth'] = $x;

					// id check
					if($idcol !== '') {
						$idcheck[$k] = $cache[$i]['id'];
					}

					$i++;
				}

				// check for duplicate ids
				if(count($idcheck) > 0) {
					$errors = array_unique(array_diff_assoc( $idcheck, array_unique($idcheck)));
					if(is_array($errors) && count($errors) > 0) {
						$msg = '';
						foreach($errors as $row => $id) {
							$msg .= 'Error: Duplicate ID '.$id.' in row '.$row.'<br>';
						}
						// redirect
						$response->redirect(
							$response->get_url(
								$this->actions_name.'[import]', 'select', $this->message_param, $msg
							)
						);
					}
				}

				$this->file->make_ini($this->datadir.'standort.cache.ini',$cache);

				// remove params from cache output
				$this->params = $cache['params'];
				unset($cache['params']);

			} else {
				// raise error message
				$_REQUEST[$this->message_param] = 'Error: '.$content;
			}
		} else {
			$cache = $this->file->get_ini($this->datadir.'standort.cache.ini');

			// get import infos
			$this->params = $cache['params'];
			unset($cache['params']);

			// handle remove
			$action = $this->response->html->request()->get('xlxs_action');
			if(is_array($action)) {
				$action = key($action);
			}
			$idents = $this->response->html->request()->get('ident');

			// Remove
			if($action === 'remove' && is_array($idents)) {
				$msg = '';
				foreach($idents as $ident) {
					if(isset($cache[$ident])) {
						unset($cache[$ident]);
						$msg .= 'removed row '.$ident.'<br>';
					}
				}
				if($msg !== '') {
					// add params
					$cache['params'] = $this->params;
					$this->file->make_ini($this->datadir.'standort.cache.ini',$cache);
					if(strlen($msg) > 200) {
						$msg = substr($msg, 0, strrpos(substr($msg, 0, 200), ' ')).' ...';
					}
					$_REQUEST[$this->message_param] = $msg;
				}
			}
			// Real Import
			else if($action === 'import') {
				$error = $this->__import($cache);
				if($error !== '') {
					if(strlen($error) > 200) {
						$msg = substr($error, 0, strrpos(substr($error, 0, 200), ' ')).' ...';
					}
				} else {
					$msg = 'Success';
				}
				$response->redirect($response->get_url($this->actions_name.'[import][parse]', 'parse', $this->message_param, $msg));
			}
		}

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

			$params  = $this->lang['label_sheet'].': '.$this->params['sheet'].'<br>';
			$params .= $this->lang['label_columns'].': '.$this->params['column'].'<br>';
			$params .= $this->lang['label_offset'].': '.$this->params['offset'].'<br>';
			$params .= $this->lang['label_delimiter'].': '.$this->params['delimiter'].'<br>';
			if($this->params['id'] !== '') {
				$params .= $this->lang['label_idcolumn'].': '.$this->params['id'].'<br>';
			}
			$table->add_headrow($params);

			$import = $this->response->html->input();
			$import->name  = 'xlxs_action[import]';
			$import->css   = 'form-control btn btn-default btn-inline submit';
			$import->style = 'width: 150px;margin-top: 3px;';
			$import->value = 'Import';
			$import->type  = 'submit';

			$table->add_bottomrow('<div style="text-align:center;">'.$import->get_string().'</div>');

			return $table;
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
		$form     = $response->get_form($this->actions_name.'[import]', 'select');
		$path     = $this->path;
		$ini      = $this->file->get_ini($this->settings);

		$d = array();
		if($this->file->exists($path)) {
			$d['place_sheet']['label']                     = $this->lang['label_sheet'];
			$d['place_sheet']['css']                       = 'autosize';
			$d['place_sheet']['style']                     = 'float:right; clear:both;';
			$d['place_sheet']['required']                  = true;
			$d['place_sheet']['validate']['regex']         = '/^[0-9]+$/i';
			$d['place_sheet']['validate']['errormsg']      = sprintf('%s must be a number', $this->lang['label_sheet']);
			$d['place_sheet']['object']['type']            = 'htmlobject_input';
			$d['place_sheet']['object']['attrib']['name']  = 'place[sheet]';
			$d['place_sheet']['object']['attrib']['title'] = $this->lang['title_sheet'];
			if(isset($ini['place']['sheet'])) {
				$d['place_sheet']['object']['attrib']['value'] = $ini['place']['sheet'];
			}

			$d['place_offset']['label']                     = $this->lang['label_offset'];
			$d['place_offset']['css']                       = 'autosize';
			$d['place_offset']['style']                     = 'float:right; clear:both;';
			$d['place_offset']['required']                  = true;
			$d['place_offset']['validate']['regex']         = '/^[0-9]+$/i';
			$d['place_offset']['validate']['errormsg']      = sprintf('%s must be a number', $this->lang['label_offset']);
			$d['place_offset']['object']['type']            = 'htmlobject_input';
			$d['place_offset']['object']['attrib']['name']  = 'place[offset]';
			$d['place_offset']['object']['attrib']['title'] = $this->lang['title_offset'];
			if(isset($ini['place']['offset'])) {
				$d['place_offset']['object']['attrib']['value'] = $ini['place']['offset'];
			}

			$d['place_column']['label']                     = $this->lang['label_columns'];
			$d['place_column']['css']                       = 'autosize';
			$d['place_column']['style']                     = 'float:right; clear:both;';
			$d['place_column']['required']                  = true;
			$d['place_column']['validate']['regex']         = '/^[A-Z,]+$/';
			$d['place_column']['validate']['errormsg']      = sprintf('%s must be A-Z or , only', $this->lang['label_columns']);
			$d['place_column']['object']['type']            = 'htmlobject_input';
			$d['place_column']['object']['attrib']['name']  = 'place[column]';
			$d['place_column']['object']['attrib']['value'] = '';
			$d['place_column']['object']['attrib']['title'] = $this->lang['title_columns'];
			if(isset($ini['place']['column'])) {
				$d['place_column']['object']['attrib']['value'] = $ini['place']['column'];
			}

			$d['place_delim']['label']                     = $this->lang['label_delimiter'];
			$d['place_delim']['css']                       = 'autosize';
			$d['place_delim']['style']                     = 'float:right; clear:both;';
			$d['place_delim']['object']['type']            = 'htmlobject_input';
			$d['place_delim']['object']['attrib']['name']  = 'place[delimiter]';
			$d['place_delim']['object']['attrib']['value'] = '';
			$d['place_delim']['object']['attrib']['title'] = $this->lang['title_delimiter'];
			if(isset($ini['place']['delimiter'])) {
				$d['place_delim']['object']['attrib']['value'] = $ini['place']['delimiter'];
			}

			$d['place_id']['label']                     = $this->lang['label_idcolumn'];
			$d['place_id']['css']                       = 'autosize';
			$d['place_id']['style']                     = 'float:right; clear:both;';
			$d['place_id']['object']['type']            = 'htmlobject_input';
			$d['place_id']['object']['attrib']['name']  = 'place[id]';
			$d['place_id']['object']['attrib']['value'] = '';
			$d['place_id']['object']['attrib']['title'] = $this->lang['title_idcolumn'];
			if(isset($ini['place']['id'])) {
				$d['place_id']['object']['attrib']['value'] = $ini['place']['id'];
			}

			$remove = $this->response->html->input();
			$remove->name  = $this->actions_name.'[import][remove]';
			$remove->css   = 'form-control btn btn-default btn-inline submit';
			$remove->style = 'margin-top: 3px;';
			$remove->value = 'remove';
			$remove->type  = 'submit';
			$d['cancel']['object'] = $remove;

			$download = $this->response->html->button();
			$download->name  = $this->actions_name.'[import][download]';
			$download->css   = 'form-control btn btn-default btn-inline submit';
			$download->style = 'margin-top: 3px;';
			$download->value = 'download';
			$download->label = 'download';
			$download->type  = 'submit';
			$d['download']['object'] = $download;

			if($this->file->exists($this->datadir.'standort.cache.ini')) {
				$link = $this->response->html->a();
				$link->name    = $this->actions_name.'[import][parse]';
				$link->href    = $this->response->get_url($this->actions_name.'[import][parse]', 'parse');
				$link->label   = 'Load from cache';
				$link->handler = 'onclick="phppublisher.wait();"';
				$d['cache_link']['object'] = $link;
			} else {
				$d['cache_link'] = '';
			}

			$d['upload_display'] = 'none';
		} else {
			$d['cache_link']     = '';
			$d['place_sheet']    = '';
			$d['place_offset']   = '';
			$d['place_column']   = '';
			$d['place_delim']    = '';
			$d['place_id']       = '';
			$d['cancel']         = '';
			$d['download']       = '';
			$d['upload_display'] = 'block';
			$form->add('','submit');
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function download() {
		require_once(CLASSDIR.'/lib/file/file.mime.class.php');
		$path = $this->path;
		$file = $this->file->get_fileinfo($path);
		$mime = detect_mime($file['path']);

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		header("Content-disposition: attachment; filename=".$file['name']);
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
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

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/raumbuch.class.php');
		$raumbuch = new raumbuch($this->db);
		$raumbuch->delimiter = $this->params['delimiter'];
		$this->__check = $raumbuch->path();

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
						$parents = explode($raumbuch->delimiter, str_replace("\t",'',$str));
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

						$error = $this->db->insert('raumbuch', $o);
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
