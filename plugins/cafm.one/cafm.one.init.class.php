<?php
/**
 * cafm_one_init
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_init
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cafm_one_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'cafm_one_msg';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* path to profiles folder
* @access public
* @var string
*/
var $profilesdir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file_handler $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user, $db) {
		$this->file = $file;
		$this->response = $response;
		$this->user = $user->get();
		$this->db = $db;
		$this->profilesdir = PROFILESDIR;

		$this->settings = $this->profilesdir.'/cafm.one.ini';
		$this->file = $file;
		$this->response = $response;
	}

	//--------------------------------------------
	/**
	 * Start
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function start() {
		$errors = '';
		$folders = array('cafm.one', 'cafm.one/templates');
		foreach($folders as $v) {
			$target = $this->profilesdir.'/'.$v;
			if(!$this->file->exists($target)) {
				$error = $this->file->mkdir($target);
				if($error !== '') {
					$errors[] = $error;
				}
			}
		}
		// copy templates
		if($errors === '') {
			$files = $this->file->get_files(CLASSDIR.'plugins/cafm.one/setup/templates');
			if(is_array($files)) {
				$target = $this->profilesdir.'/cafm.one/templates/';
				foreach($files as $f) {
					if(!$this->file->exists($target.$f['name'])) {
						$error = $this->file->copy($f['path'],$target.$f['name']);
						if($error !== '') {
							$errors[] = $error;
						}
					}
				}
			}
		}
		if(is_array($errors)) {
			$errors = implode('<br>', $errors);
		}
		return $errors;
	}

	//--------------------------------------------
	/**
	 * Menu
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function menu() {

	}

}
?>
