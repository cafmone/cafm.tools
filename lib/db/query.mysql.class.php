<?php
/**
 * DB MySQL
 *
 * uses mysqli <http://php.net/manual/en/book.mysqli.php>
 *
 * @package db
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2011 - 2018, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_mysql
{
/**
* db object
* @access protected
* @var object
*/
var $mysql;
/**
* debug
* @access public
* @var bool
*/
var $debug = false;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @return string on error
	 */
	//--------------------------------------------
	function __construct($host, $user, $pass, $db = null) {
		$mysql = @new mysqli($host, $user, $pass, $db);
		if ($mysql->connect_error) {
			$this->error = 'Connect Error (' . $mysql->connect_errno . ') '. $mysql->connect_error;
		} else {
			$mysql->query("SET NAMES 'utf8'");
			$this->mysql = $mysql;
		}
	}

	//--------------------------------------------
	/**
	 * Query
	 *
	 * @access public
	 * @param string $query
	 * @return mixed null|string|array
	 */
	//--------------------------------------------
	function query($query, $multi = false, $assoc = true) {

		if($this->debug === true) {
			$this->__print($query);
		}

		if(isset($this->mysql)) {
			if($multi === true) {
				$result = $this->mysql->multi_query($query);
			} else {
				$result = $this->mysql->query($query);
			}
			if ($this->mysql->error) {
				$return  = 'Error (' . $this->mysql->errno . ') '. $this->mysql->error;
				$return .= '<br>'.$query;
				return $return;
			}
			if($result === false) {
				return 'false: '.$query;
			}
			if($result === true) {
				return null;
			}
			if($result instanceof mysqli_result) {
				if($assoc === false) {
					$assoc = MYSQLI_NUM;
				} else {
					$assoc = MYSQLI_ASSOC;
				}
				$return = array();
				while($row = mysqli_fetch_array($result, $assoc)) {
					$return[] = $row;
				}
				// handle empty result
				if(isset($return[0])) {
					return $return;
				} else {
					return '';
				}
			}
		} 
		else if (isset($this->error)) {
			return $this->error;
		}
		else {
			return 'MySQL connection error! Could not find mysql object.';
		}
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
		if(isset($this->mysql)) {
			return $this->mysql->insert_id;
		}
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
		if(isset( $this->mysql )) {
			return $this->mysql->real_escape_string($value);
		}
	}

	//--------------------------------------------
	/**
	 * Close db
	 *
	 * @access public
	 */
	//--------------------------------------------
	function close() {
		if(isset($this->mysql)) {
			$this->mysql->close();
		}
	}

	//--------------------------------------------
	/**
	 * get colums info
	 *
	 * @access public
	 * @param string $db name of database
	 * @param string $table name of table
	 * @param string $column name of column
	 * @return [array|string] string on error
	 */
	//--------------------------------------------
	function columns($db, $table, $column = '') {
		if(isset($this->mysql)) {
			$sql  = 'SELECT ';
			$sql .= 'COLUMN_NAME AS `Field`,';
			$sql .= 'IS_NULLABLE as `Null`,';
			$sql .= 'COLUMN_TYPE as `Type`,';
			$sql .= 'COLUMN_DEFAULT as `Default`,';
			$sql .= 'EXTRA as `Extra` ';
			$sql .= 'FROM INFORMATION_SCHEMA.COLUMNS ';
			$sql .= 'WHERE table_name = \''.$this->escape($table).'\' ';
			$sql .= 'AND table_schema = \''.$this->escape($db).'\'';
			if($column !== '') {
				$sql .= 'AND COLUMN_NAME = \''.$this->escape($column).'\'';
			}
			$columns = $this->query($sql);
			if(is_array($columns)) {
				foreach($columns as $column) {
					$type = preg_replace('~(.*)\((.*)\)(.*)~i', '$1', $column['Type']);
					$tmp['column']  = $column['Field'];
					$tmp['type']    = $type;
					switch($type) {
						case 'text':
							$tmp['length'] = NULL;
						break;
						default: 
							$tmp['length'] = intval(preg_replace('~(.*)\((.*)\)(.*)~i', '$2', $column['Type']));
						break;
					}
					$tmp['null']    = strtolower($column['Null']);
					$tmp['default'] = $column['Default'];
					$tmp['extra']   = strtolower($column['Extra']);
					$return[$column['Field']] = $tmp;
				}
			} else {
				$return = $columns;
			}
			return $return;
		}
		else if (isset($this->error)) {
			return $this->error;
		}
	}

	//------------------------------------------------
	/**
	 * print debug string
	 *
	 * @access protected
	 */
	//------------------------------------------------
	private function __print($query) {
		print '<pre>'.$query.'</pre>';
		print '<pre>backtrace {'."\n";
		foreach(debug_backtrace() as $key => $msg) {
			print '    ';
			print basename($msg['file']).' ';
			print 'line: '.$msg['line'].' ';
			print '['.$msg['class'].$msg['type'].$msg['function'].'()]';
			print "\n";
		}
		print '}</pre><br>';
	}

}
?>
