<?php
/**
 * PHPPublisher config permissions
 *
 * @package phppublisher
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_config_permissions
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
var $actions_name = 'permissions_action';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'plugin' => 'Plugin',
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
	function __construct( $response, $file, $user, $db ) {
		$this->file     = $file;
		$this->query    = $db;
		$this->response = $response->html->response();
		$this->plugins  = $this->file->get_ini( PROFILESDIR.'/plugins.ini' );
		$this->user     = $user;
		if(!isset($this->plugins)) {
			$this->plugins = array();
		}
		$plugin = $this->response->html->request()->get('plugin');
		if($plugin !== '') {
			$this->response->add('plugin',$plugin);
			$this->plugin = $plugin;
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


/*
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
			#case 'select':
			#default:
			#	return $this->select();
			#break;
			case 'edit':
*/

				$response = $this->edit();
				#if(isset($response->error)) {
				#	$_REQUEST[$this->message_param] = $response->error;
				#}
				#if(isset($response->msg) || !isset($response->form)) {
				#	$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
				#}
				#$vars = array(
				#	'label' => sprintf('Edit permissions for plugin %s on group %s',$response->plugin, $response->group),
				#	'thisfile' => $response->html->thisfile,
				#);
				$t = $this->response->html->template(CLASSDIR.'/templates/phppublisher.config.permissions.edit.html');


				#$t->add($vars);
				#$t->add($response->form);
				$t->add('Permissions','label');
				$t->group_elements(array('param_' => 'form'));
				return $t;


/*
			break;
		}
		return $content;
*/
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------

/*
	function select() {
		$response = $this->response;

		$head['group']['title']     = 'Group';
		$head['group']['sortable']  = true;
		$head['plugin']['title']    = $this->lang['plugin'];
		$head['plugin']['sortable'] = true;
		$head['permission']['title']     = 'Permissions';
		$head['permission']['sortable']  = false;
		$head['func']['title']     = '&#160;';
		$head['func']['sortable']  = false;

		$body   = array();
		$groups = $this->query->select( 'groups', array('group') );
		foreach( $this->plugins as $k => $v ) {
			if($this->file->exists(CLASSDIR.'plugins/'.$v.'/'.$v.'.permissions.class.php')) {
				$permissions = $this->query->select('groups2permissions', '*', array('plugin', $v));
				foreach($groups as $group) {
					$group = $group['group'];
					if($group !== 'Admin') {
						$permission = '&#160;';
						if(is_array($permissions)) {
							foreach($permissions as $p) {
								if($p['group'] === $group) {
									$permission = $p['permission'];
									break;
								}
							}
						}
						$a = $response->html->a();
						$a->href  = $response->get_url($this->actions_name, 'edit', 'plugin', $v).'&group='.$group;
						$a->label = 'edit';
						$a->title = 'edit';
						$a->css   = 'edit';
						$body[]	= array(
							'group' => $group,
							'plugin' => $v,
							'permission' => $permission,
							'func' => $a->get_string(),
						);
					}
				}
			}
		}
		$table                      = $response->html->tablebuilder( 'up', $response->get_array($this->actions_name, 'select') );
		$table->sort                = 'group';
		$table->css                 = 'htmlobject_table table table-bordered';
		$table->border              = 0;
		$table->id                  = 'permissions_table';
		$table->head                = $head;
		$table->body                = $body;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = true;
		$table->max                 = count($body);

		$response->table = $table;
		$vars = array(
			'thisfile' => $response->html->thisfile,
		);
		$t = $response->html->template(CLASSDIR.'/templates/phppublisher.config.permissions.select.html');
		$t->add($vars);
		$t->add($this->response->get_form($this->actions_name, 'select'));
		$t->add($table, 'table');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}
*/

	//--------------------------------------------
	/**
	 * edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {

		if($this->file->exists(CLASSDIR.'plugins/'.$this->plugin.'/class/'.$this->plugin.'.controller.class.php')) {
			$data = '';
			#$params   = $this->response->get_array($this->actions_name, 'plugin' );
			#$params[$this->actions_name.'_plugin'] = $value;

				require_once(CLASSDIR.'plugins/'.$this->plugin.'/class/'.$this->plugin.'.controller.class.php');
				$response = $this->response->response();
				#$response->add($this->actions_name.'_plugin', $value);
				$class = $this->plugin.'_controller';
				## TODO profilesdir?
				$controller = new $class($this->file, $response, $this->query, $this->user);
				$controller->actions_name  = $this->plugin.'_action';
				$controller->message_param = $this->plugin.'_msg';
				$controller->prefix_tab    = $this->plugin.'_tab';

$methods = get_class_methods($controller);
if(is_array($methods)) {
	foreach($methods as $k => $method) {
		if(
			#$method === 'api' ||
			$method === 'action' || 
			substr($method ,0,2) === '__'
		) {
			unset($methods[$k]);
		}
	}
}
if(is_array($methods)) {
	foreach($methods as $k => $method) {
		if($this->file->exists(CLASSDIR.'plugins/'.$this->plugin.'/class/'.$this->plugin.'.'.$method.'.controller.class.php')) {
			echo $this->plugin.'.'.$method.'.controller.class.php<br>';
		}
	}
}


$this->response->html->help($methods);


		}

/*
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
*/





/*
		$response = $this->response;
		$plugin = $this->response->html->request()->get('plugin');
		if($plugin !== '') {
			$file = CLASSDIR.'plugins/'.$plugin.'/'.$plugin.'.permissions.class.php';
			if($this->file->exists($file)) {
				require_once($file);
				$class = $plugin.'_permissions';
				$controller  = new $class($this->response);
				if(method_exists($controller, 'permissions')) {
					$response = $this->get_response($controller->permissions());
					#$response->html->help($response);
				}
			}
		}
*/
		#return $response;


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
	function get_response( $permissions ) {
		$plugin = $this->response->html->request()->get('plugin');
		$group = $this->response->html->request()->get('group');
		$this->response->add('plugin', $plugin);
		$this->response->add('group', $group);
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');

		$response->plugin = $plugin;
		$response->group = $group;

		$p = array();
		$result = $this->query->select('groups2permissions', '*', array('plugin', $plugin));
		if(is_array($result)) {
			foreach($result as $value) {
				if($value['group'] === $group) {
					$p = explode(', ', $value['permission']);
					break;
				}
			}
		}


		if(is_array($permissions)) {
			foreach($permissions as $key => $value) {
				$d['param_'.$key]['label'] = $key;
				if(isset($value['label'])) {
					$d['param_'.$key]['label'] = $value['label'];
				}
				$d['param_'.$key]['object']['type']           = 'htmlobject_input';
				$d['param_'.$key]['object']['attrib']['type'] = 'checkbox';
				if(isset($value['type']) && $value['type'] === 'bool') {
					if(in_array($key, $p)) {
						$d['param_'.$key]['object']['attrib']['checked'] = true;
					}
				}
				if(isset($value['type']) && $value['type'] === 'string') {
					$d['param_'.$key]['object']['attrib']['type']  = 'text';
					$d['param_'.$key]['object']['attrib']['value'] = $value['default'];
				}
				$d['param_'.$key]['object']['attrib']['name']  = $key;
				if(isset($value['title'])) {
					$d['param_'.$key]['object']['attrib']['title']  = $value['title'];
				}
			}
		}
		$form->add($d);

#echo $form->get_string();

		$response->form = $form;
		return $response;
	}

}
?>
