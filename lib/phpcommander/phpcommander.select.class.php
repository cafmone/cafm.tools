<?php
/**
 * PHPCommander Select
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_select
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $actions
	 * @param string $path
	 * @param string $__root
	 * @param string $root
	 * @param string $dir
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct( $actions, $path, $__root, $root, $dir, $phpcommander ) {
		$this->root      = $root;
		$this->dir       = $dir;
		$this->__actions = $actions;
		$this->__path    = $path;
		$this->__root    = $__root;
		$this->__pc      = $phpcommander;

		// handlers
		$this->handlers = $this->__pc->__handlers;
	}

	//--------------------------------------------
	/**
	 * Action select (overview)
	 *
	 * @access public
	 * @return htmlobject_formbuilder
	 */
	//--------------------------------------------
	function action() {

		$head['s_name']['hidden']   = true;
		$head['s_name']['sortable'] = false;
		$head['name']['sortable']   = false;
		$head['name']['hidden']     = true;
		$head['type']['title']      = $this->__pc->lang['folder']['label_name'];
		$head['type']['sortable']   = true;
		$head['type']['map']        = 's_name';
		$head['date']['title']      = $this->__pc->lang['folder']['label_date'];
		#$head['date']['style']      = 'width:150px;';
		$head['filesize']['title']  = $this->__pc->lang['folder']['label_size'];
		#$head['filesize']['style']  = 'width:150px;';

		$prefix = $this->__pc->__prefix;

		$params = $this->__pc->response->get_array($this->__pc->response->actions_name, 'select');
		unset($params[$prefix]['limit']);
		unset($params[$prefix]['offset']);
		unset($params[$prefix]['order']);
		unset($params[$prefix]['action']);
		unset($params[$prefix]['sort']);

		$table = $this->__pc->html->tablebuilder( $prefix, $params);
		$table->identifier_disabled = array('..');
		$table->sort_form = false;
		$table->limit = 0;


		$body = array();
		$filter = $this->__pc->html->request()->get($this->__pc->__prefix.'[filter]');

		if($this->__pc->allow['dir'] === true) {

			$folders = $this->__pc->file->get_folders($this->__path, '', '*');
			$folders = $this->__folder_array($folders);

			$count = count($folders);
			for($i=0; $i<$count; $i++) {
				if($folders[$i]['read'] != true) { $table->identifier_disabled[] = $folders[$i]['name']; }
				
				$s_name = 'd '.$folders[$i]['name'];
				if($folders[$i]['name'] === '..') { $s_name = 'a'; }

				$body[] = array(
					's_name'   => $s_name,
					'type'     => $folders[$i]['link'],
					'name'     => $folders[$i]['name'],
					'filesize' => '&#160;',
					'date'     => ($folders[$i]['date'] !== '') ? '<span>'.$folders[$i]['date'].'</span>' : '&#160;',
					);
			}
		}

		// get files only if dir is not fake root (..)
		if($this->dir !== '..') {
			$deny = array();
			if( isset ( $this->deny['files'] ) ) {
				$deny = $this->deny['files'];
			}
			$files = $this->__pc->file->get_files( $this->__path, $deny, $this->__pc->allow['files'], $filter);
			$count_files = count( $files );
			for($i=0; $i<$count_files; $i++) {
				$label = $files[$i]['name'];
				if(isset($this->__pc->substr) && is_integer($this->__pc->substr) && $this->__pc->substr !== false) {
					$label = substr($label, 0, $this->__pc->substr);
					strlen($label) < strlen($files[$i]['name']) ? $label = $label.'...' : null;
				}
				$link = $this->__pc->html->a();
				$link->css = 'file';
				$link->label = '<span class="icon icon-file"></span>'.$label;
				$link->title = $files[$i]['name'];

				if($files[$i]['read'] === true) {
						$link->href = $this->__pc->html->thisfile
							.$this->__pc->response->get_string($this->__pc->response->actions_name, 'edit', '?', true ).'&'
							.$this->__pc->response->identifier.'='.urlencode($files[$i]['name']);
				}
				elseif ($files[$i]['read'] !== true) {
					$table->identifier_disabled[] = $files[$i]['name'];
					$link->title = $this->__pc->file->lang['permission_denied'];
					$link->css = 'file denied';
				}
				// allow edit when download is allowed
				if ($this->__pc->allow['download'] !== true) {
					$link = $this->__pc->html->div();
					$link->css = 'file';
					$link->add($label);
					$link->title = $files[$i]['name'];
				}

				##### TODO
				// handlers
				$handlers = '';
				if(isset($this->handlers) && is_array($this->handlers)) {
					$handlerdata = array(
						'root' => $this->root,
						'dir'  => $this->dir,
						'file' => $files[$i]['name']
					);
					foreach($this->handlers as $obj) {
						if(method_exists($obj, 'select')) {
							$handlers .= $obj->select($handlerdata);
						}
					}
				}

				$body[] = array(
					's_name'    => 'f '.$files[$i]['name'],
					'type'      => $link->get_string().''.$handlers,
					'name'      => $files[$i]['name'],
					'filesize'  => $files[$i]['filesize'],
					'date'      => '<span>'.$files[$i]['date'].'</span>',
					);
			}
		}

		$table->css                 = 'htmlobject_table table table-bordered';
		$table->sort                = 's_name';
		$table->border              = 0;
		$table->head                = $head;
		$table->body                = $body;
		$table->autosort            = true;
		$table->identifier          = 'name';
		$table->identifier_name     = $this->__pc->identifier_name;
		$table->handler_tr          = $this->__pc->handler_tr;

		if($this->dir === '..') {
			foreach($this->__root as $root) {
				$table->identifier_disabled[] = $root['name'];
			}
		}
		$table->actions             = $this->__actions;
		$table->actions_name        = $prefix.'['.$this->__pc->actions_name.']';
		$table->max                 = count($body);

		$table->limit_select        = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
				);


		$form = $this->get_form();
		$form->add($table, 'table');
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get form
	 *
	 * @access protected
	 * @return htmlobject_formbuilder
	 */
	//--------------------------------------------
	function get_form() {
		$form = $this->__pc->response->get_form(null, null, false);
		$co        = $this->__pc->html->input();
		$co->type  = 'submit';
		$co->value = $this->__pc->lang['folder']['lang_paste'];
		$co->style = 'visibility:show; margin: 0 0 0 0;';
		$co->css   = 'form-control btn btn-default';
		$co->name  = $this->__pc->__prefix.'['.$this->__pc->actions_name.']';
		if( !isset($_SESSION['copy']) || count($_SESSION['copy']) < 1 ) {
			$co->style = 'visibility:hidden; margin: 0 0 0 0;';
		}

		$cl        = $this->__pc->html->button();
		$cl->type  = 'submit';
		$cl->value = $this->__pc->lang['folder']['lang_clear'];
		$cl->label = '&#160;';
		$cl->title = $this->__pc->lang['folder']['lang_clear'];
		$cl->style = 'visibility:show; margin: 0 0 0 0;';
		$cl->css   = 'form-control btn btn-default';
		$cl->name  = $this->__pc->__prefix.'['.$this->__pc->actions_name.']';
		$cl->handler = 'onclick="phppublisher.wait();"';
		if( !isset($_SESSION['copy']) || count($_SESSION['copy']) < 1 ) {
			$cl->style = 'visibility:hidden; margin: 0 0 0 0;';
		}

		if($this->__pc->allow['new'] === true) {
			$nd        = $this->__pc->html->input();
			$nd->type  = 'submit';
			$nd->name  = $this->__pc->__prefix.'['.$this->__pc->actions_name.']';
			$nd->value = $this->__pc->lang['select']['new'];
			$nd->css   = 'form-control btn btn-default';
			// disable button if path is not writable
			// or dir is fake root (..)
			if(!$this->__pc->file->is_writeable($this->__path) || $this->dir === '..') {
				$nd->disabled = true;
				$co->disabled = true;
			}
			#$form->add($nf, 'new_file');
			$form->add($nd, 'new');
			if($this->__pc->allow['dir'] !== true) {
				$form->add('', 'new_folder');
			}
		} else {
			$form->add('', 'new');
		}
		$form->add($co, 'copy');
		$form->add($cl, 'clear');
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get folders
	 *
	 * @access protected
	 * @return array
	 */
	//--------------------------------------------
	function __folder_array($folders, $substr = null) {
		$strDirs = '';
		$path    = $this->__path;
		$params =  $this->__pc->response->get_array($this->__pc->response->actions_name, 'select');
		unset($params[$this->__pc->__prefix]['offset']);
		unset($params[$this->__pc->__prefix]['order']);
		unset($params[$this->__pc->__prefix]['sort']);
		unset($params[$this->__pc->__prefix]['action']);
		unset($params[$this->__pc->__prefix]['dir']);

		$down = array();
		$dirs = array();

		if($this->dir !== '..') {
			$dirs    = array();
			foreach($folders as $dir) {
				$dirs[] = str_replace($this->__root[$this->root]['path'].'/', '', $dir);
			}
			$down = explode('/', $this->dir);
			if($down[0] === ''){
				$down[0] = '..';
			} else {
				array_pop($down);
			}
			if($this->dir !== '' || count($this->__root) > 1) {
				$down_path = array(array('path' => join('/', $down), 'name' => '..', 'date' => '', 'read' => true));
				$arDirs    = array_merge($down_path, $dirs);
			} else {
				$arDirs = $dirs;
			}
			
			foreach($arDirs as $key => $value) {
				$p = $params;
				$p[$this->__pc->__prefix]['dir']  = str_replace( '//', '/',$value['path'] );
				$p = $this->__pc->html->response()->get_params_string($p, '?', true);

				$label = $value['name'];
				if(isset($substr)) {
					$label = substr($label, 0, $substr);
				}
				else if(isset($this->__pc->substr) && is_integer($this->__pc->substr) && $this->__pc->substr !== false) {
					$label = substr($label, 0, $this->__pc->substr);
				}
				$label = strlen($label) < strlen($value['name']) ? $label.'...' : $label;

				$link = $this->__pc->html->a();
				$link->label = '<span class="icon icon-folder"></span>'.$label;
				$link->css   = 'folder';
				$link->title = $value['name'];


				$handlers = '';
				if(isset($this->handlers['folder'])) {
					foreach ($this->handlers['folder'] as $v) {
						if(isset($v['extension'])) {
							if(strripos($value['name'], $v['extension']) !== false) {
								$a = $this->__pc->response->html->a();
								$a->href = $this->__pc->response->get_url('handler', $v['handler']).'&file='.$value['name'];
								$a->label = $v['handler'];
								$handlers .= $a->get_string();
							}
						}
					}
				}


				if($value['read'] == true) {
					$link->href  = $this->__pc->html->thisfile.$p;
				} else {
					$link->css = 'folder denied';
					$link->title = $this->__pc->file->lang['permission_denied'];
				}
				$arDirs[$key]['link'] = $link->get_string().''.$handlers;
			}
		} else {
			foreach($this->__root as $key => $dir) {
				$p = $params;
				$p[$this->__pc->__prefix]['dir']  = '';
				$p[$this->__pc->__prefix]['root'] = $key;
				$p = $this->__pc->html->response()->get_params_string($p, '?', true);	

				$link = $this->__pc->html->a();
				$link->href  = $this->__pc->html->thisfile.$p;
				$link->label = '<span class="icon icon-folder" style="padding-right: 10px;"></span>'.$dir['name'];
				#$link->css   = 'folder';
				$link->title = $dir['name'];

				$arDirs[] = array(
					'read' => isset($dir['read']) ? $dir['read'] : '',
					'path' => $key,
					'name' => $dir['name'],
					'date' => '',
					'link' => $link,
				);
			}
		}
		return $arDirs;
	}

}
?>
