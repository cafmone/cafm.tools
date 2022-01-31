<?php
/**
 * Form Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_form_debug extends htmlobject_form
{

	function group_object( $params, $tostring = false ) {
		$error = null;
		$keys = array_keys($params);
		foreach($keys as $key) {
			if(!is_string($key)) $error[] = array('ERROR', '["'.$key.'"] must be of type string');
		}

		if($error) {
			htmlobject_debug::_print( '', 'group_object', __CLASS__ );
			foreach( $error as $value ) {
				htmlobject_debug::_print( $value[0], $value[1] );
			}
		}
		return parent::group_object( $params, $tostring );
	}

}
?>
