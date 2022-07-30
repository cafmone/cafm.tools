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

	//--------------------------------------------
	/**
	 * crypt password
	 *
	 * @access public
	 * @param string $plainpasswd
	 * @return string
	 */
	//--------------------------------------------
	function crypt($plainpasswd) {
		$salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
		$len = strlen($plainpasswd);
		$text = $plainpasswd.'$apr1$'.$salt;
		$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
		for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
		for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
		$bin = pack("H32", md5($text));
		for($i = 0; $i < 1000; $i++) {
			$new = ($i & 1) ? $plainpasswd : $bin;
			if ($i % 3) $new .= $salt;
			if ($i % 7) $new .= $plainpasswd;
			$new .= ($i & 1) ? $bin : $plainpasswd;
			$bin = pack("H32", md5($new));
		}
		for ($i = 0; $i < 5; $i++) {
			$k = $i + 6;
			$j = $i + 12;
			if ($j == 16) $j = 5;
			$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
		}
		$tmp = chr(0).chr(0).$bin[11].$tmp;
		$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
		return "$"."apr1"."$".$salt."$".$tmp;
	}

}
?>
