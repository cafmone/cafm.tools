<?php
/**
 * bestandsverwaltung_init
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_init
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'bestand_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'bestand_msg';
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
	'label' => 'Inventory',
	'all' => 'Survey',
	'recording' => 'Record',
	'settings' => 'Settings',
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
		#$this->plugins = $this->profilesdir.'/plugins.ini';
		#$this->settings = $this->profilesdir.'/bestandsverwaltung.ini';
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

		$folders = array('bestand','import','bestand/templates','bestand/devices','bestand/raumbuch' );
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
			$files = $this->file->get_files(CLASSDIR.'plugins/bestandsverwaltung/setup/templates');
			if(is_array($files)) {
				$target = $this->profilesdir.'/bestand/templates/';
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
		// copy shorturl
		if(count($errors) < 1) {
			$folders = array('shorturl','shorturl/bestand','shorturl/bestand/filter','shorturl/bestand/filter/id');
			foreach($folders as $v) {
				$target = $basedir.'/'.$v;
				if(!$this->file->exists($target)) {
					$error = $this->file->mkdir($target);
					if($error !== '') {
						$errors[] = $error;
					}
				}
			}
			$files = $this->file->get_files(CLASSDIR.'plugins/bestandsverwaltung/setup/shorturl/bestand/filter/id');
			if(is_array($files)) {
				$target = $basedir.'/shorturl/bestand/filter/id/';
				foreach($files as $f) {
					// handle .htaccess
					if($f['name'] === 'htaccess') { $f['name'] = '.'.$f['name']; }
					if(!$this->file->exists($target.$f['name'])) {
						if(isset($settings['config']['link_virtual'])) {
							$error = $this->file->symlink( $f['path'], $target.'/'.$f['name']);
						} else {
							$error = $this->file->copy($f['path'], $target.'/'.$f['name']);
						}
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
		$settings = $this->file->get_ini($this->profilesdir.'/bestandsverwaltung.ini');
		$response = $this->response;
		$action   = $this->response->html->request()->get($this->actions_name);
		$links    = '';
		// Validate user
		$groups = array();
		if(isset($settings['settings']['supervisor'])) {
			$groups[] = $settings['settings']['supervisor']; 
		}

		$a = $response->html->a();
		if($action === 'inventory') {
			$a->css   = 'list-group-item list-group-item-action active';
		} else {
			$a->css   = 'list-group-item list-group-item-action';
		}
		$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'inventory', '?', true );
		$a->label = '<span class="icon icon-home" style="margin: 0 10px 0 0;"></span> '.$this->lang['all'];
		$links .= $a->get_string();

		$a = $response->html->a();
		if($action === 'recording') {
			$a->css   = 'list-group-item list-group-item-action active';
		} else {
			$a->css   = 'list-group-item list-group-item-action';
		}
		$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'recording', '?', true );
		$a->label = '<span class="icon icon-plus" style="margin: 0 10px 0 0;"></span> '. $this->lang['recording'];
		$links .= $a->get_string();

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

		$t = $response->html->template($this->tpldir.'bestandsverwaltung.menu.html');
		$t->add($links, 'links');
		$t->add($this->lang['label'], 'label');

		if($this->file->exists( $GLOBALS['settings']['config']['basedir'].'login/dokumentation/' )) {

			$docu  = '<div id="betand_docu" class="card">';
			$docu .= '<div class="card-header">';
			$docu .= 'Dokumentation';
			$docu .= '</div>';
			$docu .= '<div class="list-group list-group-flush">';

			#if($this->file->exists( $GLOBALS['settings']['config']['basedir'].'login/dokumentation/inhalt.html' )) {
			#	$a = $response->html->a();
			#	$a->css   = 'list-group-item list-group-item-action';
			#	$a->target = '_blank';
			#	$a->href   = $GLOBALS['settings']['config']['baseurl'].'login/dokumentation/inhalt.html';
			#	$a->label  = 'Inhalt';
			#	$docu .= $a->get_string();
			#}

			$a = $response->html->a();
			$a->css   = 'list-group-item list-group-item-action';
			#$a->target = '_blank';
			$a->href   = $GLOBALS['settings']['config']['baseurl'].'login/dokumentation/';
			$a->label  = 'Dateien';
			$docu .= $a->get_string();

			$docu .= '</div>';
			$docu .= '</div>';
			$t->add($docu, 'docu');

		} else {
			$t->add('', 'docu');
		}
		return $t->get_string();
	}

}
?>
