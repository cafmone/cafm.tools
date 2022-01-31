<?php
/**
 * @package FTP
 */

/**
 * Ftpfactory
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ftp
{
/**
* url to ftpserver
* @access public
* @var string
*/
var $server_url;
/**
* ftpserver port
* @access public
* @var int
*/
var $server_port = 21;
/**
* user to access ftpserver
* @access public
* @var string
*/
var $server_user;
/**
* password to access ftpserver
* @access public
* @var string
*/
var $server_pass;
/**
* ftpserver directory
* @access public
* @var string
*/
var $server_dir = '';
/**
* debugmode
* @access public
* @var bool
*/
var $debug = false;






	function ftp( $path ) {
		$this->__path = realpath($path);
	}


	function files() {		
		if(isset($this->__files)) {
			$return = $this->__files;
		} else {
			$return = $this->__factory( 'handler', $this );
			$this->__files = $return;
		}
		return $return;
	}

	//--------------------------------------------
	/**
	 * build objects
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function __factory( $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null ) {
		if (!is_string( $name ) || !strlen( $name )) {
			throw new exception('Die zu ladende Klasse muss in einer Zeichenkette benannt werden');
		}
		$file  = $this->__path.'/ftp.'.$name;
		require_once( $file.'.class.php' );
		$class = 'ftp_'.$name;
		if(isset($this->__debug) && $this->__debug === 'debug') {
			require_once( $this->__path.'/ftp.debug.class.php' );
			if( file_exists($file.'.'.$this->__debug.'.class.php') ) {
				require_once( $file.'.'.$this->__debug.'.class.php' );
				$class = $class.'_'.$this->__debug;
			}
		}	
		return new $class( $arg1, $arg2, $arg3, $arg4, $arg5, $arg6 );
	}
		
}
