<?php
/**
 * Custom Tag
 * 
 * Object to build a custom tag like
 * <pre>, <code>, <u>, <i> etc.
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_customtag extends htmlobject_div
{
/**
* HTML Tag
*
* @access public
* @var string
*/
var $tag = 'p';

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
		$str     = "\n<".$this->tag.$attribs.'>'.$str.'</'.$this->tag.'>';
		return $str;
	}

}
?>
