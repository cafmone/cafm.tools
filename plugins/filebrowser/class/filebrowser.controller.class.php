<?php
/**
 * filebrowser_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */


class filebrowser_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'filebrowser_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'filebrowser_msg';
/**
* lang
* @access public
* @var string
*/
var $lang;
/**
* prefix for tab menu
* @access public
* @var string
*/
var $prefix_tab;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* path to translation
* @access public
* @var string
*/
var $langdir;
/**
* allow [true] or disallow [false] functions
* @access public
* @var array
*/
var $allow = array(
	'new'      => true,
	'upload'   => true,
	'download' => true,
	'copy'     => true,
	'cut'      => true,
	'rename'   => true,
	'delete'   => true,
	'edit'     => true,
	'dir'      => true,
	'filter'   => true,
	'create'   => false,
	'search'   => true,
	'files'    => '*',
	);

### TODO
var $md5_params = '&plugin=filebrowser&filebrowser_action=md5';
var $download_params = '?index_action=plugin&index_action_plugin=filebrowser&filebrowser_action=download';

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $file, $response, $query, $user ) {
		$this->response = $response;
		$this->file     = $file;
		$this->db       = $query;
		$this->user     = $user;
		$this->tpldir   = CLASSDIR.'/plugins/filebrowser/templates/';
		$this->langdir  = CLASSDIR.'/plugins/filebrowser/lang/';
		if(	!isset($_SESSION) ) {
			session_start();
		}

		require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
		$path = CLASSDIR.'/lib/phpcommander';
		$commander = new phpcommander($path, $this->response->html, $this->file, 'pc', $this->response->params);
		$commander->upload_multiple = true;
		if($this->file->exists(PROFILESDIR.'/lang/')) {
			$commander->lang = $this->user->translate($commander->lang, PROFILESDIR.'/lang/', 'phpcommander.ini');
		} else {
			$commander->lang = $this->user->translate($commander->lang, CLASSDIR.'/lang/', 'phpcommander.ini');
		}
		$this->commander = $commander;
		$this->ini = $this->file->get_ini(PROFILESDIR.'filebrowser.ini');

		// handle folders
		if(!isset($this->ini['folders'])) {
			$this->ini['folders'] = array();
		}
		$this->ini['folders']['PROFILES'] = PROFILESDIR;
		$this->ini['folders']['HTTPDOCS'] = $GLOBALS['settings']['config']['basedir'];

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action( $private = false ) {

		// handle commander params
		$this->commander->actions_name  = 'folders_action';
		$this->commander->message_param = $this->message_param;

		$this->action = '';
		$action = $this->response->html->request()->get($this->actions_name);
		if($action !== '') {
			$this->action = $action;
		}

		$this->response->add($this->actions_name, $this->action);
		$data = array();
		switch( $this->action ) {
			case '':
			default:
			case 'select':
				$content = $this->select(true, $private);
			break;
			case 'search':
				$content = $this->search(true);
			break;
			case 'download':
				$content = $this->download(true);
			break;
		}

		$c['label']   = '&#160;';
		$c['value']   = $content;
		$c['hidden']  = true;
		$c['target']  = $this->response->html->thisfile;
		$c['request'] = $this->response->get_array($this->actions_name, 'select' );
		$c['onclick'] = false;
		$c['active']  = true;

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->boxcss = 'tabs-content noborder';
		$tab->message_param = $this->message_param;
		$tab->add(array($c));

		return $tab;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function select($visible = false, $private = false) {

		if($visible === true) {
			$ini = $this->ini;
			if(!isset($ini['config']['private']) || $private === true) {
				// Group switch
				$groups = array();
				if(isset($ini['config']['supervisor'])) {
					$groups[] = $ini['config']['supervisor'];
				}
				// check user is_valid
				if($this->user->is_valid($groups)) {

					$substr = 40;
					if(isset($ini['config']['substr'])) {
						$substr = (int)$ini['config']['substr'];
					}
					$this->commander->substr = $substr;
					$this->commander->allow  = $this->allow;

					$controller = $this->commander->controller($this->ini['folders']);
					$template = $controller->get_template();

					// handle search
					if(isset($controller->action) && $controller->action === 'select') {
						$template->set_template(CLASSDIR.'plugins/filebrowser/templates/filebrowser.select.html');

						$url  = $this->response->get_url($this->actions_name, 'search');
						$url .= '&'.$controller->prefix.'[dir]='.urlencode($controller->dir);
						$url .= '&'.$controller->prefix.'[root]='.urlencode($controller->root);

						$search = '';
						if(isset($this->allow) && isset($this->allow['search']) && $this->allow['search'] === true ) {
							$search = $this->response->html->a();
							$search->css = 'btn btn-default';
							$search->href = $url;
							$search->style = 'margin-right: 4px;';
							$search->label = 'Search';
							$search->handler = 'onclick="phppublisher.wait();"';
							if($controller->dir === '..') {
								$search->href = '#';
								$search->customattribs = 'disabled="disabled"';
								$search->handler = ''; 
							}
						}

						$template->add($search,'search');
					}
					$content = $template;
					return $content;
				} else {
					$div = $this->response->html->div();
					$div->add('Permission denied');
					return $div;
				}
			} else {
				$div = $this->response->html->div();
				$div->add('Permission denied');
				return $div;
			}
		}

	}

	//--------------------------------------------
	/**
	 * serach
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function search( $visible = false ) {
		if($visible === true) {
			require_once(CLASSDIR.'plugins/filebrowser/class/filebrowser.search.class.php');
			$controller = new filebrowser_search($this);
			$controller->tpldir = $this->tpldir;
			#$controller->identifier_name = $this->identifier_name;
			$data = $controller->action();
			return $data;
		}
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function download($visible = false) {
		if($visible === true) {
			$controller = $this->commander->controller($this->ini['folders']);
			$folders = $controller->get_root();
			$dir  = $controller->dir;
			$root = $controller->root;
			$name = $this->response->html->request()->get('file');
			$path = '';
			if(isset($folders) && is_array($folders) && $name !== '') {
				$path  = $folders[$root]['path'];
				$path .= ''.$dir.'/';
				$path .= $name;
			}
			if($path !== '' && $this->file->exists($path)) {
				require_once(CLASSDIR.'/lib/file/file.mime.class.php');
				$mime = detect_mime($path);
				$file = $this->file->get_fileinfo($path);
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
			} else {
				$div = $this->response->html->div();
				$div->add('Error: File not found');
				return $div;
			}
		}
	}


}
?>
