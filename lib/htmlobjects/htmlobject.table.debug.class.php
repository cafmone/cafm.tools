<?php
/**
 * Table Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_table_debug extends htmlobject_table
{
	//-------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array | htmlobject_tr $data
	 * @param string $key key for elements array
	 */
	//-------------------------------------------------
	function add($data = '', $key = null) {
		if(is_array($data) || $data instanceof htmlobject_tr) {
		}
		else {
			if($data === '') {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'NOTICE',  'data is empty or not set');
			} else {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				htmlobject_debug::_print( 'ERROR',  'add method does support array or htmlobject_tr as content only');
			}
		}
		return parent::add($data, $key);
	}


}
?>
