<?php
/**
 * Response
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class htmlobject_response
{
/**
* Params
* 
* @access public
* @var array
*/
var $params = array();
/**
* Parent response
* 
* @access private
* @var array
*/
var $parent = array();
/**
* Do redirect
* 
* @access public
* @var bool
*/
var $redirect = true;
/**
* Is response redirected
*
* Stores redirect url if
* redirect is set to false
* 
* @access public
* @var array
*/
var $isredirected;

	//------------------------------------------------
	/**
	 * Constructor
	 *
	 * @param htmlobject $html
	 * @param string $id
	 * @access public
	 */
	//------------------------------------------------
	function __construct( $html, $id = 'response' ) {
		$this->html = $html;
		$this->id   = $id;
	}

	//-------------------------------------------------
	/**
	* Add params
	*
	* @access public
	* @param string $key
	* @param string | array $values
	*/
	//-------------------------------------------------
	function add($key, $values) {
		if(is_string($values)) { 
			$this->params[$key] = $values;
		} else if (is_array($values)) {
			foreach($values as $k => $v) {
				if(isset($key)) {
					$this->params[$key][$k] = $v;
				} else {
					$this->params[$k] = $v;
				}
			}
		}
	}

	//-------------------------------------------------
	/**
	* Is form canceled ?
	*
	* @access public
	* @return true when form is canceled
	*/
	//-------------------------------------------------
	function cancel() {
		$submit = $this->html->request()->get($this->id.'[cancel]');
		if($submit !== '') {
			return true;
		}
	}

	//-------------------------------------------------
	/**
	* Params to array
	*
	* @access public
	* @param string $tag name of action param
	* @param string $action action
	* @return array(key => value)
	*/
	//-------------------------------------------------
	function get_array($key = null, $action = null) {
		$params = $this->params;
		unset($params['submit']);
		unset($params['cancel']);
		if(isset($key) && isset($action)) {
			$params[$key] = $action;
		}
		return $params;
	}

	//-------------------------------------------------
	/**
	* Params to htmlobject_formbuilder
	*
	* param $custom can be used to pass an attrib
	* to formbuilder call when htmlobject class is
	* derived and aditional params are needed
	*
	* @access public
	* @param string $tag name of action param
	* @param string $action action
	* @param bool $submit add submit buttons
	* @param string $custom custom attrib to pass to formbuilder call
	* @return htmlobject_formbuilder
	*/
	//-------------------------------------------------
	function get_form($tag = null, $action = null, $submit = true, $custom = null) {
		$d = array();
		$i = 0;
		foreach($this->params as $key => $value) {
			if(is_array($value)) {
				foreach($value as $k => $v) {
					if(is_array($v)) {
						$p = '&'.$key.'['.$k.']';
						$r = $this->__array2string( $v, $p, false );
						$r = explode('&',$r);
						foreach($r as $y) {
							if($y !== '') {
								$t = explode('=', $y);
								if(isset($t[1]) && $t[1] !== '') {
									$d['param_o'.$i]['label']                     = '';
									$d['param_o'.$i]['static']                    = true;
									$d['param_o'.$i]['object']['type']            = 'htmlobject_input';
									$d['param_o'.$i]['object']['attrib']['type']  = 'hidden';
									$d['param_o'.$i]['object']['attrib']['name']  = $t[0];
									$d['param_o'.$i]['object']['attrib']['value'] = $t[1];
									$i++;
								}
							}
						}
					} else {
						$d['param_o'.$i]['label']                     = '';
						$d['param_o'.$i]['static']                    = true;
						$d['param_o'.$i]['object']['type']            = 'htmlobject_input';
						$d['param_o'.$i]['object']['attrib']['type']  = 'hidden';
						$d['param_o'.$i]['object']['attrib']['name']  = $key.'['.$k.']';
						$d['param_o'.$i]['object']['attrib']['value'] = $v;
						++$i;
					}
				}
			} else {
				$d['param_o'.$i]['label']                     = '';
				$d['param_o'.$i]['static']                    = true;
				$d['param_o'.$i]['object']['type']            = 'htmlobject_input';
				$d['param_o'.$i]['object']['attrib']['type']  = 'hidden';
				$d['param_o'.$i]['object']['attrib']['name']  = $key;
				$d['param_o'.$i]['object']['attrib']['value'] = $value;
				++$i;
			}
		}
		if(isset($tag) && isset($action)) {
			$d['param_action']['label']                     = '';
			$d['param_action']['static']                    = true;
			$d['param_action']['object']['type']            = 'htmlobject_input';
			$d['param_action']['object']['attrib']['type']  = 'hidden';
			$d['param_action']['object']['attrib']['name']  = $tag;
			$d['param_action']['object']['attrib']['value'] = $action;
		}
		if($submit === true) {
			$d['cancel']['label']                     = '';
			$d['cancel']['static']                    = true;
			$d['cancel']['object']['type']            = 'htmlobject_input';
			$d['cancel']['object']['attrib']['css']   = 'btn btn-default btn-inline';
			$d['cancel']['object']['attrib']['type']  = 'submit';
			$d['cancel']['object']['attrib']['name']  = $this->id.'[cancel]';
			$d['cancel']['object']['attrib']['value'] = $this->html->lang['response']['cancel'];

			$d['submit']['label']                     = '';
			$d['submit']['static']                    = false;
			$d['submit']['object']['type']            = 'htmlobject_input';
			$d['submit']['object']['attrib']['css']   = 'btn btn-default btn-inline';
			$d['submit']['object']['attrib']['type']  = 'submit';
			$d['submit']['object']['attrib']['name']  = $this->id.'[submit]';
			$d['submit']['object']['attrib']['value'] = $this->html->lang['response']['submit'];
		}
		$form = $this->html->formbuilder($this->id, $custom);
		$form->add($d);
		return $form;
	}

	//-------------------------------------------------
	/**
	* Get params as string
	*
	* @access public
	* @param array $params
	* @param enum $firstchar [?|&]
	* @param bool $encode urlencode
	* @return string
	*/
	//-------------------------------------------------
	function get_params_string( $params, $firstchar = '?', $encode = true ) {
		$str = '';
		if(is_array($params)) {
			foreach($params as $key => $val) {
				if($encode === true) {
					$key = urlencode($key);
				}
				if(is_array($val)) {
					$prefix = '&'.$key;
					foreach($val as $k => $v) {
						if(is_array($v)) {
							$p = $prefix.'['.$k.']';
							if($encode === true) {
								$p = $prefix.'%5B'.$k.'%5D';
							}
							$return = $this->__array2string( $v, $p, $encode );
							$str .= $return;
						} else {
							if($encode === true) {
								$v = urlencode($v);
								$str .= $prefix.'%5B'.$k.'%5D='.$v;
							} else {
								$str .= $prefix.'['.$k.']='.$v;
							}
						}
					}
				} else {
					if($encode === true) {
						$val = urlencode($val);
					}
					$str .= '&'.$key.'='.$val;
				}
			}
		}
		if($str !== '') {
			$str = preg_replace('/^&/', $firstchar, $str);
		}
		return $str;
	}

	//-------------------------------------------------
	/**
	* convert multidimesional array to string
	*
	* @access public
	* @param array $params
	* @param string $prefix
	* @param bool $encode urlencode
	* @return string &somevale[x][y][z]..
	*/
	//-------------------------------------------------
	function __array2string( $params, $prefix, $encode = true ) {
		$str = '';
		foreach($params as $k => $v) {
			if(is_array($v)) {
				$p = $prefix.'['.$k.']';
				if($encode === true) {
					$p = $prefix.'%5B'.$k.'%5D';
				}
				$str .= $this->__array2string( $v, $p, $encode);
			} else {
				if($encode === true) {
					$v = urlencode($v);
					$str .= $prefix.'%5B'.$k.'%5D='.$v;
				} else {
					$str .= $prefix.'['.$k.']='.$v;
				}
			}
		}
		return $str;
	}

	//-------------------------------------------------
	/**
	* Params to string
	*
	* @access public
	* @param string $tag name of action param
	* @param string $action action
	* @param enum $firstchar [?|&]
	* @param bool $encode urlencode
	* @return string
	*/
	//-------------------------------------------------
	function get_string( $tag = null, $action = null, $firstchar = '&', $encode = true ) {
		$params = $this->params;
		unset($params['submit']);
		unset($params['cancel']);
		if(isset($tag) && isset($action)) {
			$params[$tag] = $action;
		}
		return $this->get_params_string($params, $firstchar, $encode);
	}

	//-------------------------------------------------
	/**
	* Get url
	*
	* @access public
	* @param string $tag name of additional param
	* @param string $action action
	* @param string $msgparam name of message param
	* @param string|array $msg message
	* @param integer $msgsize maxlength of message
	* @return string
	*/
	//-------------------------------------------------
	function get_url($tag, $action, $msgparam = null, $msg = null, $msgsize = 200) {
		$url  = $this->html->thisfile;
		$url .= $this->get_string($tag, $action, '?');
		if(isset($msgparam) && isset($msg)) {
			if(!is_array($msg)) { $msg = array($msg); }
			$msg = implode('<br>', $msg);
			if(strlen($msg) > $msgsize) {
				$msg = substr($msg, 0, strrpos(substr($msg, 0, $msgsize), ' ')).' ...';
			}
			$url .= '&'.$msgparam.'='.$msg;
		}
		return $url;
	}

	//-------------------------------------------------
	/**
	* Do redirect
	*
	* tries php header redirect,
	* on fail js redirect,
	* on fail meta redirect
	*
	* @access public
	* @param string $url
	*/
	//-------------------------------------------------
	function redirect($url){
		if($this->redirect === true) {
			if (!headers_sent()){
				header('Location: '.$url); exit;
			} else {
				echo '<script type="text/javascript">';
				echo 'window.location.href="'.$url.'";';
				echo '</script>';
				echo '<noscript>';
				echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
				echo '</noscript>'; exit;
			}
		} else {
			$this->isredirected[] = $url;
		}
	}

	//-------------------------------------------------
	/**
	* Is form submitted ?
	*
	* @access public
	* @return true when form is submitted
	*/
	//-------------------------------------------------
	function submit() {
		$submit = $this->html->request()->get($this->id.'[submit]');
		if($submit !== '') {
			return true;
		}
	}

	//-------------------------------------------------
	/**
	* Clone Response
	*
	* @access public
	* @return htmlobject_response
	*/
	//-------------------------------------------------
	function response() {
		$response = $this->html->response();
		$response->params = $this->params;
		$response->parent($this);
		return $response;
	}

	//-------------------------------------------------
	/**
	* Save cloned Response
	*
	* @access protected
	* @return true
	*/
	//-------------------------------------------------
	function parent( $hmlobject_response = null ) {
		if(isset($hmlobject_response)) {
			$this->parent[] = $hmlobject_response;
			return true;
		} else {
			// return last
		}
	}

	//-------------------------------------------------
	/**
	* remove param
	*
	* @access public
	* @param string $param
	* @return null
	*/
	//-------------------------------------------------
	function remove( $param ) {
		if(isset($this->params) && isset($this->params[$param])) {
			unset($this->params[$param]);
		}
	}




}
?>
