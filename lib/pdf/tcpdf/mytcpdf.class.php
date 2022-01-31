<?php

class mytcpdf extends TCPDF
{


	/**
	 * Print 2D Barcode.
	 * @param $code (string) code to print
	 * @param $type (string) type of barcode (see tcpdf_barcodes_2d.php for supported formats).
	 * @param $x (int) x position in user units
	 * @param $y (int) y position in user units
	 * @param $w (int) width in user units
	 * @param $h (int) height in user units
	 * @param $style (array) array of options:<ul>
	 * <li>boolean $style['border'] if true prints a border around the barcode</li>
	 * <li>int $style['padding'] padding to leave around the barcode in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['hpadding'] horizontal padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['vpadding'] vertical padding in barcode units (set to 'auto' for automatic padding)</li>
	 * <li>int $style['module_width'] width of a single module in points</li>
	 * <li>int $style['module_height'] height of a single module in points</li>
	 * <li>array $style['fgcolor'] color array for bars and text</li>
	 * <li>mixed $style['bgcolor'] color array for background or false for transparent</li>
	 * <li>string $style['position'] barcode position on the page: L = left margin; C = center; R = right margin; S = stretch</li><li>$style['module_width'] width of a single module in points</li>
	 * <li>$style['module_height'] height of a single module in points</li></ul>
	 * @param $align (string) Indicates the alignment of the pointer next to barcode insertion relative to barcode height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
	 * @param $distort (boolean) if true distort the barcode to fit width and height, otherwise preserve aspect ratio
	 * @author Nicola Asuni
	 * @since 4.5.037 (2009-04-07)
	 * @public
	 */
	public function write2DBarcode($code, $type, $x='', $y='', $w='', $h='', $style='', $align='', $distort=false) {
		if (TCPDF_STATIC::empty_string(trim($code))) {
			return;
		}
		require_once(dirname(__FILE__).'/tcpdf_barcodes_2d.php');
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// create new barcode object
		$barcodeobj = new TCPDF2DBarcode($code, $type);
		$arrcode = $barcodeobj->getBarcodeArray();
		if (($arrcode === false) OR empty($arrcode) OR !isset($arrcode['num_rows']) OR ($arrcode['num_rows'] == 0) OR !isset($arrcode['num_cols']) OR ($arrcode['num_cols'] == 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// set default values
		if (!isset($style['position'])) {
			$style['position'] = '';
		}
		if (!isset($style['fgcolor'])) {
			$style['fgcolor'] = array(0,0,0); // default black
		}
		if (!isset($style['bgcolor'])) {
			$style['bgcolor'] = false; // default transparent
		}
		if (!isset($style['border'])) {
			$style['border'] = false;
		}
		// padding
		if (!isset($style['padding'])) {
			$style['padding'] = 0;
		} elseif ($style['padding'] === 'auto') {
			$style['padding'] = 4;
		}
		if (!isset($style['hpadding'])) {
			$style['hpadding'] = $style['padding'];
		} elseif ($style['hpadding'] === 'auto') {
			$style['hpadding'] = 4;
		}
		if (!isset($style['vpadding'])) {
			$style['vpadding'] = $style['padding'];
		} elseif ($style['vpadding'] === 'auto') {
			$style['vpadding'] = 4;
		}
		$hpad = (2 * $style['hpadding']);
		$vpad = (2 * $style['vpadding']);
		// cell (module) dimension
		if (!isset($style['module_width'])) {
			$style['module_width'] = 1; // width of a single module in points
		}
		if (!isset($style['module_height'])) {
			$style['module_height'] = 1; // height of a single module in points
		}
		if ($x === '') {
			$x = $this->x;
		}
		if ($y === '') {
			$y = $this->y;
		}
		// check page for no-write regions and adapt page margins if necessary
		list($x, $y) = $this->checkPageRegions($h, $x, $y);
		// number of barcode columns and rows
		$rows = $arrcode['num_rows'];
		$cols = $arrcode['num_cols'];
		if (($rows <= 0) || ($cols <= 0)){
			$this->Error('Error in 2D barcode string');
		}
		// module width and height
		$mw = $style['module_width'];
		$mh = $style['module_height'];
		if (($mw <= 0) OR ($mh <= 0)) {
			$this->Error('Error in 2D barcode string');
		}
		// get max dimensions
		if ($this->rtl) {
			$maxw = $x - $this->lMargin;
		} else {
			$maxw = $this->w - $this->rMargin - $x;
		}
		$maxh = ($this->h - $this->tMargin - $this->bMargin);
		$ratioHW = ((($rows * $mh) + $hpad) / (($cols * $mw) + $vpad));
		$ratioWH = ((($cols * $mw) + $vpad) / (($rows * $mh) + $hpad));
		if (!$distort) {
			if (($maxw * $ratioHW) > $maxh) {
				$maxw = $maxh * $ratioWH;
			}
			if (($maxh * $ratioWH) > $maxw) {
				$maxh = $maxw * $ratioHW;
			}
		}

// Get rid of this
		// set maximum dimensions
		#if ($w > $maxw) {
		#	$w = $maxw;
		#}
		#if ($h > $maxh) {
		#	$h = $maxh;
		#}

		// set dimensions
		if ((($w === '') OR ($w <= 0)) AND (($h === '') OR ($h <= 0))) {
			$w = ($cols + $hpad) * ($mw / $this->k);
			$h = ($rows + $vpad) * ($mh / $this->k);
		} elseif (($w === '') OR ($w <= 0)) {
			$w = $h * $ratioWH;
		} elseif (($h === '') OR ($h <= 0)) {
			$h = $w * $ratioHW;
		}
		// barcode size (excluding padding)
		$bw = ($w * $cols) / ($cols + $hpad);
		$bh = ($h * $rows) / ($rows + $vpad);
		// dimension of single barcode cell unit
		$cw = $bw / $cols;
		$ch = $bh / $rows;
		if (!$distort) {
			if (($cw / $ch) > ($mw / $mh)) {
				// correct horizontal distortion
				$cw = $ch * $mw / $mh;
				$bw = $cw * $cols;
				$style['hpadding'] = ($w - $bw) / (2 * $cw);
			} else {
				// correct vertical distortion
				$ch = $cw * $mh / $mw;
				$bh = $ch * $rows;
				$style['vpadding'] = ($h - $bh) / (2 * $ch);
			}
		}
		// fit the barcode on available space
		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, false);
		// set alignment
		$this->img_rb_y = $y + $h;
		// set alignment
		if ($this->rtl) {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x - $w;
			}
			$this->img_rb_x = $xpos;
		} else {
			if ($style['position'] == 'L') {
				$xpos = $this->lMargin;
			} elseif ($style['position'] == 'C') {
				$xpos = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
			} elseif ($style['position'] == 'R') {
				$xpos = $this->w - $this->rMargin - $w;
			} else {
				$xpos = $x;
			}
			$this->img_rb_x = $xpos + $w;
		}
		$xstart = $xpos + ($style['hpadding'] * $cw);
		$ystart = $y + ($style['vpadding'] * $ch);
		// barcode is always printed in LTR direction
		$tempRTL = $this->rtl;
		$this->rtl = false;
		// print background color
		if ($style['bgcolor']) {
			$this->Rect($xpos, $y, $w, $h, $style['border'] ? 'DF' : 'F', '', $style['bgcolor']);
		} elseif ($style['border']) {
			$this->Rect($xpos, $y, $w, $h, 'D');
		}
		// set foreground color
		$this->SetDrawColorArray($style['fgcolor']);
		// print barcode cells
		// for each row
		for ($r = 0; $r < $rows; ++$r) {
			$xr = $xstart;
			// for each column
			for ($c = 0; $c < $cols; ++$c) {
				if ($arrcode['bcode'][$r][$c] == 1) {
					// draw a single barcode cell
					$this->Rect($xr, $ystart, $cw, $ch, 'F', array(), $style['fgcolor']);
				}
				$xr += $cw;
			}
			$ystart += $ch;
		}
		// restore original direction
		$this->rtl = $tempRTL;
		// restore previous settings
		$this->setGraphicVars($gvars);
		// set pointer to align the next text/objects
		switch($align) {
			case 'T':{
				$this->y = $y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'M':{
				$this->y = $y + round($h/2);
				$this->x = $this->img_rb_x;
				break;
			}
			case 'B':{
				$this->y = $this->img_rb_y;
				$this->x = $this->img_rb_x;
				break;
			}
			case 'N':{
				$this->SetY($this->img_rb_y);
				break;
			}
			default:{
				break;
			}
		}
		$this->endlinex = $this->img_rb_x;
	}

}
?>
