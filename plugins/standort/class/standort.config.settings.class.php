<?php
/**
 * standort_config_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_config_settings
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
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
		"lang_query" => "Database",
		"lang_permissions" => "Permissions",
		"update_sucess" => "Settings updated successfully",
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->file     = $controller->file;
		$this->response = $controller->response;
		$this->user     = $controller->user;
		$this->settings = PROFILESDIR.'standort.ini';
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

		$form = $this->update();
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'standort.config.settings.html');
		$t->add($this->lang['lang_query'], 'lang_db');
		$t->add($this->lang['lang_permissions'], 'lang_permissions');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update() {
		$form = $this->get_form();
		if(!$form->get_errors() && $this->response->submit()) {
			$error = '';
			$request = $form->get_request();
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['query']);
				unset($old['settings']);
				$request = array_merge($old, $request);
			}

			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['update_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'settings', $this->message_param, $msg));
					} else {
						$_REQUEST[$this->message_param] = $error;
					}
			} else {
				$_REQUEST[$this->message_param] = $error;
			}
		} 
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = implode('<br>', $form->get_errors());
		}
		return $form;
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
		$ini  = $this->file->get_ini( $this->settings, true, true );
		$form = $this->response->get_form($this->actions_name, 'settings');

		$d['db']['label']                     = 'DB';
		$d['db']['required']                  = true;
		$d['db']['object']['type']            = 'htmlobject_input';
		$d['db']['object']['attrib']['name']  = 'settings[db]';
		$d['db']['object']['attrib']['type']  = 'text';
		if(isset($ini['settings']['db'])) {
			$d['db']['object']['attrib']['value'] = $ini['settings']['db'];
		}

		$d['prefix']['label']                     = 'Prefix';
		$d['prefix']['required']                  = true;
		$d['prefix']['object']['type']            = 'htmlobject_input';
		$d['prefix']['object']['attrib']['name']  = 'query[prefix]';
		$d['prefix']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['prefix'])) {
			$d['prefix']['object']['attrib']['value'] = $ini['query']['prefix'];
		}
		
		$d['table']['label']                     = 'Content';
		$d['table']['required']                  = true;
		$d['table']['object']['type']            = 'htmlobject_input';
		$d['table']['object']['attrib']['name']  = 'query[content]';
		$d['table']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['content'])) {
			$d['table']['object']['attrib']['value'] = $ini['query']['content'];
		}
		
		$d['identifiers']['label']                     = 'Identifiers';
		$d['identifiers']['required']                  = true;
		$d['identifiers']['object']['type']            = 'htmlobject_input';
		$d['identifiers']['object']['attrib']['name']  = 'query[identifiers]';
		$d['identifiers']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['identifiers'])) {
			$d['identifiers']['object']['attrib']['value'] = $ini['query']['identifiers'];
		}

		// Permissions

		$groups = $this->user->list_groups();
		if(!isset($groups)) {
			$groups = array();
		}
		array_unshift($groups, '');
		$d['supervisor']['label']                       = 'Supervisor group';
		$d['supervisor']['object']['type']              = 'htmlobject_select';
		$d['supervisor']['object']['attrib']['index']   = array(0,0);
		$d['supervisor']['object']['attrib']['options'] = $groups;
		$d['supervisor']['object']['attrib']['name']    = 'settings[supervisor]';
		if(isset($ini['settings']['supervisor'])) {
			$d['supervisor']['object']['attrib']['selected'] = array($ini['settings']['supervisor']);
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
