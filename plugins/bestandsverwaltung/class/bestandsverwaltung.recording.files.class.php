<?php
/**
 * bestandsverwaltung_recording_files
 *
 * This file is part of plugin bestandsverwaltung
 *
 *  This file is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU GENERAL PUBLIC LICENSE Version 2
 *  as published by the Free Software Foundation;
 *
 *  This file is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this file (see ../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_recording_files
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
		$url        = $this->response->get_url($this->controller->controller->actions_name, 'download').'&path=/bestand/devices/'.$this->id;
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
				$te->__template = $this->tpldir.'/bestandsverwaltung.recording.files.html';

				return $te;
			break;
			case 'rename':
				$_REQUEST[$pc->response->id]['submit'] = 'x';
				$res = $pc->rename($controller->path)->action();
				if(!isset($res->error)) {
					$msg = join("<br>", $res->msg);
				} else {
					$error = join("<br>", $res->error);
				}
			break;
			case 'delete':
				$_REQUEST[$pc->response->id]['submit'] = 'x';
				$res = $pc->delete($controller->path)->action();
				if(!isset($res->error)) {
					$msg = join("<br>", $res->msg);
				} else {
					$error = join("<br>", $res->error);
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
		if(isset($msg)) {
			$this->response->redirect(
				$this->response->get_url(
					'', '', $this->message_param, $msg
				).'&'.$pc->__prefix.'['.$pc->actions_name.']'.'=select'
			);
		}
		elseif(isset($error)) {
				$this->response->redirect(
				$this->response->get_url(
					'', '', $this->message_param.'[error]', $error
				).'&'.$pc->__prefix.'['.$pc->actions_name.']'.'=select'
			);	
		}
	}

}
?>
