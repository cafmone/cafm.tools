<?php
/**
 * user_logout
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_logout
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'logout_action';
/**
* lang
* @access public
* @var array
*/
var $lang = array(
	"logout" => "Logout",
);
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $response
	 * @param object $file
	 * @param object $user
	 */
	//--------------------------------------------
	function __construct( $response, $file, $user ) {
		$this->user = $user;
		$this->response = $response;		
		$this->settings = $file->get_ini(PROFILESDIR.'/settings.ini');
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
					case $this->lang['logout']:
					case 'submit':
						if(isset($this->settings['user']['authorize'])) {
							#if($this->settings['user']['authorize'] === 'httpd') {
							#	$host = $_SERVER["HTTP_HOST"];
							#	$url  = 'http://logout:logout@'.$host.'/admin/logout';
							#}
							if($this->settings['user']['authorize'] === 'session') {
								$this->user->logout(true);
							}
							$this->response->redirect($this->response->html->thisfile);
						}
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
		$form = $this->get_form();
		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'/user.logout.html');
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

		$form = $this->response->get_form($this->actions_name, 'update');

		$d['submit']['object']['type']            = 'htmlobject_button';
		$d['submit']['object']['attrib']['type']  = 'submit';
		$d['submit']['object']['attrib']['name']  = 'subaction';
		$d['submit']['object']['attrib']['label'] = $this->lang['logout'];
		$d['submit']['object']['attrib']['value'] = $this->lang['logout'];
		if(isset($this->settings['user']['authorize'])) {
			if($this->settings['user']['authorize'] === 'httpd') {
				$d['submit']['object']['attrib']['handler'] = 'onclick="UserLogout();return false;"';
			}
		}
		$form->add($d);

		return $form;
	}

}
?>
