<?php
/**
 * cafm_one_config_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cafm_one_action';
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'cafm_one_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'cafm_one_ident';
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'tab_login' => 'Login',
	'tab_groups' => 'Groups',
	'tab_pdf' => 'PDF',
	'label_url' => 'Url',
	'label_user' => 'User',
	'label_pass' => 'Password',
	'button_hide' => 'Hide Groups',
	'upload_template' => 'Upload custom background .pdf',
	'download_template' => 'Download .docx template',
	'msg_sucess' => 'Settings updated successfully',
	'error_login_data' => 'ERROR: Please check login credentials',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct( $file, $response, $db, $user ) {
		$this->classdir = CLASSDIR.'/plugins/cafm.one/class/';
		$this->file     = $file;
		$this->settings = PROFILESDIR.'cafm.one.ini';
		$this->ini      = $this->file->get_ini($this->settings);
		$this->response = $response;
		$this->langdir  = CLASSDIR.'/plugins/cafm.one/lang';
		$this->tpldir   = CLASSDIR.'/plugins/cafm.one/templates';
		$this->user     = $user;
		$this->db = $db;

		#require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
		#$this->taetigkeiten = new cafm_one($this->file, $this->response);
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

		$this->response->params[$this->actions_name] = $this->action;
		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'login':
				$content[] = $this->login(true);
				$content[] = $this->groups();
				$content[] = $this->pdf();
			break;
			case 'groups':
				$content[] = $this->login();
				$content[] = $this->groups(true);
				$content[] = $this->pdf();
			break;
			case 'pdf':
				$content[] = $this->login();
				$content[] = $this->groups();
				$content[] = $this->pdf(true);
			break;
			case 'download':
				$content[] = $this->download();
			break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Login
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function login($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'cafm.one.config.login.class.php');
			$controller = new cafm_one_config_login($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_login'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'login' );
		$content['onclick'] = false;
		if($this->action === 'login'){
			$content['active']  = true;
		}
		return $content;

	}

	//--------------------------------------------
	/**
	 * Groups
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function groups($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'cafm.one.config.groups.class.php');
			$controller = new cafm_one_config_groups($this);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_groups'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'groups' );
		$content['onclick'] = false;
		if($this->action === 'groups'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Pdf
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function pdf($visible = false) {
		$data = '';
		if( $visible === true ) {
			require_once($this->classdir.'cafm.one.config.pdf.class.php');
			$controller = new cafm_one_config_pdf($this->file, $this->response, $this->db, $this->user);
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->actions_name = $this->actions_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_pdf'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'pdf' );
		$content['onclick'] = false;
		if($this->action === 'pdf'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function download() {
		require_once(CLASSDIR.'/lib/file/file.mime.class.php');
		$path = CLASSDIR.'plugins/cafm.one/templates/Checklist.docx';
		$file = $this->file->get_fileinfo($path);
		$mime = detect_mime($file['path']);

		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		header("Content-disposition: attachment; filename=".$file['name']);
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
	}

}
?>
