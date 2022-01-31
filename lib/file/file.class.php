<?php
/**
 * @package file
 */

/**
 * Filefactory
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class file
{

	#var $_debug;
	#var $_files;


	function file( $path ) {
		$this->__path = realpath($path);
	}

	function debug($level = 1, $tag = 'htmlobject_debug') {
		$this->__debug = 'debug';
	}

	/**
	 * build objects
	 *
	 * @access protected
	 */
	function factory( $name, $arg1 = null, $arg2 = null) {
		$class = $name;
		if($name === '') {
			$name = 'file';
		}
		if($name !== 'file') {
			$class = 'file_'.$name;
			$name  = 'file.'.$name;
		}
		require_once( $this->__path.'/'.$name.'.class.php' );
		return new $class( $arg1, $arg2 );
	}

	function files() {		
		if(isset($this->__files)) {
			$return = $this->__files;
		} else {
			$return = $this->factory( 'handler' );
			$this->__files = $return;
		}
		return $return;
	}

	function upload() {
		$file = $this->files();
		return $this->factory( 'upload', $file );
	}


}
?>
