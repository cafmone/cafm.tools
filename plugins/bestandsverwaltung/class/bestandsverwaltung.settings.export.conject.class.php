<?php
/**
 * bestandsverwaltung_settings_export_conject
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

class bestandsverwaltung_settings_export_conject
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
		$this->settings    = $controller->settings;
		$this->profilesdir = $controller->profilesdir;

		if($this->response->html->request()->get('debug') !== '' && 
			$this->response->html->request()->get('debug') === 'true' 
		) {
			$this->debug = true;
		} else {
			$this->debug = null;
		}

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/raumbuch.class.php');
		$this->raumbuch = new raumbuch($this->db);
		$this->raumbuch->delimiter = '/';
		$this->raumbuch->options = $this->raumbuch->options();
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
		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.export.conject.html');
		$t->add($response->html->thisfile,'thisfile');
		$t->add($response->form);
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
		$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
		$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
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
		$response = $this->update();
		if(isset($response->msg)) {
			$msg = 'updated';
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		else if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		$settings = $this->controller->settings;
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$params = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.conject.ini');
			$params['gewerk'] = $form->get_request('gewerk');
			$params['tables'] = $form->get_request('tables');
			$error = $this->file->make_ini( $this->profilesdir.'/bestandsverwaltung.export.conject.ini', $params );
			if( $error === '' ) {
				$gewerke = $this->__gewerke($params['gewerk'][0]);

				if(is_array($gewerke)) {

					$dir = $this->profilesdir.'tmp/'.uniqid('dir');
					if (file_exists($dir)) { unlink($dir); }
					$this->file->mkdir($dir);
					// disable regex
					$this->file->regex_filename = null;

					foreach($gewerke as $g) {

						// handle filename special chars
						$name = str_replace('/', '-', $g['label']);
						$name = str_replace('\\', '-', $name);
						$name = @iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $name);
						$path = $dir.'/'.$name.'.csv';

						if(isset($g['bezeichner']) && count($g['bezeichner']) > 0) {
							$result = $this->__attribs($params['tables'], $g['bezeichner'],$g['label']);
							if(is_array($result)) {
								// build string
								$str = pack('H*','EFBBBF');
								foreach($result as $r){
									$str .='"';
									$str .= implode('";"',$r);
									$str .='"';
									$str .= "\r\n";
								}
								$error = $this->file->mkfile($path, $str, 'w+', true);
								if($error !== '') {
									$response->error = $error;
								}
							} 
							else if(is_string($result) && $result !== '') {
								$response->error = $result;
								break;
							}
						}
					}

/*
						$zip = new ZipArchive;
						if ($zip->open($file) === TRUE) {
							$files = $this->file->get_files($dir);
							if(is_array($files)) {
								foreach($files as $f) {
									#if(mb_detect_encoding($name) === 'UTF-8') {
										//iconv("UTF-8", "ASCII",  $name);
										#$f['name'] = mb_convert_encoding($f['name'], "ISO-8859-1");
										$f['name'] = utf8_decode($f['name']);
									#}
									$zip->addFile($f['path'], $f['name']);
								}
							}
							$zip->close();
						}
*/

					if(!isset($response->error)) {
						// handle zip
						require_once(CLASSDIR.'/lib/archiv/archive.php');
						$file = $this->profilesdir.'tmp/'.uniqid('zip');
						if(function_exists("gzcompress")) {
							$archiv = new zip_file($file);
							$mime   = 'application/zip';
							$fname  = 'conject.zip';
						}
						else if(function_exists("gzencode")) {
							$archiv = new gzip_file($file);
							$mime   = 'application/x-compressed-tar';
							$fname  = 'conject.tar.gz';
						}
						else if(function_exists("bzopen")) {
							$archiv = new bzip_file($file);
							$mime   = 'application/x-bzip-compressed-tar';
							$fname  = 'conject.tar.bz2';
						} else {
							$archiv = new tar_file($file);
							$mime   = 'application/x-tar';
							$fname  = 'conject.tar';
						}

						$archiv->set_options(array('basedir' => $dir, 'overwrite' => 1, 'level' => 9, 'storepaths' => 0));
						$archiv->add_files($dir);
						$archiv->create_archive();

						if(!isset($archiv->error) || (is_array($archiv->error) && count($archiv->error) < 1)) {
							$size = filesize($file);

							header("Pragma: public");
							header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
							header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
							header("Cache-Control: must-revalidate");
							header("Content-type: $mime");
							header("Content-Length: ".$size);
							header("Content-disposition: attachment; filename=$fname");
							header("Accept-Ranges: ".$size);

							flush();
							readfile($file);
							$this->file->remove($dir, true);
							$this->file->remove($file);
							exit(0);
						} else {
							$this->file->remove($dir, true);
							$this->file->remove($file);
							if(is_array($archiv->error)) {
								$response->error = implode('<br>', $archiv->error);
							}
						}
					}
				} else {
					$response->error = $list;
				}
			} else {
				$response->error = $error;
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Gewerke
	 *
	 * @access private
	 * @return array|string
	 */
	//--------------------------------------------
	function __gewerke($gewerk) {
		$return = '';

		$gewerk = $this->db->handler->escape($gewerk);
		$sql  = 'SELECT g.gewerk_lang, g.gewerk_kurz, g.parent, b.bezeichner_kurz, b.bezeichner_lang ';
		$sql .= 'FROM `gewerke` as g, `bezeichner` as b, `gewerk2bezeichner` as g2b ';
		$sql .= 'WHERE b.bezeichner_kurz=g2b.bezeichner_kurz ';
		$sql .= 'AND (g.gewerk_kurz=\''.$gewerk.'\' ';
		$sql .= 'OR g.parent=\''.$gewerk.'\' ';
		$sql .= 'OR g.parent LIKE \'%,'.$gewerk.'\' ';
		$sql .= 'OR g.parent LIKE \'%,'.$gewerk.',%\' ';
		$sql .= 'OR g.parent LIKE \''.$gewerk.',%\') ';
		$sql .= 'AND g.gewerk_kurz=g2b.gewerk_kurz ';
		$sql .= 'ORDER BY b.bezeichner_lang';
		$bez = $this->db->handler()->query($sql);

		if(is_array($bez)) {
			$return = array();
			foreach($bez as $b) {
				$return[$b['gewerk_kurz']]['label'] = $b['gewerk_lang'];
				$return[$b['gewerk_kurz']]['bezeichner'][$b['bezeichner_kurz']] = $b['bezeichner_lang'];
			}
		} else {
			$return = $bez;
		}

		return $return;
	}

	//--------------------------------------------
	/**
	 * Attribs
	 *
	 * @access private
	 * @return array|string
	 */
	//--------------------------------------------
	function __attribs($tables, $bezeichner, $gewerk) {
		$return = '';

		$i = 0;
		$sql = '';
		$where1 = 'WHERE `bezeichner_kurz` = \'*\' ';
		$where2 = 'WHERE ';
		foreach($bezeichner as $k => $b) {
			$k = $this->db->handler->escape($k);
			$where1 .= 'OR (`bezeichner_kurz` = \''.$k.'\' ';
			$where1 .= 'OR `bezeichner_kurz` LIKE \'%,'.$k.'\' ';
			$where1 .= 'OR `bezeichner_kurz` LIKE \''.$k.',%\' ';
			$where1 .= 'OR `bezeichner_kurz` LIKE \'%,'.$k.',%\') ';
			if($i !== 0) {
				$where2 .= 'OR ';
			}
			$where2 .= '`bezeichner_kurz` = \''.$k.'\' ';
			$i = 1;
		}

		$i = 0;
		foreach($tables as $table) {
			if($table !== 'standort') {
				$table = $this->db->handler->escape($table);
				if($i !== 0) {
					$sql .= 'UNION ALL ';
				}
				$sql .= 'SELECT `merkmal_kurz`, `merkmal_lang` FROM `bestand_'.$this->db->handler->escape($table).'` '.$where1.' ';
				$i = 1;
			}
		}

		$attribs = $this->db->handler()->query($sql);

		if(is_array($attribs)) {



			$header['external_01']     = 'Objekt-Id';
			$header['RAUMBUCHID']      = 'Standort';
			$header['external_02']     = 'Logikbaum-Objektbezeichnung';
			$header['external_03']     = 'Logikbaum-Objekt-Id';
			$header['external_04']     = 'Symbolname';
			$header['external_05']     = 'SymbolId';
			$header['external_06']     = 'Bezeichnung';
			$header['external_07']     = 'Zuständiger Sachbearbeiter';
			$header['bezeichner_kurz'] = 'AKS Anlagenkennzeichen';
			$header['external_08']     = 'Barcode';
			$header['gewerk']          = 'Anlagenkennung';
			$header['external_09']     = 'Typ/ Gerätenummer';
			$header['external_10']     = 'Baujahr';
			$header['external_11']     = 'Gewährleistung bis';
			$header['external_12']     = 'vor Gewährleistungsende geprüft';
			$header['external_13']     = 'Objekt-Bezeichnung';
			$header['external_14']     = 'Service Level';
			$header['external_15']     = 'Hersteller';
			$header['bezeichner_lang'] = 'bezeichner_lang';
			$header['id']              = 'ID';

			$body = array();
			foreach($header as $k => $v) {
				$body[$k] = '';
			}

			$filter  = ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'RAUMBUCHID\', wert, NULL )) AS \'RAUMBUCHID\' ';
			$filter .= ', GROUP_CONCAT(DISTINCT `bezeichner_kurz` ) AS \'bezeichner_kurz\' ';
			$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'DUMMY\', wert, NULL )) AS \'bezeichner_lang\' ';
			$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'DUMMY\', wert, NULL )) AS \'gewerk\' ';
			foreach($attribs as $a) {
				$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\''.$a['merkmal_kurz'].'\', wert, NULL ) ) AS \''.$a['merkmal_kurz'].'\' ';
				$header[$a['merkmal_kurz']] = $a['merkmal_lang'];
			}

			$sql  = 'SELECT `id` ';
			$sql .= $filter;
			$sql .= 'FROM `bestand` ';
			$sql .= $where2;
			$sql .= 'GROUP BY `id`';

			$query  = $this->db->handler()->query($sql);
			$result = array();
			if(is_array($query)) {
				foreach($query as $k => $r) {
					$tmp = $body;
					$tmp = array_merge($tmp, $query[$k]);
					$tmp['gewerk'] = $gewerk;
					if(isset($this->raumbuch->options[$this->raumbuch->indexprefix.$r['RAUMBUCHID']]['label'])) {
						$tmp['RAUMBUCHID'] = $this->raumbuch->options[$this->raumbuch->indexprefix.$r['RAUMBUCHID']]['label'];
					} else {
						$tmp['RAUMBUCHID'] = $r['RAUMBUCHID'];
					}
					if(isset($bezeichner[$r['bezeichner_kurz']])) {
						$tmp['bezeichner_lang'] = $bezeichner[$r['bezeichner_kurz']];
					} else {
						$tmp['bezeichner_lang'] = $r['bezeichner_kurz'];
					}
					$result[$k] = $tmp;
					/// grrrr
					foreach($query[$k] as $key => $value) {
						if(
							$key !== 'gewerk' && 
							$key !== 'RAUMBUCHID' && 
							$key !== 'bezeichner_kurz' 
						) {
							if(isset($value) && $value !== '') {
								$result[$k][$key] = $header[$key].': '.$value;
							}
						}
					}
				}
				array_unshift($result, $header);
				$return = $result;
			} else {
				$return = $result;
			}
		}
		return $return;
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
		$settings = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.conject.ini');

		$gewerke = $this->gewerke->options();
		if(is_array($gewerke)) {
			array_unshift($gewerke, array('id' => '', 'label' => ''));
			$d['gewerk']['label']                       = 'Gewerk';
			$d['gewerk']['required']                    = true;
			$d['gewerk']['css']                         = 'autosize pull-right clearfix';
			$d['gewerk']['object']['type']              = 'htmlobject_select';
			$d['gewerk']['object']['attrib']['index']   = array('id','label');
			$d['gewerk']['object']['attrib']['name']    = 'gewerk[]';
			$d['gewerk']['object']['attrib']['id']      = 'filter_gewerk';
			$d['gewerk']['object']['attrib']['options'] = $gewerke;
			$d['gewerk']['object']['attrib']['style']   = 'width: 300px;';
			$d['gewerk']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \'Gewerk\'); return false;"';
			if(isset($settings['gewerk'])) {
				$d['gewerk']['object']['attrib']['selected'] = array($settings['gewerk'][0]);
			}
		} else {
			$d['gewerk'] = '';
		}

		// export
		#$raumbuch = $this->raumbuch->options;


		$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang', null, 'pos');
		#if(is_array($raumbuch)) {
		#	array_unshift($tables,array('tabelle_kurz'=>'standort','tabelle_lang'=>'Standort'));
		#}

		$d['attribs']['label']                        = 'Attribute';
		$d['attribs']['required']                     = true;
		$d['attribs']['css']                          = 'autosize pull-right';
		$d['attribs']['style']                        = 'clear:both;';
		$d['attribs']['object']['type']               = 'htmlobject_select';
		$d['attribs']['object']['attrib']['index']    = array('tabelle_kurz','tabelle_lang');
		$d['attribs']['object']['attrib']['name']     = 'tables[]';
		$d['attribs']['object']['attrib']['multiple'] = true;
		$d['attribs']['object']['attrib']['options']  = $tables;
		$d['attribs']['object']['attrib']['size']     = count($tables);
		$d['attribs']['object']['attrib']['style']    = 'width: 300px;';
		if(isset($settings['tables'])) {
			$d['attribs']['object']['attrib']['selected'] = $settings['tables'];
		}

		$form = $response->get_form($this->actions_name, 'conject');

		$submit = $form->get_elements('submit');
		$s = $this->response->html->button();
		$s->type  = 'submit';
		$s->css   = $submit->css;
		$s->name  = $submit->name;
		$s->label = $submit->value;
		$s->value = $submit->value;

		$form->add($s,'submit');

		$form->add('','cancel');
		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

}
?>
