<?php
/**
 * Table
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_table extends htmlobject_base
{
/**
* Align
*
* @access public
* @var enum [left|center|right]
*/
var $align = '';
/**
* Table border
*
* @access public
* @var int
*/
var $border = '';
/**
* Table backgroundcolor
*
* @access public
* @var HEX
*/
var $bgcolor = '';
/**
* Cellpadding
*
* @access public
* @var int
*/
var $cellpadding;
/**
* Cellspacing
*
* @access public
* @var int
*/
var $cellspacing;
/**
* Frame
*
* @access public
* @var enum [void|above|below|hsides|lhs|rhs|vsides|box|border]
*/
var $frame = '';
/**
* Rules
*
* @access public
* @var enum [none|groups|rows|cols|all]
*/
var $rules = '';
/**
* Summary
*
* @access public
* @var string
*/
var $summary = '';
/**
* Width
*
* @access public
* @var int
*/
var $width = '';

	//------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array | htmlobject_tr $data
	 * @param string $key key for elements array
	 */
	//------------------------------------------------
	function add($data = '', $key = null) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if(is_array($v)) {
					$tr = new htmlobject_tr();
					$tr->add($v);
					$this->__elements[] = $tr;
				}
				elseif ($v instanceof htmlobject_tr) {
					$this->__elements[] = $v;
				}
			}
		}
		elseif ($data instanceof htmlobject_tr) {
			if(isset($key)) {
				$this->__elements[$key] = $data;
			} else {
				$this->__elements[] = $data;
			}
		}
	}

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
		$_str = "\n<table$attribs>";
		if(isset($this->__elements)) {
			foreach($this->__elements as $tr) {
				$_str .= $tr->get_string();
			}
		}
		$_str .= "</table>\n";
		return $_str;
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
		if ($this->align != '') { $str .= ' align="'.$this->align.'"'; }
		if (isset($this->border) && $this->border !== '') { $str .= ' border="'.$this->border.'"'; }
		if ($this->bgcolor != '') { $str .= ' bgcolor="'.$this->bgcolor.'"'; }
		if (isset($this->cellpadding) && $this->cellpadding !== '') { $str .= ' cellpadding="'.$this->cellpadding.'"'; }
		if (isset($this->cellspacing) && $this->cellspacing !== '') { $str .= ' cellspacing="'.$this->cellspacing.'"'; }
		if ($this->frame != '') { $str .= ' frame="'.$this->frame.'"'; }
		if ($this->rules != '') { $str .= ' rules="'.$this->rules.'"'; }
		if ($this->summary != '') { $str .= ' summary="'.$this->summary.'"'; }
		if ($this->width != '') { $str .= ' width="'.$this->width.'"'; }
		return $str;
	}

}
?>
