<?php
/**
 * standort_settings_import_step2
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_import_step2
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
		$this->db          = $controller->db;
		$this->file        = $controller->file;
		$this->response    = $controller->response;
		$this->profilesdir = $controller->profilesdir;
		$this->datadir     = $this->controller->datadir;
		$this->path        = $this->controller->path;
		$this->ini         = $this->profilesdir.'standort.import.ini';
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

		$errors = '';
		if(isset($response->error)) {
			$errors = '<div class="msgBox alert alert-danger">'.$response->error.'</div>';
		}

		$t = $this->response->html->template($this->tpldir.'standort.settings.import.step2.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);

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
		$form = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {

			$cache   = array();
			$idcheck = array();

			$params = $this->response->html->request()->get('place');

				// handle config file
				$this->file->make_ini($this->ini, array('place' => $params));

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

				$path = array();
				$ids  = array();
				// build checker
				$this->controller->standort->delimiter = $delim;
				$check = $this->controller->standort->options();
				if(is_array($check)) {
					foreach($check as $c) {
						$path[] = $c['path'];
						$ids[]  = $c['id'];
					}
				}

				// store import infos
				$cache['params'] = $params;

				$i = 0;

$error = '';


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

					// assemble
					$cache[$i]['row'] = $k;
					if($idcol !== '' && $c[$idcol] !== '' && !in_array($c[$idcol], $idcheck)) {
						$cache[$i]['id'] = $c[$idcol];
					}
					elseif(in_array($c[$idcol], $idcheck)) {
						$error .= sprintf($this->lang['error_import_duplicate_id'], $c[$idcol], $k).'<br>';
					} else {
						$cache[$i]['id'] = uniqid('s');
					}
					$cache[$i]['path'] = $p;
					$cache[$i]['depth'] = $x;

					// handle path found in db -> warning only
					if(in_array($p, $path)) {
						$cache['errors'][] = sprintf($this->lang['warning_duplicate_path'], $k);
						unset($cache[$i]);
						continue;
					}

					// id check
					if($idcol !== '') {
						$idcheck[$k] = $cache[$i]['id'];
					}
					$i++;
				}

				// ERROR Control
				if(count($idcheck) > 0) {
					// check duplicate ids (import file)
					$errors = array_unique(array_diff_assoc( $idcheck, array_unique($idcheck)));
					if(is_array($errors) && count($errors) > 0) {
						$msg = '';
						foreach($errors as $row => $id) {
							$msg .= sprintf($this->lang['error_import_duplicate_id'], $id, $row).'<br>';
						}
						$error .= $msg;
					}
					// check duplicate ids (database)
					foreach($idcheck as $row => $id) {
						if(in_array($id, $ids)) {
							$error .= sprintf($this->lang['error_duplicate_id'], $id, $row).'<br>';
						}
					}
				}

				// nothing wrong?
				if($error === '') {
					// handle cache
					$this->file->make_ini($this->datadir.'standort.cache.ini',$cache);
					$msg = $this->lang['msg_step2_success'];
					$this->response->redirect(
						$this->response->get_url($this->actions_name, 'step3', $this->message_param, $msg)
					);
				} else {
					$response->error = $error;
				}

			} else {
				// raise error message
				$response->error = 'Error: '.$content;
			}

		}		
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param]['error'] = implode('<br>', $form->get_errors());
		}
		return $response;
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
		$form     = $response->get_form($this->actions_name, 'step2');
		$path     = $this->path;
		$ini      = $this->file->get_ini($this->ini);

		$d = array();
		if($this->file->exists($path)) {
			$d['place_sheet']['label']                     = $this->lang['label_sheet'];
			$d['place_sheet']['css']                       = 'autosize';
			$d['place_sheet']['style']                     = 'float:right; clear:both;';
			$d['place_sheet']['required']                  = true;
			$d['place_sheet']['validate']['regex']         = '/^[0-9]+$/i';
			$d['place_sheet']['validate']['errormsg']      = sprintf($this->lang['error_NaN'], $this->lang['label_sheet']);
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
			$d['place_offset']['validate']['errormsg']      = sprintf($this->lang['error_NaN'], $this->lang['label_offset']);
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
			$d['place_column']['validate']['errormsg']      = sprintf($this->lang['error_misspelled'], $this->lang['label_columns'], '[A-Z,]');
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

			$d['place_id']['label']                         = $this->lang['label_idcolumn'];
			$d['place_id']['css']                           = 'autosize';
			$d['place_id']['style']                         = 'float:right; clear:both;';
			$d['place_id']['validate']['regex']             = '/^[A-Z]+$/';
			$d['place_id']['validate']['errormsg']          = sprintf($this->lang['error_misspelled'], $this->lang['label_idcolumn'], '[A-Z]');
			$d['place_id']['object']['type']                = 'htmlobject_input';
			$d['place_id']['object']['attrib']['name']      = 'place[id]';
			$d['place_id']['object']['attrib']['maxlength'] = 1;
			$d['place_id']['object']['attrib']['title']     = $this->lang['title_idcolumn'];
			if(isset($ini['place']['id'])) {
				$d['place_id']['object']['attrib']['value'] = $ini['place']['id'];
			}

			$download = $this->response->html->button();
			$download->name  = $this->actions_name;
			$download->css   = 'form-control btn btn-default btn-inline submit';
			$download->style = 'margin-top: 4px; margin-bottom: 4px;';
			$download->value = 'download';
			$download->label = 'download';
			$download->type  = 'submit';

			$d['download']['static'] = true;
			$d['download']['object'] = $download;

		} else {
			$d['cache_link']     = '';
			$d['place_sheet']    = '';
			$d['place_offset']   = '';
			$d['place_column']   = '';
			$d['place_delim']    = '';
			$d['place_id']       = '';
			$d['cancel']         = '';
			$d['download']       = '';
			$form->add('','submit');
		}

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
