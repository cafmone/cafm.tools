<?php
/**
 * Tabmenu
*
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_tabmenu extends htmlobject_div
{
/**
* url to process request
*
* Form disabled if empty
* @access public
* @var string
*/
var $form_action = '';
/**
* Css class to highlight active tab
*
* @access public
* @var string
*/
var $activecss = 'active';
/**
* Css class for ul
*
* @access public
* @var string
*/
var $ulcss = 'nav nav-tabs';
### TODO
/**
* Css class for li
*
* @access public
* @var string
*/
var $licss = 'nav-item';
/**
* Css class for a
*
* @access public
* @var string
*/
var $acss = 'nav-link';
/**
* Css class for wrapper box
*
* @access public
* @var string
*/
var $boxcss = 'tab-content';
/**
* Css class tab pane
*
* @access public
* @var string
*/
var $panecss = 'tab-pane';
/**
* Add a param to handle active tab
* If set to true ['active'] will be ignored
*
* @access public
* @var bool
*/
var $auto_tab = true;
/**
* Add a custom string to tabs
*
* @access public
* @var string
*/
var $custom_tab = '';
/**
* Add a floatbreaking div between tab and box
*
* @access public
* @var string
*/
var $floatbreaker = true;
/**
* Name of param to transport message to messagebox
*
* @access public
* @var string
*/
var $message_param = 'strMsg';
/**
* Regex pattern for messagebox (XSS)
*
* replace pattern with replace
* @access public
* @var array(array('pattern'=>'','replace'=>''));
*/
var $message_filter = array (
	array ( 'pattern' => '~</?script.+~i', 'replace' => ''),
	array ( 'pattern' => '~</?iframe.+~i', 'replace' => ''),
	array ( 'pattern' => '~</?object.+~i', 'replace' => ''),
	array ( 'pattern' => '~javascript~i', 'replace' => ''),
	array ( 'pattern' => '~://~', 'replace' => ':&frasl;&frasl;'),
	);
