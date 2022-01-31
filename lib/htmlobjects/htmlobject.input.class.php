<?php
/**
 * Input
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_input extends htmlobject_base
{
/**
* Check Input
*
* @access public
* @var bool
*/
var $checked = false;
/**
* Disable select
*
* @access public
* @var bool
*/
var $disabled = false;
/**
* Maxlength
*
* @access public
* @var int
*/
var $maxlength;
/**
* Attribute name
*
* @access public
* @var string
*/
var $name = '';
/**
* Attribute readonly
*
* @access public
* @var bool
*/
var $readonly = false;
/**
* Size of input
*
* @access public
* @var int
*/
var $size;
/**
* Attribute tabindex
*
* @access public
* @var int
*/
var $tabindex;
/**
* Type of element
*
* @access public
* @var enum [text|password|checkbox|radio|submit|reset|file|hidden|image|button]
*/
var $type = 'text';
/**
* Value of input
*
* @access public
* @var string
*/
var $value = '';

	//-------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//-------------------------------------------------
	function get_string() {
	$_strReturn = '';
		$attribs = $this->__attribs();
		$_strReturn = "<input$attribs>";
	return $_strReturn;
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
		if ($this->type === '') { $this->type = 'text'; } 
		($this->css !== '') ? $this->css = $this->css.' '.$this->type : $this->css = $this->type;

		$str = parent::__attribs();
		if ($this->checked !== false)  	{ $str .= ' checked="checked"'; }
		if ($this->disabled === true)	{ $str .= ' disabled="disabled"'; }
		if ($this->readonly === true)	{ $str .= ' readonly="readonly"'; }
		if (isset($this->maxlength))	{ $str .= ' maxlength="'.$this->maxlength.'"'; }
		if ($this->name !== '')  		{ $str .= ' name="'.$this->name.'"'; }
		if (isset($this->size))			{ $str .= ' size="'.$this->size.'"'; }
		if (isset($this->tabindex))  	{ $str .= ' tabindex="'.$this->tabindex.'"'; }
		if ($this->value !== '')  		{ $str .= ' value="'.$this->value.'"'; }
		$this->type = strtolower($this->type);
		switch($this->type) {
			case 'text':
			case 'password':
			case 'checkbox':
			case 'radio':
			case 'submit':
			case 'reset':
			case 'hidden':
			case 'image':
			case 'button':
			case 'file':
				$str .= ' type="'.$this->type.'"';
			break;
		}
		return $str;
	}

}
?>
