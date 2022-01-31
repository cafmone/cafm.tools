<?php
/**
 * Option
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_option extends htmlobject_base
{
/**
* Disable option
*
* @access public
* @var bool
*/
var $disabled = false;
/**
* Label for option
*
* @access public
* @var string
*/
var $label = '';
/**
* Select option
*
* @access public
* @var bool
*/
var $selected = false;
/**
* Value of option
*
* @access public
* @var string
*/
var $value;

	//------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//------------------------------------------------	
	function get_string() {
		$_str = '';
		$attribs = $this->__attribs();
		$_str = "\n<option$attribs>".$this->label."</option>";
		return $_str;
	}

	//------------------------------------------------
	/**
	 * Init attribs
	 *
	 * @access protected
	 * @return string
	 */
	//------------------------------------------------
	function __attribs() {
		$str = parent::__attribs();
		if ($this->disabled === true) { $str .= ' disabled="disabled"'; }
		if ($this->selected === true) { $str .= ' selected="selected"'; }
		if ($this->label !== '')      { $str .= ' label="'.$this->label.'"'; }
		if (isset($this->value))      { $str .= ' value="'.$this->value.'"'; }
		return $str;
	}

}
?>
