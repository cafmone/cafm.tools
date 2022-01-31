<?php
/**
 * PHPCommander
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'pc';
/**
*  colors
*  @access public
*  @var array
*/
var $colors = array(
		'#000000',
		'#800000',
		'#008000',
		'#808000',
		'#000080',
		'#800080',
		'#008080',
		'#C0C0C0',
		'#808080',
		'#FF0000',
		'#00FF00',
		'#FFFF00',
		'#0000FF',
		'#FF00FF',
		'#00FFFF',
		'#FFFFFF',
	);
/**
*  translation for message strings
*  @access public
*  @var array
*/
var $lang = array(
	'select' => array(
		'new' => 'New',
		),
	'folder' => array(
		'label_date'          => "Date",
		'label_size'          => "Size",
		'label_name'          => "Name",
		'lang_name'           => "Foldername",
		'lang_new'            => "New folder",
		'lang_edit'           => "browse",
		'lang_delete'         => "delete",
		'lang_saved'          => "%s has been saved",
		'lang_delete_confirm' => "really delete?",
		'lang_copy'           => "copy",
		'lang_cut'            => "cut",
		'lang_clear'          => "clear",
		'lang_rename'         => "rename",
		'lang_download'       => "download",
		'lang_paste'          => "paste",
		'lang_upload'         => "upload",
		'lang_uploaded'       => "%s has been uploaded",
		'lang_filter'         => "filter"
		),
	'file' => array(
		'lang_edit'    => "edit",
		'lang_name'    => "Filename",
		'lang_new'     => "New File",
		'lang_saved'   => "%s has been saved",
		'lang_delete'  => "Delete File(s)",
		'lang_deleted' => "%s has been deleted",
		'lang_rename'  => "Rename File(s)",
		'lang_renamed' => "%s has been renamed to %s",
		'lang_submit'  => "submit",
		'lang_cancel'  => "cancel",
		),
	'editor' => array(
		'lang_colors'  => "Colors",
		'lang_files'   => "Files",
		'lang_delete'  => "delete",
		'lang_save'    => "save",
		'lang_big'     => "enlarge",
		'lang_small'   => "downsize",
		'lang_close'   => "close",
		'lang_insert'  => "insert",
		'lang_loading' => "loading",
		),
	'colors' => array(
		'lang_headline' => "Colors",
		'lang_close'    => "close",
		),
	);
/**
*  param name for messages
*  @access public
*  @var string
*/
var $message_param = 'strMsg';
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'file_id';
/**
* allow [true] or disallow [false] functions
*
* new = create file
* upload = show upload button
* download = allow download
* copy = allow copy
* cut = allow move
* rename = allow rename
* delete = allow remove
* fliter = show filter box
* dir = show directories
* create = create folder on upload if missing
* files = filter e.g. *.jpg
* 
* @access public
* @var array
*/
var $allow = array(
	'new'      => true,
	'upload'   => true,
	'download' => true,
	'copy'     => true,
	'cut'      => true,
	'rename'   => true,
	'delete'   => true,
	'filter'   => true,
	'edit'     => true,
	'dir'      => true,
	'create'   => false,
	'files'    => '*'
	);
/**
* Not used yet
*/
var $deny = array();
/**
* Add handler and callback to trs
*
* element and id of identifier will
* be added to event callback function
*
* @access array
* @var string
* <code>
* $table->handler_tr = array('onclick' => 'tr_click');
* 
* <tr onclick="tr_click(this, 'id')">
* </code>
*/
var $handler_tr = array(
	'onclick'     => 'tr_click',
	'onmouseover' => 'tr_hover',
	'onmouseout'  => 'tr_hover'
);
/**
* Path to template dir
*
* @access public
* @var string
*/
var $tpldir = '../phpcommander/templates';
/**
* Length of text in table
*
* rest will be replaced by ...
* false to disable
*
* @access public
* @var int
*/
var $substr = 24;
/**
* enable multiple file upload
* @access public
* @var bool
*/
var $upload_multiple = false;



