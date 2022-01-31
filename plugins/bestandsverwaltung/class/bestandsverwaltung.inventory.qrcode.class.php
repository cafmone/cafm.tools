<?php
/**
 * QR Template
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

class bestandsverwaltung_inventory_qrcode
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cms_action';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'headline' => 'QRCode',
		'replacements' => 'Replacements',
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param user $user
	 */
	//-------------------------------------------
	function __construct($controller) {
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->user = $controller->user;
		$this->db = $controller->db;
		$this->controller = $controller;
		$this->datadir = $controller->profilesdir.'bestand/templates/';
		$this->settings = $controller->profilesdir.'bestandsverwaltung.qrcode.ini';
		$this->ini = $this->file->get_ini( $this->settings, true, true );
	}

	function init() {
		$this->id = $this->response->html->request()->get($this->identifier_name);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function action( ) {
		$this->init();
		if($this->id !== '') {
			$this->download();
		} else {
			#return $this->editor();
		}
	}

	//--------------------------------------------
	/**
	 * Content
	 *
	 * @access public
	 * @param bool $hidden
	 * @return htmlobject_template
	 */
	//-------------------------------------------
/*
	function editor( $hidden = false) {
		$data = '';
		if( $hidden === false ) {
			if(isset($this->ini['qrcode']['typ'])) {
				switch($this->ini['qrcode']['typ']) {
					case 'leitz_icon':
						require_once(CLASSDIR.'/lib/file/file.class.php');
						$file = new file(CLASSDIR.'/lib/file/');
						require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
						$commander = new phpcommander(CLASSDIR.'/lib/phpcommander', $this->response->html, $file->files(), 'pc', $this->response->params);
						if($commander->response->cancel()) {
							$this->response->redirect($this->response->get_url($this->actions_name,'content'));
						}
						$commander->colors = array();
						$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
						$commander->actions_name = 'cms_action';
						$commander->message_param = $this->message_param;
						$commander->identifier_name = 'ttt';
						$commander->allow['edit'] = true;
						$commander->allow['delete'] = false;
						$commander->lang = $this->user->translate($commander->lang, CLASSDIR.'/lang', 'phpcommander.ini');

						$_REQUEST[$commander->identifier_name] = 'qrcode.LeitzLbl';
						$controller = $commander->controller($this->datadir);
						$tmp       = $controller->edit();
						$tmp->add('', 'delete');

						$content = $tmp->get_elements('content');
						$content->wrap = 'off';
						$tmp->add($content, 'content');

						$t = $this->response->html->template($this->tpldir.'/bestandsverwaltung.config.qrcode.html');
						$t->add($tmp->get_string(), 'editor');
						$content = $this->get_template();
						if(isset($content->__keys)) {
							unset($content->__keys['?']);
							$t->add(implode('<br>', $content->__keys), 'keys');
						}
						$t->add($this->lang['replacements'], 'replacements');
						$t->add($this->lang['headline'], 'headline');
						$data = $t;
					break;
					default:
						$data = 'nothing to do';
					break;
				}
			}
		}
		return $data;
	}
*/

	//--------------------------------------------
	/**
	 * Template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return htmlobject_template
	 */
	//-------------------------------------------
	function get_template() {
		$this->init();
		$return = '';

		if(is_array($this->id)) {

			// handle height/width
			$height = '36';
			$width  = '90';
			$border = '2';
			$font   = 8;

			if(isset($this->ini['size']['height'])) {
				$height = $this->ini['size']['height'];
			}
			if(isset($this->ini['size']['width'])) {
				$width = $this->ini['size']['width'];
			}
			if(isset($this->ini['size']['border'])) {
				$border = $this->ini['size']['border'];
			}
			if(isset($this->ini['size']['font'])) {
				$font = $this->ini['size']['font'];
			}

			$str = '';
			if(isset($this->ini['settings']['type'])) {
				switch($this->ini['settings']['type']) {
					case 'barcode':
						$style = array(
							'border' => false,
							'padding' => 0,
							'fgcolor' => array(0,0,0),
							'bgcolor' => false,
							'module_width' => 1,
							'module_height' => 1
						);
					break;
					case 'qrcode':
						$style = array(
							'border' => true,
							'padding' => 2,
							'fgcolor' => array(0,0,0),
							'bgcolor' => false,
							'module_width' => 1,
							'module_height' => 1
						);
						$url  = $_SERVER['REQUEST_SCHEME'].'://';
						$url .= $_SERVER['SERVER_NAME'];
						$url .= $this->response->html->thisurl.'/';
						$url .= '?index_action=plugin';
						$url .= '&index_action_plugin=bestandsverwaltung';
						$url .= '&'.$this->controller->controller->actions_name.'=inventory';
						$url .= '&'.$this->controller->actions_name.'=select';
					break;
				}

				require_once(CLASSDIR.'lib/pdf/tcpdf/tcpdf.php');
				require_once(CLASSDIR.'lib/pdf/tcpdf/mytcpdf.class.php');

				$pdf = new mytcpdf('L', 'mm', array($width,$height), true, 'UTF-8', false);
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
				$pdf->SetMargins($border, $border, $border,true);
				$pdf->SetAutoPageBreak(false, 50);
				$pdf->SetAuthor('cafm.one');
				$pdf->SetTitle('QRCodes');
				$pdf->SetCreator('cafm.one');
				$pdf->SetFont(PDF_FONT_MONOSPACED, 'N', $font);
			}

			$max = count($this->id)-1;
			$i = 0;
			foreach($this->id as $id) {
				if($id !== '') {
					$result = $this->db->select('bestand', '*', array('id',$id));
					$bezeichner = $this->db->select('bezeichner', 'bezeichner_lang', array('bezeichner_kurz',$result[0]['bezeichner_kurz']));
					if(isset($bezeichner[0])) {
						$bezeichner = ''.$result[0]['bezeichner_kurz'].' - '.$bezeichner[0]['bezeichner_lang'];
						$bezeichner_kurz = $result[0]['bezeichner_kurz'];
					} else {
						$bezeichner = $result[0]['bezeichner_kurz'];
						$bezeichner_kurz = $result[0]['bezeichner_kurz'];
					}

					switch($this->ini['settings']['type']) {
						case 'barcode':
							$pdf->AddPage();
							$pdf->write1DBarcode($id, 'C39', $border, $border, ($width-($border*2)), 4, 0.6, $style, 'N');
							$pdf->SetY(($border+6));
							$pdf->Cell(0, 3, $bezeichner, false, true, 'L', 0, '', 1);
							$pdf->Cell(0, 3, 'ID: '.$id, false, true, 'L', 0, '', 1);
							if(isset($this->ini['replacements']) && is_array($this->ini['replacements'])) {
								foreach($result as $res) {
									if(in_array($res['merkmal_kurz'], $this->ini['replacements'])) {
										$pdf->Cell(0, 3, $res['wert'], false, true, 'L', 0, '', 1);
									}
								}
							}
						break;
						case 'qrcode':
							$pdf->AddPage();
							$pdf->Bookmark($id, 0, 0);
							$pdf->SetX($height);
							$pdf->Cell(0, 4, $bezeichner, false, true, 'L', 0, '', 1);
							$pdf->SetX($height);
							$pdf->Cell(0, 4, 'ID: '.$id, false, true, 'L', 0, '', 1);
							if(isset($this->ini['replacements']) && is_array($this->ini['replacements'])) {
								foreach($result as $res) {
									if(in_array($res['merkmal_kurz'], $this->ini['replacements'])) {
										$pdf->SetX($height);
										$pdf->Cell(0, 4, $res['wert'], false, true, 'L', 0, '', 1);
									}
								}
							}
							$type = $this->ini['url']['type'];
							if($type === 'custom' && isset($this->ini['url']['path'])) {
								$path = str_replace('{id}' ,$id, $this->ini['url']['path']);
							}
							else if($type === 'auto') {
								$path = $url.'&filter[id]='.$id;
							} else {
								$path = $id;
							}
							$pdf->write2DBarcode($path, 'QRCODE,L', $border, $border, ($height-($border*2)), ($height-($border*2)), $style, 'N', false);
						break;
						case 'leitz_icon':
							$t = $this->response->html->template($this->datadir.'qrcode.LeitzLbl');
							$t->add($id, 'id');
							$t->add($bezeichner, 'bezeichner_lang');
							$t->add($bezeichner_kurz, 'bezeichner_kurz');
							if(isset($this->ini['replacements']) && is_array($this->ini['replacements'])) {
								foreach($this->ini['replacements'] as $qk => $qr) {
									if($qk !== 'typ') {
										$t->add('', $qr);
									}
								}
								foreach($result as $res) {
									if(in_array($res['merkmal_kurz'], $this->ini['replacements'])) {
										## TODO escape &
										$t->add($res['wert'], $res['merkmal_kurz']);
									}
								}
							}
							$str = $t->get_string();
							if($max > 0) {
								$tmp = explode("\n", $str);
								if($i === 0) {
									unset($tmp[count($tmp)-1]);
								}
								else if($i < $max) {
									unset($tmp[count($tmp)-1]);
									unset($tmp[0]);
									unset($tmp[1]);
								}
								else if($i === $max) {
									unset($tmp[0]);
									unset($tmp[1]);
								}
								$str = implode("\n", $tmp);
							}
							$return .= $str;
						break;
						default:
							$str = 'qrtype not found';
							$return .= $str;
						break;
					}
					$i++;
				}
			}
		} else {


/*
			// get editor content
			if(isset($this->ini['qrcode']['typ'])) {
				switch($this->ini['qrcode']['typ']) {
					case 'leitz_icon':
						$t = $this->response->html->template($this->datadir.'qrcode.LeitzLbl');
						$t->add('', 'id');
						$t->add('', 'bezeichner_lang');
						$t->add('', 'bezeichner_kurz');
						if(isset($this->ini['qrcode']) && is_array($this->ini['qrcode'])) {
							foreach($this->ini['qrcode'] as $qk => $qr) {
								if($qk !== 'typ') {
									$t->add($qr, $qr);
								}
							}
						}
						$return = $t;
					break;
					default:
						$t = $this->response->html->div();
						$t->add('qrtype not found');
						$return = $t;
					break;
				}
			}
*/
		}

		if(isset($return) && $return !== '') {
			return $return;
		} else {
			return $pdf;
		}
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function download() {
		if(isset($this->ini['settings']['type'])) {
			switch($this->ini['settings']['type']) {
				case 'leitz_icon':
					$content = $this->get_template();
					$name    = 'qrcode.LeitzAB';
					$size    = strlen($content);
					header("Pragma: public");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
					header("Cache-Control: must-revalidate");
					header("Content-type: application/leitz");
					header("Content-Length: ".$size);
					header("Content-disposition: inline; filename=".$name);
					header("Accept-Ranges: ".$size);
					flush();
					echo $content;
					exit(0);
				break;
				default:
					$pdf = $this->get_template();
					$pdf->Output('qrcodes.pdf', 'D');
				break;
			}
		}
	}

}
