<?php
/**
 * standort_settings_import_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_import_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_import_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_import_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_import_ident';

var $tpldir;

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->file = $controller->file;
		$this->response = $controller->response->response();
		$this->db = $controller->db;
		$this->user = $controller->user;
		$this->controller = $controller;
		$this->classdir = $controller->classdir;
		$this->profilesdir = $controller->profilesdir;
		$this->settings = $controller->settings;
		$this->standort = $controller->standort;

#### TODO
		$this->datadir = $this->profilesdir.'import/standort/';
		$this->path = $this->datadir.'standort.xlsx';

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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		else if($ar === '') {
			$this->action = 'step1';
		}

		// wizard
		if($this->action === 'step1') {
			if($this->file->exists($this->path)) {
				$this->action = 'step2';
			}
		}
		if($this->action === 'step2') {
			if($this->response->cancel()) {
				$error = $this->file->remove($this->path);
				if($error === '') {
					$this->action = 'step1';
				} else {
					$_REQUEST[$this->message_param]['error'] = $error;
				}
			} 
			else if($this->file->exists($this->datadir.'/standort.cache.ini')) {
				$this->action = 'step3';
			}
		}
		if($this->action === 'step3') {
			if(!$this->file->exists($this->path)) {
				$this->action = 'step1';
			}
			elseif(!$this->file->exists($this->datadir.'/standort.cache.ini')) {
				$this->action = 'step2';
			}
			elseif($this->response->cancel()) {
				$error = $this->file->remove($this->datadir.'/standort.cache.ini');
				if($error === '') {
					$this->action = 'step2';
				} else {
					$_REQUEST[$this->message_param]['error'] = $error;
				}
			} 
		}
		if($this->action === 'step4') {
			if(!$this->file->exists($this->path)) {
				$this->action = 'step1';
			}
			elseif(!$this->file->exists($this->datadir.'/standort.cache.ini')) {
				$this->action = 'step2';
			}
		}

		$this->response->add($this->actions_name, $this->action);

		$data = array();
		switch( $this->action ) {
			default:
			case 'step1':
				#$data[] = $this->step4(false, true);
				$data[] = $this->step3(false, true);
				$data[] = $this->step2(false, true);
				$data[] = $this->step1(true, false);
			break;
			case 'step2':
				#$data[] = $this->step4(false, true);
				$data[] = $this->step3(false, true);
				$data[] = $this->step2(true, false);
				$data[] = $this->step1(false, false);
			break;
			case 'step3':
				#$data[] = $this->step4(false, true);
				$data[] = $this->step3(true, false);
				$data[] = $this->step2(false, false);
				$data[] = $this->step1(false, false);
			break;
			case 'step4':
				$data[] = $this->step4(true, false);
				$data[] = $this->step2(false, false);
				$data[] = $this->step1(false, false);
			break;
			case 'download':
				$data = $this->download();
			break;
		}

		$tab = $this->response->html->tabmenu('standort_import_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs right noprint';
		$tab->auto_tab = false;
		$tab->add($data);

		return $tab;
	}


	//--------------------------------------------
	/**
	 * step1
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function step1($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.import.step1.class.php');
			$controller = new standort_settings_import_step1($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_step1'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step1' );
		$content['onclick'] = false;
		if($this->action === 'step1') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * step2
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function step2($visible = false, $disabled = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.import.step2.class.php');
			$controller = new standort_settings_import_step2($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_step2'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step2' );
		$content['onclick'] = false;

		if($disabled === true) {
			$content['css'] = 'disabled';
			$content['request'] = '';
			$content['target'] = '#';
		}
		if($this->action === 'step2') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * step3
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function step3($visible = false, $disabled = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.import.step3.class.php');
			$controller = new standort_settings_import_step3($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_step3'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step3' );
		$content['onclick'] = false;

		if($disabled === true) {
			$content['css'] = 'disabled';
			$content['request'] = '';
			$content['target'] = '#';
		}
		if($this->action === 'step3') {
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * step4
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function step4($visible = false, $disabled = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'standort.settings.import.step4.class.php');
			$controller = new standort_settings_import_step4($this);
			$controller->message_param = $this->message_param;
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['tab_step3'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step3' );
		$content['onclick'] = false;

		if($disabled === true) {
			$content['css'] = 'disabled';
			$content['request'] = '';
			$content['target'] = '#';
		}
		if($this->action === 'step4') {
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
		$path = $this->path;
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
