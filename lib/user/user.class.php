<?php
/**
 * User
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2018, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user
{
/**
 * Authorization type
 *
 * @access public
 * @param enum [httpd|session]
 */
var $authorize = 'httpd';
/**
 * Authentication type
 *
 * @access public
 * @param enum [file|db]
 */
var $authenticate = 'file';
/**
 * Salt
 *
 * @access public
 * @param string
 */
var $salt = 'w.zTff-44#55+stEQ';
/**
 * Session Name
 *
 * @access public
 */
var $session = 'MySession';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $fileobject
	 * @param object $query
	 * @param object [query] $external
	 */
	//--------------------------------------------
	function __construct( $file, $query, $external = null ) {
		$this->file  = $file;
		$this->query = $query;
		$this->external = $external;

		## TODO Profilesdir
		##$this->profiles = PROFILESDIR.'users';
	}

	//--------------------------------------------
	/**
	 * Get User
	 *
	 * If no user is found
	 * method returns null
	 *
	 * @access public
	 * @param string $user
	 * @return array | null
	 */
	//--------------------------------------------
	function get( $user = null ) {
		if( isset( $user ) ) {
			$user = $user;
		} else {
			switch ($this->authorize) {
			case 'httpd' :
				if ( isset( $_SERVER['REMOTE_USER'] ) ) {
					$user = $_SERVER['REMOTE_USER'];
				}
			break;
			case 'session' :
				$this->__session();
				if ( isset($_SESSION[$this->session]) ) {
					if($this->__crypt() === $_SESSION[$this->session]['id']) {
						$user = $_SESSION[$this->session]['user'];
					}
				}
			break;
			case 'ldap' :
				## TODO handle groups - default readonly?
				if ( isset( $_SERVER['REMOTE_USER'] ) ) {
					$user = $_SERVER['REMOTE_USER'];
					if( isset( $user ) ) {
						$this->__user['login'] = $user;
					}
				}
			break;
			};
		}

		if( isset( $user ) ) {
			if(isset($this->__user) && $this->__user['login'] === $user) {
				return $this->__user;
			} else {

				## TODO check external
				if(isset($this->external) && $user !== 'admin') {
					$ini    = $this->external->select( 'users', '*', array('login', $user) );
					$groups = $this->external->select( 'users2groups', array('group'), array('login', $user) );
					if(is_array($ini)) {
						$ini[key($ini)]['external'] = true;
					}
				}
				// user not external ?
				if(!isset($ini) || $ini === '') {
					$ini    = $this->query->select( 'users', '*', array('login', $user) );
					$groups = $this->query->select( 'users2groups', array('group'), array('login', $user) );
				}

				if( is_array($ini) ) {
					$ini = array_shift($ini);
					$ini['login']  = $user;
					if( is_array($groups) ) {
						foreach( $groups as $k => $v ) {
							$group[$v['group']] = $v['group'];
						}
						$ini['group'] = $group;
					}
					$this->detach();
					$this->__user = $ini;
					return $this->__user;
				}
			}
		}
	}

	//--------------------------------------------
	/**
	 * List Groups
	 *
	 * If no groups found
	 * method returns null
	 *
	 * @access public
	 * @param bool $group
	 * @param bool $admin (hide Admin group)
	 * @return array | null
	 */
	//--------------------------------------------
	function list_groups($group = false, $admin = false) {
		$result = $this->query->select( 'users2groups', array('group'), null, 'group');
		if(is_array($result)) {
			foreach($result as $r) {
				if($admin === false && $r['group'] === 'Admin') {
					continue;
				}
				if($group === true) {
					$groups[] = $r['group'];
				} else {
					$groups[] = array($r['group']);
				}
			}
			if(isset($groups)) {
				return $groups;
			}
		}
	}

	//--------------------------------------------
	/**
	 * List Users
	 *
	 * If no users found
	 * method returns null
	 *
	 * @access public
	 * @return array | null
	 */
	//--------------------------------------------
	function list_users() {
		$ini    = array();
		$users  = $this->query->select( 'users', '*' );
		$groups = $this->query->select( 'users2groups', array('login','group') );
		if(isset($users) && is_array($users)) {
			foreach( $users as $u ) {
				$ini[$u['login']] = $u;
				if( is_array($groups) ) {
					foreach( $groups as $k => $v ) {
						if(isset($v['login']) && $v['login'] === $u['login']) {
							$ini[$u['login']]['group'][$v['group']] = $v['group'];
						}
					}
				}
			}
		}
		// handle external userdata
		if(isset($this->external)) {
			$users  = $this->external->select( 'users', '*' );
			$groups = $this->external->select( 'users2groups', array('login','group') );
			if(isset($users) && is_array($users)) {
				foreach( $users as $u ) {
					$tmpgroup = null;
					if(isset($ini[$u['login']])) {
						// handle internal user groups
						if(isset($ini[$u['login']]['group'])) {
							$tmpgroup = $ini[$u['login']]['group'];
						}
					}
					// replace internal user by external user
					$ini[$u['login']] = $u;
					$ini[$u['login']]['external'] = true;
					if( is_array($groups) ) {
						foreach( $groups as $k => $v ) {
							if(isset($v['login']) && $v['login'] === $u['login']) {
								$ini[$u['login']]['group'][$v['group']] = $v['group'];
							}
						}
					}
					// merge groups
					if(isset($tmpgroup)) {
						if(isset($ini[$u['login']]['group'])) {
							$ini[$u['login']]['group'] = array_merge($ini[$u['login']]['group'], $tmpgroup);
						} else {
							$ini[$u['login']]['group'] = $tmpgroup;
						}
					}
				}
			}
		}
		if(count($ini) > 0) {
			return $ini;
		}
	}

	//--------------------------------------------
	/**
	 * is_admin
	 *
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------
	function is_admin() {
		$user = $this->get();
		if(isset($user)) {
			if( $user['login'] === 'admin' || (isset($user['group']) && in_array('Admin', $user['group'])) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * is_readonly
	 *
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------
	function is_readonly() {
		$user = $this->get();
		if( $user['login'] === 'admin' ) {
			return false;
		}
		else if(isset($user['readonly'])) {
			return true;
		} else {
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * is_valid
	 *
	 * @access public
	 * @param array $groups
	 * @return bool
	 */
	//--------------------------------------------
	function is_valid($groups = array()) {
		$user   = $this->get();
		$return = false;
		if(isset($user)) {
			if( $user['login'] === 'admin' || (isset($user['group']) && in_array('Admin', $user['group'])) ) {
				$return = true;
			} else {
				#if(!isset($user['readonly'])) {
					if(isset($groups) && is_array($groups)) {
						$groups = array_intersect($groups, $user['group']);
						if(count($groups) > 0) {
							$return = true;
						}
					}
				#}
			}
		}
		return $return;
	}

	//--------------------------------------------
	/**
	 * Unset user
	 *
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------
	function detach() {
		unset($this->__user);
	}

	//--------------------------------------------
	/**
	 * Get query object
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function query() {
		return $this->query;
	}

	//--------------------------------------------
	/**
	 * Translate
	 *
	 * @access public
	 * @param array $lang array to translate
	 * @param string $dir dir of translation files
	 * @param string $file translation file
	 * @return array
	 */
	//--------------------------------------------
	function translate( $lang, $dir, $file ) {
		$user = $this->get();
		if(isset($user) && isset($user['lang'])) {
			$path = $dir.'/'.$user['lang'].'.'.$file;
			$tmp  = $this->file->get_ini( $path );
			if(is_array($tmp)) {
				foreach($tmp as $k => $v) {
					if(is_array($v)) {
						foreach($v as $k2 => $v2) {
							$lang[$k][$k2] = $v2;
						}
					} else {
						$lang[$k] = $v;
					}
				}
			}
		}
		return $lang;
	}

	//--------------------------------------------
	/**
	 * Send access denied message
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function access_denied() {
		header('HTTP/1.0 401 Authorization Required');
		echo '<h1>Authorization Required</h1>';
		echo '<div>PHPPublisher could not verify that you are authorized to access the document requested.</div>';
		echo '<div>Make sure your server is configured correctly and .htaccess is at the right place.</div>';
		exit;
	}

	//--------------------------------------------
	/**
	 * Login (Session)
	 *
	 * @access public
	 * @param string $user
	 * @return null
	 */
	//--------------------------------------------
	function login($user) {
		$this->__session();
		$_SESSION[$this->session]['user'] = $user;
		$_SESSION[$this->session]['time'] = time();
		$_SESSION[$this->session]['id']   = $this->__crypt();
	}

	//--------------------------------------------
	/**
	 * Logout (Session)
	 *
	 * @access public
	 * @param bool $kill
	 * @return null
	 */
	//--------------------------------------------
	function logout( $kill = false) {
		if(isset($_SESSION[$this->session])) {
			if($kill === true) {
				if (ini_get("session.use_cookies")) {
					$p = session_get_cookie_params();
					setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
				}
				unset($_SESSION);
			}
			if($kill === false) {
				unset($_SESSION[$this->session]);
			}
		}
	}
	
	//--------------------------------------------
	/**
	 * Crypt Session id
	 *
	 * @access private
	 * @return string
	 */
	//--------------------------------------------
	function __crypt() {
		$ip = $_SERVER["REMOTE_ADDR"];
		$ag = $_SERVER["HTTP_USER_AGENT"];
		$id = crypt($this->salt.''.$ag.''.$ip, $this->salt);
		return $id;
	}
	
	//--------------------------------------------
	/**
	 * Start session
	 *
	 * @access private
	 * @return string
	 */
	//--------------------------------------------
	function __session() {
		if(!isset($_SESSION) ) {
			session_start();
		}
	}

	//--------------------------------------------
	/**
	 * Set Access Rights
	 *
	 * @access public
	 * @param array $allow array of controller rights
	 * @param string $file access rights
	 * @return array

	 */
	//--------------------------------------------

