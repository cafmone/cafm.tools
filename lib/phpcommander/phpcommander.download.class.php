<?php
/**
 * PHPCommander Download
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_download
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param object $response
	 */
	//--------------------------------------------
	function __construct($path, $response, $file) {
		$this->__path = $path;
		$this->__pc   = $response;
		$this->__file = $file;
	}

	//--------------------------------------------
	/**
	 * Action download
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$msg  = array();
		$files = $this->__pc->html->request()->get($this->__pc->identifier);
		if($files !== '') {
			$file = @tempnam('/dummydir', 'xx');
				if(function_exists("gzcompress")) {
					$archiv = new zip_file($file);
					$mime   = 'application/zip';
					$fname  = 'download.zip';
				}
				#if(function_exists("gzencode")) {
				#	$archiv = new gzip_file($file);
				#	$mime   = 'application/x-compressed-tar';
				#	$fname  = 'download.tar.gz';
				#}
				#else if(function_exists("bzopen")) {
				#	$archiv = new bzip_file($file);
				#	$mime   = 'application/x-bzip-compressed-tar';
				#	$fname  = 'download.tar.bz2';
				else {
					$archiv = new tar_file($file);
					$mime   = 'application/x-tar';
					$fname  = 'download.tar';
				}

			$archiv->set_options(array('basedir' => $this->__path, 'overwrite' => 1, 'level' => 9, 'storepaths' => 1));
			foreach($files as $f) {
				$archiv->add_files($f);
			}
			$archiv->create_archive();
			$size = filesize($file);

			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: $mime");
			header("Content-Length: ".$size);
			header("Content-disposition: inline; filename=$fname");
			header("Accept-Ranges: ".$size);
   			#ob_end_flush();
   			flush();
			readfile($file);
			$this->__file->remove($file);

			exit(0);

			#$this->__pc->html->help($archiv);
		}
	}

}
?>
