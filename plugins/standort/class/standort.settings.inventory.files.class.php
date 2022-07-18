<?php
/**
 * standort_settings_inventory_files
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_files
{

#var $prefix_tab = 'filetab';

var $datadir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->user       = $controller->user;
		$this->settings   = $controller->settings;
		$this->classdir   = $controller->classdir;

		$id = $this->response->html->request()->get('id');
		if( $id !== '') {
			$this->id = $id;
			$this->response->add('id', $this->id);
		}
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

		require_once(CLASSDIR.'/lib/phpcommander/phpcommander.class.php');
		$path = CLASSDIR.'/lib/phpcommander';
		$pc = new phpcommander($path, $this->response->html, $this->file, 'pc', $this->response->params);
		$pc->actions_name  = 'folders_action';
		$pc->message_param = $this->message_param;
		if($this->file->exists(PROFILESDIR.'/lang/')) {
			$pc->lang = $this->user->translate($pc->lang, PROFILESDIR.'/lang/', 'phpcommander.ini');
		} else {
			$pc->lang = $this->user->translate($pc->lang, CLASSDIR.'/lang/', 'phpcommander.ini');
		}

		$pc->allow['dir'] = false;
		$pc->allow['download'] = false;
		$pc->allow['cut'] = false;
		$pc->allow['copy'] = false;
		$pc->allow['edit'] = false;
		$pc->allow['filter'] = false;
		$pc->allow['create'] = true;
		$pc->substr = 50;
		$pc->upload_multiple = true;
		$pc->handler_tr = array(
				'onclick'     => 'FileBrowser_tr_click',
				'onmouseover' => 'FileBrowser_tr_hover',
				'onmouseout'  => 'FileBrowser_tr_hover'
			);

		## TODO path
		$controller = $pc->controller(array($this->datadir.$this->id));

		$action     = $pc->html->request()->get($pc->__prefix.'['.$pc->actions_name.']');
		$script     = '';
		$url        = $this->response->get_url($this->controller->controller->actions_name, 'download').'&path=devices/'.$this->id;
		if($controller->dir !== '..' && $controller->dir !== '') {
			$url  = $url.'/'.$controller->dir;	
		} 

		switch($action) {
			case '':
			case 'select':
			case 'upload':
				$te = $controller->get_template();

				$te->__elements['table']->id = 'FileBrowserTable';
				$te->add($url, 'url');
				$te->add('', 'label_files');
				$te->add($pc->lang['folder']['lang_delete'], 'lang_delete');
				$te->add($pc->lang['folder']['lang_delete_confirm'], 'lang_delete_confirm');
				$te->add($pc->lang['folder']['lang_rename'], 'lang_rename');
				$te->add($pc->lang['folder']['lang_new'], 'lang_new_folder');
				$te->add($pc->lang['file']['lang_cancel'], 'lang_cancel');
				$te->add($pc->lang['editor']['lang_close'], 'lang_close');
				$te->add($pc->lang['editor']['lang_loading'], 'lang_loading');
				$te->add($pc->lang['editor']['lang_insert'], 'lang_insert');
				$te->add($pc->lang['folder']['lang_download'], 'lang_download');
				$te->add($pc->lang['editor']['lang_loading'], 'lang_loading');
				$te->add($this->response->html->thisfile, 'thisfile');
				$te->add($pc->identifier_name.'[]', 'identifier');
				$te->add($pc->__prefix.'['.$pc->actions_name.']', 'actions_name');
				$te->__template = $this->tpldir.'/standort.settings.inventory.files.html';

				return $te;
			break;
			case 'rename':
				$_REQUEST[$pc->response->id]['submit'] = 'x';
				$res = $pc->rename($controller->path)->action();
				if(!isset($res->error)) {
					$msg = join("<br>", $res->msg);
				} else {
					$msg = join("<br>", $res->error);
				}
			break;
			case 'delete':
				$_REQUEST[$pc->response->id]['submit'] = 'x';
				$res = $pc->delete($controller->path)->action();
				if(!isset($res->error)) {
					$msg = join("<br>", $res->msg);
				} else {
					$msg = join("<br>", $res->error);
				}
			break;
/*
			case 'upload':
				$res = $pc->upload($controller->path)->upload();

$this->response->html->help($res);

				if($res['status'] === '200') {
					$msg = $res['msg'];
				} else {
					$msg = $res['msg'];
				}
			break;
*/
		}

		// do redirect
		$this->response->redirect(
			$this->response->get_url(
				'', '', $this->message_param, $msg
			)
		);
	}

}
?>
