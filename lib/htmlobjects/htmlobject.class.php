<?php
 /**
 * Htmlobjects
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2017, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject
{
/**
* Translation
* 
* @access public
* @var array
*/
var $lang = array(
	'table' => array(
		'button_refresh' => 'refresh',
		'label_sort'     => 'sort by ..',
		'label_order'    => 'sort order',
		'label_offset'   => 'offset',
		'label_limit'    => 'limit',
		'option_nolimit' => 'none',
		'option_asc'     => 'ASC',
		'option_desc'    => 'DESC',
		'select_label'   => 'Select:',
		'select_all'     => 'all',
		'select_none'    => 'none',
		'select_invert'  => 'inverted',
		'pageturn_first' => 'First page',
		'pageturn_prev'  => 'Previous page',
		'pageturn_next'  => 'Next page',
		'pageturn_last'  => 'Last page',
		'no_data'        => 'no data'
	),
	'form' => array(
		'error_value'     => '%s value is invalid',
		'error_required'  => '%s must not be empty',
		'error_maxlength' => '%s exeeds maxlength of %d',
		'error_minlength' => '%s undercuts minlength of %d',
		'required'        => '*'
	),
	'response' => array(
		'submit' => 'submit',
		'cancel' => 'cancel',
		'reset'  => 'reset'
	)
);
/**
* base href
* 
* @access public
* @var string
*/
var $thisfile;
/**
* base url
* 
* @access public
* @var string
*/
var $thisurl;
/**
* base dir
* 
* @access public
* @var string
*/
var $thisdir;

	//------------------------------------------------
	/**
	 * Constructor
	 *
	 * @param string $path path to htmlobject directory
	 * @access public
	 */
	//------------------------------------------------
	function __construct( $path ) {
		$this->__path = realpath($path);
		if(isset($_SERVER['REDIRECT_URL'])) {
			$this->thisfile = basename($_SERVER['REDIRECT_URL']);
		}
		else if(isset($_SERVER['PHP_SELF'])) {
			$this->thisfile = basename($_SERVER['PHP_SELF']);
		}
		if(isset($_SERVER['SCRIPT_NAME'])) {
			$dir = dirname($_SERVER['SCRIPT_NAME']);
			($dir !== '') ? $dir = $dir.'/' : null;
			$this->thisurl = dirname($_SERVER['SCRIPT_NAME']);
		}
		if(isset($_SERVER['SCRIPT_FILENAME'])) {
			$dir = dirname($_SERVER['SCRIPT_FILENAME']);
			// windows drive letter hack
			($dir !== '') ? $dir = preg_replace('~^[A-Z]:(.+)~i','$1',$dir).'/' : null;
			$this->thisdir = $dir;
		}
	}

	//------------------------------------------------
	/**
	 * A Object
	 *
	 * @access public
	 * @return htmlobject_a
	 */
	//------------------------------------------------
	function a() {
		$this->__require( 'base' );
		$this->__require( 'div' );
		return $this->__factory( 'a' );
	}

	//------------------------------------------------
	/**
	 * Base Object
	 *
	 * To force the base object into debug
	 * mode, debug must be triggered just
	 * after creating the htmlobject.
	 * Otherwise the normal base object will
	 * be set after creating any new object.
	 *
	 * @access protected
	 * @return htmlobject_base
	 */
	//------------------------------------------------
	function base() {
		if(isset($this->__base)) {
			$base = $this->__base;
		} else {
			$file = 'htmlobject.base.class.php';
			if(isset($this->__debug)) {
				$file = 'htmlobject.base.debug.class.php';
			}
			require_once( $this->__path.'/'.$file );
			$base = new htmlobject_base();
			$this->__base = $base;
		}
		return $base;
	}

	//------------------------------------------------
	/**
	 * Box Object
	 *
	 * @access public
	 * @return htmlobject_box
	 */
	//------------------------------------------------
	function box() {
		$this->__require( 'base' );
		$this->__require( 'div' );
		return $this->__factory( 'box' );
	}

	//------------------------------------------------
	/**
	 * Button Object
	 *
	 * @access public
	 * @return htmlobject_button
	 */
	//------------------------------------------------
	function button() {
		$this->__require( 'base' );
		return $this->__factory( 'button' );
	}

	//------------------------------------------------
	/**
	 * Custom Tag Object
	 *
	 * @access public
	 * @param string $tag html tag
	 * @return htmlobject_customtag
	 */
	//------------------------------------------------
	function customtag( $tag ) {
		$this->__require( 'base' );
		$this->__require( 'div' );
		$obj = $this->__factory( 'customtag' );
		$obj->tag = $tag;
		return $obj;
	}

	//------------------------------------------------
	/**
	 * Enable/Disable Debugger
	 *
	 * @access public
	 * @param bool $enable
	 */
	//------------------------------------------------
	function debug( $enable = true ) {
		if($enable === true) {
			$this->__debug = 'debug';
		}
		elseif($enable === false) {
			unset($this->__debug);
		}
	}

	//------------------------------------------------
	/**
	 * Div Object
	 *
	 * @access public
	 * @return htmlobject_div
	 */
	//------------------------------------------------
	function div() {
		$this->__require( 'base' );
		return $this->__factory( 'div' );
	}

	//------------------------------------------------
	/**
	 * Form Object
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//------------------------------------------------
	function form() {
		$this->__require( 'base' );
		$form = $this->__factory( 'form' );
		$form->action = $this->thisfile;
		return $form;
	}

	//------------------------------------------------
	/**
	 * Formbuilder Object
	 *
	 * params $response_id and $custom are set if
	 * formbuilder is created by htmlobject_response->get_form()
	 * param $custom can be used to pass additonal params to
	 * formbuilder call if needed e.g. htmlobject.class is derived
	 *
	 * @access public
	 * @param string $response_id
	 * @return htmlobject_formbuilder
	 */
	//------------------------------------------------
	function formbuilder( $response_id = null, $custom = null ) {
		$this->__require( 'base' );
		$this->__require( 'form' );
		$form = $this->__factory( 'formbuilder', $this );
		$form->action = $this->thisfile;
		return $form;
	}

	//------------------------------------------------
	/**
	 * Head Object
	 *
	 * @access public
	 * @return htmlobject_head
	 */
	//------------------------------------------------
	function head() {		
		return $this->__factory( 'head' );
	}

	//------------------------------------------------
	/**
	 * Print object information
	 *
	 * @access public
	 * @param object $object
	 */
	//------------------------------------------------
	function help( $obj ) {
		echo '<pre>';
		print_r($obj);
		if(get_class_methods($obj)) {
			echo join("()\n", get_class_methods($obj)).'()';
		}
		echo '</pre>';
	}

	//------------------------------------------------
	/**
	 * Iframe Object
	 *
	 * @access public
	 * @return htmlobject_img
	 */
	//------------------------------------------------
	function iframe() {
		$this->__require( 'base' );
		$this->__require( 'div' );
		return $this->__factory( 'iframe' );
	}

	//------------------------------------------------
	/**
	 * Image Object
	 *
	 * @access public
	 * @return htmlobject_img
	 */
	//------------------------------------------------
	function img() {
		$this->__require( 'base' );
		return $this->__factory( 'img' );
	}

	//------------------------------------------------
	/**
	 * Input Object
	 *
	 * @access public
	 * @return htmlobject_input
	 */
	//------------------------------------------------
	function input() {
		require_once($this->__path.'/htmlobject.base.class.php');
		return $this->__factory( 'input' );
	}

	//------------------------------------------------
	/**
	 * Option Object
	 *
	 * @access public
	 * @return htmlobject_option
	 */
	//------------------------------------------------
	function option() {
		$this->__require( 'base' );
		return $this->__factory( 'option' );
	}

	//------------------------------------------------
	/**
	 * Response Object
	 *
	 * @access public
	 * @param string $id prefix response cancel/submit
	 * @return htmlobject_response
	 */
	//------------------------------------------------
	function response( $id = 'response' ) {
		return $this->__factory( 'response', $this, $id );
	}

	//------------------------------------------------
	/**
	 * Request Object
	 *
	 * @access public
	 * @return htmlobject_request
	 */
	//------------------------------------------------
	function request() {		
		if(isset($this->__request)) {
			$request = $this->__request;
		} else {
			$request = $this->__factory( 'request' );
			$this->__request = $request;
		}
		return $request;
	}

	//------------------------------------------------
	/**
	 * Select Object
	 *
	 * @access public
	 * @return htmlobject_select
	 */
	//------------------------------------------------
	function select() {
		$this->__require( 'base' );
		$this->__require( 'option' );
		return $this->__factory( 'select' );
	}

	//------------------------------------------------
	/**
	 * Table Object
	 *
	 * @access public
	 * @return htmlobject_table
	 */
	//------------------------------------------------
	function table() {
		$this->__require( 'base' );
		$this->__require( 'td' );
		$this->__require( 'tr' );
		return $this->__factory( 'table' );
	}

	//------------------------------------------------
	/**
	 * Tablebuilder Object
	 *
	 * @access public
	 * @param string $id prefix for posted vars
	 * @param array $params array(key => value, ...);
	 * @return htmlobject_tablebuilder
	 */
	//------------------------------------------------
	function tablebuilder( $id, $params = null ) {
		$this->__require( 'base' );
		$this->__require( 'td' );
		$this->__require( 'tr' );
		$this->__require( 'table' );
		return $this->__factory( 'tablebuilder', $this, $id, $params);
	}

	//------------------------------------------------
	/**
	 * Tabmenu Object
	 *
	 * @access public
	 * @param string $id prefix for posted vars
	 * @return htmlobject_tabmenu
	 */
	//------------------------------------------------
	function tabmenu( $id ) {
		$this->__require( 'base' );
		$this->__require( 'div' );
		return $this->__factory( 'tabmenu', $this, $id);
	}

	//------------------------------------------------
	/**
	 * Template Object
	 *
	 * @access public
	 * @param string $template path to templatefile
	 * @return htmlobject_template
	 */
	//------------------------------------------------
	function template($template) {
		return $this->__factory( 'template', $template );
	}

	//------------------------------------------------
	/**
	 * Textarea Object
	 *
	 * @access public
	 * @return htmlobject_textarea
	 */
	//------------------------------------------------
	function textarea() {
		$this->__require( 'base' );
		return $this->__factory( 'textarea' );
	}

	//------------------------------------------------
	/**
	 * Td Object
	 *
	 * @access public
	 * @return htmlobject_td
	 */
	//------------------------------------------------
	function td() {
		$this->__require( 'base' );
		return $this->__factory( 'td' );
	}

	//------------------------------------------------
	/**
	 * Tr Object
	 *
	 * @access public
	 * @return htmlobject_tr
	 */
	//------------------------------------------------
	function tr() {
		$this->__require( 'base' );
		$this->__require( 'td' );
		return $this->__factory( 'tr' );
	}

	//------------------------------------------------
	/**
	 * Build objects
	 *
	 * @param string $name
	 * @param multi $arg1
	 * @param multi $arg2
	 * @param multi $arg3
	 * @param multi $arg4
	 * @param multi $arg5
	 * @param multi $arg6
	 * @access protected
	 * @return object
	 */
	//------------------------------------------------
	function __factory( $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null ) {
		$this->__require( $name );
		$class = 'htmlobject_'.$name;
		if(isset($this->__debug)) {
			require_once( $this->__path.'/htmlobject.debug.class.php' );
			$file = $this->__path.'/htmlobject.'.$name.'.'.$this->__debug.'.class.php';
			if( file_exists($file) ) {
				require_once( $file );
				$class = $class.'_debug';
			}
		}	
		return new $class( $arg1, $arg2, $arg3, $arg4, $arg5, $arg6 );
	}

	//------------------------------------------------
	/**
	 * require file
	 *
	 * @param string $name
	 * @access protected
	 * @return null
	 */
	//------------------------------------------------
	function __require( $name ) {
		$file  = $this->__path.'/htmlobject.'.$name;
		require_once( $file.'.class.php' );
	}

}
?>
