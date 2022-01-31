<?php
/**
 * filebrowser_config_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class filebrowser_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* lang
* @access public
* @var string
*/
var $lang = array(
	'settings' => 'Settings',
	'phpinfo' => 'Php',
	'quota' => 'Quota',
	'browser' => 'Browser',
	'config_substr' => 'Number of displayed characters',
	'config_private' => 'Hide Browser (Admin Mode)',
	'update_sucess' => 'Settings updated successfully',
	'error_NaN' => '%s must be a number',
);
/**
* prefix for tab menu
* @access public
* @var string
*/
var $prefix_tab;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir = '';

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param filer $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $file, $response, $db, $user ) {
		$this->file     = $file;
		$this->db       = $db;
		$this->user     = $user;
		$this->response = $response;
		$this->tpldir   = CLASSDIR.'/plugins/filebrowser/templates';
		$this->basedir  = $GLOBALS['settings']['config']['basedir'];
		$this->settings = PROFILESDIR.'filebrowser.ini';
		if($this->file->exists($this->settings)) {
			$this->ini = $this->file->get_ini( $this->settings );
		} else {
			$this->ini = array();
		}
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action( $action = null ) {

		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'config':
				$content[] = $this->config(true);
				if(isset($this->ini['config']['private'])) {
					$content[] = $this->browser();
				}
				$content[] = $this->phpinfo();
				$content[] = $this->quota();
			break;
			case 'browser':
				$content[] = $this->config();
				if(isset($this->ini['config']['private'])) {
					$content[] = $this->browser(true);
				}
				$content[] = $this->phpinfo();
				$content[] = $this->quota();
			break;
			case 'phpinfo':
				$content[] = $this->config();
				if(isset($this->ini['config']['private'])) {
					$content[] = $this->browser();
				}
				$content[] = $this->phpinfo(true);
				$content[] = $this->quota();
			break;
			case 'quota':
				$content[] = $this->config();
				if(isset($this->ini['config']['private'])) {
					$content[] = $this->browser();
				}
				$content[] = $this->phpinfo();
				$content[] = $this->quota(true);
			break;
			case 'backup':
				$this->backup();
			break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function config( $visible = false ) {
		$ini  = $this->ini;
		$form = $this->response->get_form($this->actions_name, 'config');
		$i = 0;

## TODO Group config

		$d['config_0']['label']                         = $this->lang['config_private'];
		$d['config_0']['object']['type']                = 'htmlobject_input';
		$d['config_0']['object']['attrib']['type']      = 'checkbox';
		$d['config_0']['object']['attrib']['name']      = 'config[private]';
		if(isset($ini['config']['private'])) {
			$d['config_0']['object']['attrib']['checked'] = true;
		}

		$d['config_1']['label']                         = $this->lang['config_substr'];
		$d['config_1']['required']                      = true;
		$d['config_1']['validate']['regex']             = '/^[0-9]+$/i';
		$d['config_1']['validate']['errormsg']          = sprintf($this->lang['error_NaN'], $this->lang['config_substr']);
		$d['config_1']['object']['type']                = 'htmlobject_input';
		$d['config_1']['object']['attrib']['type']      = 'text';
		$d['config_1']['object']['attrib']['name']      = 'config[substr]';
		$d['config_1']['object']['attrib']['maxlength'] = 3;
		$d['config_1']['object']['attrib']['style']     = 'width: 60px;';
		if(isset($ini['config']['substr'])) {
			$d['config_1']['object']['attrib']['value'] = $ini['config']['substr'];
		} else {
			$d['config_1']['object']['attrib']['value'] = 40;
		}

		// Permissions
		$groups = $this->user->list_groups();
		if(!isset($groups)) {
			$groups = array();
		}
		array_unshift($groups, '');
		$d['config_2']['label']                       = 'Supervisor group';
		$d['config_2']['object']['type']              = 'htmlobject_select';
		$d['config_2']['object']['attrib']['index']   = array(0,0);
		$d['config_2']['object']['attrib']['options'] = $groups;
		$d['config_2']['object']['attrib']['name']    = 'config[supervisor]';
		if(isset($ini['config']['supervisor'])) {
			$d['config_2']['object']['attrib']['selected'] = array($ini['config']['supervisor']);
		}

		if(isset($ini['folders']) && is_array($ini['folders'])) {
			foreach($ini['folders'] as $v) {
				$d['folder_'.$i]['label']                     = 'Path '.($i+1);
				$d['folder_'.$i]['object']['type']            = 'htmlobject_input';
				$d['folder_'.$i]['object']['attrib']['type']  = 'text';
				$d['folder_'.$i]['object']['attrib']['name']  = 'folders['.$i.']';
				$d['folder_'.$i]['object']['attrib']['value'] = $v;
				$i++;
			}
			$d['folder_'.$i]['label']                    = 'New';
			$d['folder_'.$i]['object']['type']           = 'htmlobject_input';
			$d['folder_'.$i]['object']['attrib']['type'] = 'text';
			$d['folder_'.$i]['object']['attrib']['name'] = 'folders['.$i.']';
		} else {
			$d['folder_0'] = '';
		}
		$form->add($d);
		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request();
			unset($ini['config']);
			unset($ini['folders']);
			if($request !== '') {
				$ini = $ini + $request;
			}
			$error = $this->file->make_ini( $this->settings, $ini );
			if($error === '') {
				$msg = $this->lang['update_sucess'];
				$this->response->redirect($this->response->get_url($this->actions_name, 'config', $this->message_param, $msg));
			} else {
				$_REQUEST[$this->message_param] = $error;
			}
		}
		$vars = array(
			'thisfile' => $this->response->html->thisfile,
		);
		$t = $this->response->html->template($this->tpldir.'/filebrowser.config.config.html');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		$t->group_elements(array('folder_' => 'folders'));
		$t->group_elements(array('config_' => 'config'));

		$content['label']   = $this->lang['settings'];
		$content['value']   = $t;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'config' );
		$content['onclick'] = false;
		if($this->action === 'config'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Browser
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function browser( $visible = false ) {
		$data = '';
		if( $visible === true ) {
			require_once(CLASSDIR.'plugins/filebrowser/class/filebrowser.controller.class.php');
			$response = $this->response->response();
			$response->add($this->actions_name, 'browser');

			$controller = new filebrowser_controller($this->file, $response, $this->db, $this->user);
			$controller->actions_name = 'server_action';
			$controller->message_param = 'browser_msg';
			$data = $controller->action(true);
		}
		$content['label']   = $this->lang['browser'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'browser' );
		$content['onclick'] = false;
		if($this->action === 'browser'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Phpinfo
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function phpinfo( $visible = false ) {
		$t = '';
		#$this->response->html->help($this->get_list($this->basedir));
		if( $visible === true ) {
			// Get PHP INFO
			ob_start();
			phpinfo();
			$phpinfo = ob_get_contents();
			ob_end_clean();
			$phpinfo = preg_replace ('~\n~i', '', $phpinfo);
			$phpinfo = preg_replace ('~<!DOCTYPE.*<body>~i', '', $phpinfo);
			$phpinfo = preg_replace ('~</body></html>~i', '', $phpinfo);

			$data['info']   = $phpinfo;

			$t = $this->response->html->template($this->tpldir.'/filebrowser.config.php.html');
			$t->add($data);
			$t->group_elements(array('param_' => 'form'));
		}
		$content['label']   = $this->lang['phpinfo'];
		$content['value']   = $t;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'phpinfo' );
		$content['onclick'] = false;
		if($this->action === 'phpinfo'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Quota
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function quota( $visible = false ) {
		$t = '';
		if( $visible === true ) {
			$list = $this->__get_list($this->basedir);
			$quota = 0;
			$count = count($list['files']);
			$table = $this->response->html->table();
			$table->border = 0;
			$table->style  = 'width:100%;';
			$table->css    = 'table table-bordered';
			$table->cellpadding = 0;
			$table->cellspacing = 0;
			for($i=0;$i<$count;$i++) {
				$tr = $this->response->html->tr();

				$td = $this->response->html->td();
				$td->add($i+1);
				$tr->add($td);

				$td = $this->response->html->td();
				$td->add($list['files'][$i]['path']);
				$tr->add($td);

				$td = $this->response->html->td();
				$td->add($list['files'][$i]['name']);
				$tr->add($td);

				$td = $this->response->html->td();
				$td->style = 'text-align:right;';
				$td->add($list['files'][$i]['size']);
				$tr->add($td);

				$table->add($tr);
				$quota += intval($list['files'][$i]['size']);
			}
			$tr = $this->response->html->tr();
			$td = $this->response->html->td();
			$td->style = 'text-align:right;';
			$td->colspan = 5;
			$td->add('<b>total:</b> '.$count.' <b>size:</b> '.$quota.' bytes');
			$tr->add($td);
			$table->add($tr);

			$href = $this->response->html->a();
			$href->href  = $this->response->get_url($this->actions_name, 'backup').'&header=false';
			$href->label = 'backup';

			$t = $this->response->html->template($this->tpldir.'/filebrowser.config.files.html');

			$t->add($href->get_string(), 'backup');
			$t->add($table->get_string(), 'table');
			$t->group_elements(array('param_' => 'form'));
		}
		$content['label']   = $this->lang['quota'];
		$content['value']   = $t;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'quota' );
		$content['onclick'] = false;
		if($this->action === 'quota'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Backup
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function backup() {
		require_once(CLASSDIR.'lib/archiv/archive.php');

		$name = @tempnam('/dummydir', 'xx');
		if(function_exists("gzcompress")) {
			$archiv = new zip_file($name);
			$mime   = 'application/zip';
			$fname  = 'download.zip';
		}
		#else if(function_exists("gzencode")) {
		#	$archiv = new gzip_file($name);
		#	$mime   = 'application/x-compressed-tar';
		#	$fname  = 'backup.tar.gz';
		#}
		#else if(function_exists("bzopen")) {
		#	$archiv = new bzip_file($name);
		#	$mime   = 'application/x-bzip-compressed-tar';
		#	$fname  = 'backup.tar.bz2';
		#}
		else {
			$archiv = new tar_file($name);
			$mime   = 'application/x-tar';
			$fname  = 'backup.tar';
		}

		$archiv->set_options(array('basedir' => $this->basedir, 'overwrite' => 1, 'level' => 9));
		$archiv->add_files(array('*'));
		$archiv->exclude_files("./admin");
		$archiv->create_archive();

		$file = $name;
		$size = filesize($file);

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$size);
		header("Content-disposition: inline; filename=$fname");
		header("Accept-Ranges: ".$size);
		flush();
		readfile($file);
		$this->file->remove($file);
		exit();
	}

	//--------------------------------------------
	/**
	 * Allow
	 *
	 * @access public
	 * @return array htmlobject_formbuilder
	 */
	//--------------------------------------------
	function allow($group, $ini = null) {
		require_once(CLASSDIR.'/plugins/filebrowser/filebrowser.controller.class.php');
		$controller = new filebrowser_controller(null, null, null);
		$i = 0;	
		foreach($controller->allow as $key => $value) {
			if($key === 'files') {
				$d['param_f'.$i]['label']                     = $key;
				$d['param_f'.$i]['object']['type']            = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']  = 'text';
				$d['param_f'.$i]['object']['attrib']['name']  = $group.'['.$key.']';
				$d['param_f'.$i]['object']['attrib']['value'] = $value;
				if(isset($ini[$key])) {
					$d['param_f'.$i]['object']['attrib']['value'] = $ini[$key];
				}  else if ($ini === null && $controller->allow[$key] === true) {
					$d['param_f'.$i]['object']['attrib']['value'] = $value;
				}
			} else {
				$d['param_f'.$i]['label']                    = $key;
				$d['param_f'.$i]['object']['type']           = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type'] = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name'] = $group.'['.$key.']';
				if(isset($ini[$key])) {
					$d['param_f'.$i]['object']['attrib']['checked'] = true;
				} else if ($ini === null && $controller->allow[$key] === true) {
					$d['param_f'.$i]['object']['attrib']['checked'] = true;
				}
			}
			$i++;
		}
		return $d;
	}

	function __get_list( $path ) {
		$list = array();
		$handle = opendir("$path/.");
		while (false !== ($file = @readdir($handle))) {
			if ($file !== '.'  && $file !== '..' && $path.'/'.$file !== $this->basedir.'/admin') {
				if(is_dir($path.'/'.$file)) {
					$list['folders'][] = str_replace($this->basedir, '',$path.'/'.$file);
					$tmp = $this->__get_list($path.'/'.$file);
					foreach($tmp as $k => $f) {
						if($k === 'folders') {
							foreach($f as $f2) {
								$list['folders'][] = str_replace($this->basedir, '', $f2);
							}
						}
						else  {
							foreach($f as $f2) {
								$list['files'][] = str_replace($this->basedir, '', $f2);
							}
						}
					}					
				} else {
					$info = array(
						'path' => str_replace($this->basedir, '', $path.'/'),
						'name' => $file,
						'size' => @filesize($path.'/'.$file),
					);
					$list['files'][] = $info;
				}
			}
		}
		isset($list['folders']) ? sort($list['folders']) : null;
		isset($list['files'])   ? sort($list['files'])   : null;
		return $list;
	}

}
?>
