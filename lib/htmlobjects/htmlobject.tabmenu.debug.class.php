<?php
/**
 * Tabmenu
*
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_tabmenu_debug extends htmlobject_tabmenu
{

	//------------------------------------------------
	/**
	* Constructor
	*
	* @access public
	* @param string $prefix
	* @param htmlobject $htmlobject
	*/
	//------------------------------------------------
	function __construct($prefix, $htmlobject) {
		parent::__construct($prefix, $htmlobject);	
	}

	//------------------------------------------------
	/**
	 * Add content
	 *
	 * @access public
	 * @param array $data
	 * @param null $key not in use
	 */
	//------------------------------------------------
	function add($data, $key = null) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if(isset($v)) {
					if(is_array($v)) {
						if(
							isset($v['label']) ||
							isset($v['value']) ||
							isset($v['target']) ||
							isset($v['request']) ||
							isset($v['onclick']) ||
							isset($v['active'])
						) { } else {
							htmlobject_debug::_print( '', 'add', __CLASS__ );
							htmlobject_debug::_print( 'ERROR',  'could not find expected key(s) in array');
						}
					} else {
						htmlobject_debug::_print( '', 'add', __CLASS__ );
						htmlobject_debug::_print( 'ERROR',  'add supports arrays only');
					}
				}
			}
		} else {
			htmlobject_debug::_print( '', 'add', __CLASS__ );
			htmlobject_debug::_print( 'ERROR',  'add supports arrays only');
		}
		parent::add($data, $key);	
	}

	//------------------------------------------------
	/**
	* Get tabs as string
	*
	* @access public
	* @param array $arr
	* @return string
	*/
	//------------------------------------------------
	function get_string() {
		if(isset($this->__data)) {
			$current = $this->get_current();
			foreach($this->__data as $key => $value) {
				if(
					$this->__data[$key]['onclick'] === true &&
					(!isset($this->__data[$key]['value']) || $this->__data[$key]['value'] === '')
				) {
					htmlobject_debug::_print( '', 'init', __CLASS__ );
					htmlobject_debug::_print( 'WARNING',  'found key ['.$key.'] onclick = true but no value');
				}
				if(
					$current === $key &&
					!isset($this->__data[$key]['value'])
				) {
					htmlobject_debug::_print( '', 'init', __CLASS__ );
					htmlobject_debug::_print( 'NOTICE',  'no value set for key ['.$key.']');
				}
				if(	$this->__data[$key]['label'] === ''	) {
					htmlobject_debug::_print( '', 'init', __CLASS__ );
					htmlobject_debug::_print( 'ERROR',  'no label set for key ['.$key.']');
				}
			}
		}
		return parent::get_string();	
	}

}
?>
