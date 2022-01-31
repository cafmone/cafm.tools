<?php
/**
 * Textarea
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_textarea extends htmlobject_base
{
/**
* Attribute cols
*
* @access public
* @var int
*/
var $cols = '';
/**
* Disable textarea
*
* @access public
* @var bool
*/
var $disabled = false;
/**
* Attribute name
*
* @access public
* @var string
*/
var $name = '';
/**
* Set textarea to readonly
*
* @access public
* @var bool
*/
var $readonly = false;
/**
* Number of rows
*
* @access public
* @var int
*/
var $rows = '';
/**
* Attribute tabindex
*
* @access public
* @var int
*/
var $tabindex = '';
/**
* Content of textarea
*
* @access public
* @var string
*/
var $value = '';
/**
* Wrap type
*
* @access public
* @var enum [hard|soft|off]
*/
var $wrap = '';


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
		$_str = "\n<textarea$attribs>\n";
		$_str .= $this->value;
		$_str .= "</textarea>\n";
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
		if ($this->cols != '')			{ $str .= ' cols="'.$this->cols.'"'; }
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->name != '')  		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->readonly === true)	{ $str .= ' readonly="readonly"'; }
		if ($this->rows != '')			{ $str .= ' rows="'.$this->rows.'"'; }
		if ($this->tabindex != '')  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->wrap != '')  		{ $str .= ' wrap="'.$this->wrap.'"'; }
		return $str;
	}

}
?>
