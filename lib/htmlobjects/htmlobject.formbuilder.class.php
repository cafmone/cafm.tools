<?php
/**
 * Formbuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class htmlobject_formbuilder extends htmlobject_form
{
/**
* Css class for boxes
*
* @access public
* @var string
*/
var $box_css = 'htmlobject_box';
/**
* Display errors inline
*
* @access public
* @var bool
*/
var $display_errors = true;
/**
* Request filter for form elements
*
* @access public
* @var array
* <code>
* $form = new htmlobject_formbuilder($htmlobject);
* $form->request_filter = array(
*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
*  );
* # disable filter
* $form->request_filter = array();
* </code>
*/
var $request_filter = array(
		array( 'pattern' => '~\r\n~', 'replace' => "\n"),
		array( 'pattern' => '~&#60;~', 'replace' => '<'),
		array( 'pattern' => '~&#34;~', 'replace' => '"'),
		array( 'pattern' => '~&#38;~', 'replace' => '&')
	);
/**
* Value filter for form elements
*
* uses str_replace
*
* @access public
* @var array
* <code>
* $form = new htmlobject_formbuilder($htmlobject);
* $form->value_filter = array(
*    array ( 'pattern' => '<', 'replace' => '&#60;'),
*  );
* # disable filter
* $form->value_filter = array();
* </code>
*/
var $value_filter = array(
		array( 'pattern' => '&', 'replace' => '&#38;'),
		array( 'pattern' => '<', 'replace' => '&#60;'),
		array( 'pattern' => '"', 'replace' => '&#34;')
	);


	//---------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject $htmlobject
	 */
	//---------------------------------------
	function __construct( $htmlobject ) {
		$this->html = $htmlobject;
	}

	//---------------------------------------
	/**
	 * Init Formbuilder
	 *
	 * @access public
	 */
	//---------------------------------------
	function init() {
		if(isset($this->__data)) {
			$ar = array();
			foreach($this->__data as $key => $value) {
				if(isset($value['object']) && is_object($value['object'])) {
					$this->__elements[$key] = $value['object'];
					unset($this->__data[$key]['object']);
				} 
				elseif(isset($value['object']) && is_array($value['object'])) {
					if(isset($value['object']['type'])) {
						$obj = str_replace('htmlobject_', '', strtolower($value['object']['type']));
						$obj = $this->html->$obj();
						foreach($value['object']['attrib'] as $akey => $attrib) {
							// handle value specialchars
							if(strtolower($akey) === 'value') {
								foreach($this->value_filter as $p) {
									$attrib = str_replace($p['pattern'],$p['replace'], $attrib);
								}
							}
							$obj->$akey = $attrib;
						}

						// handle bootstrap css -> input type file
						$bootstrap = 'form-control';
						if(isset($obj->type) && $obj->type === 'file') {
							$bootstrap = '';
						}
						// handle css
						if(isset($obj->css)) {
							$obj->css = $bootstrap.' '.$obj->css;
						} else {
							$obj->css = $bootstrap;
						}
						if($obj instanceof htmlobject_select || $obj instanceof htmlobject_select_debug) {
							if(isset($obj->options)) {
								if(isset($obj->index)) {
									$obj->add($obj->options, $obj->index);
									unset($obj->index);
								} else {
									$obj->add($obj->options);
								}
								unset($obj->options);
							}
						}
						unset($this->__data[$key]['object']);
						$this->__elements[$key] = $obj;
					}
				}
			}
			$this->__set_request();
			$this->__set_request_errors();
			$this->__set_elements_value();
		}
	}

	//---------------------------------------
	/**
	 * Add additional content
	 *
	 * @access public
	 * @param array $data
	 * @param dummy $key set for compatibility
 	 * <code>
	 * $html = new htmlobject('path_to_htmlobjects');
	 *
	 * $data['name']['label']    = 'Name';
	 * $data['name']['required'] = true;
	 * $data['name']['css']      = 'htmlobject_box';
	 * $data['name']['style']    = 'background: #ccc;';
	 * // static
	 * // element will not be returned by get_request()
	 * // to get value use method get_static()
	 * // if false element will trigger submited
	 * // if true element will be ignored
	 * $data['name']['static'] = false;
	 * // validation
	 * $data['name']['validate']['regex']    = '/^[a-z0-9~._-]+$/i';
	 * $data['name']['validate']['errormsg'] = 'string must be a-z0-9~._-';
	 * // build object
	 * $data['name']['object']['type']                = 'htmlobject_input';
	 * $data['name']['object']['attrib']['type']      = 'text';
	 * $data['name']['object']['attrib']['name']      = 'name';
	 * $data['name']['object']['attrib']['value']     = 'somevalue';
	 * $data['name']['object']['attrib']['minlength'] = 8;
	 * $data['name']['object']['attrib']['maxlength'] = 100;
	 *
	 * $formbuilder = $html->formbuilder();
	 * $formbuilder->add( $data );
	 *
	 * // Actions
	 * // no errors, do something
	 * if(!$formbuilder->get_errors()) {
	 *	    $values = $formbuilder->get_request();
	 *	    print_r($values);
	 * }
	 * </code>
	 */
	//---------------------------------------
	function add( $data, $key = null ) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if(is_array($v)) {
					if(
						isset($data[$k]['label']) ||
						isset($data[$k]['required']) ||
						isset($data[$k]['css']) ||
						isset($data[$k]['style']) ||
						isset($data[$k]['static']) ||
						isset($data[$k]['validate']) ||
						isset($data[$k]['object'])
					) {
						if(!isset($data[$k]['object'])) {
							if(isset($this->__elements[$k])) {
								$v['object'] = $this->__elements[$k];
							}
						}
						$this->__data[$k] = $v;
					}
				} else {
					parent::add($v, $k);
				}
			}
		} else {
			parent::add($data, $key);
		}
		$this->init();
	}

	//---------------------------------------
	/**
	 * Get array of objects
	 *
	 * will return array($name => htmlobject_box)
	 *
	 * @access public
	 * @param string $name name of element
	 * @return array of objects
	 */
	//---------------------------------------
	function get_elements( $name = null ) {
		if(isset($this->__elements)) {
			$a = array();
			if( $name ) {
				$elements[$name] = $this->__elements[$name];
			} else {
				$elements = $this->__elements;
			}

			foreach($elements as $key => $value) {
				if(isset($this->__data[$key])) {
					$label = $this->__get_label($key);
					if($label !== '') {
						$box      = $this->html->box();
						$box->css = $this->box_css.' form-group';
						if(isset($this->__data[$key]['css'])) {
							$box->css = $box->css.' '.$this->__data[$key]['css'];
						}
						if(isset($this->__data[$key]['id'])) {
							$box->id = $this->__data[$key]['id'];
						}
						if(isset($this->__data[$key]['style'])) {
							$box->style = $this->__data[$key]['style'];
						}
						$box->label = $label;
						$box->add($value);
						if(isset($this->request_errors[$key])) {
							$box->css = $box->css.' error';
							if($this->display_errors === true) {
								$div = $this->html->div();
								$div->css = 'errormsg';
								$div->add($this->request_errors[$key]);
								$box->add($div);
							}
						}
						$a = array_merge($a, array($key => $box));
					} else {
						if(isset($this->request_errors[$key])) {
							if(isset($value->css)) {
								$value->css = $value->css.' htmlobject error';
							} else {
								$value->css = 'htmlobject error';
							}
						}
						$a = array_merge($a, array($key => $value));
					}
				} else {
					$a = array_merge($a, array($key => $value));
				}
			}
			if(isset($name)) {
				return $a[$name];
			} else {
				return $a;
			}
		}
	}

	//---------------------------------------
	/**
	 * Get one or all errors
	 *
	 * return array('name' => 'errormsg', ...)
	 * or string 'errormsg' if param name is set
	 * or null if no error occured
	 *
	 * @access public
	 * @param string $name key of element
	 * @return string|array|null
	 */
	//---------------------------------------
	function get_errors( $name = null ) {
		if(isset($this->request_errors)) {
			if(isset($name)) {
				if(isset($this->request_errors[$name])) {
					return $this->request_errors[$name];
				}
			} else {
				return $this->request_errors;
			}
		}
	}

	//---------------------------------------
	/**
	 * Get elements names as array[keys]
	 *
	 * TODO array of arrays
	 *
	 * @access public
	 * @return array | null
	 */
	//---------------------------------------
	function get_names() {
		if(isset($this->__elements)) {
			$return = array();
			foreach($this->__elements as $k => $v) {
				if(
					isset($v->name) &&
					$v->name !== '' &&
					strpos($v->name, '[submit]') === false &&
					strpos($v->name, '[cancel]') === false &&
					strpos($v->name, '[reset]') === false &&
					(!isset($this->__data[$k]['static']) || $this->__data[$k]['static'] === false)
				) {
					$name = $this->html->request()->unindex_array($v->name);
					$regex = '~\[(.[^\]]*)\]~';
					preg_match_all($regex, $name, $matches);
					if($matches) {
						$tag = preg_replace('~\[.*\]~', '', $name);
						if(isset($matches[1][0])) {

							if(isset($return[$tag]) && is_array($return[$tag])) {
								$return[$tag] = $this->__arrayReplaceRecursive($return[$tag], $this->__array2arrays($matches[1], 0, ''));
							} else {
								$return[$tag] = $this->__array2arrays($matches[1], 0, '');
							}

						} else {
							$return[$tag] = '';
						}
					} else {
						$return[$name] = '';
					}
				}
			}
			return $return;
		}
	}

	//---------------------------------------
	/**
	 * Get form objects
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//---------------------------------------
	function get_object() {
		$form = $this->html->form();
		$form->css = $this->css;
		$form->id = $this->id;
		$form->style = $this->style;
		$form->title = $this->title;
		$form->action = $this->action;
		$form->enctype = $this->enctype;
		$form->method = $this->method;
		$form->name = $this->name;
		$form->target = $this->target;
		$form->handler = $this->handler;
		$form->customattribs = $this->customattribs;
		if (isset($this->__customattribs)) {
			$form->__customattribs = $this->__customattribs;
		}
		$form->add($this->get_elements());
		return $form;
	}

	//---------------------------------------
	/**
	 * Get request values as array
	 *
	 * @access public
	 * @param string $name name of input
	 * @param bool $raw return emtpy values too
	 * @return array|string
	 */
	//---------------------------------------
	function get_request($name = null, $raw = false) {
		$request = '';
		if(isset($this->request) && is_array($this->request)) {
			$request = $this->request;
		}
		if($raw === true ) {
			$names = $this->get_names();
			if(is_array($names)) {
				if(is_array($request)) {
					$request = $this->__arrayReplaceRecursive($names,$request);
				} else {
					$request = $names;
				}
			}
		}
		if(isset($name)) {
			$tmp = '$request'.$this->html->request()->string_to_index($name);
			if(eval("return isset($tmp);")) {
				return eval("return $tmp;");
			} else {
				return '';
			}
		} else {
			return $request;
		}
	}

	//---------------------------------------
	/**
	 * Get one or all static
	 *
	 * @access public
	 * @param string $name key of element
	 * @return string|array|null
	 */
	//---------------------------------------
	function get_static( $name = null ) {
		if(isset($this->__static)) {
			if(isset($name)) {
				$tmp = '$this->__static'.$this->html->request()->string_to_index($name);
				if(eval("return isset($tmp);")) {
					return eval("return $tmp;");
				}
			} else {
				return $this->__static;
			}
		}
	}

	//---------------------------------------
	/**
	 * Get formbuilder as string
	 *
	 * @access public
	 * @return string
	 */
	//---------------------------------------
	function get_string() {
		return $this->get_object()->get_string();
	}

	//---------------------------------------
	/**
	 * Set error by element name
	 *
	 * @access public
	 * @param string $name name of input
	 * @param string $value
	 */
	//---------------------------------------
	function set_error($name, $value) {
		if(isset($this->__elements)) {
			foreach($this->__elements as $k => $v) {
				if(is_object($v) && isset($v->name) && $v->name === $name) {
					$this->request_errors[$k] = $value;
				}
			}
		}		
	}

	//---------------------------------------
	/**
	 * Set label
	 *
	 * @access public
	 * @param string $key key of element
	 * @param string $value
	 */
	//---------------------------------------
	function set_label($key, $value) {
		if(isset($this->__data) && isset($this->__data[$key])) {
			$this->__data[$key]['label'] = $value;
		}
	}

	//---------------------------------------
	/**
	 * Remove element by name
	 *
	 * Name must not match exactly e.g.
	 * val[ matches val[x] val[x][y] etc.
	 *
	 * @access public
	 * @param string $name name of input
	 */
	//---------------------------------------
	function remove($name) {
		if(isset($this->__elements)) {
			foreach($this->__elements as $k => $v) {
				if(is_object($v) && isset($v->name) && strpos($v->name, $name) === 0) {
					unset($this->__elements[$k]);
					if(isset($this->__data) && isset($this->__data[$k])) {
						unset($this->__data[$k]);
					}
					if(isset($this->__static) && isset($this->__static[$k])) {
						unset($this->__static[$k]);
					}
					if(isset($this->request)) {
						$tmp = '$this->request'.$this->html->request()->string_to_index($name);
						eval("unset($tmp);");
					}
				}
			}
		}		
	}

	//---------------------------------------
	/**
	 * Set values from http request as array
	 * TODO static radio array
	 *
	 * @access protected
	 */
	//---------------------------------------
	function __set_request() {
		if(isset($this->__data)) {
			// store filter
			$tmpfilter = $this->html->request()->filter;
			$filter    = array_merge($tmpfilter,$this->request_filter);
			$this->html->request()->set_filter($filter);
			foreach($this->__data as $key => $value) {
				if(isset($this->__elements[$key]->name) && $this->__elements[$key]->name !== '') {
					$name = $this->html->request()->unindex_array($this->__elements[$key]->name);
					// non static
					if(!isset($this->__data[$key]['static'])) {
						// set vars
						$request = $this->html->request()->get($name);
						if($request !== '') {
							$regex = '~\[(.[^\]]*)\]~';
							preg_match_all($regex, $name, $matches);
							if($matches) {
								$tag = preg_replace('~\[.*\]~', '', $name);
								if(isset($matches[1][0])) {

									if(isset($this->request[$tag]) && is_array($this->request[$tag])) {
										$this->request[$tag] = $this->__arrayReplaceRecursive($this->request[$tag], $this->__array2arrays($matches[1], 0, $request));
									} else {
										$this->request[$tag] = $this->__array2arrays($matches[1], 0, $request);
									}

								} else {
									$this->request[$tag] = $request;
								}
							} else {
								$this->request[$name] = $request;
							}
							$this->__submited = true;
						}
					}
					// static but false
					else if(isset($this->__data[$key]['static']) && $this->__data[$key]['static'] === false) {
						$request = $this->html->request()->get($name);
						if($request !== '') {
							$this->__submited = true;
						}
					}
					// static
					else if(isset($this->__data[$key]['static']) && $this->__data[$key]['static'] === true) {
						if($this->__elements[$key] instanceof htmlobject_select || $this->__elements[$key] instanceof htmlobject_select_debug) {
							$request = $this->__elements[$key]->selected;
							if(strrpos($this->__elements[$key]->name,'[]') === false) {
								if(isset($request[0])) {
									$request = $request[0];
								} else {
									$request = '';
								}
							} else {
								if(count($request) < 1) {
									$request = '';
								}
							}
						} else {
							$request = $this->__elements[$key]->value;
							// handle value filter (reverse) 
							foreach($this->value_filter as $p) {
								$request = str_replace($p['replace'], $p['pattern'], $request);
							}
						}
						if($request !== '') {
							$regex = '~\[(.[^\]]*)\]~';
							preg_match_all($regex, $name, $matches);
							if($matches) {
								$tag = preg_replace('~\[.*\]~', '', $name);
								if(isset($matches[1][0])) {
									if(isset($this->__static[$tag]) && is_array($this->__static[$tag])) {
										$this->__static[$tag] = $this->__arrayReplaceRecursive($this->__static[$tag], $this->__array2arrays($matches[1], 0, $request));
									} else {
										$this->__static[$tag] = $this->__array2arrays($matches[1], 0, $request);
									}

								} else {
									$this->__static[$tag] = $request;
								}
							} else {
								$this->__static[$name] = $request;
							}
						}
					}
				}
			}
			// reset filter
			$this->html->request()->set_filter($tmpfilter);
		}
	}

	//---------------------------------------
	/**
	 * Multidimesional array from array
	 * 
	 * Example:
	 * input array(a,b,c)
	 * output array(a => array(b => array(c =>'value')))
	 *
	 * @access protected
	 * @param array $array array to convert
	 * @param integer $num array index
	 * @param string $value value
	 * @return array
	 */
	//---------------------------------------
	function __array2arrays( $array, $num, $value) {
		if(isset($array[$num])) {
			if(isset($array[$num+1])) {
				$r[$array[$num]] = $this->__array2arrays($array, $num+1, $value);
			} else {
				$r[$array[$num]] = $value;
			}
		} else {
			$r = $value;
		}
		return $r;
	}

	//---------------------------------------
	/**
	 * Replacement for array_replace_recursive 1
	 *
	 * @access protected
	 * @param array $array1
	 * @param array $array1
	 * @return array
	 */
	//---------------------------------------
	function __arrayReplaceRecursive($array, $array1) {
		// handle arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array)) {
			return $array;
		}
		for ($i = 1; $i < count($args); $i++) {
			if (is_array($args[$i])) {
				$array = $this->__recurse($array, $args[$i]);
			}
		}
		return $array;
	}

	//---------------------------------------
	/**
	 * Replacement for array_replace_recursive 2
	 *
	 * @access protected
	 * @param array $array1
	 * @param array $array1
	 * @return array
	 */
	//---------------------------------------
	function __recurse($array, $array1) {
		foreach ($array1 as $key => $value) {
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
				$array[$key] = array();
			}
			// overwrite the value in the base array
			if (is_array($value)) {
				$value = $this->__recurse($array[$key], $value);
			}
			$array[$key] = $value;
		}
		return $array;
	}

	//---------------------------------------
	/**
	 * Check $this->__data request
	 *
	 * Returns array of errors if
	 * request does not match given regex.
	 * Empty if no missmatch occured.
	 *
	 * @access protected
	 * @todo pregmatch for arrays
	 */
	//---------------------------------------
	function __set_request_errors() {
		foreach ($this->__data as $key => $data) {
			if(isset($this->__elements[$key]->name)) {

				// handle name
				$name = $this->__elements[$key]->name;

				// handle value
				$value = $this->html->request()->get($name, true);

				// handle missing label
				$label = $name;
				if(isset($data['label'])) {
					$label = $data['label'];
				}

				if(!isset($value) || $value === '') {
					// handle required
					if(isset($data['required'])) {
						if (
							isset($this->__submited) &&
							$this->__submited === true &&
							isset($data['required']) &&
							$data['required'] === true
						) {
							$this->request_errors[$key] = sprintf($this->html->lang['form']['error_required'], $label);
						}
					}
				} else {
					// handle value is array
					!is_array($value) ? $value = array($value) : null;

					// handle validate
					if(
						isset($data['validate']) &&
						isset($data['validate']['regex']) &&
						isset($data['validate']['errormsg']) &&
						isset($this->request) &&
						count($this->request) > 0
					) {
						$regex   = $this->__data[$key]['validate']['regex'];
						$request = '$this->request'.$this->html->request()->string_to_index($name);
						if(eval("return isset($request);") && isset($request) && isset($regex) && $regex != '') {
							$request = eval("return $request;");
							if(is_array($request)) {
								foreach($request as $v) {
									$matches = @preg_match($regex, $v);
									if(!$matches) {
										$this->request_errors[$key] = $data['validate']['errormsg'];
										break;
									}
								}
							} else {
								$matches = @preg_match($regex, $request);
								if(!$matches) {
									$this->request_errors[$key] = $data['validate']['errormsg'];
								}
							}
						}
					}

					// handle select as whitelist
					if(
						$this->__elements[$key] instanceof htmlobject_select || 
						$this->__elements[$key] instanceof htmlobject_select_debug
					) {
						$check = $this->__elements[$key]->get_elements();
						foreach($value as $v) {
							if(!is_array($check) || !array_key_exists($v, $check))	{
								$this->request_errors[$key] = sprintf($this->html->lang['form']['error_value'], $label);
							}
						}
					}

					// handle length
					if(
						isset($this->__submited) &&
						$this->__submited === true &&
						(isset($this->__elements[$key]->maxlength) || isset($this->__elements[$key]->minlength))
					) {
						foreach($value as $v) {
							$v = str_replace("\r\n", "\n", $v);
							if (preg_match('!\S!u', $v)) {
								$value = utf8_decode($v);
							}
							// handle maxlength
							if(isset($this->__elements[$key]->maxlength)) {
								if(strlen($v) > $this->__elements[$key]->maxlength) {
									$this->request_errors[$key] = sprintf(
											$this->html->lang['form']['error_maxlength'],
											$label,
											$this->__elements[$key]->maxlength);
								}
							}
							// handle minlength
							if(isset($this->__elements[$key]->minlength)) {
								if(strlen($v) < $this->__elements[$key]->minlength) {
									$this->request_errors[$key] = sprintf(
											$this->html->lang['form']['error_minlength'],
											$label,
											$this->__elements[$key]->minlength);
								}
							}
						}
					}
				}
			}
		}
	}

	//---------------------------------------
	/**
	 * Set elements value
	 * make sure data, request and request_errors
	 * are set first
	 *
	 * @access protected
	 */
	//---------------------------------------
	function __set_elements_value() {
		$k = array_keys($this->__data);
		$c = count($k);
		for($i = 0; $i<$c; ++$i) {
			if(isset($this->__elements[$k[$i]]->name) && $this->__elements[$k[$i]]->name !== '') {
				$name = $this->__elements[$k[$i]]->name;
				if( isset($this->__data[$k[$i]]['static']) && $this->__data[$k[$i]]['static'] === true ) {
					if($this->__elements[$k[$i]] instanceof htmlobject_select || $this->__elements[$k[$i]] instanceof htmlobject_select_debug) {
						$this->__handle_htmlobject($k[$i], $this->__elements[$k[$i]]->selected);
					} else {
						$this->__handle_htmlobject($k[$i], $this->__elements[$k[$i]]->value);
					}
				} else {
					if(	isset($this->request) && count($this->request) > 0) {
						$request = $this->html->request()->get($this->html->request()->unindex_array($name));
						// escape specialchars
						foreach($this->value_filter as $p) {
							$request = str_replace($p['pattern'], $p['replace'], $request);
						}
						$this->__handle_htmlobject($k[$i], $request);
					}
				}
			}
		}
	}

	//---------------------------------------
	/**
	 * Handle htmlobject
	 *
	 * @access protected
	 * @param string $key
	 * @param string $value
	 */
	//---------------------------------------
	function __handle_htmlobject($key, $value) {
		$html = $this->__elements[$key];
		if($html instanceof htmlobject_input || $html instanceof htmlobject_input_debug) {
			$html->type = strtolower($html->type);
			switch($html->type) {
				case 'submit':
				case 'reset':
				case 'file':
				case 'image':
				case 'button':
					// do nothing
				break;
				case 'radio':
					if($value == $html->value) {
						$html->checked = true;
					} else {
						$html->checked = false;
					}
				break;
				case 'checkbox':
					if(is_string($value)) {
						if($value !== '') {
							$html->checked = true;
						} else {
							$html->checked = false;
						}
					}
					if(is_array($value)) {
						if(in_array($html->value, $value)) {
							$html->checked = true;
						} else {
							$html->checked = false;
						}
					}
				break;
				case 'text':
				case 'hidden':
				case 'password':
						$html->value = $value;
				break;
			}
		}
		if($html instanceof htmlobject_textarea || $html instanceof htmlobject_textarea_debug) {
			$html->value = $value;
		}
		if($html instanceof htmlobject_select || $html instanceof htmlobject_select_debug) {
			if(isset($value) && $value !== '') {
				if(!is_array($value)) {
					$value = array($value);
				}
				$html->selected = $value;
			}
		}
	}

	//---------------------------------------
	/**
	 * Handle label
	 *
	 * @access protected
	 * @param string $key
	 * @return string
	 */
	//---------------------------------------
	function __get_label($key) {
		$label = '';
		if(
			isset($this->__data[$key]['label']) && $this->__data[$key]['label'] != '' &&
			isset($this->__elements[$key]->name)
		) {
			if($this->__elements[$key]->id === '') { 
				$this->__elements[$key]->id = substr(uniqid('p', true), 0, 14);
			}
			$label = $this->__data[$key]['label'];
			// mark required
			if(isset($this->__data[$key]['required']) && $this->__data[$key]['required'] === true) {
				$label = $label.' '.$this->html->lang['form']['required'];
			}
			// mark error
			if(isset($this->request_errors)) {
				if(array_key_exists($key, $this->request_errors)) {
					$label = '<span class="error">'.$label.'</span>';
				}
			}
		}
		return $label;
	}

}
?>
