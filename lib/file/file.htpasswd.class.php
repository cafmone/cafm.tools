<?php
/**
 * htpasswd
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class file_htpasswd
{
/**
* translation
* @access public
* @var string
*/
var $lang = array(
				'error_file_not_found' => 'Htpasswd File %s not found'		
			);
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param $root
	 */
	//--------------------------------------------
	function __construct( $file ) {
		$this->file = $file;
	}

	//--------------------------------------------
	/**
	 * Select user from httpd
	 *
	 * @access public
	 * @param string $name
	 * @return string|array
	 */
	//--------------------------------------------
	function select( $name = null ) {
		// read password file
		if(file_exists($this->file)) {
 			$handle = fopen ($this->file, "r");
	 		while (!feof($handle)) {
	 			$tmp = explode(':', fgets($handle, 4096));
	 			if($tmp[0] !== '') {
					$old[$tmp[0]] = $tmp[1];
				}
			}
			fclose ($handle);
			return $old;
		} else {
			return sprintf($this->lang['error_file_not_found'], $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Insert user
	 *
	 * @access public
	 * @param string $name
	 * @param string $password
	 * @return string
	 */
	//--------------------------------------------
	function insert( $name, $password ) {
		return $this->__write( $name, $password, $mode = 'insert' );
	}

	//--------------------------------------------
	/**
	 * Update user
	 *
	 * @access public
	 * @param string $name
	 * @param string $password
	 * @return string
	 */
	//--------------------------------------------
	function update( $name, $password ) {
		return $this->__write( $name, $password, $mode = 'update' );
	}

	//--------------------------------------------
	/**
	 * Delete user
	 *
	 * @access public
	 * @param string $name
	 * @return string
	 */
	//--------------------------------------------
	function delete( $name ) {
		return $this->__write( $name, null, $mode = 'delete' );
	}

	//--------------------------------------------
	/**
	 * Set httpd password
	 *
	 * @access protected
	 * @param string $name
	 * @param string $password
	 * @param enum $mode [insert|update|delete]
	 * @return string
	 */
	//--------------------------------------------
	function __write( $name, $password = null, $mode = 'update' ) {
		$error = '';
		$old    = $this->select();
 		$handle = fopen ($this->file, "w+");
		// insert or update user in password file
		if($mode === 'update' || $mode === 'insert') {
			$set = false;
			if(is_array($old)) {
				foreach($old as $key => $value) {
	 				if($key === $name) {
	 					#fputs($handle, $name.':'.$this->__crypt($password)."\n");
	 					fputs($handle, $name.':'.crypt($password, base64_encode($password))."\n");
						$set = true;
					} else {
						fputs($handle, "$key:$value");
					}
				}
			}
			if($set === false) {
				#fputs($handle, $name.':'.$this->crypt($password)."\n");
				fputs($handle, $name.':'.crypt($password, base64_encode($password))."\n");
			}
 		}
		// remove user from password file
		if($mode === 'delete') {
			foreach($old as $key => $value) {
				if($key !== $name) {
 					fputs($handle, "$key:$value");
				}
			}
		}
 		fclose ($handle);
		return $error;
	}

}
?>
