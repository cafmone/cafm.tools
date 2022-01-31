<?php
/**
 * Div
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_div extends htmlobject_base
{

	//------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array | string | object $data
	 * @param string $key key for elements array
	 */
	//------------------------------------------------
	function add( $data, $key = null ) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$this->__elements[$k] = $v;
			}
		} else {
			if(isset($key)) {
				$this->__elements[$key] = $data;
			} else {
				$this->__elements[] = $data;
			}
		}		
	}

	//---------------------------------------
	/**
	 * Get array of objects
	 *
	 * @access public
	 * @param string $name name of element
	 * @return null | array of objects
	 */
	//---------------------------------------
	function get_elements( $name = null ) {
		if(isset($this->__elements)) {
			$a = array();
			if(isset($name)) {
				$a[$name] = $this->__elements[$name];
			} else {
				$a = $this->__elements;
			}
			return $a;
		}
	}

	//------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//------------------------------------------------	
	function get_string() {
		$attribs = $this->__attribs();
		$str     = $this->__str();
		$str     = "\n<div$attribs>$str</div>";
		return $str;
	}
	
	//---------------------------------------
	/**
	 * Elements to string
	 *
	 * @access protected
	 * @return string
	 */
	//---------------------------------------
	function __str() {
		$str = '';
		if(isset($this->__elements)) {
			foreach($this->__elements as $value) {
				if(is_object($value)) {
					$str .= $value->get_string();
				} else {
					$str .= $value;
				}
			}
		}
		return $str;
	}

}
?>
