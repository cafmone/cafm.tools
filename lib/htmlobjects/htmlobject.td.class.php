<?php
/**
 * Td
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_td extends htmlobject_base
{
/**
* Align
*
* @access public
* @var enum [left|center|right]
*/
var $align = '';
/**
* Colspan
*
* @access public
* @var int
*/
var $colspan = '';
/**
* Rowspan
*
* @access public
* @var int
*/
var $rowspan = '';
/**
* Td type
*
* @access public
* @var enum [td|th]
*/
var $type = 'td';

	//------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array|string|object $text
	 * @param string $key key for elements array
	 */
	//------------------------------------------------
	function add($text = '', $key = null) {
		if(is_array($text)) {
			foreach($text as $k => $v) {
				$this->__elements[$k] = $v;
			}
		} else {
			if(isset($key)) {
				$this->__elements[$key] = $text;
			} else {
				$this->__elements[] = $text;
			}
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
		$str     = '';
		$attribs = $this->__attribs();
		$text    = '';
		if(isset($this->__elements)) {
			$text = $this->__elements;
		}
		if(!is_array($text)) {
			$text = array($text);
		}

    	$k = array_keys($text);
    	$s = sizeOf($k);
		reset($text);

		for($i = 0; $i < $s; ++$i) {
			$value = $text[$k[$i]];
			if(is_object($value)) {
				$str .= $value->get_string();
			} else {
				if($value === '') { $value = '&#160;'; }
				$str .= $value;
			}
		}
		$_str  = "\n<$this->type$attribs>";
		$_str .= $str;
		$_str .= "</$this->type>";
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
		if ($this->align   !== '') { $str .= ' align="'.$this->align.'"'; }
		if ($this->colspan !== '') { $str .= ' colspan="'.$this->colspan.'"'; }
		if ($this->rowspan !== '') { $str .= ' rowspan="'.$this->rowspan.'"'; }
		return $str;
	}

}
?>