/**
* Time to show messagebox in milliseconds (1/1000 sec.)
*
* @access public
* @var int
*/
var $message_time = 10000;
/**
* Css class for messagebox
*
* @access public
* @var string
*/
var $message_css = array(
	'default' =>'msgBox alert alert-info',
	'error'   =>'msgBox alert alert-danger',
	'success' =>'msgBox alert alert-success',
);

	//------------------------------------------------
	/**
	* Constructor
	*
	* @access public
	* @param htmlobject $htmlobject
	* @param string $id
	*/
	//------------------------------------------------
	function __construct($htmlobject, $id = 'currenttab') {
		$this->html = $htmlobject;
		$this->id   = $id;
	}

	//------------------------------------------------
	/**
	* Init tabs
	*
	* @access public
	*/
	//------------------------------------------------
	function init() {
		if(isset($this->__data)) {
			$float = $this->html->div();
			$float->style = 'line-height:0px;clear:both;';
			$float->css   = 'floatbreaker';
			$float->add('&#160;');
			$i = 0;
			foreach ($this->__data as $key => $val) {
				if(isset($val) && $val !== '') {
					$val['id'] = $this->id.$key;
					if(isset($val['value'])) {
						$v      = $this->html->div();
						$v->id  = $val['id'];
						$v->css = $this->panecss;
						$v->add($val['value']);
						$v->add($float);
						unset($val['value']);
					}
					elseif(isset($this->__elements[$key])) {
						$v = $this->__elements[$key];
					} else { 
						$v = null;
					}
					if(!isset($val['label']))   { $val['label'] = ''; }
					if(!isset($val['target']))  { $val['target'] = $this->html->thisfile; }
					if(!isset($val['request'])) { $val['request'] = array(); }
					if(!isset($val['onclick'])) { $val['onclick'] = false; }
					if(
						isset($val['active']) &&
						$val['active'] === true &&
						$this->html->request()->get($this->id) === ''
					) {
						$_REQUEST[$this->id] = "$key";
					}
					$this->__data[$key] = $val;
					isset($v) ? $this->__elements[$key] = $v : null;
					$i++;
				}
			}
		}
	}

	//------------------------------------------------
	/**
	 * Add content
	 *
	 * @access public
	 * @param array $data
	 * @param null $key not in use
	 * <code>
	 * $html = new htmlobject('path_to_htmlobjects');
	 * $tab  = $html->tabmenu('id');
	 *
	 * $content               = array();
	 * $content[0]['label']   = 'some label';
	 * $content[0]['title']   = 'some title';
	 * $content[0]['value']   = 'some content text';
	 * $content[0]['target']  = 'somefile.php';
	 * $content[0]['request'] = array('param1'=>'value1');
	 * $content[0]['onclick'] = false;  // use js to toggle tabs
	 * $content[0]['active']  = false;  // set tab active
	 * $content[0]['hidden']  = false;  // hide tab
	 * $content[0]['css']     = null;   // add a custom class to tab and box
	 *
	 * $tab->add($content);
	 * </code>
	 */
	//------------------------------------------------
	function add($data, $key = null) {
		if(is_array($data)) {
			foreach($data as $k => $v) {
				if(is_array($v)) {
					if(
						isset($v['label']) ||
						isset($v['value']) ||
						isset($v['target']) ||
						isset($v['request']) ||
						isset($v['onclick']) ||
						isset($v['active'])
					) {
						if(isset($this->__data[$k])) {
							$v = $v + $this->__data[$k];
						}
						$this->__data[$k] = $v;
					}
				}
			}
		}
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
		$this->init();
		$_str = '';
		($this->form_action != '') ? $_str .= '<form action="'.$this->form_action.'" method="POST">' : null;
		if(isset($this->__data)) {
			$current = $this->__get_current();
			#$_str .= $this->__get_js();
			foreach ($this->__data as $key => $value) {
				if(isset($this->__elements[$key])) {
					$html = $this->__elements[$key];
					if($value['id'] !== $this->id.$current) {
						if(isset($value['onclick']) && $value['onclick'] === true) {
							$html = $this->__elements[$key];
						} else {
							$old = $html;
							$html = $this->html->div();
							$html->css = $old->css;
							if(isset($value['css']) && $value['css'] !== '') {
								$html->css = $html->css.' '.$value['css'];
							}
							$html->id  = $old->id;
							$html->add('&#160;');
							$html->style = 'display:none;';
						}
					} else {
						$old = $html;
						$html = $this->html->div();
						$html->css = $old->css.' active ';
						if(isset($value['css']) && $value['css'] !== '') {
							$html->css = $html->css.' '.$value['css'];
						}
						$html->id  = $old->id;
						#$html->add($this->__get_js());
						$html->add($old->get_elements());
					}
					$_str .= $html->get_string();
				}
			}
		}

		$_tab = $this->__get_tabs($current);
		$div = $this->html->div();
		$div->css = $this->boxcss;
		$div->add($this->__get_messagebox(), 'msg');
		$div->add($_str);
		$x = $_tab.$div->get_string();

		($this->form_action != '') ? $x .= '</form>' : null;
		return $x;
	}

	//------------------------------------------------
	/**
	* Get current active element
	*
	* @access public
	* @return string | null
	*/
	//------------------------------------------------
	function get_current() {
		$current = $this->__get_current();
		if(
			isset($this->__data) && 
			isset($this->__data[$current]) && 
			isset($this->__data[$current]['value'])
		) {
			return $this->__data[$current]['value'];
		}
	}

	//------------------------------------------------
	/**
	* Get array key of current element
	*
	* @access private
	* @return string | null
	*/
	//------------------------------------------------
	function __get_current() {
		$req = $this->html->request()->get($this->id);
		if($req !== '') {
			return "$req";
		} else {
			if(isset($this->__data)) {
				$current = array_keys($this->__data);
				return "$current[0]";
			}
		}
	}

	//------------------------------------------------
	/**
	* Create tabs
	*
	* @access private
	* @param string $currenttab
	* @return string
	*/
	//------------------------------------------------
	function __get_tabs($currenttab) {
		$thisfile = $this->html->thisfile;
		$attribs  = $this->__attribs();
		$_str = '';	
		foreach($this->__data as $key => $tab) {
			$licss = ' class="'.$this->licss.'"';
			$acss  = ' class="'.$this->acss.'"';
			if(!isset($tab['hidden']) || !$tab['hidden'] === true) {
				if(isset($tab['css']) && $tab['css'] !== '') {
					$licss = ' class="'.$this->licss.' '.$tab['css'].'"';
					if($tab['id'] == $this->id.$currenttab) {
						$licss = ' class="'.$this->licss.' '.$tab['css'].' '.$this->activecss.'"';
						$acss  = ' class="'.$this->acss.' '.$this->activecss.'"';
					}
				}
				else if($tab['id'] == $this->id.$currenttab) {
					$licss = ' class="'.$this->licss.' '.$this->activecss.'"';
					$acss  = ' class="'.$this->acss.' '.$this->activecss.'"';
				}
				$auto = '';
				if($this->auto_tab === true) {
					$auto = '?'.$this->id.'='.$key;
				}
				$i = 0;
				if(!isset($tab['request']) || $tab['request'] === '') {
					$tab['target'] = $tab['target'].$auto;
				}
				else if(is_array($tab['request'])) {
					$r = '';
					foreach ($tab['request'] as $ke => $arg) {
						$d = '&amp;';
						if($i === 0 && $auto === '') {
							$d = '?';
							$i = 1;
						}
						if(is_array($arg)) {
							foreach($arg as $k => $v) {
								if(is_array($v)) {
									## TODO array of arrays
								} else {
									$r .= $d.$ke.'['.$k.']='.$v;
								}
							}
						}
						if(is_string($arg)) {
							$r .= $d.$ke.'='.$arg;
						}
					}
					$tab['target'] = $tab['target'].$auto.$r;
				}
				else if(is_string($tab['request'])) {
					if($auto !== '') {
						$tab['target'] = $tab['target'].$auto.'&amp;'.$tab['request'];
					} else {
						$tab['target'] = $tab['target'].'?'.$tab['request'];
					}
				}
				$_str .= '<li id="tab_'.$tab['id'].'"'.$licss.'>';
				$title = '';
				if(isset($tab['title']) && $tab['title'] !== '') {
					$title = 'title="'.$tab['title'].'"';
				}

				if($tab['onclick'] !== false) {
				#if(strstr($tab['target'], $thisfile) && $tab['onclick'] !== false) {
					#$_str .= '<a data-toggle="tab"'.$title.' href="'.$tab['target'].'" onclick="'.$this->id.'Toggle(\''.$tab['id'].'\'); this.blur(); return false;">';
					$_str .= '<a '.$acss.' data-toggle="tab" '.$title.' href="'.$tab['target'].'" onclick="this.blur(); return false;">';

				} else {
					$_str .= '<a '.$acss.' '.$title.' href="'.$tab['target'].'" onclick="this.blur();">';
				}
				$_str .= $tab['label'];
				$_str .= "</a>";
				$_str .= "</li>\n";
			}
		}
		// build tab box
		$str = '';
		if($_str !== '') {
			$str = "\n<div ".$attribs.">\n";
			$str .= '<ul class="'.$this->ulcss.'">'."\n";
			$str .= $_str;
			$str .= "</ul>\n";
			if($this->custom_tab != '') {
				$str .= "<div class=\"custom_tab\">".$this->custom_tab."</div>\n";
			}
			if($this->floatbreaker === true) {
				$str .= "<div class=\"floatbreaker\" style=\"line-height:0px;clear:both;\">&#160;</div>\n";
			}
			$str .= "</div>\n";
		}
		return $str;
	}

	//------------------------------------------------
	/**
	* Create JS toggle function
	*
	* @access private
	* @return string
	*/
	//------------------------------------------------
	function __get_js() {
	$_str = '';
		$_str .= "\n<script type=\"text/javascript\">\n";
		$_str .= "function ".$this->id."Toggle(id) {\n";
		foreach($this->__data as $key => $tab) {
			if(isset($this->__elements[$key])) {
				$_str .= "document.getElementById('".$tab['id']."').style.display = 'none';\n";
			}
			$css = '';
			if(isset($tab['css']) && $tab['css'] !== '') {
				$css = $tab['css'];
			}
			$_str .= "document.getElementById('tab_".$tab['id']."').className = '".$css."';\n";
		}
		$_str .= "tab = document.getElementById('tab_' + id);\n";
		$_str .= "tab.className = tab.className+' ".$this->activecss."';\n";
		$_str .= "document.getElementById(id).style.display = 'block';\n";
		$_str .= "}\n";	
		$_str .= "</script>\n";
	return $_str;
	}

	//------------------------------------------------
	/**
	* Create messagebox
	*
	* @access private
	* @return string
	*/
	//------------------------------------------------	
	function __get_messagebox() {
	$_str = '';

		// handle filter
		$tmpfilter = $this->html->request()->filter;
		$filter    = array_merge($tmpfilter,$this->message_filter);
		$this->html->request()->set_filter($filter);
		$msg = $this->html->request()->get($this->message_param);
		// reset filter
		$this->html->request()->set_filter($tmpfilter);

		// handle css
		$css = $this->message_css['default'];
		if(is_array($msg)) {
			$key = key($msg);
			$msg = $msg[$key];
			if(array_key_exists($key, $this->message_css)) {
				$css = $this->message_css[$key];
			}
		}

		if($msg !== "") {

			// sanitize message string
			$msg = str_replace('<br>', '[[br]]', $msg);
			$msg = htmlspecialchars($msg);
			$msg = str_replace('[[br]]', '<br>', $msg);

			$_str .= '';
			$_str .= '<div class="'.$css.'" id="'.$this->id.'msgBox">'.$msg.'</div>';
			$_str .= '<script type="text/javascript">';
			$_str .= 'var '.$this->id.'aktiv = window.setInterval("'.$this->id.'msgBox()", '.$this->message_time.');';
			$_str .= 'function '.$this->id.'msgBox() {';
			$_str .= '    document.getElementById(\''.$this->id.'msgBox\').style.display = \'none\';';
			$_str .= '    window.clearInterval('.$this->id.'aktiv);';
			$_str .= '}';
			$_str .= '</script>';
			$_str .= '';
		}
	return $_str;
	}

}
?>
