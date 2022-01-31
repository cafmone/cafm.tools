<?php
/**
 * Template
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_template
{

	//------------------------------------------------
	/**
	* Constructor
	*
	* @access public
	* @param string $template path to template file
	*/
	//------------------------------------------------
	function __construct($template) {
		$this->__template = $template;
	}

	//------------------------------------------------
	/**
	* Add string, object or array to template
	*
	* @access public
	* @param string | object | float | integer | array $content
	* @param string $key
	*/
	//------------------------------------------------
	function add($content, $key = null) {
		$this->__keys['?'] = $this->__varname('?');
		if(!isset($this->__elements['?'])) {
			$this->__elements['?'] = '';
		}
		if (is_float($content) || is_int($content)) {
			settype($content, 'string');
		}
		if(
			$content instanceof htmlobject_form ||
			$content instanceof htmlobject_form_debug
		) {
			$vars = $content->get_elements();
		}
		elseif (is_object($content) && isset($key)) {
			$vars[$key] = $content;
		}
		elseif (is_string($content) && isset($key)) {
			$vars[$key] = $content;
		}
		elseif (is_array($content) && isset($key)) {
			$vars[$key] = $content;
		}
		elseif (is_array($content) && !isset($key)) {
			$vars = $content;
		}
		if(isset($vars)) {
			foreach($vars as $k => $v) {
				if ($k !== '') {
					if(!isset($this->__elements[$k])) {
						$this->__elements['?'] .= '&#123;'.$k.'&#125;';
					}
					$this->__keys[$k] = $this->__varname($k);
					$this->__elements[$k]  = $v;
				}
			}
		}
	}

	//------------------------------------------------
	/**
	* Get one or all elements
	*
	* @access public
	* @param string $name
	* @return array | object | null
	*/
	//------------------------------------------------
	function get_elements( $name = null ) {
		if(isset($this->__elements)) {
			$return = null;
			$tmp = $this->__elements['?'];
			unset($this->__elements['?']);
			if(isset($name)) {
				if(isset($this->__elements[$name])) {
					$return = $this->__elements[$name];
				}
			} else {
				$return = $this->__elements;
			}
			$this->__elements['?'] = $tmp;
			return $return;
		}
	}

	//------------------------------------------------  
	/**
	* Get Template as string
	*
	* @access public
	* @return string
	*/
	//------------------------------------------------
	function get_string() {
		return $this->parse($this->__get_file());
	}

	//------------------------------------------------  
	/**
	* Get vars from template file
	*
	* @access public
	* @return array | null
	*/
	//------------------------------------------------
	function get_vars() {
		preg_match_all('~{(.*?)}~i', $this->__get_file(), $matches );
		if(isset($matches[1][0])) {
			return $matches[1];
		}
	}

	//------------------------------------------------  
	/**
	* Set Template
	*
	* @access public
	* @param string $path
	* @return null
	*/
	//------------------------------------------------
	function set_template( $path ) {
		$this->__template = $path;
	}

	//---------------------------------------
	/**
	 * Group elements by params
	 *
	 * @access public
	 * @param array $params (substring=>replace)
	 */
	//---------------------------------------
	function group_elements( $params ) {
		if(isset($this->__elements)) {
			$a = array();
			foreach($this->__elements as $key => $value) {
				if($key !== '?') {
					$changed = false;
					foreach($params as $param => $replace) {
						if( strpos($key, $param) !== false ) {
							$a[$replace][$key] = $value;
							$changed = true;
						}
					}
					if($changed === false) {
						$a[$key] = $value;
					}
				}
			}
			unset($this->__elements);
			unset($this->__keys);
			$this->add($a);
		}
	}

	//------------------------------------------------
	/**
	* Parse variables into file
	*
	* @access private
	* @return string
	*/
	//------------------------------------------------    
	function parse($string) {
		$search  = array();
		$replace = array();
		$i       = 0;
		if(isset($this->__keys)) {
			foreach($this->__keys as $key => $value) {
				$search[$i]  = $value;
				$replace[$i] = '';
				$element     = $this->__elements[$key];
				if(!is_array($element)) {
					$element = array($element);
				}
				foreach($element as $v) {
					if(is_object($v)) {
						$replace[$i] .= $v->get_string();
					} else {
						$replace[$i] .= $v;
					}
				}
				++$i;
			}
			return str_replace($search, $replace, $string);
		} else {
			return $string;
		}
	}

	//------------------------------------------------
	/**
	* Remove an element by name
	*
	* @access public
	* @param  string $name
	*/
	//------------------------------------------------
	function remove($name) {
		unset($this->__elements[$name]);
		unset($this->__keys[$name]);
	}

	//------------------------------------------------
	/**
	* Protect a replacement variable
	*
	* @access private
	* @param  string $varname
	* @return string replaced variable
	*/
	//------------------------------------------------
	function __varname($varname) {
		return "{".$varname."}";
	}

	//------------------------------------------------
	/**
	* Get template file
	*
	* @access private
	* @return mixed FALSE if error, string if ok
	*/
	//------------------------------------------------
	function __get_file() {
		$str = '';
		$filename = $this->__template;
		if (!file_exists($filename)) {
			$this->__halt(sprintf("filename: file %s does not exist.",$filename));
			return false;
		}
		if (function_exists("file_get_contents")) {
			$str = @file_get_contents($filename);
			if($str === false) {
				$this->__halt("loadfile: couldn't open $filename");
				return false;
			}
		} else {
			if (!$fp = @fopen($filename,"r")) {
				$this->__halt("loadfile: couldn't open $filename");
				return false;
			}
			$str = @fread($fp,filesize($filename));
			@fclose($fp);
		}
		if ($str === '') {
			//$this->__halt("could not read $filename");
			return false;
		}
		return $str;
	}

	//------------------------------------------------
	/**
	* Error message to show
	*
	* @access private
	* @param string $msg
	*/
	//------------------------------------------------
	function __halt($msg) {
		printf("<b>Template Error:</b> %s<br>\n", $msg);
	}

}
?>
