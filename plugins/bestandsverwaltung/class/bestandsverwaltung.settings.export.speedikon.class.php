<?php
/**
 * bestandsverwaltung_settings_export_speedikon
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

class bestandsverwaltung_settings_export_speedikon
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
		$t = $response->html->template($this->tpldir.'bestandsverwaltung.settings.export.speedikon.html');
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
			$params = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.speedikon.ini');
			$params['tables'] = $form->get_request('tables');
			$error = $this->file->make_ini( $this->profilesdir.'/bestandsverwaltung.export.speedikon.ini', $params );
			if( $error === '' ) {
				$bezeichner = $this->__bezeichner();
				if(is_array($bezeichner)) {

					$dir = $this->profilesdir.'tmp/'.uniqid('dir');
					if (file_exists($dir)) { unlink($dir); }
					$this->file->mkdir($dir);
					// disable regex
					$this->file->regex_filename = null;

					foreach($bezeichner as $k => $b) {
						$result = $this->__attribs($params['tables'], $k, $b['bezeichner_lang']);
						if(is_array($result)) {

							// handle filename special chars
							$name = str_replace('/', '-', $k.'-'.$b['bezeichner_lang']);
							$name = str_replace('\\', '-', $name);
							$name = @iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $name);
							$path = $dir.'/'.$name.'.csv';

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
								break;
							}
						}
					}

					// make zip
					if(!isset($response->error)) {
						// handle zip
						require_once(CLASSDIR.'/lib/archiv/archive.php');
						$file = $this->profilesdir.'tmp/'.uniqid('zip');
						if(function_exists("gzcompress")) {
							$archiv = new zip_file($file);
							$mime   = 'application/zip';
							$fname  = 'speedikon.zip';
						}
						else if(function_exists("gzencode")) {
							$archiv = new gzip_file($file);
							$mime   = 'application/x-compressed-tar';
							$fname  = 'speedikon.tar.gz';
						}
						else if(function_exists("bzopen")) {
							$archiv = new bzip_file($file);
							$mime   = 'application/x-bzip-compressed-tar';
							$fname  = 'speedikon.tar.bz2';
						} else {
							$archiv = new tar_file($file);
							$mime   = 'application/x-tar';
							$fname  = 'speedikon.tar';
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
	 * Bezeichner
	 *
	 * @access private
	 * @return array|string
	 */
	//--------------------------------------------
	function __bezeichner() {
		$return = '';

		$sql  = 'SELECT b.bezeichner_lang, b.bezeichner_kurz ';
		$sql .= 'FROM `bezeichner` as b ';
		$sql .= 'ORDER BY b.bezeichner_lang';
		$bezeichner = $this->db->handler()->query($sql);
		
		if(is_array($bezeichner)) {
			foreach($bezeichner as $b) {
				$return[$b['bezeichner_kurz']] = $b;
			}
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
	function __attribs($tables, $bezeichner, $label) {

		$return = '';
		$k = $this->db->handler->escape($bezeichner);

		$where1  = 'WHERE `bezeichner_kurz` = \'*\' ';
		$where1 .= 'OR (`bezeichner_kurz` = \''.$k.'\' ';
		$where1 .= 'OR `bezeichner_kurz` LIKE \'%,'.$k.'\' ';
		$where1 .= 'OR `bezeichner_kurz` LIKE \''.$k.',%\' ';
		$where1 .= 'OR `bezeichner_kurz` LIKE \'%,'.$k.',%\') ';

		$where2  = 'WHERE ';
		$where2 .= '`bezeichner_kurz` = \''.$k.'\' ';

		$sql = '';
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

			$header['liegenschaft'] = 'Kennzeichen Liegenschaft';
			$header['gebaeude'] = 'Kennzeichen Gebäude';
			$header['ebene'] = 'Kennzeichen Ebene';
			$header['raum'] = 'Kennzeichen Raum';
			$header['parent'] = 'Kennzeichen Vorgänger Anlage';
			$header['id'] = 'Kennzeichen Objekt';
			$header['bezeichner_lang'] = 'Benennung Objekt';
			$header['bezeichner_kurz'] = 'Bauteiltypkennzeichen';

			$filter  = ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'RAUMBUCHID\', wert, NULL )) AS \'RAUMBUCHID\' ';
			$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\'DUMMY\', wert, NULL )) AS \'bezeichner_lang\' ';
			$filter .= ', GROUP_CONCAT(DISTINCT `bezeichner_kurz` ) AS \'bezeichner_kurz\' ';
			foreach($attribs as $a) {
				$filter .= ', GROUP_CONCAT(DISTINCT if( `merkmal_kurz`=\''.$a['merkmal_kurz'].'\', wert, NULL ) ) AS \''.$a['merkmal_kurz'].'\' ';
				$header[$a['merkmal_kurz']] = $a['merkmal_lang'];
			}

			$sql  = 'SELECT `id` ';
			$sql .= $filter;
			$sql .= 'FROM `bestand` ';
			$sql .= $where2;
			$sql .= 'GROUP BY `id`';

			$result = $this->db->handler()->query($sql);
			if(is_array($result)) {
				foreach($result as $k => $r) {

					$head['liegenschaft'] = '';
					$head['gebaeude'] = '';
					$head['ebene'] = '';
					$head['raum'] = '';
					$head['parent'] = '';

					if(
						isset($r['RAUMBUCHID']) &&
						$r['RAUMBUCHID'] !== '' &&
						isset($this->raumbuch->options[$r['RAUMBUCHID']]['label'])
					) {
						$s = explode($this->raumbuch->delimiter, $this->raumbuch->options[$r['RAUMBUCHID']]['label']);
						if(isset($s[0]) && $s[0] !== '') {
							$head['liegenschaft'] = $s[0];
						}
						if(isset($s[1]) && $s[1] !== '') {
							$head['gebaeude'] = $s[1];
						}
						if(isset($s[2]) && $s[2] !== '') {
							$head['ebene'] = $s[2];
						}
						if(isset($s[3]) && $s[3] !== '') {
							$head['raum'] = $s[3];
						}
					}
					unset($result[$k]['RAUMBUCHID']);

					if(isset($label)) {
						$result[$k]['bezeichner_lang'] = $label;
					}
					$result[$k] = array_merge($head, $result[$k]);

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
		$settings = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.export.speedikon.ini');

		$tables = $this->db->select('bestand_index', 'tabelle_kurz,tabelle_lang', null, 'pos');
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

		$form = $response->get_form($this->actions_name, 'speedikon');

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
