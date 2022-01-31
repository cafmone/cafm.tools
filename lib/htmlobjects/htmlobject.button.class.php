<?php
 /**
 * Button
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_button extends htmlobject_base
{
/**
* Disable button
*
* @access public
* @var bool
*/
var $disabled = false;
/**
* Label for button
*
* @access public
* @var string
*/
var $label = '';
/**
* Attribute name
*
* @access public
* @var string
*/
var $name = '';
/**
* Attribute tabindex
*
* @access public
* @var int
*/
var $tabindex = '';
/**
* Type of button
*
* @access public
* @var enum [button|submit|reset]
*/
var $type = '';
/**
* Value of button
*
* @access public
* @var string
*/
var $value = '';

	//------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//------------------------------------------------
	function get_string() {
	$_strReturn = '';
		$attribs = $this->__attribs();
		$_strReturn = "<button$attribs>$this->label</button>";
	return $_strReturn;
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
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value != '')  		{ $str .= ' value="'.$this->value.'"'; }
		$this->type = strtolower($this->type);
		switch($this->type) {
			case 'submit':
			case 'reset':
			case 'button':
				$str .= ' type="'.$this->type.'"';
			break;
			default:
				$str .= ' type="button"';
			break;
		}
		return $str;
	}

}
?>
