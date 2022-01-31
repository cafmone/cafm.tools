<?php
/**
 * File XLSX
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class file_xlsx
{
/**
*  sheet number to parse
*  @access public
*  @var integer
*/
var $sheet = 1;
/**
*  row to start with
*  @access public
*  @var integer
*/
var $row = 1;
/**
*  columns to parse
*  @access public
*  @var array
*/
var $cols = array('A');
/**
*  unpack to memory
*  @access public
*  @var bool
*/
var $inmemory  = true;
/**
*  date format (date syntax)
*  @access public
*  @var string
*/
var $dateformat = 'Y-m-d H:i:s';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'errors' => array(
		'unknown' => 'An unknown error has occurred',
		'xlsx' => '%s is not an xlsx file',
		1 => 'Multi-disk zip archives not supported',
		2 => 'Renaming temporary file failed',
		3 => 'Closing zip archive failed',
		4 => 'Seek error',
		5 => 'Read error',
		6 => 'Write error',
		7 => 'CRC error',
		8 => 'Containing zip archive was closed',
		9 => '%s No such file',
		10 => '%s already exists',
		11 => 'Can\'t open %s',
		12 => 'Failure to create temporary file',
		13 => 'Zlib error',
		14 => 'Malloc failure',
		15 => 'Entry has been changed',
		16 => 'Compression method not supported',
		17 => 'Premature EOF',
		18 => 'Invalid argument',
		19 => '%s is not a zip archive',
		20 => 'Internal error',
		21 => 'Zip archive inconsistent',
		22 => 'Can\'t remove file',
		23 => 'Entry has been deleted',
	)
);

	//---------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param $object file
	 * @access public
	 */
	//---------------------------------------
	function __construct( $file ) {
		$this->file = $file;
	}

	//---------------------------------------
	/**
	 * parse
	 *
	 * @access public
	 * @return array
	 */
	//---------------------------------------
	function parse( $path ) {
		$out = array();
		$zip = new ZipArchive;
		$res = $zip->open($path);
		if ($res === true) {
			if(isset($this->inmemory) && $this->inmemory === true) {
				$pSs = 'zip://'.$path.'#xl/sharedStrings.xml';
				$pWs = 'zip://'.$path.'#xl/worksheets/'.'sheet'.$this->sheet.'.xml';
			} else {
				$tmpdir = @tempnam('/dummydir', 'xx');
				if (file_exists($tmpdir)) { unlink($tmpdir); }
				$error = $this->file->mkdir($tmpdir);
				if($error === '') {
					$this->file->copy('zip://'.$path.'#xl/sharedStrings.xml', $tmpdir.'/sharedStrings.xml', true);
					$this->file->copy('zip://'.$path.'#xl/worksheets/'.'sheet'.$this->sheet.'.xml', $tmpdir.'/sheet'.$this->sheet.'.xml', true);
					$zip->close();
					$pSs = $tmpdir.'/sharedStrings.xml';
					$pWs = $tmpdir.'/sheet'.$this->sheet.'.xml';
				}
			}
			if(!isset($error)) {
				$sharedstrings = @simplexml_load_file($pSs);
				$worksheet = @simplexml_load_file($pWs);

				if($sharedstrings !== false && $worksheet !== false) {
					foreach ( $worksheet->sheetData->row as $row ) {

						$tmp = $row->attributes();
						$i = (int)$tmp['r'];

						if($i >= $this->row) {
							foreach ( $row->c as $col ) {
								$v = (array) $col->attributes();
								if(isset($v['@attributes']['r'])) {
									$name = preg_replace('/[0-9]/', '', $v['@attributes']['r']);
									if(isset($col->v) && in_array($name, $this->cols)) {
										if(!isset($v['@attributes']['t'])) {
											$type = '';
										} else {
											$type = $v['@attributes']['t'];
										}
										switch ($type) {
											case 'n':
												// integer
												$out[$i][$name] = (int) $col->v;
											break;
											case 's':
												// string
												if(isset($sharedstrings->si[(int)$col->v]->t)) {
													$out[$i][$name] = (string) $sharedstrings->si[(int)$col->v]->t;
												}
											break;
											case 'b':
												// boolean
												$out[$i][$name] = (string) $col->v;
											break;
											case 'd':
												// date
												$out[$i][$name] = $this->dateformat ? gmdate( $this->dateformat, $this->unixstamp( (float) $cell->v ) ) : (float) $cell->v;
											break;
											case '':
											default: 
												$out[$i][$name] = $col->v->__toString();
											break;
										};
									}
								}
							}
							// check out is set
							if(isset($out[$i])) {
								// fill up empty cells
								if(count($this->cols) > count($out[$i])) {
									foreach($this->cols as $c) {
										if(!array_key_exists($c, $out[$i])) {
											$out[$i][$c] = '';
										}
									}
								}
							}
						}
					}

					// clean up
					if(isset($this->inmemory) && $this->inmemory === true) {
						$zip->close();
					}
					else if(isset($tmpdir)) {
						$error = $this->file->remove($tmpdir);
					}
					unset($sharedStrings);
					unset($worksheet);
				} else {
					// raise wrong file error
					$error = sprintf($this->lang['errors']['xlsx'], basename($path));
				}
			}
		} else {
			if(isset($this->lang['errors'][$res])) {
				$error = sprintf($this->lang['errors'][$res], basename($path));
			} else {
				$error = $this->lang['errors']['unknown'];
			}
		}

		if(isset($error) && $error !== '') {
			return $error;
		} else {
			return $out;
		}

	}

}
?>
