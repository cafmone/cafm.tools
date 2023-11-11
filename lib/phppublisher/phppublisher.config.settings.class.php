<?php
/**
 * phppublisher_config_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_config_settings
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_action';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* path to ini file
* @access public
* @var string
*/
var $settings;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'label_basics' => 'Basics',
		'label_modules' => 'Modules',
		'label_folders' => 'Folders',
		'label_smtp' => 'Smtp',
		'label_permissions' => 'Permissions',
		'action_configure' => 'configure',
		'error_folder_not_found' => 'Could not find folder %s',
		'update_sucess' => 'Settings updated successfully',
		'config' => array(
			'title' => 'Title',
			'index' => 'Index',
			'query' => 'Database',
			'smtp' => 'Smtp',
			'users' => 'Users',
			'baseurl' => 'Baseurl',
			'basedir' => 'Basedir',
			'link_virtual' => 'Link files virtual',
		),
		'permission' => array(
			'file' => 'Files',
			'dir' => 'Directories',
			'config_admin_only' => 'Settings user admin only',
			'plugins_admin_only' => 'Plugins user admin only',
		),
		'folders' => array(
			'templates' => 'Templates',
			'lang' => 'Languages',
			'images' => 'Images',
			'login' => 'Login',
			'js' => 'Js',
			'css' => 'Css',
			'fonts' => 'Fonts',
			'derived' => 'derived',
			'error_folder' => '%s folder name must be a-z0-9/_-'
		)
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller  = $controller;
		$this->file        = $controller->file;
		$this->response    = $controller->response->response();
		$this->user        = $controller->user;
		$this->PROFILESDIR = $controller->PROFILESDIR;
		$this->settings    = $this->PROFILESDIR.'settings.ini';

		$this->folders['custom']   = array('css', 'images', 'js', 'login');
		$this->folders['static']   = array('fonts');
		$this->folders['profiles'] = array('backup','export','import','tmp','webdav');
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
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		if($this->response->cancel()) {
			$this->action = 'settings';
		}
		if($this->response->html->request()->get('usersettings[cancel]') !== '') {
			$this->action = 'settings';
		}
		if($this->response->html->request()->get('querysettings[cancel]') !== '') {
			$this->action = 'settings';
		}
		if($this->response->html->request()->get('smtpsettings[cancel]') !== '') {
			$this->action = 'settings';
		}

		if(isset($action)) {
			$this->action = $action;
		}

		switch( $this->action ) {
			case '':
			case 'settings':
				$form = $this->settings();
				$users = '&#160;';
				if(isset($GLOBALS['settings']['config']['users'])) {
					$p = $this->response->get_string($this->actions_name, 'user', '?', true );
					$a = $this->response->html->a();
					$a->href  = $this->response->html->thisfile.$p;
					$a->label = $this->lang['action_configure'];
					$a->title = $this->lang['action_configure'];
					$a->css   = 'configure';
					$a->handler = 'onclick="phppublisher.wait();"';
					$users = $a->get_string();
				}
				$smtp = '&#160;';
				if(isset($GLOBALS['settings']['config']['smtp'])) {
					$p = $this->response->get_string($this->actions_name, 'smtp', '?', true );
					$a = $this->response->html->a();
					$a->href  = $this->response->html->thisfile.$p;
					$a->label = $this->lang['action_configure'];
					$a->title = $this->lang['action_configure'];
					$a->css   = 'configure';
					$a->handler = 'onclick="phppublisher.wait();"';
					$smtp = $a->get_string();
				}
				$query = '&#160;';
				if(isset($GLOBALS['settings']['config']['query'])) {
					$p = $this->response->get_string($this->actions_name, 'query', '?', true );
					$a = $this->response->html->a();
					$a->href  = $this->response->html->thisfile.$p;
					$a->label = $this->lang['action_configure'];
					$a->title = $this->lang['action_configure'];
					$a->css   = 'configure';
					$a->handler = 'onclick="phppublisher.wait();"';
					$query = $a->get_string();
				}

				$vars = array('thisfile' => $this->response->html->thisfile);
				$t = $this->response->html->template($this->tpldir.'phppublisher.config.settings.html');
				$t->add($this->lang['label_basics'], 'label_basics');
				$t->add($this->lang['label_modules'], 'label_modules');
				$t->add($this->lang['label_folders'], 'label_folders');
				$t->add($this->lang['label_permissions'], 'label_permissions');
				$t->add($query, 'link_query');
				$t->add($smtp, 'link_smtp');
				$t->add($users, 'link_users');
				$t->add($vars);
				$t->add($form);
				$t->group_elements(array('param_' => 'form', 'folder_' => 'folders', 'config_' => 'config'));
				return $t;
			break;
			case 'smtp':
				return $this->smtp();
			break;
			case 'query':
				return $this->query();
			break;
			case 'user':
				return $this->user();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @return htmlobject_template
	 * @TODO login folder + baseurl and basedir
	 */
	//--------------------------------------------
	function settings() {
		$form = $this->get_form();
		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request();

			// handle profiles folders
			$this->file->permissions_dir = intval('0777', 8);
			$folders =  $this->folders['profiles'];
			foreach($folders as $v) {
				$error = '';
				if(!$this->file->exists($this->PROFILESDIR.$v)) {
					$error = $this->file->mkdir($this->PROFILESDIR.$v);
				}
				if($error !== '') {
					$_REQUEST[$this->message_param] = $error;
					break;
				}
			}

			// handle permissions
			if(isset($request['permissions']['file'])) {
				if(
					$request['permissions']['file'] === '0666' ||
					$request['permissions']['file'] === '0664' ||
					$request['permissions']['file'] === '0644'
				) {				
					$this->file->permissions_file = intval($request['permissions']['file'], 8);
				} else {
					$form->set_error('permissions[file]', 'error file');
				}
			}
			if(isset($request['permissions']['dir'])) {
				if(
					$request['permissions']['dir'] === '0777' ||
					$request['permissions']['dir'] === '0775' ||
					$request['permissions']['dir'] === '0755'
				) {	
					$this->file->permissions_dir = intval($request['permissions']['dir'], 8);
				} else {
					$form->set_error('permissions[dir]', 'error dir');
				}
			}

			// handle httpdocs folders
			if(isset($request['folders']) && !$form->get_errors()) {
				foreach($request['folders'] as $k => $v) {
					$error = '';
					if(!preg_match('~/$~', $v)) {
						$request['folders'][$k] = $v.'/';
					}
					$target = $GLOBALS['settings']['config']['basedir'].$v;
					if(isset($GLOBALS['settings']['folders'][$k])) {
						if($GLOBALS['settings']['folders'][$k] !== $v) {
							$source = $GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders'][$k];
							$error  = $this->file->rename($source, $target);
						}
					}
					if(!$this->file->exists($target)) {
						$error = $this->file->mkdir($target);
					}
					if($error === '') {
						$files = $this->file->get_files(CLASSDIR.'setup/'.$k);
						foreach($files as $file) {
							if($error === '' && !$this->file->exists($target.'/'.$file['name'])) {
								if(isset($request['config']['link_virtual'])) {
									$error = $this->file->symlink( $file['path'], $target.'/'.$file['name']);
								} else {
									$error = $this->file->copy($file['path'], $target.'/'.$file['name']);
								}
							}
						}
					}
					// handle static custom.css
					if($k === 'css') {
						if(! $this->file->exists($target.'/custom.css')) {
							$error = $this->file->mkfile($target.'/custom.css', '');
						}
					}

					if($error !== '') {
						$form->set_error('folders['.$k.']', $error);
					}
				}
			}

			// handle login folder
			if(isset($request['folders']['login']) && !$form->get_errors()) {
				if(!isset($GLOBALS['settings']['folders']['login'])) {
					$path = $GLOBALS['settings']['config']['basedir'].$request['folders']['login'].'.htaccess-disabled';
					if(!$this->file->exists($path)) {
						$error = $this->file->mkfile($path, $this->__htaccess());
						if($error !== '') {
							$form->set_error('folders[login]', $error);
						}
					}
				}
				if(!isset($request['config']['basedir'])) {
					$request['config']['basedir'] = $this->response->html->thisdir;
				}
				if(!isset($request['config']['baseurl'])) {
					$request['config']['baseurl'] = $this->response->html->thisurl;
				}
				// handle .htaccess
				$f = $request['config']['basedir'].$request['folders']['login'].'.htaccess';
				if(!isset($request['config']['users'])) {
					if($this->file->exists($f)) {
						$error = $this->file->rename($f, $f.'-disabled');
					}
				} 
				else if(
						#$this->user instanceof user && 
						isset($GLOBALS['settings']['user']['authorize']) &&
						$GLOBALS['settings']['user']['authorize'] === 'httpd'
				) {
					if($this->file->exists($f.'-disabled')) {
						$error = $this->file->rename($f.'-disabled', $f);
					}
				}
			}
			// handle basedir
			if(isset($request['config']['basedir'])) {
				if(is_dir($request['config']['basedir'])) {
					if(!preg_match('~/$~', $request['config']['basedir'])) {
						$request['config']['basedir'] = $request['config']['basedir'].'/';
					}
				} else {
					$error = $this->lang['error_folder_not_found'];
					$form->set_error('config[basedir]', '');
				}
			}
			// handle baseurl
			if(isset($request['config']['baseurl'])) {
				if(!preg_match('~/$~', $request['config']['baseurl'])) {
					$request['config']['baseurl'] = $request['config']['baseurl'].'/';
				}
			}
			// change ini
			if(!$form->get_errors()) {
				$old = $this->file->get_ini( $this->settings );
				if(is_array($old)) {
					// handle changed login folder
					if(
						isset($request['folders']['login']) && 
						isset($old['folders']['login']) && 
						$request['folders']['login'] !== $old['folders']['login']
					) {
						$redirect = true;
					}
					unset($old['config']);
					unset($old['permissions']);
					unset($old['folders']);
					$request = array_merge($old,$request);
				}
				$error = $this->file->make_ini( $this->settings, $request );
				if($error === '') {
					$msg = $this->lang['update_sucess'];
					$url = $this->response->get_url($this->actions_name, 'settings', $this->message_param, $msg);
					// handle redirect if login folder changed
					if(isset($redirect)) {
						$url = $request['config']['baseurl'].$request['folders']['login'].$url;
					}
					// redirect to trigger settings (user,db,smtp)
					$this->response->redirect($url);
				} else {
					$_REQUEST[$this->message_param] = $error;
				}
			} else {
				$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
			}
		} 
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
		}
		return $form;
	}

	//--------------------------------------------
	/**
	 * Smtp
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function smtp( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			$response = $this->response->response();
			$response->id = 'smtpsettings';
			$response->add($this->actions_name, 'smtp');
			require_once(CLASSDIR.'lib/smtp/smtp.settings.class.php');
			$controller = new smtp_settings($this->file, $response);
			$controller->actions_name = 'smtp_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'lib/smtp/templates/';
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Query
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function query( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			$response = $this->response->response();
			$response->id = 'querysettings';
			$response->add($this->actions_name, 'query');
			require_once(CLASSDIR.'lib/db/query.settings.class.php');
			$controller = new query_settings($this->file, $response);
			$controller->actions_name = 'query_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'lib/db/templates/';
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Users
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function user( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			$response = $this->response->response();
			$response->id = 'usersettings';
			$response->add($this->actions_name, 'user');
			require_once(CLASSDIR.'lib/user/user.settings.class.php');
			$controller = new user_settings($this->file, $response);
			$controller->actions_name = 'user_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'lib/user/templates/';
			$controller->lang = $this->user->translate($controller->lang, CLASSDIR.'/lib/user/lang', 'user.settings.ini');
			$data = $controller->action();
		}
		return $data;
	}

	//--------------------------------------------
	/**
	 * Get Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form() {
		$ini  = $this->file->get_ini( $this->settings );
		$form = $this->response->get_form($this->actions_name, 'settings');

		$d['config_url']['label'] = $this->lang['config']['baseurl'];
		if(isset($ini['folders']['login'])) {
			$d['config_url']['required'] = true;
		}
		$d['config_url']['object']['type']            = 'htmlobject_input';
		$d['config_url']['object']['attrib']['name']  = 'config[baseurl]';
		$d['config_url']['object']['attrib']['type']  = 'text';
		if(isset($ini['config']['baseurl'])) {
			$d['config_url']['object']['attrib']['value'] = $ini['config']['baseurl'];
		}

		$d['config_dir']['label'] = $this->lang['config']['basedir'];
		if(isset($ini['folders']['login'])) {
			$d['config_dir']['required'] = true;
		}
		$d['config_dir']['object']['type']           = 'htmlobject_input';
		$d['config_dir']['object']['attrib']['name'] = 'config[basedir]';
		$d['config_dir']['object']['attrib']['type'] = 'text';
		if(isset($ini['config']['basedir'])) {
			$d['config_dir']['object']['attrib']['value'] = $ini['config']['basedir'];
		}

		$d['config_title']['label'] = $this->lang['config']['title'];
		$d['config_title']['object']['type']            = 'htmlobject_input';
		$d['config_title']['object']['attrib']['name']  = 'config[title]';
		$d['config_title']['object']['attrib']['type']  = 'text';
		$d['config_title']['object']['attrib']['maxlength']  = 50;
		if(isset($ini['config']['title'])) {
			$d['config_title']['object']['attrib']['value'] = $ini['config']['title'];
		}

		$d['module_users']['label']                    = $this->lang['config']['users'];
		$d['module_users']['object']['type']           = 'htmlobject_input';
		$d['module_users']['object']['attrib']['type'] = 'checkbox';
		$d['module_users']['object']['attrib']['name'] = 'config[users]';
		if(isset($ini['config']['users'])){
			$d['module_users']['object']['attrib']['checked'] = true;
		}

		$d['module_query']['label']                    = $this->lang['config']['query'];
		$d['module_query']['object']['type']           = 'htmlobject_input';
		$d['module_query']['object']['attrib']['type'] = 'checkbox';
		$d['module_query']['object']['attrib']['name'] = 'config[query]';
		if(isset($ini['config']['query'])){
			$d['module_query']['object']['attrib']['checked'] = true;
		}

		$d['module_smtp']['label']                    = $this->lang['config']['smtp'];
		$d['module_smtp']['object']['type']           = 'htmlobject_input';
		$d['module_smtp']['object']['attrib']['type'] = 'checkbox';
		$d['module_smtp']['object']['attrib']['name'] = 'config[smtp]';
		if(isset($ini['config']['smtp'])){
			$d['module_smtp']['object']['attrib']['checked'] = true;
		}

		// PERMISSIONS
		$d['permission_settings_admin_only']['label']                    = $this->lang['permission']['config_admin_only'];
		$d['permission_settings_admin_only']['object']['type']           = 'input';
		$d['permission_settings_admin_only']['object']['attrib']['type'] = 'checkbox';
		$d['permission_settings_admin_only']['object']['attrib']['name'] = 'permissions[config_admin_only]';
		if(isset($ini['permissions']['config_admin_only'])) {
			$d['permission_settings_admin_only']['object']['attrib']['checked'] = true;
		}

		$d['permission_plugins_admin_only']['label']                    = $this->lang['permission']['plugins_admin_only'];
		$d['permission_plugins_admin_only']['object']['type']           = 'input';
		$d['permission_plugins_admin_only']['object']['attrib']['type'] = 'checkbox';
		$d['permission_plugins_admin_only']['object']['attrib']['name'] = 'permissions[plugins_admin_only]';
		if(isset($ini['permissions']['plugins_admin_only'])) {
			$d['permission_plugins_admin_only']['object']['attrib']['checked'] = true;
		}

		$options = array(array("0666", "0666"), array("0664", "0664"), array("0644", "0644"));
		$d['permission_file']['label']                       = $this->lang['permission']['file'];
		$d['permission_file']['required']                    = true;
		$d['permission_file']['object']['type']              = 'select';
		$d['permission_file']['object']['attrib']['name']    = 'permissions[file]';
		$d['permission_file']['object']['attrib']['index']   = array(0,1);
		$d['permission_file']['object']['attrib']['options'] = $options;
		if(isset($ini['permissions']['file'])) {
			$d['permission_file']['object']['attrib']['selected'] = array($ini['permissions']['file']);
		}

		$options = array(array("0777", "0777"), array("0775", "0775"), array("0755", "0755"));
		$d['permission_dir']['label']                       = $this->lang['permission']['dir'];
		$d['permission_dir']['required']                    = true;
		$d['permission_dir']['object']['type']              = 'select';
		$d['permission_dir']['object']['attrib']['name']    = 'permissions[dir]';
		$d['permission_dir']['object']['attrib']['index']   = array(0,1);
		$d['permission_dir']['object']['attrib']['options'] = $options;
		if(isset($ini['permissions']['dir'])) {
			$d['permission_dir']['object']['attrib']['selected'] = array($ini['permissions']['dir']);
		}

		$d['link_virtual']['label']                    = $this->lang['config']['link_virtual'];
		$d['link_virtual']['object']['type']           = 'htmlobject_input';
		$d['link_virtual']['object']['attrib']['type'] = 'checkbox';
		$d['link_virtual']['object']['attrib']['name'] = 'config[link_virtual]';
		if(isset($ini['config']['link_virtual'])){
			$d['link_virtual']['object']['attrib']['checked'] = true;
		}
		// Handle Windows
		if($this->file->is_win()) {
			$d['link_virtual']['object']['attrib']['disabled'] = true;
		}

		// FOLDERS 
		// customizeable
		$folders = $this->folders['custom'];
		foreach($folders as $v) {
			$d['folder_'.$v]['label'] = $this->lang['folders'][$v];
			$d['folder_'.$v]['validate']['regex']    = '~^[a-z0-9/_-]+$~i';
			$d['folder_'.$v]['validate']['errormsg'] = sprintf($this->lang['folders']['error_folder'], $this->lang['folders'][$v]);
			if($v === 'css' || $v === 'images' || $v === 'js' || $v === 'login') {
				$d['folder_'.$v]['required'] = true;
			}
			$d['folder_'.$v]['object']['type']            = 'htmlobject_input';
			$d['folder_'.$v]['object']['attrib']['name']  = 'folders['.$v.']';
			$d['folder_'.$v]['object']['attrib']['type']  = 'text';
			if(isset($ini['folders'][$v])) {
				$d['folder_'.$v]['object']['attrib']['value'] = $ini['folders'][$v];
			}
		}
		// FOLDERS 
		// static
		$folders =  $this->folders['static'];
		foreach($folders as $v) {
			$d['folder_dummy_'.$v]['label'] = $this->lang['folders'][$v];
			$d['folder_dummy_'.$v]['static'] = true;
			$d['folder_dummy_'.$v]['object']['type']            = 'htmlobject_input';
			$d['folder_dummy_'.$v]['object']['attrib']['name']  = 'dummy['.$v.']';
			$d['folder_dummy_'.$v]['object']['attrib']['type']  = 'text';
			$d['folder_dummy_'.$v]['object']['attrib']['disabled'] = true;
			$d['folder_dummy_'.$v]['object']['attrib']['value'] = $v.'/';

			$d['folder_'.$v]['object']['type']            = 'htmlobject_input';
			$d['folder_'.$v]['object']['attrib']['name']  = 'folders['.$v.']';
			$d['folder_'.$v]['object']['attrib']['type']  = 'hidden';
			$d['folder_'.$v]['object']['attrib']['value'] = $v.'/';
		}

		// index file
		if(isset($ini['config']['index'])) {
			$d['config_index_dummy_'.$v]['label'] = $this->lang['config']['index'];
			$d['config_index_dummy_'.$v]['static'] = true;
			$d['config_index_dummy_'.$v]['object']['type']            = 'htmlobject_input';
			$d['config_index_dummy_'.$v]['object']['attrib']['name']  = 'dummy[index]';
			$d['config_index_dummy_'.$v]['object']['attrib']['type']  = 'text';
			$d['config_index_dummy_'.$v]['object']['attrib']['disabled'] = true;
			$d['config_index_dummy_'.$v]['object']['attrib']['value'] = $ini['config']['index'];
		
			#$d['config_index']['label'] = sprintf($this->lang['config']['index'],$ini['config']['index']);
			$d['config_index']['object']['type']            = 'htmlobject_input';
			$d['config_index']['object']['attrib']['name']  = 'config[index]';
			$d['config_index']['object']['attrib']['type']  = 'hidden';
			$d['config_index']['object']['attrib']['value'] = $ini['config']['index'];
		} else {
			#$d['config_index'] = '';
		}

		if($this->file->exists($this->PROFILESDIR.'templates')) {
			$box = $this->response->html->div();
			$box->css  = 'form-control-static';
			$box->name = 'box';
			$box->add($this->lang['folders']['derived']);
			$d['folder_templates']['label'] = $this->lang['folders']['templates'];
			$d['folder_templates']['object'] = $box;
		}
		if($this->file->exists($this->PROFILESDIR.'lang')) {
			$box = $this->response->html->box();
			$box->css  = 'htmlobject_box';
			$box->name = 'box';
			$box->label = '<label class="control-label">'.$this->lang['folders']['lang'].'</label>';
			$box->add($this->lang['folders']['derived']);
			$d['folder_lang']['object'] = $box;
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get htaccess string
	 *
	 * @access private
	 * @return string
	 */
	//--------------------------------------------
	function __htaccess() {
		$s  = 'AuthName "CAFM.TOOLS"'."\n";
		$s .= 'AuthType Basic'."\n";
		$s .= 'AuthUserFile '.$this->PROFILESDIR.'.htpasswd'."\n";
		$s .= 'require valid-user'."\n";
		return $s;	
		/*
		<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteRule (.*)[-](.*)[-](.*)\.html$ preview.php?dir=$1&id=$2&lang=$3 [NC,QSA,L]
		RewriteRule (.*)[-](.*)[-](.*)\.php$ preview.php?dir=$1&id=$2&lang=$3 [NC,QSA,L]
		</IfModule>
		*/
	}

}
?>

