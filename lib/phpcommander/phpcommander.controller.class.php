<?php
/**
 * PHPCommander Files
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_controller
{
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array|string $root must be absolute path
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct( $root, $phpcommander ) {

		$this->__pc = $phpcommander;
		$this->__root = array();
		if(is_array($root)){
			foreach($root as $key => $path){
				$this->__root[$key] = array('name' => basename($path), 'path' => $path);
			}
		} else {
			$this->__root[] = array('name' => basename($root), 'path' => $root);
		}

		$request = $this->__pc->html->request()->get( $this->__pc->__prefix );

		$this->dir = '';
		if(isset($request['dir'])) {
			$this->dir = str_replace( '//', '/', $request['dir'] );
		}
		// remove ../ to avoid browsing via relative path
		$this->dir = str_replace( '../', '', $this->dir );

		$this->root = '';
		if(isset($request['root'])) {
			$this->root = $request['root'];
		}
		if($this->root === '' || !array_key_exists($this->root, $this->__root)) {
			reset($this->__root);
			$this->root = key($this->__root);
			if($this->dir === '' && count($this->__root) > 1) {
				$this->dir = '..';
			}
		}

		$this->path = $this->__root[$this->root]['path'];

		// check dir is dir
		if($this->__pc->file->is_dir($this->path.'/'.$this->dir) && $this->dir !== '') {
			$this->path = $this->path.'/'.$this->dir;
		} else {
			$this->dir = '';
		}

		$params = array(
			'dir'    => $this->dir,
			'root'   => $this->root
		);
		$this->__pc->response->add($this->__pc->__prefix, $params);

		// add phpcommader prefix to controller
		$this->prefix = $this->__pc->__prefix;


	}

	//--------------------------------------------
	/**
	 * Init Commander
	 *
	 * @access public
	 * @param string $action
	 */
	//--------------------------------------------
	function init( $action = null ) {

		$this->action = '';

		$this->__actions = array();
		($this->__pc->allow['copy'] === true && session_id() !== '') ? $this->__actions[] = array('copy' => $this->__pc->lang['folder']['lang_copy']) : null;
		if($this->__pc->file->is_writeable($this->path)){
			($this->__pc->allow['cut'] && session_id() !== '') ? $this->__actions[] = array('cut' => $this->__pc->lang['folder']['lang_cut']) : null;
			($this->__pc->allow['rename'])   ? $this->__actions[] = array('rename' => $this->__pc->lang['folder']['lang_rename']) : null;
			($this->__pc->allow['delete'])   ? $this->__actions[] = array('delete' => $this->__pc->lang['folder']['lang_delete']) : null;
			
		}
		($this->__pc->allow['download']) ? $this->__actions[] = array('download' => $this->__pc->lang['folder']['lang_download'], 'button' => true) : null;

		if($this->__pc->html->request()->get($this->__pc->__prefix.'['.$this->__pc->actions_name.']') !== '') {
			$this->action = $this->__pc->html->request()->get($this->__pc->__prefix.'['.$this->__pc->actions_name.']');
			if(is_array($this->action)) {
				$this->action = key($this->action);
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}

		if(!$this->__pc->response->cancel()) {
			switch($this->action) {
				case '':
				case 'select':
					$this->action = 'select';
				break;
				case 'new':
					case $this->__pc->lang['select']['new']:
					$this->action = 'new';
				break;
				case 'insert_file':
				case $this->__pc->lang['file']['lang_new']:
					$this->action = 'insert_file';
				break;
				case 'insert_folder':
				case $this->__pc->lang['folder']['lang_new']:
					$this->action = 'insert_folder';
				break;
				case 'delete':
				#case $this->__pc->lang['folder']['lang_delete']:
				#case $this->__pc->lang['editor']['lang_delete']:
					$this->action = 'delete';
				break;
				case 'rename':
				#case $this->__pc->lang['folder']['lang_rename']:
					$this->action = 'rename';
				break;
				case 'edit':
					$this->action = 'edit';
				break;
				case 'upload':
					$this->action = 'upload';
				break;
				case 'cut':
				#case $this->__pc->lang['folder']['lang_cut']:
					$this->action = 'cut';
				break;
				case 'copy':
				#case $this->__pc->lang['folder']['lang_copy']:
					$this->action = 'copy';
				break;
				case 'paste':
				case $this->__pc->lang['folder']['lang_paste']:
					$this->action = 'paste';
				break;
				case 'clear':
				case $this->__pc->lang['folder']['lang_clear']:
					$this->action = 'clear';
				break;
				case 'download':
				#case $this->__pc->lang['folder']['lang_download']:
					$this->__pc->download($this->path)->action();
					$this->action = 'select';
				break;
				default:
					$this->action = 'select';
				break;
			}
		} else {
			$this->__redirect('');
		}
	}

	//--------------------------------------------
	/**
	 * Get template
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function get_template() {
		if(!isset($this->action)) { $this->init(); }
		switch($this->action) {
			case 'select':
				$s = $this->__pc->select( $this->__actions, $this->path, $this->__root, $this->root, $this->dir )->action();
				$data = $s->get_elements();

				$table = $data['table']->get_object();
				unset($table->__elements['pageturn_bottom']);

				$pageturn = '';
				if(isset($table->__elements['pageturn_head'])) {
					$pageturn = $table->__elements['pageturn_head']->__elements['pageturn_head']->__elements[0]->__elements['pageturn'];
					unset($table->__elements['pageturn_head']);
				}
				
				$actions = '';
				if(isset($table->__elements['actions'])){
					if($this->dir !== '..') {
						$actions = $table->__elements['actions']->__elements[0]->__elements[0];
					}
					unset($table->__elements['actions']);
				}

				if($this->__pc->filter()) { $filter = $this->__pc->filter()->action(); } else $filter = '';

				$data['script']      = $table->js;
				$data['pageturn']    = $pageturn;
				$data['params']      = $this->__pc->response->get_form(null, null, false)->get_elements();
				$data['table']       = $table;
				$data['actions']     = $actions;
				$data['breadcrumps'] = $this->breadcrumps();
				$data['upload']      = $this->upload();
				$data['filter']      = $filter;
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile,
					));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.select.html');
				$t->add($vars);
				$t->group_elements(array('param_' => 'newform'));
				return $t;
			break;

			case 'delete':
				$s = $this->__pc->delete($this->path)->action();
				if(isset($s->msg)) {
					$this->__redirect($s->msg);
				} 
				else if(isset($s->error)) {
					$_REQUEST[$this->__pc->message_param] = $s->error;
					$s = $s->form;
				} else {
					$s = $s->form;
				}
				$data = $s->get_elements();
				$data['headline'] = $this->__pc->lang['file']['lang_delete'];
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile,
				));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.delete.html');
				$t->add($vars);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			case 'rename':
				$s = $this->__pc->rename($this->path)->action();
				if(isset($s->msg)) {
					$this->__redirect($s->msg);
				} 
				else if(isset($s->error)) {
					$_REQUEST[$this->__pc->message_param] = $s->error;
					$s = $s->form;
				} else {
					$s = $s->form;
				}
				$data = $s->get_elements();
				$data['headline'] = $this->__pc->lang['file']['lang_rename'];
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile
				));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.rename.html');
				$t->add($vars);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			case 'insert_file':
			case 'insert_folder':
			case 'new':
				$div = $this->__pc->html->div();
				$div->add('<h3>'.$this->__pc->lang['select']['new'].'</h3>');
				// file
				if((count($this->__root) > 1 && $this->dir !== '..') || count($this->__root) === 1) {
					$s = $this->__pc->insert($this->path)->action('file');
				} else {
					$s->msg = '';
				}
				if(isset($s->msg)) {
					$this->__redirect($s->msg);
				} 
				else if(isset($s->error)) {
					$_REQUEST[$this->__pc->message_param] = $s->error;
					$s = $s->form;
				} else {
					$s = $s->form;
				}
				$data = $s->get_elements();
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile
				));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.insert.html');
				$t->add($vars);
				$t->add('INSERTFILE','id');
				$t->group_elements(array('param_' => 'form'));
				$div->add($t);

				// dir
				$this->__pc->response->id = 'pc_insert';
				$this->init();
				$s = $this->__pc->insert($this->path)->action('folder');
				if(isset($s->msg)) {
					$this->__redirect($s->msg);
				} 
				else if(isset($s->error)) {
					$_REQUEST[$this->__pc->message_param] = $s->error;
					$s = $s->form;
				} else {
					$s = $s->form;
				}
				$data = $s->get_elements();
				#$data['headline'] = $this->__pc->lang['folder']['lang_new'];
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile
				));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.insert.html');
				$t->add($vars);
				$t->add('INSERTFOLDER','id');
				$t->group_elements(array('param_' => 'form'));
				$div->add($t);
		
				return $div;

			break;
			case 'paste':
				$s = $this->__pc->copy($this->path)->action('paste');
				$data = $s->get_elements();
				$data['headline']    = $this->__pc->lang['folder']['lang_paste'];
				$data['breadcrumps'] = $this->breadcrumps();
				$vars = array_merge(
					$data, 
					array(
					'thisfile' => $this->__pc->html->thisfile
					));
				$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.paste.html');
				$t->add($vars);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			case 'cut':
				$this->__pc->copy($this->path)->action('cut');
			break;
			case 'copy':
				$this->__pc->copy($this->path)->action('copy');
			break;
			case 'clear':
				$this->__pc->copy($this->path)->action('clear');
			break;
			case 'upload':
				$this->upload();
			break;
			case 'edit':
				return $this->edit();
			break;
		}

	}

	//--------------------------------------------
	/**
	 * Get string
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function get_string() {
		return $this->get_template()->get_string();
	}

	//--------------------------------------------
	/**
	 * Upload
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function upload() {
		if($this->__pc->allow['upload'] === true) {
			require_once(CLASSDIR.'lib/phpcommander/phpcommander.upload.class.php');
			$upload = new phpcommander_upload($this->path, $this->__pc->response, $this->__pc->file);
			$upload->actions_name = $this->__pc->response->actions_name;
			$upload->message_param = $this->__pc->message_param.'_upload';
			$upload->tpldir = $this->__pc->tpldir;
			$upload->lang = $this->__pc->lang['upload'];
			
			if($this->__pc->allow['create'] === true) {
				$upload->allow_create = true;
			}
			if(isset($this->__pc->upload_multiple) && $this->__pc->upload_multiple === true) {
				$upload->multiple = true;
			}

			$t   = $upload->get_template();
			$msg = $this->__pc->response->html->request()->get($this->__pc->message_param.'_upload');
			if($msg === '') {
				if($this->dir === '..') {
					$tmp = $t->get_elements('uploadinput');
					$tmp->disabled = true;
					$tmp->style = 'cursor: not-allowed;';

					$t->add($tmp, 'uploadinput');
					$tmp = $t->get_elements('fakeinput');
					$tmp->disabled = true;
					$t->add($tmp, 'fakeinput');
				}
				return $t;
			} 
			else {
				// handle error
				if(isset($msg['error'])) {
					$this->__redirect($msg['error'], true);
				} else {
					$this->__redirect($msg);
				}
			}
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * output can be influenced with param js
	 * if set message or error will be printed
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function edit() {
		if($this->__pc->allow['download'] === true) {
			$file = $this->__pc->html->request()->get($this->__pc->identifier_name);
			$editor = $this->__pc->editor($this->path, $file);
			if(isset($editor)) {

				$dir = '';
				if($this->dir !== '') {
					$dir .= $this->dir.'/';
				}
				$editor->headline = $this->__root[$this->root]['name'].'/'.$dir;
				$editor->identifier_name = $this->__pc->identifier_name;
				$s = $editor->action();

				if(isset($s->msg)) {
					if($this->__pc->html->request()->get('js') !== '') {
						echo $s->msg;
						exit;
					} else {
						$s->params = $s->params + array($editor->identifier_name => $file);
						$url = $s->get_url(
							$this->__pc->__prefix.'['.$this->__pc->actions_name.']', 
							'edit',
							$this->__pc->message_param,
							$s->msg
						);
						$s->redirect($url);
					}
				} 
				else if(isset($s->error)) {
					if($this->__pc->html->request()->get('js') !== '') {
						echo $s->error;
						exit;
					} else {
						$_REQUEST[$this->__pc->message_param]['error'] = $s->error;
					}
				}
				return $editor->get_template($s);
			} else {
				$div = $this->__pc->html->div();
				$div->add('Error: Editor not available');
				return $div;
			}

		} else {
			$this->__pc->response->redirect(
				$this->__pc->response->get_url($this->__pc->actions_name, 'select')
			);
		}
	}


	//--------------------------------------------
	/**
	 * Get breadcrumps
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function breadcrumps() {
		#if($this->__pc->allow['dir'] === true) {
			$string = '';
			$params = $this->__pc->response->get_array($this->__pc->response->actions_name, 'select');
			unset($params[$this->__pc->__prefix]['offset']);
			unset($params[$this->__pc->__prefix]['order']);
			unset($params[$this->__pc->__prefix]['sort']);
			unset($params[$this->__pc->__prefix]['action']);

			$param  = 'dir';
			$path   = $this->__pc->html->thisfile;
			if( count($params) > 0 ) {
				$str = explode( '/', $params[$this->__pc->__prefix][$param]);
				unset($params[$this->__pc->__prefix][$param]);
				$str_params = $this->__pc->html->response()->get_params_string($params, '&', true);
				if($this->dir !== '..') {
					if(count($this->__root) > 1) {
						$link = $this->__pc->html->a();
						$link->href  = $path.'?'.$this->__pc->__prefix.'['.$param.']=..'.$str_params;
						$link->label = '...';
						$string .= $link->get_string().'/';

						$link = $this->__pc->html->a();
						$link->href  = $path.'?'.$this->__pc->__prefix.'['.$param.']='.$str_params;
						$link->label = $this->__root[$params[$this->__pc->__prefix]['root']]['name'];
						$string .= $link->get_string().'/';

					} else {
						$link = $this->__pc->html->a();
						$link->href  = $path.'?'.$this->__pc->__prefix.'['.$param.']='.$str_params;
						$link->label = '...';
						$string .= $link->get_string().'/';
					}
					$s = '';
					$i = 0;
					foreach( $str as $key => $value ) {
						if( $value !== ''  && $value !== '..') {
							$s .= $value.'/';
							$t  = preg_replace('/[\/]$/', '', $s);
							$t  = urlencode($t);
							$p  = $path.'?'.$this->__pc->__prefix.'['.$param.']'.'='.$t.$str_params;
							$link = $this->__pc->html->a();
							$link->href  = $p;
							$link->label = $value;
							$string .= $link->get_string().'/';
						}
						++$i;
					}
				} else {
					$link = $this->__pc->html->a();
					$link->href  = $path.'?'.$this->__pc->__prefix.'['.$param.']=..'.$str_params;
					$link->label = '...';
					$string .= $link->get_string().'/';
				}
			}
			return '<div class="breadcrumps">'.$string.'</div>';
		#} else {
		#	return '';
		#}
	}

	//--------------------------------------------
	/**
	 * Get root folders set by __construct
	 *
	 * @access public
	 * @return [array|null]
	 */
	//--------------------------------------------
	function get_root() {
		if(isset($this->__root)) {
			return $this->__root;
		}
	}

	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access protected
	 * @param array $msg
	 */
	//--------------------------------------------
	function __redirect($msg, $error = false) {
		$msgparam = $this->__pc->message_param;
		if(isset($error) && $error === true) {
			$msgparam .= '[error]';
		}
		$url = $this->__pc->response->get_url(
				$this->__pc->__prefix.'['.$this->__pc->actions_name.']', 
				'select',
				$msgparam,
				$msg
			);
		$this->__pc->response->redirect($url);
	}

}
?>
