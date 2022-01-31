<?php
/**
 * PHPCommander Insert
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_insert
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($path, $response, $file, $root) {
		$this->__path     = $path;
		$this->__response = $response;
		$this->__file     = $file;
		$this->__root     = $root;
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action( $type ) {
		if($type === 'folder') {
			return $this->insert_folder();
		}
		if($type === 'file') {
			return $this->insert_file();
		}
	}

	//--------------------------------------------
	/**
	 * Insert file (new file)
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert_file() {
		$response = $this->get_response('insert_file');
		$form     = $response->form;
		$file 	  = $response->html->request()->get('new_file');
		if(
			!$form->get_errors() 
			&& $file !== ''
			&& $response->submit() 
		) {
			$path  = $this->__path.'/'.$file;
			$error = $this->__file->mkfile($path, '');
			if($error !== '') {
				$form->set_error('new_file',$error);
				$response->error = $error;
			} else {
				$response->msg = sprintf($response->lang['file']['lang_saved'], $file);
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Insert folder (new directory)
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert_folder() {
		$response = $this->get_response('insert_folder');
		$form     = $response->form;
		$file     = $response->html->request()->get('new_dir');
		if(
			!$form->get_errors() 
			&& $file !== ''
			&& $response->submit()
		 ) {
			$path  = $this->__path.'/'.$file;
			$error = $this->__file->mkdir($path, '');
			if($error !== '') {
				$form->set_error('new_dir',$error);
				$response->error = $error;
			} else {
				$response->msg = sprintf($response->lang['folder']['lang_saved'], $file);
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @param enum $action [insert_file|insert_folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response( $action ) {
		$response = $this->__response;
		$form = $response->get_form($response->actions_name, $action);
		$regex_filename = $this->__file->regex_filename;
		if($action === 'insert_file') {
			$d['name']['label']                    = $response->lang['file']['lang_new'];
			$d['name']['required']                 = true;
			$d['name']['css']                      = 'autosize float-right';
			$d['name']['validate']['regex']        = '/^'.$regex_filename.'+$/i';
			$d['name']['validate']['errormsg']     = 'string must be '.$regex_filename;
			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['type'] = 'text';
			$d['name']['object']['attrib']['name'] = 'new_file';
			$d['name']['object']['attrib']['id']   = 'new_file';
		}
		if($action === 'insert_folder') {
			$d['name']['label']                    = $response->lang['folder']['lang_new'];
			$d['name']['required']                 = true;
			$d['name']['css']                      = 'autosize float-right';
			$d['name']['validate']['regex']        = '/^'.$regex_filename.'+$/i';
			$d['name']['validate']['errormsg']     = 'string must be '.$regex_filename;
			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['type'] = 'text';
			$d['name']['object']['attrib']['name'] = 'new_dir';
			$d['name']['object']['attrib']['id']   = 'new_dir';
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
