<?php
/**
 * Dummy User object
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_user
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct($query = null) {
		$this->query = $query;
	}

	//--------------------------------------------
	/**
	 * Get User
	 *
	 * @access public
	 * @param string $user
	 * @return array | null
	 */
	//--------------------------------------------
	function get() {
		$ini['login']  = 'admin';
		$ini['group'] = array('Admin');
		$ini['lang'] = 'en';
		return $ini;
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
		return $lang;
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
		return true;
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
		return false;
	}

	//--------------------------------------------
	/**
	 * is_valid
	 *
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------
	function is_valid() {
		return true;
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
		return null;
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
	function set_rights( $rights, $file ) {
		$user  = $this->get();
		$allow = $rights;
		if(file_exists($file)) {
			if($user['name'] === 'admin' || in_array('Admin', $user['group'])) {
				foreach($allow as $k => $v) {
					if($v === false) {
						$allow[$k] = true;
					}
				}
			}
		}
		return $allow;
	}

}
?>