var $__handlers = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path (to phpcommander directory)
	 * @param object $htmlobject
	 * @param object $fileobject
	 * @param string $prefix
	 * @param array $params array(key=>value)
	 */
	//--------------------------------------------
	function __construct( $path, $htmlobject, $fileobject, $prefix = 'commander', $params = array() ) {
		$this->file     = $fileobject;
		$this->html     = $htmlobject;
		$this->tpldir   = realpath($path).'/templates';
		$this->__path   = realpath($path);
		$this->__prefix = $prefix;

		$this->response          = $this->html->response($this->__prefix.'x');
		$this->response->params  = $params;
		$this->response->prefix  = $this->__prefix;

	}

	function add_handler( $handler, $key = null ) {
		if(isset($key)) {
			$this->__handlers[$key] = $handler;
		} else {
			$this->__handlers[] = $handler;
		}
	}

	//--------------------------------------------
	/**
	 * Api
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function api() {
		return $this->__factory( 'api', $this );
	}

	//--------------------------------------------
	/**
	 * Controller
	 *
	 * @access public
	 * @param array|string $root must be absolute path
	 * @return object
	 */
	//--------------------------------------------
	function controller($root) {
		return $this->__factory( 'controller', $root, $this );
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function select($actions, $path, $__root, $root, $dir) {
		$select = $this->__factory( 'select', $actions, $path, $__root, $root, $dir, $this);
		return $select;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function delete($path) {
		# identifier
		# actions_name
		# lang
		if($this->allow['delete'] === true) {
			return $this->__factory( 'delete', $path, $this->response, $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Rename
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function rename($path) {
		# identifier
		# actions_name
		# lang
		if($this->allow['rename'] === true) {
			return $this->__factory( 'rename', $path, $this->response, $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function insert($path) {
		# actions_name
		# lang
		if($this->allow['new'] === true) {
			return $this->__factory( 'insert', $path, $this->response, $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Copy / Cut
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function copy($path) {
		# identifier
		# actions_name
		# message_param ?
		if($this->allow['copy'] === true) {
			return $this->__factory( 'copy', $path, $this->response, $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function download($path) {
		# identifier
		if($this->allow['download'] === true) {
			require_once($this->__path.'/../archiv/archive.php');
			return $this->__factory( 'download', $path, $this->response, $this->file);
		}
	}

	//--------------------------------------------
	/**
	 * Filter
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function filter() {
		# actions_name
		# prefix
		# lang
		# tpldir
		if($this->allow['filter'] === true) {
			return $this->__factory( 'filter', $this->response);
		}
	}

	//--------------------------------------------
	/**
	 * Colors
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function colors() {
		# colors
		# lang
		# tpldir
		return $this->__factory( 'colors', $this );
	}

	//--------------------------------------------
	/**
	 * Editor
	 *
	 * @access public
	 * @param string $path
	 * @param object $file
	 * @return object
	 */
	//--------------------------------------------
	function editor($path) {
		# identifier
		# actions_name
		# lang
		# tpldir
		# colors()
		// allow edit when download is allowed
		if($this->allow['download'] === true) {
			return $this->__factory( 'editor', $this, $path);
		}
	}

	//--------------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @param string $path
	 * @return object
	 */
	//--------------------------------------------
	function upload($path) {
		# actions_name
		# identifier
		# tpldir
		# lang
		if($this->allow['upload'] === true) {
			$upload = $this->__factory( 'upload', $path, $this->response, $this->file);
			if($this->allow['create'] === true) {
				$upload->allow_create = true;
			}
			return $upload;
		}
	}

	//--------------------------------------------
	/**
	 * Build Objects
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function __factory( $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null ) {

		# TODO values can't be overwritten in constructor
		$this->response->actions_name = $this->__prefix.'['.$this->actions_name.']';
		$this->response->identifier   = $this->identifier_name;
		$this->response->tpldir       = $this->tpldir;
		$this->response->lang         = $this->lang;
		$this->response->message      = $this->message_param;

		$params = $this->html->request()->get($this->__prefix);
		if($params !== '') {
			unset($params[$this->actions_name]);
			$this->response->add($this->__prefix, $params);
		}
		#$this->html->help($this->response);


		if (!is_string( $name ) || !strlen( $name )) {
			throw new exception('Die zu ladende Klasse muss in einer Zeichenkette benannt werden');
		}
		$file  = $this->__path.'/phpcommander.'.$name;
		require_once( $file.'.class.php' );
		$class = 'phpcommander_'.$name;
		if(isset($this->__debug) && $this->__debug === 'debug') {
			require_once( $this->__path.'/phpcommander.debug.class.php' );
			if( file_exists($file.'.'.$this->__debug.'.class.php') ) {
				require_once( $file.'.'.$this->__debug.'.class.php' );
				$class = $class.'_'.$this->__debug;
			}
		}	
		return new $class( $arg1, $arg2, $arg3, $arg4, $arg5, $arg6 );
	}
		
}
?>
