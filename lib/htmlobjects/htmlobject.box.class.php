<?php
/**
 * Box
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */ 
class htmlobject_box extends htmlobject_div
{
/**
* css class for left box
*
* @access public
* @var string
*/
var $css_left = 'left';
/**
* css class for right box
*
* @access public
* @var string
*/
var $css_right = 'right';
/**
* Label (Title) of box
*
* @access public
* @var string
*/
var $label = '';
/**
* Label for input
*
* @access public
* @var string
*/
var $label_for = '';

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
		$str = '';
		foreach($this->get_elements() as $value) {
			if(is_object($value)) {
				if(!isset($id) && isset($value->id)) {
					if($this->id === '') {
						$this->id = $value->id.'_box';
					}
					$id = $value->id;
				}
				$str .= $value->get_string();
			} else { $str .= $value; }
		}
		if($this->label !== '') {
			$attr  = $this->__attribs();
			$_str .= "\n<div".parent::__attribs().">";
			$_str .= "\n<div".$attr['left'].">";
			if($this->label_for !== '') { $_str .= '<label class="control-label" for="'.$this->label_for.'">'.$this->label.'</label>'; }
			elseif(isset($id)) { $_str .= '<label class="control-label" for="'.$id.'">'.$this->label.'</label>'; }
			else { $_str .= $this->label; }
			$_str .= "</div>";
			$_str .= "\n<div".$attr['right'].">";
			$_str .= $str;
			$_str .= "</div>";
			$_str .= "\n<div style=\"line-height:0px;height:0px;clear:both;\" class=\"floatbreaker\">&#160;</div>";
			$_str .= "\n</div>";
		} else {
			$_str .= $str;
		}
		return $_str;
	}

	//------------------------------------------------
	/**
	 * Init attribs
	 *
	 * @access protected
	 * @return array
	 */
	//------------------------------------------------
	function __attribs() {
		$a = array('left' => '', 'right' => '');
		if ($this->css_left !== '')  { $a['left'] = ' class="'.$this->css_left.'"'; }
		if ($this->css_right !== '') { $a['right'] = ' class="'.$this->css_right.'"'; }
		return $a;
	}


}
?>
