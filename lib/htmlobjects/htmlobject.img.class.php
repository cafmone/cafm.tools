<?php
/**
 * Image
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2011, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_img extends htmlobject_base
{
/**
* Align image
*
* @access public
* @var enum [top | middle | bottom | left | right]
*/
var $align = '';
/**
* Alternative text
*
* @access public
* @var string
*/
var $alt = '';
/**
* Border
*
* @access public
* @var int
*/
var $border = '';
/**
* Height of image
*
* @access public
* @var int
*/
var $height = '';
/**
* Horizontal distance to text
*
* @access public
* @var int
*/
var $hspace = '';
/**
* Serverside handling map
*
* @access public
* @var bool
*/
var $ismap = false;
/**
* URI to long description text
*
* @access public
* @var string URI (RFC 2396)
*/
var $longdesc = '';
/**
* Name of image
*
* @access public
* @var string
*/
var $name = '';
/**
* Source of image
*
* @access public
* @var string
*/
var $src = '';
/**
* Source of image map
*
* @access public
* @var string
*/
var $usemap = '';
/**
* Vertical distance to text
*
* @access public
* @var int
*/
var $vspace = '';
/**
* Width of image
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
		$str = '';
		$attribs = $this->__attribs();
		$str = "<img$attribs>";
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
		if ($this->align !== '') 	{ $str .= ' align="'.$this->align.'"'; }
		if ($this->alt !== '')  	{ $str .= ' alt="'.$this->alt.'"'; }
		if ($this->border !== '')  	{ $str .= ' border="'.$this->border.'"'; }
		if ($this->height !== '')  	{ $str .= ' height="'.$this->height.'"'; }
		if ($this->hspace !== '')  	{ $str .= ' hspace="'.$this->hspace.'"'; }
		if ($this->ismap === true) 	{ $str .= ' ismap="ismap"'; }
		if ($this->longdesc !== '')	{ $str .= ' longdesc="'.$this->longdesc.'"'; }
		if ($this->name !== '')		{ $str .= ' name="'.$this->name.'"'; }
		if ($this->src !== '')		{ $str .= ' src="'.$this->src.'"'; }
		if ($this->usemap !== '')	{ $str .= ' usemap="'.$this->usemap.'"'; }
		if ($this->vspace !== '')	{ $str .= ' vspace="'.$this->vspace.'"'; }
		if ($this->width !== '')	{ $str .= ' width="'.$this->width.'"'; }
		return $str;
	}

}
?>
