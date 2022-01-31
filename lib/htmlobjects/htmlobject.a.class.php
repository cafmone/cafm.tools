<?php
/**
 * A
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_a extends htmlobject_div
{
/**
* URI (RFC 2396) to process request
*
* @access public
* @var string
*/
var $href = '';
/**
* Label for a
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
* Attribute target
*
* @access public
* @var string [_blank|_parent|_self|_top]
*/
var $target = '';

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
		$str     = '<a'.$attribs.'>'.$this->label.$str.'</a>';
		return $str;
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
		if ($this->target !== '')    { $str .= ' target="'.$this->target.'"'; }
		if ($this->name !== '')      { $str .= ' name="'.$this->name.'"'; }
		if ($this->tabindex !== '')  { $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->href !== '')      { $str .= ' href="'.htmlspecialchars($this->href).'"'; }
		return $str;
	}

}
?>
