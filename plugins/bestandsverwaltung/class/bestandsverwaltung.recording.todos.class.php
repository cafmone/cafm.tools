<?php
/**
 * bestandsverwaltung_recording_todos
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

use setasign\Fpdi;
require_once(CLASSDIR.'lib/pdf/tcpdf/tcpdf.php');
require_once(CLASSDIR.'lib/pdf/fpdi/src/autoload.php');

class bestandsverwaltung_recording_todos
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestand_recording_todos';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_recording_msg';
/**
*  date as formated string
*  @access public
*  @var string
*/
var $date_format = "Y-m-d H:i";

var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->settings = $controller->settings;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->plugins = $this->file->get_ini(PROFILESDIR.'/plugins.ini');

		$tables = $this->db->select('bestand_index', array('tabelle_kurz','tabelle_lang'), null, 'pos');
		if(is_array($tables)) {
			foreach($tables as $table) {
				$this->tables[$table['tabelle_kurz']] = $table['tabelle_lang'];
			}
		} else {
			$this->tables = array();
		}

		require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
		$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);

### TODO diffrent path

		$this->pdftpl = $this->profilesdir.'cafm.one/templates/Checklist.pdf';

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		$prefix = $this->response->html->request()->get('prefix');
		// handle empty prefix
		if($prefix === '') {
			$tables = $this->taetigkeiten->tables();
			// no tables => this should not happen
			if(is_array($tables)) {
				$this->prefix = implode(',',array_keys($tables));
			} else {
				if(is_string($tables)) {
					echo $tables;
				} else {
					var_dump($tables);
				}
				exit;
			}
		}
		else if (is_array($prefix)) {
			$this->prefix = implode(',',array_keys($prefix));
		} else {
			$this->prefix = $prefix;
		}

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name.'[todos]');
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 

		if(!isset($this->db->type)) {
			$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			switch( $this->action ) {
				case '':
				default:
				case 'pdf':
					$content = $this->pdf( true );
				break;
			}
		}

		return '';
	}

	//--------------------------------------------
	/**
	 * Todos pdf
	 *
	 * @access public
	 * @return htmlobject_tabs
	 */
	//--------------------------------------------
	function pdf($visible = false) {
		if($visible === true) {

			$strbez = '';
			$strid  = '';
			$interval = $this->response->html->request()->get('interval');
			$bezeichner = $this->db->handler()->escape($this->response->html->request()->get('bezeichner'));
			$hide_disabled = $this->response->html->request()->get('hide_disabled');


			if($bezeichner !== '') {
				$tmp = $this->db->select('bezeichner','bezeichner_lang',array('bezeichner_kurz'=>$bezeichner));
				if(isset($tmp[0]['bezeichner_lang'])) {
					$strbez = $tmp[0]['bezeichner_lang'].' ('.$bezeichner.')'."\n\n";
				}
				$gewerke = $this->gewerke->bezeichner2gewerk($bezeichner);
			}
			$id = $this->response->html->request()->get('id');
			if($id !== '') {
				$strid .= 'ID: '.$id."\n\n";
			}

			// handle attribs
			$attribs = array();
			$fields  = array();
			if($id !== '') {
				foreach($this->tables as $k => $t) {
					if($k !== 'prozess') {
						$attribs[$k] = $this->response->html->request()->get($k, true);
					}
				}
				// todos
				$fields = $this->response->html->request()->get('TODO', true);
				if(!isset($fields) || $fields === '') {
					$fields = array();
				} else {
					$attribs['TODO'] = $fields;
				}
			}

			if(is_array($attribs)) {
				// User
				$user = $this->user->get();
				// Date
				$now = time();


				// initiate PDF
				$pdf = new PDF();
				#if($id !== '') {
				#	$pdf->id = 'ID: '.$id.'  Ersteller: '.$user['login'].' '.date('Y-m-d',$now);
				#} 
				#else {
				#	$pdf->id = 'Ersteller: '.$user['login'];
				#}
				$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT);
				$pdf->SetAutoPageBreak(false, 50);

				$pdf->SetAuthor('CAFM.ONE');
				$pdf->SetTitle('Checkliste '.$strbez);
				$pdf->SetCreator('CAFM.ONE');
				#$pdf->SetProtection(array('modify','copy'));
				$pdf->SetXY(PDF_MARGIN_LEFT, 5);

				$pdf->AddPage();
				$pageCount = $pdf->setSourceFile($this->pdftpl);
				$pdf->useTemplate($pdf->importPage(1));

				$pdf->Bookmark('Anlage', 0, 0);

				$pdf->SetFont('', 'B', 10);
				$pdf->Write(6, $strbez);
				$pdf->SetFont('', '', 10);

				$pdf->SetFont('', 'B', 10);
				$pdf->Write(5, 'Ersteller der Arbeitskarte: ');
				$pdf->SetFont('', '', 10);
				$pdf->Write(5, $user['login']."\n");
				$pdf->SetFont('', 'B', 10);
				$pdf->Write(5, 'Datum: ');
				$pdf->SetFont('', '', 10);
				$pdf->Write(5, date('Y-m-d H:i:s',$now)."\n");

				// Standort
				if(in_array('standort', $this->plugins)) {
					$standort = $this->response->html->request()->get('SYSTEM', true);
					if(isset($standort['RAUMBUCHID'])) {
						require_once(CLASSDIR.'plugins/standort/class/standort.class.php');
						$raumbuch = new standort($this->db, $this->file);
						$raumbuch->options = $raumbuch->options();
						$pdf->SetFont('', 'B', 10);
						$pdf->Write(5, 'Standort: ');
						$pdf->SetFont('', '', 10);
						if(isset($raumbuch->options[$raumbuch->indexprefix.$standort['RAUMBUCHID']])) {
							$pdf->Write(5, $raumbuch->options[$raumbuch->indexprefix.$standort['RAUMBUCHID']]['label'].' ('.$standort['RAUMBUCHID'].')'."\n");
						} else {
							$pdf->Write(5, $standort['RAUMBUCHID']."\n");
						}
					}
				}
				$pdf->Write(5, "\n");

				// Id
				$pdf->Write(5, $strid);

				// handle attribs - only available
				foreach($this->tables as $k => $t) {
					$sql  = 'SELECT `merkmal_kurz`,`merkmal_lang`, `datentyp` ';
					$sql .= 'FROM `bestand_'.$k.'` ';
					$sql .= 'WHERE (';
					$sql .= '`bezeichner_kurz` = \'*\' ';
					$sql .= 'OR `bezeichner_kurz` = \''.$bezeichner.'\' ';
					$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$bezeichner.'\' ';
					$sql .= 'OR `bezeichner_kurz` LIKE \'%,'.$bezeichner.',%\' ';
					$sql .= 'OR `bezeichner_kurz` LIKE \''.$bezeichner.',%\') ';
					$res = $this->db->handler->query($sql);
					if(is_array($res)) {
						foreach($res as $r) {
							if(isset($r['merkmal_lang']) && $r['merkmal_lang'] !== ''){ 
								$table[$k][$r['merkmal_kurz']]['label'] = $r['merkmal_lang'];
							} else {
								$table[$k][$r['merkmal_kurz']]['label'] = $r['merkmal_kurz'];
							}
							$table[$k][$r['merkmal_kurz']]['type'] = $r['datentyp'];
						}
					}
				}

				// handle options
				$opts = $this->db->select('bestand_options',array('row','value'));
				if(is_array($opts)) {
					$options = array();
					foreach($opts as $option) {
						$options[$option['row']] = $option['value'];
					}
					unset($opts);
				}

				foreach($attribs as $key => $value) {
					if(is_array($value)) {

						if(isset($table[$key])) {
							$pdf->SetFont('', 'B', 10);
							$pdf->Write(5, $this->tables[$key]."\n");
						}
						else if($key === 'TODO') {
							$pdf->SetFont('', 'B', 10);
							$pdf->Write(5, 'TODO'."\n");
						}
						$pdf->SetFont('', '', 10);

						foreach($value as $k => $v) {
							if($v !== '') {
								if(is_array($v)) {
									$v = implode(', ', $v);
								}
								if( isset($table[$key]) && isset($table[$key][$k]) ) {
									$label = $table[$key][$k]['label'];
									$value = $v;
									if(isset($options)) {
										if($table[$key][$k]['type'] === 'select') {
											if(is_numeric($value) && isset($options[$value])) {
												$value = $options[$value];
											}
										}
									}
									$pdf->Write(5, $label.': '.$value."\n");
								}
								else if($key === 'TODO') {
									$pdf->Write(5, $k.': '.$v."\n");
								}
							}
						}
					}
				}

### TODO List todo attribs

				// Gewerke
				if(isset($gewerke) && $gewerke !== '') {
					$pdf->SetFont('', 'B', 10);
					$pdf->Write(5, "\nGewerk\n");
					$pdf->SetFont('', '', 10);
					$pdf->Write(5, str_replace('<br>',"\n",$gewerke)."\n");
				}

### TODO configure files link

				// Files
				if($id !== '') {
					$path = $this->profilesdir.'/webdav/bestand/devices/'.$id;
					$f = $this->file->get_files($path);
					if(is_array($f)) {
						$url  = $_SERVER['REQUEST_SCHEME'].'://';
						$url .= $_SERVER['SERVER_NAME'];
						$url .= $this->response->html->thisurl.'/';
						$url .= '?index_action=plugin';
						$url .= '&index_action_plugin=bestandsverwaltung';
						$url .= '&'.$this->controller->controller->actions_name.'=download';
						$url .= '&path=/devices/'.$id.'/';

						$pdf->setColor('text',0,0,255);
						$pdf->setFont('','U');
						$pdf->Write(10, "\n");
						foreach($f as $file) {
							$link = $url.$file['name'];
							$pdf->Write(5, $file['name'], $link, false, 'L', false);
							$pdf->Write(5, "\n");
						}
					}
				}

				$qrcodeini = $this->file->get_ini($this->profilesdir.'bestandsverwaltung.qrcode.ini');
				$style = array(
					'border' => true,
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => array(0,0,0),
					'bgcolor' => array(255,255,255),
					'module_width' => 1,
					'module_height' => 1
				);
				// QrCode
				if($id !== '') {
					$qtype = 'qrcode';
					if(isset($qrcodeini['settings']['type'])) {
						if($qrcodeini['settings']['type'] === 'barcode') {
							$qtype = 'barcode';
						}
					}
					$url = $id;
					if(isset($qrcodeini['url']['type'])) {
						if($qrcodeini['url']['type'] === 'auto') {
							$url  = $_SERVER['REQUEST_SCHEME'].'://';
							$url .= $_SERVER['SERVER_NAME'];
							$url .= $this->response->html->thisurl.'/';
							$url .= '?index_action=plugin';
							$url .= '&index_action_plugin=bestandsverwaltung';
							$url .= '&'.$this->controller->controller->actions_name.'=inventory';
							$url .= '&'.$this->controller->actions_name.'=select';
							$url .= '&filter[id]='.$id;
						}
						if($qrcodeini['url']['type'] === 'custom' && isset($qrcodeini['url']['path'])) {
							$url = str_replace('{id}', $id, $qrcodeini['url']['path']);
						}
					}
					if($qtype === 'barcode') {
						$pdf->write1DBarcode($id, 'C39', '125', '58', '70', 10, 0.5, $style, 'N');
					}
					else if($qtype === 'qrcode') {
						$pdf->write2DBarcode($url, 'QRCODE,H', 160, 56, 40, 40, $style, 'N');
						$pdf->SetY(93);
						$pdf->SetX(168);
						$pdf->setColor('text',0,0,255);
						$pdf->setFont('','U',9);
						$pdf->Write(5,'bearbeiten', $url, false, 'L', false);
					}
				} else { 
					// handle backlink
					$link = $this->response->html->request()->get('backlink');
					if ($link === 'true') {
						$backlink  = $_SERVER['REQUEST_SCHEME'].'://';
						$backlink .= $_SERVER['SERVER_NAME'];
						$backlink .= $this->response->html->thisurl.'/';
						$backlink .= '?index_action=plugin';
						$backlink .= '&index_action_plugin=arbeitskarte';
						$backlink .= '&'.$this->actions_name.'=step3';
						$backlink .= '&interval='.$interval;
						$backlink .= '&bezeichner='.$bezeichner;
						if(isset($this->prefix) && $this->prefix !== '') {
							$prefix = explode(',',$this->prefix);
							foreach($prefix as $v) {
								$backlink .= '&prefix['.$v.']='.$v;
							}
						}
						// handle backlink disabled
						$disabled = $this->response->html->request()->get('disabled');
						if(is_array($disabled)) {
							foreach($disabled as $k => $d) {
								$backlink .= '&disabled['.$k.']='.$d;
							}
						}
						// handle backlink attribs
						foreach($attribs as $key => $value) {
							if(isset($value) && is_array($value)) {
								foreach($value as $k => $v) {
									if(is_array($v)) {
										foreach($v as $l) {
											$backlink .= '&'.$key.'['.$k.'][]='.urlencode($l);
										}
									} else {
										$backlink .= '&'.$key.'['.$k.']='.urlencode($v);
									}
								}
							}
						}
						#$pdf->write2DBarcode($backlink, 'QRCODE,H', 160, 56, 40, 40, $style, 'N');

						$pdf->SetY(45);
						$pdf->SetX(172);
						$pdf->setColor('text',0,0,255);
						$pdf->setFont('','U',9);
						$pdf->Write(5,'bearbeiten', $backlink, false, 'L', false);
					}
				}

				// Bezeichner
				$pdf->setColor('text',0,0,0);
				if($bezeichner !== '') {
					$result = $this->taetigkeiten->details($bezeichner, $fields, $this->prefix, $interval, true, true);
					if(is_array($result)) {
						foreach($result as $todos) {
							// get disabled todos
							$disabled = array();
							if($id !== '') {
								$tdisabled = $this->db->select('todos_disabled','*',array('device'=>$id,'prefix'=>$todos['prefix']));
								if(is_array($tdisabled)) {
									foreach($tdisabled as $td) {
										$disabled[$td['todo']] = $td;
									}
								}
							} else {
								$tmp = $this->response->html->request()->get('disabled');
								if(is_array($tmp)) {
									$disabled = $tmp;
								}
							}

							// handle disabled if $hide_disabled is set
							if(count($disabled) > 0 && $hide_disabled !== '') {
								if(isset($todos['groups']) && is_array($todos['groups'])){
									foreach($todos['groups'] as $g => $gewerk) {
										if(isset($gewerk['groups']) && is_array($gewerk['groups'])){
											foreach($gewerk['groups'] as $h => $group) {
												if(isset($group['groups']) && is_array($group['groups'])){
													foreach($group['groups'] as $b => $bau) {
														foreach($bau['todos'] as $key => $value) {
															if(array_key_exists($key, $disabled)) {
																unset($todos['groups'][$g]['groups'][$h]['groups'][$b]['todos'][$key]);
															}
														}
														if(count($todos['groups'][$g]['groups'][$h]['groups'][$b]['todos']) < 1) {
															unset($todos['groups'][$g]['groups'][$h]['groups'][$b]);
														}
													}
													if(count($todos['groups'][$g]['groups'][$h]['groups']) < 1) {
														unset($todos['groups'][$g]['groups'][$h]);
													}
												}
											}
										}
									}
								}
							}

							$pdf->AddPage();
							$pdf->useTemplate($pdf->importPage(2));

							// label
							if(isset($todos['label'])){
								$pdf->Bookmark($todos['shortcut'], 0, 0);
								$pdf->SetFont('', 'B', 10);
								$pdf->Write(5, $todos['label']."\n\n");
							}
							
							// timestamp
							if(isset($todos['time']) && $todos['time'] !== '' && $todos['time'] !== 0){
								$pdf->SetFont('', '', 8);
								$pdf->Write(4, 'Last Update '.date($this->date_format, $todos['time'])."\n\n");
							}
							
							// copyright
							if(isset($todos['copyright']) && $todos['copyright'] !== ''){
								$pdf->setX(PDF_MARGIN_LEFT+3);
								preg_match('/^<img.*src="(.*?)".*>(.*)$/i', $todos['copyright'], $matches);
								if(isset($matches[1]) && $matches[1] !== '') {
									$img = @file_get_contents($matches[1]);
									$pdf->SetFont('', '', 8);
									$pdf->setImageScale(2);
									$pdf->setJPEGQuality(100);
									$pdf->Image('@'.$img);
									if(isset($matches[2]) && $matches[2] !== '') {
										$pdf->WriteHTMLCell('','',$pdf->getImageRBX()+1, '', $matches[2], 0, true);
									}
									// handle Y
									if($pdf->getImageRBY() > $pdf->getY()) {
										$pdf->setY($pdf->getImageRBY());
									} else {
										$pdf->setY($pdf->getY());
									}
									$pdf->Write(5, "\n");
								} else {
									$pdf->SetFont('', '', 8);
									$pdf->WriteHTMLCell('','','','',$todos['copyright'],0,true);
									$pdf->setY($pdf->getY());
									$pdf->Write(5, "\n");
								}
							}
							if(isset($todos['groups']) && is_array($todos['groups'])){
								foreach($todos['groups'] as $gewerk) {

									if($pdf->getY() > 230) {
										$pdf->AddPage();
										$pdf->useTemplate($pdf->importPage(2));
									}
									$pdf->SetFont('', 'B', 9);
									$pdf->Write(5, $gewerk['label']);
									if(isset($gewerk['link']) && $gewerk['link'] !== '') {
										$pdf->Write(5,' ');
										$pdf->setColor('text',0,0,255);
										$pdf->setFont('','U',8);
										$pdf->Write(5,'link', $gewerk['link'], false, 'L', false);
									}
									$pdf->setColor('text',0,0,0);
									$pdf->Write(5, "\n");

									if(isset($gewerk['groups']) && is_array($gewerk['groups'])){
										foreach($gewerk['groups'] as $group) {

											if($pdf->getY() > 230) {
												$pdf->AddPage();
												$pdf->useTemplate($pdf->importPage(2));
											}

											$pdf->SetFont('', 'B', 9);
											$pdf->Write(5, $group['label']);
											if(isset($group['link']) && $group['link'] !== '') {
												$pdf->Write(5,' ');
												$pdf->setColor('text',0,0,255);
												$pdf->setFont('','U',8);
												$pdf->Write(5,'link', $group['link'], false, 'L', false);
											}
											$pdf->setColor('text',0,0,0);
											$pdf->Write(5, "\n");

											if(isset($group['groups']) && is_array($group['groups'])){
												foreach($group['groups'] as $bau) {

													if($pdf->getY() > 230) {
														$pdf->AddPage();
														$pdf->useTemplate($pdf->importPage(2));
													}

													$pdf->SetFont('', 'B', 8);
													$pdf->Write(5, '  '.$bau['label']);
													if(isset($bau['link']) && $bau['link'] !== '') {
														$pdf->Write(5,' ');
														$pdf->setColor('text',0,0,255);
														$pdf->setFont('','U',8);
														$pdf->Write(5,'link', $bau['link'], false, 'L', false);
													}
													$pdf->setColor('text',0,0,0);
													$pdf->Write(5, "\n\n");
													$pdf->SetFont('', '', 8);

													$i = 1;
													foreach($bau['todos'] as $key => $value) {

														$next = ceil(strlen($value['label'])/70) + floor($pdf->getY());
														if($next >= 240) {
															$pdf->AddPage();
															$pdf->useTemplate($pdf->importPage(2));
														}
														$interval = '';
														if($value['interval'] !== '0' && $value['period'] !== '0' && $value['person'] !== '0') {
															$interval = '('.$value['interval'].' / '.$value['period'].' / '.$value['person'].')';
														}
														// replace \n by <br>
														$text = str_replace("\n", '<br>', $value['label']).' '.$interval;
														// check disabled
														if(array_key_exists($key, $disabled)) {
															$text = '<del>'.$text.'</del>';
														}
														if(isset($value['risks'])) {
															$text .= ' <sup>'.$value['risks'].'</sup>';
														}
														if(isset($value['link']) && $value['link'] !== '') {
															$text .= ' <a href="'.$value['link'].'">link</a>';
														}

														$height = $pdf->getStringHeight(170,$text);
														#$height2 = $pdf->getStringHeight(50,$interval);
														#if($height1 > $height2) {
														#	$height = $height1;
														#} else {
														#	$height = 0;
														#}


														$pdf->MultiCell(8, 4, '&bull;', false, 'R', 0, 0, PDF_MARGIN_LEFT,null,true,0,true);
														$pdf->MultiCell(170, 0, $text, false, 'L', 0, 1, (PDF_MARGIN_LEFT+7),null,true,0,true);
/*
														$pdf->MultiCell(
															50, 
															$height, 
															$interval, 
															0,
															'L', 
															false, 
															1, 
															(PDF_MARGIN_LEFT+135),
															'',
															true,
															0,
															false,
															true,
															$height,
															'T');
*/
														$pdf->Ln(1);
														$i++;
													}
													$pdf->Write(5, "\n");
												}
											}
										}
									}

								}
							}

							if(isset($todos['risks']) && is_array($todos['risks'])){

								if($pdf->getY() > 230) {
									$pdf->AddPage();
									$pdf->useTemplate($pdf->importPage(2));
								}

								$pdf->SetFont('', 'B', 9);
								$pdf->WriteHTML('Gef&auml;hrdungen:'."<br>");
								foreach($todos['risks'] as $risk) {
									if($pdf->getY() > 230) {
										$pdf->AddPage();
										$pdf->useTemplate($pdf->importPage(2));
									}
									$pdf->SetFont('', 'B', 8);
									$pdf->Write(5, $risk['label']."\n");
									$pdf->SetFont('', '', 8);
									if(isset($risk['todos']) && is_array($risk['todos'])) {
										foreach($risk['todos'] as $t) {
											if($pdf->getY() > 230) {
												$pdf->AddPage();
												$pdf->useTemplate($pdf->importPage(2));
											}
											$pdf->Write(5, $t."\n");
										}
									}
								}
							}
						}
					}
				}

				if($pageCount > 2) {
					for($i = 3; $i <= $pageCount; $i++) {
						$pdf->AddPage();
						$pdf->useTemplate($pdf->importPage($i));
						if($i === 3) {
							$pdf->Bookmark('Bemerkungen', 0, 0);
						}
					}
				}

				if($id !== '') {
					$pdf->Output($bezeichner.'.'.$id.'.pdf', 'D');
				} else {
					$pdf->Output($bezeichner.'.pdf', 'D');
				}
			}
		}
	}


}

class PDF extends Fpdi\TcpdfFpdi
{
var $_tplIdx;
var $pdftpl;

	function Header() {
		#if (null === $this->_tplIdx) {
		#	$this->setSourceFile($this->pdftpl);
		#	$this->_tplIdx = $this->importPage(2);
		#}
		#$this->useTemplate($this->_tplIdx);
	}

	function Footer() {
		$this->SetY(-10);
		$this->SetFont('', '', 8);
		if(isset($this->id)) {
			$this->Cell(0, 0, $this->id.'  Seite: '.$this->getAliasNumPage().' von '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'C', 'B'); 
		} else {
			$this->Cell(0, 0, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'C', 'B'); 
		}
	}
}
?>
