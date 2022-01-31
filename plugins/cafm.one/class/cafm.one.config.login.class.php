<?php
/**
 * cafm_one_config_login
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_config_login
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
var $lang = array();

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
		$this->file     = $controller->file;
		$this->response = $controller->response;
		$this->settings = $controller->settings;
		$this->ini      = $controller->ini;
		$this->db       = $controller->db;
		$this->user     = $controller->user;
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

		if(isset($form->error)) {
			$_REQUEST[$this->message_param]['error'] = $form->error;
		}

		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'cafm.one.config.login.html');
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
				unset($old['login']);
				$request = array_merge($old, $request);
			}

			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					### TODO
					// init class with new settings
					require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
					$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
					$todos = $this->taetigkeiten->prefixes(false);
					if(is_array($todos)) {
						$msg = $this->lang['msg_sucess'];
						$this->response->redirect($this->response->get_url($this->actions_name, 'login', $this->message_param, $msg));
					} else {
						$form->error = $todos;
					}
				} else {
					$form->error = $error;
				}
			} else {
				$form->error = $error;
			}
		} 
		else if($form->get_errors()) {
			$form->error = implode('<br>', $form->get_errors());
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
		$ini  = $this->file->get_ini( $this->settings );
		$form = $this->response->get_form($this->actions_name, 'login');

		$d['url']['label']                     = $this->lang['label_url'];
		$d['url']['required']                  = true;
		$d['url']['object']['type']            = 'htmlobject_input';
		$d['url']['object']['attrib']['name']  = 'login[url]';
		$d['url']['object']['attrib']['type']  = 'text';
		if(isset($ini['login']['url'])) {
			$d['url']['object']['attrib']['value'] = $ini['login']['url'];
		}

		$d['user']['label']                    = $this->lang['label_user'];
		$d['user']['required']                 = true;
		$d['user']['object']['type']           = 'htmlobject_input';
		$d['user']['object']['attrib']['name'] = 'login[user]';
		$d['user']['object']['attrib']['type'] = 'text';
		if(isset($ini['login']['user'])) {
			$d['user']['object']['attrib']['value'] = $ini['login']['user'];
		}

		$d['pass']['label']                    = $this->lang['label_pass'];
		$d['pass']['required']                 = true;
		$d['pass']['object']['type']           = 'htmlobject_input';
		$d['pass']['object']['attrib']['name'] = 'login[pass]';
		$d['pass']['object']['attrib']['type'] = 'text';
		if(isset($ini['login']['pass'])) {
			$d['pass']['object']['attrib']['value'] = $ini['login']['pass'];
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
