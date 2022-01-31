<?php
/**
 * standort_init
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_init
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_msg';
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
var $lang = array(
	'label' => 'Standort',
	'all' => '&Uuml;bersicht',
	'settings' => 'Einstellungen',
);

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
		$this->response = $response;
		$this->file = $file;
		$this->user = $user;
		$this->db = $db;
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
		$settings = $this->file->get_ini($this->profilesdir.'/settings.ini');
		$basedir  = $settings['config']['basedir'];
		$errors   = array();

		$folders = array('standort','standort/templates','standort/lang','standort/devices','import','import/standort');
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
		if(count($errors) < 1) {
			$files = $this->file->get_files(CLASSDIR.'plugins/standort/setup/templates');
			if(is_array($files)) {
				$target = $this->profilesdir.'/standort/templates/';
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
		if(count($errors) > 0) {
			$errors = implode('<br>', $errors);
		} else {
			$errors = '';
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
		#$ini = $this->file->get_ini($this->plugins);
		$settings = $this->file->get_ini($this->profilesdir.'/standort.ini');
		$response = $this->response;
		$action   = $this->response->html->request()->get($this->actions_name);
		$links    = '';
		// Validate user
		$groups = array();
		if(isset($settings['settings']['supervisor'])) {
			$groups[] = $settings['settings']['supervisor']; 
		}

/*
		$a = $response->html->a();
		if($action === 'inventory') {
			$a->css   = 'list-group-item active';
		} else {
			$a->css   = 'list-group-item';
		}
		$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'inventory', '?', true );
		$a->label = '<span class="glyphicon glyphicon-globe" style="margin: 0 10px 0 0;"></span> '.$this->lang['all'];
		$links .= $a->get_string();
*/

		if($this->user->is_valid($groups)) {
			$a = $response->html->a();
			if($action === 'settings') {
				$a->css   = 'list-group-item list-group-item-action active';
			} else {
				$a->css   = 'list-group-item list-group-item-action';
			}
			$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'settings', '?', true );
			$a->label = '<span class="icon icon-settings" style="margin: 0 10px 0 0;"></span> '.$this->lang['settings'];
			$links .= $a->get_string();
		}

		$t = $response->html->template($this->tpldir.'standort.menu.html');
		$t->add($links, 'links');
		$t->add($this->lang['label'], 'label');

		return $t->get_string();
	}

}
?>
