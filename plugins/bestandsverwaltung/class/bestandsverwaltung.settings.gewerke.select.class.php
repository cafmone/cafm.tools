<?php
/**
 * bestandsverwaltung_settings_gewerke_select
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

class bestandsverwaltung_settings_gewerke_select
{
/**
* mode
* @access public
* @var string
*/
var $mode;

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
		$this->user       = $controller->user->get();
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->settings   = $controller->settings;

		// Translation
		#$anlagen = $this->db->select('anlage2bezeichner', array('anlage_kurz','bezeichner_kurz'));
		#if(is_array($anlagen)) {
		#	foreach($anlagen as $v) {
		#		$a[$v['bezeichner_kurz']] = $v['anlage_kurz'];
		#	}
		#	if(isset($a)) {
		#		$this->anlagen = $a;
		#	}
		#}

		if($this->response->html->request()->get('debug') !== '' && 
			$this->response->html->request()->get('debug') === 'true' 
		) {
			$this->debug = true;
		} else {
			$this->debug = null;
		}

		$clip =$this->response->html->request()->get('clip');
		if($clip !== '' && $clip !== 'true') {
			$this->response->add('clip', $clip);
			$this->clip = $clip;
		} else {
			$this->clip = 'true';
		}

		// unset params for clip href
		$params = $this->response->get_array();
		unset($params['clip']);
		$this->params = $this->response->get_params_string($params);

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);
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
		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.gewerke.select.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);
		$t->add($response->table, 'table');
		$t->add($response->count, 'count');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
		if(isset($this->label)) {
			$t->add($this->label, 'label');
		} else {
			$t->add('', 'label');
		}

		if(!isset($this->mode)) {
			$a = $response->html->a();
			if(isset($this->debug) && $this->debug === true) {
				$a->href = $response->get_url($this->actions_name, 'select').'&debug=false';
			} else {
				$a->href = $response->get_url($this->actions_name, 'select').'&debug=true';
			}
			$a->css   = 'btn btn-default icon icon-info debug';
			$a->customattribs = 'data-toggle="tooltip"';
			$a->title = $this->lang['button_title_debug'];
			$a->handler = 'onclick="phppublisher.wait();"';
			$t->add($a, 'debug');

			$a        = $response->html->a();
			$a->href  = $this->response->get_url($this->actions_name, 'pdf');
			$a->customattribs = 'data-toggle="tooltip"';
			$a->title = $this->lang['button_title_download'];
			$a->css   = 'btn btn-default icon icon-download';
			$t->add($a,'pdf');

			$a        = $response->html->a();
			$a->href  = 'javascript:gewerk.open(\'\',\'insert\');';
			$a->customattribs = 'data-toggle="tooltip"';
			$a->title = $this->lang['button_title_add'];
			$a->css   = 'btn btn-default icon icon-plus insert noprint';
			$t->add($a,'insert');

			$t->add('','selector');

			$a = $response->html->a();
			$a->label = '';
			$a->customattribs = 'data-toggle="tooltip"';
			if(isset($this->clip) && $this->clip !== 'false') {
				$a->title = $this->lang['button_title_unclip'];
				$a->css   = 'btn btn-default icon icon-menu-down';
				$a->href = $response->get_url($this->actions_name, 'select').'&clip=false';
			} else {
				$a->title = $this->lang['button_title_clip'];
				$a->css   = 'btn btn-default icon icon-menu-right';
				$a->href = $response->get_url($this->actions_name, 'select').'&clip=true';
			}
			$a->handler = 'onclick="phppublisher.wait();"';
			$t->add($a, 'clip');

		} 
		else if(isset($this->mode) && $this->mode === 'todos')  {
			$t->add('','pdf');
			$t->add('','debug');
			$t->add('','insert');

			$form = $this->response->get_form($this->actions_name, 'taetigkeiten', false);
			$form->display_errors = false;

			$d['prefix']['object']['type']              = 'htmlobject_select';
			$d['prefix']['object']['attrib']['index']   = array('prefix','lang');
			$d['prefix']['object']['attrib']['name']    = 'prefix';
			$d['prefix']['object']['attrib']['options'] = $this->tables;
			$d['prefix']['object']['attrib']['handler'] = 'onchange="this.form.submit(); phppublisher.wait(\'Loading ...\'); return false;"';
			if(isset($this->prefix)) {
				$d['prefix']['object']['attrib']['selected'] = array($this->prefix);
			}

			$form->add($d);
			$t->add($form->get_string(),'selector');
			$t->add('', 'clip');
		}
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('filter_' => 'filter'));

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
		$settings = $this->controller->settings;

		if(isset($this->tables) && isset($this->prefix)) {
			$gewerke = $this->gewerke->listGewerke($this->tables[$this->prefix]['bezeichner'], null, '', true);
		} else {
			$gewerke = $this->gewerke->listGewerke(null, null, $this->clip, true);
			// handle missing clip
			if(!isset($gewerke['clip'])) {
				$gewerke['clip'] = array();
			}
		}

