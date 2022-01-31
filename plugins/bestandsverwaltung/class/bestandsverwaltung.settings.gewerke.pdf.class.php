<?php
/**
 * bestandsverwaltung_settings_gewerke_pdf
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

class bestandsverwaltung_settings_gewerke_pdf
{

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
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->user       = $controller->user;
		$this->settings   = $controller->settings;
		$this->classdir   = $controller->classdir;

		require_once(CLASSDIR.'plugins/bestandsverwaltung/class/gewerke.class.php');
		$this->gewerke = new gewerke($this->db);

		$this->pdftpl = PROFILESDIR.'cafm.one/templates/Checklist.pdf';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {

		$gewerke = $this->gewerke->listGewerke(null,null,'',true);

		$pdf = new PDF();
		$pdf->pdftpl = $this->pdftpl;
		$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT);
		$pdf->SetAutoPageBreak(true, 50);

		$pdf->SetAuthor('cafm.one');
		$pdf->SetTitle('Gewerke');
		$pdf->SetCreator('cafm.one');
		#$pdf->SetProtection(array('modify','copy'));
		$pdf->SetXY(PDF_MARGIN_LEFT, 5);

		$pdf->AddPage();
		$pdf->setFont('','N',8);

		$this->pdf = $pdf;

		if(is_array($gewerke)) {
			$count = 0;
			foreach($gewerke as $k => $g) {
				$this->pdf->setColor('text',0,0,0);
				$this->pdf->setFont('','B',8);
				$this->pdf->Bookmark($g['label'],0, -1, '', '', array(0,0,0));
				$this->pdf->Write(4, $g['label']."\n");
				// Untergewerke
				foreach($g as $k1 => $g1) {
					if($k1 !== 'label' && $k1 !== 'bezeichner' && $k1 !== 'sql') {
						$d = $this->__level($k1, $g1, 2, $k);
					}
				}
			}
			$pdf->Output('Gewerke.pdf', 'D');
		}
	}


	//--------------------------------------------
	/**
	 * Generate Levels
	 *
	 * @access protected
	 * @param string $key
	 * @param array $gewerk
	 * @param integer $level
	 * @param string $path
	 * @return string
	 */
	//--------------------------------------------
	function __level($key, $gewerk, $level, $path=null) {

		$count = 0;

		$this->pdf->Bookmark($gewerk['label'],($level-1), -1, '', '', array(0,0,0));
		$this->pdf->setColor('text',0,0,0);
		$this->pdf->setFont('','B',8);
		$this->pdf->SetX($level * 10);
		$this->pdf->Write(4, $gewerk['label']."\n");

#$this->response->html->help($gewerk);

		// Bezeichner
		if(isset($gewerk['bezeichner'])) {
			$tmp = '';
			foreach($gewerk['bezeichner'] as $x => $b) {
				#$anlage = 'dummy';

				$this->pdf->setColor('text',0,0,255);
				$this->pdf->setFont('','N',8);

				$url  = $_SERVER['REQUEST_SCHEME'].'://';
				$url .= $_SERVER['SERVER_NAME'];
				$url .= $this->response->html->thisurl.'/';
				$url .= $this->controller->controller->controller->response->get_url($this->controller->controller->controller->actions_name, 'recording');
				$url .= '&bestand_recording_action=insert&bezeichner='.$x;

				$this->pdf->SetX(($level * 10)+10);
				$this->pdf->Write(4, $b, $url, false, 'L', false);
				$this->pdf->Write(2, "\n");
			}
			$this->pdf->Write(5, "\n");
		}

		// Untergewerke
		foreach($gewerk as $k => $g) {
			if($k !== 'label' && $k !== 'bezeichner'  && $k !== 'sql') {
				$d = $this->__level($k, $g, ($level+1), $path);
			}
		}
	}
}


class PDF extends Fpdi\TcpdfFpdi
{
var $_tplIdx;
var $pdftpl;

	function Header() {
		if (null === $this->_tplIdx) {
			$this->setSourceFile($this->pdftpl);
			$this->_tplIdx = $this->importPage(2);
		}
		$this->useTemplate($this->_tplIdx);
	}

	function Footer() {
		$this->SetY(-10);
		$this->SetFont('', '', 8);
		$this->Cell(0, 0, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'C', 'B'); 
	}
}

?>
