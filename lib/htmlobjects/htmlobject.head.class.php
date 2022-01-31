<?php
/**
 * HTML Head
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_head
{
/**
* Doctype
*
* @access public
* @var enum [html|xhtml]
*/
var $doctype = 'html';
/**
* Doctypemodel
*
* @access public
* @var enum [strict|transitional|frameset]
*/
var $doctypemodel = 'transitional';
/**
* Title of page
*
* @access public
* @var string
*/
var $title = '';
/**
* Meta Information
*
* @access private
* @var string
*/
var $__meta = array();
/**
* External Styles
*
* @access private
* @var string
*/
var $__style = array();
/**
* External Scripts
*
* @access private
* @var string
*/
var $__script = array();

	//-------------------------------------------------
	/**
	* Get head values as string
	*
	* @access public
	* @return string
	*/
	//-------------------------------------------------
	function get_string() {
	
		if(count($this->__style) > 0) {
			$this->add_meta('Content-Style-Type', 'text/css');
		}
		if(count($this->__script) > 0) {
			$this->add_meta('Content-Script-Type', 'text/javascript');
		}
		$_str = "\n";
		$_str .= $this->__get_doctype();
		$_str .= "<head>\n";
		$_str .= implode("\n", $this->__meta)."\n";
		$_str .= implode("\n", $this->__style)."\n";
		$_str .= implode("\n", $this->__script)."\n";
		$_str .= '<title>'.$this->title."</title>\n";
		$_str .= "</head>\n";

		return $_str;
	}

	//-------------------------------------------------
	/**
	* Add metatag to head
	*
	* @access public
	* @param string $value
	* @param string $content
	* @param enum $type [http-equiv|name]	
	*/
	//-------------------------------------------------	
	function add_meta ($value, $content ,$type = 'http-equiv') {
		$this->__meta[] = '<meta '.$type.'="'.$value.'" content="'.$content.'">';
	}

	//-------------------------------------------------
	/**
	* Add external stylesheet to head
	*
	* if $path is null, $media will
	* be used as content.
	*
	* @access public
	* @param string | null $path
	* @param enum | string $media [all|screen|print]
	*/
	//-------------------------------------------------
	function add_style ($path, $media='all') {
		if(isset($path)) {
			$this->__style[] = '<link rel="stylesheet" media="'.$media.'" href="'.$path.'" type="text/css">';
		} else {
			$this->__style[] = '<style type="text/css">'.$media.'</style>';
		}
	}

	//-------------------------------------------------
	/**
	* Add external script to head
	*
	* @access public
	* @param string $path [url]
	*/
	//-------------------------------------------------	
	function add_script ($path) {
		$this->__script[] = '<script src="'.$path.'" type="text/javascript"></script>';
	}

	//-------------------------------------------------
	/**
	* Get doctype
	*
	* @access private
	* @return string
	*/
	//-------------------------------------------------
	function __get_doctype() {

		$this->doctype      = strtolower($this->doctype);
		$this->doctypemodel = strtolower($this->doctypemodel);

		if($this->doctype === 'xhtml' && $this->doctypemodel === 'strict') {
			$_str  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n";
			$_str .= '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
			$_str .= '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		}

		if($this->doctype === 'xhtml' && $this->doctypemodel === 'transitional') {
			$_str  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n";
			$_str .= '	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
			$_str .= '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		}

		if($this->doctype === 'xhtml' && $this->doctypemodel === 'frameset') {
			$_str  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"'."\n";
			$_str .= '	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'."\n";
			$_str .= '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		}

		if($this->doctype === 'html' && $this->doctypemodel === 'strict') {
			$_str  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"'."\n";
			$_str .= '	"http://www.w3.org/TR/html4/strict.dtd">'."\n";
			$_str .= '<html>'."\n";
		}

		if($this->doctype === 'html' && $this->doctypemodel === 'transitional') {
			$_str  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'."\n";
			$_str .= '	"http://www.w3.org/TR/html4/loose.dtd">'."\n";
			$_str .= '<html>'."\n";
		}

		if($this->doctype === 'html' && $this->doctypemodel === 'frameset') {
			$_str  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"'."\n";
			$_str .= '	"http://www.w3.org/TR/html4/frameset.dtd">'."\n";
			$_str .= '<html>'."\n";
		}

		return $_str;
	}

}

?>
