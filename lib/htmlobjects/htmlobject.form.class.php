<?php
/**
 * Form
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_form extends htmlobject_base
{
/**
* URI (RFC 2396) to process form data
*
* @access public
* @var string
*/
var $action = '';
/**
* MIME-Typ (RFC 2045) of form data
*
* @access public
* @var string
*/
var $enctype = 'multipart/form-data';
/**
* Post/Get
*
* @access public
* @var enum [post|get]
*/
var $method = 'get';
/**
* Name of form
*
* @access public
* @var string
*/
var $name = '';
/**
* Name of form response window
*
* @access public
* @var string
*/
var $target = '';

	//---------------------------------------
	/**
	 * Add object or array of objects
	 *
	 * @access public
	 * @param object | string | array $element
	 * @param string $key key for elements array
	 */
	//---------------------------------------
	function add($element, $key = null) {
		if(is_array($element)) {
			foreach($element as $key => $value) {
				$this->__elements[$key] = $value;
			}
		} else {
			if(isset($key)) {
				$this->__elements[$key] = $element;
			} else {
				$this->__elements[] = $element;
			}
		}
	}

	//---------------------------------------
	/**
	 * Get object or an array of objects
	 *
	 * @access public
	 * @return null|array of objects
	 */
	//---------------------------------------
	function get_elements( $name = null ) {
		if(isset($this->__elements)) {
			if(isset($name)) {
				return $this->__elements[$name];
			} else {
				return $this->__elements;
			}
		}
	}

	//---------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//---------------------------------------
	function get_string() {
		$str = '';
		if(isset($this->__elements)) {
			$arr = $this->get_elements();
			foreach($arr as $value) {
				if(is_object($value)) {
					$str .= $value->get_string();
				} else {
					$str .= $value;
				}
			}
		}
		$attribs = $this->__attribs();
		$_str  = '';
		$_str .= "\n<form$attribs>\n";
		$_str .= $str;
		$_str .= "\n</form>\n";
		return $_str;
	}

	//---------------------------------------
	/**
	 * Init attribs
	 *
	 * @access private
	 * @return string
	 */
	//---------------------------------------
	function __attribs() {
		$str = parent::__attribs();
		if ($this->action != '')  { $str .= ' action="'.$this->action.'"'; }
		if ($this->enctype != '') { $str .= ' enctype="'.$this->enctype.'"'; }
		if ($this->method != '')  { $str .= ' method="'.$this->method.'"'; }
		if ($this->name != '')    { $str .= ' name="'.$this->name.'"'; }
		if ($this->target != '')  { $str .= ' target="'.$this->target.'"'; }
		return $str;
	}

}
?>
