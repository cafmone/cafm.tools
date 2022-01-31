<?php
/**
 * DB
 *
 * @package phppublisher
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2011, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query
{
/**
* db host
* @access public
* @var string
*/
var $host;
/**
* db
* @access public
* @var string
*/
var $db;
/**
* db user
* @access public
* @var string
*/
var $user;
/**
* db pass
* @access public
* @var string
*/
var $pass;
/**
* db type
* @access public
* @var enum [mysql]
*/
var $type;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path
	 */
	//--------------------------------------------
	function __construct( $path ) {
		$this->__path = realpath($path);
	}

	//---------------------------------------
	/**
	 * Set db handler
	 *
	 * @access public
	 */
	//---------------------------------------
	function handler() {
		if(isset($this->type)) {
			strtolower($this->type);
			if(!isset($this->handler)) {
				$this->handler = $this->__factory( $this->type );
			}
			return $this->handler;
		} else {
			echo '<b>ERROR:</b> Query type not defined. Please check your settings.<br>';
		}
	}

	//---------------------------------------
	/**
	 * SELECT
	 *
	 * @access public
	 * @param string $path
	 * @param array $data
	 * @param string|array $where
	 * @param string|array $order
	 * @return string
	 */
	//---------------------------------------
	function select($path, $data = '*', $where = null, $order = null, $limit = null) {
		$this->handler();
		if(isset($where)) {
			$where = $this->__where($where);
		}
		if(isset($order)) {
			$order = $this->__order($order);
		}
		if(isset($limit)) {
			$limit = $this->__limit($limit);
		}
		if($this->type === 'mysql') {
			$path = $this->handler->escape($path);
			$keys = '';
			if (is_array($data)) {
				$c = count($data);
				$i = 1;
				foreach($data as $key) {
					$keys .= "`".$this->handler->escape($key)."`";
					if($c > 1 && $c > $i) {
						$keys .= ', ';
					}
					$i++;
				}
			} else {
				$keys = $this->handler->escape($data);
			}
			$result = $this->handler->query("SELECT $keys FROM `$path` $where $order $limit");
		} 
		else if($this->type === 'file') {
			if($data === '*') { $data = null; }
			$result = $this->handler->query("SELECT", $path, $data, $where, $order, $limit);
		}
		if(!isset($result)) { $result = ''; }
		return $result;
	}

	//---------------------------------------
	/**
	 * INSERT
	 *
	 * @access public
	 * @param string $path
	 * @param array $data array('key' => 'value', ...)
	 * @return string
	 */
	//---------------------------------------
	function insert( $path, $data ) {
		$this->handler();
		if($this->type === 'mysql') {
			$path   = $this->handler->escape($path);
			$count  = count($data);
			$values = '';
			$keys   = '';
			$i      = 1;
			foreach($data as $key => $value) {
				if($value !== '') {
					$values .= "'".$this->handler->escape($value)."'";
				} else {
					$values .= 'NULL';
				}
				$keys .= "`".$this->handler->escape( $key )."`";
				if($count > 1 && $count > $i) {
					$values .= ',';
					$keys   .= ',';
				}
				$i++;
			}
			$result = $this->handler->query("INSERT INTO `$path` (".$keys.") VALUES (".$values.")");
			if(!isset($result)) { $result = ''; }
		} 
		else if($this->type === 'file') {
			$result = $this->handler->query("INSERT", $path, $data);
		}
		return $result;
	}

	//---------------------------------------
	/**
	 * UPDATE
	 *
	 * @access public
	 * @param string $path table
	 * @param array $data array('key' => 'value', ...)
	 * @param array $where array('haystack', 'needle');
	 * @return string
	 */
	//---------------------------------------
	function update($path, $data, $where) {
		$this->handler();
		$result = '';
		if($this->type === 'mysql') {
			$path  = $this->handler->escape($path);
			$count = count($data);
			$set   = '';
			$i     = 1;
			foreach($data as $key => $value) {
				if($value !== '') {
					$value = "'".$this->handler->escape($value)."'";
				} else {
					$value = 'NULL';
				}
				$set .= "`".$this->handler->escape($key)."`=".$value;
				if($count > 1 && $count > $i) {
					$set .= ',';
				}
				$i++;
			}
			$where = $this->__where($where);
			$result = $this->handler->query("UPDATE $path SET $set $where");
			if(!isset($result)) { $result = ''; }
		}
		if($this->type === 'file') {
			$result = $this->handler->query("UPDATE", $path, $data, $where);
		}
		return $result;
	}

	//---------------------------------------
	/**
	 * DELETE
	 *
	 * @access public
	 * @param string $path
	 * @param array $where array('haystack', 'needle');
	 * @return string
	 */
	//---------------------------------------
	function delete($path, $where = '') {
		$this->handler();
		if($this->type === 'mysql') {
			$path   = $this->handler->escape($path);
			if($where !== '') {
				$where  = $this->__where($where);
			}
			$result = $this->handler->query("DELETE FROM $path $where");
			if(!isset($result)) { $result = ''; }
		}
		if($this->type === 'file') {
			$result = $this->handler->query("DELETE", $path, null, $where);
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
		$this->handler();
		return $this->handler->last_insert_id();
	}	

	//---------------------------------------
	/**
	 * Close connection
	 *
	 * @access public
	 */
	//---------------------------------------
	function close() {
		if(isset($this->handler)) {
			$this->handler->close();
		}
	}

	//---------------------------------------
	/**
	 * Debug
	 *
	 * @access public
	 * @param bool $mode
	 */
	//---------------------------------------
	function debug( $mode = true ) {
		if(!isset($this->handler)) {
			$this->handler();
		}
		$this->handler->debug = $mode;
	}

	//---------------------------------------
	/**
	 * Handle WHERE clause
	 *
	 * AND = array(
	 *        'haystack1' => 'needle1',
	 *        'haystack2' => 'needle2'
	 *      );
	 * OR  = array(
	 *        'haystack1', 'needle1',
	 *        'haystack2', 'needle2'
	 *      );
	 *
	 * @access protected
	 * @param array $where array('haystack', 'needle');
	 */
	//---------------------------------------
	function __where($where) {
		switch ($this->type) {
			case 'mysql':
				if(is_array($where)) {
					$r = '';
					// number indexed array?
					if(array_values($where) === $where) {
						$num = count($where);
						for ($i=0; $i<$num;$i+2) {
							if($i === 0) {
								$a = $this->handler->escape($where[$i]);
								$b = $this->handler->escape($where[$i+1]);
								$r = "WHERE `$a`='$b'";
							} else {
								$a  = $this->handler->escape($where[$i]);
								$b  = $this->handler->escape($where[$i+1]);
								$r .= " OR `$a`='$b'";
							}
							$i = $i+2;
						}
					} else {
						$i = 0;
						foreach ($where as $k => $v) {
							if($i === 0) {
								$a = $this->handler->escape($k);
								$b = $this->handler->escape($v);
								$r = "WHERE `$a`='$b'";
							}
							else {
								$a  = $this->handler->escape($k);
								$b  = $this->handler->escape($v);
								$r .= " AND `$a`='$b'";
							}
							$i++;
						}
					}
					return $r;
				}
				if(is_string($where) && $where !== '') {
					#$where = $this->handler->escape($where);
					$r = "WHERE $where";
					return $r;
				}
			break;	
			case 'file':
				return $where;
			break;
		}
	}

	//---------------------------------------
	/**
	 * Handle ORDER BY clause
	 *
	 * @access protected
	 * @param string $order;
	 * @TODO check order on mysql (`) ?
	 */
	//---------------------------------------
	function __order($order) {
		if($this->type === 'mysql') {
			if(is_array($order)) {
				$a = implode(',', $order);
				$a = $this->handler->escape($a);
				$r = "ORDER BY $a";
			}
			if(is_string($order)) {
				$a = $this->handler->escape($order);
				$r = "ORDER BY $a";
			}
			return $r;
		}
		if($this->type === 'file') {
			return $order;
		}
	}

	//---------------------------------------
	/**
	 * Handle LIMIT clause
	 *
	 * @access protected
	 * @param string $order;
	 * @return string
	 */
	//---------------------------------------
	function __limit($limit) {
		if($this->type === 'mysql') {
			#if(is_integer($limit)) {
				$a = $this->handler->escape($limit);
				$r = "LIMIT $a";
			#}
			return $r;
		}
		if($this->type === 'file') {
			return $limit;
		}
	}

	//--------------------------------------------
	/**
	 * Build Objects
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function __factory( $name ) {
		$class = $name;
		if($name === '') {
			$name = 'query';
		}
		if($name !== 'query') {
			$class = 'query_'.$name;
			$name  = 'query.'.$name;
		}
		require_once( $this->__path.'/'.$name.'.class.php' );
		return new $class( $this->host, $this->user, $this->pass, $this->db );
	}

}
?>
