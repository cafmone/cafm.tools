<?php
/**
 * Base Class
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_base
{
/**
* Attribute class
*
* @access public
* @var string
*/
var $css = '';
/**
* Custom Attributes
*
* @access public
* @var string
*/
var $customattribs = '';
/**
* Add an eventhandler
*
* @access public
* @var string
*/
var $handler = '';
/**
* Attribute id
*
* @access public
* @var string
*/
var $id = '';
/**
* Attribute style
*
* @access public
* @var string
*/
var $style = '';
/**
* Attribute title
*
* @access public
* @var string
*/
var $title = '';

	//------------------------------------------------
	/**
	 * Add custom attributes as string
	 *
	 * @access public
	 * @param string $str
	 */
	//------------------------------------------------
	function setAttributes( $str ) {
		$this->customattribs = $str;
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
		$str = '';
		if ($this->css !== '') { $str .= ' class="'.$this->css.'"'; }
		if ($this->style !== '') { $str .= ' style="'.$this->style.'"'; }
		if ($this->title !== '') { $str .= ' title="'.$this->title.'"'; }
		if ($this->handler != '') { $str .= ' '.$this->handler; }
		if ($this->id !== '') { $str .= ' id="'.$this->id.'"'; }
		if ($this->customattribs !== '') { $str .= ' '.$this->customattribs; }
		return $str;
	}

}
?>
