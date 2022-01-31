<?php
/**
 * query_file
 *
 * uses parse_ini_file <http://php.net/manual/en/function.parse-ini-file.php>
 *
 * @package query
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_file
{
/**
*  file permissions
*  @access public
*  @var string
*/
var $permissions_file = 0666;
/**
*  dir permissions
*  @access public
*  @var string
*/
var $permissions_dir = 0777;
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'error_file_not_found' => 'File %s not found',
	'error_write' => 'Could not write to file %s',
	'error_not_array' => 'Data is not of type array'
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db represents path for file
	 * @return string on error
	 */
	//--------------------------------------------
	function __construct($host, $user, $pass, $db) {
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;

		#echo 'CONSTRUCTOR:';
		#echo '<br>host - '.$this->host;
		#echo '<br>user - '.$this->user;
		#echo '<br>pass - '.$this->pass;
		#echo '<br>db - '.$this->path;
		#echo '<br>';
	}

	//--------------------------------------------
	/**
	 * Query
	 *
	 * @access public
	 * @param string $command
	 * @param string $path
	 * @param string $data
	 * @param array $where
	 * @param array $order
	 * @return mixed null|string|array
	 */
	//--------------------------------------------
	function query($command, $path, $data = null, $where = null, $order = null) {

		#echo 'QUERY:';
		#echo '<br>command - '.$command;
		#echo '<br>path - '.$path;
		#echo '<br>data - '.$data;
		#echo '<br>where - ';
		#echo '<pre>';
		#print_r( $where );
		#echo'</pre>';
		#echo '<br>order - '.$order;
		#echo '<br>';

		$result = null;
		switch( $command ) {
			case 'SELECT':
				$result = $this->select( $path, $data, $where, $order );
			break;
			case 'INSERT':
				$result = $this->insert( $path, $data );
			break;
			case 'UPDATE':
				$result = $this->update( $path, $data, $where );
			break;
			case 'DELETE':
				$result = $this->delete( $path, $where );
			break;
		}
		return $result;
	}

	//---------------------------------------
	/**
	 * Last Insert id
	 *
	 * @access public
	 * @return null|integer
	 */
	//---------------------------------------	
	function last_insert_id() {
		return 'last_insert_id';
	}

	//--------------------------------------------
	/**
	 * Escape strings
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	//--------------------------------------------
	function escape( $value ) {
		return $value;
	}

	//--------------------------------------------
	/**
	 * Close db
	 *
	 * @access public
	 */
	//--------------------------------------------
	function close() {
		return null;
	}

	//--------------------------------------------
	/**
	 * Select from ini file
	 *
	 * @access public
	 * @param string $path
	 * @param array $keys
	 * @param array $where
	 * @return string|array
	 */
	//--------------------------------------------
	function select( $path , $keys = null, $where = null, $order = null) {

		// handle path
		$path = $this->db.$path;
		if( file_exists( $path ) ) {
			$out    = '';
			$result = parse_ini_file($path, true);
			// KEYS
			if(isset($keys)) {
				$col = null;
				foreach($result as $k => $v) {
					foreach($keys as $key) {
						if(array_key_exists($key, $v)) {
							$col[$k][$key] = $v[$key];
						}
					}
				}
				$out = $col;
			}
			// WHERE
			if(isset($where)) {
				$col = null;
				if($out === '') {
					$out = $result;
				}
				foreach($result as $k => $v) {
					if(
						array_key_exists($where[0], $v) &&
						$v[$where[0]] === $where[1]
					){
						if(isset($out[$k])) {
							$col[$k] = $out[$k];
						}
					}
				}
				$out = $col;
			}
			if(!isset($keys) && !isset($where)) {
				$out = $result;
			}
			// ORDER
			if(isset($order)) {
				if($out === '') {
					$out = $result;
				}
				$col = array();
				if(is_array($out)) {
					reset($out);
					foreach($out as $v) {
						if(isset($v[$order[0]])) {
							$col[] = $v[$order[0]];
						}
					}
					if(count($out) === count($col)) {
						array_multisort($col, SORT_ASC, $out);
					}
				}
			}
		}
		else if( !file_exists( $path ) ) {
			$out = sprintf($this->lang['error_file_not_found'], $path);
		}
		return $out;
	}

	//--------------------------------------------
	/**
	 * Update an ini file
	 *
	 * @access public
	 * @param string $path
	 * @param array $data
	 * @param array $where
	 * @return string|array
	 */
	//--------------------------------------------
	function update( $path, $data, $where ) {

		#echo 'update:';
		#echo '<br>path - '.$path;
		#echo '<pre>';
		#print_r( $data );
		#echo'</pre>';

		if( file_exists( $this->db.$path ) ) {
			$update = $this->select( $path, null, $where );
			$out    = $this->select( $path, null );
			if(isset($update) && is_array($update)) {
				foreach($update as $k => $v) {
					$out[$k] = array_merge($out[$k], $data);
				}
				return $this->__write( $path, $out );
			}
		}		
		else if( !file_exists( $path ) ) {
			return sprintf($this->lang['error_file_not_found'], $path);
		}	
			
	}

	//--------------------------------------------
	/**
	 * Insert data into an ini file
	 *
	 * @access public
	 * @param string $path
	 * @param array $data
	 * @return string
	 */
	//--------------------------------------------
	function insert( $path, $data ) {

		#echo 'insert:';
		#echo '<br>path - '.$path;
		#echo '<pre>';
		#print_r( $data );
		#echo'</pre>';

		$out = $this->select( $path );
		if(is_array($out)) {
			$out[] = $data;
		} else {
			$out = array($data);
		}
		return $this->__write( $path, $out );
	}

	//--------------------------------------------
	/**
	 * Delete an entry in an ini file
	 *
	 * @access public
	 * @param string $path
	 * @param array $where
	 * @return string
	 */
	//--------------------------------------------
	function delete( $path, $where ) {

		#echo 'delete:';
		#echo '<br>path - '.$path;
		#echo '<pre>';
		#print_r( $data );
		#echo'</pre>';

		if( file_exists( $this->db.$path ) ) {
			$delete = $this->select( $path, null, $where );
			$out    = $this->select( $path, null );
			if(is_array($delete) && is_array($out)) {
				foreach($delete as $k => $v) {
					unset($out[$k]);
				}
				return $this->__write( $path, $out );
			} else {
				return '';
			}
		}		
		else if( !file_exists( $path ) ) {
			return sprintf($this->lang['error_file_not_found'], $path);
		}	
			
	}
	
	//-------------------------------------------------------
	/**
	 * Create an ini file ($path) from an array
	 *
	 * " will be converted to &#34;
	 * empty values will not be saved
	 *
	 * @access protected
	 * @param $path string
	 * @param $data array
	 * @return string
	 */
	//-------------------------------------------------------
	function __write( $path, $data ) {

		#echo '__write:';
		#echo '<br>path - '.$path;
		#echo '<pre>';
		#print_r( $data );
		#echo'</pre>';

		// handle path
		$path = $this->db.$path;

		$str = '';
		if(is_array($data)) {
			$fp = @fopen($path, 'w+');
			if($fp) {
				foreach($data as $k => $v) {
					if(!is_array($v) && $v !== '') {
						fwrite($fp, trim($k).' = "'.trim(str_replace('"', '&#34;', $v))."\"\n");
					} else {
						fwrite($fp, '['.trim($k).']'."\n");
						foreach($v as $sk => $sv) {
							if($sv !== '') { 
								fwrite($fp, trim($sk).' = "'.trim(str_replace('"', '&#34;', $sv))."\"\n");
							}
						}
					}
				}
				fclose($fp);
				$this->__chmod($path);
			} else {
				$str = sprintf($this->lang['error_write'], $path);
			}
		} else {
			$str = $this->lang['error_not_array'];
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * set file permissions
	 *
	 * @access protected
	 * @param $path string
	 */
	//-------------------------------------------------------
	function __chmod($path) {
		if(is_file($path)) @chmod($path, $this->permissions_file);
		if(is_dir($path))  @chmod($path, $this->permissions_dir);
	}

}
?>