/*
	function set_rights( $rights, $file ) {
		// get current user
		$user  = $this->get();
		$allow = $rights;
		if($user['login'] === 'admin' || in_array('Admin', $user['group'])) {
			foreach($allow as $k => $v) {
				if($v === false) {
					$allow[$k] = true;
				}
			}
		} else {
			if(file_exists($file)) {
				$ini = $this->file->get_ini( $file );
				$groups = array();
				foreach($ini as $k => $v) {
					if(in_array($k, $user['group'])) {
						$groups[] = array('count' => count($v), 'key' => $k);
					}
				}
				if(count($groups) > 0) {
					if(count($groups) > 1) {
						$group = $groups[0]['key'];
					} else {
						$group = $groups[0]['key'];
					}
					foreach($allow as $k => $v) {
						if(array_key_exists($k, $ini[$group])) {
							$tmp = $ini[$group][$k];
							if($tmp === 'on') {
								$tmp = true;
							}
							$allow[$k] = $tmp;
						} else {
							$allow[$k] = false;
						}
					}
				}
			}
		}

		#echo 'Allow --';
		#print_r($allow);

		#echo '<br>';
		return $allow;

	}
*/	
	
/*	
	function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {

		set_error_handler(array(&$this, 'handleError'));
			try {

			} catch (Exeption $e) {
				echo $e->getMessage();
			}
		restore_error_handler();

		echo '<br>';
		echo '<h6>Error</h6>';
		echo $errno.' ';
		echo $errstr.' ';
		echo $errfile.' ';
		echo $errline.' ';
		echo '<hr>';
		#echo '<pre>';
		#print_r($errcontext);
		#echo'</pre>';
		// error was suppressed with the @-operator
		if (0 === error_reporting()) {
			// handover to standard error hanlder
		    return false;
		}
		#throw new Exception($errstr);
		#echo '<br>';
		#return false;
	}
*/

}
?>
