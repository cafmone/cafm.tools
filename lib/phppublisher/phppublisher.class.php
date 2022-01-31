<?php
/**
 * PHPPublisher
 *
 * @package phppublisher
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher
{
/**
* path to profiles
* @access public
* @var string
*/
var $profilesdir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $prefix
	 */
	//--------------------------------------------
	function __construct($prefix = 'publisher') {
		require_once(CLASSDIR.'lib/htmlobjects/htmlobject.class.php');
		require_once(CLASSDIR.'lib/db/query.class.php');
		require_once(CLASSDIR.'lib/file/file.handler.class.php');
		require_once(CLASSDIR.'lib/user/user.class.php');
		$this->PROFILESDIR = PROFILESDIR;
		$this->LIBDIR = CLASSDIR.'lib/phppublisher/';
		require_once($this->LIBDIR.'phppublisher.htmlobject.class.php');
	}

	//--------------------------------------------
	/**
	 * Init PPHPublisher
	 *
	 * @access private
	 */
	//--------------------------------------------
	function __init() {
		$file = new file_handler();
		$s    = $file->get_ini($this->PROFILESDIR.'settings.ini');

		$file->permissions_file = isset($s['permissions']['file']) ? intval($s['permissions']['file'], 8) : 0666;
		$file->permissions_dir = isset($s['permissions']['dir']) ? intval($s['permissions']['dir'], 8) : 0777;

		if(isset($s['config']['users']) && isset($s['user'])) {
			if(isset($s['user']['saveto'])) {
				$query = new query(CLASSDIR.'lib/db');
				if($s['user']['saveto'] === 'file') {
					$query->db = $this->PROFILESDIR;
					$query->type = 'file';
				}
				else if($s['user']['saveto'] === 'db') {
					$query->host = isset($s['query']['host']) ? $s['query']['host'] : null ;
					$query->db   = isset($s['query']['db'])   ? $s['query']['db']   : null ;
					$query->user = isset($s['query']['user']) ? $s['query']['user'] : null ;
					$query->pass = isset($s['query']['pass']) ? $s['query']['pass'] : null ;
					$query->type = isset($s['query']['type']) ? $s['query']['type'] : null ;
				}

				// handle external user data
				$external = null;
				if(isset($s['user']['data_external'])) {
					if($file->exists($s['user']['data_external'].'/users')) {
						$external = new query(CLASSDIR.'lib/db');
						$external->db = $s['user']['data_external'].'/';
						$external->type = 'file';
					}
				}
			}

			$user = new user($file, $query, $external);
			$user->authenticate = isset($s['user']['authenticate']) ? $s['user']['authenticate'] : null ;
			$user->authorize    = isset($s['user']['authorize'])    ? $s['user']['authorize']    : null ;
	
			// check for initial user
			$admin = $user->get('admin');
			if(!isset($admin)) {
				require_once(CLASSDIR.'lib/phppublisher/phppublisher.user.class.php');
				$user = new phppublisher_user($query);
			} else {
				$user->detach();
			}


		} else {
			// no user -> load dummy
			require_once(CLASSDIR.'lib/phppublisher/phppublisher.user.class.php');
			$user = new phppublisher_user();
		}

		// load html
		$html = new phppublisher_htmlobject($user);
		
		// set environment
		isset($s['config']['baseurl']) ? null : $s['config']['baseurl'] = $html->thisurl.'/';
		isset($s['config']['basedir']) ? null : $s['config']['basedir'] = $html->thisdir;

		$query = new query(CLASSDIR.'lib/db');
		if(isset($s['config']['query'])) {
			$query->host = isset($s['query']['host']) ? $s['query']['host'] : null ;
			$query->db   = isset($s['query']['db'])   ? $s['query']['db']   : null ;
			$query->user = isset($s['query']['user']) ? $s['query']['user'] : null ;
			$query->pass = isset($s['query']['pass']) ? $s['query']['pass'] : null ;
			$query->type = isset($s['query']['type']) ? $s['query']['type'] : null ;
		}

		// set environment classes
		$this->response    = $html->response();
		$this->file        = $file;
		$this->user        = $user;
		$this->db          = $query;
		$this->PROFILESDIR = $this->PROFILESDIR;

		$this->langdir = CLASSDIR.'lang/';
		if($this->file->exists($this->PROFILESDIR.'lang')) {
			$this->langdir = $this->PROFILESDIR.'lang/';
		}

		// set Globals
		$GLOBALS['settings'] = $s;

		//do translate
		$this->response->html->lang = $this->user->translate($this->response->html->lang, $this->langdir, 'htmlobjects.ini');
		$this->file->lang = $this->user->translate($this->file->lang, $this->langdir, 'file.handler.ini');
	}


	//--------------------------------------------
	/**
	 * Controller
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function controller() {
		$this->__init();
		return $this->__factory( 'controller', $this );
	}

	//--------------------------------------------
	/**
	 * API
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function api() {
		$this->__init();
		return $this->__factory( 'api', $this );
	}

	//--------------------------------------------
	/**
	 * Json
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function json() {
		$this->__init();
		return $this->__factory( 'json', $this );
	}

	//--------------------------------------------
	/**
	 * build objects
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function __factory( $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null ) {
		if (!is_string( $name ) || !strlen( $name )) {
			throw new exception('Die zu ladende Klasse muss in einer Zeichenkette benannt werden');
		}
		$file  = $this->LIBDIR.'phppublisher.'.$name;
		require_once( $file.'.class.php' );
		$class = 'phppublisher_'.$name;
		if(isset($this->__debug) && $this->__debug === 'debug') {
			require_once( $this->__path.'/lib/phppublisher/phppublisher.debug.class.php' );
			if( file_exists($file.'.'.$this->__debug.'.class.php') ) {
				require_once( $file.'.'.$this->__debug.'.class.php' );
				$class = $class.'_'.$this->__debug;
			}
		}	
		return new $class( $arg1, $arg2, $arg3, $arg4, $arg5, $arg6 );
	}

}
?>
