<?php
/**
 * Formbuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_formbuilder_debug extends htmlobject_formbuilder
{

	//---------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject $htmlobject
	 */
	//---------------------------------------
	function __construct( $htmlobject ) {
		parent::__construct($htmlobject);
	}

	//---------------------------------------
	/**
	 * Init Formbuilder
	 *
	 * @access public
	 */
	//---------------------------------------
	function init() {
		parent::init();
		$error = null;
		if(isset($this->__data)) {
			foreach($this->__data as $key => $value) {
				if(!isset($this->__elements[$key])) {
					$error[] = array('WARNING', 'could not find object for key '.$key);
				}
			}
		}
		if($error) {
			htmlobject_debug::_print( '', 'init', __CLASS__ );
			foreach( $error as $value ) {
				htmlobject_debug::_print( $value[0], $value[1] );
			}
		}
	}

	//---------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array $data
	 * @param dummy $key set for compatibility
	 */
	//---------------------------------------
	function add( $data, $key = null ) {

		$error = null;
		if(is_array($data)) {
			foreach($data as $key => $value) {
				if(is_array($value)) {
					if(isset($value['object']) && !is_object($value['object'])) {
						if(!isset($value['object']['type'])) {
							$error[] = array('ERROR', '["'.$key.'"]["object"]["type"] not set');
						} else {
							switch(str_replace('htmlobject_', '', strtolower($value['object']['type']))) {
								case 'input':
								case 'select':
								case 'textarea':
								case 'button':
									break;
								default:
									$error[] = array('ERROR', $value['object']['type'].' is not supported');
								break;
							}
						}

						if(!isset($value['object']['attrib'])) {
							$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"] not set');
						}

						if(!isset($value['object']['attrib']['name'])) {
							$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"]["name"] not set');
						}
						elseif ($value['object']['attrib']['name'] == '') {
							$error[] = array('ERROR', '["'.$key.'"]["object"]["attrib"]["name"] is empty');
						}
					}
					// debug validate
					if(isset($value['validate']) &&
						!isset($value['validate']['errormsg'])
					) {
						$error[] = array('ERROR', '["'.$key.'"]["validate"]["errormsg"] not set');
					}
					elseif (isset($value['validate']) &&
							$value['validate']['errormsg'] == ''
					) {
						$error[] = array('NOTICE', '["'.$key.'"]["validate"]["errormsg"] is empty');
					}
					if(isset($value['static']) && 
						$value['static'] === true &&
						isset($value['required']) &&
						$value['required'] === true
					) {
						$error[] = array('ERROR', 'a static element ('.$key.') can not be required');
					}
					// debug minlength, maxlength
					if(isset($value['object'])) {
						if(is_array($value['object'])) {
							if(
								isset($value['object']['attrib']['minlength']) &&
								isset($value['object']['attrib']['maxlength']) &&								
								$value['object']['attrib']['minlength'] > $value['object']['attrib']['maxlength']
							) {
								$error[] = array('ERROR', '["'.$key.'"] minlength exeeds maxlength');
							}
						}
						if(is_object($value['object'])) {
							if(
								isset($value['object']->minlength) &&
								isset($value['object']->maxlength) &&								
								$value['object']->minlength > $value['object']->maxlength
							) {
								$error[] = array('ERROR', '["'.$key.'"] minlength exeeds maxlength');
							}
						}
					}

				}
			}

			if($error) {
				htmlobject_debug::_print( '', 'add', __CLASS__ );
				foreach( $error as $value ) {
					htmlobject_debug::_print( $value[0], $value[1] );
				}
			}
		}
		parent::add( $data, $key );
	}
	
}
?>
