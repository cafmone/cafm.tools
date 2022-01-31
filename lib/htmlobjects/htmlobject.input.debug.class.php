<?php
 /**
 * Input Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_input_debug extends htmlobject_input
{

/**
 * supported input types
 * @access public
 * @var array
 */
var $supported = array(
		'text',
		'password',
		'checkbox',
		'file',
		'radio',
		'submit',
		'reset',
		'hidden',
		'image',
		'button',
	);

	//-------------------------------------------------
	/**
	 * init attribs
	 *
	 * @access protected
	 */
	//-------------------------------------------------
	function __attribs() {
		if(! in_array( $this->type , $this->supported) ) {
			htmlobject_debug::_print( '', '__attribs', __CLASS__ );
			htmlobject_debug::_print( 'ERROR', 'attribute type '. $this->type.' is not supported' );
		}
		if(!isset($this->name) || $this->name === '' ) {
			htmlobject_debug::_print( '', '__attribs', __CLASS__ );
			htmlobject_debug::_print( 'WARNING', 'input name not set' );
		}
		return parent::__attribs();	
	}
}
?>
