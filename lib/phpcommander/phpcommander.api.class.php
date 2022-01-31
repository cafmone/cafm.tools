<?php
/**
 * PHPCommander Api
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_api
{
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array|string $root
	 * @param phpcommander $phpcommander
	 */
	//--------------------------------------------
	function __construct( $phpcommander ) {
		$this->__pc = $phpcommander;
	}


	//--------------------------------------------
	/**
	 * Controller
	 *
	 * @access public
	 * @param array|string $root
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function controller($root) {

		$prefix = $this->__pc->__prefix;

		$params1 = $this->__pc->html->request()->get($prefix.'2');
		if($params1 === '') {
			$params1 = array();
		} else {
			$params1 = array($prefix.'2' => $params1);
		}
		$params1 = $params1 + $this->__pc->response->params;

		$params2 = $this->__pc->html->request()->get($prefix.'1');
		if($params2 === '') {
			$params2 = array();
		} else {
			$params2 = array($prefix.'1' => $params2);
		}
		$params2 = $params2 + $this->__pc->response->params;

		$pc1 = new phpcommander($this->__pc->__path, $this->__pc->html, $this->__pc->file, $prefix.'1', $params1);
		$pc1->actions_name    = $this->__pc->actions_name.'1';
		$pc1->message_param   = $this->__pc->message_param;
		$pc1->allow           = $this->__pc->allow;
		$pc1->colors          = $this->__pc->colors;
		$pc1->lang            = $this->__pc->lang;
		$pc1->identifier_name = $this->__pc->identifier_name.'1';
		$pc1->deny            = $this->__pc->deny;
		$pc1->tpldir          = $this->__pc->tpldir;
		$pc1->handler_tr      = $this->__pc->handler_tr;

		$pc2 = new phpcommander($this->__pc->__path, $this->__pc->html, $this->__pc->file, $prefix.'2', $params2);
		$pc2->actions_name    = $this->__pc->actions_name.'2';
		$pc2->message_param   = $this->__pc->message_param;
		$pc2->allow           = $this->__pc->allow;
		$pc2->colors          = $this->__pc->colors;
		$pc2->lang            = $this->__pc->lang;
		$pc2->identifier_name = $this->__pc->identifier_name.'2';
		$pc2->deny            = $this->__pc->deny;
		$pc2->tpldir          = $this->__pc->tpldir;
		$pc2->handler_tr      = $this->__pc->handler_tr;

		$controller1 = $pc1->controller($root);
		$controller2 = $pc2->controller($root);

		$controller1->init();
		$controller2->init();

		if($this->__pc->html->request()->get($pc1->__prefix.'['.$pc1->actions_name.']') === 'edit') {
			return $controller1->get_template();
		}
		else if($this->__pc->html->request()->get($pc2->__prefix.'['.$pc2->actions_name.']') === 'edit') {
			return $controller2->get_template();
		} else {
			$vars = array(
				'controller1' => $controller1,
				'controller2' => $controller2,
				);
			$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.api.controller.html');
			$t->add($vars);
			return $t;
		}

	}


	//--------------------------------------------
	/**
	 * Api select
	 *
	 * @access public
	 * @param array|string $root
	 * @param bool $object return as object?
	 */
	//--------------------------------------------
	function select($root, $object = false) {

		$this->__pc->allow['dir'] = false;
		$this->__pc->allow['download'] = false;
		$this->__pc->allow['cut'] = false;
		$this->__pc->allow['copy'] = false;
		$this->__pc->allow['edit'] = false;
		$this->__pc->substr = false;

		$controller = $this->__pc->controller($root);
		$action     = $this->__pc->html->request()->get($this->__pc->__prefix.'['.$this->__pc->actions_name.']');
		$script     = '';
		$docroot    = $_SERVER['DOCUMENT_ROOT'];
		$current    = $controller->__root[$controller->root]['path'];
		$path       = str_replace($docroot, '', $current);
		$url        = '/'.$controller->__root[$controller->root]['name'];

		if($controller->dir !== '..' && $controller->dir !== '') {
			$path = $path.'/'.$controller->dir;
			$url  = $url.'/'.$controller->dir;	
		} 

		switch($action) {
			case '':
				$vars = array(
					'thisfile' => $this->__pc->html->thisfile,
					'prefix'   => $this->__pc->__prefix
					);
				$script = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.api.select.js');
				$script->add($vars);

			case 'select':
				$te = $controller->get_template();
				//unset upload script
				$te->__elements['upload']->add('', 'script');

				$folders = $this->__pc->file->get_folders($controller->path, '', '*');
				$folders = $this->__pc->select($controller->__actions, $controller->path, $controller->__root, $controller->root, $controller->dir)->__folder_array($folders, 14);
				foreach($folders as $folder) {
					$fo[] =  $folder['link'];
				}
				$te->add($script, 'script');
				$te->add($path, 'path');
				$te->add($url, 'url');
				$te->add($fo, 'folders');
				$count = count($fo);
				if($controller->dir !== '..') {
					$count = $count-1;
				}
				($count === 0) ? $count = '&#160;' : null;
				$te->add("$count", 'folders_num');
				$te->add($this->__pc->lang['folder']['lang_delete'], 'lang_delete');
				$te->add($this->__pc->lang['folder']['lang_delete_confirm'], 'lang_delete_confirm');
				$te->add($this->__pc->lang['folder']['lang_rename'], 'lang_rename');
				$te->add($this->__pc->lang['folder']['lang_new'], 'lang_new_folder');
				$te->add($this->__pc->lang['file']['lang_cancel'], 'lang_cancel');
				$te->add($this->__pc->lang['editor']['lang_close'], 'lang_close');
				$te->add($this->__pc->lang['editor']['lang_loading'], 'lang_loading');
				$te->add($this->__pc->lang['editor']['lang_insert'], 'lang_insert');
				$te->add($this->__pc->identifier_name.'[]', 'identifier');
				$te->add($this->__pc->__prefix.'['.$this->__pc->actions_name.']', 'actions_name');
				$te->__template = $this->__pc->tpldir.'/phpcommander.api.select.html';
				if($object === true) {
					return $te;
				}
				else if($object === false) {
					echo $te->get_string();
				}
			break;
			case 'rename':
				$_REQUEST[$this->__pc->response->id]['submit'] = 'x';
				$res = $this->__pc->rename($controller->path)->action();
				if(!isset($res->error)) {
					echo 200;
				} else {
					echo join("\n", $res->error);
				}
			break;
			case 'delete':
				$_REQUEST[$this->__pc->response->id]['submit'] = 'x';
				$res = $this->__pc->delete($controller->path)->action();
				if(!isset($res->error)) {
					echo 200;
				} else {
					echo join("\n", $res->error);
				}
			break;
			case 'upload':
				$res = $this->__pc->upload($controller->path)->upload();
				if($res['status'] === '200') {
					echo 200;
				} else {
					echo $res['msg'];
				}
			break;
			case 'insert_folder':
				if((count($controller->__root) > 1 && $controller->dir !== '..') || count($controller->__root) === 1) {
					$_REQUEST[$this->__pc->response->id]['submit'] = 'x';
					$res = $this->__pc->insert($controller->path)->insert_folder();
					if(!isset($res->error)) {
						echo 200;
					} else {
						echo $res->error;
					}
				} else {
					echo 200;
				}
			break;
		}
	}


}
