<?php
/**
 * user_settings
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_settings
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'admin_id';
/**
* name of message param
* @access public
* @var string
*/
var $message_param = 'Msg';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'settings_action';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	"required" => 'required',
	"form_saveto" => "Store Data",
	"data_external" => "Path to external data directory",
	"form_email" => "Email",
	"form_salutation" => "Salutation",
	"form_forename" => "Forename",
	"form_lastname" => "Last Name",
	"form_title" => "Title",
	"form_date" => "Date",
	"form_address" => "Adress",
	"form_city" => "City",
	"form_zip" => "Zip",
	"form_state" => "State",
	"form_country" => "Country",
	"form_firm" => "Firm",
	"form_office" => "Office",
	"form_phone" => "Phone",
	"form_cellphone" => "Cell Phone",
	"form_comment" => "Comment",
	"saved" => "Users Settings have been saved",
	"lang_authorize" => "Authorization",
	"lang_authenticate" => "Authentication",
	"lang_form" => "Users Form",
	"lang_login" => "Users Login",
	"lang_secdir" => "Directory",
	"authorize_httpd" => "Httpd",
	"authorize_session" => "Session",
	"authorize_ldap" => "Ldap",
	"authenticate_file" => "File",
	"authenticate_db" => "DB",
	"error_folder_not_found" => "Folder not found"
);
/**
* path to templates dir
* @access public
* @var string
*/
var $tpldir = '';

	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array|string $root
	 * @param phpcommander $phpcommander
	 */
	//--------------------------------------------
	function __construct( $file, $response ) {
		$this->settings = PROFILESDIR.'/settings.ini';
		$this->file     = $file;
		$this->response = $response;
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
		if($this->response->cancel()) {
			$this->action = 'update';
		}

		$content   = array();
		switch( $this->action ) {
			case '':
			case 'update':
				$response = $this->update();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'lang_login' => $this->lang['lang_login'],
					'lang_form' => $this->lang['lang_form'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'/user.settings.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response('update');
		$form     = $response->form;
		$ini      = $form->get_request();
		if(!isset($ini)) {
			$ini = array();
		}
		// handle login dir
		if(!isset($GLOBALS['settings']['folders']['login'])) {
			if(isset($ini['user']['authorize']) && $ini['user']['authorize'] === 'httpd') {
				$form->set_error('user[authorize]', 'please set login dir');
			}
		}
		// handle saveto
		if(isset($ini['user']) && isset($ini['user']['saveto']) && $ini['user']['saveto'] === 'db') {
			if(!isset($GLOBALS['settings']['config']['query'])) {
				$form->set_error('user[saveto]', 'db not configured');
			}
		}
		if(!$form->get_errors() && $response->submit()) {
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['user']);
				$ini = array_merge($old,$ini);
			}
			$error = $this->file->make_ini($this->settings, $ini, null );
			if($error === '') {
				if(
					isset($GLOBALS['settings']['user']) &&
					$GLOBALS['settings']['user']['authorize'] !== $ini['user']['authorize']
				) {
					$f = $GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders']['login'].'.htaccess';
					if($ini['user']['authorize'] === 'session') {
						$error = $this->file->rename($f, $f.'-disabled');
					}
					if($ini['user']['authorize'] === 'httpd') {
						$error = $this->file->rename($f.'-disabled', $f);
					}
				}
			}
			if($error === '') {
				$response->msg = $this->lang['saved'];
			} else {
				$response->error = $error;
			}
		} 
		else if ($form->get_errors() && $response->submit()){
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response( $mode ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);
		if( $mode === 'update' ) {
		
			$ini = $this->file->get_ini( $this->settings );

			$auth[] = array('session', $this->lang['authorize_session']);
			if(!$this->file->is_win()) {
				$auth[] = array('httpd', $this->lang['authorize_httpd']);
			}
			$auth[] = array('ldap', $this->lang['authorize_ldap']);

			$d['user_authorize']['label']                       = $this->lang['lang_authorize'];
			$d['user_authorize']['required']                    = true;
			$d['user_authorize']['object']['type']              = 'htmlobject_select';
			$d['user_authorize']['object']['attrib']['name']    = 'user[authorize]';
			$d['user_authorize']['object']['attrib']['index']   = array(0,1);
			$d['user_authorize']['object']['attrib']['options'] = $auth;
			if(isset($ini['user']['authorize'])) {
				$d['user_authorize']['object']['attrib']['selected'] = array($ini['user']['authorize']);
			}

			$aut[] = array('file', $this->lang['authenticate_file']);
			#$aut[] = array('db', $this->lang['authenticate_db']);

			$d['user_authenticate']['label']                       = $this->lang['lang_authenticate'];
			$d['user_authenticate']['required']                    = true;
			$d['user_authenticate']['object']['type']              = 'htmlobject_select';
			$d['user_authenticate']['object']['attrib']['name']    = 'user[authenticate]';
			$d['user_authenticate']['object']['attrib']['index']   = array(0,1);
			$d['user_authenticate']['object']['attrib']['options'] = $aut;
			if(isset($ini['user']['authenticate'])) {
				$d['user_authenticate']['object']['attrib']['selected'] = array($ini['user']['authenticate']);
			}

			$s2[] = array('file', $this->lang['authenticate_file']);

			$d['form_saveto']['label']                       = $this->lang['form_saveto'];
			$d['form_saveto']['required']                    = true;
			$d['form_saveto']['object']['type']              = 'htmlobject_select';
			$d['form_saveto']['object']['attrib']['name']    = 'user[saveto]';
			$d['form_saveto']['object']['attrib']['index']   = array(0,1);
			$d['form_saveto']['object']['attrib']['options'] = $s2;
			if(isset($ini['user']['saveto'])) {
				$d['form_saveto']['object']['attrib']['selected'] = array($ini['user']['saveto']);
			}

			## TODO check
			$d['data_external']['label']                       = $this->lang['data_external'];
			$d['data_external']['object']['type']              = 'htmlobject_input';
			$d['data_external']['object']['attrib']['name']    = 'user[data_external]';
			if(isset($ini['user']['data_external'])) {
				$d['data_external']['object']['attrib']['value'] = $ini['user']['data_external'];
			}

			$elements = array(
				'email',
				'salutation',
				'forename',
				'lastname',
				'title',
				'date',
				'address',
				'city',
				'zip',
				'state',
				'country',
				'firm',
				'office',
				'phone',
				'cellphone'
			);
			foreach($elements as $v) {
				$d['form_'.$v]['label']                     = $this->lang['form_'.$v];
				$d['form_'.$v]['css']                       = 'autosize float-right';
				$d['form_'.$v]['object']['type']            = 'htmlobject_input';
				$d['form_'.$v]['object']['attrib']['name']  = 'user['.$v.']';
				$d['form_'.$v]['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v]['object']['attrib']['value'] = '';
				if(isset($ini['user'][$v])) {
					$d['form_'.$v]['object']['attrib']['checked'] = true;
				}
				$d['form_'.$v.'_required']['label']                     = $this->lang['required'];
				$d['form_'.$v.'_required']['css']                       = 'autosize inverted checkbox';
				$d['form_'.$v.'_required']['object']['type']            = 'htmlobject_input';
				$d['form_'.$v.'_required']['object']['attrib']['name']  = 'user['.$v.'_required]';
				$d['form_'.$v.'_required']['object']['attrib']['type']  = 'checkbox';
				$d['form_'.$v.'_required']['object']['attrib']['value'] = '';
				if(isset($ini['user'][$v.'_required'])) {
					$d['form_'.$v.'_required']['object']['attrib']['checked'] = true;
				}
			}
			
			$d['form_comment']['label']                     = $this->lang['form_comment'];
			$d['form_comment']['css']                       = 'autosize float-right';
			$d['form_comment']['object']['type']            = 'htmlobject_input';
			$d['form_comment']['object']['attrib']['name']  = 'user[comment]';
			$d['form_comment']['object']['attrib']['type']  = 'checkbox';
			$d['form_comment']['object']['attrib']['value'] = '';
			if(isset($ini['user']['comment'])) {
				$d['form_comment']['object']['attrib']['checked'] = true;
			}
			
			$form->add($d);
		}
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access public
	 * @param string $msg
	 * @param string $mode
	 */
	//--------------------------------------------
	function __redirect( $msg, $mode = '' ) {
		$this->response->redirect($this->response->get_url($this->actions_name, $mode, $this->message_param, $msg));
	}

}
?>
