<?php
/**
 * Iframe
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_iframe extends htmlobject_div
{
/**
* Align iframe
*
* @access public
* @var enum [top | middle | bottom | left | right]
*/
var $align = '';
/**
* Border
*
* @access public
* @var int
*/
var $frameborder = '';
/**
* Height of iframe
*
* @access public
* @var int
*/
var $height = '';
/**
* URI to long description text
*
* @access public
* @var string URI (RFC 2396)
*/
var $longdesc = '';
/**
* Name of iframe
*
* @access public
* @var string
*/
var $name = '';
/**
* Width of margin
*
* @access public
* @var int
*/
var $marginwidth = '';
/**
* Width of margin
*
* @access public
* @var int
*/
var $marginheight = '';
/**
* Scrolling
*
* @access public
* @var enum yes | no | auto
*/
var $scrolling = '';
/**
* Source of iframe
*
* @access public
* @var string URI (RFC 2396)
*/
var $src = '';
/**
* Width of iframe
*
* @access public
* @var int
*/
var $width = '';

	//-------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//-------------------------------------------------
	function get_string() {
		$attribs = $this->__attribs();
		$str     = $this->__str();
		$str     = '<iframe'.$attribs.'>'.$str.'</iframe>';
		return $str;
	}

	//-------------------------------------------------
	/**
	 * Init attribs
	 *
	 * @access protected
	 * @return string
	 */
	//-------------------------------------------------
	function __attribs() {
		$str = parent::__attribs();
		if ($this->align !== '') 		{ $str .= ' align="'.$this->align.'"'; }
		if ($this->frameborder !== '')	{ $str .= ' frameborder="'.$this->frameborder.'"'; }
		if ($this->height !== '')  		{ $str .= ' height="'.$this->height.'"'; }
		if ($this->longdesc !== '')		{ $str .= ' longdesc="'.$this->longdesc.'"'; }
		if ($this->marginwidth !== '')  { $str .= ' marginwidth="'.$this->marginwidth.'"'; }
		if ($this->marginheight !== '') { $str .= ' marginwidth="'.$this->marginheight.'"'; }
		if ($this->name !== '')			{ $str .= ' name="'.$this->name.'"'; }
		if ($this->scrolling !== '')	{ $str .= ' scrolling="'.$this->scrolling.'"'; }
		if ($this->src !== '')			{ $str .= ' src="'.$this->src.'"'; }
		if ($this->width !== '')		{ $str .= ' width="'.$this->width.'"'; }
		return $str;
	}

}
?>
