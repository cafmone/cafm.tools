<?php
/**
 * Tr
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_tr extends htmlobject_base
{

	//------------------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param string | array | htmlobject_td $data
	 * @param string $key key for elements array
	 */
	//------------------------------------------------
	function add($data = '', $key = null) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if ($v instanceof htmlobject_td) {
					$this->__elements[$k] = $v;
				}
				elseif (is_string($v) || is_array($v)) {
					$td = new htmlobject_td();
					$td->add($v);
					$this->__elements[$k] = $td;
				}
			}
		}
		if ($data instanceof htmlobject_td) {
			if(isset($key)) {
				$this->__elements[$key] = $data;
			} else {
				$this->__elements[] = $data;
			}
		}		
		elseif (is_string($data) && $data !== '') {
			$td = new htmlobject_td();
			$td->add($data);
			if(isset($key)) {
				$this->__elements[$key] = $td;
			} else {
				$this->__elements[] = $td;
			}
		}
	}

	//------------------------------------------------
	/**
	 * Get html element as string
	 *
	 * @access public
	 * @return string
	 */
	//------------------------------------------------
	function get_string() {
		$_str = '';
		$attr = $this->__attribs();
		if(isset($this->__elements)) {
			$_str  = "\n<tr$attr>";
			foreach($this->__elements as $td) {
				$_str .= $td->get_string();
			}
			$_str .= "</tr>\n";
		}
		return $_str;
	}

}
?>
