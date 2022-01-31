<?php
/**
 * query_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_settings
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_action';
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
		"query" => array(
			"type" => "Type",
			"host" => "Host",
			"db" => "DB",
			"user" => "User",
			"pass" => "Pass"
		),
		"update_sucess" => "Settings updated successfully",
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
	function __construct( $file, $response ) {
		$this->file     = $file;
		$this->response = $response;
		$this->settings = PROFILESDIR.'settings.ini';
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
		else if(isset($action)) {
			$this->action = $action;
		}
		switch( $this->action ) {
			case '':
			case 'update':
				return $this->update();
			break;
		}
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
				$request = array_merge($old,$request);
			}
			$query = new query(CLASSDIR.'lib/db');
			if($request['query']['type'] === 'file') {
				$query->db = PROFILESDIR;
				$query->type = 'file';
			}
			else if($request['query']['type'] !== 'file') {
				$query->host = isset($request['query']['host']) ? $request['query']['host'] : null ;
				$query->db   = isset($request['query']['db'])   ? $request['query']['db']   : null ;
				$query->user = isset($request['query']['user']) ? $request['query']['user'] : null ;
				$query->pass = isset($request['query']['pass']) ? $request['query']['pass'] : null ;
				$query->type = isset($request['query']['type']) ? $request['query']['type'] : null ;
			}
			if(isset($query->handler()->error)) {
				$error = $query->handler()->error;
			}
			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['update_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'update', $this->message_param, $msg));
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
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'query.settings.html');
		$t->add($this->lang['lang_query'], 'lang_query');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
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
		$form = $this->response->get_form($this->actions_name, 'update');

		// DB
		$db[] = array('mysql', 'MySQL');

		$d['query_type']['label']                      = $this->lang['query']['type'];
		$d['query_type']['object']['type']             = 'htmlobject_select';
		$d['query_type']['object']['attrib']['name']   = 'query[type]';
		$d['query_type']['object']['attrib']['index']  = array(0,1);
		$d['query_type']['object']['attrib']['options'] = $db;
		if(isset($ini['query']['type'])) {
			$d['query_type']['object']['attrib']['selected'] = array($ini['query']['type']);		
		}

		$d['query_host']['label']                     = $this->lang['query']['host'];
		$d['query_host']['required']                  = true;
		$d['query_host']['object']['type']            = 'htmlobject_input';
		$d['query_host']['object']['attrib']['name']  = 'query[host]';
		$d['query_host']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['host'])) {
			$d['query_host']['object']['attrib']['value'] = $ini['query']['host'];		
		}

		$d['query_db']['label']                     = $this->lang['query']['db'];
		$d['query_db']['required']                  = true;
		$d['query_db']['object']['type']            = 'htmlobject_input';
		$d['query_db']['object']['attrib']['name']  = 'query[db]';
		$d['query_db']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['db'])) {
			$d['query_db']['object']['attrib']['value'] = $ini['query']['db'];		
		}

		$d['query_user']['label']                     = $this->lang['query']['user'];
		$d['query_user']['required']                  = true;
		$d['query_user']['object']['type']            = 'htmlobject_input';
		$d['query_user']['object']['attrib']['name']  = 'query[user]';
		$d['query_user']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['user'])) {
			$d['query_user']['object']['attrib']['value'] = $ini['query']['user'];		
		}

		$d['query_pass']['label']                     = $this->lang['query']['pass'];
		$d['query_pass']['object']['type']            = 'htmlobject_input';
		$d['query_pass']['object']['attrib']['name']  = 'query[pass]';
		$d['query_pass']['object']['attrib']['type']  = 'text';
		if(isset($ini['query']['pass'])) {
			$d['query_pass']['object']['attrib']['value'] = $ini['query']['pass'];		
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
