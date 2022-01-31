<?php
/**
 * user_login
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_login
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'login_action';
/**
* lang
* @access public
* @var array
*/
var $lang = array(
	"login" => "Login",
	"password" => "Password",
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
	 * @param object $response
	 * @param object $user
	 */
	//--------------------------------------------
	function __construct( $response, $user ) {
		$this->response = $response;
		$this->user = $user;
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
	function action( $action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		$subaction = $this->response->html->request()->get('subaction');
		switch( $this->action ) {
			case '':
			case 'update':
				switch( $subaction ) {
					case '':
						return $this->update();
					break;
					case $this->lang['login']:
					case 'submit':
						return $this->update();
						#$host = $_SERVER["HTTP_HOST"];
						#$url  = 'http://logout:logout@'.$host.'/admin/logout';
						#$this->response->redirect($url);
					break;
				}
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
		$response = $this->get_response('update');
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$user = $this->user->get($form->get_request('login'));
			if(!$user) {
				$form->set_error('login', 'unknown' );
				$_REQUEST['user_login_msg'] = 'Login or Password unknown';
			} else {
				$ht = array();
				### TODO external user
				$handle = fopen (PROFILESDIR.'/.htpasswd', "r");
				while (!feof($handle)) {
					$tmp = explode(':', fgets($handle, 4096));
					if($tmp[0] !== '') {
						$ht[$tmp[0]] = $tmp[1];
					}
				}
				fclose ($handle);
				foreach($ht as $key => $value) {
		 			if($key === $user['login']) {
						$pass = crypt($form->get_request('pass'), $value);
						if(trim($value) === $pass) {
							$this->user->login($form->get_request('login'));
							$this->response->redirect($form->get_request('forwarder'));
						} else {
							$form->set_error('login', 'unknown' );
							$_REQUEST['user_login_msg'] = 'Login or Password unknown';
							// slow down brute force
							sleep(5);
						}
					}
				}
			}
		} else {
			if($form->get_errors()) {
				$_REQUEST['user_login_msg'] = implode('<br>', $form->get_errors());
			}
		}
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'user.login.html');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));

		$c['label']   = 'Login';
		$c['value']   = $t;
		$c['target']  = $this->response->html->thisfile;
		$c['request'] = $this->response->get_array($this->actions_name, 'login' );
		$c['hidden']  = true;
		$c['onclick'] = false;
		$c['css']     = 'noborder';

		$content[] = $c;

		$tab = $this->response->html->tabmenu('user_login_tab');
		$tab->message_param = 'user_login_msg';
		$tab->css = 'htmlobject_tabs';
		$tab->boxcss = 'tabs-content noborder';
		$tab->auto_tab = false;
		$tab->add($content);

		return $tab;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response->response();
		$response->id = 'userlogin';
		$form = $response->get_form($this->actions_name, 'update', true, true);
		$forwarder = $response->html->thisfile;
		if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"] !== '') {
			$f = explode('?', $_SERVER["HTTP_REFERER"]);
			if(isset($f[1])) {
				$f = str_replace('&', '&amp;', $f[1]);
				$forwarder = $forwarder.'?'.$f;
			}
		}
		$d['forwarder']['object']['type']            = 'htmlobject_input';
		$d['forwarder']['object']['attrib']['type']  = 'hidden';
		$d['forwarder']['object']['attrib']['name']  = 'forwarder';
		$d['forwarder']['object']['attrib']['value'] = $forwarder;

		$d['login']['label']                    = $this->lang['login'];
		$d['login']['required']                 = true;
		$d['login']['object']['type']           = 'htmlobject_input';
		$d['login']['object']['attrib']['type'] = 'text';
		$d['login']['object']['attrib']['name'] = 'login';

		$d['pass']['label']                    = $this->lang['password'];
		$d['pass']['required']                 = true;
		$d['pass']['object']['type']           = 'htmlobject_input';
		$d['pass']['object']['attrib']['type'] = 'password';
		$d['pass']['object']['attrib']['name'] = 'pass';

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;
		
		return $response;
	}


}
?>
