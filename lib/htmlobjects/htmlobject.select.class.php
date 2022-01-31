<?php
/**
 * Select
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_select extends htmlobject_base
{
/**
* Disable select
*
* @access public
* @var bool
*/
var $disabled = false;
/**
* Allow multiple selection
*
* @access public
* @var bool
*/
var $multiple = false;
/**
* Attribute name
*
* @access public
* @var string
*/
var $name = '';
/**
* Array of option values
*
* @access public
* @var array
*/
var $selected = array();
/**
* Number of lines to be shown
*
* @access public
* @var int
*/
var $size = '';
/**
* Attribute tabindex
*
* @access public
* @var int
*/
var $tabindex = '';

	//-------------------------------------------------
	/**
	 * Add options to select
	 *
	 * @access public
	 * @param array | htmlobject_option $content
	 * @param array $index
	 * <code>
	 * $content['arg1'] = 'dummyvalue1';
	 * $content['arg2'] = 'dummy label';
	 * $index = array('arg1','arg2');
	 * $select->add($content, $index);
	 *
	 * $content[0][0] = 'dummyvalue2';
	 * $content[0][1] = 'dummy label';
	 * $index = array(0,1);
	 * $select->add($content, $index);
	 * </code>
	 */
	//-------------------------------------------------
	function add($content, $index = array('value','label')) {
		if(is_array($content)) {
			foreach($content as $key => $value) {
				$o = new htmlobject_option();
				if(is_array($value) && isset($index[0]) && isset($index[1])) {
					if(isset($value[$index[0]]) && isset($value[$index[1]])) {
						$label = $value[$index[1]];
						if($label === '') {
							$label = '&#160;';
						}
						$o->value = $value[$index[0]];
						$o->label = $label;
						$this->__elements[$o->value] = $o;
					}
				}
				elseif($value instanceof htmlobject_option) {
					if($value->value !== '') {
						$o = $value;
						$this->__elements[$o->value] = $o;
					}
				} 
				elseif( isset($index[0]) &&
						isset($index[1]) &&
						isset($content[$index[0]]) &&
						isset($content[$index[1]])) {
					$o->value = $content[$index[0]];
					$o->label = $content[$index[1]];
					$this->__elements[$o->value] = $o;
				}
			}
		}
		elseif($content instanceof htmlobject_option) {
			if($content->label !== '' && $content->value !== '') {
				$o = $content;
				$this->__elements[$o->value] = $o;
			}
		}

	}

	//-------------------------------------------------
	/**
	 * Get elements
	 *
	 * @access public
	 * @return array|empty
	 */
	//-------------------------------------------------
	function get_elements() {
		if(isset($this->__elements)){
			return $this->__elements;
		} else {
			return '';
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
		$_str  = "<select$attribs>";
		$_str .= $this->__elements();
		$_str .= "</select>";
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
		if ($this->disabled === true) { $str .= ' disabled="disabled"'; }
		if ($this->multiple === true) { $str .= ' multiple="multiple"'; }
		if ($this->name != '')        { $str .= ' name="'.$this->name.'"'; }
		if ($this->size != '')        { $str .= ' size="'.$this->size.'"'; }
		if ($this->tabindex != '')    { $str .= ' tabindex="'.$this->tabindex.'"'; }
		return $str;
	}

	//-------------------------------------------------
	/**
	 * Get options
	 *
	 * @access protected
	 * @return string
	 */
	//-------------------------------------------------
	function __elements() {
		$_str = '';
		if(isset($this->__elements)){
			foreach ($this->__elements as $key => $obj) {
				if(in_array($key, $this->selected)) {
					$obj->selected = true;
				}
				$_str .= $obj->get_string();
			}
		} else {
			$_str .= '';
		}
		return $_str;
	}

}
?>
