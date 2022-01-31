<?php
/**
 * Template Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_template_debug extends htmlobject_template
{

	//------------------------------------------------  
	/**
	* Get Template as string
	*
	* @access public
	* @return string
	*/
	//------------------------------------------------
	function get_string() {
		$elements = parent::get_elements();
		$vars = parent::get_vars(); 
		if(isset($elements) && is_array($elements)) {
			foreach($elements as $k => $v) {
				if(!in_array($k, $vars)) {
					if($k !== 'submit' && $k !== 'cancel' && $k !== 'reset') {
						htmlobject_debug::_print( '', 'get_string', __CLASS__ );
						htmlobject_debug::_print( 'NOTICE',  'found element '.$k.' but no replacement in '.$this->__template);
					}
				}

			}
		}
		return parent::get_string();
	}


}
?>
