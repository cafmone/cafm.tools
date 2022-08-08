<?php
/**
 * phppublisher_config_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'config_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'config_msg';
/**
* prefix for tab menu
* @access public
* @var string
*/
var $prefix_tab = 'config_tab';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	"tab_settings" => "Settings",
	"tab_users" => "Users",
	"tab_plugins" => "Plugins",
	"setup_msg" => "Please setup first"
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller  = $controller;
		$this->db          = $controller->db;
		$this->file        = $controller->file;
		$this->response    = $controller->response->response();
		$this->user        = $controller->user;
		$this->plugins     = $this->file->get_ini( $controller->PROFILESDIR.'/plugins.ini' );
		$this->langdir     = CLASSDIR.'lang/';
		$this->PROFILESDIR = $controller->PROFILESDIR;
		$this->LIBDIR      = $controller->LIBDIR;

		$this->title       = $controller->title;

		if($this->file->exists($this->PROFILESDIR.'lang')) {
			$this->langdir = $this->PROFILESDIR.'lang/';
		}
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
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->action === '') {
			if(isset($this->plugins[0])) {
				$this->action = 'loader';
				$_REQUEST['config_action_loader'] = $this->plugins[0];
			} else {
				$this->action = 'plugins';
			}
		}

		// SETUP
		$settings = '';
		$plugins  = '';

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		$loaders = array();
		switch( $this->action ) {
			case '':
			default:
			case 'loader':
				if(isset($settings)) {
					$content[] = $this->settings(true);
				}
				if(isset($plugins)) {
					$content[] = $this->plugins(true);
				}
				if(isset($GLOBALS['settings']['config']['users'])) {
					$content[] = $this->users(true);
				}
				$loaders = $this->loader();
			break;
			case 'users':
				if(isset($settings)) {
					$content[] = $this->settings(true);
				}
				if(isset($plugins)) {
					$content[] = $this->plugins(true);
				}
				$content[] = $this->users();
				$loaders = $this->loader(true);
			break;
			case 'settings':
				if(isset($settings)) {
					$content[] = $this->settings();
				}
				if(isset($plugins)) {
					$content[] = $this->plugins(true);
				}
				if(isset($GLOBALS['settings']['config']['users'])) {
					$content[] = $this->users(true);
				}
				$loaders = $this->loader(true);
			break;
			case 'plugins':
				if(isset($settings)) {
					$content[] = $this->settings(true);
				}
				if(isset($plugins)) {
					$content[] = $this->plugins();
				}
				if(isset($GLOBALS['settings']['config']['users'])) {
					$content[] = $this->users(true);
				}
				$loaders = $this->loader(true);
			break;
		};
		foreach($loaders as $loader) {
			$content[] = $loader;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);

		$div = $this->response->html->div();
		$div->id = 'Publisher';
		$div->add($tab);
		if(isset($this->__style)) {
			$div->__style = $this->__style;
		}
		if(isset($this->__title)) {
			$div->__title = $this->__title;
		}
		return $div;
	}

	//--------------------------------------------
	/**
	 * Settings
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function settings( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once($this->LIBDIR.'phppublisher.config.settings.class.php');
			$controller = new phppublisher_config_settings($this);
			$controller->actions_name = 'settings_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'templates/';
			if($this->file->exists($this->PROFILESDIR.'/lang/')) {
				$controller->langdir = $this->PROFILESDIR.'/lang/';
			}
			$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'phppublisher.config.settings.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_settings'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'settings' );
		$content['onclick'] = false;
		if($this->action === 'settings'){
			$this->__title = $this->title.' / '.$this->lang['tab_settings'];
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Plugins
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function plugins( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once($this->LIBDIR.'phppublisher.config.plugins.class.php');
			$controller = new phppublisher_config_plugins($this);
			$controller->actions_name = 'plugins_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = CLASSDIR.'templates/';
			if($this->file->exists($this->PROFILESDIR.'/lang/')) {
				$controller->langdir = $this->PROFILESDIR.'/lang/';
			}
			$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'phppublisher.config.plugins.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_plugins'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'plugins' );
		$content['onclick'] = false;
		if($this->action === 'plugins'){
			$this->__title = $this->title.' / '.$this->lang['tab_plugins'];
			$content['active']  = true;
		}
		return $content;
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
	function users( $hidden = false ) {
		$data = '';
		if( $hidden === false ) {
			require_once(CLASSDIR.'lib/user/user.controller.class.php');
			require_once($this->LIBDIR.'phppublisher.config.users.class.php');
			$controller = new phppublisher_config_users($this->file, $this->response->response(), $this->user);
			$controller->actions_name = 'user_action';
			$controller->message_param = 'user_msg';
			$controller->tpldir = CLASSDIR.'lib/user/templates/';
			$controller->langdir = CLASSDIR.'lib/user/lang/';
			if($this->file->exists($this->PROFILESDIR.'/lang/')) {
				$controller->langdir = $this->PROFILESDIR.'/lang/';
			}
			$controller->lang = $this->user->translate($controller->lang, $controller->langdir, 'user.controller.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_users'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'users' );
		$content['onclick'] = false;
		if($this->action === 'users'){
			$this->__title = $this->title.' / '.$this->lang['tab_users'];
			$content['active']  = true;
		}
		return $content;
	}

	//-------------------------------------------------
	//   LOADER
	//------------------------------------------------- 
	function loader() {
		$ini      = $this->plugins;
		$return   = array();
		if(isset($ini)) {
			foreach($ini as $key => $value) {
				if(file_exists(CLASSDIR.'plugins/'.$value.'/class/'.$value.'.config.controller.class.php')) {
					$data = '';
					$params   = $this->response->get_array($this->actions_name, 'loader' );
					$params[$this->actions_name.'_loader'] = $value;
					$action = $this->response->html->request()->get($this->actions_name.'_loader');
					if( $action === $value ) {
						require_once(CLASSDIR.'plugins/'.$value.'/class/'.$value.'.config.controller.class.php');
						$response = $this->response->response();
						$response->add($this->actions_name.'_loader', $value);
						// handle folder name - allow .
						$classname = str_replace('.','_',$value);
						$class = $classname.'_config_controller';
						$controller = new $class($this->file, $response, $this->db, $this->user);
						$controller->actions_name  = $classname.'_action';
						$controller->message_param = $classname.'_msg';
						$controller->prefix_tab    = $classname.'_tab';
						$controller->tpldir = CLASSDIR.'plugins/'.$value.'/templates/';
						$controller->langdir = CLASSDIR.'plugins/'.$value.'/lang/';
						if($this->file->exists($this->PROFILESDIR.'/lang/')) {
							$controller->langdir = $this->PROFILESDIR.'/lang/';
						}
						$controller->lang = $this->user->translate($controller->lang, $controller->langdir, $value.'.config.controller.ini');
						$data = $controller->action();
					}
					$content['label']   = ucfirst($value);
					$content['value']   = $data;
					$content['target']  = $this->response->html->thisfile;
					$content['request'] = $params;
					$content['onclick'] = false;
					if($action === $value){
						$content['active']  = true;
						$this->__title = $this->title.' / '.ucfirst($value);
						if($this->file->exists(CLASSDIR.'plugins/'.$value.'/'.$value.'.config.css')) {
							$this->__style = $this->file->get_contents(CLASSDIR.'plugins/'.$value.'/'.$value.'.config.css');
						}
					}
					$return[] = $content;
				}
			}
		}
		return $return;
	}

}
?>
