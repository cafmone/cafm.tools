<?php
/**
 * Select Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_select_debug extends htmlobject_select
{
	//-------------------------------------------------
	/**
	 * add options to select
	 *
	 * @access public
	 * @param array | htmlobject_option $content
	 * @param array $index
	 */
	//-------------------------------------------------
	function add($content, $index = array('value','label')) {
		if(is_array($content) || $content instanceof htmlobject_option) {
			if(is_array($content)) {
				foreach($content as $key => $value) {
					$o = new htmlobject_option();
					if(is_array($value)) {
						if(isset($index[0]) && isset($index[1])) {
							if(isset($value[$index[0]]) && isset($value[$index[1]])) {
							} else {
								htmlobject_debug::_print( '', 'add', __CLASS__ );
								htmlobject_debug::_print( 'ERROR',  'could not find expected index '. $index[0].' or '.  $index[1]);
							}
						} else {
							htmlobject_debug::_print( '', 'add', __CLASS__ );
							htmlobject_debug::_print( 'ERROR',  'index array must be of type array("value", "label")');
						}
					}
					elseif ($value instanceof htmlobject_option) {
						if($value->label !== '' && $value->value !== '') {
						} else {
							htmlobject_debug::_print( '', 'add', __CLASS__ );
							htmlobject_debug::_print( 'ERROR',  'object must have attrib label and value');
						}
					}
					elseif( isset($index[0]) &&
							isset($index[1]) && 
							isset($content[$index[0]]) &&
							isset($content[$index[1]])) {
					} else {
						htmlobject_debug::_print( '', 'add', __CLASS__ );
						htmlobject_debug::_print( 'ERROR',  'could not find expected index '. $index[0].' or '.  $index[1]);
					}
				}
			}
		}
		elseif ($content instanceof htmlobject_option) {
			if($content->label !== '' && $content->value !== '') {
			} else {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'ERROR',  'object must have attrib label and value');
			}
		} else {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'ERROR',  'add method does support array or htmlobject_option as content only');
		}
		return parent::add($content, $index);
	}

	//-------------------------------------------------
	/**
	 * init attribs
	 *
	 * @access protected
	 */
	//-------------------------------------------------
	function __attribs() {
		if(!isset($this->name) || $this->name === '' ) {
			htmlobject_debug::_print( '', '__attribs', __CLASS__ );
			htmlobject_debug::_print( 'WARNING', 'attribute name not set' );
		} else {
			if($this->multiple === true ) {
				preg_match('~\[\]~', $this->name, $matches);
				if(!$matches) {
					htmlobject_debug::_print( '', '__attribs', __CLASS__ );
					htmlobject_debug::_print( 'WARNING', 'if multiple=true, name must be '.$this->name.'[]' );
				}
			}
		}
		return parent::__attribs();	
	}

}
?>