#### TODO

		$div = $this->response->html->div();
		$result = array();
		$str = '';
		$count = '';

		// Debug Mode
		if(!isset($this->mode) && $this->debug === true) {

			// Get Unused
			$bez = '';
			foreach($gewerke as $k => $g) {
				if($k !== 'clip') {
					if($bez !== '') {
						$bez .= ' OR ';
					}
					$bez .= $this->gewerke->gewerk2sql($k);
				}
			}
			if($bez !== '') {
				$sql  = 'SELECT * FROM bezeichner ';
				$sql .= 'WHERE NOT (';
				$sql .= $bez;
				$sql .= ') ';
				$sql .= 'GROUP by bezeichner_kurz ';
				$sql .= 'ORDER BY `bezeichner_lang` ';
				$unused = $this->db->handler()->query($sql);
				if(is_array($unused)) {
					$div->add('<div style="margin: 0 40px 0 0;">');
					$div->add('<h4>Unzugeordnete Bezeichner <small>(gewerke)</small></h4>');
					$str = '';
					$i = 1;
					foreach($unused as $u) {
						$str .= '<div style="clear:both;">';
						$str .= '<div style="float:left;width:40px;text-align:right;padding:0 15px 0 0;">'.$i.'.</div>';
						$str .= '<div style="float:left;">'.$u['bezeichner_lang'].' ('.$u['bezeichner_kurz'].')</div>';
						$str .= '</div>';
						$i++;
					}
					$div->add($str);
					$div->add('<div class="floatbreaker">&#160;</div>');
					$div->add('</div>');
				}
			}

			// missing gewerk

				$sql  = 'SELECT `g2b`.`row`, `g2b`.`gewerk_kurz`, `g2b`.`bezeichner_kurz` ';
				$sql .= 'FROM `gewerk2bezeichner` AS g2b ';
				$sql .= 'LEFT JOIN `gewerke` AS g ON g2b.gewerk_kurz = g.gewerk_kurz ';
				$sql .= 'WHERE g.gewerk_kurz IS NULL ';
				$sql .= 'ORDER BY `g2b`.`gewerk_kurz` ';
				$double = $this->db->handler()->query($sql);

				if(is_array($double)) {
					$div->add('<div style="margin: 10px 0 0 0;">');
					$div->add('<h4>Fehlende Gewerke <small>(gewerk2bezeichner)</small></h4>');
					$str = '';
					$i = 1;
					foreach($double as $d) {
						$str .= '<div style="clear:both;">';
						$str .= '<div style="float:left;width:40px;text-align:right;padding:0 15px 0 0;">'.$i.'.</div>';
						$str .= '<div style="float:left;">'.$d['gewerk_kurz'].' - '.$d['bezeichner_kurz'].' <small>(row '.$d['row'].')</small></div>';
						$str .= '</div>';
						$i++;
					}
					$div->add($str);
					$div->add('<div class="floatbreaker">&#160;</div>');
					$div->add('</div>');
				}


			// Get double

				$sql  = 'SELECT ';
				$sql .= '`g`.`bezeichner_kurz` as kurz, ';
				$sql .= '`b`.`bezeichner_lang` as lang, ';
				$sql .= 'COUNT(*) as count ';
				$sql .= 'FROM ';
				$sql .= '`gewerk2bezeichner` as g, ';
				$sql .= '`bezeichner` as b ';
				$sql .= 'WHERE `g`.`bezeichner_kurz`=`b`.`bezeichner_kurz` ';
				$sql .= 'GROUP BY `g`.`bezeichner_kurz` ';
				$sql .= 'HAVING COUNT(*) > 1 ';
				$sql .= 'ORDER BY `b`.`bezeichner_lang` ';
				$double = $this->db->handler()->query($sql);
				if(is_array($double)) {
					$div->add('<div style="margin: 10px 0 0 0;">');
					$div->add('<h4>Doppelte Bezeichner <small>(gewerk2bezeichner)</small></h4>');
					$str = '';
					$i = 1;
					foreach($double as $d) {
						$str .= '<div style="clear:both;">';
						$str .= '<div style="float:left;width:40px;text-align:right;padding:0 15px 0 0;">'.$i.'.</div>';
						$str .= '<div style="float:left;">'.$d['count'].' x '.$d['lang'].' ('.$d['kurz'].')</div>';
						$str .= '</div>';
						$i++;
					}
					$div->add($str);
					$div->add('</div>');
				}


		} else {
			if(is_array($gewerke)) {
				$count = 0;
				$div->add('<ol class="level1" style="clear:both;">');

				$i = 0;
				foreach($gewerke as $k => $g) {
					// handle gewerke[clip]
					if($k === 'clip') {
						continue;
					}
					$id = uniqid('level2');
					// if mode is set and no bzeichner found continue
					if(isset($this->mode) && $this->mode === 'todos') {
						if(isset($g['sql'])) {
							if(is_array($g['sql'])) {
								$bezeichner = implode($this->gewerke->delimiter, $g['sql']);
							} else {
								$bezeichner = $g['sql'];
							}
							$bezeichner = explode($this->gewerke->delimiter, $bezeichner);
						} else {
							continue;
						}
					}

					if(!isset($this->mode)) {
						if(
							(!isset($this->clip) || 
							$this->clip === 'false' || 
							in_array($k,$gewerke['clip'])) || 
							($this->clip === 'true' && $i === 0)
						) {
							$li  = '<li class="level1" id="'.$k.'">';
							$li .= $g['label'];
							$li .= '<span style="position: relative;" class="noprint">';
## TODO js selector ?
							$li .= '<a class="edit icon icon-settings" ';
							$li .= 'onmouseenter="btoggle(\''.$id.'x\');" ';
							$li .= 'onmouseout="btoggle(\''.$id.'x\',\'none\');">';
							$li .= '</a>';
							$li .= '<div class="gewerkfunctions" id="'.$id.'x" ';
							$li .= 'onmouseout="btoggle(\''.$id.'x\', \'none\');" ';
							$li .= 'onmouseover="btoggle(\''.$id.'x\', \'block\');"';
							$li .= '>';
							$li .= '<a href="javascript:gewerk.open(\''.$k.'\',\'insert\');">insert</a>';
							$li .= '<a href="javascript:gewerk.open(\''.$k.'\',\'update\');">edit</a>';
							$li .= '<a href="javascript:gewerk.open(\''.$k.'\',\'delete\');">remove</a>';
							#$li .= '<a href="javascript:bezeichner.open(\''.$k.'\',\'insert\');">+ Bezeichner</a>';
							#if(isset($g['bezeichner'])){
							#	$li .= '<a href="javascript:bezeichner.open(\''.$k.'\',\'delete\');">- Bezeichner</a>';
							#}
							$li .= '</div>';
							$li .= '</span>';
							$li .= '</li>';

							$div->add($li);
							$div->add('<ol class="level2 box" id="'.$id.'" style="display:block;">');
							// Untergewerke
							foreach($g as $k1 => $g1) {
								if($k1 !== 'label' && $k1 !== 'bezeichner' && $k1 !== 'sql') {
									$d = $this->__level($k1, $g1, 2, $k, $gewerke['clip']);
									if($d !== '') {
										$div->add($d->get_string());
									}
								}
							}
							$div->add('</ol>');
						} else {
							$a          = $this->response->html->a();
							$a->label   = '';
							$a->css     = 'btn btn-default btn-sm icon icon-menu-right noprint';
							$a->href    = $this->response->html->thisfile.$this->params.'&clip='.$k.'#'.$k;
							$a->handler = 'onclick="phppublisher.wait();"';

							$li  = '<li class="level1" id="'.$k.'">';
							$li .= $g['label'].' ';
							$li .= $a->get_string();
							$li .= '</li>';

							$div->add($li);
						}
					}
					// handle todos
					else if($this->mode === 'todos') {
						$li  = '<li class="level1">';
						$li .= $g['label'];
						$li .= '</li>';
						$div->add($li);
						$div->add('<ol class="level2 box" id="'.$id.'" style="display:block;">');
						// Untergewerke
						foreach($g as $k1 => $g1) {
							if($k1 !== 'label' && $k1 !== 'bezeichner' && $k1 !== 'sql') {
								$d = $this->__level($k1, $g1, 2, $k);
								if($d !== '') {
									$div->add($d->get_string());
								}
							}
						}
						$div->add('</ol>');
					}
					$i++;
				}
				$div->add('</ol>');
			}
		}
		$response->count = $count;
		$response->table = $div;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Generate Levels
	 *
	 * @access protected
	 * @param string $key
	 * @param array $gewerk
	 * @param integer $gewerk
	 * @param string $path
	 * @return string
	 */
	//--------------------------------------------
	function __level($key, $gewerk, $level, $path=null, $clip=array()) {

#$this->response->html->help($gewerk);

		$count = 0;
		$div = $this->response->html->div();

		// if mode is set and no bzeichner found return empty
		if(isset($this->mode)) {
			if(isset($gewerk['sql'])) {
				if(is_array($gewerk['sql'])) {
					$bezeichner = implode($this->gewerke->delimiter, $gewerk['sql']);
				} else {
					$bezeichner = $gewerk['sql'];
				}
				$bezeichner = explode($this->gewerke->delimiter, $bezeichner);
			} else {
				return '';
			}
		}

		$id   = uniqid('level'.$level);
		$str  = '<li class="level'.($level).'" id="'.$key.'">';
		$str .= $gewerk['label'];

		$continue = true;

		// handle mode
		if(!isset($this->mode)) {
			if(
				!isset($this->clip) || 
				$this->clip === 'false' || 
				in_array($key,$clip)
			) {

				$str .= '<span style="position: relative;" class="noprint">';
				$str .= '<a class="edit icon icon-settings" ';
				$str .= 'onmouseover="btoggle(\''.$id.'x\');" ';
				$str .= 'onmouseout="btoggle(\''.$id.'x\',\'none\');" ';
				$str .= '></a>';
				// New Link
				if(isset($path)) {
					$path = $path.','.$key;
				} else {
					$path = $key;
				}
				$str .= '<div class="gewerkfunctions" id="'.$id.'x" ';
				$str .= 'onmouseout="btoggle(\''.$id.'x\', \'none\');" ';
				$str .= 'onmouseover="btoggle(\''.$id.'x\', \'block\');" ';
				$str .= '>';
				if($level < 8) {
					$str .= '<a href="javascript:gewerk.open(\''.$path.'\',\'insert\');">insert</a>';
				}
				$str .= '<a href="javascript:gewerk.open(\''.$key.'\',\'update\');">edit</a>';
				$str .= '<a href="javascript:gewerk.open(\''.$key.'\',\'delete\');">remove</a>';
				$str .= '<a href="javascript:bezeichner.open(\''.$key.'\',\'insert\');">+ Bezeichner</a>';
				if(isset($gewerk['bezeichner'])){
					$str .= '<a href="javascript:bezeichner.open(\''.$key.'\',\'delete\');">- Bezeichner</a>';
				}
				$str .= '</div>';
				$str .= '</span>';
			} else {
				$a          = $this->response->html->a();
				$a->label   = '';
				$a->css     = 'btn btn-default btn-sm icon icon-menu-down noprint';
				$a->href    = $this->response->html->thisfile.$this->params.'&clip='.$key.'#'.$key;
				$a->handler = 'onclick="phppublisher.wait();"';

				$str .= $a->get_string();

				$continue = false;
			}
		}
		$str .= '</li>'."\n";
		$str .= '<ol class="level'.($level+1).' box" id="'.$id.'" style="display:block;">';

		// Bezeichner
		if(isset($gewerk['bezeichner']) && $continue === true) {
			$tmp = '';
			foreach($gewerk['bezeichner'] as $x => $b) {
				if(!isset($this->mode)) {

					#$anlage = 'dummy';
					#if(isset($this->anlagen[$x])) {
					#	$anlage = $this->anlagen[$x];
					#}

					$edit = $this->response->html->a();
					$edit->label = '';
					$edit->css   = 'icon icon-plus';
					$edit->handler = 'onclick="phppublisher.wait();"';
					$edit->title = "New device";
					$edit->href  = $this->controller->controller->controller->response->get_url($this->controller->controller->controller->actions_name, 'recording');
					$edit->href .= '&bestand_recording_action=insert&bezeichner='.$x;
					$tmp .= '<div class="'.($level).'">';
					$tmp .= '<div style="float:left;line-height:25px;">'.$b.'</div>';
					$tmp .= '<div style="float:left;line-height:25px;margin: 0 10px 0 8px;" class="noprint">';
					$tmp .= $edit->get_string();
					$tmp .= '<a class="icon icon-tasks todos" href="javascript:todospicker.init(\''.$x.'\');" title="todos"></a> ';
					$tmp .= '<a class="icon icon-info" id="'.uniqid('h').'" tabindex="0" title="'.$x.'" data-toggle="popover" data-trigger="focus" role="button" data-placement="right" onclick="bezeichnerhelp.init(this);"></a> ';
					$tmp .= '</div>';
					$tmp .= '<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&#160;</div>';
					$tmp .= '</div>'."\n";
				} else {
					// handle todos
					if(in_array($x, $this->tables[$this->prefix]['bezeichner'])) {
						$id = uniqid('box');
						$tmp .= '<div>'.$b.'</div>';
						$tmp .= '<div id="'.$id.'"></div>';
						$tmp .= '<script>todos.init(\''.$id.'\',\''.$this->prefix.'\',\''.$x.'\',\'false\');</script>';
					}
				}
			}
			if($tmp !== '') {
				#$str .= '<div class="'.($level).' box" id="'.$id.'" style="display:block;">'."\n";
				$str .= '<div>'."\n";
				$str .= $tmp;
				$str .= '</div>'."\n";
				$str .= '<div style="line-height:0px;height:0px;clear:both;" class="floatbreaker">&#160;</div>';
				#$str .= '</div>'."\n";
			}
		}

		// Untergewerke
		if($continue === true) {
			foreach($gewerk as $k => $g) {
				if($k !== 'label' && $k !== 'bezeichner'  && $k !== 'sql') {
					$d = $this->__level($k, $g, ($level+1), $path, $clip);
					if($d !== '') {
						$str .= $d->get_string();
					}
				}
			}
		}

		$str .= '</ol>';
		$div->add($str);
		return $div;
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
		if(!isset($this->mode)) {
			#$label = $this->db->select('bestand_gebaeude', 'merkmal_lang', array('merkmal_kurz' => $this->settings['recording']['WE']));
			#if(isset($label[0]['merkmal_lang'])) {
			#	$d['filter_we']['label']                         = $label[0]['merkmal_lang'];
			#	$d['filter_we']['object']['type']                = 'htmlobject_input';
			#	$d['filter_we']['object']['attrib']['name']      = 'filter[WE]';
			#	$d['filter_we']['object']['attrib']['maxlength'] = 255;
			#} else {
			#	$d['filter_we'] = 'Error: '.$this->settings['recording']['WE'].'not found in table gebaeude';
			#}
			$d['filter_xy'] = '';
		} else {
			$d['filter_xy'] = '';
		}

		$form = $response->get_form($this->actions_name, 'gewerke');
		$form->add('','cancel');
		$form->add('','submit');
		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
