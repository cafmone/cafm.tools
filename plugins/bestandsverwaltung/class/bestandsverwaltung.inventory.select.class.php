<?php
/**
 * bestandsverwaltung_inventory_select
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
 *  Copyright (c) 2015-2022, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_inventory_select
{
/**
* translation
* @access public
* @var string
*/
var $lang = array();
/**
* is output filtered?
* @access private
* @var bool
*/
var $__filtered = false;
/**
* current filters
* @access private
* @var array
*/
var $__filters;

/**
* todo todos
* @access private
* @var array
*/
var $__todos;
/**
* todo interval
* @access private
* @var array
*/
var $__interval;
/**
* todo attribs
* @access private
* @var array
*/
var $__attribs;

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
		$this->user       = $controller->user->get();
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->settings   = $controller->settings;
		$this->plugins    = $this->file->get_ini(PROFILESDIR.'/plugins.ini');

		// Validate user
		$groups = array();
		if(isset($this->settings['settings']['supervisor'])) {
			$groups[] = $this->settings['settings']['supervisor']; 
		}
		$this->user['is_valid'] = $this->controller->user->is_valid($groups);

		// Filter
		$this->filter = $this->response->html->request()->get('filter',true);
		if($this->filter !== '') {
			$this->response->add('filter',$this->filter);
		}
		
		// Custom Filters
		if(is_array($this->settings['filter'])) {
			$this->filters = array();
			foreach($this->settings['filter'] as $k => $f) {
				$tmp = explode('::', $f);
				if(isset($tmp[1])) {
					$this->filters[$tmp[1]]['table'] = $tmp[0];
					$this->filters[$tmp[1]]['key']   = $tmp[1];
				}
			}
		}

		// Export
		$export = $this->response->html->request()->get('export');
		if($export !== '') {
			unset($export['table']);
			$this->response->add('export',$export);
		}

		// Printout
		$printout = $this->response->html->request()->get('printout');
		if($export !== '') {
			$this->response->add('printout',$printout);
		}

		// bezeichner
		$this->bezeichner = array();
		$sql  = 'SELECT ';
		$sql .= '`b`.`bezeichner_kurz` as bezeichner, ';
		$sql .= '`b`.`bezeichner_lang` as label, ';
		$sql .= '`bs`.`type` as type ';
		$sql .= 'FROM bezeichner AS b ';
		$sql .= 'LEFT JOIN bezeichner_settings AS bs ON (b.bezeichner_kurz=bs.bezeichner_kurz) ';
		$sql .= 'GROUP BY bezeichner, label, type ';
		$bezeichner = $this->db->handler()->query($sql);
		if(is_array($bezeichner)) {
			foreach($bezeichner as $v) {
				$lb[$v['bezeichner']]['label'] = $v['label'];
				$lb[$v['bezeichner']]['type']  = $v['type'];
			}
			$this->bezeichner = $lb;
		}

		// handle CAFM.ONE connected 
		if(in_array('standort', $this->plugins)) {
			require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
			$this->raumbuch = new standort($this->db, $this->file);
			$this->raumbuch->options = $this->raumbuch->options();
		}

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		// handle prozess check bestand_index
		$check = $this->db->select('bestand_index', 'tabelle_kurz', array('tabelle_kurz' => 'prozess'));
		if(is_array($check)) {
			$this->prozesses = $this->db->select('bestand_prozess', array('merkmal_lang','merkmal_kurz','datentyp','bezeichner_kurz'), 'bezeichner_kurz IS NOT NULL', '`row`');
		} else {
			$this->prozesses = '';
		}

		// handle CAFM.ONE connected 
		if(in_array('cafm.one', $this->plugins)) {
			require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
			$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->controller->user);
		}

		$url  = $this->response->html->thisfile;
		$url .= '?index_action=plugin';
		$url .= '&index_action_plugin=bestandsverwaltung';
		$url .= '&'.$this->controller->controller->actions_name.'=download';
		$url .= '&path=/devices/';
		$this->href_download  = $url;

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/bestandsverwaltung.class.php');
		$this->bestandsverwaltung = new bestandsverwaltung($this->db);
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
		$response = $this->select();

		$t = $response->html->template($this->tpldir.'bestandsverwaltung.inventory.select.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($response->table, 'table');
		$t->add($response->form);
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');

		#$t->add('Filter','title_filter');
		#$t->add('Print','title_print');
		#$t->add('Export','title_export');
		
		// translation
		$t->add($this->lang);

		// download link for udate popunder
		$t->add($this->href_download,'href_download');

		// handle filtered state
		if(isset($this->__filtered) && $this->__filtered === true) {
			$t->add('btn-success','filtered');
			if(isset($this->__filters) && is_array($this->__filters)) {
				$filters = implode('<br>', $this->__filters);
				$t->add('<div style="margin: 0 20px 20px 0;">'.$filters.'</div>','active_filters');
			} else {
				$t->add('','active_filters');
			}
		} else {
			$t->add('btn-default','filtered');
			$t->add('','active_filters');
		}

		// handle jsparams string
		if(isset($response->jsparams)) {
			$t->add($response->jsparams,'jsparams');
			$t->add('none','filterdisplay');
		} else {
			$t->add('block','filterdisplay');
			$t->add('var jsparams = [];','jsparams');
		}

		// assemble boxes - add not filter
		$box = $t->get_elements('filter_date');
		$not = $t->get_elements('filter_order_date');
		$box->add($not);
		$box->add('<div class="floatbreaker">&#160;</div>');
		$t->remove('filter_order_date');

 		// bezeichner not
		$box = $t->get_elements('filter_bezeichner');
		$not = $t->get_elements('filter_bezeichner_not');
		$box->add($not);
		$t->remove('filter_bezeichner_not');

		// handle custom filter tab
		$custom_empty = $t->get_elements('cfilter_0');
		if(
			isset($this->filters) 
			&& is_array($this->filters) 
			&& !isset($custom_empty)
		) {
			foreach($this->filters as $f) {
				$key = $f['key'];
				$box = $t->get_elements('cfilter_'.$key);
				$not = $t->get_elements('cfilter_not_'.$key);
				$box->add($not);
				$box->add('<div class="floatbreaker">&#160;</div>');
				$t->remove('cfilter_not_'.$key);
			}
			$t->add('block','css_tab_filter_custom');
		} else {
			$t->add('none','css_tab_filter_custom');
		}

		// handle prozess tab
		$prozess_empty = $t->get_elements('prozess_0');
 		if(is_array($this->prozesses) && !isset($prozess_empty)) {
			foreach( $this->prozesses as $k => $r ) {
				$box = $t->get_elements('prozess_'.$r['merkmal_kurz']);
				$not = $t->get_elements('prozess_not_'.$r['merkmal_kurz']);
				$box->add($not);
				$t->remove('prozess_not_'.$r['merkmal_kurz']);
			}
			$t->add('block','css_tab_filter_prozess');
		} else {
			$t->add('none','css_tab_filter_prozess');
		}

		// handle todos tab
		$todos_empty = $t->get_elements('filter_prefix');
 		if($todos_empty !== '') {
			$t->add('block','css_tab_filter_todos');
		} else {
			$t->add('none','css_tab_filter_todos');
		}

		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('prozess_' => 'filter_prozess'));
		$t->group_elements(array('cfilter_' => 'custom'));
		$t->group_elements(array('tfilter_' => 'filter_todo_attribs'));
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
		$settings = $this->settings;

		$body = array();
		$ids  = $this->get_ids();

		$table = $response->html->tablebuilder( 'bestand_select', $response->get_array() );
		## TODO handle first impact
		if(isset($this->filter['group']) && $this->filter['group'] !== 'single') {
			$table->sort  = 'bezeichner';
			$table->order = 'ASC';
		} else {
			$table->sort  = 'date';
			$table->order = 'DESC';
		}
		$table->limit           = 50;
		$table->offset          = 0;
		$table->max             = count($ids);
		$table->css             = 'htmlobject_table table table-bordered';
		$table->id              = 'bestand_select';
		$table->sort_form       = true;
		$table->sort_buttons    = array('sort','order','limit','offset');
		$table->autosort        = false;
		$table->identifier      = 'id';
		$table->identifier_name = $this->identifier_name;
		$table->actions_name    = $this->actions_name;

		if($this->user['is_valid']) {
			$table->actions = array(
									array('identifier' => $this->lang['button_identifier']),
									array('remove' => $this->lang['button_remove']),
									array('qrcode' => $this->lang['button_qrcode'], 'button' => true),
									array('process' => $this->lang['button_process'])
								);
		} else {
			$table->actions = array(
									array('qrcode'=>'qrcode')
								);
		}

		$table->limit_select = array(
									array("value" => 0,  "text" => 'none'),
									array("value" => 500, "text" => 500),
									array("value" => 200, "text" => 200),
									array("value" => 100, "text" => 100),
									array("value" => 50, "text" => 50),
									array("value" => 20, "text" => 20),
									array("value" => 10, "text" => 10),
								);

		// handle grouping
		if(isset($this->filter['group']) && $this->filter['group'] !== 'single') {
			$table->autosort        = null;
			$table->identifier      = '';
			$table->identifier_name = null;
			$table->actions_name    = null;
			$table->actions         = null;
		}

		$head = array();

		// handle marker filter select
		if(!isset($this->filter['group']) || $this->filter['group'] === '' || $this->filter['group'] === 'single') {
			$os = array('silver','lime','gold','red');
			$options[] = array('','');
			foreach($os as $o) {
				$option = $this->response->html->option();
				$option->value = $o;
				if(isset($this->filter['marker']) && $this->filter['marker'] === $o) {
					$option->selected = true;
				}
				if($o !== 'silver') {
					$option->label = '&#9733';
					$option->title = $o;
					$option->style = 'color:'.$o.'; font-size: 18px;';
				} else {
					$option->label = '&#9734;';
					$option->title = 'silver';
					$option->style = 'font-size: 18px; color:silver;';
				}
				$options[] = $option;
			}

			$marker = $this->response->html->select();
			$marker->style = 'font-size: 16px; color:black;';
			$marker->name = 'filter[marker]';
			$marker->css = 'form-control form-control-sm';
			$marker->add($options,array(0,1));
			$marker->id ='marker';
			$marker->handler ='onchange="phppublisher.wait();this.form.submit();"';

			$head['marker']['title'] = $marker->get_string();
			$head['marker']['sortable'] = false;
			$head['marker']['style'] = 'width: 50px;';
		}

		// handle table head
		if(isset($this->filter['group']) && $this->filter['group'] !== 'single') {
			$check = $this->response->html->request()->get('bestand_select[sort]');
			if($check !== 'bezeichner' && $check !== 'SUMM') {
				unset($_REQUEST['bestand_select']['sort']);
				unset($_REQUEST['bestand_select']['order']);
			} 
			$head['bezeichner']['title'] = $this->lang['label_identifier'];
			$head['bezeichner']['sortable'] = true;
			$head['bezeichner']['hidden'] = true;
			$head['data']['title'] = '&#160;';
			$head['data']['sortable'] = false;
			$head['action']['title'] = '&#160;';
			$head['action']['sortable'] = false;
			$head['SUMM']['title'] = 'Count';
			$head['SUMM']['sortable'] = true;
			$head['SUMM']['hidden'] = true;
		} else {
			$check = $this->response->html->request()->get('bestand_select[sort]');
			if($check === 'SUMM') {
				unset($_REQUEST['bestand_select']['sort']);
			} 
			$head['id']['title'] = 'ID';
			$head['id']['sortable'] = true;
			$head['id']['hidden'] = true;
			$head['bezeichner']['title'] = $this->lang['label_identifier'];
			$head['bezeichner']['sortable'] = true;
			$head['bezeichner']['hidden'] = true;
			$head['date']['title'] = $this->lang['label_date'];
			$head['date']['sortable'] = true;
			$head['date']['hidden'] = true;
			// Custom Filters
			if(isset($this->filters) && is_array($this->filters)) {
				foreach($this->filters as $f) {
					$key = $f['key'];
					$head[$key]['title'] = $key;
					$head[$key]['hidden'] = true;
				}
			}
			$head['data']['title'] = '&#160;';
			$head['data']['sortable'] = false;
			$head['action']['title'] = '&#160;';
			$head['action']['sortable'] = false;
		}

		$tparams = '';
		$params  = $this->response->html->request()->get($table->__id);
		if($params !== '') {
			foreach($params as $k => $v) {
				$tparams .= $this->response->get_params_string(array($table->__id.'['.$k.']' => $v), '&' );
			}
		}


		if(is_array($ids)) {
			// performance
			$href_update    = $response->get_url($this->actions_name, 'update');
			$href_qrcode    = $response->get_url($this->actions_name, 'qrcode');
			$href_prozess   = $response->get_url($this->actions_name, 'process');
			#$href_changelog = $response->get_url($this->actions_name, 'changelog');
			$href_copy      = $response->get_url($this->actions_name, 'copy');
			$href_download  = $this->href_download;
			// handle ungroup filter
			$href_ungroup = '';
			if(isset($this->filter['group']) && $this->filter['group'] !== 'single') {
				// handle filter[not][bezeichner]
				$ar = $response->get_array();
				unset($ar['filter']['bezeichner']);
				unset($ar['filter']['group']);
				unset($ar['filter']['not']['bezeichner']);
				$href_ungroup = $this->response->get_params_string($ar).'&filter[group]=single';
			}

			// sort ids
			$table->init();
			$sort = $table->sort;
			if($this->response->html->request()->get($table->__id.'[sort]') !== '') {
				$sort = $this->response->html->request()->get($table->__id.'[sort]');
			}
			$ids = $this->__sort($ids, $sort, $table->order);
			$count = count($ids);
			$limit = $table->limit + $table->offset;
			if($table->limit === '0' || $count < $table->limit) {
				$limit = $count;
			}

			// handle printout and export
			if($response->html->request()->get('doexport') !== '' && is_array($ids)) {
				$this->__export($ids);
			}
			else if($response->html->request()->get('doprintout') !== '' && is_array($ids)) {
				$a          = $response->html->a();
				$a->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).$tparams;
				$a->label   = '<span class="icon icon-home"></span>';
				$a->title   = $this->lang['button_title_back'];
				$a->css     = 'btn btn-default noprint';
				$a->handler = 'onclick="phppublisher.wait();"';
				$response->table  = '<div id="linksbox" class="noprint" style="z-index:2;">'.$a->get_string().'&#160;<a title="'.$this->lang['button_title_print'].'" class="btn btn-default" href="javascript:print();"><span class="icon icon-print"></a></div>';
				$response->table .= '<script type="text/javascript">$( document ).ready(function() { details.open(); });</Script>';
				$response->jsparams = $this->__printout($ids);
				// return to not display table
				return $response;
			}

			for($i = $table->offset; $i < $limit; $i++) {
				if(isset($ids[$i])) {
					$id = $ids[$i];
					$bezeichner = $id['bezeichner'];
					if(array_key_exists($id['bezeichner'], $this->bezeichner)) {
						$bezeichner = $this->bezeichner[$id['bezeichner']]['label'].' ('.$id['bezeichner'].')';
					}

					// handle grouping
					if(!isset($id['SUMM']) || $id['SUMM'] === 0) {
						$data  = '<div style="float:left; min-height: 50px;">';
						$data .= $id['id'].'<br>';
						$data .= $bezeichner;
						$data .= '<a class="icon icon-info-sign" id="'.uniqid('h').'" title="'.$id['bezeichner'].'" tabindex="0" data-toggle="popover" data-trigger="focus" role="button" data-placement="right" onclick="bezeichnerhelp.init(this);return false;"></a><br>';
						$data .= date('Y-m-d H:i',$id['date']).'<br><br>';

						// Raumbuch
						if(isset($this->raumbuch)) {
							if(isset($id['RAUMBUCHID'])) {
								if(array_key_exists($this->raumbuch->indexprefix.$id['RAUMBUCHID'], $this->raumbuch->options)) {
									$data .= $this->lang['label_location'].': '.$this->raumbuch->options[$this->raumbuch->indexprefix.$id['RAUMBUCHID']]['path'].' ['.$id['RAUMBUCHID'].']<br>';
								}
							}
						}

						// Custom Filters
						if(isset($this->filters) && is_array($this->filters)) {
							foreach($this->filters as $f) {
								$key = $f['key'];
								if(isset($id[$key]) && $id[$key] !== '') {
									$data .= $key.': '.$id[$key].'<br>';
								}
							}
						}
						// handle files
						$data .= '<br>';

						$path = PROFILESDIR.'/bestand/devices/'.$id['id'];
						$f = $this->file->get_files($path);
						foreach($f as $file) {

							$label = substr($file['name'], 0, 50);
							strlen($label) < strlen($file['name']) ? $label = $label.'...' : null;

							$a = $this->response->html->a();
							$a->href = $href_download.$id['id'].'/'.$file['name'].'&inline=true';
							$a->label = $label;
							$a->target = '_blank';
							$data .= $a->get_string().'<br>';
						}
						$data .= '</div>';
					} else {
						$data  = '<div style="float:left; min-height: 50px;">';
						$data .= $bezeichner.' <a href="#" title="'.$id['bezeichner'].'" class="tooltip">?</a><br>';
						$data .= '</div>';
						$data .= '<div style="float:right;margin: 5px 20px 0 0;">';
						$data .= '<b>'.$id['SUMM'].'</b>';
						$data .= '</div>';
					}
					$data .= '<div style="clear:both;" class="floatbreaker">&#160;</div>';

					$update = '';
					// handle grouping
					if(!isset($id['SUMM']) || $id['SUMM'] === 0) {
						$a          = $response->html->a();
						$a->href    = $href_update.'&id='.$id['id'].$tparams;
						$a->label   = $this->lang['button_update'];
						$a->title   = $this->lang['button_update'];
						$a->css     = 'btn btn-sm btn-default update';
						$a->handler = 'onclick="phppublisher.wait();"';
						#$a->handler = 'onclick="updatepicker.init(\'updatewrapper\',\''.$id['id'].'\'); return false;"';
						$update = $a->get_string();

						if($this->user['is_valid']) {
							$a          = $response->html->a();
							$a->href    = $href_copy.'&id='.$id['id'].$tparams;
							$a->label   = $this->lang['button_copy'];
							$a->title   = $this->lang['button_copy'];
							$a->css     = 'btn btn-sm btn-default copy';
							$a->handler = 'onclick="copy.confirm(this, \''.$id['id'].'\');return false;"';
							$update .= $a->get_string();
						}

						$a        = $response->html->a();
						$a->href  = $href_qrcode.'&'.$this->identifier_name.'%5B%5D='.$id['id'];
						$a->label = $this->lang['button_qrcode'];
						$a->title = $this->lang['button_qrcode'];
						$a->css   = 'btn btn-sm btn-default update';
						$update .= $a->get_string();

						if($this->user['is_valid']) {
							$a        = $response->html->a();
							$a->href  = $href_prozess.'&'.$this->identifier_name.'%5B%5D='.$id['id'].$tparams;
							$a->label = $this->lang['button_process'];
							$a->title = $this->lang['button_process'];
							$a->css   = 'btn btn-sm btn-default process';
							$update .= $a->get_string();
						}

						if(isset($this->raumbuch)) {
							if(isset($id['RAUMBUCHID'])) {
								$a        = $response->html->a();
								$a->href  = '#';
								$a->label = $this->lang['button_location'];
								$a->title = $this->lang['button_location'];
								$a->css   = 'btn btn-sm btn-default raumbuch';
								$a->handler = 'onclick="raumbuchpicker.init(\''.$id['RAUMBUCHID'].'\',\''.$id['id'].'\'); return false;"';
								$update .= $a->get_string();
							}
						}
					}

					// handle CAFM.ONE connected
					if(in_array('cafm.one', $this->plugins)) {
						$todo = '';
						$interval = '';
						if(isset($this->filter['todos']['prefix'])) {
							$todo = $this->filter['todos']['prefix'];
						}
						if(isset($this->filter['todos']['interval'])) {
							$interval = $this->filter['todos']['interval'];
						}
						$a        = $response->html->a();
						$a->href  = '#';
						$a->label = $this->lang['button_todos'];
						$a->title = $this->lang['button_todos'];
						$a->css   = 'btn btn-sm btn-default todos';
						// handle grouped
						if(!isset($id['SUMM']) || $id['SUMM'] === 0) {
							$a->handler = 'onclick="todospicker.init(\''.$id['bezeichner'].'\',\''.$id['id'].'\',\''.$todo.'\',\''.$interval.'\'); return false;"';
						} else {
							$a->handler = 'onclick="todospicker.init(\''.$id['bezeichner'].'\',\'\',\''.$todo.'\',\''.$interval.'\'); return false;"';
						}
						$update .= $a->get_string();
					}

					// handle ungroup button
					if(isset($id['SUMM']) && $id['SUMM'] !== 0) {
						$a = $this->response->html->a();
						$a->href = $href_ungroup.'&filter[bezeichner]='.$id['bezeichner'];
						$a->label = $this->lang['button_ungroup'];
						$a->css   = 'btn btn-sm btn-default ungroup';
						$a->handler = 'onclick="phppublisher.wait();"';
						$update .= $a->get_string();
					}

					// handle marker
					if(isset($id['marker'])) {
						$d['marker'] = '<a class="icon icon-star" style="font-size: 35px;color: '.$id['marker'].';" onclick="marker.init(this,\''.$id['id'].'\'); return false;"></a>';
					} else {
						$d['marker'] = '<a class="icon icon-star-empty" style="font-size: 35px;" onclick="marker.init(this,\''.$id['id'].'\'); return false;"></a>';
					}
					$d['id']         = $id['id'];
					$d['bezeichner'] = $id['bezeichner'];
					$d['date']       = $id['date'];
					$d['data']       = $data;
					$d['action']     = $update;
					// Custom Filters
					if(isset($this->filters) && is_array($this->filters)) {
						foreach($this->filters as $f) {
							$key = $f['key'];
							$d[$key] = $id[$key];
						}
					}

					// Custom Filters
					if(isset($this->filters) && is_array($this->filters)) {
						foreach($this->filters as $f) {
							$key = $f['key'];
							if(isset($id[$key]) && $id[$key] !== '') {
								$d[$key] = $id[$key];
							}
						}
					}

					// Counter
					if(isset($head['SUMM']) && isset($id['SUMM'])) {
						$d['SUMM'] = $id['SUMM'];
					}
					else if(isset($head['SUMM'])) {
						$d['SUMM'] = 0;
					}
					$body[] = $d;
				}
			}
		}

		$table->head = $head;
		$table->body = $body;
		#$table->identifier_disabled = $identifier_disabled;
		$response->table = $table;
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
		$form = $response->get_form($this->actions_name, 'select');

		$submit = $form->get_elements('submit');
		$submit->value = 'Filter';
		$form->add($submit,'submit');

		// unset double entry from response
		$form->remove('filter[');
		$form->remove('export[');
		
		$o = $this->response->html->option();
		$o->label = $this->lang['button_group'];
		$o->value = 'xx';
		$o->selected = true;
		$o->disabled = true;
		$o->customattribs = 'hidden';
		$o->style = 'color:silver;';
		$groups[] = $o;
		$o = $this->response->html->option();
		$o->label = '';
		$o->value = 'single';
		$groups[] = $o;
		$o = $this->response->html->option();
		$o->label = $this->lang['label_identifier'];
		$o->value = 'bezeichner';
		$groups[] = $o;

		$d['filter_group']['object']['type']              = 'htmlobject_select';
		$d['filter_group']['object']['attrib']['title']   = $this->lang['button_title_group'];
		$d['filter_group']['object']['attrib']['options'] = $groups;
		$d['filter_group']['object']['attrib']['css']     = 'form-control-sm float-left';
		$d['filter_group']['object']['attrib']['style']   = 'width: auto;';
		$d['filter_group']['object']['attrib']['name']    = 'filter[group]';
		$d['filter_group']['object']['attrib']['handler'] = 'onchange="phppublisher.wait();this.form.submit();"';


		$params = $this->response->get_array();
		unset($params['export']);
		unset($params['printout']);
		$url  = $_SERVER['REQUEST_SCHEME'].'://';
		$url .= $_SERVER['SERVER_NAME'];
		$url .= $response->html->thisurl.'/';
		$url .= $this->response->get_params_string($params, '?',true);

		#$url .= $response->get_url($this->actions_name, 'select');

		$d['linkbox']['static']                    = true;
		$d['linkbox']['object']['type']            = 'htmlobject_textarea';
		$d['linkbox']['object']['attrib']['id']    = 'LinkBox';
		$d['linkbox']['object']['attrib']['value'] = $url;
		$d['linkbox']['object']['attrib']['name']  = 'linkbox';
		$d['linkbox']['object']['attrib']['rows']  = 8;
		$d['linkbox']['object']['attrib']['style'] = 'width: 100%;margin: 0;';

		$d['filter_id']['label']                     = 'ID';
		$d['filter_id']['object']['type']            = 'htmlobject_input';
		$d['filter_id']['object']['attrib']['name']  = 'filter[id]';
		if(isset($this->filter['id']) && $this->filter['id'] !== '') {
			$this->__filters[] = 'ID: '.$this->filter['id'];
		}

		$d['filter_bezeichner']['label']                     = $this->lang['label_identifier'];
		$d['filter_bezeichner']['object']['type']            = 'htmlobject_input';
		$d['filter_bezeichner']['object']['attrib']['name']  = 'filter[bezeichner]';
		$d['filter_bezeichner']['object']['attrib']['title'] = $this->lang['title_filter_identifier'];
		if(isset($this->filter['bezeichner']) && $this->filter['bezeichner'] !== '') {
			$this->__filters['bezeichner'] = $this->lang['label_identifier'].': '.$this->filter['bezeichner'];
		}
		$d['filter_bezeichner_not']['object']['type']            = 'htmlobject_input';
		$d['filter_bezeichner_not']['object']['attrib']['type']  = 'checkbox';
		$d['filter_bezeichner_not']['object']['attrib']['title'] = 'Not';
		$d['filter_bezeichner_not']['object']['attrib']['css']   = 'included';
		$d['filter_bezeichner_not']['object']['attrib']['name']  = 'filter[not][bezeichner]';
		if(isset($this->filter['bezeichner']) && $this->filter['bezeichner'] !== '') {
			$not = '';
			if(isset($this->filter['not']['bezeichner'])) {
				$not = ' (not)';
			}
			$this->__filters['bezeichner'] .= $not;
		}

		if(isset($this->raumbuch)) {
			$raumbuch = $this->raumbuch->options;
			if(is_array($raumbuch) && count($raumbuch) > 0) {
				array_unshift($raumbuch, array('id' => '', 'path' => ''));
				$d['filter_raumbuch']['label']                       = $this->lang['label_location'];
				$d['filter_raumbuch']['object']['type']              = 'htmlobject_select';
				$d['filter_raumbuch']['object']['attrib']['index']   = array('id','path');
				$d['filter_raumbuch']['object']['attrib']['name']    = 'filter[raumbuch]';
				$d['filter_raumbuch']['object']['attrib']['id']      = 'filter_raumbuch';
				$d['filter_raumbuch']['object']['attrib']['options'] = $raumbuch;
				$d['filter_raumbuch']['object']['attrib']['style']   = 'width: 300px;';
				$d['filter_raumbuch']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['label_location'].'\'); return false;"';
				if(isset($this->filter['raumbuch']) && $this->filter['raumbuch'] !== '') {
					$this->__filters[] = $this->lang['label_location'].': '.$raumbuch[$this->raumbuch->indexprefix.$this->filter['raumbuch']]['label'].' ['.$this->filter['raumbuch'].']';
				}
			} else {
				$d['filter_raumbuch'] = '';
			}
		} else {
			$d['filter_raumbuch'] = '';
			$raumbuch = '';
		}

		$gewerke = $this->gewerke->options();
		if(is_array($gewerke)) {
			array_unshift($gewerke, array('id' => '', 'label' => ''));
			$d['filter_gewerk']['label']                       = $this->lang['label_trade'];
			$d['filter_gewerk']['object']['type']              = 'htmlobject_select';
			$d['filter_gewerk']['object']['attrib']['index']   = array('id','label');
			$d['filter_gewerk']['object']['attrib']['name']    = 'filter[gewerk]';
			$d['filter_gewerk']['object']['attrib']['id']      = 'filter_gewerk';
			$d['filter_gewerk']['object']['attrib']['options'] = $gewerke;
			$d['filter_gewerk']['object']['attrib']['css']     = 'chosen-select-deselect';
			$d['filter_gewerk']['object']['attrib']['style']   = 'width: 300px;';
			$d['filter_gewerk']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['label_trade'].'\'); return false;"';
			if(isset($this->filter['gewerk']) && $this->filter['gewerk'] !== '') {
				$this->__filters[] = $this->lang['label_trade'].': '.$gewerke[$this->filter['gewerk']]['label'];
			}
		} else {
			$d['filter_gewerk'] = '';
		}

		#$d['filter_user']['label']                     = 'User';
		#$d['filter_user']['object']['type']            = 'htmlobject_input';
		#$d['filter_user']['object']['attrib']['name']  = 'filter[user]';
		#if(isset($this->filter['user']) && $this->filter['user'] !== '') {
		#	$this->__filters[] = 'User: '.$this->filter['user'];
		#}
		$d['filter_user'] = '';

		$d['filter_date']['label']                     = $this->lang['label_date'];
		$d['filter_date']['object']['type']            = 'htmlobject_input';
		$d['filter_date']['object']['attrib']['id']    = 'filter_date';
		$d['filter_date']['object']['attrib']['name']  = 'filter[date]';
		if(isset($this->filter['date']) && $this->filter['date'] !== '') {
			$order = '&gt;';
			if($this->filter['order']['date'] === 'smaller') {
				$order = '&lt;';
			}
			$this->__filters[] = $this->lang['label_date'].': '.$order.' '.$this->filter['date'];
		}

		$d['filter_order_date']['object']['type']              = 'htmlobject_select';
		$d['filter_order_date']['object']['attrib']['index']   = array(0,1);
		$d['filter_order_date']['object']['attrib']['options'] = array(array('larger','&gt;'),array('smaller','&lt;'));
		$d['filter_order_date']['object']['attrib']['css']     = 'form-control-sm date included';
		$d['filter_order_date']['object']['attrib']['name']    = 'filter[order][date]';

		if(isset($this->filters) && is_array($this->filters)) {
			foreach($this->filters as $f) {
				$key = $f['key'];
			
				$d['cfilter_'.$key]['label']                     = $key;
				$d['cfilter_'.$key]['object']['type']            = 'htmlobject_input';
				$d['cfilter_'.$key]['object']['attrib']['name']  = 'filter['.$key.']';
				$d['cfilter_'.$key]['object']['attrib']['style'] = '';
				if(isset($this->filter[$key]) && $this->filter[$key] !== '') {
					$not = '';
					if(isset($this->filter['not'][$key])) {
						$not = ' (not)';
					}
					$this->__filters[$key] = $key.': '.$this->filter[$key].$not;
				} else {
					if(isset($this->filter['not'][$key])) {
						$this->__filters[] = $key.': <i>NULL</i>';
					}
				}
				$d['cfilter_not_'.$key]['object']['type']            = 'htmlobject_input';
				$d['cfilter_not_'.$key]['object']['attrib']['type']  = 'checkbox';
				$d['cfilter_not_'.$key]['object']['attrib']['title'] = 'Not';
				$d['cfilter_not_'.$key]['object']['attrib']['css']   = 'included';
				$d['cfilter_not_'.$key]['object']['attrib']['name']  = 'filter[not]['.$key.']';
			}
		} else {
			$d['cfilter_0'] = '';
		}

		// Todos
		if(isset($this->taetigkeiten)) {
			$todos = $this->taetigkeiten->prefixes();
			if(is_array($todos)) {
				$this->__todos = $todos;
				array_unshift($this->__todos,array('prefix'=>'','lang'=>''));
				$d['filter_prefix']['label']                       = $this->lang['label_todo_obligations'];
				$d['filter_prefix']['css']                         = '';
				$d['filter_prefix']['object']['type']              = 'htmlobject_select';
				$d['filter_prefix']['object']['attrib']['index']   = array('prefix','lang');
				$d['filter_prefix']['object']['attrib']['name']    = 'filter[todos][prefix]';
				$d['filter_prefix']['object']['attrib']['options'] = $this->__todos;
				$d['filter_prefix']['object']['attrib']['style']   = 'width: 300px;';
				#$d['filter_prefix']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['label_todo_obligations'].'\'); return false;" onchange="todosfilter.init(this);"';
				$d['filter_prefix']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['label_todo_obligations'].'\'); return false;"';

				if(isset($this->filter['todos']['prefix']) && $this->filter['todos']['prefix'] !== '') {

					// set filter highlight
					$this->__filters[] = 'Todos: '.$this->__todos[$this->filter['todos']['prefix']]['lang'];

					// handle todo attribs
					#$attribs  = $this->taetigkeiten->attribs($this->filter['todos']['prefix']);
					#$interval = $this->taetigkeiten->interval($this->filter['todos']['prefix']);

/*
					if(is_array($interval)) {
						$this->__interval = $interval;
						array_unshift($interval, array('key'=>'','label'=>''));
						## TODO translation
						$d['filter_interval']['label']                       = 'Interval';
						$d['filter_interval']['css']                         = '';
						$d['filter_interval']['object']['type']              = 'htmlobject_select';
						$d['filter_interval']['object']['attrib']['index']   = array('key','label');
						$d['filter_interval']['object']['attrib']['name']    = 'filter[todos][interval]';
						$d['filter_interval']['object']['attrib']['options'] = $interval;
						$d['filter_interval']['object']['attrib']['style']   = 'width: 300px;';
						// set filter highlight
						if(isset($this->filter['todos']['interval']) && $this->filter['todos']['interval'] !== '') {
							$this->__filters[] = 'Interval: '.$this->__interval[$this->filter['todos']['interval']]['label'];
						}
					} else {
						$d['filter_interval'] = '';
					}
*/

/*
					// handle todo attribs
					if(is_array($attribs)) {
						if(isset($this->__todos[$this->filter['todos']['prefix']]['bezeichner'])) {
							$__interval = $this->__todos[$this->filter['todos']['prefix']]['bezeichner'];
						}
						if(
							isset($this->filter['todos']['interval']) && 
							isset($this->__interval[$this->filter['todos']['interval']]['bezeichner'])
						) {
							$__interval = $this->__interval[$this->filter['todos']['interval']]['bezeichner'];
						}
						foreach($attribs as $a) {
							$d = array_merge($d, $a['element']);
							$key = str_replace('tfilter_', '', key($a['element']));
							if(
								isset($this->filter['todos']['attribs'][$key]) && 
								$this->filter['todos']['attribs'][$key] !== ''
							) {
								$this->__filters[] = $d['tfilter_'.$key]['label'].': '.$this->filter['todos']['attribs'][$key];
								// handle attribs bezeichner
								$bezeichner = explode(',',$a['bezeichner']);
								foreach($bezeichner as $b) {
									if(in_array($b, $__interval)) {
										$this->__attribs[$b][$key] = $this->filter['todos']['attribs'][$key];
										$this->__attribs['bezeichner'][$b] = $b;
									}
								}
							}
						}
					} else {
						$d['tfilter_0'] = '';
					}
*/
				} else {
					$d['filter_interval'] = '';
					$d['tfilter_0'] = '';
				}

			} else {
				$div = $this->response->html->div();
				$div->add($todos);
				$div->style = 'text-align: center;';
				$d['filter_prefix']['object'] = $div;
				$d['filter_interval'] = '';
				$d['tfilter_0'] = '';
			}
			$d['tfilter_0'] = '';
		} else {
			$d['filter_interval'] = '';
			$d['filter_prefix'] = '';
			$d['tfilter_0'] = '';
		}

		// Process
 		if(is_array($this->prozesses)) {
			foreach( $this->prozesses as $k => $r ) {
				$d = array_merge($d, $this->bestandsverwaltung->element($r, 'prozess', 'filter', array(), 'bestand_'));
				if(isset($this->filter[$r['merkmal_kurz']]) && $this->filter[$r['merkmal_kurz']] !== '') {
					$not = '';
					if(isset($this->filter['not'][$r['merkmal_kurz']])) {
						$not = ' (not)';
					}
					if(is_array($this->filter[$r['merkmal_kurz']])) {
						$tf = implode(', ', $this->filter[$r['merkmal_kurz']]);
					} else {
						$tf = $this->filter[$r['merkmal_kurz']];
					}
					$this->__filters[] = $r['merkmal_lang'].': '.$tf.$not;
				} else {
					if(isset($this->filter['not'][$r['merkmal_kurz']])) {
						$this->__filters[] = $r['merkmal_lang'].': <i>NULL</i>';
					}
				}
				$d['prozess_not_'.$r['merkmal_kurz']]['object']['type']            = 'htmlobject_input';
				$d['prozess_not_'.$r['merkmal_kurz']]['object']['attrib']['type']  = 'checkbox';
				$d['prozess_not_'.$r['merkmal_kurz']]['object']['attrib']['title'] = 'Not';
				$d['prozess_not_'.$r['merkmal_kurz']]['object']['attrib']['css']   = 'included';
				$d['prozess_not_'.$r['merkmal_kurz']]['object']['attrib']['name']  = 'filter[not]['.$r['merkmal_kurz'].']';
			}
		} else {
			$d['prozess_0'] = '';
		}

		// Export
		$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang', null, 'pos');
		if(is_array($tables)) {
			// handle export grouping
			if(!isset($this->filter['group']) || $this->filter['group'] === 'single') {
				if(is_array($raumbuch) && is_array($tables)) {
					array_unshift($tables,array('tabelle_kurz'=>'standort','tabelle_lang'=>$this->lang['label_location']));
				}
			} else {
				// handle missing standort (prevent error message)
				if(isset($_REQUEST['export']['table']) && count($_REQUEST['export']['table']) > 0) {
					$key = array_search('standort', $_REQUEST['export']['table']);
					if($key !== false) {
						unset($_REQUEST['export']['table'][$key]);
						if(count($_REQUEST['export']['table']) < 1) {
							unset($_REQUEST['export']['table']);
						}
					}
				}
			}
		} else {
			$tables = array();
		}

		// handle export todos
		if(isset($todos) && is_array($todos)) {
			## TODO translation
			$tables[] = array('tabelle_kurz'=>'todos','tabelle_lang'=>'Todos');
		}

		// export link
		$tables[] = array('tabelle_kurz'=>'link','tabelle_lang'=>'Link');

		$d['export_table']['label']                        = $this->lang['label_export_details'];
		$d['export_table']['css']                          = 'autosize';
		$d['export_table']['object']['type']               = 'htmlobject_select';
		$d['export_table']['object']['css']                = 'form-control-sm';
		$d['export_table']['object']['attrib']['index']    = array('tabelle_kurz','tabelle_lang');
		$d['export_table']['object']['attrib']['name']     = 'export[table][]';
		$d['export_table']['object']['attrib']['multiple'] = true;
		$d['export_table']['object']['attrib']['options']  = $tables;
		$d['export_table']['object']['attrib']['style']    = 'width:150px; height: 115px;';

		$d['bom']['label']                     = 'BOM';
		$d['bom']['css']                       = 'autosize';
		$d['bom']['style']                     = 'margin: 0 0 0 0; float:right;';
		$d['bom']['object']['type']            = 'htmlobject_input';
		$d['bom']['object']['attrib']['type']  = 'checkbox';
		$d['bom']['object']['attrib']['name']  = 'export[bom]';
		$d['bom']['object']['attrib']['title'] = $this->lang['title_export_bom'];
		if(isset($this->settings['export']['bom'])) {
			$d['bom']['object']['attrib']['checked'] = true;
		}

		// inline export ?
		$d['inline']['label']                     = $this->lang['label_export_inline'];
		$d['inline']['css']                       = 'autosize';
		$d['inline']['style']                     = 'margin: 0; float:right;clear:right;';
		$d['inline']['object']['type']            = 'htmlobject_input';
		$d['inline']['object']['attrib']['type']  = 'checkbox';
		$d['inline']['object']['attrib']['name']  = 'export[inline]';
		$d['inline']['object']['attrib']['title'] = $this->lang['title_export_inline'];

		$o = array();
		$o[] = array('\n');
		$o[] = array('\r\n');

		$d['linefeed']['label']                       = $this->lang['label_export_linefeed'];
		$d['linefeed']['css']                         = 'autosize';
		$d['linefeed']['object']['type']              = 'htmlobject_select';
		$d['linefeed']['object']['attrib']['index']   = array(0,0);
		$d['linefeed']['object']['attrib']['options'] = $o;
		$d['linefeed']['object']['attrib']['name']    = 'export[linefeed]';
		$d['linefeed']['object']['attrib']['id']      = 'delimiter';
		$d['linefeed']['object']['attrib']['style']   = 'width:65px;';
		$d['linefeed']['object']['attrib']['css']     = 'form-control-sm';
		$d['linefeed']['object']['attrib']['title']   = $this->lang['title_export_linefeed'];
		if(isset($this->settings['export']['linefeed'])) {
			$d['linefeed']['object']['attrib']['selected'] = array($this->settings['export']['linefeed']);
		} else {
			$d['linefeed']['object']['attrib']['selected'] = array('\\n');
		}

		$o = array();
		$o[] = array(',');
		$o[] = array(';');
		$o[] = array('\t');

		$d['delimiter']['label']                       = $this->lang['label_export_delimiter'];
		$d['delimiter']['css']                         = 'autosize';
		$d['delimiter']['object']['type']              = 'htmlobject_select';
		$d['delimiter']['object']['attrib']['index']   = array(0,0);
		$d['delimiter']['object']['attrib']['options'] = $o;
		$d['delimiter']['object']['attrib']['name']    = 'export[delimiter]';
		$d['delimiter']['object']['attrib']['id']      = 'delimiter';
		$d['delimiter']['object']['attrib']['style']   = 'width:65px;';
		$d['delimiter']['object']['attrib']['css']     = 'form-control-sm';
		$d['delimiter']['object']['attrib']['title']   = $this->lang['title_export_delimiter'];
		if(isset($this->settings['export']['delimiter'])) {
			$d['delimiter']['object']['attrib']['selected'] = array($this->settings['export']['delimiter']);
		} else {
			$d['delimiter']['object']['attrib']['selected'] = array(';');
		}

		$o = array();
		$o[] = array('','');
		$o[] = array("'","'");
		$o[] = array('quot','&#34;');

		$d['enclosure']['label']                       = $this->lang['label_export_enclosure'];
		$d['enclosure']['css']                         = 'autosize';
		$d['enclosure']['object']['type']              = 'htmlobject_select';
		$d['enclosure']['object']['attrib']['index']   = array(0,1);
		$d['enclosure']['object']['attrib']['options'] = $o;
		$d['enclosure']['object']['attrib']['name']    = 'export[enclosure]';
		$d['enclosure']['object']['attrib']['id']      = 'enclosure';
		$d['enclosure']['object']['attrib']['style']   = 'width:65px;';
		$d['enclosure']['object']['attrib']['css']     = 'form-control-sm';
		$d['enclosure']['object']['attrib']['title']   = $this->lang['title_export_enclosure'];
		if(isset($this->settings['export']['enclosure'])) {
			$d['enclosure']['object']['attrib']['selected'] = array($this->settings['export']['enclosure']);
		} else {
			$d['enclosure']['object']['attrib']['selected'] = array('');
		}

		// Export submit
		$d['export']['object']['type']                = 'htmlobject_button';
		$d['export']['object']['attrib']['css']       = 'btn btn-default';
		$d['export']['object']['attrib']['type']      = 'button';
		$d['export']['object']['attrib']['name']      = 'doexport';
		$d['export']['object']['attrib']['value']     = 'export';
		$d['export']['object']['attrib']['label']     = $this->lang['button_export'];
		$d['export']['object']['attrib']['handler']   = 'onclick="doExport(this.form);"';

		// Printout
		$d['printout']['object']['type']                = 'htmlobject_input';
		$d['printout']['object']['attrib']['css']       = 'btn btn-default';
		$d['printout']['object']['attrib']['type']      = 'submit';
		$d['printout']['object']['attrib']['name']      = 'doprintout';
		$d['printout']['object']['attrib']['value']     = $this->lang['button_printout'];

		$mode[] = array('text');
		$mode[] = array('form');
		if(isset($this->taetigkeiten)) {
			$mode[] = array('todos');
		}

		$d['printout_mode']['label']                       = $this->lang['label_print_mode'];
		$d['printout_mode']['css']                         = 'autosize';
		$d['printout_mode']['object']['type']              = 'htmlobject_select';
		$d['printout_mode']['object']['attrib']['index']   = array(0,0);
		$d['printout_mode']['object']['attrib']['name']    = 'printout[mode]';
		$d['printout_mode']['object']['attrib']['options'] = $mode;

		$form->display_errors = false;
		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Ids
	 *
	 * @access public
	 * @return array|string
	 */
	//--------------------------------------------
	function get_ids() {

		$where = '';
		if(isset($this->filter['id']) && $this->filter['id'] !== '') {
			// mark as filtered
			$this->__filtered = true;
			$where = 'WHERE `id` LIKE \''.$this->db->handler()->escape($this->filter['id']).'\' ';
			// mark to ignore gewerk and todos
			$id = true;
		} else {
			if (isset($this->filter['bezeichner']) && $this->filter['bezeichner'] !== '') {
				// mark as filtered
				$this->__filtered = true;
				$bez = explode(',',$this->filter['bezeichner']);
				$i = 0;
				$w = '';
				$not = '';
				if(isset($this->filter['not']['bezeichner'])) {
					$not = 'NOT';
				}
				foreach($bez as $b) {
					$b = $this->db->handler()->escape($b);
					if($i > 0) {
						if($not === 'NOT') {
							$w .= ' AND ';
						} else {
							$w .= ' OR ';
						}
					}
					$w .= '`bezeichner_kurz` '.$not.' LIKE \''.$b.'\' ';
					$i = 1;
				}
				$sql = 'SELECT bezeichner_kurz as b FROM bezeichner WHERE '.$w;
				$result = $this->db->handler()->query($sql);
				if(is_array($result)) {
					foreach($result as $r) {
						$bezeichner[] = $r['b'];
					}
				}
				// no matching bezeichner return empty
				if(!isset($bezeichner)) {
					## TODO translation
					$_REQUEST[$this->message_param] = 'Filter '.$this->filter['bezeichner'].' returned no result';
					return array();
				}
			}
		}

		// handle gewerk
		if(!isset($id)) {
			if (
				isset($this->filter['gewerk']) && 
				$this->filter['gewerk'] !== ''
			){
				// mark as filtered
				$this->__filtered = true;
				$bez = $this->gewerke->gewerk2sql($this->filter['gewerk'], true);
				if(isset($bezeichner)) {
					$bezeichner = array_intersect($bezeichner, $bez);
				} else {
					$bezeichner = $bez;
				}
			}
		}

		// handle todos
		if (
			!isset($id) && 
			isset($this->__todos) && 
			isset($this->filter['todos']['prefix']) && 
			$this->filter['todos']['prefix'] !== '' && 
			isset($this->__todos[$this->filter['todos']['prefix']]['bezeichner'])
		){
			// mark as filtered
			$this->__filtered = true;
			// handle todo attribs
			if(
				isset($this->filter['todos']['attribs']) && 
				$this->filter['todos']['attribs'] !== '' && 
				isset($this->__attribs['bezeichner'])
			) {
				$prefix = $this->filter['todos']['prefix'];
				$interval = '';
				if(isset($this->filter['todos']['interval']) && $this->filter['todos']['interval'] !== '') {
					$interval = $this->filter['todos']['interval'];
				}
				if(isset($bezeichner)) {
					$this->__attribs['bezeichner'] = array_intersect($bezeichner, $this->__attribs['bezeichner']);
					unset($bezeichner);
				}
				// check if settings return non empty taetigkeiten
				foreach($this->__attribs['bezeichner'] as $b) {
					$x = $this->taetigkeiten->details(
								$b, 
								$this->__attribs[$b], 
								$prefix, 
								$interval, 
								false,
								true,
								array(),
								false
							);
					// set bezeichner only if x not empty
					if(is_array($x)) {
						$bezeichner[] = $b;
					}
				}
			}
			// handle interval
			else if(
				isset($this->filter['todos']['interval']) && 
				$this->filter['todos']['interval'] !== '' && 
				isset($this->__interval[$this->filter['todos']['interval']]['bezeichner'])
			) {
				if(isset($bezeichner)) {
					$this->__interval[$this->filter['todos']['interval']]['bezeichner'] = array_intersect($bezeichner, $this->__interval[$this->filter['todos']['interval']]['bezeichner']);
					unset($bezeichner);
				}
				$bezeichner = $this->__interval[$this->filter['todos']['interval']]['bezeichner'];
			} 
			else {
				if(isset($bezeichner)) {
					$this->__todos[$this->filter['todos']['prefix']]['bezeichner'] = array_intersect($bezeichner, $this->__todos[$this->filter['todos']['prefix']]['bezeichner']);
					unset($bezeichner);
				}
				$bezeichner = $this->__todos[$this->filter['todos']['prefix']]['bezeichner'];
			}

			if(!isset($bezeichner)) {
				## TODO translation
				$_REQUEST[$this->message_param] = 'Filter returned no todos';
				return array();
			}
		}

		// build bezeichner where
		if(isset($bezeichner) && !in_array('*', $bezeichner)) {
			## TODO identifier hits
			//$this->__filters[] = '<div class="hits">'.$this->lang['label_identifier_hits'].': '.count($bezeichner).'</div>';
			if($where === '') {
				$where .= 'WHERE bezeichner_kurz=\''.implode('\' OR bezeichner_kurz=\'', $bezeichner).'\' ';
			} else {
				$where .= ' AND ( bezeichner_kurz=\''.implode('\' OR bezeichner_kurz=\'', $bezeichner).'\') ';
			}
		}

		// handle user
		#if (isset($this->filter['user']) && $this->filter['user'] !== '') {
		#	// mark as filtered
		#	$this->__filtered = true;
		#	$cleaned = $this->db->handler()->escape($this->filter['user']);
		#	if($where !== '') {
		#		$where .= ' AND `user` LIKE \''.$cleaned.'\' ';
		#	} else {
		#		$where .= 'WHERE `user` LIKE \''.$cleaned.'\' ';
		#	}
		#}

		// handle attrib filters
		$filter = '';
		$filters = array();
		// Custom Filters
		if(isset($this->filters) && is_array($this->filters)) {
			foreach($this->filters as $f) {
				$key = $f['key'];
				if(isset($this->filter[$key]) && $this->filter[$key] !== '') {
					$filters[$key] = $this->db->handler()->escape($this->filter[$key]);
				}
				else if(isset($this->filter['not'][$key])) {
					$filters[$key] = '<i>NULL</i>';
					$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\''.$key.'\' AND `tabelle`=\''.$f['table'].'\', wert, NULL ) ) AS \''.$key.'\' ';
				} else {
					$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\''.$key.'\' AND `tabelle`=\''.$f['table'].'\', wert, NULL ) ) AS \''.$key.'\' ';
				}
			}
		}

		// Todo attribs filter
 		if(isset($this->filter['todos']['attribs']) && is_array($this->filter['todos']['attribs'])) {
			foreach( $this->filter['todos']['attribs'] as $k => $v ) {
				if($v !== '') {
					$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\''.$k.'\' AND `tabelle`=\'TODO\', wert, NULL ) ) AS \''.$k.'\' ';
				}
			}
		}

		// Prozess Filter
		if(is_array($this->prozesses)) {
			foreach( $this->prozesses as $k => $v ) {
				if(
					isset($this->filter[$v['merkmal_kurz']]) &&
					$this->filter[$v['merkmal_kurz']] !== ''
				) {
					// handle array
					if(is_array($this->filter[$v['merkmal_kurz']])) {
						$filters[$v['merkmal_kurz']] = array();
						foreach($this->filter[$v['merkmal_kurz']] as $value) {
							$filters[$v['merkmal_kurz']][] = $this->db->handler()->escape($value);
						}
					} else {
						$filters[$v['merkmal_kurz']] = $this->db->handler()->escape($this->filter[$v['merkmal_kurz']]);
					}
				} else {
					// handle not
					// if not is set but value is empty add filter <i>NULL</i>
					if(isset($this->filter['not'][$v['merkmal_kurz']])) {
						$filters[$v['merkmal_kurz']] = '<i>NULL</i>';
					}
				}
			}
		}

		// handle export
		$this->exports = array();
		if($this->response->html->request()->get('doexport') !== '') {

			// handle grouping
			if(!isset($this->filter['group']) || $this->filter['group'] === 'single') {
				$this->exports['id'] = 'ID';
			}

			$this->exports['bezeichner'] = $this->lang['label_identifier'];

			// handle grouping
			if(isset($this->filter['group']) && $this->filter['group'] === 'bezeichner') {
				## TODO translation
				$this->exports['SUMM'] = 'Summe';
			}

			$table = $this->response->html->request()->get('export[table]');
			if(is_array($table)) {
				// handle grouping
				if(!isset($this->filter['group']) || $this->filter['group'] === 'single') {
					if(in_array('standort',$table)) {
						$this->exports['RAUMBUCH']   = $this->lang['label_location'];
						$this->exports['RAUMBUCHID'] = $this->lang['label_location'].' ID';
					}
				}
				if(in_array('todos',$table)) {
					## TODO translation
					$this->exports['todos'] = 'Todos';
				}
				$mb = '';
				if(isset($bezeichner) && is_array($bezeichner)) {
					$mb = 'WHERE `bezeichner_kurz` LIKE \'*\' OR `bezeichner_kurz` LIKE \'*,%\' OR `bezeichner_kurz` LIKE \'%,*\'  OR `bezeichner_kurz` LIKE \'%,*,%\' ';
					foreach($bezeichner as $b) {
						$mb .= 'OR `bezeichner_kurz` LIKE \''.$b.'\' OR `bezeichner_kurz` LIKE \''.$b.',%\' OR `bezeichner_kurz` LIKE \'%,'.$b.'\' OR `bezeichner_kurz` LIKE \'%,'.$b.',%\' ';
					}
				}
				foreach($table as $t) {
					// handle grouping
					if(!isset($this->filter['group']) || $this->filter['group'] === 'single') {
						if($t !== 'raumbuch' && $t !== 'gewerk' && $t !== 'todos') {
							$t = $this->db->handler()->escape($t);
							$sql = 'SELECT merkmal_kurz, merkmal_lang FROM bestand_'.$t.' '.$mb;
							$tmp = $this->db->handler()->query($sql);
							if(is_array($tmp)) {
								foreach($tmp as $v) {
									$filter .= ', GROUP_CONCAT(DISTINCT if( ';
									$filter .= '`merkmal_kurz`=\''.$v['merkmal_kurz'].'\' AND `tabelle`=\''.$t.'\', ';
									$filter .= 'wert, NULL ) ) AS \''.$v['merkmal_kurz'].'\' ';
									$this->exports[$v['merkmal_kurz']] = $v['merkmal_lang'];
								}
							}
						}
					} else {
						#var_dump($table);
					}
				}
			}
		}

		foreach($filters as $k => $v) {
			$not = '';
			if(isset($this->filter['not'][$k])) {
				$not = 'NOT';
			}
			if(is_array($v)) {
				foreach($v as $value) {
					$filter .= ', GROUP_CONCAT(DISTINCT if( ';
					$filter .= '`merkmal_kurz`=\''.$k.'\' AND `wert` '.$not.' LIKE \''.$value.'\', ';
					$filter .= 'wert, NULL ) ) AS \''.$k.'\' ';
				}
			} else {
				// handle NULL
				if($v !== '<i>NULL</i>') {
					$filter .= ', GROUP_CONCAT(DISTINCT if( ';
					$filter .= '`merkmal_kurz`=\''.$k.'\' AND `wert` '.$not.' LIKE \''.$v.'\', ';
					$filter .= 'wert, NULL ) ) AS \''.$k.'\' ';
				} else {
					$filter .= ', GROUP_CONCAT(DISTINCT IF(`merkmal_kurz`=\''.$k.'\', wert,\'<i>NULL</i>\')) AS \''.$k.'\' ';
				}
			}
		}

		// handle raumbuch
		if(isset($this->filter['raumbuch']) && $this->filter['raumbuch'] !== '') {
			$children = $this->raumbuch->children($this->filter['raumbuch']);
			$tmp  = ', GROUP_CONCAT(DISTINCT ';
			$tmp .= 'if( `merkmal_kurz`=\'RAUMBUCHID\' ';
			$tmp .= 'AND `tabelle`=\'SYSTEM\' ';
			if(is_array($children)) {
				$tmp .= 'AND ( `wert`=\''.$this->filter['raumbuch'].'\' ';
				foreach($children as $c) {
					$tmp .= 'OR `wert`=\''.$c['id'].'\' ';
				}
				$tmp .= ') ';
			} else {
				$tmp .= 'AND `wert`=\''.$this->filter['raumbuch'].'\' ';
			}
			$tmp .= ', wert, NULL ) ) AS \'RAUMBUCHID\' ';
			$filter .= $tmp;
			$filters['RAUMBUCHID'] = $this->filter['raumbuch'];
		} else {
			$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'RAUMBUCHID\' AND `tabelle`=\'SYSTEM\', wert, NULL ) ) AS \'RAUMBUCHID\' ';
		}

		// assemble query
		$sql  = 'SELECT `b`.`id` AS id, `b`.`bezeichner_kurz` AS bezeichner,`b`.`date` AS date, ';
		$sql .= 'GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'MARKER\' AND `tabelle`=\'SYSTEM\', wert, NULL ) ) AS \'marker\' ';
		$sql .= $filter;
		$sql .= 'FROM `bestand` AS b ';
		$sql .= $where;
		$sql .= 'GROUP BY id, bezeichner, date';

		#echo $sql;
		#$this->db->debug();
		$ids = $this->db->handler()->query($sql);
		#$this->response->html->help($filters);
		#$this->response->html->help($ids);
		#$this->db->debug(false);
		#$this->response->html->help($ids);

		if(is_array($ids)) {
			// handle date
			if (isset($this->filter['date']) && $this->filter['date'] !== '') {
				// mark as filtered
				$this->__filtered = true;
				$date = new DateTime($this->filter['date']);
				$date = $date->getTimestamp();
				if(is_array($ids) && count($ids) > 0) {
					foreach($ids as $ki => $id) {
						if($this->filter['order']['date'] === 'smaller') {
							if($id['date'] > $date) {
								unset($ids[$ki]);
							}
						} else {
							if($id['date'] < $date) {
								unset($ids[$ki]);
							}
						}
					}
				}
			}

			// Run filters
			if(count($filters) > 0) {
				// mark as filtered
				$this->__filtered = true;
				if(is_array($ids) && count($ids) > 0) {
					foreach($filters as $kf => $filter) {
						foreach($ids as $ki => $id) {
							## TODO is null
							## TODO value check?
							if(!isset($id[$kf])) {
								unset($ids[$ki]);
							}
							elseif($filter === '<i>NULL</i>' && $id[$kf] !== '<i>NULL</i>') {
								unset($ids[$ki]);
							}
						}
					}
				}
			}

			// Run Marker Filter
			if(isset($this->filter['marker']) && $this->filter['marker'] !== '') {
				foreach($ids as $ki => $id) {
					if($this->filter['marker'] !== 'silver') {
						if(!isset($id['marker']) || $id['marker'] !== $this->filter['marker']) {
							unset($ids[$ki]);
						}
					} else {
						if(isset($id['marker'])) {
							unset($ids[$ki]);
						}
					}
				}
			}

			// Run todo attrib filters
			if(isset($this->__attribs) && isset($this->__attribs['bezeichner'])) {
				// mark as filtered
				$this->__filtered = true;
				if(is_array($ids) && count($ids) > 0) {
					foreach($ids as $ki => $id) {
						if(isset($id['bezeichner']) && isset($this->__attribs[$id['bezeichner']])) {
							foreach($this->__attribs[$id['bezeichner']] as $k => $a) {
								if(!isset($id[$k]) || $id[$k] !== $a) {
									unset($ids[$ki]);
								}
							}
						}
					}
				}
			}

			// Group bezeichner
			if(
				isset($this->filter['group']) && 
				$this->filter['group'] !== 'single'
			 ) {
				// mark as filtered
				#$this->__filtered = true;
				if(is_array($ids) && count($ids) > 0) {
					$tmp = array();
					foreach($ids as $k => $i) {
						// group slaves only
						if($this->filter['group'] === 'master') {
							if(
								isset($this->bezeichner[$i['bezeichner']]['type']) && 
								$this->bezeichner[$i['bezeichner']]['type'] === 'S'
							) {
								$tmp[$i['bezeichner']][] = $i;
								unset($ids[$k]);
							} else {
								$ids[$k]['SUMM'] = 0;
							}
						}
						else if($this->filter['group'] === 'bezeichner') {
							$tmp[$i['bezeichner']][] = $i;
							unset($ids[$k]);
						}
					}
					if(count($tmp) > 0) {
						foreach($tmp as $k => $v) {
							$dd = $v[0];
							$dd['SUMM'] = count($v);
							$ids[] = $dd;
						}
						// reset array keys
						$ids = array_values($ids);
					}
				}
			}

			// handle counter if no result or error
			if(is_string($ids)) {
				if($ids !== '') {
					$_REQUEST[$this->message_param] = $ids;
				}
				$ids = array();
			}
		} else {
			if($ids !== '') {
				$_REQUEST[$this->message_param] = $ids;
			}
			$ids = array();
		}
		
		#$this->response->html->help($ids);

		return $ids;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @param array $data
	 */
	//--------------------------------------------
	function __export($data) {

		$settings = array();
		$settings['delimiter'] = $this->response->html->request()->get('export[delimiter]');
		$settings['enclosure'] = $this->response->html->request()->get('export[enclosure]');
		$settings['linefeed']  = $this->response->html->request()->get('export[linefeed]');
		$settings['bom']       = $this->response->html->request()->get('export[bom]');
		$settings['table']     = $this->response->html->request()->get('export[table]');
		$settings['inline']    = $this->response->html->request()->get('export[inline]', true);

		if($settings['delimiter'] === '' || $settings['table'] === '') {
			echo 'nothing to do';
			exit(0);
		}
		elseif($settings['delimiter'] === '\t' || $settings['delimiter'] === 'tab') {
			$settings['delimiter'] = chr(9);
		}

		if($settings['enclosure'] === 'quot') {
			$settings['enclosure'] = chr(34);
		}

		if($settings['linefeed'] === '') {
			$settings['linefeed'] = chr(10);
		}
		else if($settings['linefeed'] === '\n') {
			$settings['linefeed'] = chr(10);
		}
		else if($settings['linefeed'] === '\r\n') {
			$settings['linefeed'] = "\r\n";
		}

		$url = '';
		if(isset($settings['table']) && is_array($settings['table']) && array_search('link',$settings['table']) !== false) {
			if(!isset($this->filter['group']) || $this->filter['group'] === 'single') {
				$ini  = $this->file->get_ini($this->controller->profilesdir.'settings.ini');
				$url  = $_SERVER['REQUEST_SCHEME'].'://';
				$url .= $_SERVER['SERVER_NAME'];
				$url .= $ini['config']['baseurl'];
				$url .= 'shorturl/bestand/filter/id/';
			}
		}

		$name = 'bestand.'.implode('.',$settings['table']).'.'.date('Y-m-d',time()).'.csv';

/*
		### TODO export entkoppeln
		# settings
		# $this->exports
		# $data
		# $this->__todos

		#var_dump($this->__todos);

		$dump = array();
		$dump['settings'] = $settings;
		$dump['exports']  = $this->exports;
		$dump['data']     = $data;
		#$dump['todos']   = $this->__todos;

		$path = $this->controller->profilesdir.'/export/bestand.export.tmp';

		require_once(CLASSDIR.'lib/file/file.config.class.php');
		$error = make_configfile($path, $dump);
		var_dump($error);
		unset($data);
		require_once($path);
		$this->response->html->help($data);
		exit();
*/

		if(isset($settings['inline'])) {
			echo '<!DOCTYPE html>';
			echo '<html>';
			echo '<head>';
			echo '<title>Export</title>';
			echo '<meta http-equiv="content-type" content="text/html;charset=utf-8">';
			echo '</head>';
			echo '<body style="overflow:scroll;">';
			echo '<pre>';
		} else {
			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: text/csv; charset=utf-8");
			header("Content-disposition: attachment; filename=$name");
			header('Content-Transfer-Encoding: binary');
			flush();
			if($settings['bom'] !== '') {
				echo pack('H*','EFBBBF');
			}
		}
		$i = 0;

		#$handle = fopen("php://output",'w');
		foreach($data as $v) {
			$v = array_intersect_key($v, $this->exports);
			// handle raumbuch
			if(
				isset($v['RAUMBUCHID']) && 
				is_array($this->raumbuch->options) && 
				isset($this->raumbuch->options[$this->raumbuch->indexprefix.$v['RAUMBUCHID']]) 
			) {
				$v['RAUMBUCH'] = $this->raumbuch->options[$this->raumbuch->indexprefix.$v['RAUMBUCHID']]['label'];
				$v['RAUMBUCHID'] = $v['RAUMBUCHID'];
			}
			if(isset($this->exports['todos']) && isset($this->__todos) ) {
				if(isset($tmp[$v['bezeichner']])) {
					$v['todos'] = $tmp[$v['bezeichner']];
				} else {
					$str = '';
					foreach($this->__todos as $t) {
						if(isset($t['bezeichner'])) {
							if(in_array($v['bezeichner'], $t['bezeichner'])) {
								if($str !== '') {
									$str .= ',';
								}
								$str .= $t['kurz'];
							}
						}
					}
					$tmp[$v['bezeichner']] = $str;
					$v['todos'] = $str;
				}
			}

			$v = array_replace($this->exports, $v);
			// csv header
			if($i === 0) {
				echo $settings['enclosure'].implode($settings['enclosure'].$settings['delimiter'].$settings['enclosure'], $this->exports).$settings['enclosure'];
				echo $settings['linefeed'];
				$i = 1;
			}
			// translate bezeichner
			$bezeichner = $v['bezeichner'];
			if(isset($this->bezeichner[$v['bezeichner']]['label'])) {
				$v['bezeichner'] = $this->bezeichner[$v['bezeichner']]['label'].' ('.$v['bezeichner'].')';
			}

			// handle attribs
			$m = 0;
			foreach($v as $value) {
				// remove linefeed from $v
				$value = str_replace("\n", ', ', $value);
				if($m === 1) {
					echo $settings['delimiter'];
				}
				echo $settings['enclosure'];
				echo $value;
				echo $settings['enclosure'];
				$m = 1;
			}

			// handle attribs (grouping)
			if(isset($this->filter['group']) && $this->filter['group'] === 'bezeichner') {
				if(isset($settings['table']) && is_array($settings['table'])) {
					foreach($settings['table'] as $t) {
						if($t !== 'todos') {
							$sql  = 'SELECT merkmal_lang AS m FROM bestand_'.$this->db->handler()->escape($t).' ';
							$sql .= 'WHERE `bezeichner_kurz` LIKE \'*\' OR `bezeichner_kurz` LIKE \'*,%\' OR `bezeichner_kurz` LIKE \'%,*\'  OR `bezeichner_kurz` LIKE \'%,*,%\' ';
							$sql .= 'OR `bezeichner_kurz` LIKE \''.$bezeichner.'\' OR `bezeichner_kurz` LIKE \''.$bezeichner.',%\' OR `bezeichner_kurz` LIKE \'%,'.$bezeichner.'\' OR `bezeichner_kurz` LIKE \'%,'.$bezeichner.',%\' ';
							$sql .= 'ORDER BY `row` ';
							$result = $this->db->handler()->query($sql);
							if(is_array($result)) {
								$m = 0;
								echo $settings['delimiter'];
								echo $settings['enclosure'];
								foreach($result as $r) {
									if($m === 1) {
										echo ', ';
									}
									echo $r['m'];
									$m = 1;
								}
								echo $settings['enclosure'];
							}
						}
					}
				}
			}

			// handle link
			if($url !== '') {
				echo $settings['delimiter'];
				echo $settings['enclosure'];
				echo $url.$v['id'];
				echo $settings['enclosure'];
			}

			echo $settings['linefeed'];
		}

		if(isset($settings['inline'])) { 
			echo '</pre>';
			echo '</body>';
			echo '</html>';
		}

		#fclose($handle);
		exit(0);
	}

	//--------------------------------------------
	/**
	 * Printout
	 *
	 * @access protected
	 * @param array $ids
	 * @return string
	 */
	//--------------------------------------------
	function __printout($ids) {
		$mode = $this->response->html->request()->get('printout[mode]');
		$prefix = $this->response->html->request()->get('filter[todos][prefix]');
		$interval = $this->response->html->request()->get('filter[todos][interval]');

		if(isset($this->filter['group']) && $this->filter['group'] === 'bezeichner' ) {
			$jsparams  = 'var group = "bezeichner";'."\n";
			$jsparams .= 'var jsparams = ['."\n";
			foreach($ids as $id) {
				$jsparams .= '"'.$id['bezeichner'].'"'.",\n";
			}
		} else {
			$jsparams  = 'var group = "single";'."\n";
			$jsparams .= 'var jsparams = ['."\n";
			foreach($ids as $id) {
				$jsparams .= '"'.$id['id'].'"'.",\n";
			}
		}
		$jsparams = substr($jsparams,0, strrpos($jsparams, ","))."\n";
		$jsparams .= '];'."\n";
		$jsparams .= 'var printoutmode = \''.$mode.'\';'."\n";
		$jsparams .= 'var todoprefix = \''.$prefix.'\';'."\n";
		$jsparams .= 'var todointerval = \''.$interval.'\';'."\n";
		return $jsparams;
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
			} else {
				$column[] = '';
			}
		}
		if(count($ids) === count($column)) {
			array_multisort($column, $sort_order, $ids);
		}
		return $ids;
	}

}
?>
