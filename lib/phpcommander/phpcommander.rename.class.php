<?php
/**
 * PHPCommander Rename
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */


class phpcommander_rename
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct($path, $response, $file) {
		$this->__path     = $path;
		$this->__response = $response;
		$this->__file     = $file;
	}

	//--------------------------------------------
	/**
	 * Action rename
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$msg      = array();
		$response = $this->get_response();
		$form     = $response->form;
		$files    = $response->html->request()->get($response->identifier);
		if($files === '') {
			$files = $response->html->request()->get('old');
		}
		$new = $response->html->request()->get('new');
		if($files !== '') {
			if(!$form->get_errors() && $response->submit() ) {
				foreach($files as $key => $file) {
					$path   = $this->__path.'/'.$file;
					$target = $this->__path.'/'.$new[$key];
					$error = $this->__file->move($path, $target);
					if($error !== '') {
						$form->set_error("new[$key]", $error);
					} else {
						$form->remove("old[$key]");
						$form->remove("new[$key]");
						$msg[] = sprintf($response->lang['file']['lang_renamed'], $file, $new[$key]);
					}
				}
				if(!$form->get_errors()) {
					$response->msg = $msg;
				} else {
					$response->error = $form->get_errors() + $msg;
				}
			}
			else if($form->get_errors()) {
				$response->error = $form->get_errors();
			}
		}
		else if($files === '' && !$form->get_errors()) {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->__response;
		$form     = $response->get_form($response->actions_name, 'rename');
		$files    = $response->html->request()->get($response->identifier);
		if($files === '') {
			$files = $response->html->request()->get('old');
		}
		if( $files !== '' ) {
			$regex_filename = $this->__file->regex_filename;
			$i = 0;
			if(!is_array($files)){ $files = array($files); }	
			foreach($files as $file) {

				$d['param_f'.$i]['object']['type']            = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']  = 'hidden';
				$d['param_f'.$i]['object']['attrib']['name']  = 'old['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value'] = $file;

				$d['param_n'.$i]['label']                     = $file;
				$d['param_n'.$i]['required']                  = true;
				$d['param_n'.$i]['validate']['regex']         = '/^'.$regex_filename.'+$/i';
				$d['param_n'.$i]['validate']['errormsg']      = 'string must be '.$regex_filename;
				$d['param_n'.$i]['object']['type']            = 'htmlobject_input';
				$d['param_n'.$i]['object']['attrib']['type']  = 'text';
				$d['param_n'.$i]['object']['attrib']['name']  = 'new['.$i.']';
				$d['param_n'.$i]['object']['attrib']['value'] = $file;
				++$i;
			}
			$form->add($d);
		}
		$response->form = $form;		
		return $response;
	}

}
?>
