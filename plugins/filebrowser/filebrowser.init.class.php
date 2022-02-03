<?php
/**
 * filebrowser_init
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class filebrowser_init
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'filebrowser_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'filebrowser_msg';

var $tpldir;

var $lang = array(
	'label' => 'Files',
	'browser' => 'Browser',
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
	function __construct($response, $file, $user) {
		$this->file = $file;
		$this->response = $response;
		$this->user = $user;
		$this->ini = $this->file->get_ini(PROFILESDIR.'filebrowser.ini');
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
		$errors = array();
		$files = array('filebrowser.ini');
		foreach($files as $file) {
			if(!$this->file->exists(PROFILESDIR.$file)) {
				$error = $this->file->copy(CLASSDIR.'plugins/filebrowser/setup/'.$file, PROFILESDIR.$file);
				if($error !== '') {
					$errors[] = $error;
				}
			}
		}

		// handle templates
		if($this->file->exists(PROFILESDIR.'/templates/')) {
			$files = $this->file->get_files(CLASSDIR.'plugins/filebrowser/templates/');
			foreach($files as $file) {
				if(strpos($file['name'], '.config.') === false) {
					if(!$this->file->exists(PROFILESDIR.'templates/'.$file['name'])) {
						$error = $this->file->copy($file['path'], PROFILESDIR.'templates/'.$file['name']);
						if($error !== '') {
							$errors[] = $error;
						}
					}
				}
			}
		}

		// handle lang
		if($this->file->exists(PROFILESDIR.'/lang/')) {
			$files = $this->file->get_files(CLASSDIR.'plugins/filebrowser/lang/');
			foreach($files as $file) {
				if(!$this->file->exists(PROFILESDIR.'lang/'.$file['name'])) {
					$error = $this->file->copy($file['path'], PROFILESDIR.'lang/'.$file['name']);
					if($error !== '') {
						$errors[] = $error;
					}
				}
			}
		}
		
		if(is_array($errors) && count($errors) > 0) {
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
		if(!isset($this->ini['config']['private'])) {

			// Group switch
			$groups = array();
			if(isset($this->ini['config']['supervisor'])) {
				$groups[] = $this->ini['config']['supervisor'];
			}

			if($this->user->is_valid($groups)) {
				$response = $this->response;
				$action   = $this->response->html->request()->get('index_action_plugin');

				$a        = $response->html->a();
				$a->href  =  $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true );
				$a->label = '<span class="icon icon-hd" style="margin: 0 10px 0 0;"></span>'.$this->lang['browser'];
				if($action === 'filebrowser') {
					$a->css   = 'list-group-item list-group-item-action active';
				} else {
					$a->css   = 'list-group-item list-group-item-action';
				}

				$t = $response->html->template($this->tpldir.'filebrowser.menu.html');
				//$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($a, 'browser');
				$t->add($this->lang['label'], 'label');
				return $t->get_string();
			}
		}
	}

}
?>
