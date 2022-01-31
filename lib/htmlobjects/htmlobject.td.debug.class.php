<?php
/**
 * Td Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_td_debug extends htmlobject_td
{
	//-------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param string | array | htmlobject
	 * @param string $key key for elements array
	 */
	//-------------------------------------------------
	function add($text = '', $key = null) {
		if(
			is_array($text) || 
			is_string($text) || 
			is_object($text) ||
			is_float($text) || 
			is_int($text)
		) {
			if($text === '') {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'NOTICE',  'text is empty or not set');
			}
		}
		else {
			htmlobject_debug::_print( '', 'add', __CLASS__ );
			htmlobject_debug::_print( 'ERROR',  'add method does support array, string, integer or htmlobjects as content only');
		}
		return parent::add($text, $key);
	}


}
?>
