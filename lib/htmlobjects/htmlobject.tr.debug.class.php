<?php
/**
 * Tr Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_tr_debug extends htmlobject_tr
{
	//-------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param string | array | htmlobject_td
	 * @param string $key key for elements array
	 */
	//-------------------------------------------------
	function add($data = '', $key = null) {
		if(is_array($data) || is_string($data) || $data instanceof htmlobject_td) {
			if($data === '') {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'NOTICE',  'data is empty or not set');
			}
		}
		else {
			htmlobject_debug::_print( '', 'add', __CLASS__ );
			htmlobject_debug::_print( 'ERROR',  'add method does support array or htmlobject_td as content only');
		}
		return parent::add($data, $key);
	}


}
?>
