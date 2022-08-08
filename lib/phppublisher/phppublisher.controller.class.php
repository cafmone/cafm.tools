<?php
/**
 * phppublisher_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'index_action';
/**
* path to template dir
* @access public
* @var string
*/
protected $tpldir;
/**
* path to translation files
* @access public
* @var string
*/
protected $langdir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'link_home' => 'Home',
	'link_config' => 'Settings',
	'link_account' => 'My Account',
	'link_login' => 'Login',
	'toggle_menu' => 'click to toggle menu',
	'edit_page' => 'click to edit page',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 */
	//--------------------------------------------
	function __construct( $phppublisher ) {
		## TODO profilesdir?
		$this->response    = $phppublisher->response;
		$this->db          = $phppublisher->db;
		$this->file        = $phppublisher->file;
		$this->user        = $phppublisher->user;
		$this->PROFILESDIR = $phppublisher->PROFILESDIR;
		$this->LIBDIR      = $phppublisher->LIBDIR;
		$this->langdir     = $phppublisher->langdir;
		$this->tpldir      = CLASSDIR.'templates/';

		$this->title       = $phppublisher->title;

		## TODO check files
		if($this->file->exists($this->PROFILESDIR.'templates')) {
			$this->tpldir = $this->PROFILESDIR.'templates/';
		}

		$this->settings = $this->PROFILESDIR.'/settings.ini';
		$this->plugins  = $this->file->get_ini( $this->PROFILESDIR.'/plugins.ini' );
		if(!is_array($this->plugins)){
			$this->plugins = array();
		}
		$this->addons = array();
		if(
			isset($GLOBALS['settings']) && 
			isset($GLOBALS['settings']['config']) &&
			isset($GLOBALS['settings']['config']['basedir'])
		 ) {
			$addons = $GLOBALS['settings']['config']['basedir'].'addons';
			if($this->file->exists($addons)) {
				$this->addons = $this->file->get_folders($addons);
			}
		}
		//do translate
		$this->lang = $this->user->translate($this->lang, $this->langdir, 'phppublisher.ini');
	}

	//--------------------------------------------
	/**
	 * Setup
	 *
	 * @access public
	 */
	//--------------------------------------------
	function setup() {
		$continue = true;
		if($this->user->is_admin()) {

			// handle folder permissions
			if(!$this->file->is_writeable($GLOBALS['settings']['config']['basedir'])) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'settings';
				$_REQUEST['config_msg'] = 'Root folder is not writeable. Please contact your administrator.';
				$this->action = 'config';
				$continue = false;
			}
			else if(!$this->file->exists($this->PROFILESDIR)) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'settings';
				$_REQUEST['config_msg'] = 'Profiles folder not found. Please contact your administrator.';
				$this->action = 'config';
				$continue = false;
			}
			else if(!$this->file->is_writeable($this->PROFILESDIR)) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'settings';
				$_REQUEST['config_msg'] = 'Profiles folder is not writeable. Please contact your administrator.';
				$this->action = 'config';
				$continue = false;
			}

			// handle folders
			if(
				!isset($GLOBALS['settings']['folders']['login']) &&
				$continue === true
			) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'settings';
				$_REQUEST['config_msg'] = 'Please configure folders';
				$this->action = 'config';
				$continue = false;
			}
			else if(
				$this->file->exists($GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders']['login']) === false || 
				$this->file->exists($GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders']['css']) === false || 
				$this->file->exists($GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders']['js']) === false
			) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'settings';
				$_REQUEST['config_msg'] = 'Please configure folders';
				$this->action = 'config';
				$continue = false;
			}

			// handle user settings
			if(
				isset($GLOBALS['settings']['config']['users']) &&
				$continue === true
			) {
				if(!isset($GLOBALS['settings']['user'])) {
					if($this->response->html->request()->get('usersettings[cancel]') !== '') {
						$error = $this->__unset('users');
						unset($_REQUEST['usersettings']['cancel']);
						unset($_REQUEST['settings_action']);
						$_REQUEST['config_msg'] = $error;
					} else {
						$_REQUEST[$this->actions_name] = 'config';
						$_REQUEST['config_action'] = 'settings';
						$_REQUEST['settings_action'] = 'user';
						$_REQUEST['config_msg'] = 'Please configure user settings';
						$this->action = 'config';
						$continue = false;
					}
				}
				else if($this->user instanceof phppublisher_user) {
					if($this->response->html->request()->get('usersettings[cancel]')) {
						unset($_REQUEST['usersettings']['cancel']);
					}
					$_REQUEST[$this->actions_name] = 'config';
					$_REQUEST['config_action'] = 'users';
					$this->action = 'config';
					$continue = false;
				}
			}

			// handle db settings
			if(
				isset($GLOBALS['settings']['config']['query']) &&
				!isset($GLOBALS['settings']['query']) &&
				$continue === true
			) {
				if($this->response->html->request()->get('querysettings[cancel]') !== '') {
					$error = $this->__unset('query');
					unset($_REQUEST['querysettings']['cancel']);
					unset($_REQUEST['settings_action']);
					$_REQUEST['config_msg'] = $error;
				} else {
					$_REQUEST[$this->actions_name] = 'config';
					$_REQUEST['config_action'] = 'settings';
					$_REQUEST['settings_action'] = 'query';
					$_REQUEST['config_msg'] = 'Please configure database settings';
					$this->action = 'config';
					$continue = false;
				}
			}

			// handle smtp settings
			if(
				isset($GLOBALS['settings']['config']['smtp']) &&
				!isset($GLOBALS['settings']['smtp']) &&
				$continue === true
			) {
				if($this->response->html->request()->get('smtpsettings[cancel]') !== '') {
					$error = $this->__unset('smtp');
					unset($_REQUEST['smtpsettings']['cancel']);
					unset($_REQUEST['settings_action']);
					$_REQUEST['config_msg'] = $error;
				} else {
					$_REQUEST[$this->actions_name] = 'config';
					$_REQUEST['config_action'] = 'settings';
					$_REQUEST['settings_action'] = 'smtp';
					$_REQUEST['config_msg'] = 'Please configure smtp settings';
					$this->action = 'config';
					$continue = false;
				}
			}

			// make sure at least one plugin is started
			if(
				!isset($this->plugins[0]) &&
				$continue === true &&
				$this->response->html->request()->get('config_action') !== 'settings' &&
				$this->response->html->request()->get('config_action') !== 'plugins'
			) {
				$_REQUEST[$this->actions_name] = 'config';
				$_REQUEST['config_action'] = 'plugins';
				$_REQUEST['config_msg'] = 'Please start a plugin';
				$this->action = 'config';
				$continue = false;
			}

			// handle templates folder
			if(
				$this->file->exists($this->PROFILESDIR.'templates/') &&
				!$this->file->exists($this->PROFILESDIR.'templates/phppublisher.menu.html') &&
				$continue === true
			) {
				$files = $this->file->get_files(CLASSDIR.'templates/');
				foreach($files as $file) {
					if(strpos($file['name'], '.config.') === false) {
						$error = $this->file->copy($file['path'], $this->PROFILESDIR.'templates/'.$file['name']);
					}
				}
				$continue = true;
			}

			// handle lang folder
			if(
				$this->file->exists($this->PROFILESDIR.'lang/') &&
				!$this->file->exists($this->PROFILESDIR.'templates/de.htmlobjects.ini') &&
				$continue === true
			) {
				$files = $this->file->get_files(CLASSDIR.'lang/');
				foreach($files as $file) {
					$error = $this->file->copy($file['path'], $this->PROFILESDIR.'lang/'.$file['name']);
				}
				$continue = true;
			}
		}
		return $continue;
	}

	//--------------------------------------------
	/**
	 * Unset config settings
	 *
	 * @access private
	 */
	//--------------------------------------------
	function __unset($config) {
		$ini = $this->file->get_ini( $this->settings );
		unset($ini['config'][$config]);
		unset($GLOBALS['settings']['config'][$config]);
		$error = $this->file->make_ini( $this->settings, $ini );
		return $error;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$s = $GLOBALS['settings'];

		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}

		// handle user
		if(!in_array('cms', $this->plugins)) {
			if(isset($s['config']['users'])) {
				$user = $this->user->get();
				if( !isset($user) ) {
					$this->action = 'login';
				}
			}
		} 

		// handle template
		if($this->file->exists($this->tpldir.'/index.html')) {
			$t = $this->response->html->template($this->tpldir.'/index.html');
		} else {
			$t = $this->response->html->template(CLASSDIR.'templates/index.html');
		}
		$t->add('', 'script');
		$t->add($this->response->html->thisfile, 'thisfile');

		// handle cms plugin
		if(in_array('cms', $this->plugins)) {
			$ini = $this->file->get_ini( $this->PROFILESDIR.'cms.ini' );
			require_once(CLASSDIR.'/plugins/cms/page.class.php');
			$page = new page($GLOBALS['settings']['config']['basedir'].$ini['folders']['datadir'], $this->response);
			$page->type = 'url';
			$page->extension = 'html';
			$page->include_content = true;
			$page->baseurl = $s['config']['baseurl'];
			if(isset($ini['config']['title_delimiter'])) {
				$page->title_delimiter = $ini['config']['title_delimiter'];
			}
			$t = $page->get_template();
			$t->add('', 'plugins');
			$t->add('', 'addons');
			$t->add('', 'login');
			$t->add('', 'label');
			$t->add($s['config']['baseurl'].$s['folders']['css'], 'cssurl');
			$t->add($url = $s['config']['baseurl'].$s['folders']['js'], 'jsurl');
			$t->add($url = $s['config']['baseurl'].$s['folders']['images'], 'imgurl');
		}

		$continue = $this->setup();
		$this->response->params[$this->actions_name] = $this->action;

		$content = array();
		switch( $this->action ) {
			case 'plugin':
				$content = $this->plugin();
			break;
			case 'addon':
				$content = $this->addon();
			break;
			case 'login':
				$content = $this->login();
			break;
			case 'logout':
				$content = $this->logout();
			break;
			case 'account':
				$content = $this->account();
			break;
			case 'config':
				if($this->user->is_admin()) {
					$content = $this->config();
				} else {
					$content = $this->plugin();
				}
			break;
			default:
				if(in_array('cms', $this->plugins)) {
					$content = $t->get_elements('content');
				} else {
					$content = $this->plugin();
				}
			break;
		}

		// handle style
		if(isset($content->__style) && $content->__style !== '') {
			$style = $t->get_elements('style').'<style type="text/css">'.$content->__style.'</style>';
			$t->add($style, 'style');
		}

		// handle script
		if(isset($content->__script) && $content->__script !== '') {
			$js = $t->get_elements('script').'<script language="JavaScript" type="text/javascript">'.$content->__script.'</script>';
			$t->add($js, 'script');
		}

		// handle title
		if(isset($content->__title) && $content->__title !== '') {
			if(isset($s['config']['title']) && $s['config']['title'] !== '') {
				$t->add($s['config']['title'].' / '.$content->__title, 'title');
			} else {
				$t->add($content->__title, 'title');
			}
		} else {
			if(isset($s['config']['title']) && $s['config']['title'] !== '') {
				$t->add($s['config']['title'], 'title');
			} else {
				$t->add('', 'title');
			}
		}

		// handle label
		if(isset($s['config']['title']) && $s['config']['title'] !== '') {
			$t->add($s['config']['title'], 'label');
		} else {
			$t->add($this->title, 'label');
		}

		// handle login dir
		if(isset($s['folders']['login']) && $continue === true) {
			$login = $s['folders']['login'];
			$base  = $s['config']['basedir'];
			if(
				!in_array('cms', $this->plugins) &&
				$base.$login !== $this->response->html->thisdir
			) {
				$this->response->redirect($s['config']['baseurl'].$login);
			} 
			else if ($base.$login === $this->response->html->thisdir) {
				$t = $this->__logedin($t, $s);
				// check content is cms content
				if(isset($page) && is_string($content)) {
					// check authentication
					$auth = true;
					if(isset($s['config']['users'])) {
						$auth = false;
						$user = $this->user->get();
						if( isset($user) ) {
							$auth = true;
						}
					}
					if($auth === true) {
						$cssurl = $s['config']['baseurl'].$s['folders']['css'];
						$cssurl = '<link rel="stylesheet" type="text/css" href="'.$cssurl.'cms.css">';
						$t->add($t->get_elements('style').$cssurl, 'style');
						$t->add($page->get_template()->get_elements('title'), 'title');
						$params  = 'index.php?index_action=plugin&amp;index_action_plugin=cms&amp;cms_action=editor&amp;dir='.$page->dir;
						$params .= '&amp;id='.$page->id;
						$params .= '&amp;lang='.$page->lang;
						$content = '<a href="'.$params.'" id="CmsEdit" class="edit" title="'.$this->lang['edit_page'].'">&#160;</a><div id="previewpanel">'.$content.'</div>';
					}
				} else {
					// check authentication
					if(isset($s['config']['users'])) {
						$user = $this->user->get();
						if( !isset($user) ) {
							$content = $this->login();
						}
					}
				}
				// make sure content is last in
				// array to avoid double parsing
				$t->remove('content');
				$t->add($content, 'content');
			}
		} else {
			$t = $this->__logedin($t, $s);
			// make sure content is last in
			// array to avoid double parsing
			$t->remove('content');
			$t->add($content, 'content');
		}
		return $t;
	}

	//--------------------------------------------
	/**
	 * Config
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function config() {
		require_once(CLASSDIR.'lib/phppublisher/phppublisher.config.controller.class.php');
		$controller = new phppublisher_config_controller($this);
		$controller->actions_name = 'config_action';
		$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'phppublisher.config.ini');
		$data = $controller->action();
		return $data;
	}

	//--------------------------------------------
	/**
	 * Login
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function login() {
		require_once(CLASSDIR.'lib/user/user.login.class.php');
		## TODO profilesdir?
		$controller = new user_login($this->response, $this->user);
		if($this->file->exists($this->tpldir.'/user.login.html')) {
			$controller->tpldir = $this->tpldir;
		} else {
			$controller->tpldir = CLASSDIR.'/templates/';
		}
		$controller->lang   = $this->user->translate($controller->lang, $this->langdir, 'user.login.ini');
		$data = $controller->action();
		$data->__title = $this->lang['link_login'];
		return $data;
	}

	//--------------------------------------------
	/**
	 * Logout
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function logout() {
		require_once(CLASSDIR.'lib/user/user.logout.class.php');
		## TODO profilesdir?
		$controller = new user_logout($this->response, $this->file, $this->user);
		if($this->file->exists($this->tpldir.'user.logout.html')) {
			$controller->tpldir = $this->tpldir;
		} else {
			$controller->tpldir = CLASSDIR.'/templates/';
		}
		$controller->lang   = $this->user->translate($controller->lang, $this->langdir, 'user.logout.ini');
		$data = $controller->action();
		$data->__title = $this->lang['link_logout'];
		return $data;
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function account() {
		require_once(CLASSDIR.'lib/user/user.controller.class.php');
		## TODO profilesdir?
		$controller = new user_controller($this->file, $this->response, $this->user);
		if($this->file->exists($this->tpldir.'user.account.html')) {
			$controller->tpldir = $this->tpldir;
		} else {
			$controller->tpldir = CLASSDIR.'/templates';
		}
		$controller->langdir = $this->langdir;
		$controller->actions_name = $this->actions_name;
		$controller->message_param = 'account_msg';
		$controller->lang   = $this->user->translate($controller->lang, $this->langdir, 'user.controller.ini');
		$data = $controller->action('account');

		$c['label']   = '&#160;';
		$c['value']   = $data;
		$c['hidden']  = true;
		$c['target']  = $this->response->html->thisfile;
		$c['request'] = $this->response->get_array($this->actions_name, 'select' );
		$c['onclick'] = false;
		$c['active']  = true;

		$tab = $this->response->html->tabmenu('account_tab');
		$tab->boxcss = 'tab-content noborder';
		$tab->message_param = 'account_msg';
		$tab->__title = $this->lang['link_account'];
		$tab->add(array($c));

		return $tab;
	}

	//--------------------------------------------
	/**
	 * Plugin Loader
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function plugin() {
		$action = $this->response->html->request()->get($this->actions_name.'_plugin');
		if($action === '' && isset($this->plugins[0])) {
			$action = $this->plugins[0];
			$_REQUEST[$this->actions_name.'_plugin'] = $this->plugins[0];
		}
## TODO plugin not found page

		$ini = $this->plugins;
		$return = '';
		if(isset($ini)) {
			foreach($ini as $key => $value) {
				if(file_exists(CLASSDIR.'plugins/'.$value.'/class/'.$value.'.controller.class.php')) {
					$data = '';
					$params   = $this->response->get_array($this->actions_name, 'plugin' );
					$params[$this->actions_name.'_plugin'] = $value;
					if( $action === $value ) {
						require_once(CLASSDIR.'plugins/'.$value.'/class/'.$value.'.controller.class.php');
						$response = $this->response->response();
						$response->add($this->actions_name.'_plugin', $value);
						// handle folder name - allow .
						$classname = str_replace('.','_',$value);
						$class = $classname.'_controller';
						## TODO profilesdir?
						$controller = new $class($this->file, $response, $this->db, $this->user);
						$controller->actions_name  = $classname.'_action';
						$controller->message_param = $classname.'_msg';
						$controller->prefix_tab    = $classname.'_tab';
						// Templates folder
						$controller->tpldir = CLASSDIR.'plugins/'.$value.'/templates/';
						if($this->file->exists($this->PROFILESDIR.'/templates/'.$value)) {
							$controller->tpldir = $this->PROFILESDIR.'/templates/'.$value;
						}
						$controller->langdir = CLASSDIR.'plugins/'.$value.'/lang/';
						#if($this->file->exists($this->PROFILESDIR.'/lang/')) {
						#	$controller->langdir = $this->PROFILESDIR.'/lang/';
						#}
						$return = $controller->action();
						$return->__title = ucfirst($value);
						#if($this->file->exists(CLASSDIR.'plugins/'.$value.'/'.$value.'.css')) {
							#$return->__style = $this->file->get_contents(CLASSDIR.'plugins/'.$value.'/'.$value.'.css');
						#}
						break;
					}
				}
			}
		}
		return $return;
	}

	//--------------------------------------------
	/**
	 * Addon Loader
	 *
	 * @access public
	 * @return htmlobject_template
	 *
	 * TODO load addons from CLASSDIR/addons ?
	 */
	//--------------------------------------------
	function addon() {
		$action = $this->response->html->request()->get($this->actions_name.'_addon');
		$return = '';
		foreach($this->addons as $key => $value) {
			if(isset($value['path'])) {
				$name = $value['name'];
				if(file_exists($value['path'].'/class/'.$name.'.controller.class.php')) {
					$data = '';
					$params   = $this->response->get_array($this->actions_name, 'addon' );
					$params[$this->actions_name.'_addon'] = $name;
					if( $action === $name ) {
						require_once($value['path'].'/class/'.$name.'.controller.class.php');
						$response = $this->response->response();
						$response->add($this->actions_name.'_addon', $name);
						$class = $name.'_controller';
						## TODO profilesdir?
						$controller = new $class($this->file, $response, $this->db, $this->user);
						$controller->actions_name  = $name.'_action';
						$controller->message_param = $name.'_msg';
						$controller->prefix_tab    = $name.'_tab';
						$controller->tpldir = $value['path'].'/'.$name.'/templates/';
						if($this->file->exists($value['path'].'/templates/')) {
							$controller->tpldir = $value['path'].'/templates/';
						}
						$controller->langdir = $value['path'].'/'.$name.'/lang/';
						if($this->file->exists($value['path'].'/lang/')) {
							$controller->langdir = $value['path'].'/lang/';
						}
						$return = $controller->action();
## TODO style
						if($this->file->exists($value['path'].'/setup/css/'.$name.'.css')) {
							$return->__style = $this->file->get_contents($value['path'].'/setup/css/'.$name.'.css');
						}
						if($this->file->exists($value['path'].'/setup/js/'.$name.'.js')) {
							$return->__script = $this->file->get_contents($value['path'].'/setup/js/'.$name.'.js');
						}
						$return->__title = ucfirst($name);
						break;
					}
				}
			}
		}
		return $return;
	}

	//--------------------------------------------
	/**
	 * Menus
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function menu() {
		$ini = $this->plugins;
		$return = '';
		if(isset($ini) && is_array($ini)) {
			$user = $this->user->get();
			if(isset($user)) {
				foreach($ini as $key => $value) {
					if(file_exists(CLASSDIR.'plugins/'.$value.'/'.$value.'.init.class.php')) {
						require_once(CLASSDIR.'plugins/'.$value.'/'.$value.'.init.class.php');
						$response = $this->response->response();
						$response->add($this->actions_name, 'plugin');
						$response->add($this->actions_name.'_plugin', $value);
						// handle folder name - allow .
						$classname = str_replace('.','_',$value);
						$class = $classname.'_init';
						## TODO profilesdir?
						$controller = new $class($response, $this->file, $this->user, $this->db);
						$controller->actions_name = $classname.'_action';
						$controller->PROFILESDIR = $this->PROFILESDIR;
						// handle templates
						if($this->file->exists($this->PROFILESDIR.'/templates/'.$value)) {
							$controller->tpldir = $this->PROFILESDIR.'/templates/'.$value;
						} else {
							$controller->tpldir = CLASSDIR.'plugins/'.$value.'/templates/';
						}
						#if($this->file->exists($this->PROFILESDIR.'/lang/')) {
						#	$controller->lang = $this->user->translate($controller->lang, $this->PROFILESDIR.'/lang/', $value.'.init.ini');
						#	$controller->langdir = $this->PROFILESDIR.'/lang/';
						#} else {
							$controller->lang = $this->user->translate($controller->lang, CLASSDIR.'/plugins/'.$value.'/lang', $value.'.init.ini');
							$controller->langdir = CLASSDIR.'plugins/'.$value.'/lang/';
						#}
						if(method_exists($controller, 'menu')) {
							$return .= $controller->menu();
						}
					}
				}
			}
		}

		if(isset($user)) {
			foreach($this->addons as $key => $value) {
				if(isset($value['path'])) {
					$file = $value['path'].'/'.$value['name'].'.init.class.php';
					if(file_exists($file)) {
						require_once($file);
						$response = $this->response->response();
						$response->add($this->actions_name, 'addon');
						$response->add($this->actions_name.'_addon', $value['name']);
						$class = $value['name'].'_init';
						## TODO profilesdir?
						$controller = new $class($response, $this->file, $this->user, $this->db);
						$controller->actions_name = $value['name'].'_action';
						#if($this->file->exists($this->PROFILESDIR.'/templates/')) {
						#	$controller->tpldir = $this->PROFILESDIR.'/templates/';
						#} else {
							$controller->tpldir = $value['path'].'/templates/';
						#}
						#if($this->file->exists($this->PROFILESDIR.'/lang/')) {
						#	$controller->lang = $this->user->translate($controller->lang, $this->PROFILESDIR.'/lang/', $value['name'].'.init.ini');
						#	$controller->langdir = $this->PROFILESDIR.'/lang/';
						#} else {
							$controller->lang = $this->user->translate($controller->lang,  $value['path'].'/lang/', $value['name'].'.init.ini');
							$controller->langdir =  $value['path'].'/lang/';
						#}
						if(method_exists($controller, 'menu')) {
							$return .= $controller->menu();
						}
					}
				}
			}
		}

		if($return === '') {
			if(!in_array('cms', $this->plugins)) {
				$return = '&#160;';
			}
		}
		else if($return !== '') {
			if(in_array('cms', $this->plugins)) {
				$t = $this->response->html->template(CLASSDIR.'templates/phppublisher.menu.html');
				$t->add($return, 'content');
				$t->add($this->lang['toggle_menu'], 'toggle_menu');
				$return = $t->get_string();
			} else {
				$return = '<div id="plugins">'.$return.'</div>';
			}
		}

		return $return;
	}

	//--------------------------------------------
	/**
	 * Menus when loged in
	 *
	 * @access private
	 * @param htmlobject_template $t
	 * @param array $s
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function __logedin( $t, $s) {

		$user     = $this->user->get();
		$response = $this->response;
		$account  = '';
		$config   = '';
		$logout   = '';
		
		if(
			isset($s['config']['users']) &&
			isset($s['user']) && 
			isset($user)
		) {
			$a        = $response->html->a();
			$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'account', '?', true );
			$a->label = '<span>'.$this->lang['link_account'].': '.$user['login'].'</span>';
			$a->css   = "account";
			$a->title = $this->lang['link_account'].': '.$user['login'];
			$account  = '<li>'.$a->get_string().'</li>';
			$logout = '';
		} 
		else if(isset($s['config']['users'])) {
			$url      = $response->html->thisfile.$response->get_string($this->actions_name, 'login', '?', true );
			$a        = $response->html->a();
			$a->href  = $url;
			$a->label = '<span>'.$this->lang['link_login'].'</span>';
			$a->css   = "login";
			$logout   = '<li>'.$a->get_string().'</li>';
		}

		if($this->user->is_admin()){
			$a        = $response->html->a();
			$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'config', '?', true );
			$a->label = '<span>'.$this->lang['link_config'].'</span>';
			$a->css   = "settings";
			$a->title = $this->lang['link_config'];
			$config = '<li>'.$a->get_string().'</li>';
			if(isset($login)) {
				if($base.$login !== $this->response->html->thisdir) {
					$config = '';
				}
			}
		}

		$div = $this->response->html->customtag('ul');
		$div->id = 'LoginButtons';
		$div->css ='nav navbar-nav navbar-right';
		$div->add($config);
		$div->add($account);
		$div->add($logout);

		$t->add($this->menu(), 'plugins');
		$t->add($div, 'login');

		if(!in_array('cms', $this->plugins)) {
			$a        = $response->html->a();
			$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, '', '?', true );
			$a->label = $this->lang['link_home'];
			$a->css   = 'home';
			$t->add($a, 'home');
		}

		// Style
		if(
			!isset($s['folders']['css']) || 
			$this->file->exists($s['config']['basedir'].$s['folders']['css'].'phppublisher.css') === false
		) {
			$style  = $t->get_elements('style');
			$style .= '<style type="text/css">'.$this->file->get_contents(CLASSDIR.'setup/css/bootstrap4.css').'</style>'."\n";
			$style .= '<style type="text/css">'.$this->file->get_contents(CLASSDIR.'setup/css/phppublisher.css').'</style>'."\n";
			$t->add($style, 'style');
		} else {
			$url   = $s['config']['baseurl'].$s['folders']['css'];
			$path  = $s['config']['basedir'].$s['folders']['css'];
			$style = $t->get_elements('style');
			if(!isset($style)) {
				$style = '';
			}
			$plugin = $this->response->html->request()->get($this->actions_name.'_plugin');
			if($plugin !== '') {
				if($this->file->exists($path.$plugin.'.css')) {
					$style .= '<link rel="stylesheet" type="text/css" href="'.$url.$plugin.'.css">'."\n";
				}
			}
			if(in_array('cms', $this->plugins)) {
				$style = $t->get_elements('style').$style;
				$t->add($style, 'style');

			} else {
				$t->add($style, 'style');
			}
		}

		// Script
		if(
			!isset($s['folders']['js']) || 
			$this->file->exists($s['config']['basedir'].$s['folders']['js'].'phppublisher.js') === false
		) {
			$script  = $t->get_elements('script');
			$script .= '<script type="text/javascript">'.$this->file->get_contents(CLASSDIR.'setup/js/jquery.min.js').'</script>'."\n";
			$script .= '<script type="text/javascript">'.$this->file->get_contents(CLASSDIR.'setup/js/phppublisher.js').'</script>'."\n";
			$script .= '<script>$(document).ready(function (){ $(\'#Leftbar\').toggleClass(\'active\'); });</script>';
			$t->add($script, 'script');
		} else {
			$path    = $s['config']['basedir'].$s['folders']['js'];
			$url     = $s['config']['baseurl'].$s['folders']['js'];

			$script = '';
			#$script  = '<script src="'.$url.'jquery.js" type="text/javascript"></script>'."\n";
			#$script .= '<script src="'.$url.'phppublisher.js" type="text/javascript"></script>'."\n";
			#if(in_array('cms', $this->plugins)) {
				$script = $t->get_elements('script').$script;
			#}
			$plugin = $this->response->html->request()->get($this->actions_name.'_plugin');
			if($plugin !== '') {
				if($this->file->exists($path.$plugin.'.js')) {
					$script .= '<script src="'.$url.$plugin.'.js" type="text/javascript"></script>'."\n";
				}
			}
			$t->add($script, 'script');
		}

		// add baseurl
		$t->add($s['config']['baseurl'], 'baseurl');

		// add cssurl
		$cssurl = '';
		if(isset($s['folders']['css'])) {
			$cssurl = $s['config']['baseurl'].$s['folders']['css'];
		}
		$t->add($cssurl, 'cssurl');

		// add jsurl
		$jsurl = '';
		if(isset($s['folders']['js'])) {
			$jsurl = $s['config']['baseurl'].$s['folders']['js'];
		}
		$t->add($jsurl, 'jsurl');

		// add imgurl
		$imgurl = '';
		if(isset($s['folders']['images'])) {
			$imgurl = $s['config']['baseurl'].$s['folders']['images'];
		}
		$t->add($imgurl, 'imgurl');
		return $t;

	}

}
?>
