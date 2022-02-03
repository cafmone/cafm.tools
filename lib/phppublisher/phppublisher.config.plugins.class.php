<?php
/**
 * PHPPublisher config plugins
 *
 * @package phppublisher
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_config_plugins
{
/**
* path to tpldir
* @access public
* @var string
*/
var $tpldir   = '';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'server_action';
/**
* identifier_name
* @access public
* @var string
*/
var $identifier_name = 'saction';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'plugin' => 'Plugin',
	'description' => 'Description',
	'active'  => 'Active',
	'rank' => 'Rank',
	'edit'   => 'edit',
	'start'  => 'start',
	'stop'   => 'stop',
	'stopped'  => '%s has been stopped',
	'started'  => '%s has been started',
	'sorted' => 'Plugins have been sorted',
	'sort'   => 'Sort Plugins',
	'noscript' => 'Error: JavaScript must be activated for this page'
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $file
	 * @param object $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller  = $controller;
		$this->file        = $controller->file;
		$this->response    = $controller->response->response();
		$this->user        = $controller->user;
		$this->db          = $controller->db;
		$this->PROFILESDIR = $controller->PROFILESDIR;
		$this->settings    = $this->PROFILESDIR.'/plugins.ini';
		$this->ini         = $this->file->get_ini( $this->settings );
		$this->config      = $this->file->get_ini( $this->PROFILESDIR.'/settings.ini' );
		if(!isset($this->ini)) {
			$this->ini = array();
		}
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_response
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
		if($this->response->cancel()) {
			$this->action = 'select';
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
			default:
				return $this->select();
			break;
			case 'update':
				return $this->update();
			break;
			case 'sort':
				return $this->sort();
			break;
			case 'start':
			case $this->lang['start']:
				return $this->start();
			break;
			case 'stop':
			case $this->lang['stop']:
				return $this->stop();
			break;
			case 'permissions':
				return $this->permissions(true);
			break;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;
		$files    = $this->file->get_folders( CLASSDIR.'plugins' );
		$count    = count( $files );

		$head['rank']['title']      = $this->lang['rank'];
		$head['rank']['sortable']   = true;
		$head['rank']['style']   = 'width:90px;';
		$head['plugin']['title']    = $this->lang['plugin'];
		$head['plugin']['sortable'] = true;
		$head['description']['title']    = $this->lang['description'];
		$head['description']['sortable'] = false;

		$i = 0;
		$body  = array();
		foreach( $files as $k => $f ) {
			$action = '';
			$groups = '';
			$description = '&#160;';

			if($this->file->exists($f['path'].'/'.$f['name'].'.init.class.php')) {
				require_once($f['path'].'/'.$f['name'].'.init.class.php');
				$c = str_replace('.','_', $f['name']).'_init';
				$c = new $c($this->response, $this->file, $this->user, $this->db);
				$c->profilesdir = $this->PROFILESDIR;
				if(method_exists($c, 'description')) {
					$description = $c->description();
				}
			}

			if(isset($this->ini) && in_array($f['name'], $this->ini)) {
				$pos = array_keys($this->ini, $f['name']);
				$body[]	= array(
					'plugin' => $f['name'],
					'rank'   => "$pos[0]",
					'description' => $description,
				);
			} else {
				$body[]	= array(
					'plugin' => $f['name'],
					'rank'   => '&#160;',
					'description' => $description,
				);
			}
		}
		$table                      = $response->html->tablebuilder( 'st', $response->get_array($this->actions_name, 'select') );
		$table->sort                = 'rank';
		$table->css                 = 'htmlobject_table table table-bordered';
		$table->border              = 0;
		$table->id                  = 'Plugins_table';
		$table->head                = $head;
		$table->body                = $body;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = false;
		$table->autosort            = true;
		$table->identifier          = 'plugin';
		$table->identifier_name     = $this->identifier_name;
		$table->actions             = array($this->lang['start'], $this->lang['stop']);
		$table->actions_name        = $this->actions_name;
		$table->max                 = $count;

		$href = '';
		if(count($this->ini) > 1) {
			$href        = $response->html->a();
			$href->css   = 'btn btn-default';
			$href->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'sort', '?', true );
			$href->label = $this->lang['sort'];
			$href = $href->get_string();
		}

		$response->table = $table;
		$vars = array(
			'thisfile' => $response->html->thisfile,
			'sort'     => $href,
		);
		$t = $response->html->template($this->tpldir.'phppublisher.config.plugins.select.html');
		$t->add($vars);
		$t->add($this->response->get_form($this->actions_name, 'select'));
		$t->add($table, 'table');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Start
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function start() {
		$error = '';
		$plugins = $this->response->html->request()->get($this->identifier_name);
		if( $plugins !== '' ) {
			$msg = '';
			foreach($plugins as $p) {
				if($this->file->exists(CLASSDIR.'plugins/'.$p.'/'.$p.'.init.class.php')) {
					require_once(CLASSDIR.'plugins/'.$p.'/'.$p.'.init.class.php');
					$c = str_replace('.','_', $p).'_init';
					$c = new $c($this->response, $this->file, $this->user, $this->db);
					// add profilesdir
					## TODO change to PROFILESDIR in plugin?
					$c->profilesdir = $this->PROFILESDIR;
					if(method_exists($c, 'start')) {
						$error = $c->start();

					}
					if($error !== '') {
						$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param.'[error]', $error));
					} else {
						// copy files
						$error = $this->__copy_files($p);
						if($error !== '') {
							$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param.'[error]', $error));
						}
					}
				}
				if(!in_array($p, $this->ini)) {
					$this->ini[] = $p;
				}
				$msg .= sprintf($this->lang['started'], $p).'<br>';
			}
			$error = $this->file->make_ini( $this->settings, $this->ini, null );
			if($error !== '') {
				$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param.'[error]', $error));
			} else {
				$this->response->redirect(
					$this->response->get_url(
							$this->actions_name, 'select', $this->message_param, $msg
					)
				);
			}
		} else {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
		}
	}

	//--------------------------------------------
	/**
	 * Stop
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function stop() {
		$plugins = $this->response->html->request()->get($this->identifier_name);
		if( $plugins !== '' ) {
			$msg = '';
			foreach($plugins as $p) {
				if(in_array($p, $this->ini)) {
					$pos = array_keys($this->ini, $p);
					unset($this->ini[$pos[0]]);
					$msg .= sprintf($this->lang['stopped'], $p).'<br>';	
				}
			}
			$ini = array();
			foreach($this->ini as $p) {
				$ini[] = $p;
			}
			$error = $this->file->make_ini( $this->settings, $ini, null );
			if($error === '') {
				$this->response->redirect(
					$this->response->get_url(
							$this->actions_name, 'select', $this->message_param, $msg
					)
				);
			} else {
				$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
			}
		} else {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
		}
	}


	//--------------------------------------------
	/**
	 * Sort
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sort( $hidden = false ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'sort');
		$plugins  = $this->ini;

		if(count($plugins) > 1) {
			$plugin = array();
			foreach($plugins as $key => $value){
				$plugin[] = array($value, $value);
			}
			$d['select']['label']                        = '';
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['index']    = array(0, 1);
			$d['select']['object']['attrib']['id']       = 'plugin_select';
			$d['select']['object']['attrib']['name']     = 'plugins[]';
			$d['select']['object']['attrib']['options']  = $plugin;
			$d['select']['object']['attrib']['multiple'] = true;
			$d['select']['object']['attrib']['css']      = 'picklist';
	
			$form->add($d);
			$request = $form->get_request('plugins');
			if(!$form->get_errors() && $response->submit()) {
				$error = $this->file->make_ini($this->settings, $request, null);
				if($error === '') {
					$msg = $this->lang['sorted'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $msg));

				} else {
					$_REQUEST[$this->message_param] = $error;
				}
			}
		} else {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
		}

		$data['headline'] = $this->lang['sort'];
		$data['noscript'] = $this->lang['noscript'];
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'/phppublisher.config.plugins.sort.html');
		$t->add($vars);
		$t->add($form);
		$t->add('plugin_select', 'id');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Permissions
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function permissions( $visible = false ) {
		$data = '';
		if( $visible === true ) {
			require_once(CLASSDIR.'/phppublisher.config.permissions.class.php');
			$controller = new phppublisher_config_permissions( $this->response, $this->file, $this->user, $this->db);
			$controller->settings     = $this->settings;
			$controller->actions_name = 'permissions_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			#$controller->lang = $this->user->translate($controller->lang, $this->langdir, 'user.permissions.ini');
			$data = $controller->action();
		}

		return $data;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_response( $plugin, $group, $mode ) {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, $mode);

		$d['param_plug']['static']                    = true;
		$d['param_plug']['object']['type']            = 'htmlobject_input';
		$d['param_plug']['object']['attrib']['name']  = 'plugin';
		$d['param_plug']['object']['attrib']['type']  = 'hidden';
		$d['param_plug']['object']['attrib']['value'] = $plugin;

		$d['param_group']['static']                    = true;
		$d['param_group']['object']['type']            = 'htmlobject_input';
		$d['param_group']['object']['attrib']['name']  = 'group';
		$d['param_group']['object']['attrib']['type']  = 'hidden';
		$d['param_group']['object']['attrib']['value'] = $group;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Copy plugin files
	 *
	 * @access protected
	 * @param string $plugin
	 * @return string
	 */
	//--------------------------------------------
	function __copy_files($plugin) {
		$errors = array();
		$folders = array('css','images','js');
		foreach($folders as $v) {
			$target = $GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders'][$v];
			$files  = $this->file->get_files(CLASSDIR.'plugins/'.$plugin.'/setup/'.$v.'/');
			foreach($files as $file) {
				if(!$this->file->exists($target.'/'.$file['name'])) {
					if(isset($this->config['config']['link_virtual'])) {
						$error = $this->file->symlink( $file['path'], $target.'/'.$file['name']);
					} else {
						$error = $this->file->copy($file['path'], $target.'/'.$file['name']);
					}
					if($error !== '') {
						$errors[] = $error;
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

}
?>
